{{-- resources/views/puntero/lista_punteros.blade.php --}}
<div class="row mb-2">
    <div class="col-md-4">
        <label for="equipo_punteros" class="form-label fw-bold">Equipos</label>
        <input type="hidden" id="dirigente_id" value="{{ $dirigenteId ?? '' }}">
        <x-adminlte-select2 name="equipo_punteros" id="equipo_punteros" onchange="filtrarPunterosPorEquipo()"
            enable-old-support>
            <option value="">Todos</option>
            @foreach ($equipos as $eq)
                <option value="{{ $eq->id }}"
                    {{ isset($equipoSeleccionado) && $equipoSeleccionado == $eq->id ? 'selected' : '' }}>
                    {{ $eq->descripcion }}
                </option>
            @endforeach
        </x-adminlte-select2>
    </div>

    <div class="col-md-4">
        <label for="dirigente_punteros" class="form-label fw-bold">Dirigentes</label>
        <x-adminlte-select2 name="dirigente_punteros" id="dirigente_punteros" onchange="filtrarPunterosPorDirigente()"
            enable-old-support>
            <option value="">Todos</option>
            @foreach ($dirigentes as $dir)
                <option value="{{ $dir->id }}"
                    {{ isset($dirigenteSeleccionado) && $dirigenteSeleccionado == $dir->id ? 'selected' : '' }}>
                    {{ $dir->nombre }}
                </option>
            @endforeach
        </x-adminlte-select2>
    </div>

    <div class="col-md-4 text-right">
        <button class="btn btn-primary" id="btnAgregarPuntero">
            <i class="fas fa-user-plus"></i>
            Agregar Puntero
        </button>
    </div>
</div>

<h4 class="mb-3">
    Total General de Votos:
    <span class="badge badge-success">
        {{ number_format($totalVotantesGeneral ?? 0, 0, '', '.') }}
    </span>
</h4>

<div class="card">
    <div class="card-body">
        <table id="punteros-lista-table" class="table table-striped table-bordered">
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
                    <th>Acciones</th>
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
                                onclick="abrirModalVotantesPuntero({{ $p->id }}, '{{ $p->nombre }}')">
                                <i class="fas fa-users"></i>
                            </button>
                            <button class="btn btn-danger btn-sm"
                                onclick="eliminarPunteroModal({{ $p->id }}, {{ $p->id_dirigente }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<!-- Modal agregar puntero -->
