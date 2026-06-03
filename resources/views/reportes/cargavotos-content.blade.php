@php
    $sumaTotal = 0;
    $sumaVotaron = 0;
    $sumaNoVotaron = 0;
    $dirigentesData = [];

    foreach ($punters as $p) {
        $noVotaron = $p->total_votantes - $p->votaron;
        $sumaTotal += $p->total_votantes;
        $sumaVotaron += $p->votaron;
        $sumaNoVotaron += $noVotaron;

        if (!isset($dirigentesData[$p->dirigente_nombre])) {
            $dirigentesData[$p->dirigente_nombre] = ['votaron' => 0, 'no_votaron' => 0, 'total' => 0];
        }
        $dirigentesData[$p->dirigente_nombre]['votaron'] += $p->votaron;
        $dirigentesData[$p->dirigente_nombre]['no_votaron'] += $noVotaron;
        $dirigentesData[$p->dirigente_nombre]['total'] += $p->total_votantes;
    }

    $topPunteros = collect($punters)->sortByDesc('votaron')->take(10)->values();
    $topPunterosNombres = $topPunteros->pluck('puntero_nombre')->map(fn($n) => strlen($n) > 25 ? substr($n, 0, 25).'...' : $n);
    $topPunterosVotos = $topPunteros->pluck('votaron');

    $dirigentesNombres = collect($dirigentesData)->keys();
    $dirigentesVotaron = collect($dirigentesData)->pluck('votaron');
    $dirigentesNoVotaron = collect($dirigentesData)->pluck('no_votaron');
    $totalDirigentes = count($dirigentesData);
@endphp

