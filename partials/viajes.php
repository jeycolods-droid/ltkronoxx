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
<!-- Módulo de Viajes (Reescrito con Bootstrap 5) -->
<div class="container py-5">
    
    <!-- Título y filtros -->
    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-start align-items-sm-center mb-5">
        <h2 class="fw-bold mb-4 mb-sm-0">Descubre tu próximo viaje <span class="badge bg-light text-dark ms-2">5 destinos</span></h2>
        <div class="d-flex align-items-center">
            <span class="text-muted me-3">Ofertas de vuelo desde</span>
            <select class="form-select w-auto me-3">
                <option selected>Bogotá</option>
                <option>Medellín</option>
                <option>Cali</option>
            </select>
            <a href="#" class="text-primary fw-semibold text-nowrap d-none d-md-block">Ver ofertas en millas</a>
        </div>
    </div>

    <!-- Pestañas de categorías -->
    <div class="mb-5 border-bottom">
        <div id="category-carousel" class="d-flex flex-nowrap overflow-x-auto pb-2 mobile-carousel-bootstrap" style="gap: 1.5rem;">
            <a href="#" class="pb-2 text-primary fw-bold border-bottom border-primary border-2 text-decoration-none text-nowrap"><i class="bi bi-tag me-1"></i>En oferta</a>
            <a href="#" class="pb-2 text-muted text-decoration-none text-nowrap"><i class="bi bi-sun me-1"></i>Destinos playeros</a>
            <a href="#" class="pb-2 text-muted text-decoration-none text-nowrap"><i class="bi bi-buildings me-1"></i>Aventuras urbanas</a>
            <a href="#" class="pb-2 text-muted text-decoration-none text-nowrap"><i class="bi bi-cup-straw me-1"></i>Vida nocturna</a>
            <a href="#" class="pb-2 text-muted text-decoration-none text-nowrap"><i class="bi bi-image-alt me-1"></i>Retiros naturales</a>
            <a href="#" class="pb-2 text-muted text-decoration-none text-nowrap"><i class="bi bi-gem me-1"></i>Joyas Sudamericanas</a>
        </div>
    </div>

    <!-- Grid de destinos -->
    <div class="row g-4">
        
        <!-- Tarjeta de Viaje: Cartagena -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="position-relative">
                    <img src="assets/img/ctg.jpg" class="card-img-top" alt="Cartagena" style="height: 12rem; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex" style="gap: 0.5rem;">
                        <span class="badge bg-light text-dark p-2 shadow-sm">Recomendado para ti</span>
                        <span class="badge text-white p-2 shadow-sm" style="background-color: #5b21b6;">Vuelo directo</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div>
                        <h5 class="card-title fw-bold">Cartagena de Indias</h5>
                        <p class="card-text text-muted small">Solo ida 01/10/25</p>
                    </div>
                    <div class="d-flex align-items-center my-3">
                        <span class="badge text-white fw-bold p-2" style="background-color: #5b21b6;">Economy</span>
                        <span class="badge text-dark fw-semibold p-2 ms-3" style="background-color: #facc15;">✓ Acumula millas</span>
                    </div>
                    <div class="mt-auto text-end">
                         <hr class="my-2">
                         <p class="text-muted mb-0 small">Precio desde</p>
                         <p class="h5 fw-bold">COP 177.300</p>
                         <p class="small text-muted">Tasas incluidas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Viaje: Miami -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="position-relative">
                    <img src="assets/img/miami.jpg" class="card-img-top" alt="Miami" style="height: 12rem; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex" style="gap: 0.5rem;">
                        <span class="badge bg-light text-dark p-2 shadow-sm">Recomendado para ti</span>
                        <span class="badge text-white p-2 shadow-sm" style="background-color: #5b21b6;">Vuelo directo</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div>
                        <h5 class="card-title fw-bold">Miami</h5>
                        <p class="card-text text-muted small">Ida 02/09/25 - Vuelta 22/09/25</p>
                    </div>
                    <div class="d-flex align-items-center my-3">
                        <span class="badge text-white fw-bold p-2" style="background-color: #5b21b6;">Economy</span>
                        <span class="badge text-dark fw-semibold p-2 ms-3" style="background-color: #facc15;">✓ Acumula millas</span>
                    </div>
                    <div class="mt-auto text-end">
                         <hr class="my-2">
                         <p class="text-muted mb-0 small">Precio desde</p>
                         <p class="h5 fw-bold">COP 1.316.850</p>
                         <p class="small text-muted">Tasas incluidas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Viaje: Santiago -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="position-relative">
                    <img src="assets/img/chile.jpg" class="card-img-top" alt="Santiago" style="height: 12rem; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex" style="gap: 0.5rem;">
                        <span class="badge bg-light text-dark p-2 shadow-sm">Recomendado para ti</span>
                        <span class="badge text-white p-2 shadow-sm" style="background-color: #5b21b6;">Vuelo directo</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div>
                        <h5 class="card-title fw-bold">Santiago de Chile</h5>
                        <p class="card-text text-muted small">Solo ida 05/09/25</p>
                    </div>
                    <div class="d-flex align-items-center my-3">
                        <span class="badge text-white fw-bold p-2" style="background-color: #5b21b6;">Economy</span>
                        <span class="badge text-dark fw-semibold p-2 ms-3" style="background-color: #facc15;">✓ Acumula millas</span>
                    </div>
                    <div class="mt-auto text-end">
                         <hr class="my-2">
                         <p class="text-muted mb-0 small">Precio desde</p>
                         <p class="h5 fw-bold">COP 1.047.890</p>
                         <p class="small text-muted">Tasas incluidas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Viaje: Lima -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="position-relative">
                    <img src="assets/img/lima.jpg" class="card-img-top" alt="Lima" style="height: 12rem; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex" style="gap: 0.5rem;">
                        <span class="badge bg-light text-dark p-2 shadow-sm">Recomendado para ti</span>
                        <span class="badge text-white p-2 shadow-sm" style="background-color: #5b21b6;">Vuelo directo</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div>
                        <h5 class="card-title fw-bold">Lima</h5>
                        <p class="card-text text-muted small">Solo ida 11/09/25</p>
                    </div>
                    <div class="d-flex align-items-center my-3">
                        <span class="badge text-white fw-bold p-2" style="background-color: #5b21b6;">Economy</span>
                        <span class="badge text-dark fw-semibold p-2 ms-3" style="background-color: #facc15;">✓ Acumula millas</span>
                    </div>
                    <div class="mt-auto text-end">
                         <hr class="my-2">
                         <p class="text-muted mb-0 small">Precio desde</p>
                         <p class="h5 fw-bold">COP 866.790</p>
                         <p class="small text-muted">Tasas incluidas</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Tarjeta de Viaje: Cali -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 border-0 shadow-sm" style="border-radius: 1rem;">
                <div class="position-relative">
                    <img src="assets/img/cali.jpg" class="card-img-top" alt="Cali" style="height: 12rem; object-fit: cover; border-top-left-radius: 1rem; border-top-right-radius: 1rem;">
                    <div class="position-absolute bottom-0 start-50 translate-middle-x mb-2 d-flex" style="gap: 0.5rem;">
                        <span class="badge bg-light text-dark p-2 shadow-sm">Recomendado para ti</span>
                        <span class="badge text-white p-2 shadow-sm" style="background-color: #5b21b6;">Vuelo directo</span>
                    </div>
                </div>
                <div class="card-body p-4 d-flex flex-column">
                    <div>
                        <h5 class="card-title fw-bold">Cali</h5>
                        <p class="card-text text-muted small">Solo ida 22/09/25</p>
                    </div>
                    <div class="d-flex align-items-center my-3">
                        <span class="badge text-white fw-bold p-2" style="background-color: #5b21b6;">Economy</span>
                        <span class="badge text-dark fw-semibold p-2 ms-3" style="background-color: #facc15;">✓ Acumula millas</span>
                    </div>
                    <div class="mt-auto text-end">
                         <hr class="my-2">
                         <p class="text-muted mb-0 small">Precio desde</p>
                         <p class="h5 fw-bold">COP 133.750</p>
                         <p class="small text-muted">Tasas incluidas</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Tarjeta de "Ver más" -->
        <div class="col-md-6 col-lg-4">
            <div class="card h-100 align-items-center justify-content-center text-center p-4" style="border-radius: 1rem; border: 2px dashed #e5e7eb;">
                 <img src="https://placehold.co/100x100/E0F2FE/0284C7?text=✈️" alt="Avión" class="mb-4 rounded-pill" width="80">
                <h5 class="fw-semibold mt-3">¿Todavía no encuentras la oferta a tu destino?</h5>
                <a href="#" class="fw-bold text-primary">Ver más vuelos</a>
            </div>
        </div>

    </div>

    <!-- Términos y condiciones -->
    <div class="text-center mt-5">
        <a href="#" class="text-muted small">
            Términos y condiciones generales <i class="bi bi-box-arrow-up-right"></i>
        </a>
    </div>

