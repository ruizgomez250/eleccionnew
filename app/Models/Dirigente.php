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
    // En el modelo Dirigente.php
    public function sistema()
    {
        return $this->hasOneThrough(
            Sistema::class,
            Equipo::class,
            'id',      // Foreign key on equipos table (local key of equipos)
            'id',      // Foreign key on sistemas table (local key of sistemas)
            'id_equipo', // Local key on dirigentes table
            'sist'     // Local key on equipos table
        );
    }
}
