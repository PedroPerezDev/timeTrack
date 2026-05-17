<?php
/*
 * Vacaciones del trabajador
 * Permite solicitar días libres seleccionando fechas en un calendario
 * y ver el estado de las solicitudes anteriores
 */

include "../includes/funciones.php";
verificarSesion('trabajador');

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//---------- ENVIAR SOLICITUD ----------------------- |
//-----------------------------------------------------|

if (isset($_POST['solicitar'])) {

    $fecha_inicio = $_POST['fecha_inicio'];
    $fecha_fin    = $_POST['fecha_fin'];
    $tipo         = $_POST['tipo'];
    $motivo       = $_POST['motivo'];

    if (empty($fecha_inicio) || empty($fecha_fin)) {
        $mensaje_error = "Debes seleccionar las fechas";

    } elseif ($fecha_fin < $fecha_inicio) {
        $mensaje_error = "La fecha de fin no puede ser anterior a la de inicio";

    } else {

        $insertar = $conexion->query("INSERT INTO solicitudes
            (usuario_id, fecha_inicio, fecha_fin, tipo, motivo)
            VALUES ('" . $_SESSION['id'] . "', '$fecha_inicio', '$fecha_fin', '$tipo', '$motivo')");

        if ($insertar) {
            $mensaje_ok = "Solicitud enviada correctamente. El administrador la revisará pronto.";
        } else {
            $mensaje_error = "Error al enviar la solicitud: " . $conexion->error;
        }
    }
}

// Datos del trabajador
$trabajador = $conexion->query("SELECT dias_vacaciones_totales, dias_vacaciones_gastados
    FROM usuarios WHERE id = '" . $_SESSION['id'] . "'")->fetch_assoc();

$dias_disponibles = diasVacacionesDisponibles(
    $trabajador['dias_vacaciones_totales'],
    $trabajador['dias_vacaciones_gastados']
);

// Solicitudes del trabajador ordenadas por fecha
$solicitudes = $conexion->query("SELECT * FROM solicitudes
    WHERE usuario_id = '" . $_SESSION['id'] . "'
    ORDER BY fecha_solicitud DESC");

desconectar($conexion);

$tipos_label = [
    'vacaciones'      => 'Vacaciones',
    'libre'           => 'Día libre',
    'medico'          => 'Médico',
    'asuntos_propios' => 'Asuntos propios',
];

$estados_label = [
    'pendiente' => 'Pendiente',
    'aprobada'  => 'Aprobada',
    'denegada'  => 'Denegada',
];

$estados_css = [
    'pendiente' => 'estado-en-curso',
    'aprobada'  => 'estado-completo',
    'denegada'  => 'estado-sin-fichar',
];

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacaciones - TimeTrack</title>

    <!-- Flatpickr — librería de calendario para selección de fechas -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">

    <style>
        /* Integración del calendario Flatpickr con el diseño de TimeTrack */
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

        .flatpickr-day:hover {
            background: var(--color-borde);
        }

        .flatpickr-months .flatpickr-month {
            background: var(--color-principal);
            color:      #fff;
        }

        .flatpickr-current-month .flatpickr-monthDropdown-months,
        .flatpickr-current-month input.cur-year {
            color: #fff;
        }

        .flatpickr-weekday {
            color: var(--color-texto-apagado);
        }
    </style>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Mis vacaciones</h2>

    <?php
    if (isset($mensaje_ok))    mostrarMensaje($mensaje_ok);
    if (isset($mensaje_error)) mostrarMensaje($mensaje_error, 'error');
    ?>

    <!-- Resumen de días disponibles -->
    <div class="perfil-datos">
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

    <!-- Formulario de solicitud con calendario Flatpickr -->
    <h3>Solicitar días</h3>

    <form action="vacaciones.php" method="POST">
        <fieldset>
            <legend>NUEVA SOLICITUD</legend>

            <label>Tipo de solicitud</label>
            <select name="tipo">
                <option value="vacaciones">Vacaciones</option>
                <option value="libre">Día libre</option>
                <option value="medico">Médico</option>
                <option value="asuntos_propios">Asuntos propios</option>
            </select>

            <label>Fecha de inicio</label>
            <!-- Flatpickr actúa sobre este campo al cargar la página -->
            <input type="text" name="fecha_inicio" id="fecha-inicio"
                placeholder="Selecciona una fecha" readonly>

            <label>Fecha de fin</label>
            <input type="text" name="fecha_fin" id="fecha-fin"
                placeholder="Selecciona una fecha" readonly>

            <label>Motivo (opcional)</label>
            <textarea name="motivo" rows="3"
                placeholder="Añade una nota si lo necesitas"></textarea>

            <input type="submit" name="solicitar" value="Enviar solicitud">
        </fieldset>
    </form>

    <!-- Historial de solicitudes del trabajador -->
    <?php if ($solicitudes->num_rows > 0): ?>

        <h3>Mis solicitudes</h3>

        <div class="solicitudes-lista">
            <?php while ($sol = $solicitudes->fetch_assoc()): ?>
            <div class="solicitud-item">

                <div class="solicitud-cabecera">
                    <div class="solicitud-info">
                        <span class="solicitud-tipo">
                            <?php echo $tipos_label[$sol['tipo']]; ?>
                        </span>
                        <span class="solicitud-fechas">
                            <?php
                            echo formatearFecha($sol['fecha_inicio']);
                            if ($sol['fecha_inicio'] !== $sol['fecha_fin']) {
                                echo " → " . formatearFecha($sol['fecha_fin']);
                            }
                            ?>
                        </span>
                    </div>
                    <span class="estado-badge <?php echo $estados_css[$sol['estado']]; ?>">
                        <?php echo $estados_label[$sol['estado']]; ?>
                    </span>
                </div>

                <?php if (!empty($sol['motivo'])): ?>
                    <p class="solicitud-motivo"><?php echo $sol['motivo']; ?></p>
                <?php endif; ?>

                <?php if (!empty($sol['respuesta_admin'])): ?>
                    <p class="solicitud-respuesta">
                        <b>Respuesta:</b> <?php echo $sol['respuesta_admin']; ?>
                    </p>
                <?php endif; ?>

                <span class="solicitud-fecha-envio">
                    Enviada el <?php echo formatearFecha(substr($sol['fecha_solicitud'], 0, 10)); ?>
                </span>

            </div>
            <?php endwhile; ?>
        </div>

    <?php else: ?>
        <p style="color:var(--color-texto-apagado); font-size:13px">
            No tienes solicitudes registradas todavía.
        </p>
    <?php endif; ?>

    <!-- Festivos nacionales -->
    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">Fuente: API Nager.Date</p>
    <div id="festivos"></div>

</main>

<?php include "../includes/footer.php"; ?>

<!-- Flatpickr JS con localización en español -->
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
<script>
/*
 * Inicializo Flatpickr en los dos campos de fecha
 * El calendario está en español y no permite fechas pasadas
 * Al seleccionar la fecha de inicio, la de fin se actualiza
 * para que no pueda ser anterior
 */
var pickerInicio = flatpickr("#fecha-inicio", {
    locale:     "es",
    dateFormat: "Y-m-d",
    minDate:    "today",
    onChange: function(selectedDates) {
        pickerFin.set("minDate", selectedDates[0]);
        if (pickerFin.selectedDates[0] < selectedDates[0]) {
            pickerFin.clear();
        }
    }
});

var pickerFin = flatpickr("#fecha-fin", {
    locale:     "es",
    dateFormat: "Y-m-d",
    minDate:    "today"
});
</script>

</body>
</html>
