<?php
/*
 * Cierre de sesión
 * Destruye todos los datos de la sesión activa,
 * borra las cookies de "Recuérdame" y redirige al login
 */

session_start();
session_destroy(); // destruye la sesión completamente

// Borramos las cookies de autologin poniendo fecha de expiración pasada
setcookie('timetrack_usuario',  '', time() - 3600, '/');
setcookie('timetrack_password', '', time() - 3600, '/');

header("Location: /index.php");
exit;
?>
