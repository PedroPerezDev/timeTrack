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
                $_SESSION['user']    = $fila['nombre'];
                $_SESSION['rol']     = $fila['rol'];
                $_SESSION['id']      = $fila['id'];
                $_SESSION['nombre']  = $fila['nombre'];

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
//---------- MOSTRAMOS EL FORMULARIO DE LOGIN ---------|
//-----------------------------------------------------|

// Si ya hay sesión activa redirigimos directamente
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
</head>
<body>

<h1>TimeTrack</h1>
<h2>Iniciar sesión</h2>

<!-- Mostramos el error si existe -->
<?php if (isset($error)) { echo "<p style='color:red'>" . $error . "</p>"; } ?>

<form action="index.php" method="POST">
    <fieldset>
        <legend>Acceso a la aplicación</legend>
        <label>Usuario: </label>
        <input type="text" name="user"><br>
        <label>Contraseña: </label>
        <input type="password" name="password"><br>
        <input type="submit" name="login" value="Iniciar Sesión">
    </fieldset>
</form>

</body>
</html>