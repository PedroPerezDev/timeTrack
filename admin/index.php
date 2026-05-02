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

    <!-- Festivos nacionales cargados desde la API -->
    <h3>Festivos nacionales <?php echo date('Y'); ?></h3>
    <p style="font-size:11px; color:var(--color-texto-apagado)">
        Fuente: API Nager.Date
    </p>
    <div id="festivos"></div>

</main>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<script src="/timetrack/js/main.js"></script>

<?php include "../includes/footer.php"; ?>