@extends('adminlte::page')

@section('title', 'Cargar Votos - ' . $miembro->nombre)

@section('content_header')
    <div class="row">
        <div class="col-12">
            <h3 class="m-0">
                <i class="fas fa-vote-yea"></i> Cargar Votos - {{ $miembro->nombre }}
            </h3>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Información del miembro -->
            <div class="card">
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-3">
                            <small class="text-muted">Miembro:</small><br>
                            <strong>{{ $miembro->nombre }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Cédula:</small><br>
                            <strong>{{ $miembro->cedula }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Local:</small><br>
                            <strong>{{ $equipo->descripcion ?? 'N/A' }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted">Total Votos:</small><br>
                            <strong><span id="totalVotos"
                                    class="badge badge-success">{{ $votosCargados ?? 0 }}</span></strong>
                        </div>
                    </div>
                </div>
            </div>

            <!-- BÚSQUEDA COMBINADA: Cédula (izquierda) + Mesa/Orden (derecha) -->
            <div class="card">
                <div class="card-header py-2 bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-search"></i> Buscar Votante
                    </h5>
                </div>
                <div class="card-body py-3">
                    <div class="row">
                        <div class="col-md-6 col-12 mb-2 mb-md-0">
                            <label class="small mb-1"><i class="fas fa-id-card"></i> Por Cédula</label>
                            <div class="input-group input-group-sm">
                                <input type="text" class="form-control" id="buscarCedula"
                                    placeholder="Ingrese número de cédula">
                                <div class="input-group-append">
                                    <button class="btn btn-primary" type="button" id="btnBuscarCedula">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-secondary" type="button" id="btnLimpiarCedula">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 col-12">
                            <label class="small mb-1"><i class="fas fa-table"></i> Por Mesa y Orden</label>
                            <div class="input-group input-group-sm">
                                <select class="form-control" id="mesaSelect" style="flex: 0 0 110px;">
                                    <option value="">Mesa</option>
                                    @for ($i = 1; $i <= $cantidadMesas; $i++)
                                        <option value="{{ $i }}">{{ $i }}</option>
                                    @endfor
                                </select>
                                <input type="number" class="form-control" id="ordenInput"
                                    placeholder="Orden">
                                <div class="input-group-append">
                                    <button class="btn btn-info" type="button" id="btnBuscarMesaOrden">
                                        <i class="fas fa-search"></i>
                                    </button>
                                    <button class="btn btn-secondary" type="button" id="btnLimpiarMesaOrden">
                                        <i class="fas fa-eraser"></i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de búsqueda -->
            <div id="resultadoBusqueda" style="display: none;">
                <div class="card">
                    <div class="card-header py-2 bg-success text-white">
                        <h5 class="mb-0">
                            <i class="fas fa-user-check"></i> Datos del Votante
                        </h5>
                    </div>
                    <div class="card-body py-3">
                        <form id="formGuardarVoto">
                            @csrf
                            <input type="hidden" id="votante_cedula" name="cedula">
                            <input type="hidden" id="votante_nombres" name="nombres">
                            <input type="hidden" id="votante_apellidos" name="apellidos">
                            <input type="hidden" id="votante_localvotacion" name="localvotacion">
                            <input type="hidden" id="votante_distrito" name="distrito">
                            <input type="hidden" id="votante_mesa" name="mesa">
                            <input type="hidden" name="idmiembrodemesa" value="{{ $miembro->id }}">

                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="mb-0 small">Cédula:</label>
                                        <p class="form-control-static bg-light p-1 rounded mb-0" id="display_cedula"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-2">
                                        <label class="mb-0 small">Nombres Completos:</label>
                                        <p class="form-control-static bg-light p-1 rounded mb-0" id="display_nombres"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="mb-0 small">Local:</label>
                                        <p class="form-control-static bg-light p-1 rounded mb-0" id="display_local"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="mb-0 small">Distrito:</label>
                                        <p class="form-control-static bg-light p-1 rounded mb-0" id="display_distrito">
                                        </p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-2">
                                        <label class="mb-0 small">Mesa:</label>
                                        <p class="form-control-static bg-light p-1 rounded mb-0" id="display_mesa"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center mt-3">
                                <button type="submit" class="btn btn-success btn-sm">
                                    <i class="fas fa-save"></i> Confirmar Voto
                                </button>
                                <button type="button" class="btn btn-secondary btn-sm" id="btnNuevoVoto">
                                    <i class="fas fa-plus"></i> Nuevo
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loading" style="display: none;" class="text-center py-4">
                <div class="spinner-border spinner-border-sm text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <span class="ml-2">Buscando...</span>
            </div>

            <!-- TABLA DE VOTOS CARGADOS -->
            <div class="card mt-4">
                <div class="card-header py-2 bg-secondary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-list"></i> Votos Cargados
                    </h5>
                </div>
                <div class="card-body">
                    <table id="votos-table" class="table table-bordered table-striped table-sm">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Cédula</th>
                                <th>Nombres</th>
                                <th>Local</th>
                                <th>Distrito</th>
                                <th>Mesa</th>
                                <th>Fecha</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($votosCargadosLista ?? [] as $voto)
                                <tr id="voto-row-{{ $voto->id }}">
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $voto->cedula }}</td>
                                    <td>{{ $voto->nombres }} {{ $voto->apellidos }}</td>
                                    <td>{{ $voto->localvotacion }}</td>
                                    <td>{{ $voto->distrito }}</td>
                                    <td>{{ $voto->mesa }}</td>
                                    <td>{{ $voto->created_at ? $voto->created_at->format('d/m/Y H:i') : '' }}</td>
                                    <td class="text-center">
                                        <button class="btn btn-danger btn-sm"
                                            onclick="eliminarVoto({{ $voto->id }})">
                                            <i class="fas fa-trash"></i>
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
@stop

@push('css')
    <style>
        .main-sidebar, .main-header {
            display: none !important;
        }
        .content-wrapper {
            margin-left: 0 !important;
        }
        .form-control-static {
            font-size: 14px;
            border: 1px solid #e0e0e0;
        }

        .card {
            margin-bottom: 15px;
        }

        .card-header {
            padding: 8px 15px;
        }

        .card-body {
            padding: 12px 15px;
        }

        .btn-group-sm .btn {
            padding: 4px 12px;
            font-size: 12px;
        }

        .input-group-sm .form-control,
        .input-group-sm .input-group-text,
        .input-group-sm .btn {
            font-size: 12px;
            padding: 4px 8px;
        }

        .form-group label {
            font-size: 12px;
            color: #666;
        }

        .badge {
            font-size: 12px;
            padding: 3px 8px;
        }

        .table-sm th,
        .table-sm td {
            padding: 4px 8px;
            font-size: 12px;
        }

        .dataTables_wrapper .dataTables_length,
        .dataTables_wrapper .dataTables_filter,
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate {
            font-size: 12px;
        }
    </style>
@endpush

@push('js')
    <script>
        let votosDataTable;

        $(document).ready(function() {
            // Inicializar DataTable de votos
            inicializarTablaVotos();

            // Buscar por cédula
            $('#btnBuscarCedula').click(function() {
                const cedula = $('#buscarCedula').val().trim();
                if (!cedula) {
                    Swal.fire('Error', 'Ingrese un número de cédula', 'error');
                    return;
                }

                buscarVotante('{{ route('votos.buscar.cedula') }}', {
                    cedula: cedula,
                    miembro_id: {{ $miembro->id }}
                });
            });

            // Limpiar campo cédula
            $('#btnLimpiarCedula').click(function() {
                $('#buscarCedula').val('').focus();
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
            });

            // Buscar por mesa y orden
            $('#btnBuscarMesaOrden').click(function() {
                const mesa = $('#mesaSelect').val();
                const orden = $('#ordenInput').val();

                if (!mesa) {
                    Swal.fire('Error', 'Seleccione una mesa', 'error');
                    return;
                }

                if (!orden) {
                    Swal.fire('Error', 'Ingrese el número de orden', 'error');
                    return;
                }

                buscarVotante('{{ route('votos.buscar.mesaorden') }}', {
                    mesa: mesa,
                    orden: orden,
                    miembro_id: {{ $miembro->id }}
                });
            });

            // Limpiar campos mesa y orden
            $('#btnLimpiarMesaOrden').click(function() {
                $('#mesaSelect').val('');
                $('#ordenInput').val('');
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
            });

            function buscarVotante(url, data) {
                $('#loading').show();
                $('#resultadoBusqueda').hide();

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: function(response) {
                        $('#loading').hide();

                        if (response.success) {
                            mostrarDatosVotante(response.data);
                            $('#resultadoBusqueda').show();
                            if (response.message) {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Aviso',
                                    text: response.message,
                                    timer: 3000,
                                    showConfirmButton: false
                                });
                            }
                        } else {
                            Swal.fire('No encontrado', response.message, 'warning');
                        }
                    },
                    error: function(xhr) {
                        console.log('Error:', xhr);
                        $('#loading').hide();
                        let errorMsg = 'Error al buscar el votante';
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMsg = xhr.responseJSON.message;
                        }
                        Swal.fire('Error', errorMsg, 'error');
                    }
                });
            }

            function mostrarDatosVotante(data) {
                $('#votante_cedula').val(data.cedula);
                $('#votante_nombres').val(data.nombres);
                $('#votante_apellidos').val(data.apellidos);
                $('#votante_localvotacion').val(data.localvotacion);
                $('#votante_distrito').val(data.distrito);
                $('#votante_mesa').val(data.mesa);

                $('#display_cedula').text(data.cedula);
                $('#display_nombres').text((data.nombres || '') + ' ' + (data.apellidos || ''));
                $('#display_local').text(data.localvotacion || 'N/A');
                $('#display_distrito').text(data.distrito || 'N/A');
                $('#display_mesa').text(data.mesa || 'N/A');
            }

            // Guardar voto
            $('#formGuardarVoto').submit(function(e) {
                e.preventDefault();

                if (!$('#votante_cedula').val()) {
                    Swal.fire('Error', 'No hay datos de votante para guardar', 'error');
                    return;
                }

                const formData = $(this).serialize();

                Swal.fire({
                    title: '¿Confirmar voto?',
                    text: '¿Está seguro de registrar este voto?',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#28a745',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        $('#loading').show();

                        $.ajax({
                            url: '{{ route('votos.guardar') }}',
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            },
                            success: function(response) {
                                $('#loading').hide();

                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Éxito!',
                                        text: response.message,
                                        timer: 1500,
                                        showConfirmButton: false
                                    });

                                    // Actualizar contador
                                    $('#totalVotos').text(response.total_votos);

                                    // Agregar voto a la tabla
                                    agregarVotoATabla(response.voto);

                                    // Limpiar formulario
                                    limpiarFormulario();
                                    $('#resultadoBusqueda').hide();
                                    $('#buscarCedula').val('');
                                    $('#mesaSelect').val('');
                                    $('#ordenInput').val('');
                                    $('#buscarCedula').focus();
                                } else {
                                    Swal.fire('Error', response.message, 'error');
                                }
                            },
                            error: function(xhr) {
                                $('#loading').hide();
                                let errorMsg = 'Error al guardar el voto';
                                if (xhr.responseJSON && xhr.responseJSON.message) {
                                    errorMsg = xhr.responseJSON.message;
                                }
                                Swal.fire('Error', errorMsg, 'error');
                            }
                        });
                    }
                });
            });

            function inicializarTablaVotos() {
                votosDataTable = $('#votos-table').DataTable({
                    responsive: true,
                    ordering: true,
                    order: [
                        [0, 'desc']
                    ],
                    dom: "<'row'<'col-md-4'l><'col-md-4 text-center'f><'col-md-4 text-right'B>>" +
                        "<'row'<'col-sm-12'tr>>" +
                        "<'row'<'col-sm-5'i><'col-sm-7'p>>",
                    buttons: [{
                            extend: 'excelHtml5',
                            className: 'btn btn-success btn-sm',
                            text: '<i class="fas fa-file-excel"></i> Excel',
                            title: 'Votos Cargados - {{ $miembro->nombre }}',
                            filename: 'votos_{{ $miembro->cedula }}_{{ date('Y-m-d') }}',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'pdfHtml5',
                            className: 'btn btn-danger btn-sm',
                            text: '<i class="fas fa-file-pdf"></i> PDF',
                            title: 'Votos Cargados - {{ $miembro->nombre }}',
                            filename: 'votos_{{ $miembro->cedula }}_{{ date('Y-m-d') }}',
                            exportOptions: {
                                columns: [0, 1, 2, 3, 4, 5]
                            }
                        },
                        {
                            extend: 'print',
                            className: 'btn btn-secondary btn-sm',
                            text: '<i class="fas fa-print"></i> Imprimir'
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

            function agregarVotoATabla(voto) {
                let rowCount = votosDataTable.rows().count() + 1;

                let nuevaFila = [
                    rowCount,
                    voto.cedula,
                    (voto.nombres || '') + ' ' + (voto.apellidos || ''),
                    voto.localvotacion,
                    voto.distrito,
                    voto.mesa || '',
                    voto.created_at || (new Date().toLocaleDateString('es-ES') + ' ' + new Date().toLocaleTimeString('es-ES')),
                    '<button class="btn btn-danger btn-sm" onclick="eliminarVoto(' + voto.id +
                    ')"><i class="fas fa-trash"></i></button>'
                ];

                votosDataTable.row.add(nuevaFila).draw();

                // Asignar id al tr para que eliminarVoto pueda encontrarlo
                let nodes = votosDataTable.rows().nodes();
                let lastNode = nodes[nodes.length - 1];
                $(lastNode).attr('id', 'voto-row-' + voto.id);

                reordenarNumeros();
            }

            $('#btnNuevoVoto').click(function() {
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
                $('#buscarCedula').val('').focus();
                $('#mesaSelect').val('');
                $('#ordenInput').val('');
            });

            function limpiarFormulario() {
                $('#votante_cedula, #votante_nombres, #votante_apellidos, #votante_localvotacion, #votante_distrito, #votante_mesa')
                    .val('');
                $('#display_cedula, #display_nombres, #display_local, #display_distrito, #display_mesa').text('');
            }

            function reordenarNumeros() {
                let rows = $('#votos-table tbody tr');
                rows.each(function(index, row) {
                    $(row).find('td:first').text(index + 1);
                });
            }

            // Enter para buscar
            $('#buscarCedula').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnBuscarCedula').click();
                }
            });

            $('#ordenInput').keypress(function(e) {
                if (e.which === 13) {
                    e.preventDefault();
                    $('#btnBuscarMesaOrden').click();
                }
            });
        });

        // Función global para eliminar voto
        // Función global para eliminar voto
        function eliminarVoto(id) {
            Swal.fire({
                title: '¿Eliminar voto?',
                text: 'Esta acción no se puede deshacer.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    const url = '{{ route("votos.eliminar", "") }}/' + id;

                    $.ajax({
                        url: url,
                        type: 'DELETE',
                        data: {
                            _token: '{{ csrf_token() }}'
                        },
                        success: function(response) {
                            if (response.success) {
                                Swal.fire('Eliminado', response.message, 'success');

                                // Eliminar fila del DataTable
                                let row = $('#voto-row-' + id);
                                if (votosDataTable) {
                                    votosDataTable.row(row).remove().draw();
                                } else {
                                    row.remove();
                                }

                                // Actualizar contador
                                $('#totalVotos').text(response.total_votos);

                                // Reordenar números
                                let rows = $('#votos-table tbody tr');
                                rows.each(function(index, row) {
                                    $(row).find('td:first').text(index + 1);
                                });
                            } else {
                                Swal.fire('Error', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            Swal.fire('Error', 'Error al eliminar el voto', 'error');
                        }
                    });
                }
            });
        }
    </script>
@endpush
