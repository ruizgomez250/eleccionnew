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

        {{-- Certificado de Resultado --}}
        <div class="card">
            <div class="card-header py-2 bg-primary text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-file-signature"></i> Certificado de Resultado</h5>
                <button type="button" class="btn btn-sm btn-light" data-toggle="modal" data-target="#modalCertificado">
                    <i class="fas fa-external-link-alt"></i> Ver Certificado
                </button>
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
                <div>
                    <select id="arrastreIntendente" class="form-control form-control-sm" style="min-width:280px">
                        <option value="">Todos los intendentes</option>
                    </select>
                    <span class="badge badge-light ml-2" id="arrastreCount">—</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Compara por mesa los votos de Intendente vs. la suma de Concejales.
                    La <strong>discrepancia</strong> revela votantes que marcaron solo un cargo.
                    Si la diferencia coincide con los votos de un candidato específico,
                    sugiere que ese candidato <strong>arrastra votos propios</strong> que no van al intendente.
                    <br>Usá el selector de arriba para filtrar por un candidato a intendente específico.
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

        {{-- Arrastre Comite Analysis --}}
        <div class="card">
            <div class="card-header py-2 bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-layer-group"></i> Arrastre Concejal → Comité</h5>
                <div>
                    <select id="arrastreComiteIntendente" class="form-control form-control-sm" style="min-width:280px">
                        <option value="">Todos los intendentes</option>
                    </select>
                    <span class="badge badge-light ml-2" id="arrastreComiteCount">—</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Compara por mesa los votos totales de Concejales vs. la suma de Comité.
                    Cada concejal debería <strong>arrastrar</strong> su voto a la misma posición de comité.
                    Si la suma de comité es menor, hay votantes que marcaron concejal pero no comité.
                    Si la diferencia coincide con los votos de un concejal, sugiere que ese concejal
                    <strong>no logró arrastrar</strong> a sus votantes al comité.
                </p>
                <div class="row">
                    <div class="col-md-8">
                        <canvas id="chartArrastreComite" height="350"></canvas>
                    </div>
                    <div class="col-md-4">
                        <div class="table-responsive" style="max-height:400px;overflow-y:auto">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="thead-dark" style="position:sticky;top:0">
                                    <tr>
                                        <th>Partido</th>
                                        <th>Mesa</th>
                                        <th class="text-right">Conc.</th>
                                        <th class="text-right">Com.</th>
                                        <th class="text-center">Dif.</th>
                                        <th class="text-center">Sospechoso</th>
                                    </tr>
                                </thead>
                                <tbody id="arrastreComiteBody">
                                    <tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <hr>
                <div class="small text-muted mb-1"><i class="fas fa-info-circle"></i> <strong>Vista por posición:</strong> seleccioná una mesa en la tabla de arriba para ver la comparación posición por posición.</div>
                <div id="arrastreComitePosiciones" style="display:none">
                    <div class="row">
                        <div class="col-md-8">
                            <canvas id="chartComitePosiciones" height="250"></canvas>
                        </div>
                        <div class="col-md-4">
                            <table class="table table-sm table-bordered mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th class="text-center">Pos.</th>
                                        <th>Candidato</th>
                                        <th class="text-right">Conc.</th>
                                        <th class="text-right">Comité</th>
                                        <th class="text-center">Dif.</th>
                                    </tr>
                                </thead>
                                <tbody id="arrastreComitePosBody"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Arrastre Completo (Int → Conc → Com) --}}
        <div class="card">
            <div class="card-header py-2 bg-indigo text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0"><i class="fas fa-chart-simple"></i> Embudo Intendente → Concejal → Comité</h5>
                <div>
                    <select id="arrastreCompletoIntendente" class="form-control form-control-sm" style="min-width:280px">
                        <option value="">Todos los intendentes</option>
                    </select>
                    <span class="badge badge-light ml-2" id="arrastreCompletoCount">—</span>
                </div>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-3">
                    Muestra las <strong>3 barras</strong> por mesa: Intendente, Concejales (suma) y Comité (suma).
                    El <strong>embudo</strong> visual revela dónde se pierden más votos en cada escalón.
                    Ideal para identificar mesas donde el corte de boleta es más frecuente.
                </p>
                <div class="row">
                    <div class="col-md-7">
                        <canvas id="chartArrastreCompleto" height="400"></canvas>
                    </div>
                    <div class="col-md-5">
                        <div class="table-responsive" style="max-height:450px;overflow-y:auto">
                            <table class="table table-sm table-striped mb-0">
                                <thead class="thead-dark" style="position:sticky;top:0">
                                    <tr>
                                        <th>Partido</th>
                                        <th>Mesa</th>
                                        <th class="text-right">Int.</th>
                                        <th class="text-right">Conc.</th>
                                        <th class="text-right">Com.</th>
                                        <th class="text-center">Ef.Global</th>
                                    </tr>
                                </thead>
                                <tbody id="arrastreCompletoBody">
                                    <tr><td colspan="6" class="text-center text-muted py-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</td></tr>
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

