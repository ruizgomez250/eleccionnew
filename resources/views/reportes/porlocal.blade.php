@extends('adminlte::page')

@section('title', 'Reporte por Escuela')

@section('content_header')
    <div class="ua-header">
        <div>
            <h1 class="ua-title"><i class="fas fa-school"></i> Reporte por Escuela</h1>
            <p class="ua-subtitle">Votantes por escuela y su participación en la última elección</p>
        </div>
    </div>
@stop

@section('content')

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

    <div class="card ua-card">
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
                                <button type="button" class="btn btn-sm btn-info" data-toggle="modal" data-target="#modalEscuela{{ Str::slug($escuela->escuela) }}">
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

    @foreach ($escuelas as $escuela)
        @php $modalId = 'modalEscuela' . Str::slug($escuela->escuela); @endphp
        <div class="modal fade" id="{{ $modalId }}" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-xl" role="document">
                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle"></i> Detalle: {{ $escuela->escuela }}
                        </h5>
                        <button type="button" class="close text-white" data-dismiss="modal">
                            <span>&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
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
                                    @forelse ($escuela->votantes as $votante)
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
                                    @empty
                                        <tr>
                                            <td colspan="7" class="text-center text-muted">No hay votantes registrados</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
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

@section('css')
    @include('useradmin._dark_pages')
@stop

@push('js')
<script>
    $(document).ready(function () {

        let table = $('#escuelas-table').DataTable({
            dom:
                "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",

            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Reporte por Escuela',
                    filename: 'reporte_escuelas_{{ date("Y-m-d_H-i-s") }}'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger',
                    title: 'Reporte por Escuela',
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