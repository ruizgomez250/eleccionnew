@extends('adminlte::page')

@section('title', 'Reporte por Local de Votación')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-map-marker-alt"></i> Reporte por Local de Votación
    </h4>
@stop

@section('content')

    {{-- 🔹 Totales Generales --}}
    <div class="row mb-3">
        <div class="col-md-3">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ $totalEquipos }}</h4>
                    <p>Total Locales</p>
                </div>
                <div class="icon"><i class="fas fa-building"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ $totalDirigentes }}</h4>
                    <p>Total Dirigentes</p>
                </div>
                <div class="icon"><i class="fas fa-user-tie"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ $totalPunteros }}</h4>
                    <p>Total Punteros</p>
                </div>
                <div class="icon"><i class="fas fa-users"></i></div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="small-box bg-danger">
                <div class="inner">
                    <h4>{{ $totalVotantes }}</h4>
                    <p>Total Votantes</p>
                </div>
                <div class="icon"><i class="fas fa-check-circle"></i></div>
            </div>
        </div>
    </div>

    {{-- Segunda fila de tarjetas (opcional) --}}
    <div class="row mb-4">
        <div class="col-md-6">
            <div class="small-box bg-primary">
                <div class="inner">
                    <h4>{{ $totalVotos ?? 0 }}</h4>
                    <p>Votantes que Votaron en la ultima eleccion</p>
                </div>
                <div class="icon"><i class="fas fa-vote-yea"></i></div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="small-box bg-secondary">
                <div class="inner">
                    <h4>{{ $totalSinVoto ?? 0 }}</h4>
                    <p>Votantes que NO Votaron en la ultima eleccion</p>
                </div>
                <div class="icon"><i class="fas fa-ban"></i></div>
            </div>
        </div>
    </div>

    {{-- 🔹 Tabla de Equipos/Locales --}}
    <div class="card">
        <div class="card-header">
            <h5 class="mb-0">Detalle por Local de Votación</h5>
        </div>
        <div class="card-body">

            <table id="equipos-table" class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Local de Votación</th>
                        <th>Colegio</th>
                        <th>Ciudad</th>
                        <th>Dirigentes</th>
                        <th>Total Dirigentes</th>
                        <th>Total Punteros</th>
                        <th>Total Votantes</th>
                        <th>Total Vehículos</th>
                        <th>Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($equipos as $equipo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $equipo->descripcion ?? $equipo->nombre }}</td>
                            <td>{{ $equipo->colegio ?? 'N/A' }}</td>
                            <td>{{ $equipo->ciudad ?? 'N/A' }}</td>
                            
                            {{-- Lista de dirigentes --}}
                            <td>
                                @if ($equipo->dirigentes->isEmpty())
                                    <span class="text-muted">Sin dirigentes</span>
                                @else
                                    @foreach ($equipo->dirigentes as $dirigente)
                                        <span class="badge badge-info mb-1 d-inline-block">
                                            {{ $dirigente->nombre }}
                                        </span><br>
                                    @endforeach
                                @endif
                            </td>
                            
                            {{-- Totales --}}
                            <td class="text-center font-weight-bold">
                                {{ $equipo->dirigentes_count ?? $equipo->total_dirigentes ?? $equipo->dirigentes->count() }}
                            </td>
                            
                            <td class="text-center font-weight-bold">
                                {{ $equipo->punteros_count ?? $equipo->total_punteros ?? $equipo->punteros->count() }}
                            </td>
                            
                            <td class="text-center font-weight-bold">
                                {{ $equipo->votantes_count ?? $equipo->total_votantes ?? $equipo->votantes->count() }}
                            </td>
                            
                            <td class="text-center font-weight-bold">
                                {{ $equipo->vehiculos_count ?? $equipo->total_vehiculos ?? $equipo->vehiculos->count() }}
                            </td>
                            
                            {{-- Acciones --}}
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalEquipo{{ $equipo->id }}">
                                    <i class="fas fa-eye"></i> Ver detalle
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="5" class="text-right">TOTALES GENERALES:</td>
                        <td class="text-center">{{ $totalDirigentes }}</td>
                        <td class="text-center">{{ $totalPunteros }}</td>
                        <td class="text-center">{{ $totalVotantes }}</td>
                        <td class="text-center">{{ $totalVehiculos }}</td>
                        <td></td>
                    </tr>
                </tfoot>

            </table>

        </div>
    </div>

    {{-- 🔹 Modales para cada equipo/local --}}
    @foreach ($equipos as $equipo)
        <div class="modal fade" id="modalEquipo{{ $equipo->id }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle"></i> Detalle del Local: {{ $equipo->descripcion ?? $equipo->nombre }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        
                        {{-- Información General --}}
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <strong>🏫 Colegio:</strong> {{ $equipo->colegio ?? 'No registrado' }}
                            </div>
                            <div class="col-md-4">
                                <strong>🏙️ Ciudad:</strong> {{ $equipo->ciudad ?? 'No registrada' }}
                            </div>
                            <div class="col-md-4">
                                <strong>📊 Sistema ID:</strong> {{ $equipo->sist ?? 'N/A' }}
                            </div>
                        </div>

                        <ul class="nav nav-tabs" id="myTab{{ $equipo->id }}" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link active" id="dirigentes-tab-{{ $equipo->id }}" data-toggle="tab" href="#dirigentes-{{ $equipo->id }}" role="tab">
                                    <i class="fas fa-user-tie"></i> Dirigentes ({{ $equipo->dirigentes->count() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="punteros-tab-{{ $equipo->id }}" data-toggle="tab" href="#punteros-{{ $equipo->id }}" role="tab">
                                    <i class="fas fa-users"></i> Punteros ({{ $equipo->punteros->count() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="votantes-tab-{{ $equipo->id }}" data-toggle="tab" href="#votantes-{{ $equipo->id }}" role="tab">
                                    <i class="fas fa-check-circle"></i> Votantes ({{ $equipo->votantes->count() }})
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" id="vehiculos-tab-{{ $equipo->id }}" data-toggle="tab" href="#vehiculos-{{ $equipo->id }}" role="tab">
                                    <i class="fas fa-truck"></i> Vehículos ({{ $equipo->vehiculos->count() }})
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content mt-3" id="myTabContent{{ $equipo->id }}">
                            {{-- Dirigentes Tab --}}
                            <div class="tab-pane fade show active" id="dirigentes-{{ $equipo->id }}" role="tabpanel">
                                @if ($equipo->dirigentes->isEmpty())
                                    <p class="text-muted">No hay dirigentes registrados</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Cédula</th>
                                                    <th>Teléfono</th>
                                                    <th>Teléfono 2</th>
                                                    <th>Barrio</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($equipo->dirigentes as $dirigente)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $dirigente->nombre }}</td>
                                                        <td>{{ number_format($dirigente->cedula, 0, ',', '.') }}</td>
                                                        <td>{{ $dirigente->telefono }}</td>
                                                        <td>{{ $dirigente->telefono2 ?? '-' }}</td>
                                                        <td>{{ $dirigente->barrio ?? '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Punteros Tab --}}
                            <div class="tab-pane fade" id="punteros-{{ $equipo->id }}" role="tabpanel">
                                @if ($equipo->punteros->isEmpty())
                                    <p class="text-muted">No hay punteros registrados</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Cédula</th>
                                                    <th>Teléfono</th>
                                                    <th>Barrio</th>
                                                    <th>Dirigente</th>
                                                    <th>Votantes</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($equipo->punteros as $puntero)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $puntero->nombre }}</td>
                                                        <td>{{ number_format($puntero->cedula, 0, ',', '.') }}</td>
                                                        <td>{{ $puntero->telefono }}</td>
                                                        <td>{{ $puntero->barrio ?? '-' }}</td>
                                                        <td>{{ $puntero->dirigente->nombre ?? 'Sin asignar' }}</td>
                                                        <td><span class="badge badge-primary">{{ $puntero->votantes->count() }}</span></td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Votantes Tab --}}
                            <div class="tab-pane fade" id="votantes-{{ $equipo->id }}" role="tabpanel">
                                @if ($equipo->votantes->isEmpty())
                                    <p class="text-muted">No hay votantes registrados</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Nombre</th>
                                                    <th>Cédula</th>
                                                    <th>Mesa</th>
                                                    <th>Orden</th>
                                                    <th>Voto</th>
                                                    <th>Puntero</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($equipo->votantes as $votante)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $votante->nombre }}</td>
                                                        <td>{{ number_format($votante->cedula, 0, ',', '.') }}</td>
                                                        <td>{{ $votante->mesa }}</td>
                                                        <td>{{ $votante->orden }}</td>
                                                        <td>
                                                            @if($votante->voto == 1)
                                                                <span class="badge badge-success">✔️ Votó</span>
                                                            @else
                                                                <span class="badge badge-danger">❌ No votó</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $votante->puntero->nombre ?? 'Sin puntero' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- Vehículos Tab --}}
                            <div class="tab-pane fade" id="vehiculos-{{ $equipo->id }}" role="tabpanel">
                                @if ($equipo->vehiculos->isEmpty())
                                    <p class="text-muted">No hay vehículos registrados</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm table-bordered">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>#</th>
                                                    <th>Chapa</th>
                                                    <th>Chofer</th>
                                                    <th>Tipo</th>
                                                    <th>Capacidad</th>
                                                    <th>Teléfonos</th>
                                                    <th>Punteros</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($equipo->vehiculos as $vehiculo)
                                                    <tr>
                                                        <td>{{ $loop->iteration }}</td>
                                                        <td>{{ $vehiculo->chapa }}</td>
                                                        <td>{{ $vehiculo->nombre }}</td>
                                                        <td>{{ $vehiculo->tipovehiculo }}</td>
                                                        <td>{{ $vehiculo->capacidad }}</td>
                                                        <td>
                                                            {{ $vehiculo->telefono1 }}
                                                            @if($vehiculo->telefono2) - {{ $vehiculo->telefono2 }} @endif
                                                            @if($vehiculo->telefono3) - {{ $vehiculo->telefono3 }} @endif
                                                        </td>
                                                        <td>
                                                            @if($vehiculo->punteros->isEmpty())
                                                                <span class="text-muted">Sin punteros</span>
                                                            @else
                                                                @foreach($vehiculo->punteros as $p)
                                                                    • {{ $p->nombre }}<br>
                                                                @endforeach
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                        <button type="button" class="btn btn-primary" onclick="window.print()">
                            <i class="fas fa-print"></i> Imprimir
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

@stop

@push('js')
<script>
    $(document).ready(function () {

        let table = $('#equipos-table').DataTable({
            dom:
                "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",

            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Reporte por Local de Votación',
                    filename: 'reporte_locales_{{ date("Y-m-d_H-i-s") }}'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger',
                    title: 'Reporte por Local de Votación',
                    orientation: 'landscape',
                    pageSize: 'A4'
                },
                {
                    extend: 'print',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    className: 'btn btn-secondary'
                }
            ],

            responsive: true,
            
            pageLength: 10,
            
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            },

            order: [[1, 'asc']]
        });

    });
</script>
@endpush