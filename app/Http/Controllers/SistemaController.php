<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Equipo;
use App\Models\PrePadron;
use App\Models\Sistema;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

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
                $idaguardar = Auth::id();
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
    public function mostrarArbol()
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;

            // 🔹 Definir los tipos de candidaturas permitidas
            $tiposCandidaturas = [
                'Intendente',
                'Concejal',
                'Convencional',
                'Convencional Juventud',
                'Miembro de Comite',
                'Miembro de la Juventud'
            ];

            // 🔹 Obtener los sistemas permitidos según el usuario
            $sistemas = Sistema::with(['equipos.dirigentes.punteros.votantes', 'ciudad', 'usuario'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->get();

            // 🔹 DEPURACIÓN: Ver cuántos sistemas hay en total
            Log::info('Total sistemas encontrados: ' . $sistemas->count());
            Log::info('Tipos de sistemas encontrados:', $sistemas->pluck('tipo')->toArray());

            // 🔹 Filtrar solo candidaturas
            $sistemasCandidaturas = $sistemas->filter(function ($sistema) use ($tiposCandidaturas) {
                return in_array($sistema->tipo, $tiposCandidaturas);
            });

            // 🔹 DEPURACIÓN: Ver cuántas candidaturas hay
            Log::info('Total candidaturas filtradas: ' . $sistemasCandidaturas->count());

            // 🔹 Construir árbol jerárquico
            $arbolJerarquico = $this->construirArbolPorUsuario($sistemasCandidaturas);

            // 🔹 DEPURACIÓN: Ver el árbol construido
            Log::info('Árbol jerárquico:', ['count' => count($arbolJerarquico), 'data' => $arbolJerarquico]);

            // 🔹 Calcular totales por distrito
            $totalesDistritos = $this->calcularTotalesPorDistrito($sistemas);

            // 🔹 DEPURACIÓN: También puedes pasar los sistemas a la vista para debug
            return view('arbol.index', [
                'arbolJerarquico' => $arbolJerarquico,
                'totalesDistritos' => $totalesDistritos,
                'debug_sistemas' => $sistemas, // Para depuración
                'debug_candidaturas' => $sistemasCandidaturas // Para depuración
            ]);
        } catch (\Exception $e) {
            Log::error('Error en mostrarArbol: ' . $e->getMessage());
            return back()->with('error', 'Ocurrió un error al cargar las ciudades: ' . $e->getMessage());
        }
    }
    /**
     * Construir árbol jerárquico basado en el usuario que creó cada sistema
     */
    private function construirArbolPorUsuario(Collection $sistemas)
    {
        // Si no hay sistemas, retornar array vacío
        if ($sistemas->isEmpty()) {
            return [];
        }

        // Agrupar sistemas por usuario (idusuario)
        $sistemasPorUsuario = $sistemas->groupBy('idusuario');

        $arbol = [];

        foreach ($sistemasPorUsuario as $usuarioId => $sistemasUsuario) {
            // Obtener información del usuario
            $usuario = $sistemasUsuario->first()->usuario;
            $nombreUsuario = $usuario ? $usuario->name : 'Usuario ' . $usuarioId;

            // Buscar intendentes de este usuario
            $intendentes = $sistemasUsuario->filter(function ($sistema) {
                return $sistema->tipo === 'Intendente';
            });

            // Si hay intendentes, agregarlos como nodos principales
            if ($intendentes->count() > 0) {
                foreach ($intendentes as $intendente) {
                    $nodoIntendente = [
                        'id' => $intendente->id,
                        'nombre' => $intendente->nombre,
                        'tipo' => 'intendente',
                        'tipo_nivel' => 'Intendente',
                        'ciudad' => $intendente->ciudad ? $intendente->ciudad->descripcion : 'Sin ciudad',
                        'departamento' => $intendente->ciudad ? $intendente->ciudad->departamento : 'Sin departamento',
                        'candidatos' => $this->obtenerCandidatos($intendente),
                        'concejales' => $this->getHijosPorTipo($sistemasUsuario, $intendente->id, 'Concejal'),
                        'hijos' => []
                    ];

                    $arbol[] = $nodoIntendente;
                }
            } else {
                // Si no hay intendentes, buscar concejales directamente
                $concejales = $sistemasUsuario->filter(function ($sistema) {
                    return $sistema->tipo === 'Concejal';
                });

                if ($concejales->count() > 0) {
                    // Crear nodo virtual para agrupar concejales sin intendente
                    $nodoVirtual = [
                        'id' => null,
                        'nombre' => 'Sin Intendente',
                        'tipo' => 'intendente_virtual',
                        'tipo_nivel' => 'Sin Intendente',
                        'ciudad' => $sistemasUsuario->first()->ciudad ? $sistemasUsuario->first()->ciudad->descripcion : 'Sin ciudad',
                        'departamento' => $sistemasUsuario->first()->ciudad ? $sistemasUsuario->first()->ciudad->departamento : 'Sin departamento',
                        'candidatos' => [],
                        'concejales' => $this->getHijosPorTipo($sistemasUsuario, null, 'Concejal'),
                        'hijos' => []
                    ];

                    $arbol[] = $nodoVirtual;
                } else {
                    // Si no hay intendentes ni concejales, mostrar otros niveles
                    $otrosNiveles = $this->getOtrosNiveles($sistemasUsuario);
                    if (!empty($otrosNiveles)) {
                        $nodoVirtual = [
                            'id' => null,
                            'nombre' => 'Sin Intendente',
                            'tipo' => 'intendente_virtual',
                            'tipo_nivel' => 'Sin Intendente',
                            'ciudad' => $sistemasUsuario->first()->ciudad ? $sistemasUsuario->first()->ciudad->descripcion : 'Sin ciudad',
                            'departamento' => $sistemasUsuario->first()->ciudad ? $sistemasUsuario->first()->ciudad->departamento : 'Sin departamento',
                            'candidatos' => [],
                            'hijos' => $otrosNiveles,
                            'concejales' => []
                        ];

                        $arbol[] = $nodoVirtual;
                    }
                }
            }
        }

        // Ordenar por departamento y ciudad
        usort($arbol, function ($a, $b) {
            if ($a['departamento'] == $b['departamento']) {
                return strcmp($a['ciudad'], $b['ciudad']);
            }
            return strcmp($a['departamento'], $b['departamento']);
        });

        return $arbol;
    }

    /**
     * Obtener hijos de un tipo específico para un usuario
     */
    private function getHijosPorTipo(Collection $sistemas, $parentId, $tipo)
    {
        // Filtrar sistemas del mismo usuario y del tipo especificado
        $hijos = $sistemas->filter(function ($sistema) use ($tipo) {
            return $sistema->tipo === $tipo;
        });

        $hijosNodos = [];

        foreach ($hijos as $hijo) {
            $nodo = [
                'id' => $hijo->id,
                'nombre' => $hijo->nombre,
                'tipo' => $this->getTipoSlug($tipo),
                'tipo_nivel' => $tipo,
                'candidatos' => $this->obtenerCandidatos($hijo),
                'hijos' => []
            ];

            // Agregar subniveles según el tipo
            switch ($tipo) {
                case 'Concejal':
                    $nodo['convencionales'] = $this->getHijosPorTipo($sistemas, $hijo->id, 'Convencional');
                    break;
                case 'Convencional':
                    $nodo['convencionales_juventud'] = $this->getHijosPorTipo($sistemas, $hijo->id, 'Convencional Juventud');
                    break;
                case 'Convencional Juventud':
                    $nodo['miembros_comite'] = $this->getHijosPorTipo($sistemas, $hijo->id, 'Miembro de Comite');
                    break;
                case 'Miembro de Comite':
                    $nodo['miembros_juventud'] = $this->getHijosPorTipo($sistemas, $hijo->id, 'Miembro de la Juventud');
                    break;
            }

            $hijosNodos[] = $nodo;
        }

        return $hijosNodos;
    }

    /**
     * Obtener otros niveles cuando no hay intendentes ni concejales
     */
    private function getOtrosNiveles(Collection $sistemas)
    {
        $niveles = [
            'Convencional' => 'convencionales',
            'Convencional Juventud' => 'convencionales_juventud',
            'Miembro de Comite' => 'miembros_comite',
            'Miembro de la Juventud' => 'miembros_juventud'
        ];

        $resultado = [];

        foreach ($niveles as $tipo => $key) {
            $items = $sistemas->filter(function ($sistema) use ($tipo) {
                return $sistema->tipo === $tipo;
            });

            foreach ($items as $item) {
                $nodo = [
                    'id' => $item->id,
                    'nombre' => $item->nombre,
                    'tipo' => $this->getTipoSlug($tipo),
                    'tipo_nivel' => $tipo,
                    'candidatos' => $this->obtenerCandidatos($item),
                    'hijos' => []
                ];

                // Agregar subniveles si existen
                if ($tipo === 'Convencional') {
                    $nodo['convencionales_juventud'] = $this->getHijosPorTipo($sistemas, $item->id, 'Convencional Juventud');
                } elseif ($tipo === 'Convencional Juventud') {
                    $nodo['miembros_comite'] = $this->getHijosPorTipo($sistemas, $item->id, 'Miembro de Comite');
                } elseif ($tipo === 'Miembro de Comite') {
                    $nodo['miembros_juventud'] = $this->getHijosPorTipo($sistemas, $item->id, 'Miembro de la Juventud');
                }

                $resultado[] = $nodo;
            }
        }

        return $resultado;
    }

    /**
     * Convertir tipo a slug para clases CSS
     */
    private function getTipoSlug($tipo)
    {
        $map = [
            'Intendente' => 'intendente',
            'Concejal' => 'concejal',
            'Convencional' => 'convencional',
            'Convencional Juventud' => 'convencional_juventud',
            'Miembro de Comite' => 'miembro_comite',
            'Miembro de la Juventud' => 'miembro_juventud'
        ];

        return $map[$tipo] ?? strtolower(str_replace(' ', '_', $tipo));
    }

    /**
     * Obtener candidatos (dirigentes, punteros, votantes)
     */
    private function obtenerCandidatos($sistema)
    {
        $candidatos = [];

        foreach ($sistema->equipos as $equipo) {
            foreach ($equipo->dirigentes as $dirigente) {
                $dirigenteData = [
                    'id' => $dirigente->id,
                    'nombre' => $dirigente->nombre,
                    'cedula' => $dirigente->cedula,
                    'telefono' => $dirigente->telefono,
                    'tipo' => 'dirigente',
                    'punteros' => []
                ];

                foreach ($dirigente->punteros as $puntero) {
                    $punteroData = [
                        'id' => $puntero->id,
                        'nombre' => $puntero->nombre,
                        'cedula' => $puntero->cedula,
                        'telefono' => $puntero->telefono,
                        'tipo' => 'puntero',
                        'votantes' => $puntero->votantes->map(function ($votante) {
                            return [
                                'id' => $votante->id,
                                'nombre' => $votante->nombre,
                                'cedula' => $votante->cedula,
                                'tipo' => 'votante'
                            ];
                        })->toArray()
                    ];

                    $dirigenteData['punteros'][] = $punteroData;
                }

                $candidatos[] = $dirigenteData;
            }
        }

        return $candidatos;
    }

    /**
     * Calcular totales por distrito (funcionalidad existente)
     */
    private function calcularTotalesPorDistrito(Collection $sistemas)
    {
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

            if (!isset($totalesDistritos[$ciudadNombre])) {
                $totalesDistritos[$ciudadNombre] = [
                    'dirigentes' => $totalDirigentes,
                    'punteros' => $totalPunteros,
                    'votantes' => $totalVotantes,
                    'id_ciudad_electoral' => $sistema->id_ciudad_electoral,
                    'departamento' => $sistema->ciudad->departamento ?? '',
                    'descripcion' => $ciudadNombre
                ];
            } else {
                $totalesDistritos[$ciudadNombre]['dirigentes'] += $totalDirigentes;
                $totalesDistritos[$ciudadNombre]['punteros'] += $totalPunteros;
                $totalesDistritos[$ciudadNombre]['votantes'] += $totalVotantes;
            }
        }

        return collect($totalesDistritos)->sortBy(['departamento', 'descripcion']);
    }

    /**
     * API: Obtener dirigentes de un sistema
     */
    public function getDirigentes($sistemaId)
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;

            $sistema = Sistema::with(['equipos.dirigentes.punteros.votantes'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->findOrFail($sistemaId);

            $dirigentes = [];
            foreach ($sistema->equipos as $equipo) {
                foreach ($equipo->dirigentes as $dirigente) {
                    $dirigentes[] = $dirigente;
                }
            }

            return view('arbol.partials.dirigentes', compact('dirigentes', 'sistema'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar dirigentes: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Obtener punteros de un sistema
     */
    public function getPunteros($sistemaId)
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;

            $sistema = Sistema::with(['equipos.dirigentes.punteros'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->findOrFail($sistemaId);

            $punteros = [];
            foreach ($sistema->equipos as $equipo) {
                foreach ($equipo->dirigentes as $dirigente) {
                    foreach ($dirigente->punteros as $puntero) {
                        $punteros[] = $puntero;
                    }
                }
            }

            return view('arbol.partials.punteros', compact('punteros', 'sistema'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar punteros: ' . $e->getMessage()], 500);
        }
    }

    /**
     * API: Obtener sistemas por ciudad
     */
    public function getSistemasByCiudad($ciudadId)
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;

            $sistemas = Sistema::with(['ciudad', 'usuario'])
                ->where('id_ciudad_electoral', $ciudadId)
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->get();

            return view('arbol.partials.sistemas-ciudad', compact('sistemas'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar sistemas: ' . $e->getMessage()], 500);
        }
    }
}
