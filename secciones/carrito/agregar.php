<?php
include("../../config.php");

header('Content-Type: application/json');

// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit;
}

// Verificar si el usuario está logueado
if (!is_logged_in()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'error' => 'login_required', 'message' => 'Debes iniciar sesión']);
    exit;
}

$product_id = isset($_POST['product_id']) ? intval($_POST['product_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($product_id <= 0 || $quantity <= 0) {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
    exit;
}

// Verificar que el producto existe y tiene stock
$sentencia = $conexion->prepare("
    SELECT p.*, COALESCE(SUM(s.quantity), 0) as stock_total
    FROM products p 
    LEFT JOIN stocks s ON p.product_id = s.product_id
    WHERE p.product_id = :id
    GROUP BY p.product_id
");
$sentencia->bindParam(':id', $product_id);
$sentencia->execute();
$producto = $sentencia->fetch(PDO::FETCH_ASSOC);

if (!$producto) {
    echo json_encode(['success' => false, 'message' => 'Producto no encontrado']);
    exit;
}

if ($producto['stock_total'] <= 0) {
    echo json_encode(['success' => false, 'message' => 'Producto sin stock']);
    exit;
}

// Inicializar carrito en sesión si no existe
if (!isset($_SESSION['carrito'])) {
    $_SESSION['carrito'] = array();
}

// Agregar o actualizar producto en el carrito
$producto_existe = false;
foreach ($_SESSION['carrito'] as &$item) {
    if ($item['product_id'] == $product_id) {
        $item['quantity'] += $quantity;
        $producto_existe = true;
        break;
    }
}

if (!$producto_existe) {
    $_SESSION['carrito'][] = array(
        'product_id' => $product_id,
        'product_name' => $producto['product_name'],
        'price' => $producto['price'],
        'quantity' => $quantity,
        'foto' => $producto['foto']
    );
}

echo json_encode([
    'success' => true,
    'message' => 'Producto agregado al carrito',
    'cart_count' => count($_SESSION['carrito'])
]);
?>
