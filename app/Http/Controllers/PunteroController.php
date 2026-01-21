<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Puntero;
use App\Models\Equipo;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PunteroController extends Controller
{
    // Mostrar todos los punteros
    public function index(Request $request)
    {
        $id_equipo = $request->id_equipo; // o request('id_equipo')
        return redirect()->route('puntero.createWithEquipo', ['id_equipo' => $id_equipo]);
    }
    public function createWithDirigente($dirigenteId = null)
    {
        $dirigente = Dirigente::all();
        $equipos = Equipo::all(); // todos los equipos para el select
        $punteros = Puntero::all();
        $equipoId = $dirigente->id_equipo;

        // Si se selecciona un equipo, traer sus dirigentes; si no, traer todos
        if ($dirigenteId) {
            $punteros = Puntero::where('id_dirigente', $dirigenteId)->get();
        }

        return view('puntero.index', compact('equipos', 'equipoId', 'dirigentes', 'dirigenteId'));
    }

    // Mostrar punteros de un equipo específico
    public function indexByEquipo($equipoId)
    {
        $equipo = Equipo::findOrFail($equipoId);
        $punteros = $equipo->punteros; // relación en el modelo
        return view('puntero.index', compact('punteros', 'equipo'));
    }

    // Crear puntero con equipo preseleccionado
    public function createWithEquipo($equipoId)
    {
        $equipo = Equipo::findOrFail($equipoId);
        return view('puntero.create', compact('equipo'));
    }

    // Guardar puntero
    public function store(Request $request)
    {
        //dd($request->input());

        DB::beginTransaction();

        try {

            $idEquipo = $request->id_equipo;
            $cedula = $request->cedula;
            $idDirigente = $request->id_dirigente;

            /* ===========================
           VALIDAR EQUIPO
        ============================ */
            $equipoActual = Equipo::find($idEquipo);
            if (!$equipoActual) {
                throw new Exception('El equipo seleccionado no existe.');
            }

            /* ===========================
           VALIDAR DIRIGENTE
        ============================ */
            $dirigente = Dirigente::find($idDirigente);
            if (!$dirigente) {
                return redirect()->back()
                    ->with('errorAlert', 'Error: no se encontró el dirigente.')
                    ->with('abrirModalPuntero', true);
            }

            $equipoActual = $dirigente->equipo;
            $sistemaActual = $equipoActual->sist ?? 'default';

            /* ===========================
           VERIFICAR MISMO SISTEMA
        ============================ */
            $punteroMismoSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', $sistemaActual);
                })
                ->first();

            if ($punteroMismoSistema) {
                $mensaje = "Error: esta cédula ya está registrada en el mismo sistema bajo el dirigente '{$punteroMismoSistema->dirigente->nombre}'.";
                return redirect()->back()
                    ->with('errorAlert', $mensaje)
                    ->with('abrirModalPuntero', true)
                    ->with('punteroIdDirigente', $idDirigente);
            }

            /* ===========================
           VERIFICAR OTRO SISTEMA
        ============================ */
            $punteroOtroSistema = Puntero::where('cedula', $cedula)
                ->whereHas('dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', '!=', $sistemaActual);
                })
                ->first();

            /* ===========================
           CREAR PUNTERO
        ============================ */
            $nuevoPuntero = Puntero::create($request->all());

            DB::commit();

            /* ===========================
           MENSAJE FINAL
        ============================ */
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
        // Si vino en query string
        $id_dirigente = $request->query('dirigente_id');

        // Todos los equipos
        $equipos = Equipo::all();

        // Filtramos dirigentes por equipo si se pasó un ID
        $dirigentes = Dirigente::when($id_equipo, function ($q) use ($id_equipo) {
            $q->where('id_equipo', $id_equipo);
        })->get();

        // Filtramos punteros por equipo si se pasó un ID
        $punteros = Puntero::with(['dirigente', 'equipo', 'votantes'])
            ->when($id_equipo, fn($q) => $q->where('id_equipo', $id_equipo))
            ->get();

        // Calculamos total de votantes por puntero
        foreach ($punteros as $p) {
            $p->votantes_count = $p->votantes->count();
        }

        // Total general de votantes
        $totalVotantesGeneral = $punteros->sum(fn($p) => $p->votantes_count);

        return view(
            'puntero.index',
            compact('equipos', 'dirigentes', 'punteros', 'id_equipo', 'id_dirigente', 'totalVotantesGeneral')
        );
    }




    // Editar puntero
    public function edit(Puntero $puntero)
    {
        return view('puntero.edit', compact('puntero'));
    }

    // Actualizar puntero
    public function update(Request $request, Puntero $puntero)
    {
        $puntero->update($request->all());
        return redirect()->route('puntero.index')
            ->with('success', 'Puntero actualizado correctamente');
    }

    // Eliminar puntero
    public function destroy(String $id)
    {
        try {
            $puntero = Puntero::findOrFail($id);
            $dirigente = Dirigente::find($puntero->id_dirigente);

            // 1️⃣ Borrar votantes asociados
            $puntero->votantes()->delete();

            // 2️⃣ Borrar puntero
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

    // Opcional: mostrar puntero
    public function show(Puntero $puntero)
    {
        return view('puntero.show', compact('puntero'));
    }
}
