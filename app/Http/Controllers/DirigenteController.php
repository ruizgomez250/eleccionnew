<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\PadronIluminado;
use App\Models\Socio;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;

class DirigenteController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:Dirigente', [
            'only' => [
                'index',
                'create',
                'store',
                'destroy',
                'createWithEquipo',
                'punteros',
                'buscarPorCedula'
            ]
        ]);
    }
    public function index(Request $request)
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        $equipo_id = $request->query('equipo_id');
        $dirigentes = Dirigente::whereHas('equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })
            ->when($equipo_id, function ($query, $equipo_id) {
                $query->where('id_equipo', $equipo_id);
            })
            ->orderBy('nombre')
            ->get();


        return view('dirigente.index', compact('dirigentes', 'equipos', 'equipo_id'));
    }

    public function create()
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();
        return view('dirigente.index', compact('equipos'));
    }

    public function store(Request $request)
    {

        $cedula = $request->cedula;
        $idEquipo = $request->id_equipo;
        $equipoActual = Equipo::find($idEquipo);

        if (!$equipoActual) {
            return redirect()->back()
                ->with('errorAlert', 'El equipo seleccionado no existe.');
        }

        // Obtener el equipo actual
        $equipoActual = Equipo::find($idEquipo);
        $sistemaActual = $equipoActual->sist ?? 'default';

        // Verificar si ya existe en el mismo sistema
        $dirigenteMismoSistema = Dirigente::where('cedula', $cedula)
            ->whereHas('equipo', function ($q) use ($sistemaActual) {
                $q->where('sist', $sistemaActual);
            })
            ->first();

        if ($dirigenteMismoSistema) {
            // No guardar y mostrar mensaje de error indicando en qué equipo está
            $mensaje = "Error: esta cédula ya está registrada en el mismo sistema en el equipo '{$dirigenteMismoSistema->equipo->descripcion}'.";
            return redirect()->back()
                ->with('errorAlert', $mensaje)
                ->with('abrirModalDirigente', true)
                ->with('equipoId', $idEquipo);
        }

        // Verificar si existe en otro sistema
        $dirigenteOtroSistema = Dirigente::where('cedula', $cedula)
            ->whereHas('equipo', function ($q) use ($sistemaActual) {
                $q->where('sist', '!=', $sistemaActual);
            })
            ->first();

        // Guardar siempre si no está en el mismo sistema
        $nuevoDirigente = Dirigente::create($request->all());

        // Mensaje informativo si ya existe en otro sistema, indicando equipo
        $mensaje = 'Dirigente agregado correctamente.';
        if ($dirigenteOtroSistema) {
            $mensaje = "Atención: esta cédula ya existe en otro sistema en el equipo '{$dirigenteOtroSistema->equipo->descripcion}'.";
        }

        return redirect()->back()
            ->with('successAlert', $mensaje)
            ->with('abrirModalDirigente', true)
            ->with('equipoId', $idEquipo);
    }





    // Devuelve los punteros de un dirigente para el modal
    public function punteros(Dirigente $dirigente)
    {
        $punteros = $dirigente->punteros()->get(); // relación en el modelo
        return response()->json($punteros);
    }
    public function createWithEquipo($equipoId = null)
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get(); // Para el select

        $sistemaUsuario = Auth::user()->sistema;

        $dirigentes = Dirigente::with('punteros.votantes', 'equipo')
            ->whereHas('equipo', function ($q) use ($sistemaUsuario) {
                $q->where('sist', $sistemaUsuario);
            })
            ->when($equipoId, fn($q) => $q->where('id_equipo', $equipoId))
            ->get();


        // Calcular punteros_count y votantes_count por dirigente
        foreach ($dirigentes as $dir) {
            $dir->punteros_count = $dir->punteros->count();
            $dir->votantes_count = $dir->punteros->sum(fn($p) => $p->votantes->count());
        }

        // Total general de votantes
        $totalVotantesGeneral = $dirigentes->sum(fn($d) => $d->votantes_count);

        return view('dirigente.index', compact('equipos', 'equipoId', 'dirigentes', 'totalVotantesGeneral'));
    }


    public function buscarPorCedula($cedula)
    {
        $dirigente = Socio::where('cedula', $cedula)->first();
        if ($dirigente) {
            $dirigente->telefono = $dirigente->telefono . ' ' . $dirigente->telefono1 . ' ' . $dirigente->telefono2;
            return response()->json([
                'encontrado' => true,
                'data' => $dirigente
            ]);
        } else {
            $dirigente = PadronIluminado::where('cedula', $cedula)->first();
            if ($dirigente) {
                // Crear alias
                $dirigente->direccion = $dirigente->localdesc;
                $dirigente->nombre = $dirigente->nombre . ' ' . $dirigente->apellido;
                return response()->json([
                    'encontrado' => true,
                    'data' => $dirigente
                ]);
            }
        }




        return response()->json([
            'encontrado' => false
        ]);
    }
    public function destroy($id)
    {
        try {
            DB::transaction(function () use ($id) {

                // Traer dirigente con punteros y votantes de punteros
                $dirigente = Dirigente::with('punteros.votantes')
                    ->findOrFail($id);

                // 1️⃣ Borrar votantes de cada puntero
                foreach ($dirigente->punteros as $puntero) {
                    $puntero->votantes()->delete();
                }

                // 2️⃣ Borrar punteros
                $dirigente->punteros()->delete();

                // 3️⃣ Borrar dirigente
                $dirigente->delete();
            });

            return redirect()->back()
                ->with('successAlert', 'Dirigente, punteros y votantes eliminados correctamente.');
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('errorAlert', 'Error al eliminar: ' . $e->getMessage());
        } //134
    }
    public function dirigentesPorSistema($sistemaId, Request $request)
    {
        $equipoId = $request->equipo_id;
        $equipoSeleccionado = request('equipo_id');
        // Equipos del sistema
        $equipos = Equipo::where('sist', $sistemaId)->get();

        // Dirigentes
        $dirigentes = Dirigente::with(['punteros.votantes', 'equipo'])
            ->whereHas('equipo', function ($q) use ($sistemaId) {
                $q->where('sist', $sistemaId);
            })
            ->when($equipoId, function ($q) use ($equipoId) {
                $q->where('id_equipo', $equipoId);
            })
            ->get();

        // Calcular punteros y votantes
        foreach ($dirigentes as $dir) {

            $dir->punteros_count = $dir->punteros->count();

            $dir->votantes_count = $dir->punteros->sum(function ($p) {
                return $p->votantes->count();
            });
        }

        // Total general
        $totalVotantesGeneral = $dirigentes->sum('votantes_count');

        return view(
            'ciudades.partials.lista_dirigentes',
            compact(
                'dirigentes',
                'equipos',
                'equipoId',
                'totalVotantesGeneral',
                'equipoSeleccionado',
                'sistemaId'
            )
        );
    }
    // Agrega esta función al final del DirigenteController
    public function storeAjax(Request $request)
    {
        try {
            $cedula = $request->cedula;
            $idEquipo = $request->id_equipo;
            $idSistema = $request->sistema_id;

            $equipoActual = Equipo::find($idEquipo);
            if (!$equipoActual) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo seleccionado no existe.'
                ], 404);
            }

            $sistemaActual = $equipoActual->sist ?? 'default';

            // Verificar si ya existe en el mismo sistema
            $dirigenteMismoSistema = Dirigente::where('cedula', $cedula)
                ->whereHas('equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', $sistemaActual);
                })
                ->first();

            if ($dirigenteMismoSistema) {
                return response()->json([
                    'success' => false,
                    'message' => "Esta cédula ya está registrada en el equipo '{$dirigenteMismoSistema->equipo->descripcion}'."
                ], 422);
            }

            // Verificar si existe en otro sistema
            $dirigenteOtroSistema = Dirigente::where('cedula', $cedula)
                ->whereHas('equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', '!=', $sistemaActual);
                })
                ->first();

            // Crear el dirigente
            $nuevoDirigente = Dirigente::create([
                'cedula' => $request->cedula,
                'nombre' => $request->nombre,
                'telefono' => $request->telefono,
                'barrio' => $request->barrio,
                'id_equipo' => $idEquipo
            ]);

            $mensaje = 'Dirigente agregado correctamente.';
            if ($dirigenteOtroSistema) {
                $mensaje = "Dirigente agregado. Nota: esta cédula ya existe en otro sistema en el equipo '{$dirigenteOtroSistema->equipo->descripcion}'.";
            }

            return response()->json([
                'success' => true,
                'message' => $mensaje,
                'data' => $nuevoDirigente
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar: ' . $e->getMessage()
            ], 500);
        }
    }
    // Agrega este método al final del controlador
    public function destroyAjax($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $dirigente = Dirigente::with('punteros.votantes')
                    ->findOrFail($id);

                foreach ($dirigente->punteros as $puntero) {
                    $puntero->votantes()->delete();
                }

                $dirigente->punteros()->delete();
                $dirigente->delete();
            });

            return response()->json([
                'success' => true,
                'message' => 'Dirigente, punteros y votantes eliminados correctamente.'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
    public function getPunterosCount($id)
    {
        $dirigente = Dirigente::findOrFail($id);
        $count = $dirigente->punteros()->count();

        return response()->json(['count' => $count]);
    }
}
