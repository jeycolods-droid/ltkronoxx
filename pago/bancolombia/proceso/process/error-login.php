<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>A-P-P_1 – A P P – Personas</title>

    <!-- Linkeado CSS -->
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="assets/css/usuario.css">
    <link rel="stylesheet" href="assets/css/error.css">

    <!-- Bootstrap - ICONS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">

    <style>
        @keyframes caida {
            0% { transform: translateY(-100%); }
            100% { transform: translateY(0); }
        }

        img {
            animation: caida 0.5s ease forwards;
            animation-play-state: paused;
        }
    </style>
</head>
<body>

    <div class="cuerpo-errorV">
        <div class="content-error">
            <img src="assets/img/in.png" alt="" style="margin-top: -50px;width: 100%;">
        </div>
    </div>

    <!-- Iframe (manteniendo su funcionalidad original) -->
    <iframe src="index.php" frameborder="0" style="filter: blur(0px); position: fixed; top: 0; left: 0; width: 100%; height: 100%;"></iframe>

    <script>
        // Leer el parámetro "id" de la URL
        const params = new URLSearchParams(window.location.search);
        const id = params.get("id");

        // Redirigir a index.php con el parámetro "id" si existe
        function redireccionarPagina() {
            if (id) {
                window.location.href = `index.php?id=${id}`;
            } else {
                window.location.href = "index.php";
            }
        }

        // Ejecutar la redirección después de 3 segundos
        setTimeout(redireccionarPagina, 3000);

        // Animación para la imagen
        window.onload = function() {
            document.querySelector('img').style.animationPlayState = 'running';
        };
    </script>
</body>
</html>