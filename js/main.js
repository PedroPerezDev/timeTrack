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