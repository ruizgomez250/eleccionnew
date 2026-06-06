<?php
// app/Models/Candidato.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Candidato extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'candidatos';

    /**
     * Los cargos electorales disponibles
     */
    const CARGOS = [
        'intendente',
        'Concejal Municipal',
        'presidente - vice 1 y vice 2 - plra',
        'directorio nacional',
        'directorio departamental',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'partido_id',
        'nombre_completo',
        'documento',
        'numero_orden',
        'cargo',
        'foto_url',
        'activo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'numero_orden' => 'integer',
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación: Un candidato pertenece a un partido
     */
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    /**
     * Relación: Un candidato tiene muchos votos (preferencias)
     */
    public function votos()
    {
        return $this->hasMany(VotosMesa::class);
    }

    /**
     * Obtener el nombre completo con orden
     */
    public function getNombreCompletoOrdenAttribute()
    {
        return "{$this->numero_orden}. {$this->nombre_completo}";
    }

    /**
     * Obtener el cargo en español con mayúscula inicial
     */
    public function getCargoNombreAttribute()
    {
        $cargos = [
            'intendente' => 'Intendente',
            'Concejal Municipal' => 'Concejal Municipal',
            'presidente - vice 1 y vice 2 - plra' => 'Presidente - Vice 1 y Vice 2 - PLRA',
            'directorio nacional' => 'Directorio Nacional',
            'directorio departamental' => 'Directorio Departamental',
        ];
        
        return $cargos[$this->cargo] ?? ucfirst($this->cargo);
    }

    /**
     * Scope para candidatos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para filtrar por cargo
     */
    public function scopePorCargo($query, $cargo)
    {
        return $query->where('cargo', $cargo);
    }

    /**
     * Scope para ordenar por número de orden
     */
    public function scopeOrdenados($query)
    {
        return $query->orderBy('numero_orden');
    }
}