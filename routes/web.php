<?php

use App\Http\Controllers\CertificadoController;
use App\Http\Controllers\CiudadElectoralController;
use App\Http\Controllers\ConfiguracionMontoController;
use App\Http\Controllers\DirigenteController;
use App\Http\Controllers\DuplicadosEntreSistemasController;
use App\Http\Controllers\EfectividadController;
use App\Http\Controllers\EquipoController;
use App\Http\Controllers\MiembroDeMesaController;
use App\Http\Controllers\PadronCoopController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProfilesController;
use App\Http\Controllers\PunteroController;
use App\Http\Controllers\ReportesController;
use App\Http\Controllers\RolesController;
use App\Http\Controllers\SistemaController;
use App\Http\Controllers\UserAdminController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\VehiculoController;
use App\Http\Controllers\VehiculoPunteroController;
use App\Http\Controllers\VotanteController;
use App\Http\Controllers\VotantesDuplicadosController;
use App\Http\Controllers\VotosController;
use Illuminate\Support\Facades\Route;

// Ruta para cargar votos (la que necesita tu link)
Route::get('/cargarvotos/{cedula_encriptada}', [VotosController::class, 'cargarVotos'])->name('cargar.votos');

// Padrón cooperativa - búsqueda pública sin autenticación
Route::get('/padron-coop', [PadronCoopController::class, 'index'])->name('padron-coop.index');
Route::get('/padron-coop/search', [PadronCoopController::class, 'search'])->name('padron-coop.search');


Route::prefix('votos')->name('votos.')->group(function () {
    Route::get('/cargar/{cedula_encriptada}', [VotosController::class, 'cargarVotos'])->name('cargar');
    Route::post('/buscar-por-cedula', [VotosController::class, 'buscarPorCedula'])->name('buscar.cedula');
    Route::post('/buscar-por-mesa-orden', [VotosController::class, 'buscarPorMesaYOrden'])->name('buscar.mesaorden');
    Route::post('/guardar', [VotosController::class, 'guardarVoto'])->name('guardar');
    Route::get('/estadisticas/{miembro_id}', [VotosController::class, 'estadisticasMesa'])->name('estadisticas');
    Route::delete('/eliminar/{id}', [VotosController::class, 'eliminarVoto'])->name('eliminar');
});



Route::get('/efectividad', [EfectividadController::class, 'index'])->name('efectividad.index');

Route::get('votante/buscador', [VotanteController::class, 'buscador']);
Route::get('/votantes/datatables', [VotanteController::class, 'datatables'])->name('votantes.datatables');
Route::post('/votante/buscar-simple', [VotanteController::class, 'buscarSimplePorCedula'])
    ->name('votante.buscar.simple');

Auth::routes();
Route::get('/home', [SistemaController::class, 'mostrarCiudades'])
    ->name('home')
    ->middleware('auth');

Route::get('mesas-entrada/data1', [MesaEntradaController::class, 'getData'])->name('recepcionadoData');
//acceden los autenticados


