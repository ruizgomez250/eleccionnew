<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Votante;
use App\Models\Puntero;
use App\Models\Dirigente;
use App\Models\Equipo;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class VotantesDuplicadosController extends Controller
{
    public function index(Request $request)
    {
        // Obtener el sistema del usuario logueado
        $sistemaId = Auth::user()->sistema;
        
        // ============================================================
        // CORRECCIÓN: Obtener equipos que pertenecen al sistema
        // El campo 'sist' está en la tabla 'equipo', no en 'dirigente'
        // ============================================================
        $equiposDelSistema = Equipo::where('sist', $sistemaId)->pluck('id')->toArray();
        
        // Si no hay equipos en este sistema, retornar vacío
        if (empty($equiposDelSistema)) {
            return $this->vistaVacia();
        }
        
        // Obtener los dirigentes que pertenecen a esos equipos
        $dirigentesDelSistema = Dirigente::whereIn('id_equipo', $equiposDelSistema)->pluck('id')->toArray();
        
        if (empty($dirigentesDelSistema)) {
            return $this->vistaVacia();
        }
        
        // Obtener los punteros que pertenecen a esos dirigentes
        $punterosDelSistema = Puntero::whereIn('id_dirigente', $dirigentesDelSistema)->pluck('id')->toArray();
        
        if (empty($punterosDelSistema)) {
            return $this->vistaVacia();
        }
        
        // Obtener todos los votantes cuyos punteros están en el sistema
        $votantesDelSistema = Votante::whereIn('idpuntero', $punterosDelSistema)
            ->with(['puntero', 'puntero.dirigente', 'puntero.equipo'])
            ->get();
        
        if ($votantesDelSistema->isEmpty()) {
            return $this->vistaVacia();
        }
        
        // 1. VOTANTES DUPLICADOS POR CÉDULA
        $votantesDuplicados = $votantesDelSistema->groupBy('cedula')
            ->filter(function($grupo) {
                return $grupo->count() > 1;
            })
            ->flatten();
        
        // 2. VOTANTES DUPLICADOS POR PUNTERO
        $votantesConMultiplesPunteros = $votantesDelSistema->groupBy('cedula')
            ->filter(function($grupo) {
                $punterosUnicos = $grupo->pluck('idpuntero')->unique()->count();
                return $punterosUnicos > 1;
            })
            ->flatten();
        
        // 3. VOTANTES DUPLICADOS POR DIRIGENTE
        $votantesDuplicadosPorDirigente = $votantesDelSistema->groupBy('cedula')
            ->filter(function($grupo) {
                $dirigentesUnicos = collect();
                foreach ($grupo as $votante) {
                    if ($votante->puntero && $votante->puntero->dirigente) {
                        $dirigentesUnicos->push($votante->puntero->dirigente->id);
                    }
                }
                $dirigentesUnicos = $dirigentesUnicos->unique();
                return $dirigentesUnicos->count() > 1;
            })
            ->flatten();
        
        // ESTADÍSTICAS GENERALES
        $totalVotantesSistema = $votantesDelSistema->count();
        $totalCedulasUnicas = $votantesDelSistema->unique('cedula')->count();
        $totalDuplicadosCedula = $votantesDuplicados->unique('cedula')->count();
        $totalDuplicadosPuntero = $votantesConMultiplesPunteros->unique('cedula')->count();
        $totalDuplicadosDirigente = $votantesDuplicadosPorDirigente->unique('cedula')->count();
        $totalVotantesEnDuplicados = $votantesDuplicados->unique('cedula')->count();
        
        return view('reportes.votantes_duplicados', compact(
            'votantesDuplicados',
            'votantesConMultiplesPunteros',
            'votantesDuplicadosPorDirigente',
            'totalVotantesSistema',
            'totalCedulasUnicas',
            'totalDuplicadosCedula',
            'totalDuplicadosPuntero',
            'totalDuplicadosDirigente',
            'totalVotantesEnDuplicados'
        ));
    }
    
    /**
     * Método para retornar vista vacía cuando no hay datos
     */
    private function vistaVacia()
    {
        return view('reportes.votantes_duplicados', [
            'votantesDuplicados' => collect(),
            'votantesConMultiplesPunteros' => collect(),
            'votantesDuplicadosPorDirigente' => collect(),
            'totalVotantesSistema' => 0,
            'totalCedulasUnicas' => 0,
            'totalDuplicadosCedula' => 0,
            'totalDuplicadosPuntero' => 0,
            'totalDuplicadosDirigente' => 0,
            'totalVotantesEnDuplicados' => 0
        ]);
    }
    
    /**
     * Método para depuración - ver la jerarquía de datos
     */
    public function debug(Request $request)
    {
        $sistemaId = Auth::user()->sistema;
        
        $debug = [];
        
        // Equipos del sistema
        $equipos = Equipo::where('sist', $sistemaId)->get();
        $debug['equipos'] = $equipos;
        $debug['total_equipos'] = $equipos->count();
        
        // Dirigentes de esos equipos
        $equiposIds = $equipos->pluck('id')->toArray();
        $dirigentes = Dirigente::whereIn('id_equipo', $equiposIds)->get();
        $debug['dirigentes'] = $dirigentes;
        $debug['total_dirigentes'] = $dirigentes->count();
        
        // Punteros de esos dirigentes
        $dirigentesIds = $dirigentes->pluck('id')->toArray();
        $punteros = Puntero::whereIn('id_dirigente', $dirigentesIds)->get();
        $debug['punteros'] = $punteros;
        $debug['total_punteros'] = $punteros->count();
        
        // Votantes de esos punteros
        $punterosIds = $punteros->pluck('id')->toArray();
        $votantes = Votante::whereIn('idpuntero', $punterosIds)->get();
        $debug['votantes'] = $votantes;
        $debug['total_votantes'] = $votantes->count();
        
        // Duplicados
        $debug['cedulas_duplicadas'] = $votantes->groupBy('cedula')
            ->filter(fn($g) => $g->count() > 1)
            ->map(fn($g) => $g->count())
            ->toArray();
        
        return response()->json($debug);
    }
}