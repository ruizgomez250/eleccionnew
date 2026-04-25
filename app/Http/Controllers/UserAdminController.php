<?php

namespace App\Http\Controllers;

use App\Models\CiudadElectoral;
use App\Models\Role;
use App\Models\User;
use App\Models\Sistema;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserAdminController extends Controller
{
    /**
     * Verifica que el usuario logueado tenga permisos (solo id 1 y 4)
     */
    private function verificarPermiso()
    {
        $userId = Auth::id();
        if (!in_array($userId, [1, 4])) {
            $roles = Role::pluck('name', 'name')->all();
            $users = User::with('sistemaRelacion')
                ->where('idusuario', $userId)  // Directo, sin whereHas
                ->get();
            $user = User::with('sistemaRelacion')->find($userId);
            $sistemas = Sistema::where('id', $user->sistema)->get();
            //$ciudades = CiudadElectoral::orderBy('descripcion')->get();
            return view('useradmin.solouser', compact('users', 'roles', 'sistemas'));
        }
    }

    public function index()
    {
        if ($resp = $this->verificarPermiso()) {
            return $resp;
        }

        $roles = Role::pluck('name', 'name')->all();
        $users = User::with('sistemaRelacion')->get();

        // 👇 Cargar la relación 'usuario' junto con los sistemas
        $sistemas = Sistema::with('usuario')->get();

        $ciudades = CiudadElectoral::orderBy('descripcion')->get();

        return view('useradmin.index', compact('users', 'sistemas', 'roles', 'ciudades'));
    }

    public function store(Request $request)
    {

        // Validación base
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($request->user_id ? ",{$request->user_id}" : ''),
            'password' => $request->user_id ? 'nullable|string|min:6' : 'required|string|min:6',
            'roles' => 'required'
        ]);

        DB::beginTransaction();

        try {
            $sistemaId = null;

            // 🔹 Verificar si se está creando un nuevo sistema
            if ($request->sistema === 'nuevo') {
                $request->validate([
                    'sistema_nombre' => 'required|string|max:255',
                    'sistema_tipo' => 'required|string',
                    'sistema_candidatosup1' => 'required|not_in:0',
                ]);

                // Obtener el candidato superior seleccionado
                $candidatoSuperior = User::with('sistemaRelacion.ciudad')->find($request->sistema_candidatosup1);

                if (!$candidatoSuperior || !$candidatoSuperior->sistemaRelacion) {
                    throw new Exception('El candidato superior seleccionado no tiene un sistema asociado');
                }

                // Copiar los datos del sistema del candidato superior
                $sistemaOrigen = $candidatoSuperior->sistemaRelacion;

                // Crear el nuevo sistema
                $sistema = Sistema::create([
                    'nombre' => $request->sistema_nombre,
                    'id_ciudad_electoral' => $sistemaOrigen->id_ciudad_electoral,
                    'tipo' => $request->sistema_tipo,
                    'idusuario' => $request->sistema_candidatosup1,
                ]);

                $sistemaId = $sistema->id;

                // 🔹 CREAR EQUIPOS PARA EL NUEVO SISTEMA
                // Obtener la ciudad electoral del sistema origen
                $ciudad = CiudadElectoral::findOrFail($sistemaOrigen->id_ciudad_electoral);
                // Insert masivo de equipos para el nuevo sistema
                // Usar DB::insert para obtener el número de filas afectadas
                $insertados = DB::insert("
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
                ", [$sistema->id, $sistema->id, $ciudad->descripcion, $ciudad->departamento]);

                //dd($insertados);
            } else {
                // Usar sistema existente (puede ser null)
                $sistemaId = $request->sistema ?? null;
            }

            // 🔹 Crear o actualizar usuario
            if ($request->user_id) {
                // ACTUALIZAR
                $user = User::findOrFail($request->user_id);

                $user->name = $request->name;
                $user->email = $request->email;

                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }

                $user->sistema = $sistemaId;
                $user->save();
            } else {
                // CREAR
                $userId = Auth::id();
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'sistema' => $sistemaId,
                    'idusuario' => $userId,
                ]);
            }

            // 🔹 ASIGNAR ROL (ahora es selección única)
            if ($request->has('roles')) {
                $rol = is_array($request->roles) ? $request->roles[0] : $request->roles;
                $user->syncRoles([$rol]);
            }

            DB::commit();

            $message = 'Usuario guardado correctamente';
            if ($request->sistema === 'nuevo') {
                $cantidadEquipos = DB::table('equipo')->where('sist', $sistemaId)->count();
                $message .= " y sistema creado correctamente con {$cantidadEquipos} equipos generados";
            }

            return redirect()
                ->route('useradmin.index')
                ->with('success', $message);
        } catch (Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error al guardar usuario: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $this->verificarPermiso(); // 🔹 Verificar permiso antes de eliminar

        $user = User::findOrFail($id);
        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente');
    }
}
