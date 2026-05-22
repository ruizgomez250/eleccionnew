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
        $sistemaUsuario = Auth::user()->sistema;
        
        // Obtener TODOS los votantes de TODOS los sistemas
        $todosLosPunteros = Puntero::with(['dirigente', 'dirigente.equipo'])->get();
        $todosLosPunterosIds = $todosLosPunteros->pluck('id')->toArray();
        
        if (empty($todosLosPunterosIds)) {
            return $this->vistaVacia();
        }
        
        // Obtener TODOS los votantes con sus relaciones
        $todosLosVotantes = Votante::whereIn('idpuntero', $todosLosPunterosIds)
            ->with(['puntero', 'puntero.dirigente', 'puntero.dirigente.equipo'])
            ->get();
        
        if ($todosLosVotantes->isEmpty()) {
            return $this->vistaVacia();
        }
        
        // Agrupar por cédula
        $agrupadosPorCedula = $todosLosVotantes->groupBy('cedula');
        
        // Filtrar SOLO las cédulas que tienen duplicados que involucran al sistema del usuario
        $cedulasConDuplicadosRelevantes = [];
        
        foreach ($agrupadosPorCedula as $cedula => $registros) {
            // Verificar si esta cédula tiene registros en el sistema del usuario
            $tieneRegistroEnSistemaUsuario = false;
            $sistemasInvolucrados = [];
            
            foreach ($registros as $votante) {
                $sistemaId = $this->getSistemaIdFromVotante($votante);
                $sistemasInvolucrados[] = $sistemaId;
                
                if ($sistemaId == $sistemaUsuario) {
                    $tieneRegistroEnSistemaUsuario = true;
                }
            }
            
            // Solo incluir si:
            // 1. Tiene al menos un registro en el sistema del usuario
            // 2. Y tiene más de 1 registro en TOTAL (está duplicado en algún lado)
            if ($tieneRegistroEnSistemaUsuario && $registros->count() > 1) {
                $cedulasConDuplicadosRelevantes[$cedula] = $registros;
            }
        }
        
        // Construir el array de resultados (una sola fila por cédula)
        $resultados = [];
        
        foreach ($cedulasConDuplicadosRelevantes as $cedula => $registros) {
            $primerRegistro = $registros->first();
            
            // Contar duplicados por tipo
            $totalRegistros = $registros->count();
            
            // Duplicados por PUNTERO (contar punteros únicos diferentes)
            $punterosUnicos = $registros->pluck('idpuntero')->unique();
            $totalPunteros = $punterosUnicos->count();
            $duplicadoPorPuntero = $totalPunteros > 1;
            
            // Duplicados por DIRIGENTE (contar dirigentes únicos diferentes)
            $dirigentesUnicos = collect();
            foreach ($registros as $votante) {
                if ($votante->puntero && $votante->puntero->dirigente) {
                    $dirigentesUnicos->push($votante->puntero->dirigente->id);
                }
            }
            $dirigentesUnicos = $dirigentesUnicos->unique();
            $totalDirigentes = $dirigentesUnicos->count();
            $duplicadoPorDirigente = $totalDirigentes > 1;
            
            // Obtener información del sistema del usuario (para mostrar)
            $registroEnSistemaUsuario = $registros->first(function($v) use ($sistemaUsuario) {
                return $this->getSistemaIdFromVotante($v) == $sistemaUsuario;
            });
            
            $resultados[] = (object)[
                'cedula' => $cedula,
                'nombre' => $primerRegistro->nombre,
                'direccion' => $primerRegistro->direccion,
                'mesa' => $primerRegistro->mesa,
                'orden' => $primerRegistro->orden,
                'partido' => $primerRegistro->partido,
                'puntero' => $registroEnSistemaUsuario->puntero->nombre ?? 'N/A',
                'dirigente' => $registroEnSistemaUsuario->puntero->dirigente->nombre ?? 'N/A',
                'equipo' => $registroEnSistemaUsuario->puntero->equipo->descripcion ?? 'N/A',
                'total_registros' => $totalRegistros,
                'duplicado_por_puntero' => $duplicadoPorPuntero,
                'duplicado_por_dirigente' => $duplicadoPorDirigente,
                'total_punteros' => $totalPunteros,
                'total_dirigentes' => $totalDirigentes,
                'sistemas_involucrados' => collect($sistemasInvolucrados)->unique()->values()->toArray()
            ];
        }
        
        // Estadísticas
        $totalCedulasDuplicadas = count($resultados);
        $totalConDuplicadoPuntero = collect($resultados)->where('duplicado_por_puntero', true)->count();
        $totalConDuplicadoDirigente = collect($resultados)->where('duplicado_por_dirigente', true)->count();
        
        return view('reportes.votantes_duplicados', compact(
            'resultados',
            'totalCedulasDuplicadas',
            'totalConDuplicadoPuntero',
            'totalConDuplicadoDirigente',
            'sistemaUsuario'
        ));
    }
    
    /**
     * Obtener el ID del sistema a partir de un votante
     * Votante -> Puntero -> Dirigente -> Equipo -> sist
     */
    private function getSistemaIdFromVotante($votante)
    {
        try {
            if ($votante && $votante->puntero && $votante->puntero->dirigente && $votante->puntero->dirigente->equipo) {
                return $votante->puntero->dirigente->equipo->sist;
            }
        } catch (\Exception $e) {
            return null;
        }
        return null;
    }
    
    private function vistaVacia()
    {
        return view('reportes.votantes_duplicados', [
            'resultados' => collect(),
            'totalCedulasDuplicadas' => 0,
            'totalConDuplicadoPuntero' => 0,
            'totalConDuplicadoDirigente' => 0,
            'sistemaUsuario' => Auth::user()->sistema
        ]);
    }
}