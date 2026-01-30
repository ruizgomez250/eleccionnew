<?php


use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MesaEntradaController;
use App\Http\Controllers\DirigenteController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\PunteroController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VotanteController;



Route::get('votante/buscador', [VotanteController::class, 'buscador']);
Route::get('/votantes/datatables', [VotanteController::class, 'datatables'])->name('votantes.datatables');
Auth::routes();
Route::get('/home', [EquipoController::class, 'index'])
    ->name('home')
    ->middleware('auth');

Route::get('mesas-entrada/data1', [MesaEntradaController::class, 'getData'])->name('recepcionadoData');
//acceden los autenticados


Route::middleware('auth')->group(function () {
    Route::get('punterosyvotantespordirigente', [ReportesController::class, 'index'])
        ->name('punterosyvotantespordirigente');
    Route::resource('equipo', EquipoController::class);
    // Rutas RESTful estándar: index, store, show, edit, update, destroy

    // Ruta para crear un dirigente vinculado a un equipo
    // (botón "Agregar Dirigente" en la vista de equipos)
    Route::get('dirigente/create/{equipo?}', [DirigenteController::class, 'createWithEquipo'])
        ->name('dirigente.createWithEquipo');


    // Ruta para almacenar el dirigente creado desde la vista con equipo
    Route::post('dirigente/store', [DirigenteController::class, 'store'])->name('dirigente.store');

    // Opcional: si querés listar dirigentes de un equipo específico
    Route::get('dirigente/equipo/{equipo}', [DirigenteController::class, 'indexByEquipo'])
        ->name('dirigente.indexByEquipo');
    Route::get('dirigente', [DirigenteController::class, 'index'])->name('dirigente.index'); // Datatable
    Route::get('dirigente/create', [DirigenteController::class, 'create'])->name('dirigente.create'); // Form Agregar
    Route::post('dirigente/store', [DirigenteController::class, 'store'])->name('dirigente.store'); // Guardar
    Route::get('dirigente/{dirigente}/punteros', [DirigenteController::class, 'punteros'])->name('dirigente.punteros');
    Route::delete('/dirigente/{id}', [DirigenteController::class, 'destroy'])
        ->name('dirigente.destroy');
    Route::get('puntero/createp/{equipo?}', [PunteroController::class, 'createWithDirigente'])
        ->name('puntero.createWithDirigente');




    // Ruta para crear un puntero vinculado a un equipo
    // (botón "Agregar Puntero" en la vista de equipos)
    Route::get('puntero/create/{equipo?}', [PunteroController::class, 'create'])
        ->name('puntero.createWithEquipo');


    // Ruta para almacenar el puntero creado
    Route::post('puntero/store', [PunteroController::class, 'store'])->name('puntero.store');
    // Ruta para eliminar un puntero

    Route::delete('/puntero/destroy/{id}', [PunteroController::class, 'destroy'])
        ->name('puntero.destroy');

    // Opcional: listar punteros de un equipo específico
    Route::get('puntero/equipo/{equipo}', [PunteroController::class, 'indexByEquipo'])
        ->name('puntero.indexByEquipo');
    Route::get('puntero/{idpuntero}/votantes', [VotanteController::class, 'votantespuntero'])
        ->name('puntero.votantespuntero');
    Route::delete('/votante/delete/{id}', [VotanteController::class, 'destroy'])
        ->name('votante.destroy');
    Route::get('dirigente/buscar-por-cedula/{cedula}', [DirigenteController::class, 'buscarPorCedula'])
        ->name('dirigente.buscarPorCedula');
    Route::get('dirigente/buscar-por-cedulap/{cedula}', [DirigenteController::class, 'buscarPorCedula'])
        ->name('dirigente.buscarPorCedulap');
    Route::get('votante/buscar-por-cedula/{cedula}', [VotanteController::class, 'buscarPorCedula'])
        ->name('votante.buscarPorCedula');
    Route::resource('votante', VotanteController::class)
        ->except(['destroy']);
    Route::get('/profile', [ProfileController::class, 'show'])->name('profile.show');
    Route::get('/profile/edit', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/profiles', [ProfilesController::class, 'index'])->name('profiles');
    Route::resource('users', UserController::class);
    Route::resource('roles', RolesController::class);
    Route::get('roles/{role}/give-permissions', [RolesController::class, 'addPermissionToRole'])->name('roles.addpermissionrole');
    Route::put('roles/{role}/give-permissions', [RolesController::class, 'givePermissionToRole'])->name('roles.updatepermissionrole');
    Route::resource('permissions', PermissionController::class);
    Route::get('roles/{role}/give-permissions', [RolesController::class, 'addPermissionToRole'])->name('roles.addpermissionrole');
    Route::put('roles/{role}/give-permissions', [RolesController::class, 'givePermissionToRole'])->name('roles.updatepermissionrole');
    // web.php
    Route::get('dirigentes/data', [DirigenteController::class, 'data'])->name('dirigentes.data');

    Route::get('/votantespordirigente/{id}', [ReportesController::class, 'votantesPorDirigente'])
        ->name('votantes.por.dirigente');
    Route::resource('vehiculo', VehiculoController::class);
    Route::get('/vehiculos/contrato/{vehiculo}', [VehiculoController::class, 'generarContratoPDF'])
        ->name('vehiculo.contrato');
    Route::put('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'actualizarPunteros'])
        ->name('vehiculo.punteros.update');
    // Traer punteros y asignados de un vehículo
    Route::get('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'punteros'])->name('vehiculos.punteros');

    // Guardar asignaciones
    Route::post('/vehiculos/punteros/guardar', [VehiculoController::class, 'guardarPunteros'])->name('vehiculos.punteros.guardar');
    Route::get('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'punteros']);
    Route::post('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'asignarPuntero']);
    Route::delete('/vehiculos/{vehiculo}/punteros/{puntero}', [VehiculoController::class, 'quitarPuntero']);
});
