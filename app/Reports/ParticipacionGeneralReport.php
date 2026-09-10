<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;

class ParticipacionGeneralReport
{
    public function generate(int $sistemaId): array
    {
        // Sólo se devuelven agregados; las cédulas se utilizan dentro de SQL.
        $mysql = DB::connection()->getDriverName() === 'mysql';
        $cedula = 'vt.cedula';
        $buscada = $cedula;
        if ($mysql) {
            // El hosting puede usar latin1, utf8 o utf8mb4. No convertir la
            // columna indexada de votos: adaptar únicamente el valor buscado.
            $table = DB::connection()->getQueryGrammar()->wrapTable('votos');
            $column = DB::selectOne("SHOW FULL COLUMNS FROM $table LIKE 'cedula'");
            $collation = $column->Collation ?? null;
            if ($collation && preg_match('/^[a-zA-Z0-9_]+$/D', $collation)) {
                $charset = explode('_', $collation)[0];
                $buscada = "CONVERT(vt.cedula USING $charset) COLLATE $collation";
            }
        }
        $base = DB::table('puntero as p')
            ->join('dirigente as d', 'd.id', '=', 'p.id_dirigente')
            ->join('equipo as e', 'e.id', '=', 'd.id_equipo')
            ->where('e.sist', $sistemaId)
            ->leftJoin('votante as vt', function ($join) {
                $join->on('vt.idpuntero', '=', 'p.id')->where('vt.cedula', '<>', '');
            });

        $counts = "COUNT(DISTINCT $cedula) as total, "
            . "COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM votos v WHERE v.cedula = $buscada) THEN $cedula END) as registrados";

        if ($mysql) {
            // Un solo recorrido produce grupos, subtotales y total general.
            // COUNT DISTINCT se calcula en cada nivel, sin sumar duplicados.
            $rows = $base->selectRaw('d.id as dirigente_id, p.id as puntero_id, '
                .'MAX(d.nombre) as dirigente, MAX(p.nombre) as nombre, '.$counts)
                ->groupByRaw('d.id, p.id WITH ROLLUP')->get();
            $resumen = $rows->first(fn ($row) => $row->dirigente_id === null)
                ?? (object) ['total' => 0, 'registrados' => 0];
            $dirigentes = $rows->filter(fn ($row) => $row->dirigente_id !== null && $row->puntero_id === null)
                ->map(fn ($row) => (object) ['nombre' => $row->dirigente, 'total' => $row->total, 'registrados' => $row->registrados])
                ->sortBy('nombre')->values();
            $punteros = $rows->filter(fn ($row) => $row->puntero_id !== null)->sortBy('nombre')->values();
        } else {
            $resumen = (clone $base)->selectRaw($counts)->first();
            $dirigentes = (clone $base)->select('d.id', 'd.nombre')
                ->selectRaw($counts)->groupBy('d.id', 'd.nombre')->orderBy('d.nombre')->get();
            $punteros = (clone $base)->select('p.id', 'p.nombre', 'd.nombre as dirigente')
                ->selectRaw($counts)->groupBy('p.id', 'p.nombre', 'd.id', 'd.nombre')
                ->orderBy('p.nombre')->get();
        }

        $metrics = static function ($row): array {
            $total = (int) $row->total;
            $registrados = (int) $row->registrados;

            return [
                'total' => $total,
                'registrados' => $registrados,
                'sin_registro' => $total - $registrados,
                'porcentaje' => $total ? round(100 * $registrados / $total, 1) : 0,
            ];
        };

        return [
            'resumen' => $metrics($resumen),
            'dirigentes' => $dirigentes->map(fn ($row) => [
                'nombre' => $row->nombre,
            ] + $metrics($row))->all(),
            'punteros' => $punteros->map(fn ($row) => [
                'nombre' => $row->nombre, 'dirigente' => $row->dirigente,
            ] + $metrics($row))->all(),
            'generado_en' => now()->toIso8601String(),
        ];
    }
}
