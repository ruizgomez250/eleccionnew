@extends('adminlte::page')

@section('content_header')
    <div class="ua-header">
        <h1 class="ua-title"><i class="fas fa-chart-bar"></i> Gráficos</h1>
        <p class="ua-subtitle">Documentos emitidos por mes y tipo</p>
    </div>
@stop

@push('js')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Chart.defaults.color = '#8ea3bf';
            Chart.defaults.borderColor = 'rgba(142, 163, 191, .15)';
            Chart.defaults.scale.grid.color = 'rgba(142, 163, 191, .12)';

            // Definir etiquetas (meses)
            const labels = @json($documentosporfechas->pluck('mes'));

            // Configuración del primer gráfico (Total de Documentos por mes)
            const data = {
                labels: labels,
                datasets: [{
                    label: 'Total de Documentos',
                    data: @json($documentosporfechas->pluck('cantidad')),
                    backgroundColor: '#6c757d',
                }]
            };

            const config = {
                type: 'bar',
                data: data,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Total de Documentos por Mes' }
                    },
                    scales: {
                        x: { stacked: false },
                        y: { stacked: false, beginAtZero: true }
                    }
                }
            };

            const ctx = document.getElementById('myChart').getContext('2d');
            new Chart(ctx, config);

            // Segundo gráfico: Tipos de Documentos por Mes
            const tiposDocsData = @json($portiposdocs); // Asegurar que portiposdocs está definido

            const tiposUnicos = [...new Set(tiposDocsData.map(item => item.tipo_doc))];

            const datasets = tiposUnicos.map((tipo, index) => {
                return {
                    label: tipo,
                    data: labels.map(mes => {
                        const found = tiposDocsData.find(item => item.mes === mes && item.tipo_doc === tipo);
                        return found ? found.cantidad : 0;
                    }),
                    borderColor: `hsl(${index * 50}, 70%, 50%)`,
                    backgroundColor: `hsla(${index * 50}, 70%, 50%, 0.5)`,
                    fill: false,
                };
            });

            const data1 = { labels: labels, datasets: datasets };

            const config1 = {
                type: 'line',
                data: data1,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Tipos de Documentos por Mes' }
                    }
                }
            };

            const ctx1 = document.getElementById('myChart1').getContext('2d');
            new Chart(ctx1, config1);
        });
    </script>
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-5">
            <div class="card ua-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-file-alt"></i> Total de Documentos por Mes</h3>
                </div>
                <div class="card-body">
                    <canvas id="myChart" width="30" height="30" style="max-width:400px; max-height:300px;"></canvas>
                </div>
            </div>
        </div>

        <div class="col-lg-7">
            <div class="card ua-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-chart-line"></i> Tipos de Documentos por Mes</h3>
                </div>
                <div class="card-body">
                    <canvas id="myChart1" width="30" height="30" style="max-height:900px;"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Tabla debajo del segundo gráfico -->
    <div class="card ua-card mt-3">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-table"></i> Detalle por Mes y Tipo de Documento</h3>
        </div>
        <div class="card-body">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>Mes</th>
                        <th>Tipo de Documento</th>
                        <th>Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($portiposdocs as $documento)
                        <tr>
                            <td>{{ $documento->mes }}</td>
                            <td>{{ $documento->tipo_doc }}</td>
                            <td>{{ $documento->cantidad }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@stop

@section('css')
    @include('useradmin._dark_pages')
@stop
