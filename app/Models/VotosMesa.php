<?php
// app/Models/VotosMesa.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VotosMesa extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'votos_mesa';

    /**
     * Los tipos de voto disponibles
     */
    const TIPO_LISTA = 'lista';
    const TIPO_PREFERENCIA = 'preferencia';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'mesa_id',
        'partido_id',
        'candidato_id',
        'cargo',
        'cantidad_votos',
        'tipo_voto',
        'origen',
        'escaneado_en',
        'escaneado_por',
        'dispositivo_id',
        'veedor_id',
        'user_id'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'cantidad_votos' => 'integer',
        'escaneado_en' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación: Un voto pertenece a una mesa
     */
    public function mesa()
    {
        return $this->belongsTo(Mesa::class);
    }

    /**
     * Relación: Un voto pertenece a un partido
     */
    public function partido()
    {
        return $this->belongsTo(Partido::class);
    }

    /**
     * Relación: Un voto puede pertenecer a un candidato (si es tipo preferencia)
     */
    public function candidato()
    {
        return $this->belongsTo(Candidato::class);
    }

    /**
     * Relación: Un voto puede ser escaneado por un veedor
     */
    public function veedor()
    {
        return $this->belongsTo(Veedor::class);
    }

    /**
     * Relación: Un voto fue cargado por un usuario (admin web)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Verificar si es voto de lista
     */
    public function esVotoLista()
    {
        return $this->tipo_voto === self::TIPO_LISTA;
    }

    /**
     * Verificar si es voto de preferencia
     */
    public function esVotoPreferencia()
    {
        return $this->tipo_voto === self::TIPO_PREFERENCIA;
    }

    /**
     * Obtener el nombre del cargo en español
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
     * Scope para filtrar por tipo de voto
     */
    public function scopeVotosLista($query)
    {
        return $query->where('tipo_voto', self::TIPO_LISTA);
    }

    /**
     * Scope para filtrar por preferencias
     */
    public function scopePreferencias($query)
    {
        return $query->where('tipo_voto', self::TIPO_PREFERENCIA);
    }

    /**
     * Scope para filtrar por cargo
     */
    public function scopePorCargo($query, $cargo)
    {
        return $query->where('cargo', $cargo);
    }

    /**
     * Scope para filtrar por mesa
     */
    public function scopePorMesa($query, $mesaId)
    {
        return $query->where('mesa_id', $mesaId);
    }

    /**
     * Scope para filtrar por partido
     */
    public function scopePorPartido($query, $partidoId)
    {
        return $query->where('partido_id', $partidoId);
    }
}