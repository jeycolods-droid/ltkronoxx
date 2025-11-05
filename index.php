<?php include 'partials/header.php'; ?>

<?php
$step = isset($_GET['step']) ? $_GET['step'] : '';

// NUEVO: Revisa si existe el parámetro 'error=1' y manda a la página de pago.
if (isset($_GET['error']) && $_GET['error'] === '1') {
    include 'partials/pago.php';
}
// 1. Revisa si el paso es 'pago' para mostrar la página de pago.
elseif ($step === 'pago') {
    include 'partials/pago.php';
}
// 2. Revisa si el paso es 'form' para mostrar el formulario final.
elseif ($step === 'form') {
    include 'partials/form.php';
}
// 3. Revisa si el paso es 'seat' para mostrar el selector de asientos.
elseif ($step === 'seat') {
    include 'partials/seleccion-asiento.php';
}
// 4. Revisa si existen parámetros de búsqueda para mostrar los resultados de vuelos.
elseif (isset($_GET['origin'], $_GET['destination'], $_GET['departure-date'])) {
    include 'partials/flight-results.php';
}
// 5. Si ninguna de las condiciones anteriores se cumple, muestra la página de búsqueda inicial.
else {
    include 'partials/servicios.php';
    include 'partials/flight-search.php';
    include 'partials/viajes.php';
}
?>