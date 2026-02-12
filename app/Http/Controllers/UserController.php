<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserDestino;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{

    public function __construct()
    {
        $this->middleware('permission:Listar Usuarios', ['only' => ['index', 'show']]);
        $this->middleware('permission:Guardar Usuarios', ['only' => ['store', 'create']]);
        $this->middleware('permission:Actualizar Usuarios', ['only' => ['update', 'edit']]);
        $this->middleware('permission:Eliminar Usuarios', ['only' => ['destroy']]);
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $users = User::where('sistema', auth()->user()->sistema)->get();

        return view('role-permission.user.index', compact('users'));
    }
    

    /** user does not have correct Role
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $roles = Role::pluck('name', 'name')->all();
        // @dd($roles);
        return view('role-permission.user.create', compact('roles'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //   @dd($request);
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|max:20',
            'roles' => 'required'
        ]);
        $sistema   = Auth::user()->sistema;
        // $role = Role::create(['name' => 'writer']);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'sistema' => $sistema,
        ]);
        $user->syncRoles($request->roles);
        return redirect('users')->with('status', 'Usuario creado exitosamente');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        $roles = Role::pluck('name', 'name')->all();
        $userRoles = $user->roles->pluck('name', 'name')->all();
        return view('role-permission.user.edit', compact('user', 'roles', 'userRoles'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',

            'password' => 'nullable|string|min:8|max:20',
            'roles' => 'required'
        ]);
        $data = [
            'name' => $request->name,
            'email' => $request->email,
        ];
        if (!empty($request->password)) {
            $data += ['password' => Hash::make($request->password),];
        }
        $user->update($data);
        $user->syncRoles($request->roles);
        return redirect('users')->with('status', 'Usuario actualizado exitosamente');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(User $user)
    {
        $deleted = UserDestino::where('user_id', $user->id)->delete();
        $user->delete();
        return redirect('users')->with('status', 'Usuario ' . $user->name . ' eliminado exitosamente');
    }
}
