<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Equipo;
use App\Models\PrePadron;
use App\Models\Sistema;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SistemaController extends Controller
{
    /**
     * Verifica que el usuario logueado tenga permisos.
     */
    private function verificarPermiso($sistema)
    {
        $userId = Auth::id();

        if (!in_array($userId, [1, 4]) && $sistema->idusuario != $userId) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        //$this->verificarPermiso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'sistema_id' => 'nullable|exists:sistemas,id',
            'id_ciudad_electoral' => 'required|exists:ciudades_electorales,id',
        ]);

        DB::beginTransaction();

        try {

            if ($request->sistema_id) {

                // 🔹 Actualizar sistema
                $sistema = Sistema::findOrFail($request->sistema_id);
                $sistema->nombre = $request->nombre;
                $sistema->id_ciudad_electoral = $request->id_ciudad_electoral;
                $sistema->tipo = $request->tipo;
                if ($request->candidatosup != 0) {
                    $sistema->idusuario = $request->candidatosup;
                }
                $sistema->save();

                DB::commit();
                return back()->with('success', 'Sistema actualizado correctamente');
            } else {
                $idaguardar=Auth::id();
                if ($request->candidatosup != 0) {
                    $idaguardar = $request->candidatosup;
                }
                // 🔹 Crear sistema
                $sistema = Sistema::create([
                    'nombre' => $request->nombre,
                    'id_ciudad_electoral' => $request->id_ciudad_electoral,
                    'idusuario' => $idaguardar,
                    'tipo' => $request->tipo
                ]);

                // 🔹 Obtener ciudad electoral
                $ciudad = CiudadElectoral::findOrFail($request->id_ciudad_electoral);

                // 🔹 Insert masivo filtrado por ciudad electoral
                DB::statement("
                INSERT INTO equipo (sist, ciudad, colegio, descripcion, created_at, updated_at)
                SELECT 
                    ? AS sist,
                    li.distrito_nombre AS ciudad,
                    li.local_interna AS colegio,
                    li.local_interna AS descripcion,
                    NOW(),
                    NOW()
                FROM locales_internas li
                LEFT JOIN equipo e
                    ON e.sist = ?
                    AND e.ciudad = li.distrito_nombre
                    AND e.colegio = li.local_interna
                WHERE e.id IS NULL
                AND li.distrito_nombre = ?
                AND li.departamento_nombre = ?
            ", [
                    $sistema->id,
                    $sistema->id,
                    $ciudad->descripcion,
                    $ciudad->departamento
                ]);

                DB::commit();

                return back()->with('success', 'Sistema creado correctamente con equipos generados');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $sistema = Sistema::findOrFail($id);
        $this->verificarPermiso($sistema); // 🔹 Verificar permiso

        try {


            // Verificar si tiene usuarios asignados antes de borrar
            if ($sistema->users()->count() > 0) {
                return back()->with('error', 'No se puede eliminar un sistema que tiene usuarios asignados. Primero debe eliminar el usuario');
            }

            $sistema->delete();

            return back()->with('success', 'Sistema eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            // Cuando no encuentra el sistema
            return back()->with('error', 'Sistema no encontrado');
        } catch (Exception $e) {

            // Otros errores generales
            return back()->with('error', 'Ocurrió un error al eliminar el sistema: ' . $e->getMessage());
        }
    }
    public function mostrarCiudades()
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;
            // 🔹 Obtener los sistemas permitidos según el usuario
            $sistemas = Sistema::with(['equipos.dirigentes.punteros.votantes', 'ciudad'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->get();
            // 🔹 Agrupar por ciudad y calcular totales
            $totalesDistritos = [];

            foreach ($sistemas as $sistema) {
                $ciudadNombre = $sistema->ciudad->descripcion ?? 'Sin ciudad';

                $totalDirigentes = $sistema->equipos->flatMap->dirigentes->count();
                $totalPunteros = $sistema->equipos->flatMap->dirigentes->sum(function ($d) {
                    return $d->punteros->count();
                });
                $totalVotantes = $sistema->equipos->flatMap->dirigentes->sum(function ($d) {
                    return $d->punteros->sum(function ($p) {
                        return $p->votantes->count();
                    });
                });

                // 🔹 Si la ciudad ya existe, sumamos los totales
                if (!isset($totalesDistritos[$ciudadNombre])) {
                    $totalesDistritos[$ciudadNombre] = [
                        'dirigentes' => $totalDirigentes,
                        'punteros' => $totalPunteros,
                        'votantes' => $totalVotantes,
                        'id_ciudad_electoral' => $sistema->id_ciudad_electoral,
                        'departamento' => $sistema->ciudad->departamento ?? ''
                    ];
                } else {
                    $totalesDistritos[$ciudadNombre]['dirigentes'] += $totalDirigentes;
                    $totalesDistritos[$ciudadNombre]['punteros'] += $totalPunteros;
                    $totalesDistritos[$ciudadNombre]['votantes'] += $totalVotantes;
                }
            }

            // 🔹 Ordenar por departamento y nombre de ciudad
            $totalesDistritos = collect($totalesDistritos)
                ->sortBy(['departamento', function ($item) {
                    return $item['descripcion'] ?? '';
                }]);

            return view('ciudades.index', [
                'totalesDistritos' => $totalesDistritos
            ]);
        } catch (\Exception $e) {
            return back()->with('error', 'Ocurrió un error al cargar las ciudades: ' . $e->getMessage());
        }
    }
    // public function sistemasPorDistrito($idCiudad)
    // {
    //     $userId = Auth::id();
    //     $userSistema = Auth::user()->sistema;
    //     // 🔹 Buscamos la ciudad electoral por ID
    //     $ciudad = CiudadElectoral::find($idCiudad);
    //     if (!$ciudad) {
    //         return response()->json([
    //             'error' => 'Distrito no encontrado'
    //         ], 404);
    //     }

    //     // 🔹 Solo los sistemas que el usuario puede ver
    //     if (in_array($userId, [1, 4])) {
    //         $sistemas = Sistema::where('id_ciudad_electoral', $ciudad->id)->get();
    //     } else {
    //         $sistemas = Sistema::where('id_ciudad_electoral', $ciudad->id)
    //             ->where('idusuario', $userId)
    //             ->orWhere('id', $userSistema)
    //             ->get();
    //     }

    //     // 🔹 Retornamos solo el HTML parcial para el modal
    //     return view('ciudades.partials.sistemas_modal', compact('sistemas'))->render();
    // }
    public function sistemasPorDistrito($idCiudad)
    {
        $userId = Auth::id();
        $userSistema = Auth::user()->sistema;

        // 🔹 Buscamos la ciudad electoral por ID
        $ciudad = CiudadElectoral::find($idCiudad);
        if (!$ciudad) {
            return response()->json([
                'error' => 'Distrito no encontrado'
            ], 404);
        }

        // 🔹 Sistemas visibles según usuario
        if (in_array($userId, [1, 4])) {
            $sistemas = Sistema::where('id_ciudad_electoral', $ciudad->id)->get();
        } else {
            $sistemas = Sistema::where('id_ciudad_electoral', $ciudad->id)
                ->where(function ($q) use ($userId, $userSistema) {
                    $q->where('idusuario', $userId)
                        ->orWhere('id', $userSistema);
                })
                ->get();
        }

        // 🔹 Calculamos totales por sistema
        $totalesSistemas = [];

        foreach ($sistemas as $sistema) {

            $totalDirigentes = $sistema->equipos->flatMap->dirigentes->count();

            $totalPunteros = $sistema->equipos->flatMap->dirigentes->sum(function ($d) {
                return $d->punteros->count();
            });

            $totalVotantes = $sistema->equipos->flatMap->dirigentes->sum(function ($d) {
                return $d->punteros->sum(function ($p) {
                    return $p->votantes->count();
                });
            });

            $totalesSistemas[$sistema->id] = [
                'dirigentes' => $totalDirigentes,
                'punteros' => $totalPunteros,
                'votantes' => $totalVotantes,
            ];
        }

        // 🔹 Retornamos la vista con sistemas + totales
        return view('ciudades.partials.sistemas_modal', compact('sistemas', 'totalesSistemas'))->render();
    }
}
