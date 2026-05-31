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
                        <th>Mesa</th>
                        <th>Equipo</th>
                        <th>Cédula Proponente</th>
                        <th>Proponente</th>
                        <th>Tel. Proponente</th>
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
                            <td>
                                @if ($miembro->funcion == 'Titular')
                                    <span class="badge badge-success">{{ $miembro->funcion }}</span>
                                @else
                                    <span class="badge badge-warning">{{ $miembro->funcion }}</span>
                                @endif
                            </td>
                            <td>
                                @if ($miembro->mesa)
                                    <span class="badge badge-success">
                                        {{ $miembro->mesa }}
                                    </span>
                                @else
                                    <span class="badge badge-danger">
                                        Sin Asignar
                                    </span>
                                @endif
                            </td>
                            <td>{{ $miembro->equipo->descripcion ?? '' }}</td>
                            <td>{{ $miembro->cedulaproponente ?? '-' }}</td>
                            <td>{{ $miembro->nombreproponente ?? '-' }}</td>
                            <td>{{ $miembro->telefonoproponente ?? '-' }}</td>
                            <td>
                                <button class="btn btn-info btn-sm"
                                    onclick="copiarRutaVotos('{{ base64_encode($miembro->cedula) }}')"
                                    title="Copiar ruta para cargar votos">
                                    <i class="fas fa-link"></i> 
                                </button>
                                <button class="btn btn-primary btn-sm" onclick="editarMiembro({{ $miembro->id }})"
                                    title="Editar">
                                    <i class="fas fa-edit"></i>
                                </button>
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

    {{-- MODAL AGREGAR/EDITAR --}}
    <div class="modal fade" id="modalMiembro" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form id="formMiembro" action="{{ route('miembros-de-mesa.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="methodField" value="POST">
                <input type="hidden" name="id" id="miembroId">

                <div class="modal-content">
                    <div class="modal-header bg-primary text-white">
                        <h5 class="modal-title" id="modalTitle">Agregar Miembro de Mesa</h5>
                        <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
                    </div>

                    <div class="modal-body">
                        <input type="hidden" name="idequipo" id="idequipo" value="{{ $equipoId }}">

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cedula">Cédula <span class="text-danger">*</span></label>
                                    <input type="text" name="cedula" id="cedula" class="form-control" required>
                                    <small class="text-muted">Ingrese la cédula y automáticamente se buscarán los
                                        datos</small>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="text" name="telefono" id="telefono" class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="nombre">Nombre <span class="text-danger">*</span></label>
                                    <input type="text" name="nombre" id="nombre" class="form-control" required>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="funcion">Función <span class="text-danger">*</span></label>
                                    <select name="funcion" id="funcion" class="form-control" required>
                                        <option value="Titular">Titular</option>
                                        <option value="Suplente">Suplente</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label for="mesa">Mesa</label>

                                    <select name="mesa" id="mesa" class="form-control">

                                        <option value="">Sin asignar</option>

                                        @if ($localInterna)

                                            @for ($i = 1; $i <= $localInterna->cantmesa; $i++)
                                                <option value="{{ $i }}">
                                                    Mesa {{ $i }}
                                                </option>
                                            @endfor

                                        @endif

                                    </select>
                                </div>
                            </div>
                        </div>

                        <hr class="my-3">

                        <div class="row">
                            <div class="col-12">
                                <h6 class="text-info">
                                    <i class="fas fa-user-friends"></i> Datos del Proponente (Opcional)
                                </h6>
                                <small class="text-muted">Complete estos campos si el miembro tiene un proponente
                                    asociado</small>
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="cedulaproponente">Cédula del Proponente</label>
                                    <input type="text" name="cedulaproponente" id="cedulaproponente"
                                        class="form-control">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="telefonoproponente">Teléfono del Proponente</label>
                                    <input type="text" name="telefonoproponente" id="telefonoproponente"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-12">
                                <div class="form-group">
                                    <label for="nombreproponente">Nombre del Proponente</label>
                                    <input type="text" name="nombreproponente" id="nombreproponente"
                                        class="form-control">
                                </div>
                            </div>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary" id="btnGuardar">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">
                            <i class="fas fa-times"></i> Cerrar
                        </button>
                    </div>

                </div>
            </form>
        </div>
    </div>

