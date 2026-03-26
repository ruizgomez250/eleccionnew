<form id="formAgregarDirigente">
    <div class="row mb-2">
        <div class="col-md-6">
            <input type="hidden" id="sistema_id" value="{{ $sistemaId }}">

            {{-- Cambiar a un select simple para mejor control --}}
            <div class="input-group">
                <label class="form-label fw-bold">Equipos: </label>
                <select name="equipo_id_dir" id="equipo_id_dir" class="form-control" onchange="filtrarDirigentes()">
                    <option value="">Todos</option>
                    @foreach ($equipos as $eq)
                        <option value="{{ $eq->id }}" {{ $equipoSeleccionado == $eq->id ? 'selected' : '' }}>
                            {{ $eq->descripcion }}
                        </option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <button type="button" class="btn btn-info" onclick="abrirModalEquipos()">
                        <i class="fas fa-search"></i>
                    </button>

                </div>
            </div>
        </div>
    </div>

    <div class="row mb-2">
        <div class="col-md-2">
            <label>Cédula</label>
            <input type="text" name="cedula" class="form-control" required autofocus>
            <small class="text-danger" id="error-cedula"></small>
        </div>
        <div class="col-md-4">
            <label>Nombre</label>
            <input type="text" name="nombre" class="form-control" required>
            <small class="text-danger" id="error-nombre"></small>
        </div>
        <div class="col-md-2">
            <label>Teléfono</label>
            <input type="text" name="telefono" class="form-control">
            <small class="text-danger" id="error-telefono"></small>
        </div>
        <div class="col-md-4">
            <label>Barrio</label>
            <input type="text" name="barrio" class="form-control">
            <small class="text-danger" id="error-barrio"></small>
        </div>
    </div>
        <div class="row mt-3">
            <div class="col-md-6">
                <button type="button" class="btn btn-primary" id="btnGuardarDirigente">
                <i class="fas fa-save"></i> Guardar Dirigente
            </button>


            </div>
            <div class="col-md-6 text-right">


                <button type="button" class="btn btn-danger ml-2" data-dismiss="modal">
                    <i class="fas fa-arrow-left"></i> Volver a Atras
                </button>
            </div>
        </div>
</form>
{{-- MODAL DE BÚSQUEDA DE EQUIPOS --}}
<div class="modal fade" id="modalEquipos" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-search"></i> Buscar Equipo
                </h5>
                <button type="button" class="close text-white" onclick="cerrarmodalequipo()">&times;</button>
            </div>

            <div class="modal-body">
                <div class="table-responsive">
                    <table id="tablaEquipos" class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Descripción</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($equipos as $eq)
                                <tr>
                                    <td>{{ $eq->id }}</td>
                                    <td>{{ $eq->descripcion }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-success btn-sm"
                                            onclick="seleccionarEquipo({{ $eq->id }}, '{{ addslashes($eq->descripcion) }}')">
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
<h4 class="mb-3">
    Total General de Votos:
    <span class="badge badge-success">
        {{ number_format($totalVotantesGeneral, 0, '', '.') }}
    </span>
</h4>

<div class="card">
    <div class="card-body">
        <table id="dirigentes-table" class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Teléfono</th>
                    <th>Barrio</th>
                    <th>Equipo</th>
                    <th>Punteros</th>
                    <th>Votantes</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($dirigentes as $dir)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $dir->cedula }}</td>
                        <td>{{ $dir->nombre }}</td>
                        <td>{{ $dir->telefono }}</td>
                        <td>{{ $dir->barrio }}</td>
                        <td>{{ $dir->equipo->descripcion ?? '' }}</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-info"
                                onclick="abrirModalPunterosListapordir({{ $dir->id }},'{{ $dir->sistema->nombre }}')">
                                <i class="fas fa-users"> <span
                                        class="badge badge-info">{{ $dir->punteros_count }}</span></i>
                            </button>

                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ $dir->votantes_count ?? 0 }}</span>
                        </td>
                        <td>

                            <button class="btn btn-danger btn-sm" onclick="eliminarDirigente({{ $dir->id }})">
                                <i class="fas fa-trash"></i>
                            </button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

