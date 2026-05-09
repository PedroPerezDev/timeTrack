<?php
/*
 * Generación de PDF de fichajes
 * Sigue el mismo patrón del examen de clase
 * Solo PHP, sin HTML mezclado
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

// Cargo la librería FPDF igual que en el examen
require "../libs/fpdf/fpdf.php";
include "../config.php";

$conexion = conectar();

// Recupero los datos guardados en sesión desde informes.php
if (isset($_SESSION['pdf_trabajador_id'])) {

    $id_trabajador    = $_SESSION['pdf_trabajador_id'];
    $nombre_trabajador = $_SESSION['pdf_trabajador_nombre'];
    $fecha_inicio     = $_SESSION['pdf_fecha_inicio'];
    $fecha_fin        = $_SESSION['pdf_fecha_fin'];

    // Recupero los fichajes del período
    $resultado = $conexion->query("SELECT * FROM fichajes 
        WHERE usuario_id = '$id_trabajador'
        AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ORDER BY fecha ASC, tipo ASC");

    // Nombres de los tipos de fichaje
    $nombres_tipo = [
        'entrada_1' => 'Entrada mañana',
        'salida_1'  => 'Salida mañana',
        'entrada_2' => 'Entrada tarde',
        'salida_2'  => 'Salida tarde'
    ];

    //-----------------------------------------------------|
    //------------- GENERO EL PDF ------------------------|
    //-----------------------------------------------------|

    $pdf = new FPDF();
    $pdf->AddPage();

    // Título del informe
    $pdf->SetFont('Courier', 'B', 16);
    $pdf->Cell(0, 10, 'TimeTrack - Informe de fichajes', 1, 1, 'C');
    $pdf->Ln(5);

    // Datos del trabajador y período
    $pdf->SetFont('Courier', 'B', 11);
    $pdf->Cell(0, 8, 'Trabajador: ' . $nombre_trabajador, 0, 1, 'L');
    $pdf->Cell(0, 8, 'Periodo: ' . date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1, 'L');
    $pdf->Ln(5);

    // Cabecera de la tabla
    $pdf->SetFont('Courier', 'B', 10);
    $pdf->Cell(30, 8, 'FECHA',    1, 0, 'C');
    $pdf->Cell(45, 8, 'FICHAJE',  1, 0, 'C');
    $pdf->Cell(35, 8, 'HORA',     1, 0, 'C');
    $pdf->Cell(40, 8, 'DIFERENCIA', 1, 1, 'C');

    // Variables para el resumen
    $total_extra = 0;
    $total_menos = 0;

    // Filas de fichajes
    $pdf->SetFont('Courier', '', 9);

    if ($resultado->num_rows > 0) {

        while ($fila = $resultado->fetch_assoc()) {

            $fecha     = date('d/m/Y', strtotime($fila['fecha']));
            $tipo      = isset($nombres_tipo[$fila['tipo']]) ? $nombres_tipo[$fila['tipo']] : $fila['tipo'];
            $hora      = substr($fila['hora_fichaje'], 0, 5);
            $dif       = $fila['minutos_diferencia'];

            // Calculo el texto de la diferencia
            if ($dif > 0) {
                $dif_texto = '+' . $dif . ' min (tarde)';
                $total_menos += $dif;
            } elseif ($dif < 0) {
                $dif_texto = $dif . ' min (extra)';
                $total_extra += abs($dif);
            } else {
                $dif_texto = 'Puntual';
            }

            $pdf->Cell(30, 7, $fecha,     1, 0, 'C');
            $pdf->Cell(45, 7, $tipo,      1, 0, 'C');
            $pdf->Cell(35, 7, $hora,      1, 0, 'C');
            $pdf->Cell(40, 7, $dif_texto, 1, 1, 'C');
        }

    } else {
        $pdf->Cell(150, 7, 'No hay fichajes en este periodo', 1, 1, 'C');
    }

    $pdf->Ln(8);

    // Resumen del período
    $pdf->SetFont('Courier', 'B', 10);
    $pdf->Cell(0, 8, 'RESUMEN DEL PERIODO', 1, 1, 'C');

    $pdf->SetFont('Courier', '', 10);
    $pdf->Cell(75, 7, 'Minutos de mas trabajados:', 1, 0, 'L');
    $pdf->Cell(75, 7, $total_extra . ' min (' . floor($total_extra / 60) . 'h ' . ($total_extra % 60) . 'min)', 1, 1, 'L');

    $pdf->Cell(75, 7, 'Minutos de menos trabajados:', 1, 0, 'L');
    $pdf->Cell(75, 7, $total_menos . ' min (' . floor($total_menos / 60) . 'h ' . ($total_menos % 60) . 'min)', 1, 1, 'L');

    $balance = $total_extra - $total_menos;
    $balance_texto = $balance > 0 ? '+' . $balance . ' min a favor del trabajador' : $balance . ' min a favor de la empresa';

    $pdf->SetFont('Courier', 'B', 10);
    $pdf->Cell(75, 7, 'Balance total:', 1, 0, 'L');
    $pdf->Cell(75, 7, $balance_texto, 1, 1, 'L');

    $pdf->Ln(5);

    // Pie del documento
    $pdf->SetFont('Courier', 'I', 8);
    $pdf->Cell(0, 6, 'Generado el ' . date('d/m/Y') . ' - TimeTrack by Pedro Perez Alfonso', 0, 1, 'C');

    // Genero el PDF igual que en el examen
    $pdf->Output();
}

desconectar($conexion);
?>