<?php
/*
 * Página principal de TimeTrack
 * Gestiona el login de usuarios
 * Redirige al panel de admin o trabajador según el rol
 */

include "./config.php"; // incluimos la configuración y función de conexión

session_start(); // iniciamos la sesión

//-----------------------------------------------------|
//---------------------- LOGIN ------------------------|
//-----------------------------------------------------|

// COMPROBAMOS EL POST ANTES QUE LA SESIÓN
// para que el formulario funcione a la primera

if (isset($_POST['login'])) { // si se pulsa el botón de login

    $conexion = conectar(); // conectamos a la base de datos

    if ($conexion) {

        // Buscamos el usuario por nombre en la base de datos
        $consulta = "SELECT * FROM usuarios WHERE nombre = '" . $_POST['user'] . "'";
        $resultado = $conexion->query($consulta);

        if ($resultado->num_rows == 1) { // si existe el usuario

            $fila = $resultado->fetch_assoc();

            if ($_POST['password'] === $fila['password']) { // comprobamos la contraseña

                // Guardamos en sesión los datos del usuario
                $_SESSION['user']   = $fila['nombre'];
                $_SESSION['rol']    = $fila['rol'];
                $_SESSION['id']     = $fila['id'];

                // Redirigimos según el rol
                if ($fila['rol'] === 'admin') {
                    header("Location: admin/index.php");
                } else {
                    header("Location: trabajador/index.php");
                }
                exit;

            } else {
                $error = "Contraseña incorrecta"; // guardamos el error
            }

        } else {
            $error = "Usuario incorrecto"; // guardamos el error
        }

        desconectar($conexion); // cerramos la conexión
    }
}

//-----------------------------------------------------|
//------- SI YA HAY SESIÓN ACTIVA REDIRIGIMOS ---------|
//-----------------------------------------------------|

// Si el usuario ya estaba logueado lo mandamos directamente a su panel
if (isset($_SESSION['user'])) {
    if ($_SESSION['rol'] === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: trabajador/index.php");
    }
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack - Login</title>
    <link rel="stylesheet" href="/timetrack/css/style.css">
</head>
<body>

<!-- Contenedor centrado del login -->
<div class="contenedor-login">
  <!-- Slideshow del logo tipo reloj -->
    <div id="logo-reloj">
        <?php for($i = 1; $i <= 12; $i++): ?>
            <img src="/timetrack/img/<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>.png"
                 class="logo-frame"
                 alt="TimeTrack">
        <?php endfor; ?>
    </div>
    <h1>TimeTrack</h1>
    <h2>Control horario de trabajadores</h2>

    <!-- Mostramos el error si existe -->
    <?php if (isset($error)) { echo "<p style='color:red'>" . $error . "</p>"; } ?>

    <form action="index.php" method="POST">
        <fieldset>
            <legend>Acceso a la aplicación</legend>

            <label>Usuario</label>
            <input type="text" name="user" placeholder="Tu nombre de usuario">

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Tu contraseña">

            <input type="submit" name="login" value="Iniciar Sesión">


<!-- Slideshow de imágenes debajo del formulario -->
    <!-- imágenes están ocultas con display:none -->
    <!-- menos la primera que se muestra al cargar -->
    <div id="slideshow">
        <img src="/timetrack/img/slide1.jpg" class="slide" alt="Slide 1">
        <img src="/timetrack/img/slide2.jpg" class="slide" alt="Slide 2">
        <img src="/timetrack/img/slide3.jpg" class="slide" alt="Slide 3">
    </div>


        </fieldset>
    </form>

</div>
 

</div>


<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="/timetrack/js/main.js"></script>
</body>
</html>