@extends('adminlte::page')

@section('title', 'Reporte por Puntero')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-check-double"></i> Reporte por Puntero - Votos Cargados
    </h4>
@stop

@section('content')
    <div class="card mb-3">
        <div class="card-body">
            <form id="filterForm" class="form-inline">
                <div class="form-group mr-3">
                    <label class="mr-2"><strong>Filtrar por carga:</strong></label>
                    <select name="miembro_id" id="miembro_id" class="form-control">
                        <option value="">Todas las cargas</option>
                        @foreach($miembros as $miembro)
                            <option value="{{ $miembro->id }}">
                                {{ $miembro->nombre }} ({{ $miembro->equipo->descripcion ?? 'Sin equipo' }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-search"></i> Generar
                </button>
            </form>
        </div>
    </div>

    <div id="loadingContainer" class="text-center" style="padding: 50px; display: none;">
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
                if (p < 30) $('#loadingMessage').text('Consultando punteros...');
                else if (p < 60) $('#loadingMessage').text('Cruzando con votos cargados...');
                else if (p < 80) $('#loadingMessage').text('Procesando resultados...');
                else $('#loadingMessage').text('Preparando reporte...');
            }, 200);
            return interval;
        }

        function loadReport(miembroId) {
            let interval = startProgress();
            $('#loadingContainer').show();
            $('#reporteContent').hide().empty();

            $.ajax({
                url: '{{ route("reportes.carga-votos.data") }}',
                type: 'GET',
                data: { miembro_id: miembroId },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        clearInterval(interval);
                        $('#progressBar').css('width', '100%').text('100%');
                        $('#loadingMessage').text('¡Reporte listo!');
                        setTimeout(function() {
                            $('#loadingContainer').fadeOut(500);
                            $('#reporteContent').html(response.html).fadeIn(500, function() {
                                $('#tabla-punteros').DataTable({
                                    dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                                         "<'row'<'col-sm-12'tr>>" +
                                         "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                                    buttons: [
                                        { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success', title: 'Reporte_Punteros_{{ date("Y-m-d_H-i-s") }}' },
                                        { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger', title: 'Reporte por Puntero', orientation: 'landscape', pageSize: 'A4' },
                                        { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary' }
                                    ],
                                    responsive: true,
                                    pageLength: 25,
                                    language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                                    order: [[0, 'asc']]
                                });
                            });
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
            let label = tipo === 'votaron' ? 'Votaron' : 'No Votaron';

            $('#modalTitulo').text(label + ' - ' + nombre);
            $('#modalBodyContent').html(`
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            `);
            $('#detalleModal').modal('show');

            $.ajax({
                url: '{{ route("reportes.carga-votos.detalle") }}',
                type: 'GET',
                data: {
                    puntero_id: punteroId,
                    tipo: tipo,
                    miembro_id: $('#miembro_id').val()
                },
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
            loadReport($('#miembro_id').val());
        });

        loadReport('');
    });
</script>
@endpush
