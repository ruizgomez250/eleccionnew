<div class="row mb-2">
    <div class="col-md-4">
        <label for="equipo_id" class="form-label fw-bold">Equipos</label>
        <input type="hidden" id="sistema_id" value="{{ $sistemaId }}">
        <x-adminlte-select2 name="equipo_id" id="equipo_id" onchange="filtrarDirigentes()" enable-old-support>
            <option value="">Todos</option>
            @foreach ($equipos as $eq)
                <option value="{{ $eq->id }}" {{ $equipoSeleccionado == $eq->id ? 'selected' : '' }}>
                    {{ $eq->descripcion }}
                </option>
            @endforeach
        </x-adminlte-select2>
    </div>

    <div class="col-md-8 text-right">
        <button class="btn btn-primary" id="btnAgregarDirigente">
            <i class="fas fa-user-plus"></i>
            Agregar Dirigente
        </button>
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
                            <span class="badge badge-info">{{ $dir->punteros_count }}</span>
                        </td>
                        <td class="text-center">
                            <span class="badge badge-success">{{ $dir->votantes_count ?? 0 }}</span>
                        </td>
                        <td>
                            <button class="btn btn-success btn-sm"
                                onclick="abrirModalPunteros({{ $dir->id }}, '{{ $dir->nombre }}')">
                                <i class="fas fa-user-plus"></i>
                            </button>
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

