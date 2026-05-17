<?php
/*
 * Importa los festivos nacionales y de la Comunidad Valenciana
 * desde la API Nager.Date a la tabla horarios_especiales
 * Se ejecuta automáticamente al cargar el dashboard del admin
 * Solo inserta los que no existen ya para no duplicar
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    echo json_encode(['error' => 'No autorizado']);
    exit;
}

include "../config.php";

$conexion = conectar();

$anyo      = date('Y');
$importados = 0;

// Llamo a la API de festivos de España
$url      = "https://date.nager.at/api/v3/PublicHolidays/" . $anyo . "/ES";
$respuesta = file_get_contents($url);

if ($respuesta === false) {
    echo json_encode(['error' => 'No se ha podido conectar con la API']);
    exit;
}

$todos = json_decode($respuesta, true);

// Filtro solo los nacionales y los de la Comunidad Valenciana
$festivos = array_filter($todos, function($festivo) {
    return empty($festivo['counties']) || in_array('ES-VC', $festivo['counties']);
});

// Recupero todos los trabajadores
$trabajadores = $conexion->query("SELECT id FROM usuarios 
    WHERE rol = 'trabajador'");

while ($t = $trabajadores->fetch_assoc()) {

    foreach ($festivos as $festivo) {

        $fecha = $festivo['date'];
        $nombre = $conexion->real_escape_string($festivo['localName']);

        // Compruebo si ya existe este festivo para este trabajador
        $check = $conexion->query("SELECT id FROM horarios_especiales 
            WHERE usuario_id = '" . $t['id'] . "' 
            AND fecha = '$fecha' 
            AND tipo = 'festivo'");

        // Solo lo inserto si no existe
        if ($check->num_rows == 0) {
            $conexion->query("INSERT INTO horarios_especiales 
                (usuario_id, fecha, tipo, observaciones, creado_por)
                VALUES 
                ('" . $t['id'] . "', '$fecha', 'festivo', '$nombre', '" . $_SESSION['id'] . "')");
            $importados++;
        }
    }
}

desconectar($conexion);

echo json_encode([
    'ok'        => true,
    'importados' => $importados,
    'mensaje'   => $importados > 0 
        ? $importados . " festivos importados correctamente" 
        : "Los festivos ya estaban actualizados"
]);
?>