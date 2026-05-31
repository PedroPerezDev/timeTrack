<?php
/*
 * AJAX: devuelve el HTML con los resultados de incidencias
 * Recibe los filtros por GET y construye el WHERE dinámicamente
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

// Verificamos sesión y rol antes de hacer nada
if (!isset($_SESSION['user']) || $_SESSION['rol'] !== 'admin') {
    echo "<p class='fichaje-mensaje'>No autorizado.</p>";
    exit;
}

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//---------- LEEMOS LOS FILTROS DEL GET ------------- |
//-----------------------------------------------------|

$mes           = isset($_GET['mes'])  ? (int) $_GET['mes']  : (int) date('n');
$anyo          = isset($_GET['anyo']) ? (int) $_GET['anyo'] : (int) date('Y');
$tipo_filtro   = isset($_GET['tipo_incidencia']) && $_GET['tipo_incidencia'] !== ''
                     ? $_GET['tipo_incidencia']
                     : '';
$trabajador_id = isset($_GET['trabajador_id']) && $_GET['trabajador_id'] !== ''
                     ? (int) $_GET['trabajador_id']
                     : null;

// Búsqueda por nombre si no viene id del select
$buscar_nombre = isset($_GET['buscar_nombre']) ? trim($_GET['buscar_nombre']) : '';

/*
 * Si el admin escribió un nombre en el buscador y no eligió del select,
 * buscamos el id del trabajador que coincida
 */
if (!$trabajador_id && $buscar_nombre !== '') {
    $nombre_escaped = $conexion->real_escape_string($buscar_nombre);
    $res_nombre = $conexion->query("SELECT id FROM usuarios
        WHERE rol = 'trabajador' AND activo = 1
        AND (nombre LIKE '%$nombre_escaped%' OR apellidos LIKE '%$nombre_escaped%')
        LIMIT 1");
    if ($res_nombre && $res_nombre->num_rows > 0) {
        $trabajador_id = (int) $res_nombre->fetch_assoc()['id'];
    }
}

// Nombre del mes en español para los mensajes
$meses_es = [
    1  => 'Enero',     2  => 'Febrero',   3  => 'Marzo',
    4  => 'Abril',     5  => 'Mayo',      6  => 'Junio',
    7  => 'Julio',     8  => 'Agosto',    9  => 'Septiembre',
    10 => 'Octubre',   11 => 'Noviembre', 12 => 'Diciembre'
];
$nombre_mes = $meses_es[$mes];

//-----------------------------------------------------|
//---------- CONSTRUIMOS EL WHERE ------------------- |
//-----------------------------------------------------|

// Primer y último día del mes seleccionado
$primer_dia = date('Y-m-01', mktime(0, 0, 0, $mes, 1, $anyo));
$ultimo_dia = date('Y-m-t',  mktime(0, 0, 0, $mes, 1, $anyo));

/*
 * Construimos el WHERE dinámicamente según los filtros activos
 * Siempre filtramos por mes, y opcionalmente por trabajador y tipo
 */
$where = "WHERE i.fecha BETWEEN '$primer_dia' AND '$ultimo_dia'";

if ($trabajador_id) {
    $where .= " AND i.usuario_id = '$trabajador_id'";
}

if ($tipo_filtro !== '') {
    $where .= " AND i.tipo = '$tipo_filtro'";
}

//-----------------------------------------------------|
//---------- CONSULTA Y AGRUPACIÓN POR TRABAJADOR --- |
//-----------------------------------------------------|

$resultado = $conexion->query("
    SELECT
        i.id,
        i.fecha,
        i.tipo,
        i.minutos,
        i.observaciones,
        i.fecha_creacion,
        u.id       AS usuario_id,
        u.nombre,
        u.apellidos
    FROM incidencias i
    JOIN usuarios u ON u.id = i.usuario_id
    $where
    ORDER BY u.apellidos ASC, u.nombre ASC, i.fecha ASC
");

// Agrupamos por trabajador para mostrar una sección por cada uno
$por_trabajador = [];

while ($fila = $resultado->fetch_assoc()) {
    $uid = $fila['usuario_id'];

    if (!isset($por_trabajador[$uid])) {
        $por_trabajador[$uid] = [
            'nombre'      => $fila['nombre'] . ' ' . $fila['apellidos'],
            'incidencias' => []
        ];
    }

    $por_trabajador[$uid]['incidencias'][] = $fila;
}

desconectar($conexion);

//-----------------------------------------------------|
//---------- ETIQUETAS Y CLASES POR TIPO ----------- |
//-----------------------------------------------------|

$tipos_etiqueta = [
    'retraso'              => 'Retraso',
    'horas_extra'          => 'Horas extra',
    'ausencia'             => 'Ausencia',
    'fichaje_no_realizado' => 'Sin fichar'
];

$tipos_clase = [
    'retraso'              => 'badge-retraso',
    'horas_extra'          => 'badge-extra',
    'ausencia'             => 'badge-ausencia',
    'fichaje_no_realizado' => 'badge-sin-fichar'
];

//-----------------------------------------------------|
//---------- GENERAMOS EL HTML DE RESULTADOS -------- |
//-----------------------------------------------------|

if (empty($por_trabajador)) {

    // No hay resultados para los filtros aplicados
    echo "<p class='fichaje-mensaje'>";
    echo "No hay incidencias en " . $nombre_mes . " " . $anyo;
    echo $trabajador_id ? " para el trabajador seleccionado" : "";
    echo ".";
    echo "</p>";

} else {

    // Título del bloque de resultados
    echo "<h3 class='incidencias-titulo-resultado'>";
    echo "Resultados: " . $nombre_mes . " " . $anyo;
    if ($trabajador_id) {
        echo " — " . $por_trabajador[array_key_first($por_trabajador)]['nombre'];
    }
    echo "</h3>";

    // Una sección por cada trabajador con incidencias
    foreach ($por_trabajador as $datos) {

        echo "<div class='incidencias-trabajador'>";
        echo "<h3 class='incidencias-nombre'>" . $datos['nombre'] . "</h3>";

        echo "<div class='tabla-wrapper'>
            <table class='tabla-apilable'>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Tipo</th>
                        <th>Minutos</th>
                        <th>Observaciones</th>
                        <th>Registrada el</th>
                    </tr>
                </thead>
                <tbody>";

        foreach ($datos['incidencias'] as $inc) {

            $fecha_fmt    = date('d/m/Y', strtotime($inc['fecha']));
            $creacion_fmt = date('d/m/Y H:i', strtotime($inc['fecha_creacion']));
            $minutos_txt  = $inc['minutos'] > 0 ? $inc['minutos'] . ' min' : '—';
            $obs_txt      = $inc['observaciones'] ? $inc['observaciones'] : '—';
            $badge_clase  = $tipos_clase[$inc['tipo']];
            $badge_label  = $tipos_etiqueta[$inc['tipo']];

            echo "<tr>
                <td data-label='Fecha'>$fecha_fmt</td>
                <td data-label='Tipo'>
                    <span class='badge $badge_clase'>$badge_label</span>
                </td>
                <td data-label='Minutos'>$minutos_txt</td>
                <td data-label='Observaciones'>$obs_txt</td>
                <td data-label='Registrada el'>$creacion_fmt</td>
            </tr>";
        }

        $total = count($datos['incidencias']);

        echo "      </tbody>
            </table>
        </div>";

        echo "<p class='incidencias-resumen'>
            Total: $total incidencia(s) en $nombre_mes
        </p>";

        echo "</div>"; // .incidencias-trabajador
    }
}
?>
