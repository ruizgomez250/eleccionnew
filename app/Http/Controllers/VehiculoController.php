<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Equipo;
use App\Models\Puntero;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
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
            // Validación completa con todos los campos
            $validated = $request->validate([
                'cedulachofer' => 'required|string|max:20',
                'nombre' => 'required|string|max:150',
                'chapa' => 'required|string|max:10',
                'tipovehiculo' => 'required|string|max:20',
                'capacidad' => 'required|integer|min:1',
                'direccion' => 'nullable|string|max:255',
                'barriocompania' => 'nullable|string|max:100',
                'telefono1' => 'required|string|max:20',
                'telefono2' => 'nullable|string|max:20',
                'telefono3' => 'nullable|string|max:20',
                'montopagar' => 'required|numeric|min:0',
                'cantidadpagos' => 'required|integer|min:1',
                'rol' => 'required|string|in:PUNTERO,LOGISTICA',
                'id_equipo' => 'nullable|exists:equipo,id',
                'cedulaproponente' => 'nullable|string|max:20',
                'nombreproponente' => 'nullable|string|max:150',
                'telefonoproponente' => 'nullable|string|max:20',
            ]);

            // Decodificar caracteres especiales
            $validated['nombre'] = html_entity_decode($validated['nombre']);
            $validated['direccion'] = html_entity_decode($validated['direccion'] ?? '');
            $validated['barriocompania'] = html_entity_decode($validated['barriocompania'] ?? '');
            $validated['nombreproponente'] = html_entity_decode($validated['nombreproponente'] ?? '');

            // Buscar el último numero_auto del equipo
            $ultimoNumero = Vehiculo::where('id_equipo', $validated['id_equipo'])
                ->max('numero_auto');

            $validated['numero_auto'] = $ultimoNumero ? $ultimoNumero + 1 : 1;

            // Agregar el id_sistema del usuario logueado
            $validated['id_sistema'] = Auth::user()->sistema;

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
        $vehiculo->direccion = html_entity_decode($vehiculo->direccion);
        $vehiculo->barriocompania = html_entity_decode($vehiculo->barriocompania);
        $vehiculo->nombre = html_entity_decode($vehiculo->nombre);
        $vehiculo->nombreproponente = html_entity_decode($vehiculo->nombreproponente);
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
        // Validación de todos los campos
        $request->validate([
            'cedulachofer' => 'required|string|max:20',
            'nombre' => 'required|string|max:150',
            'chapa' => 'required|string|max:10',
            'tipovehiculo' => 'required|string|max:20',
            'capacidad' => 'required|integer|min:1',
            'direccion' => 'nullable|string|max:255',
            'barriocompania' => 'nullable|string|max:100',
            'telefono1' => 'required|string|max:20',
            'telefono2' => 'nullable|string|max:20',
            'telefono3' => 'nullable|string|max:20',
            'montopagar' => 'required|integer|min:0',
            'cantidadpagos' => 'required|integer|min:1',
            'rol' => 'required|string|in:PUNTERO,LOGISTICA',
            'id_equipo' => 'nullable|exists:equipo,id',
            'cedulaproponente' => 'nullable|string|max:20',
            'nombreproponente' => 'nullable|string|max:150',
            'telefonoproponente' => 'nullable|string|max:20',
        ]);

        // Buscar el vehículo con restricción de sistema
        $vehiculo = Vehiculo::whereHas('equipo', function ($q) {
            $q->where('sist', Auth::user()->sistema);
        })->findOrFail($id);

        // Decodificar caracteres escapados antes de guardar
        $data = [
            'cedulachofer' => $request->cedulachofer,
            'nombre' => html_entity_decode($request->nombre),
            'chapa' => $request->chapa,
            'tipovehiculo' => $request->tipovehiculo,
            'capacidad' => $request->capacidad,
            'direccion' => html_entity_decode($request->direccion),
            'barriocompania' => html_entity_decode($request->barriocompania),
            'telefono1' => $request->telefono1,
            'telefono2' => $request->telefono2,
            'telefono3' => $request->telefono3,
            'montopagar' => $request->montopagar,
            'cantidadpagos' => $request->cantidadpagos,
            'rol' => $request->rol,
            'id_equipo' => $request->id_equipo,
            'cedulaproponente' => $request->cedulaproponente,
            'nombreproponente' => html_entity_decode($request->nombreproponente),
            'telefonoproponente' => $request->telefonoproponente,
        ];

        // Actualizar el vehículo
        $vehiculo->update($data);

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
        $equipo = Equipo::findOrFail($vehiculo->id_equipo);
        // Calcular montos
        $montoTotal = $vehiculo->montopagar;
        $senal = $montoTotal / 2;
        $pagoFinal = $montoTotal - $senal;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetMargins(15, 15, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('helvetica', '', 10);

        // ------------------------
        // CABECERA POLÍTICA
        // ------------------------
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 14);
        $pdf->Cell(0, 7, 'NUEVO LIBERALISMO – LISTA 3', 0, 1, 'C');
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 5, 'MANOLO ACHUCARRO GILL – INTENDENTE', 0, 1, 'C');
        $pdf->Ln(3);

        $pdf->SetFont('helvetica', '', 10);
        $pdf->SetFillColor(240, 240, 240);

        // Crear una tabla simple con dos columnas
        $anchoColumna = ($pdf->getPageWidth() - $pdf->getMargins()['left'] - $pdf->getMargins()['right']) / 2;

        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->Cell($anchoColumna, 6, 'ALCIDES RIVEROS', 0, 0, 'C');
        $pdf->Cell($anchoColumna, 6, 'RODRIGO BLANCO AMARILLA', 0, 1, 'C');

        $pdf->SetFont('helvetica', 'I', 8);
        $pdf->SetX($pdf->getMargins()['left']);
        $pdf->Cell($anchoColumna, 5, 'PRESIDENTE DEL DIRECTORIO PLRA', 0, 0, 'C');
        $pdf->Cell($anchoColumna, 5, 'MIEMBRO DEL DIRECTORIO', 0, 1, 'C');
        $pdf->Ln(8);

        // ------------------------
        // TÍTULO DEL CONTRATO
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'CONTRATO DE ALQUILER DE VEHICULO Y SERVICIO DE CONDUCCION', 0, 1, 'C');
        $pdf->Ln(5);

        // ------------------------
        // INTRODUCCIÓN
        // ------------------------
        $pdf->SetFont('helvetica', '', 10);

        $fechaActual = date('d');
        $mesActual = 'junio';
        $anioActual = date('Y');

        $textoIntroduccion = "En la Ciudad de Luque, Departamento Central de la República del Paraguay a los {$fechaActual} días del mes de {$mesActual} del año {$anioActual}, se celebra el presente CONTRATO DE ALQUILER DE VEHICULO Y DE SERVICIO DE CONDUCCION, entre el Señor, {$vehiculo->nombre}, con Cedula de Identidad N° {$vehiculo->cedulachofer}, domiciliado en la casa de la calle {$vehiculo->direccion}, del barrio/compañía {$vehiculo->barriocompania} por una parte, y llamado en adelante el PROPIETARIO; y por la otra parte el Señor _________________________, con Cedula de Identidad N° ____________ llamado en adelante el ARRENDATARIO, domiciliado en la casa de la calle _____________________, del barrio ___________________________ de esta Ciudad; el mismo se regirá por las siguientes clausulas y condiciones.";

        $pdf->MultiCell(0, 5, $textoIntroduccion, 0, 'J');
        $pdf->Ln(3);

        // ------------------------
        // CLAUSULA PRIMERA
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'CLAUSULA PRIMERA: OBJETO', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 5, 'El presente Contrato de Alquiler de Vehículo y Servicio de Conducción tiene por objeto establecer derechos y obligaciones de ambas partes.', 0, 'J');
        $pdf->Ln(2);

        // ------------------------
        // CLAUSULA SEGUNDA
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'CLAUSULA SEGUNDA: CONDICIONES', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $textoCondiciones = "El propietario/a, es dueño/a del vehículo tipo: {$vehiculo->tipo_vehiculo}; marca: {$vehiculo->marca}; modelo: {$vehiculo->modelo}; color: {$vehiculo->color}; con numero de chapa: {$vehiculo->chapa}, el cual se encuentra en perfectas condiciones mecánicas y generales, para circular y trasladar personas durante el día de las elecciones (7 de junio de {$anioActual}). 
    
