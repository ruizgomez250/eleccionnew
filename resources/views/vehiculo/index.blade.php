@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <div class="ua-header">
        <div>
            <label for="equipo_id" class="form-label fw-bold">Equipos</label>
            <div style="min-width: 280px; display: inline-block; vertical-align: middle;">
                <x-adminlte-select2 name="equipo_id" id="equipo_id" onchange="filtrarPorEquipo()" disable-faster-look>
                    <option value="">Todos los equipos</option>
                    @foreach ($equipos as $eq)
                        <option value="{{ $eq->id }}" @if ($equipoId == $eq->id) selected @endif>
                            {{ $eq->descripcion }}
                        </option>
                    @endforeach
                </x-adminlte-select2>
            </div>
        </div>

        <button class="ua-btn ua-btn-grad" data-toggle="modal" data-target="#modalReporteEquipos">
            <i class="fas fa-file-pdf"></i> Reporte por Equipo
        </button>
    </div>
@stop

@section('content')
    {{-- FORMULARIO NUEVO VEHÍCULO --}}
    <div class="card mb-4">
        <div class="card-header bg-primary">
            <strong><i class="fas fa-plus-circle"></i> Nuevo Vehículo</strong>
        </div>
        <form action="{{ route('vehiculo.store') }}" method="POST" id="formVehiculo">
            @csrf
            <div class="card-body">
                <div class="row">
                    <x-adminlte-input name="cedulachofer" id="cedulachofer" label="Cédula del Chofer"
                        placeholder="Ej: 1.234.567" fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="nombre" id="nombre" label="Nombre del Chofer" placeholder="Nombre completo"
                        fgroup-class="col-md-5" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-user"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="chapa" label="Chapa" placeholder="ABC123" fgroup-class="col-md-2" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-car"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="tipovehiculo" label="Tipo" fgroup-class="col-md-2">
                        <option value="AUTOMOVIL">AUTOMÓVIL</option>
                        <option value="CAMIONETA">CAMIONETA</option>
                        <option value="FURGONETA">FURGONETA</option>
                    </x-adminlte-select>
                </div>

                <div class="row">
                    <x-adminlte-input name="direccion" id="direccion" label="Dirección" fgroup-class="col-md-4">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="barriocompania" id="barriocompania" label="Barrio/Compañía" fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="capacidad" label="Capacidad" type="number" value="5" fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text"><i class="fas fa-users"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="telefono1" id="telefono1" label="Teléfono" placeholder="0981xxxxxx" fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                <div class="row">
                    <x-adminlte-input name="telefono2" id="telefono2" label="Teléfono 2" fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="montopagar" label="Monto (Gs.)" fgroup-class="col-md-2">
                        <option value="0">0</option>
                        <option value="200000">200.000</option>
                        <option value="300000" selected>300.000</option>
                        <option value="350000">350.000</option>
                        <option value="400000">400.000</option>
                        <option value="450000">450.000</option>
                        <option value="500000">500.000</option>
                        <option value="550000">550.000</option>
                    </x-adminlte-select>

                    <x-adminlte-input name="cantidadpagos" label="Cantidad Pagos" type="number" value="2" fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text"><i class="fas fa-list-ol"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="rol" id="rol" label="Rol" fgroup-class="col-md-3">
                        <option value="PUNTERO" selected>PUNTERO</option>
                        <option value="LOGISTICA">LOGISTICA</option>
                    </x-adminlte-select>

                    <div class="col-md-2">
                        <label for="id_equipo" class="form-label fw-bold">
                            Equipo <span id="equipoRequired" class="text-danger">*</span>
                        </label>
                        <select name="id_equipo" id="id_equipo" class="form-control">
                            <option value="">Sin Equipo</option>
                            @foreach ($equipos as $eq)
                                <option value="{{ $eq->id }}">{{ $eq->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                {{-- =================== SECCIÓN PROPONENTE (OBLIGATORIO) =================== --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header bg-secondary text-white">
                                <h6 class="card-title mb-0">
                                    <i class="fas fa-user-check"></i> Datos del Proponente <span class="text-danger">*</span>
                                </h6>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool text-white" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label for="cedulaproponente">Cédula del Proponente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-id-card"></i></span>
                                                </div>
                                                <input type="text" name="cedulaproponente" id="cedulaproponente" 
                                                    class="form-control" placeholder="Ej: 1.234.567" required>
                                            </div>
                                            <small class="text-muted">Ingrese la cédula para buscar automáticamente</small>
                                        </div>
                                    </div>

                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label for="nombreproponente">Nombre del Proponente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-user-tie"></i></span>
                                                </div>
                                                <input type="text" name="nombreproponente" id="nombreproponente" 
                                                    class="form-control"  required>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label for="telefonoproponente">Teléfono del Proponente <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-phone"></i></span>
                                                </div>
                                                <input type="text" name="telefonoproponente" id="telefonoproponente" 
                                                    class="form-control"  required>
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
                        <button class="btn btn-success w-100" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar Vehículo
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- TABLA DE VEHÍCULOS (DATATABLE) --}}
    <div class="card">
        <div class="card-body">
            <table id="vehiculos-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cédula Chofer</th>
                        <th>Chofer</th>
                        <th>Chapa</th>
                        <th>Tipo</th>
                        <th>Teléfono</th>
                        <th>Equipo</th>
                        <th>Rol</th>
                        <th>Proponente</th>
                        <th>Punteros</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($vehiculos as $vehiculo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ number_format($vehiculo->cedulachofer, 0, ',', '.') }}</td>
                            <td>{{ $vehiculo->nombre }}</td>
                            <td>{{ $vehiculo->chapa }}</td>
                            <td>{{ $vehiculo->tipovehiculo }}</td>
                            <td>{{ $vehiculo->telefono1 }}{{ $vehiculo->telefono2 ? ' / ' . $vehiculo->telefono2 : '' }}</td>
                            <td>{{ $vehiculo->equipo->descripcion ?? 'Sin equipo' }}</td>
                            <td>
                                <span class="badge badge-{{ $vehiculo->rol == 'PUNTERO' ? 'primary' : 'secondary' }}">
                                    {{ $vehiculo->rol }}
                                </span>
                            </td>
                            <td>{{ $vehiculo->nombreproponente ?? 'N/A' }}</td>
                            <td class="text-center">
                                <span class="badge badge-info">
                                    {{ $vehiculo->punteros_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-primary btn-sm" 
                                    onclick="generarPDFContratoVehicular({{ $vehiculo->id }})"
                                    title="Contrato de Alquiler">
                                    <i class="fas fa-file-pdf"></i>
                                </button>
                                
                                <button class="btn btn-warning btn-sm" 
                                    onclick="abrirModalPunteros({{ $vehiculo->id }}, '{{ addslashes($vehiculo->nombre) }}', {{ $vehiculo->id_equipo ?? 0 }})"
                                    title="Asignar Punteros">
                                    <i class="fas fa-users"></i>
                                </button>
                                
                                <a href="{{ route('vehiculo.edit', $vehiculo->id) }}" 
                                    class="btn btn-secondary btn-sm" title="Editar">
                                    <i class="fas fa-edit"></i>
                                </a>
                                
                                <form action="{{ route('vehiculo.destroy', $vehiculo->id) }}" 
                                    method="POST" class="form-delete d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-danger btn-sm btn-delete" title="Eliminar">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- MODALES --}}
    <div class="modal fade" id="modalReporteEquipos" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md">
            <div class="modal-content">
                <div class="modal-header bg-danger">
                    <h5 class="modal-title"><i class="fas fa-file-pdf"></i> Reporte por Equipo</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Equipo</label>
                        <select id="selectEquipoReporte" class="form-control select2" style="width:100%">
                            <option value="">Seleccione un equipo</option>
                            @foreach ($equipos as $equipo)
                                <option value="{{ $equipo->id }}">{{ $equipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal"><i class="fas fa-times"></i> Cerrar</button>
                    <button class="btn btn-danger" id="btnAbrirReporte"><i class="fas fa-file-pdf"></i> Ver PDF</button>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalPunteros" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalPunterosLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-user-plus"></i> Agregar puntero</label>
                        <select id="selectPunteros" class="form-control"></select>
                    </div>
                    <button class="btn btn-success mb-3" id="btnAsignar"><i class="fas fa-plus"></i> Asignar</button>
                    <table class="table table-bordered table-striped" id="tablaAsignados">
                        <thead class="thead-dark">
                            <th>Nombre</th>
                            <th width="80">Acción</th>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .is-valid {
            border-color: #34d399 !important;
            background-color: rgba(5, 150, 105, .25) !important;
        }
        .is-invalid {
            border-color: #fb7185 !important;
            background-color: rgba(225, 29, 72, .20) !important;
        }
    </style>
    @include('useradmin._dark_pages')
@stop

@section('js')
<script>
    const BASE_URL = '{{ url('/') }}';
    let vehiculoActual = null;
    let nombreVehiculoActual = '';
    let equipoActual = null;
    let tablaAsignados = null;

    $(document).ready(function() {
        inicializarTablaVehiculos();
        inicializarSelects();
        inicializarEventos();
        
        // Mensaje de éxito
        const successAlert = @json(session('success'));
        if (successAlert) {
            Swal.fire({ icon: 'success', title: 'Éxito', text: successAlert, timer: 1800, showConfirmButton: false });
        }
    });

    function inicializarTablaVehiculos() {
        $('#vehiculos-table').DataTable({
            dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                 "<'row'<'col-sm-12'tr>>" +
                 "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [
                { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-info', title: 'Lista de Vehículos', filename: 'vehiculos_export_{{ date('Y-m-d') }}', exportOptions: { columns: ':visible' } },
                { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger', title: 'Lista de Vehículos', filename: 'vehiculos_export_{{ date('Y-m-d') }}', exportOptions: { columns: ':visible' } },
                { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary', exportOptions: { columns: ':visible' } }
            ],
            responsive: true,
            language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            columnDefs: [
                { targets: 0, orderable: false, searchable: false },
                { targets: 9, orderable: false, searchable: false },
                { targets: 10, orderable: false, searchable: false }
            ]
        });
    }

    function inicializarSelects() {
        $('#id_equipo, #selectEquipoReporte').select2({ theme: 'bootstrap4', width: '100%' });
        toggleEquipoRequired();
    }

    function inicializarEventos() {
        // Búsqueda de chofer
        $('#cedulachofer').on('blur', buscarPorCedulaChofer);
        $('#cedulachofer').on('keypress', function(e) {
            if (e.which === 13) { e.preventDefault(); buscarPorCedulaChofer(); $('#nombre').focus(); }
        });

        // Búsqueda de proponente
        $('#cedulaproponente').on('blur', buscarPorCedulaProponente);
        $('#cedulaproponente').on('keypress', function(e) {
            if (e.which === 13) { e.preventDefault(); buscarPorCedulaProponente(); }
        });
        $('#nombreproponente').on('keypress', function(e) {
            if (e.which === 13) { e.preventDefault(); $('#telefonoproponente').focus(); }
        });
        $('#cedulaproponente').on('focus', function() { $(this).removeClass('is-valid is-invalid'); });

        // Validaciones
        $('#rol').on('change', toggleEquipoRequired);
        $('#formVehiculo').on('submit', function(e) { if (!validarFormulario()) e.preventDefault(); });

        // Reporte PDF
        $('#btnAbrirReporte').on('click', function() {
            let equipoId = $('#selectEquipoReporte').val();
            if (!equipoId) { Swal.fire('Atención', 'Debe seleccionar un equipo', 'warning'); return; }
            window.open(`{{ url('reportes/vehiculos-equipo') }}/${equipoId}`, '_blank');
        });

        // Asignar puntero
        $('#btnAsignar').on('click', asignarPuntero);
    }

    function filtrarPorEquipo() {
        let equipoId = $('#equipo_id').val();
        window.location.href = "{{ url('vehiculos') }}/" + (equipoId || '');
    }

    function generarPDFContratoVehicular(id) {
        window.open(`${BASE_URL}/vehiculos/contrato/${id}`, '_blank');
    }

    // ==================== BÚSQUEDA DE CHOFER ====================
    function buscarPorCedulaChofer() {
        let cedula = $('#cedulachofer').val().trim();
        if (cedula.length < 3) return;

        $.get(BASE_URL + "/dirigente/buscar-por-cedulap/" + cedula, function(response) {
            if (response.encontrado) {
                $('#nombre').val(response.data.nombre ?? '');
                $('#telefono1').val(response.data.telefono ?? '');
                $('#direccion').val(response.data.direccion ?? '');
                $('#barriocompania').val(response.data.barrio ?? '');
            } else {
                $('#nombre, #telefono1, #direccion, #barriocompania').val('');
            }
        });
    }

    // ==================== BÚSQUEDA DE PROPONENTE (OBLIGATORIO) ====================
    function buscarPorCedulaProponente() {
        let cedula = $('#cedulaproponente').val().trim();
        
        if (cedula === '') {
            $('#nombreproponente').val('');
            $('#telefonoproponente').val('');
            $('#cedulaproponente').removeClass('is-valid is-invalid');
            $('#nombreproponente').focus();
            return;
        }
        
        if (cedula.length < 3) {
            Swal.fire({ icon: 'warning', title: 'Cédula muy corta', text: 'Ingrese al menos 3 dígitos', timer: 2000, showConfirmButton: false });
            $('#nombreproponente').val('');
            $('#telefonoproponente').val('');
            $('#cedulaproponente').removeClass('is-valid is-invalid');
            $('#nombreproponente').focus();
            return;
        }
        
        $('#nombreproponente').val('Buscando...');
        
        $.get(BASE_URL + "/dirigente/buscar-por-cedulap/" + cedula, function(response) {
            if (response.encontrado) {
                $('#nombreproponente').val(response.data.nombre ?? '');
                $('#telefonoproponente').val(response.data.telefono ?? '');
                $('#cedulaproponente').removeClass('is-invalid').addClass('is-valid');
                
                Swal.fire({ icon: 'success', title: 'Proponente encontrado', text: `Nombre: ${response.data.nombre}`, timer: 1500, showConfirmButton: false, toast: true, position: 'top-end' });
                $('#nombreproponente').focus();
            } else {
                $('#nombreproponente').val('');
                $('#telefonoproponente').val('');
                $('#cedulaproponente').removeClass('is-valid').addClass('is-invalid');
                
                Swal.fire({ icon: 'error', title: 'Proponente no encontrado', text: `No se encontró un proponente con la cédula ${cedula}`, confirmButtonColor: '#dc3545' });
                $('#nombreproponente').focus();
            }
        }).fail(function() {
            $('#nombreproponente').val('');
            $('#telefonoproponente').val('');
            $('#cedulaproponente').removeClass('is-valid').addClass('is-invalid');
            Swal.fire({ icon: 'error', title: 'Error', text: 'Error al buscar la cédula', confirmButtonColor: '#dc3545' });
            $('#nombreproponente').focus();
        });
    }

    // ==================== VALIDACIONES ====================
    function toggleEquipoRequired() {
        const isPuntero = $('#rol').val() === 'PUNTERO';
        $('#equipoRequired').toggle(isPuntero);
    }

    function validarProponente() {
        let cedula = $('#cedulaproponente').val().trim();
        let nombre = $('#nombreproponente').val().trim();
        
        if (cedula === '') {
            Swal.fire({ icon: 'error', title: 'Campo requerido', text: 'Debe ingresar la cédula del proponente', confirmButtonColor: '#dc3545' });
            $('#cedulaproponente').focus();
            return false;
        }
        
        if (cedula.length < 3) {
            Swal.fire({ icon: 'error', title: 'Cédula inválida', text: 'La cédula debe tener al menos 3 dígitos', confirmButtonColor: '#dc3545' });
            $('#cedulaproponente').focus();
            return false;
        }
        
        if (nombre === '' || nombre === 'Buscando...') {
            Swal.fire({ icon: 'error', title: 'Proponente no encontrado', text: 'Debe buscar y seleccionar un proponente válido', confirmButtonColor: '#dc3545' });
            $('#cedulaproponente').focus();
            return false;
        }
        
        return true;
    }

    function validarFormulario() {
        // Validar equipo para rol PUNTERO
        if ($('#rol').val() === 'PUNTERO') {
            let equipo = $('#id_equipo').val();
            if (!equipo || equipo === '') {
                Swal.fire('Campo requerido', 'Debe seleccionar un equipo para rol PUNTERO', 'error');
                return false;
            }
        }
        
        // ✅ VALIDAR PROPONENTE OBLIGATORIO
        if (!validarProponente()) {
            return false;
        }
        
        return true;
    }

    // ==================== FUNCIONES DE PUNTEROS ====================
    window.abrirModalPunteros = function(idVehiculo, nombreVehiculo, idEquipo) {
        vehiculoActual = idVehiculo;
        nombreVehiculoActual = nombreVehiculo;
        equipoActual = idEquipo;

        $('#modalPunterosLabel').text(`Punteros - ${nombreVehiculo}`);
        
        const url = `${BASE_URL}/vehiculosasignar/${vehiculoActual}/punteros?equipo=${equipoActual}`;

        fetch(url)
            .then(response => response.json())
            .then(data => {
                $('#selectPunteros').empty();
                if (data.todos?.length > 0) {
                    data.todos.forEach(p => { $('#selectPunteros').append(`<option value="${p.id}">${p.nombre}</option>`); });
                } else {
                    $('#selectPunteros').append('<option value="">No hay punteros disponibles</option>');
                }

                $('#selectPunteros').select2({ dropdownParent: $('#modalPunteros'), width: '100%', placeholder: 'Seleccione un puntero' });

                if (tablaAsignados) { tablaAsignados.destroy(); }

                tablaAsignados = $('#tablaAsignados').DataTable({
                    data: data.asignados || [],
                    columns: [
                        { data: 'nombre', title: 'Nombre' },
                        { data: 'id', title: 'Acción', width: '80px', render: function(id) { return `<button class="btn btn-danger btn-sm" onclick="quitarPuntero(${id})"><i class="fas fa-trash"></i></button>`; } }
                    ],
                    language: { emptyTable: 'No hay punteros asignados', info: 'Mostrando _START_ a _END_ de _TOTAL_ registros', search: 'Buscar:' }
                });

                $('#modalPunteros').modal('show');
            })
            .catch(error => { console.error('Error:', error); Swal.fire('Error', 'Error al cargar los punteros', 'error'); });
    };

    window.quitarPuntero = function(punteroId) {
        if (!vehiculoActual) return;
        Swal.fire({ title: '¿Quitar puntero?', text: 'Este puntero ya no estará asignado', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, quitar' })
            .then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' } })
                        .then(response => response.json())
                        .then(() => { Swal.fire('Eliminado', 'Puntero removido exitosamente', 'success'); abrirModalPunteros(vehiculoActual, nombreVehiculoActual, equipoActual); })
                        .catch(error => { console.error('Error:', error); Swal.fire('Error', 'No se pudo quitar el puntero', 'error'); });
                }
            });
    };

    function asignarPuntero() {
        const punteroId = $('#selectPunteros').val();
        if (!punteroId) { Swal.fire('Error', 'Debe seleccionar un puntero', 'warning'); return; }
        Swal.fire({ title: 'Asignar puntero', text: '¿Deseas asignar este puntero al vehículo?', icon: 'question', showCancelButton: true, confirmButtonText: 'Sí, asignar' })
            .then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json', 'Content-Type': 'application/json' } })
                        .then(response => response.json())
                        .then(() => { Swal.fire('Asignado', 'Puntero asignado exitosamente', 'success'); abrirModalPunteros(vehiculoActual, nombreVehiculoActual, equipoActual); })
                        .catch(error => { console.error('Error:', error); Swal.fire('Error', 'No se pudo asignar el puntero', 'error'); });
                }
            });
    }

    // Eliminar vehículo
    document.querySelectorAll('.btn-delete').forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            let form = this.closest('.form-delete');
            if (!form) return;
            Swal.fire({ title: '¿Eliminar vehículo?', text: 'Esta acción no se puede deshacer', icon: 'warning', showCancelButton: true, confirmButtonColor: '#d33', confirmButtonText: 'Sí, eliminar' })
                .then(result => { if (result.isConfirmed) { form.submit(); } });
        });
    });
</script>
@stop