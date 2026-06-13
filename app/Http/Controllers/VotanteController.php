<?php

namespace App\Http\Controllers;

use App\Models\PadronIluminado;
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
        $this->middleware('auth')->except(['buscador', 'datatables', 'buscarSimplePorCedula']);
    }
    public function buscarSimplePorCedula(Request $request)
    {
        $request->validate([
            'cedula' => 'required'
        ]);

        $votante = DB::table('padroncoopluque9062026')
            ->select('NRO', 'SOCIO NRO', 'CI NRO', 'NOMBRE Y APELLIDO', 'SITUACION')
            ->where('CI NRO', $request->cedula)
            ->first();

        if (!$votante) {
            return response()->json([
                'encontrado' => false
            ]);
        }

        return response()->json([
            'encontrado' => true,
            'data' => [
                'cedula'             => $votante->{'CI NRO'} ?? '',
                'nombre'             => $votante->{'NOMBRE Y APELLIDO'} ?? '',
                'apellido'           => '',
                'local_interna'      => '',
                'local_generales'    => '',
                'direccion'          => '',
                'mesa'               => '0',
                'orden'              => $votante->NRO ?? '0',
                'afiliaciones'       => $votante->SITUACION ?? '',
            ]
        ]);
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
        $query = DB::table('prepadron')->select('cedula', 'local_interna', 'local_generales', 'nombre', 'apellido', 'direccion', 'afiliaciones');

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
        $votante = DB::table('padroncoopluque9062026')
            ->where('CI NRO', $cedula)
            ->first();

        if (!$votante) {
            return response()->json([
                'encontrado' => false
            ]);
        }

        $data = [
            'cedula'       => $votante->{'CI NRO'} ?? '',
            'nombre'       => $votante->{'NOMBRE Y APELLIDO'} ?? '',
            'direccion'    => '',
            'mesa'         => '0',
            'orden'        => $votante->NRO ?? '0',
            'partido'      => $votante->SITUACION ?? '',
            'escuela'      => '',
            'ciudad'       => '',
            'departamento' => '',
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
           VERIFICAR QUE EXISTA EN PADRÓN
        ============================ */
            $existeEnPadronCoop = DB::table('padroncoopluque9062026')
                ->where('CI NRO', $cedula)
                ->exists();

            if (!$existeEnPadronCoop) {
                throw new \Exception("La cédula {$cedula} no existe en el padrón de la cooperativa.");
            }

            /* ===========================
            OBTENER PUNTERO Y SISTEMA
         ============================ */
            $puntero = Puntero::with('dirigente.equipo')->find($idPuntero);

            if (!$puntero || !$puntero->dirigente || !$puntero->dirigente->equipo) {
                throw new \Exception('No se pudo determinar el sistema del puntero.');
            }

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
                'observacion'   => $request->observacion,
            ]);

            DB::commit();

            return redirect()->back()
                ->with('successAlert', 'Votante agregado correctamente.')
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
    public function updateObservacion(Request $request, $id)
    {
        try {
            $request->validate([
                'observacion' => 'nullable|string|max:500',
            ]);

            $votante = Votante::findOrFail($id);
            $votante->observacion = $request->observacion;
            $votante->save();

            return response()->json([
                'success' => true,
                'message' => 'Observación actualizada correctamente',
                'observacion' => $votante->observacion
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar la observación: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeAjax(Request $request)
    {
        try {
            DB::beginTransaction();

            /* =========================== VALIDACIONES BÁSICAS ============================ */
            $request->validate([
                'cedula' => 'required',
                'nombre' => 'required',
                'idpuntero' => 'required|exists:puntero,id',
                'tipo_votante' => 'required'
            ]);

            $cedula = $request->cedula;
            $idPuntero = $request->idpuntero;

            /* =========================== VERIFICAR QUE EXISTA EN PADRÓN COOPERATIVA ============================ */
            $existeEnPadronCoop = DB::table('padroncoopluque9062026')
                ->where('CI NRO', $cedula)
                ->exists();

            if (!$existeEnPadronCoop) {
                return response()->json([
                    'success' => false,
                    'message' => "Error: la cédula {$cedula} no existe en el padrón de la cooperativa."
                ], 422);
            }

            /* =========================== OBTENER PUNTERO Y SISTEMA ============================ */
            $puntero = Puntero::with('dirigente.equipo')->find($idPuntero);

            if (!$puntero || !$puntero->dirigente || !$puntero->dirigente->equipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se pudo determinar el sistema del puntero.'
                ], 400);
            }

            /* =========================== CREAR VOTANTE ============================ */
            $votante = Votante::create([
                'cedula' => $cedula,
                'nombre' => $request->nombre,
                'tipo_votante' => $request->tipo_votante,
                'voto' => $request->voto ?? null,
                'idpuntero' => $idPuntero,
                'idusuario' => auth()->id(),
                'direccion' => $request->direccion,
                'mesa' => $request->mesa,
                'orden' => $request->orden,
                'partido' => $request->partido,
                'escuela' => $request->escuela,
                'ciudad' => $request->ciudad,
                'departamento' => $request->departamento,
                'observacion' => $request->observacion,
            ]);

            DB::commit();

            // Obtener la lista actualizada de votantes
            $votantes = Votante::porPuntero($idPuntero);

            return response()->json([
                'success' => true,
                'message' => 'Votante agregado correctamente.',
                'tipo_alerta' => 'success',
                'punteroId' => $idPuntero,
                'punteroNombre' => $puntero->nombre,
                'votantes' => $votantes,
                'votante_creado' => [
                    'id' => $votante->id,
                    'cedula' => $votante->cedula,
                    'nombre' => $votante->nombre,
                    'escuela' => $votante->escuela,
                    'mesa' => $votante->mesa,
                    'orden' => $votante->orden,
                    'tipo_votante' => $votante->tipo_votante,
                    'observacion' => $votante->observacion
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear votante AJAX', [
                'cedula' => $request->cedula ?? 'N/A',
                'puntero_id' => $request->idpuntero ?? 'N/A',
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al guardar el votante: ' . $e->getMessage()
            ], 500);
        }
    }
}
