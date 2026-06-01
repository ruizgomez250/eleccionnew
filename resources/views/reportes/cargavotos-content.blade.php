<div class="row mb-3">
    <div class="col-md-12">
        <div class="info-box bg-info">
            <span class="info-box-icon"><i class="fas fa-chart-bar"></i></span>
            <div class="info-box-content">
                <span class="info-box-text">Total General de Votos Cargados</span>
                <span class="info-box-number" style="font-size: 2rem;">{{ number_format($totalGeneral, 0, ',', '.') }}</span>
            </div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header">
        <h5 class="card-title"><i class="fas fa-list"></i> Desglose por Puntero</h5>
    </div>
    <div class="card-body table-responsive p-0">
        <table id="tabla-punteros" class="table table-bordered table-striped table-hover" width="100%">
            <thead>
                <tr>
                    <th>Dirigente</th>
                    <th>Puntero</th>
                    <th>Total Votantes</th>
                    <th>Votaron</th>
                    <th>No Votaron</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $sumaTotal = 0;
                    $sumaVotaron = 0;
                    $sumaNoVotaron = 0;
                @endphp
                @foreach($punters as $p)
                    @php
                        $noVotaron = $p->total_votantes - $p->votaron;
                        $sumaTotal += $p->total_votantes;
                        $sumaVotaron += $p->votaron;
                        $sumaNoVotaron += $noVotaron;
                    @endphp
                    <tr>
                        <td>{{ $p->dirigente_nombre }}</td>
                        <td>{{ $p->puntero_nombre }}</td>
                        <td class="text-center">{{ number_format($p->total_votantes, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($p->votaron > 0)
                                <a href="#" class="btn-detalle" data-puntero="{{ $p->puntero_id }}" data-tipo="votaron" data-nombre="{{ $p->puntero_nombre }}">
                                    <span class="badge badge-success" style="font-size: 1rem; cursor: pointer;">
                                        {{ number_format($p->votaron, 0, ',', '.') }}
                                    </span>
                                </a>
                            @else
                                <span class="badge badge-secondary">0</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($noVotaron > 0)
                                <a href="#" class="btn-detalle" data-puntero="{{ $p->puntero_id }}" data-tipo="no_votaron" data-nombre="{{ $p->puntero_nombre }}">
                                    <span class="badge badge-danger" style="font-size: 1rem; cursor: pointer;">
                                        {{ number_format($noVotaron, 0, ',', '.') }}
                                    </span>
                                </a>
                            @else
                                <span class="badge badge-secondary">0</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="font-weight-bold" style="background: #e9ecef;">
                    <td colspan="2" class="text-right">TOTALES</td>
                    <td class="text-center">{{ number_format($sumaTotal, 0, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge badge-success" style="font-size: 1.1rem;">
                            {{ number_format($sumaVotaron, 0, ',', '.') }}
                        </span>
                    </td>
                    <td class="text-center">
                        <span class="badge badge-danger" style="font-size: 1.1rem;">
                            {{ number_format($sumaNoVotaron, 0, ',', '.') }}
                        </span>
                    </td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<div class="modal fade" id="detalleModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalTitulo">Detalle</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body" id="modalBodyContent">
                <div class="text-center">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
