<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
        return self::where('idpuntero', $idpuntero)
            ->orderBy('id', 'desc')
            ->get([
                'id',
                'cedula',
                'nombre',
                'tipo_votante',
                'mesa'
            ]);
    }
}
