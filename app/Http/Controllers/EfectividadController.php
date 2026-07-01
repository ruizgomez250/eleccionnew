<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Mesa;
use App\Models\Partido;
use App\Models\VotosMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EfectividadController extends Controller
{
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
