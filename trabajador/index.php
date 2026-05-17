<?php
/*
 * Panel principal del trabajador
 * Muestra el horario del día y el botón de fichar
 * El sistema activa solo el siguiente fichaje pendiente
 */

include "../includes/funciones.php";
verificarSesion('trabajador');

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
// $dia_semana = 5; // 1=Lunes, 5=Viernes
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

//-----------------------------------------------------|
//---------- HORAS DEL MES ACTUAL ---------------|
//-----------------------------------------------------|

/*
 * Recupero todos los fichajes del trabajador en el mes actual
 * para calcular las horas trabajadas por día y el total del mes
 */
$primer_dia_mes = date('Y-m-01');
$ultimo_dia_mes = date('Y-m-t');

$resultado_mes = $conexion->query("SELECT * FROM fichajes
    WHERE usuario_id = '" . $_SESSION['id'] . "'
    AND fecha BETWEEN '$primer_dia_mes' AND '$ultimo_dia_mes'
    ORDER BY fecha ASC, tipo ASC");

// Indexo los fichajes por fecha y tipo
$fichajes_mes = [];
while ($f = $resultado_mes->fetch_assoc()) {
    $fichajes_mes[$f['fecha']][$f['tipo']] = $f['hora_fichaje'];
}

$total_minutos_mes = 0;

desconectar($conexion);

/*
 * Los 4 botones están siempre operativos
 * Cada uno se activa o desactiva según si ya ha sido pulsado hoy
 * El orden se mantiene solo para mostrarlo en pantalla
 */
$orden_fichajes = ['entrada_1', 'salida_1', 'entrada_2', 'salida_2'];

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
        } elseif (count($fichajes_hoy) === 4) {
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

                <!-- Botón de fichar siempre operativo si no se ha fichado aún -->
                <?php else: ?>
                    <!-- El tipo se manda por AJAX a fichar.php con data-tipo -->
                    <!-- La hora prevista se manda con data-hora para calcular diferencia -->
                    <button type="button" class="btn-fichar"
                        data-tipo="<?php echo $tipo; ?>"
                        data-hora="<?php echo isset($horas_previstas[$tipo]) ? $horas_previstas[$tipo] : ''; ?>">
                        FICHAR
                    </button>
                <?php endif; ?>

            </div>
            <?php endforeach; ?>
        </div>

        <!-- Aquí jQuery mostrará la respuesta del AJAX sin recargar la página -->
        <div id="respuesta-fichaje"></div>

        <?php } ?>

    <!-- Botón para desplegar el resumen mensual de horas -->
    <?php
    $meses_boton = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                    'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
    $nombre_mes_boton = $meses_boton[date('n') - 1];
    ?>
    <button type="button" id="btn-mis-horas" class="btn-mis-horas">
        Ver mis horas de <?php echo $nombre_mes_boton; ?>
    </button>

    <!-- Tabla de horas del mes, oculta por defecto -->
    <div id="tabla-mis-horas" style="display:none">

        <?php

        // Nombre del mes actual en español
        $meses_es = ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
                     'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
        $nombre_mes = $meses_es[date('n') - 1];

        echo "<div class='mis-horas-cabecera'>
                <span class='mis-horas-titulo'>$nombre_mes " . date('Y') . "</span>
              </div>";

        echo "<table class='mis-horas-tabla'>
            <tr>
                <th>Día</th>
                <th>Horas</th>
            </tr>";

        // Recorro todos los días del mes hasta hoy
        $num_dias = date('t');
        for ($d = 1; $d <= $num_dias; $d++) {

            $fecha_dia  = date('Y-m-') . str_pad($d, 2, '0', STR_PAD_LEFT);
            $dia_semana = date('N', strtotime($fecha_dia));
            $nombre_dia = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'][$dia_semana - 1];
            $es_hoy     = $fecha_dia === $fecha_hoy;
            $es_futuro  = $fecha_dia > $fecha_hoy;

            // No muestro días futuros
            if ($es_futuro) break;

            $fila_class = $es_hoy ? ' class="dia-hoy"' : '';

            // Fin de semana sin datos
            if ($dia_semana > 5 && !isset($fichajes_mes[$fecha_dia])) {
                continue;
            }

            $f = isset($fichajes_mes[$fecha_dia]) ? $fichajes_mes[$fecha_dia] : [];

            // Sumo mañana y tarde
            $min_dia = 0;
            if (isset($f['entrada_1'], $f['salida_1'])) {
                $min_dia += (strtotime($f['salida_1']) - strtotime($f['entrada_1'])) / 60;
            }
            if (isset($f['entrada_2'], $f['salida_2'])) {
                $min_dia += (strtotime($f['salida_2']) - strtotime($f['entrada_2'])) / 60;
            }

            $total_minutos_mes += $min_dia;

            if ($min_dia > 0) {
                $h = floor($min_dia / 60); $m = $min_dia % 60;
                $txt_total = $m > 0 ? $h . 'h ' . $m . 'min' : $h . 'h';
            } elseif (empty($f)) {
                $txt_total = '—';
            } else {
                $txt_total = 'En curso';
            }

            echo "<tr$fila_class>
                <td>$d</td>
                <td class='horas-col'><b>$txt_total</b></td>
            </tr>";
        }

        // Fila de total del mes
        $h_mes = floor($total_minutos_mes / 60);
        $m_mes = $total_minutos_mes % 60;
        $txt_total_mes = $m_mes > 0 ? $h_mes . 'h ' . $m_mes . 'min' : $h_mes . 'h';

        echo "<tr class='fila-total'>
            <td>Total</td>
            <td class='horas-col'>$txt_total_mes</td>
        </tr>";

        echo "</table>";
        ?>

    </div>

    </div>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>