<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Mesa;
use App\Models\Partido;
use App\Models\Sistema;
use App\Models\VotosMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EfectividadController extends Controller
{
    public function puntero()
    {
        // Solo candidatos que tienen certificado de resultados cargado (votos_mesa):
        // la efectividad se calcula contra los votos reales del certificado.
        $candidatos = Candidato::where('cargo', 'Concejal Municipal')
            ->activos()
            ->with('partido')
            ->whereIn('id', DB::table('votos_mesa')
                ->where('cargo', 'Concejal Municipal')
                ->whereNotNull('candidato_id')
                ->distinct()
                ->pluck('candidato_id'))
            ->orderBy('partido_id')
            ->orderBy('numero_orden')
            ->get();

        // Los usuarios 1-4 (admin) pueden elegir contra qué sistema se consulta.
        $userId = (int) Auth::id();
        $esAdmin = in_array($userId, [1, 2, 3, 4]);
        $sistemas = $esAdmin
            ? Sistema::orderBy('nombre')->get(['id', 'nombre'])
            : collect();

        return view('reportes.efectividad-puntero', compact('candidatos', 'esAdmin', 'sistemas'));
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

            // Cédulas que registraron voto (asistencia real en la tabla voto).
            // Se comparan por número de cédula: si la cédula está en votos, votó.
            $votosCedulas = DB::table('votos as v')
                ->where('v.cedula', '<>', '')
                ->distinct()
                ->pluck('cedula')
                ->flip();

            // Mapa: colegio normalizado -> equipo_id (solo colegios que tienen mesas).
            $colegioToEquipo = [];
            DB::table('equipo as e')
                ->join('mesas as m', 'm.equipo_id', '=', 'e.id')
                ->distinct()
                ->select('e.id', 'e.colegio')
                ->get()
                ->each(function ($row) use (&$colegioToEquipo) {
                    $colegioToEquipo[$this->normalizarLocal($row->colegio)] = (int) $row->id;
                });

            // Mapa: (equipo_id, numero_mesa) -> mesa_id.
            $mesaByEquipoNum = [];
            DB::table('mesas')->get(['id', 'equipo_id', 'numero_mesa'])
                ->each(function ($row) use (&$mesaByEquipoNum) {
                    $mesaByEquipoNum[(int) $row->equipo_id][(string) $row->numero_mesa] = (int) $row->id;
                });

            // Metadatos de mesas (código, colegio) para mostrar.
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

            // Votos reales del candidato por mesa = certificado de resultados (V_C,M).
            $votosCandidatoPorMesa = DB::table('votos_mesa as vm')
                ->where('vm.candidato_id', $candidatoId)
                ->where('vm.cargo', 'Concejal Municipal')
                ->select('vm.mesa_id', DB::raw('SUM(vm.cantidad_votos) as total'))
                ->groupBy('vm.mesa_id')
                ->pluck('total', 'mesa_id');

            $tieneCarga = (int) array_sum($votosCandidatoPorMesa->all()) > 0;

            // Estructura a consultar.
            // - Usuarios 1-4 (admin): el sistema a consultar lo eligen en el formulario.
            // - Resto de usuarios: punteros de su sistema (equipo.sist = usuario.sistema)
            //   más los punteros con votantes asignados a su usuario (votante.idusuario).
            $esAdmin = in_array($userId, [1, 2, 3, 4]);

            if ($esAdmin) {
                $sistemaSeleccionado = (int) $request->input('sistema_id');
                if (!$sistemaSeleccionado) {
                    return response()->json(['message' => 'Seleccioná un sistema para generar el reporte.'], 422);
                }

                $punterosUsuario = DB::table('puntero as p')
                    ->join('equipo as e', 'p.id_equipo', '=', 'e.id')
                    ->where('e.sist', $sistemaSeleccionado)
                    ->distinct()
                    ->pluck('p.id')
                    ->unique()
                    ->values();
            } else {
                $userSistema = Auth::user()->sistema ?? null;
                $punterosUsuario = collect();

                if (!is_null($userSistema) && $userSistema !== '' && $userSistema !== 0) {
                    $punterosUsuario = DB::table('puntero as p')
                        ->join('equipo as e', 'p.id_equipo', '=', 'e.id')
                        ->where('e.sist', $userSistema)
                        ->distinct()
                        ->pluck('p.id');
                }

                $punterosUsuario = $punterosUsuario->merge(
                    DB::table('votante as vt')
                        ->join('puntero as p', 'vt.idpuntero', '=', 'p.id')
                        ->where('vt.idusuario', $userId)
                        ->where('vt.cedula', '<>', '')
                        ->distinct()
                        ->pluck('p.id')
                )->unique()->values();
            }

            $votantes = DB::table('votante as vt')
                ->join('puntero as p', 'vt.idpuntero', '=', 'p.id')
                ->leftJoin('dirigente as d', 'p.id_dirigente', '=', 'd.id')
                ->whereIn('p.id', $punterosUsuario)
                ->where('vt.cedula', '<>', '')
                ->select(
                    'p.id as puntero_id',
                    'p.nombre as puntero_nombre',
                    'd.nombre as dirigente_nombre',
                    'vt.id as votante_id',
                    'vt.cedula as cedula',
                    DB::raw("COALESCE(NULLIF(vt.nombre, ''), CONCAT_WS(' ', NULLIF(vt.padron_nombre, ''), NULLIF(vt.padron_apellido, '')), vt.cedula) as votante_nombre"),
                    'vt.direccion as direccion',
                    'vt.escuela as escuela',
                    'vt.mesa as nro_mesa'
                )
                ->get()
                ->groupBy('puntero_id');

            // ── Paso 1: agrupar la estructura por mesa ──
            // mesas[mesaId]  -> ['n' => votantes de la estructura, 'f' => cuáles votaron, 'v_cm' => votos reales del candidato]
            // punteroMesas[punteroId][mesaId] -> ['n','f']
            $mesas = [];
            $punteroMesas = [];
            $sinMesa = [];

            foreach ($votantes as $punteroId => $filas) {
                foreach ($filas as $fila) {
                    $fila->mesa_id = null;
                    $eid = $colegioToEquipo[$this->normalizarLocal($fila->escuela)] ?? null;
                    $numMesa = trim((string) $fila->nro_mesa);
                    if ($eid !== null && $numMesa !== '' && isset($mesaByEquipoNum[$eid][$numMesa])) {
                        $mesaId = (int) $mesaByEquipoNum[$eid][$numMesa];
                        $fila->mesa_id = $mesaId;

                        $fue = isset($votosCedulas[$fila->cedula]);

                        $mesas[$mesaId]['n'] = ($mesas[$mesaId]['n'] ?? 0) + 1;
                        if ($fue) {
                            $mesas[$mesaId]['f'] = ($mesas[$mesaId]['f'] ?? 0) + 1;
                        }

                        $punteroMesas[$punteroId][$mesaId]['n'] = ($punteroMesas[$punteroId][$mesaId]['n'] ?? 0) + 1;
                        if ($fue) {
                            $punteroMesas[$punteroId][$mesaId]['f'] = ($punteroMesas[$punteroId][$mesaId]['f'] ?? 0) + 1;
                        }
                    } else {
                        $sinMesa[$punteroId] = ($sinMesa[$punteroId] ?? 0) + 1;
                    }
                }
            }

            // ── Paso 2: Rendimiento electoral por mesa (R_M) ──
            // R_M = min(1, V_C,M / F_E,M), donde F_E,M = toda la estructura que fue a votar en esa mesa.
            foreach ($mesas as $mesaId => &$info) {
                $vCM = (int) ($votosCandidatoPorMesa[$mesaId] ?? 0);
                $f = (int) ($info['f'] ?? 0);
                $info['v_cm'] = $vCM;
                $info['rendimiento'] = $f > 0 ? min(1, $vCM / $f) : 0;
            }
            unset($info);

            // ── Paso 3: indicadores por puntero (modelo reformulado) ──
            $punteros = [];
            foreach ($votantes as $punteroId => $filas) {
                $NP = count($filas);
                $FP = 0;
                foreach ($filas as $fila) {
                    if (isset($votosCedulas[$fila->cedula])) {
                        $FP++;
                    }
                }

                // Votos esperados del puntero = Σ por mesa de (F_P,M × R_M)
                $votosEsperados = 0;
                foreach (($punteroMesas[$punteroId] ?? []) as $mesaId => $info) {
                    $rendimiento = $mesas[$mesaId]['rendimiento'] ?? 0;
                    $votosEsperados += (int) ($info['f'] ?? 0) * $rendimiento;
                }

                $brecha = $FP - $votosEsperados;
                $ausentes = $NP - $FP;

                // MOV_P = F_P / N_P (Movilización)
                $mov = $NP > 0 ? $FP / $NP : 0;
                // REM_P = VE_P / F_P (Rendimiento Electoral estimado de Movilizados)
                $rem = $FP > 0 ? $votosEsperados / $FP : 0;
                // RGE_P = VE_P / N_P (Rendimiento Global Estimado)
                $rge = $NP > 0 ? $votosEsperados / $NP : 0;

                // Detalle por votante: aporte estadístico = R_M si asistió, 0 si no asistió.
                // VE_i = P_i = min(1, V_C,M / F_E,M), observable sólo a nivel agregado de mesa.
                $votantesDetalle = [];
                foreach ($filas as $fila) {
                    $fue = isset($votosCedulas[$fila->cedula]);
                    $mesaId = $fila->mesa_id;
                    $rendimiento = $mesaId !== null ? ($mesas[$mesaId]['rendimiento'] ?? 0) : 0;
                    $vCM = $mesaId !== null ? (int) ($mesas[$mesaId]['v_cm'] ?? 0) : 0;
                    $fE = $mesaId !== null ? (int) ($mesas[$mesaId]['f'] ?? 0) : 0;
                    $aporte = $rendimiento * ($fue ? 1 : 0);
                    $saturado = $fE > 0 && $vCM >= $fE;

                    // Si la mesa no tuvo ningún voto del candidato (V_C,M = 0), es determinista:
                    // nadie de esa mesa votó por el candidato. No es una inferencia.
                    $clas = !$fue
                        ? 'Ausente'
                        : ($mesaId === null
                            ? 'Sin mesa'
                            : ($vCM === 0 ? 'No votó por el candidato' : 'Asistió'));

                    $votantesDetalle[] = [
                        'nombre' => $this->normalizarTexto($fila->votante_nombre) ?: $fila->cedula,
                        'cedula' => $fila->cedula,
                        'mesa' => $mesaId !== null ? ($mesaInfo[$mesaId]['codigo'] ?? '') : '',
                        'escuela' => (string) $fila->escuela,
                        'voto' => $fue,
                        'votos_candidato_mesa' => $vCM,
                        'estructura_movilizada_mesa' => $fE,
                        'rendimiento_mesa' => round($rendimiento, 4),
                        'aporte_estadistico' => round($aporte, 4),
                        'saturado' => $saturado,
                        'clasificacion' => $clas,
                    ];
                }

                $punteros[] = [
                    'puntero_id' => (int) $punteroId,
                    'nombre' => $filas[0]->puntero_nombre,
                    'dirigente' => $filas[0]->dirigente_nombre ?? '',
                    'anotados' => $NP,
                    'votaron' => $FP,
                    'no_votaron' => $NP - $FP,
                    'ausentes' => $ausentes,
                    'sin_mesa' => $sinMesa[$punteroId] ?? 0,
                    'mesas' => count($punteroMesas[$punteroId] ?? []),
                    'votos_esperados' => round($votosEsperados, 2),
                    'brecha' => round($brecha, 2),
                    'mov' => round($mov, 4),
                    'rem' => round($rem, 4),
                    'rge' => round($rge * 100, 1),
                    'color' => $rge >= 0.80 ? 'success' : ($rge >= 0.60 ? 'info' : ($rge >= 0.40 ? 'warning' : 'danger')),
                    'votantes' => $votantesDetalle,
                ];
            }

            usort($punteros, fn ($a, $b) => $b['rge'] <=> $a['rge']);

            // ── Resumen global ──
            $totalN = array_sum(array_column($punteros, 'anotados'));
            $totalF = array_sum(array_column($punteros, 'votaron'));
            $totalVE = array_sum(array_column($punteros, 'votos_esperados'));
            $totalBrecha = array_sum(array_column($punteros, 'brecha'));
            $totalSinMesa = array_sum(array_column($punteros, 'sin_mesa'));
            $movG = $totalN > 0 ? $totalF / $totalN : 0;
            $remG = $totalF > 0 ? $totalVE / $totalF : 0;
            $rgeG = $totalN > 0 ? $totalVE / $totalN : 0;

            // ── Detalle por mesa y votos externos ──
            // Externos_M = max(0, V_C,M − F_E,M): votos del candidato que exceden a la
            // estructura movilizada en esa mesa, por lo que no pudieron ser aportados por ella.
            $mesasDetalle = [];
            $votosCandidatoTotal = 0;
            $votosEstructuraTotal = 0;
            $votosExternosTotal = 0;

            $mesaIds = collect(array_keys($votosCandidatoPorMesa->all()))
                ->merge(array_keys($mesas))
                ->unique();

            foreach ($mesaIds as $mesaId) {
                $vCM = (int) ($votosCandidatoPorMesa[$mesaId] ?? 0);
                $fE = (int) ($mesas[$mesaId]['f'] ?? 0);
                $estructura = min($vCM, $fE);
                $externos = max(0, $vCM - $fE);

                $votosCandidatoTotal += $vCM;
                $votosEstructuraTotal += $estructura;
                $votosExternosTotal += $externos;

                $mesasDetalle[] = [
                    'mesa' => $mesaInfo[$mesaId]['codigo'] ?? ('Mesa #' . $mesaId),
                    'colegio' => $mesaInfo[$mesaId]['colegio'] ?? '',
                    'votos_candidato' => $vCM,
                    'estructura_movilizada' => $fE,
                    'votos_estructura' => $estructura,
                    'votos_externos' => $externos,
                    'rendimiento' => $fE > 0 ? round(min(1, $vCM / $fE), 4) : 0,
                ];
            }

            usort($mesasDetalle, fn ($a, $b) => $b['votos_externos'] <=> $a['votos_externos']);

            $resumen = [
                'punteros' => count($punteros),
                'anotados' => $totalN,
                'votaron' => $totalF,
                'no_votaron' => $totalN - $totalF,
                'ausentes' => $totalN - $totalF,
                'votos_esperados' => round($totalVE, 2),
                'brecha' => round($totalBrecha, 2),
                'sin_mesa' => $totalSinMesa,
                'mesas' => count($mesas),
                'mov' => round($movG, 4),
                'rem' => round($remG, 4),
                'rge' => round($rgeG * 100, 1),
                'votos_candidato' => $votosCandidatoTotal,
                'votos_estructura' => $votosEstructuraTotal,
                'votos_externos' => $votosExternosTotal,
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
                'mesas_detalle' => $mesasDetalle,
                'tiene_carga' => $tieneCarga,
                'mensaje_sin_carga' => $tieneCarga
                    ? null
                    : 'El candidato «' . $candidato->nombre_completo . '» aún no tiene certificado de resultados cargado. El rendimiento de mesa saldrá en 0 hasta que se carguen los votos reales por mesa.',
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

    private function normalizarTexto($texto): string
    {
        return trim(preg_replace('/[\t\r\n\x00-\x1F]+/', ' ', (string) $texto));
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
