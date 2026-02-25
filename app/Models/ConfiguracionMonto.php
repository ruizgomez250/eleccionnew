<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionMonto extends Model
{
    use HasFactory;

    protected $table = 'configuracion_montos';

    /**
     * Campos que se pueden asignar masivamente
     */
    protected $fillable = [
        'concepto',
        'monto',
        'activo',
        'sistema_id',
    ];

    /**
     * Relación con Sistema
     */
    public function sistema()
    {
        return $this->belongsTo(Sistema::class, 'sistema_id');
    }

    /**
     * Alcance: solo montos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Formatear monto con 2 decimales
     */
    public function getMontoFormateadoAttribute()
    {
        return number_format($this->monto, 2);
    }
}