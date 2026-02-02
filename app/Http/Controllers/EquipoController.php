<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;


class EquipoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Equipo', ['only' => ['index', 'show']]);
        $this->middleware('permission:Guardar Equipos', [
            'only' => ['store', 'update', 'destroy', 'edit']
        ]);
    }

    /**
     * Muestra la lista de equipos
     */
    public function index()
    {
        $equipos = Equipo::where('sist', Auth::user()->sistema)
            ->orderBy('id', 'desc')
            ->get();

        return view('equipo.index', compact('equipos'));
    }
    public function edit($id)
    {
        $equipo = Equipo::where('id', $id)
            ->where('sist', Auth::user()->sistema)
            ->firstOrFail();

        return view('equipo.edit', compact('equipo'));
    }

    public function update(Request $request, $id)
    {
        $equipo = Equipo::where('id', $id)
            ->where('sist', Auth::user()->sistema)
            ->firstOrFail();

        $request->validate([
            'descripcion' => 'required|string|max:255',
            'colegio'     => 'nullable|string|max:255',
            'ciudad'      => 'nullable|string|max:255',
        ]);

        $equipo->update([
            'descripcion' => $request->descripcion,
            'colegio'     => $request->colegio,
            'ciudad'      => $request->ciudad,
            // 🚫 nunca permitir cambiar sist
        ]);

        return redirect()
            ->route('equipo.index')
            ->with('success', 'Equipo actualizado correctamente');
    }

    public function destroy($id)
    {
        $equipo = Equipo::where('id', $id)
            ->where('sist', Auth::user()->sistema)
            ->firstOrFail();

        $equipo->delete();

        return back()->with('success', 'Equipo eliminado correctamente');
    }

    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'colegio'     => 'nullable|string|max:255',
            'ciudad'      => 'nullable|string|max:255',
        ]);

        Equipo::create([
            'descripcion' => $request->descripcion,
            'sist'        => Auth::user()->sistema, // 🔒 seguro
            'colegio'     => $request->colegio,
            'ciudad'      => $request->ciudad,
        ]);

        return redirect()
            ->route('equipo.index')
            ->with('success', 'Equipo creado correctamente');
    }
}
