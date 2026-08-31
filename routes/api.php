<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Api\PrePadronController;
use App\Http\Controllers\Api\VotoController;
use App\Http\Controllers\Api\VisitaPunteroApiController;
use App\Http\Controllers\Api\UserApiController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// =============================================
// TEST DE CONEXIÓN
// =============================================
Route::get('/ping', function () {
    $db = DB::select('SELECT 1');
    $userCount = \App\Models\User::count();

    return response()->json([
        'success' => true,
        'message' => 'Conexión exitosa',
        'servidor' => [
            'php_version' => phpversion(),
            'laravel_version' => app()->version(),
            'entorno' => config('app.env'),
            'url' => config('app.url'),
            'hora_servidor' => now()->toDateTimeString(),
            'timezone' => config('app.timezone'),
        ],
        'base_datos' => [
            'conexion' => count($db) > 0 ? 'OK' : 'FALLA',
            'driver' => config('database.default'),
            'usuarios_registrados' => $userCount,
        ],
    ]);
})->name('api.ping');

// =============================================
// RUTAS EXISTENTES (Prepádron)
// =============================================
Route::get('/prepadron/{cedula}', [PrePadronController::class, 'buscarPorCedula']);

// =============================================
// NUEVAS RUTAS PARA EL SISTEMA DE VOTOS (Luque)
// =============================================
Route::prefix('v1')->group(function () {
    
    // === Rutas principales para la APK ===
    Route::post('/cargar-resultados-mesa', [VotoController::class, 'cargarResultadosMesa']);
    Route::post('/cargar-resultados-mesa/json', [VotoController::class, 'cargarResultadosMesaJson']);
    
    // === Rutas para consultar resultados ===
    Route::get('/resultados-generales', [VotoController::class, 'resultadosGenerales']);
    Route::get('/resultados-por-cargo/{cargo}', [VotoController::class, 'resultadosPorCargo']);
    Route::get('/resultados-mesa/{codigoMesa}', [VotoController::class, 'resultadosPorMesa']);
    Route::get('/mesas', [VotoController::class, 'listarMesas']);
    
    // === Rutas para partidos y candidatos ===
    Route::get('/partidos', [VotoController::class, 'listarPartidos']);
    Route::get('/candidatos/{partidoId}/{cargo}', [VotoController::class, 'listarCandidatosPorPartido']);
    
    // === Ruta para estadísticas en tiempo real ===
    Route::get('/estadisticas', [VotoController::class, 'estadisticasGenerales']);
});

// =============================================
// RUTAS PARA ANÁLISIS DE EFECTIVIDAD ELECTORAL
// =============================================
Route::prefix('efectividad')->group(function () {
    Route::get('/resumen', [App\Http\Controllers\EfectividadController::class, 'resumen']);
    Route::get('/mesa/{id}', [App\Http\Controllers\EfectividadController::class, 'mesa']);
    Route::get('/ranking', [App\Http\Controllers\EfectividadController::class, 'ranking']);
    Route::get('/comparar', [App\Http\Controllers\EfectividadController::class, 'comparar']);
    Route::get('/candidatos', [App\Http\Controllers\EfectividadController::class, 'candidatos']);
    Route::get('/arrastre', [App\Http\Controllers\EfectividadController::class, 'arrastre']);
    Route::get('/intendentes', [App\Http\Controllers\EfectividadController::class, 'intendentes']);
    Route::get('/arrastre-comite', [App\Http\Controllers\EfectividadController::class, 'arrastreComite']);
    Route::get('/arrastre-completo', [App\Http\Controllers\EfectividadController::class, 'arrastreCompleto']);
});

// =============================================
// RUTAS DE AUTENTICACIÓN (Login)
// =============================================
Route::post('/login', [UserApiController::class, 'login']);

// =============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// =============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    // Logout
    Route::post('/logout', [UserApiController::class, 'logout']);

    // === Rutas de Usuarios API (para APK) ===
    Route::prefix('v1')->group(function () {
        Route::get('/usuarios', [UserApiController::class, 'index']);
        Route::post('/usuarios', [UserApiController::class, 'store']);
        Route::get('/usuarios/{id}', [UserApiController::class, 'show']);
        Route::put('/usuarios/{id}', [UserApiController::class, 'update']);
        Route::delete('/usuarios/{id}', [UserApiController::class, 'destroy']);
        Route::get('/sistemas', [UserApiController::class, 'sistemas']);
        Route::get('/roles', [UserApiController::class, 'roles']);
    });

    // Rutas de Visitas de Punteros
    Route::prefix('v1')->group(function () {
        Route::get('/visitas', [VisitaPunteroApiController::class, 'index']);
        Route::get('/mis-punteros', [VisitaPunteroApiController::class, 'misPunteros']);
        Route::post('/visitas', [VisitaPunteroApiController::class, 'store']);
        Route::get('/visitas/estadisticas', [VisitaPunteroApiController::class, 'estadisticas']);
        Route::get('/visitas/por-puntero/{id}', [VisitaPunteroApiController::class, 'porPuntero']);
        Route::get('/visitas/{id}', [VisitaPunteroApiController::class, 'show']);
        Route::put('/visitas/{id}', [VisitaPunteroApiController::class, 'update']);
        Route::delete('/visitas/{id}', [VisitaPunteroApiController::class, 'destroy']);
    });
});
