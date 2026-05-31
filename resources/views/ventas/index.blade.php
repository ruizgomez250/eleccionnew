@extends('adminlte::page')

@section('title', 'Cargar Votos - ' . $miembro->nombre)

@section('content_header')
    <div class="row">
        <div class="col-6">
            <h1 class="m-0 custom-heading">
                <i class="fas fa-vote-yea"></i> Cargar Votos - {{ $miembro->nombre }}
            </h1>
        </div>
        <div class="col-6">
            <a href="{{ route('miembros.index') }}" class="btn btn-secondary" style="float: right;">
                <i class="fas fa-arrow-left"></i> Volver a Miembros
            </a>
        </div>
    </div>
@stop

@section('content')
    <div class="row">
        <div class="col-12">
            <!-- Información del miembro de mesa -->
            <div class="card card-info">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-user"></i> Información del Miembro de Mesa
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <strong><i class="fas fa-user"></i> Miembro:</strong> {{ $miembro->nombre }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-id-card"></i> Cédula:</strong> {{ $miembro->cedula }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-building"></i> Local:</strong> {{ $equipo->descripcion ?? 'N/A' }}
                        </div>
                        <div class="col-md-3">
                            <strong><i class="fas fa-chart-line"></i> Total Votos:</strong>
                            <span id="totalVotos" class="badge badge-success">{{ $votosCargados ?? 0 }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Selector de método de búsqueda -->
            <div class="card card-primary">
                <div class="card-header">
                    <h3 class="card-title">
                        <i class="fas fa-search"></i> Buscar Votante
                    </h3>
                </div>
                <div class="card-body">
                    <div class="row mb-4">
                        <div class="col-md-12">
                            <div class="btn-group btn-group-toggle" data-toggle="buttons">
                                <label class="btn btn-outline-primary active" id="btnMetodoCedula">
                                    <input type="radio" name="metodo" value="cedula" checked>
                                    <i class="fas fa-id-card"></i> Buscar por Cédula
                                </label>
                                <label class="btn btn-outline-primary" id="btnMetodoMesa">
                                    <input type="radio" name="metodo" value="mesa">
                                    <i class="fas fa-table"></i> Buscar por Mesa y Orden
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de búsqueda por Cédula -->
                    <div id="formCedula">
                        <div class="row">
                            <div class="col-md-8">
                                <div class="form-group">
                                    <label for="buscarCedula"><i class="fas fa-search"></i> Número de Cédula:</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control form-control-lg" id="buscarCedula"
                                            placeholder="Ingrese el número de cédula">
                                        <div class="input-group-append">
                                            <button class="btn btn-primary btn-lg" type="button" id="btnBuscarCedula">
                                                <i class="fas fa-search"></i> Buscar
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Formulario de búsqueda por Mesa y Orden -->
                    <div id="formMesaOrden" style="display: none;">
                        <div class="row">
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="mesaSelect"><i class="fas fa-table"></i> Número de Mesa:</label>
                                    <select class="form-control form-control-lg" id="mesaSelect">
                                        <option value="">Seleccione una mesa</option>
                                        @for($i = 1; $i <= $cantidadMesas; $i++)
                                            <option value="{{ $i }}">Mesa {{ $i }}</option>
                                        @endfor
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label for="ordenInput"><i class="fas fa-sort-numeric-up"></i> Número de Orden:</label>
                                    <input type="number" class="form-control form-control-lg" id="ordenInput"
                                        placeholder="Ingrese el número de orden">
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>&nbsp;</label>
                                    <button class="btn btn-primary btn-lg btn-block" type="button" id="btnBuscarMesaOrden">
                                        <i class="fas fa-search"></i> Buscar Votante
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Resultados de la búsqueda -->
            <div id="resultadoBusqueda" style="display: none;">
                <div class="card card-success">
                    <div class="card-header">
                        <h3 class="card-title">
                            <i class="fas fa-user-check"></i> Datos del Votante
                        </h3>
                    </div>
                    <div class="card-body">
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
                                    <div class="form-group">
                                        <label><strong>Cédula:</strong></label>
                                        <p class="form-control-static" id="display_cedula"></p>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label><strong>Nombres Completos:</strong></label>
                                        <p class="form-control-static" id="display_nombres"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Local de Votación:</strong></label>
                                        <p class="form-control-static" id="display_local"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Distrito:</strong></label>
                                        <p class="form-control-static" id="display_distrito"></p>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label><strong>Mesa:</strong></label>
                                        <p class="form-control-static" id="display_mesa"></p>
                                    </div>
                                </div>
                            </div>

                            <div class="text-center">
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save"></i> Confirmar y Guardar Voto
                                </button>
                                <button type="button" class="btn btn-secondary btn-lg" id="btnNuevoVoto">
                                    <i class="fas fa-plus"></i> Nuevo Voto
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Loading -->
            <div id="loading" style="display: none;" class="text-center">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
                <p class="mt-2">Buscando información...</p>
            </div>
        </div>
    </div>
@stop

@push('css')
    <style>
        .form-control-static {
            background: #f4f4f4;
            padding: 8px 12px;
            border-radius: 4px;
            font-weight: normal;
            margin-bottom: 0;
            border: 1px solid #ddd;
        }
        
        .btn-group-toggle .btn {
            padding: 10px 20px;
        }
        
        #totalVotos {
            font-size: 16px;
            padding: 5px 12px;
        }
        
        .spinner-border {
            width: 3rem;
            height: 3rem;
        }
        
        .card-header .badge {
            font-size: 14px;
        }
    </style>
@endpush

@push('js')
    <script>
        $(document).ready(function() {
            let metodoActual = 'cedula';
            
            // Cambiar entre métodos de búsqueda
            $('#btnMetodoCedula').click(function() {
                metodoActual = 'cedula';
                $('#formCedula').show();
                $('#formMesaOrden').hide();
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
                $(this).addClass('active');
                $('#btnMetodoMesa').removeClass('active');
            });
            
            $('#btnMetodoMesa').click(function() {
                metodoActual = 'mesa';
                $('#formCedula').hide();
                $('#formMesaOrden').show();
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
                $(this).addClass('active');
                $('#btnMetodoCedula').removeClass('active');
            });
            
            // Buscar por cédula
            $('#btnBuscarCedula').click(function() {
                const cedula = $('#buscarCedula').val().trim();
                if (!cedula) {
                    Swal.fire('Error', 'Ingrese un número de cédula', 'error');
                    return;
                }
                
                buscarVotante('{{ route("votos.buscar.cedula") }}', { 
                    cedula: cedula, 
                    miembro_id: {{ $miembro->id }} 
                });
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
                
                buscarVotante('{{ route("votos.buscar.mesaorden") }}', { 
                    mesa: mesa, 
                    orden: orden, 
                    miembro_id: {{ $miembro->id }} 
                });
            });
            
            function buscarVotante(url, data) {
                $('#loading').show();
                $('#resultadoBusqueda').hide();
                
                $.ajax({
                    url: url,
                    type: 'POST',
                    data: data,
                    dataType: 'json',
                    success: function(response) {
                        $('#loading').hide();
                        
                        if (response.success) {
                            mostrarDatosVotante(response.data);
                            $('#resultadoBusqueda').show();
                            $('html, body').animate({
                                scrollTop: $('#resultadoBusqueda').offset().top - 100
                            }, 500);
                        } else {
                            Swal.fire('No encontrado', response.message, 'warning');
                        }
                    },
                    error: function(xhr) {
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
                            url: '{{ route("votos.guardar") }}',
                            type: 'POST',
                            data: formData,
                            dataType: 'json',
                            success: function(response) {
                                $('#loading').hide();
                                
                                if (response.success) {
                                    Swal.fire({
                                        icon: 'success',
                                        title: '¡Éxito!',
                                        text: response.message,
                                        timer: 2000,
                                        showConfirmButton: false
                                    });
                                    
                                    $('#totalVotos').text(response.total_votos);
                                    limpiarFormulario();
                                    $('#resultadoBusqueda').hide();
                                    
                                    if (metodoActual === 'cedula') {
                                        $('#buscarCedula').val('').focus();
                                    } else {
                                        $('#mesaSelect').val('');
                                        $('#ordenInput').val('');
                                    }
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
            
            // Botón nuevo voto
            $('#btnNuevoVoto').click(function() {
                $('#resultadoBusqueda').hide();
                limpiarFormulario();
                if (metodoActual === 'cedula') {
                    $('#buscarCedula').val('').focus();
                } else {
                    $('#mesaSelect').val('');
                    $('#ordenInput').val('');
                }
            });
            
            function limpiarFormulario() {
                $('#votante_cedula, #votante_nombres, #votante_apellidos, #votante_localvotacion, #votante_distrito, #votante_mesa').val('');
                $('#display_cedula, #display_nombres, #display_local, #display_distrito, #display_mesa').text('');
            }
            
            // Buscar con Enter
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
    </script>
@endpush