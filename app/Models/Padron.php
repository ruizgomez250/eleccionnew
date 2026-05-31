<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Padron extends Model
{
    protected $table = 'padron';
    
    protected $fillable = [
        'cedula',
        'nombre',
        'apellido',
        'local_interna',
        'distrito_nombre',
        'mesa',
        'orden'
    ];
    
    // Relación con Voto
    public function voto()
    {
        return $this->hasOne(Voto::class, 'cedula', 'cedula');
    }
}