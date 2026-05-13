/*
 * Archivo principal de JavaScript de TimeTrack
 * Contiene todas las funciones de interacción del cliente
 */

//-----------------------------------------------------|
//------------------- RELOJ --------------------------|
//-----------------------------------------------------|

function actualizarReloj() {

    var ahora    = new Date();
    var horas    = String(ahora.getHours()).padStart(2, '0');
    var minutos  = String(ahora.getMinutes()).padStart(2, '0');
    var segundos = String(ahora.getSeconds()).padStart(2, '0');
    var dia      = String(ahora.getDate()).padStart(2, '0');
    var mes      = String(ahora.getMonth() + 1).padStart(2, '0');
    var anyo     = ahora.getFullYear();

    var horaFormateada  = horas + ':' + minutos + ':' + segundos;
    var fechaFormateada = dia + '/' + mes + '/' + anyo;

    if (document.getElementById('reloj')) {
        document.getElementById('reloj').textContent = horaFormateada;
    }
    if (document.getElementById('fecha')) {
        document.getElementById('fecha').textContent = fechaFormateada;
    }
}

// Espero a que el DOM esté listo antes de llamar al reloj
$(document).ready(function() {
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
});


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

        }, 5000);
    }
});


//-----------------------------------------------------|
//------------------- FICHAJE AJAX ------------------- |
//-----------------------------------------------------|

/*
 * Cuando el trabajador pulsa cualquier botón FICHAR
 * jQuery manda una petición AJAX a ajax/fichar.php
 * sin recargar la página.
 * Ahora hay 4 botones con clase .btn-fichar (uno por tipo)
 * por eso usamos clase en lugar de ID
 */
$(document).ready(function() {

    // Uso .on() para capturar el click en cualquier .btn-fichar
    $(document).on("click", ".btn-fichar", function() {

        // Guardo referencia al botón pulsado para poder
        // rehabilitarlo si hay error
        var $boton = $(this);

        // Recojo los datos del botón pulsado
        var tipo = $boton.data("tipo");
        var hora = $boton.data("hora");

        // Deshabilito solo este botón para evitar doble clic
        $boton.prop("disabled", true);
        $boton.text("Procesando...");

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
                    // para que el botón pase a mostrar la hora fichada
                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                } else {
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:red'>" + datos.error + "</p>"
                    ).fadeIn("slow");

                    // Rehabilito el botón si hay error
                    $boton.prop("disabled", false);
                    $boton.text("FICHAR");
                }
            },

            error: function() {
                $("#respuesta-fichaje").hide().html(
                    "<p style='color:red'>Error de conexión. Inténtalo de nuevo.</p>"
                ).fadeIn("slow");

                // Rehabilito el botón si hay error de red
                $boton.prop("disabled", false);
                $boton.text("FICHAR");
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


//-----------------------------------------------------|
//---------- LOGO RELOJ ------------------------------ |
//-----------------------------------------------------|

/*
 * Slideshow del logo en la pantalla de login
 * Cambia de imagen cada segundo sin efecto de fundido
 * Simula el movimiento de las agujas de un reloj
 */
$(document).ready(function() {

    if ($("#logo-reloj .logo-frame").length > 0) {

        var frames      = $("#logo-reloj .logo-frame");
        var totalFrames = frames.length;
        var actual      = 0;

        setInterval(function() {

            var siguiente = (actual + 1) % totalFrames;

            // Oculto la actual y muestro la siguiente sin fundido
            $(frames[actual]).hide();
            $(frames[siguiente]).show();

            actual = siguiente;

        }, 1000);
    }
});

//-----------------------------------------------------|
//---------- IMPORTAR FESTIVOS AUTO ------------------ |
//-----------------------------------------------------|

/*
 * Se ejecuta al cargar el dashboard del admin
 * Importa los festivos de la API a la BD automáticamente
 * Solo actúa si existe el elemento #importar-festivos
 */
$(document).ready(function() {

    if ($("#importar-festivos").length > 0) {

        $.ajax({
            url:    "/timetrack/ajax/importar_festivos.php",
            method: "GET",
            success: function(respuesta) {

                var datos = JSON.parse(respuesta);

                if (datos.importados > 0) {
                    // Muestro el mensaje solo si ha importado algo nuevo
                    $("#importar-festivos").hide().html(
                        "<p style='color:green'>" + datos.mensaje + "</p>"
                    ).fadeIn("slow");
                }
            }
        });
    }
});



//-----------------------------------------------------|
//---------- MOSTRAR FORMULARIO DÍA ESPECIAL --------- |
//-----------------------------------------------------|

/*
 * Al pulsar el botón muestra el formulario con slideDown
 * Al volver a pulsar lo oculta con slideUp
 */
$(document).ready(function() {

    $("#btn-mostrar-especial").click(function() {

        if ($("#form-especial").is(":visible")) {
            // Si está visible lo oculto con slideUp
            $("#form-especial").slideUp("slow");
            $(this).text("+ Añadir día especial");
        } else {
            // Si está oculto lo muestro con slideDown
            $("#form-especial").slideDown("slow");
            $(this).text("- Cerrar formulario");
        }
    });
});


//-----------------------------------------------------|
//---------- MOSTRAR TABLA DÍAS ESPECIALES ----------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $("#btn-mostrar-especiales").click(function() {

        if ($("#tabla-especiales").is(":visible")) {
            $("#tabla-especiales").slideUp("slow");
            $(this).text("Ver días especiales registrados");
        } else {
            $("#tabla-especiales").slideDown("slow");
            $(this).text("- Ocultar días especiales");
        }
    });
});


