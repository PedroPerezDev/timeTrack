<?php
/*
 * Panel principal del trabajador
 * Muestra el horario del día y el botón de fichar
 * El sistema activa solo el siguiente fichaje pendiente
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "trabajador") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

//-----------------------------------------------------|
//---------- OBTENER DÍA Y HORARIO DE HOY ------------|
//-----------------------------------------------------|

/*
 * Obtenemos el día de la semana actual
 * date('N') devuelve 1=Lunes hasta 7=Domingo
 * Nosotros solo usamos del 1 al 5 (lunes a viernes)
 */
$dia_semana = date('N'); // 1=Lunes, 5=Viernes
$fecha_hoy  = date('Y-m-d');

// Primero compruebo si hay un día especial para hoy
$especial = $conexion->query("SELECT * FROM horarios_especiales 
    WHERE usuario_id = '" . $_SESSION['id'] . "' 
    AND fecha = '$fecha_hoy'")->fetch_assoc();

// Si no hay día especial busco el horario normal del día
$horario = null;
if (!$especial) {
    $resultado_horario = $conexion->query("SELECT * FROM horarios 
        WHERE usuario_id = '" . $_SESSION['id'] . "' 
        AND dia_semana = '$dia_semana'");
    $horario = $resultado_horario->fetch_assoc();
}

//-----------------------------------------------------|
//---------- FICHAJES YA HECHOS HOY ------------------|
//-----------------------------------------------------|

/*
 * Recupero los fichajes que ya ha hecho el trabajador hoy
 * Los guardo en un array indexado por tipo para acceder fácilmente
 * tipos: entrada_1, salida_1, entrada_2, salida_2
 */
$fichajes_hoy = [];
$resultado_fichajes = $conexion->query("SELECT * FROM fichajes 
    WHERE usuario_id = '" . $_SESSION['id'] . "' 
    AND fecha = '$fecha_hoy'");

while ($f = $resultado_fichajes->fetch_assoc()) {
    // Guardo cada fichaje indexado por su tipo
    $fichajes_hoy[$f['tipo']] = $f;
}

desconectar($conexion);

/*
 * Determino cuál es el siguiente fichaje pendiente
 * El orden siempre es: entrada_1 -> salida_1 -> entrada_2 -> salida_2
 */
$orden_fichajes    = ['entrada_1', 'salida_1', 'entrada_2', 'salida_2'];
$siguiente_fichaje = null;

foreach ($orden_fichajes as $tipo) {
    if (!isset($fichajes_hoy[$tipo])) {
        // Este fichaje no está hecho, es el siguiente pendiente
        $siguiente_fichaje = $tipo;
        break;
    }
}

// Nombres legibles para mostrar en pantalla
$nombres_fichaje = [
    'entrada_1' => 'Entrada mañana',
    'salida_1'  => 'Salida mañana',
    'entrada_2' => 'Entrada tarde',
    'salida_2'  => 'Salida tarde'
];

// Horas previstas según el horario
$horas_previstas = [];
if ($horario) {
    $horas_previstas = [
        'entrada_1' => $horario['hora_entrada_1'],
        'salida_1'  => $horario['hora_salida_1'],
        'entrada_2' => $horario['hora_entrada_2'],
        'salida_2'  => $horario['hora_salida_2']
    ];
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi jornada - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Mi jornada de hoy</h2>

    <div class="jornada-wrapper">

        <!-- Fecha arriba -->
        <span class="jornada-fecha" id="fecha"></span>

        <!-- Hora debajo -->
        <span class="jornada-hora" id="reloj"></span>

        <?php

        //-----------------------------------------------------|
        //---------- CASOS ESPECIALES --------------------------|
        //-----------------------------------------------------|

        // Si es fin de semana no hay fichaje
        if ($dia_semana > 5) {
            echo "<p class='fichaje-mensaje'>Hoy es fin de semana, descansa!</p>";

        // Si hay día especial de vacaciones, festivo o libre
        } elseif ($especial && $especial['tipo'] != 'cambio_horario') {
            $tipos_mensaje = [
                'vacaciones' => 'Hoy estás de vacaciones',
                'festivo'    => 'Hoy es festivo',
                'libre'      => 'Hoy tienes el día libre'
            ];
            echo "<p class='fichaje-mensaje'>" . $tipos_mensaje[$especial['tipo']] . "</p>";

        // Si no tiene horario asignado
        } elseif (!$horario && !$especial) {
            echo "<p class='fichaje-mensaje'>No tienes horario asignado para hoy. Contacta con el administrador.</p>";

        // Si ha completado todos los fichajes del día
        } elseif ($siguiente_fichaje === null) {
            echo "<p class='fichaje-mensaje'>¡Jornada completada, hasta mañana!</p>";

        // Muestra los 4 botones de fichaje
        } else {
        ?>

        <!-- Tabla de fichajes del día -->
        <div class="fichajes-wrapper">
            <?php foreach ($orden_fichajes as $tipo): ?>
            <div class="fichaje-item <?php echo isset($fichajes_hoy[$tipo]) ? 'fichaje-hecho' : ''; ?>">

                <!-- Nombre del fichaje -->
                <span class="fichaje-nombre"><?php echo $nombres_fichaje[$tipo]; ?></span>

                <!-- Hora prevista según horario -->
                <span class="fichaje-hora-prevista">
                    Prevista: <?php echo isset($horas_previstas[$tipo]) ? substr($horas_previstas[$tipo], 0, 5) : '--:--'; ?>
                </span>

                <!-- Hora real fichada si ya se ha hecho -->
                <?php if (isset($fichajes_hoy[$tipo])): ?>
                    <span class="fichaje-hora-real">
                        Fichada: <?php echo substr($fichajes_hoy[$tipo]['hora_fichaje'], 0, 5); ?>
                    </span>

                    <?php
                    /*
                     * Muestro los minutos de diferencia
                     * Positivo = minutos a favor del trabajador (verde/azul)
                     * Negativo = minutos en contra del trabajador (rojo)
                     */
                    $dif = $fichajes_hoy[$tipo]['minutos_diferencia'];
                    if ($dif > 0): ?>
                        <span class="fichaje-pronto">+<?php echo $dif; ?> min a favor</span>
                    <?php elseif ($dif < 0): ?>
                        <span class="fichaje-tarde"><?php echo $dif; ?> min</span>
                    <?php else: ?>
                        <span class="fichaje-puntual">Puntual ✓</span>
                    <?php endif; ?>

                <!-- Botón de fichar si es el siguiente pendiente -->
                <?php elseif ($tipo === $siguiente_fichaje): ?>
                    <!-- El tipo se manda por AJAX a fichar.php con data-tipo -->
                    <!-- La hora prevista se manda con data-hora para calcular diferencia -->
                    <button type="button" id="btn-fichar"
                        data-tipo="<?php echo $tipo; ?>"
                        data-hora="<?php echo isset($horas_previstas[$tipo]) ? $horas_previstas[$tipo] : ''; ?>">
                        FICHAR
                    </button>

                <!-- Botones pendientes desactivados -->
                <?php else: ?>
                    <button type="button" disabled class="btn-pendiente">Pendiente</button>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- Aquí jQuery mostrará la respuesta del AJAX sin recargar la página -->
        <div id="respuesta-fichaje"></div>

        <?php } ?>

    </div>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>