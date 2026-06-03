<?php

namespace App\Http\Controllers;

use App\Models\Votante;
use App\Models\Puntero;
use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\Sistema;
use App\Models\CiudadElectoral;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCPDF;

class DuplicadosEntreSistemasController extends Controller
{
    public function index(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $userId = Auth::id();
        $esUsuarioPrivilegiado = in_array($userId, [1, 4]);
        $sistemasMap = Sistema::pluck('nombre', 'id')->toArray();

        $sistemaConsulta = $sistemaUsuario;

        if ($esUsuarioPrivilegiado) {
            $sistemaSeleccionado = $request->input('sistema_id');
            if ($sistemaSeleccionado && $sistemaSeleccionado !== 'todos') {
                $sistemaConsulta = $sistemaSeleccionado;
            }
        }

        $votantesDuplicados = $this->getVotantesDuplicados($sistemaConsulta, $sistemasMap);
        $punterosDuplicados = $this->getPunterosDuplicados($sistemaConsulta, $sistemasMap);
        $dirigentesDuplicados = $this->getDirigentesDuplicados($sistemaConsulta, $sistemasMap);

        $ciudadIds = Sistema::whereNotNull('id_ciudad_electoral')->distinct()->pluck('id_ciudad_electoral');
        $ciudades = CiudadElectoral::whereIn('id', $ciudadIds)->orderBy('descripcion')->get();
        $sistemas = Sistema::with('ciudad')->orderBy('nombre')->get();

        return view('reportes.duplicados_entre_sistemas', compact(
            'votantesDuplicados',
            'punterosDuplicados',
            'dirigentesDuplicados',
            'sistemaUsuario',
            'sistemasMap',
            'userId',
            'esUsuarioPrivilegiado',
            'sistemaConsulta',
            'ciudades',
            'sistemas'
        ));
    }

