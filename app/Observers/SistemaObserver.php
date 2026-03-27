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
        // Verificar si el usuario tiene un sistema asignado
        if ($sistema->idusuario) {
            $usuario = User::find($sistema->idusuario);
            
            if ($usuario && $usuario->sistema) {
                // Guardar en sistemaspadre
                Sistemaspadre::create([
                    'idsistema' => $sistema->id,
                    'idsistemapadre' => $usuario->sistema, // sistema que viene del usuario
                ]);
            } else {
                // Si el usuario no tiene sistema padre, guardar con null
                Sistemaspadre::create([
                    'idsistema' => $sistema->id,
                    'idsistemapadre' => null,
                ]);
            }
        } else {
            // Si no hay idusuario, guardar con null
            Sistemaspadre::create([
                'idsistema' => $sistema->id,
                'idsistemapadre' => null,
            ]);
        }
    }

    /**
     * Handle the Sistema "updated" event.
     */
    public function updated(Sistema $sistema): void
    {
        // Verificar si cambió el idusuario o el sistema del usuario
        if ($sistema->wasChanged('idusuario')) {
            // Buscar el registro en sistemaspadre
            $sistemaPadre = Sistemaspadre::where('idsistema', $sistema->id)->first();
            
            if ($sistema->idusuario) {
                $usuario = User::find($sistema->idusuario);
                
                if ($usuario && $usuario->sistema) {
                    if ($sistemaPadre) {
                        // Actualizar el registro existente
                        $sistemaPadre->update([
                            'idsistemapadre' => $usuario->sistema,
                        ]);
                    } else {
                        // Crear nuevo registro si no existe
                        Sistemaspadre::create([
                            'idsistema' => $sistema->id,
                            'idsistemapadre' => $usuario->sistema,
                        ]);
                    }
                } else {
                    // Si el usuario no tiene sistema padre, actualizar con null
                    if ($sistemaPadre) {
                        $sistemaPadre->update([
                            'idsistemapadre' => null,
                        ]);
                    }
                }
            } else {
                // Si no hay idusuario, actualizar con null
                if ($sistemaPadre) {
                    $sistemaPadre->update([
                        'idsistemapadre' => null,
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
        // Opcional: Eliminar también el registro en sistemaspadre
        Sistemaspadre::where('idsistema', $sistema->id)->delete();
    }
}