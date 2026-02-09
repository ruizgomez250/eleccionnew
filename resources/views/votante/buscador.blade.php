<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Consulta Padrón</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        body {
            background: #f4f6f9;
        }

        .resultado-card {
            display: none;
        }
    </style>
</head>

<body>

<div class="container mt-5">

    <div class="row justify-content-center">
        <div class="col-md-6">

            <div class="card shadow-sm">
                <div class="card-body">

                    <h5 class="mb-3 text-center">
                        <i class="fas fa-search text-primary"></i> Consulta Pre Padron
                    </h5>

                    <div class="input-group mb-3">
                        <input type="text" id="cedula" class="form-control"
                               placeholder="Ingrese cédula">
                        <button class="btn btn-primary" id="btnBuscar">
                            <i class="fas fa-search"></i>
                        </button>
                    </div>

                    <div id="mensaje" class="text-danger text-center"></div>

                </div>
            </div>

        </div>
    </div>

    {{-- Resultado --}}
    <div class="row justify-content-center mt-4">
        <div class="col-md-8">

            <div class="card resultado-card shadow-sm" id="resultado">
                <div class="card-header bg-primary text-white">
                    <i class="fas fa-user"></i> Datos del ciudadano
                </div>

                <div class="card-body">
                    <table class="table table-sm table-bordered">
                        <tr>
                            <th>Cédula</th>
                            <td id="r-cedula"></td>
                        </tr>
                        <tr>
                            <th>Nombre</th>
                            <td id="r-nombre"></td>
                        </tr>
                        <tr>
                            <th>Apellido</th>
                            <td id="r-apellido"></td>
                        </tr>
                        <tr>
                            <th>Local Interna</th>
                            <td id="r-local-interna"></td>
                        </tr>
                        <tr>
                            <th>Local Generales</th>
                            <td id="r-local-generales"></td>
                        </tr>
                        <tr>
                            <th>Dirección</th>
                            <td id="r-direccion"></td>
                        </tr>
                        <tr>
                            <th>Afiliaciones</th>
                            <td id="r-afiliaciones"></td>
                        </tr>
                    </table>
                </div>
            </div>

        </div>
    </div>

</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<script>
    $('#btnBuscar').on('click', buscar);
    $('#cedula').on('keypress', function (e) {
        if (e.which === 13) buscar();
    });

    function buscar() {

        let cedula = $('#cedula').val().trim();
        $('#mensaje').text('');
        $('#resultado').hide();

        if (!cedula) {
            $('#mensaje').text('Ingrese una cédula');
            return;
        }

        $.ajax({
            url: "{{ route('votante.buscar.simple') }}",
            type: "POST",
            data: {
                cedula: cedula,
                _token: "{{ csrf_token() }}"
            },
            success: function (res) {

                if (!res.encontrado) {
                    $('#mensaje').text('No se encontró registro para esa cédula');
                    return;
                }

                $('#r-cedula').text(res.data.cedula);
                $('#r-nombre').text(res.data.nombre);
                $('#r-apellido').text(res.data.apellido);
                $('#r-local-interna').text(res.data.local_interna);
                $('#r-local-generales').text(res.data.local_generales);
                $('#r-direccion').text(res.data.direccion);
                $('#r-afiliaciones').text(res.data.afiliaciones);

                $('#resultado').fadeIn();
            },
            error: function () {
                $('#mensaje').text('Error al consultar');
            }
        });
    }
</script>

</body>
</html>
