<?php
/*
 * Panel principal del administrador
 */
session_start();
include "../includes/header.php";
?>

<h2>Panel de Administración</h2>
<p>Bienvenido, <?php echo $_SESSION['user']; ?></p>

<?php include "../includes/footer.php"; ?>