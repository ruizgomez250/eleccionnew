<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require __DIR__ . '/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;

function norm($s) {
    $texto = mb_strtoupper(trim((string)$s), 'UTF-8');
    return strtr($texto, ['Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ü'=>'U','Ñ'=>'N']);
}

// Simulación del cálculo para candidato id=5 (Francisca Franco, concejal)
$candidatoId = 5;

$votosCedulas = DB::table('votos as v')->select('v.cedula')->where('v.cedula','<>','')->distinct()->pluck('cedula')->flip();
echo "cedulas en votos: " . count($votosCedulas) . "\n";

$colegioToEquipo = [];
DB::table('equipo as e')->join('mesas as m','m.equipo_id','=','e.id')->distinct()->select('e.id','e.colegio')->get()
    ->each(function ($row) use (&$colegioToEquipo) { $colegioToEquipo[norm($row->colegio)] = (int)$row->id; });
echo "colegios->equipo con mesas: " . count($colegioToEquipo) . "\n";

$mesaByEquipoNum = [];
DB::table('mesas')->get(['id','equipo_id','numero_mesa'])->each(function ($row) use (&$mesaByEquipoNum) {
    $mesaByEquipoNum[(int)$row->equipo_id][(string)$row->numero_mesa] = (int)$row->id;
});

$votosCand = DB::table('votos_mesa')->where('candidato_id',$candidatoId)->where('cargo','Concejal Municipal')
    ->select('mesa_id', DB::raw('SUM(cantidad_votos) total'))->groupBy('mesa_id')->pluck('total','mesa_id');
echo "mesas con votos del candidato $candidatoId: " . count($votosCand) . ", total votos: " . array_sum($votosCand->all()) . "\n";

$votantes = DB::table('votante as vt')->join('puntero as p','vt.idpuntero','=','p.id')
    ->leftJoin('dirigente as d','p.id_dirigente','=','d.id')
    ->where('vt.cedula','<>','')
    ->select('p.id as puntero_id','p.nombre as puntero_nombre','d.nombre as dirigente_nombre','vt.cedula as cedula','vt.escuela','vt.mesa')
    ->get()->groupBy('puntero_id');
echo "punteros con votantes: " . count($votantes) . "\n";

$punteros = [];
foreach ($votantes as $pid => $filas) {
    $total=0; $seFueron=0; $mesas=[];
    foreach ($filas as $f){
        $total++;
        if (isset($votosCedulas[$f->cedula])) $seFueron++;
        $eid = $colegioToEquipo[norm($f->escuela)] ?? null;
        $nm = trim((string)$f->mesa);
        if ($eid!==null && $nm!=='' && isset($mesaByEquipoNum[$eid][$nm])) $mesas[$mesaByEquipoNum[$eid][$nm]]=true;
    }
    $votosReales=0;
    foreach (array_keys($mesas) as $mId) $votosReales += (int)($votosCand[$mId] ?? 0);
    $debio = $seFueron;
    $efect = $debio>0 ? round(100*$votosReales/$debio,1) : 0;
    $punteros[]=['nombre'=>$filas[0]->puntero_nombre,'dirigente'=>$filas[0]->dirigente_nombre ?? '','anotados'=>$total,'se_fueron'=>$seFueron,'mesas'=>count($mesas),'debio_tener'=>$debio,'votos_reales'=>$votosReales,'efectividad'=>$efect];
}
usort($punteros, fn($a,$b)=>$b['efectividad']<=>$a['efectividad']);
echo "\nTop 10 punteros por efectividad (candidato $candidatoId):\n";
foreach (array_slice($punteros,0,10) as $p) {
    echo "  {$p['nombre']} (dir {$p['dirigente']}) anot={$p['anotados']} seFueron={$p['se_fueron']} mesas={$p['mesas']} debio={$p['debio_tener']} reales={$p['votos_reales']} ef={$p['efectividad']}%\n";
}
$sumDebio = array_sum(array_column($punteros,'debio_tener'));
$sumReal = array_sum(array_column($punteros,'votos_reales'));
echo "\nResumen: punteros=" . count($punteros) . " anotados=" . array_sum(array_column($punteros,'anotados'))
    . " se_fueron=" . array_sum(array_column($punteros,'se_fueron'))
    . " debio=$sumDebio reales=$sumReal efectividad_gral=" . ($sumDebio>0?round(100*$sumReal/$sumDebio,1):0) . "%\n";