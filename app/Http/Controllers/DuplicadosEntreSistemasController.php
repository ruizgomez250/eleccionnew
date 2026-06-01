<?php

namespace App\Http\Controllers;

use App\Models\Votante;
use App\Models\Puntero;
use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\Sistema;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DuplicadosEntreSistemasController extends Controller
{
    public function index(Request $request)
    {
        $sistemaUsuario = Auth::user()->sistema;
        $sistemasMap = Sistema::pluck('nombre', 'id')->toArray();

        $votantesDuplicados = $this->getVotantesDuplicados($sistemaUsuario, $sistemasMap);
        $punterosDuplicados = $this->getPunterosDuplicados($sistemaUsuario, $sistemasMap);
        $dirigentesDuplicados = $this->getDirigentesDuplicados($sistemaUsuario, $sistemasMap);

        return view('reportes.duplicados_entre_sistemas', compact(
            'votantesDuplicados',
            'punterosDuplicados',
            'dirigentesDuplicados',
            'sistemaUsuario',
            'sistemasMap'
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
                            'sistema' => $sistemasMap[$pid] ?? 'Sistema #'.$pid
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
}
