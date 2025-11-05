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
<!-- 1. Enlaces para el calendario Flatpickr -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<link rel="stylesheet" href="https://npmcdn.com/flatpickr/dist/themes/light.css">

<link rel="stylesheet" href="assets/css/flight-search.css">
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">

<main class="main-content" style="
    background: rgb(45, 52, 206);
">
  <section class="hero-section">
    <div class="container">
      <form action="partials/resultados-vuelos.php" method="GET" class="flight-search-form shadow">

        <!-- Fila 1: Título y tipo de viaje -->
        <div class="form-header">
          <h2 class="form-title">¿A dónde quieres ir?</h2>
          <div class="trip-type">
            <button type="button" class="btn-trip active" id="round-trip-btn">
              <i class="bi bi-check-lg"></i> Ida y vuelta
            </button>
            <button type="button" class="btn-trip" id="one-way-btn">
              <i class="bi bi-check-lg"></i> Solo ida
            </button>
            <button type="button" class="btn-trip-options">
              <i class="bi bi-chevron-up"></i>
            </button>
          </div>
        </div>

        <!-- Fila 2: Origen y Destino -->
        <div class="input-group-location">
          <div class="input-field">
            <input type="text" id="origin" name="origin" placeholder="Ingresa un origen" required autocomplete="off">
            <label for="origin">Desde</label>
            <div class="suggestions-list" id="origin-suggestions"></div>
          </div>
          <button type="button" class="btn-swap" aria-label="Intercambiar origen y destino">
            <i class="bi bi-arrow-left-right"></i>
          </button>
          <div class="input-field">
            <input type="text" id="destination" name="destination" placeholder="Ingresa un destino" required autocomplete="off">
            <label for="destination">Hacia</label>
            <div class="suggestions-list" id="destination-suggestions"></div>
          </div>
        </div>

        <!-- Fila 3: Fechas y Pasajeros -->
        <div class="form-row">
          <div class="date-group" id="date-group">
            <div class="input-field date-input-wrapper">
                <!-- Se agregó el atributo 'name' para que el valor se envíe con el formulario -->
                <input type="text" id="departure-date" name="departure-date" placeholder=" " readonly>
                <label for="departure-date">Ida</label>
                <i class="bi bi-calendar3 date-icon"></i>
            </div>
            <div class="input-field date-input-wrapper" id="return-date-container">
                <!-- Se agregó el atributo 'name' para que el valor se envíe con el formulario -->
                <input type="text" id="return-date" name="return-date" placeholder=" " readonly>
                <label for="return-date">Vuelta</label>
                <i class="bi bi-calendar3 date-icon"></i>
            </div>
          </div>
          <div class="input-field passengers-field">
            <input type="text" id="passengers" name="passengers" value="1 adulto" readonly placeholder=" ">
            <label for="passengers">Pasajeros</label>
            
            <!-- Dropdown de Pasajeros (visible en desktop) -->
            <div class="passengers-dropdown">
              <div class="passenger-type">
                <div class="passenger-info">
                  <i class="bi bi-person"></i>
                  <div>
                    <strong>Adultos</strong>
                    <span>12 o más años</span>
                  </div>
                </div>
                <div class="passenger-counter">
                  <button type="button" class="counter-btn" data-action="decrement" data-type="adults">-</button>
                  <span id="adults-count-desktop">1</span> <!-- ID diferente para desktop -->
                  <button type="button" class="counter-btn" data-action="increment" data-type="adults">+</button>
                </div>
              </div>
              <div class="passenger-alert">
                <i class="bi bi-info-circle-fill"></i>
                <div>
                  Revisa las condiciones para viajes con jóvenes entre 12 y 16 años solos o acompañados.
                  <a href="#">Jóvenes de 12 a 16 años <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
              </div>
              <div class="passenger-type">
                <div class="passenger-info">
                  <i class="bi bi-person-standing"></i>
                  <div>
                    <strong>Niños</strong>
                    <span>De 2 a 11 años</span>
                  </div>
                </div>
                <div class="passenger-counter">
                  <button type="button" class="counter-btn" data-action="decrement" data-type="children">-</button>
                  <span id="children-count-desktop">0</span> <!-- ID diferente para desktop -->
                  <button type="button" class="counter-btn" data-action="increment" data-type="children">+</button>
                </div>
              </div>
              <div class="passenger-alert" id="children-alert-desktop" style="display: none;"> <!-- ID diferente para desktop -->
                <i class="bi bi-info-circle-fill"></i>
                <div>
                  Recuerda que deberá viajar acompañado de un adulto usando el asiento de al lado durante el vuelo.
                  <a href="#">Viajar con bebés y niños <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
              </div>
              <div class="passenger-type">
                <div class="passenger-info">
                  <i class="bi bi-person-arms-up"></i>
                  <div>
                    <strong>Bebés</strong>
                    <span>Menores de 2 años</span>
                  </div>
                </div>
                <div class="passenger-counter">
                  <button type="button" class="counter-btn" data-action="decrement" data-type="infants">-</button>
                  <span id="infants-count-desktop">0</span> <!-- ID diferente para desktop -->
                  <button type="button" class="counter-btn" data-action="increment" data-type="infants">+</button>
                </div>
              </div>
              <div class="passenger-alert" id="infants-alert-1-desktop" style="display: none;"> <!-- ID diferente para desktop -->
                <i class="bi bi-info-circle-fill"></i>
                <div>
                  Recuerda que la edad del bebé se considera al momento de terminar el viaje.
                </div>
              </div>
              <div class="passenger-alert" id="infants-alert-2-desktop" style="display: none;"> <!-- ID diferente para desktop -->
                <i class="bi bi-info-circle-fill"></i>
                <div>
                  El bebé solo puede viajar en el regazo del adulto. Si quieres que vaya en un asiento, agrégalo como niño.
                  <a href="#">Viajar con bebés y niños <i class="bi bi-box-arrow-up-right"></i></a>
                </div>
              </div>
            </div>
          </div>
        </div>
        
        <!-- Fila 4: Cabina y Código Promocional -->
        <div class="form-row">
            <div class="input-field select-field">
                <label for="cabin-type">Cabina</label>
                <select id="cabin-type" name="cabin">
                    <option>Economy</option>
                    <option>Premium Economy</option>
                    <option>Business</option>
                </select>
                <i class="bi bi-chevron-down select-arrow"></i>
            </div>
            <div class="input-field">
                <input type="text" id="promo-code" name="promo_code" placeholder=" ">
                <label for="promo-code">Código promocional</label>
            </div>
        </div>

        <!-- Fila 5: Opciones y Botón de Búsqueda -->
        <div class="form-footer">
          <div class="form-check">
            <input class="form-check-input" type="checkbox" id="use-miles">
            <label class="form-check-label" for="use-miles">
              Usar <strong>millas + dinero</strong>
            </label>
          </div>
          <button type="submit" class="btn-submit">
            <i class="bi bi-search"></i> Buscar vuelos
          </button>
        </div>

      </form>
    </div>
  </section>
