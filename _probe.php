<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

try { DB::statement("SET SESSION max_statement_time=60"); } catch (Throwable $e) {}

function timed($label, $fn) {
    try {
        $t = microtime(true);
        $r = $fn();
        echo "$label: t=" . round(microtime(true)-$t,2) . "s\n";
        return $r;
    } catch (Throwable $e) {
        echo "$label: FALLÓ ({$e->getMessage()})\n";
        return null;
    }
}

$idx = DB::select("SHOW INDEX FROM votos");
$hasIdm = array_filter($idx, fn($i) => $i->Key_name === 'idx_votos_idmiembrodemesa' || ($i->Column_name === 'idmiembrodemesa' && $i->Key_name === 'idmiembrodemesa'));
echo "idx idmiembrodemesa existente: " . (count($hasIdm) ? "SI" : "NO") . "\n";
if (!count($hasIdm)) {
    timed("CREATE INDEX idx_votos_idmiembrodemesa", fn() => DB::statement('CREATE INDEX idx_votos_idmiembrodemesa ON votos (idmiembrodemesa)'));
}

function puntersQ($miembroId, $sistemaFiltro) {
    return DB::table('puntero as p')
        ->join('dirigente as d', 'p.id_dirigente', '=', 'd.id')
        ->join('equipo as e', 'p.id_equipo', '=', 'e.id')
        ->leftJoin('votante as vt', 'vt.idpuntero', '=', 'p.id')
        ->leftJoin('votos as v', function ($join) use ($miembroId, $sistemaFiltro) {
            $join->on(DB::raw('v.cedula'), '=', DB::raw('vt.cedula COLLATE utf8mb4_general_ci'));
            if ($miembroId) $join->where('v.idmiembrodemesa', $miembroId);
            elseif ($sistemaFiltro) $join->whereIn('v.idmiembrodemesa', function ($q2) use ($sistemaFiltro) {
                $q2->select('m.id')->from('miembros_de_mesa as m')
                    ->join('equipo as e', 'e.id', '=', 'm.idequipo')
                    ->where('e.sist', $sistemaFiltro);
            });
        })
        ->when($sistemaFiltro, fn($q) => $q->where('e.sist', $sistemaFiltro))
        ->select('d.nombre as dirigente_nombre','p.id as puntero_id','p.nombre as puntero_nombre',
            DB::raw('COUNT(DISTINCT vt.id) as total_votantes'),
            DB::raw('COUNT(DISTINCT CASE WHEN v.id IS NOT NULL THEN vt.id END) as votaron'))
        ->groupBy('d.nombre','p.id','p.nombre')->orderBy('d.nombre')->orderBy('p.nombre')->get();
}

echo "\n== punters SIN filtro (superadmin) ==\n";
$r = timed("punters_sin_filtro", fn() => puntersQ(null, null));
if ($r) echo " filas=" . $r->count() . "\n";

echo "\n== punters con sistema (sist=7) ==\n";
$r = timed("punters_sist7", fn() => puntersQ(null, 7));
if ($r) { echo " filas=" . $r->count() . " suma_votaron=" . $r->sum('votaron') . "\n"; }

echo "\n== punters con miembro ==\n";
$m = DB::selectOne('SELECT id FROM miembros_de_mesa LIMIT 1');
if ($m) {
    $r = timed("punters_miembro", fn() => puntersQ($m->id, null));
    if ($r) echo " filas=" . $r->count() . "\n";
}

echo "\n== votosPorSistema (con indice) ==\n";
timed("votosPorSistema", function() {
    return DB::table('votos')
        ->join('miembros_de_mesa as m','m.id','=','votos.idmiembrodemesa')
        ->join('equipo as e','e.id','=','m.idequipo')
        ->select('e.sist', DB::raw('COUNT(DISTINCT votos.cedula) as t'))
        ->groupBy('e.sist')->pluck('t','e.sist');
});