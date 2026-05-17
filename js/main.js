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

$(document).ready(function() {
    actualizarReloj();
    setInterval(actualizarReloj, 1000);
});


//-----------------------------------------------------|
//------------------ SLIDESHOW ----------------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    if ($("#slideshow .slide").length > 0) {

        var slides      = $("#slideshow .slide");
        var totalSlides = slides.length;
        var actual      = 0;

        setInterval(function() {

            var siguiente = (actual + 1) % totalSlides;

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

$(document).ready(function() {

    $(document).on("click", ".btn-fichar", function() {

        var $boton = $(this);
        var tipo   = $boton.data("tipo");
        var hora   = $boton.data("hora");

        $boton.prop("disabled", true);
        $boton.text("Procesando...");

        $.ajax({
            url:    "/ajax/fichar.php",
            method: "POST",
            data: {
                tipo: tipo,
                hora: hora
            },
            success: function(respuesta) {

                var datos = JSON.parse(respuesta);

                if (datos.ok) {

                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:green'>" + datos.mensaje + "</p>"
                    ).fadeIn("slow");

                    setTimeout(function() {
                        location.reload();
                    }, 2000);

                } else {
                    $("#respuesta-fichaje").hide().html(
                        "<p style='color:red'>" + datos.error + "</p>"
                    ).fadeIn("slow");

                    $boton.prop("disabled", false);
                    $boton.text("FICHAR");
                }
            },

            error: function() {
                $("#respuesta-fichaje").hide().html(
                    "<p style='color:red'>Error de conexión. Inténtalo de nuevo.</p>"
                ).fadeIn("slow");

                $boton.prop("disabled", false);
                $boton.text("FICHAR");
            }
        });
    });
});


//-----------------------------------------------------|
//---------- API FESTIVOS NACIONALES ----------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    if ($("#festivos").length > 0) {

        $("#festivos").html("<p>Cargando festivos...</p>");

        $.ajax({
            url:    "/ajax/get_festivos.php",
            method: "GET",
            success: function(respuesta) {

                var festivos = JSON.parse(respuesta);

                if (festivos.error) {
                    $("#festivos").html("<p style='color:red'>" + festivos.error + "</p>");
                    return;
                }

                var html = "<div class='festivos-grid'>";

                $.each(festivos, function(i, festivo) {

                    var partes     = festivo.date.split("-");
                    var dia        = partes[2];
                    var mes        = partes[1];
                    var meses      = ["ENE", "FEB", "MAR", "ABR", "MAY", "JUN",
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

$(document).ready(function() {

    if ($("#logo-reloj .logo-frame").length > 0) {

        var frames      = $("#logo-reloj .logo-frame");
        var totalFrames = frames.length;
        var actual      = 0;

        setInterval(function() {

            var siguiente = (actual + 1) % totalFrames;

            $(frames[actual]).hide();
            $(frames[siguiente]).show();

            actual = siguiente;

        }, 1000);
    }
});


//-----------------------------------------------------|
//---------- IMPORTAR FESTIVOS AUTO ------------------ |
//-----------------------------------------------------|

$(document).ready(function() {

    if ($("#importar-festivos").length > 0) {

        $.ajax({
            url:    "/ajax/importar_festivos.php",
            method: "GET",
            success: function(respuesta) {

                var datos = JSON.parse(respuesta);

                if (datos.importados > 0) {
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

$(document).ready(function() {

    $("#btn-mostrar-especial").click(function() {

        if ($("#form-especial").is(":visible")) {
            $("#form-especial").slideUp("slow");
            $(this).text("+ Añadir día especial");
        } else {
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
//---------- VALIDACIÓN ALTA TRABAJADOR -------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $(document).on("click", "input[name='alta']", function(e) {

        $(".error-campo").remove();

        var hayErrores = false;

        var regexNombre   = /^[a-záéíóúüñA-ZÁÉÍÓÚÜÑ\s]+$/;
        var regexEmail    = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
        var regexDni      = /^[0-9]{8}[A-Za-z]$/;
        var regexTelefono = /^[679][0-9]{8}$/;
        var regexPassword = /^.{8,}$/;

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

function mostrarError(selector, mensaje) {
    $(selector).after(
        "<span class='error-campo'>" + mensaje + "</span>"
    );
}


//-----------------------------------------------------|
//---------- MENÚ HAMBURGUESA ----------------------- |
//-----------------------------------------------------|

$(document).ready(function() {

    $("#btn-menu").click(function() {
        $("#nav-menu").toggleClass("nav-activo");
        $(this).toggleClass("btn-menu-activo");
    });

    $("#nav-menu a").click(function() {
        $("#nav-menu").removeClass("nav-activo");
        $("#btn-menu").removeClass("btn-menu-activo");
    });
});


//-----------------------------------------------------|
//---------- MODO OSCURO ----------------------------- |
//-----------------------------------------------------|

/*
 * Activa y desactiva el modo oscuro añadiendo o quitando
 * la clase .modo-oscuro al body.
 * Guarda la preferencia en localStorage para mantenerla
 * entre visitas.
 * Uso innerHTML con código de entidad HTML para que el
 * emoji se renderice bien en todos los navegadores.
 * &#127769; = 🌙   &#9728; = ☀️
 */
function toggleModo() {

    $("body").toggleClass("modo-oscuro");

    if ($("body").hasClass("modo-oscuro")) {
        document.getElementById("btn-modo").innerHTML = "&#9728;";
        localStorage.setItem("timetrack_modo", "oscuro");
    } else {
        document.getElementById("btn-modo").innerHTML = "&#127769;";
        localStorage.setItem("timetrack_modo", "claro");
    }
}

// Al cargar recupero la preferencia guardada en localStorage
$(document).ready(function() {
    if (localStorage.getItem("timetrack_modo") === "oscuro") {
        $("body").addClass("modo-oscuro");
        document.getElementById("btn-modo").innerHTML = "&#9728;";
    }
});


//-----------------------------------------------------|
//---------- MOSTRAR TABLA FICHAJES HOY -------------- |
//-----------------------------------------------------|

/*
 * En el dashboard del admin muestra u oculta la tabla
 * de fichajes del día con slideDown/slideUp
 */
$(document).ready(function() {

    $("#btn-mostrar-fichajes").click(function() {

        if ($("#tabla-fichajes").is(":visible")) {
            $("#tabla-fichajes").slideUp("slow");
            $(this).text("Ver estado de fichajes de hoy");
        } else {
            $("#tabla-fichajes").slideDown("slow");
            $(this).text("Ocultar fichajes de hoy");
        }
    });
});


//-----------------------------------------------------|
//---------- MOSTRAR INCIDENCIAS DEL PERÍODO --------- |
//-----------------------------------------------------|

/*
 * En informes.php muestra u oculta la tabla de incidencias
 * al pulsar el botón, con efecto slideDown/slideUp
 */
$(document).ready(function() {

    $(document).on("click", "#btn-mostrar-incidencias", function() {

        if ($("#tabla-incidencias").is(":visible")) {
            $("#tabla-incidencias").slideUp("slow");
            $(this).text($(this).text().replace("Ocultar", "Ver"));
        } else {
            $("#tabla-incidencias").slideDown("slow");
            $(this).text($(this).text().replace("Ver", "Ocultar"));
        }
    });
});


//-----------------------------------------------------|
//---------- FORMULARIO MODIFICAR TRABAJADOR --------- |
//-----------------------------------------------------|

/*
 * Al pulsar Modificar en la tabla de trabajadores
 * el formulario aparece con slideDown
 * Si ya está visible lo oculta con slideUp
 */
$(document).ready(function() {

    $(document).on("click", "input[name='ver_modificar']", function(e) {
        // Dejo que el form haga POST normalmente
        // El PHP genera el div#form-modificar oculto
        // y tras recargar lo mostramos automáticamente
    });

    // Si existe el div#form-modificar en la página lo mostramos con slideDown
    if ($("#form-modificar").length > 0) {
        $("#form-modificar").slideDown("slow");
    }
});


//-----------------------------------------------------|
//---------- FORMULARIO NUEVO TRABAJADOR ------------- |
//-----------------------------------------------------|

/*
 * Al pulsar + Nuevo trabajador muestra el formulario
 * con slideDown y cambia el texto del botón
 */
$(document).ready(function() {

    $("#btn-nuevo-trabajador").click(function() {

        if ($("#form-nuevo").is(":visible")) {
            $("#form-nuevo").slideUp("slow");
            $(this).text("+ Nuevo trabajador");
        } else {
            $("#form-nuevo").slideDown("slow");
            $(this).text("- Cerrar formulario");
        }
    });
});


//-----------------------------------------------------|
//---------- MIS HORAS DEL MES ----------------------- |
//-----------------------------------------------------|

/*
 * Despliega u oculta la tabla de horas del mes actual
 * al pulsar el botón, con efecto slideDown/slideUp
 */
$(document).ready(function() {

    $("#btn-mis-horas").click(function() {

        if ($("#tabla-mis-horas").is(":visible")) {
            $("#tabla-mis-horas").slideUp("slow");
            $(this).text($(this).text().replace("Ocultar", "Ver"));
        } else {
            $("#tabla-mis-horas").slideDown("slow");
            $(this).text($(this).text().replace("Ver", "Ocultar"));
        }
    });
});
