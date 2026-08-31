@extends('adminlte::page')

@section('title', 'Certificados de Resultado')

@section('content_header')
    <div class="ua-header">
        <h1 class="ua-title"><i class="fas fa-file-signature"></i> Certificados de Resultado</h1>
        <p class="ua-subtitle">Carga de resultados por mesa, seguimiento y cálculo D'Hondt</p>
    </div>
@stop

@section('content')
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif
    @if (session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert">&times;</button>
        </div>
    @endif

    {{-- ==================== SELECTOR: DISTRITO → LOCAL → MESA → CARGO ==================== --}}
    <div class="card card-primary card-outline">
        <div class="card-header">
            <h3 class="card-title"><i class="fas fa-plus-circle"></i> Carga de Resultados</h3>
            <div class="card-tools">
                <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
            </div>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
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
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-school"></i> Local / Escuela</label>
                        <select id="localSelect" class="form-control select2" disabled>
                            <option value="">Primero seleccione distrito</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-table"></i> Mesa</label>
                        <select id="mesaSelect" class="form-control select2" disabled>
                            <option value="">Primero seleccione local</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label><i class="fas fa-briefcase"></i> Cargo</label>
                        <select id="cargoSelect" class="form-control">
                            <option value="">Seleccione cargo</option>
                            @foreach ($cargos as $cargo)
                                <option value="{{ $cargo }}">{{ ucfirst($cargo) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button id="btnCargar" class="btn btn-primary btn-lg" disabled>
                        <i class="fas fa-search"></i> Cargar
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- ==================== TABLA DE CARGA (AJAX) ==================== --}}
    <div id="cargaContainer">
        <div class="card card-default">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-arrow-up fa-3x mb-3"></i>
                <p class="mb-0">Seleccione distrito, local, mesa y cargo para comenzar la carga.</p>
            </div>
        </div>
    </div>

    {{-- ==================== RESUMEN ==================== --}}
    <div class="row">
        <div class="col-lg-3 col-6">
            <div class="small-box bg-info">
                <div class="inner">
                    <h3>{{ number_format($totalCertificados, 0, ',', '.') }}</h3>
                    <p>Certificados Cargados</p>
                </div>
                <div class="icon"><i class="fas fa-file-signature"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-success">
                <div class="inner">
                    <h3>{{ number_format($totalVotosCargados, 0, ',', '.') }}</h3>
                    <p>Total Votos</p>
                </div>
                <div class="icon"><i class="fas fa-vote-yea"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h3>{{ $mesasConCarga }} / {{ $totalMesas }}</h3>
                    <p>Mesas con carga</p>
                </div>
                <div class="icon"><i class="fas fa-table"></i></div>
            </div>
        </div>
        <div class="col-lg-3 col-6">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h3>{{ $totalMesas > 0 ? round(($mesasConCarga / $totalMesas) * 100, 1) : 0 }}%</h3>
                    <p>Avance</p>
                </div>
                <div class="icon"><i class="fas fa-chart-line"></i></div>
            </div>
        </div>
    </div>

    {{-- ==================== TABLAS DE CERTIFICADOS POR CARGO ==================== --}}
    <div class="card">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="cargosTabs" role="tablist">
                @foreach ($cargos as $index => $cargo)
                    @php
                        $cargoSlug = \Illuminate\Support\Str::slug($cargo);
                        $cargoVotos = $votosPorCargo[$cargo] ?? collect();
                    @endphp
                    <li class="nav-item">
                        <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="tab-{{ $cargoSlug }}" data-toggle="tab" href="#table-{{ $cargoSlug }}" role="tab">
                            {{ ucfirst($cargo) }}
                            <span class="badge badge-info ml-1">{{ $cargoVotos->count() }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content">
                @foreach ($cargos as $index => $cargo)
                    @php
                        $cargoSlug = \Illuminate\Support\Str::slug($cargo);
                        $cargoVotos = $votosPorCargo[$cargo] ?? collect();
                    @endphp
                    <div class="tab-pane {{ $index === 0 ? 'active' : '' }}" id="table-{{ $cargoSlug }}" role="tabpanel">
                        @if($cargoVotos->isEmpty())
                            <p class="text-muted text-center py-3">No hay certificados cargados para {{ ucfirst($cargo) }}.</p>
                        @else
                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-sm certificados-table" id="table-{{ $cargoSlug }}-data">
                                    <thead class="bg-primary text-white">
                                        <tr>
                                            <th>ID</th>
                                            <th>Mesa</th>
                                            <th>Local</th>
                                            <th>Distrito</th>
                                            <th>Partido</th>
                                            <th>Votos</th>
                                            <th>Tipo</th>
                                            <th>Candidato</th>
                                            <th>Usuario</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($cargoVotos as $voto)
                                            <tr>
                                                <td>{{ $voto->id }}</td>
                                                <td>{{ $voto->mesa->codigo_mesa ?? 'N/A' }}</td>
                                                <td>{{ $voto->mesa->equipo->colegio ?? $voto->mesa->equipo->descripcion ?? 'N/A' }}</td>
                                                <td>{{ $voto->mesa->equipo->ciudad ?? 'N/A' }}</td>
                                                <td>{{ $voto->partido->sigla ?? $voto->partido->nombre ?? 'N/A' }}</td>
                                                <td class="editable-votos" data-id="{{ $voto->id }}" data-value="{{ $voto->cantidad_votos }}">
                                                    <span class="voto-valor">{{ number_format($voto->cantidad_votos, 0, ',', '.') }}</span>
                                                    <input type="number" class="voto-input form-control form-control-sm" value="{{ $voto->cantidad_votos }}" style="display:none;width:100px;">
                                                </td>
                                                <td>
                                                    <span class="badge badge-{{ $voto->tipo_voto === 'lista' ? 'primary' : 'success' }}">
                                                        {{ ucfirst($voto->tipo_voto) }}
                                                    </span>
                                                </td>
                                                <td>{{ $voto->candidato->nombre_completo ?? '-' }}</td>
                                                <td>{{ $voto->user->name ?? $voto->escaneado_por ?? '-' }}</td>
                                                <td>{{ $voto->created_at->format('d/m/Y H:i') }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- ==================== REPORTES GRÁFICOS ==================== --}}
    <div class="row">
        @foreach ($chartLabels as $cargoNombre => $labels)
            <div class="col-md-6">
                <div class="card card-success card-outline">
                    <div class="card-header">
                        <h3 class="card-title"><i class="fas fa-chart-bar"></i> Votos por Partido - {{ $cargoNombre }}</h3>
                        <div class="card-tools">
                            <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                        </div>
                    </div>
                    <div class="card-body">
                        <canvas id="chart-{{ $chartSlugs[$cargoNombre] }}" style="height: 250px;"></canvas>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- ==================== CÁLCULO D'HONDT ==================== --}}
    @if (!empty($dhont))
        <div class="card card-danger card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-calculator"></i> Cálculo D'Hondt</h3>
                <div class="card-tools">
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    @foreach ($dhont as $cargo => $partidos)
                        <div class="col-md-6">
                            <div class="card card-outline card-secondary mb-3">
                                <div class="card-header">
                                    <h5 class="card-title">{{ ucfirst($cargo) }} ({{ count($partidos) > 0 ? array_sum(array_column($partidos, 'bancas')) : 0 }} bancas)</h5>
                                </div>
                                <div class="card-body p-0">
                                    <table class="table table-sm table-bordered mb-0">
                                        <thead>
                                            <tr>
                                                <th>Partido</th>
                                                <th class="text-right">Votos</th>
                                                <th class="text-center">Bancas</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($partidos as $p)
                                                <tr>
                                                    <td>
                                                        <span class="badge" style="background: {{ $p['color'] ?? '#6c757d' }}; width: 12px; height: 12px; display: inline-block; border-radius: 50%;"></span>
                                                        {{ $p['sigla'] }}
                                                    </td>
                                                    <td class="text-right">{{ number_format($p['votos'], 0, ',', '.') }}</td>
                                                    <td class="text-center"><strong>{{ $p['bancas'] }}</strong></td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    {{-- ==================== RESULTADOS FINALES POR CARGO ==================== --}}
    @if (!empty($reporteCargos))
        <div class="card card-success card-outline">
            <div class="card-header">
                <h3 class="card-title"><i class="fas fa-trophy"></i> Resultados Finales</h3>
                <div class="card-tools">
                    <a href="{{ route('certificados.exportar.pdf') }}" class="btn btn-danger btn-sm mr-2" target="_blank">
                        <i class="fas fa-file-pdf"></i> Exportar PDF
                    </a>
                    <button type="button" class="btn btn-tool" data-card-widget="collapse"><i class="fas fa-minus"></i></button>
                </div>
            </div>
            <div class="card-body">
                <ul class="nav nav-tabs" id="resultadosTabs" role="tablist">
                    @foreach ($cargos as $index => $cargo)
                        @php $cargoSlug = \Illuminate\Support\Str::slug($cargo); @endphp
                        <li class="nav-item">
                            <a class="nav-link {{ $index === 0 ? 'active' : '' }}" id="resultados-tab-{{ $cargoSlug }}" data-toggle="tab" href="#resultados-content-{{ $cargoSlug }}" role="tab">
                                {{ ucfirst($cargo) }}
                            </a>
                        </li>
                    @endforeach
                </ul>
                <div class="tab-content mt-3">
                    @foreach ($cargos as $index => $cargo)
                        @php
                            $cargoSlug = \Illuminate\Support\Str::slug($cargo);
                            $rc = $reporteCargos[$cargo] ?? null;
                            $tieneOpcion = in_array($cargo, $cargosConOpcion);
                            $bancasCargo = \App\Http\Controllers\CertificadoController::BANCAS[$cargo] ?? 1;
                        @endphp
                        <div class="tab-pane {{ $index === 0 ? 'active' : '' }}" id="resultados-content-{{ $cargoSlug }}" role="tabpanel">
                            @if($rc && !empty($rc['dhont']))
                                @php
                                    $totalBancas = array_sum(array_column($rc['dhont'], 'bancas'));
                                    $ordenadoPorBancas = collect($rc['dhont'])->sortByDesc('bancas')->values();
                                @endphp
                                <div class="row">
                                    <div class="{{ $tieneOpcion ? 'col-md-5' : 'col-md-12' }}">
                                        <div class="card card-outline card-secondary mb-0">
                                            <div class="card-header py-2">
                                                <h6 class="card-title mb-0">Distribución D'Hondt ({{ $totalBancas }} bancas)</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Lista</th>
                                                            <th>Partido</th>
                                                            <th class="text-right">Votos</th>
                                                            <th class="text-center">Bancas</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($ordenadoPorBancas as $p)
                                                            <tr class="{{ $p['bancas'] > 0 ? 'table-success' : '' }}">
                                                                <td class="text-center">{{ $rc['partidos'][$p['partido_id']]['partido']->numero_lista ?? '-' }}</td>
                                                                <td>
                                                                    <span class="badge" style="background: {{ $p['color'] ?? '#6c757d' }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%;"></span>
                                                                    {{ $p['sigla'] }}
                                                                </td>
                                                                <td class="text-right">{{ number_format($p['votos'], 0, ',', '.') }}</td>
                                                                <td class="text-center"><strong>{{ $p['bancas'] }}</strong></td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @if($tieneOpcion)
                                            @php
                                                $electosPorLista = collect();
                                                foreach ($ordenadoPorBancas as $p) {
                                                    $bancas = $p['bancas'];
                                                    if ($bancas > 0) {
                                                        $candidatosLista = $rc['partidos'][$p['partido_id']]['candidatos'] ?? collect();
                                                        $electos = $candidatosLista->take($bancas)->values()->map(function($cand, $idx) use ($p, $rc) {
                                                            $cand->cociente_dhont = $p['votos'] / ($idx + 1);
                                                            $cand->numero_lista = $rc['partidos'][$p['partido_id']]['partido']->numero_lista ?? '-';
                                                            $cand->sigla_partido = $p['sigla'];
                                                            $cand->color_partido = $p['color'] ?? '#6c757d';
                                                            return $cand;
                                                        });
                                                        $electosPorLista = $electosPorLista->merge($electos);
                                                    }
                                                }
                                            @endphp
                                            @if($electosPorLista->isNotEmpty())
                                                @php
                                                    $electosOrdenados = $electosPorLista->sortByDesc('cociente_dhont');
                                                @endphp
                                                <div class="card card-outline card-success mt-2">
                                                    <div class="card-header py-2">
                                                        <h6 class="card-title mb-0"><i class="fas fa-users"></i> Candidatos Electos</h6>
                                                    </div>
                                                    <div class="card-body p-0">
                                                        <table class="table table-sm table-bordered mb-0">
                                                            <thead>
                                                                <tr>
                                                                    <th class="text-center">#</th>
                                                                    <th>Lista</th>
                                                                    <th>Partido</th>
                                                                    <th>Candidato</th>
                                                                    <th class="text-right">Votos</th>
                                                                    <th class="text-right">Cociente D'Hondt</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                @foreach ($electosOrdenados as $i => $cand)
                                                                    <tr>
                                                                        <td class="text-center">{{ $i + 1 }}</td>
                                                                        <td class="text-center">{{ $cand->numero_lista }}</td>
                                                                        <td>
                                                                            <span class="badge" style="background: {{ $cand->color_partido }}; width: 10px; height: 10px; display: inline-block; border-radius: 50%;"></span>
                                                                            {{ $cand->sigla_partido }}
                                                                        </td>
                                                                        <td>
                                                                            <span class="badge badge-secondary mr-1">{{ $cand->candidato->numero_orden ?? '' }}</span>
                                                                            {{ $cand->candidato->nombre_completo ?? '' }}
                                                                        </td>
                                                                        <td class="text-right">{{ number_format($cand->total_votos, 0, ',', '.') }}</td>
                                                                        <td class="text-right">{{ number_format($cand->cociente_dhont, 2, ',', '.') }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                </div>
                                            @endif
                                        @else
                                            @php
                                                $ganador = $ordenadoPorBancas->firstWhere('bancas', '>', 0);
                                            @endphp
                                            @if($ganador)
                                                @php
                                                    $partidoGanador = $rc['partidos'][$ganador['partido_id']] ?? null;
                                                    $primerCandidato = $partidoGanador ? ($partidoGanador['candidatos']->first()?->candidato ?? null) : null;
                                                @endphp
                                                <div class="card card-outline card-warning mt-2">
                                                    <div class="card-header py-2">
                                                        <h6 class="card-title mb-0"><i class="fas fa-trophy"></i> Electo</h6>
                                                    </div>
                                                    <div class="card-body text-center py-3">
                                                        <h5 class="text-success mb-1">
                                                            {{ $ganador['sigla'] }}
                                                            (Lista {{ $rc['partidos'][$ganador['partido_id']]['partido']->numero_lista ?? '-' }})
                                                        </h5>
                                                        @if($primerCandidato)
                                                            <p class="mb-0 lead">
                                                                <span class="badge badge-secondary">{{ $primerCandidato->numero_orden }}</span>
                                                                {{ $primerCandidato->nombre_completo }}
                                                            </p>
                                                        @endif
                                                        <small class="text-muted">Votos: {{ number_format($ganador['votos'], 0, ',', '.') }}</small>
                                                    </div>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    @if($tieneOpcion)
                                    <div class="col-md-7">
                                        <div class="card card-outline card-info mb-0">
                                            <div class="card-header py-2">
                                                <h6 class="card-title mb-0">Votos de preferencia por candidato</h6>
                                            </div>
                                            <div class="card-body p-0">
                                                <table class="table table-sm table-bordered mb-0">
                                                    <thead>
                                                        <tr>
                                                            <th>Lista</th>
                                                            <th>Partido</th>
                                                            <th>Candidato</th>
                                                            <th class="text-right">Votos</th>
                                                            <th class="text-center">Electo</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach ($ordenadoPorBancas as $p)
                                                                @php
                                                                    $candidatos = $rc['partidos'][$p['partido_id']]['candidatos'] ?? collect();
                                                                    $bancasAsignadas = $p['bancas'];
                                                                    $idxCand = 0;
                                                                @endphp
                                                                @foreach ($candidatos as $cand)
                                                                    @php
                                                                        $esElecto = $idxCand < $bancasAsignadas && $bancasAsignadas > 0;
                                                                        $idxCand++;
                                                                    @endphp
                                                                <tr class="{{ $esElecto ? 'table-success' : '' }}">
                                                                    <td class="text-center">{{ $rc['partidos'][$p['partido_id']]['partido']->numero_lista ?? '-' }}</td>
                                                                    <td>{{ $p['sigla'] }}</td>
                                                                    <td>
                                                                        <span class="badge badge-secondary mr-1">{{ $cand->candidato->numero_orden ?? '' }}</span>
                                                                        {{ $cand->candidato->nombre_completo ?? 'Sin nombre' }}
                                                                    </td>
                                                                    <td class="text-right">{{ number_format($cand->total_votos, 0, ',', '.') }}</td>
                                                                    <td class="text-center">
                                                                        @if($esElecto)
                                                                            <span class="badge badge-success"><i class="fas fa-check"></i></span>
                                                                        @else
                                                                            <span class="badge badge-secondary">-</span>
                                                                        @endif
                                                                    </td>
                                                                </tr>
                                                            @endforeach
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    @endif
                                </div>
                            @else
                                <p class="text-muted text-center py-3">No hay datos de votos para {{ ucfirst($cargo) }}.</p>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@stop

@section('css')
    <style>
        .small-box { border-radius: 0.5rem; }
        .small-box>.inner h3 { font-size: 2rem; }
        #cargaContainer .card-body { min-height: 100px; }
    </style>
    @include('useradmin._dark_pages')
@stop

@section('js')
<script>
    $(document).ready(function() {
        Chart.defaults.color = '#8ea3bf';
        Chart.defaults.borderColor = 'rgba(142, 163, 191, .15)';
        Chart.defaults.scale.grid.color = 'rgba(142, 163, 191, .12)';

        $('.select2').select2({ placeholder: 'Seleccione...', width: '100%' });

        $('.certificados-table').each(function() {
            var $table = $(this);
            var tableId = $table.attr('id');
            var cargoName = tableId.replace('table-', '').replace('-data', '').replace(/-/g, ' ');
            $table.DataTable({
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                     "<'row'<'col-sm-12'tr>>" +
                     "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [
                    { extend: 'excelHtml5', text: '<i class="fas fa-file-excel"></i> Excel', className: 'btn btn-info', title: 'Certificados - ' + cargoName, exportOptions: { columns: ':visible' } },
                    { extend: 'pdfHtml5', text: '<i class="fas fa-file-pdf"></i> PDF', className: 'btn btn-danger', title: 'Certificados - ' + cargoName, exportOptions: { columns: ':visible' } },
                    { extend: 'print', text: '<i class="fas fa-print"></i> Imprimir', className: 'btn btn-secondary', exportOptions: { columns: ':visible' } }
                ],
                responsive: true,
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                order: [[0, 'desc']],
                pageLength: 25,
                lengthMenu: [[10, 25, 50, 100, -1], [10, 25, 50, 100, 'Todos']]
            });
        });

        // Distrito → Locales
        $('#distritoSelect').on('change', function() {
            var distrito = $(this).val();
            var $local = $('#localSelect').empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $('#mesaSelect').empty().append('<option value="">Primero seleccione local</option>').prop('disabled', true);
            $('#btnCargar').prop('disabled', true);

            if (distrito) {
                $.get('{{ route("certificados.locales") }}', { distrito: distrito }, function(data) {
                    $local.empty().append('<option value="">Seleccione local</option>');
                    $.each(data, function(i, l) {
                        $local.append('<option value="' + l.id + '" data-cantmesa="' + l.cantmesa + '">' + l.local_interna + ' (' + l.cantmesa + ' mesas)</option>');
                    });
                    $local.prop('disabled', false);
                });
            } else {
                $local.empty().append('<option value="">Primero seleccione distrito</option>').prop('disabled', true);
            }
        });

        // Local → Mesas
        $('#localSelect').on('change', function() {
            var localId = $(this).val();
            var $mesa = $('#mesaSelect').empty().append('<option value="">Cargando...</option>').prop('disabled', true);
            $('#btnCargar').prop('disabled', true);

            if (localId) {
                $.get('{{ route("certificados.mesas") }}', { local_interna_id: localId }, function(resp) {
                    $mesa.empty().append('<option value="">Seleccione mesa</option>');
                    if (resp.mesas && resp.mesas.length > 0) {
                        $.each(resp.mesas, function(i, m) {
                            $mesa.append('<option value="' + m.id + '">' + m.codigo_mesa + (m.numero_mesa ? ' (Mesa ' + m.numero_mesa + ')' : '') + '</option>');
                        });
                        $mesa.prop('disabled', false);
                    } else {
                        $mesa.empty().append('<option value="">Sin mesas — vincule colegio electoral a mesas</option>');
                    }
                });
            } else {
                $mesa.empty().append('<option value="">Primero seleccione local</option>').prop('disabled', true);
            }
        });

        // Habilitar botón Cargar
        $('#mesaSelect, #cargoSelect').on('change', function() {
            $('#btnCargar').prop('disabled', !($('#mesaSelect').val() && $('#cargoSelect').val()));
        });

        // Cargar tabla de resultados
        $('#btnCargar').on('click', function() {
            var mesaId = $('#mesaSelect').val();
            var cargo = $('#cargoSelect').val();
            if (!mesaId || !cargo) return;

            $('#cargaContainer').html(
                '<div class="card card-default"><div class="card-body text-center py-5">' +
                '<i class="fas fa-spinner fa-spin fa-3x mb-3"></i><p>Cargando...</p></div></div>'
            );

            $.get('{{ route("certificados.formulario") }}', { mesa_id: mesaId, cargo: cargo }, function(resp) {
                $('#cargaContainer').html(resp.html);
            }).fail(function() {
                $('#cargaContainer').html(
                    '<div class="alert alert-danger">Error al cargar el formulario.</div>'
                );
            });
        });

        // Inline editing for votos
        $(document).on('dblclick', '.editable-votos .voto-valor', function() {
            var $cell = $(this).closest('.editable-votos');
            $cell.find('.voto-valor').hide();
            $cell.find('.voto-input').show().focus().select();
        });

        $(document).on('blur keydown', '.voto-input', function(e) {
            if (e.type === 'keydown' && e.keyCode !== 13 && e.keyCode !== 27) return;

            var $input = $(this);
            var $cell = $input.closest('.editable-votos');
            var id = $cell.data('id');
            var newVal = $input.val();
            var oldVal = $cell.data('value');

            if (e.keyCode === 27) {
                $input.val(oldVal);
                $input.hide();
                $cell.find('.voto-valor').show();
                return;
            }

            if (newVal === '' || newVal === oldVal) {
                $input.val(oldVal);
                $input.hide();
                $cell.find('.voto-valor').show();
                return;
            }

            $.ajax({
                url: '{{ url("certificados") }}/' + id,
                type: 'PUT',
                data: {
                    cantidad_votos: newVal,
                    _token: '{{ csrf_token() }}'
                },
                success: function(resp) {
                    $cell.data('value', newVal);
                    $cell.find('.voto-valor').text(Number(newVal).toLocaleString('es-PY'));
                    $input.hide();
                    $cell.find('.voto-valor').show();
                    Swal.fire({
                        icon: 'success',
                        title: 'Actualizado',
                        text: resp.message,
                        timer: 1000,
                        showConfirmButton: false
                    }).then(function() {
                        location.reload();
                    });
                },
                error: function() {
                    $input.val(oldVal);
                    $input.hide();
                    $cell.find('.voto-valor').show();
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo actualizar'
                    });
                }
            });
        });

        const successAlert = @json(session('success'));
        if (successAlert) {
            Swal.fire({ icon: 'success', title: 'Éxito', text: successAlert, timer: 1800, showConfirmButton: false });
        }

        // ==================== CHARTJS GRAPHS ====================
        @foreach ($chartLabels as $cargoNombre => $labels)
            new Chart(document.getElementById('chart-{{ $chartSlugs[$cargoNombre] }}'), {
                type: 'bar',
                data: {
                    labels: @json($labels),
                    datasets: [{
                        label: 'Votos',
                        data: @json($chartData[$cargoNombre]),
                        backgroundColor: @json($chartColors[$cargoNombre]),
                        borderColor: @json($chartColors[$cargoNombre]),
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: function(value) { return value.toLocaleString('es-PY'); }
                            }
                        }
                    }
                }
            });
        @endforeach
    });
</script>
@stop
