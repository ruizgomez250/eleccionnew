<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Error - Cargar Votos</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            background: #f8f9fa;
            font-family: 'Source Sans Pro', sans-serif;
        }
        .error-card {
            max-width: 500px;
            width: 100%;
            border: none;
            border-radius: 8px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
        }
        .error-icon {
            font-size: 48px;
            color: #dc3545;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card error-card">
                    <div class="card-body text-center py-5">
                        <div class="error-icon">
                            <i class="fas fa-exclamation-triangle"></i>
                        </div>
                        <h4 class="text-danger mb-3">Error</h4>
                        <p class="text-muted mb-0">{{ $error ?? 'No se puede abrir la página. El enlace no es válido o ha expirado.' }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>