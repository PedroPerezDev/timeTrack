<?php
/*
 * Panel principal del administrador
 * Muestra un resumen de las acciones más comunes
 */
session_start();
include "../includes/header.php";
?>

<main>
    <h2>Panel de Administración</h2>
    <p>Bienvenido, <?php echo $_SESSION['user']; ?></p>
</main>

<?php include "../includes/footer.php"; ?>