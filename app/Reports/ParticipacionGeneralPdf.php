<?php

namespace App\Reports;

use Carbon\Carbon;
use TCPDF;

class ParticipacionGeneralPdf
{
    public function render(array $data, string $candidato): string
    {
        $pdf = new class('L', 'mm', 'A4', true, 'UTF-8', false) extends TCPDF {
            public function Footer()
            {
                $this->SetY(-12);
                $this->SetFont('dejavusans', '', 8);
                $this->SetTextColor(100, 110, 125);
                $this->Cell(0, 6, 'Participación general | Página '.$this->getAliasNumPage().' de '.$this->getAliasNbPages(), 0, 0, 'R');
            }
        };
        $pdf->SetCreator('Sistema de reportes');
        $pdf->SetTitle('Participación general - '.$candidato);
        $pdf->setPrintHeader(false);
        $pdf->SetMargins(15, 14, 15);
        $pdf->SetAutoPageBreak(true, 19);
        $pdf->SetFont('dejavusans', '', 10);
        $fecha = Carbon::parse($data['generado_en'])->format('d/m/Y H:i:s');
        $heading = function (string $section) use ($pdf, $candidato, $fecha) {
            $pdf->AddPage();
            $pdf->SetTextColor(24, 48, 77);
            $pdf->SetFont('dejavusans', 'B', 18);
            $pdf->MultiCell(267, 10, 'Participación general', 0, 'L');
            $pdf->SetFont('dejavusans', '', 10);
            $pdf->MultiCell(267, 6, $candidato.' | Datos al '.$fecha, 0, 'L');
            $pdf->Ln(3);
            $pdf->SetFont('dejavusans', 'B', 12);
            $pdf->MultiCell(267, 8, $section, 0, 'L');
            $pdf->SetFont('dejavusans', '', 9);
        };
        $number = static fn ($value) => number_format($value, 0, ',', '.');
        $heading('Resumen');
        foreach ([
            'Personas únicas registradas' => $data['resumen']['total'],
            'Con participación registrada' => $data['resumen']['registrados'],
            'Sin participación registrada' => $data['resumen']['sin_registro'],
        ] as $label => $value) {
            $pdf->SetFillColor(241, 245, 249);
            $pdf->Cell(210, 12, $label, 0, 0, 'L', true);
            $pdf->Cell(57, 12, $number($value), 0, 1, 'R', true);
            $pdf->Ln(2);
        }
        $percent = max(0, min(100, (float) $data['resumen']['porcentaje']));
        $pdf->Ln(6);
        $pdf->SetFont('dejavusans', 'B', 12);
        $pdf->Cell(267, 10, 'Participación registrada: '.number_format($percent, 1, ',', '.').'%', 0, 1);
        $y = $pdf->GetY();
        $pdf->SetFillColor(222, 226, 230);
        $pdf->Rect(15, $y, 267, 9, 'F');
        $pdf->SetFillColor(0, 110, 210);
        if ($percent > 0) $pdf->Rect(15, $y, 267 * $percent / 100, 9, 'F');
        $pdf->SetY($y + 13);
        $pdf->SetFont('dejavusans', '', 9);
        $pdf->MultiCell(267, 6, 'Azul: con participación registrada. Gris: sin registro de participación.', 0, 'L');
        $pdf->Ln(5);
        $pdf->MultiCell(267, 6, 'Cada persona se cuenta una vez dentro de cada grupo. Si figura en varios grupos, las filas no suman necesariamente el total general. Sin registro no confirma que una persona no haya votado. Este documento sólo contiene datos agregados.', 0, 'L');

        foreach (['dirigentes' => 'Por dirigente', 'punteros' => 'Por puntero'] as $type => $section) {
            $columns = $type === 'punteros'
                ? ['Puntero' => 72, 'Dirigente' => 72, 'Personas únicas' => 32, 'Con registro' => 32, 'Sin registro' => 32, 'Participación' => 27]
                : ['Dirigente' => 103, 'Personas únicas' => 40, 'Con registro' => 42, 'Sin registro' => 42, 'Participación' => 40];
            $tableHeader = function () use ($pdf, $columns) {
                $pdf->SetFont('dejavusans', 'B', 8);
                $pdf->SetFillColor(226, 235, 245);
                $x = 15;
                $y = $pdf->GetY();
                foreach ($columns as $label => $width) {
                    $pdf->MultiCell($width, 13, $label, 0, 'L', true, 0, $x, $y);
                    $x += $width;
                }
                $pdf->SetXY(15, $y + 13);
                $pdf->SetFont('dejavusans', '', 8);
            };
            $heading($section.' ('.count($data[$type]).' grupos)');
            $tableHeader();
            if (!$data[$type]) $pdf->MultiCell(267, 12, 'No hay grupos para mostrar.', 0, 'L');
            foreach ($data[$type] as $index => $row) {
                $values = [$row['nombre']];
                if ($type === 'punteros') $values[] = $row['dirigente'];
                foreach (['total', 'registrados', 'sin_registro'] as $key) $values[] = $number($row[$key]);
                $values[] = number_format($row['porcentaje'], 1, ',', '.').'%';
                $widths = array_values($columns);
                $height = 10;
                foreach ($values as $i => $value) $height = max($height, $pdf->getStringHeight($widths[$i], (string) $value) + 2);
                if ($pdf->GetY() + $height > $pdf->getPageHeight() - 19) {
                    $heading($section.' (continuación)');
                    $tableHeader();
                }
                $pdf->SetFillColor(...($index % 2 ? [244, 247, 250] : [255, 255, 255]));
                $x = 15;
                $y = $pdf->GetY();
                foreach ($values as $i => $value) {
                    $pdf->MultiCell($widths[$i], $height, (string) $value, 0, 'L', true, 0, $x, $y);
                    $x += $widths[$i];
                }
                $pdf->SetXY(15, $y + $height);
            }
        }

        return $pdf->Output('participacion-general.pdf', 'S');
    }
}
