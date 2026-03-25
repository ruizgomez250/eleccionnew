<?php

namespace App\Http\Controllers;

use App\Models\MiembroDeMesa;
use App\Models\Equipo;
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
            'miembros'
        ));
    }

    public function store(Request $request)
    {

        $request->validate([
            'cedula'   => 'required',
            'nombre'   => 'required',
            'telefono' => 'nullable',
            'funcion'  => 'required|in:Titular,Suplente',
            'idequipo' => 'required'
        ]);

        try {
            MiembroDeMesa::create($request->all());

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
                ->with('equipoId', $request->idequipo);
        } catch (\Exception $e) {
            // Capturar cualquier otro error
            Log::error('Error inesperado: ' . $e->getMessage());
            return redirect()
                ->back()
                ->with('errorAlert', 'Ocurrió un error inesperado. Intente nuevamente.')
                ->with('abrirModalMiembro', true)
                ->with('equipoId', $request->idequipo);
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

            return redirect()->back()
                ->with('errorAlert', 'Error al eliminar: ' . $e->getMessage());
        }
    }
}