<div class="modal fade" id="modalAgregarPunteroLista" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">Agregar Puntero</h5>
                <button type="button" class="close text-white" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAgregarPunteroLista">
                    @csrf
                    <input type="hidden" name="id_dirigente" id="puntero_id_dirigente_lista"
                        value="{{ $dirigenteId ?? '' }}">
                    <input type="hidden" name="id_equipo" id="puntero_id_equipo_lista"
                        value="{{ $equipoSeleccionado ?? '' }}">

                    <div class="form-group">
                        <label>Cédula</label>
                        <input type="text" name="cedula" id="puntero_cedula_lista" class="form-control" required>
                        <small class="text-danger" id="error-puntero-cedula"></small>
                    </div>

                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" id="puntero_nombre_lista" class="form-control" required>
                        <small class="text-danger" id="error-puntero-nombre"></small>
                    </div>

                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" id="puntero_telefono_lista" class="form-control">
                        <small class="text-danger" id="error-puntero-telefono"></small>
                    </div>

                    <div class="form-group">
                        <label>Barrio</label>
                        <input type="text" name="barrio" id="puntero_barrio_lista" class="form-control">
                        <small class="text-danger" id="error-puntero-barrio"></small>
                    </div>

                    <button type="button" class="btn btn-primary" id="btnGuardarPunteroLista">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar DataTable
        if ($.fn.DataTable && $('#punteros-lista-table').length) {
            $('#punteros-lista-table').DataTable({
                responsive: true,
                dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [{
                        extend: 'excelHtml5',
                        className: 'btn btn-info btn-sm',
                        text: '<i class="fas fa-file-excel"></i> Excel',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'pdfHtml5',
                        className: 'btn btn-danger btn-sm',
                        text: '<i class="fas fa-file-pdf"></i> PDF',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    },
                    {
                        extend: 'print',
                        className: 'btn btn-secondary btn-sm',
                        text: '<i class="fas fa-print"></i> Imprimir',
                        exportOptions: {
                            columns: [0, 1, 2, 3, 4, 5, 6, 7]
                        }
                    }
                ],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Todos"]
                ]
            });
        }

        // Buscar por cédula en el modal
        $('#puntero_cedula_lista').on('blur', function() {
            buscarPunteroPorCedulaLista();
        });

        $('#puntero_cedula_lista').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarPunteroPorCedulaLista();
                $('#puntero_nombre_lista').focus();
            }
        });

        // Enter como TAB en el formulario
        $('#formAgregarPunteroLista').on('keydown', 'input', function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                let inputs = $('#formAgregarPunteroLista').find('input:visible');
                let index = inputs.index(this);

                if (index === inputs.length - 1) {
                    guardarPunteroListaAjax();
                } else {
                    inputs.eq(index + 1).focus();
                }
            }
        });

        // Guardar puntero
        $('#btnGuardarPunteroLista').on('click', function() {
            guardarPunteroListaAjax();
        });

        // Abrir modal
        $('#btnAgregarPuntero').on('click', function() {
            let equipoId = $('#equipo_punteros').val();
            let dirigenteId = $('#dirigente_punteros').val();

            $('#puntero_id_equipo_lista').val(equipoId);
            $('#puntero_id_dirigente_lista').val(dirigenteId);

            $('#formAgregarPunteroLista')[0].reset();
            limpiarErroresPuntero();
            $('#modalAgregarPunteroLista').modal('show');

            setTimeout(function() {
                $('#puntero_cedula_lista').focus();
            }, 500);
        });
    });

    function buscarPunteroPorCedulaLista() {
        let cedula = $('#puntero_cedula_lista').val().trim();
        if (cedula.length < 3) return;

        $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
            if (response.encontrado) {
                $('#puntero_nombre_lista').val(response.data.nombre ?? '');
                $('#puntero_telefono_lista').val(response.data.telefono ?? '');
                $('#puntero_barrio_lista').val(response.data.direccion ?? '');
            }
        });
    }

    function guardarPunteroListaAjax() {
        let btnGuardar = $('#btnGuardarPunteroLista');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        limpiarErroresPuntero();

        let formData = {
            cedula: $('#puntero_cedula_lista').val(),
            nombre: $('#puntero_nombre_lista').val(),
            telefono: $('#puntero_telefono_lista').val(),
            barrio: $('#puntero_barrio_lista').val(),
            id_dirigente: $('#puntero_id_dirigente_lista').val(),
            id_equipo: $('#puntero_id_equipo_lista').val(),
            _token: '{{ csrf_token() }}'
        };

        // Validar que tenga dirigente
        if (!formData.id_dirigente) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debes seleccionar un dirigente primero'
            });
            btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            return;
        }

        $.ajax({
            url: "{{ route('puntero.store.ajax') }}",
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#modalAgregarPunteroLista').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Recargar la lista
                filtrarPunterosGeneral();

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error de validación'
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON?.message || 'Error al guardar el puntero'
                    });
                }
            }
        });
    }

    function limpiarErroresPuntero() {
        $('.text-danger').text('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function filtrarPunterosPorEquipo() {
        let equipoId = $('#equipo_punteros').val();
        let dirigenteId = $('#dirigente_punteros').val();
        let url = `{{ url('/') }}/punteros/filtrar?equipo_id=${equipoId}`;

        if (dirigenteId) {
            url += `&dirigente_id=${dirigenteId}`;
        }

        cargarPunterosLista(url);
    }

    function filtrarPunterosPorDirigente() {
        let equipoId = $('#equipo_punteros').val();
        let dirigenteId = $('#dirigente_punteros').val();
        let url = `{{ url('/') }}/punteros/filtrar?dirigente_id=${dirigenteId}`;

        if (equipoId) {
            url += `&equipo_id=${equipoId}`;
        }

        cargarPunterosLista(url);
    }

    function filtrarPunterosGeneral() {
        let equipoId = $('#equipo_punteros').val();
        let dirigenteId = $('#dirigente_punteros').val();
        let url = "{{ url('/') }}/punteros/filtrar";
        let params = [];

        if (equipoId) params.push(`equipo_id=${equipoId}`);
        if (dirigenteId) params.push(`dirigente_id=${dirigenteId}`);

        if (params.length) {
            url += '?' + params.join('&');
        }

        cargarPunterosLista(url);
    }



    function cargarPunterosLista(url) {
        $('#contenidoPunteros').html(
            '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando punteros...</p></div>'
        );

        $.get(url, function(data) {
            $('#contenidoPunteros').html(data);
        }).fail(function(xhr) {
            $('#contenidoPunteros').html(
                '<div class="alert alert-danger text-center p-4">' +
                '<i class="fas fa-exclamation-circle fa-2x mb-3"></i>' +
                '<p>Error cargando punteros. Intente nuevamente.</p>' +
                '</div>'
            );
        });
    }

    function eliminarPunteroModal(punteroId, dirigenteId) {
        Swal.fire({
            title: '¿Eliminar puntero?',
            text: 'Esta acción no se puede deshacer. Se borrarán los votantes asociados.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    title: 'Eliminando...',
                    text: 'Por favor espera',
                    allowOutsideClick: false,
                    allowEscapeKey: false,
                    showConfirmButton: false,
                    didOpen: () => Swal.showLoading()
                });

                $.ajax({
                    url: "{{ route('puntero.destroy.ajax') }}",
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}',
                        id: punteroId
                    },
                    success: function(response) {
                        Swal.close();

                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Eliminado',
                                text: 'El puntero ha sido eliminado',
                                timer: 1500,
                                showConfirmButton: false
                            });

                            filtrarPunterosGeneral();
                        }
                    },
                    error: function(xhr) {
                        Swal.close();
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: xhr.responseJSON?.message ||
                                'No se pudo eliminar el puntero'
                        });
                    }
                });
            }
        });
    }

    function abrirModalVotantesPuntero(punteroId, nombre) {
        // Llamar a la función cargarVotantes que está en el archivo principal
        if (typeof window.cargarVotantes === 'function') {
            window.cargarVotantes(punteroId, nombre);
        } else {
            console.error('Función cargarVotantes no encontrada');
            // Fallback: intentar acceder directamente
            if (typeof cargarVotantes === 'function') {
                cargarVotantes(punteroId, nombre);
            } else {
                alert('Error: No se puede cargar la función de votantes');
            }
        }
    }
</script>
