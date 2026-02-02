<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Vehiculo extends Model
{
    use HasFactory;

    protected $table = 'vehiculo';

    protected $fillable = [
        'cedulachofer',
        'nombre',
        'chapa',
        'capacidad',
        'tipovehiculo',
        'telefono1',
        'telefono2',
        'telefono3',
        'montopagar',
        'cantidadpagos',
        'numero_auto',
        'id_equipo',
    ];
    public function punteros()
    {
        return $this->belongsToMany(Puntero::class, 'puntero_vehiculo', 'vehiculo_id', 'puntero_id');
    }
    // Dirigente pertenece a un equipo
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'id_equipo');
    }
}
