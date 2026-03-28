<?php

namespace App\Observers;

use App\Models\Sistema;
use App\Models\Sistemaspadre;
use App\Models\User;

class SistemaObserver
{
    /**
     * Handle the Sistema "created" event.
     */
    public function created(Sistema $sistema): void
    {
        $idsistemapadre = null;
        
        // Verificar si el usuario tiene un sistema asignado
        if ($sistema->idusuario) {
            $usuario = User::find($sistema->idusuario);
            
            if ($usuario && $usuario->sistema) {
                $idsistemapadre = $usuario->sistema;
                
                // 🔹 VERIFICACIÓN: Si el sistema padre es igual al sistema actual, poner null
                if ($idsistemapadre == $sistema->id) {
                    $idsistemapadre = null;
                }
            }
        }
        
        // Guardar en sistemaspadre
        Sistemaspadre::create([
            'idsistema' => $sistema->id,
            'idsistemapadre' => $idsistemapadre,
        ]);
    }

    /**
     * Handle the Sistema "updated" event.
     */
    public function updated(Sistema $sistema): void
    {
        // Verificar si cambió el idusuario
        if ($sistema->wasChanged('idusuario')) {
            // Buscar el registro en sistemaspadre
            $sistemaPadre = Sistemaspadre::where('idsistema', $sistema->id)->first();
            
            $idsistemapadre = null;
            
            if ($sistema->idusuario) {
                $usuario = User::find($sistema->idusuario);
                
                if ($usuario && $usuario->sistema) {
                    $idsistemapadre = $usuario->sistema;
                    
                    // 🔹 VERIFICACIÓN: Si el sistema padre es igual al sistema actual, poner null
                    if ($idsistemapadre == $sistema->id) {
                        $idsistemapadre = null;
                    }
                }
            }
            
            if ($sistemaPadre) {
                // Actualizar el registro existente
                $sistemaPadre->update([
                    'idsistemapadre' => $idsistemapadre,
                ]);
            } else {
                // Crear nuevo registro si no existe
                Sistemaspadre::create([
                    'idsistema' => $sistema->id,
                    'idsistemapadre' => $idsistemapadre,
                ]);
            }
        }
        
        // 🔹 NUEVA VERIFICACIÓN: Si cambió el sistema del usuario que está asociado
        // Esto es útil si el usuario actualiza su sistema asignado después de crear el sistema
        if ($sistema->wasChanged('idusuario') === false && $sistema->idusuario) {
            $usuario = User::find($sistema->idusuario);
            
            if ($usuario && $usuario->wasChanged('sistema')) {
                $sistemaPadre = Sistemaspadre::where('idsistema', $sistema->id)->first();
                
                $idsistemapadre = $usuario->sistema;
                
                // 🔹 VERIFICACIÓN: Si el sistema padre es igual al sistema actual, poner null
                if ($idsistemapadre == $sistema->id) {
                    $idsistemapadre = null;
                }
                
                if ($sistemaPadre) {
                    $sistemaPadre->update([
                        'idsistemapadre' => $idsistemapadre,
                    ]);
                }
            }
        }
    }

    /**
     * Handle the Sistema "deleted" event.
     */
    public function deleted(Sistema $sistema): void
    {
        // Eliminar también el registro en sistemaspadre
        Sistemaspadre::where('idsistema', $sistema->id)->delete();
    }
}