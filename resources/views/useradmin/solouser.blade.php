@extends('adminlte::page')

@section('title', 'Administración de Usuarios')

@section('css')
    @include('useradmin._dark_theme')
@stop

@section('content_header')
    <div class="ua-header">
        <div>
            <h1 class="ua-title"><i class="fas fa-user-cog"></i> Administración de Usuarios</h1>
            <p class="ua-subtitle">Gestión de usuarios y sistemas</p>
        </div>
    </div>
@stop

@section('js')
    @if (session('success'))
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: '{{ session('success') }}',
                confirmButtonColor: '#3085d6'
            });
        </script>
    @endif

    @if (session('error'))
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: '{{ session('error') }}',
                confirmButtonColor: '#d33'
            });
        </script>
    @endif
@endsection

@section('content')
    <div class="row">
        {{-- Sección de usuarios - Ocupa toda la pantalla --}}
        <div class="col-md-12">
            <div class="card ua-card">
                <div class="card-header">
                    <h3 class="card-title"><i class="fas fa-users"></i>Usuarios</h3>
                    <button class="ua-btn ua-btn-grad btn-sm float-right" data-toggle="modal" data-target="#modalUsuario">
                        <i class="fas fa-plus"></i> Nuevo Usuario
                    </button>
                </div>
                <div class="card-body">
                    <table class="table ua-table" id="usuarios-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Sistema</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($users as $user)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <span class="ua-badge ua-badge-teal">
                                            <i class="fas fa-user"></i> {{ $user->name }}
                                        </span>
                                    </td>
                                    <td>{{ $user->email }}</td>
                                    <td>
                                        <span class="ua-badge ua-badge-violet">
                                            {{ $user->sistemaRelacion->nombre ?? 'Sin sistema' }}
                                        </span>
                                    </td>
                                    <td>
                                        <button class="ua-btn-icon ua-btn-edit"
                                            onclick="editarUsuario({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->sistema }}')">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('useradmin.destroy', $user->id) }}" method="POST"
                                            class="d-inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="ua-btn-icon ua-btn-del btn-eliminar">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Modal Usuario --}}
    <div class="modal fade ua-modal" id="modalUsuario" tabindex="-1" role="dialog">
        <div class="modal-dialog" role="document">
            <form action="{{ route('useradmin.store') }}" method="POST" id="formUsuario">
                @csrf
                <input type="hidden" name="user_id" id="user_id">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title"><i class="fas fa-user-plus mr-1"></i> Nuevo Usuario</h5>
                        <button type="button" class="close" data-dismiss="modal">&times;</button>
                    </div>
                    <div class="modal-body ua-form">
                        <div class="form-group">
                            <label>Nombre</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label>Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted">Dejar en blanco para no cambiar la contraseña</small>
                        </div>
                        <div class="form-group">
                            <label>Sistema</label>
                            <select name="sistema" id="sistema" class="form-control">
                                @foreach ($sistemas as $s)
                                    <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="">Roles</label>
                            <select name="roles[]" class="form-control" multiple>
                                @foreach ($roles as $item)
                                    <option value="{{ $item }}"
                                        @php
                                            $selected = false;
                                            if(!empty($userRoles) && is_array($userRoles)){
                                                $selected = in_array($item, $userRoles);
                                            } else {
                                                $selected = ($item === 'Administrador General');
                                            }
                                        @endphp
                                        {{ $selected ? 'selected' : '' }}>
                                        {{ $item }}
                                    </option>
                                @endforeach
                            </select>
                            @error('roles')
                                <span class="text-danger">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="ua-btn ua-btn-grad">Guardar Usuario</button>
                        <button type="button" class="ua-btn ua-btn-ghost" data-dismiss="modal">Cerrar</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
@stop

@push('js')
    <script>
        document.querySelectorAll('.btn-eliminar').forEach(button => {
            button.addEventListener('click', function() {
                let form = this.closest('form');
                Swal.fire({
                    title: '¿Está seguro?',
                    text: "No podrá revertir esta acción",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });

        const successAlert = @json(session('successAlert'));
        const errorAlert = @json(session('errorAlert'));

        if (errorAlert) {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: errorAlert,
                confirmButtonColor: '#dc3545'
            });
        }
        if (successAlert) {
            Swal.fire({
                icon: 'success',
                title: 'Éxito',
                text: successAlert,
                confirmButtonColor: '#28a745'
            });
        }

        $(document).ready(function() {
            $('#usuarios-table').DataTable({
                responsive: true,
                language: {
                    url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                }
            });
        });

        function editarUsuario(id, name, email, sistema) {
            $('#user_id').val(id);
            $('#name').val(name);
            $('#email').val(email);
            $('#sistema').val(sistema);
            $('#modalUsuario .modal-title').text('Editar Usuario');
            $('#modalUsuario').modal('show');
        }
    </script>
@endpush