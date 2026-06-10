<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PadronCoopController extends Controller
{
    public function index()
    {
        return view('padron-coop');
    }

    public function search(Request $request)
    {
        $query = $request->get('q');

        $results = collect();

        if ($query) {
            $results = DB::select("
                SELECT *
                FROM `padroncoopluque9062026`
                WHERE `CI NRO` LIKE ?
                   OR `SOCIO NRO` LIKE ?
                   OR `NOMBRE Y APELLIDO` LIKE ?
                ORDER BY `NRO`
                LIMIT 50
            ", ["%{$query}%", "%{$query}%", "%{$query}%"]);
        }

        if ($request->ajax()) {
            return response()->json([
                'results' => $results,
                'count' => count($results),
            ]);
        }

        return view('padron-coop', compact('results', 'query'));
    }
}
