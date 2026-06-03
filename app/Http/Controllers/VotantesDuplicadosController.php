<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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

class VotantesDuplicadosController extends Controller
{
    public function index(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $userId = Auth::id();
        $esUsuarioPrivilegiado = in_array($userId, [1, 4]);

        $sistemaConsulta = $sistemaUsuario;
        $modoTodos = false;

        if ($esUsuarioPrivilegiado) {
            $sistemaSeleccionado = $request->input('sistema_id');
            if ($sistemaSeleccionado === 'todos') {
                $modoTodos = true;
            } elseif ($sistemaSeleccionado) {
                $sistemaConsulta = $sistemaSeleccionado;
            }
        }

        $sistemasMap = Sistema::pluck('nombre', 'id')->toArray();

        $votantesDuplicados = $this->getVotantesDuplicados($sistemaConsulta, $modoTodos, $sistemasMap);
        $punterosDuplicados = $this->getPunterosDuplicados($sistemaConsulta, $modoTodos, $sistemasMap);
        $dirigentesDuplicados = $this->getDirigentesDuplicados($sistemaConsulta, $modoTodos, $sistemasMap);

        $totalCedulasDuplicadas = count($votantesDuplicados);
        $totalConDuplicadoPuntero = count($punterosDuplicados);
        $totalConDuplicadoDirigente = count($dirigentesDuplicados);

        $ciudadIds = Sistema::whereNotNull('id_ciudad_electoral')->distinct()->pluck('id_ciudad_electoral');
        $ciudades = CiudadElectoral::whereIn('id', $ciudadIds)->orderBy('descripcion')->get();
        $sistemas = Sistema::with('ciudad')->orderBy('nombre')->get();

        return view('reportes.votantes_duplicados', compact(
            'votantesDuplicados',
            'punterosDuplicados',
            'dirigentesDuplicados',
            'totalCedulasDuplicadas',
            'totalConDuplicadoPuntero',
            'totalConDuplicadoDirigente',
            'sistemaUsuario',
            'userId',
            'esUsuarioPrivilegiado',
            'modoTodos',
            'sistemaConsulta',
            'ciudades',
            'sistemas'
        ));
    }

