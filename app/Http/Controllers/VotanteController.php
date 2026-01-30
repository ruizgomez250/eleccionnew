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

    public function __construct()
    {
        $this->middleware('auth')->except(['buscador', 'datatables']);
    }

    public function buscador()
    {
        return view('votante.buscador');
    }

    public function datatables(Request $request)
    {
        $cedula = $request->cedula;
        $nombre = $request->nombre;
        $apellido = $request->apellido;

        // Si no hay filtros, devolvemos vacío
        if (empty($cedula) && empty($nombre) && empty($apellido)) {
            return response()->json([
                "draw" => intval($request->draw),
                "recordsTotal" => 0,
                "recordsFiltered" => 0,
                "data" => []
            ]);
        }

        // Seleccionamos solo columnas necesarias
        $query = DB::table('prepadron')->select('cedula', 'nombre', 'apellido', 'direccion', 'afiliaciones');

        if (!empty($cedula)) {
            $query->where('cedula', 'like', "{$cedula}"); // más rápido que '%...%'
        }

        if (!empty($nombre)) {
            $query->where('nombre', 'like', "%{$nombre}%");
        }

        if (!empty($apellido)) {
            $query->where('apellido', 'like', "%{$apellido}%");
        }

        // DataTables con query builder
        return datatables($query)
            ->addColumn('nombre_completo', function ($row) {
                return trim($row->nombre . ' ' . $row->apellido);
            })
            ->rawColumns(['nombre_completo'])
            ->make(true);
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
