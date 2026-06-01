<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Puntero;
use App\Models\Equipo;
use App\Models\PadronIluminado;
use App\Models\Sistema;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class PunteroController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Puntero', [
            'only' => ['index', 'create', 'store', 'destroy', 'edit', 'update', 'show', 'createWithDirigente', 'createWithEquipo']
        ]);
    }

    // Mostrar todos los punteros
    public function index(Request $request)
    {
        $id_equipo = $request->id_equipo;
        return redirect()->route('puntero.createWithEquipo', ['id_equipo' => $id_equipo]);
    }

    public function createWithDirigente($dirigenteId = null)
    {
        $dirigentes = Dirigente::whereHas('equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->get();

        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();
        $punteros = Puntero::whereHas('dirigente.equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->get();

        if ($dirigenteId) {
            $punteros = Puntero::where('id_dirigente', $dirigenteId)
                ->whereHas('dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                })->get();
        }

        return view('puntero.index', compact('equipos', 'dirigentes', 'punteros', 'dirigenteId'));
    }

    public function indexByEquipo($equipoId)
    {
        $equipo = Equipo::where('sist', Auth::user()->sistema)
            ->findOrFail($equipoId);

        $punteros = $equipo->punteros()->whereHas('dirigente.equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->get();

        return view('puntero.index', compact('punteros', 'equipo'));
    }

    public function createWithEquipo($equipoId)
    {
        $equipo = Equipo::where('sist', Auth::user()->sistema)
            ->findOrFail($equipoId);

        return view('puntero.create', compact('equipo'));
    }

    public function store(Request $request)
    {
        //dd($request->input());
        DB::beginTransaction();

        try {
            $idEquipo = $request->id_equipo;
            $cedula = $request->cedula;
            $idDirigente = $request->id_dirigente;

            // Buscar dirigente
            $dirigente = Dirigente::find($idDirigente);

            if (!$dirigente) {
                return redirect()->back()
                    ->with('errorAlert', 'Error: no se encontró el dirigente.')
                    ->with('abrirModalPuntero', true);
            }
            if ($idEquipo == null) {
                $idEquipo = $dirigente->id_equipo;
                $request->merge([
                    'id_equipo' => $idEquipo
                ]);
            }

            // Si no viene equipo en el request usar el del dirigente



            // Validar que el equipo exista y pertenezca al sistema del usuario
            $equipoActual = Equipo::where('sist', Auth::user()->sistema)
                ->find($idEquipo);

            if (!$equipoActual) {
                throw new Exception('El equipo seleccionado no existe.');
            }


            $equipoActual = $dirigente->equipo;
            $sistemaActual = $equipoActual->sist ?? 'default';

            $punteroMismoSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', $sistemaActual);
                })->first();

            if ($punteroMismoSistema) {
                $mensaje = "Error: esta cédula ya está registrada en el mismo sistema bajo el dirigente '{$punteroMismoSistema->dirigente->nombre}'.";
                return redirect()->back()
                    ->with('errorAlert', $mensaje)
                    ->with('abrirModalPuntero', true)
                    ->with('punteroIdDirigente', $idDirigente);
            }

            $punteroOtroSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', '!=', $sistemaActual);
                })->first();

            $nuevoPuntero = Puntero::create($request->all());

            DB::commit();

            $mensaje = 'Puntero agregado correctamente.';
            if ($punteroOtroSistema) {
                $mensaje = "Atención: esta cédula ya existe en otro sistema bajo el dirigente '{$punteroOtroSistema->dirigente->nombre}'.";
            }

            return redirect()->back()
                ->with('successAlert', $mensaje)
                ->with('abrirModalPuntero', true)
                ->with('punteroIdDirigente', $idDirigente)
                ->with('dirigentenombre', $dirigente->nombre);
        } catch (Exception $e) {
            DB::rollBack();
            Log::error('Error al crear puntero', [
                'cedula' => $request->cedula,
                'dirigente_id' => $request->id_dirigente,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('errorAlert', 'Error: ' . $e->getMessage())
                ->with('abrirModalPuntero', true)
                ->with('punteroIdDirigente', $request->id_dirigente);
        }
    }

    public function create(Request $request, $id_equipo = null)
    {

        $id_dirigente = $request->query('dirigente_id');

        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        $dirigentes = Dirigente::whereHas('equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->when($id_equipo, fn($q) => $q->where('id_equipo', $id_equipo))
            ->get();

        $punteros = Puntero::with(['dirigente', 'equipo', 'votantes'])
            ->whereHas('dirigente.equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->when($id_equipo, function ($query) use ($id_equipo) {
                return $query->where('id_equipo', $id_equipo);
            })
            ->when($id_dirigente, function ($query) use ($id_dirigente) {
                return $query->where('id_dirigente', $id_dirigente);
            })
            ->get();

        foreach ($punteros as $p) {
            $p->votantes_count = $p->votantes->count();
        }

        $totalVotantesGeneral = $punteros->sum(fn($p) => $p->votantes_count);

        return view('puntero.index', compact('equipos', 'dirigentes', 'punteros', 'id_equipo', 'id_dirigente', 'totalVotantesGeneral'));
    }

    public function edit(Puntero $puntero)
    {
        return view('puntero.edit', compact('puntero'));
    }

    public function update(Request $request, Puntero $puntero)
    {
        $puntero->update($request->all());
        return redirect()->route('puntero.index')
            ->with('success', 'Puntero actualizado correctamente');
    }

    public function destroy(String $id)
    {
        try {
            $puntero = Puntero::findOrFail($id);
            $dirigente = Dirigente::find($puntero->id_dirigente);

            $puntero->votantes()->delete();
            $puntero->delete();

            return redirect()->back()
                ->with('successAlert', 'Puntero y sus votantes eliminados correctamente.')
                ->with('abrirModalPuntero', true)
                ->with('punteroIdDirigente', $dirigente->id)
                ->with('dirigentenombre', $dirigente->nombre);
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('errorAlert', 'No se pudo eliminar el puntero.');
        }
    }
    public function destroyAjax(Request $request)
    {
        try {
            $puntero = Puntero::findOrFail($request->id);
            $dirigente = Dirigente::find($puntero->id_dirigente);

            $puntero->votantes()->delete();
            $puntero->delete();

            return response()->json([
                'success' => true,
                'message' => 'Puntero y sus votantes eliminados correctamente.',
                'dirigente_id' => $dirigente->id
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo eliminar el puntero.'
            ], 500);
        }
    }

    public function show(Puntero $puntero)
    {
        return view('puntero.show', compact('puntero'));
    }
    public function storeAjax(Request $request)
    {
        DB::beginTransaction();

        try {
            // Estos valores vienen del formulario con los hidden
            $idDirigente = $request->id_dirigente; // Del hidden
            $cedula = $request->cedula;
            $idEquipo = $request->id_equipo;

            // Validación básica
            if (!$idDirigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: no se especificó el dirigente.'
                ], 422);
            }

            // Buscar dirigente
            $dirigente = Dirigente::find($idDirigente);

            if (!$dirigente) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error: no se encontró el dirigente.'
                ], 404);
            }

            // Si no viene equipo, usar el del dirigente
            if ($idEquipo == null) {
                $idEquipo = $dirigente->id_equipo;
            }

            // Validar que el equipo exista y pertenezca al sistema del usuario
            $equipoActual = Equipo::where('sist', Auth::user()->sistema)
                ->find($idEquipo);

            if (!$equipoActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo seleccionado no existe.'
                ], 404);
            }

            $sistemaActual = $equipoActual->sist ?? 'default';

            // Validar si ya existe en el mismo sistema
            $punteroMismoSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', $sistemaActual);
                })->first();

            if ($punteroMismoSistema) {
                return response()->json([
                    'success' => false,
                    'message' => "Error: esta cédula ya está registrada en el mismo sistema bajo el dirigente '{$punteroMismoSistema->dirigente->nombre}'."
                ], 422);
            }

            // Verificar si existe en otro sistema (solo para advertencia)
            $punteroOtroSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', '!=', $sistemaActual);
                })->first();

            // Crear el puntero con los datos del formulario
            $nuevoPuntero = Puntero::create([
                'cedula' => $cedula,
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'barrio' => $request->barrio,
                'id_dirigente' => $idDirigente,
                'id_equipo' => $idEquipo
            ]);

            DB::commit();

            // Cargar relaciones para la respuesta
            $nuevoPuntero->load(['dirigente', 'dirigente.equipo']);

            $mensaje = 'Puntero agregado correctamente.';
            if ($punteroOtroSistema) {
                $mensaje = "Atención: esta cédula ya existe en otro sistema bajo el dirigente '{$punteroOtroSistema->dirigente->nombre}'.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $nuevoPuntero,
                'dirigente_id' => $idDirigente,
                'dirigente_nombre' => $dirigente->nombre
            ], 200);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear puntero (AJAX)', [
                'cedula' => $request->cedula,
                'dirigente_id' => $request->id_dirigente,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el puntero: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtener punteros por sistema (para el modal)
     */
    public function porSistema($sistemaId)
    {
        try {
            $user = Auth::user();
            $user->sistema = $sistemaId;
            $user->save();
            $sistema = Sistema::findOrFail($sistemaId);

            // Obtener equipos del sistema
            $equipos = Equipo::where('sist', $sistema->id)
                ->with('dirigentes.punteros')
                ->get();

            // Obtener todos los punteros del sistema
            $punteros = Puntero::whereHas('dirigente.equipo', function ($q) use ($sistema) {
                $q->where('sist', $sistema->id);
            })->with(['dirigente', 'equipo', 'votantes', 'vehiculos'])->get(); // Agregar 'vehiculos' a with

            // Contar votantes y vehículos por puntero
            foreach ($punteros as $p) {
                $p->votantes_count = $p->votantes->count();
                $p->vehiculos_count = $p->vehiculos->count(); // Agregar contador de vehículos
            }

            // Total de votantes general
            $totalVotantesGeneral = $punteros->sum('votantes_count');

            // Obtener todos los dirigentes del sistema para el filtro
            $dirigentes = Dirigente::whereHas('equipo', function ($q) use ($sistema) {
                $q->where('sist', $sistema->id);
            })->get();

            // Valores seleccionados (si vienen por request)
            $equipoSeleccionado = request('equipo_id');
            $dirigenteSeleccionado = request('dirigente_id');
            $dirigenteId = request('dirigente_id');

            // Aplicar filtros si vienen
            if ($equipoSeleccionado) {
                $punteros = $punteros->where('id_equipo', $equipoSeleccionado);
            }

            if ($dirigenteSeleccionado) {
                $punteros = $punteros->where('id_dirigente', $dirigenteSeleccionado);
            }

            if (request()->ajax()) {
                return view('ciudades.partials.lista_punteros', compact(
                    'equipos',
                    'punteros',
                    'dirigentes',
                    'totalVotantesGeneral',
                    'equipoSeleccionado',
                    'dirigenteSeleccionado',
                    'dirigenteId'
                ));
            }

            return view('puntero.index', compact(
                'equipos',
                'punteros',
                'dirigentes',
                'totalVotantesGeneral',
                'equipoSeleccionado',
                'dirigenteSeleccionado',
                'dirigenteId'
            ));
        } catch (\Exception $e) {
            Log::error('Error en porSistema: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'Error al cargar punteros'], 500);
            }
            return redirect()->back()->with('error', 'Error al cargar punteros');
        }
    }
    public function porEquipo($equipoId)
    {
        try {
            $equipoAct = Equipo::findOrFail($equipoId);
            $sistema = Sistema::findOrFail($equipoAct->sist);

            // Obtener equipos del sistema
            $equipos = Equipo::where('sist', $sistema->id)
                ->with('dirigentes.punteros')
                ->get();

            // Obtener todos los punteros del sistema
            $punteros = Puntero::where('id_equipo', $equipoId)->get();

            // Contar votantes por puntero
            foreach ($punteros as $p) {
                $p->votantes_count = $p->votantes->count();
            }

            // Total de votantes general
            $totalVotantesGeneral = $punteros->sum('votantes_count');

            // Obtener todos los dirigentes del sistema para el filtro
            $dirigentes = Dirigente::whereHas('equipo', function ($q) use ($sistema) {
                $q->where('sist', $sistema->id);
            })->get();

            // Valores seleccionados (si vienen por request)
            $equipoSeleccionado = $equipoId;
            $dirigenteSeleccionado = request('dirigente_id');
            $dirigenteId = request('dirigente_id');

            // Aplicar filtros si vienen
            if ($equipoSeleccionado) {
                $punteros = $punteros->where('id_equipo', $equipoSeleccionado);
            }

            if ($dirigenteSeleccionado) {
                $punteros = $punteros->where('id_dirigente', $dirigenteSeleccionado);
            }

            if (request()->ajax()) {
                return view('ciudades.partials.lista_punteros', compact(
                    'equipos',
                    'punteros',
                    'dirigentes',
                    'totalVotantesGeneral',
                    'equipoSeleccionado',
                    'dirigenteSeleccionado',
                    'dirigenteId'
                ));
            }

            return view('puntero.index', compact(
                'equipos',
                'punteros',
                'dirigentes',
                'totalVotantesGeneral',
                'equipoSeleccionado',
                'dirigenteSeleccionado',
                'dirigenteId'
            ));
        } catch (\Exception $e) {
            Log::error('Error en porSistema: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'Error al cargar punteros'], 500);
            }
            return redirect()->back()->with('error', 'Error al cargar punteros');
        }
    }
    public function porDirigente($dirigenteId)
    {
        try {
            $dirigente = Dirigente::findOrFail($dirigenteId);
            //$equipo = Equipo::findOrFail($dirigente->id_equipo);
            $sistema = Sistema::findOrFail(Auth::user()->sistema);
            $nombreSistema = $sistema->nombre;

            // Obtener equipos del sistema
            $equipos = Equipo::where('sist', $sistema->id)
                ->with('dirigentes.punteros')
                ->get();

            // Obtener todos los punteros del dirigente con sus relaciones
            $punteros = Puntero::where('id_dirigente', $dirigenteId)
                ->with(['votantes', 'vehiculos']) // Agregar 'vehiculos' a with
                ->get();

            // Contar votantes y vehículos por puntero
            foreach ($punteros as $p) {
                $p->votantes_count = $p->votantes->count();
                $p->vehiculos_count = $p->vehiculos->count(); // Agregar contador de vehículos
            }

            // Total de votantes general
            $totalVotantesGeneral = $punteros->sum('votantes_count');

            // Obtener todos los dirigentes del sistema para el filtro
            $dirigentes = Dirigente::whereHas('equipo', function ($q) use ($sistema) {
                $q->where('sist', $sistema->id);
            })->get();

            // Valores seleccionados (si vienen por request)
            $equipoSeleccionado = null;
            $dirigenteSeleccionado = $dirigenteId;
            $dirigenteId = $dirigenteId;

            if (request()->ajax()) {
                return view('ciudades.partials.lista_punteros', compact(
                    'equipos',
                    'punteros',
                    'dirigentes',
                    'totalVotantesGeneral',
                    'equipoSeleccionado',
                    'dirigenteSeleccionado',
                    'nombreSistema',
                    'dirigenteId'
                ));
            }

            return view('puntero.index', compact(
                'equipos',
                'punteros',
                'dirigentes',
                'totalVotantesGeneral',
                'equipoSeleccionado',
                'dirigenteSeleccionado',
                'dirigenteId',
                'nombreSistema'
            ));
        } catch (\Exception $e) {
            Log::error('Error en porDirigente: ' . $e->getMessage());
            if (request()->ajax()) {
                return response()->json(['error' => 'Error al cargar punteros'], 500);
            }
            return redirect()->back()->with('error', 'Error al cargar punteros');
        }
    }

    /**
     * Filtrar punteros vía AJAX
     */
    public function filtrarAjax(Request $request)
    {
        //dd($request->input());
        try {
            $equipoId = $request->equipo_id;
            $dirigenteId = $request->dirigente_id;

            $query = Puntero::with(['dirigente', 'equipo', 'votantes'])
                ->whereHas('dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                });

            if ($equipoId) {
                $query->where('id_equipo', $equipoId);
            }

            if ($dirigenteId) {
                $query->where('id_dirigente', $dirigenteId);
            }

            $punteros = $query->get();

            // Contar votantes por puntero
            foreach ($punteros as $p) {
                $p->votantes_count = $p->votantes->count();
            }

            $totalVotantesGeneral = $punteros->sum('votantes_count');

            // Obtener equipos y dirigentes para los filtros
            $equipos = Equipo::where('sist', Auth::user()->sistema)->get();
            $dirigentes = Dirigente::whereHas('equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })->get();
            $equipoSeleccionado = $equipoId;
            $dirigenteSeleccionado = $dirigenteId;

            return view('ciudades.partials.lista_punteros', compact(
                'punteros',
                'equipos',
                'dirigentes',
                'totalVotantesGeneral',
                'equipoId',
                'equipoSeleccionado',
                'dirigenteSeleccionado',
                'dirigenteId'
            ));
        } catch (\Exception $e) {
            Log::error('Error en filtrarAjax: ' . $e->getMessage());
            return response()->json(['error' => 'Error al filtrar punteros'], 500);
        }
    }
    public function getVehiculos($id)
    {
        try {
            $puntero = Puntero::with('vehiculos')->find($id);

            if (!$puntero) {
                return response()->json([]);
            }
            $vehiculos = $puntero->vehiculos->map(function ($v) {
                return [
                    'id' => $v->id,
                    'chapa' => $v->chapa,
                    'nombre' => $v->nombre,
                    'cedulachofer' => $v->cedulachofer,
                    'telefono1' => $v->telefono1,
                    'rol' => $v->rol,
                    'nombreproponente' => $v->nombreproponente,
                    'telefonoproponente' => $v->telefonoproponente,
                ];
            });

            return response()->json($vehiculos);
        } catch (\Exception $e) {
            return response()->json([], 500);
        }
    }
    public function buscarPersonas(Request $request)
    {
        try {
            $nombre = $request->get('nombre');
            $apellido = $request->get('apellido');

            $query = PadronIluminado::query();

            if ($nombre) {
                $query->where('nombre', 'LIKE', "%{$nombre}%");
            }

            if ($apellido) {
                $query->where('apellido', 'LIKE', "%{$apellido}%");
            }

            // Limitar resultados para mejor rendimiento
            $personas = $query->limit(50)->get();

            return response()->json([
                'success' => true,
                'data' => $personas,
                'count' => $personas->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error en buscarPersonas: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar personas: ' . $e->getMessage()
            ], 500);
        }
    }
    /**
     * Obtener datos del puntero para edición vía AJAX
     */
    public function editAjax($id)
    {
        try {
            $puntero = Puntero::with(['dirigente', 'equipo'])->find($id);

            if (!$puntero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puntero no encontrado'
                ], 404);
            }

            // Verificar que pertenezca al sistema del usuario
            if ($puntero->equipo->sist != Auth::user()->sistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para editar este puntero'
                ], 403);
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'id' => $puntero->id,
                    'cedula' => $puntero->cedula,
                    'nombre' => $puntero->nombre,
                    'telefono' => $puntero->telefono,
                    'barrio' => $puntero->barrio,
                    'id_dirigente' => $puntero->id_dirigente,
                    'id_equipo' => $puntero->id_equipo
                ]
            ]);
        } catch (\Exception $e) {
            Log::error('Error en editAjax: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos del puntero'
            ], 500);
        }
    }

    /**
     * Actualizar puntero vía AJAX
     */
    public function updateAjax(Request $request, $id)
    {
        DB::beginTransaction();

        try {
            // Validar datos
            $validated = $request->validate([
                'cedula' => 'required|string|max:20',
                'nombre' => 'required|string|max:255',
                'telefono' => 'nullable|string|max:20',
                'barrio' => 'nullable|string|max:255',
                'id_dirigente' => 'required|exists:dirigente,id',
                'id_equipo' => 'required|exists:equipo,id',
            ]);

            // Buscar el puntero
            $puntero = Puntero::find($id);

            if (!$puntero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puntero no encontrado'
                ], 404);
            }

            // Verificar que pertenezca al sistema del usuario
            if ($puntero->equipo->sist != Auth::user()->sistema) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para editar este puntero'
                ], 403);
            }

            // Verificar que la cédula no exista en otro puntero (excepto el actual)
            $cedulaExistente = Puntero::where('cedula', $request->cedula)
                ->where('id', '!=', $id)
                ->whereHas('dirigente.equipo', function ($q) {
                    $q->where('sist', Auth::user()->sistema);
                })->first();

            if ($cedulaExistente) {
                return response()->json([
                    'success' => false,
                    'message' => "Error: la cédula {$request->cedula} ya está registrada en el sistema bajo el puntero '{$cedulaExistente->nombre}'."
                ], 422);
            }

            // Actualizar el puntero
            $puntero->update($validated);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Puntero actualizado correctamente',
                'data' => $puntero->fresh(['dirigente', 'equipo'])
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error en updateAjax: ' . $e->getMessage(), [
                'puntero_id' => $id,
                'data' => $request->all(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el puntero: ' . $e->getMessage()
            ], 500);
        }
    }
}
