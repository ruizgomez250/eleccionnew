<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CargaEfectividad extends Model
{
    protected $table = 'carga_efectividad';

    protected $fillable = [
        'mesa', 'intendente',
        'c1', 'c2', 'c3', 'c4', 'c5', 'c6', 'c7', 'c8', 'c9', 'c10', 'c11', 'c12',
        'com1', 'com2', 'com3', 'com4', 'com5', 'com6', 'com7', 'com8', 'com9', 'com10', 'com11', 'com12',
        'juv1', 'juv2', 'juv3', 'juv4', 'juv5', 'juv6', 'juv7', 'juv8', 'juv9', 'juv10', 'juv11', 'juv12',
    ];

    protected $casts = [
        'intendente' => 'integer',
        'c1' => 'integer', 'c2' => 'integer', 'c3' => 'integer', 'c4' => 'integer',
        'c5' => 'integer', 'c6' => 'integer', 'c7' => 'integer', 'c8' => 'integer',
        'c9' => 'integer', 'c10' => 'integer', 'c11' => 'integer', 'c12' => 'integer',
        'com1' => 'integer', 'com2' => 'integer', 'com3' => 'integer', 'com4' => 'integer',
        'com5' => 'integer', 'com6' => 'integer', 'com7' => 'integer', 'com8' => 'integer',
        'com9' => 'integer', 'com10' => 'integer', 'com11' => 'integer', 'com12' => 'integer',
        'juv1' => 'integer', 'juv2' => 'integer', 'juv3' => 'integer', 'juv4' => 'integer',
        'juv5' => 'integer', 'juv6' => 'integer', 'juv7' => 'integer', 'juv8' => 'integer',
        'juv9' => 'integer', 'juv10' => 'integer', 'juv11' => 'integer', 'juv12' => 'integer',
    ];
}
