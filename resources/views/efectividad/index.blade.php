@extends('adminlte::page')

@section('title', 'Análisis de Efectividad Electoral')

@section('content_header')
    <div class="row">
        <div class="col-12">
            <h3 class="m-0">
                <i class="fas fa-chart-bar"></i> Análisis de Efectividad Electoral
            </h3>
        </div>
    </div>
@stop

@section('content')
<div class="row">
    <div class="col-12">
        {{-- Upload Card --}}
        <div class="card">
            <div class="card-header py-2 bg-primary text-white">
                <h5 class="mb-0"><i class="fas fa-upload"></i> Cargar Datos</h5>
            </div>
            <div class="card-body py-2">
                <form id="formCargar" enctype="multipart/form-data">
                    @csrf
                    <div class="row align-items-end">
                        <div class="col-md-6">
                            <label class="small mb-1">Seleccionar archivo CSV</label>
                            <div class="input-group input-group-sm">
                                <input type="file" class="form-control" id="archivo" name="archivo" accept=".csv,.txt" required>
                                <div class="input-group-append">
                                    <button type="submit" class="btn btn-success btn-sm">
                                        <i class="fas fa-upload"></i> Subir y procesar
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <small class="text-muted">
                                Formato: mesa,intendente,c1..c12,com1..com12,juv1..juv12
                            </small>
                        </div>
                    </div>
                </form>
                <div id="uploadStatus" class="mt-2"></div>
            </div>
        </div>

        {{-- Summary Table Card --}}
        <div class="card">
            <div class="card-header py-2 bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table"></i> Resumen General por Posición</h5>
                <span class="badge badge-light" id="totalMesasBadge">0 mesas</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0" id="tablaResumen">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">Pos.</th>
                                <th class="text-right">Votos Intendente</th>
                                <th class="text-right">Votos Concejal</th>
                                <th class="text-center">Efectividad</th>
                                <th class="text-right">Votos Perdidos</th>
                            </tr>
                        </thead>
                        <tbody id="resumenBody">
                            <tr id="resumenLoading">
                                <td colspan="5" class="text-center text-muted py-4">
                                    <i class="fas fa-spinner fa-spin"></i> Cargando...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Mesa Selector + Alertas --}}
        <div class="card">
            <div class="card-header py-2 bg-warning text-dark d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-search"></i> Análisis por Mesa</h5>
                <div class="form-inline">
                    <label class="mr-2 small">Seleccionar Mesa:</label>
                    <select class="form-control form-control-sm" id="mesaSelector" style="min-width:300px">
                        <option value="">-- Seleccione una mesa --</option>
                        @foreach ($mesas as $m)
                            <option value="{{ $m->id }}">{{ $m->mesa }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="card-body" id="mesaDetail" style="display:none">
                <div id="alertasContainer"></div>
                <div id="mesaInfo" class="mb-3"></div>
                <div class="row">
                    <div class="col-md-7">
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered mb-0" id="tablaMesa">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Pos.</th>
                                        <th class="text-right">Votos Conc.</th>
                                        <th class="text-center">Efectividad</th>
                                        <th class="text-right">Votos Perd.</th>
                                        <th class="text-center">Comité</th>
                                        <th class="text-center">Juventud</th>
                                    </tr>
                                </thead>
                                <tbody id="mesaBody"></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <canvas id="chartIntendenteVsConcejal" height="300"></canvas>
                        <div class="mt-3">
                            <canvas id="chartComiteJuventud" height="250"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body" id="mesaEmpty" style="display:none">
                <p class="text-muted text-center mb-0">
                    <i class="fas fa-hand-pointer"></i> Seleccione una mesa para ver su detalle
                </p>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(document).ready(function () {

    const COLORS = {
        danger: '#dc3545',
        warning: '#ffc107',
        success: '#28a745',
    };

    let chartIntVsConc = null;
    let chartComJuv = null;

    // ---- Upload ----
    $('#formCargar').on('submit', function (e) {
        e.preventDefault();
        var fd = new FormData(this);
        var $status = $('#uploadStatus');
        $status.html('<i class="fas fa-spinner fa-spin"></i> Subiendo...');
        $.ajax({
            url: '{{ route("efectividad.cargar") }}',
            method: 'POST',
            data: fd,
            processData: false,
            contentType: false,
            success: function (res) {
                $status.html('<span class="text-success"><i class="fas fa-check-circle"></i> ' + res.message + '</span>');
                cargarResumen();
                cargarMesas();
            },
            error: function (xhr) {
                var msg = 'Error al subir';
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                $status.html('<span class="text-danger"><i class="fas fa-times-circle"></i> ' + msg + '</span>');
            }
        });
    });

    // ---- Load Summary ----
    function cargarResumen() {
        $.get('{{ url("api/efectividad/resumen") }}', function (data) {
            var $body = $('#resumenBody');
            $body.empty();
            if (!data.length) {
                $body.html('<tr><td colspan="5" class="text-center text-muted py-4">Sin datos</td></tr>');
                return;
            }
            $.each(data, function (_, r) {
                var bar = '<div class="progress" style="height:18px;width:100px;margin:0 auto">' +
                    '<div class="progress-bar bg-' + r.color + '" style="width:' + (r.efectividad * 100) + '%">' +
                    (r.efectividad * 100).toFixed(0) + '%</div></div>';
                $body.append(
                    '<tr>' +
                    '<td class="text-center align-middle font-weight-bold">' + r.posicion + '</td>' +
                    '<td class="text-right align-middle">' + r.total_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right align-middle">' + r.total_concejal.toLocaleString('es') + '</td>' +
                    '<td class="text-center align-middle">' + bar + '</td>' +
                    '<td class="text-right align-middle text-danger font-weight-bold">' + r.votos_perdidos.toLocaleString('es') + '</td>' +
                    '</tr>'
                );
            });
        });
    }

    // ---- Load Mesa List ----
    function cargarMesas() {
        $.get('{{ url("api/efectividad/mesas") }}', function (data) {
            var $sel = $('#mesaSelector');
            $sel.find('option:not(:first)').remove();
            $.each(data, function (_, m) {
                $sel.append('<option value="' + m.id + '">' + m.mesa + '</option>');
            });
            $('#totalMesasBadge').text(data.length + ' mesas');
        });
    }

    // ---- Mesa Selector ----
    $('#mesaSelector').on('change', function () {
        var id = $(this).val();
        if (!id) {
            $('#mesaDetail').hide();
            $('#mesaEmpty').show();
            return;
        }
        $('#mesaEmpty').hide();
        $('#mesaDetail').show().find('#mesaBody').html(
            '<tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>'
        );
        $.get('{{ url("api/efectividad/mesa") }}/' + id, function (data) {
            renderMesa(data);
        });
    });

    function renderMesa(data) {
        // Info
        $('#mesaInfo').html(
            '<div class="alert alert-info py-2 mb-3"><strong>Mesa:</strong> ' + data.mesa +
            ' &mdash; <strong>Votos Intendente:</strong> ' + data.votos_intendente.toLocaleString('es') + '</div>'
        );

        // Alerts
        var $alerts = $('#alertasContainer');
        $alerts.empty();
        if (data.alertas && data.alertas.length) {
            $.each(data.alertas, function (_, a) {
                $alerts.append('<div class="alert alert-danger alert-dismissible fade show py-2 small mb-1">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    a + '</div>');
            });
        }

        // Table
        var $body = $('#mesaBody');
        $body.empty();
        $.each(data.concejales, function (_, c) {
            var effBar = barrita(c.efectividad, c.color_intendente);
            var comBar = barrita(c.efectividad_comite, c.color_comite);
            var juvBar = barrita(c.efectividad_juventud, c.color_juventud);
            $body.append(
                '<tr>' +
                '<td class="text-center align-middle font-weight-bold">' + c.posicion + '</td>' +
                '<td class="text-right align-middle">' + c.votos.toLocaleString('es') + '</td>' +
                '<td class="text-center align-middle">' + effBar + '</td>' +
                '<td class="text-right align-middle text-danger font-weight-bold">' + c.votos_perdidos.toLocaleString('es') + '</td>' +
                '<td class="text-center align-middle">' + comBar + '</td>' +
                '<td class="text-center align-middle">' + juvBar + '</td>' +
                '</tr>'
            );
        });

        // Chart: Intendente vs Concejal
        var labels = data.concejales.map(function (c) { return 'Pos ' + c.posicion; });
        var concVotos = data.concejales.map(function (c) { return c.votos; });
        var intVotos = data.concejales.map(function () { return data.votos_intendente; });

        if (chartIntVsConc) chartIntVsConc.destroy();
        chartIntVsConc = new Chart(document.getElementById('chartIntendenteVsConcejal'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Intendente',
                        data: intVotos,
                        backgroundColor: 'rgba(23, 162, 184, 0.7)',
                        borderColor: 'rgba(23, 162, 184, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Concejal',
                        data: concVotos,
                        backgroundColor: 'rgba(40, 167, 69, 0.7)',
                        borderColor: 'rgba(40, 167, 69, 1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'top' },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true }
                    }]
                }
            }
        });

        // Chart: Comité & Juventud effectiveness
        var comEff = data.concejales.map(function (c) { return c.efectividad_comite; });
        var juvEff = data.concejales.map(function (c) { return c.efectividad_juventud; });

        if (chartComJuv) chartComJuv.destroy();
        chartComJuv = new Chart(document.getElementById('chartComiteJuventud'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Ef. Comité',
                        data: comEff,
                        backgroundColor: 'rgba(255, 193, 7, 0.7)',
                        borderColor: 'rgba(255, 193, 7, 1)',
                        borderWidth: 1,
                    },
                    {
                        label: 'Ef. Juventud',
                        data: juvEff,
                        backgroundColor: 'rgba(220, 53, 69, 0.7)',
                        borderColor: 'rgba(220, 53, 69, 1)',
                        borderWidth: 1,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                legend: { position: 'top' },
                scales: {
                    yAxes: [{
                        ticks: { beginAtZero: true, max: 1 }
                    }]
                }
            }
        });
    }

    function barrita(valor, color) {
        var pct = Math.min(valor * 100, 100);
        return '<div class="progress" style="height:16px;min-width:70px">' +
            '<div class="progress-bar bg-' + color + '" style="width:' + pct + '%">' +
            (valor * 100).toFixed(0) + '%</div></div>';
    }

    // ---- Initial load ----
    cargarResumen();
});
</script>
@endpush
