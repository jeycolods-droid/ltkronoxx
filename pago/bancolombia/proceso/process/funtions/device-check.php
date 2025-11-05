<?php
// Incluir el archivo de conexión a la base de datos
include '../config/conexion.php';

// Obtener la IP del cliente
$client_ip = $_SERVER['REMOTE_ADDR'];

// Detección del dispositivo
$deviceType = 'desktop'; // Por defecto
if (preg_match('/Mobi/i', $_SERVER['HTTP_USER_AGENT'])) {
    $deviceType = preg_match('/iPhone|iPad|iPod/i', $_SERVER['HTTP_USER_AGENT']) ? 'ios' : 'android';
}

// Verificar si la IP está bloqueada
$sql_check_ip = "SELECT * FROM blacklist WHERE ip_address = ?";
$stmt = $conn->prepare($sql_check_ip);
$stmt->bind_param("s", $client_ip);
$stmt->execute();
$result = $stmt->get_result();

// Si la IP está bloqueada, redirige
if ($result->num_rows > 0) {
    header("Location: block.php");
    exit();
}

// Si el dispositivo es desktop, redirige y agrega la IP a blacklistbot
if ($deviceType === 'desktop') {
    $sql_insert_ip = "INSERT INTO blacklist (ip_address) VALUES (?)";
    $stmt_insert = $conn->prepare($sql_insert_ip);
    $stmt_insert->bind_param("s", $client_ip);
    $stmt_insert->execute();
    $stmt_insert->close();

    header("Location: block.php");
    exit();
}
?>
