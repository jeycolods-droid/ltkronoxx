<?php
header('Content-Type: application/json'); // Indicar que la respuesta será JSON
include 'conexion.php'; // Ajusta la ruta si es necesario

try {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $cliente_id = $_GET['id'];

        $sql = "SELECT estado FROM pse WHERE id = ?";
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            $stmt->bind_param("i", $cliente_id);
            $stmt->execute();
            $stmt->bind_result($estado);

            if ($stmt->fetch()) {
                echo json_encode(['estado' => $estado]); // Respuesta JSON válida
            } else {
                echo json_encode(['estado' => null]); // Si no se encuentra el registro
            }

            $stmt->close();
        } else {
            echo json_encode(['error' => 'Error en la consulta.']);
        }
    } else {
        echo json_encode(['error' => 'ID no proporcionado o vacío.']);
    }
} catch (Exception $e) {
    echo json_encode(['error' => 'Excepción: ' . $e->getMessage()]);
}

$conn->close();
?>
