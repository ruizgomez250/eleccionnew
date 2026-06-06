<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Equipo extends Model
{
    use HasFactory;

    protected $table = 'equipo';

    protected $fillable = [
        'descripcion',
        'sist',
        'colegio',
        'ciudad',
    ];

    /**
     * RELACIONES (opcionales pero recomendadas)
     */

    // Un equipo tiene muchos dirigentes
    public function dirigentes()
    {
        return $this->hasMany(Dirigente::class, 'id_equipo');
    }
    // Un equipo tiene muchos punteros
    public function punteros()
    {
        return $this->hasMany(Puntero::class, 'id_equipo');
    }
    // Un equipo tiene muchos votantes
    public function votantes()
    {
        return $this->hasMany(Votante::class, 'idequipo');
    }
    // Un equipo tiene muchos dirigentes
    public function vehiculos()
    {
        return $this->hasMany(Vehiculo::class, 'id_equipo');
    }
    // Votantes a través de punteros
    public function votantesPorPuntero()
    {
        return $this->hasManyThrough(
            Votante::class,   // Modelo final
            Puntero::class,   // Modelo intermedio
            'id_equipo',      // FK de puntero hacia equipo
            'idpuntero',      // FK de votante hacia puntero
            'id',             // PK de equipo
            'id'              // PK de puntero
        );
    }
    public function miembrosDeMesa()
    {
        return $this->hasMany(MiembroDeMesa::class, 'idequipo');
    }

    public function mesas()
    {
        return $this->hasMany(Mesa::class, 'equipo_id');
    }
}
