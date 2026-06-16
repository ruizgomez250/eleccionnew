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
        {{-- Partido Selector --}}
        <div class="card">
            <div class="card-body py-2">
                <div class="form-inline">
                    <label class="mr-2"><i class="fas fa-filter"></i> Partido:</label>
                    <select class="form-control form-control-sm" id="partidoSelector" style="min-width:250px">
                        <option value="">Todos los partidos</option>
                        @foreach ($partidos as $p)
                            <option value="{{ $p->id }}">{{ $p->nombre_completo }}</option>
                        @endforeach
                    </select>
                    <button class="btn btn-sm btn-info ml-2" id="btnRefrescar"><i class="fas fa-sync-alt"></i> Refrescar</button>
                </div>
            </div>
        </div>

        {{-- Summary Table Card --}}
        <div class="card">
            <div class="card-header py-2 bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-table"></i> Resumen General por Posición</h5>
                <span class="badge badge-light" id="totalIntendenteBadge">—</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0" id="tablaResumen">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">Pos.</th>
                                <th>Candidato</th>
                                <th class="text-right">Votos Int.</th>
                                <th class="text-right">Votos Conc.</th>
                                <th class="text-center">Ef. Concejal</th>
                                <th class="text-center">Ef. Comité</th>
                                <th class="text-center">Ef. Juventud</th>
                                <th class="text-right">Votos Perd.</th>
                            </tr>
                        </thead>
                        <tbody id="resumenBody">
                            <tr id="resumenLoading">
                                <td colspan="8" class="text-center text-muted py-4">
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
                    <label class="mr-2 small">Mesa:</label>
                    <select class="form-control form-control-sm" id="mesaSelector" style="min-width:300px">
                        <option value="">-- Seleccione una mesa --</option>
                        @foreach ($mesas as $m)
                            <option value="{{ $m->id }}">{{ $m->codigo_mesa }}</option>
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
                                        <th>Candidato</th>
                                        <th class="text-right">Votos</th>
                                        <th class="text-center">Ef. Conc.</th>
                                        <th class="text-center">Ef. Comité</th>
                                        <th class="text-center">Ef. Juventud</th>
                                        <th class="text-right">Votos Perd.</th>
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

        {{-- Mesa Ranking --}}
        <div class="card">
            <div class="card-header py-2 bg-success text-white">
                <h5 class="mb-0"><i class="fas fa-trophy"></i> Ranking de Mesas por Efectividad</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-sm table-striped mb-0">
                        <thead class="thead-dark">
                            <tr>
                                <th class="text-center">#</th>
                                <th>Mesa</th>
                                <th class="text-right">Votos Int.</th>
                                <th class="text-right">Votos Conc.</th>
                                <th class="text-center">Efectividad</th>
                                <th class="text-right">Votos Perd.</th>
                            </tr>
                        </thead>
                        <tbody id="rankingBody">
                            <tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Arrastre Analysis --}}
        <div class="card">
            <div class="card-header py-2 bg-secondary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-link"></i> Análisis de Arrastre y Discrepancia</h5>
                <span class="badge badge-light" id="arrastreCount">—</span>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Compara por mesa los votos de Intendente vs. la suma de Concejales.
                    La <strong>discrepancia</strong> revela votantes que marcaron solo un cargo.
                    Si la diferencia coincide con los votos de un candidato específico,
                    sugiere que ese candidato <strong>arrastra votos propios</strong> que no van al intendente.
                </p>
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="chartArrastreGlobal" height="350"></canvas>
                    </div>
                    <div class="col-md-4">
                        <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="thead-dark" style="position:sticky;top:0">
                                    <tr>
                                        <th>Mesa</th>
                                        <th class="text-right">Int.</th>
                                        <th class="text-right">Conc.</th>
                                        <th class="text-center">Dif.</th>
                                        <th>¿Coincide con?</th>
                                    </tr>
                                </thead>
                                <tbody id="arrastreBody">
                                    <tr><td colspan="5" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Candidate Comparison --}}
        <div class="card">
            <div class="card-header py-2 bg-danger text-white">
                <h5 class="mb-0"><i class="fas fa-balance-scale"></i> Comparación de Candidatos</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-5">
                        <label class="small">Candidato A</label>
                        <select class="form-control form-control-sm" id="candidatoA">
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>
                    <div class="col-md-2 text-center pt-4">
                        <button class="btn btn-sm btn-danger" id="btnComparar"><i class="fas fa-balance-scale"></i> Comparar</button>
                    </div>
                    <div class="col-md-5">
                        <label class="small">Candidato B</label>
                        <select class="form-control form-control-sm" id="candidatoB">
                            <option value="">-- Seleccionar --</option>
                        </select>
                    </div>
                </div>
                <div id="comparacionResult" class="mt-3" style="display:none"></div>
            </div>
        </div>
    </div>
