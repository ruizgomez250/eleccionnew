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
     * Muestra la lista de equipos (optimizado con paginación)
     */
    public function index(Request $request)
    {
        $query = $request->input('search');

        $equipos = Equipo::where('sist', Auth::user()->sistema)
            ->when($query, function ($q) use ($query) {
                $q->where('descripcion', 'like', "%{$query}%")
                    ->orWhere('ciudad', 'like', "%{$query}%");
            })
            ->select('id', 'descripcion', 'colegio', 'ciudad')
            ->orderBy('id', 'desc')
            ->paginate(24)
            ->withQueryString(); // mantiene el parámetro de búsqueda en la paginación

        // Si es AJAX, devolvemos solo el partial
        if ($request->ajax()) {
            return view('equipo.partials.lista_equipos', compact('equipos'))->render();
        }

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
            'sist'        => Auth::user()->sistema,
            'colegio'     => $request->colegio,
            'ciudad'      => $request->ciudad,
        ]);

        return redirect()
            ->route('equipo.index')
            ->with('success', 'Equipo creado correctamente');
    }
}
