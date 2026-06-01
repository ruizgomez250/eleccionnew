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

    {{-- Tabla de resultados --}}
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-list"></i> Listado de Cédulas Duplicadas</h5>
            <small>Se muestra la fila correspondiente a <strong>mi sistema</strong>; la cédula también existe en otros punteros/dirigentes.</small>
        </div>
        <div class="card-body">
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
            
            <div class="table-responsive">
                <table class="table table-bordered table-striped table-hover" id="table-duplicados">
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
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($resultados as $item)
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
                                                 @if(($sistemaUsuario ?? Auth::user()->sistema) == $s['id'])
                                                    - <strong>duplicado</strong> ({{ $s['nombre'] }})<br>
                                                 @else
                                                    - otro candidato<br>
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
                                                     @if(($sistemaUsuario ?? Auth::user()->sistema) == ($p['sistema_id'] ?? null))
                                                        - {{ $p['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                     @else
                                                        - {{ $p['nombre'] }} <small class='text-muted'>(otro candidato)</small><br>
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
                                                     @if(($sistemaUsuario ?? Auth::user()->sistema) == ($p['sistema_id'] ?? null))
                                                        - {{ $p['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                     @else
                                                        - {{ $p['nombre'] }} <small class='text-muted'>(otro candidato)</small><br>
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
                                                     @if(($sistemaUsuario ?? Auth::user()->sistema) == ($d['sistema_id'] ?? null))
                                                        - {{ $d['nombre'] }} <small class='text-muted'>(duplicado)</small><br>
                                                     @else
                                                        - {{ $d['nombre'] }} <small class='text-muted'>(otro candidato)</small><br>
                                                     @endif
                                                  @endforeach">
                                            <i class="fas fa-user-tag"></i> Dirigente x{{ $item->total_dirigentes }}
                                        </span>
                                    @endif
                                 </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-success">
                                    <i class="fas fa-check-circle"></i> No hay votantes duplicados que involucren a mi sistema
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
        $('#table-duplicados').DataTable({
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