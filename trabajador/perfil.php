<?php
/*
 * Perfil del trabajador
 * Muestra los datos personales del trabajador conectado
 * Solo lectura, no se puede editar nada
 */

session_start();

// Si no hay sesión activa o no es trabajador redirige al login
if (!isset($_SESSION['user']) || $_SESSION['rol'] != "trabajador") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

// Recupero los datos del trabajador conectado usando el id de la sesión
$resultado = $conexion->query("SELECT * FROM usuarios WHERE id = '" . $_SESSION['id'] . "'");
$trabajador = $resultado->fetch_assoc();

desconectar($conexion);

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi perfil - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Mi perfil</h2>

    <!-- Foto del trabajador -->
    <div class="perfil-foto">
        <?php if (!empty($trabajador['foto'])): ?>
            <img src="/timetrack/uploads/fotos_trabajadores/<?php echo $trabajador['foto']; ?>"
                 alt="Foto de perfil">
        <?php else: ?>
            <div class="perfil-sin-foto">Sin foto</div>
        <?php endif; ?>
    </div>

    <!-- Datos personales -->
    <div class="perfil-datos">

        <h3>Datos personales</h3>

        <div class="perfil-campo">
            <span class="perfil-label">Nombre completo</span>
            <span class="perfil-valor"><?php echo $trabajador['nombre'] . " " . $trabajador['apellidos']; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Email</span>
            <span class="perfil-valor"><?php echo $trabajador['email']; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">DNI</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['dni']) ? $trabajador['dni'] : "No especificado"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Teléfono</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['telefono']) ? $trabajador['telefono'] : "No especificado"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Dirección</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['direccion']) ? $trabajador['direccion'] : "No especificada"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Fecha de nacimiento</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['fecha_nacimiento']) ? date('d/m/Y', strtotime($trabajador['fecha_nacimiento'])) : "No especificada"; ?></span>
        </div>

        <h3>Datos laborales</h3>

        <div class="perfil-campo">
            <span class="perfil-label">Departamento</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['departamento']) ? $trabajador['departamento'] : "No especificado"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Puesto</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['puesto']) ? $trabajador['puesto'] : "No especificado"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Fecha de incorporación</span>
            <span class="perfil-valor"><?php echo !empty($trabajador['fecha_incorporacion']) ? date('d/m/Y', strtotime($trabajador['fecha_incorporacion'])) : "No especificada"; ?></span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Días de vacaciones disponibles</span>
            <span class="perfil-valor">
                <?php echo ($trabajador['dias_vacaciones_totales'] - $trabajador['dias_vacaciones_gastados']); ?> días
            </span>
        </div>

        <div class="perfil-campo">
            <span class="perfil-label">Días de vacaciones totales</span>
            <span class="perfil-valor"><?php echo $trabajador['dias_vacaciones_totales']; ?> días</span>
        </div>

    </div>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>