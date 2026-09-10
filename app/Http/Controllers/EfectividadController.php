<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Mesa;
use App\Models\Partido;
use App\Models\VotosMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EfectividadController extends Controller
{
    public function puntero()
    {
        $candidatos = Candidato::where('cargo', 'Concejal Municipal')
            ->activos()
            ->with('partido')
            ->orderBy('partido_id')
            ->orderBy('numero_orden')
            ->get();

        return view('reportes.efectividad-puntero', compact('candidatos'));
    }

    public function punteroData(Request $request)
    {
        try {
            $candidatoId = (int) $request->input('candidato_id');

            $candidato = Candidato::with('partido')
                ->where('cargo', 'Concejal Municipal')
                ->find($candidatoId);

            if (!$candidato) {
                return response()->json(['message' => 'Seleccioná un candidato a concejal válido.'], 422);
            }

            $userId = (int) Auth::id();
            $esSuperAdmin = $userId >= 1 && $userId <= 4;
            $sistemaFiltro = $esSuperAdmin ? null : (int) Auth::user()->sistema;

            // Cédulas que registraron voto (asistencia real por miembro de mesa).
            // Se comparan por número de cédula contra el padrón de cada puntero:
            // si la cédula está en votos, el votante votó. (votos no guarda
            // candidato ni fecha; por eso cualquier cédula presente cuenta como voto.)
            $votosCedulas = DB::table('votos as v')
                ->where('v.cedula', '<>', '')
                ->distinct()
                ->pluck('cedula')
                ->flip();

            // Mapa: colegio normalizado -> equipo_id (solo colegios que tienen mesas)
            $colegioToEquipo = [];
            DB::table('equipo as e')
                ->join('mesas as m', 'm.equipo_id', '=', 'e.id')
                ->distinct()
                ->select('e.id', 'e.colegio')
                ->get()
                ->each(function ($row) use (&$colegioToEquipo) {
                    $colegioToEquipo[$this->normalizarLocal($row->colegio)] = (int) $row->id;
                });

            // Mapa: (equipo_id, numero_mesa) -> mesa_id
            $mesaByEquipoNum = [];
            DB::table('mesas')->get(['id', 'equipo_id', 'numero_mesa'])
                ->each(function ($row) use (&$mesaByEquipoNum) {
                    $mesaByEquipoNum[(int) $row->equipo_id][(string) $row->numero_mesa] = (int) $row->id;
                });

            // Metadatos de mesas (código, colegio) para las comparaciones.
            $mesaInfo = [];
            DB::table('mesas as m')
                ->join('equipo as e', 'e.id', '=', 'm.equipo_id')
                ->select('m.id', 'm.codigo_mesa', 'm.numero_mesa', 'e.colegio')
                ->get()
                ->each(function ($row) use (&$mesaInfo) {
                    $mesaInfo[(int) $row->id] = [
                        'codigo' => $row->codigo_mesa,
                        'colegio' => $row->colegio,
                        'numero' => $row->numero_mesa,
                    ];
                });

            // Votos reales del candidato por mesa (informativo, cuando hay carga).
            $votosCandidatoPorMesa = DB::table('votos_mesa as vm')
                ->where('vm.candidato_id', $candidatoId)
                ->where('vm.cargo', 'Concejal Municipal')
                ->when($sistemaFiltro, function ($q) use ($sistemaFiltro) {
                    $q->join('mesas as m', 'm.id', '=', 'vm.mesa_id')
                        ->join('equipo as e', 'e.id', '=', 'm.equipo_id')
                        ->where('e.sist', $sistemaFiltro);
                })
                ->select('vm.mesa_id', DB::raw('SUM(vm.cantidad_votos) as total'))
                ->groupBy('vm.mesa_id')
                ->pluck('total', 'mesa_id');

            $tieneCarga = (int) array_sum($votosCandidatoPorMesa->all()) > 0;

            // Votantes por puntero con su escuela y mesa.
            $votantes = DB::table('votante as vt')
                ->join('puntero as p', 'vt.idpuntero', '=', 'p.id')
                ->leftJoin('dirigente as d', 'p.id_dirigente', '=', 'd.id')
                ->when($sistemaFiltro, function ($q) use ($sistemaFiltro) {
                    $q->join('equipo as e', 'p.id_equipo', '=', 'e.id')
                        ->where('e.sist', $sistemaFiltro);
                })
                ->where('vt.cedula', '<>', '')
                ->select(
                    'p.id as puntero_id',
                    'p.nombre as puntero_nombre',
                    'd.nombre as dirigente_nombre',
                    'vt.cedula as cedula',
                    'vt.escuela',
                    'vt.mesa'
                )
                ->get()
                ->groupBy('puntero_id');

            $punteros = [];
            $puntMesas = [];
            $punteroMeta = [];
            foreach ($votantes as $punteroId => $filas) {
                $total = 0;
                $votaron = 0;
                $mesas = [];

                foreach ($filas as $fila) {
                    $total++;
                    // Comparación por cédula: si está en votos, votó.
                    if (isset($votosCedulas[$fila->cedula])) {
                        $votaron++;
                    }

                    $eid = $colegioToEquipo[$this->normalizarLocal($fila->escuela)] ?? null;
                    $numMesa = trim((string) $fila->mesa);
                    if ($eid !== null && $numMesa !== '' && isset($mesaByEquipoNum[$eid][$numMesa])) {
                        $mesaId = (int) $mesaByEquipoNum[$eid][$numMesa];
                        $mesas[$mesaId] = true;
                        $puntMesas[$punteroId][$mesaId]['total'] = ($puntMesas[$punteroId][$mesaId]['total'] ?? 0) + 1;
                        if (isset($votosCedulas[$fila->cedula])) {
                            $puntMesas[$punteroId][$mesaId]['votaron'] = ($puntMesas[$punteroId][$mesaId]['votaron'] ?? 0) + 1;
                        }
                    }
                }

                $punteros[] = [
                    'puntero_id' => (int) $punteroId,
                    'nombre' => $filas[0]->puntero_nombre,
                    'dirigente' => $filas[0]->dirigente_nombre ?? '',
                    'anotados' => $total,
                    'votaron' => $votaron,
                    'no_votaron' => $total - $votaron,
                    'mesas' => count($mesas),
                    'participacion' => $total > 0 ? round(100 * $votaron / $total, 1) : 0,
                ];

                $punteroMeta[$punteroId] = [
                    'nombre' => $filas[0]->puntero_nombre,
                    'dirigente' => $filas[0]->dirigente_nombre ?? '',
                ];
            }

            // Mesas compartidas: mesas donde votan votantes de 2 o más punteros,
            // comparando cuántos votaron (por cédula en votos) por puntero.
            $mesasCompartidas = [];
            $mesaPunters = [];
            foreach ($puntMesas as $punteroId => $mesas) {
                foreach ($mesas as $mesaId => $info) {
                    $mesaPunters[$mesaId][$punteroId] = $info;
                }
            }

            foreach ($mesaPunters as $mesaId => $punters) {
                if (count($punters) < 2) {
                    continue;
                }

                $rows = [];
                foreach ($punters as $punteroId => $info) {
                    $votaronMesa = (int) ($info['votaron'] ?? 0);
                    $rows[] = [
                        'puntero_id' => (int) $punteroId,
                        'nombre' => $punteroMeta[$punteroId]['nombre'] ?? '',
                        'dirigente' => $punteroMeta[$punteroId]['dirigente'] ?? '',
                        'votantes' => (int) ($info['total'] ?? 0),
                        'votaron' => $votaronMesa,
                        'movilizo' => $votaronMesa > 0,
                    ];
                }
                usort($rows, fn ($a, $b) => $b['votaron'] <=> $a['votaron']);

                $mesasCompartidas[] = [
                    'mesa_id' => (int) $mesaId,
                    'codigo' => $mesaInfo[$mesaId]['codigo'] ?? '',
                    'colegio' => $mesaInfo[$mesaId]['colegio'] ?? '',
                    'numero' => $mesaInfo[$mesaId]['numero'] ?? '',
                    'num_punteros' => count($rows),
                    'punteros' => $rows,
                ];
            }
            usort($mesasCompartidas, fn ($a, $b) => $b['num_punteros'] <=> $a['num_punteros']);

            usort($punteros, fn ($a, $b) => $b['participacion'] <=> $a['participacion']);

            $maxParticipacion = $punteros[0]['participacion'] ?? 0;

            foreach ($punteros as &$puntero) {
                if ($puntero['anotados'] > 0 && $puntero['participacion'] === $maxParticipacion && $maxParticipacion > 0) {
                    $puntero['color'] = 'success';
                } elseif ($puntero['participacion'] >= 80) {
                    $puntero['color'] = 'success';
                } elseif ($puntero['participacion'] >= 60) {
                    $puntero['color'] = 'warning';
                } else {
                    $puntero['color'] = 'danger';
                }
                $puntero['es_mejor'] = $puntero['anotados'] > 0 && $maxParticipacion > 0
                    && $puntero['participacion'] === $maxParticipacion;
            }
            unset($puntero);

            $mesasGlobales = [];
            foreach ($votantes as $filas) {
                foreach ($filas as $fila) {
                    $eid = $colegioToEquipo[$this->normalizarLocal($fila->escuela)] ?? null;
                    $numMesa = trim((string) $fila->mesa);
                    if ($eid !== null && $numMesa !== '' && isset($mesaByEquipoNum[$eid][$numMesa])) {
                        $mesasGlobales[$mesaByEquipoNum[$eid][$numMesa]] = true;
                    }
                }
            }

            $votaronTotal = array_sum(array_column($punteros, 'votaron'));
            $anotadosTotal = array_sum(array_column($punteros, 'anotados'));

            $resumen = [
                'punteros' => count($punteros),
                'anotados' => $anotadosTotal,
                'votaron' => $votaronTotal,
                'no_votaron' => $anotadosTotal - $votaronTotal,
                'mesas' => count($mesasGlobales),
                'participacion' => $anotadosTotal > 0 ? round(100 * $votaronTotal / $anotadosTotal, 1) : 0,
                'mesas_compartidas' => count($mesasCompartidas),
            ];

            return response()->json([
                'candidato' => [
                    'id' => $candidato->id,
                    'nombre' => $candidato->nombre_completo,
                    'numero_orden' => $candidato->numero_orden,
                    'partido' => $this->nombrePartido($candidato),
                ],
                'punteros' => $punteros,
                'resumen' => $resumen,
                'mesas_compartidas' => $mesasCompartidas,
                'tiene_carga' => $tieneCarga,
                'mensaje_sin_carga' => $tieneCarga
                    ? null
                    : 'El candidato «' . $candidato->nombre_completo . '» aún no tiene votos cargados. La columna Votó saldrá en 0 por ahora; una vez cargados los votos se compararán las cédulas contra votos.',
                'generado_en' => now()->toIso8601String(),
            ])->header('Cache-Control', 'private, no-store');
        } catch (\Throwable $e) {
            Log::error('Error en efectividad del puntero: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'No se pudo generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    private function nombrePartido(Candidato $candidato): string
    {
        if (!$candidato->partido) {
            return '';
        }
        return trim(($candidato->partido->numero_lista ?? '') . ' ' . ($candidato->partido->sigla ?? $candidato->partido->nombre ?? ''));
    }

    private function normalizarLocal(?string $local): string
    {
        $texto = mb_strtoupper(trim((string) $local), 'UTF-8');
        $reemplazos = ['Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U', 'Ü' => 'U', 'Ñ' => 'N'];
        return strtr($texto, $reemplazos);
    }

    public function index()
    {
        $partidos = Partido::activos()->orderBy('numero_lista')->get();
        $mesas = Mesa::with('equipo')->orderBy('codigo_mesa')->get();
        $cargos = Candidato::CARGOS;
        return view('efectividad.index', compact('partidos', 'mesas', 'cargos'));
    }

    public function resumen(Request $request)
    {
        $partidoId = $request->get('partido_id');

        $partidos = $partidoId
            ? Partido::where('id', $partidoId)->get()
            : Partido::activos()->orderBy('numero_lista')->get();

        $result = [];

        foreach ($partidos as $partido) {
            $totalIntendente = (int) VotosMesa::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')
                ->sum('cantidad_votos');

            if ($totalIntendente === 0) continue;

            $intendente = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')->first();

            $concejales = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'Concejal Municipal')
                ->orderBy('numero_orden')
                ->get();

            if ($concejales->isEmpty()) continue;

            $comiteTotals = $this->getCargoTotals('comite', $partido->id);
            $juventudTotals = $this->getCargoTotals('juventud', $partido->id);

            $concejalesData = [];
            foreach ($concejales as $cand) {
                $pos = $cand->numero_orden;
                $votosConc = (int) VotosMesa::where('candidato_id', $cand->id)
                    ->where('partido_id', $partido->id)
                    ->where('cargo', 'Concejal Municipal')
                    ->sum('cantidad_votos');
                $votosCom = (int) ($comiteTotals[$pos] ?? 0);
                $votosJuv = (int) ($juventudTotals[$pos] ?? 0);

                $efectividad = $totalIntendente > 0 ? round($votosConc / $totalIntendente, 2) : 0;
                $efectividadCom = $votosConc > 0 ? round($votosCom / $votosConc, 2) : 0;
                $efectividadJuv = $votosConc > 0 ? round($votosJuv / $votosConc, 2) : 0;

                $concejalesData[] = [
                    'posicion' => $pos,
                    'candidato' => $cand->nombre_completo,
                    'votos' => $votosConc,
                    'votos_comite' => $votosCom,
                    'votos_juventud' => $votosJuv,
                    'efectividad' => $efectividad,
                    'efectividad_comite' => $efectividadCom,
                    'efectividad_juventud' => $efectividadJuv,
                    'votos_perdidos' => max(0, $totalIntendente - $votosConc),
                    'color' => $efectividad < 0.6 ? 'danger' : ($efectividad <= 0.8 ? 'warning' : 'success'),
                    'color_comite' => $efectividadCom < 0.6 ? 'danger' : ($efectividadCom <= 0.8 ? 'warning' : 'success'),
                    'color_juventud' => $efectividadJuv < 0.6 ? 'danger' : ($efectividadJuv <= 0.8 ? 'warning' : 'success'),
                ];
            }

            $result[] = [
                'partido_id' => $partido->id,
                'partido' => $partido->nombre_completo,
                'partido_sigla' => $partido->sigla,
                'intendente' => $intendente ? $intendente->nombre_completo : '',
                'total_intendente' => $totalIntendente,
                'concejales' => $concejalesData,
            ];
        }

        return response()->json($result);
    }

    public function mesa(Request $request, $id)
    {
        $mesa = Mesa::findOrFail($id);
        $partidoId = $request->get('partido_id');

        $intendenteVotos = (int) VotosMesa::where('mesa_id', $id)
            ->where('cargo', 'intendente')
            ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
            ->sum('cantidad_votos');

        $concejales = Candidato::where('cargo', 'Concejal Municipal')
            ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
            ->orderBy('numero_orden')
            ->get();

        $detalle = [];
        $alertas = [];

        foreach ($concejales as $cand) {
            $pos = $cand->numero_orden;
            $votosConc = (int) VotosMesa::where('mesa_id', $id)
                ->where('candidato_id', $cand->id)
                ->where('cargo', 'Concejal Municipal')
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->sum('cantidad_votos');

            $votosCom = (int) VotosMesa::where('mesa_id', $id)
                ->where('cargo', "comite {$pos}")
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->sum('cantidad_votos');

            $votosJuv = (int) VotosMesa::where('mesa_id', $id)
                ->where('cargo', "juventud {$pos}")
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->sum('cantidad_votos');

            $efectividad = $intendenteVotos > 0 ? round($votosConc / $intendenteVotos, 2) : 0;
            $efectividadCom = $votosConc > 0 ? round($votosCom / $votosConc, 2) : 0;
            $efectividadJuv = $votosConc > 0 ? round($votosJuv / $votosConc, 2) : 0;

            $detalle[] = [
                'posicion' => $pos,
                'candidato' => $cand->nombre_completo,
                'votos' => $votosConc,
                'votos_comite' => $votosCom,
                'votos_juventud' => $votosJuv,
                'efectividad' => $efectividad,
                'votos_perdidos' => max(0, $intendenteVotos - $votosConc),
                'efectividad_comite' => $efectividadCom,
                'efectividad_juventud' => $efectividadJuv,
                'color_intendente' => $efectividad < 0.6 ? 'danger' : ($efectividad <= 0.8 ? 'warning' : 'success'),
                'color_comite' => $efectividadCom < 0.6 ? 'danger' : ($efectividadCom <= 0.8 ? 'warning' : 'success'),
                'color_juventud' => $efectividadJuv < 0.6 ? 'danger' : ($efectividadJuv <= 0.8 ? 'warning' : 'success'),
            ];

            if ($efectividad < 0.6 && $intendenteVotos > 0) {
                $alertas[] = "Posición {$pos} ({$cand->nombre_completo}): efectividad {$efectividad} en {$mesa->codigo_mesa} ({$detalle[count($detalle)-1]['votos_perdidos']} votos perdidos)";
            }
            if ($efectividadCom < 0.6 && $votosConc > 0) {
                $alertas[] = "Posición {$pos}: comité solo arrastra {$efectividadCom} de los votos del concejal";
            }
            if ($efectividadJuv < 0.6 && $votosConc > 0) {
                $alertas[] = "Posición {$pos}: juventud solo arrastra {$efectividadJuv} de los votos del concejal";
            }
        }

        return response()->json([
            'id' => $mesa->id,
            'mesa' => $mesa->codigo_mesa,
            'votos_intendente' => $intendenteVotos,
            'concejales' => $detalle,
            'alertas' => $alertas,
        ]);
    }

    public function ranking(Request $request)
    {
        $partidoId = $request->get('partido_id');

        $mesasConVotos = VotosMesa::where('cargo', 'intendente')
            ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
            ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
            ->groupBy('mesa_id')
            ->having('total', '>', 0)
            ->pluck('total', 'mesa_id');

        $result = [];
        foreach ($mesasConVotos as $mesaId => $intVotos) {
            $concejales = Candidato::where('cargo', 'Concejal Municipal')
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->pluck('id');

            $concSum = (int) VotosMesa::where('mesa_id', $mesaId)
                ->where('cargo', 'Concejal Municipal')
                ->whereIn('candidato_id', $concejales)
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->sum('cantidad_votos');

            $efectividadGeneral = $intVotos > 0 ? round($concSum / $intVotos, 2) : 0;

            $mesa = Mesa::find($mesaId);
            $result[] = [
                'mesa_id' => $mesaId,
                'mesa' => $mesa ? $mesa->codigo_mesa : "Mesa #{$mesaId}",
                'votos_intendente' => (int) $intVotos,
                'votos_concejales_total' => $concSum,
                'efectividad' => $efectividadGeneral,
                'votos_perdidos' => max(0, (int) $intVotos - $concSum),
            ];
        }

        usort($result, fn($a, $b) => $b['efectividad'] <=> $a['efectividad']);

        return response()->json($result);
    }

    public function comparar(Request $request)
    {
        $partidoId = $request->get('partido_id');
        $candidatoA = $request->get('candidato_a');
        $candidatoB = $request->get('candidato_b');

        $cargosComparables = ['intendente', 'Concejal Municipal', 'comite 1', 'comite 2', 'comite 3', 'comite 4',
            'comite 5', 'comite 6', 'comite 7', 'comite 8', 'comite 9', 'comite 10', 'comite 11', 'comite 12',
            'juventud 1', 'juventud 2', 'juventud 3', 'juventud 4', 'juventud 5', 'juventud 6',
            'juventud 7', 'juventud 8', 'juventud 9', 'juventud 10', 'juventud 11', 'juventud 12',
        ];

        $candidatos = Candidato::whereIn('cargo', $cargosComparables)
            ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
            ->orderBy('cargo')->orderBy('numero_orden')
            ->get(['id', 'nombre_completo', 'cargo', 'numero_orden']);

        $comparacion = null;
        if ($candidatoA && $candidatoB) {
            $candA = Candidato::find($candidatoA);
            $candB = Candidato::find($candidatoB);
            if (!$candA || !$candB) {
                return response()->json(['error' => 'Candidatos no encontrados'], 404);
            }

            $votosA = VotosMesa::where('candidato_id', $candidatoA)
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
                ->groupBy('mesa_id')->pluck('total', 'mesa_id');

            $votosB = VotosMesa::where('candidato_id', $candidatoB)
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
                ->groupBy('mesa_id')->pluck('total', 'mesa_id');

            $todasMesas = $votosA->keys()->merge($votosB->keys())->unique()->sort();
            $detalle = [];
            foreach ($todasMesas as $mesaId) {
                $mesa = Mesa::find($mesaId);
                $detalle[] = [
                    'mesa' => $mesa ? $mesa->codigo_mesa : "Mesa #{$mesaId}",
                    'votos_a' => (int) ($votosA[$mesaId] ?? 0),
                    'votos_b' => (int) ($votosB[$mesaId] ?? 0),
                ];
            }

            $totalA = $votosA->sum();
            $totalB = $votosB->sum();
            $comparacion = [
                'candidato_a' => ['id' => $candA->id, 'nombre' => $candA->nombre_completo, 'cargo' => $candA->cargo_nombre, 'total' => (int) $totalA],
                'candidato_b' => ['id' => $candB->id, 'nombre' => $candB->nombre_completo, 'cargo' => $candB->cargo_nombre, 'total' => (int) $totalB],
                'diferencia' => (int) ($totalA - $totalB),
                'ganador' => $totalA > $totalB ? 'A' : ($totalB > $totalA ? 'B' : 'EMPATE'),
                'detalle' => $detalle,
            ];
        }

        return response()->json([
            'candidatos' => $candidatos,
            'comparacion' => $comparacion,
        ]);
    }

    public function intendentes()
    {
        return response()->json(
            Candidato::where('cargo', 'intendente')
                ->with('partido')
                ->orderBy('partido_id')
                ->get(['id', 'partido_id', 'nombre_completo'])
        );
    }

    public function candidatos(Request $request)
    {
        $partidoId = $request->get('partido_id');
        $cargosComparables = ['intendente', 'Concejal Municipal',
            'comite 1', 'comite 2', 'comite 3', 'comite 4', 'comite 5', 'comite 6',
            'comite 7', 'comite 8', 'comite 9', 'comite 10', 'comite 11', 'comite 12',
            'juventud 1', 'juventud 2', 'juventud 3', 'juventud 4', 'juventud 5', 'juventud 6',
            'juventud 7', 'juventud 8', 'juventud 9', 'juventud 10', 'juventud 11', 'juventud 12',
        ];

        return response()->json(
            Candidato::whereIn('cargo', $cargosComparables)
                ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
                ->orderBy('cargo')->orderBy('numero_orden')
                ->get(['id', 'nombre_completo', 'cargo', 'numero_orden'])
        );
    }

    public function arrastre(Request $request)
    {
        $partidoId = $request->get('partido_id');

        $partidos = $partidoId
            ? Partido::where('id', $partidoId)->get()
            : Partido::activos()->orderBy('numero_lista')->get();

        $result = [];

        foreach ($partidos as $partido) {
            $concejales = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'Concejal Municipal')
                ->orderBy('numero_orden')
                ->get();

            if ($concejales->isEmpty()) continue;

            $intendenteVotos = VotosMesa::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')
                ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
                ->groupBy('mesa_id')
                ->having('total', '>', 0)
                ->pluck('total', 'mesa_id');

            if ($intendenteVotos->isEmpty()) continue;

            $intendente = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')->first();

            foreach ($intendenteVotos as $mesaId => $intVotos) {
                $intVotos = (int) $intVotos;

                $concSum = (int) VotosMesa::where('mesa_id', $mesaId)
                    ->where('partido_id', $partido->id)
                    ->where('cargo', 'Concejal Municipal')
                    ->whereIn('candidato_id', $concejales->pluck('id'))
                    ->sum('cantidad_votos');

                $diferencia = $intVotos - $concSum;

                $candidatosCoincidentes = [];
                $candidatoMasCercano = null;
                $menorDistancia = PHP_INT_MAX;

                foreach ($concejales as $cand) {
                    $votosCand = (int) VotosMesa::where('mesa_id', $mesaId)
                        ->where('partido_id', $partido->id)
                        ->where('candidato_id', $cand->id)
                        ->where('cargo', 'Concejal Municipal')
                        ->sum('cantidad_votos');

                    if ($votosCand === 0) continue;

                    if ($votosCand === abs($diferencia)) {
                        $candidatosCoincidentes[] = [
                            'nombre' => $cand->nombre_completo,
                            'orden' => $cand->numero_orden,
                            'votos' => $votosCand,
                        ];
                    }

                    $distancia = abs($votosCand - abs($diferencia));
                    if ($distancia < $menorDistancia) {
                        $menorDistancia = $distancia;
                        $candidatoMasCercano = [
                            'nombre' => $cand->nombre_completo,
                            'orden' => $cand->numero_orden,
                            'votos' => $votosCand,
                            'distancia' => $distancia,
                        ];
                    }
                }

                $sospechoso = $diferencia < 0 && $candidatoMasCercano
                    ? $candidatoMasCercano
                    : null;

                $mesa = Mesa::with('equipo')->find($mesaId);
                $result[] = [
                    'partido_id' => $partido->id,
                    'partido' => $partido->nombre_completo,
                    'partido_sigla' => $partido->sigla,
                    'intendente' => $intendente ? $intendente->nombre_completo : '',
                    'mesa_id' => $mesaId,
                    'mesa' => $mesa ? $mesa->codigo_mesa : "Mesa #{$mesaId}",
                    'local' => $mesa && $mesa->equipo ? $mesa->equipo->descripcion : '',
                    'votos_intendente' => $intVotos,
                    'suma_concejales' => $concSum,
                    'diferencia' => $diferencia,
                    'tipo_discrepancia' => $diferencia > 0 ? 'intendente_tiene_mas' : ($diferencia < 0 ? 'concejales_tienen_mas' : 'igual'),
                    'candidatos_coincidentes' => $candidatosCoincidentes,
                    'candidato_mas_cercano' => $candidatoMasCercano,
                    'sospechoso' => $sospechoso,
                ];
            }
        }

        usort($result, fn($a, $b) => abs($b['diferencia']) <=> abs($a['diferencia']));

        return response()->json($result);
    }

    public function arrastreCompleto(Request $request)
    {
        $partidoId = $request->get('partido_id');

        $partidos = $partidoId
            ? Partido::where('id', $partidoId)->get()
            : Partido::activos()->orderBy('numero_lista')->get();

        $result = [];

        foreach ($partidos as $partido) {
            $concejales = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'Concejal Municipal')
                ->orderBy('numero_orden')
                ->get();

            if ($concejales->isEmpty()) continue;

            $intendente = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')->first();
            if (!$intendente) continue;

            $mesasConVotos = VotosMesa::where('partido_id', $partido->id)
                ->where('cargo', 'intendente')
                ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
                ->groupBy('mesa_id')
                ->having('total', '>', 0)
                ->pluck('total', 'mesa_id');

            if ($mesasConVotos->isEmpty()) continue;

            foreach ($mesasConVotos as $mesaId => $intVotos) {
                $intVotos = (int) $intVotos;

                $concSum = (int) VotosMesa::where('mesa_id', $mesaId)
                    ->where('partido_id', $partido->id)
                    ->where('cargo', 'Concejal Municipal')
                    ->whereIn('candidato_id', $concejales->pluck('id'))
                    ->sum('cantidad_votos');

                $comiteSum = (int) VotosMesa::where('mesa_id', $mesaId)
                    ->where('partido_id', $partido->id)
                    ->where('cargo', 'like', 'comite %')
                    ->sum('cantidad_votos');

                $efectividadConc = $intVotos > 0 ? round($concSum / $intVotos, 2) : 0;
                $efectividadCom = $concSum > 0 ? round($comiteSum / $concSum, 2) : 0;
                $efectividadGlobal = $intVotos > 0 ? round($comiteSum / $intVotos, 2) : 0;

                $mesa = Mesa::with('equipo')->find($mesaId);
                $result[] = [
                    'partido_id' => $partido->id,
                    'partido' => $partido->nombre_completo,
                    'partido_sigla' => $partido->sigla,
                    'intendente' => $intendente->nombre_completo,
                    'mesa_id' => $mesaId,
                    'mesa' => $mesa ? $mesa->codigo_mesa : "Mesa #{$mesaId}",
                    'local' => $mesa && $mesa->equipo ? $mesa->equipo->descripcion : '',
                    'votos_intendente' => $intVotos,
                    'suma_concejales' => $concSum,
                    'suma_comite' => $comiteSum,
                    'perdidos_int_conc' => max(0, $intVotos - $concSum),
                    'perdidos_conc_com' => max(0, $concSum - $comiteSum),
                    'perdidos_int_com' => max(0, $intVotos - $comiteSum),
                    'efectividad_concejal' => $efectividadConc,
                    'efectividad_comite' => $efectividadCom,
                    'efectividad_global' => $efectividadGlobal,
                ];
            }
        }

        usort($result, fn($a, $b) => abs($b['perdidos_int_com']) <=> abs($a['perdidos_int_com']));

        return response()->json($result);
    }

    public function arrastreComite(Request $request)
    {
        $partidoId = $request->get('partido_id');

        $partidos = $partidoId
            ? Partido::where('id', $partidoId)->get()
            : Partido::activos()->orderBy('numero_lista')->get();

        $result = [];

        foreach ($partidos as $partido) {
            $concejales = Candidato::where('partido_id', $partido->id)
                ->where('cargo', 'Concejal Municipal')
                ->orderBy('numero_orden')
                ->get();

            if ($concejales->isEmpty()) continue;

            $concejalVotos = VotosMesa::where('partido_id', $partido->id)
                ->where('cargo', 'Concejal Municipal')
                ->whereIn('candidato_id', $concejales->pluck('id'))
                ->select('mesa_id', DB::raw('SUM(cantidad_votos) as total'))
                ->groupBy('mesa_id')
                ->having('total', '>', 0)
                ->pluck('total', 'mesa_id');

            if ($concejalVotos->isEmpty()) continue;

            foreach ($concejalVotos as $mesaId => $concVotos) {
                $concVotos = (int) $concVotos;

                $comiteSum = 0;
                $comitePorPos = [];
                for ($i = 1; $i <= 12; $i++) {
                    $v = (int) VotosMesa::where('mesa_id', $mesaId)
                        ->where('partido_id', $partido->id)
                        ->where('cargo', "comite {$i}")
                        ->sum('cantidad_votos');
                    $comiteSum += $v;
                    $comitePorPos[$i] = $v;
                }

                $diferencia = $concVotos - $comiteSum;

                $porPosicion = [];
                foreach ($concejales as $cand) {
                    $pos = $cand->numero_orden;
                    $vC = (int) VotosMesa::where('mesa_id', $mesaId)
                        ->where('partido_id', $partido->id)
                        ->where('candidato_id', $cand->id)
                        ->where('cargo', 'Concejal Municipal')
                        ->sum('cantidad_votos');
                    $vM = (int) ($comitePorPos[$pos] ?? 0);
                    $porPosicion[] = [
                        'posicion' => $pos,
                        'candidato' => $cand->nombre_completo,
                        'votos_concejal' => $vC,
                        'votos_comite' => $vM,
                        'diferencia' => $vC - $vM,
                    ];
                }

                $candidatosCoincidentes = [];
                $candidatoMasCercano = null;
                $menorDistancia = PHP_INT_MAX;

                foreach ($concejales as $cand) {
                    $votosCand = (int) VotosMesa::where('mesa_id', $mesaId)
                        ->where('partido_id', $partido->id)
                        ->where('candidato_id', $cand->id)
                        ->where('cargo', 'Concejal Municipal')
                        ->sum('cantidad_votos');

                    if ($votosCand === 0) continue;

                    if ($votosCand === abs($diferencia)) {
                        $candidatosCoincidentes[] = [
                            'nombre' => $cand->nombre_completo,
                            'orden' => $cand->numero_orden,
                            'votos' => $votosCand,
                        ];
                    }

                    $distancia = abs($votosCand - abs($diferencia));
                    if ($distancia < $menorDistancia) {
                        $menorDistancia = $distancia;
                        $candidatoMasCercano = [
                            'nombre' => $cand->nombre_completo,
                            'orden' => $cand->numero_orden,
                            'votos' => $votosCand,
                            'distancia' => $distancia,
                        ];
                    }
                }

                $sospechoso = $diferencia < 0 && $candidatoMasCercano
                    ? $candidatoMasCercano
                    : null;

                $mesa = Mesa::with('equipo')->find($mesaId);
                $result[] = [
                    'partido_id' => $partido->id,
                    'partido' => $partido->nombre_completo,
                    'partido_sigla' => $partido->sigla,
                    'mesa_id' => $mesaId,
                    'mesa' => $mesa ? $mesa->codigo_mesa : "Mesa #{$mesaId}",
                    'local' => $mesa && $mesa->equipo ? $mesa->equipo->descripcion : '',
                    'total_concejales' => $concVotos,
                    'total_comite' => $comiteSum,
                    'diferencia' => $diferencia,
                    'tipo_discrepancia' => $diferencia > 0 ? 'concejal_tiene_mas' : ($diferencia < 0 ? 'comite_tiene_mas' : 'igual'),
                    'por_posicion' => $porPosicion,
                    'candidatos_coincidentes' => $candidatosCoincidentes,
                    'candidato_mas_cercano' => $candidatoMasCercano,
                    'sospechoso' => $sospechoso,
                ];
            }
        }

        usort($result, fn($a, $b) => abs($b['diferencia']) <=> abs($a['diferencia']));

        return response()->json($result);
    }

    private function getCargoTotals(string $prefix, ?int $partidoId): array
    {
        $query = VotosMesa::where('cargo', 'like', "{$prefix} %")
            ->when($partidoId, fn($q) => $q->where('partido_id', $partidoId))
            ->select('cargo', DB::raw('SUM(cantidad_votos) as total'))
            ->groupBy('cargo');

        $result = [];
        foreach ($query->get() as $row) {
            $num = (int) substr($row->cargo, strlen($prefix) + 1);
            $result[$num] = (int) $row->total;
        }
        return $result;
    }
}
