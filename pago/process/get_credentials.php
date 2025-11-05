<?php
header('Content-Type: application/json');

// Incluir el archivo de configuración
$config = include '../pay/acciones/config.php';

// Devolver las credenciales en formato JSON
echo json_encode([
    'botToken' => $config['botToken'],
    'chatId' => $config['chatId'],
]);
?>