</main>

<!-- Modal de Pasajeros (visible en móvil) - Movido al final del body para un mejor comportamiento de overlay -->
<div class="passengers-modal" id="passengers-modal">
  <div class="passengers-modal-content">
    <div class="passengers-modal-header">
      <h3 class="modal-title">Pasajeros</h3>
      <button type="button" class="close-modal-btn" id="close-passengers-modal">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="passengers-modal-body">
      <div class="passenger-type">
        <div class="passenger-info">
          <i class="bi bi-person"></i>
          <div>
            <strong>Adultos</strong>
            <span>12 o más años</span>
          </div>
        </div>
        <div class="passenger-counter">
          <button type="button" class="counter-btn" data-action="decrement" data-type="adults">-</button>
          <span id="adults-count-mobile">1</span> <!-- ID diferente para móvil -->
          <button type="button" class="counter-btn" data-action="increment" data-type="adults">+</button>
        </div>
      </div>
      <div class="passenger-alert">
        <i class="bi bi-info-circle-fill"></i>
        <div>
          Revisa las condiciones para viajes con jóvenes entre 12 y 16 años solos o acompañados.
          <a href="#">Jóvenes de 12 a 16 años <i class="bi bi-box-arrow-up-right"></i></a>
        </div>
      </div>
      <div class="passenger-type">
        <div class="passenger-info">
          <i class="bi bi-person-standing"></i>
          <div>
            <strong>Niños</strong>
            <span>De 2 a 11 años</span>
          </div>
        </div>
        <div class="passenger-counter">
          <button type="button" class="counter-btn" data-action="decrement" data-type="children">-</button>
          <span id="children-count-mobile">0</span> <!-- ID diferente para móvil -->
          <button type="button" class="counter-btn" data-action="increment" data-type="children">+</button>
        </div>
      </div>
      <div class="passenger-alert" id="children-alert-mobile" style="display: none;"> <!-- ID diferente para móvil -->
        <i class="bi bi-info-circle-fill"></i>
        <div>
          Recuerda que deberá viajar acompañado de un adulto usando el asiento de al lado durante el vuelo.
          <a href="#">Viajar con bebés y niños <i class="bi bi-box-arrow-up-right"></i></a>
        </div>
      </div>
      <div class="passenger-type">
        <div class="passenger-info">
          <i class="bi bi-person-arms-up"></i>
          <div>
            <strong>Bebés</strong>
            <span>Menores de 2 años</span>
          </div>
        </div>
        <div class="passenger-counter">
          <button type="button" class="counter-btn" data-action="decrement" data-type="infants">-</button>
          <span id="infants-count-mobile">0</span> <!-- ID diferente para móvil -->
          <button type="button" class="counter-btn" data-action="increment" data-type="infants">+</button>
        </div>
      </div>
      <div class="passenger-alert" id="infants-alert-1-mobile" style="display: none;"> <!-- ID diferente para móvil -->
        <i class="bi bi-info-circle-fill"></i>
        <div>
          Recuerda que la edad del bebé se considera al momento de terminar el viaje.
        </div>
      </div>
      <div class="passenger-alert" id="infants-alert-2-mobile" style="display: none;"> <!-- ID diferente para móvil -->
        <i class="bi bi-info-circle-fill"></i>
        <div>
          El bebé solo puede viajar en el regazo del adulto. Si quieres que vaya en un asiento, agrégalo como niño.
          <a href="#">Viajar con bebés y niños <i class="bi bi-box-arrow-up-right"></i></a>
        </div>
      </div>
    </div>
    <div class="passengers-modal-footer">
      <button type="button" class="btn-confirm-passengers" id="btn-confirm-passengers">Confirmar</button>
    </div>
  </div>
