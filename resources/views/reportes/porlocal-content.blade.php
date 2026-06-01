{{-- 🔹 Totales Generales --}}
<div class="row mb-3">
    <div class="col-md-4">
        <div class="small-box bg-info">
            <div class="inner">
                <h4>{{ $totalEscuelas }}</h4>
                <p>Total Escuelas</p>
            </div>
            <div class="icon"><i class="fas fa-school"></i></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-danger">
            <div class="inner">
                <h4>{{ $totalVotantes }}</h4>
                <p>Total Votantes</p>
            </div>
            <div class="icon"><i class="fas fa-check-circle"></i></div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="small-box bg-primary">
            <div class="inner">
                <h4>{{ $totalVotos ?? 0 }}</h4>
                <p>Votaron en la última elección</p>
            </div>
            <div class="icon"><i class="fas fa-vote-yea"></i></div>
        </div>
    </div>
</div>

<div class="row mb-4">
    <div class="col-md-12">
        <div class="small-box bg-secondary">
            <div class="inner">
                <h4>{{ $totalSinVoto ?? 0 }}</h4>
                <p>Votantes que NO Votaron en la última elección</p>
            </div>
            <div class="icon"><i class="fas fa-ban"></i></div>
        </div>
    </div>
</div>

{{-- 🔹 Tabla de Escuelas --}}
<div class="card">
    <div class="card-header">
        <h5 class="mb-0">Detalle por Escuela</h5>
    </div>
    <div class="card-body">
        <table id="escuelas-table" class="table table-bordered table-striped">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Escuela</th>
                    <th>Total Votantes</th>
                    <th>Votaron</th>
                    <th>No Votaron</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($escuelas as $escuela)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $escuela->escuela }}</td>
                        <td class="text-center font-weight-bold">{{ $escuela->total_votantes }}</td>
                        <td class="text-center font-weight-bold text-success">{{ $escuela->votaron }}</td>
                        <td class="text-center font-weight-bold text-danger">{{ $escuela->no_votaron }}</td>
                        <td class="text-center">
                            <button type="button" class="btn btn-sm btn-info btn-ver-detalle" 
                                    data-escuela="{{ $escuela->escuela }}"
                                    data-nombre="{{ $escuela->escuela }}">
                                <i class="fas fa-eye"></i> Ver detalle
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-weight-bold bg-light">
                    <td colspan="2" class="text-right">TOTALES GENERALES:</td>
                    <td class="text-center">{{ $totalVotantes }}</td>
                    <td class="text-center text-success">{{ $totalVotos }}</td>
                    <td class="text-center text-danger">{{ $totalSinVoto }}</td>
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