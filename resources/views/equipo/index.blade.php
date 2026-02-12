@extends('adminlte::page')

@section('title', 'Equipos')

@section('content_header')
    <h1 class="m-0">
        <i class="fas fa-users text-primary"></i> Gestión de Equipos
    </h1>
@stop

@section('content')

    {{-- FORMULARIO NUEVO EQUIPO --}}
    @can('Guardar Equipos')
        <div class="card mb-4">
            <div class="card-header bg-primary">
                <strong><i class="fas fa-plus-circle"></i> Nuevo Equipo</strong>
            </div>

            <form action="{{ route('equipo.store') }}" method="POST">
                @csrf
                <div class="card-body">
                    <div class="row">
                        <x-adminlte-input name="descripcion" label="Descripción del Equipo"
                            placeholder="Ej: Equipo Central" fgroup-class="col-md-3" required>
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-users"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="colegio" label="Colegio" fgroup-class="col-md-3">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-school"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <x-adminlte-input name="ciudad" label="Ciudad" fgroup-class="col-md-2">
                            <x-slot name="prependSlot">
                                <div class="input-group-text">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                            </x-slot>
                        </x-adminlte-input>

                        <div class="col-md-2 d-flex align-items-end">
                            <button class="btn btn-success w-100">
                                <i class="fas fa-save"></i> Guardar
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    @endcan

    {{-- BUSCADOR --}}
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

    {{-- LISTA DE EQUIPOS (se actualizará con AJAX) --}}
    <div id="listaEquipos">
        @include('equipo.partials.lista_equipos')
    </div>

@stop

@section('js')
<script>
    

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

    // Botones eliminar
    function initDeleteButtons() {
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
                    if (result.isConfirmed) form.submit();
                });
            });
        });
    }

    initDeleteButtons(); // inicializamos botones de la primera carga

    // Búsqueda AJAX
    const buscador = document.getElementById('buscadorEquipo');
    let typingTimer;
    buscador.addEventListener('keyup', function() {
        clearTimeout(typingTimer);
        let query = this.value;

        typingTimer = setTimeout(() => {
            fetch(`{{ route('equipo.index') }}?search=${query}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.text())
            .then(html => {
                document.getElementById('listaEquipos').innerHTML = html;
                initDeleteButtons(); // re-inicializar botones eliminar después del AJAX

                // Capturar clicks de paginación AJAX
                document.querySelectorAll('#listaEquipos .pagination a').forEach(link => {
                    link.addEventListener('click', function(e) {
                        e.preventDefault();
                        let url = this.href;
                        fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                            .then(res => res.text())
                            .then(html => {
                                document.getElementById('listaEquipos').innerHTML = html;
                                initDeleteButtons();
                                attachPaginationEvents();
                            });
                    });
                });
            });
        }, 300); // espera 300ms para no saturar el servidor
    });

    // Capturar clicks de paginación inicial
    function attachPaginationEvents() {
        document.querySelectorAll('#listaEquipos .pagination a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                let url = this.href;
                fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(res => res.text())
                    .then(html => {
                        document.getElementById('listaEquipos').innerHTML = html;
                        initDeleteButtons();
                        attachPaginationEvents();
                    });
            });
        });
    }
    attachPaginationEvents();
</script>
@endsection
