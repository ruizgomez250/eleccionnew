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
}
