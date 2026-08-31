@extends('adminlte::page')

@section('title', 'Visitas de Punteros')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clipboard-check"></i> Visitas de Punteros
        </h4>
        <a href="{{ route('visita-puntero.create') }}" class="btn btn-success">
            <i class="fas fa-plus"></i> Nueva Visita
        </a>
    </div>
@stop

@section('content')
    @if(session('successAlert'))
        <script>
            Swal.fire({
                title: 'Éxito',
                text: '{{ session("successAlert") }}',
                icon: 'success',
                timer: 3000,
                showConfirmButton: false
            });
        </script>
    @endif

    @if(session('errorAlert'))
        <script>
            Swal.fire({
                title: 'Error',
                text: '{{ session("errorAlert") }}',
                icon: 'error',
                timer: 4000,
                showConfirmButton: false
            });
        </script>
    @endif

    <div class="card">
        <div class="card-header">
            <h5 class="card-title"><i class="fas fa-filter"></i> Filtros</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label><strong>Colegio electoral</strong></label>
                        <select id="filtroEquipo" class="form-control select2">
                            <option value="">Todos los colegios electorales</option>
                            @foreach($equipos as $equipo)
                                <option value="{{ $equipo->id }}">{{ $equipo->descripcion }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><strong>Puntero</strong></label>
                        <select id="filtroPuntero" class="form-control select2">
                            <option value="">Todos los punteros</option>
                            @foreach($punteros as $puntero)
                                <option value="{{ $puntero->id }}">{{ $puntero->nombre }} ({{ $puntero->cedula }})</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>Resultado</strong></label>
                        <input type="text" id="filtroResultado" class="form-control" placeholder="Ej: positivo">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>Desde</strong></label>
                        <input type="date" id="filtroFechaDesde" class="form-control">
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="form-group">
                        <label><strong>Hasta</strong></label>
                        <input type="date" id="filtroFechaHasta" class="form-control">
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12">
                    <button id="btnBuscar" class="btn btn-primary">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                    <button id="btnLimpiar" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div id="loadingSpinner" class="text-center py-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2 text-muted">Cargando visitas...</p>
            </div>
            <div id="tablaContainer" style="display: none;">
                <table id="tablaVisitas" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Puntero</th>
                            <th>Cédula</th>
                            <th>Votante</th>
                            <th>Casa de</th>
                            <th>Resultado</th>
                            <th>Próx. Visita</th>
                            <th>Usuario</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .badge-positivo { background-color: #28a745; color: #fff; }
        .badge-negativo { background-color: #dc3545; color: #fff; }
        .badge-neutro { background-color: #6c757d; color: #fff; }
        .badge-default { background-color: #17a2b8; color: #fff; }
    </style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({ theme: 'bootstrap4', width: '100%' });

        function cargarVisitas() {
            $('#loadingSpinner').show();
            $('#tablaContainer').hide();

            $.ajax({
                url: '{{ route("visita-puntero.index") }}',
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                data: {
                    equipo_id: $('#filtroEquipo').val(),
                    puntero_id: $('#filtroPuntero').val(),
                    resultado: $('#filtroResultado').val(),
                    fecha_desde: $('#filtroFechaDesde').val(),
                    fecha_hasta: $('#filtroFechaHasta').val(),
                },
                success: function(response) {
                    if (response.success) {
                        renderTabla(response.data);
                        $('#loadingSpinner').hide();
                        $('#tablaContainer').show();
                    }
                },
                error: function() {
                    $('#loadingSpinner').html('<div class="alert alert-danger">Error al cargar las visitas</div>');
                }
            });
        }

        function renderTabla(data) {
            if ($.fn.DataTable.isDataTable('#tablaVisitas')) {
                $('#tablaVisitas').DataTable().destroy();
            }

            let tbody = '';
            data.forEach(function(v) {
                let badgeClass = 'badge-default';
                if (v.resultado.toLowerCase().includes('positivo')) badgeClass = 'badge-positivo';
                else if (v.resultado.toLowerCase().includes('negativo')) badgeClass = 'badge-negativo';
                else if (v.resultado.toLowerCase().includes('neutro')) badgeClass = 'badge-neutro';

                tbody += `<tr>
                    <td>${v.fecha_visita}</td>
                    <td>${v.puntero_nombre}</td>
                    <td>${v.cedula}</td>
                    <td>${v.nombre_votante} ${v.apellido_votante || ''}</td>
                    <td>${v.casa_de || '-'}</td>
                    <td><span class="badge ${badgeClass}">${v.resultado}</span></td>
                    <td>${v.proxima_visita}</td>
                    <td>${v.usuario_nombre}</td>
                    <td>
                        <a href="/visita-puntero/${v.id}/edit" class="btn btn-sm btn-warning" title="Editar">
                            <i class="fas fa-edit"></i>
                        </a>
                        <button class="btn btn-sm btn-danger btn-eliminar" data-id="${v.id}" title="Eliminar">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>`;
            });

            $('#tablaVisitas tbody').html(tbody);

            $('#tablaVisitas').DataTable({
                responsive: true,
                order: [[0, 'desc']],
                language: {
                    url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                },
                dom: 'Bfrtip',
                buttons: [
                    { extend: 'excel', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm' },
                    { extend: 'pdf', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm' },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-info btn-sm' },
                ],
            });
        }

        $('#btnBuscar').on('click', cargarVisitas);
        $('#btnLimpiar').on('click', function() {
            $('#filtroEquipo').val('').trigger('change');
            $('#filtroPuntero').val('').trigger('change');
            $('#filtroResultado').val('');
            $('#filtroFechaDesde').val('');
            $('#filtroFechaHasta').val('');
            cargarVisitas();
        });

        $(document).on('click', '.btn-eliminar', function() {
            let id = $(this).data('id');
            Swal.fire({
                title: '¿Eliminar visita?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    $.ajax({
                        url: '/visita-puntero/' + id,
                        type: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                            'X-Requested-With': 'XMLHttpRequest'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado', response.message, 'success');
                                cargarVisitas();
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function() {
                            Swal.fire('Error', 'No se pudo eliminar', 'error');
                        }
                    });
                }
            });
        });

        cargarVisitas();
    });
</script>
@stop
