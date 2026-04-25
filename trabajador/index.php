<?php
/*
 * Panel principal del trabajador
 */
session_start();
include "../includes/header.php";
?>

<h2>Mi jornada de hoy</h2>
<p>Bienvenido, <?php echo $_SESSION['user']; ?></p>

<?php include "../includes/footer.php"; ?>