</div>
@stop

@push('js')
<script>
$(document).ready(function () {
    let chartIntVsConc = null;
    let chartComJuv = null;

    function getPartidoId() {
        return $('#partidoSelector').val();
    }

    function apiUrl(path) {
        var url = '{{ url("api/efectividad") }}' + path;
        var pid = getPartidoId();
        if (pid) url += (path.includes('?') ? '&' : '?') + 'partido_id=' + pid;
        return url;
    }

    // ---- Partido / Refresh ----
    $('#partidoSelector, #btnRefrescar').on('change click', function () {
        cargarResumen();
        cargarRanking();
        cargarCandidatos();
        cargarArrastre();
    });

    // ---- Summary ----
    function cargarResumen() {
        $.get(apiUrl('/resumen'), function (data) {
            var $body = $('#resumenBody');
            $body.empty();
            if (!data.length) {
                $body.html('<tr><td colspan="8" class="text-center text-muted py-4">Sin datos</td></tr>');
                $('#totalIntendenteBadge').text('—');
                return;
            }
            var totalInt = data[0].total_intendente;
            $('#totalIntendenteBadge').text('Total Intendente: ' + totalInt.toLocaleString('es'));
            $.each(data, function (_, r) {
                $body.append(
                    '<tr>' +
                    '<td class="text-center align-middle font-weight-bold">' + r.posicion + '</td>' +
                    '<td class="align-middle"><small>' + (r.candidato || '') + '</small></td>' +
                    '<td class="text-right align-middle">' + r.total_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right align-middle">' + r.total_concejal.toLocaleString('es') + '</td>' +
                    '<td class="text-center align-middle">' + barrita(r.efectividad, r.color) + '</td>' +
                    '<td class="text-center align-middle">' + barrita(r.efectividad_comite, r.color_comite) + '</td>' +
                    '<td class="text-center align-middle">' + barrita(r.efectividad_juventud, r.color_juventud) + '</td>' +
                    '<td class="text-right align-middle text-danger font-weight-bold">' + r.votos_perdidos.toLocaleString('es') + '</td>' +
                    '</tr>'
                );
            });
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
            '<tr><td colspan="7" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>'
        );
        $.get(apiUrl('/mesa/' + id), function (data) {
            renderMesa(data);
        });
    });

    function renderMesa(data) {
        $('#mesaInfo').html(
            '<div class="alert alert-info py-2 mb-3"><strong>Mesa:</strong> ' + data.mesa +
            ' &mdash; <strong>Votos Intendente:</strong> ' + data.votos_intendente.toLocaleString('es') + '</div>'
        );

        var $alerts = $('#alertasContainer');
        $alerts.empty();
        if (data.alertas && data.alertas.length) {
            $.each(data.alertas, function (_, a) {
                $alerts.append('<div class="alert alert-danger alert-dismissible fade show py-2 small mb-1">' +
                    '<button type="button" class="close" data-dismiss="alert">&times;</button>' +
                    a + '</div>');
            });
        }

        var $body = $('#mesaBody');
        $body.empty();
        $.each(data.concejales, function (_, c) {
            $body.append(
                '<tr>' +
                '<td class="text-center align-middle font-weight-bold">' + c.posicion + '</td>' +
                '<td class="align-middle"><small>' + (c.candidato || '') + '</small></td>' +
                '<td class="text-right align-middle">' + c.votos.toLocaleString('es') + '</td>' +
                '<td class="text-center align-middle">' + barrita(c.efectividad, c.color_intendente) + '</td>' +
                '<td class="text-center align-middle">' + barrita(c.efectividad_comite, c.color_comite) + '</td>' +
                '<td class="text-center align-middle">' + barrita(c.efectividad_juventud, c.color_juventud) + '</td>' +
                '<td class="text-right align-middle text-danger font-weight-bold">' + c.votos_perdidos.toLocaleString('es') + '</td>' +
                '</tr>'
            );
        });

        var labels = data.concejales.map(function (c) { return 'Pos ' + c.posicion; });
        var concVotos = data.concejales.map(function (c) { return c.votos; });
        var intVotos = data.concejales.map(function () { return data.votos_intendente; });

        if (chartIntVsConc) chartIntVsConc.destroy();
        chartIntVsConc = new Chart(document.getElementById('chartIntendenteVsConcejal'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Intendente', data: intVotos, backgroundColor: 'rgba(23, 162, 184, 0.7)', borderColor: 'rgba(23, 162, 184, 1)', borderWidth: 1 },
                    { label: 'Concejal', data: concVotos, backgroundColor: 'rgba(40, 167, 69, 0.7)', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 1 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'top' }, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
        });

        var comEff = data.concejales.map(function (c) { return c.efectividad_comite; });
        var juvEff = data.concejales.map(function (c) { return c.efectividad_juventud; });

        if (chartComJuv) chartComJuv.destroy();
        chartComJuv = new Chart(document.getElementById('chartComiteJuventud'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'Ef. Comité', data: comEff, backgroundColor: 'rgba(255, 193, 7, 0.7)', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 1 },
                    { label: 'Ef. Juventud', data: juvEff, backgroundColor: 'rgba(220, 53, 69, 0.7)', borderColor: 'rgba(220, 53, 69, 1)', borderWidth: 1 }
                ]
            },
            options: { responsive: true, maintainAspectRatio: false, legend: { position: 'top' }, scales: { yAxes: [{ ticks: { beginAtZero: true, max: 1 } }] } }
        });
    }

    // ---- Ranking ----
    function cargarRanking() {
        $.get(apiUrl('/ranking'), function (data) {
            var $body = $('#rankingBody');
            $body.empty();
            if (!data.length) {
                $body.html('<tr><td colspan="6" class="text-center text-muted py-3">Sin datos</td></tr>');
                return;
            }
            $.each(data, function (i, r) {
                var icono = i === 0 ? '<i class="fas fa-trophy text-warning"></i>' : (i < 3 ? '<i class="fas fa-medal text-secondary"></i>' : '');
                var badge = i < 3 ? '<span class="badge badge-' + (i === 0 ? 'warning' : i === 1 ? 'secondary' : 'danger') + '">#' + (i+1) + '</span>' : (i+1);
                $body.append(
                    '<tr class="' + (i < 3 ? 'font-weight-bold' : '') + '">' +
                    '<td class="text-center align-middle">' + badge + '</td>' +
                    '<td class="align-middle">' + r.mesa + '</td>' +
                    '<td class="text-right align-middle">' + r.votos_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right align-middle">' + r.votos_concejales_total.toLocaleString('es') + '</td>' +
                    '<td class="text-center align-middle">' + barrita(r.efectividad, r.efectividad < 0.6 ? 'danger' : (r.efectividad <= 0.8 ? 'warning' : 'success')) + '</td>' +
                    '<td class="text-right align-middle text-danger">' + r.votos_perdidos.toLocaleString('es') + '</td>' +
                    '</tr>'
                );
            });
        });
    }

    // ---- Candidate Comparison ----
    function cargarCandidatos() {
        $.get(apiUrl('/candidatos'), function (data) {
            var opts = '<option value="">-- Seleccionar --</option>';
            $.each(data, function (_, c) {
                opts += '<option value="' + c.id + '">[' + c.cargo + '] ' + c.nombre_completo + '</option>';
            });
            $('#candidatoA').html(opts);
            $('#candidatoB').html(opts);
        });
    }

    $('#btnComparar').on('click', function () {
        var a = $('#candidatoA').val();
        var b = $('#candidatoB').val();
        if (!a || !b) {
            alert('Seleccioná dos candidatos para comparar');
            return;
        }
        if (a === b) {
            alert('Seleccioná dos candidatos diferentes');
            return;
        }
        var url = apiUrl('/comparar?candidato_a=' + a + '&candidato_b=' + b);
        $.get(url, function (data) {
            var $r = $('#comparacionResult');
            if (!data.comparacion) { $r.hide(); return; }
            var cmp = data.comparacion;
            var ganador = cmp.ganador === 'A' ? cmp.candidato_a.nombre : (cmp.ganador === 'B' ? cmp.candidato_b.nombre : 'Empate');
            var html = '<div class="alert alert-' + (cmp.ganador === 'EMPATE' ? 'info' : 'success') + ' text-center">';
            html += '<strong>GANADOR: ' + ganador + '</strong> (Diferencia: ' + Math.abs(cmp.diferencia).toLocaleString('es') + ' votos)</div>';
            html += '<div class="row text-center mb-3">';
            html += '<div class="col-5"><div class="card bg-light p-2"><h4>' + cmp.candidato_a.total.toLocaleString('es') + '</h4><small>' + cmp.candidato_a.nombre + '<br>' + cmp.candidato_a.cargo + '</small></div></div>';
            html += '<div class="col-2 pt-3"><h5>VS</h5></div>';
            html += '<div class="col-5"><div class="card bg-light p-2"><h4>' + cmp.candidato_b.total.toLocaleString('es') + '</h4><small>' + cmp.candidato_b.nombre + '<br>' + cmp.candidato_b.cargo + '</small></div></div>';
            html += '</div>';

            if (cmp.detalle && cmp.detalle.length) {
                html += '<table class="table table-sm table-bordered mb-0"><thead class="thead-light"><tr><th>Mesa</th><th class="text-right">' + cmp.candidato_a.nombre + '</th><th class="text-right">' + cmp.candidato_b.nombre + '</th></tr></thead><tbody>';
                $.each(cmp.detalle, function (_, d) {
                    html += '<tr><td>' + d.mesa + '</td><td class="text-right">' + d.votos_a.toLocaleString('es') + '</td><td class="text-right">' + d.votos_b.toLocaleString('es') + '</td></tr>';
                });
                html += '</tbody></table>';
            }

            $r.html(html).show();
        });
    });

    function barrita(valor, color) {
        var pct = Math.min(valor * 100, 100);
        return '<div class="progress" style="height:16px;min-width:70px">' +
            '<div class="progress-bar bg-' + color + '" style="width:' + pct + '%">' +
            (valor * 100).toFixed(0) + '%</div></div>';
    }

    // ---- Arrastre Analysis ----
    let chartArrastre = null;

    function cargarArrastre() {
        $.get(apiUrl('/arrastre'), function (data) {
            $('#arrastreCount').text(data.length + ' mesas');
            var $body = $('#arrastreBody');
            $body.empty();

            if (!data.length) {
                $body.html('<tr><td colspan="5" class="text-center text-muted py-3">Sin datos</td></tr>');
                return;
            }

            $.each(data, function (_, r) {
                var badgeClass = r.diferencia > 0 ? 'badge-info' : (r.diferencia < 0 ? 'badge-warning' : 'badge-secondary');
                var signo = r.diferencia > 0 ? '+' : '';
                var matchHtml = '';
                if (r.candidatos_coincidentes && r.candidatos_coincidentes.length) {
                    matchHtml = '<small class="text-danger font-weight-bold">';
                    $.each(r.candidatos_coincidentes, function (_, c) {
                        var absDiff = Math.abs(r.diferencia);
                        matchHtml += 'Pos.' + c.orden + ' ' + c.nombre + ' (' + c.votos + ' votos)<br>';
                    });
                    matchHtml += '</small>';
                } else {
                    matchHtml = '<small class="text-muted">—</small>';
                }
                $body.append(
                    '<tr>' +
                    '<td><small>' + r.mesa + '</small></td>' +
                    '<td class="text-right">' + r.votos_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right">' + r.suma_concejales.toLocaleString('es') + '</td>' +
                    '<td class="text-center"><span class="badge ' + badgeClass + '">' + signo + r.diferencia + '</span></td>' +
                    '<td>' + matchHtml + '</td>' +
                    '</tr>'
                );
            });

            // Global chart: top 20 mesas sorted by abs discrepancy
            var top20 = data.slice(0, 20).reverse();
            var labels = top20.map(function (r) { return r.mesa; });
            var intData = top20.map(function (r) { return r.votos_intendente; });
            var concData = top20.map(function (r) { return r.suma_concejales; });

            if (chartArrastre) chartArrastre.destroy();
            chartArrastre = new Chart(document.getElementById('chartArrastreGlobal'), {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Intendente', data: intData, backgroundColor: 'rgba(23, 162, 184, 0.7)', borderColor: 'rgba(23, 162, 184, 1)', borderWidth: 1 },
                        { label: 'Concejales (suma)', data: concData, backgroundColor: 'rgba(40, 167, 69, 0.7)', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 1 }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    legend: { position: 'top' },
                    scales: {
                        xAxes: [{ ticks: { beginAtZero: true } }],
                        yAxes: [{ ticks: { fontSize: 10 } }]
                    },
                    tooltips: {
                        callbacks: {
                            afterBody: function (tooltipItem, data) {
                                var idx = tooltipItem.index;
                                var d = top20[top20.length - 1 - idx];
                                if (d.candidatos_coincidentes && d.candidatos_coincidentes.length) {
                                    return '⚠ Coincide: ' + d.candidatos_coincidentes.map(function (c) {
                                        return c.nombre + ' (' + c.votos + 'v)';
                                    }).join(', ');
                                }
                                return '';
                            }
                        }
                    }
                }
            });
        });
    }

    // ---- Initial load ----
    cargarResumen();
    cargarRanking();
    cargarCandidatos();
    cargarArrastre();
});
</script>
@endpush
