<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalInterna extends Model
{
    use HasFactory;

    protected $table = 'locales_internas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'distrito_nombre',
        'departamento_nombre',
        'local_interna',
        'cantmesa',
    ];
}