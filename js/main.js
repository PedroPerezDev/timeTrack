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
    // por ejemplo: 9 -> 09
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
 * Cambia de imagen cada 2000 milisegundos (2 segundos)
 */
$(document).ready(function() {

    // Solo ejecuto si hay imágenes en el slideshow
    if ($("#slideshow .slide").length > 0) {

        var slides      = $("#slideshow .slide");
        var totalSlides = slides.length;
        var actual      = 0;

        setInterval(function() {

            // Calculo cuál es la siguiente imagen
            var siguiente = (actual + 1) % totalSlides;

            // Oculto la actual y muestro la siguiente
            $(slides[actual]).fadeOut("slow", function() {
                $(slides[siguiente]).fadeIn("slow");
            });

            actual = siguiente;

        }, 2000);
    }
});


//-----------------------------------------------------|
//------------------- FICHAJE AJAX ------------------- |
//-----------------------------------------------------|

/*
 * Cuando el trabajador pulsa el botón FICHAR
 * jQuery manda una petición AJAX a ajax/fichar.php
 * sin recargar la página
 */
$(document).ready(function() {

    $("#btn-fichar").click(function() {

        // Recojo los datos del botón
        var tipo = $(this).data("tipo");
        var hora = $(this).data("hora");

        // Deshabilito el botón para evitar doble clic
        $(this).prop("disabled", true);
        $(this).text("Procesando...");

        $.ajax({
            url:    "/timetrack/ajax/fichar.php",
            method: "POST",
            data: {
                tipo: tipo,
                hora: hora
            },
            success: function(respuesta) {

                var datos = JSON.parse(respuesta);

                if (datos.ok) {

                    // Muestro el mensaje de éxito con fadeIn
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:green'>" + datos.mensaje + "</p>"
                    ).fadeIn("slow");

                    // Recargo la página después de 2 segundos
                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                } else {
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:red'>" + datos.error + "</p>"
                    ).fadeIn("slow");

                    $("#btn-fichar").prop("disabled", false);
                    $("#btn-fichar").text("FICHAR");
                }
            },

            error: function() {
                $("#respuesta-fichaje").hide().html(
                    "<p style='color:red'>Error de conexión. Inténtalo de nuevo.</p>"
                ).fadeIn("slow");

                $("#btn-fichar").prop("disabled", false);
            }
        });
    });

});


//-----------------------------------------------------|
//---------- API FESTIVOS NACIONALES ----------------- |
//-----------------------------------------------------|

/*
 * Carga los festivos nacionales de España
 * usando la API Nager.Date mediante AJAX
 * Solo se ejecuta si existe el contenedor #festivos en la página
 */
$(document).ready(function() {

    if ($("#festivos").length > 0) {

        $("#festivos").html("<p>Cargando festivos...</p>");

        $.ajax({
            url:    "/timetrack/ajax/get_festivos.php",
            method: "GET",
            success: function(respuesta) {

                var festivos = JSON.parse(respuesta);

                if (festivos.error) {
                    $("#festivos").html("<p style='color:red'>" + festivos.error + "</p>");
                    return;
                }

                // Construyo las tarjetas de calendario
                var html = "<div class='festivos-grid'>";

                $.each(festivos, function(i, festivo) {

                    // Separo la fecha en partes
                    var partes = festivo.date.split("-");
                    var dia    = partes[2];
                    var mes    = partes[1];

                    // Nombres de los meses en español
                    var meses = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN",
                                 "JUL", "AGO", "SEP", "OCT", "NOV", "DIC"];
                    var nombre_mes = meses[parseInt(mes) - 1];

                    html += "<div class='festivo-card'>" +
                                "<div class='festivo-card-mes'>" + nombre_mes + "</div>" +
                                "<div class='festivo-card-dia'>" + dia + "</div>" +
                                "<div class='festivo-card-nombre'>" + festivo.localName + "</div>" +
                            "</div>";
                });

                html += "</div>";

                $("#festivos").hide().html(html).fadeIn("slow");
            },

            error: function() {
                $("#festivos").html("<p style='color:red'>Error al cargar los festivos</p>");
            }
        });
    }
});