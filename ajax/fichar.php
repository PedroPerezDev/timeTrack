<?php
/*
 * Archivo AJAX para registrar el fichaje del trabajador
 * Recibe el tipo de fichaje y la hora prevista por POST
 * Calcula los minutos de diferencia y guarda el fichaje
 * Devuelve una respuesta en formato JSON
 */

session_start();

// Si no hay sesión activa devuelvo error en JSON
if (!isset($_SESSION['user']) || $_SESSION['rol'] != "trabajador") {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include "../config.php";

$conexion = conectar();

// Recojo los datos que manda jQuery por POST
$tipo          = $_POST['tipo'];
$hora_prevista = $_POST['hora'];
$fecha_hoy     = date('Y-m-d');
$hora_actual   = date('H:i:s');

//-----------------------------------------------------|
//---------- COMPRUEBO QUE NO EXISTE YA --------------|
//-----------------------------------------------------|

/*
 * Antes de guardar compruebo que no existe ya un fichaje
 * de este tipo para este trabajador hoy
 * Esto evita que se pueda fichar dos veces el mismo tipo
 */
$check = $conexion->query("SELECT id FROM fichajes 
    WHERE usuario_id = '" . $_SESSION['id'] . "'
    AND fecha = '$fecha_hoy'
    AND tipo = '$tipo'");

if ($check->num_rows > 0) {
    echo json_encode(['error' => 'Ya has fichado este turno hoy']);
    exit;
}

//-----------------------------------------------------|
//---------- CALCULO MINUTOS DE DIFERENCIA ------------|
//-----------------------------------------------------|

/*
 * Calculo cuántos minutos de diferencia hay entre
 * la hora prevista y la hora real del fichaje
 *
 * En entradas: llegar tarde es negativo (trabaja menos)
 *              llegar antes es positivo (trabaja más)
 * En salidas:  salir antes es negativo (trabaja menos)
 *              salir tarde es positivo (trabaja más)
 *
 * Por eso invertimos el signo en las entradas
 */
$minutos_diferencia = 0;

if (!empty($hora_prevista)) {

    $prevista_minutos = strtotime($hora_prevista);
    $actual_minutos   = strtotime($hora_actual);
    $diferencia       = round(($actual_minutos - $prevista_minutos) / 60);

    if ($tipo == 'entrada_1' || $tipo == 'entrada_2') {
        // En entradas invertimos el signo
        // llegar tarde = negativo, llegar antes = positivo
        $minutos_diferencia = -$diferencia;
    } else {
        // En salidas el signo es directo
        // salir tarde = positivo, salir antes = negativo
        $minutos_diferencia = $diferencia;
    }
}

//-----------------------------------------------------|
//---------- GUARDO EL FICHAJE ----------------------- |
//-----------------------------------------------------|

$insertar = $conexion->query("INSERT INTO fichajes 
    (usuario_id, fecha, hora_fichaje, tipo, minutos_diferencia)
    VALUES 
    ('" . $_SESSION['id'] . "', '$fecha_hoy', '$hora_actual', '$tipo', '$minutos_diferencia')");

if (!$insertar) {
    echo json_encode(['error' => 'Error al guardar el fichaje: ' . $conexion->error]);
    exit;
}

//-----------------------------------------------------|
//---------- GUARDO INCIDENCIA SI HAY DIFERENCIA -----|
//-----------------------------------------------------|

/*
 * Si hay diferencia de más de 5 minutos genero una incidencia
 * automática para que el admin pueda verla en los informes
 * Positivo = minutos a favor del trabajador
 * Negativo = minutos en contra del trabajador
 */
if (abs($minutos_diferencia) > 5) {

    if ($minutos_diferencia > 0) {
        $tipo_incidencia = 'horas_extra';
    } else {
        $tipo_incidencia = 'retraso';
    }

    $observacion = "Fichaje automático. Diferencia de " . abs($minutos_diferencia) . " minutos en " . $tipo;

    $conexion->query("INSERT INTO incidencias
        (usuario_id, fecha, tipo, minutos, observaciones, creado_por)
        VALUES
        ('" . $_SESSION['id'] . "', '$fecha_hoy', '$tipo_incidencia', 
        '" . abs($minutos_diferencia) . "', '$observacion', '" . $_SESSION['id'] . "')");
}

desconectar($conexion);

//-----------------------------------------------------|
//---------- DEVUELVO RESPUESTA JSON -----------------|
//-----------------------------------------------------|

$respuesta = [
    'ok'                 => true,
    'tipo'               => $tipo,
    'hora_fichaje'       => substr($hora_actual, 0, 5),
    'minutos_diferencia' => $minutos_diferencia,
    'mensaje'            => 'Fichaje registrado a las ' . substr($hora_actual, 0, 5)
];

echo json_encode($respuesta);
?>