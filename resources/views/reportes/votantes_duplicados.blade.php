@extends('adminlte::page')

@section('title', 'Reporte de Votantes Duplicados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clone"></i> Reporte de Votantes Duplicados
        </h4>
        <div>
            <span class="badge badge-primary">
                <i class="fas fa-building"></i> Mi Sistema ID: {{ $sistemaUsuario ?? Auth::user()->sistema }}
            </span>
        </div>
    </div>
    <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
        <i class="fas fa-sitemap"></i> Jerarquía: <strong>Votante</strong> → <strong>Puntero</strong> → <strong>Dirigente</strong> → <strong>Equipo</strong>
    </p>

    @if($esUsuarioPrivilegiado)
    <div class="card mt-3">
        <div class="card-body py-2">
            <form method="GET" action="{{ route('reportes.votantes.duplicados') }}" class="form-inline">
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
                <a href="{{ route('reportes.votantes.duplicados') }}" class="btn btn-secondary btn-sm ml-1"><i class="fas fa-undo"></i> Limpiar</a>
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
                    <h4>{{ number_format($totalCedulasDuplicadas, 0, ',', '.') }}</h4>
                    <p>Cédulas duplicadas entre candidaturas</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoPuntero, 0, ',', '.') }}</h4>
                    <p>Punteros duplicados entre sistemas</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoDirigente, 0, ',', '.') }}</h4>
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i>
                        <strong>¿Qué se está duplicando?</strong> Una misma cédula de votante aparece más de una vez en la base de datos.
                        Esto puede ocurrir a distintos niveles de la jerarquía:
                        <br><br>
                        <div class="row">
                            <div class="col-md-4">
                                <span class="badge badge-warning" style="font-size: 0.9rem;">x2 / Votante x2</span><br>
                                <small>El <strong>votante</strong> (cédula) fue registrado más de una vez dentro del <strong>mismo puntero</strong>.</small>
                            </div>
                            <div class="col-md-4">
                                <span class="badge badge-info" style="font-size: 0.9rem;">Puntero x2</span><br>
                                <small>El mismo votante está asignado a <strong>distintos punteros</strong> (aunque sean del mismo dirigente).</small>
                            </div>
                            <div class="col-md-4">
                                <span class="badge badge-danger" style="font-size: 0.9rem;">Dirigente x2</span><br>
                                <small>Los punteros donde está el votante pertenecen a <strong>diferentes dirigentes</strong> (o diferentes equipos).</small>
                            </div>
                        </div>
                        <hr>
                        <i class="fas fa-filter"></i> Solo se muestran cédulas duplicadas <strong>entre diferentes candidaturas (sistemas)</strong> que involucren a <strong>MI SISTEMA</strong>. Duplicados dentro del mismo sistema no se tienen en cuenta.
                    </div>

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
                                    <th>Dirección</th>
                                    <th>Mesa/Orden</th>
                                    <th>Puntero (mi sistema)</th>
                                    <th>Dirigente (mi sistema)</th>
                                    <th>Equipo</th>
                                    <th>Duplicados</th>
                                    @if($esUsuarioPrivilegiado)
                                        <th>Otro Sistema</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($votantesDuplicados as $item)
                                    <tr>
                                        <td>
                                            {{ $item->cedula }}
                                            @if($item->total_registros > 1)
                                                <span class="badge badge-warning ml-1">x{{ $item->total_registros }}</span>
                                            @endif
                                        </td>
                                        <td>{{ $item->nombre ?? 'N/A' }}</td>
                                        <td>{{ $item->direccion ?? 'N/A' }}</td>
                                        <td>{{ $item->mesa ?? 'N/A' }} - {{ $item->orden ?? 'N/A' }}</td>
                                        <td>{{ $item->puntero }}</td>
                                        <td>{{ $item->dirigente }}</td>
                                        <td>{{ $item->equipo }}</td>
                                        <td style="min-width: 180px;">
                                            @php
                                                $esVotante = !$item->duplicado_por_puntero && !$item->duplicado_por_dirigente && $item->total_registros > 1;
                                            @endphp

                                            <span class="badge badge-warning"
                                                  data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                  title="Candidaturas ({{ $item->total_registros }} veces)"
                                                  data-content="
                                                      @foreach($item->sistemas_info as $s)
                                                         @if($s['id'] == $sistemaConsulta)
                                                            - <strong>duplicado</strong> ({{ $s['nombre'] }})<br>
                                                         @else
                                                            @if($esUsuarioPrivilegiado)
                                                                - {{ $s['nombre'] }}<br>
                                                            @else
                                                                - otro sistema<br>
                                                            @endif
                                                         @endif
                                                      @endforeach">
                                                <i class="fas fa-clone"></i> x{{ $item->total_registros }}
                                            </span>

                                            @if($esVotante)
                                                <span class="badge badge-warning"
                                                      data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                      title="Mismo puntero"
                                                      data-content="
                                                          @foreach($item->punteros_info as $p)
                                                             @if($sistemaConsulta == ($p['sistema_id'] ?? null))
                                                                - {{ $p['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                             @else
                                                                - {{ $p['nombre'] }} <small class='text-muted'>@if($esUsuarioPrivilegiado)({{ $p['sistema'] }})@else(otro sistema)@endif</small><br>
                                                             @endif
                                                          @endforeach">
                                                    <i class="fas fa-user-check"></i> Votante x{{ $item->total_registros }}
                                                </span>
                                            @endif

                                            @if($item->duplicado_por_puntero)
                                                <span class="badge badge-info"
                                                      data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                      title="Punteros ({{ $item->total_punteros }} diferentes)"
                                                      data-content="
                                                          @foreach($item->punteros_info as $p)
                                                             @if($sistemaConsulta == ($p['sistema_id'] ?? null))
                                                                - {{ $p['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                             @else
                                                                - {{ $p['nombre'] }} <small class='text-muted'>@if($esUsuarioPrivilegiado)({{ $p['sistema'] }})@else(otro sistema)@endif</small><br>
                                                             @endif
                                                          @endforeach">
                                                    <i class="fas fa-user-tie"></i> Puntero x{{ $item->total_punteros }}
                                                </span>
                                            @endif

                                            @if($item->duplicado_por_dirigente)
                                                <span class="badge badge-danger"
                                                      data-toggle="popover" data-trigger="hover focus" data-html="true"
                                                      title="Dirigentes ({{ $item->total_dirigentes }} diferentes)"
                                                      data-content="
                                                          @foreach($item->dirigentes_info as $d)
                                                             @if($sistemaConsulta == ($d['sistema_id'] ?? null))
                                                                - {{ $d['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                             @else
                                                                - {{ $d['nombre'] }} <small class='text-muted'>@if($esUsuarioPrivilegiado)({{ $d['sistema'] }})@else(otro sistema)@endif</small><br>
                                                             @endif
                                                          @endforeach">
                                                    <i class="fas fa-user-tag"></i> Dirigente x{{ $item->total_dirigentes }}
                                                </span>
                                             @endif
                                         </td>
                                         @if($esUsuarioPrivilegiado)
                                         <td>
                                             @foreach($item->punteros_info as $p)
                                                 @if(($p['sistema_id'] ?? null) != $sistemaConsulta)
                                                     <span class="badge badge-secondary d-inline-block mb-1" style="font-size:0.8rem;white-space:normal;text-align:left;">
                                                         {{ $p['sistema'] }} -> {{ $p['dirigente'] }} -> {{ $p['nombre'] }}
                                                     </span><br>
                                                 @endif
                                             @endforeach
                                         </td>
                                         @endif
                                     </tr>
                                 @empty
                                     <tr>
                                         <td colspan="{{ $esUsuarioPrivilegiado ? 9 : 8 }}" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay votantes duplicados que involucren a mi sistema
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Muestra punteros registrados con la misma cédula en <strong>más de un sistema</strong>.
                    </div>
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
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Muestra dirigentes registrados con la misma cédula en <strong>más de un sistema</strong>.
                    </div>
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
                    title: 'Votantes Duplicados',
                    exportOptions: { columns: ':visible' }
                },
                {
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    extend: 'pdfHtml5',
                    title: 'Votantes Duplicados',
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
