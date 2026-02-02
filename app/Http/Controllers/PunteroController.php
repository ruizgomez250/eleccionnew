<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Puntero;
use App\Models\Equipo;
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
        DB::beginTransaction();

        try {
            $idEquipo = $request->id_equipo;
            $cedula = $request->cedula;
            $idDirigente = $request->id_dirigente;

            $equipoActual = Equipo::where('sist', Auth::user()->sistema)
                ->find($idEquipo);
            if (!$equipoActual) throw new Exception('El equipo seleccionado no existe.');

            $dirigente = Dirigente::find($idDirigente);
            if (!$dirigente) {
                return redirect()->back()
                    ->with('errorAlert', 'Error: no se encontró el dirigente.')
                    ->with('abrirModalPuntero', true);
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
            ->when($id_equipo, fn($q) => $q->where('id_equipo', $id_equipo))
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

    public function show(Puntero $puntero)
    {
        return view('puntero.show', compact('puntero'));
    }
}
