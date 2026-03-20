@extends('adminlte::page')

@section('title', 'Administración de Usuarios y Sistemas')

@section('content_header')
    <h1>Administración de Usuarios y Sistemas <button class="btn btn-info btn-sm float-right mr-2"
            onclick="abrirModalReporte()">
            <i class="fas fa-file-excel"></i> Reporte Totales
        </button>
    </h1>


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

        {{-- Sección de sistemas --}}
        <div class="col-md-6">
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
                                <th>Ciudad</th>
                                <th>Su Candidato</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($sistemas as $sistema)
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>{{ $sistema->nombre . ' - ' . $sistema->tipo }}</td>
                                    <td>{{ $sistema->ciudad->descripcion }}</td>
                                    <td>{{ $sistema->usuario->name ?? 'Sin asignar' }}</td>
                                    <td>
                                        <button class="btn btn-warning btn-sm"
                                            onclick="editarSistema(
                                            {{ $sistema->id }},
                                            '{{ $sistema->nombre }}',
                                            {{ $sistema->id_ciudad_electoral }},
                                            '{{ $sistema->tipo }}',
                                            {{ $sistema->idusuario }}
                                        )">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <form action="{{ route('sistema.destroy', $sistema->id) }}" method="POST"
                                            class="d-inline form-eliminar">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar">
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
        <div class="col-md-6">
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
                            @foreach ($users as $user)
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
                                        <form action="{{ route('useradmin.destroy', $user->id) }}" method="POST"
                                            class="d-inline form-eliminar">
                                            @csrf
                                            @method('DELETE')

                                            <button type="button" class="btn btn-danger btn-sm btn-eliminar">
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

                        <div class="form-group">
                            <x-adminlte-select2 name="id_ciudad_electoral" label="Ciudad" label-class="text-lightblue"
                                igroup-size="lg">
                                @foreach ($ciudades as $ciudad)
                                    <option value="{{ $ciudad->id }}">
                                        {{ $ciudad->descripcion }} - {{ $ciudad->departamento }}
                                    </option>
                                @endforeach
                            </x-adminlte-select2>
                        </div>

                        <div class="form-group">
                            <label>Tipo de Candidato</label>
                            <select name="tipo" id="tipo" class="form-control" required>
                                <option value="concejal">Concejal</option>
                                <option value="intendente">Intendente</option>
                                <option value="Miembro de Comite">Miembro de Comite</option>
                                <option value="Convencional">Convencional</option>
                                <option value="Miembro de la Juventud">Miembro de la Juventud</option>
                                <option value="Convencional Juventud">Convencional Juventud</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <x-adminlte-select2 name="candidatosup" label="Candidato Superior" label-class="text-lightblue"
                                igroup-size="lg">
                                <option value="0">Sin Dato</option>
                                @foreach ($users as $user)
                                    <option value="{{ $user->id }}">
                                        {{ $user->sistemaRelacion->nombre . ' - ' . $user->sistemaRelacion->tipo . ' - ' . $user->name }}
                                    </option>
                                @endforeach
                            </x-adminlte-select2>

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
    {{-- Modal Reporte --}}
    <div class="modal fade" id="modalReporte" tabindex="-1">
        <div class="modal-dialog modal-xl">
            <div class="modal-content">
                <div class="modal-header bg-success">
                    <h5 class="modal-title">Reporte General de Sistemas</h5>
                    <button type="button" class="close" data-dismiss="modal">&times;</button>
                </div>
                <div class="modal-body">

                    <table class="table table-bordered table-striped" id="reporte-table">
                        <thead>
                            <tr>
                                <th>Sistema</th>
                                <th>Equipo</th>
                                <th>Total Dirigentes</th>
                                <th>Total Punteros</th>
                                <th>Total Votantes</th>
                            </tr>
                        </thead>
                        <tbody>

                        </tbody>
                    </table>

                </div>
            </div>
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

            // Verificar si $userRoles existe y es un array
            if(!empty($userRoles) && is_array($userRoles)){
                $selected = in_array($item, $userRoles);
            } else {
                // Si $userRoles no existe, comparamos con 'Administrador General'
                $selected = ($item === 'Administrador General');
            } @endphp
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

        $('#modalSistema').on('shown.bs.modal', function() {
            $('#id_ciudad_electoral').select2({
                width: '100%',
                dropdownParent: $('#modalSistema'),
                placeholder: 'Seleccionar Ciudad',
                allowClear: true
            });
            $('#candidatosup').select2({
                width: '100%',
                dropdownParent: $('#modalSistema'),
                allowClear: true
            });
        });

        function abrirModalReporte() {
            $('#modalReporte').modal('show');

            $.get("{{ route('reportes.totalesporSistema') }}", function(data) {
                let tbody = $('#reporte-table tbody');
                tbody.empty();

                data.forEach(d => {
                    let rowClass = d.es_total ? 'table-success font-weight-bold' : '';
                    tbody.append(`
                <tr class="${rowClass}">
                    <td>${d.sistema}</td>
                    <td>${d.equipo}</td>
                    <td>${d.dirigentes}</td>
                    <td>${d.punteros}</td>
                    <td>${d.votantes}</td>
                </tr>
            `);
                });

                // Inicializar DataTable si no estaba inicializado
                if (!$.fn.DataTable.isDataTable('#reporte-table')) {
                    $('#reporte-table').DataTable({
                        responsive: true,
                        dom: 'Bfrtip',
                        buttons: [{
                            extend: 'excelHtml5',
                            text: 'Exportar a Excel',
                            className: 'btn btn-success btn-sm'
                        }],
                        language: {
                            url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                        }
                    });
                }
            });
        }



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
                searching: true
            });
        });

        function editarSistema(id, nombre, ciudad, tipo, idusuario) {

            $('#sistema_id').val(id);
            $('#nombre_sistema').val(nombre);

            // seleccionar ciudad
            $('#id_ciudad_electoral').val(ciudad).trigger('change');

            $('#candidatosup').val(idusuario).trigger('change');
            // seleccionar tipo
            $('#tipo').val(tipo);

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
