<?php
/*
 * Informes de fichajes - Panel de administrador
 * Permite ver los fichajes de un trabajador en un rango de fechas
 * Muestra horas de más, de menos e incidencias
 * Solo accesible para usuarios con rol 'admin'
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

include "../config.php";

$conexion = conectar();

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informes - TimeTrack</title>
</head>
<body>

<?php include "../includes/header.php"; ?>

<main>
    <h2>Informes de fichajes</h2>

    <?php

    //-----------------------------------------------------|
    //------------ BUSCADOR DE TRABAJADORES --------------|
    //-----------------------------------------------------|

    ?>

    <form action="informes.php" method="POST">
        <fieldset>
            <legend>Seleccionar trabajador y rango de fechas</legend>

            <label>Buscar por nombre</label>
            <input type="text" name="buscar_nombre" placeholder="Escribe el nombre..."
                value="<?php echo isset($_POST['buscar_nombre']) ? $_POST['buscar_nombre'] : ''; ?>">

            <label>O selecciona de la lista</label>
            <select name="buscar_id">
                <option value="">-- Selecciona un trabajador --</option>
                <?php
                $todos = $conexion->query("SELECT id, nombre, apellidos FROM usuarios 
                    WHERE rol = 'trabajador' ORDER BY apellidos ASC");
                while ($t = $todos->fetch_assoc()) {
                    $selected = (isset($_POST['buscar_id']) && $_POST['buscar_id'] == $t['id']) ? "selected" : "";
                    echo "<option value='" . $t['id'] . "' $selected>" . $t['apellidos'] . ", " . $t['nombre'] . "</option>";
                }
                ?>
            </select>

            <label>Fecha inicio</label>
            <input type="date" name="fecha_inicio" 
                value="<?php echo isset($_POST['fecha_inicio']) ? $_POST['fecha_inicio'] : date('Y-m-01'); ?>">

            <label>Fecha fin</label>
            <input type="date" name="fecha_fin"
                value="<?php echo isset($_POST['fecha_fin']) ? $_POST['fecha_fin'] : date('Y-m-d'); ?>">

            <input type="submit" name="buscar" value="Ver informe">
        </fieldset>
    </form>

    <?php

    //-----------------------------------------------------|
    //------------ GENERAR INFORME ----------------------- |
    //-----------------------------------------------------|

    if (isset($_POST['buscar'])) {

        $id_trabajador = null;

        if (!empty($_POST['buscar_id'])) {
            $id_trabajador = $_POST['buscar_id'];

        } elseif (!empty($_POST['buscar_nombre'])) {
            $nombre_buscar = $_POST['buscar_nombre'];
            $res = $conexion->query("SELECT id FROM usuarios 
                WHERE (nombre LIKE '%$nombre_buscar%' OR apellidos LIKE '%$nombre_buscar%')
                AND rol = 'trabajador' LIMIT 1");
            if ($res->num_rows > 0) {
                $id_trabajador = $res->fetch_assoc()['id'];
            }
        }

        if (!$id_trabajador) {
            echo "<p style='color:red'>Introduce un nombre o selecciona un trabajador</p>";

        } else {

            $fecha_inicio = $_POST['fecha_inicio'];
            $fecha_fin    = $_POST['fecha_fin'];

            $trabajador = $conexion->query("SELECT nombre, apellidos FROM usuarios 
                WHERE id = '$id_trabajador'")->fetch_assoc();

            // Guardo los datos en sesión para recuperarlos en pdf.php
            $_SESSION['pdf_trabajador_id']     = $id_trabajador;
            $_SESSION['pdf_trabajador_nombre'] = $trabajador['nombre'] . " " . $trabajador['apellidos'];
            $_SESSION['pdf_fecha_inicio']      = $fecha_inicio;
            $_SESSION['pdf_fecha_fin']         = $fecha_fin;

            echo "<h3>Informe de " . $trabajador['nombre'] . " " . $trabajador['apellidos'] . "</h3>";
            echo "<p>Período: " . date('d/m/Y', strtotime($fecha_inicio)) . " — " . date('d/m/Y', strtotime($fecha_fin)) . "</p>";

            //-----------------------------------------------------|
            //-------- CÁLCULO HORAS SEMANALES PREVISTAS ----------|
            //-----------------------------------------------------|

            /*
             * Calculo las horas semanales que debería hacer el trabajador
             * sumando la diferencia entre entrada y salida de cada día
             * Resto 30 minutos por día de descanso para almorzar
             * que no cuenta como tiempo trabajado
             */
            $horarios_semana   = $conexion->query("SELECT * FROM horarios 
                WHERE usuario_id = '$id_trabajador'");
            $minutos_semanales = 0;

            while ($h = $horarios_semana->fetch_assoc()) {

                // Minutos de la mañana
                $entrada_1 = strtotime($h['hora_entrada_1']);
                $salida_1  = strtotime($h['hora_salida_1']);
                $minutos_semanales += ($salida_1 - $entrada_1) / 60;

                // Minutos de la tarde
                $entrada_2 = strtotime($h['hora_entrada_2']);
                $salida_2  = strtotime($h['hora_salida_2']);
                $minutos_semanales += ($salida_2 - $entrada_2) / 60;

                // Resto 30 minutos de descanso por día
                $minutos_semanales -= 30;
            }

            $horas_semanales   = floor($minutos_semanales / 60);
            $minutos_restantes = $minutos_semanales % 60;

            //-----------------------------------------------------|
            //------------ FICHAJES DEL PERÍODO ------------------- |
            //-----------------------------------------------------|

            $fichajes = $conexion->query("SELECT * FROM fichajes 
                WHERE usuario_id = '$id_trabajador'
                AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY fecha ASC, tipo ASC");

            $fichajes_por_dia = [];
            while ($f = $fichajes->fetch_assoc()) {
                $fichajes_por_dia[$f['fecha']][$f['tipo']] = $f;
            }

            $nombres_tipo = [
                'entrada_1' => 'Entrada mañana',
                'salida_1'  => 'Salida mañana',
                'entrada_2' => 'Entrada tarde',
                'salida_2'  => 'Salida tarde'
            ];

            if (empty($fichajes_por_dia)) {
                echo "<p style='color:red'>No hay fichajes en este período</p>";

            } else {

                $total_minutos_extra = 0;
                $total_minutos_menos = 0;
                $dias_trabajados     = 0;

                echo "<div class='tabla-wrapper'><table class='tabla-apilable'>
                    <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Fichaje</th>
                        <th>Hora prevista</th>
                        <th>Hora fichada</th>
                        <th>Diferencia</th>
                    </tr>
                    </thead>
                    <tbody>";

                foreach ($fichajes_por_dia as $fecha => $fichajes_dia) {

                    $fecha_formateada = date('d/m/Y', strtotime($fecha));
                    $primer_fichaje   = true;
                    $dias_trabajados++;

                    foreach (['entrada_1', 'salida_1', 'entrada_2', 'salida_2'] as $tipo) {

                        if (!isset($fichajes_dia[$tipo])) continue;

                        $f   = $fichajes_dia[$tipo];
                        $dif = $f['minutos_diferencia'];

                        /*
                         * Positivo = minutos a favor del trabajador
                         * Negativo = minutos en contra del trabajador
                         */
                        if ($dif > 0) {
                            $dif_html = "<span style='color:var(--color-informacion)'>+" . $dif . " min</span>";
                            $total_minutos_extra += $dif;
                        } elseif ($dif < 0) {
                            $dif_html = "<span style='color:var(--color-error)'>" . $dif . " min</span>";
                            $total_minutos_menos += abs($dif);
                        } else {
                            $dif_html = "<span style='color:var(--color-principal)'>Puntual</span>";
                        }

                        $dia_semana  = date('N', strtotime($fecha));
                        $horario_dia = $conexion->query("SELECT * FROM horarios 
                            WHERE usuario_id = '$id_trabajador' 
                            AND dia_semana = '$dia_semana'")->fetch_assoc();

                        $campo_hora    = 'hora_' . $tipo;
                        $hora_prevista = $horario_dia ? substr($horario_dia[$campo_hora], 0, 5) : '--:--';

                        echo "<tr>
                            <td data-label='Fecha'>" . ($primer_fichaje ? $fecha_formateada : '') . "</td>
                            <td data-label='Fichaje'>" . $nombres_tipo[$tipo] . "</td>
                            <td data-label='Hora prevista'>" . $hora_prevista . "</td>
                            <td data-label='Hora fichada'>" . substr($f['hora_fichaje'], 0, 5) . "</td>
                            <td data-label='Diferencia'>" . $dif_html . "</td>
                        </tr>";

                        $primer_fichaje = false;
                    }
                }

                echo "</tbody></table></div>";

                //-----------------------------------------------------|
                //------------ RESUMEN TOTAL -------------------------- |
                //-----------------------------------------------------|

                /*
                 * Calculo las semanas del período para saber
                 * cuántas horas debería haber trabajado en total
                 */
                $semanas_periodo       = $dias_trabajados / 5;
                $minutos_previstos     = $minutos_semanales * $semanas_periodo;
                $horas_previstas_total = floor($minutos_previstos / 60);
                $min_previstos_resto   = $minutos_previstos % 60;

                $balance         = $total_minutos_extra - $total_minutos_menos;
                $balance_horas   = floor(abs($balance) / 60);
                $balance_minutos = abs($balance) % 60;

                echo "<div class='informe-resumen'>
                    <h3>Resumen del período</h3>

                    <div class='informe-resumen-item'>
                        <span>Horas semanales previstas</span>
                        <span>" . $horas_semanales . "h " . $minutos_restantes . "min por semana</span>
                    </div>

                    <div class='informe-resumen-item'>
                        <span>Horas totales previstas en el período</span>
                        <span>" . $horas_previstas_total . "h " . $min_previstos_resto . "min</span>
                    </div>

                    <div class='informe-resumen-item'>
                        <span>Minutos a favor del trabajador</span>
                        <span style='color:var(--color-informacion)'>+" . $total_minutos_extra . " min (" . floor($total_minutos_extra/60) . "h " . ($total_minutos_extra%60) . "min)</span>
                    </div>

                    <div class='informe-resumen-item'>
                        <span>Minutos en contra del trabajador</span>
                        <span style='color:var(--color-error)'>-" . $total_minutos_menos . " min (" . floor($total_minutos_menos/60) . "h " . ($total_minutos_menos%60) . "min)</span>
                    </div>

                    <div class='informe-resumen-item'>
                        <span>Balance total</span>";

                if ($balance > 0) {
                    echo "<span style='color:var(--color-informacion)'>+" . $balance_horas . "h " . $balance_minutos . "min a favor del trabajador</span>";
                } elseif ($balance < 0) {
                    echo "<span style='color:var(--color-error)'>-" . $balance_horas . "h " . $balance_minutos . "min en contra del trabajador</span>";
                } else {
                    echo "<span style='color:var(--color-principal)'>Balance equilibrado</span>";
                }

                echo "  </div>
                </div>";

                //-----------------------------------------------------|
                //------------ INCIDENCIAS DEL PERÍODO ---------------- |
                //-----------------------------------------------------|

                $incidencias = $conexion->query("SELECT * FROM incidencias
                    WHERE usuario_id = '$id_trabajador'
                    AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                    ORDER BY fecha ASC");

                if ($incidencias->num_rows > 0) {

                    echo "<h3>Incidencias del período</h3>";
                    echo "<div class='tabla-wrapper'><table class='tabla-apilable'>
                        <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Minutos</th>
                            <th>Observaciones</th>
                        </tr>
                        </thead>
                        <tbody>";

                    while ($inc = $incidencias->fetch_assoc()) {
                        echo "<tr>
                            <td data-label='Fecha'>" . date('d/m/Y', strtotime($inc['fecha'])) . "</td>
                            <td data-label='Tipo'>" . ucfirst(str_replace('_', ' ', $inc['tipo'])) . "</td>
                            <td data-label='Minutos'>" . $inc['minutos'] . " min</td>
                            <td data-label='Observaciones'>" . $inc['observaciones'] . "</td>
                        </tr>";
                    }

                    echo "</tbody></table></div>";
                }

                // Botón para generar el PDF en nueva pestaña
                echo "<form action='pdf.php' method='POST' target='_blank'>
                    <input type='submit' name='pdf' value='Generar PDF'>
                </form>";
            }
        }
    }

    desconectar($conexion);
    ?>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>