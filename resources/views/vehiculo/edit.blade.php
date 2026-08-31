@extends('adminlte::page')

@section('title', 'Editar Vehículo')

@section('content_header')
    <div class="ua-header">
        <h1 class="ua-title"><i class="fas fa-car"></i> Editar Vehículo</h1>
    </div>
@stop

@section('content')
<div class="card">
    <div class="card-body">

        <form action="{{ route('vehiculo.update', $vehiculo->id) }}" method="POST">
            @csrf
            @method('PUT')

            {{-- FILA 1 --}}
            <div class="row">
                {{-- Cédula Chofer --}}
                <x-adminlte-input name="cedulachofer" id="cedulachofer" label="Cédula del Chofer"
                    value="{{ old('cedulachofer', $vehiculo->cedulachofer) }}" fgroup-class="col-md-3" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-id-card"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Nombre Chofer --}}
                <x-adminlte-input name="nombre" id="nombre" label="Nombre del Chofer"
                    value="{{ old('nombre', $vehiculo->nombre) }}" fgroup-class="col-md-3" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-user"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Chapa --}}
                <x-adminlte-input name="chapa" label="Chapa"
                    value="{{ old('chapa', $vehiculo->chapa) }}" fgroup-class="col-md-2" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-car"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Tipo Vehículo --}}
                <x-adminlte-select name="tipovehiculo" label="Tipo de Vehículo" fgroup-class="col-md-2">
                    <option value="AUTOMOVIL" {{ $vehiculo->tipovehiculo=='AUTOMOVIL' ? 'selected' : '' }}>AUTOMÓVIL</option>
                    <option value="CAMIONETA" {{ $vehiculo->tipovehiculo=='CAMIONETA' ? 'selected' : '' }}>CAMIONETA</option>
                    <option value="FURGONETA" {{ $vehiculo->tipovehiculo=='FURGONETA' ? 'selected' : '' }}>FURGONETA</option>
                </x-adminlte-select>

                {{-- Capacidad --}}
                <x-adminlte-input name="capacidad" label="Capacidad" type="number"
                    value="{{ old('capacidad', $vehiculo->capacidad) }}" fgroup-class="col-md-2" required>
                    <x-slot name="appendSlot"><div class="input-group-text"><i class="fas fa-users"></i></div></x-slot>
                </x-adminlte-input>
            </div>

            {{-- FILA 2: Dirección y Barrio --}}
            <div class="row mt-2">
                {{-- Dirección --}}
                <x-adminlte-input name="direccion" id="direccion" label="Dirección del Chofer"
                    value="{{ old('direccion', $vehiculo->direccion) }}" placeholder="Ej: Calle Fulgencio Yegros N° 123" 
                    fgroup-class="col-md-4">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-map-marker-alt"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Barrio/Compañía --}}
                <x-adminlte-input name="barriocompania" id="barriocompania" label="Barrio/Compañía"
                    value="{{ old('barriocompania', $vehiculo->barriocompania) }}" placeholder="Ej: Barrio San Rafael"
                    fgroup-class="col-md-3">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-building"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Teléfonos --}}
                <x-adminlte-input name="telefono1" id="telefono1" label="Teléfono 1"
                    value="{{ old('telefono1', $vehiculo->telefono1) }}" fgroup-class="col-md-2" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone"></i></div></x-slot>
                </x-adminlte-input>

                <x-adminlte-input name="telefono2" id="telefono2" label="Teléfono 2"
                    value="{{ old('telefono2', $vehiculo->telefono2) }}" fgroup-class="col-md-2">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone-alt"></i></div></x-slot>
                </x-adminlte-input>

                <x-adminlte-input name="telefono3" label="Teléfono 3"
                    value="{{ old('telefono3', $vehiculo->telefono3) }}" fgroup-class="col-md-1">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone-alt"></i></div></x-slot>
                </x-adminlte-input>
            </div>

            {{-- FILA 3: Montos y Equipo --}}
            <div class="row mt-2">
                {{-- Monto a pagar --}}
                <x-adminlte-select name="montopagar" label="Monto a Pagar (Gs.)" fgroup-class="col-md-3">
                    @php
                        $montos = [0, 200000, 300000, 350000, 400000, 450000, 500000, 550000];
                    @endphp
                    @foreach($montos as $monto)
                        <option value="{{ $monto }}" {{ $vehiculo->montopagar == $monto ? 'selected' : '' }}>
                            {{ number_format($monto,0,',','.') }}
                        </option>
                    @endforeach
                </x-adminlte-select>

                {{-- Cantidad de pagos --}}
                <x-adminlte-input name="cantidadpagos" label="Cantidad de Pagos" type="number"
                    value="{{ old('cantidadpagos', $vehiculo->cantidadpagos) }}" fgroup-class="col-md-2" required>
                    <x-slot name="appendSlot"><div class="input-group-text"><i class="fas fa-list-ol"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Rol del Vehículo --}}
                <x-adminlte-select name="rol" id="rol" label="Rol del Vehículo" fgroup-class="col-md-3">
                    <option value="PUNTERO" {{ $vehiculo->rol == 'PUNTERO' ? 'selected' : '' }}>PUNTERO</option>
                    <option value="LOGISTICA" {{ $vehiculo->rol == 'LOGISTICA' ? 'selected' : '' }}>LOGISTICA</option>
                </x-adminlte-select>

                {{-- Equipo --}}
                <div class="col-md-4">
                    <label for="id_equipo" class="form-label fw-bold">Colegio electoral</label>
                    <x-adminlte-select2 name="id_equipo" id="id_equipo" enable-old-support>
                        <option value="">Sin Colegio electoral</option>
                        @foreach ($equipos as $eq)
                            <option value="{{ $eq->id }}" {{ $vehiculo->id_equipo == $eq->id ? 'selected' : '' }}>
                                {{ $eq->descripcion }}
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                </div>
            </div>

            {{-- FILA 4: DATOS DEL PROPONENTE (Opcionales) --}}
            <div class="row mt-4">
                <div class="col-12">
                    <div class="card card-outline card-secondary">
                        <div class="card-header">
                            <h6 class="card-title">
                                <i class="fas fa-user-check"></i> Datos del Proponente (Opcional)
                            </h6>
                            <div class="card-tools">
                                <button type="button" class="btn btn-tool" data-card-widget="collapse">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="row">
                                <x-adminlte-input name="cedulaproponente" id="cedulaproponente" 
                                    label="Cédula del Proponente" placeholder="Ej: 1.234.567"
                                    value="{{ old('cedulaproponente', $vehiculo->cedulaproponente) }}" fgroup-class="col-md-3">
                                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-id-card"></i></div></x-slot>
                                </x-adminlte-input>

                                <x-adminlte-input name="nombreproponente" id="nombreproponente" 
                                    label="Nombre del Proponente" placeholder="Nombre completo"
                                    value="{{ old('nombreproponente', $vehiculo->nombreproponente) }}" fgroup-class="col-md-5">
                                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-user-tie"></i></div></x-slot>
                                </x-adminlte-input>

                                <x-adminlte-input name="telefonoproponente" id="telefonoproponente" 
                                    label="Teléfono del Proponente" placeholder="0981xxxxxx"
                                    value="{{ old('telefonoproponente', $vehiculo->telefonoproponente) }}" fgroup-class="col-md-4">
                                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone"></i></div></x-slot>
                                </x-adminlte-input>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- FILA 5: Botón Actualizar --}}
            <div class="row mt-3">
                <div class="col-md-2">
                    <button class="btn btn-success w-100">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
                <div class="col-md-2">
                    <a href="{{ route('vehiculo.index') }}" class="btn btn-secondary w-100">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('css')
    @include('useradmin._dark_pages')
