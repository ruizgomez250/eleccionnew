<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MiembroDeMesa extends Model
{
    use HasFactory;

    protected $table = 'miembros_de_mesa';

    protected $fillable = [
        'nombre',
        'telefono',
        'cedula',
        'funcion',
        'idequipo',
        'cedulaproponente',
        'nombreproponente',
        'telefonoproponente',
        'mesa',

    ];

    /**
     * Relación: Un miembro pertenece a un equipo
     */
    public function equipo()
    {
        return $this->belongsTo(Equipo::class, 'idequipo');
    }
}