<!-- Modal agregar dirigente - solo agrega los divs de error -->
<div class="modal fade" id="modalAgregarDirigente" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Dirigente</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAgregarDirigente">
                    <input type="hidden" name="id_equipo">
                    <div class="form-group">
                        <label>Cédula</label>
                        <input type="text" name="cedula" class="form-control" required>
                        <small class="text-danger" id="error-cedula"></small>
                    </div>
                    <div class="form-group">
                        <label>Nombre</label>
                        <input type="text" name="nombre" class="form-control" required>
                        <small class="text-danger" id="error-nombre"></small>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="text" name="telefono" class="form-control">
                        <small class="text-danger" id="error-telefono"></small>
                    </div>
                    <div class="form-group">
                        <label>Barrio</label>
                        <input type="text" name="barrio" class="form-control">
                        <small class="text-danger" id="error-barrio"></small>
                    </div>
                    <button type="button" class="btn btn-primary" id="btnGuardarDirigente">
                        <i class="fas fa-save"></i> Guardar
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    // Reemplaza TODO el contenido del script actual con esto:
    $(document).ready(function() {
        //buscador de punteros
        function buscarPunteroPorCedula() {
            let cedula = $('#puntero_cedula').val().trim();
            if (cedula.length < 3) return;

            $.get("{{ url('dirigente/buscar-por-cedula') }}/" + cedula, function(response) {
                if (response.encontrado) {
                    $('#puntero_nombre').val(response.data.nombre);
                    $('#puntero_telefono').val(response.data.telefono);
                    $('#puntero_barrio').val(response.data.direccion);
                    // NO asignar id_dirigente aquí porque ya viene del botón
                }
            });
        }

        // Ejecutar búsqueda al perder foco
        $('#puntero_cedula').on('blur', buscarPunteroPorCedula);

        // Ejecutar búsqueda al presionar Enter en cédula
        $('#puntero_cedula').on('keydown', function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                buscarPunteroPorCedula();
                $('#puntero_nombre').focus();
            }
        });

        // Enter funciona como TAB en todo el formulario de punteros
        $('#formAgregarPuntero').on('keydown', 'input', function(e) {
            if (e.key === "Enter") {
                e.preventDefault();
                let inputs = $('#formAgregarPuntero').find('input:visible');
                let index = inputs.index(this);

                if (index === inputs.length - 1) {
                    // Último campo → enviar formulario
                    $('#formAgregarPuntero').submit();
                } else {
                    // pasar al siguiente campo
                    inputs.eq(index + 1).focus();
                }
            }
        });
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

                // Si es el último campo → guardar
                if (index === inputs.length - 1) {

                    guardarDirigenteAjax();

                } else {

                    // pasar al siguiente campo
                    inputs.eq(index + 1).focus();

                }

            }

        });
        $('#equipo_id').select2({
            width: '100%'
        });
        $('#dirigentes-table').DataTable({
            destroy: true,
            responsive: true,
            language: {
                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
            }
        });

        // Abrir modal y asignar equipo filtrado
        $('#btnAgregarDirigente').on('click', function() {
            let equipoId = $('#equipo_id').val();
            $('input[name="id_equipo"]').val(equipoId);
            $('#formAgregarDirigente')[0].reset();
            limpiarErrores();
            $('#modalAgregarDirigente').modal('show');
        });

        // Cambiar el tipo de button para evitar submit normal
        $('#formAgregarDirigente button[type="submit"]').attr('type', 'button');

        // Guardar con AJAX
        $('#formAgregarDirigente button[type="button"]').on('click', function() {
            guardarDirigenteAjax();
        });
    });

    function guardarDirigenteAjax() {
        let btnGuardar = $('#formAgregarDirigente button[type="button"]');
        btnGuardar.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Guardando...');

        limpiarErrores();

        let formData = {
            cedula: $('input[name="cedula"]').val(),
            nombre: $('input[name="nombre"]').val(),
            telefono: $('input[name="telefono"]').val(),
            barrio: $('input[name="barrio"]').val(),
            id_equipo: $('input[name="id_equipo"]').val(),
            sistema_id: $('#sistema_id').val(),
            _token: '{{ csrf_token() }}'
        };

        $.ajax({
            url: '{{ route('dirigentes.store.ajax') }}',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                $('#modalAgregarDirigente').modal('hide');

                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: response.message,
                    timer: 2000,
                    showConfirmButton: false
                });

                // Recargar la lista usando la función que ya funciona
                filtrarDirigentes();

                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');
            },
            error: function(xhr) {
                btnGuardar.prop('disabled', false).html('<i class="fas fa-save"></i> Guardar');

                if (xhr.status === 422) {
                    // Error de validación
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: xhr.responseJSON.message
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
        $('.text-danger').remove();
        $('.is-invalid').removeClass('is-invalid');
    }

    // Mantén TODAS las demás funciones igual (filtrarDirigentes, abrirModalPunteros, etc.)
    // No modifiques nada más

    function filtrarDirigentes() {
        let equipoId = $('#equipo_id').val();
        let sistemaId = $('#sistema_id').val();
        let url = `{{ url('/') }}/sistemas/${sistemaId}/dirigentes?equipo_id=${equipoId}`;
        $("#contenidoDirigentes").html(
            '<div class="text-center p-4"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

        $.get(url, function(data) {
            $("#contenidoDirigentes").html(data);
        }).fail(function(xhr) {
            console.log(xhr.responseText);
            $("#contenidoDirigentes").html('<div class="alert alert-danger">Error cargando dirigentes</div>');
        });
    }

    function abrirModalPunteros(dirigenteId, nombreDirigente) {
        // 1. Primero establecer el ID en el hidden (¡esto es lo importante!)
        $('#puntero_id_dirigente').val(dirigenteId);

        // 2. Establecer el equipo si es necesario
        let equipoId = $('#equipo_id').val();
        if (equipoId) {
            $('#puntero_id_equipo').val(equipoId);
        }

        // 3. Cambiar el título
        $('#modalPunteros .modal-title').text('Punteros del dirigente: ' + nombreDirigente);

        // 4. Limpiar el formulario (pero el hidden ya tiene el valor)
        $('#formAgregarPuntero')[0].reset();
        // Restaurar el valor del hidden porque reset lo borra
        $('#puntero_id_dirigente').val(dirigenteId);
        if (equipoId) $('#puntero_id_equipo').val(equipoId);

        // 5. Abrir el modal
        $('#modalPunteros').modal('show');

        // 6. Cargar los punteros
        cargarPunteros(dirigenteId);

        // 7. Enfocar el campo cédula cuando se abra
        $('#modalPunteros').off('shown.bs.modal').on('shown.bs.modal', function() {
            $('#puntero_cedula').focus();
        });
    }
</script>
