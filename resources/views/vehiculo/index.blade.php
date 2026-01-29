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

                {{-- ===================== FILA 1 ===================== --}}
                <div class="row">

                    <x-adminlte-input name="cedulachofer" label="Cédula del Chofer" placeholder="Ej: 1.234.567"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-id-card"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="nombre" label="Nombre del Chofer" placeholder="Nombre completo"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-user"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="chapa" label="Chapa" placeholder="ABC123" fgroup-class="col-md-2" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-car"></i>
                            </div>
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
                            <div class="input-group-text">
                                <i class="fas fa-users"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                </div>

                {{-- ===================== FILA 2 ===================== --}}
                <div class="row mt-2">

                    <x-adminlte-input name="telefono1" label="Teléfono Principal" placeholder="0981xxxxxx"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-phone"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-input name="telefono2" label="Teléfono Secundario" placeholder="Opcional"
                        fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-phone-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <x-adminlte-select name="montopagar" label="Monto a Pagar (Gs.)" fgroup-class="col-md-2">
                        <option value="300000" selected>300.000</option>
                        <option value="400000">400.000</option>
                        <option value="500000">500.000</option>
                    </x-adminlte-select>

                    <x-adminlte-input name="cantidadpagos" label="Cantidad de Pagos" type="number" value="2"
                        fgroup-class="col-md-2" required>
                        <x-slot name="appendSlot">
                            <div class="input-group-text">
                                <i class="fas fa-list-ol"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>

                </div>

            </div>
        </form>

    </div>

    {{-- =================== BUSCADOR =================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorVehiculo" class="form-control" placeholder="Buscar por chofer o chapa">
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

                        <p class="mb-1">
                            <i class="fas fa-id-card"></i>
                            {{ number_format($vehiculo->cedulachofer, 0, ',', '.') }}
                        </p>

                        <p class="mb-1">
                            <i class="fas fa-hashtag"></i>
                            {{ $vehiculo->chapa }}
                        </p>

                        {{-- Teléfonos --}}
                        @if ($vehiculo->telefono1 || $vehiculo->telefono2 || $vehiculo->telefono3)
                            <p class="mb-1">
                                <i class="fas fa-phone-alt"></i>
                                @if ($vehiculo->telefono1)
                                    {{ $vehiculo->telefono1 }}
                                @endif
                                @if ($vehiculo->telefono2)
                                    - {{ $vehiculo->telefono2 }}
                                @endif
                                @if ($vehiculo->telefono3)
                                    - {{ $vehiculo->telefono3 }}
                                @endif
                            </p>
                        @endif

                        {{-- Número de auto --}}
                        @if ($vehiculo->numero_auto)
                            <p class="mb-0">
                                <i class="fas fa-car-side"></i>
                                {{ $vehiculo->numero_auto }}
                            </p>
                        @endif
                    </div>
                    {{-- Botón Contrato de Alquiler --}}
                    <button class="btn btn-primary btn-sm" onclick="generarPDFContratoVehicular({{ $vehiculo->id }})">
                        <i class="fas fa-file-pdf"></i> Contrato de Alquiler
                    </button>
                    <!-- Botón Asignar/Eliminar Punteros -->
                    <button class="btn btn-warning btn-sm mt-1"
                        onclick="abrirModalPunteros({{ $vehiculo->id }}, '{{ $vehiculo->nombre }}')">
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
    <!-- Modal genérico para asignar/borrar punteros -->
    <div class="modal fade" id="modalPunteros">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title" id="modalPunterosLabel"></h5>
                    <button class="close" data-dismiss="modal">&times;</button>
                </div>

                <div class="modal-body">

                    <!-- SELECT2 -->
                    <div class="form-group">
                        <label>Agregar puntero</label>
                        <select id="selectPunteros" class="form-control"></select>
                    </div>

                    <button class="btn btn-success mb-3" id="btnAsignar">
                        <i class="fas fa-plus"></i> Asignar
                    </button>

                    <!-- DATATABLE -->
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
        let vehiculoActual = null;
        let tabla;

        function abrirModalPunteros(id, nombre) {
            vehiculoActual = id;
            $('#modalPunterosLabel').text(`Punteros - ${nombre}`);

            fetch(`/vehiculos/${id}/punteros`)
                .then(r => r.json())
                .then(data => {

                    // SELECT2
                    $('#selectPunteros').empty();
                    data.todos.forEach(p => {
                        $('#selectPunteros').append(
                            `<option value="${p.id}">${p.nombre}</option>`
                        );
                    });

                    $('#selectPunteros').select2({
                        dropdownParent: $('#modalPunteros'),
                        width: '100%'
                    });

                    // DATATABLE
                    if (tabla) tabla.destroy();
                    tabla = $('#tablaAsignados').DataTable({
                        data: data.asignados,
                        columns: [{
                                data: 'nombre'
                            },
                            {
                                data: 'id',
                                render: id => `
                            <button class="btn btn-danger btn-sm"
                                onclick="quitarPuntero(${id})">
                                <i class="fas fa-trash"></i>
                            </button>`
                            }
                        ]
                    });

                    $('#modalPunteros').modal('show');
                });
        }
        $('#btnAsignar').click(() => {
            const puntero = $('#selectPunteros').val();

            fetch(`/vehiculos/${vehiculoActual}/punteros`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    puntero_id: puntero
                })
            }).then(() => abrirModalPunteros(vehiculoActual, ''));
        });

        function quitarPuntero(punteroId) {
            fetch(`/vehiculos/${vehiculoActual}/punteros/${punteroId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }).then(() => abrirModalPunteros(vehiculoActual, ''));
        }




        // Guardar cambios
        document.getElementById('guardarPunteros').addEventListener('click', function() {
            let formData = new FormData(document.getElementById('formPunteros'));

            fetch('/vehiculos/punteros/guardar', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        Swal.fire('Éxito', data.message, 'success');
                        $('#modalPunteros').modal('hide');
                    }
                })
                .catch(err => console.error(err));
        });

        function generarPDFContratoVehicular(id) {
            var url = `{{ url('/') }}/vehiculos/contrato/${id}`; // Ajustar según tu ruta real
            window.open(url, '_blank');
        }
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
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        document.getElementById('buscadorVehiculo').addEventListener('keyup', function() {
            let texto = this.value.toLowerCase();
            document.querySelectorAll('.vehiculo-card').forEach(card => {
                card.style.display = card.dataset.search.includes(texto) ? '' : 'none';
            });
        });
    </script>
@endsection
