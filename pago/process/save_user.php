<?php
header('Content-Type: application/json'); // Indicar que la respuesta será JSON

// Incluir el archivo de conexión PDO (PostgreSQL)
// Asumo que 'conexionb.php' es tu archivo de conexión PDO
include '../pay/acciones/conexionb.php'; 

// Capturar datos del formulario
$email = $_POST['email'] ?? '';
$pwd = $_POST['pwd'] ?? '';

// Validar datos
if (empty($email) || empty($pwd)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

try {
    // Preparar y ejecutar la consulta para insertar datos
    // La consulta SQL es la misma para PostgreSQL
    $sql = "INSERT INTO usuarios (username, password) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        
        // 1. En PDO, se ejecuta pasando un array con los valores
        //    (Esto reemplaza a bind_param y execute)
        $stmt->execute([$email, $pwd]);

        // 2. Si el script llega aquí, la inserción fue exitosa
        echo json_encode(['status' => 'success', 'message' => 'Datos guardados correctamente']);

        // 3. En PDO, no se usa $stmt->close()
        $stmt = null;

    } else {
        // Esto es poco probable con PDO (lanzaría una excepción), pero por si acaso.
        echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta.']);
    }

} catch (PDOException $e) {
    // 4. En PDO, los errores se capturan con un bloque catch
    //    (Esto reemplaza a $stmt->error y $conn->error)
    echo json_encode(['status' => 'error', 'message' => 'Error al guardar: ' . $e->getMessage()]);
}

// 5. En PDO, la conexión se cierra asignando null
//    (Esto reemplaza a $conn->close())
$conn = null;
?>