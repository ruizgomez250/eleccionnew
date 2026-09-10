<?php

namespace App\Http\Controllers;

use App\Reports\ParticipacionGeneralReport;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

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

        $data = Cache::remember('participacion-general:v1:'.$sistemaId, 60,
            fn () => $report->generate($sistemaId));

        return response()->json($data)->header('Cache-Control', 'private, no-store');
    }
}
