@extends('adminlte::page')

@section('title', 'Reporte de Vehículos')

@section('content_header')
    <h4 class="mb-2">
        <i class="fas fa-truck"></i> Planilla de Vehículos y Punteros
    </h4>
@stop

@section('content')

    {{-- 🔹 Totales --}}
    <div class="row mb-3">
        <div class="col-md-4">
            <div class="small-box bg-info">
                <div class="inner">
                    <h4>{{ $totalVehiculos }}</h4>
                    <p>Total Vehículos</p>
                </div>
                <div class="icon"><i class="fas fa-car"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-success">
                <div class="inner">
                    <h4>{{ number_format($totalMonto, 0, ',', '.') }}</h4>
                    <p>Total Monto</p>
                </div>
                <div class="icon"><i class="fas fa-money-bill-wave"></i></div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="small-box bg-warning">
                <div class="inner">
                    <h4>{{ $totalPagos }}</h4>
                    <p>Total Pagos</p>
                </div>
                <div class="icon"><i class="fas fa-list-ol"></i></div>
            </div>
        </div>
    </div>

    {{-- 🔹 Tabla --}}
    <div class="card">
        <div class="card-body">

            <table id="vehiculos-table" class="table table-bordered table-striped">
                <thead class="thead-dark">
                    <tr>
                        <th>#</th>
                        <th>Chofer</th>
                        <th>Cédula</th>
                        <th>Chapa</th>
                        <th>Tipo</th>
                        <th>Cap.</th>
                        <th>Teléfonos</th>
                        <th>Monto</th>
                        <th>Pagos</th>
                        <th>Equipo</th>
                        <th style="min-width:220px">Punteros</th>
                    </tr>
                </thead>

                <tbody>
                    @foreach ($vehiculos as $vehiculo)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $vehiculo->nombre }}</td>
                            <td>{{ number_format($vehiculo->cedulachofer, 0, ',', '.') }}</td>
                            <td>{{ $vehiculo->chapa }}</td>
                            <td>{{ $vehiculo->tipovehiculo }}</td>
                            <td class="text-center">{{ $vehiculo->capacidad }}</td>

                            <td>
                                @php
                                    $telefonos = collect([
                                        $vehiculo->telefono1,
                                        $vehiculo->telefono2,
                                        $vehiculo->telefono3
                                    ])->filter();
                                @endphp
                                {{ $telefonos->implode(' - ') }}
                            </td>

                            <td class="text-right">
                                {{ number_format($vehiculo->montopagar, 0, ',', '.') }}
                            </td>

                            <td class="text-center">
                                {{ $vehiculo->cantidadpagos }}
                            </td>

                            <td>
                                <span class="badge badge-primary">
                                    {{ $vehiculo->equipo->descripcion ?? '' }}
                                </span>
                            </td>

                            <td>
                                @if ($vehiculo->punteros->isEmpty())
                                    <span class="text-muted">Sin punteros</span>
                                @else
                                    @foreach ($vehiculo->punteros as $p)
                                        • {{ $p->nombre }}<br>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>

                <tfoot>
                    <tr class="font-weight-bold bg-light">
                        <td colspan="7" class="text-right">TOTALES</td>
                        <td class="text-right">
                            {{ number_format($totalMonto, 0, ',', '.') }}
                        </td>
                        <td class="text-center">{{ $totalPagos }}</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>

            </table>

        </div>
    </div>

@stop

@push('js')
<script>
    $(document).ready(function () {

        let table = $('#vehiculos-table').DataTable({
            dom:
                "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",

            buttons: [
                {
                    extend: 'excelHtml5',
                    text: '<i class="fas fa-file-excel"></i> Excel',
                    className: 'btn btn-success',
                    title: 'Vehículos por Sistema',
                    filename: 'vehiculos_sistema_{{ date("Y-m-d_H-i-s") }}'
                },
                {
                    extend: 'pdfHtml5',
                    text: '<i class="fas fa-file-pdf"></i> PDF',
                    className: 'btn btn-danger',
                    title: 'Vehículos por Sistema',
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

            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

    });
</script>
@endpush
