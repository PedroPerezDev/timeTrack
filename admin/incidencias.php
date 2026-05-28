<?php
/*
 * Gestión de incidencias - Panel de administrador
 * Muestra las incidencias filtradas por mes y/o trabajador
 * Solo se muestran resultados si se ha enviado el formulario
 * Tipos posibles: retraso, horas_extra, ausencia, fichaje_no_realizado
 * Solo accesible para usuarios con rol 'admin'
 */

include "../includes/funciones.php";
verificarSesion('admin');

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//---------- CARGAMOS LA LISTA DE TRABAJADORES ------ |
//-----------------------------------------------------|

/*
 * Necesitamos la lista completa para el select del formulario
 * La cargamos siempre, independientemente de si hay filtro activo
 */
$todos_trabajadores = $conexion->query("
    SELECT id, nombre, apellidos
    FROM usuarios
    WHERE rol = 'trabajador' AND activo = 1
    ORDER BY apellidos ASC, nombre ASC
");

//-----------------------------------------------------|
//---------- LEEMOS LOS FILTROS DEL FORMULARIO ------ |
//-----------------------------------------------------|

/*
 * Los filtros llegan por GET para que la URL sea compartible
 * $buscar indica si el admin ha pulsado el botón de buscar
 * Si no ha buscado aún, no mostramos ningún resultado
 */
$buscar       = isset($_GET['buscar']);
$mes          = isset($_GET['mes'])          ? (int) $_GET['mes']          : (int) date('n');
$anyo         = isset($_GET['anyo'])         ? (int) $_GET['anyo']         : (int) date('Y');
$trabajador_id = isset($_GET['trabajador_id']) && $_GET['trabajador_id'] !== ''
                    ? (int) $_GET['trabajador_id']
                    : null;

// Nombre del mes en español para el título
$meses_es = [
    1  => 'Enero',     2  => 'Febrero',   3  => 'Marzo',
    4  => 'Abril',     5  => 'Mayo',      6  => 'Junio',
    7  => 'Julio',     8  => 'Agosto',    9  => 'Septiembre',
    10 => 'Octubre',   11 => 'Noviembre', 12 => 'Diciembre'
];
$nombre_mes = $meses_es[$mes];

//-----------------------------------------------------|
//---------- CONSULTA SOLO SI HAY BÚSQUEDA ---------- |
//-----------------------------------------------------|

$por_trabajador = [];

if ($buscar) {

    // Primer y último día del mes seleccionado
    $primer_dia = date('Y-m-01', mktime(0, 0, 0, $mes, 1, $anyo));
    $ultimo_dia = date('Y-m-t',  mktime(0, 0, 0, $mes, 1, $anyo));

    /*
     * Construimos el WHERE dinámicamente según los filtros activos
     * Siempre filtramos por mes, y opcionalmente por trabajador
     */
    $where = "WHERE i.fecha BETWEEN '$primer_dia' AND '$ultimo_dia'";

    if ($trabajador_id) {
        $where .= " AND i.usuario_id = '$trabajador_id'";
    }

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

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Incidencias - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Incidencias</h2>

    <!-- ------------------------------------------------- -->
    <!-- Formulario de búsqueda: mes + trabajador          -->
    <!-- ------------------------------------------------- -->
    <form action="incidencias.php" method="GET">
        <fieldset>
            <legend>Filtrar incidencias</legend>

            <!-- Filtro por mes -->
            <label>Mes</label>
            <select name="mes">
                <?php foreach ($meses_es as $num => $nombre): ?>
                    <option value="<?php echo $num; ?>" <?php echo ($num == $mes) ? 'selected' : ''; ?>>
                        <?php echo $nombre; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <!-- Filtro por año -->
            <label>Año</label>
            <input type="number" name="anyo" value="<?php echo $anyo; ?>" min="2020" max="2099">

            <!-- Filtro por trabajador (opcional) -->
            <label>Trabajador <span class="label-opcional">(opcional)</span></label>
            <select name="trabajador_id">
                <option value="">— Todos los trabajadores —</option>
                <?php
                /*
                 * Recorremos el resultado del SELECT de trabajadores
                 * Marcamos como selected el que coincide con el filtro activo
                 */
                while ($t = $todos_trabajadores->fetch_assoc()):
                    $sel = ($trabajador_id == $t['id']) ? 'selected' : '';
                ?>
                    <option value="<?php echo $t['id']; ?>" <?php echo $sel; ?>>
                        <?php echo $t['apellidos'] . ', ' . $t['nombre']; ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <input type="submit" name="buscar" value="Buscar incidencias">

        </fieldset>
    </form>

    <!-- ------------------------------------------------- -->
    <!-- Resultados: solo se muestran tras buscar          -->
    <!-- ------------------------------------------------- -->

    <?php if (!$buscar): ?>

        <!-- Estado inicial: invitamos al admin a filtrar -->
        <p class="fichaje-mensaje">
            Selecciona un mes y pulsa <strong>Buscar incidencias</strong> para ver los resultados.
        </p>

    <?php elseif (empty($por_trabajador)): ?>

        <!-- Se buscó pero no hay resultados -->
        <p class="fichaje-mensaje">
            No hay incidencias en <?php echo $nombre_mes . ' ' . $anyo; ?>
            <?php echo $trabajador_id ? ' para el trabajador seleccionado' : ''; ?>.
        </p>

    <?php else: ?>

        <!-- Título del bloque de resultados -->
        <h3 class="incidencias-titulo-resultado">
            Resultados: <?php echo $nombre_mes . ' ' . $anyo; ?>
            <?php if ($trabajador_id): ?>
                — <?php echo $por_trabajador[array_key_first($por_trabajador)]['nombre']; ?>
            <?php endif; ?>
        </h3>

        <!-- Una sección por cada trabajador con incidencias -->
        <?php foreach ($por_trabajador as $datos): ?>

            <div class="incidencias-trabajador">

                <h3 class="incidencias-nombre"><?php echo $datos['nombre']; ?></h3>

                <div class="tabla-wrapper">
                    <table class="tabla-apilable">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Tipo</th>
                                <th>Minutos</th>
                                <th>Observaciones</th>
                                <th>Registrada el</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($datos['incidencias'] as $inc): ?>
                            <tr>

                                <td data-label="Fecha">
                                    <?php echo date('d/m/Y', strtotime($inc['fecha'])); ?>
                                </td>

                                <td data-label="Tipo">
                                    <span class="badge <?php echo $tipos_clase[$inc['tipo']]; ?>">
                                        <?php echo $tipos_etiqueta[$inc['tipo']]; ?>
                                    </span>
                                </td>

                                <td data-label="Minutos">
                                    <?php echo $inc['minutos'] > 0 ? $inc['minutos'] . ' min' : '—'; ?>
                                </td>

                                <td data-label="Observaciones">
                                    <?php echo $inc['observaciones'] ? $inc['observaciones'] : '—'; ?>
                                </td>

                                <td data-label="Registrada el">
                                    <?php echo date('d/m/Y H:i', strtotime($inc['fecha_creacion'])); ?>
                                </td>

                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <p class="incidencias-resumen">
                    Total: <?php echo count($datos['incidencias']); ?> incidencia(s) en <?php echo $nombre_mes; ?>
                </p>

            </div>

        <?php endforeach; ?>

    <?php endif; ?>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>
