<?php
include("../../config.php");

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    header("Location: " . APP_URL . "login.php");
    exit;
}

$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

if ($order_id <= 0) {
    header("Location: " . APP_URL . "landing.php");
    exit;
}

// Obtener los detalles de la orden
$sentencia = $conexion->prepare("SELECT o.*, c.email, c.first_name, c.last_name 
                                FROM orders o
                                LEFT JOIN customers c ON o.customer_id = c.customer_id
                                WHERE o.order_id = :order_id AND o.user_id = :user_id");
$sentencia->bindParam(':order_id', $order_id);
$sentencia->bindParam(':user_id', $_SESSION['user_id']);
$sentencia->execute();
$orden = $sentencia->fetch(PDO::FETCH_ASSOC);

if (!$orden) {
    header("Location: " . APP_URL . "landing.php");
    exit;
}

// Obtener los items de la orden
$sentencia = $conexion->prepare("SELECT oi.*, p.product_name FROM order_items oi
                                LEFT JOIN products p ON oi.product_id = p.product_id
                                WHERE oi.order_id = :order_id");
$sentencia->bindParam(':order_id', $order_id);
$sentencia->execute();
$items = $sentencia->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago Exitoso - <?php echo APP_NAME; ?></title>
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
        }
        .success-card {
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .success-icon {
            font-size: 4em;
            color: #28a745;
            text-align: center;
            margin-bottom: 20px;
        }
        .order-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin: 20px 0;
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
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

    <main class="py-5">
        <div class="container">
            <div class="row">
                <div class="col-md-8 offset-md-2">
                    <div class="success-card">
                        <div class="success-icon">
                            <i class="bi bi-check-circle"></i>
                        </div>
                        
                        <h2 class="text-center mb-2">¡Pago Completado!</h2>
                        <p class="text-center text-muted mb-4">
                            Tu orden ha sido procesada exitosamente. Recibirás un correo de confirmación en breve.
                        </p>

                        <div class="order-details">
                            <div class="row mb-3">
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-ticket"></i> Número de Orden</h6>
                                    <p class="fw-bold">#<?php echo str_pad($order_id, 6, '0', STR_PAD_LEFT); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-calendar"></i> Fecha</h6>
                                    <p class="fw-bold"><?php echo date('d/m/Y H:i', strtotime($orden['order_date'])); ?></p>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-person"></i> Cliente</h6>
                                    <p><?php echo htmlspecialchars($orden['first_name'] . ' ' . $orden['last_name']); ?></p>
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-2"><i class="bi bi-envelope"></i> Email</h6>
                                    <p><?php echo htmlspecialchars($orden['email']); ?></p>
                                </div>
                            </div>
                        </div>

                        <h5 class="mt-4 mb-3">Productos Comprados</h5>
                        <table class="table table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio Unitario</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>

                        <div class="row mt-4">
                            <div class="col-md-6"></div>
                            <div class="col-md-6">
                                <div class="alert alert-info">
                                    <div class="d-flex justify-content-between mb-2">
                                        <span>Monto Total:</span>
                                        <span class="fw-bold">$<?php echo number_format($orden['total_amount'], 2); ?></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="<?php echo APP_URL; ?>secciones/pedidos/" class="btn btn-primary btn-lg me-2">
                                <i class="bi bi-box-seam"></i> Ver mis Pedidos
                            </a>
                            <a href="<?php echo APP_URL; ?>landing.php" class="btn btn-outline-primary btn-lg">
                                <i class="bi bi-shop"></i> Continuar Comprando
                            </a>
                        </div>
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
