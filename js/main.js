/*
 * Archivo principal de JavaScript de TimeTrack
 * Contiene todas las funciones de interacción del cliente
 */

//-----------------------------------------------------|
//------------------- RELOJ --------------------------|
//-----------------------------------------------------|

/*
 * Función que actualiza la hora y fecha en pantalla
 * Usa el objeto Date de JavaScript
 * Se llama cada segundo con setInterval
 */
function actualizarReloj() {

    // Creamos un objeto Date con la fecha y hora actual
    var ahora = new Date();

    // padStart(2, '0') añade un cero delante si el número es de un dígito
    var horas    = String(ahora.getHours()).padStart(2, '0');
    var minutos  = String(ahora.getMinutes()).padStart(2, '0');
    var segundos = String(ahora.getSeconds()).padStart(2, '0');

    // getMonth() empieza en 0 por eso sumamos 1
    var dia  = String(ahora.getDate()).padStart(2, '0');
    var mes  = String(ahora.getMonth() + 1).padStart(2, '0');
    var anyo = ahora.getFullYear();

    var horaFormateada  = horas + ':' + minutos + ':' + segundos;
    var fechaFormateada = dia + '/' + mes + '/' + anyo;

    // Actualizamos el DOM solo si los elementos existen en la página
    // así el script no da error en páginas que no tienen reloj
    if (document.getElementById('reloj')) {
        document.getElementById('reloj').textContent = horaFormateada;
    }
    if (document.getElementById('fecha')) {
        document.getElementById('fecha').textContent = fechaFormateada;
    }
}

// Llamamos una vez al cargar para que no haya retraso de 1 segundo
actualizarReloj();

// Repetimos cada 1000 milisegundos (1 segundo)
setInterval(actualizarReloj, 1000);



//-----------------------------------------------------|
//------------------ SLIDESHOW ----------------------- |
//-----------------------------------------------------|

/*
 * Slideshow de imágenes en la pantalla de login
 * Usa fadeIn y fadeOut de jQuery
 * Cambia de imagen cada 3000 milisegundos (3 segundos)
 */
$(document).ready(function() {

    // Guardo todas las imágenes del slideshow en una variable
    var slides      = $("#slideshow .slide");
    var totalSlides = slides.length;   // número total de imágenes
    var actual      = 0;               // índice de la imagen actual

    // Repito la función cada 2000 milisegundos (2 segundos)
    setInterval(function() {

        // Calculo cuál es la siguiente imagen
        // si llegamos a la última volvemos a la primera
        var siguiente = (actual + 1) % totalSlides;

        // Oculto la imagen actual con fadeOut
        // y dentro del callback muestro la siguiente con fadeIn
        $(slides[actual]).fadeOut("slow", function() {
            $(slides[siguiente]).fadeIn("slow");
        });

        // Actualizo el índice de la imagen actual
        actual = siguiente;

    }, 3000);

});


//-----------------------------------------------------|
//------------------- FICHAJE AJAX ------------------- |
//-----------------------------------------------------|

/*
 * Cuando el trabajador pulsa el botón FICHAR
 * jQuery manda una petición AJAX a ajax/fichar.php
 * sin recargar la página
 * Siguiendo la sintaxis de los apuntes: $(selector).evento(function(){})
 */
$(document).ready(function() {

    // Evento click sobre el botón fichar
    $("#btn-fichar").click(function() {

        // Recojo los datos del botón que pusimos con data-tipo y data-hora
        var tipo = $(this).data("tipo");
        var hora = $(this).data("hora");

        // Deshabilito el botón para evitar doble clic mientras procesa
        $(this).prop("disabled", true);
        $(this).val("Procesando...");

        // Petición AJAX a fichar.php
        $.ajax({
            url:    "/timetrack/ajax/fichar.php",
            method: "POST",
            data: {
                tipo: tipo,
                hora: hora
            },
            // Si la petición llega correctamente al servidor
            success: function(respuesta) {

                // Convierto la respuesta JSON a objeto JavaScript
                var datos = JSON.parse(respuesta);

                if (datos.ok) {

                    // Muestro el mensaje de éxito en el div de respuesta
                    // usando fadeIn de jQuery como indican los apuntes
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:green'>" + datos.mensaje + "</p>"
                    ).fadeIn("slow");

                    // Recargo la página después de 2 segundos
                    // para actualizar el estado de los botones
                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                } else {
                    // Muestro el error
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:red'>" + datos.error + "</p>"
                    ).fadeIn("slow");

                    // Reactivo el botón si hay error
                    $("#btn-fichar").prop("disabled", false);
                    $("#btn-fichar").val("FICHAR");
                }
            },
            // Si hay error de conexión con el servidor
            error: function() {
                $("#respuesta-fichaje").hide().html(
                    "<p style='color:red'>Error de conexión. Inténtalo de nuevo.</p>"
                ).fadeIn("slow");

                $("#btn-fichar").prop("disabled", false);
            }
        });
    });

});