<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Mesa;
use App\Models\Partido;
use App\Models\Candidato;
use App\Models\VotosMesa;
use App\Models\Veedor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VotoController extends Controller
{
    public function cargarResultadosMesa(Request $request)
    {
        try {
            $request->validate([
                'codigo_mesa' => 'required|string',
                'resultados' => 'required|array',
                'resultados.*.partido_id' => 'required|exists:partidos,id',
                'resultados.*.cargo' => 'required|string',
                'resultados.*.cantidad_votos' => 'required|integer|min:0',
                'resultados.*.tipo_voto' => 'required|in:lista,preferencia',
                'resultados.*.candidato_id' => 'nullable|exists:candidatos,id',
                'veedor_id' => 'nullable|exists:veedores,id',
                'dispositivo_id' => 'nullable|string',
            ]);

            DB::beginTransaction();

            $mesa = Mesa::where('codigo_mesa', $request->codigo_mesa)->first();

            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            $registros = [];

            foreach ($request->resultados as $item) {
                $voto = VotosMesa::updateOrCreate(
                    [
                        'mesa_id' => $mesa->id,
                        'partido_id' => $item['partido_id'],
                        'candidato_id' => $item['tipo_voto'] === 'preferencia' ? ($item['candidato_id'] ?? null) : null,
                        'cargo' => $item['cargo'],
                        'tipo_voto' => $item['tipo_voto'],
                    ],
                    [
                        'cantidad_votos' => $item['cantidad_votos'],
                        'origen' => 'apk',
                        'escaneado_en' => now(),
                        'escaneado_por' => $request->veedor_id ? 'veedor:' . $request->veedor_id : 'dispositivo:' . $request->dispositivo_id,
                        'dispositivo_id' => $request->dispositivo_id,
                        'veedor_id' => $request->veedor_id,
                    ]
                );

                $registros[] = $voto->id;
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resultados cargados correctamente',
                'data' => [
                    'mesa' => $mesa->codigo_mesa,
                    'registros' => $registros
                ]
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en cargarResultadosMesa: ' . $e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar resultados: ' . $e->getMessage()
            ], 500);
        }
    }

    public function cargarResultadosMesaJson(Request $request)
    {
        return $this->cargarResultadosMesa($request);
    }

    public function resultadosGenerales(Request $request)
    {
        try {
            $resultados = VotosMesa::select(
                'partido_id',
                'cargo',
                DB::raw('SUM(cantidad_votos) as total_votos')
            )
                ->where('tipo_voto', 'lista')
                ->groupBy('partido_id', 'cargo')
                ->with('partido:id,nombre,sigla,numero_lista,color_hex')
                ->get()
                ->groupBy('cargo');

            return response()->json([
                'success' => true,
                'data' => $resultados
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resultadosPorCargo($cargo)
    {
        try {
            $resultados = VotosMesa::select(
                'partido_id',
                DB::raw('SUM(cantidad_votos) as total_votos')
            )
                ->where('cargo', $cargo)
                ->where('tipo_voto', 'lista')
                ->groupBy('partido_id')
                ->with('partido:id,nombre,sigla,numero_lista,color_hex')
                ->orderByDesc('total_votos')
                ->get();

            $preferencias = VotosMesa::select(
                'candidato_id',
                DB::raw('SUM(cantidad_votos) as total_votos')
            )
                ->where('cargo', $cargo)
                ->where('tipo_voto', 'preferencia')
                ->whereNotNull('candidato_id')
                ->groupBy('candidato_id')
                ->with('candidato:id,nombre_completo,numero_orden,partido_id')
                ->orderByDesc('total_votos')
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'lista' => $resultados,
                    'preferencias' => $preferencias
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function resultadosPorMesa($codigoMesa)
    {
        try {
            $mesa = Mesa::where('codigo_mesa', $codigoMesa)->first();

            if (!$mesa) {
                return response()->json([
                    'success' => false,
                    'message' => 'Mesa no encontrada'
                ], 404);
            }

            $resultados = VotosMesa::where('mesa_id', $mesa->id)
                ->with(['partido:id,nombre,sigla,numero_lista', 'candidato:id,nombre_completo,numero_orden'])
                ->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'mesa' => $mesa->codigo_mesa,
                    'resultados' => $resultados
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listarMesas()
    {
        try {
            $mesas = Mesa::select('id', 'codigo_mesa', 'departamento', 'distrito', 'direccion')
                ->orderBy('codigo_mesa')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $mesas
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listarPartidos()
    {
        try {
            $partidos = Partido::activos()
                ->select('id', 'numero_lista', 'nombre', 'sigla', 'color_hex')
                ->orderBy('numero_lista')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $partidos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function listarCandidatosPorPartido($partidoId, $cargo)
    {
        try {
            $candidatos = Candidato::where('partido_id', $partidoId)
                ->where('cargo', $cargo)
                ->activos()
                ->ordenados()
                ->select('id', 'nombre_completo', 'documento', 'numero_orden', 'foto_url')
                ->get();

            return response()->json([
                'success' => true,
                'data' => $candidatos
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }

    public function estadisticasGenerales()
    {
        try {
            $totalMesas = Mesa::count();
            $mesasConVotos = VotosMesa::distinct('mesa_id')->count('mesa_id');
            $totalVotos = VotosMesa::sum('cantidad_votos');
            $totalVotosLista = VotosMesa::where('tipo_voto', 'lista')->sum('cantidad_votos');
            $totalVotosPreferencia = VotosMesa::where('tipo_voto', 'preferencia')->sum('cantidad_votos');

            return response()->json([
                'success' => true,
                'data' => [
                    'total_mesas' => $totalMesas,
                    'mesas_con_votos' => $mesasConVotos,
                    'total_votos' => $totalVotos,
                    'votos_lista' => $totalVotosLista,
                    'votos_preferencia' => $totalVotosPreferencia,
                    'porcentaje_mesas' => $totalMesas > 0 ? round(($mesasConVotos / $totalMesas) * 100, 2) : 0,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
