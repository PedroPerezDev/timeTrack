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
</footer>


<script src="/timetrack/js/main.js"></script>
<script>
    /* ------------------------------------------------
       MODO OSCURO / CLARO
       Activa o desactiva la clase .modo-oscuro en el body
       y actualiza el texto del botón según el estado
       ------------------------------------------------ */
    function toggleModo() {
        // Añade o quita la clase modo-oscuro del body
        $('body').toggleClass('modo-oscuro');

        // Cambia el texto del botón según el estado actual
        if ($('body').hasClass('modo-oscuro')) {
            $('#btn-modo').text('Modo claro');
        } else {
            $('#btn-modo').text('Modo oscuro');
        }
    }
</script>

</body>
</html>