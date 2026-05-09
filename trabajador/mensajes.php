<?php
/*
 * Mensajes del trabajador
 * Permite enviar mensajes al administrador y ver las respuestas
 * Solo accesible para usuarios con rol 'trabajador'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "trabajador") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//------------- ENVIAR MENSAJE ----------------------- |
//-----------------------------------------------------|

if (isset($_POST['enviar'])) {

    $asunto  = $_POST['asunto'];
    $mensaje = $_POST['mensaje'];

    if (empty($asunto) || empty($mensaje)) {
        $mensaje_error = "El asunto y el mensaje son obligatorios";
    } else {

        // Busco el id del admin para mandárselo
        $admin = $conexion->query("SELECT id FROM usuarios WHERE rol = 'admin' LIMIT 1")->fetch_assoc();

        $insertar = $conexion->query("INSERT INTO mensajes
            (remitente_id, destinatario_id, asunto, mensaje, leido, fecha)
            VALUES
            ('" . $_SESSION['id'] . "', '" . $admin['id'] . "', '$asunto', '$mensaje', 0, NOW())");

        if ($insertar) {
            $mensaje_ok = "Mensaje enviado correctamente";
        } else {
            $mensaje_error = "Error al enviar el mensaje: " . $conexion->error;
        }
    }
}

//-----------------------------------------------------|
//------------- RECUPERO MIS MENSAJES ---------------- |
//-----------------------------------------------------|

/*
 * Recupero todos los mensajes del trabajador
 * tanto los enviados por él como las respuestas del admin
 * Los ordeno por fecha descendente para ver los más recientes primero
 */
$mensajes = $conexion->query("SELECT m.*, 
    u_remitente.nombre  AS nombre_remitente,
    u_remitente.apellidos AS apellidos_remitente
    FROM mensajes m
    JOIN usuarios u_remitente ON m.remitente_id = u_remitente.id
    WHERE m.remitente_id = '" . $_SESSION['id'] . "'
    OR m.destinatario_id = '" . $_SESSION['id'] . "'
    ORDER BY m.fecha DESC");

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
    <h2>Mensajes</h2>

    <?php
    if (isset($mensaje_ok))    echo "<p style='color:green'>" . $mensaje_ok    . "</p>";
    if (isset($mensaje_error)) echo "<p style='color:red'>"   . $mensaje_error . "</p>";
    ?>

    <!-- Botón para mostrar el formulario de nuevo mensaje -->
    <button type="button" id="btn-nuevo-mensaje">+ Nuevo mensaje</button>

    <!-- Formulario oculto por defecto -->
    <div id="form-mensaje" style="display:none">
        <h3>Nuevo mensaje</h3>
        <form action="mensajes.php" method="POST">
        <fieldset>
            <legend>ENVIAR MENSAJE AL ADMINISTRADOR</legend>

            <label>Asunto *</label>
            <input type="text" name="asunto" placeholder="Escribe el asunto">

            <label>Mensaje *</label>
            <textarea name="mensaje" rows="5" placeholder="Escribe tu mensaje aquí..."></textarea>

            <input type="submit" name="enviar" value="Enviar mensaje">
        </fieldset>
        </form>
    </div>

    <?php

    //-----------------------------------------------------|
    //------------- LISTADO DE MENSAJES ------------------ |
    //-----------------------------------------------------|

    if ($mensajes->num_rows > 0) {

        echo "<h3>Mis mensajes</h3>";

        while ($m = $mensajes->fetch_assoc()) {

            // Determino si es un mensaje enviado o recibido
            $es_enviado = $m['remitente_id'] == $_SESSION['id'];
            $clase      = $es_enviado ? 'mensaje-enviado' : 'mensaje-recibido';
            $etiqueta   = $es_enviado ? 'Enviado' : 'Respuesta del administrador';

            echo "<div class='mensaje-item " . $clase . "'>";
            echo "  <div class='mensaje-cabecera'>";
            echo "      <span class='mensaje-etiqueta'>" . $etiqueta . "</span>";
            echo "      <span class='mensaje-fecha'>" . date('d/m/Y H:i', strtotime($m['fecha'])) . "</span>";
            echo "  </div>";
            echo "  <div class='mensaje-asunto'>" . $m['asunto'] . "</div>";
            echo "  <div class='mensaje-texto'>" . nl2br($m['mensaje']) . "</div>";
            echo "</div>";
        }

    } else {
        echo "<p>No tienes mensajes todavía</p>";
    }

    ?>

</main>




<?php include "../includes/footer.php"; ?>

</body>
</html>