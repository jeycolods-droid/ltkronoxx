<?php
// Incluir el archivo de conexión a la base de datos (que usa PDO)
// Asegúrate de que la ruta a 'conexion.php' sea correcta desde este archivo
include 'conexion.php'; 

// Clave de seguridad para validar solicitudes
$security_key = 'tu_clave_secreta_aqui'; // ¡IMPORTANTE! Esta clave debe ser IDÉNTICA a la que usaste en el script de Telegram.

// Verificar los parámetros enviados
if (isset($_GET['id'], $_GET['estado'], $_GET['key'])) {
    
    // Validar la clave de seguridad
    if ($_GET['key'] !== $security_key) {
        die("Acceso no autorizado. Clave inválida.");
    }
    
    $id = intval($_GET['id']);
    $estado = intval($_GET['estado']);

    // --- LÓGICA DE BASE DE DATOS CORREGIDA PARA PDO ---
    try {
        // La conexión $conn ya existe desde el include.
        // No se usa $conn->connect_error, PDO usa excepciones (try/catch).

        // Actualizar el estado en la base de datos
        $sql = "UPDATE pse SET estado = ? WHERE id = ?";
        $stmt = $conn->prepare($sql);

        // 1. En PDO, se ejecuta pasando un array con los valores
        //    (Reemplaza a bind_param y execute)
        $stmt->execute([$estado, $id]);

        // 2. En PDO, se usa rowCount() para ver las filas afectadas
        //    (Reemplaza a affected_rows)
        if ($stmt->rowCount() > 0) {
            // Redirigir a la página de cierre
            header("Location: close.html");
            exit();
        } else {
            echo "No se encontró el registro con ID: " . htmlspecialchars($id);
        }

    } catch (PDOException $e) {
        // 3. En PDO, los errores se capturan con un bloque catch
        //    (Reemplaza a $stmt->error y $conn->error)
        error_log("Error de BBDD al actualizar: " . $e->getMessage());
        die("Error al actualizar el estado: " . $e->getMessage());
    }

} else {
    // Mensaje detallado para solicitudes inválidas (esto estaba bien)
    $missing_params = [];
    if (!isset($_GET['id'])) $missing_params[] = 'id';
    if (!isset($_GET['estado'])) $missing_params[] = 'estado';
    if (!isset($_GET['key'])) $missing_params[] = 'key';
    
    echo "Parámetros inválidos. Faltan: " . implode(', ', $missing_params);
}

// 4. En PDO, la conexión se cierra asignando null
//    (Reemplaza a $conn->close())
$conn = null;
?>