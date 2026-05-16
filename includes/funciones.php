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
        header("Location: ../index.php");
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
function mostrarFoto($foto, $ancho = 40, $estilo = 'border-radius:50%') {
    if (!empty($foto)) {
        return "<img src='/uploads/fotos_trabajadores/$foto' width='$ancho' style='$estilo'>";
    }
    return "Sin foto";
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
