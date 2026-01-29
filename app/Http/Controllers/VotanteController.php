<?php

namespace App\Http\Controllers;

use App\Models\PadronIluminado;
use App\Models\PrePadron;
use App\Models\Puntero;
use App\Models\Socio;
use App\Models\Votante;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class VotanteController extends Controller
{
    public function buscador()
    {
        return view('votante.buscador');
    }

    public function datatables(Request $request)
    {
        $columns = [
            0 => 'cedula',
            1 => 'nombre',
            2 => 'apellido',
            3 => 'direccion',
            4 => 'ciudad'
        ];

        $totalData = PrePadron::count();
        $totalFiltered = $totalData;

        $limit  = $request->input('length');
        $start  = $request->input('start');
        $order  = $columns[$request->input('order.0.column')];
        $dir    = $request->input('order.0.dir');

        $query = PrePadron::query();

        if($search = $request->input('search.value')) {
            $query->where(function($q) use ($search){
                $q->where('cedula','like',"%$search%")
                  ->orWhere('nombre','like',"%$search%")
                  ->orWhere('apellido','like',"%$search%");
            });

            $totalFiltered = $query->count();
        }

        $data = $query
            ->offset($start)
            ->limit($limit)
            ->orderBy($order,$dir)
            ->get();

        // agregar botón seleccionar
        $data->transform(function($item){
            $item->action = '<button class="btn btn-success btn-sm select-votante" data-id="'.$item->id.'" data-nombre="'.$item->nombre.' '.$item->apellido.'">
                                <i class="fas fa-check"></i> Seleccionar
                             </button>';
            return $item;
        });

        return response()->json([
            "draw" => intval($request->input('draw')),
            "recordsTotal" => $totalData,
            "recordsFiltered" => $totalFiltered,
            "data" => $data
        ]);
    }

    public function buscarPorCedula($cedula)
    {
        $votante = PrePadron::where('cedula', $cedula)->first();

        if (!$votante) {
            return response()->json([
                'encontrado' => false
            ]);
        }

        // Aliases normalizados para el formulario
        $data = [
            'cedula'       => $votante->cedula,
            'nombre'       => trim($votante->nombre . ' ' . $votante->apellido),
            'direccion'    => $votante->local_interna,
            'mesa'         => '',
            'orden'        => '',
            'partido'      => $votante->afiliaciones,
            'escuela'      => $votante->local_interna,
            'ciudad'       => $votante->distrito_nombre,
            'departamento' => $votante->departamento_nombre,
        ];

        return response()->json([
            'encontrado' => true,
            'data' => $data
        ]);
    }
    public function store(Request $request)
    {
        DB::beginTransaction();

        try {

            /* ===========================
           VALIDACIONES BÁSICAS
        ============================ */
            $request->validate([
                'cedula'        => 'required',
                'nombre'        => 'required',
                'idpuntero'     => 'required|exists:puntero,id',
                'tipo_votante'  => 'required'
            ]);

            $cedula    = $request->cedula;
            $idPuntero = $request->idpuntero;

            /* ===========================
           OBTENER PUNTERO Y SISTEMA
        ============================ */
            $puntero = Puntero::with('dirigente.equipo')->find($idPuntero);

            if (!$puntero || !$puntero->dirigente || !$puntero->dirigente->equipo) {
                throw new \Exception('No se pudo determinar el sistema del puntero.');
            }

            $sistemaActual = $puntero->dirigente->equipo->sist;

            /* ===========================
           BLOQUEAR MISMO SISTEMA
        ============================ */
            $votanteMismoSistema = Votante::with(['puntero.dirigente'])
                ->where('cedula', $cedula)
                ->whereHas('puntero.dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', $sistemaActual);
                })
                ->first();


            if ($votanteMismoSistema) {

                $nombrePuntero = $votanteMismoSistema->puntero->nombre ?? 'No especificado';
                $nombreDirigente = $votanteMismoSistema->puntero->dirigente->nombre ?? 'No especificado';

                return redirect()->back()
                    ->with(
                        'errorAlert',
                        "Error: esta cédula ya está registrada bajo el puntero «{$nombrePuntero}» y el dirigente «{$nombreDirigente}»."
                    )
                    ->with('abrirModalVotante', true)
                    ->with('punteroId', $idPuntero)
                    ->with('punteroNombre', $puntero->nombre);
            }


            /* ===========================
           BUSCAR EN OTRO SISTEMA (AVISO)
        ============================ */
            $votanteOtroSistema = Votante::where('cedula', $cedula)
                ->whereHas('puntero.dirigente.equipo', function ($q) use ($sistemaActual) {
                    $q->where('sist', '!=', $sistemaActual);
                })
                ->with('puntero.dirigente.equipo')
                ->first();

            /* ===========================
           CREAR VOTANTE
        ============================ */
            Votante::create([
                'cedula'        => $cedula,
                'nombre'        => $request->nombre,
                'tipo_votante'  => $request->tipo_votante,
                'voto'          => $request->voto ?? null,
                'idpuntero'     => $idPuntero,
                'idusuario'     => auth()->id(),
                'direccion'     => $request->direccion,
                'mesa'          => $request->mesa,
                'orden'         => $request->orden,
                'partido'       => $request->partido,
                'escuela'       => $request->escuela,
                'ciudad'        => $request->ciudad,
                'departamento'  => $request->departamento,
            ]);

            DB::commit();

            /* ===========================
           MENSAJE FINAL
        ============================ */
            $mensaje = 'Votante agregado correctamente.';

            if ($votanteOtroSistema) {
                $mensaje = "Atención: esta cédula ya existe en otro sistema (" .
                    $votanteOtroSistema->puntero->dirigente->equipo->descripcion .
                    ").";
            }

            return redirect()->back()
                ->with('successAlert', $mensaje)
                ->with('abrirModalVotante', true)
                ->with('punteroId', $idPuntero)
                ->with('punteroNombre', $puntero->nombre);
        } catch (\Exception $e) {

            DB::rollBack();

            Log::error('Error al crear votante', [
                'cedula' => $request->cedula,
                'puntero_id' => $request->idpuntero,
                'error' => $e->getMessage()
            ]);

            return redirect()->back()
                ->with('errorAlert', 'Error: ' . $e->getMessage())
                ->with('abrirModalVotante', true)
                ->with('punteroId', $request->idpuntero ?? null);
        }
    }
    public function votantespuntero($idpuntero)
    {
        $votantes = Votante::porPuntero($idpuntero);

        return response()->json($votantes);
    }
    public function destroy($id)
    {
        try {

            $votante = Votante::with('puntero')->findOrFail($id);

            $idPuntero = $votante->idpuntero;
            $nombrePuntero = $votante->puntero->nombre ?? null;

            $votante->delete();

            return response()->json([
                'success' => true,
                'message' => 'Votante eliminado correctamente',
                'abrirModalVotante' => true,
                'punteroId' => $idPuntero,
                'punteroNombre' => $nombrePuntero
            ]);
        } catch (\Exception $e) {

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el votante'
            ], 500);
        }
    }
}
