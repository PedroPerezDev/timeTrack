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
    <link rel="stylesheet" href="/css/style.css">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
</head>
<body>
<header>

    <div class="header-top">

        <!-- Logo y nombre de la aplicación a la izquierda -->
        <div class="header-logo">
            <img src="/img/logo_timetrack.png" alt="Logo TimeTrack">
            <h1>TimeTrack</h1>
        </div>

        <!-- Usuario conectado en el centro -->
        <div class="header-usuario">
            <span class="header-usuario-saludo">Hola,</span>
            <span class="header-usuario-nombre"><?php echo $_SESSION['user']; ?></span>
        </div>

        <!-- Botón modo oscuro: siempre visible, fuera del menú -->
        <!-- &#127769; = 🌙  &#9728; = ☀️                       -->
        <button id="btn-modo" onclick="toggleModo()" aria-label="Cambiar modo">&#127769;</button>

        <!-- Botón hamburguesa visible solo en móvil y tablet -->
        <button id="btn-menu" aria-label="Abrir menú">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>

    <!-- Menú de navegación                                    -->
    <!-- El botón modo oscuro está al final del nav            -->
    <!-- para que en desktop quede a la derecha del todo       -->
    <nav id="nav-menu">
        <a href="/index.php">Inicio</a>
        <?php
        if ($_SESSION['rol'] === 'admin') {
            echo "
                <a href='/admin/trabajadores.php'>Trabajadores</a>
                <a href='/admin/informes.php'>Informes</a>
                <a href='/admin/solicitudes.php'>Solicitudes</a>
            ";
        }
        if ($_SESSION['rol'] === 'trabajador') {
            echo "
                
                <a href='/trabajador/index.php'>Mi jornada</a>
                <a href='/trabajador/perfil.php'>Mi perfil</a>
                <a href='/trabajador/vacaciones.php'>Vacaciones</a>
            ";
        }
        ?>
        <a href="/includes/cerrar_sesion.php">Cerrar sesión</a>

    </nav>

</header>
