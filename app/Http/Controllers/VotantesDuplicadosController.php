<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Votante;
use App\Models\Puntero;
use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\Sistema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotantesDuplicadosController extends Controller
{
    public function index(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;

        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();
        $todosLosPunterosIds = $todosLosPunteros->pluck('id')->toArray();

        if (empty($todosLosPunterosIds)) {
            return $this->vistaVacia();
        }

        $todosLosVotantes = Votante::whereIn('idpuntero', $todosLosPunterosIds)
            ->with(['puntero', 'puntero.dirigente', 'puntero.dirigente.equipo'])
            ->get();

        if ($todosLosVotantes->isEmpty()) {
            return $this->vistaVacia();
        }

        $sistemasMap = Sistema::pluck('nombre', 'id')->toArray();

        $agrupadosPorCedula = $todosLosVotantes->groupBy('cedula');

        $cedulasConDuplicadosRelevantes = [];

        foreach ($agrupadosPorCedula as $cedula => $registros) {
            $tieneRegistroEnSistemaUsuario = false;
            $sistemasInvolucrados = [];

            foreach ($registros as $votante) {
                $sistemaId = $this->getSistemaIdFromVotante($votante);
                $sistemasInvolucrados[] = $sistemaId;
                if ($sistemaId == $sistemaUsuario) {
                    $tieneRegistroEnSistemaUsuario = true;
                }
            }

            $sistemasDeEstaCedula = collect($sistemasInvolucrados)->unique()->filter()->values();

            // Solo si está en mi sistema Y aparece en al menos 2 sistemas diferentes (entre candidaturas)
            if ($tieneRegistroEnSistemaUsuario && $sistemasDeEstaCedula->count() >= 2) {
                $cedulasConDuplicadosRelevantes[$cedula] = $registros;
            }
        }

        $resultados = [];

        foreach ($cedulasConDuplicadosRelevantes as $cedula => $registros) {
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

            $registroEnSistemaUsuario = $registros->first(function($v) use ($sistemaUsuario) {
                return $this->getSistemaIdFromVotante($v) == $sistemaUsuario;
            });

            // Build details
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
                        'sistema' => $sistemasMap[$pid] ?? 'Sistema #'.$pid
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
                        'sistema' => $sistemasMap[$did] ?? 'Sistema #'.$did
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
                'puntero' => $registroEnSistemaUsuario->puntero->nombre ?? 'N/A',
                'dirigente' => $registroEnSistemaUsuario->puntero->dirigente->nombre ?? 'N/A',
                'equipo' => $registroEnSistemaUsuario->puntero->equipo->descripcion ?? 'N/A',
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
        
        // Estadísticas
        $totalCedulasDuplicadas = count($resultados);
        $totalConDuplicadoPuntero = collect($resultados)->where('duplicado_por_puntero', true)->count();
        $totalConDuplicadoDirigente = collect($resultados)->where('duplicado_por_dirigente', true)->count();
        
        return view('reportes.votantes_duplicados', compact(
            'resultados',
            'totalCedulasDuplicadas',
            'totalConDuplicadoPuntero',
            'totalConDuplicadoDirigente',
            'sistemaUsuario'
        ));
    }
    
    /**
     * Obtener el ID del sistema a partir de un votante
     * Votante -> Puntero -> Dirigente -> Equipo -> sist
     */
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
    
    public function indexInterno(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;

        $punterosDelSistema = Puntero::whereHas('dirigente.equipo', function ($q) use ($sistemaUsuario) {
                $q->where('sist', $sistemaUsuario);
            })
            ->with(['dirigente', 'dirigente.equipo'])
            ->get();

        $idsPunteros = $punterosDelSistema->pluck('id')->toArray();

        if (empty($idsPunteros)) {
            return $this->vistaVaciaInterno();
        }

        $votantes = Votante::whereIn('idpuntero', $idsPunteros)
            ->with(['puntero', 'puntero.dirigente'])
            ->get();

        if ($votantes->isEmpty()) {
            return $this->vistaVaciaInterno();
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
                    $pInfo[] = ['nombre' => $v->puntero->nombre];
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

        return view('reportes.votantes_duplicados_interno', compact(
            'resultados',
            'totalCedulasDuplicadas',
            'totalConDuplicadoPuntero',
            'totalConDuplicadoDirigente',
            'totalDuplicadoSimple',
            'sistemaUsuario'
        ));
    }

    private function vistaVacia()
    {
        return view('reportes.votantes_duplicados', [
            'resultados' => collect(),
            'totalCedulasDuplicadas' => 0,
            'totalConDuplicadoPuntero' => 0,
            'totalConDuplicadoDirigente' => 0,
            'sistemaUsuario' => Auth::user()->sistema
        ]);
    }

    private function vistaVaciaInterno()
    {
        return view('reportes.votantes_duplicados_interno', [
            'resultados' => collect(),
            'totalCedulasDuplicadas' => 0,
            'totalConDuplicadoPuntero' => 0,
            'totalConDuplicadoDirigente' => 0,
            'totalDuplicadoSimple' => 0,
            'sistemaUsuario' => Auth::user()->sistema
        ]);
    }
}