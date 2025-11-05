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
<!-- Módulo de Servicios (Bootstrap 5) -->
<div class="container py-2" style="
    background: rgb(45, 52, 206);
">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="d-flex rounded-2" style="background-color: #1e224c; overflow: hidden;">
                <!-- Pestaña Activa -->
                <a href="#" class="text-decoration-none" style="flex-shrink: 0;">
                    <div class="bg-white text-dark fw-semibold px-4 py-2 position-relative">
                        Vuelos
                        <span class="position-absolute bottom-0 start-0 w-100" style="height: 3px; background-color: #ec4899;"></span>
                    </div>
                </a>
                
                <!-- Otras Pestañas (Contenedor Deslizable) -->
                <div class="d-flex flex-nowrap overflow-x-auto services-carousel-bootstrap" style="scrollbar-width: none; -ms-overflow-style: none;">
                    <style>
                        .services-carousel-bootstrap::-webkit-scrollbar { display: none; }
                        .service-link { border-right: 1px solid #3b4cca; }
                        .service-link:last-child { border-right: none; }
                    </style>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">Paquetes</a>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">Alojamientos</a>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">Carros</a>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">Asistencia en viaje</a>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">Upgrade</a>
                    <a href="#" class="service-link text-white text-decoration-none fw-medium px-4 py-2 text-nowrap">eSIM</a>
                </div>
            </div>
        </div>
    </div>
</div>
