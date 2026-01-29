<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Buscador de Votantes</title>

    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">

    <!-- Font Awesome para iconos -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">

</head>
<body>
    <div class="container mt-4">
        <h1 class="mb-4"><i class="fas fa-user-friends text-primary"></i> Buscador de Votantes</h1>

        <!-- Filtro por puntero o cedula -->
        <div class="row mb-3">
            <div class="col-md-4">
                <input type="text" id="filtro_cedula" class="form-control" placeholder="Filtrar por cédula">
            </div>
            <div class="col-md-4">
                <input type="text" id="filtro_nombre" class="form-control" placeholder="Filtrar por nombre">
            </div>
            <div class="col-md-4">
                <button id="btn_filtrar" class="btn btn-primary"><i class="fas fa-search"></i> Filtrar</button>
            </div>
        </div>

        <!-- Tabla DataTables -->
        <table id="tablaVotantes" class="table table-striped table-bordered" style="width:100%">
            <thead>
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Dirección</th>
                    <th>Partido</th>
                    <th>Mesa</th>
                    <th>Acciones</th>
                </tr>
            </thead>
        </table>
    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>

    <script>
        $(document).ready(function() {
            // Inicializar DataTable con server-side
            var tabla = $('#tablaVotantes').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: '{{ route("votante.datatables") }}',
                    data: function(d) {
                        d.cedula = $('#filtro_cedula').val();
                        d.nombre = $('#filtro_nombre').val();
                    }
                },
                columns: [
                    { data: 'cedula', name: 'cedula' },
                    { data: 'nombre', name: 'nombre' },
                    { data: 'direccion', name: 'direccion' },
                    { data: 'partido', name: 'partido' },
                    { data: 'mesa', name: 'mesa' },
                    { data: 'acciones', name: 'acciones', orderable: false, searchable: false }
                ],
                order: [[1, 'asc']]
            });

            // Botón filtrar
            $('#btn_filtrar').click(function() {
                tabla.draw();
            });

            // Filtrar al presionar Enter
            $('#filtro_cedula, #filtro_nombre').on('keyup', function(e) {
                if (e.key === 'Enter') {
                    tabla.draw();
                }
            });
        });
    </script>
</body>
</html>
