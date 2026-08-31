<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VisitaPuntero extends Model
{
    use HasFactory;

    protected $table = 'visita_puntero';

    protected $fillable = [
        'idpuntero',
        'cedula',
        'nombre_votante',
        'apellido_votante',
        'direccion',
        'casa_de',
        'cedula_votante',
        'observacion',
        'latitud',
        'longitud',
        'fecha_visita',
        'resultado',
        'proxima_visita',
        'precision_gps',
        'referencia',
        'idusuario',
    ];

    protected $casts = [
        'fecha_visita' => 'datetime',
        'proxima_visita' => 'datetime',
        'latitud' => 'decimal:7',
        'longitud' => 'decimal:7',
        'precision_gps' => 'decimal:2',
    ];

    // Relaciones

    public function puntero()
    {
        return $this->belongsTo(Puntero::class, 'idpuntero');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }

    // Accessors

    public function getNombreCompletoAttribute(): string
    {
        return trim(($this->nombre_votante ?? '') . ' ' . ($this->apellido_votante ?? ''));
    }

    public function getUbicacionAttribute(): ?string
    {
        if ($this->latitud && $this->longitud) {
            return "{$this->latitud}, {$this->longitud}";
        }
        return null;
    }

    // Scopes

    public function scopePorPuntero($query, $idpuntero)
    {
        return $query->where('idpuntero', $idpuntero);
    }

    public function scopePorFecha($query, $desde, $hasta)
    {
        if ($desde) {
            $query->where('fecha_visita', '>=', $desde);
        }
        if ($hasta) {
            $query->where('fecha_visita', '<=', $hasta . ' 23:59:59');
        }
        return $query;
    }

    public function scopePorResultado($query, $resultado)
    {
        if ($resultado) {
            $query->where('resultado', 'LIKE', "%{$resultado}%");
        }
        return $query;
    }

    public function scopeDelSistema($query, $sistemaId)
    {
        return $query->whereHas('puntero.dirigente.equipo', function ($q) use ($sistemaId) {
            $q->where('sist', $sistemaId);
        });
    }

    public function scopeProximasVisitas($query)
    {
        return $query->whereNotNull('proxima_visita')
            ->where('proxima_visita', '>=', now())
            ->orderBy('proxima_visita', 'asc');
    }
}
