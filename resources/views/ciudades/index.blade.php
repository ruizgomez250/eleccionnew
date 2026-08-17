@extends('adminlte::page')

@section('title', 'Distritos')

@section('content_header')
    <div class="ua-header">
        <h1 class="ua-title">
            <i class="fas fa-map-marker-alt"></i> Distritos
        </h1>
        <p class="ua-subtitle">Resumen de afiliados, sistemas y estructura por distrito</p>
    </div>
@stop

@section('content')

    {{-- BUSCADOR DE DISTRITOS --}}
    <div class="card ua-card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text ua-input-icon">
                        <i class="fas fa-search"></i>
                    </span>
                </div>
                <input type="text" id="buscadorDistrito" class="form-control" placeholder="Buscar distrito por nombre...">
            </div>
        </div>
    </div>

    {{-- LISTA DISTRITOS --}}
    <div class="row" id="listaDistritos">
        @foreach ($totalesDistritos as $distrito => $totales)
            <div class="col-md-3 mb-3">
                <div class="card distrito-card h-100 shadow-sm"
                    style="cursor:pointer; transition: transform 0.2s;"
                    data-ciudad-id="{{ $totales['id_ciudad_electoral'] }}" data-distrito="{{ $distrito }}">
                    <div class="card-body text-center">
                        <div class="row mb-2 justify-content-center align-items-center">
                            <div class="col-12">
                                <h5 class="card-title font-weight-bold">
                                    <i class="fas fa-map-marker-alt fa-2x text-primary"></i> {{ $distrito }}
                                </h5>
                            </div>
                        </div>
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-user-tie text-danger"></i>
                                    <strong>Intendentes:</strong>
                                    <span class="badge badge-danger badge-pill">
                                        {{ number_format($totales['intendentes'] ?? 0, 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-users text-info"></i>
                                    <strong>Concejales:</strong>
                                    <span class="badge badge-info badge-pill">
                                        {{ number_format($totales['concejales'] ?? 0, 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-user-tie text-warning"></i>
                                    <strong>Dirigentes:</strong>
                                    <span class="badge badge-warning badge-pill">
                                        {{ number_format($totales['dirigentes'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-user-friends text-success"></i>
                                    <strong>Punteros:</strong>
                                    <span class="badge badge-success badge-pill">
                                        {{ number_format($totales['punteros'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-vote-yea text-primary"></i>
                                    <strong>Votantes:</strong>
                                    <span class="badge badge-primary badge-pill">
                                        {{ number_format($totales['votantes'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL SISTEMAS --}}
    <div class="modal fade" id="modalSistemas" tabindex="-1" role="dialog" aria-labelledby="modalSistemasTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSistemasTitle">
                        <i class="fas fa-map-marker-alt"></i> Sistemas del Distrito
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalSistemasBody">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                        <p>Selecciona un distrito para ver sus sistemas</p>
                    </div>
                </div>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i>Volver Atras
                </button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalDirigentes" tabindex="-1" role="dialog" aria-labelledby="tituloDirigentes"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="tituloDirigentes">
                        <i class="fas fa-users"></i> Dirigentes
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoDirigentes" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando dirigentes...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver Atras
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL PUNTEROS (lista completa) --}}
    <div class="modal fade" id="modalPunterosLista" tabindex="-1" role="dialog" aria-labelledby="tituloPunterosLista"
        aria-hidden="true">
        <div class="modal-dialog modal-xl" style="max-width: 98%; width: 98%; margin: 10px auto;">
            <div class="modal-content" style="height: 98vh; max-height: 98vh;">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="tituloPunterosLista">
                        <i class="fas fa-users"></i> Punteros
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoPunteros" style="overflow: auto; height: calc(98vh - 120px);">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Selecciona una opción para ver los punteros...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver Atras
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DE VEHÍCULOS DEL PUNTERO (CON PROPONENTE) --}}
    <div class="modal fade" id="modalVehiculosPuntero" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title">
                        <i class="fas fa-truck"></i> Vehículos del Puntero: <span id="vehiculo_puntero_nombre"></span>
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    {{-- FORMULARIO NUEVO VEHÍCULO --}}
                    <div class="card mb-3">
                        <div class="card-header bg-success text-white">
                            <i class="fas fa-plus-circle"></i> Nuevo Vehículo
                        </div>
                        <div class="card-body">
                            <form id="formCrearVehiculoPuntero">
                                @csrf
                                <input type="hidden" name="id_puntero" id="vehiculo_id_puntero">

                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Cédula del Chofer <span class="text-danger">*</span></label>
                                            <input type="text" name="cedulachofer" id="vehiculo_cedulachofer"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>Nombre del Chofer <span class="text-danger">*</span></label>
                                            <input type="text" name="nombre" id="vehiculo_nombre"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Chapa <span class="text-danger">*</span></label>
                                            <input type="text" name="chapa" id="vehiculo_chapa"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Tipo Vehículo</label>
                                            <select name="tipovehiculo" id="vehiculo_tipovehiculo" class="form-control">
                                                <option value="AUTOMOVIL">AUTOMÓVIL</option>
                                                <option value="CAMIONETA">CAMIONETA</option>
                                                <option value="FURGONETA">FURGONETA</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Capacidad</label>
                                            <input type="number" name="capacidad" id="vehiculo_capacidad"
                                                class="form-control" value="5">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono Principal <span class="text-danger">*</span></label>
                                            <input type="text" name="telefono1" id="vehiculo_telefono1"
                                                class="form-control" required>
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Teléfono Secundario</label>
                                            <input type="text" name="telefono2" id="vehiculo_telefono2"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Monto a Pagar (Gs.)</label>
                                            <select name="montopagar" id="vehiculo_montopagar" class="form-control">
                                                <option value="0">0</option>
                                                <option value="200000">200.000</option>
                                                <option value="300000" selected>300.000</option>
                                                <option value="350000">350.000</option>
                                                <option value="400000">400.000</option>
                                                <option value="450000">450.000</option>
                                                <option value="500000">500.000</option>
                                                <option value="550000">550.000</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>Cantidad Pagos</label>
                                            <input type="number" name="cantidadpagos" id="vehiculo_cantidadpagos"
                                                class="form-control" value="2">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>Dirección</label>
                                            <input type="text" name="direccion" id="vehiculo_direccion"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Barrio/Compañía</label>
                                            <input type="text" name="barriocompania" id="vehiculo_barriocompania"
                                                class="form-control">
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>Rol del Vehículo</label>
                                            <select name="rolvehiculo" id="vehiculo_rolvehiculo" class="form-control">
                                                <option value="PUNTERO">PUNTERO</option>
                                                <option value="LOGISTICA">LOGISTICA</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        {{-- Espacio vacío --}}
                                    </div>
                                </div>

                                {{-- SECCIÓN PROPONENTE --}}
                                <div class="row mt-4">
                                    <div class="col-12">
                                        <div class="card card-outline card-secondary">
                                            <div class="card-header bg-secondary text-white">
                                                <h6 class="card-title mb-0">
                                                    <i class="fas fa-user-check"></i> Datos del Proponente <span
                                                        class="text-danger">*</span>
                                                </h6>
                                            </div>
                                            <div class="card-body">
                                                <div class="row">
                                                    <div class="col-md-3">
                                                        <div class="form-group">
                                                            <label>Cédula del Proponente <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-id-card"></i></span>
                                                                </div>
                                                                <input type="text" name="cedulaproponente"
                                                                    id="vehiculo_cedulaproponente" class="form-control"
                                                                    placeholder="Ej: 1.234.567" required>
                                                            </div>
                                                            <small class="text-muted">Ingrese la cédula para buscar
                                                                automáticamente</small>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-5">
                                                        <div class="form-group">
                                                            <label>Nombre del Proponente <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-user-tie"></i></span>
                                                                </div>
                                                                <input type="text" name="nombreproponente"
                                                                    id="vehiculo_nombreproponente" class="form-control"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <div class="form-group">
                                                            <label>Teléfono del Proponente <span
                                                                    class="text-danger">*</span></label>
                                                            <div class="input-group">
                                                                <div class="input-group-prepend">
                                                                    <span class="input-group-text"><i
                                                                            class="fas fa-phone"></i></span>
                                                                </div>
                                                                <input type="text" name="telefonoproponente"
                                                                    id="vehiculo_telefonoproponente" class="form-control"
                                                                    required>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="row mt-3">
                                    <div class="col-md-12">
                                        <button type="submit" class="btn btn-primary w-100">
                                            <i class="fas fa-save"></i> Guardar Vehículo
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- TABLA DE VEHÍCULOS ASIGNADOS --}}
                    <div class="card">
                        <div class="card-header bg-secondary text-white">
                            <i class="fas fa-list"></i> Vehículos Asignados
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table id="vehiculos-puntero-table" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>Chapa</th>
                                            <th>Chofer</th>
                                            <th>Cédula</th>
                                            <th>Teléfono</th>
                                            <th>Rol</th>
                                            <th>Proponente</th>
                                            <th>Tel. Proponente</th>
                                            <th>Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td colspan="9" class="text-center">Seleccione un puntero</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i> Volver Atrás
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL VOTANTES --}}
    <div class="modal fade" id="modalVotantes" tabindex="-1" role="dialog" aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tituloVotantes">
                        <i class="fas fa-users"></i> Votantes del Puntero
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="p-4">
                    <form id="formAgregarVotante">
                        @csrf
                        <input type="hidden" name="idpuntero" id="votante_id_puntero">
                        <input type="hidden" name="idusuario" value="{{ auth()->id() }}">
                        <div class="row mb-2">
                            <div class="col-md-3">
                                <input name="cedula" id="votante_cedula" class="form-control" placeholder="Cédula"
                                    required>
                            </div>
                            <div class="col-md-5">
                                <input name="nombre" id="votante_nombre" class="form-control" placeholder="Nombre"
                                    required readonly>
                            </div>
                            <div class="col-md-4">
                                <select name="tipo_votante" class="form-control" id="tipo_votante">
                                    <option value="seguro" selected>Seguro</option>
                                    <option value="dudoso">Dudoso</option>
                                    <option value="solo visita">Solo Visita</option>
                                </select>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="direccion" id="direccion" class="form-control" placeholder="Dirección"
                                    readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="mesa" id="mesa" class="form-control" placeholder="Mesa" readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="orden" id="orden" class="form-control" placeholder="Orden" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="partido" id="partido" class="form-control" placeholder="Partido"
                                    readonly>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="escuela" id="escuela" class="form-control" placeholder="Escuela"
                                    readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="ciudad" id="ciudad" class="form-control" placeholder="Ciudad" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="departamento" id="departamento" class="form-control"
                                    placeholder="Departamento" readonly>
                            </div>
                        </div>
                        <div class="row mb-2">
                            <div class="col-md-12">
                                <textarea name="observacion" id="votante_observacion" class="form-control" placeholder="Observación (opcional)" rows="2" maxlength="500"></textarea>
                            </div>
                        </div>
                        <div class="row mt-3">
                            <div class="col-md-6">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-save"></i> Guardar Votante
                                </button>
                            </div>
                            <div class="col-md-6 text-right">
                                <button type="button" class="btn btn-danger ml-2" data-dismiss="modal">
                                    <i class="fas fa-arrow-left"></i> Volver a Punteros
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-body" id="contenidoVotantes">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando votantes...</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-danger" data-dismiss="modal">
                        <i class="fas fa-arrow-left"></i>Volver a Punteros
                    </button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .distrito-card:hover {
            transform: scale(1.03) !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .modal-xl {
            max-width: 90%;
        }

        @media (min-width: 1200px) {
            .modal-xl {
                max-width: 1140px;
            }
        }
    </style>
    @include('useradmin._dark_pages')
@stop

@section('js')
    <script>
        // =============================================
        // FUNCIONES GENERALES Y DE DISTRITOS
        // =============================================
        $(document).ready(function() {
            mostrarBadgeNuevoManual();
            // === VOTANTES ===
            $('#formAgregarVotante').on('submit', function(e) {
                e.preventDefault();
                if (window.buscandoVotante) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Búsqueda en curso',
                        text: 'Espera a que termine la búsqueda del votante antes de guardar',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    return;
                }
                let formData = $(this).serialize();
                let submitBtn = $(this).find('button[type="submit"]');
                let nombrePuntero = $('#tituloVotantes').text().replace('Votantes del Puntero: ', '')
                    .trim();
                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Guardando...');
                $.ajax({
                    url: "{{ route('votante.store.ajax') }}",
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        Swal.fire({
                            icon: response.tipo_alerta || 'success',
                            title: response.tipo_alerta === 'warning' ? 'Aviso' : 'Éxito',
                            text: response.message,
                            timer: 3000,
                            showConfirmButton: false
                        });
                        limpiarFormularioVotante();
                        // Recargar votantes del puntero
                        setTimeout(() => {
                            let punteroId = $('#votante_id_puntero').val();
                            window.cargarVotantes(punteroId, nombrePuntero);
                        }, 100);
                        // Recargar lista de punteros si está visible (actualiza contadores)
                        if (typeof window.filtrarPunterosGeneral === 'function') {
                            window.filtrarPunterosGeneral();
                        }
                    },
                    error: function(xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message || 'Error al guardar'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Guardar Votante');
                    }
                });
            });

            function limpiarFormularioVotante() {
                $('#votante_cedula, #votante_nombre, #direccion, #mesa, #orden, #partido, #escuela, #ciudad, #departamento, #votante_observacion')
                    .val('');
                $('#tipo_votante').val('seguro');
                $('#votante_cedula').removeClass('is-invalid');
                $('#votante_cedula').focus();
            }

            // Buscar votante por cédula
            $('#votante_cedula').on('blur', function() {
                buscarVotantePorCedula();
            });
            $('#votante_cedula').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarVotantePorCedula();
                    $('#tipo_votante').focus();
                }
            });

            $('#modalVotantes').on('hidden.bs.modal', function() {
                limpiarFormularioVotante();
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#votantes-table')) {
                    $('#votantes-table').DataTable().destroy();
                }
            });

            // Efecto hover en tarjetas de distritos
            document.querySelectorAll('.distrito-card').forEach(card => {
                card.addEventListener('mouseover', () => card.style.transform = 'scale(1.03)');
                card.addEventListener('mouseout', () => card.style.transform = 'scale(1)');
            });

            // Buscador de distritos
            const buscador = document.getElementById('buscadorDistrito');
            if (buscador) {
                buscador.addEventListener('keyup', function() {
                    let query = this.value.toLowerCase().trim();
                    document.querySelectorAll('.distrito-card').forEach(card => {
                        let distrito = card.dataset.distrito.toLowerCase();
                        card.closest('.col-md-3').style.display = distrito.includes(query) ?
                            'block' : 'none';
                    });
                    const visibleCards = document.querySelectorAll('.distrito-card:visible').length;
                    const sinResultados = document.getElementById('sinResultadosDistritos');
                    if (visibleCards === 0) {
                        if (!sinResultados) {
                            const mensaje = document.createElement('div');
                            mensaje.id = 'sinResultadosDistritos';
                            mensaje.className = 'col-12 text-center text-muted py-5';
                            mensaje.innerHTML =
                                `<i class="fas fa-search fa-3x mb-3"></i><p>No se encontraron distritos que coincidan con "${query}"</p>`;
                            document.getElementById('listaDistritos').appendChild(mensaje);
                        }
                    } else if (sinResultados) {
                        sinResultados.remove();
                    }
                });
            }

            // Cargar sistemas al hacer clic en distrito
            document.querySelectorAll('.distrito-card').forEach(card => {
                card.addEventListener('click', function() {
                    let ciudadId = this.dataset.ciudadId;
                    let distritoNombre = this.dataset.distrito;
                    let modalBody = document.getElementById('modalSistemasBody');
                    let modalTitle = document.querySelector('#modalSistemas .modal-title');
                    if (modalTitle) modalTitle.innerHTML =
                        `<i class="fas fa-map-marker-alt"></i> Sistemas del Distrito: ${distritoNombre}`;
                    modalBody.innerHTML =
                        `<div class="text-center text-muted py-5"><div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Cargando...</span></div><p>Cargando sistemas del distrito ${distritoNombre}...</p></div>`;
                    $('#modalSistemas').modal('show');
                    let url = `{{ url('/') }}/distritos/${ciudadId}/sistemas`;
                    fetch(url, {
                            headers: {
                                'X-Requested-With': 'XMLHttpRequest',
                                'Accept': 'text/html'
                            }
                        })
                        .then(res => {
                            if (!res.ok) throw new Error('Error');
                            return res.text();
                        })
                        .then(html => {
                            modalBody.innerHTML = html;
                            setTimeout(function() {
                                if ($.fn.DataTable && $('#sistemas-table').length) {
                                    if ($.fn.DataTable.isDataTable('#sistemas-table'))
                                        $('#sistemas-table').DataTable().destroy();
                                    $('#sistemas-table').DataTable({
                                        dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                                        responsive: true,
                                        language: {
                                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                                            search: "Buscar sistema:",
                                            searchPlaceholder: "Nombre, ubicación..."
                                        },
                                        buttons: [{
                                            extend: 'print',
                                            text: '<i class="fas fa-print"></i> Imprimir',
                                            className: 'btn btn-secondary',
                                            autoPrint: true,
                                            title: 'Sistemas del Distrito',
                                            customize: function(win) {
                                                $(win.document.body)
                                                    .find('table')
                                                    .addClass(
                                                        'table table-bordered'
                                                    );
                                                $(win.document.body)
                                                    .find('h1').css(
                                                        'text-align',
                                                        'center');
                                            }
                                        }],
                                        pageLength: 10,
                                        lengthMenu: [
                                            [10, 25, 50, -1],
                                            [10, 25, 50, "Todos"]
                                        ]
                                    });
                                }
                            }, 100);
                        })
                        .catch(error => {
                            modalBody.innerHTML =
                                `<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>No se pudieron cargar los sistemas. Intente nuevamente.</p><p class="small text-muted">Error: ${error.message}</p><button class="btn btn-sm btn-primary" onclick="cargarSistemasManual(${ciudadId}, '${distritoNombre}')"><i class="fas fa-sync"></i> Reintentar</button></div>`;
                        });
                });
            });
        });

        function mostrarBadgeNuevoManual() {
            const yaVio = localStorage.getItem('manual_notification_seen');

            if (!yaVio || yaVio) {
                // Agregar badge en la barra de título o en un lugar visible
                const badgeHTML = `
            <div id="newManualBadge" 
                 style="display: inline-block; margin-left: 15px; cursor: pointer;"
                 onclick="abrirManualPNG(); document.getElementById('newManualBadge').style.display='none'; localStorage.setItem('manual_notification_seen', 'true');">
                <span style="background: #ff4757; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px; animation: pulse 1.5s infinite;">
                    <i class="fas fa-star"></i> ¡NUEVO! Se puede cargar los votos en tiempo real para compararlo con tus votantes
                </span>
            </div>
        `;

                // Agregar al final del content_header
                $('.content-header h1').first().after(badgeHTML);

                // Agregar animación CSS
                $('head').append(`
            <style>
                @keyframes pulse {
                    0% { transform: scale(1); opacity: 1; }
                    50% { transform: scale(1.05); opacity: 0.9; background: #ff6b81; }
                    100% { transform: scale(1); opacity: 1; }
                }
            </style>
        `);
            }
        }

        function abrirManualPNG() {
            // Abrir el manual en una ventana modal (recomendado)
            Swal.fire({
                title: '📘 MANUAL DE CARGA DE VOTOS',
                html: `
            <div style="max-height: 70vh; overflow-y: auto; padding: 10px;">
                <img src="{{ asset('manuales/manual_carga_votos.png') }}" 
                     alt="Manual de carga de votos"
                     style="width: 100%; height: auto; border-radius: 8px; cursor: pointer;"
                     onclick="window.open('{{ asset('manuales/manual_carga_votos.png') }}', '_blank')">
                <p class="text-muted mt-3 small">
                    <i class="fas fa-info-circle"></i> 
                    Haz clic en la imagen para verla en tamaño completo
                </p>
            </div>
        `,
                width: '90%',
                maxWidth: '900px',
                showConfirmButton: true,
                confirmButtonText: '<i class="fas fa-check"></i> Entendido',
                showCancelButton: true,
                cancelButtonText: 'Descargar',
                cancelButtonColor: '#28a745',
                preConfirm: () => {
                    localStorage.setItem('visto_manual_nuevo', 'true');
                }
            }).then((result) => {
                if (result.dismiss === Swal.DismissReason.cancel) {
                    // Descargar manual
                    const link = document.createElement('a');
                    link.href = '{{ asset('manuales/manual_carga_votos.png') }}';
                    link.download = 'manual_carga_votos.png';
                    link.click();
                }
            });
        }

        function cargarSistemasManual(ciudadId, distritoNombre) {
            let modalBody = document.getElementById('modalSistemasBody');
            modalBody.innerHTML =
                `<div class="text-center text-muted py-5"><div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Cargando...</span></div><p>Reintentando carga de sistemas...</p></div>`;
            let url = `{{ url('/') }}/distritos/${ciudadId}/sistemas`;
            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error');
                    return res.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    modalBody.innerHTML =
                        `<div class="text-center text-danger py-5"><i class="fas fa-exclamation-triangle fa-3x mb-3"></i><p>Error persistente. Verifica la conexión.</p></div>`;
                });
        }

        // Cargar dirigentes al hacer clic en botón
        $(document).on("click", ".btn-dirigentes", function() {
            let sistema = $(this).data("sistema");
            let nombre = $(this).data("nombre");
            $("#tituloDirigentes").html('<i class="fas fa-users"></i> Dirigentes del Sistema - ' + nombre);
            $("#contenidoDirigentes").html(
                '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando...</p></div>'
            );
            $("#modalDirigentes").modal("show");
            let url = `{{ url('/') }}/sistemas/${sistema}/dirigentes`;
            $.get(url, function(data) {
                $("#contenidoDirigentes").html(data);
                setTimeout(function() {
                    if ($.fn.DataTable && $('#dirigentes-table').length) {
                        if ($.fn.DataTable.isDataTable('#dirigentes-table')) $('#dirigentes-table')
                            .DataTable().destroy();
                        $('#dirigentes-table').DataTable({
                            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-5'i><'col-sm-7'p>>",
                            responsive: true,
                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                                search: "Buscar dirigente:",
                                searchPlaceholder: "Nombre, cédula o teléfono..."
                            },
                            buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Imprimir',
                                className: 'btn btn-secondary',
                                autoPrint: true,
                                customize: function(win) {
                                    $(win.document.body).find('table').addClass(
                                        'table table-bordered');
                                }
                            }],
                            pageLength: 10,
                            lengthMenu: [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, "Todos"]
                            ],
                            columnDefs: [{
                                orderable: false,
                                targets: [8]
                            }]
                        });
                    }
                }, 100);
            }).fail(function() {
                $("#contenidoDirigentes").html(
                    '<div class="alert alert-danger text-center p-4"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><p>Error cargando dirigentes. Intente nuevamente.</p></div>'
                );
            });
        });

        // =============================================
        // FUNCIONES DE PUNTEROS (para el modal de lista)
        // =============================================
        function abrirModalPunterosLista(sistemaId, nombreSistema) {
            $('#modalPunterosLista .modal-title').html(
                `<i class="fas fa-users"></i> Punteros del Sistema: ${nombreSistema}`);
            $("#contenidoPunteros").html(
                '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando punteros...</p></div>'
            );
            $("#modalPunterosLista").modal("show");
            let url = `{{ url('/') }}/sistemas/${sistemaId}/punteros`;
            $.get(url, function(data) {
                $("#contenidoPunteros").html(data);
            }).fail(function() {
                $("#contenidoPunteros").html(
                    '<div class="alert alert-danger text-center p-4"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><p>Error cargando punteros. Intente nuevamente.</p></div>'
                );
            });
        }

        function abrirModalPunterosListapordir(idDir, nombreSistema) {
            $('#modalPunterosLista .modal-title').html(
                `<i class="fas fa-users"></i> Punteros del Sistema: ${nombreSistema}`);
            $("#contenidoPunteros").html(
                '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando punteros...</p></div>'
            );
            $("#modalPunterosLista").modal("show");
            let url = `{{ url('/') }}/dirigente/${idDir}/punteros`;
            $.get(url, function(data) {
                $("#contenidoPunteros").html(data);
            }).fail(function() {
                $("#contenidoPunteros").html(
                    '<div class="alert alert-danger text-center p-4"><i class="fas fa-exclamation-circle fa-2x mb-3"></i><p>Error cargando punteros. Intente nuevamente.</p></div>'
                );
            });
        }

        $('#modalPunterosLista').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-lista-table')) {
                $('#punteros-lista-table').DataTable().destroy();
            }
        });

        // =============================================
        // FUNCIONES DE VEHÍCULOS (con Proponente)
        // =============================================
        window.abrirModalCrearVehiculo = function(punteroId, punteroNombre) {
            $('#vehiculo_id_puntero').val(punteroId);
            $('#vehiculo_puntero_nombre').text(punteroNombre);
            $('#formCrearVehiculoPuntero')[0].reset();
            $('#vehiculo_capacidad').val(5);
            $('#vehiculo_cantidadpagos').val(2);
            $('#vehiculo_montopagar').val('300000');
            $('#vehiculo_tipovehiculo').val('AUTOMOVIL');
            $('#vehiculo_rolvehiculo').val('PUNTERO');
            $('#vehiculo_cedulaproponente, #vehiculo_nombreproponente, #vehiculo_telefonoproponente').val('');
            $('#vehiculo_cedulaproponente').removeClass('is-valid is-invalid');
            cargarVehiculosPuntero(punteroId);
            setTimeout(function() {
                $('#vehiculo_cedulachofer').focus();
            }, 500);
            $('#modalVehiculosPuntero').modal('show');
        };

        window.cargarVehiculosPuntero = function(punteroId) {
            $('#vehiculos-puntero-table tbody').html(
                '<td><td colspan="9" class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando vehículos...</td></tr>'
            );
            $.get(`{{ url('/') }}/puntero/${punteroId}/vehiculos`, function(vehiculos) {
                let tbody = '';
                if (vehiculos.length === 0) {
                    tbody =
                        '<tr><td colspan="9" class="text-center">No hay vehículos asignados a este puntero</td></tr>';
                } else {
                    vehiculos.forEach((v, i) => {
                        tbody += `<tr>
                            <td>${i+1}</td>
                            <td>${v.chapa}</td>
                            <td>${v.nombre}</td>
                            <td>${v.cedulachofer}</td>
                            <td>${v.telefono1 || ''}</td>
                            <td><span class="badge badge-${v.rol === 'PUNTERO' ? 'primary' : 'secondary'}">${v.rol}</span></td>
                            <td>${v.nombreproponente || ''}</td>
                            <td>${v.telefonoproponente || ''}</td>
                            <td class="text-center">
                                <button class="btn btn-primary btn-sm" 
                                    onclick="generarPDFContratoVehicular(${v.id})"
                                    title="Contrato de Alquiler">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="eliminarVehiculoPuntero(${v.id})"><i class="fas fa-trash"></i>
                                </button>

                                </td>
                        </tr>`;
                    });
                }
                $('#vehiculos-puntero-table tbody').html(tbody);
                if ($.fn.DataTable && $('#vehiculos-puntero-table').length) {
                    if ($.fn.DataTable.isDataTable('#vehiculos-puntero-table')) $('#vehiculos-puntero-table')
                        .DataTable().destroy();
                    $('#vehiculos-puntero-table').DataTable({
                        responsive: true,
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        },
                        pageLength: 10,
                        destroy: true
                    });
                }
            }).fail(() => $('#vehiculos-puntero-table tbody').html(
                '<tr><td colspan="9" class="text-center text-danger">Error al cargar los vehículos</td></tr>'));
        };

        window.eliminarVehiculoPuntero = function(vehiculoId) {
            let punteroId = $('#vehiculo_id_puntero').val();
            Swal.fire({
                title: '¿Desvincular vehículo?',
                text: 'El vehículo dejará de estar asignado a este puntero',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, desvincular',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: `{{ url('/') }}/vehiculo/${vehiculoId}/puntero/${punteroId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Desvinculado',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });
                            cargarVehiculosPuntero(punteroId);
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'No se pudo desvincular el vehículo'
                            });
                        }
                    });
                }
            });
        };
        const BASE_URL = '{{ url('/') }}';

        function generarPDFContratoVehicular(id) {
            window.open(`${BASE_URL}/vehiculos/contrato/${id}`, '_blank');
        }
        // Búsqueda automática de chofer
        function buscarChoferPorCedula() {
            let cedula = $('#vehiculo_cedulachofer').val().trim();
            if (cedula.length < 3) return;
            $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#vehiculo_nombre').val(response.data.nombre ?? '');
                    $('#vehiculo_telefono1').val(response.data.telefono ?? '');
                    $('#vehiculo_telefono2').val(response.data.telefono2 ?? '');
                    $('#vehiculo_direccion').val(response.data.direccion ?? '');
                    $('#vehiculo_barriocompania').val(response.data.barrio ?? '');
                } else {
                    $('#vehiculo_nombre, #vehiculo_telefono1, #vehiculo_telefono2, #vehiculo_direccion, #vehiculo_barriocompania')
                        .val('');
                }
            }).fail(() => console.log('Error en búsqueda de chofer'));
        }

        // Búsqueda automática de proponente
        function buscarProponentePorCedula() {
            let cedula = $('#vehiculo_cedulaproponente').val().trim();
            if (cedula === '') {
                $('#vehiculo_nombreproponente, #vehiculo_telefonoproponente').val('');
                $('#vehiculo_cedulaproponente').removeClass('is-valid is-invalid');
                $('#vehiculo_nombreproponente').focus();
                return;
            }
            if (cedula.length < 3) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Cédula muy corta',
                    text: 'Ingrese al menos 3 dígitos',
                    timer: 2000,
                    showConfirmButton: false
                });
                $('#vehiculo_nombreproponente, #vehiculo_telefonoproponente').val('');
                $('#vehiculo_nombreproponente').focus();
                return;
            }
            $('#vehiculo_nombreproponente').val('Buscando...');
            $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#vehiculo_nombreproponente').val(response.data.nombre ?? '');
                    $('#vehiculo_telefonoproponente').val(response.data.telefono ?? '');
                    $('#vehiculo_cedulaproponente').removeClass('is-invalid').addClass('is-valid');
                    $('#vehiculo_nombreproponente').focus();

                } else {
                    $('#vehiculo_nombreproponente, #vehiculo_telefonoproponente').val('');
                    $('#vehiculo_cedulaproponente').removeClass('is-valid').addClass('is-invalid');
                    Swal.fire({
                        icon: 'error',
                        title: 'No encontrado',
                        text: `No se encontró un proponente con la cédula ${cedula}`
                    });
                    $('#vehiculo_nombreproponente').focus();
                }
            }).fail(function() {
                $('#vehiculo_nombreproponente, #vehiculo_telefonoproponente').val('');
                $('#vehiculo_cedulaproponente').removeClass('is-valid').addClass('is-invalid');

                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al buscar la cédula'
                });
                $('#vehiculo_nombreproponente').focus();
            });
        }

        function validarProponente() {
            let cedula = $('#vehiculo_cedulaproponente').val().trim();
            let nombre = $('#vehiculo_nombreproponente').val().trim();
            if (cedula === '') {
                Swal.fire({
                    icon: 'error',
                    title: 'Campo requerido',
                    text: 'Debe ingresar la cédula del proponente'
                });
                $('#vehiculo_cedulaproponente').focus();
                return false;
            }
            if (cedula.length < 3) {
                Swal.fire({
                    icon: 'error',
                    title: 'Cédula inválida',
                    text: 'La cédula debe tener al menos 3 dígitos'
                });
                $('#vehiculo_cedulaproponente').focus();
                return false;
            }
            if (nombre === '' || nombre === 'Buscando...') {
                Swal.fire({
                    icon: 'error',
                    title: 'Proponente no encontrado',
                    text: 'Debe buscar y seleccionar un proponente válido'
                });
                $('#vehiculo_cedulaproponente').focus();
                return false;
            }
            return true;
        }

        $(document).ready(function() {
            // Eventos de búsqueda en el modal de vehículos
            $(document).on('blur', '#vehiculo_cedulachofer', buscarChoferPorCedula);
            $(document).on('keypress', '#vehiculo_cedulachofer', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarChoferPorCedula();
                    $('#vehiculo_nombre').focus();
                }
            });
            $(document).on('blur', '#vehiculo_cedulaproponente', buscarProponentePorCedula);
            $(document).on('keypress', '#vehiculo_cedulaproponente', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarProponentePorCedula();
                }
            });
            $(document).on('keypress', '#vehiculo_nombreproponente', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#vehiculo_telefonoproponente').focus();;
                }
            });

            // Guardar vehículo
            $(document).on('submit', '#formCrearVehiculoPuntero', function(e) {
                e.preventDefault();
                if (!validarProponente()) return;
                let btnSubmit = $(this).find('button[type="submit"]');
                let punteroId = $('#vehiculo_id_puntero').val();
                btnSubmit.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Guardando...');
                let formData = {
                    nombre: $('#vehiculo_nombre').val(),
                    cedulachofer: $('#vehiculo_cedulachofer').val(),
                    chapa: $('#vehiculo_chapa').val(),
                    tipovehiculo: $('#vehiculo_tipovehiculo').val(),
                    capacidad: $('#vehiculo_capacidad').val(),
                    telefono1: $('#vehiculo_telefono1').val(),
                    telefono2: $('#vehiculo_telefono2').val(),
                    montopagar: $('#vehiculo_montopagar').val(),
                    cantidadpagos: $('#vehiculo_cantidadpagos').val(),
                    rolvehiculo: $('#vehiculo_rolvehiculo').val(),
                    direccion: $('#vehiculo_direccion').val(),
                    barriocompania: $('#vehiculo_barriocompania').val(),
                    cedulaproponente: $('#vehiculo_cedulaproponente').val(),
                    nombreproponente: $('#vehiculo_nombreproponente').val(),
                    telefonoproponente: $('#vehiculo_telefonoproponente').val(),
                    id_puntero: punteroId,
                    _token: '{{ csrf_token() }}'
                };
                $.ajax({
                    url: "{{ route('vehiculo.store.from.puntero') }}",
                    type: 'POST',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: response.message,
                            timer: 1500,
                            showConfirmButton: false
                        });
                        $('#formCrearVehiculoPuntero')[0].reset();
                        $('#vehiculo_capacidad').val(5);
                        $('#vehiculo_cantidadpagos').val(2);
                        $('#vehiculo_montopagar').val('300000');
                        $('#vehiculo_tipovehiculo').val('AUTOMOVIL');
                        $('#vehiculo_rolvehiculo').val('PUNTERO');
                        $('#vehiculo_cedulaproponente, #vehiculo_nombreproponente, #vehiculo_telefonoproponente')
                            .val('');
                        $('#vehiculo_cedulaproponente').removeClass('is-valid is-invalid');
                        $('#vehiculo_cedulachofer').focus();
                        cargarVehiculosPuntero(punteroId);
                    },
                    error: function(xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            let errorMessage = '';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += `${key}: ${value[0]}\n`;
                            });
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                text: errorMessage
                            });
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Error al crear el vehículo'
                            });
                        }
                    },
                    complete: function() {
                        btnSubmit.prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Guardar Vehículo');
                    }
                });
            });
        });

        // =============================================
        // FUNCIONES DE VOTANTES (globales)
        // =============================================
        function buscarVotantePorCedula() {
            let cedula = $('#votante_cedula').val().trim();
            if (cedula.length < 3) {
                if (cedula.length > 0 && cedula.length < 3) {
                    Swal.fire({
                        icon: 'info',
                        title: 'Cédula muy corta',
                        text: 'Ingresa al menos 3 dígitos para buscar',
                        timer: 2000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                }
                return;
            }
            window.buscandoVotante = true;
            $('#votante_nombre').val('Buscando...');
            $('#formAgregarVotante button[type="submit"]').prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Buscando...');
            $.get("{{ url('votante/buscar-por-cedula') }}/" + cedula, function(response) {
                if (!response.encontrado) {
                    limpiarCamposVotante();
                    Swal.fire({
                        icon: 'info',
                        title: 'Votante no encontrado',
                        text: `No se encontró ningún votante con la cédula ${cedula}`,
                        timer: 3000,
                        showConfirmButton: false,
                        toast: true,
                        position: 'top-end'
                    });
                    return;
                }
                let v = response.data;
                $('#votante_nombre').val(v.nombre);
                $('#direccion').val(v.direccion || '');
                $('#mesa').val(v.mesa || '');
                $('#orden').val(v.orden || '');
                $('#partido').val(v.partido || '');
                $('#escuela').val(v.escuela || '');
                $('#ciudad').val(v.ciudad || '');
                $('#departamento').val(v.departamento || '');
                Swal.fire({
                    icon: 'success',
                    title: 'Votante encontrado',
                    text: `Nombre: ${v.nombre}`,
                    timer: 2000,
                    showConfirmButton: false,
                    toast: true,
                    position: 'top-end'
                });
            }).fail(function() {
                limpiarCamposVotante();
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Error al buscar la cédula. Intente nuevamente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }).always(function() {
                window.buscandoVotante = false;
                $('#formAgregarVotante button[type="submit"]').prop('disabled', false).html('<i class="fas fa-save"></i> Guardar Votante');
            });
        }

        function limpiarCamposVotante() {
            $('#votante_nombre, #direccion, #mesa, #orden, #partido, #escuela, #ciudad, #departamento').val('');
        }

        window.cargarVotantes = function(idPuntero, nombrePuntero = '') {
            $('#tituloVotantes').html(`<i class="fas fa-users"></i> Votantes del Puntero: ${nombrePuntero}`);
            $('#votante_id_puntero').val(idPuntero);
            $('#modalVotantes').modal('show');
            $('#contenidoVotantes').html(
                `<div class="text-center p-4"><div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;"><span class="sr-only">Cargando...</span></div><p class="mt-2">Cargando votantes del puntero...</p></div>`
            );

            let url = `{{ url('puntero') }}/${idPuntero}/votantes`;
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(data) {
                    // Construir tabla - AHORA CON 6 COLUMNAS (sin Tipo Votante)
                    let contenido = `
                <div class="table-responsive">
                    <table id="votantes-table" class="table table-striped table-bordered" style="width:100%">
                        <thead>
                            <tr>
                                <th style="width:5%">#</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Escuela</th>
                                <th>Mesa</th>
                                <th>Orden</th>
                                <th>Observación</th>
                                <th style="width:10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

                    if (data.length === 0) {
                        contenido += `
                    <tr>
                        <td colspan="8" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>No hay votantes registrados para este puntero</p>
                        </td>
                    </tr>
                `;
                    } else {
                        data.forEach((v, i) => {
                            let obs = v.observacion || '';
                            contenido += `
                        <tr>
                            <td>${i+1}</td>
                            <td>${v.cedula || ''}</td>
                            <td>${v.nombre || ''}</td>
                            <td>${v.escuela || ''}</td>
                            <td class="text-center">${v.mesa || ''}</td>
                            <td class="text-center">${v.orden || ''}</td>
                            <td class="text-center" style="max-width:200px;">
                                <span id="obs_text_${v.id}" style="word-break:break-word;display:${obs ? 'inline' : 'none'};">${obs}</span>
                                <span id="obs_empty_${v.id}" class="text-muted" style="display:${obs ? 'none' : 'inline'};">—</span>
                                <button class="btn btn-sm btn-link p-0 ml-1" onclick="editarObservacion(${v.id})" title="Editar observación">
                                    <i class="fas fa-pen"></i>
                                </button>
                            </td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" onclick="eliminarVotante(${v.id}, '${nombrePuntero}')" title="Eliminar votante">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                        });
                    }

                    contenido += `</tbody></table></div>`;
                    $('#contenidoVotantes').html(contenido);

                    setTimeout(function() {
                        if ($.fn.DataTable && $('#votantes-table').length) {
                            if ($.fn.DataTable.isDataTable('#votantes-table')) {
                                $('#votantes-table').DataTable().destroy();
                            }

                            // IMPORTANTE: DOM correcto con 'B' para botones
                            $('#votantes-table').DataTable({
                                responsive: true,
                                dom: "<'row'<'col-sm-12 col-md-4'l><'col-sm-12 col-md-4'f><'col-sm-12 col-md-4'B>>" +
                                    "<'row'<'col-sm-12'tr>>" +
                                    "<'row'<'col-sm-12 col-md-5'i><'col-sm-12 col-md-7'p>>",
                                buttons: [{
                                        extend: 'copy',
                                        className: 'btn btn-secondary btn-sm',
                                        text: '<i class="fas fa-copy"></i> Copiar',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4,
                                                5, 6
                                            ]
                                        }
                                    },
                                    {
                                        extend: 'excel',
                                        className: 'btn btn-success btn-sm',
                                        text: '<i class="fas fa-file-excel"></i> Excel',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6]
                                        },
                                        title: `Votantes_${nombrePuntero.replace(/\s/g, '_')}`,
                                        filename: function() {
                                            return `votantes_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}`;
                                        }
                                    },
                                    {
                                        extend: 'pdf',
                                        className: 'btn btn-danger btn-sm',
                                        text: '<i class="fas fa-file-pdf"></i> PDF',
                                        orientation: 'portrait',
                                        pageSize: 'A4',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5]
                                        },
                                        title: `Votantes del Puntero: ${nombrePuntero}`,
                                        filename: function() {
                                            return `votantes_${new Date().toISOString().slice(0,19).replace(/:/g, '-')}`;
                                        },
                                        customize: function(doc) {
                                            doc.defaultStyle.fontSize = 10;
                                            doc.styles.tableHeader.fontSize = 11;
                                            doc.styles.tableHeader.fillColor =
                                                '#4CAF50';
                                            doc.styles.tableHeader.color = 'white';
                                            // Columnas: #, Cédula, Nombre, Escuela, Mesa, Orden
                                            doc.content[1].table.widths = ['8%',
                                                '15%', '32%', '20%', '12%',
                                                '13%'
                                            ];

                                            // Centrar columnas numéricas
                                            let body = doc.content[1].table.body;
                                            for (let i = 1; i < body.length; i++) {
                                                body[i][0].alignment =
                                                    'center'; // #
                                                body[i][4].alignment =
                                                    'center'; // Mesa
                                                body[i][5].alignment =
                                                    'center'; // Orden
                                            }

                                            // Agregar título con nombre del puntero
                                            doc.content.splice(0, 0, {
                                                text: `VOTANTES DEL PUNTERO: ${nombrePuntero.toUpperCase()}`,
                                                fontSize: 14,
                                                alignment: 'center',
                                                margin: [0, 0, 0, 20]
                                            });

                                            // Agregar fecha de generación
                                            let fecha = new Date();
                                            doc.content.push({
                                                text: `Generado el: ${fecha.toLocaleString()}`,
                                                fontSize: 8,
                                                alignment: 'center',
                                                margin: [0, 20, 0, 0]
                                            });
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-info btn-sm',
                                        text: '<i class="fas fa-print"></i> Imprimir',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5, 6]
                                        },
                                        customize: function(win) {
                                            $(win.document.body).find('table')
                                                .addClass('table table-bordered');
                                            $(win.document.body).find('h1').css(
                                                'text-align', 'center');
                                            $(win.document.body).find('h1').text(
                                                `Votantes del Puntero: ${nombrePuntero}`
                                            );

                                            // Centrar columnas numéricas
                                            $(win.document.body).find(
                                                'td:nth-child(1), td:nth-child(5), td:nth-child(6)'
                                            ).css('text-align', 'center');
                                            $(win.document.body).find(
                                                'th:nth-child(1), th:nth-child(5), th:nth-child(6)'
                                            ).css('text-align', 'center');

                                            // Agregar fecha
                                            let fecha = new Date();
                                            $(win.document.body).append(
                                                `<p style="text-align:center; margin-top:20px;">Fecha de impresión: ${fecha.toLocaleString()}</p>`
                                            );
                                        }
                                    }
                                ],
                                language: {
                                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                                    search: "Buscar votante:",
                                    searchPlaceholder: "Nombre, cédula..."
                                },
                                pageLength: 10,
                                lengthMenu: [
                                    [5, 10, 25, 50, 100, -1],
                                    [5, 10, 25, 50, 100, "Todos"]
                                ],
                                order: [
                                    [2, 'asc']
                                ], // Ordenar por nombre
                                columnDefs: [{
                                        targets: [0],
                                        className: 'text-center',
                                        orderable: true
                                    }, // #
                                    {
                                        targets: [4],
                                        className: 'text-center',
                                        orderable: true
                                    }, // Mesa
                                    {
                                        targets: [5],
                                        className: 'text-center',
                                        orderable: true
                                    }, // Orden
                                    {
                                        targets: [6],
                                        orderable: true,
                                        searchable: true
                                    }, // Observación
                                    {
                                        targets: [7],
                                        orderable: false,
                                        searchable: false
                                    } // Acciones
                                ]
                            });
                        }
                    }, 200);
                },
                error: function(xhr) {
                    console.error('Error:', xhr);
                    $('#contenidoVotantes').html(
                        `<div class="alert alert-danger text-center p-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <h5>Error al cargar los votantes</h5>
                    <p class="mb-0">${xhr.responseJSON?.message || 'Intente nuevamente más tarde'}</p>
                    <button class="btn btn-sm btn-outline-danger mt-3" onclick="cargarVotantes(${idPuntero}, '${nombrePuntero}')">
                        <i class="fas fa-sync"></i> Reintentar
                    </button>
                </div>`
                    );
                }
            });
        };

        window.editarObservacion = function(id) {
            let currentObs = $('#obs_text_' + id).text();
            $('#modalVotantes').modal('hide');
            setTimeout(() => {
                Swal.fire({
                    title: 'Editar Observación',
                    input: 'textarea',
                    inputValue: currentObs && currentObs !== '—' ? currentObs : '',
                    inputAttributes: {
                        maxlength: 500,
                        rows: 3
                    },
                    showCancelButton: true,
                    confirmButtonText: 'Guardar',
                    cancelButtonText: 'Cancelar',
                    didOpen: () => {
                        setTimeout(() => {
                            const input = Swal.getInput();
                            if (input) input.focus();
                        }, 100);
                    }
                }).then(result => {
                    if (result.isConfirmed) {
                        let nuevaObs = result.value || '';
                        $.ajax({
                            url: "{{ url('votante') }}/" + id + "/observacion",
                            type: 'PUT',
                            data: {
                                _token: "{{ csrf_token() }}",
                                observacion: nuevaObs
                            },
                            success: function(response) {
                                if (response.success) {
                                    if (nuevaObs) {
                                        $('#obs_text_' + id).text(nuevaObs).show();
                                        $('#obs_empty_' + id).hide();
                                    } else {
                                        $('#obs_text_' + id).hide();
                                        $('#obs_empty_' + id).show();
                                    }
                                    Swal.fire({
                                        icon: 'success',
                                        title: 'Observación actualizada',
                                        timer: 1200,
                                        showConfirmButton: false
                                    }).then(() => {
                                        let pid = $('#votante_id_puntero').val();
                                        let n = $('#tituloVotantes').text().replace('Votantes del Puntero: ', '').trim();
                                        if (pid) window.cargarVotantes(pid, n);
                                    });
                                }
                            },
                            error: function(xhr) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: xhr.responseJSON?.message || 'No se pudo actualizar la observación'
                                });
                            }
                        });
                    } else {
                        let pid = $('#votante_id_puntero').val();
                        let n = $('#tituloVotantes').text().replace('Votantes del Puntero: ', '').trim();
                        if (pid) window.cargarVotantes(pid, n);
                    }
                });
            }, 300);
        };

        window.eliminarVotante = function(id, nombre) {
            Swal.fire({
                title: '¿Eliminar votante?',
                icon: 'warning',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: "{{ url('votante/delete') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire('Eliminado', response.message ??
                                'Votante eliminado correctamente', 'success');
                            if (response.punteroId) cargarVotantes(response.punteroId, nombre);
                            if (response.abrirModalVotante) $('#modalVotante').modal('show');
                        },
                        error: function(xhr) {
                            Swal.fire('Error', xhr.responseJSON?.message ??
                                'No se pudo eliminar el votante', 'error');
                        }
                    });
                }
            });
        };

        // Limpiar DataTable cuando se cierra modal de votantes
        $('#modalVotantes').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#votantes-table')) {
                $('#votantes-table').DataTable().destroy();
            }
        });

        // Exponer funciones globales necesarias
        window.abrirModalPunterosLista = abrirModalPunterosLista;
        window.abrirModalPunterosListapordir = abrirModalPunterosListapordir;
        window.eliminarDirigente = eliminarDirigente; // definida más abajo
        window.filtrarPunteros = filtrarPunteros; // definida más abajo
        window.eliminarPuntero = eliminarPuntero; // definida más abajo
        window.cargarSistemasManual = cargarSistemasManual;
        window.abrirModalVotantes = function(punteroId, nombre) {
            $('#modalVotantes').modal('show');
            $('#contenidoVotantes').html(
                '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');
            let url = `{{ url('/') }}/punteros/${punteroId}/votantes`;
            $.get(url, function(data) {
                $('#contenidoVotantes').html(data);
            });
        };

        // Funciones de punteros (para uso interno dentro de lista_punteros)
        function cargarPunteros(dirigenteId) {
            let tbody = $('#punteros-table tbody');
            tbody.html('<tr><td colspan="6" class="text-center">Cargando punteros...</td></tr>');
            let url = "{{ url('dirigente') }}/" + dirigenteId + "/punteros/json?t=" + new Date().getTime();
            $.get(url, function(data) {
                tbody.empty();
                if (data.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center">No hay punteros registrados</td></tr>');
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) $('#punteros-table')
                        .DataTable().destroy();
                    return;
                }
                let html = '';
                data.forEach((puntero, index) => {
                    html +=
                        `<tr><td>${index+1}</td><td>${puntero.cedula}</td><td>${puntero.nombre}</td><td>${puntero.telefono ?? ''}</td><td>${puntero.barrio ?? ''}</td><td><button class="btn btn-danger btn-sm" onclick="eliminarPuntero(${puntero.id}, ${dirigenteId})"><i class="fas fa-trash"></i></button></td></tr>`;
                });
                tbody.html(html);
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) $('#punteros-table')
                    .DataTable().destroy();
                $('#punteros-table').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    pageLength: 5,
                    destroy: true
                });
            }).fail(() => tbody.html(
                '<tr><td colspan="6" class="text-center text-danger">Error al cargar punteros</td></tr>'));
        }

        function cargarPunterosporEq(equipoId) {
            let tbody = $('#punteros-table tbody');
            tbody.html('<tr><td colspan="6" class="text-center">Cargando punteros...</td></tr>');
            let url = "{{ url('equipo') }}/" + equipoId + "/punteros?t=" + new Date().getTime();
            $.get(url, function(data) {
                tbody.empty();
                if (data.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center">No hay punteros registrados</td></tr>');
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) $('#punteros-table')
                        .DataTable().destroy();
                    return;
                }
                let html = '';
                data.forEach((puntero, index) => {
                    html +=
                        `<tr><td>${index+1}</td><td>${puntero.cedula}</td><td>${puntero.nombre}</td><td>${puntero.telefono ?? ''}</td><td>${puntero.barrio ?? ''}</td><td><button class="btn btn-danger btn-sm" onclick="eliminarPuntero(${puntero.id}, ${puntero.id_dirigente})"><i class="fas fa-trash"></i></button></td></tr>`;
                });
                tbody.html(html);
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) $('#punteros-table')
                    .DataTable().destroy();
                $('#punteros-table').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    pageLength: 5,
                    destroy: true
                });
            }).fail(() => tbody.html(
                '<tr><td colspan="6" class="text-center text-danger">Error al cargar punteros</td></tr>'));
        }

        function filtrarPunteros() {
            let dirigenteId = $('#puntero_id_dirigente').val();
            let equipoId = $('#equipo_punteros').val();
            if (dirigenteId) cargarPunteros(dirigenteId);
            else cargarPunterosporEq(equipoId);
        }

        function eliminarPuntero(punteroId, dirigenteId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    $.ajax({
                        url: "{{ route('puntero.destroy.ajax') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: punteroId
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.close();
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: 'El puntero ha sido eliminado',
                                    timer: 1500,
                                    showConfirmButton: false
                                });
                                cargarPunteros(dirigenteId);
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'No se pudo eliminar el puntero'
                            });
                        }
                    });
                }
            });
        }

        function eliminarDirigente(dirigenteId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir. Se eliminarán también sus punteros y votantes.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    Swal.fire({
                        title: 'Eliminando...',
                        allowOutsideClick: false,
                        didOpen: () => Swal.showLoading()
                    });
                    let url = `{{ url('/') }}/dirigentes/ajax/${dirigenteId}`;
                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            if (typeof filtrarDirigentes === 'function') filtrarDirigentes();
                            else location.reload();
                        },
                        error: function(xhr) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'Error al eliminar el dirigente'
                            });
                        }
                    });
                }
            });
        }

        // Limpiar DataTables al cerrar modales
        $('#modalSistemas').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sistemas-table')) $('#sistemas-table').DataTable()
                .destroy();
        });
        $('#modalDirigentes').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dirigentes-table')) $('#dirigentes-table')
                .DataTable().destroy();
        });

        // Mensaje de éxito global
        const successAlert = @json(session('success'));
        if (successAlert) Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: successAlert,
            timer: 1800,
            showConfirmButton: false
        });
    </script>
@stop
