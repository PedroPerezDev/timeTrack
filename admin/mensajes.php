<?php
/*
 * Mensajes del administrador
 * Permite ver todos los mensajes de los trabajadores y responderlos
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//------------- RESPONDER MENSAJE -------------------- |
//-----------------------------------------------------|

if (isset($_POST['responder'])) {

    $destinatario_id = $_POST['destinatario_id'];
    $asunto          = "Re: " . $_POST['asunto_original'];
    $mensaje         = $_POST['respuesta'];

    if (empty($mensaje)) {
        $mensaje_error = "La respuesta no puede estar vacía";
    } else {

        $insertar = $conexion->query("INSERT INTO mensajes
            (remitente_id, destinatario_id, asunto, mensaje, leido, fecha)
            VALUES
            ('" . $_SESSION['id'] . "', '$destinatario_id', '$asunto', '$mensaje', 0, NOW())");

        // Marco el mensaje original como leído
        $id_mensaje = $_POST['id_mensaje'];
        $conexion->query("UPDATE mensajes SET leido = 1 WHERE id = '$id_mensaje'");

        if ($insertar) {
            $mensaje_ok = "Respuesta enviada correctamente";
        } else {
            $mensaje_error = "Error al enviar la respuesta";
        }
    }
}

//-----------------------------------------------------|
//------------- MARCAR COMO LEÍDO -------------------- |
//-----------------------------------------------------|

if (isset($_POST['marcar_leido'])) {
    $id_mensaje = $_POST['id_mensaje'];
    $conexion->query("UPDATE mensajes SET leido = 1 WHERE id = '$id_mensaje'");
}

//-----------------------------------------------------|
//------------- RECUPERO MENSAJES -------------------- |
//-----------------------------------------------------|

/*
 * Recupero todos los mensajes donde el admin es destinatario
 * Los ordeno por leído (primero los no leídos) y luego por fecha
 */
$mensajes = $conexion->query("SELECT m.*,
    u.nombre   AS nombre_remitente,
    u.apellidos AS apellidos_remitente,
    u.foto     AS foto_remitente
    FROM mensajes m
    JOIN usuarios u ON m.remitente_id = u.id
    WHERE m.destinatario_id = '" . $_SESSION['id'] . "'
    ORDER BY m.leido ASC, m.fecha DESC");

desconectar($conexion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensajes - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Mensajes de trabajadores</h2>

    <?php
    if (isset($mensaje_ok))    echo "<p style='color:green'>" . $mensaje_ok    . "</p>";
    if (isset($mensaje_error)) echo "<p style='color:red'>"   . $mensaje_error . "</p>";
    ?>

    <?php if ($mensajes->num_rows > 0): ?>

        <?php while ($m = $mensajes->fetch_assoc()): ?>

        <div class="mensaje-item <?php echo $m['leido'] == 0 ? 'mensaje-no-leido' : ''; ?>">

            <div class="mensaje-cabecera">
                <div style="display:flex; align-items:center; gap:10px">

                    <!-- Foto del trabajador -->
                    <?php if (!empty($m['foto_remitente'])): ?>
                        <img src="/uploads/fotos_trabajadores/<?php echo $m['foto_remitente']; ?>"
                             width="35" style="border-radius:50%">
                    <?php endif; ?>

                    <div>
                        <span class="mensaje-etiqueta">
                            <?php echo $m['nombre_remitente'] . " " . $m['apellidos_remitente']; ?>
                        </span>
                        <?php if ($m['leido'] == 0): ?>
                            <span class="mensaje-badge-nuevo">Nuevo</span>
                        <?php endif; ?>
                    </div>
                </div>
                <span class="mensaje-fecha"><?php echo date('d/m/Y H:i', strtotime($m['fecha'])); ?></span>
            </div>

            <div class="mensaje-asunto"><?php echo $m['asunto']; ?></div>
            <div class="mensaje-texto"><?php echo nl2br($m['mensaje']); ?></div>

            <!-- Botón para mostrar el formulario de respuesta -->
            <button type="button"
                class="btn-responder"
                data-id="<?php echo $m['id']; ?>"
                data-destinatario="<?php echo $m['remitente_id']; ?>"
                data-asunto="<?php echo htmlspecialchars($m['asunto']); ?>">
                Responder
            </button>

            <!-- Formulario de respuesta oculto -->
            <div class="form-respuesta" id="respuesta-<?php echo $m['id']; ?>" style="display:none">
                <form action="mensajes.php" method="POST">
                    <input type="hidden" name="id_mensaje"       value="<?php echo $m['id']; ?>">
                    <input type="hidden" name="destinatario_id"  value="<?php echo $m['remitente_id']; ?>">
                    <input type="hidden" name="asunto_original"  value="<?php echo htmlspecialchars($m['asunto']); ?>">

                    <textarea name="respuesta" rows="4"
                        placeholder="Escribe tu respuesta..."></textarea>
                    <input type="submit" name="responder" value="Enviar respuesta">
                </form>
            </div>

        </div>

        <?php endwhile; ?>

    <?php else: ?>
        <p>No hay mensajes todavía</p>
    <?php endif; ?>

</main>


<?php include "../includes/footer.php"; ?>

</body>
</html>