    private function getVotantesDuplicados($sistemaConsulta, $modoTodos, $sistemasMap)
    {
        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();
        $todosLosPunterosIds = $todosLosPunteros->pluck('id')->toArray();

        if (empty($todosLosPunterosIds)) return [];

        $todosLosVotantes = Votante::whereIn('idpuntero', $todosLosPunterosIds)
            ->with(['puntero', 'puntero.dirigente', 'puntero.dirigente.equipo'])
            ->get();

        if ($todosLosVotantes->isEmpty()) return [];

        $agrupadosPorCedula = $todosLosVotantes->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            $tieneRegistroEnSistemaConsulta = false;
            $sistemasInvolucrados = [];

            foreach ($registros as $votante) {
                $sistemaId = $this->getSistemaIdFromVotante($votante);
                $sistemasInvolucrados[] = $sistemaId;
                if ($sistemaId == $sistemaConsulta) {
                    $tieneRegistroEnSistemaConsulta = true;
                }
            }

            $sistemasDeEstaCedula = collect($sistemasInvolucrados)->unique()->filter()->values();

            if ($modoTodos) {
                if ($sistemasDeEstaCedula->count() < 2) continue;
            } else {
                if (!$tieneRegistroEnSistemaConsulta || $sistemasDeEstaCedula->count() < 2) continue;
            }

            $primerRegistro = $registros->first();
            $totalRegistros = $registros->count();

            $punterosUnicos = $registros->pluck('idpuntero')->unique();
            $totalPunteros = $punterosUnicos->count();
            $duplicadoPorPuntero = $totalPunteros > 1;

            $dirigentesUnicos = collect();
            foreach ($registros as $votante) {
                if ($votante->puntero && $votante->puntero->dirigente) {
                    $dirigentesUnicos->push($votante->puntero->dirigente->id);
                }
            }
            $dirigentesUnicos = $dirigentesUnicos->unique();
            $totalDirigentes = $dirigentesUnicos->count();
            $duplicadoPorDirigente = $totalDirigentes > 1;

            $registroEnSistemaConsulta = $registros->first(function($v) use ($sistemaConsulta) {
                return $this->getSistemaIdFromVotante($v) == $sistemaConsulta;
            });

            $sistemaIdsUnicos = collect($sistemasInvolucrados)->unique()->values()->toArray();
            $sistemasInfo = [];
            foreach ($sistemaIdsUnicos as $sid) {
                $sistemasInfo[] = [
                    'id' => $sid,
                    'nombre' => $sistemasMap[$sid] ?? 'Sistema #'.$sid
                ];
            }

            $punterosInfo = [];
            $seenPIds = [];
            foreach ($registros as $v) {
                if ($v->puntero && !in_array($v->puntero->id, $seenPIds)) {
                    $seenPIds[] = $v->puntero->id;
                    $pid = $this->getSistemaIdFromVotante($v);
                    $punterosInfo[] = [
                        'nombre' => $v->puntero->nombre,
                        'dirigente' => $v->puntero->dirigente->nombre ?? 'N/A',
                        'sistema' => $sistemasMap[$pid] ?? 'Sistema #'.$pid,
                        'sistema_id' => $pid,
                    ];
                }
            }

            $dirigentesInfo = [];
            $seenDIds = [];
            foreach ($registros as $v) {
                if ($v->puntero && $v->puntero->dirigente && !in_array($v->puntero->dirigente->id, $seenDIds)) {
                    $seenDIds[] = $v->puntero->dirigente->id;
                    $did = $this->getSistemaIdFromVotante($v);
                    $dirigentesInfo[] = [
                        'nombre' => $v->puntero->dirigente->nombre,
                        'sistema' => $sistemasMap[$did] ?? 'Sistema #'.$did,
                        'sistema_id' => $did,
                    ];
                }
            }

            $resultados[] = (object)[
                'cedula' => $cedula,
                'nombre' => $primerRegistro->nombre,
                'direccion' => $primerRegistro->direccion,
                'mesa' => $primerRegistro->mesa,
                'orden' => $primerRegistro->orden,
                'partido' => $primerRegistro->partido,
                'puntero' => $registroEnSistemaConsulta->puntero->nombre ?? 'N/A',
                'dirigente' => $registroEnSistemaConsulta->puntero->dirigente->nombre ?? 'N/A',
                'equipo' => $registroEnSistemaConsulta->puntero->equipo->descripcion ?? 'N/A',
                'total_registros' => $totalRegistros,
                'duplicado_por_puntero' => $duplicadoPorPuntero,
                'duplicado_por_dirigente' => $duplicadoPorDirigente,
                'total_punteros' => $totalPunteros,
                'total_dirigentes' => $totalDirigentes,
                'sistemas_info' => $sistemasInfo,
                'punteros_info' => $punterosInfo,
                'dirigentes_info' => $dirigentesInfo,
            ];
        }

