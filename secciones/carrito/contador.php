<?php
include("../../config.php");

header('Content-Type: application/json');

// Contar items en el carrito
$total = 0;
if (isset($_SESSION['carrito'])) {
    $total = count($_SESSION['carrito']);
}

echo json_encode(['total' => $total]);
?>
