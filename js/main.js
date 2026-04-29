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