El presente contrato incluye el servicio de conducción del mismo.
    
Queda establecido que el conductor se presentará con el vehículo alquilado el día domingo 7 de junio del año {$anioActual}, a las 5:30 hs en _______________________________, donde funcionará de puesto de comando de este Equipo Político ese día, y quedará a cargo del Señor/a: _______________________________, que es el dirigente con el que trabajará esa jornada hasta las 16:30 hs.  
    
El conductor se compromete a trasladar a las personas al local de votación que será: {$equipo->descripcion}; y no puede abandonar al dirigente o a los pasajeros bajo ninguna circunstancia hasta el horario señalado; así mismo el vehículo alquilado será identificado con un distintivo de móvil Nro.: {$vehiculo->numero_auto}, así como cartelería alusiva a las candidaturas señaladas, a la fecha de la firma del contrato y dos días antes de las elecciones se le colocarán los identificatorios de lista y candidaturas de este Movimiento para su correcta identificación.";

        $pdf->MultiCell(0, 5, $textoCondiciones, 0, 'J');
        $pdf->Ln(2);

        // ------------------------
        // CLAUSULA TERCERA
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'CLAUSULA TERCERA: PRECIO', 0, 1);
        $pdf->SetFont('helvetica', '', 10);

        $montoTotalTexto = number_format($montoTotal, 0, ",", ".");
        $senalTexto = number_format($senal, 0, ",", ".");
        $montoLetras = $this->convertirNumeroALetras($montoTotal);
        $senalLetras = $this->convertirNumeroALetras($senal);

        $textoPrecio = "El precio convenido por el alquiler del vehículo y el servicio de conducción queda establecido en la suma de Gs. {$montoTotalTexto}, ({$montoLetras} guaraníes), que será pagada de la siguiente forma:
    
