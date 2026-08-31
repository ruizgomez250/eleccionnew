@php
    $totalGeneral = $totalVisitas ?? 0;
    $totalPos = $totalPositivas ?? 0;
    $totalNeg = $totalNegativas ?? 0;
    $totalNeut = $totalNeutras ?? 0;
    $porcentajePos = $totalGeneral > 0 ? round(($totalPos / $totalGeneral) * 100, 1) : 0;
    $porcentajeNeg = $totalGeneral > 0 ? round(($totalNeg / $totalGeneral) * 100, 1) : 0;
@endphp

<div class="row mb-3">
    <div class="col-md-3">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-clipboard-check"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total Visitas</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($totalGeneral, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-success">
            <span class="info-box-icon"><i class="fas fa-thumbs-up"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Positivas ({{ $porcentajePos }}%)</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($totalPos, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-danger">
            <span class="info-box-icon"><i class="fas fa-thumbs-down"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Negativas ({{ $porcentajeNeg }}%)</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($totalNeg, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="info-box bg-secondary">
            <span class="info-box-icon"><i class="fas fa-minus-circle"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Neutras</span>
                <span class="info-box-number" style="font-size: 1.8rem;">{{ number_format($totalNeut, 0, ',', '.') }}</span>
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
            <li class="nav-item">
                <a class="nav-link" id="proximas-tab" data-toggle="tab" href="#proximas" role="tab">
                    <i class="fas fa-calendar-alt"></i> Próximas Visitas
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="listado-tab" data-toggle="tab" href="#listado" role="tab">
                    <i class="fas fa-table"></i> Listado Completo
                </a>
            </li>
        </ul>
    </div>
    <div class="card-body">
        <div class="tab-content" id="reportTabsContent">

            {{-- TAB: RESUMEN GRÁFICO --}}
            <div class="tab-pane fade show active" id="resumen" role="tabpanel">
                <div class="row">
                    <div class="col-md-6">
                        <div class="card card-outline card-success">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-chart-pie"></i> Distribución por Resultado</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartResultado" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card card-outline card-info">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-chart-bar"></i> Visitas por Día (últimos 30 días)</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartDias" height="250"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="card card-outline card-primary">
                            <div class="card-header">
                                <h6 class="card-title"><i class="fas fa-chart-bar"></i> Top 15 Punteros con más Visitas</h6>
                            </div>
                            <div class="card-body">
                                <canvas id="chartPunteros" height="150"></canvas>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- TAB: DETALLE POR PUNTERO --}}
            <div class="tab-pane fade" id="detalle" role="tabpanel">
                <table class="table table-bordered table-striped">
                    <thead class="thead-dark">
                        <tr>
                            <th>Puntero</th>
                            <th>Total Visitas</th>
                            <th>Positivas</th>
                            <th>Negativas</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($porPuntero as $nombre => $datos)
                            <tr>
                                <td>{{ $nombre }}</td>
                                <td><span class="badge badge-info">{{ $datos['total'] }}</span></td>
                                <td><span class="badge badge-success">{{ $datos['positivas'] }}</span></td>
                                <td><span class="badge badge-danger">{{ $datos['negativas'] }}</span></td>
                                <td>
                                    <button class="btn btn-sm btn-primary btn-detalle" data-puntero="" data-tipo="todas" data-nombre="{{ $nombre }}">
                                        <i class="fas fa-eye"></i> Ver Todas
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center text-muted">No hay datos disponibles</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- TAB: PRÓXIMAS VISITAS --}}
            <div class="tab-pane fade" id="proximas" role="tabpanel">
                @if($proximasVisitas && $proximasVisitas->count() > 0)
                    <table class="table table-bordered table-striped">
                        <thead class="thead-dark">
                            <tr>
                                <th>Fecha Agendada</th>
                                <th>Puntero</th>
                                <th>Votante</th>
                                <th>Casa de</th>
                                <th>Resultado Anterior</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($proximasVisitas as $visita)
                                <tr>
                                    <td>
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clock"></i>
                                            {{ $visita->proxima_visita->format('d/m/Y H:i') }}
                                        </span>
                                    </td>
                                    <td>{{ $visita->puntero->nombre ?? 'N/A' }}</td>
                                    <td>{{ $visita->nombre_votante }} {{ $visita->apellido_votante }}</td>
                                    <td>{{ $visita->casa_de ?? '-' }}</td>
                                    <td><span class="badge badge-secondary">{{ $visita->resultado }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                @else
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-calendar-check fa-3x mb-3"></i>
                        <h5>No hay próximas visitas agendadas</h5>
                    </div>
                @endif
            </div>

            {{-- TAB: LISTADO COMPLETO --}}
            <div class="tab-pane fade" id="listado" role="tabpanel">
                <table id="tablaListadoCompleto" class="table table-bordered table-striped table-hover" style="width:100%">
                    <thead class="thead-dark">
                        <tr>
                            <th>Fecha</th>
                            <th>Puntero</th>
                            <th>Votante</th>
                            <th>Casa de</th>
                            <th>Resultado</th>
                            <th>Observación</th>
                            <th>GPS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($visitas as $visita)
                            <tr>
                                <td>{{ $visita->fecha_visita->format('d/m/Y H:i') }}</td>
                                <td>{{ $visita->puntero->nombre ?? 'N/A' }}</td>
                                <td>{{ $visita->nombre_votante }} {{ $visita->apellido_votante }}</td>
                                <td>{{ $visita->casa_de ?? '-' }}</td>
                                <td><span class="badge badge-secondary">{{ $visita->resultado }}</span></td>
                                <td>{{ Str::limit($visita->observacion, 50) ?? '-' }}</td>
                                <td>
                                    @if($visita->latitud && $visita->longitud)
                                        <a href="https://maps.google.com/?q={{ $visita->latitud }},{{ $visita->longitud }}" target="_blank" class="btn btn-sm btn-info">
                                            <i class="fas fa-map-marker-alt"></i>
                                        </a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Inicializar DataTable del listado completo
    if ($('#tablaListadoCompleto').length) {
        $('#tablaListadoCompleto').DataTable({
            responsive: true,
            order: [[0, 'desc']],
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
        });
    }

    // Colores para gráficos
    const colores = ['#28a745', '#dc3545', '#6c757d', '#17a2b8', '#ffc107', '#fd7e14', '#6f42c1'];

    // Gráfico de Resultados (Torta)
    @if($porResultado->count() > 0)
    new Chart(document.getElementById('chartResultado'), {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($porResultado->keys()->toArray()) !!},
            datasets: [{
                data: {!! json_encode($porResultado->values()->toArray()) !!},
                backgroundColor: colores.slice(0, {{ $porResultado->count() }}),
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { position: 'bottom' } }
        }
    });
    @endif

    // Gráfico de Visitas por Día (Línea)
    @if($visitasPorDia->count() > 0)
    new Chart(document.getElementById('chartDias'), {
        type: 'line',
        data: {
            labels: {!! json_encode($visitasPorDia->keys()->toArray()) !!},
            datasets: [{
                label: 'Visitas',
                data: {!! json_encode($visitasPorDia->values()->toArray()) !!},
                borderColor: '#007bff',
                backgroundColor: 'rgba(0,123,255,0.1)',
                fill: true,
                tension: 0.3
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } }
        }
    });
    @endif

    // Gráfico de Punteros (Barras)
    @if($porPuntero->count() > 0)
    new Chart(document.getElementById('chartPunteros'), {
        type: 'bar',
        data: {
            labels: {!! json_encode($porPuntero->keys()->toArray()) !!},
            datasets: [{
                label: 'Total Visitas',
                data: {!! json_encode($porPuntero->pluck('total')->toArray()) !!},
                backgroundColor: 'rgba(0,123,255,0.7)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            indexAxis: 'y',
            plugins: { legend: { display: false } }
        }
    });
    @endif
});
</script>
