<?php
/*
 * Cierre de sesión
 * Destruye todos los datos de la sesión activa
 * y redirige al login
 */

session_start();
session_destroy(); // destruye la sesión completamente
header("Location: ../index.php");
exit;
?>