<?php
// Habilitar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json'); // ¡Importante! Enviar JSON

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

// Obtener el ID del cliente desde la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "ID no proporcionado."]);
    exit();
}

try {
    // Consultar el estado del cliente (Esta lógica está perfecta)
    $stmt = $pdo->prepare("SELECT estado FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $estado = $stmt->fetchColumn();

    if ($estado === false) {
        http_response_code(404);
        echo json_encode(["error" => "Cliente no encontrado."]);
        // Devolvemos un estado 'null' o 'error' para que el JS lo maneje
        // echo json_encode(["estado" => null]); 
        exit();
    }

    // Responder con el estado del cliente
    echo json_encode(["estado" => (int)$estado]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al consultar el estado: " . $e->getMessage()]);
}

$pdo = null; // Cerrar conexión
?>