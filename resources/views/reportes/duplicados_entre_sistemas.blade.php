@extends('adminlte::page')

@section('title', 'Reporte de Duplicados entre Sistemas')

@section('content_header')
    <div class="ua-header">
        <div>
            <h1 class="ua-title"><i class="fas fa-clone"></i> Reporte de Duplicados entre Sistemas</h1>
            <p class="ua-subtitle">Votantes, punteros y dirigentes registrados en más de un sistema</p>
        </div>
        <div>
            <a href="{{ route('reportes.duplicados.entre.sistemas.pdf') }}" class="btn btn-danger btn-sm" target="_blank">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
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

    @if($esUsuarioPrivilegiado)
    <div class="card mt-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('reportes.duplicados.entre.sistemas') }}" class="form-inline">
                <div class="form-group mr-3">
                    <label for="ciudad_id" class="mr-2"><i class="fas fa-city"></i> Distrito:</label>
                    <select name="ciudad_id" id="ciudad_id" class="form-control form-control-sm select2" style="min-width: 220px;">
                        <option value="">-- Todos los distritos --</option>
                        @foreach($ciudades as $ciudad)
                            <option value="{{ $ciudad->id }}" {{ request('ciudad_id') == $ciudad->id ? 'selected' : '' }}>
                                {{ $ciudad->descripcion }} {{ $ciudad->departamento ? '- ' . $ciudad->departamento : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group mr-3">
                    <label for="sistema_id" class="mr-2"><i class="fas fa-building"></i> Sistema:</label>
                    <select name="sistema_id" id="sistema_id" class="form-control form-control-sm select2" style="min-width: 270px;">
                        <option value="todos" {{ request('sistema_id') == 'todos' ? 'selected' : '' }}>-- Todos los sistemas --</option>
                        @foreach($sistemas as $sistema)
                            <option value="{{ $sistema->id }}" {{ request('sistema_id') == $sistema->id ? 'selected' : '' }}
                                data-ciudad="{{ $sistema->id_ciudad_electoral }}">
                                {{ $sistema->nombre }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filtrar</button>
                <a href="{{ route('reportes.duplicados.entre.sistemas') }}" class="btn btn-secondary btn-sm ml-1"><i class="fas fa-undo"></i> Limpiar</a>
            </form>
        </div>
    </div>
    @endif
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
    <div class="card ua-card">
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
                    <div class="loading-overlay">
                        <div class="overlay" id="loading-votantes">
                            <div class="text-center">
                                <div class="spinner-border text-primary" role="status" style="width:3rem;height:3rem;">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted"><i class="fas fa-spinner fa-pulse"></i> Cargando votantes duplicados...</p>
                            </div>
                        </div>
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
                                                <span class="badge badge-{{ $s['id'] == $sistemaConsulta ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i>
                                                    @if($s['id'] == $sistemaConsulta)
                                                        {{ $s['nombre'] }}
                                                    @else
                                                        @if($esUsuarioPrivilegiado)
                                                            {{ $s['nombre'] }}
                                                        @else
                                                            otro sistema
                                                        @endif
                                                    @endif
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
                                                          title="Puntero" data-content="Cédula: {{ $p['cedula'] ?? 'N/A' }}<br>Sistema: {{ $esUsuarioPrivilegiado ? $p['sistema'] : 'otro sistema' }}">
                                                        {{ $p['nombre'] }}
                                                    </span>
                                                </div>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($item['dirigentes_info'] as $d)
                                                <div class="mb-1">
                                                    <span class="badge badge-danger" data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                          title="Dirigente" data-content="Cédula: {{ $d['cedula'] ?? 'N/A' }}<br>Sistema: {{ $esUsuarioPrivilegiado ? $d['sistema'] : 'otro sistema' }}">
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
                    </div> {{-- loading-overlay --}}
                </div>

                {{-- TAB PUNTEROS --}}
                <div class="tab-pane fade" id="punteros" role="tabpanel">
                    <div class="loading-overlay">
                        <div class="overlay" id="loading-punteros">
                            <div class="text-center">
                                <div class="spinner-border text-info" role="status" style="width:3rem;height:3rem;">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted"><i class="fas fa-spinner fa-pulse"></i> Cargando punteros duplicados...</p>
                            </div>
                        </div>
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
                                                <span class="badge badge-{{ $s['id'] == $sistemaConsulta ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i>
                                                    @if($s['id'] == $sistemaConsulta)
                                                        {{ $s['nombre'] }}
                                                    @else
                                                        @if($esUsuarioPrivilegiado)
                                                            {{ $s['nombre'] }}
                                                        @else
                                                            otro sistema
                                                        @endif
                                                    @endif
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
                                                          title="Dirigente" data-content="Cédula: {{ $d['cedula'] ?? 'N/A' }}<br>Sistema: {{ $esUsuarioPrivilegiado ? $d['sistema'] : 'otro sistema' }}">
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
                    </div> {{-- loading-overlay --}}
                </div>

                {{-- TAB DIRIGENTES --}}
                <div class="tab-pane fade" id="dirigentes" role="tabpanel">
                    <div class="loading-overlay">
                        <div class="overlay" id="loading-dirigentes">
                            <div class="text-center">
                                <div class="spinner-border text-danger" role="status" style="width:3rem;height:3rem;">
                                    <span class="sr-only">Cargando...</span>
                                </div>
                                <p class="mt-2 text-muted"><i class="fas fa-spinner fa-pulse"></i> Cargando dirigentes duplicados...</p>
                            </div>
                        </div>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-dirigentes">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Dirigente</th>
                                    <th>Teléfono</th>
                                    <th>Registros</th>
                                    <th>Sistemas donde está duplicado</th>
                                    <th>Colegios electorales involucrados</th>
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
                                                <span class="badge badge-{{ $s['id'] == $sistemaConsulta ? 'primary' : 'secondary' }} d-inline-block mb-1">
                                                    <i class="fas fa-building"></i>
                                                    @if($s['id'] == $sistemaConsulta)
                                                        {{ $s['nombre'] }}
                                                    @else
                                                        @if($esUsuarioPrivilegiado)
                                                            {{ $s['nombre'] }}
                                                        @else
                                                            otro sistema
                                                        @endif
                                                    @endif
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
                                                    <span class="badge badge-secondary">{{ $e['nombre'] }} <small class="text-muted">({{ $esUsuarioPrivilegiado ? $e['sistema'] : 'otro sistema' }})</small></span>
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
                    </div> {{-- loading-overlay --}}
                </div>

            </div>
        </div>
    </div>

@stop

@push('css')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.dataTables.min.css">
<style>
.loading-overlay {
    position: relative;
}
.loading-overlay .overlay {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: rgba(255,255,255,0.85);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10;
    border-radius: 4px;
}
.loading-overlay .overlay.loaded {
    display: none;
}
</style>
@include('useradmin._dark_pages')
@endpush

@push('js')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<script>
    $(document).ready(function() {
        @if($esUsuarioPrivilegiado)
        $('.select2').select2({
            placeholder: 'Seleccionar...',
            allowClear: true,
            width: '100%'
        });

        function filtrarSistemasPorCiudad() {
            var ciudadId = $('#ciudad_id').val();
            var sistemaActual = '{{ request('sistema_id', 'todos') }}';

            $('#sistema_id option').each(function() {
                var $opt = $(this);
                if ($opt.val() === 'todos') {
                    $opt.show();
                    return;
                }
                var ciudad = $opt.data('ciudad');
                if (!ciudadId || ciudad == ciudadId) {
                    $opt.show();
                } else {
                    $opt.hide();
                }
            });

            if ($('#sistema_id').val() !== sistemaActual) {
                $('#sistema_id').val(sistemaActual).trigger('change');
            }
        }

        $('#ciudad_id').change(filtrarSistemasPorCiudad);
        filtrarSistemasPorCiudad();
        @endif

        var btnConfig = {
            buttons: [
                {
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    extend: 'excelHtml5',
                    title: 'Duplicados entre Sistemas',
                    exportOptions: { columns: ':visible' }
                },
                {
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    extend: 'pdfHtml5',
                    title: 'Duplicados entre Sistemas',
                    orientation: 'landscape',
                    pageSize: 'A4',
                    exportOptions: { columns: ':visible' }
                }
            ],
            dom: 'Bfrtip'
        };

        function initTable(id, loadingId) {
            if ($.fn.DataTable.isDataTable(id)) $(id).DataTable().destroy();
            var table = $(id).DataTable($.extend({
                responsive: true,
                language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
                pageLength: 25,
                order: [[0, 'asc']],
                initComplete: function() {
                    $(loadingId).addClass('loaded');
                }
            }, btnConfig));
            return table;
        }

        initTable('#table-votantes', '#loading-votantes');
        initTable('#table-punteros', '#loading-punteros');
        initTable('#table-dirigentes', '#loading-dirigentes');

        $('[data-toggle="popover"]').popover({
            container: 'body',
            boundary: 'viewport'
        });
    });
</script>
@endpush
