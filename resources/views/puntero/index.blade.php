@extends('adminlte::page')

@section('content_header')
    <div class="row mb-2">

        {{-- FILTRO EQUIPO --}}
        <div class="col-md-4">
            <label for="equipo_id" class="form-label fw-bold">
                Equipos
            </label>

            <x-adminlte-select2 name="equipo_id" id="equipo_id" onchange="filtrar()">
                <option value="">Todos</option>

                @foreach ($equipos as $e)
                    <option value="{{ $e->id }}" @selected((isset($id_equipo) && $id_equipo == $e->id) || request('equipo_id') == $e->id)>
                        {{ $e->descripcion }}
                    </option>
                @endforeach
            </x-adminlte-select2>
        </div>



        {{-- FILTRO DIRIGENTE --}}
        <div class="col-md-4">
            <label for="dirigente_id" class="form-label fw-bold">
                Dirigentes
            </label>


            <x-adminlte-select2 name="dirigente_id" id="dirigente_id" onchange="filtrar()" disable-faster-look>
                <option value="">Todos</option>
                @foreach ($dirigentes as $d)
                    <option value="{{ $d->id }}" @if ($id_dirigente == $d->id) selected @endif>
                        {{ $d->nombre }}
                    </option>
                @endforeach
            </x-adminlte-select2>

        </div>


    </div>
@stop

@section('content')
    <h2>Total General de Votos: {{ $totalVotantesGeneral }}</h2>
    <div class="card">
        <div class="card-body">

            <table id="punteros-table" class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Teléfono</th>
                        <th>Barrio</th>
                        <th>Dirigente</th>
                        <th>Equipo</th>
                        <th>Votantes</th>
                        <th width="15%">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($punteros as $p)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $p->cedula }}</td>
                            <td>{{ $p->nombre }}</td>
                            <td>{{ $p->telefono }}</td>
                            <td>{{ $p->barrio }}</td>
                            <td>{{ $p->dirigente->nombre ?? '' }}</td>
                            <td>{{ $p->equipo->descripcion ?? '' }}</td>
                            <td class="text-center">
                                <span class="badge badge-success">{{ $p->votantes_count }}</span>
                            </td>
                            <td>
                                <button class="btn btn-success btn-sm"
                                    onclick="abrirModalVotantes({{ $p->id }}, '{{ $p->nombre }}')">
                                    <i class="fas fa-users"></i>
                                </button>
                                <button class="btn btn-danger btn-sm" onclick="confirmarBorrado(this)"
                                    data-url="{{ route('puntero.destroy', $p->id) }}">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

        </div>
    </div>
    <form id="formEliminarPuntero" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
    <!-- Modal punteros -->
    <div class="modal fade" id="modalAgregarPuntero" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('puntero.store') }}" method="POST" id="formAgregarPuntero">
                @csrf
                <input type="hidden" name="id_dirigente" id="id_dirigente" value="{{ $id_dirigente ?? '' }}">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Agregar Puntero</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body">
                        @if (session('successAlert') && session('abrirModalPuntero'))
                            <div id="alerta-votante" name="alerta-votante" class="alert alert-success">
                                {{ session('successAlert') }}
                            </div>
                        @endif

                        @if (session('errorAlert') && session('abrirModalPuntero'))
                            <div class="alert alert-danger">
                                {{ session('errorAlert') }}
                            </div>
                        @endif
                        <input type="hidden" name="id_equipo" value="{{ $id_equipo ?? '' }}">
                        <div class="form-group">
                            <label>Cédula</label>
                            <input type="text" name="cedula" id="cedula" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>

                        <div class="form-group">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control">
                        </div>

                        <div class="form-group">
                            <label>Barrio</label>
                            <input type="text" name="barrio" id="barrio" class="form-control">
                        </div>

                    </div>
                    <div class="modal-footer">

                        <button type="submit" class="btn btn-primary">Guardar Puntero</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>



                    </div>
                </div>
            </form>
        </div>
    </div>
    {{-- ================= MODAL VOTANTES ================= --}}
    <div class="modal fade" id="modalVotantes">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">

                <div class="modal-header">
                    <h5 class="modal-title">Agregar / Editar Votante</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                        <span aria-hidden="true">&times;</span>
                    </button>

                </div>

                <div class="modal-body">
                    @if (session('successAlert') && session('abrirModalVotante'))
                        <div class="alert alert-success">
                            {{ session('successAlert') }}
                        </div>
                    @endif

                    @if (session('errorAlert') && session('abrirModalVotante'))
                        <div class="alert alert-danger">
                            {{ session('errorAlert') }}
                        </div>
                    @endif

                    {{-- FORM VOTANTE --}}
                    <form id="formAgregarVotante" method="POST" action="{{ route('votante.store') }}">
                        @csrf
                        <input type="hidden" name="idpuntero" id="votante_id_puntero">
                        <input type="hidden" name="idusuario" value="{{ auth()->id() }}">

                        <div class="row mb-2">
                            <div class="col-md-3">
                                <input name="cedula" id="votante_cedula" class="form-control" placeholder="Cédula"
                                    required>
                            </div>
                            <div class="col-md-5">
                                <input name="nombre" id="votante_nombre" class="form-control" placeholder="Nombre"
                                    required readonly>
                            </div>
                            <div class="col-md-4">
                                <select name="tipo_votante" class="form-control" id="tipo_votante">
                                    <option value="seguro" selected>Seguro</option>
                                    <option value="dudoso">Dudoso</option>
                                    <option value="solo visita">Solo Visita</option>
                                </select>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="direccion" id="direccion" class="form-control" placeholder="Dirección" readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="mesa" id="mesa" class="form-control" placeholder="Mesa" readonly>
                            </div>
                            <div class="col-md-2">
                                <input name="orden" id="orden" class="form-control" placeholder="Orden" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="partido" id="partido" class="form-control" placeholder="Partido" readonly>
                            </div>
                        </div>

                        <div class="row mb-2">
                            <div class="col-md-4">
                                <input name="escuela" id="escuela" class="form-control" placeholder="Escuela" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="ciudad" id="ciudad" class="form-control" placeholder="Ciudad" readonly>
                            </div>
                            <div class="col-md-4">
                                <input name="departamento" id="departamento" class="form-control"
                                    placeholder="Departamento" readonly>
                            </div>
                        </div>

                        <button class="btn btn-primary mt-2">
                            <i class="fas fa-save"></i> Guardar Votante
                        </button>
                    </form>

                    <hr>

                    {{-- TABLA VOTANTES --}}
                    <table id="votantes-table" class="table table-bordered table-striped w-100">

                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Tipo</th>
                                <th>Mesa</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>

                </div>
            </div>
        </div>
    </div>

