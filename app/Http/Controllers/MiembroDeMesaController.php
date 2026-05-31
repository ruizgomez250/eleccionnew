<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\MiembroDeMesa;
use App\Models\Equipo;
use App\Models\LocalInterna;
use App\Models\Sistema;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MiembroDeMesaController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');

        $this->middleware('permission:Miembros de Mesa', [
            'only' => ['index', 'createWithEquipo', 'store', 'destroy']
        ]);
    }

    public function index(Request $request)
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        $equipoId = $request->query('equipo_id');

        $miembros = MiembroDeMesa::with('equipo')
            ->whereHas('equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->when($equipoId, function ($query, $equipoId) {
                $query->where('idequipo', $equipoId);
            })
            ->orderBy('nombre')
            ->get();

        return view('miembros_de_mesa.create', compact('miembros', 'equipos', 'equipoId'));
    }

    public function createWithEquipo($equipoId = null)
    {
        $sistemaUsuario = Auth::user()->sistema;

        $equipos = Equipo::where('sist', $sistemaUsuario)->get();

        $localInterna = null;

        /*
    |--------------------------------------------------------------------------
    | SI EXISTE EQUIPO
    |--------------------------------------------------------------------------
    */
        if ($equipoId) {

            $equipo = Equipo::find($equipoId);

            if ($equipo) {

                $sistema = Sistema::find($equipo->sist);

                if ($sistema) {

                    $ciudadElectoral = CiudadElectoral::find($sistema->id_ciudad_electoral);

                    if ($ciudadElectoral) {

                        $localInterna = LocalInterna::where('distrito_nombre', $ciudadElectoral->descripcion)
                            ->where('departamento_nombre', $ciudadElectoral->departamento)
                            ->where('local_interna', $equipo->descripcion)
                            ->first();
                    }
                }
            }
        }

        $miembros = MiembroDeMesa::with('equipo')
            ->whereHas('equipo', function ($q) use ($sistemaUsuario) {
                $q->where('sist', $sistemaUsuario);
            })
            ->when($equipoId, fn($q) => $q->where('idequipo', $equipoId))
            ->orderBy('nombre')
            ->get();

        return view('miembros_de_mesa.create', compact(
            'equipos',
            'equipoId',
            'miembros',
            'localInterna'
        ));
    }

    public function store(Request $request)
    {
        // Validación incluyendo los campos del proponente
        $request->validate([
            'cedula'   => 'required',
            'nombre'   => 'required',
            'telefono' => 'nullable',
            'funcion'  => 'required|in:Titular,Suplente',
            'idequipo' => 'required',
            // Campos del proponente (opcionales)
            'cedulaproponente'   => 'nullable',
            'nombreproponente'   => 'nullable',
            'telefonoproponente' => 'nullable',
        ]);

        try {
            // Preparar los datos para crear
            $data = $request->all();

            // Si los campos del proponente están vacíos, establecerlos como null
            // Esto evita guardar strings vacíos en la base de datos
            if (empty($data['cedulaproponente'])) {
                $data['cedulaproponente'] = null;
            }
            if (empty($data['nombreproponente'])) {
                $data['nombreproponente'] = null;
            }
            if (empty($data['telefonoproponente'])) {
                $data['telefonoproponente'] = null;
            }

            MiembroDeMesa::create($data);

            return redirect()
                ->back()
                ->with('successAlert', 'Miembro de mesa agregado correctamente')
                ->with('abrirModalMiembro', true)
                ->with('equipoId', $request->idequipo);
        } catch (QueryException $e) {
            // Verificar si es un error de duplicado
            if ($e->errorInfo[1] == 1062) { // Código de error MySQL para duplicado
                return redirect()
                    ->back()
                    ->with('errorAlert', 'La cédula ' . $request->cedula . ' ya está registrada como miembro de mesa.')
                    ->with('abrirModalMiembro', true)
                    ->with('equipoId', $request->idequipo)
                    ->withInput(); // Mantener los datos ingresados
            }

            // Para otros errores de base de datos
            Log::error('Error al agregar miembro de mesa: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('errorAlert', 'Ocurrió un error al intentar agregar el miembro de mesa. Intente nuevamente.')
                ->with('abrirModalMiembro', true)
                ->with('equipoId', $request->idequipo)
                ->withInput();
        } catch (\Exception $e) {
            // Capturar cualquier otro error
            Log::error('Error inesperado: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('errorAlert', 'Ocurrió un error inesperado. Intente nuevamente.')
                ->with('abrirModalMiembro', true)
                ->with('equipoId', $request->idequipo)
                ->withInput();
        }
    }

    /**
     * Método para actualizar un miembro de mesa existente
     * Incluye la posibilidad de actualizar los datos del proponente
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'cedula'   => 'required',
            'nombre'   => 'required',
            'telefono' => 'nullable',
            'funcion'  => 'required|in:Titular,Suplente',
            'idequipo' => 'required',
            // Campos del proponente
            'cedulaproponente'   => 'nullable',
            'nombreproponente'   => 'nullable',
            'telefonoproponente' => 'nullable',
        ]);

        try {
            $miembro = MiembroDeMesa::findOrFail($id);

            // Preparar los datos para actualizar
            $data = $request->all();

            // Si los campos del proponente están vacíos, establecerlos como null
            $data['cedulaproponente'] = empty($data['cedulaproponente']) ? null : $data['cedulaproponente'];
            $data['nombreproponente'] = empty($data['nombreproponente']) ? null : $data['nombreproponente'];
            $data['telefonoproponente'] = empty($data['telefonoproponente']) ? null : $data['telefonoproponente'];

            $miembro->update($data);

            return redirect()
                ->back()
                ->with('successAlert', 'Miembro de mesa actualizado correctamente')
                ->with('equipoId', $request->idequipo);
        } catch (QueryException $e) {
            if ($e->errorInfo[1] == 1062) {
                return redirect()
                    ->back()
                    ->with('errorAlert', 'La cédula ' . $request->cedula . ' ya está registrada como miembro de mesa.')
                    ->with('equipoId', $request->idequipo)
                    ->withInput();
            }

            Log::error('Error al actualizar miembro de mesa: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('errorAlert', 'Ocurrió un error al intentar actualizar el miembro de mesa.')
                ->with('equipoId', $request->idequipo)
                ->withInput();
        } catch (\Exception $e) {
            Log::error('Error inesperado al actualizar: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('errorAlert', 'Ocurrió un error inesperado. Intente nuevamente.')
                ->with('equipoId', $request->idequipo)
                ->withInput();
        }
    }

    /**
     * Método para mostrar un miembro específico (útil para editar)
     */
    public function show($id)
    {
        try {

            $miembro = MiembroDeMesa::with('equipo')->findOrFail($id);

            $localInterna = null;

            /*
        |--------------------------------------------------------------------------
        | OBTENER LOCAL INTERNA SEGÚN EL EQUIPO DEL MIEMBRO
        |--------------------------------------------------------------------------
        */
            if ($miembro->equipo) {

                $equipo = $miembro->equipo;

                $sistema = Sistema::find($equipo->sist);

                if ($sistema) {

                    $ciudadElectoral = CiudadElectoral::find($sistema->id_ciudad_electoral);

                    if ($ciudadElectoral) {

                        $localInterna = LocalInterna::where('distrito_nombre', $ciudadElectoral->descripcion)
                            ->where('departamento_nombre', $ciudadElectoral->departamento)
                            ->where('local_interna', $equipo->descripcion)
                            ->first();
                    }
                }
            }

            /*
        |--------------------------------------------------------------------------
        | AJAX RESPONSE
        |--------------------------------------------------------------------------
        */
            if (request()->ajax()) {

                return response()->json([
                    'miembro' => $miembro,
                    'cantmesa' => $localInterna ? $localInterna->cantmesa : 0
                ]);
            }

            return view('miembros_de_mesa.show', compact('miembro', 'localInterna'));
        } catch (\Exception $e) {

            Log::error('Error al mostrar miembro: ' . $e->getMessage());

            return redirect()
                ->back()
                ->with('errorAlert', 'No se encontró el miembro de mesa.');
        }
    }

    public function destroy($id)
    {
        try {
            $miembro = MiembroDeMesa::findOrFail($id);
            $miembro->delete();

            return redirect()->back()
                ->with('successAlert', 'Miembro eliminado correctamente.');
        } catch (\Exception $e) {
            Log::error('Error al eliminar miembro: ' . $e->getMessage());
            return redirect()->back()
                ->with('errorAlert', 'Error al eliminar: ' . $e->getMessage());
        }
    }

    /**
     * Método para obtener la cantidad de mesas de un equipo (AJAX)
     */
    public function getCantmesaByEquipo($equipoId)
    {
        try {
            $equipo = Equipo::find($equipoId);

            if (!$equipo) {
                return response()->json(['cantmesa' => 0]);
            }

            $sistema = Sistema::find($equipo->sist);

            if (!$sistema) {
                return response()->json(['cantmesa' => 0]);
            }

            $ciudadElectoral = CiudadElectoral::find($sistema->id_ciudad_electoral);

            if (!$ciudadElectoral) {
                return response()->json(['cantmesa' => 0]);
            }

            $localInterna = LocalInterna::where('distrito_nombre', $ciudadElectoral->descripcion)
                ->where('departamento_nombre', $ciudadElectoral->departamento)
                ->where('local_interna', $equipo->descripcion)
                ->first();

            return response()->json([
                'cantmesa' => $localInterna ? (int) $localInterna->cantmesa : 0
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener cantmesa: ' . $e->getMessage());
            return response()->json(['cantmesa' => 0], 500);
        }
    }

    /**
     * Método adicional para filtrar miembros por equipo
     */
    public function getByEquipo($equipoId)
    {
        try {
            $miembros = MiembroDeMesa::where('idequipo', $equipoId)
                ->orderBy('funcion')
                ->orderBy('nombre')
                ->get();

            return response()->json($miembros);
        } catch (\Exception $e) {
            Log::error('Error al obtener miembros por equipo: ' . $e->getMessage());
            return response()->json(['error' => 'Error al cargar los miembros'], 500);
        }
    }
}
