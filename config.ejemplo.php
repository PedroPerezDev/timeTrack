<?php
/*
 * Fichero de configuración y conexión a la base de datos
 * Proyecto: TimeTrack - Control horario de trabajadores
 * 
 * Copia este archivo, renómbralo como config.php
 * y rellena los datos de conexión a tu base de datos
 */


$host    = "";
$usuario = "";
$pass    = "";
$db      = "";

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