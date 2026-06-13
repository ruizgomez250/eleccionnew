<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Equipo;
use App\Models\PrePadron;
use App\Models\Sistema;
use App\Models\Sistemaspadre;
use App\Models\User;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

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


                // Si se proporcionaron datos de usuario, crear el usuario
                if ($request->filled('user_name') && $request->filled('user_email')) {
                    $userId = Auth::id();
                    $user = User::create([
                        'name' => $request->user_name,
                        'email' => $request->user_email,
                        'password' => Hash::make($request->password),
                        'sistema' => $sistema->id,
                        'idusuario' => $userId,
                    ]);
                    $user->syncRoles($request->roles);
                }
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
                $idCiudadElectoral = $sistema->id_ciudad_electoral; // o como se llame el campo

                // Suma por tipo 'Concejal' para esta ciudad electoral
                $sumaConcejal = Sistema::where('tipo', 'Concejal')
                    ->where('id_ciudad_electoral', $idCiudadElectoral)
                    ->count();

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
                        'concejales' => $sumaConcejal,
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

            // Obtener los sistemas permitidos según el usuario
            $sistemas = Sistema::with(['equipos.dirigentes.punteros.votantes', 'ciudad', 'usuario'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->get();

            // Definir los tipos de candidaturas
            $tiposCandidaturas = [
                'intendente',
                'concejal',
                'convencional',
                'convencional juventud',
                'miembro de comite',
                'miembro de la juventud',
                'miembro del consejo'
            ];

            // Filtrar solo candidaturas
            $sistemasCandidaturas = $sistemas->filter(function ($sistema) use ($tiposCandidaturas) {
                $tipo = strtolower(trim($sistema->tipo ?? ''));
                return in_array($tipo, $tiposCandidaturas);
            });

            // Construir árbol jerárquico basado en sistemaspadres
            $arbolJerarquico = $this->construirArbolConSistemaspadres($sistemasCandidaturas);

            return view('arbol.index', [
                'arbolJerarquico' => $arbolJerarquico
            ]);
        } catch (\Exception $e) {
            dd('Error capturado: ' . $e->getMessage(), $e->getTraceAsString());
        }
    }

    /**
     * Construir árbol jerárquico usando sistemaspadres
     */
    private function construirArbolConSistemaspadres(Collection $sistemas)
    {
        if ($sistemas->isEmpty()) {
            return [];
        }

        // Agrupar por ciudad (distrito)
        $sistemasPorCiudad = $sistemas->groupBy(function ($sistema) {
            return $sistema->id_ciudad_electoral ?? 'sin_ciudad';
        });

        $arbol = [];

        foreach ($sistemasPorCiudad as $ciudadId => $sistemasCiudad) {
            $ciudad = $sistemasCiudad->first()->ciudad;
            $nombreCiudad = $ciudad ? $ciudad->descripcion : 'Sin Ciudad';
            $departamento = $ciudad ? $ciudad->departamento : 'Sin Departamento';

            // Construir la jerarquía dentro del distrito usando sistemaspadres
            $jerarquia = $this->construirJerarquiaPorDistrito($sistemasCiudad);

            // Calcular totales del distrito
            $totales = $this->calcularTotalesDistrito($sistemasCiudad);

            $nodoDistrito = [
                'id' => $ciudadId,
                'nombre' => $nombreCiudad,
                'tipo' => 'distrito',
                'tipo_nivel' => 'Distrito',
                'departamento' => $departamento,
                'totales' => $totales,
                'hijos' => $jerarquia // Los nodos raíz del distrito
            ];

            $arbol[] = $nodoDistrito;
        }

        // Ordenar distritos por departamento y nombre
        usort($arbol, function ($a, $b) {
            if ($a['departamento'] == $b['departamento']) {
                return strcmp($a['nombre'], $b['nombre']);
            }
            return strcmp($a['departamento'], $b['departamento']);
        });

        return $arbol;
    }

    /**
     * Construir jerarquía dentro de un distrito usando sistemaspadres
     */
    private function construirJerarquiaPorDistrito(Collection $sistemas)
    {
        // Crear un mapa de sistemas por ID
        $sistemasMap = [];
        foreach ($sistemas as $sistema) {
            $sistemasMap[$sistema->id] = $sistema;
        }

        // Obtener todas las relaciones padre-hijo de sistemaspadres para estos sistemas
        $relaciones = Sistemaspadre::whereIn('idsistema', array_keys($sistemasMap))
            ->get()
            ->keyBy('idsistema');

        // Construir estructura jerárquica
        $nodos = [];
        $hijosPorPadre = [];

        // Primero, crear todos los nodos
        foreach ($sistemas as $sistema) {
            $nodos[$sistema->id] = $this->crearNodoSistema($sistema);
        }

        // Luego, establecer las relaciones padre-hijo
        foreach ($sistemas as $sistema) {
            $relacion = $relaciones->get($sistema->id);
            $idPadre = $relacion ? $relacion->idsistemapadre : null;

            if ($idPadre && isset($nodos[$idPadre])) {
                // Este sistema tiene un padre válido dentro del mismo distrito
                $hijosPorPadre[$idPadre][] = $nodos[$sistema->id];
            }
        }

        // Asignar hijos a sus padres
        foreach ($hijosPorPadre as $padreId => $hijos) {
            if (isset($nodos[$padreId])) {
                // Ordenar hijos por tipo (para mantener consistencia)
                usort($hijos, function ($a, $b) {
                    $orden = [
                        'intendente' => 1,
                        'concejal' => 2,
                        'convencional' => 3,
                        'convencional_juventud' => 4,
                        'miembro_comite' => 5,
                        'miembro_juventud' => 6,
                        'miembro_del_consejo' => 7
                    ];
                    $ordenA = $orden[$a['tipo']] ?? 99;
                    $ordenB = $orden[$b['tipo']] ?? 99;
                    return $ordenA <=> $ordenB;
                });
                $nodos[$padreId]['hijos'] = $hijos;
            }
        }

        // Encontrar los nodos raíz (los que no tienen padre en este distrito)
        $raices = [];
        foreach ($sistemas as $sistema) {
            $relacion = $relaciones->get($sistema->id);
            $idPadre = $relacion ? $relacion->idsistemapadre : null;

            // Es raíz si no tiene padre o el padre no está en este distrito
            if (!$idPadre || !isset($nodos[$idPadre])) {
                $raices[] = $nodos[$sistema->id];
            }
        }

        // Ordenar raíces por tipo (intendentes primero, luego concejales, etc.)
        usort($raices, function ($a, $b) {
            $orden = [
                'intendente' => 1,
                'concejal' => 2,
                'convencional' => 3,
                'convencional_juventud' => 4,
                'miembro_comite' => 5,
                'miembro_juventud' => 6,
                'miembro_del_consejo' => 7
            ];
            $ordenA = $orden[$a['tipo']] ?? 99;
            $ordenB = $orden[$b['tipo']] ?? 99;
            return $ordenA <=> $ordenB;
        });

        return $raices;
    }

    /**
     * Crear nodo para un sistema
     */
    /**
     * Crear nodo para un sistema
     */
    private function crearNodoSistema($sistema)
    {
        $tipo = strtolower(trim($sistema->tipo ?? ''));

        // Mapeo de tipos a nombres legibles
        $nombresTipos = [
            'intendente' => 'Intendente',
            'concejal' => 'Concejal',
            'convencional' => 'Convencional',
            'convencional juventud' => 'Convencional Juventud',
            'miembro de comite' => 'Miembro de Comité',
            'miembro de la juventud' => 'Miembro de la Juventud',
            'miembro del consejo' => 'Miembro del Consejo'
        ];

        // 🔹 CALCULAR TOTALES DEL SISTEMA (candidatura)
        $totalesSistema = $this->calcularTotalesSistema($sistema);

        return [
            'id' => $sistema->id,
            'nombre' => $sistema->nombre,
            'tipo' => $this->getTipoSlug($tipo),
            'tipo_nivel' => $nombresTipos[$tipo] ?? ucfirst($tipo),
            'totales' => $totalesSistema,  // 🔹 AGREGAR TOTALES DEL SISTEMA
            'candidatos' => $this->obtenerCandidatos($sistema),
            'hijos' => [] // Se llenará después
        ];
    }

    /**
     * Calcular totales de un sistema específico (candidatura)
     */
    private function calcularTotalesSistema($sistema)
    {
        $totalDirigentes = 0;
        $totalPunteros = 0;
        $totalVotantes = 0;

        foreach ($sistema->equipos as $equipo) {
            foreach ($equipo->dirigentes as $dirigente) {
                $totalDirigentes++;
                foreach ($dirigente->punteros as $puntero) {
                    $totalPunteros++;
                    $totalVotantes += $puntero->votantes->count();
                }
            }
        }

        return [
            'dirigentes' => $totalDirigentes,
            'punteros' => $totalPunteros,
            'votantes' => $totalVotantes,
        ];
    }

    /**
     * Calcular totales de un distrito
     */
    private function calcularTotalesDistrito(Collection $sistemas)
    {
        $totales = [
            'intendentes' => 0,
            'concejales' => 0,
            'convencionales' => 0,
            'convencionales_juventud' => 0,
            'miembros_comite' => 0,
            'miembros_juventud' => 0,
            'miembros_consejo' => 0,
            'total_candidaturas' => 0,
            'total_dirigentes' => 0,
            'total_punteros' => 0,
            'total_votantes' => 0
        ];

        foreach ($sistemas as $sistema) {
            $tipo = strtolower(trim($sistema->tipo ?? ''));

            switch ($tipo) {
                case 'intendente':
                    $totales['intendentes']++;
                    break;
                case 'concejal':
                    $totales['concejales']++;
                    break;
                case 'convencional':
                    $totales['convencionales']++;
                    break;
                case 'convencional juventud':
                    $totales['convencionales_juventud']++;
                    break;
                case 'miembro de comite':
                    $totales['miembros_comite']++;
                    break;
                case 'miembro de la juventud':
                    $totales['miembros_juventud']++;
                    break;
                case 'miembro del consejo':
                    $totales['miembros_consejo']++;
                    break;
            }

            // Sumar dirigentes, punteros y votantes
            $candidatosSistema = $this->obtenerCandidatos($sistema);
            $totales['total_dirigentes'] += count($candidatosSistema);
            $totales['total_punteros'] += collect($candidatosSistema)->sum(function ($d) {
                return count($d['punteros'] ?? []);
            });
            $totales['total_votantes'] += collect($candidatosSistema)->sum(function ($d) {
                return collect($d['punteros'] ?? [])->sum(function ($p) {
                    return count($p['votantes'] ?? []);
                });
            });
        }

        $totales['total_candidaturas'] = $totales['intendentes'] + $totales['concejales'] +
            $totales['convencionales'] + $totales['convencionales_juventud'] +
            $totales['miembros_comite'] + $totales['miembros_juventud'] +
            $totales['miembros_consejo'];

        return $totales;
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
     * Convertir tipo a slug para clases CSS
     */
    private function getTipoSlug($tipo)
    {
        $map = [
            'intendente' => 'intendente',
            'concejal' => 'concejal',
            'convencional' => 'convencional',
            'convencional juventud' => 'convencional_juventud',
            'miembro de comite' => 'miembro_comite',
            'miembro de la juventud' => 'miembro_juventud',
            'distrito' => 'distrito'
        ];

        return $map[$tipo] ?? strtolower(str_replace(' ', '_', $tipo));
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
            return response()->json(['error' => 'Error al cargar dirigentes'], 500);
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
            return response()->json(['error' => 'Error al cargar punteros'], 500);
        }
    }
}
