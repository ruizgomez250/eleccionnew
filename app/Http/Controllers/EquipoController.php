<?php

namespace App\Http\Controllers;

use App\Models\Equipo;
use Illuminate\Http\Request;

class EquipoController extends Controller
{

    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('permission:Equipo', ['only' => ['index', 'show']]);
    }

    /**
     * Muestra la lista de equipos
     */
    public function index()
    {
        $equipos = Equipo::orderBy('id', 'desc')->get();

        return view('equipo.index', compact('equipos'));
    }
    public function edit($id)
    {
        $equipo = Equipo::findOrFail($id);
        return view('equipo.edit', compact('equipo'));
    }
    public function update(Request $request, $id)
    {
        $equipo = Equipo::findOrFail($id);

        $equipo->update([
            'descripcion' => $request->descripcion,
            'sist'        => $request->sist,
            'colegio'     => $request->colegio,
            'ciudad'      => $request->ciudad,
        ]);

        return redirect()
            ->route('equipo.index')
            ->with('success', 'Equipo actualizado correctamente');
    }
    public function destroy($id)
    {
        $equipo = Equipo::findOrFail($id);
        $equipo->delete();

        return back()->with('success', 'Equipo eliminado correctamente');
    }
    public function store(Request $request)
    {
        // ✅ Validación
        $request->validate([
            'descripcion' => 'required|string|max:255',
            'sist'        => 'nullable|string|max:255',
            'colegio'     => 'nullable|string|max:255',
            'ciudad'      => 'nullable|string|max:255',
        ]);

        // ✅ Crear registro
        Equipo::create([
            'descripcion' => $request->descripcion,
            'sist'        => $request->sist,
            'colegio'     => $request->colegio,
            'ciudad'      => $request->ciudad,
        ]);

        // ✅ Redirigir con mensaje
        return redirect()
            ->route('equipo.index')
            ->with('success', 'Equipo creado correctamente');
    }
}
