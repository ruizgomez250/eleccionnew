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


        <form action="{{ route('vehiculo.store') }}" method="POST">
            @csrf
            <div class="card-body">

                <div class="row">
                    <x-adminlte-input name="cedulachofer" label="Cédula del Chofer" placeholder="Ej: 1.234.567"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-id-card"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="nombre" label="Nombre del Chofer" placeholder="Nombre completo"
                        fgroup-class="col-md-3" required>
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

                    <x-adminlte-input name="capacidad" label="Capacidad" type="number" value="4"
                        fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text"><i class="fas fa-users"></i></div>
                        </x-slot>
                    </x-adminlte-input>
                </div>

                <div class="row mt-2">
                    <x-adminlte-input name="telefono1" label="Teléfono Principal" placeholder="0981xxxxxx"
                        fgroup-class="col-md-2" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="telefono2" label="Teléfono Secundario" placeholder="Opcional"
                        fgroup-class="col-md-2">
                        <x-slot name="prependSlot">
                            <div class="input-group-text"><i class="fas fa-phone-alt"></i></div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="montopagar" label="Monto a Pagar (Gs.)" fgroup-class="col-md-2">
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

                    <div class="col-md-4">
                        <label for="id_equipo" class="form-label fw-bold">Equipo</label>
                        <x-adminlte-select2 name="id_equipo" id="id_equipo" enable-old-support>
                            @foreach ($equipos as $eq)
                                <option value="{{ $eq->id }}">{{ $eq->descripcion }}</option>
                            @endforeach
                        </x-adminlte-select2>
                    </div>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100"><i class="fas fa-save"></i> Guardar</button>
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
                    <div class="card-header text-center bg-light"><i class="fas fa-car fa-3x text-primary"></i></div>

                    <div class="card-body text-center">
                        <h6 class="font-weight-bold">{{ $vehiculo->nombre }}</h6>
                        <p class="mb-1"><i class="fas fa-id-card"></i>
                            {{ number_format($vehiculo->cedulachofer, 0, ',', '.') }}</p>
                        <p class="mb-1"><i class="fas fa-hashtag"></i> {{ $vehiculo->chapa }}</p>
                        <p class="mb-1">
                            <i class="fas fa-users-cog"></i> {{ $vehiculo->equipo->descripcion }}
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
                    </div>

                    {{-- Botón Contrato --}}
                    <button class="btn btn-primary btn-sm" onclick="generarPDFContratoVehicular({{ $vehiculo->id }})">
                        <i class="fas fa-file-pdf"></i> Contrato de Alquiler
                    </button>

                    {{-- Botón Punteros --}}
                    <button class="btn btn-warning btn-sm mt-1"
                        onclick="abrirModalPunteros({{ $vehiculo->id }}, '{{ $vehiculo->nombre }}', {{ $vehiculo->id_equipo }})">
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
                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
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
                            <option value="">Seleccione un equipo</option>
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
    <div class="modal fade" id="modalPunteros">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalPunterosLabel"></h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Agregar puntero</label>
                        <select id="selectPunteros" class="form-control"></select>
                    </div>
                    <button class="btn btn-success mb-3" id="btnAsignar"><i class="fas fa-plus"></i> Asignar</button>
                    <table class="table table-bordered" id="tablaAsignados">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th width="80">Acción</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
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
            btn.addEventListener('click', function() {
                let form = this.closest('.form-delete');
                Swal.fire({
                    title: '¿Eliminar vehículo?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonText: 'Cancelar',
                    confirmButtonText: 'Eliminar'
                }).then(result => {
                    if (result.isConfirmed) form.submit();
                });
            });
        });

        // Buscador dinámico
        document.getElementById('buscadorVehiculo').addEventListener('keyup', function() {
            let texto = this.value.toLowerCase();
            document.querySelectorAll('.vehiculo-card').forEach(card => {
                card.style.display = card.dataset.search.includes(texto) ? '' : 'none';
            });
        });

        // Modal punteros
        function abrirModalPunteros(idVehiculo, nombreVehiculo, idEquipo) {
            vehiculoActual = idVehiculo;
            nombreVehiculoActual = nombreVehiculo;
            equipoActual = idEquipo;

            $('#modalPunterosLabel').text(`Punteros - ${nombreVehiculo}`);

            fetch(`${BASE_URL}/vehiculosasignar/${vehiculoActual}/punteros?equipo=${equipoActual}`)
                .then(r => r.json())
                .then(data => {
                    // Select
                    $('#selectPunteros').empty();
                    data.todos.forEach(p => {
                        $('#selectPunteros').append(`<option value="${p.id}">${p.nombre}</option>`);
                    });
                    $('#selectPunteros').select2({
                        dropdownParent: $('#modalPunteros'),
                        width: '100%'
                    });

                    // Tabla
                    if (tabla) tabla.destroy();
                    tabla = $('#tablaAsignados').DataTable({
                        data: data.asignados,
                        columns: [{
                                data: 'nombre'
                            },
                            {
                                data: 'id',
                                render: id => `
                    <button class="btn btn-danger btn-sm" onclick="quitarPuntero(${id})">
                        <i class="fas fa-trash"></i>
                    </button>`
                            }
                        ]
                    });

                    $('#modalPunteros').modal('show');
                });
        }

        // Asignar puntero
        $('#btnAsignar').click(() => {
            const punteroId = $('#selectPunteros').val();
            if (!punteroId) {
                Swal.fire('Error', 'Debe seleccionar un puntero', 'warning');
                return;
            }

            fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(() => abrirModalPunteros(vehiculoActual, nombreVehiculoActual, equipoActual));
        });

        // Quitar puntero
        function quitarPuntero(punteroId) {
            fetch(`${BASE_URL}/vehiculos/${vehiculoActual}/punteros/${punteroId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            }).then(() => abrirModalPunteros(vehiculoActual, nombreVehiculoActual, equipoActual));
        }

        // Generar PDF contrato vehicular
        function generarPDFContratoVehicular(id) {
            window.open(`${BASE_URL}/vehiculos/contrato/${id}`, '_blank');
        }
        $(document).ready(function() {

            $('#selectEquipoReporte').select2({
                dropdownParent: $('#modalReporteEquipos'),
                theme: 'bootstrap4',
                placeholder: 'Seleccione un equipo'
            });

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

        });
    </script>
@endsection
