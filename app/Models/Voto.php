<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Voto extends Model
{
    protected $table = 'votos';

    protected $fillable = [
        'cedula',
        'nombres',
        'apellidos',
        'localvotacion',
        'distrito',
        'idmiembrodemesa'
    ];
}
