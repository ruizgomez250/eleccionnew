<?php

namespace App\Http\Controllers;

use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SistemaController extends Controller
{
    /**
     * Verifica que el usuario logueado tenga permisos.
     */
    private function verificarPermiso()
    {
        $userId = Auth::id();
        if (!in_array($userId, [1, 4])) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        $this->verificarPermiso(); // 🔹 Verificar permiso

        $request->validate([
            'nombre' => 'required|string|max:255',
            'sistema_id' => 'nullable|exists:sistemas,id',
        ]);

        if ($request->sistema_id) {
            // Actualizar sistema
            $sistema = Sistema::findOrFail($request->sistema_id);
            $sistema->nombre = $request->nombre;
            $sistema->save();

            return back()->with('success', 'Sistema actualizado correctamente');
        } else {
            // Crear nuevo sistema
            Sistema::create([
                'nombre' => $request->nombre
            ]);

            return back()->with('success', 'Sistema creado correctamente');
        }
    }

    public function destroy($id)
    {
        $this->verificarPermiso(); // 🔹 Verificar permiso

        $sistema = Sistema::findOrFail($id);

        // Opcional: Verificar si tiene usuarios asignados antes de borrar
        if ($sistema->users()->count() > 0) {
            return back()->with('error', 'No se puede eliminar un sistema que tiene usuarios asignados');
        }

        $sistema->delete();
        return back()->with('success', 'Sistema eliminado correctamente');
    }
}