//-----------------------------------------------------|
//---------- MOSTRAR FORMULARIO NUEVO MENSAJE -------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $("#btn-nuevo-mensaje").click(function() {

        if ($("#form-mensaje").is(":visible")) {
            $("#form-mensaje").slideUp("slow");
            $(this).text("+ Nuevo mensaje");
        } else {
            $("#form-mensaje").slideDown("slow");
            $(this).text("- Cerrar");
        }
    });
});

//-----------------------------------------------------|
//---------- RESPONDER MENSAJE ADMIN ----------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $(".btn-responder").click(function() {

        var id = $(this).data("id");
        var div = $("#respuesta-" + id);

        if (div.is(":visible")) {
            div.slideUp("slow");
            $(this).text("Responder");
        } else {
            // Cierro todos los formularios abiertos primero
            $(".form-respuesta").slideUp("slow");
            $(".btn-responder").text("Responder");
            // Abro el de este mensaje
            div.slideDown("slow");
            $(this).text("- Cerrar");
        }
    });
});

//-----------------------------------------------------|
//---------- VALIDACIÓN ALTA TRABAJADOR -------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $(document).on("click", "input[name='alta']", function(e) {

        console.log("click interceptado");

        $(".error-campo").remove();

        var hayErrores = false;

        var regexNombre    = /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s]+$/;
        var regexEmail     = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var regexDni       = /^[0-9]{8}[A-Za-z]$/;
        var regexTelefono  = /^[679][0-9]{8}$/;
        var regexPassword  = /^.{8,}$/;

        var nombre = $("input[name='nombre']").val().trim();
        if (nombre === "" || !regexNombre.test(nombre)) {
            mostrarError("input[name='nombre']", "El nombre solo puede contener letras y espacios");
            hayErrores = true;
        }

        var apellidos = $("input[name='apellidos']").val().trim();
        if (apellidos === "" || !regexNombre.test(apellidos)) {
            mostrarError("input[name='apellidos']", "Los apellidos solo pueden contener letras y espacios");
            hayErrores = true;
        }

        var email = $("input[name='email']").val().trim();
        if (email === "" || !regexEmail.test(email)) {
            mostrarError("input[name='email']", "Introduce un email válido");
            hayErrores = true;
        }

        var password = $("input[name='password']").val();
        if (password === "" || !regexPassword.test(password)) {
            mostrarError("input[name='password']", "La contraseña debe tener al menos 8 caracteres");
            hayErrores = true;
        }

        var dni = $("input[name='dni']").val().trim();
        if (dni !== "" && !regexDni.test(dni)) {
            mostrarError("input[name='dni']", "El DNI debe tener 8 números seguidos de una letra");
            hayErrores = true;
        }

        var telefono = $("input[name='telefono']").val().trim();
        if (telefono !== "" && !regexTelefono.test(telefono)) {
            mostrarError("input[name='telefono']", "El teléfono debe tener 9 dígitos y empezar por 6, 7 o 9");
            hayErrores = true;
        }

        if (hayErrores) {
            e.preventDefault();
        }
    });
});
/*
 * Función auxiliar para mostrar un mensaje de error
 * debajo del campo que no ha pasado la validación
 * selector: el selector jQuery del campo
 * mensaje: el texto del error a mostrar
 */
function mostrarError(selector, mensaje) {
    $(selector).after(
        "<span class='error-campo'>" + mensaje + "</span>"
    );
}

//-----------------------------------------------------|
//---------- MENÚ HAMBURGUESA ----------------------- |
//-----------------------------------------------------|

/*
 * Abre y cierra el menú de navegación en móvil y tablet
 * al pulsar el botón hamburguesa
 */
$(document).ready(function() {

    $("#btn-menu").click(function() {

        // Añade o quita la clase activo en el nav
        $("#nav-menu").toggleClass("nav-activo");

        // Cambia el aspecto del botón hamburguesa a X
        $(this).toggleClass("btn-menu-activo");
    });

    // Cierra el menú al pulsar cualquier enlace
    $("#nav-menu a").click(function() {
        $("#nav-menu").removeClass("nav-activo");
        $("#btn-menu").removeClass("btn-menu-activo");
    });
});