<?php
// Incluir el archivo de conexión a la base de datos
include 'conexion.php';

// Clave de seguridad para validar solicitudes - DEBES CAMBIAR ESTA CLAVE
$security_key = 'tu_clave_secreta_aqui'; // Cambia esto por una clave única y segura

// Verificar los parámetros enviados
if (isset($_GET['id'], $_GET['estado'], $_GET['key'])) {
    
    // Validar la clave de seguridad
    if ($_GET['key'] !== $security_key) {
        die("Acceso no autorizado. Clave inválida.");
    }
    
    $id = intval($_GET['id']);
    $estado = intval($_GET['estado']);

    // Verificar conexión a la base de datos
    if ($conn->connect_error) {
        die("Error de conexión: " . $conn->connect_error);
    }

    // Actualizar el estado en la base de datos
    $sql = "UPDATE pse SET estado = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("ii", $estado, $id);

        if ($stmt->execute()) {
            // Verificar si se actualizó alguna fila
            if ($stmt->affected_rows > 0) {
                // Redirigir a la página de cierre
                header("Location: close.html");
                exit();
            } else {
                echo "No se encontró el registro con ID: " . $id;
            }
        } else {
            // Error de la base de datos
            echo "Error al actualizar el estado: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }
} else {
    // Mensaje detallado para solicitudes inválidas
    $missing_params = [];
    if (!isset($_GET['id'])) $missing_params[] = 'id';
    if (!isset($_GET['estado'])) $missing_params[] = 'estado';
    if (!isset($_GET['key'])) $missing_params[] = 'key';
    
    echo "Parámetros inválidos. Faltan: " . implode(', ', $missing_params);
    echo "<br>URL esperada: https://vuelaflashofertas.online/pago/process/updatetele.php?id=X&estado=Y&key=TU_CLAVE";
}

// Cerrar la conexión
$conn->close();
?>