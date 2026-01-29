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
    ];
    public function punteros()
    {
        return $this->belongsToMany(Puntero::class, 'puntero_vehiculo', 'vehiculo_id', 'puntero_id');
    }
}
