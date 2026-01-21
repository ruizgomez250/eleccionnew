<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PadronIluminado extends Model
{
    protected $table = 'padroniluminado';

    protected $primaryKey = 'id'; // si la tabla tiene id autoincrement

    public $timestamps = false;

    protected $fillable = [
        'coddep',
        'departamento',
        'coddis',
        'distrito',
        'codzon',
        'zona',
        'codloc',
        'local',
        'cedula',
        'nombre',
        'apellido',
        'fecnac',
        'edad',
        'partido',
        'votoanr',
        'votoplra',
        'votogen',
        'mesa',
        'orden',
        'localdesc'
    ];

    protected $casts = [
        'coddep'    => 'integer',
        'coddis'    => 'integer',
        'codzon'    => 'integer',
        'codloc'    => 'integer',
        'cedula'    => 'integer',
        'edad'      => 'integer',
        'votoplra'  => 'integer',
        'votogen'   => 'integer',
        'mesa'      => 'integer',
        'orden'     => 'integer',
    ];
}

