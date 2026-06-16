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

        {{-- Summary Charts Card --}}
        <div class="card">
            <div class="card-header py-2 bg-info text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-bar"></i> Resumen por Candidatura a Intendente</h5>
                <span class="badge badge-light" id="totalIntendenteBadge">—</span>
            </div>
            <div class="card-body">
                <div class="small text-muted mb-2">
                    <i class="fas fa-info-circle"></i>
                    Cada gráfico muestra los votos de los concejales de una lista (barras naranjas)
                    comparados con los votos del intendente de esa misma lista (línea azul).
                    La <strong class="text-danger">barra roja</strong> marca la diferencia (votos perdidos).
                    Si una barra es mucho más baja que la línea azul, ese candidato
                    <strong>no arrastra</strong> a todos los que votaron al intendente.
                </div>
                <div id="resumenCharts"></div>
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
                <div class="small text-muted mb-2">
                    <i class="fas fa-info-circle"></i>
                    Muestra los mismos indicadores que el resumen, pero aplicados a una mesa específica.
                    Las <strong class="text-danger">alertas en rojo</strong> señalan posiciones con baja efectividad.
                </div>
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
            <div class="card-body">
                <div class="small text-muted mb-2">
                    <i class="fas fa-info-circle"></i>
                    <strong>Efectividad</strong> = suma de votos de concejales ÷ votos del intendente.
                    Mide qué tan cohesionado vota el electorado del partido en cada mesa.
                    Arriba del todo están las mesas donde los votantes marcaron la boleta completa (ideal).
                    Abajo, las que pierden más votos entre el intendente y los concejales.
                </div>
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
                                        <th>Partido</th>
                                        <th>Mesa</th>
                                        <th class="text-right">Int.</th>
                                        <th class="text-right">Conc.</th>
                                        <th class="text-center">Dif.</th>
                                        <th>¿Coincide con?</th>
                                        <th class="text-center">Sospechoso</th>
                                    </tr>
                                </thead>
                                <tbody id="arrastreBody">
                                    <tr><td colspan="7" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
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
                <div class="small text-muted mb-2">
                    <i class="fas fa-info-circle"></i>
                    Seleccioná dos candidatos (pueden ser de distintos cargos o partidos) para comparar
                    sus votos mesa por mesa. Útil para ver si dos candidatos <strong>comparten electorado</strong>
                    (votan juntos en las mismas mesas) o si uno <strong>le gana al otro</strong> sistemáticamente.
                </div>
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

    // ---- Summary Charts ----
    var resumenChartInstances = [];

    function cargarResumen() {
        $.get(apiUrl('/resumen'), function (data) {
            var $container = $('#resumenCharts');
            $container.empty();
            resumenChartInstances.forEach(function(c) { if (c) c.destroy(); });
            resumenChartInstances = [];

            if (!data.length) {
                $container.html('<p class="text-muted text-center py-4"><i class="fas fa-info-circle"></i> Sin datos</p>');
                $('#totalIntendenteBadge').text('—');
                return;
            }

            $.each(data, function (pi, partido) {
                var totalInt = partido.total_intendente;
                var partidoLabel = partido.partido_sigla || partido.partido || 'Partido';
                var intendenteLabel = partido.intendente || 'Intendente';

                var cardHtml =
                    '<div class="card mb-3 border-' + (pi === 0 ? 'info' : 'secondary') + '">' +
                    '<div class="card-header py-1 px-3 bg-light d-flex justify-content-between align-items-center">' +
                    '<span class="font-weight-bold"><small>' + partidoLabel + '</small></span>' +
                    '<span class="small">Intendente: <strong>' + intendenteLabel + '</strong> — <strong>' + totalInt.toLocaleString('es') + '</strong> votos</span>' +
                    '</div>' +
                    '<div class="card-body py-2 px-3">' +
                    '<div class="row">' +
                    '<div class="col-md-8"><canvas id="resumenChart' + pi + '" height="220"></canvas></div>' +
                    '<div class="col-md-4"><div class="table-responsive" style="max-height:220px;overflow-y:auto">' +
                    '<table class="table table-sm table-borderless mb-0"><tbody id="resumenMiniTable' + pi + '"></tbody></table>' +
                    '</div></div></div></div></div>';

                $container.append(cardHtml);

                var labels = [];
                var concVotos = [];
                var colores = [];
                var perdidos = [];
                var $miniBody = $('#resumenMiniTable' + pi);

                $.each(partido.concejales, function (_, c) {
                    labels.push('Pos. ' + c.posicion + ' ' + (c.candidato || '').substring(0, 18));
                    concVotos.push(c.votos);
                    perdidos.push(c.votos_perdidos);
                    var color = c.efectividad < 0.6 ? '#dc3545' : (c.efectividad <= 0.8 ? '#ffc107' : '#28a745');
                    colores.push(color);

                    var miniRow =
                        '<tr>' +
                        '<td class="text-center p-0 small font-weight-bold">' + c.posicion + '.</td>' +
                        '<td class="p-0 small">' + (c.candidato || '').substring(0, 22) + '</td>' +
                        '<td class="text-right p-0 small">' + c.votos + '</td>' +
                        '<td class="text-center p-0" style="width:50px">' +
                        '<div class="progress" style="height:10px;min-width:40px">' +
                        '<div class="progress-bar bg-' + c.color + '" style="width:' + Math.min(c.efectividad*100,100) + '%">' +
                        (c.efectividad*100).toFixed(0) + '%</div></div></td>' +
                        '</tr>';
                    $miniBody.append(miniRow);
                });

                var ctx = document.getElementById('resumenChart' + pi).getContext('2d');
                var chart = new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: labels,
                        datasets: [
                            {
                                label: 'Intendente',
                                data: labels.map(function () { return totalInt; }),
                                backgroundColor: 'rgba(23, 162, 184, 0.15)',
                                borderColor: 'rgba(23, 162, 184, 0.8)',
                                borderWidth: 2,
                                type: 'line',
                                pointRadius: 0,
                                fill: false,
                                order: 0
                            },
                            {
                                label: 'Concejales',
                                data: concVotos,
                                backgroundColor: colores.map(function (c) { return c + '99'; }),
                                borderColor: colores,
                                borderWidth: 1.5,
                                order: 1
                            }
                        ]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        legend: { position: 'top', labels: { fontSize: 10 } },
                        scales: {
                            xAxes: [{ ticks: { fontSize: 9, maxRotation: 45 } }],
                            yAxes: [{ ticks: { beginAtZero: true, fontSize: 10 } }]
                        },
                        tooltips: {
                            callbacks: {
                                afterBody: function (tooltipItem, data) {
                                    var idx = tooltipItem[0].index;
                                    var c = partido.concejales[idx];
                                    if (!c) return '';
                                    return 'Efectividad: ' + (c.efectividad * 100).toFixed(0) + '% | Perdidos: ' + c.votos_perdidos;
                                }
                            }
                        }
                    }
                });
                resumenChartInstances.push(chart);
            });

            var totalGeneral = 0;
            $.each(data, function (_, p) { totalGeneral += p.total_intendente; });
            $('#totalIntendenteBadge').text(data.length + ' listas — ' + totalGeneral.toLocaleString('es') + ' votos totales');
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
                $body.html('<tr><td colspan="7" class="text-center text-muted py-3">Sin datos</td></tr>');
                return;
            }

            $.each(data, function (_, r) {
                var badgeClass = r.diferencia > 0 ? 'badge-info' : (r.diferencia < 0 ? 'badge-warning' : 'badge-secondary');
                var signo = r.diferencia > 0 ? '+' : '';
                var matchHtml = '';
                if (r.candidatos_coincidentes && r.candidatos_coincidentes.length) {
                    matchHtml = '<small class="text-danger font-weight-bold">';
                    $.each(r.candidatos_coincidentes, function (_, c) {
                        matchHtml += 'Pos.' + c.orden + ' ' + c.nombre + ' (' + c.votos + ' votos)<br>';
                    });
                    matchHtml += '</small>';
                } else {
                    matchHtml = '<small class="text-muted">—</small>';
                }

                var sospechosoHtml = '';
                if (r.sospechoso) {
                    sospechosoHtml = '<span class="badge badge-danger" title="Concejales suman más que Intendente">' +
                        '⚠ ' + r.sospechoso.nombre + '</span>';
                } else if (r.candidato_mas_cercano && r.diferencia !== 0) {
                    sospechosoHtml = '<small class="text-muted">' + r.candidato_mas_cercano.nombre + ' (' + r.candidato_mas_cercano.votos + 'v)</small>';
                } else {
                    sospechosoHtml = '<small class="text-muted">—</small>';
                }

                var partidoLabel = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                var rowClass = r.sospechoso ? 'table-danger' : '';
                $body.append(
                    '<tr class="' + rowClass + '">' +
                    '<td><small class="font-weight-bold">' + partidoLabel + '</small></td>' +
                    '<td><small>' + r.mesa + '</small></td>' +
                    '<td class="text-right">' + r.votos_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right">' + r.suma_concejales.toLocaleString('es') + '</td>' +
                    '<td class="text-center"><span class="badge ' + badgeClass + '">' + signo + r.diferencia + '</span></td>' +
                    '<td>' + matchHtml + '</td>' +
                    '<td class="text-center align-middle">' + sospechosoHtml + '</td>' +
                    '</tr>'
                );
            });

            // Global chart: top 20 mesas sorted by abs discrepancy
            var top20 = data.slice(0, 20);
            var labels = top20.map(function (r) {
                var p = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                return p + ' - ' + r.mesa;
            });
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
                                var d = top20[idx];
                                var lines = [];
                                if (d.candidatos_coincidentes && d.candidatos_coincidentes.length) {
                                    lines.push('⚠ Coincide: ' + d.candidatos_coincidentes.map(function (c) {
                                        return c.nombre + ' (' + c.votos + 'v)';
                                    }).join(', '));
                                }
                                if (d.sospechoso) {
                                    lines.push('🔴 SOSPECHOSO: ' + d.sospechoso.nombre + ' (' + d.sospechoso.votos + 'v)');
                                }
                                return lines.join('\n');
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
