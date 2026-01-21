@extends('adminlte::page')

@section('content_header')
    <div class="row mb-2">
        <div class="col-md-4">
            <label for="equipo_id" class="form-label fw-bold">
                Equipos
            </label>

            <x-adminlte-select2 name="equipo_id" id="equipo_id" onchange="filtrarDirigentes()" disable-faster-look>
                <option value="">Todos</option>
                @foreach ($equipos as $eq)
                    <option value="{{ $eq->id }}" @if ($equipoId == $eq->id) selected @endif>
                        {{ $eq->descripcion }}
                    </option>
                @endforeach
            </x-adminlte-select2>
        </div>
    </div>

@stop

@section('content')
<h2>Total General de Votos: {{ $totalVotantesGeneral }}</h2>
    <div class="card">
        <div class="card-body">
            <table id="dirigentes-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cédula</th>
                        <th>Nombre</th>                        
                        <th>Teléfono</th>
                        <th>Barrio</th>
                        <th>Equipo</th>
                        <th>Punteros</th>
                        <th>Votantes</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($dirigentes as $dir)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $dir->cedula }}</td>
                            <td>{{ $dir->nombre }}</td>                            
                            <td>{{ $dir->telefono }}</td>
                            <td>{{ $dir->barrio }}</td>
                            <td>{{ $dir->equipo->descripcion ?? '' }}</td>
                            <td class="text-center">
                                <span class="badge badge-info">
                                    {{ $dir->punteros_count }}
                                </span>
                            </td>
                            <td class="text-center">
                                <span class="badge badge-success">
                                    {{ $dir->votantes_count ?? 0 }}
                                </span>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm"
                                    onclick="abrirModalPunteros({{ $dir->id }}, '{{ $dir->nombre }}')">
                                    <i class="fas fa-user-plus"></i>
                                </button>

                                <button class="btn btn-danger btn-sm" onclick="confirmarBorrado(this)"
                                    data-url="{{ route('dirigente.destroy', $dir->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>
    


    <form id="formEliminarDirigente" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>


    <!-- Modal para agregar dirigente -->
    <div class="modal fade" id="modalAgregarDirigente" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('dirigente.store') }}" method="POST" id="formAgregarDirigente">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Dirigente</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="id_equipo" value="{{ $equipoId ?? '' }}">
                        <div class="form-group">
                            <label>Cédula</label>
                            <input type="text" name="cedula" id="cedula" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Barrio</label>
                            <input type="text" name="barrio" id="barrio" class="form-control">
                        </div>

                    </div>
                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary">Guardar Dirigente</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        @if (session('abrirModalDirigente'))
                            <div class="alert alert-success">
                                {{ session('successAlert') }}
                            </div>
                        @endif


                    </div>
                </div>
            </form>
        </div>
    </div>
    <!-- Modal para punteros -->
    <!-- Modal para punteros -->
    <div class="modal fade" id="modalPunteros" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <!-- Header del modal -->
                <div class="modal-header">
                    <h5 class="modal-title">Punteros del dirigente </h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Formulario para agregar puntero (arriba) -->
                    <form id="formAgregarPuntero" method="POST" action="{{ route('puntero.store') }}" class="mb-3">
                        @csrf
                        <input type="hidden" name="id_dirigente" id="puntero_id_dirigente">
                        <input type="hidden" name="id_equipo" id="puntero_id_equipo">
                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Cédula</label>
                                <input type="text" name="cedula" id="puntero_cedula" class="form-control" required>
                            </div>
                            <div class="form-group col-md-9">
                                <label>Nombre</label>
                                <input type="text" name="nombre" id="puntero_nombre" class="form-control" required>
                            </div>
                            <div class="form-group col-md-4">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" id="puntero_telefono" class="form-control">
                            </div>
                            <div class="form-group col-md-8">
                                <label>Barrio</label>
                                <input type="text" name="barrio" id="puntero_barrio" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Puntero
                        </button>
                        @if (session('abrirModalPuntero'))
                            <div class="alert alert-success">
                                {{ session('successAlert') }}
                            </div>
                        @endif
                    </form>

                    <hr>

                    <!-- Tabla de punteros existentes -->
                    <table id="punteros-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>cedula</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Barrio</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>



                </div>
            </div>
        </div>
    </div>