Route::middleware('auth')->group(function () {
    Route::get('/arbol', [SistemaController::class, 'mostrarArbol'])
        ->name('arbol');
    Route::get('punterosyvotantespordirigente/{equipo?}', [ReportesController::class, 'index'])
        ->name('punterosyvotantespordirigente');
    Route::resource('equipo', EquipoController::class);
    Route::resource('useradmin', UserAdminController::class);
    Route::post('sistema', [SistemaController::class, 'store'])->name('sistema.store');
    Route::delete('sistema/{id}', [SistemaController::class, 'destroy'])->name('sistema.destroy');

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
    Route::get('dirigente/{dirigente}/punteros/json', [DirigenteController::class, 'punteros'])->name('dirigente.punteros');
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
    Route::get('/vehiculosporsistema', [ReportesController::class, 'vehicporsis'])
        ->name('vehiculos.porsistema');
    Route::get('/porlocal', [ReportesController::class, 'porlocal'])->name('informe.porlocal');
    Route::get('/porlocal-data', [ReportesController::class, 'getPorlocalData'])->name('informe.porlocal.data');
    Route::get('/porlocal-detalle', [ReportesController::class, 'getDetalleEquipo'])->name('informe.porlocal.detalle');
    Route::get('/reportes/resultados-mesa', [ReportesController::class, 'resultadosMesa'])->name('reportes.resultados.mesa');
    Route::get('/reportes/resultados-mesa-data', [ReportesController::class, 'getResultadosMesaData'])->name('reportes.resultados.mesa.data');
    Route::get('/reportes/resultados-mesa-pdf', [ReportesController::class, 'exportarResultadosMesaPDF'])->name('reportes.resultados.mesa.pdf');
    Route::get('/reportes/carga-votos', [ReportesController::class, 'cargaVotos'])->name('reportes.carga-votos');
    Route::get('/reportes/carga-votos-data', [ReportesController::class, 'getCargaVotosData'])->name('reportes.carga-votos.data');
    Route::get('/reportes/carga-votos-detalle', [ReportesController::class, 'getCargaVotosDetalle'])->name('reportes.carga-votos.detalle');
    Route::resource('vehiculo', VehiculoController::class);
    Route::get('/vehiculos/contrato/{vehiculo}', [VehiculoController::class, 'generarContratoPDF'])
        ->name('vehiculo.contrato');
    Route::put('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'actualizarPunteros'])
        ->name('vehiculo.punteros.update');
    // Traer punteros y asignados de un vehículo
    Route::get('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'punteros'])->name('vehiculos.punteros');

    // Guardar asignaciones
    Route::post('/vehiculos/punteros/guardar', [VehiculoController::class, 'guardarPunteros'])->name('vehiculos.punteros.guardar');
    Route::get('/vehiculos/{equipo}/punteroslistar', [VehiculoController::class, 'punteros']);
    Route::post('/vehiculos/{vehiculo}/punteros', [VehiculoController::class, 'asignarPuntero']);
    Route::delete('/vehiculos/{vehiculo}/punteros/{puntero}', [VehiculoController::class, 'quitarPuntero']);
    Route::get('dirigente/data', [DirigenteController::class, 'data'])->name('dirigente.data');
    // Para modal: traer punteros de un vehículo según su equipo
    Route::get('/vehiculosasignar/{vehiculo}/punteros', [VehiculoController::class, 'punteros'])
        ->name('vehiculo.punteros')
        ->middleware('auth');
    // Guardar asignación de puntero a vehículo
    Route::post('/vehiculos/{vehiculo}/punteros/{puntero}', [VehiculoController::class, 'asignarPuntero'])
        ->name('vehiculo.puntero.asignar')
        ->middleware('auth');
    Route::delete('/vehiculos/{vehiculo}/punteros/{puntero}', [VehiculoController::class, 'quitarPuntero'])
        ->name('vehiculo.puntero.quitar')
        ->middleware('auth');

    Route::get(
        'reportes/vehiculos-equipo/{equipo}',
        [ReportesController::class, 'vehiculosPorEquipo']
    )->name('reportes.vehiculos.equipo');
    Route::get('/reportestotalesporsistema', [ReportesController::class, 'totalesporSistema'])->name('reportes.totalesporSistema');
    // Rutas para Miembros de Mesa - COMPLETAS
    Route::get('miembros-de-mesa', [MiembroDeMesaController::class, 'index'])
        ->name('miembros-de-mesa.index');

    Route::get('miembros-de-mesa/create/{equipoId?}', [MiembroDeMesaController::class, 'createWithEquipo'])
        ->name('miembros-de-mesa.create');

    Route::post('miembros-de-mesa', [MiembroDeMesaController::class, 'store'])
        ->name('miembros-de-mesa.store');

    // Ruta AJAX para obtener cantmesa por equipo
    Route::get('miembros-de-mesa/cantmesa/{equipoId}', [MiembroDeMesaController::class, 'getCantmesaByEquipo'])
        ->name('miembros-de-mesa.cantmesa');

    // Ruta para mostrar un miembro específico (para editar)
    Route::get('miembros-de-mesa/{id}', [MiembroDeMesaController::class, 'show'])
        ->name('miembros-de-mesa.show');

    // Ruta para actualizar un miembro
    Route::put('miembros-de-mesa/{id}', [MiembroDeMesaController::class, 'update'])
        ->name('miembros-de-mesa.update');

    Route::delete('miembros-de-mesa/{id}', [MiembroDeMesaController::class, 'destroy'])
        ->name('miembros-de-mesa.destroy');

    // Ruta para obtener miembros por equipo (AJAX)
    Route::get('miembros-de-mesa/get-by-equipo/{equipoId}', [MiembroDeMesaController::class, 'getByEquipo'])
        ->name('miembros-de-mesa.getByEquipo');
    Route::get('/configuracion-montos', [ConfiguracionMontoController::class, 'index'])
        ->name('configuracion_montos.index');
    Route::post('/configuracion-montos/actualizar', [ConfiguracionMontoController::class, 'store'])
        ->name('configuracion_montos.store');
    Route::get('configuracion_montos/reporte', [ConfiguracionMontoController::class, 'reporteGeneral'])
        ->name('configuracion_montos.reporte')
        ->middleware(['auth', 'permission:Administracion General']);
    Route::resource('ciudades_electorales', CiudadElectoralController::class);

    Route::get('certificados', [CertificadoController::class, 'index'])->name('certificados.index');
    Route::delete('certificados/{id}', [CertificadoController::class, 'destroy'])->name('certificados.destroy');
    Route::get('certificados/candidatos', [CertificadoController::class, 'getCandidatos'])->name('certificados.candidatos');
    Route::get('certificados/data', [CertificadoController::class, 'data'])->name('certificados.data');
    Route::get('certificados/locales', [CertificadoController::class, 'getLocales'])->name('certificados.locales');
    Route::get('certificados/mesas', [CertificadoController::class, 'getMesas'])->name('certificados.mesas');
    Route::get('certificados/formulario', [CertificadoController::class, 'getFormularioCarga'])->name('certificados.formulario');
    Route::post('certificados/guardar', [CertificadoController::class, 'guardarResultados'])->name('certificados.guardar');
    Route::put('certificados/{id}', [CertificadoController::class, 'update'])->name('certificados.update');
    Route::get('certificados/exportar-pdf', [CertificadoController::class, 'exportPdf'])->name('certificados.exportar.pdf');

    Route::get('/ciudades', [SistemaController::class, 'mostrarCiudades'])
        ->name('ciudades.index'); // opcional según tu sistema de autenticación
    Route::get('/distritos/{idCiudad}/sistemas', [SistemaController::class, 'sistemasPorDistrito'])
        ->name('distritos.sistemas');
    Route::get('/sistemas/{sistema}/dirigentes', [DirigenteController::class, 'dirigentesPorSistema'])
        ->name('sistemas.dirigentes');
    Route::post('/dirigentes/ajax', [DirigenteController::class, 'storeAjax'])->name('dirigentes.store.ajax');
    Route::delete('/dirigentes/ajax/{id}', [DirigenteController::class, 'destroyAjax'])->name('dirigentes.destroy.ajax');
    Route::get('/dirigente/{dirigente}/punteros/count', [DirigenteController::class, 'getPunterosCount'])->name('dirigente.punteros.count');
    Route::post('/punteros/store-ajax', [PunteroController::class, 'storeAjax'])->name('puntero.store.ajax');
    Route::delete('/punteros/destroy-ajax', [PunteroController::class, 'destroyAjax'])->name('puntero.destroy.ajax');
    // Rutas para punteros (AJAX y filtros)
    Route::prefix('punteros')->name('puntero.')->group(function () {
        Route::get('/filtrar', [PunteroController::class, 'filtrarAjax'])->name('filtrar.ajax');
        Route::delete('/destroy-ajax', [PunteroController::class, 'destroyAjax'])->name('destroy.ajax');
        Route::post('/store-ajax', [PunteroController::class, 'storeAjax'])->name('store.ajax');
    });
    // En tu archivo web.php, dentro del grupo middleware('auth')
    // Ruta única para buscar personas en el padrón (reutilizable)
    Route::get('/buscar-personas-padron', [PunteroController::class, 'buscarPersonas'])
        ->name('buscar.personas.padron')
        ->middleware('auth');
    // Ruta para obtener punteros por sistema
    Route::get('/sistemas/{sistema}/punteros', [PunteroController::class, 'porSistema'])->name('sistemas.punteros');
    Route::get('/dirigente/{dirigente}/punteros', [PunteroController::class, 'porDirigente'])->name('dirigentes.punteros');
    Route::get('/equipo/{equipo}/punteros', [PunteroController::class, 'porEquipo'])->name('equipo.punteros');
    // Agrega esta línea junto a las otras rutas AJAX de votdantes
    Route::post('/votante/store-ajax', [VotanteController::class, 'storeAjax'])->name('votante.store.ajax');
    Route::put('/votante/{id}/observacion', [VotanteController::class, 'updateObservacion'])->name('votante.updateObservacion');
    // Ruta para crear vehículo desde el modal del puntero

    Route::get('/puntero/{id}/vehiculos', [PunteroController::class, 'getVehiculos']);
    Route::post('/vehiculo/from-puntero', [VehiculoController::class, 'storeFromPuntero'])
        ->name('vehiculo.store.from.puntero');
    // Ruta para desvincular vehículo de un puntero
    Route::delete('/vehiculo/{vehiculoId}/puntero/{punteroId}', [VehiculoController::class, 'desvincularPuntero']);
    // Reporte de votantes duplicados

    // Reporte de votantes duplicados
    Route::get('reportes/votantes-duplicados', [VotantesDuplicadosController::class, 'index'])
        ->name('reportes.votantes.duplicados');

    Route::get('reportes/votantes-duplicados/detalle', [VotantesDuplicadosController::class, 'detalleVotante'])
        ->name('reportes.votantes.duplicados.detalle');

    Route::get('reportes/votantes-duplicados/exportar', [VotantesDuplicadosController::class, 'exportarExcel'])
        ->name('reportes.votantes.duplicados.exportar');

    Route::get('reportes/duplicados-entre-sistemas', [DuplicadosEntreSistemasController::class, 'index'])
        ->name('reportes.duplicados.entre.sistemas');

    Route::get('reportes/duplicados-entre-sistemas/pdf', [DuplicadosEntreSistemasController::class, 'exportarPDF'])
        ->name('reportes.duplicados.entre.sistemas.pdf');

    Route::get('reportes/votantes-duplicados-interno', [VotantesDuplicadosController::class, 'indexInterno'])
        ->name('reportes.votantes.duplicados.interno');

    Route::get('reportes/votantes-duplicados-interno/pdf', [VotantesDuplicadosController::class, 'exportarPDFInterno'])
        ->name('reportes.votantes.duplicados.interno.pdf');
    // Dentro del grupo Route::middleware('auth')->group(), busca donde están las rutas de punteros y agrega:

    // Rutas AJAX para editar puntero
    Route::get('/puntero/{id}/editar-ajax', [PunteroController::class, 'editAjax'])->name('puntero.editar.ajax');
    Route::put('/puntero/{id}', [PunteroController::class, 'updateAjax'])->name('puntero.update.ajax');
    // routes/web.php
Route::get('/manual/carga-votos', function () {
    $path = public_path('manuales/manual_carga_votos.png');
    
    if (!file_exists($path)) {
        // Imagen por defecto o mensaje
        return response()->json(['error' => 'Manual no disponible'], 404);
    }
    
    return response()->file($path, [
        'Content-Type' => 'image/png',
        'Cache-Control' => 'public, max-age=86400'
    ]);
})->name('manual.carga');
});
