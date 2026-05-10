<?php
/*
 * Encabezado de la aplicación TimeTrack
 * Muestra el logo, el nombre del usuario conectado
 * y el menú según el rol (admin o trabajador)
 * Se incluye en todas las páginas después de iniciar sesión
 */
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
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
<header>

    <div class="header-top">

        <!-- Logo y nombre de la aplicación -->
        <div class="header-logo">
            <img src="/timetrack/img/logo_timetrack.png" alt="Logo TimeTrack">
            <h1>TimeTrack</h1>
        </div>

        <div class="header-derecha">

            <!-- Usuario conectado -->
            <div class="header-usuario">
                <span class="header-usuario-saludo">Hola,</span>
                <span class="header-usuario-nombre"><?php echo $_SESSION['user']; ?></span>
            </div>

            <!-- Botón modo oscuro -->
            <button id="btn-modo" onclick="toggleModo()">Modo oscuro</button>

            <!-- Botón hamburguesa visible solo en móvil y tablet -->
            <button id="btn-menu" aria-label="Abrir menú">
                <span></span>
                <span></span>
                <span></span>
            </button>

        </div>
    </div>

    <!-- Menú de navegación -->
    <nav id="nav-menu">
        <a href="/timetrack/index.php">Inicio</a>
        <?php
        if ($_SESSION['rol'] === 'admin') {
            echo "
                <a href='/timetrack/admin/trabajadores.php'>Trabajadores</a>
                <a href='/timetrack/admin/informes.php'>Informes</a>
                <a href='/timetrack/admin/mensajes.php'>Mensajes</a>
            ";
        }
        if ($_SESSION['rol'] === 'trabajador') {
            echo "
                <a href='/timetrack/trabajador/index.php'>Mi jornada</a>
                <a href='/timetrack/trabajador/perfil.php'>Mi perfil</a>
                <a href='/timetrack/trabajador/vacaciones.php'>Vacaciones</a>
                <a href='/timetrack/trabajador/mensajes.php'>Mensajes</a>
            ";
        }
        ?>
        <a href="/timetrack/includes/cerrar_sesion.php">Cerrar sesión</a>
    </nav>

</header>