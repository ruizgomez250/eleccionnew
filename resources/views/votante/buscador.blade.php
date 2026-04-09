<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=yes">
    <title>Consulta Padrón Electoral</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.6.0/css/all.min.css" rel="stylesheet">

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .container-custom {
            max-width: 600px;
            margin: 0 auto;
        }

        /* Tarjeta de búsqueda */
        .search-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            margin-bottom: 25px;
            transition: transform 0.3s ease;
        }

        .search-card:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 25px 20px;
            text-align: center;
            color: white;
        }

        .card-header-custom h2 {
            font-size: 1.5rem;
            margin: 0;
            font-weight: 600;
        }

        .card-header-custom p {
            margin: 5px 0 0;
            font-size: 0.85rem;
            opacity: 0.9;
        }

        .search-box {
            padding: 30px 25px;
        }

        .input-group-custom {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .input-group-custom input {
            flex: 1;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .input-group-custom input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .btn-search {
            padding: 15px 30px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-search:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .btn-search:active {
            transform: translateY(0);
        }

        /* Tarjeta de resultado */
        .result-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.2);
            overflow: hidden;
            display: none;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .result-header {
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            padding: 20px;
            color: white;
        }

        .result-header h3 {
            margin: 0;
            font-size: 1.3rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .result-header i {
            font-size: 1.5rem;
        }

        .result-body {
            padding: 20px;
        }

        /* Grid de información */
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .info-item {
            background: #f8f9fa;
            border-radius: 12px;
            padding: 15px;
            transition: all 0.3s ease;
        }

        .info-item:hover {
            transform: translateX(5px);
            background: #e9ecef;
        }

        /* Estilo especial para mesa y orden */
        .info-item.highlight {
            background: linear-gradient(135deg, #fff5e6 0%, #ffe6cc 100%);
            border-left: 4px solid #ff9800;
        }

        .info-item.highlight .info-value {
            font-size: 1.2rem;
            color: #e65100;
            font-weight: 700;
        }

        .info-label {
            font-size: 0.75rem;
            text-transform: uppercase;
            font-weight: 700;
            color: #6c757d;
            margin-bottom: 8px;
            letter-spacing: 0.5px;
        }

        .info-label i {
            margin-right: 5px;
        }

        .info-value {
            font-size: 1rem;
            font-weight: 600;
            color: #333;
            word-wrap: break-word;
        }

        /* Mensajes de error */
        .alert-message {
            background: #fee;
            border-left: 4px solid #f44336;
            padding: 12px 15px;
            border-radius: 8px;
            margin-top: 15px;
            display: none;
        }

        /* Loading spinner */
        .loading {
            display: none;
            text-align: center;
            padding: 20px;
        }

        .spinner {
            width: 40px;
            height: 40px;
            border: 4px solid #f3f3f3;
            border-top: 4px solid #667eea;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            margin: 0 auto;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Responsive para móviles */
        @media (max-width: 768px) {
            body {
                padding: 10px;
            }

            .card-header-custom h2 {
                font-size: 1.2rem;
            }

            .search-box {
                padding: 20px;
            }

            .input-group-custom {
                flex-direction: column;
            }

            .btn-search {
                width: 100%;
            }

            .info-grid {
                grid-template-columns: 1fr;
                gap: 10px;
            }

            .result-header h3 {
                font-size: 1.1rem;
            }

            .info-value {
                font-size: 0.9rem;
            }
        }

        /* Para tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .container-custom {
                max-width: 800px;
            }

            .info-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }

        /* Footer */
        .footer {
            text-align: center;
            margin-top: 30px;
            color: white;
            font-size: 0.8rem;
            opacity: 0.8;
        }

        /* Botón de limpiar */
        .btn-clear {
            background: #6c757d;
            margin-top: 10px;
        }

        .btn-clear:hover {
            background: #5a6268;
        }
    </style>
</head>

<body>
    <div class="container-custom">
        <!-- Tarjeta de búsqueda -->
        <div class="search-card">
            <div class="card-header-custom">
                <i class="fas fa-vote-yea" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <h2>Consulta al Padrón Electoral</h2>
                <p>Ingrese su número de cédula para verificar sus datos</p>
            </div>

            <div class="search-box">
                <div class="input-group-custom">
                    <input type="text" id="cedula" class="form-control" 
                           placeholder="Número de cédula" 
                           autocomplete="off"
                           inputmode="numeric">
                    <button class="btn-search" id="btnBuscar">
                        <i class="fas fa-search"></i> Buscar
                    </button>
                </div>

                <div id="mensaje" class="alert-message"></div>
                <div class="loading" id="loading">
                    <div class="spinner"></div>
                    <p style="margin-top: 10px; color: #666;">Buscando...</p>
                </div>
            </div>
        </div>

        <!-- Tarjeta de resultado -->
        <div class="result-card" id="resultado">
            <div class="result-header">
                <h3>
                    <i class="fas fa-user-check"></i>
                    Datos del Ciudadano
                </h3>
            </div>

            <div class="result-body">
                <div class="info-grid">
                    <!-- Cédula -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-id-card"></i> Número de Cédula
                        </div>
                        <div class="info-value" id="r-cedula">-</div>
                    </div>

                    <!-- Nombre -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user"></i> Nombre
                        </div>
                        <div class="info-value" id="r-nombre">-</div>
                    </div>

                    <!-- Apellido -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-user-tag"></i> Apellido
                        </div>
                        <div class="info-value" id="r-apellido">-</div>
                    </div>

                    <!-- MESA - Destacada -->
                    <div class="info-item highlight">
                        <div class="info-label">
                            <i class="fas fa-table"></i> 📍 Número de Mesa
                        </div>
                        <div class="info-value" id="r-mesa">-</div>
                    </div>

                    <!-- ORDEN - Destacada -->
                    <div class="info-item highlight">
                        <div class="info-label">
                            <i class="fas fa-sort-numeric-up"></i> 🔢 Orden en Mesa
                        </div>
                        <div class="info-value" id="r-orden">-</div>
                    </div>

                    <!-- Local Interna -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-building"></i> Local de Votación (Interna)
                        </div>
                        <div class="info-value" id="r-local-interna">-</div>
                    </div>

                    <!-- Local Generales -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-church"></i> Local de Votación (Generales)
                        </div>
                        <div class="info-value" id="r-local-generales">-</div>
                    </div>

                    <!-- Dirección -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-map-marker-alt"></i> Dirección
                        </div>
                        <div class="info-value" id="r-direccion">-</div>
                    </div>

                    <!-- Afiliaciones -->
                    <div class="info-item">
                        <div class="info-label">
                            <i class="fas fa-handshake"></i> Afiliaciones
                        </div>
                        <div class="info-value" id="r-afiliaciones">-</div>
                    </div>
                </div>
            </div>
        </div>

        <div class="footer">
            <p><i class="fas fa-check-circle"></i> Datos actualizados al 2025</p>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

    <script>
        $(document).ready(function() {
            // Evento click en botón buscar
            $('#btnBuscar').on('click', buscar);
            
            // Evento tecla Enter en el input
            $('#cedula').on('keypress', function(e) {
                if (e.which === 13) buscar();
            });
            
            // Solo números en el campo cédula
            $('#cedula').on('input', function() {
                this.value = this.value.replace(/[^0-9]/g, '');
            });
        });

        function buscar() {
            let cedula = $('#cedula').val().trim();
            
            // Ocultar resultado anterior
            $('#resultado').hide();
            $('.alert-message').hide();
            
            // Validaciones
            if (!cedula) {
                mostrarMensaje('Por favor, ingrese un número de cédula', 'error');
                return;
            }

            if (!/^\d+$/.test(cedula)) {
                mostrarMensaje('La cédula debe contener solo números', 'error');
                return;
            }

            // Mostrar loading
            $('#loading').show();

            // Realizar petición AJAX
            $.ajax({
                url: "{{ route('votante.buscar.simple') }}",
                type: "POST",
                data: {
                    cedula: cedula,
                    _token: "{{ csrf_token() }}"
                },
                success: function(res) {
                    $('#loading').hide();

                    if (!res.encontrado) {
                        mostrarMensaje('No se encontró ningún registro para la cédula ' + cedula, 'error');
                        return;
                    }

                    // Los datos vienen directamente en res.data
                    const data = res.data;
                    
                    // Debug: Ver en consola qué datos se recibieron
                    console.log('Datos recibidos:', data);
                    
                    // Llenar los campos en la vista
                    $('#r-cedula').text(data.cedula || '-');
                    $('#r-nombre').text(data.nombre || '-');
                    $('#r-apellido').text(data.apellido || '-');
                    $('#r-mesa').text(data.mesa || '-');
                    $('#r-orden').text(data.orden || '-');
                    $('#r-local-interna').text(data.local_interna || '-');
                    $('#r-local-generales').text(data.local_generales || '-');
                    $('#r-direccion').text(data.direccion || '-');
                    $('#r-afiliaciones').text(data.afiliaciones || '-');

                    // Mostrar resultado con animación
                    $('#resultado').fadeIn();
                    
                    // Scroll suave al resultado
                    $('html, body').animate({
                        scrollTop: $('#resultado').offset().top - 20
                    }, 500);
                },
                error: function(xhr, status, error) {
                    $('#loading').hide();
                    console.error('Error en la petición:', error);
                    console.error('Respuesta del servidor:', xhr.responseText);
                    mostrarMensaje('Error al consultar el servidor. Intente nuevamente.', 'error');
                }
            });
        }

        function mostrarMensaje(mensaje, tipo) {
            const $mensaje = $('#mensaje');
            $mensaje.text(mensaje);
            $mensaje.css({
                'background': tipo === 'error' ? '#fee' : '#e8f5e9',
                'border-left-color': tipo === 'error' ? '#f44336' : '#4caf50',
                'color': tipo === 'error' ? '#d32f2f' : '#2e7d32'
            });
            $mensaje.fadeIn();
            
            // Ocultar después de 5 segundos
            setTimeout(() => {
                $mensaje.fadeOut();
            }, 5000);
        }
    </script>
</body>

</html>