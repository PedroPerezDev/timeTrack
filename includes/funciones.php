<?php
/*
 * Funciones de uso común en toda la aplicación
 */


/*
 * Verifica que hay sesión activa con el rol indicado.
 * Si no, redirige al login.
 */
function verificarSesion($rol) {
    session_start();
    if (!isset($_SESSION['user']) || $_SESSION['rol'] != $rol) {
        header("Location: /index.php");
        exit;
    }
}


/*
 * Muestra un mensaje de éxito o error.
 * $tipo: 'ok' para verde, 'error' para rojo
 */
function mostrarMensaje($texto, $tipo = 'ok') {
    $color = $tipo === 'ok' ? 'green' : 'red';
    echo "<p style='color:$color'>$texto</p>";
}


/*
 * Devuelve el HTML de la foto de un trabajador.
 * Si no tiene foto devuelve texto alternativo.
 */
function mostrarFoto($foto, $ancho = 40, $estilo = 'border-radius:50%', $nombre = '', $apellidos = '') {
    if (!empty($foto)) {
        // Tiene foto: la mostramos con el estilo indicado
        return "<img src='/uploads/fotos_trabajadores/$foto' width='$ancho' height='$ancho' style='$estilo;object-fit:cover;'>";
    }

    /*
     * Sin foto: mostramos un círculo gris con las iniciales del trabajador
     * Si no se pasan nombre/apellidos el círculo queda vacío pero mantiene el tamaño
     * El tamaño del círculo es igual al de la foto para que no descoloque el layout
     */
    $ini_n = !empty($nombre)    ? mb_strtoupper(mb_substr($nombre,    0, 1, 'UTF-8'), 'UTF-8') : '';
    $ini_a = !empty($apellidos) ? mb_strtoupper(mb_substr($apellidos, 0, 1, 'UTF-8'), 'UTF-8') : '';
    $font  = max(10, (int) ($ancho * 0.38));

    return "<span style='"
        . "display:inline-flex;"
        . "align-items:center;"
        . "justify-content:center;"
        . "width:{$ancho}px;"
        . "height:{$ancho}px;"
        . "border-radius:50%;"
        . "background-color:var(--color-borde);"
        . "color:var(--color-texto-apagado);"
        . "font-size:{$font}px;"
        . "font-weight:600;"
        . "vertical-align:middle;"
        . "flex-shrink:0;"
        . "'>{$ini_n}{$ini_a}</span>";
}


/*
 * Calcula los días de vacaciones disponibles.
 */
function diasVacacionesDisponibles($total, $gastados) {
    return $total - $gastados;
}


/*
 * Formatea una fecha de Y-m-d a d/m/Y.
 */
function formatearFecha($fecha) {
    if (empty($fecha)) return 'No especificada';
    return date('d/m/Y', strtotime($fecha));
}


/*
 * Sube una foto de trabajador al servidor.
 * Devuelve el nombre del archivo o cadena vacía si falla.
 */
function subirFoto($file, $ruta_base = '../uploads/fotos_trabajadores/') {
    if (empty($file['name'])) return '';
    $nombre = time() . '_' . $file['name'];
    if (move_uploaded_file($file['tmp_name'], $ruta_base . $nombre)) {
        return $nombre;
    }
    return '';
}


/*
 * Elimina la foto de un trabajador del servidor si existe.
 */
function eliminarFoto($foto, $ruta_base = '../uploads/fotos_trabajadores/') {
    if (!empty($foto) && file_exists($ruta_base . $foto)) {
        unlink($ruta_base . $foto);
    }
}
