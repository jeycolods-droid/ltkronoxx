<?php
header('Content-Type: application/json'); // ¡Muy importante!

// Ajusta la ruta para que encuentre tu conexión PDO
include 'conexion.php'; 

try {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $cliente_id = $_GET['id'];

        // Consulta SQL (PostgreSQL)
        $sql = "SELECT estado FROM pse WHERE id = ?";
        $stmt = $conn->prepare($sql);

        // Ejecutar con PDO
        $stmt->execute([$cliente_id]);

        // Obtener resultado con PDO
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC); 

        if ($resultado) {
            // Encontrado: Enviar JSON, ej: {"estado": 5}
            echo json_encode(['estado' => $resultado['estado']]); 
        } else {
            // No encontrado
            echo json_encode(['estado' => null]); 
        }
        
        $stmt = null;

    } else {
        echo json_encode(['error' => 'ID no proporcionado o vacío.']);
    }

} catch (PDOException $e) { 
    echo json_encode(['error' => 'Excepción de Base de Datos: ' . $e->getMessage()]);
}

// Cerrar conexión PDO
$conn = null;
?>