<?php

namespace App\Http\Controllers;

use App\Models\Vehiculo;
use App\Models\Puntero;
use Illuminate\Http\Request;

class VehiculoPunteroController extends Controller
{
    public function asignar(Vehiculo $vehiculo, Puntero $puntero)
    {
        // Evitar duplicados
        if ($vehiculo->punteros()->where('puntero_id', $puntero->id)->exists()) {
            return response()->json([
                'message' => 'El puntero ya está asignado'
            ], 409);
        }

        $vehiculo->punteros()->attach($puntero->id, [
            'fecha_asignacion' => now(),
            'id_equipo' => $vehiculo->id_equipo
        ]);

        return response()->json([
            'message' => 'Puntero asignado correctamente'
        ]);
    }
    public function destroy(Puntero $puntero)
    {
        try {
            $puntero->delete();
            return response()->json(['success' => true, 'message' => 'Puntero eliminado correctamente']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'No se pudo eliminar el puntero']);
        }
    }
}
