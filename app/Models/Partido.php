<?php
// app/Models/Partido.php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Partido extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'partidos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'numero_lista',
        'nombre',
        'sigla',
        'color_hex',
        'logo_url',
        'activo'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'activo' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];

    /**
     * Relación: Un partido tiene muchos candidatos
     */
    public function candidatos()
    {
        return $this->hasMany(Candidato::class);
    }

    /**
     * Relación: Un partido tiene muchos votos
     */
    public function votos()
    {
        return $this->hasMany(VotosMesa::class);
    }

    /**
     * Relación: Un partido pertenece a muchas mesas (a través de votos_mesa)
     */
    public function mesas()
    {
        return $this->belongsToMany(Mesa::class, 'votos_mesa', 'partido_id', 'mesa_id')
                    ->withPivot('cargo', 'cantidad_votos', 'tipo_voto')
                    ->withTimestamps();
    }

    /**
     * Obtener el nombre completo con número de lista
     */
    public function getNombreCompletoAttribute()
    {
        return "{$this->numero_lista} - {$this->nombre}";
    }

    /**
     * Obtener el nombre corto (sigla o nombre abreviado)
     */
    public function getNombreCortoAttribute()
    {
        return $this->sigla ?: mb_substr($this->nombre, 0, 15);
    }

    /**
     * Scope para partidos activos
     */
    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    /**
     * Scope para buscar por número de lista
     */
    public function scopePorLista($query, $numeroLista)
    {
        return $query->where('numero_lista', $numeroLista);
    }
}