@extends('adminlte::page')

@section('title', 'Miembros de Mesa')

@section('content_header')
    <div class="row mb-2">
        <div class="col-md-4">
            <label class="form-label fw-bold">Equipos</label>

            <x-adminlte-select2 name="equipo_id" id="equipo_id" onchange="filtrarMiembros()" enable-old-support>

                <option value="">Todos</option>

                @foreach ($equipos as $eq)
                    <option value="{{ $eq->id }}" {{ (string) $equipoId === (string) $eq->id ? 'selected' : '' }}>
                        {{ $eq->descripcion }}
                    </option>
                @endforeach

            </x-adminlte-select2>
        </div>
    </div>
@stop

@section('content')

    <div class="card">
        <div class="card-body">

            <table id="miembros-table" class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Función</th>
                        <th>Equipo</th>
                        <th width="10%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($miembros as $miembro)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $miembro->cedula }}</td>
                            <td>{{ $miembro->nombre }}</td>
                            <td>{{ $miembro->telefono }}</td>
                            <td>{{ $miembro->funcion }}</td>
                            <td>{{ $miembro->equipo->descripcion ?? '' }}</td>
                            <td>
                                <button class="btn btn-danger btn-sm" onclick="confirmarBorrado(this)"
                                    data-url="{{ route('miembros-de-mesa.destroy', $miembro->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>

    <form id="formEliminar" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>

    {{-- MODAL AGREGAR --}}
    <div class="modal fade" id="modalAgregar" tabindex="-1">
        <div class="modal-dialog">
            <form id="formAgregarMiembro" action="{{ route('miembros-de-mesa.store') }}" method="POST">
                @csrf
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Miembro de Mesa</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">

                        <input type="hidden" name="idequipo" value="{{ $equipoId }}">

                        <div class="form-group">
                            <label>Cédula</label>
                            <input type="text" name="cedula" id="miembro_cedula" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="miembro_nombre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="miembro_telefono" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Función</label>
                            <select name="funcion" class="form-control" required>
                                <option value="Titular">Titular</option>
                                <option value="Suplente">Suplente</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">
                            Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            Cerrar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@stop

@push('js')
    <script>
        let bloqueaFiltro = false;
        const successAlert = @json(session('successAlert'));
        const errorAlert = @json(session('errorAlert'));

        if (errorAlert) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorAlert,
                confirmButtonColor: '#dc3545'
            });
        }

        function buscarMiembroPorCedula() {
            let cedula = $('#miembro_cedula').val().trim();

            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
                if (response.encontrado) {

                    $('#miembro_nombre').val(response.data.nombre);
                    $('#miembro_telefono').val(response.data.telefono);

                    // efecto visual opcional
                    $('#miembro_nombre').addClass('border-success');
                    $('#miembro_telefono').addClass('border-success');

                    setTimeout(() => {
                        $('#miembro_nombre').removeClass('border-success');
                        $('#miembro_telefono').removeClass('border-success');
                    }, 1500);
                }
            });
        }

        $('#miembro_cedula').on('blur', function() {
            buscarMiembroPorCedula();
        });

        $('#miembro_cedula').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarMiembroPorCedula();
                $('#miembro_nombre').focus();
            }
        });

        function confirmarBorrado(button) {
            const url = button.getAttribute('data-url');

            Swal.fire({
                title: '¿Eliminar miembro?',
                text: 'Esta acción no se puede deshacer',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonText: 'Cancelar',
                confirmButtonText: 'Sí, eliminar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('formEliminar');
                    form.action = url;
                    form.submit();
                }
            });
        }

        function filtrarMiembros() {
            if (bloqueaFiltro) return;

            let equipoId = $('#equipo_id').val();
            let url = "{{ url('miembros-de-mesa/create') }}/" + equipoId;
            window.location.href = url;
        }

        $(document).ready(function() {

            $('#equipo_id').select2({
                width: '100%'
            });

            $('#miembros-table').DataTable({
                dom: "<'row'<'col-md-6'f><'col-md-6 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",

                buttons: [{
                        text: '<i class="fas fa-user-plus"></i> Agregar Miembro',
                        className: 'btn btn-primary',
                        action: function() {
                            let equipoSeleccionado = $('#equipo_id').val();
                            if (!equipoSeleccionado) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Debe seleccionar un equipo',
                                    text: 'Seleccione un equipo antes de agregar un miembro',
                                    confirmButtonColor: '#3085d6',
                                });
                                return;
                            }

                            $('#modalAgregar input[name="idequipo"]').val(equipoSeleccionado);
                            $('#formAgregarMiembro')[0].reset();
                            $('#modalAgregar').modal('show');
                            $('#miembro_cedula').trigger('focus');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-info'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary'
                    }
                ],

                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
            @if (session()->has('abrirModalMiembro'))

                bloqueaFiltro = true;

                $('#formAgregarMiembro')[0].reset();

                @if (session()->has('equipoId'))
                    $('input[name="idequipo"]').val('{{ session('equipoId') }}');
                    $('#equipo_id').val('{{ session('equipoId') }}').trigger('change.select2');
                @endif

                $('#modalAgregar')
                    .off('shown.bs.modal')
                    .on('shown.bs.modal', function() {
                        setTimeout(function() {
                            $('#miembro_cedula').trigger('focus');
                            bloqueaFiltro = false;
                        }, 200);
                    })
                    .modal('show');
            @endif

        });
    </script>
@endpush