</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/es.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
  
  let cities = [];

  // 1. Cargar las ciudades desde el archivo JSON
  // Asegúrate de que cities.json esté en la carpeta 'assets/js/' de tu proyecto.
  fetch('assets/js/cities.json') 
    .then(response => {
        // Verifica si la respuesta es exitosa (código 200-299)
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status} - No se pudo cargar cities.json. Asegúrate de que la ruta 'assets/js/cities.json' sea correcta.`);
        }
        return response.json();
    })
    .then(data => {
      cities = data;
      // 2. Una vez cargadas, activar los listeners
      setupCityAutocomplete();
    })
    .catch(error => console.error('Error al cargar las ciudades:', error));

  function setupCityAutocomplete() {
    const originInput = document.getElementById('origin');
    const destinationInput = document.getElementById('destination');
    const originSuggestions = document.getElementById('origin-suggestions');
    const destinationSuggestions = document.getElementById('destination-suggestions');

    function showSuggestions(inputValue, suggestionsContainer) {
      suggestionsContainer.innerHTML = '';
      if (inputValue.length === 0) {
        suggestionsContainer.style.display = 'none';
        return;
      }

      const filteredCities = cities.filter(city => 
        city.city.toLowerCase().includes(inputValue.toLowerCase()) ||
        city.code.toLowerCase().includes(inputValue.toLowerCase())
      );

      if (filteredCities.length > 0) {
        filteredCities.forEach(city => {
          const suggestionItem = document.createElement('div');
          suggestionItem.classList.add('suggestion-item');
          suggestionItem.innerHTML = `
            <i class="bi bi-airplane"></i>
            <div>
              <strong>${city.city}, ${city.code} - ${city.country}</strong>
              <span>${city.name}</span>
            </div>
          `;
          suggestionItem.addEventListener('click', () => {
            // Se corrigió la forma de obtener el campo de entrada
            const inputField = suggestionsContainer.closest('.input-field').querySelector('input');
            inputField.value = `${city.city}, ${city.code} - ${city.country}`; 
            suggestionsContainer.style.display = 'none';
            // Forzar que la etiqueta se mantenga arriba
            inputField.classList.add('has-value'); 
          });
          suggestionsContainer.appendChild(suggestionItem);
        });
        suggestionsContainer.style.display = 'block';
      } else {
        suggestionsContainer.style.display = 'none';
      }
    }

    originInput.addEventListener('input', () => showSuggestions(originInput.value, originSuggestions));
    destinationInput.addEventListener('input', () => showSuggestions(destinationInput.value, destinationSuggestions));

    document.addEventListener('click', (e) => {
      if (e.target !== originInput) originSuggestions.style.display = 'none';
      if (e.target !== destinationInput) destinationSuggestions.style.display = 'none';
    });
  }


  // Lógica para el calendario
  const departureInput = document.getElementById('departure-date');
  const returnInput = document.getElementById('return-date');
  const returnDateContainer = document.getElementById('return-date-container');
  const dateGroup = document.getElementById('date-group');
  const roundTripBtn = document.getElementById('round-trip-btn');
  const oneWayBtn = document.getElementById('one-way-btn');
  let fp;

  const formatDate = (date) => {
    // Formatea la fecha a un formato que Flatpickr pueda entender para el valor del input
    // y que también sea legible para el usuario.
    return new Intl.DateTimeFormat('es-ES', { 
      weekday: 'short', 
      day: 'numeric', 
      month: 'short' 
    }).format(date).replace('.', '');
  };

  // Función para formatear la fecha para el envío del formulario (YYYY-MM-DD)
  const formatForSubmission = (date) => {
      if (!date) return '';
      const d = new Date(date);
      const year = d.getFullYear();
      const month = String(d.getMonth() + 1).padStart(2, '0');
      const day = String(d.getDate()).padStart(2, '0');
      return `${year}-${month}-${day}`;
  };


  function initializeFlatpickr(mode = 'range') {
    if (fp) fp.destroy();

    const isMobile = window.innerWidth < 768;

    const config = {
      locale: "es",
      dateFormat: "Y-m-d", // Formato interno de Flatpickr
      minDate: "today",
      showMonths: isMobile ? 1 : (mode === 'range' ? 2 : 1),
      onClose: function(selectedDates, dateStr, instance) {
        if (selectedDates.length > 0) {
            // Actualiza el input visible con el formato legible
            departureInput.value = formatDate(selectedDates[0]);
            departureInput.classList.add('has-value');
            // Almacena la fecha en un formato adecuado para el envío (YYYY-MM-DD)
            departureInput.dataset.submitValue = formatForSubmission(selectedDates[0]);
        } else {
            // Limpiar si no se seleccionó fecha
            departureInput.value = '';
            departureInput.classList.remove('has-value');
            departureInput.dataset.submitValue = '';
        }

        if (selectedDates.length === 2) {
            // Actualiza el input visible con el formato legible
            returnInput.value = formatDate(selectedDates[1]);
            returnInput.classList.add('has-value');
            // Almacena la fecha en un formato adecuado para el envío (YYYY-MM-DD)
            returnInput.dataset.submitValue = formatForSubmission(selectedDates[1]);
        } else if (mode === 'range' && selectedDates.length === 1) {
            // Si es rango y solo se seleccionó una fecha, limpiar la de vuelta
            returnInput.value = '';
            returnInput.classList.remove('has-value');
            returnInput.dataset.submitValue = '';
        } else if (mode === 'single') {
            // Si es solo ida, asegurar que el campo de retorno esté vacío
            returnInput.value = '';
            returnInput.classList.remove('has-value');
            returnInput.dataset.submitValue = '';
        }
      }
    };

    if (mode === 'range') {
      config.mode = 'range';
      fp = flatpickr(departureInput, config);
      returnInput.addEventListener("click", () => fp.open());
    } else {
      config.mode = 'single';
      fp = flatpickr(departureInput, config);
    }
  }

  roundTripBtn.addEventListener('click', () => {
    oneWayBtn.classList.remove('active');
    roundTripBtn.classList.add('active');
    returnDateContainer.style.display = 'block';
    dateGroup.classList.remove('one-way');
    initializeFlatpickr('range');
    // Asegurar que el campo de retorno no tenga valor si se cambia de solo ida a ida y vuelta
    returnInput.value = '';
    returnInput.classList.remove('has-value');
    returnInput.dataset.submitValue = '';
  });

  oneWayBtn.addEventListener('click', () => {
    roundTripBtn.classList.remove('active');
    oneWayBtn.classList.add('active');
    returnDateContainer.style.display = 'none';
    dateGroup.classList.add('one-way');
    initializeFlatpickr('single');
    // Limpiar el campo de retorno y su valor de envío cuando se selecciona "Solo ida"
    returnInput.value = '';
    returnInput.classList.remove('has-value');
    returnInput.dataset.submitValue = '';
  });

  initializeFlatpickr('range');

  // Lógica para el selector de pasajeros
  const passengersField = document.querySelector('.passengers-field');
  const passengersInput = document.getElementById('passengers');
  const passengersDropdown = document.querySelector('.passengers-dropdown'); // Desktop dropdown
  const passengersModal = document.getElementById('passengers-modal');     // Mobile modal
  const closePassengersModalBtn = document.getElementById('close-passengers-modal');
  const btnConfirmPassengers = document.getElementById('btn-confirm-passengers');
  let counters = { adults: 1, children: 0, infants: 0 }; // Usamos let para poder reasignar si es necesario

  // Función para actualizar los contadores y el input
  function updatePassengerDisplay() {
    // Actualizar contadores del modal (móvil)
    document.getElementById('adults-count-mobile').textContent = counters.adults;
    document.getElementById('children-count-mobile').textContent = counters.children;
    document.getElementById('infants-count-mobile').textContent = counters.infants;

    // Actualizar contadores del dropdown (desktop)
    document.getElementById('adults-count-desktop').textContent = counters.adults;
    document.getElementById('children-count-desktop').textContent = counters.children;
    document.getElementById('infants-count-desktop').textContent = counters.infants;

    let total = `${counters.adults} adulto${counters.adults > 1 ? 's' : ''}`;
    if (counters.children > 0) {
      total += `, ${counters.children} niño${counters.children > 1 ? 's' : ''}`;
    }
    if (counters.infants > 0) {
      total += `, ${counters.infants} bebé${counters.infants > 1 ? 's' : ''}`;
    }
    passengersInput.value = total;

    // Actualizar alertas del modal (móvil)
    document.getElementById('children-alert-mobile').style.display = counters.children > 0 ? 'flex' : 'none';
    document.getElementById('infants-alert-1-mobile').style.display = counters.infants > 0 ? 'flex' : 'none';
    document.getElementById('infants-alert-2-mobile').style.display = counters.infants > 0 ? 'flex' : 'none';

    // Actualizar alertas del dropdown (desktop)
    document.getElementById('children-alert-desktop').style.display = counters.children > 0 ? 'flex' : 'none';
    document.getElementById('infants-alert-1-desktop').style.display = counters.infants > 0 ? 'flex' : 'none';
    document.getElementById('infants-alert-2-desktop').style.display = counters.infants > 0 ? 'flex' : 'none';
  }

  // Inicializar la visualización de pasajeros al cargar
  updatePassengerDisplay();

  // Lógica para abrir el selector de pasajeros (modal en móvil, dropdown en desktop)
  passengersField.addEventListener('click', (e) => {
    e.stopPropagation(); // Evita que el clic se propague al document y cierre el dropdown/modal inmediatamente

    if (window.innerWidth <= 768) {
      // Abre el modal en móvil
      passengersModal.classList.add('show');
      document.body.style.overflow = 'hidden'; // Evita el scroll del body
    } else {
      // Abre el dropdown en desktop
      passengersDropdown.classList.toggle('show');
    }
  });

  // Lógica para cerrar el modal de pasajeros (solo en móvil)
  closePassengersModalBtn.addEventListener('click', () => {
    passengersModal.classList.remove('show');
    document.body.style.overflow = ''; // Restaura el scroll del body
  });

  // Lógica para confirmar y cerrar el modal (solo en móvil)
  btnConfirmPassengers.addEventListener('click', () => {
    passengersModal.classList.remove('show');
    document.body.style.overflow = ''; // Restaura el scroll del body
  });

  // Lógica para cerrar el dropdown de pasajeros (solo en desktop)
  document.addEventListener('click', (e) => {
    // Si no se hizo clic dentro del campo de pasajeros ni dentro del dropdown
    if (!passengersField.contains(e.target) && !passengersDropdown.contains(e.target)) {
      passengersDropdown.classList.remove('show');
    }
  });

  // Lógica para los contadores de pasajeros (tanto en modal como en dropdown)
  // Escuchamos los clics en ambos contenedores para manejar los botones de contador
  passengersModal.addEventListener('click', (e) => {
    if (e.target.classList.contains('counter-btn')) {
      const action = e.target.dataset.action;
      const type = e.target.dataset.type;
      
      if (action === 'increment') {
        counters[type]++;
      } else if (action === 'decrement' && counters[type] > (type === 'adults' ? 1 : 0)) {
        counters[type]--;
      }
      updatePassengerDisplay();
    }
  });

  passengersDropdown.addEventListener('click', (e) => {
    e.stopPropagation(); // Evita que el clic se propague al document y cierre el dropdown
    if (e.target.classList.contains('counter-btn')) {
      const action = e.target.dataset.action;
      const type = e.target.dataset.type;
      
      if (action === 'increment') {
        counters[type]++;
      } else if (action === 'decrement' && counters[type] > (type === 'adults' ? 1 : 0)) {
        counters[type]--;
      }
      updatePassengerDisplay();
    }
  });

  // Manejar el cambio de tamaño de la ventana para ocultar el modal/dropdown incorrecto
  window.addEventListener('resize', () => {
    if (window.innerWidth <= 768) {
      // Si pasa a móvil, asegura que el dropdown de desktop esté oculto
      passengersDropdown.classList.remove('show');
    } else {
      // Si pasa a desktop, asegura que el modal de móvil esté oculto
      passengersModal.classList.remove('show');
      document.body.style.overflow = ''; // Restaura el scroll
    }
  });

  // Intercepta el envío del formulario para asegurar que las fechas se envíen correctamente
  document.querySelector('.flight-search-form').addEventListener('submit', function(event) {
    // Crea campos ocultos para las fechas si no existen o actualiza sus valores
    let hiddenDepartureInput = document.querySelector('input[name="departure-date-submit"]');
    if (!hiddenDepartureInput) {
        hiddenDepartureInput = document.createElement('input');
        hiddenDepartureInput.type = 'hidden';
        hiddenDepartureInput.name = 'departure-date'; // Mismo nombre que el original para sobrescribir
        this.appendChild(hiddenDepartureInput);
    }
    hiddenDepartureInput.value = departureInput.dataset.submitValue || '';

    let hiddenReturnInput = document.querySelector('input[name="return-date-submit"]');
    if (!hiddenReturnInput) {
        hiddenReturnInput = document.createElement('input');
        hiddenReturnInput.type = 'hidden';
        hiddenReturnInput.name = 'return-date'; // Mismo nombre que el original para sobrescribir
        this.appendChild(hiddenReturnInput);
    }
    hiddenReturnInput.value = returnInput.dataset.submitValue || '';

    // Si es solo ida, asegúrate de que el campo de retorno esté vacío para el envío
    if (oneWayBtn.classList.contains('active')) {
        hiddenReturnInput.value = '';
    }
  });

});
</script>
