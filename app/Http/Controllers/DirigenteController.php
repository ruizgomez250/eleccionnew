<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\PadronIluminado;
use App\Models\Puntero;
use App\Models\Socio;
use App\Models\Votante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DirigenteController extends Controller
{
    public function index(Request $request)
    {
        $equipos = Equipo::all();
        
        $equipo_id = $request->query('equipo_id');
        $dirigentes = Dirigente::when($equipo_id, function ($query, $equipo_id) {
            $query->where('id_equipo', $equipo_id);
        })->orderBy('nombre')->get();

        return view('dirigente.index', compact('dirigentes', 'equipos', 'equipo_id'));
    }

    public function create()
    {
        $equipos = Equipo::all();
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
        $equipos = Equipo::all(); // Para el select

        // Traer dirigentes filtrando por equipo si se pasa el ID
        $dirigentes = Dirigente::with('punteros.votantes', 'equipo')
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
}