        return $resultados;
    }

    private function getPunterosDuplicados($sistemaConsulta, $modoTodos, $sistemasMap)
    {
        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();
        if ($todosLosPunteros->isEmpty()) return [];

        $agrupadosPorCedula = $todosLosPunteros->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            if ($registros->count() < 2) continue;

            $sistemasInvolucrados = [];
            foreach ($registros as $puntero) {
                $sistemaId = $this->getSistemaIdFromPuntero($puntero);
                if ($sistemaId) $sistemasInvolucrados[] = $sistemaId;
            }

            $sistemasUnicos = collect($sistemasInvolucrados)->unique()->filter()->values();
            if ($sistemasUnicos->count() < 2) continue;

            $tieneSistemaUsuario = in_array($sistemaConsulta, $sistemasInvolucrados);
            if (!$modoTodos && !$tieneSistemaUsuario) continue;

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
                        'sistema' => $sistemasMap[$did] ?? 'Sistema #'.$did,
                        'sistema_id' => $did,
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

        return $resultados;
    }

    private function getDirigentesDuplicados($sistemaConsulta, $modoTodos, $sistemasMap)
    {
        $todosLosDirigentes = Dirigente::with(['equipo'])->get();
        if ($todosLosDirigentes->isEmpty()) return [];

        $agrupadosPorCedula = $todosLosDirigentes->groupBy('cedula');
        $resultados = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            if ($registros->count() < 2) continue;

            $sistemasInvolucrados = [];
            foreach ($registros as $dirigente) {
                $sistemaId = $this->getSistemaIdFromDirigente($dirigente);
                if ($sistemaId) $sistemasInvolucrados[] = $sistemaId;
            }

            $sistemasUnicos = collect($sistemasInvolucrados)->unique()->filter()->values();
            if ($sistemasUnicos->count() < 2) continue;

            $tieneSistemaUsuario = in_array($sistemaConsulta, $sistemasInvolucrados);
            if (!$modoTodos && !$tieneSistemaUsuario) continue;

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

        return $resultados;
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
    
    public function indexInterno(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $userId = Auth::id();
        $esUsuarioPrivilegiado = in_array($userId, [1, 4]);

        $sistemaConsulta = $sistemaUsuario;

        if ($esUsuarioPrivilegiado) {
            $sistemaSeleccionado = $request->input('sistema_id');
            if ($sistemaSeleccionado && $sistemaSeleccionado !== 'todos') {
                $sistemaConsulta = $sistemaSeleccionado;
            }
        }

        $punterosDelSistema = Puntero::whereHas('dirigente.equipo', function ($q) use ($sistemaConsulta) {
                $q->where('sist', $sistemaConsulta);
            })
            ->with(['dirigente', 'dirigente.equipo'])
            ->get();

        $idsPunteros = $punterosDelSistema->pluck('id')->toArray();

        if (empty($idsPunteros)) {
            return $this->vistaVaciaInterno($userId, $esUsuarioPrivilegiado);
        }

        $votantes = Votante::whereIn('idpuntero', $idsPunteros)
            ->with(['puntero.dirigente', 'puntero.equipo'])
            ->get();

        if ($votantes->isEmpty()) {
            return $this->vistaVaciaInterno($userId, $esUsuarioPrivilegiado);
        }

        $agrupados = $votantes->groupBy('cedula')->filter(fn($regs) => $regs->count() > 1);

        $resultados = [];

        foreach ($agrupados as $cedula => $registros) {
            $primero = $registros->first();

            $punterosUnicos = $registros->pluck('idpuntero')->unique();
            $totalPunteros = $punterosUnicos->count();
            $dupPuntero = $totalPunteros > 1;

            $dirigentesUnicos = collect();
            foreach ($registros as $v) {
                if ($v->puntero && $v->puntero->dirigente) {
                    $dirigentesUnicos->push($v->puntero->dirigente->id);
                }
            }
            $dirigentesUnicos = $dirigentesUnicos->unique();
            $totalDirigentes = $dirigentesUnicos->count();
            $dupDirigente = $totalDirigentes > 1;

            $duplicadoSimple = !$dupPuntero && !$dupDirigente;

            $pInfo = [];
            $seenP = [];
            foreach ($registros as $v) {
                if ($v->puntero && !in_array($v->puntero->id, $seenP)) {
                    $seenP[] = $v->puntero->id;
                    $pInfo[] = [
                        'nombre' => $v->puntero->nombre,
                        'dirigente' => $v->puntero->dirigente->nombre ?? 'N/A',
                        'equipo' => $v->puntero->equipo->descripcion ?? ($v->puntero->dirigente->equipo->descripcion ?? 'N/A'),
                    ];
                }
            }

            $dInfo = [];
            $seenD = [];
            foreach ($registros as $v) {
                if ($v->puntero && $v->puntero->dirigente && !in_array($v->puntero->dirigente->id, $seenD)) {
                    $seenD[] = $v->puntero->dirigente->id;
                    $dInfo[] = ['nombre' => $v->puntero->dirigente->nombre];
                }
            }

            $resultados[] = (object)[
                'cedula' => $cedula,
                'nombre' => $primero->nombre,
                'direccion' => $primero->direccion,
                'mesa' => $primero->mesa,
                'orden' => $primero->orden,
                'puntero' => $primero->puntero->nombre ?? 'N/A',
                'dirigente' => $primero->puntero->dirigente->nombre ?? 'N/A',
                'total_registros' => $registros->count(),
                'duplicado_simple' => $duplicadoSimple,
                'duplicado_por_puntero' => $dupPuntero,
                'duplicado_por_dirigente' => $dupDirigente,
                'total_punteros' => $totalPunteros,
                'total_dirigentes' => $totalDirigentes,
                'punteros_info' => $pInfo,
                'dirigentes_info' => $dInfo,
            ];
        }

        $totalCedulasDuplicadas = count($resultados);
        $totalConDuplicadoPuntero = collect($resultados)->where('duplicado_por_puntero', true)->count();
        $totalConDuplicadoDirigente = collect($resultados)->where('duplicado_por_dirigente', true)->count();
        $totalDuplicadoSimple = collect($resultados)->where('duplicado_simple', true)->count();

        $ciudadIds = Sistema::whereNotNull('id_ciudad_electoral')->distinct()->pluck('id_ciudad_electoral');
        $ciudades = CiudadElectoral::whereIn('id', $ciudadIds)->orderBy('descripcion')->get();
        $sistemas = Sistema::with('ciudad')->orderBy('nombre')->get();

        return view('reportes.votantes_duplicados_interno', compact(
            'resultados',
            'totalCedulasDuplicadas',
            'totalConDuplicadoPuntero',
            'totalConDuplicadoDirigente',
            'totalDuplicadoSimple',
            'sistemaUsuario',
            'esUsuarioPrivilegiado',
            'sistemaConsulta',
            'ciudades',
            'sistemas'
        ));
    }

    public function exportarPDFInterno(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $userId = Auth::id();
        $esUsuarioPrivilegiado = in_array($userId, [1, 4]);

        $sistemaConsulta = $sistemaUsuario;

        if ($esUsuarioPrivilegiado) {
            $sistemaSeleccionado = $request->input('sistema_id');
            if ($sistemaSeleccionado && $sistemaSeleccionado !== 'todos') {
                $sistemaConsulta = $sistemaSeleccionado;
            }
        }

        $punterosDelSistema = Puntero::whereHas('dirigente.equipo', function ($q) use ($sistemaConsulta) {
                $q->where('sist', $sistemaConsulta);
            })
            ->with(['dirigente', 'dirigente.equipo'])
            ->get();

        $idsPunteros = $punterosDelSistema->pluck('id')->toArray();
        $votantes = collect();
        $resultados = [];

        if (!empty($idsPunteros)) {
            $votantes = Votante::whereIn('idpuntero', $idsPunteros)
                ->with(['puntero.dirigente', 'puntero.equipo'])
                ->get();

            $agrupados = $votantes->groupBy('cedula')->filter(fn($regs) => $regs->count() > 1);

            foreach ($agrupados as $cedula => $registros) {
                $primero = $registros->first();

                $punterosUnicos = $registros->pluck('idpuntero')->unique();
                $totalPunteros = $punterosUnicos->count();
                $dupPuntero = $totalPunteros > 1;

                $dirigentesUnicos = collect();
                foreach ($registros as $v) {
                    if ($v->puntero && $v->puntero->dirigente) {
                        $dirigentesUnicos->push($v->puntero->dirigente->id);
                    }
                }
                $dirigentesUnicos = $dirigentesUnicos->unique();
                $totalDirigentes = $dirigentesUnicos->count();
                $dupDirigente = $totalDirigentes > 1;
                $duplicadoSimple = !$dupPuntero && !$dupDirigente;

                $pInfo = [];
                $seenP = [];
                foreach ($registros as $v) {
                    if ($v->puntero && !in_array($v->puntero->id, $seenP)) {
                        $seenP[] = $v->puntero->id;
                        $pInfo[] = [
                            'nombre' => $v->puntero->nombre,
                            'dirigente' => $v->puntero->dirigente->nombre ?? 'N/A',
                            'equipo' => $v->puntero->equipo->descripcion ?? ($v->puntero->dirigente->equipo->descripcion ?? 'N/A'),
                        ];
                    }
                }

                $dInfo = [];
                $seenD = [];
                foreach ($registros as $v) {
                    if ($v->puntero && $v->puntero->dirigente && !in_array($v->puntero->dirigente->id, $seenD)) {
                        $seenD[] = $v->puntero->dirigente->id;
                        $dInfo[] = ['nombre' => $v->puntero->dirigente->nombre];
                    }
                }

                $resultados[] = [
                    'cedula' => $cedula,
                    'nombre' => $primero->nombre,
                    'direccion' => $primero->direccion,
                    'mesa' => $primero->mesa,
                    'orden' => $primero->orden,
                    'puntero' => $primero->puntero->nombre ?? 'N/A',
                    'dirigente' => $primero->puntero->dirigente->nombre ?? 'N/A',
                    'total_registros' => $registros->count(),
                    'duplicado_simple' => $duplicadoSimple,
                    'duplicado_por_puntero' => $dupPuntero,
                    'duplicado_por_dirigente' => $dupDirigente,
                    'total_punteros' => $totalPunteros,
                    'total_dirigentes' => $totalDirigentes,
                    'punteros_info' => $pInfo,
                    'dirigentes_info' => $dInfo,
                ];
            }
        }

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetAuthor('Sistema Elecciones');
        $pdf->SetTitle('Duplicados dentro del mismo Sistema');
        $pdf->SetMargins(5, 12, 5);
        $pdf->SetAutoPageBreak(true, 10);

        if (!empty($resultados)) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 10);
            $pdf->Cell(0, 6, 'DUPLICADOS DENTRO DEL MISMO SISTEMA', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Sistema ID: ' . $sistemaConsulta, 0, 1, 'C');
            $pdf->Ln(2);
            $pdf->SetFont('helvetica', 'B', 9);
            $totalSimple = collect($resultados)->where('duplicado_simple', true)->count();
            $totalPunt = collect($resultados)->where('duplicado_por_puntero', true)->count();
            $totalDir = collect($resultados)->where('duplicado_por_dirigente', true)->count();
            $pdf->Cell(0, 5, 'Totales: ' . count($resultados) . ' cedulas | Votante x2: ' . $totalSimple . ' | Puntero x2: ' . $totalPunt . ' | Dirigente x2: ' . $totalDir, 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(180, 180, 180);
            $pdf->Cell(20, 6, 'Cedula', 1, 0, 'C', true);
            $pdf->Cell(38, 6, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(12, 6, 'Regs', 1, 0, 'C', true);
            $pdf->Cell(140, 6, 'Puntero / Dirigente / Equipo', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 6.5);
            $fill = false;
            foreach ($resultados as $item) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                $fill = !$fill;

                $detalleStr = '';
                foreach ($item['punteros_info'] as $p) {
                    $detalleStr .= $p['nombre'];
                    $detalleStr .= ' -> Dirigente: ' . ($p['dirigente'] ?? 'N/A');
                    $detalleStr .= ' / Equipo: ' . ($p['equipo'] ?? 'N/A');
                    $detalleStr .= ' | ';
                }
                $detalleStr = rtrim($detalleStr, ' | ');

                $hDet = $pdf->getStringHeight(140, $detalleStr);
                $rowH = max(6, $hDet);

                $pdf->Cell(20, $rowH, $item['cedula'], 1, 0, 'C', true);
                $pdf->Cell(38, $rowH, substr($item['nombre'] ?? '', 0, 28), 1, 0, 'L', true);
                $pdf->Cell(12, $rowH, $item['total_registros'], 1, 0, 'C', true);
                $pdf->MultiCell(140, $rowH, $detalleStr, 1, 'L', true, 1);
            }
        } else {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', '', 12);
            $pdf->Cell(0, 10, 'No hay votantes duplicados dentro de mi sistema', 0, 1, 'C');
        }

        $pdf->Output('duplicados_interno.pdf', 'I');
        exit;
    }

    private function vistaVacia($userId = null)
    {
        if ($userId === null) $userId = Auth::id();
        $esUsuarioPrivilegiado = in_array($userId, [1, 4]);
        $ciudadIds = Sistema::whereNotNull('id_ciudad_electoral')->distinct()->pluck('id_ciudad_electoral');
        $ciudades = CiudadElectoral::whereIn('id', $ciudadIds)->orderBy('descripcion')->get();
        $sistemas = Sistema::with('ciudad')->orderBy('nombre')->get();

        return view('reportes.votantes_duplicados', [
            'votantesDuplicados' => [],
            'punterosDuplicados' => [],
            'dirigentesDuplicados' => [],
            'totalCedulasDuplicadas' => 0,
            'totalConDuplicadoPuntero' => 0,
            'totalConDuplicadoDirigente' => 0,
            'sistemaUsuario' => Auth::user()->sistema,
            'userId' => $userId,
            'esUsuarioPrivilegiado' => $esUsuarioPrivilegiado,
            'modoTodos' => false,
            'sistemaConsulta' => Auth::user()->sistema,
            'ciudades' => $ciudades,
            'sistemas' => $sistemas
        ]);
    }

    private function vistaVaciaInterno($userId = null, $esUsuarioPrivilegiado = false)
    {
        if ($userId === null) $userId = Auth::id();
        $ciudadIds = Sistema::whereNotNull('id_ciudad_electoral')->distinct()->pluck('id_ciudad_electoral');
        $ciudades = CiudadElectoral::whereIn('id', $ciudadIds)->orderBy('descripcion')->get();
        $sistemas = Sistema::with('ciudad')->orderBy('nombre')->get();

        return view('reportes.votantes_duplicados_interno', [
            'resultados' => collect(),
            'totalCedulasDuplicadas' => 0,
            'totalConDuplicadoPuntero' => 0,
            'totalConDuplicadoDirigente' => 0,
            'totalDuplicadoSimple' => 0,
            'sistemaUsuario' => Auth::user()->sistema,
            'esUsuarioPrivilegiado' => $esUsuarioPrivilegiado,
            'sistemaConsulta' => Auth::user()->sistema,
            'ciudades' => $ciudades,
            'sistemas' => $sistemas
        ]);
    }
}