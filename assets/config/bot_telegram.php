<?php

function enviarMensajeTelegram($chatId, $mensaje, $token) {
    $url = "https://api.telegram.org/bot" . $token . "/sendMessage";
    $data = array(
        'chat_id' => $chatId,
        'text' => $mensaje
    );

    $options = array(
        'http' => array(
            'method' => 'POST',
            'header' => "Content-Type:application/x-www-form-urlencoded\r\n",
            'content' => http_build_query($data)
        )
    );

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result === false) {
        // Error al enviar el mensaje
        return false;
    }

    // El mensaje se envió correctamente
    return true;
}


?>