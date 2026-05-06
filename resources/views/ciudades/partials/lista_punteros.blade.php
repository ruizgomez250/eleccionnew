{{-- resources/views/puntero/lista_punteros.blade.php --}}
<form id="formAgregarPunteroLista">
    @csrf
    <input type="hidden" name="id_dirigente" id="puntero_id_dirigente_lista" value="{{ $dirigenteId ?? '' }}">
    <input type="hidden" name="id_equipo" id="puntero_id_equipo_lista" value="{{ $equipoSeleccionado ?? '' }}">

    <div class="row mb-2">
        <div class="col-md-6">
            <div class="input-group">
                <label class="form-label fw-bold">Equipos: </label>
                <select name="equipo_punteros" id="equipo_punteros" class="form-control"
                    onchange="filtrarPunterosPorEquipo()">
                    <option value="">Todos</option>
                    @foreach ($equipos as $eq)
                        <option value="{{ $eq->id }}"
                            {{ isset($equipoSeleccionado) && $equipoSeleccionado == $eq->id ? 'selected' : '' }}>
                            {{ $eq->descripcion }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" onclick="abrirModalEquiposPunteros()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="input-group">
                <label class="form-label fw-bold">Dirigentes: </label>
                <select name="dirigente_punteros" id="dirigente_punteros" class="form-control"
                    onchange="filtrarPunterosPorDirigente()">
                    <option value="">Todos</option>
                    @foreach ($dirigentes as $dir)
                        <option value="{{ $dir->id }}"
                            {{ isset($dirigenteSeleccionado) && $dirigenteSeleccionado == $dir->id ? 'selected' : '' }}>
                            {{ $dir->nombre }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" onclick="abrirModalDirigentesPunteros()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-2">
            <label>Cédula</label>
            <input type="text" name="cedula" id="puntero_cedula_lista" class="form-control" required>
            <small class="text-danger" id="error-cedula"></small>
        </div>

        
        <div class="col-md-4">
            <label>Nombre</label>
            <div class="input-group">
                <input type="text" name="nombre" id="puntero_nombre_lista" class="form-control" required>
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" onclick="abrirModalBuscarPersonaPuntero()">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
            <small class="text-danger" id="error-nombre"></small>
        </div>


        <div class="col-md-2">
            <label>Teléfono</label>
            <input type="text" name="telefono" id="puntero_telefono_lista" class="form-control">
            <small class="text-danger" id="error-telefono"></small>
        </div>
        <div class="col-md-4">
            <label>Barrio</label>
            <input type="text" name="barrio" id="puntero_barrio_lista" class="form-control">
            <small class="text-danger" id="error-barrio"></small>
        </div>
    </div>
    <div class="row mb-2">
        <div class="col-md-6">
            <button type="button" class="btn btn-primary" id="btnGuardarPunteroLista">
                <i class="fas fa-save"></i> Guardar Puntero
            </button>


        </div>
        <div class="col-md-6 text-right">


            <button type="button" class="btn btn-danger ml-2" data-dismiss="modal">
                <i class="fas fa-arrow-left"></i> Volver Atras
            </button>
        </div>
    </div>
</form>

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

                            <button class="btn btn-success btn-sm"
                                onclick="abrirModalVotantesPuntero({{ $p->id }}, '{{ $p->nombre }}')">
                                <i class="fas fa-users"><span
                                        class="badge badge-success">{{ $p->votantes_count }}</span></i>
                            </button>

                        </td>
                        <td>
                            <div class="btn-group btn-group-sm" role="group">
                                {{-- Botón para crear vehículo --}}
                                <button class="btn btn-info btn-icon-with-count"
                                    onclick="abrirModalCrearVehiculo({{ $p->id }}, '{{ addslashes($p->nombre) }}')"
                                    title="Vehículos asignados">
                                    <i class="fas fa-truck"></i>
                                    <span class="count-badge {{ $p->vehiculos_count == 0 ? 'zero' : '' }}">
                                        {{ $p->vehiculos_count }}
                                    </span>
                                </button>

                                {{-- Botón eliminar --}}
                                <button class="btn btn-danger"
                                    onclick="eliminarPunteroModal({{ $p->id }}, {{ $p->id_dirigente }})">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
{{-- MODAL DE BÚSQUEDA DE EQUIPOS PARA PUNTEROS --}}
<div class="modal fade" id="modalEquiposPunteros" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Equipo
                </h5>
                <button type="button" class="close text-white" onclick="cerrarequipopunteros()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaEquiposPunteros" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                        </thead>
                        <tbody>
                            @foreach ($equipos as $eq)
                                <tr>
                                    <td>{{ $eq->id }}</td>
                                    <td>{{ $eq->descripcion }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm"
                                            onclick="seleccionarEquipoPunteros({{ $eq->id }}, '{{ addslashes($eq->descripcion) }}')">
                                            <i class="fas fa-check"></i> Seleccionar
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
{{-- MODAL DE BÚSQUEDA DE PERSONAS DEL PADRÓN PARA PUNTEROS --}}
<div class="modal fade" id="modalBuscarPersonaPadronPuntero" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Persona en el Padrón (Punteros)
                </h5>
                <button type="button" class="close text-white" onclick="cerrarpersonapuntero()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="row mb-3">
                    <div class="col-md-5">
                        <label>Nombre</label>
                        <input type="text" id="buscar_nombre_padron_puntero" class="form-control"
                            placeholder="Ingrese nombre...">
                    </div>
                    <div class="col-md-5">
                        <label>Apellido</label>
                        <input type="text" id="buscar_apellido_padron_puntero" class="form-control"
                            placeholder="Ingrese apellido...">
                    </div>
                    <div class="col-md-2">
                        <label>&nbsp;</label>
                        <button class="btn btn-primary btn-block" onclick="buscarPersonasPadronPuntero()">
                            <i class="fas fa-search"></i> Buscar
                        </button>
                    </div>
                </div>

                <div class="table-responsive" style="max-height: 400px;">
                    <table id="tablaPersonasPadronPuntero" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Apellido</th>
                                <th>Partido</th>
                                <th>Mesa</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td colspan="6" class="text-center">
                                    Ingrese criterios de búsqueda
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarpersonapuntero()">Cerrar</button>
            </div>
        </div>
    </div>
</div>
{{-- MODAL DE BÚSQUEDA DE DIRIGENTES PARA PUNTEROS --}}
<div class="modal fade" id="modalDirigentesPunteros" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Dirigente
                </h5>
                <button type="button" class="close text-white"
                    onclick="cerrarmodaldirigentespunteros()">&times;</button>
            </div>
            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaDirigentesPunteros" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Nombre</th>
                                <th>Cédula</th>
                                <th>Equipo</th>
                                <th>Acción</th>
                        </thead>
                        <tbody>
                            @foreach ($dirigentes as $dir)
                                <td>{{ $dir->id }}</td>
                                <td>{{ $dir->nombre }}</td>
                                <td>{{ $dir->cedula }}</td>
                                <td>{{ $dir->equipo->descripcion ?? '' }}</td>
                                <td class="text-center">
                                    <button class="btn btn-success btn-sm"
                                        onclick="seleccionarDirigentePunteros({{ $dir->id }}, '{{ addslashes($dir->nombre) }}')">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        // Inicializar Select2 para equipos
        // if ($('#equipo_punteros').length) {
        //     $('#equipo_punteros').select2({
        //         width: '100%',
        //         dropdownParent: $('#modalPunterosLista'),
        //         allowClear: true,
        //         minimumResultsForSearch: 1,
        //         placeholder: 'Selecciona un equipo...',
        //         language: {
        //             noResults: function() {
        //                 return "No se encontraron resultados";
        //             },
        //             searching: function() {
        //                 return "Buscando...";
        //             }
        //         }
        //     });
        // }

        // Inicializar Select2 para dirigentes
        // if ($('#dirigente_punteros').length) {
        //     $('#dirigente_punteros').select2({
        //         width: '100%',
        //         dropdownParent: $('#modalPunterosLista'),
        //         allowClear: true,
        //         minimumResultsForSearch: 1,
        //         placeholder: 'Selecciona un dirigente...',
        //         language: {
        //             noResults: function() {
        //                 return "No se encontraron resultados";
        //             },
        //             searching: function() {
        //                 return "Buscando...";
        //             }
        //         }
        //     });
        // }

        // Inicializar DataTable
        if ($.fn.DataTable && $('#punteros-lista-table').length) {
            $('#punteros-lista-table').DataTable({
                responsive: true,
                dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                    "<'row'<'col-sm-12'tr>>" +
                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                buttons: [{
                    extend: 'print',
                    className: 'btn btn-secondary btn-sm',
                    text: '<i class="fas fa-print"></i> Imprimir',
                    exportOptions: {
                        columns: [0, 1, 2, 3, 4, 5, 6, 7]
                    },
                    customize: function(win) {
                        $(win.document.body).find('table').addClass('table table-bordered');
                        $(win.document.body).find('h1').css('text-align', 'center');
                    }
                }],
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                },
                pageLength: 10,
                lengthMenu: [
                    [10, 25, 50, -1],
                    [10, 25, 50, "Todos"]
                ],
                columnDefs: [{
                    targets: [8],
                    orderable: false,
                    searchable: false
                }]
            });
        }

        // Buscar por cédula en el formulario
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

        // Cuando se cambie el equipo, actualizar el hidden
        $('#equipo_punteros').on('change', function() {
            let equipoId = $(this).val();
            $('#puntero_id_equipo_lista').val(equipoId);
        });

        // Cuando se cambie el dirigente, actualizar el hidden
        $('#dirigente_punteros').on('change', function() {
            let dirigenteId = $(this).val();
            $('#puntero_id_dirigente_lista').val(dirigenteId);
        });
    });

    function cerrarequipopunteros() {
        $('#modalEquiposPunteros').modal('hide');
    }
    function cerrarpersonapuntero() {
        $('#modalBuscarPersonaPadronPuntero').modal('hide');
    }
    // ABRIR MODAL DE EQUIPOS
    function abrirModalEquiposPunteros() {
        $('#modalEquiposPunteros').modal('show');

        if (!$.fn.DataTable.isDataTable('#tablaEquiposPunteros')) {
            $('#tablaEquiposPunteros').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    search: "Buscar equipo:",
                    searchPlaceholder: "Nombre, descripción..."
                },
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                order: [
                    [1, 'asc']
                ]
            });
        }
    }

    function cerrarmodaldirigentespunteros() {
        $('#modalDirigentesPunteros').modal('hide');
    }
    // ABRIR MODAL DE DIRIGENTES
    function abrirModalDirigentesPunteros() {
        $('#modalDirigentesPunteros').modal('show');

        if (!$.fn.DataTable.isDataTable('#tablaDirigentesPunteros')) {
            $('#tablaDirigentesPunteros').DataTable({
                responsive: true,
                pageLength: 10,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                    search: "Buscar dirigente:",
                    searchPlaceholder: "Nombre, cédula..."
                },
                lengthMenu: [
                    [5, 10, 25, 50, -1],
                    [5, 10, 25, 50, "Todos"]
                ],
                order: [
                    [1, 'asc']
                ]
            });
        }
    }
    // SELECCIONAR EQUIPO DESDE MODAL
    function seleccionarEquipoPunteros(id, descripcion) {
        $('#equipo_punteros').val(id);
        $('#puntero_id_equipo_lista').val(id);
        equipoSeleccionadoActualPunteros = id;

        $('#modalEquiposPunteros').modal('hide');

        $('#equipo_punteros').trigger('change');
        //filtrarPunterosPorEquipo();
    }
    // SELECCIONAR DIRIGENTE DESDE MODAL
    function seleccionarDirigentePunteros(id, nombre) {
        $('#dirigente_punteros').val(id);
        $('#puntero_id_dirigente_lista').val(id);
        dirigenteSeleccionadoActualPunteros = id;

        $('#modalDirigentesPunteros').modal('hide');

        $('#dirigente_punteros').trigger('change');
    }

    function buscarPunteroPorCedulaLista() {
        let cedula = $('#puntero_cedula_lista').val().trim();
        //console.log(cedula.'cedula');
        if (cedula.length < 3) return;

        $.get("{{ url('dirigente/buscar-por-cedulap') }}/" + cedula, function(response) {
            if (response.encontrado) {
                $('#puntero_nombre_lista').val(response.data.nombre ?? '');
                $('#puntero_telefono_lista').val(response.data.telefono ?? '');
                $('#puntero_barrio_lista').val(response.data.direccion ?? '');
            } else {
                // Opcional: limpiar campos si no se encuentra
                $('#puntero_nombre_lista').val('');
                $('#puntero_telefono_lista').val('');
                $('#puntero_barrio_lista').val('');
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
                Swal.fire({
                    icon: 'success',
                    title: 'Éxito',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Limpiar formulario
                $('#formAgregarPunteroLista')[0].reset();
                $('#puntero_cedula_lista').focus();

                // Recargar la lista
                filtrarPunterosGeneral();

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    // Mostrar errores de validación
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $(`#error-${key}`).text(value[0]);
                        $(`#puntero_${key}_lista`).addClass('is-invalid');
                    });

                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: 'Por favor verifica los campos marcados'
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
            console.error('Error cargando punteros:', xhr);
            $('#contenidoPunteros').html(
                '<div class="alert alert-danger text-center p-4">' +
                '<i class="fas fa-exclamation-circle fa-2x mb-3"></i>' +
                '<p>Error cargando punteros. Intente nuevamente.</p>' +
                '<button class="btn btn-sm btn-outline-danger mt-2" onclick="filtrarPunterosGeneral()">' +
                '<i class="fas fa-sync"></i> Reintentar</button>' +
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
        $('#votante_id_puntero').val(punteroId);
        // Llamar a la función cargarVotantes que está en el archivo principal
        if (typeof window.cargarVotantes === 'function') {
            window.cargarVotantes(punteroId, nombre);
        } else {
            console.error('Función cargarVotantes no encontrada');
            // Fallback: intentar acceder directamente
            if (typeof cargarVotantes === 'function') {
                cargarVotantes(punteroId, nombre);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se puede cargar la función de votantes'
                });
            }
        }
    }

    // Función para buscar por cédula de votante (si es necesaria)
    function buscarPorCedulaV() {
        let cedula = $('#votante_cedula').val().trim();
        if (cedula.length < 3) return;

        $.get("{{ url('votante/buscar-por-cedula') }}/" + cedula, function(response) {
            if (!response.encontrado) {
                $('#votante_nombre').val('');
                $('#direccion').val('');
                $('#mesa').val('');
                $('#orden').val('');
                $('#partido').val('');
                $('#escuela').val('');
                $('#ciudad').val('');
                $('#departamento').val('');

                Swal.fire({
                    icon: 'info',
                    title: 'Cédula no encontrada',
                    text: 'No se encontró ningún votante con la cédula ingresada',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
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

    //comienza las funciones de busqueda 
    // Funciones específicas para PUNTEROS
    function abrirModalBuscarPersonaPuntero() {
        // Limpiar campos de búsqueda
        $('#buscar_nombre_padron_puntero').val('');
        $('#buscar_apellido_padron_puntero').val('');

        // Limpiar tabla
        $('#tablaPersonasPadronPuntero tbody').html(`
        <tr>
            <td colspan="6" class="text-center">
                Ingrese criterios de búsqueda
            </td>
        </tr>
    `);

        $('#modalBuscarPersonaPadronPuntero').modal('show');
    }

    function buscarPersonasPadronPuntero() {
        let nombre = $('#buscar_nombre_padron_puntero').val().trim();
        let apellido = $('#buscar_apellido_padron_puntero').val().trim();

        if (!nombre && !apellido) {
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: 'Debe ingresar al menos nombre o apellido para buscar',
                timer: 2000,
                showConfirmButton: false,
                toast: true,
                position: 'top-end'
            });
            return;
        }

        // Mostrar loading
        $('#tablaPersonasPadronPuntero tbody').html(`
        <tr>
            <td colspan="6" class="text-center">
                <i class="fas fa-spinner fa-spin"></i> Buscando...
            </td>
        </tr>
    `);

        $.ajax({
            url: "{{ route('buscar.personas.padron') }}",
            type: 'GET',
            data: {
                nombre: nombre,
                apellido: apellido
            },
            dataType: 'json',
            success: function(response) {
                if (response.success && response.data.length > 0) {
                    let html = '';
                    response.data.forEach(persona => {
                        let cedula = persona.cedula || '';
                        let nombrePersona = (persona.nombre || '').replace(/'/g, "\\'");
                        let apellidoPersona = (persona.apellido || '').replace(/'/g, "\\'");

                        html += `
                        <tr>
                            <td>${cedula}</td>
                            <td>${persona.nombre || ''}</td>
                            <td>${persona.apellido || ''}</td>
                            <td>${persona.partido || ''}</td>
                            <td>${persona.mesa || ''}</td>
                            <td class="text-center">
                                <button class="btn btn-success btn-sm" 
                                    onclick="seleccionarPersonaPadronPuntero('${cedula}', '${nombrePersona}', '${apellidoPersona}')">
                                    <i class="fas fa-check"></i> Seleccionar
                                </button>
                            </td>
                        </tr>
                    `;
                    });
                    $('#tablaPersonasPadronPuntero tbody').html(html);
                } else {
                    $('#tablaPersonasPadronPuntero tbody').html(`
                    <tr>
                        <td colspan="6" class="text-center text-warning">
                            <i class="fas fa-exclamation-triangle"></i> No se encontraron resultados
                        </td>
                    </tr>
                `);
                }
            },
            error: function(xhr) {
                console.error('Error:', xhr);
                $('#tablaPersonasPadronPuntero tbody').html(`
                <tr>
                    <td colspan="6" class="text-center text-danger">
                        <i class="fas fa-times-circle"></i> Error al buscar. Intente nuevamente.
                    </td>
                </tr>
            `);
            }
        });
    }

    function seleccionarPersonaPadronPuntero(cedula, nombre, apellido) {
        let nombreCompleto = `${nombre} ${apellido}`.trim();

        // Cargar en formulario de PUNTEROS
        $('#puntero_cedula_lista').val(cedula);
        $('#puntero_nombre_lista').val(nombreCompleto);

        // Disparar búsqueda automática para llenar teléfono y barrio
        buscarPunteroPorCedulaLista();

        // Cerrar el modal
        $('#modalBuscarPersonaPadronPuntero').modal('hide');

        // Mostrar mensaje de éxito
        Swal.fire({
            icon: 'success',
            title: 'Persona seleccionada',
            text: `Se cargaron los datos de ${nombreCompleto}`,
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });

        // Enfocar el siguiente campo
        $('#puntero_nombre_lista').focus();
    }
</script>
