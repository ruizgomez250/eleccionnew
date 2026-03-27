<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sistemaspadre extends Model
{
    use HasFactory;

    protected $table = 'sistemaspadres';

    protected $fillable = [
        'idsistema',
        'idsistemapadre'
    ];

    protected $casts = [
        'idsistema' => 'integer',
        'idsistemapadre' => 'integer',
    ];

    // Relación: un sistema pertenece a un sistema padre
    public function sistemaPadre()
    {
        return $this->belongsTo(Sistemaspadre::class, 'idsistemapadre');
    }

    // Relación: un sistema padre tiene muchos sistemas hijos
    public function sistemasHijos()
    {
        return $this->hasMany(Sistemaspadre::class, 'idsistemapadre');
    }
    
    // Si tienes una tabla sistemas, podrías agregar estas relaciones
    // public function sistema()
    // {
    //     return $this->belongsTo(Sistema::class, 'idsistema');
    // }
}