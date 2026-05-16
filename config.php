<?php
/*
 * Defino las variables de conexión
 */


$host    = "localhost";
$usuario = "root";
$pass    = "";
$db      = "timetrack_db";

/*
 * Conexión a la base de datos
 */

function conectar() {
    global $host, $usuario, $pass, $db;
    $conexion = mysqli_connect($host, $usuario, $pass, $db);
    mysqli_set_charset($conexion, 'utf8');
    if (mysqli_connect_errno()) {
        echo "Error \n";
        echo "Errno: " . mysqli_connect_errno() . "\n";
        echo "Error: " . mysqli_connect_error() . "\n";
        exit;
    }
    return $conexion;
}

/*
 * Desconexión de la base de datos
 */
function desconectar($conexion) {
    mysqli_close($conexion);
}
?>