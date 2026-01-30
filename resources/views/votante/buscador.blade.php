<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Consulta pre padron</title>

    <!-- Bootstrap -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">

    <!-- DataTables -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css">

    <style>
        body {
            padding: 20px;
        }

        .dataTables_processing {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 6px;
            padding: 10px;
        }
    </style>
</head>

<body>

    <div class="container">

        <h3 class="mb-4">
            <i class="fas fa-users text-primary"></i> Consulta pre Padron
        </h3>

        <!-- Buscador -->
        <div class="row mb-3">
            <div class="col-md-3">
                <input type="text" id="cedula" class="form-control" placeholder="Cédula">
            </div>

            <div class="col-md-4">
                <input type="text" id="nombre" class="form-control" placeholder="Nombre">
            </div>

            <div class="col-md-4">
                <input type="text" id="apellido" class="form-control" placeholder="Apellido">
            </div>

            <div class="col-md-1">
                <button id="btnSearch" class="btn btn-primary w-100">
                    <i class="fas fa-search"></i> Buscar
                </button>
            </div>
        </div>

        <!-- Tabla -->
        <table id="votantesTable" class="table table-bordered table-striped w-100">
            <thead class="table-light">
                <tr>
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Dirección</th>
                    <th>Afiliaciones</th>
                </tr>
            </thead>
            <tbody></tbody>
        </table>


    </div>

    <!-- jQuery -->
    <script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>

    <!-- Bootstrap -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>

    <script>
        $(document).ready(function() {

            let table = $('#votantesTable').DataTable({
                processing: true,
                serverSide: true,
                searching: false,
                ordering: true,
                deferLoading: 0, // tabla vacía al inicio
                ajax: {
                    url: "{{ route('votantes.datatables') }}", // ruta con nombre
                    type: 'GET',
                    data: function(d) {
                        d.cedula = $('#cedula').val();
                        d.nombre = $('#nombre').val();
                        d.apellido = $('#apellido').val();
                    },
                    error: function(xhr) {
                        console.error(xhr.responseText);
                    }
                },
                columns: [{
                        data: 'cedula'
                    },
                    {
                        data: 'nombre'
                    },
                    {
                        data: 'apellido'
                    },
                    {
                        data: 'direccion'
                    },
                    {
                        data: 'afiliaciones'
                    }
                ],
                language: {
                    processing: "Buscando...",
                    emptyTable: "Ingrese cédula, nombre o apellido para buscar",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    paginate: {
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                }
            });

            function buscar() {
                if (!$('#cedula').val() && !$('#nombre').val() && !$('#apellido').val()) {
                    alert('Debe ingresar algún dato para buscar');
                    return;
                }
                table.ajax.reload();
            }

            $('#btnSearch').on('click', buscar);

            $('#cedula, #nombre, #apellido').on('keypress', function(e) {
                if (e.which === 13) {
                    buscar();
                }
            });

        });
    </script>


</body>

</html>
