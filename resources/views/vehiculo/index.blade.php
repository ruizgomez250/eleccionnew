@extends('adminlte::page')

@section('title', 'Vehículos')

@section('content_header')
    <h1 class="m-0">
        <i class="fas fa-car text-primary"></i> Gestión de Vehículos
    </h1>
@stop

@section('content')

    {{-- =================== FORMULARIO NUEVO VEHÍCULO =================== --}}
    <div class="card mb-4">
        <div class="card-header bg-primary">
            <strong><i class="fas fa-plus-circle"></i> Nuevo Vehículo</strong>
        </div>

        <form action="{{ route('vehiculo.store') }}" method="POST" id="formVehiculo">
            @csrf
            <div class="card-body">

                {{-- FILA 1 --}}
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

                    <x-adminlte-select name="tipovehiculo" label="Tipo de Vehículo" fgroup-class="col-md-2">
                        <option value="AUTOMOVIL" selected>AUTOMÓVIL</option>
                        <option value="CAMIONETA">CAMIONETA</option>
                        <option value="FURGONETA">FURGONETA</option>
                    </x-adminlte-select>
                </div>

                {{-- FILA 2 --}}
                <div class="row">
                    <x-adminlte-input name="direccion" id="direccion" label="Dirección del Chofer" 
                        placeholder="Ej: Calle Fulgencio Yegros N° 123" fgroup-class="col-md-4">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="barriocompania" id="barriocompania" label="Barrio/Compañía" 
                        placeholder="Ej: Barrio San Rafael" fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-building"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="capacidad" label="Capacidad" type="number" value="5"
                        fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text"><i class="fas fa-users"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="telefono1" id="telefono1" label="Teléfono Principal" placeholder="0981xxxxxx"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                {{-- FILA 3 --}}
                <div class="row">
                    <x-adminlte-input name="telefono2" id="telefono2" label="Teléfono Secundario" placeholder="Opcional"
                        fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="montopagar" label="Monto a Pagar (Gs.)" fgroup-class="col-md-2">
                        <option value="0">0</option>
                        <option value="200000">200.000</option>
                        <option value="300000" selected>300.000</option>
                        <option value="350000">350.000</option>
                        <option value="400000">400.000</option>
                        <option value="450000">450.000</option>
                        <option value="500000">500.000</option>
                        <option value="550000">550.000</option>
                    </x-adminlte-select>

                    <x-adminlte-input name="cantidadpagos" label="Cantidad de Pagos" type="number" value="2"
                        fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text"><i class="fas fa-list-ol"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="rol" id="rol" label="Rol del Vehículo" fgroup-class="col-md-3">
                        <option value="PUNTERO" selected>PUNTERO</option>
                        <option value="LOGISTICA">LOGISTICA</option>
                    </x-adminlte-select>

                    <div class="col-md-2">
                        <label for="id_equipo" class="form-label fw-bold">
                            Equipo <span id="equipoRequired" class="text-danger">*</span>
                        </label>
                        <x-adminlte-select2 name="id_equipo" id="id_equipo" enable-old-support>
                            <option value="">Sin Equipo</option>
                            @foreach ($equipos as $eq)
                                <option value="{{ $eq->id }}">{{ $eq->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>
                </div>

                {{-- FILA 4: Botón Guardar --}}
                

                {{-- FILA 5: DATOS DEL PROPONENTE (Opcionales) --}}
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h6 class="card-title">
                                    <i class="fas fa-user-check"></i> Datos del Proponente (Opcional)
                                </h6>
                                <div class="card-tools">
                                    <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                        <i class="fas fa-minus"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="card-body">
                                <div class="row">
                                    <x-adminlte-input name="cedulaproponente" id="cedulaproponente" 
                                        label="Cédula del Proponente" placeholder="Ej: 1.234.567"
                                        fgroup-class="col-md-3">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>

                                    <x-adminlte-input name="nombreproponente" id="nombreproponente" 
                                        label="Nombre del Proponente" placeholder="Nombre completo"
                                        fgroup-class="col-md-5">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-user-tie"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>

                                    <x-adminlte-input name="telefonoproponente" id="telefonoproponente" 
                                        label="Teléfono del Proponente" placeholder="0981xxxxxx"
                                        fgroup-class="col-md-4">
                                        <x-slot name="prependSlot">
                                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                                        </x-slot>
                                    </x-adminlte-input>
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

    {{-- =================== BUSCADOR =================== --}}

    <div class="card mb-3">
        <div class="card-body">

            {{-- ACCIONES --}}
            <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                <h6 class="mb-0 text-muted">
                    <i class="fas fa-tools"></i> Acciones
                </h6>

                <div class="btn-group">
                    <button class="btn btn-danger" data-toggle="modal" data-target="#modalReporteEquipos">
                        <i class="fas fa-file-pdf"></i> Reporte Vehículos por Equipo
                    </button>
                </div>
            </div>

            {{-- BUSCADOR --}}
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorVehiculo" class="form-control"
                    placeholder="Buscar por chofer o chapa">
            </div>

        </div>
    </div>

    {{-- =================== LISTA DE VEHÍCULOS =================== --}}
    <div class="row">
        @foreach ($vehiculos as $vehiculo)
            <div class="col-md-3 mb-4 vehiculo-card"
                data-search="{{ strtolower($vehiculo->nombre . ' ' . $vehiculo->chapa) }}">
                <div class="card shadow h-100">
                    <div class="card-header text-center bg-light">
                        <i class="fas fa-car fa-3x text-primary"></i>
                    </div>

                    <div class="card-body text-center">
                        <h6 class="font-weight-bold">{{ $vehiculo->nombre }}</h6>
                        <p class="mb-1"><i class="fas fa-id-card"></i>
                            {{ number_format($vehiculo->cedulachofer, 0, ',', '.') }}</p>
                        <p class="mb-1"><i class="fas fa-hashtag"></i> {{ $vehiculo->chapa }}</p>
                        <p class="mb-1">
                            <i class="fas fa-users-cog"></i> {{ $vehiculo->equipo->descripcion ?? 'Sin equipo' }}
                        </p>

                        @php
                            $telefonos = collect([$vehiculo->telefono1, $vehiculo->telefono2, $vehiculo->telefono3])
                                ->filter()
                                ->implode(' - ');
                        @endphp

                        @if ($telefonos)
                            <p class="mb-1"><i class="fas fa-phone-alt"></i> {{ $telefonos }}</p>
                        @endif

                        @if ($vehiculo->numero_auto)
                            <p class="mb-0"><i class="fas fa-car-side"></i> {{ $vehiculo->numero_auto }}</p>
                        @endif
                        
                        {{-- Mostrar dirección y barrio si existen --}}
                        @if($vehiculo->direccion || $vehiculo->barriocompania)
                            <hr class="my-2">
                            @if($vehiculo->direccion)
                                <p class="mb-0 small text-muted">
                                    <i class="fas fa-map-marker-alt"></i> {{ $vehiculo->direccion }}
                                </p>
                            @endif
                            @if($vehiculo->barriocompania)
                                <p class="mb-0 small text-muted">
                                    <i class="fas fa-building"></i> {{ $vehiculo->barriocompania }}
                                </p>
                            @endif
                        @endif

                        {{-- Mostrar datos del proponente si existen --}}
                        @if($vehiculo->nombreproponente)
                            <hr class="my-2">
                            <p class="mb-0 small text-muted">
                                <i class="fas fa-user-check"></i> Prop: {{ $vehiculo->nombreproponente }}
                            </p>
                        @endif
                    </div>

                    {{-- Botón Contrato --}}
                    <button class="btn btn-primary btn-sm" onclick="generarPDFContratoVehicular({{ $vehiculo->id }})">
                        <i class="fas fa-file-pdf"></i> Contrato de Alquiler
                    </button>

                    {{-- Botón Punteros --}}
                    <button class="btn btn-warning btn-sm mt-1"
                        onclick="window.abrirModalPunteros({{ $vehiculo->id }}, '{{ addslashes($vehiculo->nombre) }}', {{ $vehiculo->id_equipo ?? 0 }})">
                        <i class="fas fa-users-cog"></i> Punteros
                    </button>

                    <div class="card-footer text-center">
                        <div class="btn-group w-100">
                            <a href="{{ route('vehiculo.edit', $vehiculo->id) }}"
                                class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('vehiculo.destroy', $vehiculo->id) }}" method="POST"
                                class="form-delete d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        @endforeach
    </div>

    <div class="modal fade" id="modalReporteEquipos" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-md" role="document">
            <div class="modal-content">

                <div class="modal-header bg-danger">
                    <h5 class="modal-title">
                        <i class="fas fa-file-pdf"></i> Reporte de Vehículos y Punteros
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>

                <div class="modal-body">
                    <div class="form-group">
                        <label><i class="fas fa-users"></i> Equipo</label>
                        <select id="selectEquipoReporte" class="form-control select2" style="width:100%">
                            <option value="0">Sin Equipo</option>
                            @foreach ($equipos as $equipo)
                                <option value="{{ $equipo->id }}">
                                    {{ $equipo->descripcion }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="modal-footer">
                    <button class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>

                    <button class="btn btn-danger" id="btnAbrirReporte">
                        <i class="fas fa-file-pdf"></i> Ver PDF
                    </button>
                </div>

            </div>
        </div>
    </div>

    {{-- Modal genérico punteros --}}
    <div class="modal fade" id="modalPunteros" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="modalPunterosLabel"></h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
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
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">
                        <i class="fas fa-times"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>

@stop

@section('js')
    <script>
        const BASE_URL = '{{ url('/') }}';
        let vehiculoActual = null;
        let nombreVehiculoActual = '';
        let equipoActual = null;
        let tabla = null;

        // =================== MOSTRAR/OCULTAR ASTERISCO Y VALIDAR EQUIPO SEGÚN ROL ===================
        function toggleEquipoRequired() {
            const rolSeleccionado = $('#rol').val();
            const equipoRequired = $('#equipoRequired');

            if (rolSeleccionado === 'PUNTERO') {
                equipoRequired.show();
            } else { // LOGISTICA
                equipoRequired.hide();
            }
        }

        // =================== BÚSQUEDA POR CÉDULA DEL CHOFER ===================
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
                    $('#nombre').val('');
                    $('#telefono1').val('');
                    $('#direccion').val('');
                    $('#barriocompania').val('');
                }
            }).fail(function() {
                console.log('Error en la búsqueda de cédula');
            });
        }

        // =================== BÚSQUEDA POR CÉDULA DEL PROPONENTE ===================
        function buscarPorCedulaProponente() {
            let cedula = $('#cedulaproponente').val().trim();
            
            // Si la cédula está vacía o tiene menos de 3 caracteres, limpiar campos
            if (cedula.length < 3) {
                $('#nombreproponente').val('');
                $('#telefonoproponente').val('');
                $('#cedulaproponente').removeClass('is-valid is-invalid');
                return;
            }

            // Mostrar indicador de carga
            $('#nombreproponente').prop('placeholder', 'Buscando...');
            
            $.get(BASE_URL + "/dirigente/buscar-por-cedulap/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#nombreproponente').val(response.data.nombre ?? '');
                    $('#telefonoproponente').val(response.data.telefono ?? '');
                    
                    // Cambiar color de borde a verde para indicar éxito
                    $('#cedulaproponente').removeClass('is-invalid').addClass('is-valid');
                } else {
                    $('#nombreproponente').val('');
                    $('#telefonoproponente').val('');
                    
                    // Cambiar color de borde a rojo para indicar no encontrado
                    $('#cedulaproponente').removeClass('is-valid').addClass('is-invalid');
                }
                
                // Restaurar placeholder
                $('#nombreproponente').prop('placeholder', 'Nombre completo');
            }).fail(function() {
                console.log('Error en la búsqueda de cédula del proponente');
                $('#nombreproponente').prop('placeholder', 'Nombre completo');
            });
        }

        // =================== LIMPIAR CAMPOS DEL PROPONENTE ===================
        function limpiarCamposProponente() {
            $('#nombreproponente').val('');
            $('#telefonoproponente').val('');
            $('#cedulaproponente').removeClass('is-valid is-invalid');
        }

        // =================== VALIDACIÓN ANTES DE GUARDAR ===================
        function validarFormulario() {
            const rolSeleccionado = $('#rol').val();
            const equipoValue = $('#id_equipo').val();

            // Si el rol es PUNTERO, el equipo es obligatorio
            if (rolSeleccionado === 'PUNTERO') {
                if (!equipoValue || equipoValue === '') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Campo requerido',
                        text: 'Debe seleccionar un equipo para los vehículos con rol PUNTERO',
                        confirmButtonColor: '#3085d6'
                    });
                    return false;
                }
            }

            // Si el rol es LOGISTICA, el equipo es opcional
            return true;
        }

        // =================== FUNCIONES GLOBALES ===================

        window.abrirModalPunteros = function(idVehiculo, nombreVehiculo, idEquipo) {
            vehiculoActual = idVehiculo;
            nombreVehiculoActual = nombreVehiculo;
            equipoActual = idEquipo;

            $('#modalPunterosLabel').text(`Punteros - ${nombreVehiculo}`);

            const url = `${BASE_URL}/vehiculosasignar/${vehiculoActual}/punteros?equipo=${equipoActual}`;

            fetch(url)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    $('#selectPunteros').empty();

                    if (data.todos && data.todos.length > 0) {
                        data.todos.forEach(p => {
                            $('#selectPunteros').append(`<option value="${p.id}">${p.nombre}</option>`);
                        });
                    } else {
                        $('#selectPunteros').append(
                            '<option value="">No hay punteros disponibles en este equipo</option>');
                    }

                    if ($('#selectPunteros').data('select2')) {
                        $('#selectPunteros').select2('destroy');
                    }

                    $('#selectPunteros').select2({
                        dropdownParent: $('#modalPunteros'),
                        width: '100%',
                        placeholder: 'Seleccione un puntero'
                    });

                    if (tabla) {
                        tabla.destroy();
                        tabla = null;
                    }

                    const asignados = data.asignados || [];

                    tabla = $('#tablaAsignados').DataTable({
                        data: asignados,
                        columns: [{
                                data: 'nombre',
                                title: 'Nombre'
                            },
                            {
                                data: 'id',
                                title: 'Acción',
                                width: '80px',
                                render: function(id) {
                                    return `<button class="btn btn-danger btn-sm" onclick="window.quitarPuntero(${id})">
                                                <i class="fas fa-trash"></i>
                                            </button>`;
                                }
                            }
                        ],
                        language: {
                            emptyTable: 'No hay punteros asignados',
                            info: 'Mostrando _START_ a _END_ de _TOTAL_ registros',
                            infoEmpty: 'Mostrando 0 a 0 de 0 registros',
                            search: 'Buscar:',
                            zeroRecords: 'No se encontraron registros'
                        }
                    });

                    $('#modalPunteros').modal('show');
                })
                .catch(error => {
                    console.error('ERROR en la petición:', error);
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Error al cargar los punteros: ' + error.message
                    });
                });
        };

        window.quitarPuntero = function(punteroId) {
            if (!vehiculoActual) return;

            Swal.fire({
                title: '¿Quitar puntero?',
                text: 'Este puntero ya no estará asignado a este vehículo',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, quitar'
            }).then((result) => {
                if (result.isConfirmed) {
                    fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, {
                            method: 'DELETE',
                            headers: {
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                'Accept': 'application/json'
                            }
                        })
                        .then(response => response.json())
                        .then(() => {
                            Swal.fire('Eliminado', 'Puntero removido exitosamente', 'success');
                            window.abrirModalPunteros(vehiculoActual, nombreVehiculoActual, equipoActual);
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            Swal.fire('Error', 'No se pudo quitar el puntero', 'error');
                        });
                }
            });
        };

        // =================== FUNCIONES AUXILIARES ===================

        function generarPDFContratoVehicular(id) {
            window.open(`${BASE_URL}/vehiculos/contrato/${id}`, '_blank');
        }

        // =================== EVENTOS ===================

        $(document).ready(function() {

            // Configuración inicial del asterisco según rol inicial
            toggleEquipoRequired();

            // Evento change del select de rol
            $('#rol').on('change', function() {
                toggleEquipoRequired();
            });

            // =================== EVENTOS PARA CHOFER ===================
            // Evento blur para búsqueda por cédula del chofer
            $('#cedulachofer').on('blur', function() {
                buscarPorCedulaChofer();
            });

            // Evento keypress para búsqueda por cédula del chofer con Enter
            $('#cedulachofer').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedulaChofer();
                    $('#nombre').focus();
                }
            });

            // =================== EVENTOS PARA PROPONENTE ===================
            // Evento blur para búsqueda por cédula del proponente
            $('#cedulaproponente').on('blur', function() {
                buscarPorCedulaProponente();
            });

            // Evento keypress para búsqueda por cédula del proponente con Enter
            $('#cedulaproponente').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedulaProponente();
                    $('#nombreproponente').focus();
                }
            });

            // Evento focus para limpiar validaciones cuando el usuario modifica la cédula
            $('#cedulaproponente').on('focus', function() {
                $(this).removeClass('is-valid is-invalid');
            });

            // Evento change para limpiar campos si se borra la cédula
            $('#cedulaproponente').on('change', function() {
                if ($(this).val().trim() === '') {
                    limpiarCamposProponente();
                }
            });

            // Validación antes de enviar el formulario
            $('#formVehiculo').on('submit', function(e) {
                if (!validarFormulario()) {
                    e.preventDefault();
                }
            });

            // Select2 para reporte
            $('#selectEquipoReporte').select2({
                dropdownParent: $('#modalReporteEquipos'),
                theme: 'bootstrap4',
                placeholder: 'Seleccione un equipo'
            });

            // Botón abrir reporte
            $('#btnAbrirReporte').on('click', function() {
                let equipoId = $('#selectEquipoReporte').val();

                if (!equipoId) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Atención',
                        text: 'Debe seleccionar un equipo'
                    });
                    return;
                }

                let url = `{{ url('reportes/vehiculos-equipo') }}/${equipoId}`;
                window.open(url, '_blank');
            });

            // Asignar puntero
            $('#btnAsignar').on('click', function() {
                const punteroId = $('#selectPunteros').val();
                if (!punteroId) {
                    Swal.fire('Error', 'Debe seleccionar un puntero', 'warning');
                    return;
                }

                Swal.fire({
                    title: 'Asignar puntero',
                    text: '¿Deseas asignar este puntero al vehículo?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Sí, asignar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, {
                                method: 'POST',
                                headers: {
                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                    'Accept': 'application/json',
                                    'Content-Type': 'application/json'
                                }
                            })
                            .then(response => response.json())
                            .then(() => {
                                Swal.fire('Asignado', 'Puntero asignado exitosamente',
                                    'success');
                                window.abrirModalPunteros(vehiculoActual, nombreVehiculoActual,
                                    equipoActual);
                            })
                            .catch(error => {
                                console.error('Error:', error);
                                Swal.fire('Error', 'No se pudo asignar el puntero', 'error');
                            });
                    }
                });
            });

        });

        // Mensaje SweetAlert al crear/actualizar
        const successAlert = @json(session('success'));
        if (successAlert) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: successAlert,
                timer: 1800,
                showConfirmButton: false
            });
        }

        // Eliminar vehículo con SweetAlert
        document.querySelectorAll('.btn-delete').forEach(btn => {
            btn.addEventListener('click', function(e) {
                e.preventDefault();

                let form = this.closest('.form-delete');
                if (!form) return;

                Swal.fire({
                    title: '¿Eliminar vehículo?',
                    text: 'Esta acción no se puede deshacer',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Sí, eliminar'
                }).then(result => {
                    if (result.isConfirmed) {
                        Swal.fire({
                            title: 'Eliminando...',
                            text: 'Por favor espere',
                            allowOutsideClick: false,
                            didOpen: () => {
                                Swal.showLoading();
                            }
                        });
                        form.submit();
                    }
                });
            });
        });

        // Buscador dinámico
        const buscador = document.getElementById('buscadorVehiculo');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                let texto = this.value.toLowerCase();
                document.querySelectorAll('.vehiculo-card').forEach(card => {
                    card.style.display = card.dataset.search.includes(texto) ? '' : 'none';
                });
            });
        }

        // Asegurar que el modal de punteros se cierre correctamente
        $('#modalPunteros').on('hidden.bs.modal', function() {
            if (tabla) {
                tabla.destroy();
                tabla = null;
            }
        });
    </script>
@endsection