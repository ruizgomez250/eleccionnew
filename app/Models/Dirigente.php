<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dirigente extends Model
{
    use HasFactory;

    protected $table = 'dirigente';

    protected $fillable = [
        'cedula',
        'nombre',
        'telefono',
        'telefono1',
        'telefono2',
        'id_equipo',
        'barrio',
        'idusuario',
    ];

    /**
     * RELACIONES
     */

    // Dirigente pertenece a un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }

    // Dirigente tiene muchos punteros
    public function punteros()
    {
        return $this->hasMany(Puntero::class, 'id_dirigente');
    }

    
}