</div>

<style>
    .mobile-carousel-bootstrap {
        -ms-overflow-style: none;  /* IE and Edge */
        scrollbar-width: none;  /* Firefox */
    }
    .mobile-carousel-bootstrap::-webkit-scrollbar {
        display: none; /* Chrome, Safari and Opera */
    }
</style>

<script>
    // Lógica para el carrusel de pestañas deslizable con el mouse
    const slider = document.querySelector('#category-carousel');
    if (slider) {
        let isDown = false;
        let startX;
        let scrollLeft;

        slider.addEventListener('mousedown', (e) => {
            isDown = true;
            slider.style.cursor = 'grabbing';
            startX = e.pageX - slider.offsetLeft;
            scrollLeft = slider.scrollLeft;
        });
        slider.addEventListener('mouseleave', () => {
            isDown = false;
            slider.style.cursor = 'grab';
        });
        slider.addEventListener('mouseup', () => {
            isDown = false;
            slider.style.cursor = 'grab';
        });
        slider.addEventListener('mousemove', (e) => {
            if(!isDown) return;
            e.preventDefault();
            const x = e.pageX - slider.offsetLeft;
            const walk = (x - startX) * 2; // Multiplicador para un deslizamiento más rápido
            slider.scrollLeft = scrollLeft - walk;
        });
    }
</script>
