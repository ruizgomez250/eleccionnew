@extends('adminlte::page')

@section('title', 'Duplicados dentro del mismo Sistema')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clone"></i> Duplicados dentro del mismo Sistema
        </h4>
        <div>
            <span class="badge badge-primary">
                <i class="fas fa-building"></i> Mi Sistema ID: {{ $sistemaUsuario ?? Auth::user()->sistema }}
            </span>
        </div>
    </div>
    <p class="text-muted mt-2 mb-0" style="font-size: 0.9rem;">
        <i class="fas fa-sitemap"></i> Jerarquía: <strong>Votante</strong> → <strong>Puntero</strong> → <strong>Dirigente</strong>
    </p>
@stop

@section('content')

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($totalCedulasDuplicadas, 0, ',', '.') }}</h4>
                    <p>Cédulas duplicadas (total)</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoPuntero, 0, ',', '.') }}</h4>
                    <p>Duplicadas entre punteros</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoDirigente, 0, ',', '.') }}</h4>
                    <p>Duplicadas entre dirigentes</p>
                </div>
                <div class="icon"><i class="fas fa-user-tag"></i></div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-list"></i> Listado de Cédulas Duplicadas (mismo sistema)</h5>
            <small>Votantes repetidos dentro de <strong>MI SISTEMA</strong>.</small>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i>
                <strong>¿Qué se está duplicando?</strong> Una misma cédula aparece más de una vez dentro de <strong>mi mismo sistema</strong>.
                <br><br>
                <div class="row">
                    <div class="col-md-4">
                        <span class="badge badge-warning" style="font-size: 0.9rem;">Votante x2</span><br>
                        <small>El votante fue registrado más de una vez en el <strong>mismo puntero</strong>.</small>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-info" style="font-size: 0.9rem;">Puntero x2</span><br>
                        <small>El votante está asignado a <strong>distintos punteros</strong> (mismo dirigente o diferentes).</small>
                    </div>
                    <div class="col-md-4">
                        <span class="badge badge-danger" style="font-size: 0.9rem;">Dirigente x2</span><br>
                        <small>Los punteros pertenecen a <strong>diferentes dirigentes</strong>.</small>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table-duplicados-interno">
                    <thead class="thead-dark">
                        <tr>
                            <th>Cédula</th>
                            <th>Votante</th>
                            <th>Dirección</th>
                            <th>Mesa/Orden</th>
                            <th>Puntero</th>
                            <th>Dirigente</th>
                            <th>Duplicados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resultados as $item)
                            <tr>
                                <td>
                                    {{ $item->cedula }}
                                    <span class="badge badge-warning ml-1">x{{ $item->total_registros }}</span>
                                </td>
                                <td>{{ $item->nombre ?? 'N/A' }}</td>
                                <td>{{ $item->direccion ?? 'N/A' }}</td>
                                <td>{{ $item->mesa ?? 'N/A' }} - {{ $item->orden ?? 'N/A' }}</td>
                                <td>{{ $item->puntero }}</td>
                                <td>{{ $item->dirigente }}</td>
                                <td style="min-width: 280px;">
                                    @if($item->duplicado_simple)
                                        <span class="badge badge-warning"
                                              data-toggle="popover" data-trigger="hover focus" data-html="true"
                                              title="Se duplicó en el mismo puntero"
                                              data-content="
                                                  @foreach($item->punteros_info as $p)
                                                     - {{ $p['nombre'] }}<br>
                                                  @endforeach">
                                            <i class="fas fa-clone"></i> Votante x{{ $item->total_registros }}
                                        </span>
                                        <br><small class="text-muted">Puntero: {{ $item->puntero }}</small>
                                    @endif

                                    @if($item->duplicado_por_puntero)
                                        <span class="badge badge-info"
                                              data-toggle="popover" data-trigger="hover focus" data-html="true"
                                              title="Punteros ({{ $item->total_punteros }} diferentes)"
                                              data-content="
                                                  @foreach($item->punteros_info as $p)
                                                     - {{ $p['nombre'] }}<br>
                                                  @endforeach">
                                            <i class="fas fa-user-tie"></i> Puntero x{{ $item->total_punteros }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            @foreach($item->punteros_info as $i => $p)
                                                @if($i > 0) <span class="text-info">↔</span> @endif
                                                {{ $p['nombre'] }}
                                            @endforeach
                                        </small>
                                    @endif

                                    @if($item->duplicado_por_dirigente)
                                        <span class="badge badge-danger"
                                              data-toggle="popover" data-trigger="hover focus" data-html="true"
                                              title="Dirigentes ({{ $item->total_dirigentes }} diferentes)"
                                              data-content="
                                                  @foreach($item->dirigentes_info as $d)
                                                     - {{ $d['nombre'] }}<br>
                                                  @endforeach">
                                            <i class="fas fa-user-tag"></i> Dirigente x{{ $item->total_dirigentes }}
                                        </span>
                                        <br>
                                        <small class="text-muted">
                                            @foreach($item->dirigentes_info as $i => $d)
                                                @if($i > 0) <span class="text-danger">↔</span> @endif
                                                {{ $d['nombre'] }}
                                            @endforeach
                                        </small>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-success">
                                    <i class="fas fa-check-circle"></i> No hay votantes duplicados dentro de mi sistema
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

@stop

@push('js')
<script>
    $(document).ready(function() {
        $('#table-duplicados-interno').DataTable({
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
