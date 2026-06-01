@extends('adminlte::page')

@section('title', 'Reporte por Local de Votación')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-map-marker-alt"></i> Reporte por Local de Votación
    </h4>
@stop

@section('content')
    
    {{-- Contenedor del loading --}}
    <div id="loadingContainer" class="text-center" style="padding: 50px;">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-body">
                        <div class="spinner-border text-primary" style="width: 4rem; height: 4rem;" role="status">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <h3 class="mt-3">
                            <i class="fas fa-chart-line"></i> Generando reporte...
                        </h3>
                        <p class="text-muted">Estamos procesando la información, por favor espere</p>
                        
                        <div class="progress mt-4" style="height: 30px;">
                            <div id="progressBar" class="progress-bar progress-bar-striped progress-bar-animated" 
                                 role="progressbar" style="width: 0%; font-weight: bold;">
                                0%
                            </div>
                        </div>
                        
                        <div class="mt-3">
                            <small class="text-muted" id="loadingMessage">Conectando con el servidor...</small>
                        </div>
                        
                        <div class="mt-4">
                            <div class="d-flex justify-content-center">
                                <div class="spinner-grow text-primary mx-1" style="width: 1rem; height: 1rem;"></div>
                                <div class="spinner-grow text-success mx-1" style="width: 1rem; height: 1rem;"></div>
                                <div class="spinner-grow text-danger mx-1" style="width: 1rem; height: 1rem;"></div>
                                <div class="spinner-grow text-warning mx-1" style="width: 1rem; height: 1rem;"></div>
                                <div class="spinner-grow text-info mx-1" style="width: 1rem; height: 1rem;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    {{-- Contenedor del contenido (inicialmente oculto) --}}
    <div id="reporteContent" style="display: none;"></div>

@stop

@push('js')
<script>
    $(document).ready(function() {
        let progress = 0;
        let progressInterval;
        
        // Simular progreso mientras carga
        function startProgress() {
            progressInterval = setInterval(function() {
                if (progress < 90) {
                    progress += Math.random() * 10;
                    if (progress > 90) progress = 90;
                    $('#progressBar').css('width', progress + '%');
                    $('#progressBar').text(Math.floor(progress) + '%');
                }
                
                // Cambiar mensajes según el progreso
                if (progress < 30) {
                    $('#loadingMessage').text('Consultando votantes...');
                } else if (progress < 60) {
                    $('#loadingMessage').text('Agrupando por escuela...');
                } else if (progress < 80) {
                    $('#loadingMessage').text('Procesando resultados...');
                } else {
                    $('#loadingMessage').text('Preparando reporte...');
                }
            }, 200);
        }
        
        function completeProgress() {
            clearInterval(progressInterval);
            $('#progressBar').css('width', '100%');
            $('#progressBar').text('100%');
            $('#loadingMessage').text('¡Reporte listo! Mostrando datos...');
        }
        
        // Iniciar animación de progreso
        startProgress();
        
        // Cargar datos via AJAX
        $.ajax({
            url: '{{ route("informe.porlocal.data") }}',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    completeProgress();
                    setTimeout(function() {
                        $('#loadingContainer').fadeOut(500, function() {
                            $('#reporteContent').html(response.html).fadeIn(500);
                            initializeDataTable();
                            initializeModalEvents(); // Inicializar eventos de los modales
                        });
                    }, 500);
                } else {
                    handleError(response.message);
                }
            },
            error: function(xhr) {
                let errorMsg = 'Error al cargar el reporte';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                handleError(errorMsg);
            }
        });
        
        function handleError(errorMsg) {
            clearInterval(progressInterval);
            $('#loadingContainer .card-body').html(`
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-triangle"></i>
                    <h4>Error al generar el reporte</h4>
                    <p>${errorMsg}</p>
                    <button class="btn btn-primary" onclick="location.reload()">
                        <i class="fas fa-sync-alt"></i> Reintentar
                    </button>
                    <a href="{{ url('/') }}" class="btn btn-secondary">
                        <i class="fas fa-home"></i> Volver al inicio
                    </a>
                </div>
            `);
        }
        
        function initializeDataTable() {
            let table = $('#escuelas-table').DataTable({
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    {
                        extend: 'excelHtml5',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        className: 'btn btn-success',
                        title: 'Reporte por Escuela',
                        filename: 'reporte_escuelas_{{ date("Y-m-d_H-i-s") }}'
                    },
                    {
                        extend: 'pdfHtml5',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        className: 'btn btn-danger',
                        title: 'Reporte por Escuela',
                        orientation: 'landscape',
                        pageSize: 'A4'
                    },
                    {
                        extend: 'print',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        className: 'btn btn-secondary'
                    }
                ],
                responsive: true,
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [[1, 'asc']]
            });
        }
        
        function initializeModalEvents() {
            // Evento para los botones de ver detalle
            $(document).on('click', '.btn-ver-detalle', function() {
                let escuelaNombre = $(this).data('nombre');
                
                // Actualizar título del modal
                $('#modalLocalNombre').text(escuelaNombre);
                
                // Mostrar modal
                $('#detalleModal').modal('show');
                
                // Cargar detalles via AJAX
                $.ajax({
                    url: '{{ route("informe.porlocal.detalle") }}',
                    type: 'GET',
                    data: { escuela: escuelaNombre },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            $('#modalBodyContent').html(response.html);
                        } else {
                            $('#modalBodyContent').html(`
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Error: ${response.message}
                                </div>
                            `);
                        }
                    },
                    error: function(xhr) {
                        let msg = 'Error al cargar los detalles de la escuela';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            msg = xhr.responseJSON.message;
                        }
                        $('#modalBodyContent').html(`
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle"></i>
                                ${msg}
                            </div>
                        `);
                    }
                });
            });
        }
    });
</script>
@endpush