<div class="row mb-3">
    <div class="col-md-4">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total General de Votos Cargados</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($totalGeneral, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Votaron</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($sumaVotaron, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-times"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total No Votaron</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($sumaNoVotaron, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <ul class="nav nav-tabs card-header-tabs" id="reportTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="resumen-tab" data-toggle="tab" href="#resumen" role="tab">
                    <i class="fas fa-chart-pie"></i> Resumen Gráfico
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="detalle-tab" data-toggle="tab" href="#detalle" role="tab">
                    <i class="fas fa-list"></i> Detalle por Puntero
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="reportTabsContent">

            {{-- ===== TAB: RESUMEN GRÁFICO ===== --}}
            <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-chart-pie"></i> Distribución General</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartResumen" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-trophy"></i> Top 10 Punteros con más Votos</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartTopPunteros" height="220"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @if($totalDirigentes > 0)
                <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="card card-outline card-warning">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-users"></i> Votos por Dirigente</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDirigentes" height="200"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="row mt-1">
                    <div class="col-md-12">
                        <div class="card card-outline card-secondary">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-percent"></i> Resumen de Eficiencia</h6>
                            </div>
                            <div class="card-body">
                                <div class="row text-center">
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <h5 class="text-muted">Participación General</h5>
                                        <h3 class="text-success">
                                            {{ $sumaTotal > 0 ? number_format($sumaVotaron / $sumaTotal * 100, 1) : 0 }}%
                                        </h3>
                                        <small class="text-muted">{{ number_format($sumaVotaron, 0, ',', '.') }} de {{ number_format($sumaTotal, 0, ',', '.') }} votantes</small>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <h5 class="text-muted">Total Dirigentes</h5>
                                        <h3 class="text-info">{{ $totalDirigentes }}</h3>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <h5 class="text-muted">Total Punteros</h5>
                                        <h3 class="text-info">{{ $punters->count() }}</h3>
                                    </div>
                                    <div class="col-md-3 col-sm-6 col-xs-12">
                                        <h5 class="text-muted">Promedio por Puntero</h5>
                                        <h3 class="text-warning">
                                            {{ $punters->count() > 0 ? number_format($sumaVotaron / $punters->count(), 0) : 0 }}
                                        </h3>
                                        <small class="text-muted">votos por puntero</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ===== TAB: DETALLE POR PUNTERO ===== --}}
            <div class="tab-pane fade" id="detalle" role="tabpanel">
                <div class="table-responsive">
                    <table id="tabla-punteros" class="table table-bordered table-striped table-hover" width="100%">
                        <thead>
                            <tr>
                                <th>Dirigente</th>
                                <th>Puntero</th>
                                <th>Total Votantes</th>
                                <th>Votaron</th>
                                <th>No Votaron</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($punters as $p)
                                @php
                                    $noVotaron = $p->total_votantes - $p->votaron;
                                @endphp
                                <tr>
                                    <td>{{ $p->dirigente_nombre }}</td>
                                    <td>{{ $p->puntero_nombre }}</td>
                                    <td class="text-center">{{ number_format($p->total_votantes, 0, ',', '.') }}</td>
                                    <td class="text-center">
                                        @if($p->votaron > 0)
                                            <a href="#" class="btn-detalle" data-puntero="{{ $p->puntero_id }}" data-tipo="votaron" data-nombre="{{ $p->puntero_nombre }}">
                                                <span class="badge badge-success" style="font-size: 1rem; cursor: pointer;">
                                                    {{ number_format($p->votaron, 0, ',', '.') }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="badge badge-secondary">0</span>
                                        @endif
                                    </td>
                                    <td class="text-center">
                                        @if($noVotaron > 0)
                                            <a href="#" class="btn-detalle" data-puntero="{{ $p->puntero_id }}" data-tipo="no_votaron" data-nombre="{{ $p->puntero_nombre }}">
                                                <span class="badge badge-danger" style="font-size: 1rem; cursor: pointer;">
                                                    {{ number_format($noVotaron, 0, ',', '.') }}
                                                </span>
                                            </a>
                                        @else
                                            <span class="badge badge-secondary">0</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr class="font-weight-bold" style="background: #e9ecef;">
                                <td colspan="2" class="text-right">TOTALES</td>
                                <td class="text-center">{{ number_format($sumaTotal, 0, ',', '.') }}</td>
                                <td class="text-center">
                                    <span class="badge badge-success" style="font-size: 1.1rem;">
                                        {{ number_format($sumaVotaron, 0, ',', '.') }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="badge badge-danger" style="font-size: 1.1rem;">
                                        {{ number_format($sumaNoVotaron, 0, ',', '.') }}
                                    </span>
                                </td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>

<div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Detalle</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var ctx1 = document.getElementById('chartResumen');
        if (ctx1) {
            new Chart(ctx1.getContext('2d'), {
                type: 'doughnut',
                data: {
                    labels: ['Votaron', 'No Votaron'],
                    datasets: [{
                        data: [{{ $sumaVotaron }}, {{ $sumaNoVotaron }}],
                        backgroundColor: ['#28a745', '#dc3545'],
                        borderWidth: 2
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: { position: 'bottom' }
                }
            });
        }

        var ctx2 = document.getElementById('chartTopPunteros');
        if (ctx2) {
            new Chart(ctx2.getContext('2d'), {
                type: 'horizontalBar',
                data: {
                    labels: {!! $topPunterosNombres->toJson() !!},
                    datasets: [{
                        label: 'Votaron',
                        data: {!! $topPunterosVotos->toJson() !!},
                        backgroundColor: 'rgba(23, 162, 184, 0.7)',
                        borderColor: 'rgba(23, 162, 184, 1)',
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    legend: { display: false },
                    scales: {
                        xAxes: [{ ticks: { beginAtZero: true } }]
                    }
                }
            });
        }

        var ctx3 = document.getElementById('chartDirigentes');
        if (ctx3) {
            new Chart(ctx3.getContext('2d'), {
                type: 'bar',
                data: {
                    labels: {!! $dirigentesNombres->toJson() !!},
                    datasets: [
                        {
                            label: 'Votaron',
                            data: {!! $dirigentesVotaron->toJson() !!},
                            backgroundColor: '#28a745'
                        },
                        {
                            label: 'No Votaron',
                            data: {!! $dirigentesNoVotaron->toJson() !!},
                            backgroundColor: '#dc3545'
                        }
                    ]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        xAxes: [{ stacked: true }],
                        yAxes: [{ stacked: true, ticks: { beginAtZero: true } }]
                    },
                    legend: { position: 'bottom' }
                }
            });
        }

        var table = $('#tabla-punteros');
        if (table.length && !$.fn.DataTable.isDataTable(table)) {
            table.DataTable({
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-success btn-sm', title: 'Reporte_Punteros' },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger btn-sm', title: 'Reporte por Puntero', orientation: 'landscape', pageSize: 'A4' },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary btn-sm' }
                ],
                responsive: true,
                pageLength: 25,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                order: [[0, 'asc']]
            });
        }

        $('a[data-toggle="tab"]').on('shown.bs.tab', function(e) {
            if ($(e.target).attr('href') === '#detalle' && $.fn.DataTable.isDataTable('#tabla-punteros')) {
                $('#tabla-punteros').DataTable().columns.adjust().responsive.recalc();
            }
        });
    });
</script>
