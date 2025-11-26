<?php
include("../../config.php");

// Limpiar sesión de pago
unset($_SESSION['pending_order_id']);
unset($_SESSION['pending_order_total']);
unset($_SESSION['paypal_order_id']);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Cancelado - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        main {
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .status-card {
            text-align: center;
            padding: 40px;
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .status-icon {
            font-size: 4em;
            color: #dc3545;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #06356bff;">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>landing.php">
                <i class="bi bi-bicycle"></i> <?php echo APP_NAME; ?>
            </a>
        </div>
    </nav>

    <main>
        <div class="container">
            <div class="row">
                <div class="col-md-6 offset-md-3">
                    <div class="status-card">
                        <div class="status-icon">
                            <i class="bi bi-x-circle"></i>
                        </div>
                        <h2 class="mb-3">Pago Cancelado</h2>
                        <p class="text-muted mb-4">
                            Has cancelado el proceso de pago. Tu orden no ha sido completada.
                        </p>
                        
                        <div class="alert alert-info" role="alert">
                            <i class="bi bi-info-circle"></i> Tus productos siguen en el carrito. Puedes intentar de nuevo cuando estés listo.
                        </div>

                        <a href="index.php" class="btn btn-primary btn-lg me-2">
                            <i class="bi bi-arrow-left"></i> Volver al Carrito
                        </a>
                        <a href="<?php echo APP_URL; ?>landing.php" class="btn btn-outline-primary btn-lg">
                            <i class="bi bi-shop"></i> Continuar Comprando
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
