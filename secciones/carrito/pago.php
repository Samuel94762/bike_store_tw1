<?php
include("../../config.php");

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    header("Location: " . APP_URL . "login.php");
    exit;
}

// Verificar que hay items en el carrito
if (!isset($_SESSION['carrito']) || count($_SESSION['carrito']) == 0) {
    header("Location: index.php");
    exit;
}

// Calcular totales
$subtotal = 0;
$carrito_items = $_SESSION['carrito'];

foreach ($carrito_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}

$impuesto = 0; // IVA removed per request
$total = $subtotal; // total equals subtotal when tax is removed

// Si es POST, crear la orden en estado 'Pendiente' y mostrar opciones de pago
if ($_POST && isset($_POST['procesar_pago'])) {
    // Obtener datos del cliente
    // Buscar un cliente asociado a alguna orden previa del usuario (evitar subqueries con LIMIT en MariaDB)
    $sentencia = $conexion->prepare("SELECT c.* FROM customers c
        INNER JOIN orders o ON c.customer_id = o.customer_id
        WHERE o.user_id = :user_id
        LIMIT 1");
    $sentencia->bindValue(':user_id', $_SESSION['user_id']);
    $sentencia->execute();
    $cliente = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Si no existe cliente, crear uno
    if (!$cliente) {
        // Para este ejemplo, usaremos datos básicos del usuario
        // En producción, deberías tener un sistema de datos de cliente más robusto
        $email = $_SESSION['email'];
        
        $sentencia = $conexion->prepare("INSERT INTO customers (first_name, last_name, email) 
                                        VALUES (:first_name, :last_name, :email)");
        $sentencia->bindValue(':first_name', $_SESSION['usuario']);
        $sentencia->bindValue(':last_name', '');
        $sentencia->bindValue(':email', $email);
        $sentencia->execute();
        
        $customer_id = $conexion->lastInsertId();
    } else {
        $customer_id = $cliente['customer_id'];
    }
    
    // Crear la orden (almacenar en DB el monto sin impuesto — solo el precio/subtotal)
    // Estado inicial: 'Pendiente' hasta que se confirme el pago (simulado)
    $order_date = date('Y-m-d');
    $sentencia = $conexion->prepare("INSERT INTO orders (customer_id, order_date, user_id, estado, total_amount) 
                                    VALUES (:customer_id, :order_date, :user_id, 'Pendiente', :total_amount)");
    $sentencia->bindValue(':customer_id', $customer_id);
    $sentencia->bindValue(':order_date', $order_date);
    $sentencia->bindValue(':user_id', $_SESSION['user_id']);
    // Guardamos el subtotal (solo precio) en la columna total_amount para mantener consistencia con pedidos manuales
    $sentencia->bindValue(':total_amount', $subtotal);
    $sentencia->execute();
    
    $order_id = $conexion->lastInsertId();
    
    // Agregar items a la orden
    foreach ($carrito_items as $item) {
        $sentencia = $conexion->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount) 
                        VALUES (:order_id, :product_id, :quantity, :price, 0)");
        $sentencia->bindValue(':order_id', $order_id);
        $sentencia->bindValue(':product_id', $item['product_id']);
        $sentencia->bindValue(':quantity', $item['quantity']);
        $sentencia->bindValue(':price', $item['price']);
        $sentencia->execute();
    }
    
    // Guardar order_id y monto en sesión para procesamiento posterior
    $_SESSION['pending_order_id'] = $order_id;
    $_SESSION['pending_order_total'] = $total; // monto total a pagar
    // No redirigimos: el formulario seguirá mostrando las opciones de pago y el usuario
    // deberá confirmar el método. El procesamiento se hará por AJAX y será simulado.
    $payment_created = true;
}
else {
    $payment_created = isset($_SESSION['pending_order_id']);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pago - <?php echo APP_NAME; ?></title>
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
        .payment-method {
            border: 2px solid #ddd;
            padding: 20px;
            margin-bottom: 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
        }
        .payment-method:hover {
            border-color: #007bff;
            background-color: #f8f9ff;
        }
        .payment-method.active {
            border-color: #007bff;
            background-color: #e7f3ff;
        }
        .order-summary {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
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
        </div>
    </nav>

    <main class="py-5">
        <div class="container">
            <h1 class="mb-4"><i class="bi bi-credit-card"></i> Completar Pago</h1>

            <div class="row">
                <div class="col-lg-8">
                    <!-- Métodos de Pago -->
                    <div class="mb-4">
                        <h4 class="mb-3">Selecciona Método de Pago</h4>
                        
                        <form id="paymentForm">
                            <div class="payment-method active" onclick="selectPayment('card')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="card" value="card" checked>
                                    <label class="form-check-label w-100" for="card">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-credit-card" style="font-size: 2em; color: #0d6efd;"></i>
                                            <div class="ms-3">
                                                <h6 class="mb-0">Tarjeta</h6>
                                                <small class="text-muted">Paga con tu tarjeta (simulado)</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('paypal')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="paypal" value="paypal">
                                    <label class="form-check-label w-100" for="paypal">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-paypal" style="font-size: 2em; color: #003087;"></i>
                                            <div class="ms-3">
                                                <h6 class="mb-0">PayPal</h6>
                                                <small class="text-muted">Pagar con PayPal (simulado)</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <div class="payment-method" onclick="selectPayment('cash')">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="payment_method" 
                                           id="cash" value="cash">
                                    <label class="form-check-label w-100" for="cash">
                                        <div class="d-flex align-items-center">
                                            <i class="bi bi-cash-stack" style="font-size: 2em; color: #198754;"></i>
                                            <div class="ms-3">
                                                <h6 class="mb-0">Efectivo</h6>
                                                <small class="text-muted">Pago en efectivo al recoger</small>
                                            </div>
                                        </div>
                                    </label>
                                </div>
                            </div>

                            <input type="hidden" name="procesar_pago" value="1">
                            <button type="button" id="processPaymentBtn" class="btn btn-primary btn-lg w-100 mt-3">
                                <i class="bi bi-credit-card"></i> Procesar Pago - $<?php echo number_format($total, 2); ?>
                            </button>
                        </form>
                    </div>

                    <!-- Resumen de Orden -->
                    <div class="order-summary mt-4">
                        <h5 class="mb-3">Resumen de Orden</h5>
                        <table class="table table-sm">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Cantidad</th>
                                    <th>Precio</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($carrito_items as $item): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($item['product_name']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td>$<?php echo number_format($item['price'], 2); ?></td>
                                    <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="col-lg-4">
                    <div class="order-summary">
                        <h5 class="mb-3">Resumen de Compra</h5>
                        <hr>
                        <div class="d-flex justify-content-between mb-2">
                            <span>Subtotal:</span>
                            <span>$<?php echo number_format($subtotal, 2); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span>Impuesto (16%):</span>
                            <span>$<?php echo number_format($impuesto, 2); ?></span>
                        </div>
                        <hr>
                        <div class="d-flex justify-content-between mb-4">
                            <span class="fw-bold">Total a Pagar:</span>
                            <span class="fw-bold" style="color: #198754; font-size: 1.3em;">$<?php echo number_format($total, 2); ?></span>
                        </div>

                        <div class="alert alert-info">
                            <i class="bi bi-info-circle"></i>
                            <small>
                                <strong>Modo Sandbox:</strong> Este es un entorno de prueba. Usa credenciales de PayPal Sandbox para probar.
                            </small>
                        </div>

                        <a href="index.php" class="btn btn-outline-secondary w-100">
                            <i class="bi bi-arrow-left"></i> Volver al Carrito
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2024 <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function selectPayment(method) {
            document.querySelectorAll('.payment-method').forEach(el => el.classList.remove('active'));
            const input = document.querySelector('input[name=payment_method][value="' + method + '"]');
            if (input) {
                input.checked = true;
                // find the .payment-method ancestor
                let node = input.closest('.payment-method');
                if (node) node.classList.add('active');
            }
        }

        document.getElementById('processPaymentBtn').addEventListener('click', function(){
            const methodInput = document.querySelector('input[name=payment_method]:checked');
            const method = methodInput ? methodInput.value : null;
            if (!method) {
                Swal.fire('Seleccione método','Por favor elija un método de pago','warning');
                return;
            }

            Swal.fire({
                title: 'Procesar pago',
                text: 'Método: ' + method + '. ¿Deseas continuar?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonText: 'Sí, procesar',
            }).then(result => {
                if (!result.isConfirmed) return;

                Swal.fire({title: 'Procesando...', didOpen: ()=>{Swal.showLoading();}});

                fetch('process_simulated_payment.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({method: method})
                }).then(r => r.json()).then(data => {
                    if (data.success) {
                        Swal.fire('Pago procesado', data.message || 'Pago completado correctamente', 'success').then(()=>{
                            window.location.href = 'exito.php?order_id=' + encodeURIComponent(data.order_id);
                        });
                    } else {
                        Swal.fire('Error', data.message || 'No se pudo procesar el pago', 'error');
                    }
                }).catch(err => {
                    Swal.fire('Error', 'Error de red al procesar pago', 'error');
                });
            });
        });
    </script>
</body>
</html>
