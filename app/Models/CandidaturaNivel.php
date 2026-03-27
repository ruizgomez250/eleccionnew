<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
// Eliminar esta línea: use Illuminate\Database\Eloquent\SoftDeletes;

class CandidaturaNivel extends Model
{
    use HasFactory;
    // Eliminar esta línea: use SoftDeletes;

    protected $table = 'candidatura_niveles';

    protected $fillable = [
        'descripcion',
        'nivel'
    ];

    protected $casts = [
        'nivel' => 'integer',
    ];
    
    // Eliminar o comentar la propiedad $dates si existe
    // protected $dates = ['deleted_at'];
}