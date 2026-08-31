@extends('adminlte::page')

@section('title', 'Reporte de Visitas de Punteros')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-chart-line"></i> Reporte de Visitas de Punteros
    </h4>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Colegio electoral:</strong></label>
                    <select name="equipo_id" id="equipo_id" class="form-control select2" style="width: 200px;">
                        <option value="">Todos</option>
                        @foreach($equipos as $equipo)
                            <option value="{{ $equipo->id }}">{{ $equipo->descripcion }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Puntero:</strong></label>
                    <select name="puntero_id" id="puntero_id" class="form-control select2" style="width: 250px;">
                        <option value="">Todos</option>
                        @foreach($punteros as $puntero)
                            <option value="{{ $puntero->id }}">{{ $puntero->nombre }} ({{ $puntero->cedula }})</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Desde:</strong></label>
                    <input type="date" name="fecha_desde" id="fecha_desde" class="form-control">
                </div>
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Hasta:</strong></label>
                    <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control">
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar
                </button>
                <button type="button" class="btn btn-success ml-1" id="btnRefresh">
                    <i class="fas fa-sync-alt"></i> Actualizar
                </button>
            </form>
        </div>
    </div>

    <div id="loadingContainer" class="text-center" style="padding: 50px;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <h3 class="mt-3">Generando reporte...</h3>
                        <p class="text-muted">Procesando la información, por favor espere</p>
                        <div class="progress mt-4" style="height: 30px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar" style="width: 0%; font-weight: bold;">0%</div>
                        </div>
                        <div class="mt-3">
                            <small class="text-muted" id="loadingMessage">Conectando con el servidor...</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div id="reporteContent" style="display: none;"></div>

    <div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">
                <div class="modal-header bg-info text-white">
                    <h5 class="modal-title" id="modalTitulo">Detalle de Visitas</h5>
                    <button type="button" class="close text-white" data-dismiss="modal">
                        <span>&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalBodyContent">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
<script>
    $(document).ready(function() {
        function startProgress() {
            let p = 0;
            let interval = setInterval(function() {
                if (p < 90) {
                    p += Math.random() * 10;
                    if (p > 90) p = 90;
                    $('#progressBar').css('width', p + '%').text(Math.floor(p) + '%');
                }
                if (p < 30) $('#loadingMessage').text('Consultando visitas...');
                else if (p < 60) $('#loadingMessage').text('Agrupando datos por puntero...');
                else if (p < 80) $('#loadingMessage').text('Generando gráficos...');
                else $('#loadingMessage').text('Preparando reporte...');
            }, 200);
            return interval;
        }

        function loadReport() {
            let interval = startProgress();
            $('#loadingContainer').show();
            $('#reporteContent').hide().empty();

            $.ajax({
                url: '{{ route("reportes.visitas.data") }}',
                type: 'GET',
                data: {
                    equipo_id: $('#equipo_id').val(),
                    puntero_id: $('#puntero_id').val(),
                    fecha_desde: $('#fecha_desde').val(),
                    fecha_hasta: $('#fecha_hasta').val()
                },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        clearInterval(interval);
                        $('#progressBar').css('width', '100%').text('100%');
                        $('#loadingMessage').text('¡Reporte listo!');
                        setTimeout(function() {
                            $('#loadingContainer').fadeOut(500);
                            $('#reporteContent').html(response.html).fadeIn(500);
                        }, 500);
                    } else {
                        handleError(response.message, interval);
                    }
                },
                error: function(xhr) {
                    let msg = 'Error al cargar el reporte';
                    if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                    handleError(msg, interval);
                }
            });
        }

        function handleError(msg, interval) {
            clearInterval(interval);
            $('#loadingContainer .card-body').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Error</h4>
                    <p>${msg}</p>
                    <button class="btn btn-primary" onclick="location.reload()"><i class="fas fa-sync-alt"></i> Reintentar</button>
                </div>
            `);
        }

        $(document).on('click', '.btn-detalle', function(e) {
            e.preventDefault();
            let punteroId = $(this).data('puntero');
            let tipo = $(this).data('tipo');
            let nombre = $(this).data('nombre');
            let label = tipo === 'todas' ? 'Todas' : (tipo === 'positivas' ? 'Positivas' : 'Negativas');

            $('#modalTitulo').text(label + ' - ' + nombre);
            $('#modalBodyContent').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            `);
            $('#detalleModal').modal('show');

            $.ajax({
                url: '{{ route("reportes.visitas.detalle") }}',
                type: 'GET',
                data: { puntero_id: punteroId, tipo: tipo },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#modalBodyContent').html(response.html);
                    } else {
                        $('#modalBodyContent').html('<div class="alert alert-danger">' + response.message + '</div>');
                    }
                },
                error: function() {
                    $('#modalBodyContent').html('<div class="alert alert-danger">Error al cargar el detalle</div>');
                }
            });
        });

        $('#filterForm').on('submit', function(e) {
            e.preventDefault();
            loadReport();
        });

        $('#btnRefresh').on('click', function() {
            loadReport();
        });

        loadReport();
    });
</script>
@endpush
