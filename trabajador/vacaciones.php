<?php
/*
 * Vacaciones del trabajador
 * Muestra los días disponibles y los festivos nacionales
 */

include "../includes/funciones.php";
verificarSesion('trabajador');

include "../config.php";

$conexion   = conectar();
$trabajador = $conexion->query("SELECT dias_vacaciones_totales, dias_vacaciones_gastados 
    FROM usuarios WHERE id = '" . $_SESSION['id'] . "'")->fetch_assoc();
desconectar($conexion);

$dias_disponibles = diasVacacionesDisponibles(
    $trabajador['dias_vacaciones_totales'],
    $trabajador['dias_vacaciones_gastados']
);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vacaciones - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Mis vacaciones</h2>

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

    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">Fuente: API Nager.Date</p>
    <div id="festivos"></div>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>
