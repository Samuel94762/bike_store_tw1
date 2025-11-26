<?php
// factura_web.php
if (!isset($conexion)) {
    include("../../bd.php");
}

if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
} else {
    $txtID = null;
}

// Consulta parametrizada para traer los pedidos
$sentencia = $conexion->prepare("SELECT CONCAT(c.first_name,' ',c.last_name) as cliente,c.customer_id,
o.order_date,u.usuario,o.total_amount,p.product_name,oi.quantity,oi.price,oi.discount,
((oi.quantity * oi.price) - ((oi.quantity * oi.price) * oi.discount * 100 /100)) as subtotal
FROM orders o
INNER JOIN order_items oi ON o.order_id = oi.order_id
INNER JOIN customers c ON o.customer_id = c.customer_id
INNER JOIN usuarios u ON o.user_id = u.user_id
INNER JOIN products p ON oi.product_id = p.product_id
WHERE o.order_id = :txtID");
$sentencia->bindValue(':txtID', $txtID, PDO::PARAM_INT);
$sentencia->execute();
$lista_pedidos_detalle = $sentencia->fetchAll(PDO::FETCH_ASSOC);

if (!$lista_pedidos_detalle || count($lista_pedidos_detalle) === 0) {
    echo '<p>Error: no se encontró la orden o no hay detalles para el pedido.</p>';
    exit;
}

$cliente = $lista_pedidos_detalle[0];

// Calcular el total
$total = 0;
foreach($lista_pedidos_detalle as $registro) {
    $total += $registro['subtotal'];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura Web - Orden #<?php echo $txtID; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }
        .invoice-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            margin: 20px auto;
            max-width: 1000px;
        }
        .invoice-header {
            background: linear-gradient(45deg, #06356b, #0a4da8);
            color: white;
            padding: 30px;
            text-align: center;
        }
        .invoice-body {
            padding: 30px;
        }
        .company-logo {
            font-size: 2.5rem;
            margin-bottom: 10px;
        }
        .customer-info {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
        }
        .table th {
            background: #06356b;
            color: white;
            border: none;
        }
        .total-section {
            background: #28a745;
            color: white;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }
        .qr-section {
            text-align: center;
            padding: 20px;
            border-top: 2px dashed #dee2e6;
            margin-top: 30px;
        }
    </style>
</head>
<body>
    <div class="invoice-container">
        <div class="invoice-header">
            <div class="company-logo">
                <i class="bi bi-bicycle"></i>
            </div>
            <h1>Best Bikes Ever</h1>
            <p class="mb-0">Factura Electrónica</p>
        </div>
        
        <div class="invoice-body">
            <!-- Información de la empresa -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <h5><i class="bi bi-building"></i> Información de la Empresa</h5>
                    <p class="mb-1"><strong>NIT:</strong> 3139568974</p>
                    <p class="mb-1"><strong>Dirección:</strong> Zona Norte Octavo Anillo</p>
                    <p class="mb-1"><strong>Teléfono:</strong> 68689162</p>
                    <p class="mb-0"><strong>Ciudad:</strong> Santa Cruz</p>
                </div>
                <div class="col-md-6">
                    <h5><i class="bi bi-receipt"></i> Datos de la Factura</h5>
                    <p class="mb-1"><strong>N° Factura:</strong> 4561</p>
                    <p class="mb-1"><strong>Orden #:</strong> <?php echo $txtID; ?></p>
                    <p class="mb-1"><strong>Fecha:</strong> <?php echo date('d/m/Y', strtotime($cliente["order_date"])); ?></p>
                    <p class="mb-0"><strong>Código Autorización:</strong> 1234567890123456789CUH9999</p>
                </div>
            </div>

            <!-- Información del cliente -->
            <div class="customer-info">
                <h5><i class="bi bi-person-circle"></i> Información del Cliente</h5>
                <div class="row">
                    <div class="col-md-4">
                        <p class="mb-1"><strong>Razón Social:</strong> <?php echo $cliente["cliente"]; ?></p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-1"><strong>NIT/CI/CEX:</strong> 79456123</p>
                    </div>
                    <div class="col-md-4">
                        <p class="mb-0"><strong>Código Cliente:</strong> <?php echo $cliente["customer_id"]; ?></p>
                    </div>
                </div>
            </div>

            <!-- Detalles de productos -->
            <h5 class="mb-3"><i class="bi bi-list-check"></i> Detalles de la Compra</h5>
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>Precio Unitario</th>
                            <th>Descuento</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $item = 1; foreach($lista_pedidos_detalle as $registro) { ?>
                        <tr>
                            <td><?php echo $item; ?></td>
                            <td><?php echo $registro['product_name']; ?></td>
                            <td><?php echo $registro['quantity']; ?></td>
                            <td>$<?php echo number_format($registro['price'], 2); ?></td>
                            <td><?php echo ($registro['discount'] * 100); ?>%</td>
                            <td>$<?php echo number_format($registro['subtotal'], 2); ?></td>
                        </tr>
                        <?php $item++; } ?>
                    </tbody>
                </table>
            </div>

            <!-- Total -->
            <div class="total-section text-end">
                <h3 class="mb-0">TOTAL: $<?php echo number_format($total, 2); ?></h3>
            </div>

            <!-- Mensaje legal -->
            <div class="qr-section">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i>
                    <strong>Esta factura contribuye al desarrollo del país, el uso ilícito de esta será sancionado de acuerdo a la ley</strong>
                </div>
                <p class="text-muted">Escanea el código QR para ver esta factura online</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>