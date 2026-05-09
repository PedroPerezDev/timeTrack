<?php
/*
 * Panel principal del administrador
 * Muestra un resumen de la jornada actual
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

$fecha_hoy = date('Y-m-d');

//-----------------------------------------------------|
//---------- DATOS PARA EL DASHBOARD -----------------|
//-----------------------------------------------------|

// Total de trabajadores activos
$total_trabajadores = $conexion->query("SELECT COUNT(*) as total FROM usuarios 
    WHERE rol = 'trabajador' AND activo = 1")->fetch_assoc()['total'];

// Trabajadores que han fichado hoy (al menos entrada_1)
$han_fichado = $conexion->query("SELECT COUNT(DISTINCT usuario_id) as total 
    FROM fichajes WHERE fecha = '$fecha_hoy'")->fetch_assoc()['total'];

// Trabajadores que NO han fichado hoy
$no_han_fichado = $total_trabajadores - $han_fichado;

// Incidencias generadas hoy
$incidencias_hoy = $conexion->query("SELECT COUNT(*) as total FROM incidencias 
    WHERE fecha = '$fecha_hoy'")->fetch_assoc()['total'];

// Solicitudes pendientes de aprobar
$solicitudes_pendientes = $conexion->query("SELECT COUNT(*) as total FROM solicitudes 
    WHERE estado = 'pendiente'")->fetch_assoc()['total'];

//-----------------------------------------------------|
//---------- FICHAJES DE HOY -------------------------|
//-----------------------------------------------------|

/*
 * Recupero todos los trabajadores y su estado de fichaje hoy
 * Para cada trabajador miro qué fichajes ha hecho hoy
 */
$trabajadores = $conexion->query("SELECT id, nombre, apellidos, foto 
    FROM usuarios WHERE rol = 'trabajador' AND activo = 1 ORDER BY apellidos ASC");

$fichajes_hoy = [];
$resultado_fichajes = $conexion->query("SELECT * FROM fichajes WHERE fecha = '$fecha_hoy'");
while ($f = $resultado_fichajes->fetch_assoc()) {
    $fichajes_hoy[$f['usuario_id']][$f['tipo']] = $f['hora_fichaje'];
}

desconectar($conexion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Admin - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Panel de administración</h2>


<!-- Contenedor donde jQuery mostrará si se han importado festivos nuevos -->
<div id="importar-festivos"></div>

    <p style="font-size:12px; color:var(--color-texto-apagado)">
        <?php echo date('d/m/Y'); ?>
    </p>

    <?php if ($solicitudes_pendientes > 0): ?>
    <!-- Aviso de solicitudes pendientes -->
    <div class="dashboard-aviso">
        Tienes <b><?php echo $solicitudes_pendientes; ?></b> solicitud/es de vacaciones pendientes de aprobar
    </div>
    <?php endif; ?>

    
    <div class="dashboard-grid">

        <div class="dashboard-card">
            <span class="dashboard-card-numero"><?php echo $total_trabajadores; ?></span>
            <span class="dashboard-card-label">Trabajadores activos</span>
        </div>

        <div class="dashboard-card dashboard-card-ok">
            <span class="dashboard-card-numero"><?php echo $han_fichado; ?></span>
            <span class="dashboard-card-label">Han fichado hoy</span>
        </div>

        <div class="dashboard-card dashboard-card-error">
            <span class="dashboard-card-numero"><?php echo $no_han_fichado; ?></span>
            <span class="dashboard-card-label">Sin fichar hoy</span>
        </div>

        <div class="dashboard-card dashboard-card-warning">
            <span class="dashboard-card-numero"><?php echo $incidencias_hoy; ?></span>
            <span class="dashboard-card-label">Incidencias hoy</span>
        </div>

    </div>

    <?php

    //-----------------------------------------------------|
    //---------- TABLA FICHAJES DE HOY ------------------|
    //-----------------------------------------------------|

    ?>

    <h3>Estado de fichajes de hoy</h3>

    <div class="tabla-wrapper">
    <table>
        <tr>
            <th>Trabajador</th>
            <th>Entrada mañana</th>
            <th>Salida mañana</th>
            <th>Entrada tarde</th>
            <th>Salida tarde</th>
            <th>Estado</th>
        </tr>

        <?php while ($t = $trabajadores->fetch_assoc()):

            $id  = $t['id'];
            $f   = isset($fichajes_hoy[$id]) ? $fichajes_hoy[$id] : [];

            // Determino el estado general del trabajador hoy
            if (empty($f)) {
                $estado     = "Sin fichar";
                $estado_css = "estado-sin-fichar";
            } elseif (isset($f['salida_2'])) {
                $estado     = "Jornada completa";
                $estado_css = "estado-completo";
            } else {
                $estado     = "En curso";
                $estado_css = "estado-en-curso";
            }

            // Foto del trabajador
            $foto_html = !empty($t['foto'])
                ? "<img src='/timetrack/uploads/fotos_trabajadores/" . $t['foto'] . "' width='30' style='border-radius:50%;vertical-align:middle;margin-right:6px'>"
                : "";
        ?>
        <tr>
            <td><?php echo $foto_html . $t['apellidos'] . ", " . $t['nombre']; ?></td>
            <td><?php echo isset($f['entrada_1']) ? substr($f['entrada_1'], 0, 5) : '-'; ?></td>
            <td><?php echo isset($f['salida_1'])  ? substr($f['salida_1'],  0, 5) : '-'; ?></td>
            <td><?php echo isset($f['entrada_2']) ? substr($f['entrada_2'], 0, 5) : '-'; ?></td>
            <td><?php echo isset($f['salida_2'])  ? substr($f['salida_2'],  0, 5) : '-'; ?></td>
            <td><span class="estado-badge <?php echo $estado_css; ?>"><?php echo $estado; ?></span></td>
        </tr>
        <?php endwhile; ?>

    </table>
    </div>

    <!-- Festivos nacionales -->
    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">Fuente: API Nager.Date</p>
    <div id="festivos"></div>

</main>




<?php include "../includes/footer.php"; ?>

</body>
</html>