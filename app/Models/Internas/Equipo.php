<?php
namespace App\Models\Internas;
use Illuminate\Database\Eloquent\Model;
class Equipo extends Model
{
    protected $table = 'internas_equipo';
    protected $fillable = ['descripcion', 'sist', 'colegio', 'ciudad'];
    public function mesas() { return $this->hasMany(Mesa::class, 'equipo_id'); }
}
