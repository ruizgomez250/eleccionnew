<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Sistema;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Models\Role;

class UserApiController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:sanctum')->except('login');
    }

    /**
     * Listar usuarios (solo admin id 1 y 4 ven todos, el resto ve los de su sistema)
     */
    public function index(Request $request)
    {
        try {
            $user = $request->user();
            $query = User::with('sistemaRelacion');

            if (!in_array($user->id, [1, 4])) {
                $query->where('idusuario', $user->id);
            }

            if ($request->filled('search')) {
                $search = $request->search;
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%")
                      ->orWhere('email', 'LIKE', "%{$search}%");
                });
            }

            if ($request->filled('sistema')) {
                $query->where('sistema', $request->sistema);
            }

            $users = $query->orderBy('name')
                ->paginate($request->get('per_page', 25));

            $users->getCollection()->transform(function ($u) {
                $u->roles_names = $u->getRoleNames();
                return $u;
            });

            return response()->json([
                'success' => true,
                'data' => $users,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error index usuarios', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los usuarios',
            ], 500);
        }
    }

    /**
     * Crear un nuevo usuario
     */
    public function store(Request $request)
    {
        try {
            $userId = $request->user()->id;

            if (!in_array($userId, [1, 4])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para crear usuarios',
                ], 403);
            }

            $validated = $request->validate([
                'name'     => 'required|string|max:255',
                'email'    => 'required|email|unique:users,email',
                'password' => 'required|string|min:6',
                'sistema'  => 'nullable|integer|exists:sistemas,id',
                'roles'    => 'required|array',
                'roles.*'  => 'string|exists:roles,name',
            ]);

            $user = User::create([
                'name'      => $validated['name'],
                'email'     => $validated['email'],
                'password'  => Hash::make($validated['password']),
                'sistema'   => $validated['sistema'] ?? null,
                'idusuario' => $userId,
            ]);

            $user->syncRoles($validated['roles']);
            $user->load('sistemaRelacion');

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado correctamente',
                'data'    => $user,
            ], 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Error store usuario', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al crear el usuario',
            ], 500);
        }
    }

    /**
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        try {
            $user = User::with('sistemaRelacion')->findOrFail($id);
            $user->roles_names = $user->getRoleNames();

            return response()->json([
                'success' => true,
                'data'    => $user,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado',
            ], 404);
        }
    }

    /**
     * Actualizar un usuario
     */
    public function update(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;

            if (!in_array($userId, [1, 4])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para actualizar usuarios',
                ], 403);
            }

            $user = User::findOrFail($id);

            $validated = $request->validate([
                'name'     => 'sometimes|string|max:255',
                'email'    => 'sometimes|email|unique:users,email,' . $id,
                'password' => 'nullable|string|min:6',
                'sistema'  => 'nullable|integer|exists:sistemas,id',
                'roles'    => 'sometimes|array',
                'roles.*'  => 'string|exists:roles,name',
            ]);

            $data = collect($validated)->only(['name', 'email', 'sistema'])->toArray();

            if (!empty($validated['password'])) {
                $data['password'] = Hash::make($validated['password']);
            }

            $user->update($data);

            if (isset($validated['roles'])) {
                $user->syncRoles($validated['roles']);
            }

            $user->load('sistemaRelacion');
            $user->roles_names = $user->getRoleNames();

            return response()->json([
                'success' => true,
                'message' => 'Usuario actualizado correctamente',
                'data'    => $user,
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Error update usuario', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el usuario',
            ], 500);
        }
    }

    /**
     * Eliminar un usuario
     */
    public function destroy(Request $request, $id)
    {
        try {
            $userId = $request->user()->id;

            if (!in_array($userId, [1, 4])) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permisos para eliminar usuarios',
                ], 403);
            }

            if ($userId == $id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No puede eliminarse a sí mismo',
                ], 422);
            }

            $user = User::findOrFail($id);
            $user->delete();

            return response()->json([
                'success' => true,
                'message' => 'Usuario eliminado correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('API Error destroy usuario', ['id' => $id, 'error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el usuario',
            ], 500);
        }
    }

    /**
     * Listar sistemas disponibles
     */
    public function sistemas(Request $request)
    {
        try {
            $sistemas = Sistema::with('usuario', 'ciudad')
                ->orderBy('nombre')
                ->get();

            return response()->json([
                'success' => true,
                'data'    => $sistemas,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error sistemas', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los sistemas',
            ], 500);
        }
    }

    /**
     * Listar roles disponibles
     */
    public function roles()
    {
        try {
            $roles = Role::orderBy('name')->get(['id', 'name']);

            return response()->json([
                'success' => true,
                'data'    => $roles,
            ]);
        } catch (\Exception $e) {
            Log::error('API Error roles', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener los roles',
            ], 500);
        }
    }

    /**
     * Login para obtener token Sanctum
     */
    public function login(Request $request)
    {
        try {
            $request->merge([
                'email' => strtolower(trim((string) $request->input('email'))),
            ]);

            $request->validate([
                'email'    => 'required|email',
                'password' => 'required|string',
            ]);

            $user = User::where('email', $request->email)->first();

            Log::info('API Login attempt', [
                'email'       => $request->email,
                'user_found'  => $user ? true : false,
                'has_password'=> $user ? ($user->password ? 'yes' : 'no') : 'N/A',
                'hash_algo'   => $user && $user->password ? substr($user->password, 0, 7) : 'N/A',
            ]);

            if (!$user) {
                Log::warning('API Login: usuario no encontrado', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            $passwordValid = Hash::check($request->password, $user->password);
            Log::info('API Login password check', [
                'email'    => $request->email,
                'valid'    => $passwordValid,
            ]);

            if (!$passwordValid) {
                Log::warning('API Login: contraseña incorrecta', ['email' => $request->email]);
                return response()->json([
                    'success' => false,
                    'message' => 'Credenciales incorrectas',
                ], 401);
            }

            try {
                $token = $user->createToken('apk-token')->plainTextToken;
                Log::info('API Login: token creado', ['email' => $request->email]);
            } catch (\Exception $e) {
                Log::error('API Login: error creando token', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
                throw $e;
            }

            $user->load('sistemaRelacion');
            $user->roles_names = $user->getRoleNames();

            Log::info('API Login exitoso', ['email' => $request->email]);

            return response()->json([
                'success' => true,
                'message' => 'Login exitoso',
                // Se conservan estas claves para clientes APK anteriores.
                'user'    => $user,
                'token'   => $token,
                'data'    => [
                    'user'  => $user,
                    'token' => $token,
                ],
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error de validación',
                'errors'  => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            Log::error('API Error login', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al iniciar sesión',
            ], 500);
        }
    }

    /**
     * Cerrar sesión (revocar token)
     */
    public function logout(Request $request)
    {
        try {
            $request->user()->currentAccessToken()->delete();

            return response()->json([
                'success' => true,
                'message' => 'Sesión cerrada correctamente',
            ]);
        } catch (\Exception $e) {
            Log::error('API Error logout', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'message' => 'Error al cerrar sesión',
            ], 500);
        }
    }
}
