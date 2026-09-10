<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

$db = DB::connection()->getDatabaseName();

// ¿socios tiene escuela/mesa que conecte con mesas/equipo?
echo "=== socios ejemplo con mesa<>0 ===\n";
$rows = DB::table('socios')->where('mesa', '>', 0)->take(10)->get(['id','cedula','nombre','ciudad','mesa','numero_socio','estado']);
foreach ($rows as $r) echo "  " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
echo "socios con mesa>0: " . DB::table('socios')->where('mesa','>',0)->count() . "\n";
echo "socios total: " . DB::table('socios')->count() . "\n";

echo "\n=== puntero: relación con equipo ===\n";
$p = DB::table('puntero as p')->leftJoin('equipo as e','p.id_equipo','=','e.id')->leftJoin('dirigente as d','p.id_dirigente','=','d.id')
    ->select('p.id','p.nombre','p.cedula','p.id_equipo','e.descripcion as equipo_desc','e.sist','e.colegio','d.nombre as dirigente')
    ->take(8)->get();
foreach ($p as $r) echo "  " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
echo "punteros sin equipo: " . DB::table('puntero')->whereNull('id_equipo')->count() . " / con equipo: " . DB::table('puntero')->whereNotNull('id_equipo')->count() . "\n";

echo "\n=== mesas/equipo ===\n";
$m = DB::table('mesas as m')->leftJoin('equipo as e','m.equipo_id','=','e.id')->select('m.id','m.numero_mesa','m.codigo_mesa','m.equipo_id','e.colegio','e.descripcion','e.sist')->take(8)->get();
foreach ($m as $r) echo "  " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
echo "mesas total: " . DB::table('mesas')->count() . "\n";

echo "\n=== dirigente ===\n";
$d = DB::table('dirigente')->select('id','nombre','id_equipo')->take(8)->get();
foreach ($d as $r) echo "  " . json_encode($r, JSON_UNESCAPED_UNICODE) . "\n";
echo "dirigente total: " . DB::table('dirigente')->count() . "\n";

echo "\n=== sistemas tabla ===\n";
echo "columns: \n";
foreach (DB::select("SHOW COLUMNS FROM sistemas") as $c) echo "  {$c->Field}: {$c->Type}\n";

echo "\n=== existe 'votante' como vista? ===\n";
print_r(DB::select("SELECT TABLE_NAME, TABLE_TYPE FROM information_schema.TABLES WHERE TABLE_SCHEMA = ? AND TABLE_NAME IN ('votante','votos','votos_mesa','socios','puntero_votante')", [$db]));