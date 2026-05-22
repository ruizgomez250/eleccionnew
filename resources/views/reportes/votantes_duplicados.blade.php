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
@stop

@section('content')

    {{-- Tarjetas de resumen --}}
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($totalCedulasDuplicadas, 0, ',', '.') }}</h4>
                    <p>Cédulas Duplicadas</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoPuntero, 0, ',', '.') }}</h4>
                    <p>Con duplicado por Puntero</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>
        
        <div class="col-md-4">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($totalConDuplicadoDirigente, 0, ',', '.') }}</h4>
                    <p>Con duplicado por Dirigente</p>
                </div>
                <div class="icon"><i class="fas fa-user-tag"></i></div>
            </div>
        </div>
    </div>

    {{-- Tabla de resultados --}}
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5><i class="fas fa-list"></i> Votantes Duplicados</h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                <strong>Nota:</strong> Solo se muestran cédulas que tienen duplicados que involucran a <strong>MI SISTEMA</strong>.
                Los badges indican el tipo de duplicado:
                <span class="badge badge-warning mx-1">x2</span> = Misma cédula repetida,
                <span class="badge badge-info mx-1">Puntero x2</span> = Mismo votante en diferentes punteros,
                <span class="badge badge-danger mx-1">Dirigente x2</span> = Punteros de diferentes dirigentes
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
                                <td>
                                    @if($item->duplicado_por_puntero)
                                        <span class="badge badge-info">
                                            <i class="fas fa-user-tie"></i> Puntero x{{ $item->total_punteros }}
                                        </span>
                                    @endif
                                    @if($item->duplicado_por_dirigente)
                                        <span class="badge badge-danger">
                                            <i class="fas fa-user-tag"></i> Dirigente x{{ $item->total_dirigentes }}
                                        </span>
                                    @endif
                                    @if(!$item->duplicado_por_puntero && !$item->duplicado_por_dirigente && $item->total_registros > 1)
                                        <span class="badge badge-warning">
                                            <i class="fas fa-clone"></i> Votante x{{ $item->total_registros }}
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
    });
</script>
@endpush