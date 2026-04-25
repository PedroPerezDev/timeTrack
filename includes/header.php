<?php
/*
 * Encabezado de la aplicación TimeTrack
 * Muestra el logo, el nombre del usuario conectado
 * y el menú según el rol (admin o trabajador)
 * Se incluye en todas las páginas después de iniciar sesión
 */

// Si no hay sesión activa redirige al login
if (!isset($_SESSION['user'])) {
    header("Location: ../index.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack</title>
    <link rel="stylesheet" href="/timetrack/css/style.css">
</head>
<body>

<header>
    <!-- Logo y título de la aplicación -->
    <img src="https://placehold.co/80x80" alt="Logo TimeTrack">
    <h1>TimeTrack (<?php echo $_SESSION['user']; ?>)</h1>

    <!-- Menú de navegación -->
    <nav>
        <!-- Enlace de inicio común para todos los usuarios -->
        <a href="/timetrack/index.php">Inicio</a>

        <?php
        // MENÚ DEL ADMINISTRADOR
        // Solo visible si el rol de la sesión es 'admin'
        if ($_SESSION['rol'] === 'admin') {
            echo "
                <a href='/timetrack/admin/trabajadores.php'>Trabajadores</a>
                <a href='/timetrack/admin/horarios.php'>Horarios</a>
                <a href='/timetrack/admin/informes.php'>Informes</a>
                <a href='/timetrack/admin/mensajes.php'>Mensajes</a>
            ";
        }

        // MENÚ DEL TRABAJADOR
        // Solo visible si el rol de la sesión es 'trabajador'
        if ($_SESSION['rol'] === 'trabajador') {
            echo "
                <a href='/timetrack/trabajador/index.php'>Mi jornada</a>
                <a href='/timetrack/trabajador/perfil.php'>Mi perfil</a>
                <a href='/timetrack/trabajador/vacaciones.php'>Vacaciones</a>
                <a href='/timetrack/trabajador/mensajes.php'>Mensajes</a>
            ";
        }
        ?>

        <!-- Cerrar sesión visible para todos -->
        <a href="/timetrack/includes/cerrar_sesion.php">Cerrar sesión</a>
    </nav>
</header>