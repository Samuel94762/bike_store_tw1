<?php
include("../../config.php");

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    header("Location: " . APP_URL . "login.php");
    exit;
}

// Procesar eliminación de producto
if (isset($_GET['eliminar'])) {
    $product_id = intval($_GET['eliminar']);
    if (isset($_SESSION['carrito'])) {
        foreach ($_SESSION['carrito'] as $key => $item) {
            if ($item['product_id'] == $product_id) {
                unset($_SESSION['carrito'][$key]);
                break;
            }
        }
        $_SESSION['carrito'] = array_values($_SESSION['carrito']); // Reindexar
    }
    header("Location: index.php?mensaje=Producto eliminado");
    exit;
}

// Procesar actualización de cantidad
if ($_POST && isset($_POST['actualizar_cantidad'])) {
    $product_id = intval($_POST['product_id']);
    $quantity = intval($_POST['quantity']);
    
    if ($quantity <= 0) {
        // Eliminar producto
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as $key => $item) {
                if ($item['product_id'] == $product_id) {
                    unset($_SESSION['carrito'][$key]);
                    break;
                }
            }
            $_SESSION['carrito'] = array_values($_SESSION['carrito']);
        }
    } else {
        // Actualizar cantidad
        if (isset($_SESSION['carrito'])) {
            foreach ($_SESSION['carrito'] as &$item) {
                if ($item['product_id'] == $product_id) {
                    $item['quantity'] = $quantity;
                    break;
                }
            }
        }
    }
    // Usar PRG para evitar reenvío de formulario y asegurar recálculo consistente
    header('Location: index.php');
    exit;
}

// Calcular totales
$subtotal = 0;
$carrito_items = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : array();

foreach ($carrito_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$total = $subtotal;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mi Carrito - <?php echo APP_NAME; ?></title>
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
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .product-item {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .product-img {
            max-height: 100px;
            object-fit: cover;
            border-radius: 5px;
        }
        .cart-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: sticky;
            top: 20px;
        }
        .empty-cart {
            text-align: center;
            padding: 40px 20px;
        }
        .empty-cart i {
            font-size: 4em;
            color: #ccc;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #06356bff;">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>landing.php">
                <i class="bi bi-bicycle"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>landing.php">
                            <i class="bi bi-arrow-left"></i> Continuar Comprando
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle"></i> <?php echo $_SESSION['usuario']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>secciones/pedidos/">Mis Pedidos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>cerrar.php">Cerrar Sesión</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <?php if(isset($_GET['mensaje'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($_GET['mensaje']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <h1 class="mb-4"><i class="bi bi-cart3"></i> Mi Carrito</h1>

            <?php if(count($carrito_items) > 0): ?>
                <div class="row">
                    <div class="col-lg-8">
                        <?php foreach($carrito_items as $item): ?>
                        <div class="product-item">
                            <div class="row align-items-center">
                                <div class="col-md-2">
                                    <img src="../productos/imagen/<?php echo htmlspecialchars($item['foto']); ?>" 
                                         class="product-img w-100"
                                         onerror="this.src='https://via.placeholder.com/100x100?text=No+imagen'"
                                         alt="<?php echo htmlspecialchars($item['product_name']); ?>">
                                </div>
                                <div class="col-md-4">
                                    <h6><?php echo htmlspecialchars($item['product_name']); ?></h6>
                                    <p class="text-muted mb-0">Precio: $<?php echo number_format($item['price'], 2); ?></p>
                                </div>
                                <div class="col-md-2">
                                    <form method="post" class="d-flex gap-2">
                                        <input type="hidden" name="product_id" value="<?php echo $item['product_id']; ?>">
                                        <input type="hidden" name="actualizar_cantidad" value="1">
                                        <input type="number" name="quantity" class="form-control form-control-sm" 
                                               value="<?php echo $item['quantity']; ?>" min="0" max="99" style="width: 70px;">
                                        <button type="submit" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-arrow-repeat"></i>
                                        </button>
                                    </form>
                                </div>
                                <div class="col-md-2">
                                    <p class="fw-bold mb-0">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></p>
                                    <small class="text-muted"><?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></small>
                                </div>
                                <div class="col-md-2 text-end">
                                    <a href="index.php?eliminar=<?php echo $item['product_id']; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('¿Eliminar este producto?')">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="col-lg-4">
                        <div class="cart-summary">
                            <h5 class="mb-3">Resumen de Compra</h5>
                            <hr>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Subtotal:</span>
                                <span>$<?php echo number_format($subtotal, 2); ?></span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-4">
                                <span class="fw-bold">Total:</span>
                                <span class="fw-bold" style="color: #198754; font-size: 1.3em;">$<?php echo number_format($total, 2); ?></span>
                            </div>
                            
                            <a href="<?php echo APP_URL; ?>landing.php" class="btn btn-outline-primary w-100 mb-2">
                                <i class="bi bi-arrow-left"></i> Continuar Comprando
                            </a>
                            <a href="pago.php" class="btn btn-primary w-100">
                                <i class="bi bi-credit-card"></i> Proceder al Pago
                            </a>
                        </div>
                    </div>
                </div>
            <?php else: ?>
                <div class="empty-cart">
                    <i class="bi bi-cart-x"></i>
                    <h3>Tu carrito está vacío</h3>
                    <p class="text-muted">No tienes productos en el carrito. ¡Ve y agrega algunos!</p>
                    <a href="<?php echo APP_URL; ?>landing.php" class="btn btn-primary btn-lg">
                        <i class="bi bi-arrow-left"></i> Volver a Productos
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
