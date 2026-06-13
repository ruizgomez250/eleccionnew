<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Sistema extends Model
{
    protected $fillable = ['nombre', 'id_ciudad_electoral', 'idusuario', 'tipo'];
    
    // Relaciones
    public function sistemaPadre()
    {
        return $this->hasOne(Sistemaspadre::class, 'idsistema');
    }
    
    public function users()
    {
        return $this->hasMany(User::class, 'sistema');
    }
    
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario');
    }
    
    public function equipos()
    {
        return $this->hasMany(Equipo::class, 'sist');
    }
    
    public function ciudad()
    {
        return $this->belongsTo(CiudadElectoral::class, 'id_ciudad_electoral');
    }
    
    /**
     * Obtener el nivel jerárquico basado en la descripción del tipo
     * Busca en la tabla candidatura_niveles por descripción
     * SIN GUARDAR NADA EN LA BASE DE DATOS
     */
    public function getNivelJerarquico()
    {
        // Buscar el nivel por descripción del tipo
        $tipo = strtolower(trim($this->tipo ?? ''));
        
        // Buscar en candidatura_niveles si la descripción coincide exactamente
        $nivel = CandidaturaNivel::where('descripcion', $tipo)->first();
        
        // Si no encuentra exacto, buscar con LIKE
        if (!$nivel) {
            $nivel = CandidaturaNivel::where('descripcion', 'LIKE', "%{$tipo}%")->first();
        }
        
        if ($nivel) {
            return $nivel->nivel;
        }
        
        // Si no encuentra, usar niveles por defecto basados en el tipo
        $nivelesPorTipo = [
            'intendente' => 1,
            'concejal' => 2,
            'convencional' => 3,
            'convencional juventud' => 4,
            'miembro de comite' => 5,
            'miembro de la juventud' => 6,
            'miembro del consejo' => 7
        ];
        
        return $nivelesPorTipo[$tipo] ?? 99;
    }
    
    /**
     * Obtener la información completa del nivel
     * SIN GUARDAR NADA EN LA BASE DE DATOS
     */
    public function getInfoNivel()
    {
        $tipo = strtolower(trim($this->tipo ?? ''));
        
        // Buscar coincidencia exacta
        $nivel = CandidaturaNivel::where('descripcion', $tipo)->first();
        
        // Si no encuentra exacto, buscar con LIKE
        if (!$nivel) {
            $nivel = CandidaturaNivel::where('descripcion', 'LIKE', "%{$tipo}%")->first();
        }
        
        if ($nivel) {
            return [
                'id' => $nivel->id,
                'nivel' => $nivel->nivel,
                'descripcion' => $nivel->descripcion
            ];
        }
        
        return null;
    }
}