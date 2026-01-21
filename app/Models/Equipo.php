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
}
