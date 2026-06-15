<?php

namespace App\Http\Controllers;

use App\Models\CargaEfectividad;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class EfectividadController extends Controller
{
    public function index()
    {
        $mesas = CargaEfectividad::select('id', 'mesa')->orderBy('mesa')->get();
        return view('efectividad.index', compact('mesas'));
    }

    public function resumen()
    {
        $totals = CargaEfectividad::select(
            DB::raw('SUM(intendente) as total_intendente'),
            DB::raw('SUM(c1) as c1'), DB::raw('SUM(c2) as c2'), DB::raw('SUM(c3) as c3'),
            DB::raw('SUM(c4) as c4'), DB::raw('SUM(c5) as c5'), DB::raw('SUM(c6) as c6'),
            DB::raw('SUM(c7) as c7'), DB::raw('SUM(c8) as c8'), DB::raw('SUM(c9) as c9'),
            DB::raw('SUM(c10) as c10'), DB::raw('SUM(c11) as c11'), DB::raw('SUM(c12) as c12')
        )->first();

        $totalIntendente = (int) $totals->total_intendente;

        $result = [];
        for ($i = 1; $i <= 12; $i++) {
            $col = "c{$i}";
            $votosConcejal = (int) $totals->$col;
            $efectividad = $totalIntendente > 0 ? round($votosConcejal / $totalIntendente, 2) : 0;
            $votosPerdidos = $totalIntendente - $votosConcejal;
            $color = $efectividad < 0.6 ? 'danger' : ($efectividad <= 0.8 ? 'warning' : 'success');

            $result[] = [
                'posicion' => $i,
                'total_intendente' => $totalIntendente,
                'total_concejal' => $votosConcejal,
                'efectividad' => $efectividad,
                'votos_perdidos' => max(0, $votosPerdidos),
                'color' => $color,
            ];
        }

        return response()->json($result);
    }

    public function mesa($id)
    {
        $row = CargaEfectividad::findOrFail($id);
        $intendente = (int) $row->intendente;

        $concejales = [];
        for ($i = 1; $i <= 12; $i++) {
            $votos = (int) $row->{"c{$i}"};
            $comite = (int) $row->{"com{$i}"};
            $juventud = (int) $row->{"juv{$i}"};
            $efectividad = $intendente > 0 ? round($votos / $intendente, 2) : 0;
            $votosPerdidos = $intendente - $votos;
            $efectividadComite = $votos > 0 ? round($comite / $votos, 2) : 0;
            $efectividadJuventud = $votos > 0 ? round($juventud / $votos, 2) : 0;

            $concejales[] = [
                'posicion' => $i,
                'votos' => $votos,
                'efectividad' => $efectividad,
                'votos_perdidos' => max(0, $votosPerdidos),
                'efectividad_comite' => $efectividadComite,
                'efectividad_juventud' => $efectividadJuventud,
                'votos_comite' => $comite,
                'votos_juventud' => $juventud,
                'color_intendente' => $efectividad < 0.6 ? 'danger' : ($efectividad <= 0.8 ? 'warning' : 'success'),
                'color_comite' => $efectividadComite < 0.6 ? 'danger' : ($efectividadComite <= 0.8 ? 'warning' : 'success'),
                'color_juventud' => $efectividadJuventud < 0.6 ? 'danger' : ($efectividadJuventud <= 0.8 ? 'warning' : 'success'),
            ];
        }

        // Generate alerts
        $alertas = [];
        foreach ($concejales as $c) {
            if ($c['efectividad'] < 0.6) {
                $alertas[] = "⚠️ Posición {$c['posicion']}: efectividad {$c['efectividad']} en {$row->mesa} ({$c['votos_perdidos']} votos perdidos)";
            }
            if ($c['efectividad_comite'] < 0.6 && $c['votos'] > 0) {
                $alertas[] = "⚠️ Posición {$c['posicion']}: comité solo arrastra {$c['efectividad_comite']} de los votos del concejal";
            }
            if ($c['efectividad_juventud'] < 0.6 && $c['votos'] > 0) {
                $alertas[] = "⚠️ Posición {$c['posicion']}: juventud solo arrastra {$c['efectividad_juventud']} de los votos del concejal";
            }
        }

        return response()->json([
            'id' => $row->id,
            'mesa' => $row->mesa,
            'votos_intendente' => $intendente,
            'concejales' => $concejales,
            'alertas' => $alertas,
        ]);
    }

    public function listarMesas()
    {
        return response()->json(
            CargaEfectividad::select('id', 'mesa')->orderBy('mesa')->get()
        );
    }

    public function cargar(Request $request)
    {
        $request->validate([
            'archivo' => 'required|file|mimes:csv,txt|max:5120',
        ]);

        $file = $request->file('archivo');
        $handle = fopen($file->getPathname(), 'r');
        $header = fgetcsv($handle, 0, ',');

        $expectedHeader = [
            'mesa', 'intendente',
            'c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12',
            'com1','com2','com3','com4','com5','com6','com7','com8','com9','com10','com11','com12',
            'juv1','juv2','juv3','juv4','juv5','juv6','juv7','juv8','juv9','juv10','juv11','juv12',
        ];

        if (!$header || count($header) !== count($expectedHeader)) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Formato de CSV inválido. Debe tener ' . count($expectedHeader) . ' columnas.',
            ], 422);
        }

        $normalized = array_map(function ($h) {
            return trim(mb_strtolower($h));
        }, $header);

        if ($normalized !== $expectedHeader) {
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Las columnas no coinciden con el formato esperado.',
            ], 422);
        }

        $imported = 0;
        $errors = [];

        DB::beginTransaction();
        try {
            CargaEfectividad::query()->delete();

            while (($row = fgetcsv($handle, 0, ',')) !== false) {
                $data = array_combine($expectedHeader, $row);
                $data['mesa'] = trim($data['mesa']);

                foreach ($data as $key => $value) {
                    if ($key !== 'mesa') {
                        $data[$key] = (int) preg_replace('/[^0-9]/', '', $value);
                    }
                }

                CargaEfectividad::create($data);
                $imported++;
            }

            DB::commit();
            fclose($handle);

            return response()->json([
                'success' => true,
                'message' => "Se importaron {$imported} mesas correctamente.",
                'imported' => $imported,
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return response()->json([
                'success' => false,
                'message' => 'Error al importar: ' . $e->getMessage(),
            ], 500);
        }
    }
}