@stop

@push('js')
    <script>
        let bloqueaFiltro = false;

        $(document).ready(function() {

            let equipoNombre = "{{ $equipoSeleccionado->descripcion ?? 'Todos' }}";
            inicializarTablaPunteros(equipoNombre);

            /* BUSCAR POR CÉDULA */
            $('#cedula').on('blur', function() {
                buscarPorCedula();
            });

            $('#cedula').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedula();
                    $('#nombre').focus();
                }
            });
            $('#votante_cedula').on('blur', function() {
                buscarPorCedulaV();
            });

            $('#votante_cedula').on('keypress', function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    buscarPorCedulaV();
                    $('#tipo_votante').focus();
                }
            });

            @if (session()->has('abrirModalPuntero'))
                bloqueaFiltro = true;

                $('#modalAgregarPuntero').modal('show');
                $('#formAgregarPuntero')[0].reset();
                let equipoId = $('#equipo_id').val(); // 🔹 equipo seleccionado
                $('#id_equipo').val(equipoId);
                $('#telefono').focus();

                @if (session()->has('equipoId'))
                    $('input[name="id_equipo"]').val('{{ session('equipoId') }}');
                    $('#equipo_id').val('{{ session('equipoId') }}').trigger('change.select2');
                @endif

                setTimeout(() => bloqueaFiltro = false, 300);
            @else
                @if (session()->has('abrirModalVotante'))
                    bloqueaFiltro = true;
                    abrirModalVotantes(
                        '{{ session('punteroId') }}',
                        '{{ session('punteroNombre') }}'
                    );
                    // Limpiar formulario
                    $('#formAgregarVotante')[0].reset();
                    $('#votante_nombre').focus();

                    // Restaurar quipo sin disparar redirección
                    @if (session()->has('IdPuntero'))
                        $('input[name="IdPuntero"]').val('{{ session('IdPuntero') }}');
                    @endif
                @else
                    @if (session('successAlert') && !session('abrirModalVotante') && !session('abrirModalPuntero'))
                        Swal.fire({
                            icon: 'success',
                            title: 'Éxito',
                            text: "{{ session('successAlert') }}"
                        });
                    @endif
                @endif
            @endif
        });

        /* ===========================
           BUSCAR POR CÉDULA
        ============================ */
        function buscarPorCedula() {
            let cedula = $('#cedula').val().trim();
            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#nombre').val(response.data.nombre ?? '');
                    $('#telefono').val(response.data.telefono ?? '');
                    $('#barrio').val(response.data.direccion ?? '');
                }
            });
        }

        function buscarPorCedulaV() {
            let cedula = $('#votante_cedula').val().trim();

            if (cedula.length < 3) return;

            $.get("{{ url('votante/buscar-por-cedula') }}/" + cedula, function(response) {

                if (!response.encontrado) {
                    // Opcional: limpiar campos si no encuentra
                    $('#votante_nombre').val('');
                    $('#direccion').val('');
                    $('#mesa').val('');
                    $('#orden').val('');
                    $('#partido').val('');
                    $('#escuela').val('');
                    $('#ciudad').val('');
                    $('#departamento').val('');
                    return;
                }

                let v = response.data;

                $('#votante_nombre').val(v.nombre);
                $('#direccion').val(v.direccion);
                $('#mesa').val(v.mesa);
                $('#orden').val(v.orden);
                $('#partido').val(v.partido);
                $('#escuela').val(v.escuela);
                $('#ciudad').val(v.ciudad);
                $('#departamento').val(v.departamento);
            });
        }


        /* ===========================
           DATATABLE PUNTEROS
        ============================ */
        function inicializarTablaPunteros(equipoNombre) {
            $('#punteros-table').DataTable({
                destroy: true,
                responsive: true,
                dom: "<'row'<'col-md-4'l><'col-md-4 text-center'f><'col-md-4 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [{
                        text: '<i class="fas fa-user-plus"></i> Agregar Puntero',
                        className: 'btn btn-primary',
                        action: function() {
                            $('#modalAgregarPuntero').modal('show');
                        }
                    },
                    {
                        extend: 'excelHtml5',
                        className: 'btn btn-info',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        title: 'Lista de Punteros del equipo ' + equipoNombre,
                        filename: 'punteros_' + equipoNombre.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        title: 'Lista de Punteros del equipo ' + equipoNombre,
                        filename: 'punteros_' + equipoNombre.replace(/\s+/g, '_') +
                            '_{{ date('Y-m-d_H-i-s') }}',
                        exportOptions: {
                            columns: ':visible'
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary',
                        text: '<i class="fas fa-print"></i> Imprimir'
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });

            $('#equipo_id, #puntero_id').select2({
                width: '100%'
            });
        }

        /* ===========================
           FILTRO
        ============================ */
        function filtrar() {
            if (bloqueaFiltro) return;

            let equipoId = $('#equipo_id').val();
            let punteroId = $('#dirigente_id').val();

            let url = "{{ url('puntero/create') }}";

            if (equipoId) {
                url += '/' + equipoId;
            }

            if (punteroId) {
                url += (url.includes('?') ? '&' : '?') + 'dirigente_id=' + punteroId;
            }

            window.location.href = url;
        }

        /* ===========================
           MODAL VOTANTES
        ============================ */
        function abrirModalVotantes(punteroId, nombre) {

            // Cerrar otros modales por seguridad
            $('.modal').modal('hide');

            // Abrir modal
            $('#modalVotantes').modal('show');
            $('#modalVotantes .modal-title').text('Votantes del puntero: ' + nombre);
            $('#votante_id_puntero').val(punteroId);

            // Limpiar formulario
            $('#formAgregarVotante')[0].reset();
            $('#tipo_votante').val('seguro');

            // Destruir DataTable si ya existe
            if ($.fn.DataTable.isDataTable('#votantes-table')) {
                $('#votantes-table').DataTable().clear().destroy();
            }

            // Cargar datos
            $.get("{{ url('puntero') }}/" + punteroId + "/votantes", function(data) {


                let tbody = $('#votantes-table tbody');
                tbody.empty();

                data.forEach((v, i) => {
                    tbody.append(`
                <tr>
                    <td>${i + 1}</td>
                    <td>${v.cedula}</td>
                    <td>${v.nombre ?? ''}</td>
                    <td>${v.tipo_votante ?? ''}</td>
                    <td>${v.mesa ?? ''}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm"
                            onclick="eliminarVotante(${v.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
                });

                // Inicializar DataTable
                $('#votantes-table').DataTable({
                    responsive: true,
                    dom: "<'row'<'col-md-4'l><'col-md-4 text-center'f><'col-md-4 text-right'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [{
                            extend: 'excelHtml5',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            title: 'Votantes del puntero ' + nombre,
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            title: 'Votantes del puntero ' + nombre,
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-secondary btn-sm',
                            text: '<i class="fas fa-print"></i> Imprimir',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        }
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    }
                });
            });

            // Foco correcto
            $('#modalVotantes').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('#votante_cedula').trigger('focus');
            });
        }



        /* ===========================
           ELIMINAR
        ============================ */
        function confirmarBorrado(button) {
            const url = button.getAttribute('data-url');

            Swal.fire({
                title: '¿Eliminar puntero?',
                text: 'Esta acción no se puede deshacer. Se borraran los votantes asociados',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = document.getElementById('formEliminarPuntero');
                    console.log(url);
                    form.action = url;
                    form.submit();
                }
            });
        }

        function filtrarPunteros() {
            if (bloqueaFiltro) return;
            let dirigente_id = $('#dirigente_id').val();

            let url = "{{ url('puntero/create') }}";

            if (dirigente_id) {
                url += '?dirigente_id=' + encodeURIComponent(dirigente_id);
            }

            window.location.href = url;

        }

        function eliminarVotante(id) {
            Swal.fire({
                title: '¿Eliminar votante?',
                icon: 'warning',
                showCancelButton: true
            }).then(r => {
                if (r.isConfirmed) {
                    $.ajax({
                        url: "{{ url('votante/delete') }}/" + id,
                        type: 'DELETE',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            Swal.fire(
                                'Eliminado',
                                response.message ?? 'Votante eliminado correctamente',
                                'success'
                            );
                            // ✅ RECARGAR LISTA DE VOTANTES DEL PUNTERO
                            if (response.punteroId) {
                                cargarVotantes(response.punteroId);
                            }
                            // ✅ ABRIR MODAL
                            if (response.abrirModalVotante) {
                                $('#modalVotante').modal('show');
                            }


                            // 2️⃣ o remover fila sin recargar (si usás DataTable)
                            // $('#fila-votante-' + id).remove();
                        },
                        error: function(xhr) {
                            Swal.fire(
                                'Error',
                                xhr.responseJSON?.message ?? 'No se pudo eliminar el votante',
                                'error'
                            );
                        }
                    });
                }

            });
        }
        $('#modalAgregarPuntero').on('shown.bs.modal', function() {
            document.getElementById('formAgregarPuntero').reset();
            $('#cedula').trigger('focus');
        });

        function cargarVotantes(idPuntero) {
            $.get("{{ url('puntero') }}/" + idPuntero + "/votantes", function(data) {

                // Destruir DataTable si existe
                if ($.fn.DataTable.isDataTable('#votantes-table')) {
                    $('#votantes-table').DataTable().clear().destroy();
                }

                let tbody = $('#votantes-table tbody');
                tbody.empty();

                data.forEach((v, i) => {
                    tbody.append(`
                <tr>
                    <td>${i + 1}</td>
                    <td>${v.cedula}</td>
                    <td>${v.nombre ?? ''}</td>
                    <td>${v.tipo_votante ?? ''}</td>
                    <td>${v.mesa ?? ''}</td>
                    <td class="text-center">
                        <button class="btn btn-danger btn-sm"
                            onclick="eliminarVotante(${v.id})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `);
                });

                // Inicializar DataTable otra vez
                $('#votantes-table').DataTable({
                    responsive: true,
                    dom: "<'row'<'col-md-4'l><'col-md-4 text-center'f><'col-md-4 text-right'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [{
                            extend: 'excelHtml5',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            title: 'Votantes del puntero',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            title: 'Votantes del puntero',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-secondary btn-sm',
                            text: '<i class="fas fa-print"></i> Imprimir',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4]
                            }
                        }
                    ],
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    }
                });
            });
        }
    </script>
@endpush