Seña de trato: queda establecido que la misma será el 50% de lo pactado con el arrendatario, que queda en Gs. {$senalTexto}, ({$senalLetras} guaraníes) y será abonada a la firma del presente contrato, quedando este documento como comprobante de la suma abonada que será entregada a entera satisfacción del propietario del vehículo, y firmando este conforme al pie del mismo.
    
El pago final del 50% del monto que falta será efectuado al término de las elecciones internas del P.L.R.A. a realizarse el día domingo 7 de junio de {$anioActual} o al día siguiente en el Puesto de Comando del Movimiento.";

        $pdf->MultiCell(0, 5, $textoPrecio, 0, 'J');
        $pdf->Ln(2);

        // ------------------------
        // CLAUSULA CUARTA
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'CLAUSULA CUARTA: RESPONSABILIDAD', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 5, 'Es de entera responsabilidad, a cuenta y cargo de EL PROPIETARIO, cualquier multa, infracción, daños al vehículo o terceros que pueda surgir durante el tiempo que se encuentre prestando servicios en virtud del presente contrato.', 0, 'J');
        $pdf->Ln(2);

        // ------------------------
        // CLAUSULA QUINTA
        // ------------------------
        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(0, 6, 'CLAUSULA QUINTA: SOLUCION DE CONFLICTOS', 0, 1);
        $pdf->SetFont('helvetica', '', 10);
        $pdf->MultiCell(0, 5, 'Ambas partes se comprometen al fiel cumplimiento de los términos y condiciones pactadas en el presente contrato, conforme a la buena fe que preceden este tipo de actos. Sin embargo en caso de duda en la interpretación de los términos del mismo, agotarán todas las instancias de tal forma a resolver por medio del advenimiento amistoso, mediación o arbitraje.', 0, 'J');
        $pdf->Ln(5);

        // ------------------------
        // CIERRE Y FIRMAS
        // ------------------------
        $pdf->MultiCell(0, 5, 'Una vez leído el presente documento y ratificando el contenido del mismo, en prueba de conformidad, suscriben ambas partes en dos ejemplares de un mismo tenor y a un solo efecto.', 0, 'J');
        $pdf->Ln(10);

        // Tabla de firmas
        $pdf->SetFont('helvetica', '', 10);

        // Firma PROPIETARIO
        $pdf->Cell(90, 6, 'Firma: _________________________', 0, 0, 'L');
        $pdf->Cell(90, 6, 'Firma: _________________________', 0, 1, 'L');

        $pdf->Cell(90, 6, "C.I. N°: {$vehiculo->cedulachofer}", 0, 0, 'L');
        $pdf->Cell(90, 6, 'C.I. N°: _________________________', 0, 1, 'L');

        $pdf->SetFont('helvetica', 'B', 10);
        $pdf->Cell(90, 6, 'PROPIETARIO', 0, 0, 'C');
        $pdf->Cell(90, 6, 'ARRENDATARIO', 0, 1, 'C');

        // ------------------------
        // RECIBOS (Opcional)
        // ------------------------
        // Recibo de SEÑA (50%)
        $pdf->AddPage();

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'RECIBO DE PAGO - SEÑA (50%)', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);
        $textoRecibo1 = "
    Recibimos de EL ARRENDATARIO la suma de Gs. {$senalTexto} ({$senalLetras} guaraníes)
    en concepto de SEÑA (50%) por el servicio de transporte prestado según el contrato de alquiler de vehículo firmado en fecha {$fechaActual} de {$mesActual} de {$anioActual}.
    
    Vehículo: {$vehiculo->marca} {$vehiculo->modelo} - Chapa: {$vehiculo->chapa} - Móvil N°: {$vehiculo->numero_auto}
    Propietario/Chofer: {$vehiculo->nombre} - C.I.: {$vehiculo->cedulachofer}
    
    Fecha de pago: _______________________
    
    _________________________________________
    Firma del PROPIETARIO
    
    _________________________________________
    Firma del ARRENDATARIO
    ";

        $pdf->MultiCell(0, 6, $textoRecibo1, 0, 'J');

        // Recibo de PAGO FINAL (50%)
        $pdf->AddPage();

        $pagoFinalTexto = number_format($pagoFinal, 0, ",", ".");
        $pagoFinalLetras = $this->convertirNumeroALetras($pagoFinal);

        $pdf->SetFont('helvetica', 'B', 12);
        $pdf->Cell(0, 7, 'RECIBO DE PAGO - PAGO FINAL (50%)', 0, 1, 'C');
        $pdf->Ln(5);

        $pdf->SetFont('helvetica', '', 10);
        $textoRecibo2 = "
    Recibimos de EL ARRENDATARIO la suma de Gs. {$pagoFinalTexto} ({$pagoFinalLetras} guaraníes)
    en concepto de PAGO FINAL (50% restante) por el servicio de transporte prestado según el contrato de alquiler de vehículo firmado en fecha {$fechaActual} de {$mesActual} de {$anioActual}.
    
    Vehículo: {$vehiculo->marca} {$vehiculo->modelo} - Chapa: {$vehiculo->chapa} - Móvil N°: {$vehiculo->numero_auto}
    Propietario/Chofer: {$vehiculo->nombre} - C.I.: {$vehiculo->cedulachofer}
    
    Fecha de pago: _______________________
    
    _________________________________________
    Firma del PROPIETARIO
    
    _________________________________________
    Firma del ARRENDATARIO
    ";

        $pdf->MultiCell(0, 6, $textoRecibo2, 0, 'J');

        $pdf->Output("contrato_vehiculo_{$vehiculo->numero_auto}.pdf", 'I');
        exit;
    }

    // Función auxiliar para convertir números a letras
    private function convertirNumeroALetras($numero)
    {
        $nro = $numero;
        $partes = explode('.', $nro);
        $entero = $partes[0];

        $unidades = ['', 'UN', 'DOS', 'TRES', 'CUATRO', 'CINCO', 'SEIS', 'SIETE', 'OCHO', 'NUEVE'];
        $decenas = ['', 'DIEZ', 'VEINTE', 'TREINTA', 'CUARENTA', 'CINCUENTA', 'SESENTA', 'SETENTA', 'OCHENTA', 'NOVENTA'];
        $centenas = ['', 'CIENTO', 'DOSCIENTOS', 'TRESCIENTOS', 'CUATROCIENTOS', 'QUINIENTOS', 'SEISCIENTOS', 'SETECIENTOS', 'OCHOCIENTOS', 'NOVECIENTOS'];

        $numero_letras = '';

        if ($entero >= 1000) {
            $miles = floor($entero / 1000);
            if ($miles == 1) {
                $numero_letras .= 'MIL ';
            } else {
                $numero_letras .= $this->convertirNumeroALetras($miles) . ' MIL ';
            }
            $entero = $entero % 1000;
        }

        if ($entero >= 100) {
            $centena = floor($entero / 100);
            if ($entero == 100) {
                $numero_letras .= 'CIEN ';
            } else {
                $numero_letras .= $centenas[$centena] . ' ';
            }
            $entero = $entero % 100;
        }

        if ($entero >= 1) {
            if ($entero <= 9) {
                $numero_letras .= $unidades[$entero];
            } elseif ($entero <= 19) {
                $especiales = [
                    10 => 'DIEZ',
                    11 => 'ONCE',
                    12 => 'DOCE',
                    13 => 'TRECE',
                    14 => 'CATORCE',
                    15 => 'QUINCE',
                    16 => 'DIECISÉIS',
                    17 => 'DIECISIETE',
                    18 => 'DIECIOCHO',
                    19 => 'DIECINUEVE'
                ];
                $numero_letras .= $especiales[$entero];
            } else {
                $decena = floor($entero / 10);
                $unidad = $entero % 10;
                if ($unidad == 0) {
                    $numero_letras .= $decenas[$decena];
                } else {
                    $numero_letras .= $decenas[$decena] . ' Y ' . $unidades[$unidad];
                }
            }
        }

        return trim($numero_letras);
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
        // ✅ Log para depuración
        Log::info('Datos recibidos en storeFromPuntero:', $request->all());
        
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
            'direccion' => 'nullable|string',
            'barriocompania' => 'nullable|string',
            'id_equipo' => 'nullable|exists:equipos,id',
            
            'cedulaproponente' => 'required|string',
            'nombreproponente' => 'required|string|max:150',
            'telefonoproponente' => 'required|string',
            
            'id_puntero' => 'required|exists:puntero,id',
        ]);

        // Obtener el puntero con su equipo
        $puntero = Puntero::with('equipo')->find($validated['id_puntero']);

        if (!$puntero) {
            return response()->json([
                'success' => false,
                'message' => 'Puntero no encontrado'
            ], 422);
        }

        // Si no se envió id_equipo, usar el del puntero
        if (empty($validated['id_equipo'])) {
            if (!$puntero->id_equipo) {
                return response()->json([
                    'success' => false,
                    'message' => 'El puntero no tiene un equipo asignado'
                ], 422);
            }

            if (!$puntero->equipo || !$puntero->equipo->sist) {
                return response()->json([
                    'success' => false,
                    'message' => 'El equipo del puntero no tiene un sistema asignado'
                ], 422);
            }

            $validated['id_equipo'] = $puntero->id_equipo;
            $validated['id_sistema'] = $puntero->equipo->sist;
        } else {
            $equipo = Equipo::find($validated['id_equipo']);
            if ($equipo && $equipo->sist) {
                $validated['id_sistema'] = $equipo->sist;
            }
        }

        // ==================== VERIFICAR DUPLICADOS ====================
        
        // 1. Verificar si la chapa ya existe en el MISMO sistema
        $vehiculoExistente = Vehiculo::where('chapa', $validated['chapa'])
            ->where('id_sistema', $validated['id_sistema'])
            ->first();

        if ($vehiculoExistente) {
            // Verificar si el vehículo ya está asignado a ESTE puntero
            $yaAsignado = $vehiculoExistente->punteros()
                ->where('puntero_id', $validated['id_puntero'])
                ->exists();

            if ($yaAsignado) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ Este vehículo (chapa ' . $validated['chapa'] . ') YA está asignado a este puntero en el sistema ' . ($vehiculoExistente->equipo->sistema->nombre ?? 'actual')
                ], 422);
            }

            // Verificar si el vehículo está asignado a OTRO puntero en el MISMO sistema
            $otroPuntero = $vehiculoExistente->punteros()
                ->where('puntero_id', '!=', $validated['id_puntero'])
                ->first();

            if ($otroPuntero) {
                return response()->json([
                    'success' => false,
                    'message' => '⚠️ El vehículo con chapa ' . $validated['chapa'] . ' YA está asignado al puntero "' . $otroPuntero->nombre . '" en el sistema ' . ($vehiculoExistente->equipo->sistema->nombre ?? 'actual')
                ], 422);
            }

            // Si el vehículo existe pero NO está asignado a ningún puntero, lo reutilizamos
            $vehiculo = $vehiculoExistente;
            
            // Actualizar los datos del vehículo (por si cambiaron)
            $vehiculo->update([
                'nombre' => $validated['nombre'],
                'cedulachofer' => $validated['cedulachofer'],
                'tipovehiculo' => $validated['tipovehiculo'],
                'capacidad' => $validated['capacidad'],
                'telefono1' => $validated['telefono1'],
                'telefono2' => $validated['telefono2'],
                'montopagar' => $validated['montopagar'],
                'cantidadpagos' => $validated['cantidadpagos'],
                'rolvehiculo' => $validated['rolvehiculo'],
                'direccion' => $validated['direccion'] ?? null,
                'barriocompania' => $validated['barriocompania'] ?? null,
                'cedulaproponente' => $validated['cedulaproponente'],
                'nombreproponente' => $validated['nombreproponente'],
                'telefonoproponente' => $validated['telefonoproponente'],
            ]);
            
            Log::info('Vehículo existente reutilizado:', ['id' => $vehiculo->id, 'chapa' => $vehiculo->chapa]);
            
            // Verificar si ya está asignado a este puntero (por si acaso)
            $yaAsignado = $vehiculo->punteros()->where('puntero_id', $validated['id_puntero'])->exists();
            if (!$yaAsignado) {
                $vehiculo->punteros()->attach($validated['id_puntero'], ['fecha_asignacion' => now()]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Vehículo reutilizado y asignado correctamente (ya existía en el sistema)',
                'data' => [
                    'vehiculo_id' => $vehiculo->id,
                    'chapa' => $vehiculo->chapa,
                    'id_equipo' => $vehiculo->id_equipo,
                    'id_sistema' => $vehiculo->id_sistema,
                    'proponente' => $vehiculo->nombreproponente,
                    'telefono_proponente' => $vehiculo->telefonoproponente,
                    'reutilizado' => true
                ]
            ]);
        }

        // 2. Verificar si la chapa existe en OTRO sistema (diferente)
        $vehiculoOtroSistema = Vehiculo::where('chapa', $validated['chapa'])
            ->where('id_sistema', '!=', $validated['id_sistema'])
            ->first();

        if ($vehiculoOtroSistema) {
            $nombreOtroSistema = $vehiculoOtroSistema->equipo->sistema->nombre ?? 'desconocido';
            return response()->json([
                'success' => false,
                'message' => '⚠️ El vehículo con chapa ' . $validated['chapa'] . ' YA existe en el sistema "' . $nombreOtroSistema . '". No se puede duplicar en diferentes sistemas.'
            ], 422);
        }

        // Buscar el último numero_auto del equipo
        $ultimoNumero = Vehiculo::where('id_equipo', $validated['id_equipo'])
            ->max('numero_auto');

        $validated['numero_auto'] = $ultimoNumero ? $ultimoNumero + 1 : 1;

        // ✅ Log antes de crear
        Log::info('Datos a guardar en vehículo (nuevo):', $validated);

        // Crear vehículo NUEVO
        $vehiculo = Vehiculo::create($validated);

        // ✅ Log después de crear
        Log::info('Vehículo creado:', $vehiculo->toArray());

        // Asignar el puntero al vehículo
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
                'id_sistema' => $vehiculo->id_sistema,
                'proponente' => $vehiculo->nombreproponente,
                'telefono_proponente' => $vehiculo->telefonoproponente,
                'reutilizado' => false
            ]
        ]);
        
    } catch (\Illuminate\Validation\ValidationException $e) {
        Log::error('Error de validación:', $e->errors());
        return response()->json([
            'success' => false,
            'errors' => $e->errors()
        ], 422);
    } catch (\Exception $e) {
        Log::error('Error general:', ['message' => $e->getMessage()]);
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
