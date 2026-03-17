@extends('adminlte::page')

@section('title', 'Distritos')

@section('content_header')
    <h1 class="m-0 ">
        <i class="fas fa-map-marker-alt text-primary"></i> Distritos
    </h1>
@stop

@section('content')

    {{-- BUSCADOR DE DISTRITOS --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorDistrito" class="form-control" placeholder="Buscar distrito por nombre...">
            </div>
        </div>
    </div>

    {{-- LISTA DISTRITOS --}}
    <div class="row" id="listaDistritos">
        @foreach ($totalesDistritos as $distrito => $totales)
            <div class="col-md-3 mb-3">
                <div class="card distrito-card h-100 shadow-sm border-primary"
                    style="cursor:pointer; transition: transform 0.2s;"
                    data-ciudad-id="{{ $totales['id_ciudad_electoral'] }}" data-distrito="{{ $distrito }}">
                    <div class="card-body text-center">
                        {{-- Icono y nombre del distrito --}}
                        <div class="row mb-2 justify-content-center align-items-center">
                            <div class="col-12">
                                <h5 class="card-title font-weight-bold">
                                    <i class="fas fa-map-marker-alt fa-2x text-primary"></i> {{ $distrito }}
                                </h5>
                            </div>
                        </div>

                        {{-- DIRIGENTES --}}
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-user-tie text-warning"></i>
                                    <strong>Dirigentes:</strong>
                                    <span class="badge badge-warning badge-pill">
                                        {{ number_format($totales['dirigentes'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        {{-- PUNTEROS --}}
                        <div class="row mb-1 justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-user-friends text-success"></i>
                                    <strong>Punteros:</strong>
                                    <span class="badge badge-success badge-pill">
                                        {{ number_format($totales['punteros'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>

                        {{-- VOTANTES --}}
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <p class="mb-0">
                                    <i class="fas fa-vote-yea text-primary"></i>
                                    <strong>Votantes:</strong>
                                    <span class="badge badge-primary badge-pill">
                                        {{ number_format($totales['votantes'], 0, '', '.') }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- MODAL SISTEMAS --}}
    <div class="modal fade" id="modalSistemas" tabindex="-1" role="dialog" aria-labelledby="modalSistemasTitle"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-primary text-white">
                    <h5 class="modal-title" id="modalSistemasTitle">
                        <i class="fas fa-map-marker-alt"></i> Sistemas del Distrito
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="modalSistemasBody">
                    <div class="text-center text-muted py-5">
                        <i class="fas fa-hand-pointer fa-3x mb-3"></i>
                        <p>Selecciona un distrito para ver sus sistemas</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL DIRIGENTES --}}
    <div class="modal fade" id="modalDirigentes" tabindex="-1" role="dialog" aria-labelledby="tituloDirigentes"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-warning">
                    <h5 class="modal-title" id="tituloDirigentes">
                        <i class="fas fa-users"></i> Dirigentes
                    </h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoDirigentes">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando dirigentes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL PUNTEROS (lista completa) --}}
    <div class="modal fade" id="modalPunterosLista" tabindex="-1" role="dialog" aria-labelledby="tituloPunterosLista"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tituloPunterosLista">
                        <i class="fas fa-users"></i> Punteros
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="contenidoPunteros">
                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Selecciona una opción para ver los punteros...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para punteros -->
    <div class="modal fade" id="modalPunteros" tabindex="-1" role="dialog">
        <div class="modal-dialog modal-lg" role="document">
            <div class="modal-content">

                <!-- Header del modal -->
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Punteros del dirigente</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>

                <!-- Body del modal -->
                <div class="modal-body">

                    <!-- Formulario para agregar puntero -->
                    <form id="formAgregarPuntero" class="mb-3">
                        @csrf
                        <!-- Hidden del id_dirigente - se llena cuando se abre el modal -->
                        <input type="hidden" name="id_dirigente" id="puntero_id_dirigente" value="">

                        <!-- El id_equipo se puede obtener del dirigente o del contexto -->
                        <input type="hidden" name="id_equipo" id="puntero_id_equipo" value="">

                        <div class="form-row">
                            <div class="form-group col-md-3">
                                <label>Cédula</label>
                                <input type="text" name="cedula" id="puntero_cedula" class="form-control" required>
                            </div>
                            <div class="form-group col-md-9">
                                <label>Nombre</label>
                                <input type="text" name="nombre" id="puntero_nombre" class="form-control" required>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-4">
                                <label>Teléfono</label>
                                <input type="text" name="telefono" id="puntero_telefono" class="form-control">
                            </div>
                            <div class="form-group col-md-8">
                                <label>Barrio</label>
                                <input type="text" name="barrio" id="puntero_barrio" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary" id="btnGuardarPuntero">
                            <i class="fas fa-save"></i> Guardar Puntero
                        </button>
                    </form>

                    <hr>

                    <!-- Tabla de punteros existentes -->
                    <table id="punteros-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Teléfono</th>
                                <th>Barrio</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    {{-- MODAL VOTANTES --}}
    <div class="modal fade" id="modalVotantes" tabindex="-1" role="dialog" aria-labelledby="tituloVotantes"
        aria-hidden="true">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">

                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title" id="tituloVotantes">
                        <i class="fas fa-users"></i> Votantes del Puntero
                    </h5>
                    <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                 <div class=" p-4">
                        <form id="formAgregarVotante" method="POST" >
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
                                    <input name="direccion" id="direccion" class="form-control" placeholder="Dirección"
                                        readonly>
                                </div>
                                <div class="col-md-2">
                                    <input name="mesa" id="mesa" class="form-control" placeholder="Mesa"
                                        readonly>
                                </div>
                                <div class="col-md-2">
                                    <input name="orden" id="orden" class="form-control" placeholder="Orden"
                                        readonly>
                                </div>
                                <div class="col-md-4">
                                    <input name="partido" id="partido" class="form-control" placeholder="Partido"
                                        readonly>
                                </div>
                            </div>

                            <div class="row mb-2">
                                <div class="col-md-4">
                                    <input name="escuela" id="escuela" class="form-control" placeholder="Escuela"
                                        readonly>
                                </div>
                                <div class="col-md-4">
                                    <input name="ciudad" id="ciudad" class="form-control" placeholder="Ciudad"
                                        readonly>
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
                    </div>

                <div class="modal-body" id="contenidoVotantes">
                   

                    <div class="text-center p-4">
                        <i class="fas fa-spinner fa-spin fa-2x"></i>
                        <p class="mt-2">Cargando votantes...</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('css')
    <style>
        .distrito-card:hover {
            transform: scale(1.03) !important;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
        }

        .modal-xl {
            max-width: 90%;
        }

        @media (min-width: 1200px) {
            .modal-xl {
                max-width: 1140px;
            }
        }
    </style>
@stop

@section('js')
    <script>
        // Guardar puntero por AJAX
        $(document).ready(function() {
            $('#formAgregarPuntero').on('submit', function(e) {
                e.preventDefault();

                // Verificar que el id_dirigente esté presente
                let dirigenteId = $('#puntero_id_dirigente').val();
                if (!dirigenteId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se ha seleccionado un dirigente',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                let formData = $(this).serialize();
                let submitBtn = $('#btnGuardarPuntero');

                console.log('Datos a enviar:', formData); // Para depuración

                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: "{{ route('puntero.store.ajax') }}",
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Limpiar formulario pero mantener el id_dirigente
                            $('#puntero_cedula, #puntero_nombre, #puntero_telefono, #puntero_barrio')
                                .val('');
                            $('#puntero_cedula').focus();

                            // Recargar la tabla de punteros con un pequeño retraso
                            setTimeout(function() {
                                cargarPunteros(dirigenteId);
                            }, 100);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error al guardar el puntero';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '<ul>';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += '<li>' + value + '</li>';
                            });
                            errorMessage += '</ul>';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage,
                            confirmButtonColor: '#dc3545'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Guardar Puntero');
                    }
                });
            });
            $('#formAgregarVotante').on('submit', function(e) {
                e.preventDefault();

                // Verificar que el id_dirigente esté presente
                let dirigenteId = $('#puntero_id_dirigente').val();
                if (!dirigenteId) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se ha seleccionado un dirigente',
                        confirmButtonColor: '#dc3545'
                    });
                    return;
                }

                let formData = $(this).serialize();
                let submitBtn = $('#btnGuardarPuntero');

                console.log('Datos a enviar:', formData); // Para depuración

                submitBtn.prop('disabled', true).html(
                    '<i class="fas fa-spinner fa-spin"></i> Guardando...');

                $.ajax({
                    url: "{{ route('puntero.store.ajax') }}",
                    type: 'POST',
                    data: formData,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name="_token"]').val()
                    },
                    success: function(response) {
                        if (response.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Éxito',
                                text: response.message,
                                timer: 1500,
                                showConfirmButton: false
                            });

                            // Limpiar formulario pero mantener el id_dirigente
                            $('#puntero_cedula, #puntero_nombre, #puntero_telefono, #puntero_barrio')
                                .val('');
                            $('#puntero_cedula').focus();

                            // Recargar la tabla de punteros con un pequeño retraso
                            setTimeout(function() {
                                cargarPunteros(dirigenteId);
                            }, 100);
                        }
                    },
                    error: function(xhr) {
                        let errorMessage = 'Error al guardar el puntero';

                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            errorMessage = '<ul>';
                            $.each(xhr.responseJSON.errors, function(key, value) {
                                errorMessage += '<li>' + value + '</li>';
                            });
                            errorMessage += '</ul>';
                        }

                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            html: errorMessage,
                            confirmButtonColor: '#dc3545'
                        });
                    },
                    complete: function() {
                        submitBtn.prop('disabled', false).html(
                            '<i class="fas fa-save"></i> Guardar Puntero');
                    }
                });
            });
        });

        // Función para actualizar el contador de punteros en la tabla de dirigentes
        function actualizarContadorPunteros(dirigenteId) {
            // Verificar si existe la tabla de dirigentes (puede no estar en todas las vistas)
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dirigentes-table')) {
                // Hacer una petición para obtener el nuevo conteo
                $.get(`/dirigente/${dirigenteId}/punteros/count`, function(response) {
                    // Buscar la fila que contiene el botón de este dirigente
                    let button = $(`button[onclick*="abrirModalPunteros(${dirigenteId},"]`);
                    if (button.length) {
                        button.closest('tr').find('td:eq(6) .badge').text(response.count);
                    }
                }).fail(function() {
                    console.log('No se pudo actualizar el contador');
                });
            }
        }


        // Mostrar mensaje de éxito si existe
        const successAlert = @json(session('success'));
        if (successAlert) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: successAlert,
                timer: 1800,
                showConfirmButton: false
            });
        }

        // Efecto hover en tarjetas
        document.querySelectorAll('.distrito-card').forEach(card => {
            card.addEventListener('mouseover', () => card.style.transform = 'scale(1.03)');
            card.addEventListener('mouseout', () => card.style.transform = 'scale(1)');
        });

        // Buscador de distritos
        const buscador = document.getElementById('buscadorDistrito');
        if (buscador) {
            buscador.addEventListener('keyup', function() {
                let query = this.value.toLowerCase().trim();
                document.querySelectorAll('.distrito-card').forEach(card => {
                    let distrito = card.dataset.distrito.toLowerCase();
                    card.closest('.col-md-3').style.display =
                        distrito.includes(query) ? 'block' : 'none';
                });

                // Mostrar mensaje si no hay resultados
                const visibleCards = document.querySelectorAll('.distrito-card:visible').length;
                const sinResultados = document.getElementById('sinResultadosDistritos');

                if (visibleCards === 0) {
                    if (!sinResultados) {
                        const mensaje = document.createElement('div');
                        mensaje.id = 'sinResultadosDistritos';
                        mensaje.className = 'col-12 text-center text-muted py-5';
                        mensaje.innerHTML = `
                            <i class="fas fa-search fa-3x mb-3"></i>
                            <p>No se encontraron distritos que coincidan con "${query}"</p>
                        `;
                        document.getElementById('listaDistritos').appendChild(mensaje);
                    }
                } else if (sinResultados) {
                    sinResultados.remove();
                }
            });
        }

        // Cargar sistemas al hacer clic en un distrito - VERSIÓN CORREGIDA
        document.querySelectorAll('.distrito-card').forEach(card => {
            card.addEventListener('click', function() {
                let ciudadId = this.dataset.ciudadId;
                let distritoNombre = this.dataset.distrito;
                let modalBody = document.getElementById('modalSistemasBody');
                let modalTitle = document.querySelector('#modalSistemas .modal-title');

                // Actualizar título del modal
                if (modalTitle) {
                    modalTitle.innerHTML =
                        `<i class="fas fa-map-marker-alt"></i> Sistemas del Distrito: ${distritoNombre}`;
                }

                // Mostrar spinner de carga
                modalBody.innerHTML = `
                    <div class="text-center text-muted py-5">
                        <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p>Cargando sistemas del distrito ${distritoNombre}...</p>
                    </div>
                `;

                // ABRIR EL MODAL PRIMERO (esto es clave)
                $('#modalSistemas').modal('show');

                // Luego cargar los datos
                let url = `{{ url('/') }}/distritos/${ciudadId}/sistemas`;

                fetch(url, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'text/html'
                        }
                    })
                    .then(res => {
                        if (!res.ok) throw new Error('Error en la respuesta');
                        return res.text();
                    })
                    .then(html => {
                        modalBody.innerHTML = html;

                        // Inicializar DataTable si existe en el contenido cargado
                        setTimeout(function() {
                            if ($.fn.DataTable && $('#sistemas-table').length) {
                                if ($.fn.DataTable.isDataTable('#sistemas-table')) {
                                    $('#sistemas-table').DataTable().destroy();
                                }

                                // Inicializar DataTable de sistemas con buscador y botón imprimir
                                $('#sistemas-table').DataTable({
                                    dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                        "<'row'<'col-sm-12'tr>>" +
                                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",

                                    responsive: true,

                                    language: {
                                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                                        search: "Buscar sistema:",
                                        searchPlaceholder: "Nombre, ubicación..."
                                    },

                                    buttons: [{
                                        extend: 'print',
                                        text: '<i class="fas fa-print"></i> Imprimir',
                                        className: 'btn btn-secondary',
                                        autoPrint: true,
                                        title: 'Sistemas del Distrito',
                                        customize: function(win) {
                                            // Personalizar el documento impreso
                                            $(win.document.body).find(
                                                'table').addClass(
                                                'table table-bordered');
                                            $(win.document.body).find('h1')
                                                .css('text-align',
                                                    'center');
                                        }
                                    }],

                                    pageLength: 10,
                                    lengthMenu: [
                                        [10, 25, 50, -1],
                                        [10, 25, 50, "Todos"]
                                    ]
                                });
                            }
                        }, 100);
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        modalBody.innerHTML = `
                            <div class="text-center text-danger py-5">
                                <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                                <p>No se pudieron cargar los sistemas. Intente nuevamente.</p>
                                <p class="small text-muted">Error: ${error.message}</p>
                                <button class="btn btn-sm btn-primary" onclick="cargarSistemasManual(${ciudadId}, '${distritoNombre}')">
                                    <i class="fas fa-sync"></i> Reintentar
                                </button>
                            </div>
                        `;
                    });
            });
        });

        // Función de respaldo para reintentar carga
        function cargarSistemasManual(ciudadId, distritoNombre) {
            let modalBody = document.getElementById('modalSistemasBody');

            modalBody.innerHTML = `
                <div class="text-center text-muted py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p>Reintentando carga de sistemas...</p>
                </div>
            `;

            let url = `{{ url('/') }}/distritos/${ciudadId}/sistemas`;

            fetch(url, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'text/html'
                    }
                })
                .then(res => {
                    if (!res.ok) throw new Error('Error en la respuesta');
                    return res.text();
                })
                .then(html => {
                    modalBody.innerHTML = html;
                })
                .catch(error => {
                    modalBody.innerHTML = `
                        <div class="text-center text-danger py-5">
                            <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                            <p>Error persistente. Verifica la conexión.</p>
                        </div>
                    `;
                });
        }

        // Cargar dirigentes al hacer clic en el botón
        $(document).on("click", ".btn-dirigentes", function() {
            let sistema = $(this).data("sistema");
            let nombre = $(this).data("nombre");

            $("#tituloDirigentes").html(
                '<i class="fas fa-users"></i> Dirigentes del Sistema - ' + nombre
            );

            $("#contenidoDirigentes").html(
                '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando...</p></div>'
            );

            $("#modalDirigentes").modal("show");

            let url = `{{ url('/') }}/sistemas/${sistema}/dirigentes`;

            $.get(url, function(data) {
                $("#contenidoDirigentes").html(data);

                // Reinicializar DataTable si existe en el contenido cargado
                setTimeout(function() {
                    if ($.fn.DataTable && $('#dirigentes-table').length) {
                        if ($.fn.DataTable.isDataTable('#dirigentes-table')) {
                            $('#dirigentes-table').DataTable().destroy();
                        }

                        $('#dirigentes-table').DataTable({
                            dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                "<'row'<'col-sm-12'tr>>" +
                                "<'row'<'col-sm-5'i><'col-sm-7'p>>",

                            responsive: true,

                            language: {
                                url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json',
                                search: "Buscar dirigente:",
                                searchPlaceholder: "Nombre, cédula o teléfono..."
                            },

                            buttons: [{
                                extend: 'print',
                                text: '<i class="fas fa-print"></i> Imprimir',
                                className: 'btn btn-secondary',
                                autoPrint: true,
                                customize: function(win) {
                                    // Personalizar el documento impreso
                                    $(win.document.body).find('table').addClass(
                                        'table table-bordered');
                                }
                            }],

                            pageLength: 10,
                            lengthMenu: [
                                [5, 10, 25, 50, -1],
                                [5, 10, 25, 50, "Todos"]
                            ],
                            columnDefs: [{
                                orderable: false,
                                targets: [8]
                            }]
                        });
                    }
                }, 100);
            }).fail(function(xhr) {
                console.log(xhr.responseText);
                $("#contenidoDirigentes").html(
                    '<div class="alert alert-danger text-center p-4">' +
                    '<i class="fas fa-exclamation-circle fa-2x mb-3"></i>' +
                    '<p>Error cargando dirigentes. Intente nuevamente.</p>' +
                    '</div>'
                );
            });
        });

        // Función para abrir modal de punteros
        function abrirModalPunteros(dirigenteId, nombreDirigente) {
            // Abrir modal
            $('#modalPunteros').modal('show');

            // Establecer el ID del dirigente en el campo hidden
            $('#puntero_id_dirigente').val(dirigenteId);

            // También podemos establecer el equipo si está disponible
            let equipoId = $('#equipo_id').val(); // Si existe un select de equipos
            if (equipoId) {
                $('#puntero_id_equipo').val(equipoId);
            }

            // Cambiar el título del modal
            $('#modalPunteros .modal-title')
                .text('Punteros del dirigente: ' + nombreDirigente);

            // Limpiar formulario
            $('#formAgregarPuntero')[0].reset();

            // Enfocar el campo cédula cuando se abra el modal
            $('#modalPunteros').off('shown.bs.modal').on('shown.bs.modal', function() {
                $('#puntero_cedula').trigger('focus');
            });

            // Cargar punteros existentes
            cargarPunteros(dirigenteId);
        }

        // Función para cargar punteros
        // Función para cargar punteros
        function cargarPunteros(dirigenteId) {
            console.log('Cargando punteros para dirigente:', dirigenteId);

            let tbody = $('#punteros-table tbody');
            tbody.html('<tr><td colspan="6" class="text-center">Cargando punteros...</td></tr>');

            // Agregar un parámetro timestamp para evitar cache
            let timestamp = new Date().getTime();
            let url = "{{ url('dirigente') }}/" + dirigenteId + "/punteros?t=" + timestamp;

            $.get(url, function(data) {
                console.log('Datos recibidos:', data);

                tbody.empty();

                if (data.length === 0) {
                    tbody.html('<tr><td colspan="6" class="text-center">No hay punteros registrados</td></tr>');

                    // Destruir DataTable si existe
                    if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) {
                        $('#punteros-table').DataTable().destroy();
                        $('#punteros-table').empty(); // Limpiar completamente
                    }
                    return;
                }

                // Construir el HTML de la tabla
                let html = '';
                data.forEach(function(puntero, index) {
                    html += `
                <tr>
                    <td>${index + 1}</td>
                    <td>${puntero.cedula}</td>
                    <td>${puntero.nombre}</td>
                    <td>${puntero.telefono ?? ''}</td>
                    <td>${puntero.barrio ?? ''}</td>
                    <td>
                        <button class="btn btn-danger btn-sm" onclick="eliminarPuntero(${puntero.id}, ${dirigenteId})">
                            <i class="fas fa-trash"></i>
                        </button>
                    </td>
                </tr>
            `;
                });

                tbody.html(html);

                // Destruir DataTable anterior si existe
                if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) {
                    $('#punteros-table').DataTable().destroy();
                    $('#punteros-table').empty(); // Limpiar
                    tbody.html(html); // Volver a poner el HTML
                }

                // Inicializar DataTable
                $('#punteros-table').DataTable({
                    responsive: true,
                    language: {
                        url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    pageLength: 5,
                    destroy: true, // Forzar destrucción si existe
                    retrieve: false // No recuperar instancia anterior
                });

                console.log('DataTable reinicializado');
            }).fail(function(error) {
                console.error('Error cargando punteros:', error);
                tbody.html(
                    '<tr><td colspan="6" class="text-center text-danger">Error al cargar punteros</td></tr>');
            });
        }

        // Función para filtrar punteros por equipo
        function filtrarPunteros() {
            let dirigenteId = $('#puntero_id_dirigente').val();
            let equipoId = $('#equipo_punteros').val();

            if (dirigenteId) {
                cargarPunteros(dirigenteId, equipoId);
            }
        }

        // Función para eliminar puntero
        // Función para eliminar puntero
        function eliminarPuntero(punteroId, dirigenteId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: "{{ route('puntero.destroy.ajax') }}",
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}',
                            id: punteroId
                        },
                        success: function(response) {
                            console.log('Respuesta éxito:', response);

                            if (response.success) {
                                // Cerrar el loading
                                Swal.close();

                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: 'El puntero ha sido eliminado',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // ✅ SIMPLEMENTE LLAMAR A LA FUNCIÓN EXISTENTE
                                console.log('Recargando punteros para dirigente:', dirigenteId);
                                cargarPunteros(dirigenteId);
                            }
                        },
                        error: function(xhr) {
                            console.error('Error en eliminación:', xhr);

                            Swal.close();

                            let mensaje = 'No se pudo eliminar el puntero';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                mensaje = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: mensaje
                            });
                        }
                    });
                }
            });
        }

        // Función para eliminar dirigente
        function eliminarDirigente(dirigenteId) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción no se puede revertir. Se eliminarán también sus punteros y votantes.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Mostrar loading
                    Swal.fire({
                        title: 'Eliminando...',
                        text: 'Por favor espera',
                        allowOutsideClick: false,
                        allowEscapeKey: false,
                        showConfirmButton: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    let url = `{{ url('/') }}/dirigentes/ajax/${dirigenteId}`;

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.fire({
                                icon: 'success',
                                title: '¡Eliminado!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });

                            // Recargar la lista usando la función que ya funciona
                            if (typeof filtrarDirigentes === 'function') {
                                filtrarDirigentes();
                            } else {
                                location.reload();
                            }
                        },
                        error: function(xhr) {
                            let mensaje = 'Error al eliminar el dirigente';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                mensaje = xhr.responseJSON.message;
                            }

                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: mensaje
                            });
                        }
                    });
                }
            });
        }

        // Exponer funciones globalmente
        window.abrirModalPunteros = abrirModalPunteros;
        window.eliminarDirigente = eliminarDirigente;
        window.filtrarPunteros = filtrarPunteros;
        window.eliminarPuntero = eliminarPuntero;
        window.cargarSistemasManual = cargarSistemasManual;

        // Limpiar DataTables cuando se cierran los modales
        $('#modalSistemas').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#sistemas-table')) {
                $('#sistemas-table').DataTable().destroy();
            }
        });

        $('#modalDirigentes').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#dirigentes-table')) {
                $('#dirigentes-table').DataTable().destroy();
            }
        });

        $('#modalPunteros').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-table')) {
                $('#punteros-table').DataTable().destroy();
            }
        });

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
        // Función para abrir modal de punteros (lista completa)
        function abrirModalPunterosLista(sistemaId, nombreSistema) {
            let modalTitle = document.querySelector('#modalPunterosLista .modal-title');
            if (modalTitle) {
                modalTitle.innerHTML = `<i class="fas fa-users"></i> Punteros del Sistema: ${nombreSistema}`;
            }

            $("#contenidoPunteros").html(
                '<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x"></i><p class="mt-2">Cargando punteros...</p></div>'
            );

            $("#modalPunterosLista").modal("show");

            let url = `{{ url('/') }}/sistemas/${sistemaId}/punteros`;

            $.get(url, function(data) {
                $("#contenidoPunteros").html(data);
            }).fail(function(xhr) {
                console.log(xhr.responseText);
                $("#contenidoPunteros").html(
                    '<div class="alert alert-danger text-center p-4">' +
                    '<i class="fas fa-exclamation-circle fa-2x mb-3"></i>' +
                    '<p>Error cargando punteros. Intente nuevamente.</p>' +
                    '</div>'
                );
            });
        }
        $('#modalPunterosLista').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#punteros-lista-table')) {
                $('#punteros-lista-table').DataTable().destroy();
            }
        });
        // Exponer la función globalmente
        window.abrirModalPunterosLista = abrirModalPunterosLista;
        window.abrirModalVotantes = function(punteroId, nombre) {
            console.log('Abriendo modal para:', punteroId, nombre);

            // Ejemplo básico (ajustá a tu lógica real)
            $('#modalVotantes').modal('show');

            // Si cargás datos por AJAX:
            $('#contenidoVotantes').html(
                '<div class="text-center p-3"><i class="fas fa-spinner fa-spin"></i> Cargando...</div>');

            let url = `{{ url('/') }}/punteros/${punteroId}/votantes`;

            $.get(url, function(data) {
                $('#contenidoVotantes').html(data);
            });
        };
        // Función para cargar votantes de un puntero específico
        function cargarVotantes(idPuntero, nombrePuntero = '') {
            console.log('Cargando votantes para puntero:', idPuntero);

            // Actualizar título del modal
            $('#tituloVotantes').html(`
        <i class="fas fa-users"></i> Votantes del Puntero: ${nombrePuntero}
    `);

            // Mostrar modal con spinner
            $('#modalVotantes').modal('show');

            // Mostrar loading en el body
            $('#contenidoVotantes').html(`
        <div class="text-center p-4">
            <div class="spinner-border text-success mb-3" role="status" style="width: 3rem; height: 3rem;">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2">Cargando votantes del puntero...</p>
        </div>
    `);

            // Construir URL usando la ruta de Laravel
            let url = `{{ url('puntero') }}/${idPuntero}/votantes`;

            // Petición AJAX
            $.ajax({
                url: url,
                type: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                success: function(data) {
                    console.log('Votantes recibidos:', data);

                    let contenido = `
                <div class="table-responsive">
                    <table id="votantes-table" class="table table-striped table-bordered">
                        <thead>
                            <tr>
                                <th style="width: 5%">#</th>
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Escuela</th>
                                <th>Tipo Votante</th>
                                <th>Mesa</th>
                                <th style="width: 10%">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

                    if (data.length === 0) {
                        contenido += `
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">
                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                            <p>No hay votantes registrados para este puntero</p>
                        </td>
                    </tr>
                `;
                    } else {
                        data.forEach((v, i) => {
                            contenido += `
                        <tr>
                            <td>${i + 1}</td>
                            <td>${v.cedula}</td>
                            <td>${v.nombre ?? ''}</td>
                            <td>${v.escuela ?? ''}</td>
                            <td>
                                <span class="badge badge-${v.tipo_votante === 'RESERVADO' ? 'warning' : 'info'}">
                                    ${v.tipo_votante ?? ''}
                                </span>
                            </td>
                            <td>${v.mesa ?? ''}</td>
                            <td class="text-center">
                                <button class="btn btn-danger btn-sm" 
                                    onclick="eliminarVotante(${v.id}, ${idPuntero})"
                                    title="Eliminar votante">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    `;
                        });
                    }

                    contenido += `
                        </tbody>
                    </table>
                </div>
            `;

                    $('#contenidoVotantes').html(contenido);

                    // Inicializar DataTable después de un pequeño delay
                    setTimeout(function() {
                        if ($.fn.DataTable && $('#votantes-table').length) {
                            if ($.fn.DataTable.isDataTable('#votantes-table')) {
                                $('#votantes-table').DataTable().destroy();
                            }

                            $('#votantes-table').DataTable({
                                responsive: true,
                                dom: "<'row'<'col-md-4'l><'col-md-4'f><'col-md-4 text-right'B>>" +
                                    "<'row'<'col-sm-12'tr>>" +
                                    "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                                buttons: [{
                                        extend: 'excelHtml5',
                                        className: 'btn btn-success btn-sm',
                                        text: '<i class="fas fa-file-excel"></i> Excel',
                                        title: 'Votantes del Puntero',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5]
                                        }
                                    },
                                    {
                                        extend: 'pdfHtml5',
                                        className: 'btn btn-danger btn-sm',
                                        text: '<i class="fas fa-file-pdf"></i> PDF',
                                        title: 'Votantes del Puntero',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5]
                                        }
                                    },
                                    {
                                        extend: 'print',
                                        className: 'btn btn-secondary btn-sm',
                                        text: '<i class="fas fa-print"></i> Imprimir',
                                        exportOptions: {
                                            columns: [0, 1, 2, 3, 4, 5]
                                        },
                                        customize: function(win) {
                                            $(win.document.body).find('table').addClass(
                                                'table table-bordered');
                                            $(win.document.body).find('h1').css(
                                                'text-align', 'center');
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
                    }, 100);
                },
                error: function(xhr, status, error) {
                    console.error('Error cargando votantes:', error);
                    console.error('Respuesta:', xhr.responseText);

                    $('#contenidoVotantes').html(`
                <div class="alert alert-danger text-center p-4">
                    <i class="fas fa-exclamation-circle fa-3x mb-3"></i>
                    <h5>Error al cargar los votantes</h5>
                    <p class="mb-0">${xhr.responseJSON?.message || 'Intente nuevamente más tarde'}</p>
                    <button class="btn btn-sm btn-outline-danger mt-3" onclick="cargarVotantes(${idPuntero}, '${nombrePuntero}')">
                        <i class="fas fa-sync"></i> Reintentar
                    </button>
                </div>
            `);
                }
            });
        }

        // Función para eliminar votante
        function eliminarVotante(votanteId, punteroId) {
            Swal.fire({
                title: '¿Eliminar votante?',
                text: 'Esta acción no se puede deshacer',
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
                        url: `{{ url('votante') }}/${votanteId}`,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            Swal.close();

                            if (response.success) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Eliminado',
                                    text: 'El votante ha sido eliminado',
                                    timer: 1500,
                                    showConfirmButton: false
                                });

                                // Recargar la tabla de votantes
                                let nombrePuntero = $('#tituloVotantes').text().replace(
                                    'Votantes del Puntero: ', '');
                                cargarVotantes(punteroId, nombrePuntero);
                            }
                        },
                        error: function(xhr) {
                            Swal.close();
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: xhr.responseJSON?.message ||
                                    'No se pudo eliminar el votante'
                            });
                        }
                    });
                }
            });
        }

        // Limpiar DataTable cuando se cierra el modal
        $('#modalVotantes').on('hidden.bs.modal', function() {
            if ($.fn.DataTable && $.fn.DataTable.isDataTable('#votantes-table')) {
                $('#votantes-table').DataTable().destroy();
            }
        });

        // Exponer funciones globalmente
        window.cargarVotantes = cargarVotantes;
        window.eliminarVotante = eliminarVotante;
    </script>
@stop
