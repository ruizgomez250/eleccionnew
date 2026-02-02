<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Equipo;
use Illuminate\Http\Request;
use TCPDF;
use Illuminate\Support\Facades\Auth;

class ReportesController extends Controller
{
    public function index($equipoId = null)
    {
        // Equipos del sistema del usuario
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        // Traer dirigentes filtrando por sistema y por equipo si se pasa el ID
        $dirigentes = Dirigente::with('punteros.votantes', 'equipo')
            ->whereHas('equipo', function($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->when($equipoId, fn($q) => $q->where('id_equipo', $equipoId))
            ->get();

        // Calcular punteros_count y votantes_count por dirigente
        foreach ($dirigentes as $dir) {
            $dir->punteros_count = $dir->punteros->count();
            $dir->votantes_count = $dir->punteros->sum(fn($p) => $p->votantes->count());
        }

        // Total general de votantes
        $totalVotantesGeneral = $dirigentes->sum(fn($d) => $d->votantes_count);

        return view('reportes.pordirigente', compact('equipos', 'equipoId', 'dirigentes', 'totalVotantesGeneral'));
    }

    public function votantesPorDirigente($idDirigente)
    {
        // Solo dirigente del sistema del usuario
        $dirigente = Dirigente::with(['punteros.votantes'])
            ->whereHas('equipo', function($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->findOrFail($idDirigente);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Configuración general
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetMargins(5, 15, 5);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($dirigente->punteros as $puntero) {

            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 6, 'REPORTE DE VOTANTES', 0, 1, 'C');

            $pdf->Ln(2);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(30, 6, 'Dirigente:', 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, $dirigente->nombre, 0, 1);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(30, 6, 'Puntero:', 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, $puntero->nombre, 0, 1);

            $pdf->Ln(4);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(180, 180, 180);

            $pdf->Cell(22, 8, 'Cédula', 1, 0, 'C', true);
            $pdf->Cell(48, 8, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ciudad', 1, 0, 'C', true);
            $pdf->Cell(55, 8, 'Escuela', 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Mesa', 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Orden', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 7.5);
            $fill = false;

            if ($puntero->votantes->isEmpty()) {
                $pdf->Cell(180, 8, 'No existen votantes para este puntero', 1, 1, 'C');
            } else {
                foreach ($puntero->votantes as $votante) {
                    $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

                    $cedula  = number_format($votante->cedula, 0, ',', '.');
                    $nombre  = $votante->nombre ?? '';
                    $ciudad  = $votante->ciudad ?? '';
                    $escuela = $votante->escuela ?? '';

                    $minHeight = 7;
                    $hNombre  = $pdf->getStringHeight(48, $nombre);
                    $hCiudad  = $pdf->getStringHeight(25, $ciudad);
                    $hEscuela = $pdf->getStringHeight(55, $escuela);
                    $rowHeight = max($minHeight, $hNombre, $hCiudad, $hEscuela);

                    $pdf->MultiCell(22, $rowHeight, $cedula, 1, 'C', true, 0);
                    $pdf->MultiCell(48, $rowHeight, $nombre, 1, 'L', true, 0);
                    $pdf->MultiCell(25, $rowHeight, $ciudad, 1, 'L', true, 0);
                    $pdf->MultiCell(55, $rowHeight, $escuela, 1, 'L', true, 0);
                    $pdf->MultiCell(15, $rowHeight, $votante->mesa, 1, 'C', true, 0);
                    $pdf->MultiCell(15, $rowHeight, $votante->orden, 1, 'C', true, 1);

                    $fill = !$fill;
                }
            }
        }

        $pdf->Output('reporte_votantes_por_dirigente.pdf', 'I');
        exit;
    }
}
