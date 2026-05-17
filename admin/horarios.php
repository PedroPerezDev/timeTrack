<?php
/*
 * Gestión de horarios - Panel de administrador
 * Permite asignar el horario semanal a cada trabajador
 * y gestionar días especiales
 */

include "../includes/funciones.php";
verificarSesion('admin');

include "../config.php";

$conexion = conectar();

if (!isset($_GET['id'])) {
    header("Location: trabajadores.php");
    exit;
}

$id_trabajador        = $_GET['id'];
$resultado_trabajador = $conexion->query("SELECT nombre, apellidos FROM usuarios WHERE id = '$id_trabajador'");

if ($resultado_trabajador->num_rows == 0) {
    header("Location: trabajadores.php");
    exit;
}

$trabajador = $resultado_trabajador->fetch_assoc();

$dias = [
    1 => "Lunes",
    2 => "Martes",
    3 => "Miércoles",
    4 => "Jueves",
    5 => "Viernes"
];

//-----------------------------------------------------|
//---------- GUARDAR HORARIO SEMANAL -----------------|
//-----------------------------------------------------|

if (isset($_POST['guardar_horario'])) {

    $errores = 0;

    for ($dia = 1; $dia <= 5; $dia++) {

        $entrada_1 = $_POST['entrada_1_' . $dia];
        $salida_1  = $_POST['salida_1_'  . $dia];
        $entrada_2 = $_POST['entrada_2_' . $dia];
        $salida_2  = $_POST['salida_2_'  . $dia];

        $check = $conexion->query("SELECT id FROM horarios 
            WHERE usuario_id = '$id_trabajador' AND dia_semana = '$dia'");

        if ($check->num_rows > 0) {
            $ok = $conexion->query("UPDATE horarios SET
                hora_entrada_1 = '$entrada_1',
                hora_salida_1  = '$salida_1',
                hora_entrada_2 = '$entrada_2',
                hora_salida_2  = '$salida_2'
                WHERE usuario_id = '$id_trabajador' AND dia_semana = '$dia'");
        } else {
            $ok = $conexion->query("INSERT INTO horarios 
                (usuario_id, dia_semana, hora_entrada_1, hora_salida_1, hora_entrada_2, hora_salida_2)
                VALUES ('$id_trabajador', '$dia', '$entrada_1', '$salida_1', '$entrada_2', '$salida_2')");
        }

        if (!$ok) $errores++;
    }

    if ($errores == 0) {
        $mensaje_ok = "Horario guardado correctamente";
    } else {
        $mensaje_error = "Ha habido algún error al guardar el horario";
    }
}

//-----------------------------------------------------|
//---------- GUARDAR DÍA ESPECIAL ------------------- |
//-----------------------------------------------------|

if (isset($_POST['guardar_especial'])) {

    $fecha         = $_POST['fecha'];
    $tipo          = $_POST['tipo'];
    $observaciones = $_POST['observaciones'];
    $e1 = !empty($_POST['esp_entrada_1']) ? "'" . $_POST['esp_entrada_1'] . "'" : 'NULL';
    $s1 = !empty($_POST['esp_salida_1'])  ? "'" . $_POST['esp_salida_1']  . "'" : 'NULL';
    $e2 = !empty($_POST['esp_entrada_2']) ? "'" . $_POST['esp_entrada_2'] . "'" : 'NULL';
    $s2 = !empty($_POST['esp_salida_2'])  ? "'" . $_POST['esp_salida_2']  . "'" : 'NULL';

    if (empty($fecha) || empty($tipo)) {
        $mensaje_error = "La fecha y el tipo son obligatorios";
    } else {

        $insert = $conexion->query("INSERT INTO horarios_especiales
            (usuario_id, fecha, tipo, hora_entrada_1, hora_salida_1, hora_entrada_2, hora_salida_2, observaciones, creado_por)
            VALUES ('$id_trabajador', '$fecha', '$tipo', $e1, $s1, $e2, $s2, '$observaciones', '" . $_SESSION['id'] . "')");

        if ($insert) {
            $mensaje_ok = "Día especial guardado correctamente";
        } else {
            $mensaje_error = "Error al guardar el día especial: " . $conexion->error;
        }
    }
}

//-----------------------------------------------------|
//---------- BORRAR DÍA ESPECIAL --------------------|
//-----------------------------------------------------|

if (isset($_POST['borrar_especial'])) {

    $id_especial = $_POST['id_especial'];
    $borrar      = $conexion->query("DELETE FROM horarios_especiales WHERE id = '$id_especial'");

    if ($borrar) {
        $mensaje_ok = "Día especial borrado correctamente";
    } else {
        $mensaje_error = "Error al borrar el día especial";
    }
}

$horario_actual = [];
$resultado_horario = $conexion->query("SELECT * FROM horarios WHERE usuario_id = '$id_trabajador'");
while ($fila = $resultado_horario->fetch_assoc()) {
    $horario_actual[$fila['dia_semana']] = $fila;
}

$especiales = $conexion->query("SELECT * FROM horarios_especiales 
    WHERE usuario_id = '$id_trabajador' ORDER BY fecha ASC");

desconectar($conexion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios - TimeTrack</title>

    <!-- Flatpickr — calendario para selección de fecha en día especial -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            font-family:   var(--fuente-cuerpo);
            border-radius: var(--radio-mediano);
            box-shadow:    0 4px 16px rgba(0,0,0,0.12);
            border:        1px solid var(--color-borde);
        }
        .flatpickr-day.selected,
        .flatpickr-day.selected:hover {
            background:   var(--color-principal);
            border-color: var(--color-principal);
        }
        .flatpickr-day:hover { background: var(--color-borde); }
        .flatpickr-months .flatpickr-month {
            background: var(--color-principal);
            color: #fff;
        }
        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year { color: #fff; }
        .flatpickr-weekday { color: var(--color-texto-apagado); }
    </style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Horario de <?php echo $trabajador['nombre'] . " " . $trabajador['apellidos']; ?></h2>

    <?php
    if (isset($mensaje_ok))    mostrarMensaje($mensaje_ok);
    if (isset($mensaje_error)) mostrarMensaje($mensaje_error, 'error');
    ?>

    <a href="trabajadores.php">
        <button type="button">← Volver a trabajadores</button>
    </a>

    <h3>Horario semanal</h3>

    <form action="horarios.php?id=<?php echo $id_trabajador; ?>" method="POST">
    <div class="tabla-wrapper">
    <table class="tabla-apilable">
        <thead>
        <tr>
            <th>Día</th>
            <th>Entrada mañana</th>
            <th>Salida mañana</th>
            <th>Entrada tarde</th>
            <th>Salida tarde</th>
        </tr>
        </thead>
        <tbody>

        <?php foreach ($dias as $num => $nombre):
            $e1 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_entrada_1'] : "";
            $s1 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_salida_1']  : "";
            $e2 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_entrada_2'] : "";
            $s2 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_salida_2']  : "";
            echo "<tr>
                <td data-label='Día'><b>$nombre</b></td>
                <td data-label='Entrada mañana'><input type='time' name='entrada_1_$num' value='$e1'></td>
                <td data-label='Salida mañana'><input type='time' name='salida_1_$num' value='$s1'></td>
                <td data-label='Entrada tarde'><input type='time' name='entrada_2_$num' value='$e2'></td>
                <td data-label='Salida tarde'><input type='time' name='salida_2_$num' value='$s2'></td>
            </tr>";
        endforeach; ?>

        </tbody>
    </table>
    </div>
    <input type="submit" name="guardar_horario" value="Guardar horario">
    </form>

    <button type="button" id="btn-mostrar-especial">+ Añadir día especial</button>

    <div id="form-especial" style="display:none">
        <h3>Añadir día especial</h3>
        <form action="horarios.php?id=<?php echo $id_trabajador; ?>" method="POST">
        <fieldset>
            <legend>NUEVO DÍA ESPECIAL</legend>

            <label>Fecha *</label>
            <input type="text" name="fecha" id="fecha-especial"
                placeholder="Selecciona una fecha" readonly>

            <label>Tipo *</label>
            <select name="tipo">
                <option value="vacaciones">Vacaciones</option>
                <option value="festivo">Festivo</option>
                <option value="libre">Día libre</option>
                <option value="medico">Médico</option>
                <option value="asuntos_propios">Asuntos propios</option>
                <option value="cambio_horario">Cambio de horario</option>
            </select>

            <label>Entrada mañana (solo si es cambio de horario)</label>
            <input type="time" name="esp_entrada_1">

            <label>Salida mañana</label>
            <input type="time" name="esp_salida_1">

            <label>Entrada tarde</label>
            <input type="time" name="esp_entrada_2">

            <label>Salida tarde</label>
            <input type="time" name="esp_salida_2">

            <label>Observaciones</label>
            <input type="text" name="observaciones" placeholder="Añade una nota si lo necesitas">

            <input type="submit" name="guardar_especial" value="Guardar día especial">
        </fieldset>
        </form>
    </div>

    <?php if ($especiales->num_rows > 0): ?>

        <button type="button" id="btn-mostrar-especiales">Ver días especiales registrados</button>

        <div id="tabla-especiales" style="display:none">
            <h3>Días especiales registrados</h3>
            <div class="tabla-wrapper"><table class="tabla-apilable">
                <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Tipo</th>
                    <th>Entrada mañana</th>
                    <th>Salida mañana</th>
                    <th>Entrada tarde</th>
                    <th>Salida tarde</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
                </thead>
                <tbody>
                <?php while ($esp = $especiales->fetch_assoc()): ?>
                <tr>
                    <td data-label="Fecha"><?php echo formatearFecha($esp['fecha']); ?></td>
                    <td data-label="Tipo"><?php echo ucfirst(str_replace('_', ' ', $esp['tipo'])); ?></td>
                    <td data-label="Entrada mañana"><?php echo $esp['hora_entrada_1'] ?? '-'; ?></td>
                    <td data-label="Salida mañana"><?php echo $esp['hora_salida_1']  ?? '-'; ?></td>
                    <td data-label="Entrada tarde"><?php echo $esp['hora_entrada_2'] ?? '-'; ?></td>
                    <td data-label="Salida tarde"><?php echo $esp['hora_salida_2']  ?? '-'; ?></td>
                    <td data-label="Observaciones"><?php echo $esp['observaciones']; ?></td>
                    <td data-label="Acciones">
                        <form action="horarios.php?id=<?php echo $id_trabajador; ?>" method="POST" style="display:inline">
                            <input type="hidden" name="id_especial" value="<?php echo $esp['id']; ?>">
                            <input type="submit" name="borrar_especial" value="Borrar"
                                onclick="return confirm('¿Seguro que quieres borrar este día especial?')">
                        </form>
                    </td>
                </tr>
                <?php endwhile; ?>
                </tbody>
            </table></div>
        </div>

    <?php else: ?>
        <p>No hay días especiales registrados para este trabajador</p>
    <?php endif; ?>

    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">Fuente: API Nager.Date</p>
    <div id="festivos"></div>

</main>

<?php include "../includes/footer.php"; ?>

<!-- Flatpickr JS con localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
flatpickr("#fecha-especial", {
    locale:     "es",
    dateFormat: "Y-m-d"
});
</script>

</body>
</html>
