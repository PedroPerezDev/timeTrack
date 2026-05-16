<?php
/*
 * Página principal de TimeTrack
 * Gestiona el login de usuarios
 * Redirige al panel de admin o trabajador según el rol
 * Incluye funcionalidad "Recuérdame" con cookies seguras
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
                 * Si el usuario ha marcado "Recuérdame" genero un token
                 * aleatorio seguro y lo guardo en la BD junto con su
                 * fecha de expiración (30 días desde ahora).
                 * El token también se guarda en una cookie en el navegador.
                 * La próxima vez que entre, el sistema leerá la cookie
                 * y buscará el token en la BD para logearle automáticamente.
                 */
                if (isset($_POST['recordar']) && $_POST['recordar'] == '1') {

                    // Genero un token aleatorio de 64 caracteres
                    $token      = bin2hex(random_bytes(32));
                    $expira     = date('Y-m-d H:i:s', strtotime('+30 days'));
                    $usuario_id = $fila['id'];

                    // Borro tokens anteriores de este usuario para no acumular
                    $conexion->query("DELETE FROM recordar_sesion 
                        WHERE usuario_id = '$usuario_id'");

                    // Guardo el nuevo token en la BD
                    $conexion->query("INSERT INTO recordar_sesion 
                        (usuario_id, token, expira) 
                        VALUES ('$usuario_id', '$token', '$expira')");

                    // Guardo la cookie en el navegador durante 30 días
                    // El token viaja en la cookie, nunca el ID directamente
                    setcookie('timetrack_remember', $token, [
                        'expires'  => strtotime('+30 days'),
                        'path'     => '/',
                        'httponly' => true,
                        'samesite' => 'Lax'
                    ]);
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
//---------- LOGIN AUTOMÁTICO POR COOKIE ------------- |
//-----------------------------------------------------|

/*
 * Si no hay sesión activa pero existe la cookie "timetrack_remember"
 * busco el token en la BD para verificar que es válido y no ha expirado.
 * Si es válido arranco la sesión automáticamente sin pedir credenciales.
 * Si ha expirado borro la cookie para que no moleste más.
 */
if (isset($_COOKIE['timetrack_remember'])) {

    $token    = $_COOKIE['timetrack_remember'];
    $conexion = conectar();

    if ($conexion) {

        // Busco el token en la BD comprobando que no haya expirado
        $resultado = $conexion->query("SELECT u.* FROM usuarios u
            INNER JOIN recordar_sesion r ON u.id = r.usuario_id
            WHERE r.token = '$token'
            AND r.expira > NOW()");

        if ($resultado->num_rows == 1) {

            $fila = $resultado->fetch_assoc();

            // Token válido: arranco sesión y redirijo
            iniciarSesion($fila);
            desconectar($conexion);
            redirigirPorRol($fila['rol']);

        } else {

            // Token expirado o no encontrado: borro la cookie
            setcookie('timetrack_remember', '', [
                'expires'  => time() - 3600,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }

        desconectar($conexion);
    }
}
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
            <input type="text" name="user" placeholder="Tu nombre de usuario">

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
