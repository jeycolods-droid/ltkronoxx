<?php
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

// Obtener el ID del cliente desde la URL
$id = $_GET['id'] ?? null;

if (!$id) {
    http_response_code(400);
    echo json_encode(["error" => "ID no proporcionado."]);
    exit();
}

try {
    // Consultar el estado del cliente
    $stmt = $pdo->prepare("SELECT estado FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $estado = $stmt->fetchColumn();

    if ($estado === false) {
        http_response_code(404);
        echo json_encode(["error" => "Cliente no encontrado."]);
        exit();
    }

    // Responder con el estado del cliente
    echo json_encode(["estado" => (int)$estado]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Error al consultar el estado: " . $e->getMessage()]);
}
?>
