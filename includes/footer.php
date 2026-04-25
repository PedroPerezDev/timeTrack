<?php
/*
 * Pie de página de la aplicación TimeTrack
 * Se incluye en todas las páginas de la aplicación
 */
?>

<footer>
    <p>TimeTrack &copy; <?php echo date('Y'); ?></p>
    <p>Desarrollado por Pedro Pérez Alfonso</p>
    <p>Aplicación de control horario para empresas</p>
    <!-- Muestra la hora actual del servidor, útil para el control horario -->
    <p>Hora del servidor: <?php echo date('H:i:s'); ?></p>
</footer>

</body>
</html>