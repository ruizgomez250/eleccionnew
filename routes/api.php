<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PrePadronController;
use App\Http\Controllers\Api\VotoController;

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
});

// =============================================
// RUTAS PROTEGIDAS (requieren autenticación)
// =============================================
Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });
    
    // Si quieres proteger las rutas de votos, muévelas dentro de este grupo
    // y agrega el middleware 'auth:sanctum' al grupo de v1
});