{{-- Modal Certificado de Resultado --}}
<div class="modal fade" id="modalCertificado" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg modal-dialog-scrollable" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-file-signature"></i> Certificado de Resultado</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><i class="fas fa-briefcase"></i> Candidatura Local</label>
                            <select class="form-control form-control-sm select2" id="modalCertCargo" style="width:100%">
                                <option value="">Seleccione cargo</option>
                                @foreach ($cargos as $cargo)
                                    <option value="{{ $cargo }}">{{ ucfirst($cargo) }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="form-group">
                            <label><i class="fas fa-table"></i> Mesa</label>
                            <select class="form-control form-control-sm select2" id="modalCertMesa" style="width:100%">
                                <option value="">Seleccione mesa</option>
                                @foreach ($mesas as $m)
                                    <option value="{{ $m->id }}">{{ $m->codigo_mesa }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-primary btn-block" id="btnModalCertCargar" disabled>
                            <i class="fas fa-search"></i> Cargar
                        </button>
                    </div>
                </div>
                <div id="modalCertContainer" class="mt-3">
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-arrow-up fa-2x mb-2"></i>
                        <p class="mb-0">Seleccione candidatura local y mesa para ver el certificado.</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

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
                    var clsA = '', clsB = '';
                    if (d.votos_a > d.votos_b) { clsA = 'table-success font-weight-bold'; clsB = 'table-danger'; }
                    else if (d.votos_b > d.votos_a) { clsA = 'table-danger'; clsB = 'table-success font-weight-bold'; }
                    html += '<tr><td>' + d.mesa + '</td>' +
                        '<td class="text-right ' + clsA + '">' + d.votos_a.toLocaleString('es') + (d.votos_a > d.votos_b ? ' ✓' : '') + '</td>' +
                        '<td class="text-right ' + clsB + '">' + d.votos_b.toLocaleString('es') + (d.votos_b > d.votos_a ? ' ✓' : '') + '</td></tr>';
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
        var pid = $('#arrastreIntendente').val();
        var url = '{{ url("api/efectividad/arrastre") }}';
        var params = [];
        if (pid) params.push('partido_id=' + pid);
        var topPid = getPartidoId();
        if (!pid && topPid) params.push('partido_id=' + topPid);
        if (params.length) url += '?' + params.join('&');

        $.get(url, function (data) {
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

    // ---- Intendente Selector for Arrastre ----
    function cargarIntendentes() {
        $.get('{{ url("api/efectividad/intendentes") }}', function (data) {
            var $sel = $('#arrastreIntendente');
            $sel.empty().append('<option value="">Todos los intendentes</option>');
            $.each(data, function (_, c) {
                var label = (c.partido ? (c.partido.sigla || c.partido.nombre) : 'Lista') + ' - ' + c.nombre_completo;
                $sel.append('<option value="' + c.partido_id + '">' + label + '</option>');
            });
            $sel.select2({ theme: 'bootstrap4', width: '280px', placeholder: 'Seleccionar intendente' });
            $sel.on('change', function () { cargarArrastre(); });

            var $sel2 = $('#arrastreComiteIntendente');
            $sel2.empty().append('<option value="">Todos los intendentes</option>');
            $.each(data, function (_, c) {
                var label = (c.partido ? (c.partido.sigla || c.partido.nombre) : 'Lista') + ' - ' + c.nombre_completo;
                $sel2.append('<option value="' + c.partido_id + '">' + label + '</option>');
            });
            $sel2.select2({ theme: 'bootstrap4', width: '280px', placeholder: 'Seleccionar intendente' });
            $sel2.on('change', function () { cargarArrastreComite(); });

            var $sel3 = $('#arrastreCompletoIntendente');
            $sel3.empty().append('<option value="">Todos los intendentes</option>');
            $.each(data, function (_, c) {
                var label = (c.partido ? (c.partido.sigla || c.partido.nombre) : 'Lista') + ' - ' + c.nombre_completo;
                $sel3.append('<option value="' + c.partido_id + '">' + label + '</option>');
            });
            $sel3.select2({ theme: 'bootstrap4', width: '280px', placeholder: 'Seleccionar intendente' });
            $sel3.on('change', function () { cargarArrastreCompleto(); });
        });
    }

    // ---- Arrastre Concejal → Comité ----
    let chartArrastreComite = null;
    let chartComitePosiciones = null;

    function cargarArrastreComite() {
        var pid = $('#arrastreComiteIntendente').val();
        var url = '{{ url("api/efectividad/arrastre-comite") }}';
        var params = [];
        if (pid) params.push('partido_id=' + pid);
        var topPid = getPartidoId();
        if (!pid && topPid) params.push('partido_id=' + topPid);
        if (params.length) url += '?' + params.join('&');

        $('#arrastreComitePosiciones').hide();

        $.get(url, function (data) {
            $('#arrastreComiteCount').text(data.length + ' mesas');
            var $body = $('#arrastreComiteBody');
            $body.empty();

            if (!data.length) {
                $body.html('<tr><td colspan="6" class="text-center text-muted py-3">Sin datos</td></tr>');
                return;
            }

            var $posBody = $('#arrastreComitePosBody');

            $.each(data, function (_, r) {
                var badgeClass = r.diferencia > 0 ? 'badge-info' : (r.diferencia < 0 ? 'badge-warning' : 'badge-secondary');
                var signo = r.diferencia > 0 ? '+' : '';
                var sospechosoHtml = '';
                if (r.sospechoso) {
                    sospechosoHtml = '<span class="badge badge-danger">⚠ ' + r.sospechoso.nombre + '</span>';
                } else if (r.candidato_mas_cercano && r.diferencia !== 0) {
                    sospechosoHtml = '<small class="text-muted">' + r.candidato_mas_cercano.nombre + ' (' + r.candidato_mas_cercano.votos + 'v)</small>';
                } else {
                    sospechosoHtml = '<small class="text-muted">—</small>';
                }
                var partidoLabel = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                var rowClass = r.sospechoso ? 'table-danger' : '';
                $body.append(
                    '<tr class="' + rowClass + '" data-mesa-idx="' + data.indexOf(r) + '" style="cursor:pointer">' +
                    '<td><small class="font-weight-bold">' + partidoLabel + '</small></td>' +
                    '<td><small>' + r.mesa + '</small></td>' +
                    '<td class="text-right">' + r.total_concejales.toLocaleString('es') + '</td>' +
                    '<td class="text-right">' + r.total_comite.toLocaleString('es') + '</td>' +
                    '<td class="text-center"><span class="badge ' + badgeClass + '">' + signo + r.diferencia + '</span></td>' +
                    '<td class="text-center align-middle">' + sospechosoHtml + '</td>' +
                    '</tr>'
                );
            });

            // Click row → show position detail
            $('#arrastreComiteBody tr[data-mesa-idx]').on('click', function () {
                var idx = $(this).data('mesa-idx');
                var r = data[idx];
                if (!r || !r.por_posicion) return;

                var $posBody = $('#arrastreComitePosBody');
                $posBody.empty();
                $.each(r.por_posicion, function (_, p) {
                    var badgeP = p.diferencia > 0 ? 'badge-info' : (p.diferencia < 0 ? 'badge-warning' : 'badge-secondary');
                    $posBody.append(
                        '<tr>' +
                        '<td class="text-center font-weight-bold">' + p.posicion + '</td>' +
                        '<td><small>' + (p.candidato || '').substring(0, 18) + '</small></td>' +
                        '<td class="text-right">' + p.votos_concejal + '</td>' +
                        '<td class="text-right">' + p.votos_comite + '</td>' +
                        '<td class="text-center"><span class="badge ' + badgeP + '">' + (p.diferencia > 0 ? '+' : '') + p.diferencia + '</span></td>' +
                        '</tr>'
                    );
                });

                var posLabels = r.por_posicion.map(function (p) { return 'Pos ' + p.posicion; });
                var concDataP = r.por_posicion.map(function (p) { return p.votos_concejal; });
                var comDataP = r.por_posicion.map(function (p) { return p.votos_comite; });

                if (chartComitePosiciones) chartComitePosiciones.destroy();
                chartComitePosiciones = new Chart(document.getElementById('chartComitePosiciones'), {
                    type: 'bar',
                    data: {
                        labels: posLabels,
                        datasets: [
                            { label: 'Concejal', data: concDataP, backgroundColor: 'rgba(40, 167, 69, 0.7)', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 1 },
                            { label: 'Comité', data: comDataP, backgroundColor: 'rgba(255, 193, 7, 0.7)', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 1 }
                        ]
                    },
                    options: { responsive: true, maintainAspectRatio: false, legend: { position: 'top' }, scales: { yAxes: [{ ticks: { beginAtZero: true } }] } }
                });

                $('#arrastreComitePosiciones').show();
            });

            // Chart: top 20 mesas
            var top20 = data.slice(0, 20);
            var labels = top20.map(function (r) {
                var p = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                return p + ' - ' + r.mesa;
            });
            var concTotal = top20.map(function (r) { return r.total_concejales; });
            var comTotal = top20.map(function (r) { return r.total_comite; });

            if (chartArrastreComite) chartArrastreComite.destroy();
            chartArrastreComite = new Chart(document.getElementById('chartArrastreComite'), {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Concejales (suma)', data: concTotal, backgroundColor: 'rgba(40, 167, 69, 0.7)', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 1 },
                        { label: 'Comité (suma)', data: comTotal, backgroundColor: 'rgba(255, 193, 7, 0.7)', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 1 }
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

    // ---- Arrastre Completo (Int → Conc → Com) ----
    let chartArrastreCompleto = null;

    function cargarArrastreCompleto() {
        var pid = $('#arrastreCompletoIntendente').val();
        var url = '{{ url("api/efectividad/arrastre-completo") }}';
        var params = [];
        if (pid) params.push('partido_id=' + pid);
        var topPid = getPartidoId();
        if (!pid && topPid) params.push('partido_id=' + topPid);
        if (params.length) url += '?' + params.join('&');

        $.get(url, function (data) {
            $('#arrastreCompletoCount').text(data.length + ' mesas');
            var $body = $('#arrastreCompletoBody');
            $body.empty();

            if (!data.length) {
                $body.html('<tr><td colspan="6" class="text-center text-muted py-3">Sin datos</td></tr>');
                return;
            }

            $.each(data, function (_, r) {
                var partidoLabel = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                var pct = (r.efectividad_global * 100).toFixed(0) + '%';
                var barColor = r.efectividad_global > 0.75 ? 'success' : (r.efectividad_global > 0.50 ? 'warning' : 'danger');
                $body.append(
                    '<tr>' +
                    '<td><small class="font-weight-bold">' + partidoLabel + '</small></td>' +
                    '<td><small>' + r.mesa + '</small></td>' +
                    '<td class="text-right">' + r.votos_intendente.toLocaleString('es') + '</td>' +
                    '<td class="text-right">' + r.suma_concejales.toLocaleString('es') + '</td>' +
                    '<td class="text-right">' + r.suma_comite.toLocaleString('es') + '</td>' +
                    '<td class="text-center"><span class="badge badge-' + barColor + '">' + pct + '</span></td>' +
                    '</tr>'
                );
            });

            var top20 = data.slice(0, 20);
            var labels = top20.map(function (r) {
                var p = (r.partido_sigla || r.partido || '').split(' ').slice(0,2).join(' ');
                return p + ' - ' + r.mesa;
            });
            var intData = top20.map(function (r) { return r.votos_intendente; });
            var concData = top20.map(function (r) { return r.suma_concejales; });
            var comData = top20.map(function (r) { return r.suma_comite; });

            if (chartArrastreCompleto) chartArrastreCompleto.destroy();
            chartArrastreCompleto = new Chart(document.getElementById('chartArrastreCompleto'), {
                type: 'horizontalBar',
                data: {
                    labels: labels,
                    datasets: [
                        { label: 'Intendente', data: intData, backgroundColor: 'rgba(23, 162, 184, 0.7)', borderColor: 'rgba(23, 162, 184, 1)', borderWidth: 1 },
                        { label: 'Concejales', data: concData, backgroundColor: 'rgba(40, 167, 69, 0.7)', borderColor: 'rgba(40, 167, 69, 1)', borderWidth: 1 },
                        { label: 'Comité', data: comData, backgroundColor: 'rgba(255, 193, 7, 0.7)', borderColor: 'rgba(255, 193, 7, 1)', borderWidth: 1 }
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
                                if (!d) return '';
                                var lines = [];
                                lines.push('Int → Conc: -' + d.perdidos_int_conc.toLocaleString('es') + ' (' + (d.efectividad_concejal * 100).toFixed(0) + '%)');
                                lines.push('Conc → Com: -' + d.perdidos_conc_com.toLocaleString('es') + ' (' + (d.efectividad_comite * 100).toFixed(0) + '%)');
                                lines.push('Global: -' + d.perdidos_int_com.toLocaleString('es') + ' (' + (d.efectividad_global * 100).toFixed(0) + '%)');
                                return lines.join('\n');
                            }
                        }
                    }
                }
            });
        });
    }

    // ---- Certificado de Resultado (Modal) ----
    $('#modalCertificado').on('shown.bs.modal', function() {
        $('#modalCertCargo, #modalCertMesa').select2({
            theme: 'bootstrap4',
            width: '100%',
            placeholder: 'Buscar...',
            dropdownParent: $('#modalCertificado')
        });
    });

    $('#modalCertificado').on('hidden.bs.modal', function() {
        $('#modalCertCargo, #modalCertMesa').select2('destroy');
    });

    $('#modalCertCargo, #modalCertMesa').on('change', function() {
        $('#btnModalCertCargar').prop('disabled', !($('#modalCertCargo').val() && $('#modalCertMesa').val()));
    });

    $('#btnModalCertCargar').on('click', function() {
        var cargo = $('#modalCertCargo').val();
        var mesaId = $('#modalCertMesa').val();
        if (!cargo || !mesaId) return;

        $('#modalCertContainer').html(
            '<div class="text-center py-4"><i class="fas fa-spinner fa-spin fa-2x mb-2"></i><p>Cargando certificado...</p></div>'
        );

        $.get('{{ route("certificados.formulario") }}', { mesa_id: mesaId, cargo: cargo }, function(resp) {
            $('#modalCertContainer').html(resp.html);
        }).fail(function() {
            $('#modalCertContainer').html(
                '<div class="alert alert-danger">Error al cargar el certificado.</div>'
            );
        });
    });

    // ---- Initial load ----
    cargarResumen();
    cargarRanking();
    cargarCandidatos();
    cargarIntendentes();
    cargarArrastre();
    cargarArrastreComite();
    cargarArrastreCompleto();
});
</script>
@endpush
