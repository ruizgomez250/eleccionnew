<?php

namespace App\Http\Controllers;

use App\Models\Candidato;
use App\Models\Mesa;
use App\Models\Partido;
use App\Models\VotosMesa;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class VotoController extends Controller
{
    public function cargarResultadosMesa(Request $request)
    {
        // Validación del request
        $validator = Validator::make($request->all(), [
            'codigo_mesa' => 'required|string',
            'resultados' => 'required|array',
            'resultados.*.numero_lista' => 'required|string',
            'resultados.*.cargo' => 'required|string',
            'resultados.*.votos' => 'required|integer|min:0',
            'preferencias' => 'sometimes|array',
            'preferencias.*.numero_lista' => 'required|string',
            'preferencias.*.cargo' => 'required|string',
            'preferencias.*.votos_por_candidato' => 'required|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            DB::beginTransaction();

            // Buscar o crear la mesa
            $mesa = Mesa::firstOrCreate(
                ['codigo_mesa' => $request->codigo_mesa],
                ['departamento' => $request->departamento ?? 'N/A', 'distrito' => $request->distrito ?? 'N/A']
            );

            // Registrar votos por lista
            foreach ($request->resultados as $resultado) {
                $partido = Partido::firstOrCreate(
                    ['numero_lista' => $resultado['numero_lista']],
                    ['nombre' => $resultado['nombre_lista'] ?? 'Desconocido']
                );

                VotosMesa::updateOrCreate(
                    [
                        'mesa_id' => $mesa->id,
                        'partido_id' => $partido->id,
                        'candidato_id' => null,
                        'cargo' => $resultado['cargo'],
                        'tipo_voto' => 'lista',
                    ],
                    [
                        'cantidad_votos' => $resultado['votos'],
                    ]
                );
            }

            // Registrar preferencias (votos individuales por candidato)
            if ($request->has('preferencias')) {
                foreach ($request->preferencias as $preferencia) {
                    $partido = Partido::where('numero_lista', $preferencia['numero_lista'])->first();
                    
                    if ($partido) {
                        foreach ($preferencia['votos_por_candidato'] as $orden => $votos) {
                            $candidato = Candidato::where('partido_id', $partido->id)
                                ->where('numero_orden', $orden)
                                ->where('cargo', $preferencia['cargo'])
                                ->first();

                            if ($candidato) {
                                VotosMesa::updateOrCreate(
                                    [
                                        'mesa_id' => $mesa->id,
                                        'partido_id' => $partido->id,
                                        'candidato_id' => $candidato->id,
                                        'cargo' => $preferencia['cargo'],
                                        'tipo_voto' => 'preferencia',
                                    ],
                                    [
                                        'cantidad_votos' => $votos,
                                    ]
                                );
                            }
                        }
                    }
                }
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Resultados cargados correctamente',
                'data' => [
                    'mesa_id' => $mesa->id,
                    'codigo_mesa' => $mesa->codigo_mesa
                ]
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los resultados',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function resultadosGenerales(Request $request)
    {
        $resultados = DB::table('votos_mesa')
            ->join('partidos', 'votos_mesa.partido_id', '=', 'partidos.id')
            ->join('mesas', 'votos_mesa.mesa_id', '=', 'mesas.id')
            ->select(
                'partidos.numero_lista',
                'partidos.nombre as nombre_partido',
                'votos_mesa.cargo',
                DB::raw('SUM(votos_mesa.cantidad_votos) as total_votos')
            )
            ->where('votos_mesa.tipo_voto', 'lista')
            ->groupBy('partidos.id', 'votos_mesa.cargo')
            ->orderBy('votos_mesa.cargo')
            ->orderBy('total_votos', 'DESC')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $resultados
        ]);
    }
}