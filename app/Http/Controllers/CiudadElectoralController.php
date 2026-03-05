<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CiudadElectoral;

class CiudadElectoralController extends Controller
{

    public function index()
    {
        $ciudades = CiudadElectoral::orderBy('descripcion')->get();

        return view('ciudades_electorales.index', compact('ciudades'));
    }


    public function create()
    {
        return view('ciudades_electorales.create');
    }


    public function store(Request $request)
    {
        $request->validate([
            'descripcion' => 'required',
            'departamento' => 'required'
        ]);

        CiudadElectoral::create($request->all());

        return redirect()->route('ciudades_electorales.index')
            ->with('success', 'Ciudad electoral creada correctamente');
    }


    public function edit($id)
    {
        $ciudad = CiudadElectoral::findOrFail($id);

        return view('ciudades_electorales.edit', compact('ciudad'));
    }


    public function update(Request $request, $id)
    {
        $ciudad = CiudadElectoral::findOrFail($id);

        $request->validate([
            'descripcion' => 'required',
            'departamento' => 'required'
        ]);

        $ciudad->update($request->all());

        return redirect()->route('ciudades_electorales.index')
            ->with('success', 'Ciudad electoral actualizada');
    }


    public function destroy($id)
    {
        CiudadElectoral::destroy($id);

        return redirect()->route('ciudades_electorales.index')
            ->with('success', 'Ciudad eliminada');
    }

}