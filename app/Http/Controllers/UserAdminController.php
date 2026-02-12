<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\User;
use App\Models\Sistema;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserAdminController extends Controller
{
    /**
     * Verifica que el usuario logueado tenga permisos (solo id 1 y 4)
     */
    private function verificarPermiso()
    {
        $userId = Auth::id();
        if (!in_array($userId, [1, 4])) {
            abort(403, 'No tiene permiso para realizar esta acción.');
        }
    }

    public function index()
    {
        $this->verificarPermiso(); // 🔹 Verificar permiso al entrar a la página
        $roles = Role::pluck('name', 'name')->all();
        $users = User::with('sistemaRelacion')->get();
        $sistemas = Sistema::all();
        return view('useradmin.index', compact('users', 'sistemas','roles'));
    }

    public function store(Request $request)
    {
        $this->verificarPermiso(); // 🔹 Verificar permiso antes de crear o actualizar

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email' . ($request->user_id ? ",{$request->user_id}" : ''),
            'password' => $request->user_id ? 'nullable|string|min:6' : 'required|string|min:6',
            'sistema' => 'nullable|exists:sistemas,id',
        ]);

        if ($request->user_id) {
            // Actualizar usuario
            $user = User::findOrFail($request->user_id);
            $user->name = $request->name;
            $user->email = $request->email;
            if ($request->password) {
                $user->password = Hash::make($request->password);
            }
            $user->sistema = $request->sistema;
            $user->save();

            return redirect()->route('useradmin.index')->with('success', 'Usuario actualizado correctamente');

        } else {
            // Crear nuevo usuario
            User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'sistema' => $request->sistema,
            ]);

            return redirect()->route('useradmin.index')->with('success', 'Usuario creado correctamente');

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
