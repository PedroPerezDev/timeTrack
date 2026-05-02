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
$tipo         = $_POST['tipo'];         // entrada_1, salida_1, entrada_2, salida_2
$hora_prevista = $_POST['hora'];        // hora prevista según el horario
$fecha_hoy    = date('Y-m-d');
$hora_actual  = date('H:i:s');          // hora exacta en que pulsa el botón

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
 * Positivo = ha llegado tarde o salido antes
 * Negativo = ha llegado antes o salido después (horas extra)
 */
$minutos_diferencia = 0;

if (!empty($hora_prevista)) {

    // Convierto las horas a minutos para poder restarlas
    // strtotime convierte la hora a segundos desde 1970
    // la diferencia en segundos la divido entre 60 para tener minutos
    $prevista_minutos = strtotime($hora_prevista);
    $actual_minutos   = strtotime($hora_actual);
    $minutos_diferencia = round(($actual_minutos - $prevista_minutos) / 60);
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
 * Uso 5 minutos de margen para no generar incidencias por pequeños retrasos
 */
if (abs($minutos_diferencia) > 5) {

    // Determino el tipo de incidencia según si es entrada o salida
    // y si los minutos son positivos o negativos
    if ($tipo == 'entrada_1' || $tipo == 'entrada_2') {
        $tipo_incidencia = $minutos_diferencia > 0 ? 'retraso' : 'horas_extra';
    } else {
        $tipo_incidencia = $minutos_diferencia < 0 ? 'horas_extra' : 'retraso';
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

/*
 * Devuelvo un JSON con el resultado del fichaje
 * jQuery lo recibirá y mostrará el mensaje en pantalla
 * sin recargar la página
 */
$respuesta = [
    'ok'                  => true,
    'tipo'                => $tipo,
    'hora_fichaje'        => substr($hora_actual, 0, 5),
    'minutos_diferencia'  => $minutos_diferencia,
    'mensaje'             => 'Fichaje registrado a las ' . substr($hora_actual, 0, 5)
];

echo json_encode($respuesta);
?>