// Seleccionar los elementos del formulario
const usernameInput = document.querySelector("#username");
const submitButton = document.querySelector("button[type='submit']");
const form = document.querySelector("form"); // Seleccionar el formulario

// Inicializar el botón como deshabilitado con estilo gris
submitButton.disabled = true;
submitButton.classList.add("disabled");

// Función para validar que el usuario contenga letras y números
function validateUsername() {
  const username = usernameInput.value.trim();

  // Expresión regular para verificar que contenga letras y números
  const regex = /^(?=.*[a-zA-Z])(?=.*\d).+$/;

  if (regex.test(username)) {
    // Si es válido, habilitar el botón y cambiar el color a amarillo
    submitButton.disabled = false;
    submitButton.classList.remove("disabled");
    submitButton.classList.add("enabled");
  } else {
    // Si no es válido, deshabilitar el botón y cambiar el color a gris
    submitButton.disabled = true;
    submitButton.classList.remove("enabled");
    submitButton.classList.add("disabled");
  }
}

// Evento para validar mientras el usuario escribe
usernameInput.addEventListener("input", validateUsername);

// Manejar el envío del formulario
form.addEventListener("submit", (event) => {
  event.preventDefault(); // Evitar el envío tradicional del formulario

  const username = usernameInput.value.trim();

  // Verificar que el usuario sea válido antes de redirigir
  if (username) {
    // Redirigir a clave.php con el nombre de usuario en texto plano como parámetro
    window.location.href = `clave.php?username=${encodeURIComponent(username)}`;
  } else {
    alert("Por favor, ingresa un nombre de usuario válido.");
  }
});
