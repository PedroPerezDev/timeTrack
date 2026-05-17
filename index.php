<?php
/*
 * Página principal de TimeTrack
 * Gestiona el login de usuarios
 * Redirige al panel de admin o trabajador según el rol
 * Incluye funcionalidad "Recuérdame" con cookie simple
 */

include "./config.php";

session_start();

//-----------------------------------------------------|
//------- FUNCIÓN PARA INICIAR SESIÓN CON UN USER ----|
//-----------------------------------------------------|

/*
 * Centralizo aquí el arranque de sesión para no repetir
 * el código en el login normal y en el login por cookie
 */
function iniciarSesion($fila) {
    $_SESSION['user'] = $fila['nombre'];
    $_SESSION['rol']  = $fila['rol'];
    $_SESSION['id']   = $fila['id'];
}

/*
 * Redirige al panel correspondiente según el rol
 */
function redirigirPorRol($rol) {
    if ($rol === 'admin') {
        header("Location: admin/index.php");
    } else {
        header("Location: trabajador/index.php");
    }
    exit;
}

//-----------------------------------------------------|
//---------------------- LOGIN ------------------------|
//-----------------------------------------------------|

// COMPROBAMOS EL POST ANTES QUE LA SESIÓN
// para que el formulario funcione a la primera

if (isset($_POST['login'])) {

    $conexion = conectar();

    if ($conexion) {

        // Buscamos el usuario por nombre en la base de datos
        $consulta  = "SELECT * FROM usuarios WHERE nombre = '" . $_POST['user'] . "'";
        $resultado = $conexion->query($consulta);

        if ($resultado->num_rows == 1) {

            $fila = $resultado->fetch_assoc();

            if ($_POST['password'] === $fila['password']) {

                // Arranco la sesión con los datos del usuario
                iniciarSesion($fila);

                //-----------------------------------------------------|
                //---------- COOKIE RECUÉRDAME ----------------------- |
                //-----------------------------------------------------|

                /*
                 * Si el usuario marca "Recuérdame" guardo su nombre
                 * en una cookie durante 30 días en el navegador.
                 * La próxima vez que entre, el sistema leerá la cookie
                 * y rellenará el campo usuario automáticamente.
                 */
                if (isset($_POST['recordar']) && $_POST['recordar'] == '1') {

                    // Guardo el nombre del usuario en la cookie 30 días
                    setcookie('timetrack_usuario', $fila['nombre'], strtotime('+30 days'), '/');

                } else {

                    // Si no marca recuérdame borro la cookie si existía
                    setcookie('timetrack_usuario', '', time() - 3600, '/');
                }

                desconectar($conexion);
                redirigirPorRol($fila['rol']);

            } else {
                $error = "Contraseña incorrecta";
            }

        } else {
            $error = "Usuario incorrecto";
        }

        desconectar($conexion);
    }
}

//-----------------------------------------------------|
//------- SI YA HAY SESIÓN ACTIVA REDIRIGIMOS ---------|
//-----------------------------------------------------|

if (isset($_SESSION['user'])) {
    redirigirPorRol($_SESSION['rol']);
}

//-----------------------------------------------------|
//---------- COOKIE RECUÉRDAME — RELLENAR CAMPO ------|
//-----------------------------------------------------|

/*
 * Si existe la cookie con el nombre del usuario
 * lo paso a la vista para rellenar el campo automáticamente
 */
$usuario_recordado = isset($_COOKIE['timetrack_usuario']) ? $_COOKIE['timetrack_usuario'] : '';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TimeTrack - Login</title>
    <link rel="stylesheet" href="/css/style.css">
</head>
<body>

<!-- Contenedor centrado del login -->
<div class="contenedor-login">

    <!-- Slideshow del logo tipo reloj -->
    <div id="logo-reloj">
        <?php for($i = 1; $i <= 12; $i++): ?>
            <img src="/img/<?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?>.png"
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
            <input type="text" name="user" placeholder="Tu nombre de usuario" value="<?php echo htmlspecialchars($usuario_recordado); ?>">

            <label>Contraseña</label>
            <input type="password" name="password" placeholder="Tu contraseña">

            <!-- Casilla recuérdame -->
            <div style="display:flex; align-items:center; gap:8px; margin-top:12px">
                <input type="checkbox" name="recordar" value="1" id="recordar"
                    style="width:auto; margin:0">
                <label for="recordar" style="margin:0; font-size:12px; color:var(--color-texto)">
                    Recuérdame durante 30 días
                </label>
            </div>

            <input type="submit" name="login" value="Iniciar Sesión">

            <!-- Slideshow de imágenes debajo del formulario -->
            <div id="slideshow">
                <img src="/img/slide1.jpg" class="slide" alt="Slide 1">
                <img src="/img/slide2.jpg" class="slide" alt="Slide 2">
                <img src="/img/slide3.jpg" class="slide" alt="Slide 3">
            </div>

        </fieldset>
    </form>

</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="/js/main.js"></script>
</body>
</html>
