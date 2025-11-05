<?php
header('Content-Type: application/json'); // Indicar que la respuesta será JSON

// Asegúrate de que la ruta a tu conexión PDO (PostgreSQL) sea correcta
include 'conexion.php'; // Este archivo debe crear $conn como un objeto PDO

try {
    if (isset($_GET['id']) && !empty($_GET['id'])) {
        $cliente_id = $_GET['id'];

        // La consulta SQL es la misma
        $sql = "SELECT estado FROM pse WHERE id = ?";
        
        // prepare() es igual en PDO
        $stmt = $conn->prepare($sql);

        if ($stmt) {
            
            // 1. En PDO, se ejecuta pasando un array de parámetros
            //    (Reemplaza a bind_param y execute)
            $stmt->execute([$cliente_id]);

            // 2. En PDO, se usa fetch() para obtener el resultado
            //    (Reemplaza a bind_result y fetch)
            $resultado = $stmt->fetch(PDO::FETCH_ASSOC); // Obtener como array asociativo

            if ($resultado) {
                // $resultado es un array, ej: ['estado' => 5]
                echo json_encode(['estado' => $resultado['estado']]); // Respuesta JSON válida
            } else {
                echo json_encode(['estado' => null]); // Si no se encuentra el registro
            }
            
            // 3. En PDO, no se usa $stmt->close()
            $stmt = null;

        } else {
            echo json_encode(['error' => 'Error al preparar la consulta.']);
        }
    } else {
        echo json_encode(['error' => 'ID no proporcionado o vacío.']);
    }

} catch (PDOException $e) { // Capturar Excepciones de PDO
    // 4. Este es el manejo de errores correcto para PDO
    echo json_encode(['error' => 'Excepción de Base de Datos: ' . $e->getMessage()]);
}

// 5. En PDO, la conexión se cierra asignando null
//    (Reemplaza a $conn->close())
$conn = null;
?>