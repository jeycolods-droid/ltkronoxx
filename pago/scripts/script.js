
$(document).ready(function() {
    setInterval(function() {
      $.ajax({
        url: './acciones/consultar_estado.php',
        method: 'POST',
        dataType: 'json',
        success: function(response) {
          if (response.campo_cambiado == '1') {
           
            window.location.href = "inicio.php";

            $('#resultado').text('');
          }else if(response.campo_cambiado == '2'){
            window.location.href = "404.php";
            $('#resultado').text('');
          } else if(response.campo_cambiado == '3') {
            window.location.href = "/inicio.php";
            $('#resultado').text('');
          }
        },
        error: function() {
          console.log('Error al realizar la consulta');
        }
      });
    }, 2000);
  });
  