@extends('adminlte::page')

@section('title', 'Editar Vehículo')

@section('content_header')
    <h1>
        <i class="fas fa-car"></i> Editar Vehículo
    </h1>
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
                <x-adminlte-input name="cedulachofer" label="Cédula del Chofer"
                    value="{{ $vehiculo->cedulachofer }}" fgroup-class="col-md-3" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-id-card"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Nombre Chofer --}}
                <x-adminlte-input name="nombre" label="Nombre del Chofer"
                    value="{{ $vehiculo->nombre }}" fgroup-class="col-md-3" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-user"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Chapa --}}
                <x-adminlte-input name="chapa" label="Chapa"
                    value="{{ $vehiculo->chapa }}" fgroup-class="col-md-2" required>
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
                    value="{{ $vehiculo->capacidad }}" fgroup-class="col-md-2" required>
                    <x-slot name="appendSlot"><div class="input-group-text"><i class="fas fa-users"></i></div></x-slot>
                </x-adminlte-input>
            </div>

            {{-- FILA 2 --}}
            <div class="row mt-2">
                {{-- Teléfonos --}}
                <x-adminlte-input name="telefono1" label="Teléfono 1"
                    value="{{ $vehiculo->telefono1 }}" fgroup-class="col-md-2" required>
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone"></i></div></x-slot>
                </x-adminlte-input>

                <x-adminlte-input name="telefono2" label="Teléfono 2"
                    value="{{ $vehiculo->telefono2 }}" fgroup-class="col-md-2">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone-alt"></i></div></x-slot>
                </x-adminlte-input>

                <x-adminlte-input name="telefono3" label="Teléfono 3"
                    value="{{ $vehiculo->telefono3 }}" fgroup-class="col-md-2">
                    <x-slot name="prependSlot"><div class="input-group-text"><i class="fas fa-phone-alt"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Monto a pagar --}}
                <x-adminlte-select name="montopagar" label="Monto a Pagar (Gs.)" fgroup-class="col-md-2">
                    @foreach([200000,300000,350000,400000,450000,500000,550000] as $monto)
                        <option value="{{ $monto }}" {{ $vehiculo->montopagar == $monto ? 'selected' : '' }}>
                            {{ number_format($monto,0,',','.') }}
                        </option>
                    @endforeach
                </x-adminlte-select>

                {{-- Cantidad de pagos --}}
                <x-adminlte-input name="cantidadpagos" label="Cantidad de Pagos" type="number"
                    value="{{ $vehiculo->cantidadpagos }}" fgroup-class="col-md-2" required>
                    <x-slot name="appendSlot"><div class="input-group-text"><i class="fas fa-list-ol"></i></div></x-slot>
                </x-adminlte-input>

                {{-- Equipo --}}
                <div class="col-md-4">
                    <label for="id_equipo" class="form-label fw-bold">Equipo</label>
                    <x-adminlte-select2 name="id_equipo" id="id_equipo" enable-old-support>
                        @foreach ($equipos as $eq)
                            <option value="{{ $eq->id }}" {{ $vehiculo->id_equipo == $eq->id ? 'selected' : '' }}>
                                {{ $eq->descripcion }}
                            </option>
                        @endforeach
                    </x-adminlte-select2>
                </div>
            </div>

            <div class="row mt-3">
                <div class="col-md-2">
                    <button class="btn btn-success w-100">
                        <i class="fas fa-save"></i> Actualizar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@stop

@section('js')
<script>
    // Mostrar SweetAlert si existe mensaje de éxito
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
</script>
@endsection
