<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\Puntero;
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

            // IDs de sistemas que el usuario puede ver (solo intendente/concejal)
            $sistemasQuery = Sistema::select('sistemas.id', 'sistemas.id_ciudad_electoral')
                ->whereRaw('LOWER(sistemas.tipo) IN (?, ?)', ['intendente', 'concejal'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('sistemas.idusuario', $userId)
                            ->orWhere('sistemas.id', $userSistema);
                    });
                });

            $sistemaIds = $sistemasQuery->pluck('id');

            if ($sistemaIds->isEmpty()) {
                return view('ciudades.index', ['totalesDistritos' => collect()]);
            }

            // Totales de dirigentes, punteros y votantes por ciudad electoral (1 sola query)
            $totalesEstructura = DB::table('sistemas')
                ->join('equipo', 'equipo.sist', '=', 'sistemas.id')
                ->join('dirigente', 'dirigente.id_equipo', '=', 'equipo.id')
                ->leftJoin('puntero', 'puntero.id_dirigente', '=', 'dirigente.id')
                ->leftJoin('votante', 'votante.idpuntero', '=', 'puntero.id')
                ->whereIn('sistemas.id', $sistemaIds)
                ->groupBy('sistemas.id_ciudad_electoral')
                ->select(
                    'sistemas.id_ciudad_electoral',
                    DB::raw('COUNT(DISTINCT dirigente.id) as total_dirigentes'),
                    DB::raw('COUNT(DISTINCT puntero.id) as total_punteros'),
                    DB::raw('COUNT(DISTINCT votante.id) as total_votantes')
                )
                ->get()
                ->keyBy('id_ciudad_electoral');

            // Intendentes y concejales por ciudad electoral (1 sola query)
            $candidaturasPorCiudad = DB::table('sistemas')
                ->whereIn('sistemas.id', $sistemaIds)
                ->groupBy('sistemas.id_ciudad_electoral', 'sistemas.tipo')
                ->select('sistemas.id_ciudad_electoral', 'sistemas.tipo', DB::raw('COUNT(*) as total'))
                ->get()
                ->groupBy('id_ciudad_electoral');

            // Datos de ciudades electorales involucradas
            $ciudadIds = $candidaturasPorCiudad->keys();
            $ciudadesMap = CiudadElectoral::whereIn('id', $ciudadIds)
                ->get()
                ->keyBy('id');

            // Construir resultado
            $totalesDistritos = [];

            foreach ($candidaturasPorCiudad as $ciudadId => $candidaturas) {
                $ciudad = $ciudadesMap[$ciudadId] ?? null;
                $ciudadNombre = $ciudad->descripcion ?? 'Sin ciudad';

                $intendentes = 0;
                $concejales = 0;
                foreach ($candidaturas as $c) {
                    if (strtolower(trim($c->tipo)) === 'intendente') $intendentes = $c->total;
                    if (strtolower(trim($c->tipo)) === 'concejal') $concejales = $c->total;
                }

                $estructura = $totalesEstructura[$ciudadId] ?? null;

                $totalesDistritos[$ciudadNombre] = [
                    'dirigentes' => (int) ($estructura->total_dirigentes ?? 0),
                    'punteros' => (int) ($estructura->total_punteros ?? 0),
                    'votantes' => (int) ($estructura->total_votantes ?? 0),
                    'intendentes' => $intendentes,
                    'concejales' => $concejales,
                    'id_ciudad_electoral' => $ciudadId,
                    'departamento' => $ciudad->departamento ?? ''
                ];
            }

            // Ordenar por departamento y nombre de ciudad
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

        // 🔹 Sistemas visibles según usuario (solo datos básicos, sin eager load pesado)
        $sistemasQuery = Sistema::select('id', 'nombre', 'tipo', 'id_ciudad_electoral')
            ->where('id_ciudad_electoral', $ciudad->id)
            ->whereRaw('LOWER(tipo) IN (?, ?)', ['intendente', 'concejal']);

        if (!in_array($userId, [1, 4])) {
            $sistemasQuery->where(function ($q) use ($userId, $userSistema) {
                $q->where('idusuario', $userId)
                    ->orWhere('id', $userSistema);
            });
        }

        $sistemas = $sistemasQuery->get();
        $sistemaIds = $sistemas->pluck('id');

        // 🔹 Totales por sistema via SQL (1 sola query en lugar de N+1)
        $totalesSistemas = [];

        if ($sistemaIds->isNotEmpty()) {
            $totalesQuery = DB::table('equipo')
                ->join('dirigente', 'dirigente.id_equipo', '=', 'equipo.id')
                ->leftJoin('puntero', 'puntero.id_dirigente', '=', 'dirigente.id')
                ->leftJoin('votante', 'votante.idpuntero', '=', 'puntero.id')
                ->whereIn('equipo.sist', $sistemaIds)
                ->groupBy('equipo.sist')
                ->select(
                    'equipo.sist',
                    DB::raw('COUNT(DISTINCT dirigente.id) as total_dirigentes'),
                    DB::raw('COUNT(DISTINCT puntero.id) as total_punteros'),
                    DB::raw('COUNT(DISTINCT votante.id) as total_votantes')
                )
                ->get()
                ->keyBy('sist');

            foreach ($sistemaIds as $id) {
                $fila = $totalesQuery[$id] ?? null;
                $totalesSistemas[$id] = [
                    'dirigentes' => (int) ($fila->total_dirigentes ?? 0),
                    'punteros' => (int) ($fila->total_punteros ?? 0),
                    'votantes' => (int) ($fila->total_votantes ?? 0),
                ];
            }
        }

        // 🔹 Retornamos la vista con sistemas + totales
        return view('ciudades.partials.sistemas_modal', compact('sistemas', 'totalesSistemas'))->render();
    }
    public function mostrarArbol()
    {
        try {
            $userId = Auth::id();
            $userSistema = Auth::user()->sistema;

            // Obtener los sistemas permitidos según el usuario (sin cargar votantes a memoria)
            $sistemas = Sistema::with(['ciudad', 'usuario'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->get();

            // Precargar conteos via DB para todos los sistemas de una sola vez
            $sistemaIds = $sistemas->pluck('id')->toArray();
            $conteosDirigentes = DB::table('dirigente')
                ->join('equipo', 'equipo.id', '=', 'dirigente.id_equipo')
                ->whereIn('equipo.sist', $sistemaIds)
                ->groupBy('equipo.sist')
                ->select('equipo.sist as sistema_id', DB::raw('COUNT(dirigente.id) as total'))
                ->get()
                ->keyBy('sistema_id');
            $conteosPunteros = DB::table('puntero')
                ->join('dirigente', 'dirigente.id', '=', 'puntero.id_dirigente')
                ->join('equipo', 'equipo.id', '=', 'dirigente.id_equipo')
                ->whereIn('equipo.sist', $sistemaIds)
                ->groupBy('equipo.sist')
                ->select('equipo.sist as sistema_id', DB::raw('COUNT(puntero.id) as total'))
                ->get()
                ->keyBy('sistema_id');
            $conteosVotantes = DB::table('votante')
                ->join('puntero', 'puntero.id', '=', 'votante.idpuntero')
                ->join('dirigente', 'dirigente.id', '=', 'puntero.id_dirigente')
                ->join('equipo', 'equipo.id', '=', 'dirigente.id_equipo')
                ->whereIn('equipo.sist', $sistemaIds)
                ->groupBy('equipo.sist')
                ->select('equipo.sist as sistema_id', DB::raw('COUNT(votante.id) as total'))
                ->get()
                ->keyBy('sistema_id');

            // Adjuntar conteos a cada sistema
            foreach ($sistemas as $sistema) {
                $sistema->total_dirigentes = $conteosDirigentes[$sistema->id]->total ?? 0;
                $sistema->total_punteros = $conteosPunteros[$sistema->id]->total ?? 0;
                $sistema->total_votantes = $conteosVotantes[$sistema->id]->total ?? 0;
            }

            // Definir los tipos de candidaturas
            $tiposCandidaturas = [
                'intendente',
                'concejal',
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
        ];

        // 🔹 CALCULAR TOTALES DEL SISTEMA (candidatura)
        $totalesSistema = $this->calcularTotalesSistema($sistema);

        return [
            'id' => $sistema->id,
            'nombre' => $sistema->nombre,
            'tipo' => $this->getTipoSlug($tipo),
            'tipo_nivel' => $nombresTipos[$tipo] ?? ucfirst($tipo),
            'totales' => $totalesSistema,
            'hijos' => []
        ];
    }

    /**
     * Calcular totales de un sistema específico (candidatura)
     */
    private function calcularTotalesSistema($sistema)
    {
        return [
            'dirigentes' => $sistema->total_dirigentes ?? 0,
            'punteros' => $sistema->total_punteros ?? 0,
            'votantes' => $sistema->total_votantes ?? 0,
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
            }

            $totales['total_dirigentes'] += $sistema->total_dirigentes ?? 0;
            $totales['total_punteros'] += $sistema->total_punteros ?? 0;
            $totales['total_votantes'] += $sistema->total_votantes ?? 0;
        }

        $totales['total_candidaturas'] = $totales['intendentes'] + $totales['concejales'];

        return $totales;
    }

    /**
     * Convertir tipo a slug para clases CSS
     */
    private function getTipoSlug($tipo)
    {
        $map = [
            'intendente' => 'intendente',
            'concejal' => 'concejal',
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

            $sistema = Sistema::with(['equipos'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->findOrFail($sistemaId);

            $dirigentes = Dirigente::with(['equipo'])
                ->withCount('punteros')
                ->whereHas('equipo', function ($q) use ($sistemaId) {
                    $q->where('sist', $sistemaId);
                })
                ->get();

            $dirigenteIds = $dirigentes->pluck('id');
            $votantesPorDirigente = DB::table('puntero')
                ->join('votante', 'votante.idpuntero', '=', 'puntero.id')
                ->whereIn('puntero.id_dirigente', $dirigenteIds)
                ->groupBy('puntero.id_dirigente')
                ->select('puntero.id_dirigente', DB::raw('COUNT(votante.id) as total'))
                ->get()
                ->keyBy('id_dirigente');

            foreach ($dirigentes as $dir) {
                $dir->votantes_count = $votantesPorDirigente[$dir->id]->total ?? 0;
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

            $sistema = Sistema::with(['ciudad'])
                ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                    $query->where(function ($q) use ($userId, $userSistema) {
                        $q->where('idusuario', $userId)
                            ->orWhere('id', $userSistema);
                    });
                })
                ->findOrFail($sistemaId);

            $punteros = Puntero::with(['dirigente', 'equipo'])
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaId) {
                    $q->where('sist', $sistemaId);
                })
                ->get();

            return view('arbol.partials.punteros', compact('punteros', 'sistema'));
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error al cargar punteros'], 500);
        }
    }
}
