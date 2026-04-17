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

{{-- Segunda fila de tarjetas --}}
<div class="row mb-4">
    <div class="col-md-6">
        <div class="small-box bg-primary">
            <div class="inner">
                <h4>{{ $totalVotos ?? 0 }}</h4>
                <p>Votantes que Votaron en la última elección</p>
            </div>
            <div class="icon"><i class="fas fa-vote-yea"></i></div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h4>{{ $totalSinVoto ?? 0 }}</h4>
                <p>Votantes que NO Votaron en la última elección</p>
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
                        <td>{{ $equipo->nombre }}</td>
                        <td>{{ $equipo->colegio ?? 'N/A' }}</td>
                        <td>{{ $equipo->ciudad ?? 'N/A' }}</td>
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
                        <td class="text-center font-weight-bold">{{ $equipo->total_dirigentes }}</td>
                        <td class="text-center font-weight-bold">{{ $equipo->total_punteros }}</td>
                        <td class="text-center font-weight-bold">{{ $equipo->total_votantes }}</td>
                        <td class="text-center font-weight-bold">{{ $equipo->total_vehiculos }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-info btn-ver-detalle" 
                                    data-id="{{ $equipo->id }}"
                                    data-nombre="{{ $equipo->nombre }}">
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

{{-- Modal Único (Dinámico) --}}
<div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-info-circle"></i> Detalle del Local: <span id="modalLocalNombre"></span>
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Cargando detalles...</p>
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