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
                                <button class="btn btn-primary btn-sm"
                                    onclick="generarPDFporDir({{ $dir->id }})">
                                    <i class="fas fa-file-pdf"></i>
                                </button>

                            </td>
                        </tr>
                    @endforeach
                </tbody>

            </table>
        </div>
    </div>  
@stop

@push('js')
    <script>
        function generarPDFporDir(id) {

            var url = `{{ url('/') }}/votantespordirigente/${id}`;
            window.open(url, '_blank');
        }
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


                buttons: [
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
