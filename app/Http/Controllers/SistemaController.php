<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Equipo;
use App\Models\PrePadron;
use App\Models\Sistema;
use Exception;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class SistemaController extends Controller
{
    /**
     * Verifica que el usuario logueado tenga permisos.
     */
    private function verificarPermiso($sistema)
    {
        $userId = Auth::id();

        if (!in_array($userId, [1, 4]) && $sistema->idusuario != $userId) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }
    }

    public function store(Request $request)
    {
        //$this->verificarPermiso();

        $request->validate([
            'nombre' => 'required|string|max:255',
            'sistema_id' => 'nullable|exists:sistemas,id',
            'id_ciudad_electoral' => 'required|exists:ciudades_electorales,id',
        ]);

        DB::beginTransaction();

        try {

            if ($request->sistema_id) {

                // 🔹 Actualizar sistema
                $sistema = Sistema::findOrFail($request->sistema_id);
                $sistema->nombre = $request->nombre;
                $sistema->id_ciudad_electoral = $request->id_ciudad_electoral;
                $sistema->tipo = $request->tipo;
                $sistema->save();

                DB::commit();
                return back()->with('success', 'Sistema actualizado correctamente');
            } else {

                // 🔹 Crear sistema
                $sistema = Sistema::create([
                    'nombre' => $request->nombre,
                    'id_ciudad_electoral' => $request->id_ciudad_electoral,
                    'idusuario' => Auth::id(),
                    'tipo' => $request->tipo
                ]);

                // 🔹 Obtener ciudad electoral
                $ciudad = CiudadElectoral::findOrFail($request->id_ciudad_electoral);

                // 🔹 Insert masivo filtrado por ciudad electoral
                DB::statement("
                INSERT INTO equipo (sist, ciudad, colegio, descripcion, created_at, updated_at)
                SELECT 
                    ? AS sist,
                    li.distrito_nombre AS ciudad,
                    li.local_interna AS colegio,
                    li.local_interna AS descripcion,
                    NOW(),
                    NOW()
                FROM locales_internas li
                LEFT JOIN equipo e
                    ON e.sist = ?
                    AND e.ciudad = li.distrito_nombre
                    AND e.colegio = li.local_interna
                WHERE e.id IS NULL
                AND li.distrito_nombre = ?
                AND li.departamento_nombre = ?
            ", [
                    $sistema->id,
                    $sistema->id,
                    $ciudad->descripcion,
                    $ciudad->departamento
                ]);

                DB::commit();

                return back()->with('success', 'Sistema creado correctamente con equipos generados');
            }
        } catch (\Exception $e) {

            DB::rollBack();
            return back()->with('error', 'Ocurrió un error: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $sistema = Sistema::findOrFail($id);
        $this->verificarPermiso($sistema); // 🔹 Verificar permiso

        try {
            

            // Verificar si tiene usuarios asignados antes de borrar
            if ($sistema->users()->count() > 0) {
                return back()->with('error', 'No se puede eliminar un sistema que tiene usuarios asignados. Primero debe eliminar el usuario');
            }

            $sistema->delete();

            return back()->with('success', 'Sistema eliminado correctamente');
        } catch (ModelNotFoundException $e) {
            // Cuando no encuentra el sistema
            return back()->with('error', 'Sistema no encontrado');
        } catch (Exception $e) {

            // Otros errores generales
            return back()->with('error', 'Ocurrió un error al eliminar el sistema: ' . $e->getMessage());
        }
    }
}
