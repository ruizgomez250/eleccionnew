<?php

namespace App\Http\Controllers;

use App\Reports\ParticipacionGeneralReport;
use App\Reports\ParticipacionGeneralPdf;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ParticipacionGeneralController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'permission:Reportes']);
    }

    public function index(Request $request)
    {
        $puedeSeleccionarCandidato = in_array((int) $request->user()->id, [1, 2, 3, 4], true);
        $candidatos = $puedeSeleccionarCandidato
            ? Sistema::select('id', 'nombre', 'tipo')
                ->whereRaw('LOWER(tipo) IN (?, ?)', ['intendente', 'concejal'])
                ->orderBy('tipo')->orderBy('nombre')->get()
            : collect();

        return view('reportes.participacion-general', compact('puedeSeleccionarCandidato', 'candidatos'));
    }

    public function data(Request $request, ParticipacionGeneralReport $report)
    {
        $sistemaId = $this->sistemaId($request);

        try {
            $data = Cache::remember('participacion-general:v2:'.$sistemaId, 60,
                fn () => $report->generate($sistemaId));
        } catch (\Throwable $e) {
            return $this->errorResponse($e, $sistemaId);
        }

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }

    public function pdf(Request $request, ParticipacionGeneralReport $report, ParticipacionGeneralPdf $pdf)
    {
        $sistemaId = $this->sistemaId($request);
        try {
            $data = Cache::remember('participacion-general:v2:'.$sistemaId, 60,
                fn () => $report->generate($sistemaId));
            $nombre = Sistema::find($sistemaId)?->nombre ?? 'Sistema '.$sistemaId;

            return response($pdf->render($data, $nombre), 200, [
                'Content-Type' => 'application/pdf',
                'Content-Disposition' => 'attachment; filename="participacion-general-'.$sistemaId.'.pdf"',
                'Cache-Control' => 'private, no-store',
            ]);
        } catch (\Throwable $e) {
            return $this->errorResponse($e, $sistemaId);
        }
    }

    private function sistemaId(Request $request): int
    {
        $sistemaId = (int) $request->user()->sistema;
        if (in_array((int) $request->user()->id, [1, 2, 3, 4], true) && $request->filled('candidato_id')) {
            $request->validate(['candidato_id' => ['required', 'integer']]);
            $sistemaId = (int) Sistema::whereRaw('LOWER(tipo) IN (?, ?)', ['intendente', 'concejal'])
                ->findOrFail($request->input('candidato_id'))->id;
        }
        abort_unless($sistemaId > 0, 403, 'No tiene un sistema asignado.');
        return $sistemaId;
    }

    private function errorResponse(\Throwable $e, int $sistemaId)
    {
        $reference = (string) Str::uuid();
        Log::error('Error en participacion-general', [
            'referencia' => $reference, 'sistema_id' => $sistemaId, 'exception' => $e,
        ]);

        return response()->json([
            'message' => 'No se pudo generar el reporte. Referencia: '.$reference,
        ], 500)->header('Cache-Control', 'private, no-store');
    }
}