    private function getSistemaIdFromVotante($votante)
    {
        try {
            if ($votante && $votante->puntero && $votante->puntero->dirigente && $votante->puntero->dirigente->equipo) {
                return $votante->puntero->dirigente->equipo->sist;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    private function getSistemaIdFromPuntero($puntero)
    {
        try {
            if ($puntero && $puntero->dirigente && $puntero->dirigente->equipo) {
                return $puntero->dirigente->equipo->sist;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    private function getSistemaIdFromDirigente($dirigente)
    {
        try {
            if ($dirigente && $dirigente->equipo) {
                return $dirigente->equipo->sist;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }

    private function getVotantesDuplicados($sistemaUsuario, $sistemasMap)
    {
        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();
        $todosLosPunterosIds = $todosLosPunteros->pluck('id')->toArray();

        if (empty($todosLosPunterosIds)) {
            return [];
        }

        $todosLosVotantes = Votante::whereIn('idpuntero', $todosLosPunterosIds)
            ->with(['puntero', 'puntero.dirigente', 'puntero.dirigente.equipo'])
            ->get();

        if ($todosLosVotantes->isEmpty()) {
            return [];
        }

        $agrupadosPorCedula = $todosLosVotantes->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            $sistemasInvolucrados = [];
            foreach ($registros as $votante) {
                $sistemaId = $this->getSistemaIdFromVotante($votante);
                if ($sistemaId) {
                    $sistemasInvolucrados[] = $sistemaId;
                }
            }

            $sistemasUnicos = collect($sistemasInvolucrados)->unique()->filter()->values();

            if ($sistemasUnicos->count() >= 2) {
                $tieneSistemaUsuario = in_array($sistemaUsuario, $sistemasInvolucrados);
                $primerRegistro = $registros->first();

                $punterosInfo = [];
                $seenP = [];
                $dirigentesInfo = [];
                $seenD = [];
                $sistemasInfo = [];

                foreach ($sistemasUnicos as $sid) {
                    $sistemasInfo[] = [
                        'id' => $sid,
                        'nombre' => $sistemasMap[$sid] ?? 'Sistema #'.$sid
                    ];
                }

                foreach ($registros as $v) {
                    if ($v->puntero && !in_array($v->puntero->id, $seenP)) {
                        $seenP[] = $v->puntero->id;
                        $pid = $this->getSistemaIdFromVotante($v);
                        $punterosInfo[] = [
                            'nombre' => $v->puntero->nombre,
                            'cedula' => $v->puntero->cedula,
                            'sistema' => $sistemasMap[$pid] ?? 'Sistema #'.$pid,
                            'sistema_id' => $pid,
                            'dirigente' => $v->puntero->dirigente->nombre ?? 'N/A',
                            'equipo' => $v->puntero->equipo->descripcion ?? ($v->puntero->dirigente->equipo->descripcion ?? 'N/A'),
                        ];
                    }
                    if ($v->puntero && $v->puntero->dirigente && !in_array($v->puntero->dirigente->id, $seenD)) {
                        $seenD[] = $v->puntero->dirigente->id;
                        $did = $this->getSistemaIdFromVotante($v);
                        $dirigentesInfo[] = [
                            'nombre' => $v->puntero->dirigente->nombre,
                            'cedula' => $v->puntero->dirigente->cedula,
                            'sistema' => $sistemasMap[$did] ?? 'Sistema #'.$did
                        ];
                    }
                }

                $resultados[] = [
                    'cedula' => $cedula,
                    'nombre' => $primerRegistro->nombre,
                    'total_registros' => $registros->count(),
                    'total_sistemas' => $sistemasUnicos->count(),
                    'tiene_sistema_usuario' => $tieneSistemaUsuario,
                    'sistemas_info' => $sistemasInfo,
                    'punteros_info' => $punterosInfo,
                    'dirigentes_info' => $dirigentesInfo,
                ];
            }
        }

        return $resultados;
    }

    private function getPunterosDuplicados($sistemaUsuario, $sistemasMap)
    {
        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();

        if ($todosLosPunteros->isEmpty()) {
            return [];
        }

        $agrupadosPorCedula = $todosLosPunteros->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            if ($registros->count() < 2) continue;

            $sistemasInvolucrados = [];
            foreach ($registros as $puntero) {
                $sistemaId = $this->getSistemaIdFromPuntero($puntero);
                if ($sistemaId) {
                    $sistemasInvolucrados[] = $sistemaId;
                }
            }

            $sistemasUnicos = collect($sistemasInvolucrados)->unique()->filter()->values();

            if ($sistemasUnicos->count() >= 2) {
                $tieneSistemaUsuario = in_array($sistemaUsuario, $sistemasInvolucrados);
                $primerRegistro = $registros->first();

                $sistemasInfo = [];
                foreach ($sistemasUnicos as $sid) {
                    $sistemasInfo[] = [
                        'id' => $sid,
                        'nombre' => $sistemasMap[$sid] ?? 'Sistema #'.$sid
                    ];
                }

                $dirigentesInfo = [];
                $seenD = [];
                foreach ($registros as $p) {
                    if ($p->dirigente && !in_array($p->dirigente->id, $seenD)) {
                        $seenD[] = $p->dirigente->id;
                        $did = $this->getSistemaIdFromPuntero($p);
                        $dirigentesInfo[] = [
                            'nombre' => $p->dirigente->nombre,
                            'cedula' => $p->dirigente->cedula,
                            'sistema' => $sistemasMap[$did] ?? 'Sistema #'.$did
                        ];
                    }
                }

                $resultados[] = [
                    'cedula' => $cedula,
                    'nombre' => $primerRegistro->nombre,
                    'telefono' => $primerRegistro->telefono,
                    'total_registros' => $registros->count(),
                    'total_sistemas' => $sistemasUnicos->count(),
                    'tiene_sistema_usuario' => $tieneSistemaUsuario,
                    'sistemas_info' => $sistemasInfo,
                    'dirigentes_info' => $dirigentesInfo,
                ];
            }
        }

        return $resultados;
    }

    private function getDirigentesDuplicados($sistemaUsuario, $sistemasMap)
    {
        $todosLosDirigentes = Dirigente::with(['equipo'])->get();

        if ($todosLosDirigentes->isEmpty()) {
            return [];
        }

        $agrupadosPorCedula = $todosLosDirigentes->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            if ($registros->count() < 2) continue;

            $sistemasInvolucrados = [];
            foreach ($registros as $dirigente) {
                $sistemaId = $this->getSistemaIdFromDirigente($dirigente);
                if ($sistemaId) {
                    $sistemasInvolucrados[] = $sistemaId;
                }
            }

            $sistemasUnicos = collect($sistemasInvolucrados)->unique()->filter()->values();

            if ($sistemasUnicos->count() >= 2) {
                $tieneSistemaUsuario = in_array($sistemaUsuario, $sistemasInvolucrados);
                $primerRegistro = $registros->first();

                $sistemasInfo = [];
                foreach ($sistemasUnicos as $sid) {
                    $sistemasInfo[] = [
                        'id' => $sid,
                        'nombre' => $sistemasMap[$sid] ?? 'Sistema #'.$sid
                    ];
                }

                $equiposInfo = [];
                $seenE = [];
                foreach ($registros as $d) {
                    if ($d->equipo && !in_array($d->equipo->id, $seenE)) {
                        $seenE[] = $d->equipo->id;
                        $equiposInfo[] = [
                            'nombre' => $d->equipo->descripcion,
                            'sistema' => $sistemasMap[$this->getSistemaIdFromDirigente($d)] ?? 'Sistema #'.$this->getSistemaIdFromDirigente($d)
                        ];
                    }
                }

                $resultados[] = [
                    'cedula' => $cedula,
                    'nombre' => $primerRegistro->nombre,
                    'telefono' => $primerRegistro->telefono,
                    'total_registros' => $registros->count(),
                    'total_sistemas' => $sistemasUnicos->count(),
                    'tiene_sistema_usuario' => $tieneSistemaUsuario,
                    'sistemas_info' => $sistemasInfo,
                    'equipos_info' => $equiposInfo,
                ];
            }
        }

        return $resultados;
    }

    public function exportarPDF(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $sistemasMap = Sistema::pluck('nombre', 'id')->toArray();
        $nombreSistema = $sistemasMap[$sistemaUsuario] ?? 'Sistema #'.$sistemaUsuario;

        $votantesDuplicados = $this->getVotantesDuplicados($sistemaUsuario, $sistemasMap);
        $punterosDuplicados = $this->getPunterosDuplicados($sistemaUsuario, $sistemasMap);
        $dirigentesDuplicados = $this->getDirigentesDuplicados($sistemaUsuario, $sistemasMap);

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetAuthor('Sistema Elecciones');
        $pdf->SetTitle('Reporte de Duplicados entre Sistemas');
        $pdf->SetMargins(5, 12, 5);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', '', 7);

        // ---- VOTANTES ----
        if (!empty($votantesDuplicados)) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'REPORTE DE DUPLICADOS ENTRE SISTEMAS', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Sistema: ' . $nombreSistema, 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 6, 'VOTANTES DUPLICADOS (' . count($votantesDuplicados) . ')', 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(180, 180, 180);
            $pdf->Cell(22, 6, 'Cedula', 1, 0, 'C', true);
            $pdf->Cell(38, 6, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(10, 6, 'Regs', 1, 0, 'C', true);
            $pdf->Cell(55, 6, 'Sistemas donde esta duplicado', 1, 0, 'C', true);
            $pdf->Cell(115, 6, 'Puntero / Dirigente / Equipo', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 6.5);
            $fill = false;
            foreach ($votantesDuplicados as $item) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $fill = !$fill;

                $sistemasStr = '';
                foreach ($item['sistemas_info'] as $s) {
                    if ($s['id'] == $sistemaUsuario) {
                        $sistemasStr .= 'duplicado (' . $s['nombre'] . '), ';
                    } else {
                        $sistemasStr .= 'otro candidato, ';
                    }
                }
                $sistemasStr = rtrim($sistemasStr, ', ');

                $detalleStr = '';
                foreach ($item['punteros_info'] as $p) {
                    $tag = isset($p['sistema_id']) && $p['sistema_id'] == $sistemaUsuario ? 'duplicado' : 'otro candidato';
                    $detalleStr .= $p['nombre'] . ' (' . $tag . ')';
                    $detalleStr .= ' -> Dirigente: ' . ($p['dirigente'] ?? 'N/A');
                    $detalleStr .= ' / Equipo: ' . ($p['equipo'] ?? 'N/A');
                    $detalleStr .= ' | ';
                }
                $detalleStr = rtrim($detalleStr, ' | ');

                $hSis = $pdf->getStringHeight(55, $sistemasStr);
                $hDet = $pdf->getStringHeight(115, $detalleStr);
                $rowH = max(6, $hSis, $hDet);

                $pdf->Cell(22, $rowH, $item['cedula'], 1, 0, 'C', true);
                $pdf->Cell(38, $rowH, substr($item['nombre'] ?? '', 0, 28), 1, 0, 'L', true);
                $pdf->Cell(10, $rowH, $item['total_registros'], 1, 0, 'C', true);
                $pdf->MultiCell(55, $rowH, $sistemasStr, 1, 'L', true, 0);
                $pdf->MultiCell(115, $rowH, $detalleStr, 1, 'L', true, 1);
            }
        }

        // ---- PUNTEROS ----
        if (!empty($punterosDuplicados)) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'REPORTE DE DUPLICADOS ENTRE SISTEMAS', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Sistema: ' . $nombreSistema, 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 6, 'PUNTEROS DUPLICADOS (' . count($punterosDuplicados) . ')', 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(180, 180, 180);
            $pdf->Cell(22, 6, 'Cedula', 1, 0, 'C', true);
            $pdf->Cell(40, 6, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(12, 6, 'Regs', 1, 0, 'C', true);
            $pdf->Cell(60, 6, 'Sistemas donde esta duplicado', 1, 0, 'C', true);
            $pdf->Cell(50, 6, 'Dirigentes involucrados', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 6.5);
            $fill = false;
            foreach ($punterosDuplicados as $item) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $fill = !$fill;

                $sistemasStr = '';
                foreach ($item['sistemas_info'] as $s) {
                    if ($s['id'] == $sistemaUsuario) {
                        $sistemasStr .= 'duplicado (' . $s['nombre'] . '), ';
                    } else {
                        $sistemasStr .= 'otro candidato, ';
                    }
                }
                $sistemasStr = rtrim($sistemasStr, ', ');

                $dirigentesStr = '';
                foreach ($item['dirigentes_info'] as $d) {
                    $dirigentesStr .= $d['nombre'] . (isset($d['sistema_id']) && $d['sistema_id'] == $sistemaUsuario ? ' (duplicado)' : ' (otro candidato)') . ', ';
                }
                $dirigentesStr = rtrim($dirigentesStr, ', ');

                $hSis = $pdf->getStringHeight(60, $sistemasStr);
                $hDir = $pdf->getStringHeight(50, $dirigentesStr);
                $rowH = max(6, $hSis, $hDir);

                $pdf->Cell(22, $rowH, $item['cedula'], 1, 0, 'C', true);
                $pdf->Cell(40, $rowH, substr($item['nombre'] ?? '', 0, 30), 1, 0, 'L', true);
                $pdf->Cell(12, $rowH, $item['total_registros'], 1, 0, 'C', true);
                $x = $pdf->GetX();
                $pdf->MultiCell(60, $rowH, $sistemasStr, 1, 'L', true, 0);
                $pdf->MultiCell(50, $rowH, $dirigentesStr, 1, 'L', true, 1);
            }
        }

        // ---- DIRIGENTES ----
        if (!empty($dirigentesDuplicados)) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'REPORTE DE DUPLICADOS ENTRE SISTEMAS', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Sistema: ' . $nombreSistema, 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(0, 6, 'DIRIGENTES DUPLICADOS (' . count($dirigentesDuplicados) . ')', 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(180, 180, 180);
            $pdf->Cell(22, 6, 'Cedula', 1, 0, 'C', true);
            $pdf->Cell(40, 6, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(12, 6, 'Regs', 1, 0, 'C', true);
            $pdf->Cell(60, 6, 'Sistemas donde esta duplicado', 1, 0, 'C', true);
            $pdf->Cell(60, 6, 'Equipos involucrados', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 6.5);
            $fill = false;
            foreach ($dirigentesDuplicados as $item) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $fill = !$fill;

                $sistemasStr = '';
                foreach ($item['sistemas_info'] as $s) {
                    if ($s['id'] == $sistemaUsuario) {
                        $sistemasStr .= 'duplicado (' . $s['nombre'] . '), ';
                    } else {
                        $sistemasStr .= 'otro candidato, ';
                    }
                }
                $sistemasStr = rtrim($sistemasStr, ', ');

                $equiposStr = '';
                foreach ($item['equipos_info'] as $e) {
                    $equiposStr .= $e['nombre'] . ' (' . $e['sistema'] . '), ';
                }
                $equiposStr = rtrim($equiposStr, ', ');

                $hSis = $pdf->getStringHeight(60, $sistemasStr);
                $hEq = $pdf->getStringHeight(60, $equiposStr);
                $rowH = max(6, $hSis, $hEq);

                $pdf->Cell(22, $rowH, $item['cedula'], 1, 0, 'C', true);
                $pdf->Cell(40, $rowH, substr($item['nombre'] ?? '', 0, 30), 1, 0, 'L', true);
                $pdf->Cell(12, $rowH, $item['total_registros'], 1, 0, 'C', true);
                $pdf->MultiCell(60, $rowH, $sistemasStr, 1, 'L', true, 0);
                $pdf->MultiCell(60, $rowH, $equiposStr, 1, 'L', true, 1);
            }
        }

        $pdf->Output('reporte_duplicados_entre_sistemas.pdf', 'I');
        exit;
    }
}
