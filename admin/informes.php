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

            <!-- Búsqueda por nombre -->
            <label>Buscar por nombre</label>
            <input type="text" name="buscar_nombre" placeholder="Escribe el nombre..."
                value="<?php echo isset($_POST['buscar_nombre']) ? $_POST['buscar_nombre'] : ''; ?>">

            <!-- Seleccionar del desplegable -->
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

            <!-- Rango de fechas -->
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

        // Busco por desplegable o por nombre
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

            // Recupero los datos del trabajador
            $trabajador = $conexion->query("SELECT nombre, apellidos FROM usuarios 
                WHERE id = '$id_trabajador'")->fetch_assoc();

            echo "<h3>Informe de " . $trabajador['nombre'] . " " . $trabajador['apellidos'] . "</h3>";
            echo "<p>Período: " . date('d/m/Y', strtotime($fecha_inicio)) . " — " . date('d/m/Y', strtotime($fecha_fin)) . "</p>";

            //-----------------------------------------------------|
            //------------ FICHAJES DEL PERÍODO ------------------- |
            //-----------------------------------------------------|

            /*
             * Recupero todos los fichajes del trabajador
             * en el rango de fechas seleccionado
             * Los agrupo por fecha para mostrarlos por días
             */
            $fichajes = $conexion->query("SELECT * FROM fichajes 
                WHERE usuario_id = '$id_trabajador'
                AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
                ORDER BY fecha ASC, tipo ASC");

            // Agrupo los fichajes por fecha
            $fichajes_por_dia = [];
            while ($f = $fichajes->fetch_assoc()) {
                $fichajes_por_dia[$f['fecha']][$f['tipo']] = $f;
            }

            // Nombres de los tipos de fichaje
            $nombres_tipo = [
                'entrada_1' => 'Entrada mañana',
                'salida_1'  => 'Salida mañana',
                'entrada_2' => 'Entrada tarde',
                'salida_2'  => 'Salida tarde'
            ];

            if (empty($fichajes_por_dia)) {
                echo "<p style='color:red'>No hay fichajes en este período</p>";

            } else {

                // Variables para el resumen total
                $total_minutos_extra = 0;
                $total_minutos_menos = 0;

                echo "<div class='tabla-wrapper'><table>
                    <tr>
                        <th>Fecha</th>
                        <th>Fichaje</th>
                        <th>Hora prevista</th>
                        <th>Hora fichada</th>
                        <th>Diferencia</th>
                    </tr>";

                foreach ($fichajes_por_dia as $fecha => $fichajes_dia) {

                    $fecha_formateada = date('d/m/Y', strtotime($fecha));
                    $primer_fichaje   = true; // para mostrar la fecha solo en la primera fila del día

                    foreach (['entrada_1', 'salida_1', 'entrada_2', 'salida_2'] as $tipo) {

                        if (!isset($fichajes_dia[$tipo])) continue;

                        $f = $fichajes_dia[$tipo];

                        // Calculo el color de la diferencia
                        $dif = $f['minutos_diferencia'];
                        if ($dif > 0) {
                            $dif_html = "<span style='color:var(--color-error)'>+" . $dif . " min</span>";
                            $total_minutos_menos += $dif;
                        } elseif ($dif < 0) {
                            $dif_html = "<span style='color:var(--color-informacion)'>" . $dif . " min</span>";
                            $total_minutos_extra += abs($dif);
                        } else {
                            $dif_html = "<span style='color:var(--color-principal)'>Puntual ✓</span>";
                        }

                        // Recupero la hora prevista del horario
                        $dia_semana   = date('N', strtotime($fecha));
                        $horario_dia  = $conexion->query("SELECT * FROM horarios 
                            WHERE usuario_id = '$id_trabajador' 
                            AND dia_semana = '$dia_semana'")->fetch_assoc();

                        $campo_hora = 'hora_' . $tipo;
                        $hora_prevista = $horario_dia ? substr($horario_dia[$campo_hora], 0, 5) : '--:--';

                        echo "<tr>
                            <td>" . ($primer_fichaje ? $fecha_formateada : '') . "</td>
                            <td>" . $nombres_tipo[$tipo] . "</td>
                            <td>" . $hora_prevista . "</td>
                            <td>" . substr($f['hora_fichaje'], 0, 5) . "</td>
                            <td>" . $dif_html . "</td>
                        </tr>";

                        $primer_fichaje = false;
                    }
                }

                echo "</table></div>";

                //-----------------------------------------------------|
                //------------ RESUMEN TOTAL -------------------------- |
                //-----------------------------------------------------|

                echo "<div class='informe-resumen'>
                    <h3>Resumen del período</h3>
                    <div class='informe-resumen-item'>
                        <span>Minutos de más trabajados</span>
                        <span style='color:var(--color-informacion)'>" . $total_minutos_extra . " min (" . floor($total_minutos_extra/60) . "h " . ($total_minutos_extra%60) . "min)</span>
                    </div>
                    <div class='informe-resumen-item'>
                        <span>Minutos de menos trabajados</span>
                        <span style='color:var(--color-error)'>" . $total_minutos_menos . " min (" . floor($total_minutos_menos/60) . "h " . ($total_minutos_menos%60) . "min)</span>
                    </div>
                    <div class='informe-resumen-item'>
                        <span>Balance total</span>";

                $balance = $total_minutos_extra - $total_minutos_menos;
                if ($balance > 0) {
                    echo "<span style='color:var(--color-informacion)'>+" . $balance . " min a favor del trabajador</span>";
                } elseif ($balance < 0) {
                    echo "<span style='color:var(--color-error)'>" . $balance . " min a favor de la empresa</span>";
                } else {
                    echo "<span style='color:var(--color-principal)'>Balance equilibrado ✓</span>";
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
                    echo "<div class='tabla-wrapper'><table>
                        <tr>
                            <th>Fecha</th>
                            <th>Tipo</th>
                            <th>Minutos</th>
                            <th>Observaciones</th>
                        </tr>";

                    while ($inc = $incidencias->fetch_assoc()) {
                        echo "<tr>
                            <td>" . date('d/m/Y', strtotime($inc['fecha'])) . "</td>
                            <td>" . ucfirst(str_replace('_', ' ', $inc['tipo'])) . "</td>
                            <td>" . $inc['minutos'] . " min</td>
                            <td>" . $inc['observaciones'] . "</td>
                        </tr>";
                    }

                    echo "</table></div>";
                }
            }
        }
    }

    desconectar($conexion);
    ?>

</main>

<?php include "../includes/footer.php"; ?>

</body>
</html>