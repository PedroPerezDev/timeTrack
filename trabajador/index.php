<?php
/*
 * Panel principal del trabajador
 * Muestra el horario del día y el botón de fichar
 */
session_start();
include "../includes/header.php";
?>

<main>
    <h2>Mi jornada de hoy</h2>
    <p>Bienvenido, <?php echo $_SESSION['user']; ?></p>
</main>

<?php include "../includes/footer.php"; ?>