@stop

@section('js')
<script>
    const BASE_URL = '{{ url('/') }}';

    // =================== BÚSQUEDA POR CÉDULA DEL CHOFER ===================
    function buscarPorCedulaChofer() {
        let cedula = $('#cedulachofer').val().trim();
        if (cedula.length < 3) return;

        // Mostrar indicador de carga
        $('#nombre').prop('placeholder', 'Buscando...');
        
        $.get(BASE_URL + "/dirigente/buscar-por-cedulap/" + cedula, function(response) {
            if (response.encontrado) {
                // Sobrescribir todos los campos con los datos encontrados
                $('#nombre').val(response.data.nombre ?? '');
                $('#telefono1').val(response.data.telefono ?? '');
                $('#direccion').val(response.data.direccion ?? '');
                $('#barriocompania').val(response.data.barrio ?? '');
                
                // Cambiar color de borde a verde para indicar éxito
                $('#cedulachofer').removeClass('is-invalid').addClass('is-valid');
                
                // Opcional: Mostrar notificación de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Chofer encontrado',
                    text: 'Los datos se han cargado automáticamente',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                // No encontrado: limpiar campos y mostrar advertencia
                $('#nombre').val('');
                $('#telefono1').val('');
                $('#direccion').val('');
                $('#barriocompania').val('');
                
                $('#cedulachofer').removeClass('is-valid').addClass('is-invalid');
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Chofer no encontrado',
                    text: 'La cédula ingresada no existe. Debe completar los datos manualmente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            
            // Restaurar placeholder
            $('#nombre').prop('placeholder', 'Nombre completo');
        }).fail(function() {
            console.log('Error en la búsqueda de cédula');
            $('#nombre').prop('placeholder', 'Nombre completo');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al buscar el chofer',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    // =================== BÚSQUEDA POR CÉDULA DEL PROPONENTE ===================
    function buscarPorCedulaProponente() {
        let cedula = $('#cedulaproponente').val().trim();
        
        if (cedula.length < 3) {
            return;
        }

        // Mostrar indicador de carga
        $('#nombreproponente').prop('placeholder', 'Buscando...');
        
        $.get(BASE_URL + "/dirigente/buscar-por-cedulap/" + cedula, function(response) {
            if (response.encontrado) {
                // Sobrescribir todos los campos con los datos encontrados
                $('#nombreproponente').val(response.data.nombre ?? '');
                $('#telefonoproponente').val(response.data.telefono ?? '');
                
                $('#cedulaproponente').removeClass('is-invalid').addClass('is-valid');
                
                // Opcional: Mostrar notificación de éxito
                Swal.fire({
                    icon: 'success',
                    title: 'Proponente encontrado',
                    text: 'Los datos se han cargado automáticamente',
                    timer: 1500,
                    showConfirmButton: false
                });
            } else {
                // No encontrado: limpiar campos
                $('#nombreproponente').val('');
                $('#telefonoproponente').val('');
                
                $('#cedulaproponente').removeClass('is-valid').addClass('is-invalid');
                
                Swal.fire({
                    icon: 'warning',
                    title: 'Proponente no encontrado',
                    text: 'La cédula ingresada no existe. Debe completar los datos manualmente.',
                    timer: 2000,
                    showConfirmButton: false
                });
            }
            
            // Restaurar placeholder
            $('#nombreproponente').prop('placeholder', 'Nombre completo');
        }).fail(function() {
            console.log('Error en la búsqueda de cédula del proponente');
            $('#nombreproponente').prop('placeholder', 'Nombre completo');
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error al buscar el proponente',
                timer: 1500,
                showConfirmButton: false
            });
        });
    }

    // =================== LIMPIAR CAMPOS DEL PROPONENTE ===================
    function limpiarCamposProponente() {
        $('#nombreproponente').val('');
        $('#telefonoproponente').val('');
        $('#cedulaproponente').removeClass('is-valid is-invalid');
    }

    // =================== EVENTOS ===================
    $(document).ready(function() {

        // =================== EVENTOS PARA CHOFER ===================
        // Evento blur para búsqueda por cédula del chofer
        $('#cedulachofer').on('blur', function() {
            buscarPorCedulaChofer();
        });

        // Evento keypress para búsqueda por cédula del chofer con Enter
        $('#cedulachofer').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarPorCedulaChofer();
                $('#nombre').focus();
            }
        });

        // Limpiar validación cuando el usuario modifica la cédula
        $('#cedulachofer').on('focus', function() {
            $(this).removeClass('is-valid is-invalid');
        });

        // =================== EVENTOS PARA PROPONENTE ===================
        // Evento blur para búsqueda por cédula del proponente
        $('#cedulaproponente').on('blur', function() {
            buscarPorCedulaProponente();
        });

        // Evento keypress para búsqueda por cédula del proponente con Enter
        $('#cedulaproponente').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                buscarPorCedulaProponente();
                $('#nombreproponente').focus();
            }
        });

        // Limpiar validación al modificar la cédula
        $('#cedulaproponente').on('focus', function() {
            $(this).removeClass('is-valid is-invalid');
        });

        // Evento change para limpiar campos si se borra la cédula
        $('#cedulaproponente').on('change', function() {
            if ($(this).val().trim() === '') {
                limpiarCamposProponente();
            }
        });

        // =================== MOSTRAR/OCULTAR ASTERISCO DE EQUIPO SEGÚN ROL ===================
        function toggleEquipoRequired() {
            const rolSeleccionado = $('#rol').val();
            const equipoRequired = $('#equipoRequired');
            if (equipoRequired.length) {
                if (rolSeleccionado === 'PUNTERO') {
                    equipoRequired.show();
                } else {
                    equipoRequired.hide();
                }
            }
        }

        // Inicializar
        if ($('#rol').length) {
            toggleEquipoRequired();
            $('#rol').on('change', function() {
                toggleEquipoRequired();
            });
        }

        // Si no hay asterisco en el DOM, lo creamos
        if ($('#equipoRequired').length === 0) {
            $('label[for="id_equipo"]').append('<span id="equipoRequired" class="text-danger">*</span>');
            toggleEquipoRequired();
        }
    });

    // Mensaje SweetAlert al actualizar
    const successAlert = @json(session('success'));
    if(successAlert){
        Swal.fire({
            icon: 'success',
            title: 'Éxito',
            text: successAlert,
            timer: 1800,
            showConfirmButton: false
        });
    }

    // Mensaje SweetAlert al crear/actualizar (desde controlador)
    const errorAlert = @json(session('error'));
    if(errorAlert){
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: errorAlert,
            confirmButtonColor: '#d33'
        });
    }
</script>
@endsection