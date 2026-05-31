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
$buscar        = isset($_GET['buscar']);
$mes           = isset($_GET['mes'])           ? (int) $_GET['mes']           : (int) date('n');
$anyo          = isset($_GET['anyo'])           ? (int) $_GET['anyo']          : (int) date('Y');
$trabajador_id = isset($_GET['trabajador_id']) && $_GET['trabajador_id'] !== ''
                     ? (int) $_GET['trabajador_id']
                     : null;

// Búsqueda por nombre escrito: si el admin escribe un nombre buscamos el id
$buscar_nombre = isset($_GET['buscar_nombre']) ? trim($_GET['buscar_nombre']) : '';

// Filtro por tipo de incidencia (vacío = todos)
$tipo_filtro = isset($_GET['tipo_incidencia']) && $_GET['tipo_incidencia'] !== ''
                   ? $_GET['tipo_incidencia']
                   : '';

/*
 * Si hay texto en el buscador de nombre intentamos localizar al trabajador
 * Buscamos por nombre O apellidos con LIKE para ser flexible
 * Si encontramos coincidencia usamos ese id como filtro
 * El select tiene prioridad: si el admin elige del desplegable ignoramos el texto
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

// Nombre del mes en español para el título
$meses_es = [
    1  => 'Enero',     2  => 'Febrero',   3  => 'Marzo',
    4  => 'Abril',     5  => 'Mayo',      6  => 'Junio',
    7  => 'Julio',     8  => 'Agosto',    9  => 'Septiembre',
    10 => 'Octubre',   11 => 'Noviembre', 12 => 'Diciembre'
];
$nombre_mes = $meses_es[$mes];

desconectar($conexion);

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
    <form id="form-incidencias" action="incidencias.php" method="GET">
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

            <!-- Buscador de trabajador por nombre escrito -->
            <label>Buscar por nombre <span class="label-opcional">(opcional)</span></label>
            <input type="text" name="buscar_nombre"
                placeholder="Escribe nombre o apellido..."
                value="<?php echo htmlspecialchars($buscar_nombre); ?>">

            <!-- O seleccionar del desplegable -->
            <label>O selecciona de la lista <span class="label-opcional">(opcional)</span></label>
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

            <!-- Filtro por tipo de incidencia -->
            <label>Tipo de incidencia <span class="label-opcional">(opcional)</span></label>
            <select name="tipo_incidencia">
                <option value="">— Todos los tipos —</option>
                <?php
                // Opciones de tipo: las mismas que se usan en el sistema
                $tipos_opciones = [
                    'retraso'              => 'Retraso',
                    'horas_extra'          => 'Horas extra',
                    
                    'fichaje_no_realizado' => 'Sin fichar',
                ];
                foreach ($tipos_opciones as $val => $etiqueta):
                    $sel = ($tipo_filtro === $val) ? 'selected' : '';
                ?>
                    <option value="<?php echo $val; ?>" <?php echo $sel; ?>>
                        <?php echo $etiqueta; ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <input type="submit" name="buscar" value="Buscar incidencias">

        </fieldset>
    </form>

    <!-- ------------------------------------------------- -->
    <!-- Resultados: jQuery los inyecta aquí vía AJAX      -->
    <!-- ------------------------------------------------- -->

    <div id="resultados-incidencias"></div>

</main>

<?php include "../includes/footer.php"; ?>

<!-- ============================================================ -->
<!-- AJAX: busca incidencias sin recargar la página              -->
<!-- ============================================================ -->
<script>
$(document).ready(function() {

    /*
     * Al enviar el formulario interceptamos el submit con jQuery
     * Recogemos los datos con serialize() y los mandamos por GET
     * a get_incidencias.php, que devuelve solo el HTML de resultados
     * El div #resultados-incidencias se actualiza sin recargar la página
     */
    $("#form-incidencias").on("submit", function(e) {

        // Evitamos el submit normal del formulario
        e.preventDefault();

        var $resultados = $("#resultados-incidencias");

        // Mostramos un indicador de carga mientras esperamos la respuesta
        $resultados.html("<p style='color:var(--color-texto-apagado)'>Buscando...</p>");

        $.ajax({
            url:    "/ajax/get_incidencias.php",
            method: "GET",
            data:   $(this).serialize(),
            success: function(html) {
                // Inyectamos el HTML devuelto por el servidor con fadeIn
                $resultados.hide().html(html).fadeIn("fast");
            },
            error: function() {
                $resultados.html("<p style='color:red'>Error al cargar las incidencias. Inténtalo de nuevo.</p>");
            }
        });
    });

});
</script>

</body>
</html>
