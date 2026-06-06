<?php

namespace App\Http\Controllers;

use App\Models\Mesa;
use App\Models\Partido;
use App\Models\Candidato;
use App\Models\VotosMesa;
use App\Models\Veedor;
use App\Models\Equipo;
use App\Models\LocalInterna;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use TCPDF;

class CertificadoController extends Controller
{
    const BANCAS = [
        'intendente' => 1,
        'Concejal Municipal' => 12,
        'presidente - vice 1 y vice 2 - plra' => 1,
        'directorio nacional' => 15,
        'directorio departamental' => 3,
    ];

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Carga Certificados', ['only' => ['index', 'guardarResultados', 'update']]);
        $this->middleware('permission:Guardar Certificados', ['only' => ['destroy']]);
    }

    public function index()
    {
        $distritos = LocalInterna::select('distrito_nombre')
            ->distinct()
            ->orderBy('distrito_nombre')
            ->pluck('distrito_nombre');

        $partidos = Partido::activos()->orderByRaw('CAST(numero_lista AS UNSIGNED), numero_lista')->get();
        $cargos = Candidato::CARGOS;
        $veedores = Veedor::activos()->get();

        $votosPorCargo = [];
        foreach ($cargos as $cargo) {
            $votosPorCargo[$cargo] = VotosMesa::with(['mesa.equipo', 'mesa', 'partido', 'candidato', 'veedor', 'user'])
                ->where('cargo', $cargo)
                ->orderBy('created_at', 'desc')
                ->get();
        }

        $totalCertificados = VotosMesa::count();
        $totalVotosCargados = VotosMesa::sum('cantidad_votos');
        $mesasConCarga = VotosMesa::distinct('mesa_id')->count('mesa_id');
        $totalMesas = Mesa::count();

        $votosPorPartidoYCargo = VotosMesa::select(
            'partido_id', 'cargo', DB::raw('SUM(cantidad_votos) as total_votos')
        )
            ->where('tipo_voto', 'preferencia')
            ->groupBy('partido_id', 'cargo')
            ->with('partido:id,nombre,sigla,numero_lista,color_hex')
            ->get()
            ->groupBy('cargo');

        $chartLabels = [];
        $chartData = [];
        $chartColors = [];
        $chartSlugs = [];

        foreach ($cargos as $cargo) {
            $data = $votosPorPartidoYCargo->get($cargo, collect());
            if ($data->isNotEmpty()) {
                $name = ucfirst($cargo);
                $chartLabels[$name] = $data->pluck('partido.sigla')->map(fn($s) => $s ?: 'S/D')->toArray();
                $chartData[$name] = $data->pluck('total_votos')->toArray();
                $chartColors[$name] = $data->pluck('partido.color_hex')->map(fn($c) => $c ?: '#6c757d')->toArray();
                $chartSlugs[$name] = \Illuminate\Support\Str::slug($name);
            }
        }

        $dhont = [];
        foreach ($cargos as $cargo) {
            $data = $votosPorPartidoYCargo->get($cargo, collect());
            if ($data->isNotEmpty()) {
                $dhont[$cargo] = $this->calcularDhont($data, self::BANCAS[$cargo] ?? 1);
            }
        }

        $reporteCargos = [];
        $cargosConOpcion = [];

        $prefAgg = VotosMesa::where('tipo_voto', 'preferencia')
            ->select('cargo', 'partido_id', 'candidato_id', DB::raw('SUM(cantidad_votos) as total_votos'))
            ->groupBy('cargo', 'partido_id', 'candidato_id')
            ->with('candidato:id,nombre_completo,numero_orden', 'partido:id,sigla,nombre,numero_lista,color_hex')
            ->get()
            ->groupBy('cargo');

        $todosCandidatos = Candidato::whereIn('cargo', $cargos)
            ->orderBy('numero_orden')
            ->get()
            ->groupBy('cargo')
            ->map(fn($items) => $items->groupBy('partido_id'));

        foreach ($cargos as $cargo) {
            $dhontData = $dhont[$cargo] ?? [];
            $partidosReporte = [];
            $prefCargo = $prefAgg->get($cargo, collect())->groupBy('partido_id');
            $votosPartido = $votosPorPartidoYCargo->get($cargo, collect());

            foreach ($votosPartido as $vp) {
                $candidatos = collect();
                $todosCandPartido = $todosCandidatos[$cargo][$vp->partido_id] ?? collect();
                $prefPartido = $prefCargo->get($vp->partido_id, collect());

                foreach ($todosCandPartido as $cand) {
                    $votoData = $prefPartido->firstWhere('candidato_id', $cand->id);
                    $item = new \stdClass();
                    $item->total_votos = $votoData ? (int)$votoData->total_votos : 0;
                    $item->candidato = $cand;
                    $candidatos->push($item);
                }

                $candidatos = $candidatos->sortBy([
                    ['total_votos', 'desc'],
                    ['candidato.numero_orden', 'asc'],
                ])->values();

                $partidosReporte[$vp->partido_id] = [
                    'partido' => $vp->partido,
                    'votos_lista' => $vp->total_votos,
                    'candidatos' => $candidatos,
                ];
            }

            $reporteCargos[$cargo] = [
                'dhont' => $dhontData,
                'partidos' => $partidosReporte,
            ];

            $tieneOpcion = $prefCargo->flatten()->isNotEmpty();
            if ($tieneOpcion) {
                $cargosConOpcion[] = $cargo;
            }
        }

        return view('certificados.index', compact(
            'distritos', 'partidos', 'cargos', 'veedores', 'votosPorCargo',
            'totalCertificados', 'totalVotosCargados', 'mesasConCarga', 'totalMesas',
            'chartLabels', 'chartData', 'chartColors', 'chartSlugs', 'dhont',
            'reporteCargos', 'cargosConOpcion'
        ));
    }

    public function getLocales(Request $request)
    {
        $locales = LocalInterna::where('distrito_nombre', $request->distrito)
            ->orderBy('local_interna')
            ->get(['id', 'local_interna', 'cantmesa']);

        return response()->json($locales);
    }

    public function getMesas(Request $request)
    {
        $local = LocalInterna::findOrFail($request->local_interna_id);

        $equipo = Equipo::where('ciudad', $local->distrito_nombre)
            ->where('colegio', $local->local_interna)
            ->first();

        if (!$equipo) {
            return response()->json(['mesas' => []]);
        }

        // Crear registros de mesa del 1 a cantmesa si no existen
        $mesas = collect();
        for ($i = 1; $i <= $local->cantmesa; $i++) {
            $mesa = Mesa::firstOrCreate(
                [
                    'equipo_id' => $equipo->id,
                    'numero_mesa' => $i,
                ],
                [
                    'codigo_mesa' => $equipo->colegio . ' - Mesa ' . $i,
                    'departamento' => $local->departamento_nombre,
                    'distrito' => $local->distrito_nombre,
                    'direccion' => $equipo->colegio,
                ]
            );
            $mesas->push($mesa);
        }

        return response()->json([
            'equipo' => ['id' => $equipo->id, 'descripcion' => $equipo->descripcion],
            'mesas' => $mesas->map(fn($m) => ['id' => $m->id, 'codigo_mesa' => $m->codigo_mesa, 'numero_mesa' => $m->numero_mesa]),
            'cantmesa' => $local->cantmesa,
        ]);
    }

    public function getFormularioCarga(Request $request)
    {
        $mesa = Mesa::with('equipo')->findOrFail($request->mesa_id);
        $cargo = $request->cargo;
        $partidos = Partido::activos()
            ->whereHas('candidatos', fn($q) => $q->where('cargo', $cargo)->activos())
            ->orderByRaw('CAST(numero_lista AS UNSIGNED), numero_lista')
            ->get();

        $votosExistentes = VotosMesa::where('mesa_id', $mesa->id)
            ->where('cargo', $cargo)
            ->get()
            ->groupBy('partido_id');

        $rows = [];
        foreach ($partidos as $p) {
            $candidatos = Candidato::where('partido_id', $p->id)
                ->where('cargo', $cargo)
                ->activos()
                ->ordenados()
                ->get();

            $partidoVotos = $votosExistentes->get($p->id, collect());
            $preferencias = $partidoVotos->where('tipo_voto', 'preferencia')->keyBy('candidato_id');

            $rows[] = [
                'partido' => $p,
                'candidatos' => $candidatos,
                'preferencias' => $preferencias,
            ];
        }

        $html = view('certificados._carga_table', compact('mesa', 'cargo', 'rows'))->render();

        return response()->json(['html' => $html]);
    }

    public function guardarResultados(Request $request)
    {
        $request->validate([
            'mesa_id' => 'required|exists:mesas,id',
            'cargo' => 'required|string',
            'preferencias' => 'required|array',
        ]);

        try {
            DB::beginTransaction();

            foreach ($request->preferencias as $partidoId => $candidatos) {
                foreach ($candidatos as $candidatoId => $votos) {
                    if ($votos !== '' && $votos !== null) {
                        VotosMesa::updateOrCreate(
                            [
                                'mesa_id' => $request->mesa_id,
                                'partido_id' => $partidoId,
                                'cargo' => $request->cargo,
                                'candidato_id' => $candidatoId,
                                'tipo_voto' => 'preferencia',
                            ],
                            [
                                'cantidad_votos' => $votos,
                                'origen' => 'web',
                                'escaneado_en' => now(),
                                'escaneado_por' => auth()->user()->name,
                                'user_id' => auth()->id(),
                            ]
                        );
                    }
                }
            }

            DB::commit();

            return response()->json(['success' => true, 'message' => 'Resultados guardados correctamente.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'message' => 'Error: ' . $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $voto = VotosMesa::findOrFail($id);

            if ($request->wantsJson() || $request->ajax()) {
                $voto->update(['cantidad_votos' => $request->cantidad_votos]);

                return response()->json([
                    'success' => true,
                    'message' => 'Votos actualizados correctamente'
                ]);
            }

            $request->validate([
                'mesa_id' => 'required|exists:mesas,id',
                'partido_id' => 'required|exists:partidos,id',
                'cargo' => 'required|string',
                'tipo_voto' => 'required|in:lista,preferencia',
                'cantidad_votos' => 'required|integer|min:0',
                'candidato_id' => 'nullable|exists:candidatos,id',
            ]);

            $voto->update($request->only([
                'mesa_id', 'partido_id', 'cargo', 'tipo_voto',
                'cantidad_votos', 'candidato_id', 'veedor_id'
            ]));

            return redirect()->route('certificados.index')
                ->with('success', 'Certificado actualizado correctamente');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: ' . $e->getMessage()
                ], 500);
            }

            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            VotosMesa::findOrFail($id)->delete();
            return redirect()->route('certificados.index')
                ->with('success', 'Certificado eliminado correctamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error: ' . $e->getMessage());
        }
    }

    public function getCandidatos(Request $request)
    {
        return response()->json(
            Candidato::where('partido_id', $request->partido_id)
                ->where('cargo', $request->cargo)
                ->activos()->ordenados()
                ->get(['id', 'nombre_completo', 'numero_orden'])
        );
    }

    private function calcularDhont($votosPartido, $bancas)
    {
        $resultado = [];
        $quotients = [];
        foreach ($votosPartido as $vp) {
            $votos = (int)$vp->total_votos;
            for ($i = 1; $i <= $bancas; $i++) {
                $quotients[] = [
                    'partido_id' => $vp->partido_id,
                    'sigla' => $vp->partido->sigla ?? $vp->partido->nombre,
                    'divisor' => $i,
                    'cociente' => $votos / $i,
                ];
            }
            $resultado[$vp->partido_id] = [
                'partido_id' => $vp->partido_id,
                'sigla' => $vp->partido->sigla ?? $vp->partido->nombre,
                'votos' => $votos,
                'bancas' => 0,
                'color' => $vp->partido->color_hex,
            ];
        }
        usort($quotients, fn($a, $b) => $b['cociente'] <=> $a['cociente']);
        foreach (array_slice($quotients, 0, $bancas) as $q) {
            $resultado[$q['partido_id']]['bancas']++;
        }
        return array_values($resultado);
    }

    public function exportPdf(Request $request)
    {
        $cargo = $request->cargo;

        $cargos = $cargo
            ? [$cargo]
            : Candidato::CARGOS;

        $votosPorPartidoYCargo = VotosMesa::select(
            'partido_id', 'cargo', DB::raw('SUM(cantidad_votos) as total_votos')
        )
            ->where('tipo_voto', 'preferencia')
            ->when($cargo, fn($q) => $q->where('cargo', $cargo))
            ->groupBy('partido_id', 'cargo')
            ->with('partido:id,nombre,sigla,numero_lista,color_hex')
            ->get()
            ->groupBy('cargo');

        $prefAgg = VotosMesa::where('tipo_voto', 'preferencia')
            ->when($cargo, fn($q) => $q->where('cargo', $cargo))
            ->select('cargo', 'partido_id', 'candidato_id', DB::raw('SUM(cantidad_votos) as total_votos'))
            ->groupBy('cargo', 'partido_id', 'candidato_id')
            ->with('candidato:id,nombre_completo,numero_orden', 'partido:id,sigla,nombre,numero_lista,color_hex')
            ->get()
            ->groupBy('cargo');

        $todosCandidatos = Candidato::whereIn('cargo', $cargos)
            ->orderBy('numero_orden')
            ->get()
            ->groupBy('cargo')
            ->map(fn($items) => $items->groupBy('partido_id'));

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetAuthor('Sistema Elecciones');
        $pdf->SetTitle('Resultados Finales' . ($cargo ? ' - ' . ucfirst($cargo) : ''));
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(true, 15);

        foreach ($cargos as $c) {
            $data = $votosPorPartidoYCargo->get($c, collect());
            if ($data->isEmpty()) continue;

            $bancas = self::BANCAS[$c] ?? 1;
            $dhont = $this->calcularDhont($data, $bancas);
            $prefCargo = $prefAgg->get($c, collect())->groupBy('partido_id');

            $pdf->AddPage();

            // Header
            $pdf->SetFont('helvetica', 'B', 14);
            $pdf->Cell(0, 8, 'RESULTADOS FINALES', 0, 1, 'C');
            $pdf->SetFont('helvetica', '', 11);
            $cargoNombre = ucfirst($c);
            if ($c === 'Concejal Municipal') $cargoNombre = 'Concejal Municipal';
            $pdf->Cell(0, 6, 'Cargo: ' . $cargoNombre, 0, 1, 'C');
            $pdf->Ln(4);

            // D'Hondt table
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'Distribucion D\'Hondt (' . $bancas . ' bancas)', 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(15, 7, 'Lista', 1, 0, 'C', true);
            $pdf->Cell(50, 7, 'Partido', 1, 0, 'C', true);
            $pdf->Cell(30, 7, 'Votos', 1, 0, 'C', true);
            $pdf->Cell(20, 7, 'Bancas', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $totalBancas = 0;
            foreach ($dhont as $p) {
                $pdf->Cell(15, 6, $data->firstWhere('partido_id', $p['partido_id'])?->partido?->numero_lista ?? '-', 1, 0, 'C');
                $pdf->Cell(50, 6, $p['sigla'], 1, 0, 'L');
                $pdf->Cell(30, 6, number_format($p['votos'], 0, ',', '.'), 1, 0, 'R');
                $pdf->Cell(20, 6, $p['bancas'], 1, 1, 'C');
                $totalBancas += $p['bancas'];
            }

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(65, 6, 'Total', 1, 0, 'R');
            $pdf->Cell(30, 6, number_format(array_sum(array_column($dhont, 'votos')), 0, ',', '.'), 1, 0, 'R');
            $pdf->Cell(20, 6, $totalBancas, 1, 1, 'C');

            $pdf->Ln(5);

            // Elected candidates section
            $ordenadoPorBancas = collect($dhont)->sortByDesc('bancas')->values();

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'Candidatos Electos', 0, 1, 'L');
            $pdf->Ln(1);

            $electosPorLista = collect();
            foreach ($ordenadoPorBancas as $p) {
                if ($p['bancas'] > 0) {
                    $todosCandPartido = ($todosCandidatos[$c][$p['partido_id']] ?? collect());
                    $prefPartido = $prefCargo->get($p['partido_id'], collect());
                    $candidatosLista = collect();

                    foreach ($todosCandPartido as $cand) {
                        $votoData = $prefPartido->firstWhere('candidato_id', $cand->id);
                        $item = new \stdClass();
                        $item->total_votos = $votoData ? (int)$votoData->total_votos : 0;
                        $item->candidato = $cand;
                        $candidatosLista->push($item);
                    }

                    $candidatosLista = $candidatosLista->sortBy([
                        ['total_votos', 'desc'],
                        ['candidato.numero_orden', 'asc'],
                    ])->values();

                    $electos = $candidatosLista->take($p['bancas'])->values()->map(function($cand, $idx) use ($p) {
                        $cand->cociente_dhont = $p['votos'] / ($idx + 1);
                        $cand->sigla_partido = $p['sigla'];
                        $cand->numero_lista = $data->firstWhere('partido_id', $p['partido_id'])?->partido?->numero_lista ?? '-';
                        return $cand;
                    });
                    $electosPorLista = $electosPorLista->merge($electos);
                }
            }

            $electosOrdenados = $electosPorLista->sortByDesc('cociente_dhont');

            if ($electosOrdenados->isNotEmpty()) {
                $pdf->SetFont('helvetica', 'B', 8);
                $pdf->SetFillColor(70, 130, 180);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(10, 6, '#', 1, 0, 'C', true);
                $pdf->Cell(12, 6, 'Lista', 1, 0, 'C', true);
                $pdf->Cell(35, 6, 'Partido', 1, 0, 'C', true);
                $pdf->Cell(75, 6, 'Candidato', 1, 0, 'C', true);
                $pdf->Cell(25, 6, 'Votos', 1, 0, 'C', true);
                $pdf->Cell(25, 6, 'Cociente D\'Hondt', 1, 1, 'C', true);

                $pdf->SetFont('helvetica', '', 8);
                $pdf->SetTextColor(0, 0, 0);
                foreach ($electosOrdenados as $i => $cand) {
                    $fill = $i % 2 === 0;
                    $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                    $pdf->Cell(10, 6, $i + 1, 1, 0, 'C', true);
                    $pdf->Cell(12, 6, $cand->numero_lista, 1, 0, 'C', true);
                    $pdf->Cell(35, 6, $cand->sigla_partido, 1, 0, 'L', true);
                    $pdf->Cell(75, 6, $cand->candidato->nombre_completo ?? 'Sin nombre', 1, 0, 'L', true);
                    $pdf->Cell(25, 6, number_format($cand->total_votos, 0, ',', '.'), 1, 0, 'R', true);
                    $pdf->Cell(25, 6, number_format($cand->cociente_dhont, 2, ',', '.'), 1, 1, 'R', true);
                }
            } else {
                // Single winner (intendente, presidente, etc.)
                $ganador = $ordenadoPorBancas->firstWhere('bancas', '>', 0);
                if ($ganador) {
                    $partidoGanador = $data->firstWhere('partido_id', $ganador['partido_id']);
                    $todosCandPartido = ($todosCandidatos[$c][$ganador['partido_id']] ?? collect());
                    $prefPartido = $prefCargo->get($ganador['partido_id'], collect());
                    $candidatosLista = collect();
                    foreach ($todosCandPartido as $cand) {
                        $votoData = $prefPartido->firstWhere('candidato_id', $cand->id);
                        $item = new \stdClass();
                        $item->total_votos = $votoData ? (int)$votoData->total_votos : 0;
                        $item->candidato = $cand;
                        $candidatosLista->push($item);
                    }
                    $candidatosLista = $candidatosLista->sortBy([
                        ['total_votos', 'desc'],
                        ['candidato.numero_orden', 'asc'],
                    ])->values();
                    $primerCandidato = $candidatosLista->first();

                    $pdf->SetFont('helvetica', 'B', 10);
                    $pdf->Cell(0, 7, 'GANADOR:', 0, 1, 'L');
                    $pdf->SetFont('helvetica', '', 10);
                    $pdf->Cell(0, 6, 'Partido: ' . $ganador['sigla'] . ' (Lista ' . ($partidoGanador?->partido?->numero_lista ?? '-') . ')', 0, 1, 'L');
                    if ($primerCandidato && $primerCandidato->candidato) {
                        $pdf->Cell(0, 6, 'Candidato: ' . $primerCandidato->candidato->nombre_completo, 0, 1, 'L');
                    }
                    $pdf->Cell(0, 6, 'Votos: ' . number_format($ganador['votos'], 0, ',', '.'), 0, 1, 'L');
                }
            }

            // Preferencia votes table
            $pdf->Ln(5);
            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 7, 'Votos de Preferencia por Candidato', 0, 1, 'L');
            $pdf->Ln(1);

            $pdf->SetFont('helvetica', 'B', 7);
            $pdf->SetFillColor(70, 130, 180);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(10, 6, 'Lista', 1, 0, 'C', true);
            $pdf->Cell(25, 6, 'Partido', 1, 0, 'C', true);
            $pdf->Cell(70, 6, 'Candidato', 1, 0, 'C', true);
            $pdf->Cell(20, 6, 'Votos', 1, 0, 'C', true);
            $pdf->Cell(15, 6, 'Electo', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 7);
            $pdf->SetTextColor(0, 0, 0);
            foreach ($ordenadoPorBancas as $p) {
                $todosCandPartido = ($todosCandidatos[$c][$p['partido_id']] ?? collect());
                $prefPartido = $prefCargo->get($p['partido_id'], collect());
                $candidatos = collect();
                foreach ($todosCandPartido as $cand) {
                    $votoData = $prefPartido->firstWhere('candidato_id', $cand->id);
                    $item = new \stdClass();
                    $item->total_votos = $votoData ? (int)$votoData->total_votos : 0;
                    $item->candidato = $cand;
                    $candidatos->push($item);
                }
                $candidatos = $candidatos->sortBy([
                    ['total_votos', 'desc'],
                    ['candidato.numero_orden', 'asc'],
                ])->values();
                $bancasAsignadas = $p['bancas'];
                foreach ($candidatos as $idxCand => $cand) {
                    $esElecto = $idxCand < $bancasAsignadas && $bancasAsignadas > 0;
                    $fill = ($idxCand % 2 === 0);
                    if ($esElecto) {
                        $pdf->SetFillColor(200, 255, 200);
                    } else {
                        $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);
                    }
                    $pdf->Cell(10, 5, $data->firstWhere('partido_id', $p['partido_id'])?->partido?->numero_lista ?? '-', 1, 0, 'C', true);
                    $pdf->Cell(25, 5, $p['sigla'], 1, 0, 'L', true);
                    $pdf->Cell(70, 5, ($cand->candidato->numero_orden ?? '') . '. ' . ($cand->candidato->nombre_completo ?? 'Sin nombre'), 1, 0, 'L', true);
                    $pdf->Cell(20, 5, number_format($cand->total_votos, 0, ',', '.'), 1, 0, 'R', true);
                    $pdf->Cell(15, 5, $esElecto ? 'SI' : '-', 1, 1, 'C', true);
                }
            }

            // Footer summary
            $pdf->Ln(3);
            $totalVotosCargo = VotosMesa::where('cargo', $c)->where('tipo_voto', 'preferencia')->sum('cantidad_votos');
            $pdf->SetFont('helvetica', '', 8);
            $pdf->Cell(0, 5, 'Total de votos para ' . ucfirst($c) . ': ' . number_format($totalVotosCargo, 0, ',', '.'), 0, 1, 'L');
        }

        $pdf->Output('resultados_finales.pdf', 'I');
    }

    public function data(Request $request)
    {
        $query = VotosMesa::with(['mesa.equipo', 'partido', 'candidato', 'veedor', 'user']);
        if ($request->mesa_id) $query->where('mesa_id', $request->mesa_id);
        if ($request->partido_id) $query->where('partido_id', $request->partido_id);
        if ($request->cargo) $query->where('cargo', $request->cargo);
        if ($request->tipo_voto) $query->where('tipo_voto', $request->tipo_voto);

        return datatables($query)
            ->editColumn('cantidad_votos', fn($v) => number_format($v->cantidad_votos, 0, ',', '.'))
            ->editColumn('created_at', fn($v) => $v->created_at->format('d/m/Y H:i'))
            ->addColumn('actions', function ($v) {
                $btn = '<form action="' . route('certificados.destroy', $v->id) . '" method="POST" class="form-delete d-inline">';
                $btn .= csrf_field() . method_field('DELETE');
                $btn .= '<button type="submit" class="btn btn-danger btn-sm btn-delete" title="Eliminar"><i class="fas fa-trash"></i></button></form>';
                return $btn;
            })
            ->rawColumns(['actions'])
            ->toJson();
    }
}
