<?php
// app/Models/Mesa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Mesa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'mesas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'equipo_id',
        'codigo_mesa',
        'departamento',
        'distrito',
        'zona',
        'direccion',
        'numero_mesa'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'numero_mesa' => 'integer',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación: Una mesa tiene muchos votos
     */
    public function votos()
    {
        return $this->hasMany(VotosMesa::class);
    }

    /**
     * Relación: Una mesa tiene muchos votos por partido (a través de votos_mesa)
     */
    public function partidos()
    {
        return $this->belongsToMany(Partido::class, 'votos_mesa', 'mesa_id', 'partido_id')
                    ->withPivot('cargo', 'cantidad_votos', 'tipo_voto')
                    ->withTimestamps();
    }

    /**
     * Relación: Una mesa pertenece a un equipo (escuela electoral)
     */
    public function equipo()
    {
        return $this->belongsTo(Equipo::class);
    }

    /**
     * Obtener el código completo de la mesa para mostrar
     */
    public function getCodigoCompletoAttribute()
    {
        return $this->codigo_mesa;
    }

    /**
     * Obtener la ubicación completa
     */
    public function getUbicacionAttribute()
    {
        return "{$this->departamento} - {$this->distrito}" . ($this->zona ? " - {$this->zona}" : "");
    }

    /**
     * Scope para buscar por departamento
     */
    public function scopePorDepartamento($query, $departamento)
    {
        return $query->where('departamento', 'like', "%{$departamento}%");
    }

    /**
     * Scope para buscar por distrito
     */
    public function scopePorDistrito($query, $distrito)
    {
        return $query->where('distrito', 'like', "%{$distrito}%");
    }
}