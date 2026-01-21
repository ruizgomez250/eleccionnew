<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Socio extends Model
{
    use HasFactory;

    protected $table = 'socios';

    protected $primaryKey = 'id';

    public $timestamps = false; // la tabla no tiene created_at / updated_at

    protected $fillable = [
        'numero_socio',
        'nombre',
        'direccion',
        'telefono',
        'telefono1',
        'telefono2',
        'barrio',
        'mesa',
        'orden',
        'cedula',
        'situacion',
        'estado',
        'ciudad',
        'aporte',
        'solidaridad',
        'sede',
        'credito',
        'tarjeta',
    ];

    protected $casts = [
        'numero_socio' => 'integer',
        'mesa'         => 'integer',
        'orden'        => 'integer',
        'cedula'       => 'integer',
        'aporte'       => 'float',
        'solidaridad'  => 'float',
        'sede'         => 'float',
    ];
}
