<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Puntero extends Model
{
    use HasFactory;

    protected $table = 'puntero';

    protected $fillable = [
        'cedula',
        'nombre',
        'telefono',
        'telefono1',
        'telefono2',
        'id_dirigente',
        'barrio',
        'idusuario',
        'id_equipo',
    ];

    /**
     * RELACIONES
     */

    // Puntero pertenece a un dirigente
    public function dirigente()
    {
        return $this->belongsTo(Dirigente::class, 'id_dirigente');
    }
    // Puntero pertenece a un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }
    // Puntero tiene muchos votantes
    public function votantes()
    {
        return $this->hasMany(Votante::class, 'idpuntero');
    }
    public function vehiculos()
    {
        return $this->belongsToMany(Vehiculo::class, 'puntero_vehiculo', 'puntero_id', 'vehiculo_id')
            ->withTimestamps()
            ->withPivot('fecha_asignacion');
    }
}
