@extends('adminlte::page')

@section('title', 'Reporte de Duplicados entre Sistemas')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clone"></i> Reporte de Duplicados entre Sistemas
        </h4>
        <div>
            <a href="{{ route('arbol') }}" class="btn btn-secondary btn-sm">
                <i class="fas fa-arrow-left"></i> Volver al Árbol
            </a>
            <span class="badge badge-primary ml-2">
                <i class="fas fa-building"></i> Mi Sistema ID: {{ $sistemaUsuario }}
            </span>
        </div>
    </div>
    <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
        <i class="fas fa-info-circle"></i> Muestra votantes, punteros y dirigentes que están registrados en <strong>más de un sistema</strong>.
    </p>
@stop

@section('content')

    {{-- Tarjetas de resumen --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format(count($votantesDuplicados), 0, ',', '.') }}</h4>
                    <p>Votantes duplicados entre sistemas</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format(count($punterosDuplicados), 0, ',', '.') }}</h4>
                    <p>Punteros duplicados entre sistemas</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format(count($dirigentesDuplicados), 0, ',', '.') }}</h4>
                    <p>Dirigentes duplicados entre sistemas</p>
                </div>
                <div class="icon"><i class="fas fa-user-tag"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="card">
        <div class="card-header bg-primary text-white p-0">
            <ul class="nav nav-tabs card-header-tabs ml-2 pt-2" id="duplicadosTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active text-white" id="votantes-tab" data-toggle="tab" href="#votantes" role="tab">
                        <i class="fas fa-id-card"></i> Votantes
                        <span class="badge badge-light ml-1">{{ count($votantesDuplicados) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" id="punteros-tab" data-toggle="tab" href="#punteros" role="tab">
                        <i class="fas fa-user-tie"></i> Punteros
                        <span class="badge badge-light ml-1">{{ count($punterosDuplicados) }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link text-white" id="dirigentes-tab" data-toggle="tab" href="#dirigentes" role="tab">
                        <i class="fas fa-user-tag"></i> Dirigentes
                        <span class="badge badge-light ml-1">{{ count($dirigentesDuplicados) }}</span>
                    </a>
                </li>
            </ul>
        </div>
        <div class="card-body">
            <div class="tab-content" id="duplicadosTabContent">

                {{-- TAB VOTANTES --}}
                <div class="tab-pane fade show active" id="votantes" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-votantes">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Votante</th>
                                    <th>Registros</th>
                                    <th>Sistemas donde está duplicado</th>
                                    <th>Punteros involucrados</th>
                                    <th>Dirigentes involucrados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($votantesDuplicados as $item)
                                    <tr class="{{ $item['tiene_sistema_usuario'] ? '' : 'table-secondary' }}">
                                        <td>
                                            {{ $item['cedula'] }}
                                            <span class="badge badge-warning ml-1">x{{ $item['total_registros'] }}</span>
                                        </td>
                                        <td>{{ $item['nombre'] ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-warning">{{ $item['total_registros'] }} registros</span>
                                            <br>
                                            <small class="text-muted">{{ $item['total_sistemas'] }} sistemas diferentes</small>
                                        </td>
                                        <td>
                                            @foreach($item['sistemas_info'] as $s)
                                                <span class="badge badge-{{ $s['id'] == $sistemaUsuario ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i> {{ $s['nombre'] }}
                                                </span>
                                            @endforeach
                                            @if($item['tiene_sistema_usuario'])
                                                <br><small class="text-success"><i class="fas fa-check"></i> Incluye mi sistema</small>
                                            @else
                                                <br><small class="text-muted"><i class="fas fa-times"></i> No incluye mi sistema</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($item['punteros_info'] as $p)
                                                <div class="mb-1">
                                                    <span class="badge badge-info" data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                          title="Puntero" data-content="Cédula: {{ $p['cedula'] ?? 'N/A' }}<br>Sistema: {{ $p['sistema'] }}">
                                                        {{ $p['nombre'] }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($item['dirigentes_info'] as $d)
                                                <div class="mb-1">
                                                    <span class="badge badge-danger" data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                          title="Dirigente" data-content="Cédula: {{ $d['cedula'] ?? 'N/A' }}<br>Sistema: {{ $d['sistema'] }}">
                                                        {{ $d['nombre'] }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay votantes duplicados entre sistemas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB PUNTEROS --}}
                <div class="tab-pane fade" id="punteros" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-punteros">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Puntero</th>
                                    <th>Teléfono</th>
                                    <th>Registros</th>
                                    <th>Sistemas donde está duplicado</th>
                                    <th>Dirigentes involucrados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($punterosDuplicados as $item)
                                    <tr class="{{ $item['tiene_sistema_usuario'] ? '' : 'table-secondary' }}">
                                        <td>
                                            {{ $item['cedula'] }}
                                            <span class="badge badge-info ml-1">x{{ $item['total_registros'] }}</span>
                                        </td>
                                        <td>{{ $item['nombre'] ?? 'N/A' }}</td>
                                        <td>{{ $item['telefono'] ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-info">{{ $item['total_registros'] }} registros</span>
                                            <br>
                                            <small class="text-muted">{{ $item['total_sistemas'] }} sistemas diferentes</small>
                                        </td>
                                        <td>
                                            @foreach($item['sistemas_info'] as $s)
                                                <span class="badge badge-{{ $s['id'] == $sistemaUsuario ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i> {{ $s['nombre'] }}
                                                </span>
                                            @endforeach
                                            @if($item['tiene_sistema_usuario'])
                                                <br><small class="text-success"><i class="fas fa-check"></i> Incluye mi sistema</small>
                                            @else
                                                <br><small class="text-muted"><i class="fas fa-times"></i> No incluye mi sistema</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($item['dirigentes_info'] as $d)
                                                <div class="mb-1">
                                                    <span class="badge badge-danger" data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                          title="Dirigente" data-content="Cédula: {{ $d['cedula'] ?? 'N/A' }}<br>Sistema: {{ $d['sistema'] }}">
                                                        {{ $d['nombre'] }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay punteros duplicados entre sistemas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                {{-- TAB DIRIGENTES --}}
                <div class="tab-pane fade" id="dirigentes" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-dirigentes">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Dirigente</th>
                                    <th>Teléfono</th>
                                    <th>Registros</th>
                                    <th>Sistemas donde está duplicado</th>
                                    <th>Equipos involucrados</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($dirigentesDuplicados as $item)
                                    <tr class="{{ $item['tiene_sistema_usuario'] ? '' : 'table-secondary' }}">
                                        <td>
                                            {{ $item['cedula'] }}
                                            <span class="badge badge-danger ml-1">x{{ $item['total_registros'] }}</span>
                                        </td>
                                        <td>{{ $item['nombre'] ?? 'N/A' }}</td>
                                        <td>{{ $item['telefono'] ?? 'N/A' }}</td>
                                        <td>
                                            <span class="badge badge-danger">{{ $item['total_registros'] }} registros</span>
                                            <br>
                                            <small class="text-muted">{{ $item['total_sistemas'] }} sistemas diferentes</small>
                                        </td>
                                        <td>
                                            @foreach($item['sistemas_info'] as $s)
                                                <span class="badge badge-{{ $s['id'] == $sistemaUsuario ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i> {{ $s['nombre'] }}
                                                </span>
                                            @endforeach
                                            @if($item['tiene_sistema_usuario'])
                                                <br><small class="text-success"><i class="fas fa-check"></i> Incluye mi sistema</small>
                                            @else
                                                <br><small class="text-muted"><i class="fas fa-times"></i> No incluye mi sistema</small>
                                            @endif
                                        </td>
                                        <td>
                                            @foreach($item['equipos_info'] as $e)
                                                <div class="mb-1">
                                                    <span class="badge badge-secondary">{{ $e['nombre'] }} <small class="text-muted">({{ $e['sistema'] }})</small></span>
                                                </div>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay dirigentes duplicados entre sistemas
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </div>

@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#table-votantes').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });

        $('#table-punteros').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });

        $('#table-dirigentes').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });

        $('[data-toggle="popover"]').popover({
            container: 'body',
            boundary: 'viewport'
        });
    });
</script>
@endpush