@stop

@push('js')
    <script>
        const successAlert = @json(session('successAlert'));
        const errorAlert = @json(session('errorAlert'));

        if (errorAlert) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorAlert,
                confirmButtonColor: '#dc3545'
            });
        }

        function confirmarBorrado(button) {
            const url = button.getAttribute('data-url');

            Swal.fire({
                title: '¿Eliminar dirigente?',
                text: 'Esta acción no se puede deshacer. Se borraran los punteros y votantes asociados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('formEliminarDirigente');
                    form.action = url;
                    form.submit();
                }
            });
        }

        function eliminarPuntero(idpuntero) {

            Swal.fire({
                title: '¿Eliminar puntero?',
                text: 'Esta acción no se puede deshacer. Se borrarán los votantes asociados.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {

                    // 🔹 Ruta generada por Blade
                    let url = "{{ route('puntero.destroy', ':id') }}";
                    url = url.replace(':id', idpuntero);

                    // Crear formulario temporal
                    const form = document.createElement('form');
                    form.method = 'POST';
                    form.action = url;

                    // CSRF
                    const tokenInput = document.createElement('input');
                    tokenInput.type = 'hidden';
                    tokenInput.name = '_token';
                    tokenInput.value = '{{ csrf_token() }}';
                    form.appendChild(tokenInput);

                    // Spoof DELETE
                    const methodInput = document.createElement('input');
                    methodInput.type = 'hidden';
                    methodInput.name = '_method';
                    methodInput.value = 'DELETE';
                    form.appendChild(methodInput);

                    document.body.appendChild(form);
                    form.submit();
                }
            });
        }


        $('#modalAgregarDirigente').on('shown.bs.modal', function() {
            document.getElementById('formAgregarDirigente').reset();
            $('#cedula').trigger('focus');
        });


        function buscarPorCedula() {
            let cedula = $('#cedula').val().trim();

            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#nombre').val(response.data.nombre);
                    $('#telefono').val(response.data.telefono);
                    $('#barrio').val(response.data.direccion);
                }
            });
        }

        function buscarPorCedulap() {
            let cedula = $('#puntero_cedula').val().trim();

            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#puntero_nombre').val(response.data.nombre);
                    $('#puntero_telefono').val(response.data.telefono);
                    $('#puntero_barrio').val(response.data.direccion);
                }
            });
        }

        let bloqueaFiltro = false;

        $(document).ready(function() {
            inicializarTablaDirigentes();

            // Al salir del campo
            $('#cedula').on('blur', function() {
                buscarPorCedula();
            });

            // Al presionar ENTER
            $('#cedula').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedula();
                    $('#nombre').focus();
                }
            });
            // Al salir del campo
            $('#puntero_cedula').on('blur', function() {
                buscarPorCedulap();
            });

            // Al presionar ENTER
            $('#puntero_cedula').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedulap();
                    $('#puntero_nombre').focus();
                }
            });
            $('#dirigentes-table').DataTable();

            $('#equipo_id').select2({
                width: '100%'
            });

            @if (session()->has('abrirModalDirigente'))
                bloqueaFiltro = true;

                // Abrir modal
                $('#modalAgregarDirigente').modal('show');


                // Limpiar formulario
                $('#formAgregarDirigente')[0].reset();
                $('#telefono').focus();

                // Restaurar equipo sin disparar redirección
                @if (session()->has('equipoId'))
                    $('input[name="id_equipo"]').val('{{ session('equipoId') }}');
                    $('#equipo_id').val('{{ session('equipoId') }}').trigger('change.select2');
                @endif

                // Liberar el filtro después de inicializar
                setTimeout(() => bloqueaFiltro = false, 300);
            @else

                @if (session()->has('abrirModalPuntero'))
                    bloqueaFiltro = true;
                    abrirModalPunteros('{{ session('punteroIdDirigente') }}', '{{ session('dirigentenombre') }}');
                    // Limpiar formulario
                    $('#formAgregarPuntero')[0].reset();
                    $('#puntero_telefono').focus();

                    // Restaurar quipo sin disparar redirección
                    @if (session()->has('punteroIdDirigente'))
                        $('input[name="puntero_id_dirigente"]').val('{{ session('punteroIdDirigente') }}');
                    @endif
                @else
                    if (successAlert) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: successAlert,
                            confirmButtonColor: '#28a745'
                        });
                    }
                @endif
            @endif

        });

        function filtrarDirigentes() {
            if (bloqueaFiltro) return;
            equipoId = $('#equipo_id').val();
            let url = "{{ url('dirigente/create') }}/" + equipoId;
            window.location.href = url;
        }

        function abrirModalPunteros(dirigenteId, nombreDirigente) {

            // Abrir modal
            $('#modalPunteros').modal('show');

            // Asegurarse de no duplicar el evento
            $('#modalPunteros').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('#puntero_cedula').trigger('focus');
            });

            // Cambiar el título del modal
            $('#modalPunteros .modal-title')
                .text('Punteros del dirigente: ' + nombreDirigente);

            // Limpiar formulario
            $('#formAgregarPuntero')[0].reset();
            $('#puntero_id_dirigente').val(dirigenteId);
            let equipoId = $('#equipo_id').val(); // 🔹 equipo seleccionado
            $('#puntero_id_equipo').val(equipoId);

            // Cargar punteros existentes
            $.get("{{ url('dirigente') }}/" + dirigenteId + "/punteros", function(data) {

                let tbody = $('#punteros-table tbody');
                tbody.empty();

                data.forEach(function(puntero) {
                    tbody.append(`
                <tr>
                    <td></td>
                    <td>${puntero.cedula}</td>
                    <td>${puntero.nombre}</td>
                    <td>${puntero.telefono ?? ''}</td>
                    <td>${puntero.barrio ?? ''}</td>
                    <td>
                        <button class="btn btn-danger btn-sm"
                            onclick="eliminarPuntero(${puntero.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
                });

                inicializarTablaPunteros(nombreDirigente);
            });
        }






        function inicializarTablaDirigentes() {
            $('#dirigentes-table').DataTable({
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",


                buttons: [{
                        text: '<i class="fas fa-user-plus"></i> Agregar Dirigente',
                        className: 'btn btn-primary',
                        action: function(e, dt, node, config) {
                            $('#modalAgregarDirigente').modal('show');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-info',
                        title: 'Lista de Dirigentes del equipo ' + equipoNombre,
                        filename: 'dirigentes_export_' + equipoNombre.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger',
                        title: 'Lista de Dirigentes del equipo ' + equipoNombre,
                        filename: 'dirigentes_export_' + equipoNombre.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-secondary'
                    }
                ],
                responsive: true,

                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }

            });
        }

        const equipoNombre = @json($equipoId ? $equipos->firstWhere('id', $equipoId)->descripcion : 'Todos los equipos');

        function inicializarTablaPunteros(nombreDirigente) {

            nombreDirigente = nombreDirigente ?? 'Sin nombre';

            let table = $('#punteros-table').DataTable({
                dom: "<'row'<'col-md-6'l><'col-md-6 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",

                buttons: [{
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-info',
                        title: 'Punteros del dirigente ' + nombreDirigente,
                        filename: 'punteros_' + nombreDirigente.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger',
                        title: 'Punteros del dirigente ' + nombreDirigente,
                        filename: 'punteros_' + nombreDirigente.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-secondary'
                    }
                ],

                responsive: true,
                destroy: true,

                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },

                columnDefs: [{
                        targets: 0,
                        orderable: false,
                        searchable: false
                    },
                    {
                        targets: 4,
                        orderable: false,
                        searchable: false
                    }
                ]
            });

            // 🔢 Numeración automática
            table.on('order.dt search.dt draw.dt', function() {
                table.column(0, {
                        search: 'applied',
                        order: 'applied'
                    })
                    .nodes()
                    .each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
            }).draw();
        }
    </script>
@endpush
