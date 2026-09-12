<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Votante extends Model
{
    use HasFactory;

    protected $table = 'votante';

    protected $fillable = [
        'cedula',
        'tipo_votante',
        'voto',
        'idpuntero',
        'idusuario',
        'nombre',
        'direccion',
        'mesa',
        'orden',
        'partido',
        'escuela',
        'ciudad',
        'departamento',
        'observacion',
    ];

    /**
     * RELACIONES
     */

    // Votante pertenece a un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idequipo');
    }

    // Votante pertenece a un puntero
    public function puntero()
    {
        return $this->belongsTo(Puntero::class, 'idpuntero');
    }

    
    /**
     * Obtener votantes por puntero
     */
    public static function porPuntero($idpuntero)
    {
        $votantes = self::where('idpuntero', $idpuntero)
            ->orderBy('id', 'desc')
            ->get([
                'cedula',
        'tipo_votante',
        'voto',
        'idpuntero',
        'idusuario',
        'nombre',
        'direccion',
        'mesa',
        'orden',
        'partido',
        'escuela',
        'ciudad',
        'id',
        'departamento',
        'observacion'
            ]);

        $cedulasVotantes = $votantes->pluck('cedula')
            ->filter()
            ->unique()
            ->values();

        $cedulasVotos = [];
        if ($cedulasVotantes->isNotEmpty()) {
            $cedulasVotos = DB::table('votos')
                ->whereIn('cedula', $cedulasVotantes->all())
                ->distinct()
                ->pluck('cedula')
                ->map(function ($cedula) {
                    return (string) $cedula;
                })
                ->flip()
                ->all();
        }

        foreach ($votantes as $votante) {
            $votante->ya_voto = isset($cedulasVotos[(string) $votante->cedula]);
        }

        return $votantes;
    }
    
    
}