<script>
    $(document).ready(function() {

        // Inicializar Select2 cuando se abre el modal (esto va en la página principal, no aquí)
        // Pero como este script se carga por AJAX, necesitamos inicializar después de cargar

        // Inicializar DataTable
        $('#dirigentes-table').DataTable({
            destroy: true,
            responsive: true,
            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                "<'row'<'col-sm-12'tr>>" +
                "<'row'<'col-sm-5'i><'col-sm-7'p>>",
            buttons: [{
                extend: 'print',
                text: '<i class="fas fa-print"></i> Imprimir',
                className: 'btn btn-secondary',
                autoPrint: true,
                title: 'Listado de Dirigentes',
                customize: function(win) {
                    $(win.document.body).find('table').addClass('table table-bordered');
                    $(win.document.body).find('h1').css('text-align', 'center');
                },
                exportOptions: {
                    columns: [0, 1, 2, 3, 4, 5, 6, 7]
                }
            }],
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                buttons: {
                    print: 'Imprimir'
                }
            },
            pageLength: 10,
            lengthMenu: [
                [5, 10, 25, 50, -1],
                [5, 10, 25, 50, "Todos"]
            ],
            columnDefs: [{
                targets: [8],
                orderable: false,
                searchable: false
            }]
        });

        // Inicializar Select2 después de cargar el contenido
        // if ($('#equipo_id_dir').length) {
        //     $('#equipo_id_dir').select2({
        //         width: '100%',
        //         dropdownParent: $('#modalDirigentes'),
        //         allowClear: true,
        //         minimumResultsForSearch: 1,
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

        //buscador de dirigentes
        $('#formAgregarDirigente input[name="cedula"]').on('blur', function() {
            buscarPorCedula(
                '#formAgregarDirigente input[name="cedula"]',
                '#formAgregarDirigente input[name="nombre"]',
                '#formAgregarDirigente input[name="telefono"]',
                '#formAgregarDirigente input[name="barrio"]'
            );
        });

        // Enter funciona como TAB y al final envía el formulario
        $('#formAgregarDirigente').on('keydown', 'input', function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                let inputs = $('#formAgregarDirigente').find('input:visible');
                let index = inputs.index(this);

                if (index === inputs.length - 1) {
                    guardarDirigenteAjax();
                } else {
                    inputs.eq(index + 1).focus();
                }
            }
        });

        // Guardar con AJAX
        $('#btnGuardarDirigente').on('click', function() {
            guardarDirigenteAjax();
        });
    });

    function guardarDirigenteAjax() {
        let btnGuardar = $('#btnGuardarDirigente');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        limpiarErrores();

        let formData = {
            cedula: $('input[name="cedula"]').val(),
            nombre: $('input[name="nombre"]').val(),
            telefono: $('input[name="telefono"]').val(),
            barrio: $('input[name="barrio"]').val(),
            id_equipo: $('#equipo_id_dir').val(),
            sistema_id: $('#sistema_id').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route('dirigentes.store.ajax') }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Limpiar formulario
                $('#formAgregarDirigente')[0].reset();

                // Recargar la lista
                filtrarDirigentes();

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422 && xhr.responseJSON.errors) {
                    // Mostrar errores de validación en cada campo
                    $.each(xhr.responseJSON.errors, function(key, value) {
                        $(`#error-${key}`).text(value[0]);
                        $(`input[name="${key}"]`).addClass('is-invalid');
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
                        text: xhr.responseJSON?.message || 'Error al guardar el dirigente'
                    });
                }
            }
        });
    }

    function limpiarErrores() {
        $('.text-danger').text('');
        $('.is-invalid').removeClass('is-invalid');
    }

    function buscarPorCedula(inputCedula, inputNombre, inputTelefono, inputBarrio) {
        let cedula = $(inputCedula).val().trim();
        if (cedula.length < 3) return;

        $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
            if (response.encontrado) {
                $(inputNombre).val(response.data.nombre);
                $(inputTelefono).val(response.data.telefono);
                $(inputBarrio).val(response.data.direccion);
            }
        });
    }

    function filtrarDirigentes() {
        let equipoId = $('#equipo_id_dir').val();
        let sistemaId = $('#sistema_id').val();
        let url = `{{ url('/') }}/sistemas/${sistemaId}/dirigentes?equipo_id=${equipoId}`;

        $("#contenidoDirigentes").html(
            '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

        $.get(url, function(data) {
            $("#contenidoDirigentes").html(data);
        }).fail(function(xhr) {
            // console.log(xhr.responseText);
            $("#contenidoDirigentes").html('<div class="alert alert-danger">Error cargando dirigentes</div>');
        });
    }

    function cerrarmodalequipo() {
        $('#modalEquipos').modal('hide');
    }

    function abrirModalEquipos() {
        $('#modalEquipos').modal('show');

        // Inicializar DataTable de equipos solo si no está inicializado
        if (!$.fn.DataTable.isDataTable('#tablaEquipos')) {
            $('#tablaEquipos').DataTable({
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

    function seleccionarEquipo(id, descripcion) {
        // Seleccionar la opción en el select
        $('#equipo_id_dir').val(id);
        equipoSeleccionadoActual = id;

        // Cerrar el modal
        $('#modalEquipos').modal('hide');


        // DISPARAR EL FILTRO
        $('#equipo_id_dir').trigger('change');
    }
</script>
