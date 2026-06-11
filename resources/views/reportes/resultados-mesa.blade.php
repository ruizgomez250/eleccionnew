@extends('adminlte::page')

@section('title', 'Reporte de Resultados por Mesa')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-chart-bar"></i> Reporte de Resultados por Mesa
    </h4>
@stop

@section('content')
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-filter"></i> Filtros</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Distrito</label>
                        <select id="distritoSelect" class="form-control select2">
                            <option value="">Seleccione distrito</option>
                            @foreach ($distritos as $distrito)
                                <option value="{{ $distrito }}">{{ $distrito }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-school"></i> Local / Escuela</label>
                        <select id="localSelect" class="form-control select2" disabled>
                            <option value="">Primero seleccione distrito</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label><i class="fas fa-briefcase"></i> Candidatura</label>
                        <select id="cargoSelect" class="form-control select2">
                            <option value="">Seleccione candidatura</option>
                            @foreach ($cargos as $cargo)
                                <option value="{{ $cargo }}">{{ ucfirst($cargo) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button id="btnGenerar" class="btn btn-primary btn-lg" disabled>
                        <i class="fas fa-search"></i> Generar Reporte
                    </button>
                    <a id="btnExportarPdf" class="btn btn-danger btn-lg" style="display: none;" target="_blank">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                </div>
            </div>
        </div>
    </div>

    <div id="reporteContainer">
        <div class="card card-default">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-arrow-up fa-3x mb-3"></i>
                <p class="mb-0">Seleccione distrito, local y candidatura para generar el reporte.</p>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .table-resultados th, .table-resultados td {
            white-space: nowrap;
            font-size: 0.85rem;
        }
        .table-resultados .candidato-nombre {
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .table-resultados .partido-row {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .table-resultados .total-row {
            background-color: #e9ecef;
            font-weight: bold;
        }
        .table-resultados .voto-cell {
            text-align: center;
        }
    </style>
@stop

@section('js')
<script>
    $(document).ready(function() {
        $('.select2').select2({ placeholder: 'Seleccione...', width: '100%' });

        $('#distritoSelect').on('change', function() {
            var distrito = $(this).val();
            var $local = $('#localSelect').empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $('#btnGenerar').prop('disabled', true);
            $('#btnExportarPdf').hide();

            if (distrito) {
                $.get('{{ route("certificados.locales") }}', { distrito: distrito }, function(data) {
                    $local.empty().append('<option value="">Seleccione local</option>');
                    $.each(data, function(i, l) {
                        $local.append('<option value="' + l.local_interna + '">' + l.local_interna + ' (' + l.cantmesa + ' mesas)</option>');
                    });
                    $local.prop('disabled', false);
                });
            } else {
                $local.empty().append('<option value="">Primero seleccione distrito</option>').prop('disabled', true);
            }
        });

        $('#localSelect, #cargoSelect').on('change', function() {
            $('#btnGenerar').prop('disabled', !($('#localSelect').val() && $('#cargoSelect').val()));
            $('#btnExportarPdf').hide();
        });

        $('#btnGenerar').on('click', function() {
            var local = $('#localSelect').val();
            var cargo = $('#cargoSelect').val();
            if (!local || !cargo) return;

            $('#reporteContainer').html(
                '<div class="card card-default"><div class="card-body text-center py-5">' +
                '<i class="fas fa-spinner fa-spin fa-3x mb-3"></i><p>Generando reporte...</p></div></div>'
            );
            $('#btnExportarPdf').hide();

            $.ajax({
                url: '{{ route("reportes.resultados.mesa.data") }}',
                type: 'GET',
                data: { local: local, cargo: cargo },
                dataType: 'json',
                success: function(response) {
                    if (response.success) {
                        $('#reporteContainer').html(response.html);
                        var pdfUrl = '{{ route("reportes.resultados.mesa.pdf") }}?local=' + encodeURIComponent(local) + '&cargo=' + encodeURIComponent(cargo);
                        $('#btnExportarPdf').attr('href', pdfUrl).show();
                    } else {
                        $('#reporteContainer').html(
                            '<div class="alert alert-danger">' + (response.message || 'Error al generar el reporte') + '</div>'
                        );
                    }
                },
                error: function(xhr) {
                    var msg = 'Error al generar el reporte';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $('#reporteContainer').html(
                        '<div class="alert alert-danger">' + msg + '</div>'
                    );
                }
            });
        });
    });
</script>
@stop
