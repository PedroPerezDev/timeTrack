<?php
/*
 * Gestión de horarios - Panel de administrador
 * Permite asignar el horario semanal a cada trabajador
 * y gestionar días especiales (cambios, vacaciones, festivos)
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

if (!isset($_GET['id'])) {
    header("Location: trabajadores.php");
    exit;
}

$id_trabajador = $_GET['id'];

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
//------------- GUARDAR HORARIO SEMANAL --------------|
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
            $update = $conexion->query("UPDATE horarios SET
                hora_entrada_1 = '$entrada_1',
                hora_salida_1  = '$salida_1',
                hora_entrada_2 = '$entrada_2',
                hora_salida_2  = '$salida_2'
                WHERE usuario_id = '$id_trabajador' AND dia_semana = '$dia'");
            if (!$update) $errores++;
        } else {
            $insert = $conexion->query("INSERT INTO horarios 
                (usuario_id, dia_semana, hora_entrada_1, hora_salida_1, hora_entrada_2, hora_salida_2)
                VALUES ('$id_trabajador', '$dia', '$entrada_1', '$salida_1', '$entrada_2', '$salida_2')");
            if (!$insert) $errores++;
        }
    }

    if ($errores == 0) {
        $mensaje_ok = "Horario guardado correctamente";
    } else {
        $mensaje_error = "Ha habido algún error al guardar el horario";
    }
}

//-----------------------------------------------------|
//----------- GUARDAR DÍA ESPECIAL ------------------|
//-----------------------------------------------------|

if (isset($_POST['guardar_especial'])) {

    $fecha         = $_POST['fecha'];
    $tipo          = $_POST['tipo'];
    $observaciones = $_POST['observaciones'];
    $entrada_1     = !empty($_POST['esp_entrada_1']) ? $_POST['esp_entrada_1'] : null;
    $salida_1      = !empty($_POST['esp_salida_1'])  ? $_POST['esp_salida_1']  : null;
    $entrada_2     = !empty($_POST['esp_entrada_2']) ? $_POST['esp_entrada_2'] : null;
    $salida_2      = !empty($_POST['esp_salida_2'])  ? $_POST['esp_salida_2']  : null;

    if (empty($fecha) || empty($tipo)) {
        $mensaje_error = "La fecha y el tipo son obligatorios";
    } else {

        $entrada_1 = $entrada_1 ?? 'NULL';
        $salida_1  = $salida_1  ?? 'NULL';
        $entrada_2 = $entrada_2 ?? 'NULL';
        $salida_2  = $salida_2  ?? 'NULL';

        $insert = $conexion->query("INSERT INTO horarios_especiales
            (usuario_id, fecha, tipo, hora_entrada_1, hora_salida_1, hora_entrada_2, hora_salida_2, observaciones, creado_por)
            VALUES 
            ('$id_trabajador', '$fecha', '$tipo', 
            " . ($entrada_1 == 'NULL' ? 'NULL' : "'$entrada_1'") . ",
            " . ($salida_1  == 'NULL' ? 'NULL' : "'$salida_1'")  . ",
            " . ($entrada_2 == 'NULL' ? 'NULL' : "'$entrada_2'") . ",
            " . ($salida_2  == 'NULL' ? 'NULL' : "'$salida_2'")  . ",
            '$observaciones', '" . $_SESSION['id'] . "')");

        if ($insert) {
            $mensaje_ok = "Día especial guardado correctamente";
        } else {
            $mensaje_error = "Error al guardar el día especial: " . $conexion->error;
        }
    }
}

//-----------------------------------------------------|
//----------- BORRAR DÍA ESPECIAL ------------------- |
//-----------------------------------------------------|

if (isset($_POST['borrar_especial'])) {

    $id_especial = $_POST['id_especial'];

    $borrar = $conexion->query("DELETE FROM horarios_especiales WHERE id = '$id_especial'");

    if ($borrar) {
        $mensaje_ok = "Día especial borrado correctamente";
    } else {
        $mensaje_error = "Error al borrar el día especial";
    }
}

// Recupero el horario semanal actual del trabajador
$horario_actual = [];
$resultado_horario = $conexion->query("SELECT * FROM horarios WHERE usuario_id = '$id_trabajador'");
while ($fila = $resultado_horario->fetch_assoc()) {
    $horario_actual[$fila['dia_semana']] = $fila;
}

// Recupero los días especiales del trabajador ordenados por fecha
$especiales = $conexion->query("SELECT * FROM horarios_especiales 
    WHERE usuario_id = '$id_trabajador' 
    ORDER BY fecha ASC");

desconectar($conexion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Horarios - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Horario de <?php echo $trabajador['nombre'] . " " . $trabajador['apellidos']; ?></h2>

    <?php
    if (isset($mensaje_ok))    echo "<p style='color:green'>" . $mensaje_ok    . "</p>";
    if (isset($mensaje_error)) echo "<p style='color:red'>"   . $mensaje_error . "</p>";
    ?>

    <a href="trabajadores.php">
        <button type="button">← Volver a trabajadores</button>
    </a>

    <h3>Horario semanal</h3>

    <form action="horarios.php?id=<?php echo $id_trabajador; ?>" method="POST">
    <div class="tabla-wrapper">
    <table>
        <tr>
            <th>Día</th>
            <th>Entrada mañana</th>
            <th>Salida mañana</th>
            <th>Entrada tarde</th>
            <th>Salida tarde</th>
        </tr>

        <?php foreach ($dias as $num => $nombre):
            $e1 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_entrada_1'] : "";
            $s1 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_salida_1']  : "";
            $e2 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_entrada_2'] : "";
            $s2 = isset($horario_actual[$num]) ? $horario_actual[$num]['hora_salida_2']  : "";
            echo "<tr>
                <td><b>" . $nombre . "</b></td>
                <td><input type='time' name='entrada_1_" . $num . "' value='" . $e1 . "'></td>
                <td><input type='time' name='salida_1_"  . $num . "' value='" . $s1 . "'></td>
                <td><input type='time' name='entrada_2_" . $num . "' value='" . $e2 . "'></td>
                <td><input type='time' name='salida_2_"  . $num . "' value='" . $s2 . "'></td>
            </tr>";
        endforeach; ?>

    </table>
    </div>
    <input type="submit" name="guardar_horario" value="Guardar horario">
    </form>

    <!-- Botón para mostrar el formulario de día especial -->
    <!-- Se muestra con slideDown y se oculta con slideUp -->
    <button type="button" id="btn-mostrar-especial">+ Añadir día especial</button>

    <!-- Formulario oculto por defecto -->
    <div id="form-especial" style="display:none">
        <h3>Añadir día especial</h3>
        <form action="horarios.php?id=<?php echo $id_trabajador; ?>" method="POST">
        <fieldset>
            <legend>NUEVO DÍA ESPECIAL</legend>

            <label>Fecha *</label>
            <input type="date" name="fecha">

            <label>Tipo *</label>
            <select name="tipo">
                <option value="vacaciones">Vacaciones</option>
                <option value="festivo">Festivo</option>
                <option value="libre">Día libre</option>
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

    <?php

    //-----------------------------------------------------|
    //---------- LISTADO DÍAS ESPECIALES -----------------|
    //-----------------------------------------------------|

    if ($especiales->num_rows > 0) {

        // Botón para mostrar u ocultar la tabla
        echo "<button type='button' id='btn-mostrar-especiales'>Ver días especiales registrados</button>";

        // Tabla oculta por defecto
        echo "<div id='tabla-especiales' style='display:none'>";
        echo "<h3>Días especiales registrados</h3>";
        echo "<div class='tabla-wrapper'><table>
            <tr>
                <th>Fecha</th>
                <th>Tipo</th>
                <th>Entrada mañana</th>
                <th>Salida mañana</th>
                <th>Entrada tarde</th>
                <th>Salida tarde</th>
                <th>Observaciones</th>
                <th>Acciones</th>
            </tr>";

        while ($esp = $especiales->fetch_assoc()) {

            $fecha_formateada = date('d/m/Y', strtotime($esp['fecha']));

            echo "<tr>
                <td>" . $fecha_formateada . "</td>
                <td>" . ucfirst(str_replace('_', ' ', $esp['tipo'])) . "</td>
                <td>" . ($esp['hora_entrada_1'] ?? '-') . "</td>
                <td>" . ($esp['hora_salida_1']  ?? '-') . "</td>
                <td>" . ($esp['hora_entrada_2'] ?? '-') . "</td>
                <td>" . ($esp['hora_salida_2']  ?? '-') . "</td>
                <td>" . $esp['observaciones'] . "</td>
                <td>
                    <form action='horarios.php?id=" . $id_trabajador . "' method='POST' style='display:inline'>
                        <input type='hidden' name='id_especial' value='" . $esp['id'] . "'>
                        <input type='submit' name='borrar_especial' value='Borrar'
                            onclick='return confirm(\"¿Seguro que quieres borrar este día especial?\")'>
                    </form>
                </td>
            </tr>";
        }

        echo "</table></div>";
        echo "</div>";

    } else {
        echo "<p>No hay días especiales registrados para este trabajador</p>";
    }

    ?>

    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">Fuente: API Nager.Date</p>
    <div id="festivos"></div>

</main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="/timetrack/js/main.js"></script>

<?php include "../includes/footer.php"; ?>

</body>
</html>