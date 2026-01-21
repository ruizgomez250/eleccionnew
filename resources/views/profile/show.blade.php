@extends('adminlte::page')

@section('title', 'Editar Perfil')

@section('content_header')

    <h1>Perfil</h1>
@stop
@section('plugins.Sweetalert2', true)
@push('js')
    <script>
        $(document).ready(function() {
            var Toast = Swal.mixin({
                toast: true,
                position: 'bottom-end',
                color: '#716add',
                
                showConfirmButton: false,
                timer: 3000               
            });

            @if (session('success'))
                Toast.fire({
                    icon: 'success',
                    title: '<label style="font-size: 1.6rem !important;">Operación Exitosa!</label>',
                    text:  '{{ session('success') }}',
                });
            @endif

            @if (session('error'))
                Toast.fire({
                    icon: 'error',
                    title: '<label style="font-size: 1.6rem !important;">Error Inesperado!</label>',
                    text: '{{ session('error') }}',
                });
            @endif

            // Agregar confirmación de eliminación
            $('.delete-button').on('click', function() {
                var form = $(this).closest('.delete-form');
                Swal.fire({
                    title: 'Confirmar eliminación',
                    text: '¿Estás seguro de que deseas eliminar este Proveedor?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
@endpush
@section('content')

    <div class="card">
        
        <div class="card-body">
            <div class="text-center">
                <img class="form-group" src="{{ asset('vendor/adminlte/dist/img/eleccionpy.png') }}" alt="Snoopy" style="max-width: 200px; max-height: 200px;">
            </div>
            
                @csrf
                @method('PUT')

                <div class="form-group row text-center">
                    
                        <div class="col-md-12">
                            <h4><strong>Nombre: </strong> {{ $user->name }}</h4>
                        </div>
                    
                        <div class="col-md-12">
                            <h4><strong>Correo Electrónico: </strong>{{ $user->email }}</h4>
                        </div>
                    
                </div>

                
        </div>
    </div>
@stop

