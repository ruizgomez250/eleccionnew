<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\Puntero;
use App\Models\VisitaPuntero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VisitaPunteroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function index(Request $request)
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();
        $punteros = Puntero::with(['dirigente'])
            ->whereHas('dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->get();

        if ($request->ajax()) {
            $query = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                });

            if ($request->filled('puntero_id')) {
                $query->where('idpuntero', $request->puntero_id);
            }
            if ($request->filled('equipo_id')) {
                $query->whereHas('puntero', function ($q) use ($request) {
                    $q->where('id_equipo', $request->equipo_id);
                });
            }
            if ($request->filled('resultado')) {
                $query->where('resultado', 'LIKE', "%{$request->resultado}%");
            }
            if ($request->filled('fecha_desde')) {
                $query->where('fecha_visita', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->where('fecha_visita', '<=', $request->fecha_hasta . ' 23:59:59');
            }

            $visitas = $query->orderBy('fecha_visita', 'desc')->get();

            return response()->json([
                'success' => true,
                'data' => $visitas->map(function ($v) {
                    return [
                        'id' => $v->id,
                        'puntero_nombre' => $v->puntero->nombre ?? 'N/A',
                        'puntero_cedula' => $v->puntero->cedula ?? '',
                        'dirigente_nombre' => $v->puntero->dirigente->nombre ?? 'N/A',
                        'cedula' => $v->cedula,
                        'nombre_votante' => $v->nombre_votante,
                        'apellido_votante' => $v->apellido_votante,
                        'direccion' => $v->direccion,
                        'casa_de' => $v->casa_de,
                        'resultado' => $v->resultado,
                        'fecha_visita' => $v->fecha_visita->format('d/m/Y H:i'),
                        'proxima_visita' => $v->proxima_visita ? $v->proxima_visita->format('d/m/Y H:i') : '-',
                        'observacion' => $v->observacion,
                        'latitud' => $v->latitud,
                        'longitud' => $v->longitud,
                        'usuario_nombre' => $v->usuario->name ?? 'N/A',
                    ];
                }),
            ]);
        }

        return view('visita-puntero.index', compact('equipos', 'punteros'));
    }

    public function create(Request $request)
    {
        $punteros = Puntero::with(['dirigente'])
            ->whereHas('dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->get();

        $punteroSeleccionado = $request->query('puntero_id');

        return view('visita-puntero.create', compact('punteros', 'punteroSeleccionado'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            $request->validate([
                'idpuntero' => 'required|exists:puntero,id',
                'nombre_votante' => 'required|string|max:150',
                'cedula' => 'required|string|max:20',
                'fecha_visita' => 'required|date',
                'resultado' => 'required|string|max:100',
            ]);

            $visita = VisitaPuntero::create([
                'idpuntero' => $request->idpuntero,
                'cedula' => $request->cedula,
                'nombre_votante' => $request->nombre_votante,
                'apellido_votante' => $request->apellido_votante,
                'direccion' => $request->direccion,
                'casa_de' => $request->casa_de,
                'cedula_votante' => $request->cedula_votante,
                'observacion' => $request->observacion,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'fecha_visita' => $request->fecha_visita,
                'resultado' => $request->resultado,
                'proxima_visita' => $request->proxima_visita ?: null,
                'precision_gps' => $request->precision_gps,
                'referencia' => $request->referencia,
                'idusuario' => Auth::id(),
            ]);

            DB::commit();

            $puntero = Puntero::find($request->idpuntero);

            return redirect()->route('visita-puntero.index')
                ->with('successAlert', 'Visita registrada correctamente para ' . $puntero->nombre);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors())
                ->with('errorAlert', 'Error de validación');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear visita', ['error' => $e->getMessage()]);
            return redirect()->back()->withInput()
                ->with('errorAlert', 'Error al guardar la visita: ' . $e->getMessage());
        }
    }

    public function edit($id)
    {
        $visita = VisitaPuntero::with(['puntero.dirigente'])->findOrFail($id);

        $punteros = Puntero::with(['dirigente'])
            ->whereHas('dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->get();

        return view('visita-puntero.edit', compact('visita', 'punteros'));
    }

    public function update(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            $visita = VisitaPuntero::findOrFail($id);

            $request->validate([
                'nombre_votante' => 'required|string|max:150',
                'cedula' => 'required|string|max:20',
                'fecha_visita' => 'required|date',
                'resultado' => 'required|string|max:100',
            ]);

            $visita->update([
                'idpuntero' => $request->idpuntero,
                'cedula' => $request->cedula,
                'nombre_votante' => $request->nombre_votante,
                'apellido_votante' => $request->apellido_votante,
                'direccion' => $request->direccion,
                'casa_de' => $request->casa_de,
                'cedula_votante' => $request->cedula_votante,
                'observacion' => $request->observacion,
                'latitud' => $request->latitud,
                'longitud' => $request->longitud,
                'fecha_visita' => $request->fecha_visita,
                'resultado' => $request->resultado,
                'proxima_visita' => $request->proxima_visita ?: null,
                'precision_gps' => $request->precision_gps,
                'referencia' => $request->referencia,
            ]);

            DB::commit();

            return redirect()->route('visita-puntero.index')
                ->with('successAlert', 'Visita actualizada correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return redirect()->back()->withInput()->withErrors($e->errors())
                ->with('errorAlert', 'Error de validación');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar visita', ['id' => $id, 'error' => $e->getMessage()]);
            return redirect()->back()->withInput()
                ->with('errorAlert', 'Error al actualizar: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $visita = VisitaPuntero::findOrFail($id);
            $visita->delete();

            return response()->json([
                'success' => true,
                'message' => 'Visita eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar visita', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar la visita',
            ], 500);
        }
    }

    // ==================== REPORTES ====================

    public function reporteVisitas()
    {
        $punteros = Puntero::with(['dirigente'])
            ->whereHas('dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->get();

        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        return view('reportes.visitas-loading', compact('punteros', 'equipos'));
    }

    public function reporteVisitasData(Request $request)
    {
        try {
            $query = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                });

            if ($request->filled('puntero_id')) {
                $query->where('idpuntero', $request->puntero_id);
            }
            if ($request->filled('equipo_id')) {
                $query->whereHas('puntero', function ($q) use ($request) {
                    $q->where('id_equipo', $request->equipo_id);
                });
            }
            if ($request->filled('fecha_desde')) {
                $query->where('fecha_visita', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->where('fecha_visita', '<=', $request->fecha_hasta . ' 23:59:59');
            }

            $visitas = $query->orderBy('fecha_visita', 'desc')->get();

            // Agrupar por resultado
            $porResultado = $visitas->groupBy('resultado')->map(function ($group) {
                return $group->count();
            });

            // Agrupar por puntero
            $porPuntero = $visitas->groupBy(function ($v) {
                return $v->puntero->nombre ?? 'Sin puntero';
            })->map(function ($group, $nombre) {
                return [
                    'total' => $group->count(),
                    'positivas' => $group->where('resultado', 'LIKE', '%positivo%')->count(),
                    'negativas' => $group->where('resultado', 'LIKE', '%negativo%')->count(),
                ];
            })->sortByDesc('total')->take(15);

            // Visitas por día (últimos 30 días)
            $visitasPorDia = $visitas->filter(function ($v) {
                return $v->fecha_visita->diffInDays(now()) <= 30;
            })->groupBy(function ($v) {
                return $v->fecha_visita->format('d/m');
            })->map(function ($group) {
                return $group->count();
            })->sortKeys();

            // Próximas visitas agendadas
            $proximasVisitas = VisitaPuntero::with(['puntero'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                })
                ->whereNotNull('proxima_visita')
                ->where('proxima_visita', '>=', now())
                ->orderBy('proxima_visita', 'asc')
                ->take(10)
                ->get();

            // Estadísticas
            $totalVisitas = $visitas->count();
            $totalPositivas = $visitas->where('resultado', 'LIKE', '%positivo%')->count();
            $totalNegativas = $visitas->where('resultado', 'LIKE', '%negativo%')->count();
            $totalNeutras = $visitas->where('resultado', 'LIKE', '%neutro%')->count();

            $html = view('reportes.visitas-content', compact(
                'visitas',
                'porResultado',
                'porPuntero',
                'visitasPorDia',
                'proximasVisitas',
                'totalVisitas',
                'totalPositivas',
                'totalNegativas',
                'totalNeutras'
            ))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte visitas data', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function reporteVisitasDetalle(Request $request)
    {
        try {
            $punteroId = $request->query('puntero_id');
            $tipo = $request->query('tipo', 'todas');

            $query = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                });

            if ($punteroId) {
                $query->where('idpuntero', $punteroId);
            }

            if ($tipo === 'positivas') {
                $query->where('resultado', 'LIKE', '%positivo%');
            } elseif ($tipo === 'negativas') {
                $query->where('resultado', 'LIKE', '%negativo%');
            }

            $visitas = $query->orderBy('fecha_visita', 'desc')->get();

            $html = view('reportes.visitas-detalle', compact('visitas', 'tipo', 'punteroId'))->render();

            return response()->json([
                'success' => true,
                'html' => $html,
            ]);
        } catch (\Exception $e) {
            Log::error('Error en reporte visitas detalle', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar el detalle',
            ], 500);
        }
    }
}
