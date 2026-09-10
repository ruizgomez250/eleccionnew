<?php

namespace App\Http\Controllers;

use App\Reports\ParticipacionGeneralReport;
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

    public function index()
    {
        return view('reportes.participacion-general');
    }

    public function data(Request $request, ParticipacionGeneralReport $report)
    {
        $sistemaId = (int) $request->user()->sistema;
        abort_unless($sistemaId > 0, 403, 'No tiene un sistema asignado.');

        try {
            $data = Cache::remember('participacion-general:v2:'.$sistemaId, 60,
                fn () => $report->generate($sistemaId));
        } catch (\Throwable $e) {
            $reference = (string) Str::uuid();
            Log::error('Error en participacion-general', [
                'referencia' => $reference, 'sistema_id' => $sistemaId, 'exception' => $e,
            ]);

            return response()->json([
                'message' => 'No se pudo generar el reporte. Referencia: '.$reference,
            ], 500)->header('Cache-Control', 'private, no-store');
        }

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }
}
