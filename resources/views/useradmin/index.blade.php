@extends('adminlte::page')

@section('title', 'Administración de Usuarios y Sistemas')

@section('content_header')
    <h1>Administración de Usuarios y Sistemas</h1>
@stop

@section('content')
<div class="row">

    {{-- Sección de sistemas --}}
    <div class="col-md-4">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Sistemas</h3>
                <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalSistema">
                    <i class="fas fa-plus"></i> Nuevo Sistema
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped" id="sistemas-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($sistemas as $sistema)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $sistema->nombre }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm" onclick="editarSistema({{ $sistema->id }}, '{{ $sistema->nombre }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('sistema.destroy', $sistema->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar sistema?')">
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

    {{-- Sección de usuarios --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Usuarios</h3>
                <button class="btn btn-primary btn-sm float-right" data-toggle="modal" data-target="#modalUsuario">
                    <i class="fas fa-plus"></i> Nuevo Usuario
                </button>
            </div>
            <div class="card-body">
                <table class="table table-bordered table-striped" id="usuarios-table">
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
                        @foreach($users as $user)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>{{ $user->sistemaRelacion->nombre ?? '' }}</td>
                            <td>
                                <button class="btn btn-warning btn-sm"
                                    onclick="editarUsuario({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->sistema }}')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('useradmin.destroy', $user->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-danger btn-sm" onclick="return confirm('¿Eliminar usuario?')">
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

{{-- Modal Sistema --}}
<div class="modal fade" id="modalSistema" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('sistema.store') }}" method="POST" id="formSistema">
            @csrf
            <input type="hidden" name="sistema_id" id="sistema_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Sistema</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
                    <div class="form-group">
                        <label>Nombre del Sistema</label>
                        <input type="text" name="nombre" id="nombre_sistema" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Sistema</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Modal Usuario --}}
<div class="modal fade" id="modalUsuario" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <form action="{{ route('useradmin.store') }}" method="POST" id="formUsuario">
            @csrf
            <input type="hidden" name="user_id" id="user_id">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nuevo Usuario</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">
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
                            <option value="">-- Seleccione --</option>
                            @foreach($sistemas as $s)
                                <option value="{{ $s->id }}">{{ $s->nombre }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Guardar Usuario</button>
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </form>
    </div>
</div>

@stop

@push('js')
<script>
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

        $('#sistemas-table').DataTable({
            responsive: true,
            paging: false,
            info: false,
            searching: false
        });
    });

    function editarSistema(id, nombre) {
        $('#sistema_id').val(id);
        $('#nombre_sistema').val(nombre);
        $('#modalSistema .modal-title').text('Editar Sistema');
        $('#modalSistema').modal('show');
    }

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
