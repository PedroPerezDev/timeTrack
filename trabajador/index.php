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

    <!-- Reloj en tiempo real -->
    <!-- El JS busca los ids 'reloj' y 'fecha' y los actualiza cada segundo -->
    <div class="contenedor-reloj">
        <span id="reloj"></span>
        <span id="fecha"></span>
        <button type="button" id="btn-fichar">FICHAR</button>
    </div>

</main>

<script src="/timetrack/js/main.js"></script>
<?php include "../includes/footer.php"; ?>