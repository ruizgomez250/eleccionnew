@extends('adminlte::page')

@section('title', 'Editar Perfil')

@section('content_header')
    <h1>Cambiar Contraseña</h1>
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
                <img class="form-group" src="{{ asset('vendor/adminlte/dist/img/eleccionpy.png') }}" alt="Snoopy"
                    style="max-width: 200px; max-height: 200px;">
            </div>

            <form id="profile-update-form" method="POST" action="{{ route('profile.update') }}">
                @csrf
                @method('PUT')

                <div class="form-group row">
                    <div class="form-group row">
                        <label for="contrasena_actual" class="col-md-6 col-form-label text-md-right">Contraseña
                            Actual</label>
                        <div class="col-md-6">
                            <input id="contrasena_actual" type="password"
                                class="form-control @error('contrasena_actual') is-invalid @enderror"
                                name="contrasena_actual" required autocomplete="current-password">
                            @error('contrasena_actual')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="nueva_contrasena" class="col-md-6 col-form-label text-md-right">Nueva Contraseña</label>
                        <div class="col-md-6">
                            <input id="nueva_contrasena" type="password"
                                class="form-control @error('nueva_contrasena') is-invalid @enderror" name="nueva_contrasena"
                                required minlength="8" autocomplete="new-password" oninput="verifCont()">
                            @error('nueva_contrasena')
                                <span class="invalid-feedback" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>

                    <div class="form-group row">
                        <label for="repetir_contrasena" class="col-md-6 col-form-label text-md-right">Repetir Nueva
                            Contraseña</label>
                        <div class="col-md-6">
                            <input id="repetir_contrasena" type="password" class="form-control" name="repetir_contrasena"
                                required minlength="8" autocomplete="new-password" oninput="verifCont()">
                            <span id="contrasena-mismatch" style="display: none; color: red;">Las contraseñas no
                                coinciden.</span>
                        </div>
                    </div>
                </div>

                <div class="form-group row">
                    <div class="col-md-6">
                        <button type="submit" class="btn btn-primary">
                            Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop
@push('js')
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            @if (session('success'))
                swal("Éxito", "{{ session('success') }}", "success");
            @endif

            // Verificar si hay un mensaje de error y mostrarlo como Sweet Alert
            @if (session('error'))
                swal("Error", "{{ session('error') }}", "error");
            @endif
            const nuevaContrasena = document.getElementById("nueva_contrasena");
            const repetirContrasena = document.getElementById("repetir_contrasena");
            const mismatchMessage = document.getElementById("contrasena-mismatch");

            nuevaContrasena.addEventListener("input", function() {
                verifCont();
            });

            repetirContrasena.addEventListener("input", function() {
                verifCont();
            });

            function verifCont() {
                if (nuevaContrasena.value !== repetirContrasena.value) {
                    mismatchMessage.style.display = "block";
                } else {
                    mismatchMessage.style.display = "none";
                }
            }
        });
    </script>
@endpush
