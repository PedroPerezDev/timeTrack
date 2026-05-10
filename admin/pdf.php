<?php
/*
 * Generación de PDF de fichajes
 * Solo PHP, sin HTML mezclado
 */

session_start();

if (!isset($_SESSION['user']) || $_SESSION['rol'] != "admin") {
    header("Location: ../index.php");
    exit;
}

require "../libs/fpdf/fpdf.php";
include "../config.php";

$conexion = conectar();

/*
 * Función para convertir caracteres especiales
 * FPDF usa ISO-8859-1, PHP usa UTF-8
 * sin esta conversión las tildes y ñ no se muestran bien
 */
function utf8($texto) {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT', $texto);
}

if (isset($_SESSION['pdf_trabajador_id'])) {

    $id_trabajador     = $_SESSION['pdf_trabajador_id'];
    $nombre_trabajador = $_SESSION['pdf_trabajador_nombre'];
    $fecha_inicio      = $_SESSION['pdf_fecha_inicio'];
    $fecha_fin         = $_SESSION['pdf_fecha_fin'];

    //-----------------------------------------------------|
    //-------- CÁLCULO HORAS SEMANALES PREVISTAS ----------|
    //-----------------------------------------------------|

    $horarios_semana   = $conexion->query("SELECT * FROM horarios 
        WHERE usuario_id = '$id_trabajador'");
    $minutos_semanales = 0;

    while ($h = $horarios_semana->fetch_assoc()) {
        $entrada_1 = strtotime($h['hora_entrada_1']);
        $salida_1  = strtotime($h['hora_salida_1']);
        $minutos_semanales += ($salida_1 - $entrada_1) / 60;

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

    $resultado = $conexion->query("SELECT * FROM fichajes 
        WHERE usuario_id = '$id_trabajador'
        AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'
        ORDER BY fecha ASC, tipo ASC");

    $dias_trabajados = $conexion->query("SELECT COUNT(DISTINCT fecha) as total 
        FROM fichajes 
        WHERE usuario_id = '$id_trabajador'
        AND fecha BETWEEN '$fecha_inicio' AND '$fecha_fin'")->fetch_assoc()['total'];

    $semanas_periodo       = $dias_trabajados / 5;
    $minutos_previstos     = $minutos_semanales * $semanas_periodo;
    $horas_previstas_total = floor($minutos_previstos / 60);
    $min_previstos_resto   = $minutos_previstos % 60;

    $nombres_tipo = [
        'entrada_1' => utf8('Entrada mañana'),
        'salida_1'  => utf8('Salida mañana'),
        'entrada_2' => 'Entrada tarde',
        'salida_2'  => 'Salida tarde'
    ];

    //-----------------------------------------------------|
    //------------- GENERO EL PDF ------------------------|
    //-----------------------------------------------------|

    $pdf = new FPDF();
    $pdf->AddPage();
    $pdf->SetMargins(15, 15, 15);
    $pdf->SetAutoPageBreak(true, 15);

    // Anchos de columnas ajustados para página vertical
    $col_fecha    = 25;
    $col_tipo     = 35;
    $col_prevista = 28;
    $col_fichada  = 28;
    $col_dif      = 28;
    $total        = $col_fecha + $col_tipo + $col_prevista + $col_fichada + $col_dif;

    // Calculo el margen para centrar en página vertical A4
    $ancho_pagina = 210;
    $margen_tabla = ($ancho_pagina - $total) / 2;

    //-----------------------------------------------------|
    //------------- CABECERA DEL DOCUMENTO ---------------|
    //-----------------------------------------------------|

    $pdf->SetFillColor(15, 110, 86);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 16);
    $pdf->Cell(0, 14, utf8('TimeTrack — Informe de fichajes'), 0, 1, 'C', true);
    $pdf->Ln(4);

    $pdf->SetTextColor(26, 46, 38);
    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(40, 7, utf8('Trabajador:'), 0, 0);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 7, utf8($nombre_trabajador), 0, 1);

    $pdf->SetFont('Helvetica', 'B', 11);
    $pdf->Cell(40, 7, utf8('Período:'), 0, 0);
    $pdf->SetFont('Helvetica', '', 11);
    $pdf->Cell(0, 7, date('d/m/Y', strtotime($fecha_inicio)) . ' - ' . date('d/m/Y', strtotime($fecha_fin)), 0, 1);

    $pdf->Ln(6);

    //-----------------------------------------------------|
    //------------- TABLA DE FICHAJES --------------------|
    //-----------------------------------------------------|

    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(15, 110, 86);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 9);
    $pdf->Cell($col_fecha,    8, 'FECHA',          1, 0, 'C', true);
    $pdf->Cell($col_tipo,     8, 'FICHAJE',         1, 0, 'C', true);
    $pdf->Cell($col_prevista, 8, 'HORA PREVISTA',   1, 0, 'C', true);
    $pdf->Cell($col_fichada,  8, 'HORA FICHADA',    1, 0, 'C', true);
    $pdf->Cell($col_dif,      8, 'DIFERENCIA',      1, 1, 'C', true);

    $total_extra  = 0;
    $total_menos  = 0;
    $fila_par     = false;
    $fecha_actual = '';

    $pdf->SetFont('Helvetica', '', 9);

    if ($resultado->num_rows > 0) {

        while ($fila = $resultado->fetch_assoc()) {

            if ($fila['fecha'] != $fecha_actual) {
                $fila_par     = !$fila_par;
                $fecha_actual = $fila['fecha'];
            }

            if ($fila_par) {
                $pdf->SetFillColor(240, 249, 245);
            } else {
                $pdf->SetFillColor(255, 255, 255);
            }

            // Recupero la hora prevista del horario
            $dia_semana  = date('N', strtotime($fila['fecha']));
            $horario_dia = $conexion->query("SELECT * FROM horarios 
                WHERE usuario_id = '$id_trabajador' 
                AND dia_semana = '$dia_semana'")->fetch_assoc();

            $campo_hora    = 'hora_' . $fila['tipo'];
            $hora_prevista = $horario_dia ? substr($horario_dia[$campo_hora], 0, 5) : '--:--';

            $fecha = date('d/m/Y', strtotime($fila['fecha']));
            $tipo  = isset($nombres_tipo[$fila['tipo']]) ? $nombres_tipo[$fila['tipo']] : $fila['tipo'];
            $hora  = substr($fila['hora_fichaje'], 0, 5);
            $dif   = $fila['minutos_diferencia'];

            if ($dif > 0) {
                $dif_texto = '+' . $dif . ' min';
                $total_extra += $dif;
            } elseif ($dif < 0) {
                $dif_texto = $dif . ' min';
                $total_menos += abs($dif);
            } else {
                $dif_texto = 'Puntual';
            }

            $pdf->SetX($margen_tabla);
            $pdf->SetTextColor(26, 46, 38);
            $pdf->Cell($col_fecha,    7, $fecha,         1, 0, 'C', true);
            $pdf->Cell($col_tipo,     7, $tipo,          1, 0, 'L', true);
            $pdf->Cell($col_prevista, 7, $hora_prevista, 1, 0, 'C', true);
            $pdf->Cell($col_fichada,  7, $hora,          1, 0, 'C', true);

            if ($dif > 0) {
                $pdf->SetTextColor(37, 99, 235);
            } elseif ($dif < 0) {
                $pdf->SetTextColor(220, 38, 38);
            } else {
                $pdf->SetTextColor(15, 110, 86);
            }

            $pdf->Cell($col_dif, 7, $dif_texto, 1, 1, 'C', true);
        }

    } else {
        $pdf->SetX($margen_tabla);
        $pdf->SetTextColor(26, 46, 38);
        $pdf->Cell($total, 7, utf8('No hay fichajes en este período'), 1, 1, 'C');
    }

    $pdf->Ln(8);

    //-----------------------------------------------------|
    //------------- RESUMEN DEL PERÍODO ------------------|
    //-----------------------------------------------------|

    $mitad = $total / 2;

    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(15, 110, 86);
    $pdf->SetTextColor(255, 255, 255);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell($total, 9, utf8('RESUMEN DEL PERÍODO'), 0, 1, 'C', true);

    // Horas semanales previstas
    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(240, 249, 245);
    $pdf->SetTextColor(26, 46, 38);
    $pdf->SetFont('Helvetica', '', 10);
    $pdf->Cell($mitad, 8, utf8('Horas semanales previstas:'), 1, 0, 'L', true);
    $pdf->Cell($mitad, 8, $horas_semanales . 'h ' . $minutos_restantes . 'min por semana', 1, 1, 'C', true);

    // Horas totales previstas en el período
    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(26, 46, 38);
    $pdf->Cell($mitad, 8, utf8('Horas totales previstas en el período:'), 1, 0, 'L', true);
    $pdf->Cell($mitad, 8, $horas_previstas_total . 'h ' . $min_previstos_resto . 'min', 1, 1, 'C', true);

    // Minutos a favor
    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(240, 249, 245);
    $pdf->SetTextColor(26, 46, 38);
    $pdf->Cell($mitad, 8, utf8('Minutos a favor del trabajador:'), 1, 0, 'L', true);
    $pdf->SetTextColor(37, 99, 235);
    $pdf->Cell($mitad, 8, '+' . $total_extra . ' min (' . floor($total_extra/60) . 'h ' . ($total_extra%60) . 'min)', 1, 1, 'C', true);

    // Minutos en contra
    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(255, 255, 255);
    $pdf->SetTextColor(26, 46, 38);
    $pdf->Cell($mitad, 8, utf8('Minutos en contra del trabajador:'), 1, 0, 'L', true);
    $pdf->SetTextColor(220, 38, 38);
    $pdf->Cell($mitad, 8, '-' . $total_menos . ' min (' . floor($total_menos/60) . 'h ' . ($total_menos%60) . 'min)', 1, 1, 'C', true);

    // Balance total
    $balance     = $total_extra - $total_menos;
    $bal_horas   = floor(abs($balance) / 60);
    $bal_minutos = abs($balance) % 60;

    $pdf->SetX($margen_tabla);
    $pdf->SetFillColor(240, 249, 245);
    $pdf->SetTextColor(26, 46, 38);
    $pdf->SetFont('Helvetica', 'B', 10);
    $pdf->Cell($mitad, 8, 'Balance total:', 1, 0, 'L', true);

    if ($balance > 0) {
        $pdf->SetTextColor(37, 99, 235);
        $bal_texto = '+' . $bal_horas . 'h ' . $bal_minutos . 'min a favor del trabajador';
    } elseif ($balance < 0) {
        $pdf->SetTextColor(220, 38, 38);
        $bal_texto = '-' . $bal_horas . 'h ' . $bal_minutos . 'min en contra del trabajador';
    } else {
        $pdf->SetTextColor(15, 110, 86);
        $bal_texto = 'Balance equilibrado';
    }

    $pdf->Cell($mitad, 8, utf8($bal_texto), 1, 1, 'C', true);

    $pdf->Ln(6);

    //-----------------------------------------------------|
    //------------- PIE DEL DOCUMENTO --------------------|
    //-----------------------------------------------------|

    $pdf->SetFont('Helvetica', 'I', 8);
    $pdf->SetTextColor(106, 143, 130);
    $pdf->Cell(0, 6, utf8('Generado el ' . date('d/m/Y') . ' - TimeTrack by Pedro Perez Alfonso'), 0, 1, 'C');

    $pdf->Output();
}

desconectar($conexion);
?>
