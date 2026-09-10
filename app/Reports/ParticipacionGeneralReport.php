<?php

namespace App\Reports;

use Illuminate\Support\Facades\DB;

class ParticipacionGeneralReport
{
    public function generate(int $sistemaId): array
    {
        // Sólo se devuelven agregados; las cédulas se utilizan dentro de SQL.
        $mysql = DB::connection()->getDriverName() === 'mysql';
        $cedula = $mysql ? 'vt.cedula COLLATE utf8mb4_unicode_ci' : 'vt.cedula';
        // La tabla votos importada utiliza general_ci. Convertir sólo el valor
        // buscado permite aprovechar su índice sin materializar todos los votos.
        $buscada = $mysql ? 'vt.cedula COLLATE utf8mb4_general_ci' : 'vt.cedula';
        $base = DB::table('puntero as p')
            ->join('dirigente as d', 'd.id', '=', 'p.id_dirigente')
            ->join('equipo as e', 'e.id', '=', 'd.id_equipo')
            ->where('e.sist', $sistemaId)
            ->leftJoin('votante as vt', function ($join) {
                $join->on('vt.idpuntero', '=', 'p.id')->where('vt.cedula', '<>', '');
            });

        $counts = "COUNT(DISTINCT $cedula) as total, "
            . "COUNT(DISTINCT CASE WHEN EXISTS (SELECT 1 FROM votos v WHERE v.cedula = $buscada) THEN $cedula END) as registrados";

        $resumen = (clone $base)->selectRaw($counts)->first();
        $dirigentes = (clone $base)->select('d.id', 'd.nombre')
            ->selectRaw($counts)->groupBy('d.id', 'd.nombre')->orderBy('d.nombre')->get();
        $punteros = (clone $base)->select('p.id', 'p.nombre', 'd.nombre as dirigente')
            ->selectRaw($counts)->groupBy('p.id', 'p.nombre', 'd.id', 'd.nombre')
            ->orderBy('p.nombre')->get();

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
