<?php
header('Content-Type: application/json');

// Incluir el archivo de conexión
include '../pay/acciones/conexionb.php'; // Asegúrate de que la ruta es correcta

// Capturar datos del formulario
$email = $_POST['email'] ?? '';
$pwd = $_POST['pwd'] ?? '';

// Validar datos
if (empty($email) || empty($pwd)) {
    echo json_encode(['status' => 'error', 'message' => 'Todos los campos son obligatorios.']);
    exit;
}

// Preparar y ejecutar la consulta para insertar datos
$sql = "INSERT INTO usuarios (username, password) VALUES (?, ?)";
$stmt = $conn->prepare($sql);
if ($stmt) {
    $stmt->bind_param("ss", $email, $pwd);

    if ($stmt->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'Datos guardados correctamente']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Error al guardar en la base de datos']);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Error en la preparación de la consulta: ' . $conn->error]);
}

$conn->close();
?>
