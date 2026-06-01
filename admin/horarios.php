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
/*
 * Traemos también los días de vacaciones para mostrarlos al admin
 */
$resultado_trabajador = $conexion->query("SELECT nombre, apellidos, dias_vacaciones_totales, dias_vacaciones_gastados FROM usuarios WHERE id = '$id_trabajador'");

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

    $fecha_inicio  = $_POST['fecha_inicio'];
    $fecha_fin     = $_POST['fecha_fin'];
    $tipo          = $_POST['tipo'];
    $observaciones = $conexion->real_escape_string($_POST['observaciones']);
    $e1 = !empty($_POST['esp_entrada_1']) ? "'" . $_POST['esp_entrada_1'] . "'" : 'NULL';
    $s1 = !empty($_POST['esp_salida_1'])  ? "'" . $_POST['esp_salida_1']  . "'" : 'NULL';
    $e2 = !empty($_POST['esp_entrada_2']) ? "'" . $_POST['esp_entrada_2'] . "'" : 'NULL';
    $s2 = !empty($_POST['esp_salida_2'])  ? "'" . $_POST['esp_salida_2']  . "'" : 'NULL';

    if (empty($fecha_inicio) || empty($tipo)) {
        $mensaje_error = "La fecha y el tipo son obligatorios";
    } else {

        /*
         * Recorremos cada día del rango e insertamos un registro por día
         * Si solo se seleccionó un día, fecha_inicio === fecha_fin y el bucle
         * se ejecuta una sola vez (comportamiento idéntico al anterior)
         */
        $dias_insertados = 0;
        $fecha_actual    = strtotime($fecha_inicio);
        $fecha_limite    = strtotime($fecha_fin);
        $admin_id        = $_SESSION['id'];

        while ($fecha_actual <= $fecha_limite) {

            $fecha_dia = date('Y-m-d', $fecha_actual);

            $insert = $conexion->query("INSERT INTO horarios_especiales
                (usuario_id, fecha, tipo, hora_entrada_1, hora_salida_1, hora_entrada_2, hora_salida_2, observaciones, creado_por)
                VALUES ('$id_trabajador', '$fecha_dia', '$tipo', $e1, $s1, $e2, $s2, '$observaciones', '$admin_id')");

            if ($insert) {
                $dias_insertados++;

                // Si es vacaciones sumamos 1 día gastado por cada día del rango
                if ($tipo === 'vacaciones') {
                    $conexion->query("UPDATE usuarios
                        SET dias_vacaciones_gastados = dias_vacaciones_gastados + 1
                        WHERE id = '$id_trabajador'");
                }
            }

            // Avanzamos al día siguiente
            $fecha_actual = strtotime('+1 day', $fecha_actual);
        }

        if ($dias_insertados > 0) {
            $mensaje_ok = $dias_insertados === 1
                ? "Día especial guardado correctamente"
                : "$dias_insertados días especiales guardados correctamente";
        } else {
            $mensaje_error = "Error al guardar los días especiales";
        }
    }
}

//-----------------------------------------------------|
//---------- BORRAR DÍA ESPECIAL --------------------|
//-----------------------------------------------------|

if (isset($_POST['borrar_especial'])) {

    $id_especial = $_POST['id_especial'];

    /*
     * Antes de borrar comprobamos el tipo del día especial
     * Si era vacaciones, hay que restar 1 del contador para que cuadre
     */
    $res_tipo = $conexion->query("SELECT tipo FROM horarios_especiales WHERE id = '$id_especial'");
    $tipo_especial = ($res_tipo && $res_tipo->num_rows > 0) ? $res_tipo->fetch_assoc()['tipo'] : '';

    $borrar = $conexion->query("DELETE FROM horarios_especiales WHERE id = '$id_especial'");

    if ($borrar) {
        $mensaje_ok = "Día especial borrado correctamente";

        // Si era vacaciones, devolvemos el día al contador
        if ($tipo_especial === 'vacaciones') {
            $conexion->query("UPDATE usuarios
                SET dias_vacaciones_gastados = GREATEST(dias_vacaciones_gastados - 1, 0)
                WHERE id = '$id_trabajador'");
        }

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

    <!-- Resumen de vacaciones del trabajador -->
    <?php
    $dias_disponibles = $trabajador['dias_vacaciones_totales'] - $trabajador['dias_vacaciones_gastados'];
    ?>
    <div class="perfil-datos" style="margin-bottom:20px">
        <h3>Días de vacaciones</h3>

        <div class="perfil-campo">
            <span class="perfil-label">Días totales</span>
            <span class="perfil-valor"><?php echo $trabajador['dias_vacaciones_totales']; ?> días</span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Días gastados</span>
            <span class="perfil-valor"><?php echo $trabajador['dias_vacaciones_gastados']; ?> días</span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Días disponibles</span>
            <span class="perfil-valor" style="color:var(--color-principal); font-weight:500">
                <?php echo $dias_disponibles; ?> días
            </span>
        </div>
    </div>

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
            <input type="text" id="fecha-especial"
                placeholder="Selecciona un día o un rango" readonly>
            <!-- Flatpickr rellena estos dos campos ocultos con las fechas del rango -->
            <input type="hidden" name="fecha_inicio" id="fecha-inicio">
            <input type="hidden" name="fecha_fin"    id="fecha-fin">

            <label>Tipo *</label>
            <select name="tipo">
                <option value="vacaciones">Vacaciones</option>
                <option value="libre">Libre dado por la empresa</option>
                <option value="festivo">Festivo local</option>
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
    mode:       "range",
    dateFormat: "Y-m-d",
    /*
     * Al seleccionar el rango rellenamos los dos campos ocultos
     * selectedDates[0] = fecha inicio, selectedDates[1] = fecha fin
     * Si solo se selecciona un día, fecha_fin = fecha_inicio
     */
    onChange: function(selectedDates) {
        var fmt = function(d) {
            return d.getFullYear() + '-'
                 + String(d.getMonth() + 1).padStart(2, '0') + '-'
                 + String(d.getDate()).padStart(2, '0');
        };
        if (selectedDates.length >= 1) {
            document.getElementById('fecha-inicio').value = fmt(selectedDates[0]);
            document.getElementById('fecha-fin').value    = selectedDates[1]
                ? fmt(selectedDates[1])
                : fmt(selectedDates[0]);
        }
    }
});
</script>

</body>
</html>
