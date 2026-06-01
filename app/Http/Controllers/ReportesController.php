<?php

namespace App\Http\Controllers;

use App\Models\Dirigente;
use App\Models\Equipo;
use App\Models\MiembroDeMesa;
use App\Models\Puntero;
use App\Models\Sistema;
use App\Models\Vehiculo;
use App\Models\Votante;
use Illuminate\Http\Request;
use TCPDF;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ReportesController extends Controller
{
    public function vehicporsis()
    {
        $vehiculos = Vehiculo::with(['equipo', 'punteros'])
            ->where('id_sistema', Auth::user()->sistema)
            ->get();

        $totalMonto     = $vehiculos->sum('montopagar');
        $totalPagos     = $vehiculos->sum('cantidadpagos');
        $totalVehiculos = $vehiculos->count();

        return view('reportes.vehiculos_porsistema', compact(
            'vehiculos',
            'totalMonto',
            'totalPagos',
            'totalVehiculos'
        ));
    }
    public function index($equipoId = null)
    {
        // Equipos del sistema del usuario
        $equipos = Equipo::where('sist', Auth::user()->sistema)->get();

        // Traer dirigentes filtrando por sistema y por equipo si se pasa el ID
        $dirigentes = Dirigente::with('punteros.votantes', 'equipo')
            ->whereHas('equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->when($equipoId, fn($q) => $q->where('id_equipo', $equipoId))
            ->get();

        // Calcular punteros_count y votantes_count por dirigente
        foreach ($dirigentes as $dir) {
            $dir->punteros_count = $dir->punteros->count();
            $dir->votantes_count = $dir->punteros->sum(fn($p) => $p->votantes->count());
        }

        // Total general de votantes
        $totalVotantesGeneral = $dirigentes->sum(fn($d) => $d->votantes_count);

        return view('reportes.pordirigente', compact('equipos', 'equipoId', 'dirigentes', 'totalVotantesGeneral'));
    }

    public function votantesPorDirigente($idDirigente)
    {
        // Solo dirigente del sistema del usuario
        $dirigente = Dirigente::with(['punteros.votantes'])
            ->whereHas('equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->findOrFail($idDirigente);

        $pdf = new TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Configuración general
        $pdf->SetCreator('Sistema Elecciones');
        $pdf->SetMargins(5, 15, 5);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->SetFont('helvetica', '', 9);

        foreach ($dirigente->punteros as $puntero) {

            $pdf->AddPage();

            $pdf->SetFont('helvetica', 'B', 11);
            $pdf->Cell(0, 6, 'REPORTE DE VOTANTES', 0, 1, 'C');

            $pdf->Ln(2);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(30, 6, 'Dirigente:', 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, $dirigente->nombre, 0, 1);

            $pdf->SetFont('helvetica', 'B', 9);
            $pdf->Cell(30, 6, 'Puntero:', 0, 0);
            $pdf->SetFont('helvetica', '', 9);
            $pdf->Cell(0, 6, $puntero->nombre, 0, 1);

            $pdf->Ln(4);

            $pdf->SetFont('helvetica', 'B', 8);
            $pdf->SetFillColor(180, 180, 180);

            // ✅ NUEVA CABECERA: N° como primera columna
            $pdf->Cell(12, 8, 'N°', 1, 0, 'C', true);
            $pdf->Cell(22, 8, 'Cédula', 1, 0, 'C', true);
            $pdf->Cell(48, 8, 'Nombre', 1, 0, 'C', true);
            $pdf->Cell(25, 8, 'Ciudad', 1, 0, 'C', true);
            $pdf->Cell(55, 8, 'Escuela', 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Mesa', 1, 1, 'C', true);

            $pdf->SetFont('helvetica', '', 7.5);
            $fill = false;

            if ($puntero->votantes->isEmpty()) {
                $pdf->Cell(180, 8, 'No existen votantes para este puntero', 1, 1, 'C');
            } else {
                // 🔁 Contador que empieza en 1 por cada puntero
                $numero = 1;

                foreach ($puntero->votantes as $votante) {
                    $pdf->SetFillColor($fill ? 240 : 255, $fill ? 240 : 255, $fill ? 240 : 255);

                    $cedula  = number_format($votante->cedula, 0, ',', '.');
                    $nombre  = $votante->nombre ?? '';
                    $ciudad  = $votante->ciudad ?? '';
                    $escuela = $votante->escuela ?? '';

                    $minHeight = 7;
                    $hNumero   = $pdf->getStringHeight(12, (string)$numero);
                    $hCedula   = $pdf->getStringHeight(22, $cedula);
                    $hNombre   = $pdf->getStringHeight(48, $nombre);
                    $hCiudad   = $pdf->getStringHeight(25, $ciudad);
                    $hEscuela  = $pdf->getStringHeight(55, $escuela);
                    $rowHeight = max($minHeight, $hNumero, $hCedula, $hNombre, $hCiudad, $hEscuela);

                    // ✅ N° (enumerado)
                    $pdf->MultiCell(12, $rowHeight, $numero, 1, 'C', true, 0);
                    $pdf->MultiCell(22, $rowHeight, $cedula, 1, 'C', true, 0);
                    $pdf->MultiCell(48, $rowHeight, $nombre, 1, 'L', true, 0);
                    $pdf->MultiCell(25, $rowHeight, $ciudad, 1, 'L', true, 0);
                    $pdf->MultiCell(55, $rowHeight, $escuela, 1, 'L', true, 0);
                    $pdf->MultiCell(15, $rowHeight, $votante->mesa, 1, 'C', true, 1);

                    $numero++;
                    $fill = !$fill;
                }
            }
        }

        $pdf->Output('reporte_votantes_por_dirigente.pdf', 'I');
        exit;
    }
    public function vehiculosPorEquipo($idEquipo)
    {
        // Caso especial: idEquipo = 0 para mostrar vehículos sin equipo
        if ($idEquipo == 0) {
            $vehiculos = Vehiculo::with(['punteros', 'equipo'])
                ->where('id_sistema', Auth::user()->sistema)
                ->whereNull('id_equipo')
                ->get();

            $tituloEquipo = "VEHÍCULOS SIN EQUIPO ASIGNADO";
            $equipoDescripcion = "SIN EQUIPO";
        } else {
            // Caso normal: mostrar vehículos de un equipo específico
            $equipo = Equipo::with(['vehiculos.punteros'])
                ->where('sist', Auth::user()->sistema)
                ->findOrFail($idEquipo);

            $vehiculos = $equipo->vehiculos;
            $tituloEquipo = "PLANILLA DE VEHÍCULOS Y PUNTEROS";
            $equipoDescripcion = "Equipo: " . $equipo->descripcion;
        }

        $pdf = new TCPDF('L', 'mm', 'A4', true, 'UTF-8', false); // LANDSCAPE

        $pdf->SetMargins(5, 12, 5);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->SetFont('helvetica', '', 7);

        $pdf->AddPage();

        /* ================= TÍTULO ================= */
        $pdf->SetFont('helvetica', 'B', 11);
        $pdf->Cell(0, 6, $tituloEquipo, 0, 1, 'C');

        $pdf->SetFont('helvetica', '', 9);
        $pdf->Cell(0, 5, $equipoDescripcion, 0, 1, 'C');

        $pdf->Ln(3);

        /* ================= ENCABEZADO TABLA ================= */
        $pdf->SetFont('helvetica', 'B', 7);
        $pdf->SetFillColor(200, 200, 200);

        $pdf->Cell(6, 8, '#', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Chofer', 1, 0, 'C', true);
        $pdf->Cell(22, 8, 'Cédula', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'Chapa', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'Tipo', 1, 0, 'C', true);
        $pdf->Cell(10, 8, 'Cap.', 1, 0, 'C', true);
        $pdf->Cell(30, 8, 'Teléfono', 1, 0, 'C', true);
        $pdf->Cell(18, 8, 'Monto', 1, 0, 'C', true);
        $pdf->Cell(12, 8, 'Pagos', 1, 0, 'C', true);
        $pdf->Cell(25, 8, 'Equipo', 1, 0, 'C', true);
        $pdf->Cell(55, 8, 'Punteros', 1, 1, 'C', true);

        /* ================= DATOS ================= */
        $pdf->SetFont('helvetica', '', 7);

        $i = 1;
        $totalMonto = 0;
        $totalPagos = 0;
        $totalVehiculos = 0;

        // Verificar si hay vehículos para mostrar
        if ($vehiculos->isEmpty()) {
            $pdf->Cell(0, 10, 'No hay vehículos para mostrar', 1, 1, 'C');
        } else {
            foreach ($vehiculos as $vehiculo) {

                $punterosTexto = $vehiculo->punteros
                    ->pluck('nombre')
                    ->implode("\n");

                $telefonos = collect([
                    $vehiculo->telefono1,
                    $vehiculo->telefono2,
                    $vehiculo->telefono3
                ])->filter()->implode(' - ');

                // Obtener descripción del equipo (puede ser null)
                $equipoDescripcion = $vehiculo->equipo ? $vehiculo->equipo->descripcion : 'SIN EQUIPO';

                // calcular altura dinámica
                $hPunteros = $pdf->getStringHeight(55, $punterosTexto);
                $rowHeight = max(7, $hPunteros);

                $pdf->MultiCell(6, $rowHeight, $i, 1, 'C', false, 0);
                $pdf->MultiCell(30, $rowHeight, $vehiculo->nombre, 1, 'L', false, 0);
                $pdf->MultiCell(22, $rowHeight, number_format($vehiculo->cedulachofer, 0, ',', '.'), 1, 'C', false, 0);
                $pdf->MultiCell(18, $rowHeight, $vehiculo->chapa, 1, 'C', false, 0);
                $pdf->MultiCell(18, $rowHeight, $vehiculo->tipovehiculo, 1, 'C', false, 0);
                $pdf->MultiCell(10, $rowHeight, $vehiculo->capacidad, 1, 'C', false, 0);
                $pdf->MultiCell(30, $rowHeight, $telefonos, 1, 'L', false, 0);
                $pdf->MultiCell(18, $rowHeight, number_format($vehiculo->montopagar, 0, ',', '.'), 1, 'R', false, 0);
                $pdf->MultiCell(12, $rowHeight, $vehiculo->cantidadpagos, 1, 'C', false, 0);
                $pdf->MultiCell(25, $rowHeight, $equipoDescripcion, 1, 'L', false, 0);
                $pdf->MultiCell(55, $rowHeight, $punterosTexto, 1, 'L', false, 1);

                $totalMonto += $vehiculo->montopagar;
                $totalPagos += $vehiculo->cantidadpagos;
                $totalVehiculos++;

                $i++;
            }
        }

        /* ================= TOTALES ================= */
        $pdf->Ln(2);
        $pdf->SetFont('helvetica', 'B', 8);

        $pdf->Cell(60, 7, 'TOTAL VEHÍCULOS:', 1, 0);
        $pdf->Cell(20, 7, $totalVehiculos, 1, 1, 'C');

        $pdf->Cell(60, 7, 'TOTAL MONTO:', 1, 0);
        $pdf->Cell(20, 7, number_format($totalMonto, 0, ',', '.'), 1, 1, 'R');

        $pdf->Output('planilla_vehiculos_punteros.pdf', 'I');
        exit;
    }
    public function totalesporSistema()
    {
        // Traemos todos los sistemas con sus equipos, punteros y dirigentes
        $userId = Auth::id();
        $userSistema = Auth::user()->sistema_id;

        $sistemas = Sistema::with('equipos.dirigentes', 'equipos.punteros.votantes')
            ->when(!in_array($userId, [1, 4]), function ($query) use ($userId, $userSistema) {
                $query->where(function ($q) use ($userId, $userSistema) {
                    $q->where('idusuario', $userId)
                        ->orWhere('id', $userSistema);
                });
            })
            ->get();

        $data = [];

        foreach ($sistemas as $sistema) {
            // Inicializamos los totales por sistema
            $totalDirigentes = 0;
            $totalPunteros = 0;
            $totalVotantes = 0;

            foreach ($sistema->equipos as $equipo) {
                // Contar votantes sumando los de cada puntero
                $votantes_count = $equipo->punteros->sum(fn($puntero) => $puntero->votantes->count());

                // Acumulamos totales por sistema
                $totalDirigentes += $equipo->dirigentes->count();
                $totalPunteros += $equipo->punteros->count();
                $totalVotantes += $votantes_count;

                // Guardamos los datos de cada equipo
                $data[] = [
                    'sistema' => $sistema->nombre,
                    'equipo' => $equipo->descripcion,
                    'dirigentes' => $equipo->dirigentes->count(),
                    'punteros' => $equipo->punteros->count(),
                    'votantes' => $votantes_count,
                    'es_total' => false, // Marcamos que no es fila de total
                ];
            }

            // Agregamos fila de total por sistema
            $data[] = [
                'sistema' => $sistema->nombre,
                'equipo' => 'TOTAL',
                'dirigentes' => $totalDirigentes,
                'punteros' => $totalPunteros,
                'votantes' => $totalVotantes,
                'es_total' => true, // Marcamos que es fila de total
            ];
        }

        return response()->json($data);
    }
    // En ReportesController.php, agrega este método
    public function cargaVotos()
    {
        $miembros = MiembroDeMesa::with('equipo')
            ->whereHas('equipo', function ($q) {
                $q->where('sist', Auth::user()->sistema);
            })
            ->orderBy('nombre')
            ->get();

        return view('reportes.cargavotos-loading', compact('miembros'));
    }

    public function getCargaVotosData(Request $request)
    {
        try {
            $miembroId = $request->input('miembro_id');
            $sistemaId = Auth::user()->sistema;

            $votosSub = DB::table('votos')
                ->select('cedula')
                ->whereIn('id', function ($query) use ($miembroId) {
                    $query->select(DB::raw('MIN(id)'))
                        ->from('votos')
                        ->when($miembroId, fn($q) => $q->where('idmiembrodemesa', $miembroId))
                        ->groupBy('cedula');
                });

            $punters = DB::table('puntero as p')
                ->join('dirigente as d', 'p.id_dirigente', '=', 'd.id')
                ->join('equipo as e', 'p.id_equipo', '=', 'e.id')
                ->leftJoin('votante as vt', 'vt.idpuntero', '=', 'p.id')
                ->leftJoinSub($votosSub, 'v', function ($join) {
                    $join->on(DB::raw('vt.cedula COLLATE utf8mb4_unicode_ci'), '=', DB::raw('v.cedula COLLATE utf8mb4_unicode_ci'));
                })
                ->where('e.sist', $sistemaId)
                ->select(
                    'd.nombre as dirigente_nombre',
                    'p.id as puntero_id',
                    'p.nombre as puntero_nombre',
                    DB::raw('COUNT(DISTINCT vt.id) as total_votantes'),
                    DB::raw('COUNT(DISTINCT CASE WHEN v.cedula IS NOT NULL THEN vt.id END) as votaron')
                )
                ->groupBy('d.nombre', 'p.id', 'p.nombre')
                ->orderBy('d.nombre')
                ->orderBy('p.nombre')
                ->get();

            $totalGeneral = DB::table('votos')
                ->when($miembroId, fn($q) => $q->where('idmiembrodemesa', $miembroId))
                ->count();

            return response()->json([
                'success' => true,
                'html' => view('reportes.cargavotos-content', compact(
                    'punters',
                    'totalGeneral'
                ))->render()
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getCargaVotosData: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getCargaVotosDetalle(Request $request)
    {
        try {
            $punteroId = $request->input('puntero_id');
            $tipo = $request->input('tipo'); // 'votaron' o 'no_votaron'
            $miembroId = $request->input('miembro_id');

            $votosSub = DB::table('votos')
                ->select('cedula')
                ->whereIn('id', function ($query) use ($miembroId) {
                    $query->select(DB::raw('MIN(id)'))
                        ->from('votos')
                        ->when($miembroId, fn($q) => $q->where('idmiembrodemesa', $miembroId))
                        ->groupBy('cedula');
                });

            $query = DB::table('votante as vt')
                ->join('puntero as p', 'vt.idpuntero', '=', 'p.id')
                ->leftJoinSub($votosSub, 'v', function ($join) {
                    $join->on(DB::raw('vt.cedula COLLATE utf8mb4_unicode_ci'), '=', DB::raw('v.cedula COLLATE utf8mb4_unicode_ci'));
                })
                ->where('vt.idpuntero', $punteroId)
                ->select('vt.cedula', 'vt.nombre', 'vt.mesa', 'vt.escuela', 'vt.ciudad');

            if ($tipo === 'votaron') {
                $query->whereNotNull('v.cedula');
            } else {
                $query->whereNull('v.cedula');
            }

            $votantes = $query->orderBy('vt.nombre')->get();

            $titulo = $tipo === 'votaron' ? 'Votaron' : 'No Votaron';

            $html = view('reportes.cargavotos-detalle', compact('votantes', 'titulo', 'punteroId'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getCargaVotosDetalle: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar detalle: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDetalleEquipo(Request $request)
    {
        try {
            $escuela = $request->input('escuela');
            $sistemaId = Auth::user()->sistema;

            if (!$escuela) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibió el nombre de la escuela'
                ], 400);
            }

            $votantes = Votante::where('escuela', $escuela)
                ->whereHas('puntero', function($q) use ($sistemaId) {
                    $q->whereHas('equipo', function($q2) use ($sistemaId) {
                        $q2->where('sist', $sistemaId);
                    });
                })
                ->with('puntero')
                ->get();

            $html = view('reportes.porlocal-detalle', compact('votantes', 'escuela'))->render();

            return response()->json([
                'success' => true,
                'html' => $html
            ]);
        } catch (\Exception $e) {
            Log::error('Error en getDetalleEquipo: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request' => $request->all()
            ]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar detalle: ' . $e->getMessage()
            ], 500);
        }
    }
    public function porlocal()
    {
        return view('reportes.porlocal-loading');
    }

    // Método que procesa los datos via AJAX
    public function getPorlocalData()
    {
        try {
            $sistemaId = Auth::user()->sistema;

            $escuelas = Votante::whereHas('puntero', function($q) use ($sistemaId) {
                    $q->whereHas('equipo', function($q2) use ($sistemaId) {
                        $q2->where('sist', $sistemaId);
                    });
                })
                ->whereNotNull('escuela')
                ->where('escuela', '!=', '')
                ->select(
                    'escuela',
                    DB::raw('COUNT(*) as total_votantes'),
                    DB::raw('SUM(CASE WHEN voto = 1 THEN 1 ELSE 0 END) as votaron'),
                    DB::raw('SUM(CASE WHEN voto = 0 THEN 1 ELSE 0 END) as no_votaron')
                )
                ->groupBy('escuela')
                ->orderBy('escuela')
                ->get();

            foreach ($escuelas as $escuela) {
                $escuela->votantes = Votante::where('escuela', $escuela->escuela)
                    ->whereHas('puntero', function($q) use ($sistemaId) {
                        $q->whereHas('equipo', function($q2) use ($sistemaId) {
                            $q2->where('sist', $sistemaId);
                        });
                    })
                    ->with('puntero')
                    ->get();
            }

            $totalEscuelas = $escuelas->count();
            $totalVotantes = $escuelas->sum('total_votantes');
            $totalVotos = $escuelas->sum('votaron');
            $totalSinVoto = $escuelas->sum('no_votaron');

            return response()->json([
                'success' => true,
                'html' => view('reportes.porlocal-content', compact(
                    'escuelas',
                    'totalEscuelas',
                    'totalVotantes',
                    'totalVotos',
                    'totalSinVoto'
                ))->render()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar el reporte: ' . $e->getMessage()
            ], 500);
        }
    }
}
