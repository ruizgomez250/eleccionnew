<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Equipo;
use App\Models\Puntero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class VehiculoController extends Controller
{

    /*
    |--------------------------------------------------------------------------
    | LISTAR + FILTRAR
    |--------------------------------------------------------------------------
    */
    public function index(Request $request)
    {
        $sistema = Auth::user()->sistema;
        $equipoId = $request->equipo_id;

        // 🔹 equipos solo del sistema del usuario
        $equipos = Equipo::where('sist', $sistema)
            ->orderBy('descripcion')
            ->get();

        // 🔹 vehiculos filtrando por sistema usando id_sistema
        $vehiculos = Vehiculo::with('equipo')

            // SOLO vehículos que pertenecen al sistema del usuario
            ->where('id_sistema', $sistema)

            // 🔥 filtro por equipo específico (si se selecciona uno)
            ->when($equipoId, function ($q) use ($equipoId) {
                return $q->where('id_equipo', $equipoId);
            })

            ->orderBy('nombre')
            ->get();

        return view('vehiculo.index', compact(
            'vehiculos',
            'equipos',
            'equipoId'
        ));
    }



    /*
    |--------------------------------------------------------------------------
    | CREAR
    |--------------------------------------------------------------------------
    */
    public function create()
    {
        $sistema = Auth::user()->sistema;

        $equipos = Equipo::where('sist', $sistema)
            ->orderBy('descripcion')
            ->get();

        return view('vehiculo.create', compact('equipos'));
    }



    /*
    |--------------------------------------------------------------------------
    | GUARDAR
    |--------------------------------------------------------------------------
    */
    public function store(Request $request)
    {
        try {
            // Validación
            $validated = $request->validate([
                'nombre'        => 'required|string|max:150',
                'id_equipo'     => 'nullable',
                'cedulachofer'  => 'required',
                'chapa'         => 'required',
                'tipovehiculo'  => 'required',
                'capacidad'     => 'required|integer',
                'telefono1'     => 'required',
                'telefono2'     => 'nullable',
                'telefono3'     => 'nullable',
                'montopagar'    => 'required|numeric',
                'cantidadpagos' => 'required|integer',
                'rol'   => 'required|string', // Asegúrate que el nombre coincida con el del formulario
            ]);

            // Buscar el último numero_auto del equipo
            $ultimoNumero = Vehiculo::where('id_equipo', $validated['id_equipo'])
                ->max('numero_auto');

            $validated['numero_auto'] = $ultimoNumero ? $ultimoNumero + 1 : 1;

            // Agregar el id_sistema del usuario logueado
            $validated['id_sistema'] = Auth::user()->sistema; // o Auth::user()->sistemaRelacion->id dependiendo de tu relación

            // Crear vehículo
            Vehiculo::create($validated);

            return redirect()
                ->route('vehiculo.index')
                ->with('success', 'Vehículo creado correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            // En caso de error general
            return redirect()->back()
                ->with('error', 'Ocurrió un error al crear el vehículo: ' . $e->getMessage())
                ->withInput();
        }
    }





    /*
    |--------------------------------------------------------------------------
    | EDITAR
    |--------------------------------------------------------------------------
    */
    public function edit($id)
    {
        $sistema = Auth::user()->sistema;

        $vehiculo = Vehiculo::whereHas('equipo', function ($q) use ($sistema) {
            $q->where('sist', $sistema);
        })->findOrFail($id);

        $equipos = Equipo::where('sist', $sistema)
            ->orderBy('descripcion')
            ->get();

        return view('vehiculo.edit', compact('vehiculo', 'equipos'));
    }



    /*
    |--------------------------------------------------------------------------
    | ACTUALIZAR
    |--------------------------------------------------------------------------
    */
    public function update(Request $request, $id)
    {
        $request->validate([
            'nombre'     => 'required|string|max:150',
            'equipo_id'  => 'required|exists:equipo,id',
        ]);

        $vehiculo = Vehiculo::whereHas('equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->findOrFail($id);

        $vehiculo->update($request->all());

        return redirect()
            ->route('vehiculo.index')
            ->with('success', 'Vehículo actualizado correctamente');
    }



    /*
    |--------------------------------------------------------------------------
    | ELIMINAR
    |--------------------------------------------------------------------------
    */
    public function destroy($id)
    {
        $vehiculo = Vehiculo::where('id_sistema', Auth::user()->sistema)
            ->findOrFail($id);

        $vehiculo->delete();

        return back()->with('success', 'Vehículo eliminado correctamente');
    }
    public function generarContratoPDF($idVehiculo)
    {
        $vehiculo = Vehiculo::findOrFail($idVehiculo);

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetMargins(10, 15, 10);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('helvetica', '', 10);

        // ------------------------
        // PÁGINA 1: CONTRATO
        // ------------------------
        $pdf->AddPage();
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'CONTRATO DE ALQUILER DE VEHÍCULO PARA SERVICIO ELECTORAL', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);
        $textoContrato = "
Entre la Coordinación del colegio Electoral (en adelante EL COORDINADOR), y el Sr. {$vehiculo->nombre}, C.I. N° {$vehiculo->cedulachofer} (en adelante EL CHOFER), se acuerda lo siguiente:

1. OBJETO: EL CHOFER se compromete a trasladar personal electoral en el vehículo de chapa {$vehiculo->chapa}, número de auto {$vehiculo->numero_auto}, durante las elecciones, según las indicaciones del COORDINADOR.

2. ENTREGA Y RECEPCIÓN: El vehículo será entregado por CHOFER en buenas condiciones.

3. PAGO: EL COORDINADOR abonará el monto total de Gs. " . number_format($vehiculo->montopagar, 0, ",", ".") . " en {$vehiculo->cantidadpagos} pagos, de acuerdo a la programación de cada servicio.

4. OBLIGACIONES DEL CHOFER:
   - Atender las llamadas del coordinador y presentarse en el local cuando se le solicite.
   - Cumplir con los traslados en el horario y lugares indicados.
   - Mantener el vehículo en condiciones adecuadas.

5. PENALIDAD: En caso de no atender llamadas o no presentarse, EL CHOFER renuncia a recibir el pago correspondiente a los servicios incumplidos.

6. FIRMA: La firma del contrato implica aceptación de todas las condiciones y obligaciones aquí establecidas.

";

        $pdf->MultiCell(0, 6, $textoContrato, 0, 'J');
        $pdf->Ln(10);

        $pdf->Cell(0, 6, "Firma del CHOFER: ___________________________", 0, 1);
        $pdf->Cell(0, 6, "Firma del COORDINADOR DEL COLEGIO ELECTORAL : ______________________", 0, 1);

        // ------------------------
        // PÁGINAS DE RECIBOS
        // ------------------------
        $montoPorPago = $vehiculo->montopagar / max(1, $vehiculo->cantidadpagos);

        for ($i = 1; $i <= $vehiculo->cantidadpagos; $i++) {
            $pdf->AddPage();
            $pdf->SetFont('helvetica', 'B', 12);
            $pdf->Cell(0, 7, "RECIBO DE PAGO ($i/{$vehiculo->cantidadpagos})", 0, 1, 'C');
            $pdf->Ln(5);

            $pdf->SetFont('helvetica', '', 10);
            $textoRecibo = "
Recibimos de EL COORDINADOR DEL COLEGIO electoral la suma de Gs. " . number_format($montoPorPago, 0, ",", ".") . "
en concepto de pago por el servicio de transporte prestado según el contrato de alquiler de vehículo firmado.

Vehículo: {$vehiculo->chapa} - {$vehiculo->numero_auto}
Chofer: {$vehiculo->nombre} - C.I.: {$vehiculo->cedulachofer}

Fecha: _______________________

Firma del Chofer: _______________________
Firma del Coordinador del colegio: _______________________

Este recibo constituye constancia de pago parcial o total conforme al contrato de alquiler de vehículo.
";

            $pdf->MultiCell(0, 6, $textoRecibo, 0, 'J');
        }

        $pdf->Output("contrato_vehiculo_{$vehiculo->numero_auto}.pdf", 'I');
        exit;
    }
    public function getPunteros($vehiculoId)
    {
        // Obtener el vehículo
        $vehiculo = Vehiculo::findOrFail($vehiculoId);

        // 🔹 Punteros asignados a este vehículo
        $asignados = $vehiculo->punteros()->get();

        // 🔹 Todos los punteros del mismo equipo del vehículo
        $todos = Puntero::where('id_equipo', $vehiculo->id_equipo)
            ->orderBy('nombre')
            ->get();

        return response()->json([
            'asignados' => $asignados,
            'todos' => $todos
        ]);
    }
    public function punteros(Request $request, $vehiculo)
    {
        $vehiculo = Vehiculo::with('punteros', 'equipo')->findOrFail($vehiculo);

        $equipoId = $request->query('equipo', $vehiculo->id_equipo);

        // Punteros ya asignados
        $asignados = $vehiculo->punteros;

        // Punteros disponibles del mismo equipo que no están asignados
        $todos = Puntero::where('id_equipo', $equipoId)
            ->whereNotIn('id', $asignados->pluck('id'))
            ->get();
        return response()->json([
            'asignados' => $asignados,
            'todos' => $todos
        ]);
    }

    public function asignarPuntero($vehiculoId, $punteroId)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);
        $puntero = Puntero::findOrFail($punteroId);

        // Evitar duplicados
        if (!$vehiculo->punteros->contains($puntero->id)) {
            $vehiculo->punteros()->attach($puntero->id);
        }

        // Retornar los punteros actualizados
        $vehiculo->load('punteros');
        return response()->json([
            'asignados' => $vehiculo->punteros
        ]);
    }
    public function quitarPuntero($vehiculoId, $punteroId)
    {
        $vehiculo = Vehiculo::findOrFail($vehiculoId);
        $vehiculo->punteros()->detach($punteroId);

        $vehiculo->load('punteros');
        return response()->json([
            'asignados' => $vehiculo->punteros
        ]);
    }
    public function storeFromPuntero(Request $request)
    {
        try {
            // Validación
            $validated = $request->validate([
                'nombre' => 'required|string|max:150',
                'cedulachofer' => 'required',
                'chapa' => 'required',
                'tipovehiculo' => 'required',
                'capacidad' => 'required|integer',
                'telefono1' => 'required',
                'telefono2' => 'nullable',
                'montopagar' => 'required|numeric',
                'cantidadpagos' => 'required|integer',
                'rolvehiculo' => 'required|string',
                'id_puntero' => 'required|exists:puntero,id',
            ]);

            // Obtener el puntero con su equipo
            $puntero = Puntero::with('equipo')->find($validated['id_puntero']);

            // Verificar que el puntero existe
            if (!$puntero) {
                return response()->json([
                    'success' => false,
                    'message' => 'Puntero no encontrado'
                ], 422);
            }

            // Verificar que el puntero tiene equipo
            if (!$puntero->id_equipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El puntero no tiene un equipo asignado'
                ], 422);
            }

            // Verificar que el equipo tiene sistema
            if (!$puntero->equipo || !$puntero->equipo->sist) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo del puntero no tiene un sistema asignado'
                ], 422);
            }

            // Asignar el id_equipo desde el puntero
            $validated['id_equipo'] = $puntero->id_equipo;

            // Asignar el id_sistema desde el equipo del puntero
            $validated['id_sistema'] = $puntero->equipo->sist;

            // Buscar el último numero_auto del equipo
            $ultimoNumero = Vehiculo::where('id_equipo', $validated['id_equipo'])
                ->max('numero_auto');

            $validated['numero_auto'] = $ultimoNumero ? $ultimoNumero + 1 : 1;

            // Crear vehículo
            $vehiculo = Vehiculo::create($validated);

            // Asignar el puntero al vehículo recién creado
            $vehiculo->punteros()->attach($validated['id_puntero'], [
                'fecha_asignacion' => now()
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Vehículo creado y asignado correctamente',
                'data' => [
                    'vehiculo_id' => $vehiculo->id,
                    'chapa' => $vehiculo->chapa,
                    'id_equipo' => $vehiculo->id_equipo,
                    'id_sistema' => $vehiculo->id_sistema
                ]
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }
    public function desvincularPuntero($vehiculoId, $punteroId)
    {
        try {
            $vehiculo = Vehiculo::findOrFail($vehiculoId);

            // Eliminar el vehículo completamente
            $vehiculo->delete();

            return response()->json([
                'success' => true,
                'message' => 'Vehículo eliminado correctamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el vehículo: ' . $e->getMessage()
            ], 500);
        }
    }
}
