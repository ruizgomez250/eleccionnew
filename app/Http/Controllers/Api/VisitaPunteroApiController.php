<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\VisitaPuntero;
use App\Models\Puntero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class VisitaPunteroApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum');
    }

    public function misPunteros(Request $request)
    {
        $user = $request->user();

        $punteros = Puntero::query()
            ->whereHas('equipo', function ($query) use ($user) {
                $query->where('sist', $user->sistema);
            })
            ->orderByRaw('CASE WHEN idusuario = ? THEN 0 ELSE 1 END', [$user->id])
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'cedula', 'idusuario']);

        return response()->json([
            'success' => true,
            'data' => $punteros,
        ]);
    }

    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->where(function ($scope) use ($user) {
                    $scope->whereHas('puntero.dirigente.equipo', function ($q) use ($user) {
                        $q->where('sist', $user->sistema);
                    })->orWhere(function ($withoutPuntero) use ($user) {
                        $withoutPuntero->whereNull('idpuntero')
                            ->where('idusuario', $user->id);
                    });
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
            if ($request->filled('cedula')) {
                $query->where('cedula', $request->cedula);
            }
            if ($request->filled('fecha_desde')) {
                $query->where('fecha_visita', '>=', $request->fecha_desde);
            }
            if ($request->filled('fecha_hasta')) {
                $query->where('fecha_visita', '<=', $request->fecha_hasta . ' 23:59:59');
            }

            $visitas = $query->orderBy('fecha_visita', 'desc')
                ->paginate($request->get('per_page', 25));

            return response()->json([
                'success' => true,
                'data' => $visitas,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error index visitas', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las visitas',
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'idpuntero' => 'nullable|exists:puntero,id',
                'cedula' => 'required|string|max:20',
                'nombre_votante' => 'required|string|max:150',
                'apellido_votante' => 'nullable|string|max:150',
                'direccion' => 'nullable|string|max:255',
                'casa_de' => 'nullable|string|max:150',
                'cedula_votante' => 'nullable|string|max:20',
                'observacion' => 'nullable|string|max:500',
                'latitud' => 'nullable|numeric',
                'longitud' => 'nullable|numeric',
                'fecha_visita' => 'required|date',
                'resultado' => 'required|string|max:100',
                'proxima_visita' => 'nullable|date',
                'precision_gps' => 'nullable|numeric',
                'referencia' => 'nullable|string|max:255',
            ]);

            $validated['idusuario'] = $request->user()->id;

            $visita = VisitaPuntero::create($validated);
            $visita->load(['puntero.dirigente', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Visita registrada correctamente',
                'data' => $visita,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Error store visita', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar la visita',
            ], 500);
        }
    }

    public function show($id)
    {
        try {
            $visita = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                })
                ->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => $visita,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Visita no encontrada',
            ], 404);
        }
    }

    public function update(Request $request, $id)
    {
        try {
            $visita = VisitaPuntero::whereHas('puntero.dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->findOrFail($id);

            $validated = $request->validate([
                'idpuntero' => 'sometimes|exists:puntero,id',
                'cedula' => 'sometimes|string|max:20',
                'nombre_votante' => 'sometimes|string|max:150',
                'apellido_votante' => 'nullable|string|max:150',
                'direccion' => 'nullable|string|max:255',
                'casa_de' => 'nullable|string|max:150',
                'cedula_votante' => 'nullable|string|max:20',
                'observacion' => 'nullable|string|max:500',
                'latitud' => 'nullable|numeric',
                'longitud' => 'nullable|numeric',
                'fecha_visita' => 'sometimes|date',
                'resultado' => 'sometimes|string|max:100',
                'proxima_visita' => 'nullable|date',
                'precision_gps' => 'nullable|numeric',
                'referencia' => 'nullable|string|max:255',
            ]);

            $visita->update($validated);
            $visita->load(['puntero.dirigente', 'usuario']);

            return response()->json([
                'success' => true,
                'message' => 'Visita actualizada correctamente',
                'data' => $visita,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Error update visita', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la visita',
            ], 500);
        }
    }

    public function destroy($id)
    {
        try {
            $visita = VisitaPuntero::whereHas('puntero.dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->findOrFail($id);

            $visita->delete();

            return response()->json([
                'success' => true,
                'message' => 'Visita eliminada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('API Error destroy visita', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar la visita',
            ], 500);
        }
    }

    public function estadisticas(Request $request)
    {
        try {
            $user = $request->user();
            $query = VisitaPuntero::where(function ($scope) use ($user) {
                $scope->whereHas('puntero.dirigente.equipo', function ($q) use ($user) {
                    $q->where('sist', $user->sistema);
                })->orWhere(function ($withoutPuntero) use ($user) {
                    $withoutPuntero->whereNull('idpuntero')
                        ->where('idusuario', $user->id);
                });
            });

            $total = (clone $query)->count();
            $positivas = (clone $query)->where('resultado', 'LIKE', '%positivo%')->count();
            $negativas = (clone $query)->where('resultado', 'LIKE', '%negativo%')->count();
            $neutras = (clone $query)->where('resultado', 'LIKE', '%neutro%')->count();

            $hoy = (clone $query)->whereDate('fecha_visita', today())->count();
            $semana = (clone $query)->where('fecha_visita', '>=', now()->startOfWeek())->count();
            $mes = (clone $query)->where('fecha_visita', '>=', now()->startOfMonth())->count();

            $proximasVisitas = VisitaPuntero::where(function ($scope) use ($user) {
                $scope->whereHas('puntero.dirigente.equipo', function ($q) use ($user) {
                    $q->where('sist', $user->sistema);
                })->orWhere(function ($withoutPuntero) use ($user) {
                    $withoutPuntero->whereNull('idpuntero')
                        ->where('idusuario', $user->id);
                });
            })
            ->whereNotNull('proxima_visita')
            ->where('proxima_visita', '>=', now())
            ->count();

            return response()->json([
                'success' => true,
                'data' => [
                    'total_visitas' => $total,
                    'positivas' => $positivas,
                    'negativas' => $negativas,
                    'neutras' => $neutras,
                    'visitas_hoy' => $hoy,
                    'visitas_semana' => $semana,
                    'visitas_mes' => $mes,
                    'proximas_visitas' => $proximasVisitas,
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('API Error estadisticas visitas', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estadísticas',
            ], 500);
        }
    }

    public function porPuntero($idpuntero)
    {
        try {
            $visitas = VisitaPuntero::with(['puntero.dirigente', 'usuario'])
                ->whereHas('puntero.dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                })
                ->where('idpuntero', $idpuntero)
                ->orderBy('fecha_visita', 'desc')
                ->paginate(25);

            return response()->json([
                'success' => true,
                'data' => $visitas,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error porPuntero visitas', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las visitas del puntero',
            ], 500);
        }
    }
}
