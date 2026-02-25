@extends('adminlte::page')

@section('title', 'Configuración de Montos')

@section('content_header')
    <h1>
        Configuración de Montos
        <button class="btn btn-info btn-sm float-right mr-2" onclick="abrirModalReporte()">
            <i class="fas fa-file-excel"></i> Presupuesto General
        </button>
    </h1>
@stop

@section('content')
    <div class="row">
        <div class="col-md-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Montos por Concepto</h3>
                </div>
                <div class="card-body">
                    <table class="table table-bordered table-striped" id="montos-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Concepto</th>
                                <th>Monto</th>
                                <th>Total</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($montos as $monto)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $monto->concepto }}</td>
                                    <td>{{ number_format($monto->monto, 2) }}</td>
                                    <td>
                                        @php
                                            $total = 0;
                                            $sistemaUsuario = Auth::user()->sistema;
                                            if ($monto->concepto === 'Punteros') {
                                                $total = \App\Models\Puntero::whereHas(
                                                    'equipo',
                                                    fn($q) => $q->where('sist', $sistemaUsuario),
                                                )->count();
                                            } elseif ($monto->concepto === 'Vehiculos') {
                                                $total = \App\Models\Vehiculo::whereHas(
                                                    'equipo',
                                                    fn($q) => $q->where('sist', $sistemaUsuario),
                                                )->count();
                                            } elseif ($monto->concepto === 'Miembros de Mesa') {
                                                $total = \App\Models\MiembroDeMesa::whereHas(
                                                    'equipo',
                                                    fn($q) => $q->where('sist', $sistemaUsuario),
                                                )->count();
                                            }
                                        @endphp
                                        {{ $total }}
                                    </td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                            onclick="editarMonto({{ $monto->id }}, {{ $monto->monto }})">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal para editar monto --}}
    <div class="modal fade" id="modalMonto" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('configuracion_montos.store') }}" method="POST" id="formMonto">
                @csrf
                <input type="hidden" name="monto_id" id="monto_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Editar Monto</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label>Monto</label>
                            <input type="number" step="0.01" min="0" name="monto" id="monto"
                                class="form-control" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal Reporte --}}
    <div class="modal fade" id="modalReporte" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Reporte General de Sistemas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <table class="table table-bordered table-striped" id="reporte-table">
                        <thead>
                            <tr>
                                <th>Sistema</th>
                                <th>Concepto</th>
                                <th>Cantidad</th>
                                <th>Total Presupuestado</th>
                            </tr>
                        </thead>
                        <tbody>
                            {{-- Aquí puedes cargar vía AJAX los totales por sistema si querés --}}
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@stop

@push('js')
    <script>
        $(document).ready(function() {
            $('#montos-table').DataTable({
                responsive: true,
                paging: false,
                info: false,
                searching: false,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            const successAlert = @json(session('successAlert'));
            if (successAlert) {
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: successAlert,
                    confirmButtonColor: '#28a745'
                });
            }
        });

        function editarMonto(id, monto) {
            $('#monto_id').val(id);
            $('#monto').val(monto);
            $('#modalMonto .modal-title').text('Editar Monto');
            $('#modalMonto').modal('show');
        }

        function abrirModalReporte() {
            $('#modalReporte').modal('show');

            $.get("{{ route('configuracion_montos.reporte') }}", function(data) {
                let tbody = $('#reporte-table tbody');
                tbody.empty();

                data.forEach(item => {
                    tbody.append(`
                <tr>
                    <td>${item.sistema}</td>
                    <td>${item.concepto}</td>
                    <td>${item.cantidad}</td>
                    <td>${item.total_presupuestado.toLocaleString('es-PY', {style:'currency', currency:'PYG'})}</td>
                </tr>
            `);
                });

                if (!$.fn.DataTable.isDataTable('#reporte-table')) {
                    $('#reporte-table').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Exportar a Excel',
                            className: 'btn btn-success btn-sm'
                        }],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        }
                    });
                }
            });
        }
    </script>
@endpush
