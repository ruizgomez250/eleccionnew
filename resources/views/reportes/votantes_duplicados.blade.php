@extends('adminlte::page')

@section('title', 'Reporte de Votantes Duplicados')

@section('content_header')
    <div class="d-flex justify-content-between align-items-center">
        <h4 class="mb-0">
            <i class="fas fa-clone"></i> Reporte de Votantes Duplicados
        </h4>
        <div>
            <span class="badge badge-info">
                <i class="fas fa-building"></i> Sistema: {{ Auth::user()->sistemaRelacion->nombre ?? Auth::user()->sistema }}
            </span>
            <button class="btn btn-sm btn-success ml-2" id="btnExportar">
                <i class="fas fa-file-excel"></i> Exportar
            </button>
        </div>
    </div>
@stop

@section('content')

    {{-- Mostrar advertencia si no hay datos --}}
    @if($totalVotantesSistema == 0)
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i> 
            No se encontraron votantes asociados al sistema actual. 
            Verifique que existan dirigentes y punteros asignados a este sistema.
        </div>
    @endif

    {{-- 🔹 Tarjetas de resumen --}}
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ number_format($totalVotantesSistema, 0, ',', '.') }}</h4>
                    <p>Total Votantes en Sistema</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalCedulasUnicas, 0, ',', '.') }}</h4>
                    <p>Cédulas Únicas</p>
                </div>
                <div class="icon"><i class="fas fa-id-card"></i></div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ number_format($totalVotantesEnDuplicados, 0, ',', '.') }}</h4>
                    <p>Votantes Duplicados</p>
                </div>
                <div class="icon"><i class="fas fa-exclamation-triangle"></i></div>
            </div>
        </div>
        
        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ number_format($totalVotantesEnDuplicados > 0 ? ($totalVotantesEnDuplicados / $totalVotantesSistema * 100) : 0, 1) }}%</h4>
                    <p>Tasa de Duplicación</p>
                </div>
                <div class="icon"><i class="fas fa-chart-pie"></i></div>
            </div>
        </div>
    </div>

    {{-- 🔹 Pestañas --}}
    <div class="card card-outline card-primary">
        <div class="card-header p-0">
            <ul class="nav nav-tabs" id="duplicadosTab" role="tablist">
                <li class="nav-item">
                    <a class="nav-link active" id="cedula-tab" data-toggle="tab" href="#cedula" role="tab">
                        <i class="fas fa-id-card"></i> Por Cédula 
                        <span class="badge badge-danger">{{ $votantesDuplicados->unique('cedula')->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="puntero-tab" data-toggle="tab" href="#puntero" role="tab">
                        <i class="fas fa-user-tie"></i> Por Puntero
                        <span class="badge badge-warning">{{ $votantesConMultiplesPunteros->unique('cedula')->count() }}</span>
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" id="dirigente-tab" data-toggle="tab" href="#dirigente" role="tab">
                        <i class="fas fa-user-tag"></i> Por Dirigente
                        <span class="badge badge-info">{{ $votantesDuplicadosPorDirigente->unique('cedula')->count() }}</span>
                    </a>
                </li>
            </ul>
        </div>
        
        <div class="card-body">
            <div class="tab-content">
                
                {{-- TAB 1: Duplicados por Cédula --}}
                <div class="tab-pane fade show active" id="cedula" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-cedula">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Nombre del Votante</th>
                                    <th>Dirección</th>
                                    <th>Mesa / Orden</th>
                                    <th>Puntero</th>
                                    <th>Dirigente</th>
                                    <th>Equipo</th>
                                    <th># Registros</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($votantesDuplicados->groupBy('cedula') as $cedula => $registros)
                                    @php $contador = count($registros); @endphp
                                    @foreach($registros as $votante)
                                        <tr class="{{ $loop->first ? 'table-warning' : '' }}">
                                            <td>
                                                {{ $cedula }}
                                                @if($loop->first)
                                                    <span class="badge badge-danger float-right">x{{ $contador }}</span>
                                                @endif
                                            </td>
                                            <td>{{ $votante->nombre }}</td>
                                            <td>{{ $votante->direccion ?? 'N/A' }}</td>
                                            <td>{{ $votante->mesa }} - {{ $votante->orden }}</td>
                                            <td>{{ $votante->puntero->nombre ?? 'N/A' }}</td>
                                            <td>{{ $votante->puntero->dirigente->nombre ?? 'N/A' }}</td>
                                            <td>{{ $votante->puntero->equipo->descripcion ?? 'N/A' }}</td>
                                            <td class="text-center">{{ $contador }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="8" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay votantes duplicados por cédula en este sistema
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                            </table>
                    </div>
                </div>
                
                {{-- TAB 2: Duplicados por Puntero --}}
                <div class="tab-pane fade" id="puntero" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-puntero">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Votante</th>
                                    <th>Cant. Punteros</th>
                                    <th>Punteros Asignados</th>
                                    <th>Dirigentes</th>
                                    <th>Equipos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($votantesConMultiplesPunteros->groupBy('cedula') as $cedula => $registros)
                                    @php
                                        $punterosUnicos = $registros->pluck('puntero.nombre')->unique()->filter();
                                        $dirigentesUnicos = $registros->pluck('puntero.dirigente.nombre')->unique()->filter();
                                        $equiposUnicos = $registros->pluck('puntero.equipo.descripcion')->unique()->filter();
                                    @endphp
                                    <tr>
                                        <td>{{ $cedula }}</td>
                                        <td>{{ $registros->first()->nombre }}</td>
                                        <td class="text-center">
                                            <span class="badge badge-warning">{{ $punterosUnicos->count() }}</span>
                                        </td>
                                        <td>
                                            @foreach($punterosUnicos as $puntero)
                                                <span class="badge badge-info d-block mb-1">{{ $puntero }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($dirigentesUnicos as $dirigente)
                                                <span class="badge badge-danger d-block mb-1">{{ $dirigente }}</span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($equiposUnicos as $equipo)
                                                <span class="badge badge-secondary d-block mb-1">{{ $equipo }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay votantes con múltiples punteros
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                {{-- TAB 3: Duplicados por Dirigente --}}
                <div class="tab-pane fade" id="dirigente" role="tabpanel">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped table-hover" id="table-dirigente">
                            <thead class="thead-dark">
                                <tr>
                                    <th>Cédula</th>
                                    <th>Votante</th>
                                    <th>Dirigentes</th>
                                    <th>Punteros (Dirigente)</th>
                                    <th>Equipos</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($votantesDuplicadosPorDirigente->groupBy('cedula') as $cedula => $registros)
                                    @php
                                        $agrupadoPorDirigente = [];
                                        foreach($registros as $votante) {
                                            if($votante->puntero && $votante->puntero->dirigente) {
                                                $dirigenteId = $votante->puntero->dirigente->id;
                                                $dirigenteNombre = $votante->puntero->dirigente->nombre;
                                                if(!isset($agrupadoPorDirigente[$dirigenteId])) {
                                                    $agrupadoPorDirigente[$dirigenteId] = [
                                                        'nombre' => $dirigenteNombre,
                                                        'punteros' => collect()
                                                    ];
                                                }
                                                $agrupadoPorDirigente[$dirigenteId]['punteros']->push($votante->puntero->nombre);
                                            }
                                        }
                                    @endphp
                                    <tr>
                                        <td>{{ $cedula }}</td>
                                        <td>{{ $registros->first()->nombre }}</td>
                                        <td>
                                            @foreach($agrupadoPorDirigente as $dirigente)
                                                <span class="badge badge-danger d-block mb-1">
                                                    {{ $dirigente['nombre'] }}
                                                </span>
                                            @endforeach
                                        </td>
                                        <td>
                                            @foreach($agrupadoPorDirigente as $dirigente)
                                                <strong>{{ $dirigente['nombre'] }}:</strong>
                                                {{ $dirigente['punteros']->unique()->implode(', ') }}
                                                <br>
                                            @endforeach
                                        </td>
                                        <td>
                                            @php
                                                $equiposUnicos = $registros->pluck('puntero.equipo.descripcion')->unique()->filter();
                                            @endphp
                                            @foreach($equiposUnicos as $equipo)
                                                <span class="badge badge-secondary">{{ $equipo }}</span>
                                            @endforeach
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-success">
                                            <i class="fas fa-check-circle"></i> No hay votantes con punteros de diferentes dirigentes
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
        // Inicializar DataTables
        $('#table-cedula').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });
        
        $('#table-puntero').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });
        
        $('#table-dirigente').DataTable({
            responsive: true,
            language: { url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
            pageLength: 25,
            order: [[0, 'asc']]
        });
        
        // Botón exportar
        $('#btnExportar').click(function() {
            Swal.fire({
                title: 'Exportar reporte',
                text: '¿Qué formato deseas?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Excel',
                cancelButtonText: 'Cancelar',
                showDenyButton: true,
                denyButtonText: 'PDF'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '{{ route("reportes.votantes.duplicados.exportar") }}?formato=excel';
                } else if (result.isDenied) {
                    window.location.href = '{{ route("reportes.votantes.duplicados.exportar") }}?formato=pdf';
                }
            });
        });
    });
</script>
@endpush