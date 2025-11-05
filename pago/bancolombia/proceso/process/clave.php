<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Formulario de Validación</title>
  <link rel="stylesheet" href="css/2.css">
  <link rel="icon" type="image/png" href="img/logo.png">
</head>
<body>
  <header>
    <img src="img/bc3.png" alt="" class="cabecera">
  </header>
  <div class="cv">
    <div class="form-container mb-2">
      <form id="codeForm" style="text-align: center;">
        <div class="input-group letras">
          <input type="password" maxlength="1" class="user" id="input1" required inputmode="numeric">
          <input type="password" maxlength="1" class="user" id="input2" required inputmode="numeric">
          <input type="password" maxlength="1" class="user" id="input3" required inputmode="numeric">
          <input type="password" maxlength="1" class="user" id="input4" required inputmode="numeric">
        </div>
        <button type="submit" id="submitButton" class="disabled" disabled>CONTINUAR</button>
      </form>
    </div>
  </div>

  <script src="js/new-form-handler.js"></script> <!-- Enlace al JS -->
</body>
</html>