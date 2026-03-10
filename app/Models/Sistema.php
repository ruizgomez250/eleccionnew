<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    protected $fillable = ['nombre','id_ciudad_electoral','idusuario','tipo'];

    public function users()
    {
        return $this->hasMany(User::class, 'sistema');
    }
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'sist');
    }
    public function ciudad()
    {
        return $this->belongsTo(CiudadElectoral::class, 'id_ciudad_electoral');
    }
}