@stop

@push('css')
    <style>
        .border-success {
            border-color: #28a745 !important;
            box-shadow: 0 0 0 0.2rem rgba(40, 167, 69, 0.25);
        }

        .modal-lg {
            max-width: 800px;
        }

        .badge {
            padding: 5px 10px;
            font-size: 12px;
        }

        .bg-primary {
            background-color: #007bff !important;
        }

        hr {
            border-top: 2px solid #e9ecef;
        }
    </style>
@endpush

@push('js')
    <script>
        function copiarRutaVotos(cedulaCodificada) {
            // Obtener la URL base del sitio
            const urlBase = '{{ asset("") }}';
            const rutaActual = window.location.pathname;

            // Construir la URL completa
            const urlCompleta = urlBase + 'cargarvotos/' + cedulaCodificada;

            // Copiar al portapapeles
            navigator.clipboard.writeText(urlCompleta).then(function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Ruta copiada!',
                    text: 'URL: ' + urlCompleta,
                    timer: 2000,
                    showConfirmButton: false
                });
            });
        }
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

        if (successAlert) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: successAlert,
                confirmButtonColor: '#28a745',
                timer: 3000
            });
        }

        function buscarMiembroPorCedula() {
            let cedula = $('#cedula').val().trim();

            if (cedula.length < 3) return;

            // Mostrar loading
            $('#cedula').addClass('border-info');

            $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#nombre').val(response.data.nombre);
                    $('#telefono').val(response.data.telefono);

                    // efecto visual opcional
                    $('#nombre').addClass('border-success');
                    $('#telefono').addClass('border-success');

                    setTimeout(() => {
                        $('#nombre').removeClass('border-success');
                        $('#telefono').removeClass('border-success');
                        $('#cedula').removeClass('border-info');
                    }, 1500);

                    // Mostrar notificación de éxito
                    toastr.success('Datos del dirigente cargados correctamente');
                } else {
                    $('#nombre').val('');
                    $('#telefono').val('');
                    toastr.warning('No se encontró un dirigente con esa cédula');
                    $('#cedula').removeClass('border-info');
                }
            }).fail(function() {
                toastr.error('Error al buscar la cédula');
                $('#cedula').removeClass('border-info');
            });
        }

        function buscarProponentePorCedula() {
            let cedula = $('#cedulaproponente').val().trim();

            if (cedula.length < 3) return;

            // Mostrar loading
            $('#cedulaproponente').addClass('border-info');

            $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#nombreproponente').val(response.data.nombre);
                    $('#telefonoproponente').val(response.data.telefono);

                    // efecto visual opcional
                    $('#nombreproponente').addClass('border-success');
                    $('#telefonoproponente').addClass('border-success');

                    setTimeout(() => {
                        $('#nombreproponente').removeClass('border-success');
                        $('#telefonoproponente').removeClass('border-success');
                        $('#cedulaproponente').removeClass('border-info');
                    }, 1500);

                    toastr.success('Datos del proponente cargados correctamente');
                } else {
                    $('#nombreproponente').val('');
                    $('#telefonoproponente').val('');
                    toastr.warning('No se encontró un dirigente con esa cédula');
                    $('#cedulaproponente').removeClass('border-info');
                }
            }).fail(function() {
                toastr.error('Error al buscar la cédula');
                $('#cedulaproponente').removeClass('border-info');
            });
        }

        // Eventos para búsqueda automática
        $('#cedula').on('blur', function() {
            buscarMiembroPorCedula();
        });

        $('#cedula').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarMiembroPorCedula();
                $('#nombre').focus();
            }
        });

        $('#cedulaproponente').on('blur', function() {
            buscarProponentePorCedula();
        });

        $('#cedulaproponente').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarProponentePorCedula();
                $('#nombreproponente').focus();
            }
        });

        function cargarMesas(equipoId, selectedMesa) {
            $.get("{{ url('miembros-de-mesa/cantmesa') }}/" + equipoId, function(res) {
                let mesaSelect = $('#mesa');
                mesaSelect.empty();
                mesaSelect.append(`<option value="">Sin asignar</option>`);
                for (let i = 1; i <= res.cantmesa; i++) {
                    let selected = (selectedMesa == i) ? 'selected' : '';
                    mesaSelect.append(`<option value="${i}" ${selected}>Mesa ${i}</option>`);
                }
            }).fail(function() {
                toastr.error('Error al cargar las mesas del equipo');
            });
        }

        function editarMiembro(id) {
            $.get("{{ url('miembros-de-mesa') }}/" + id, function(res) {

                let miembro = res.miembro;
                let cantmesa = res.cantmesa;

                $('#modalTitle').text('Editar Miembro de Mesa');
                $('#formMiembro').attr('action', "{{ url('miembros-de-mesa') }}/" + id);
                $('#methodField').val('PUT');

                $('#miembroId').val(miembro.id);
                $('#idequipo').val(miembro.idequipo);
                $('#cedula').val(miembro.cedula);
                $('#nombre').val(miembro.nombre);
                $('#telefono').val(miembro.telefono);
                $('#funcion').val(miembro.funcion);
                $('#cedulaproponente').val(miembro.cedulaproponente || '');
                $('#nombreproponente').val(miembro.nombreproponente || '');
                $('#telefonoproponente').val(miembro.telefonoproponente || '');

                /*
                |--------------------------------------------------------------------------
                | RECONSTRUIR SELECT DE MESAS
                |--------------------------------------------------------------------------
                */
                let mesaSelect = $('#mesa');
                mesaSelect.empty();

                mesaSelect.append(`<option value="">Sin asignar</option>`);

                for (let i = 1; i <= cantmesa; i++) {

                    let selected = (miembro.mesa == i) ? 'selected' : '';

                    mesaSelect.append(`
                <option value="${i}" ${selected}>
                    Mesa ${i}
                </option>
            `);
                }

                $('#modalMiembro').modal('show');

            }).fail(function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar los datos del miembro'
                });
            });
        }

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

        // Limpiar modal al cerrar
        $('#modalMiembro').on('hidden.bs.modal', function() {
            $('#formMiembro')[0].reset();
            $('#modalTitle').text('Agregar Miembro de Mesa');
            $('#formMiembro').attr('action', "{{ route('miembros-de-mesa.store') }}");
            $('#methodField').val('POST');
            $('#miembroId').val('');
            $('.border-success').removeClass('border-success');
            $('.border-info').removeClass('border-info');
        });

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

                            $('#idequipo').val(equipoSeleccionado);
                            $('#formMiembro')[0].reset();
                            $('#modalTitle').text('Agregar Miembro de Mesa');
                            $('#formMiembro').attr('action',
                                "{{ route('miembros-de-mesa.store') }}");
                            $('#methodField').val('POST');
                            cargarMesas(equipoSeleccionado);
                            $('#modalMiembro').modal('show');
                            $('#cedula').trigger('focus');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-info',
                        text: '<i class="fas fa-file-excel"></i> Excel'
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger',
                        text: '<i class="fas fa-file-pdf"></i> PDF'
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary',
                        text: '<i class="fas fa-print"></i> Imprimir'
                    }
                ],

                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                order: [
                    [1, 'asc']
                ],
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Todos"]
                ]
            });

            @if (session()->has('abrirModalMiembro'))
                bloqueaFiltro = true;

                $('#formMiembro')[0].reset();

                @if (session()->has('equipoId'))
                    let equipoReabrir = '{{ session('equipoId') }}';
                    $('#idequipo').val(equipoReabrir);
                    $('#equipo_id').val(equipoReabrir).trigger('change.select2');
                    cargarMesas(equipoReabrir);
                @endif

                $('#modalMiembro')
                    .off('shown.bs.modal')
                    .on('shown.bs.modal', function() {
                        setTimeout(function() {
                            $('#cedula').trigger('focus');
                            bloqueaFiltro = false;
                        }, 200);
                    })
                    .modal('show');
            @endif

            // Configurar toastr
            toastr.options = {
                "closeButton": true,
                "progressBar": true,
                "positionClass": "toast-top-right",
                "timeOut": "3000"
            };

        });
    </script>
@endpush
