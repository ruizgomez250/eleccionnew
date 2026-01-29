@extends('adminlte::page')

@section('title', 'Equipos')

@section('content_header')
    <h1 class="m-0">
        <i class="fas fa-users text-primary"></i> Gestión de Equipos
    </h1>
@stop

@section('content')

    {{-- =================== FORMULARIO NUEVO EQUIPO =================== --}}
    <div class="card mb-4">
        <div class="card-header bg-primary">
            <strong><i class="fas fa-plus-circle"></i> Nuevo Equipo</strong>
        </div>
        <form action="{{ route('equipo.store') }}" method="POST">
            @csrf

            <div class="card-body">
                <div class="row">

                    {{-- Descripción --}}
                    <x-adminlte-input name="descripcion" label="Descripción del Equipo" placeholder="Ej: Equipo Central"
                        fgroup-class="col-md-3" required>
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-users"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Sistema (oculto) --}}
                    <input type="hidden" name="sist" value="7">

                    {{-- Colegio --}}
                    <x-adminlte-input name="colegio" label="Colegio" placeholder="Nombre del colegio"
                        fgroup-class="col-md-3">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-school"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Ciudad --}}
                    <x-adminlte-input name="ciudad" label="Ciudad" placeholder="Ciudad" fgroup-class="col-md-2">
                        <x-slot name="prependSlot">
                            <div class="input-group-text">
                                <i class="fas fa-map-marker-alt"></i>
                            </div>
                        </x-slot>
                    </x-adminlte-input>

                    {{-- Botón Guardar --}}
                    <div class="col-md-2 d-flex align-items-end">
                        <button class="btn btn-success w-100">
                            <i class="fas fa-save"></i> Guardar
                        </button>
                    </div>

                </div>
            </div>
        </form>

    </div>

    {{-- =================== BUSCADOR =================== --}}
    <div class="card mb-3">
        <div class="card-body">
            <div class="input-group">
                <div class="input-group-prepend">
                    <span class="input-group-text bg-primary">
                        <i class="fas fa-search text-white"></i>
                    </span>
                </div>
                <input type="text" id="buscadorEquipo" class="form-control" placeholder="Buscar equipo ">
            </div>
        </div>
    </div>

    {{-- =================== LISTA DE EQUIPOS =================== --}}
    <div class="row">
        @foreach ($equipos as $equipo)
            <div class="col-md-3 mb-4 equipo-card"
                data-search="{{ strtolower($equipo->descripcion . ' ' . $equipo->ciudad) }}">

                <div class="card shadow h-100">
                    <div class="card-header text-center bg-light">
                        <i class="fas fa-users fa-3x text-primary"></i>
                    </div>

                    <div class="card-body text-center">
                        <h5 class="font-weight-bold">{{ $equipo->descripcion }}</h5>



                        <p class="mb-0">
                            <i class="fas fa-map-marker-alt"></i>
                            {{ $equipo->ciudad ?? '—' }}
                        </p>
                    </div>

                    <div class="card-footer text-center">
                        <a href="{{ route('dirigente.createWithEquipo', $equipo->id) }}"
                            class="btn btn-sm btn-outline-primary mb-1 w-100">
                            <i class="fas fa-user-tie"></i> Agregar Dirigente
                        </a>



                        <a href="{{ route('puntero.createWithEquipo', $equipo->id) }}"
                            class="btn btn-sm btn-outline-success mb-1 w-100">
                            <i class="fas fa-user-plus"></i> Agregar Puntero
                        </a>


                        <div class="btn-group w-100 mt-2">
                            <a href="{{ route('equipo.edit', $equipo->id) }}" class="btn btn-sm btn-outline-secondary">
                                <i class="fas fa-edit"></i>
                            </a>

                            <form action="{{ route('equipo.destroy', $equipo->id) }}" method="POST"
                                class="form-delete d-inline">
                                @csrf
                                @method('DELETE')

                                <button type="button" class="btn btn-sm btn-outline-danger btn-delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>

                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

@stop

@section('js')
    <script>
        const successAlert = @json(session('success'));
        if (successAlert) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: "{{ session('success') }}",
                timer: 1800,
                showConfirmButton: false
            });
        }
        document.querySelectorAll('.btn-delete').forEach(btn => {

            btn.addEventListener('click', function() {

                let form = this.closest('.form-delete');

                Swal.fire({
                    title: '¿Eliminar equipo?',
                    text: "Si tiene dirigentes o punteros asociados no se podra eliminar",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });

            });

        });

        document.getElementById('buscadorEquipo').addEventListener('keyup', function() {
            let texto = this.value.toLowerCase();
            let equipos = document.querySelectorAll('.equipo-card');

            equipos.forEach(function(card) {
                let contenido = card.getAttribute('data-search');

                if (contenido.includes(texto)) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    </script>
@endsection
