<?php

namespace App\Models\Internas;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LocalInterna extends Model
{
    use HasFactory;

    protected $table = 'internas_locales_internas';

    protected $primaryKey = 'id';

    public $timestamps = false;

    protected $fillable = [
        'distrito_nombre',
        'departamento_nombre',
        'local_interna',
        'cantmesa',
    ];
}