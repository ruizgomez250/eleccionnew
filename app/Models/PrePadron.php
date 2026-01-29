<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PrePadron extends Model
{
    use HasFactory;
    protected $table = 'prepadron';

    protected $fillable = [
        'nº',
        'cedula', 
        'nombre', 
        'apellido', 
        'sexo', 
        'fecha_nacimiento', 
        'fecha_inscripcion', 
        'tipo', 'direccion', 
        'voto_plra', 
        'voto_anr', 
        'voto_generales', 
        'afiliaciones', 
        'afiliado_plra_2025', 
        'departamento_nombre', 
        'distrito_nombre', 
        'zona_nombre', 
        'comite_nombre', 
        'local_generales', 
        'local_interna', 
        'archivo_origen',
    ];
    
}
