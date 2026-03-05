<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CiudadElectoral extends Model
{
    use HasFactory;

    protected $table = 'ciudades_electorales';

    protected $fillable = [
        'descripcion',
        'departamento'
    ];
}