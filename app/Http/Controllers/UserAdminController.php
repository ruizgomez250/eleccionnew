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
                ->whereHas('sistemaRelacion', function ($q) use ($userId) {
                    $q->where('idusuario', $userId);
                })
                ->get();
            $sistemas = Sistema::where('idusuario', $userId)->get();
            $ciudades = CiudadElectoral::orderBy('descripcion')->get();
            return view('useradmin.index', compact('users', 'sistemas', 'roles', 'ciudades'));
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
        //$this->verificarPermiso();
        //dd($request->input());
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($request->user_id ? ",{$request->user_id}" : ''),
            'password' => $request->user_id ? 'nullable|string|min:6' : 'required|string|min:6',
            'sistema' => 'nullable|exists:sistemas,id',
            'roles' => 'required'
        ]);

        DB::beginTransaction();

        try {

            if ($request->user_id) {

                // 🔹 ACTUALIZAR
                $user = User::findOrFail($request->user_id);

                $user->name = $request->name;
                $user->email = $request->email;

                if ($request->password) {
                    $user->password = Hash::make($request->password);
                }

                $user->sistema = $request->sistema;
                $user->save();
            } else {

                // 🔹 CREAR
                $userId = Auth::id();
                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => Hash::make($request->password),
                    'sistema' => $request->sistema,
                    'idusuario' => $userId,
                ]);
            }

            // 🔹 ASIGNAR ROL
            $user->syncRoles($request->roles);

            DB::commit();

            return redirect()
                ->route('useradmin.index')
                ->with('success', 'Usuario guardado correctamente');
        } catch (Exception $e) {
            dd($e);
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
