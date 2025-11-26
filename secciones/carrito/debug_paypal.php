<?php
include("../../config.php");

// Verificar si el usuario está logueado
if (!is_logged_in() || !isset($_SESSION['pending_order_id'])) {
    echo "No hay orden pendiente en sesión";
    exit;
}

echo "<h2>Debug PayPal Request</h2>";

$order_id = $_SESSION['pending_order_id'];
$total = isset($_SESSION['pending_order_total']) ? $_SESSION['pending_order_total'] : 0;

echo "<p><strong>Order ID:</strong> $order_id</p>";
echo "<p><strong>Total:</strong> $total</p>";

// Construir la orden para PayPal
$carrito_items = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : array();

echo "<p><strong>Carrito Items:</strong></p>";
echo "<pre>";
print_r($carrito_items);
echo "</pre>";

$items = array();
foreach ($carrito_items as $item) {
    $items[] = array(
        'name' => $item['product_name'],
        'sku' => 'BIKE-' . $item['product_id'],
        'unit_amount' => array(
            'currency_code' => 'USD',
            'value' => (string)number_format($item['price'], 2, '.', '')
        ),
        'quantity' => (int)$item['quantity']
    );
}

// Si por alguna razón no existe el total en sesión, calcularlo desde el carrito
if (empty($total) || $total <= 0) {
    $total_calc = 0;
    foreach ($carrito_items as $item) {
        $total_calc += $item['price'] * $item['quantity'];
    }
    $total = $total_calc;
}

$amount_value = (string)number_format($total, 2, '.', '');

// Construir amount sin breakdown para evitar MALFORMED_REQUEST_JSON
$amount_array = array(
    'currency_code' => 'USD',
    'value' => $amount_value
);

$purchase_units = array(
    array(
        'reference_id' => 'ORDER-' . $order_id,
        'amount' => $amount_array,
        'items' => $items
    )
);

$order_data = array(
    'intent' => 'CAPTURE',
    'purchase_units' => $purchase_units,
    'payer' => array(
        'email_address' => $_SESSION['email'],
        'name' => array(
            'given_name' => $_SESSION['usuario'],
            'surname' => 'Customer'
        )
    )
);

// Añadir application_context para controlar URLs de retorno/cancelación
$order_data['application_context'] = array(
    'return_url' => APP_URL . 'secciones/carrito/paypal_return.php',
    'cancel_url' => APP_URL . 'secciones/carrito/paypal_cancel.php',
    'brand_name' => APP_NAME,
    'landing_page' => 'BILLING',
    'user_action' => 'PAY_NOW'
);

echo "<p><strong>Order Data (JSON):</strong></p>";
echo "<pre>";
echo json_encode($order_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
echo "</pre>";

echo "<p><strong>Session Data:</strong></p>";
echo "<pre>";
print_r([
    'user_id' => $_SESSION['user_id'] ?? 'NOT SET',
    'usuario' => $_SESSION['usuario'] ?? 'NOT SET',
    'email' => $_SESSION['email'] ?? 'NOT SET',
    'pending_order_id' => $_SESSION['pending_order_id'] ?? 'NOT SET',
    'pending_order_total' => $_SESSION['pending_order_total'] ?? 'NOT SET',
    'carrito_count' => count($carrito_items)
]);
echo "</pre>";

echo "<p><a href='pago.php'>Volver a Pago</a></p>";
?>
