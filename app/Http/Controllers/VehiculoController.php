<?php

namespace App\Http\Controllers;

use App\Models\Puntero;
use App\Models\Vehiculo;
use Illuminate\Http\Request;

class VehiculoController extends Controller
{
    public function index()
    {
        $vehiculos = Vehiculo::orderBy('nombre')->get();
        return view('vehiculo.index', compact('vehiculos'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'cedulachofer' => 'required',
            'nombre'       => 'required',
            'chapa'        => 'required',
        ]);

        Vehiculo::create($request->all());

        return redirect()->route('vehiculo.index')
            ->with('success', 'Vehículo registrado correctamente');
    }

    public function edit($id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        return view('vehiculo.edit', compact('vehiculo'));
    }

    public function update(Request $request, $id)
    {
        $vehiculo = Vehiculo::findOrFail($id);
        $vehiculo->update($request->all());

        return redirect()->route('vehiculo.index')
            ->with('success', 'Vehículo actualizado');
    }

    public function destroy($id)
    {
        Vehiculo::findOrFail($id)->delete();

        return redirect()->route('vehiculo.index')
            ->with('success', 'Vehículo eliminado');
    }
    public function actualizarPunteros(Request $request, Vehiculo $vehiculo)
    {
        // Validar que los punteros existen
        $request->validate([
            'punteros' => 'array|exists:puntero,id',
        ]);

        // Sincronizar punteros asignados al vehículo
        $vehiculo->punteros()->sync($request->punteros ?? []);

        return redirect()->back()->with('success', 'Punteros actualizados correctamente.');
    }
    public function punteros(Vehiculo $vehiculo)
    {
        return response()->json([
            'todos' => Puntero::select('id', 'nombre')->get(),
            'asignados' => $vehiculo->punteros()->select('punteros.id', 'punteros.nombre')->get()
        ]);
    }


    public function guardarPunteros(Request $request)
    {
        $vehiculo = Vehiculo::findOrFail($request->vehiculo_id);
        $vehiculo->punteros()->sync($request->punteros ?? []);

        return response()->json(['success' => true, 'message' => 'Punteros actualizados correctamente']);
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
}
