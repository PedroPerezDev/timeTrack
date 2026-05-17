<?php
/*
 * Gestión de solicitudes de vacaciones — Panel de administrador
 * Permite aprobar o denegar las solicitudes de los trabajadores
 * Al aprobar, crea automáticamente los días especiales en el horario
 */

include "../includes/funciones.php";
verificarSesion('admin');

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//---------- APROBAR SOLICITUD --------------------- |
//-----------------------------------------------------|

if (isset($_POST['aprobar'])) {

    $id_solicitud    = $_POST['id_solicitud'];
    $respuesta_admin = $_POST['respuesta_admin'];

    // Recupero los datos de la solicitud para crear los días especiales
    $sol = $conexion->query("SELECT * FROM solicitudes
        WHERE id = '$id_solicitud'")->fetch_assoc();

    if ($sol) {

        // Actualizo el estado de la solicitud
        $conexion->query("UPDATE solicitudes SET
            estado           = 'aprobada',
            respuesta_admin  = '$respuesta_admin',
            fecha_respuesta  = NOW()
            WHERE id = '$id_solicitud'");

        // Cuento los días laborables del rango para descontarlos de las vacaciones
        $dias_laborables = 0;
        $f_aux = strtotime($sol['fecha_inicio']);
        $f_fin = strtotime($sol['fecha_fin']);
        while ($f_aux <= $f_fin) {
            if (date('N', $f_aux) <= 5) $dias_laborables++;
            $f_aux = strtotime('+1 day', $f_aux);
        }

        /*
         * Creo un día especial de tipo "libre" para cada día del rango
         * saltando los fines de semana ya que no cuentan como días laborables
         * Esto hace que el trabajador vea el mensaje correspondiente en su panel
         */
        $fecha_actual = strtotime($sol['fecha_inicio']);
        $fecha_fin    = strtotime($sol['fecha_fin']);

        // Mapeo el tipo de solicitud al tipo de día especial
        // Vacaciones y médico conservan su tipo, el resto se marca como libre
        // El tipo de solicitud coincide directamente con el tipo de día especial
        $tipo_especial = $sol['tipo'];

        while ($fecha_actual <= $fecha_fin) {

            $dia_semana = date('N', $fecha_actual);

            // Solo creo días especiales en días laborables
            if ($dia_semana <= 5) {

                $fecha_str   = date('Y-m-d', $fecha_actual);
                $observacion = ucfirst(str_replace('_', ' ', $sol['tipo'])) . " aprobada";

                // Compruebo que no exista ya un día especial para esa fecha
                $existe = $conexion->query("SELECT id FROM horarios_especiales
                    WHERE usuario_id = '" . $sol['usuario_id'] . "'
                    AND fecha = '$fecha_str'")->fetch_assoc();

                if (!$existe) {
                    $conexion->query("INSERT INTO horarios_especiales
                        (usuario_id, fecha, tipo, observaciones, creado_por)
                        VALUES
                        ('" . $sol['usuario_id'] . "', '$fecha_str',
                        '$tipo_especial', '$observacion', '" . $_SESSION['id'] . "')");
                }
            }

            $fecha_actual = strtotime('+1 day', $fecha_actual);
        }

        // Actualizo los días gastados del trabajador
        // Las citas médicas no descuentan días de vacaciones
        if ($sol['tipo'] !== 'medico') {
            $conexion->query("UPDATE usuarios SET
                dias_vacaciones_gastados = dias_vacaciones_gastados + $dias_laborables
                WHERE id = '" . $sol['usuario_id'] . "'");
            $mensaje_ok = "Solicitud aprobada. Se han descontado $dias_laborables día/s de vacaciones.";
        } else {
            $mensaje_ok = "Solicitud aprobada. Al ser cita médica no se descuentan días de vacaciones.";
        }

    } else {
        $mensaje_error = "No se encontró la solicitud";
    }
}

//-----------------------------------------------------|
//---------- DENEGAR SOLICITUD --------------------- |
//-----------------------------------------------------|

if (isset($_POST['denegar'])) {

    $id_solicitud    = $_POST['id_solicitud'];
    $respuesta_admin = $_POST['respuesta_admin'];

    $conexion->query("UPDATE solicitudes SET
        estado          = 'denegada',
        respuesta_admin = '$respuesta_admin',
        fecha_respuesta = NOW()
        WHERE id = '$id_solicitud'");

    $mensaje_ok = "Solicitud denegada";
}

// Recupero todas las solicitudes agrupadas por estado
// Primero las pendientes, luego el resto por fecha
$solicitudes = $conexion->query("SELECT s.*, u.nombre, u.apellidos, u.foto
    FROM solicitudes s
    INNER JOIN usuarios u ON s.usuario_id = u.id
    ORDER BY
        CASE s.estado WHEN 'pendiente' THEN 0 ELSE 1 END ASC,
        s.fecha_solicitud DESC");

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
    <title>Solicitudes - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Solicitudes de vacaciones</h2>

    <?php
    if (isset($mensaje_ok))    mostrarMensaje($mensaje_ok);
    if (isset($mensaje_error)) mostrarMensaje($mensaje_error, 'error');
    ?>

    <?php if ($solicitudes->num_rows === 0): ?>
        <p style="color:var(--color-texto-apagado)">No hay solicitudes registradas.</p>

    <?php else: ?>

    <div class="solicitudes-lista">
        <?php while ($sol = $solicitudes->fetch_assoc()): ?>
        <div class="solicitud-item <?php echo $sol['estado'] === 'pendiente' ? 'solicitud-pendiente' : ''; ?>">

            <div class="solicitud-cabecera">
                <div class="solicitud-info">

                    <!-- Foto y nombre del trabajador -->
                    <div class="solicitud-trabajador">
                        <?php echo mostrarFoto($sol['foto'], 32, 'border-radius:50%;vertical-align:middle;margin-right:8px'); ?>
                        <span class="solicitud-nombre">
                            <?php echo $sol['apellidos'] . ", " . $sol['nombre']; ?>
                        </span>
                    </div>

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

                    <?php if (!empty($sol['motivo'])): ?>
                        <span class="solicitud-motivo"><?php echo $sol['motivo']; ?></span>
                    <?php endif; ?>

                </div>

                <span class="estado-badge <?php echo $estados_css[$sol['estado']]; ?>">
                    <?php echo $estados_label[$sol['estado']]; ?>
                </span>
            </div>

            <?php if (!empty($sol['respuesta_admin'])): ?>
                <p class="solicitud-respuesta">
                    <b>Respuesta:</b> <?php echo $sol['respuesta_admin']; ?>
                </p>
            <?php endif; ?>

            <span class="solicitud-fecha-envio">
                Enviada el <?php echo formatearFecha(substr($sol['fecha_solicitud'], 0, 10)); ?>
            </span>

            <!-- Formulario de respuesta solo si está pendiente -->
            <?php if ($sol['estado'] === 'pendiente'): ?>

                <div class="solicitud-acciones">
                    <form action="solicitudes.php" method="POST">
                        <input type="hidden" name="id_solicitud" value="<?php echo $sol['id']; ?>">

                        <label>Comentario (opcional)</label>
                        <textarea name="respuesta_admin" rows="2"
                            placeholder="Añade un comentario para el trabajador..."></textarea>

                        <div class="solicitud-botones">
                            <input type="submit" name="aprobar" value="Aprobar"
                                class="btn-aprobar">
                            <input type="submit" name="denegar" value="Denegar"
                                class="btn-denegar"
                                onclick="return confirm('¿Seguro que quieres denegar esta solicitud?')">
                        </div>
                    </form>
                </div>

            <?php endif; ?>

        </div>
        <?php endwhile; ?>
    </div>

    <?php endif; ?>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>
