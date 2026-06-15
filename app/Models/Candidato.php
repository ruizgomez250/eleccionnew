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
     * Los cargos electorales disponibles si tiene cambios
     */
    const CARGOS = [
        'intendente',
        'Concejal Municipal',
        'presidente - vice 1 y vice 2 - plra',
        'directorio nacional',
        'directorio departamental',
        'comite 1 local',
        'comite 2 local',
        'comite 3 local',
        'comite 4 local',
        'convencional',
        'convencional 1',
        'convencional 2',
        'convencional 3',
        'convencional 4',
        'comite 1', 'comite 2', 'comite 3', 'comite 4', 'comite 5', 'comite 6',
        'comite 7', 'comite 8', 'comite 9', 'comite 10', 'comite 11', 'comite 12',
        'juventud 1', 'juventud 2', 'juventud 3', 'juventud 4', 'juventud 5', 'juventud 6',
        'juventud 7', 'juventud 8', 'juventud 9', 'juventud 10', 'juventud 11', 'juventud 12',
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
            'comite 1 local' => 'Comité 1 Local',
            'comite 2 local' => 'Comité 2 Local',
            'comite 3 local' => 'Comité 3 Local',
            'comite 4 local' => 'Comité 4 Local',
            'convencional' => 'Convencional',
            'convencional 1' => 'Convencional 1',
            'convencional 2' => 'Convencional 2',
            'convencional 3' => 'Convencional 3',
            'convencional 4' => 'Convencional 4',
        ];
        for ($i = 1; $i <= 12; $i++) {
            $cargos["comite {$i}"] = "Comité Concejal {$i}";
            $cargos["juventud {$i}"] = "Juventud Concejal {$i}";
        }
        
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