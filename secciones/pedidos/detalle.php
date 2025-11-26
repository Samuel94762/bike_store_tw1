<?php include("../../bd.php");
include("../../config.php");
    
    // Verificar si es admin
    if (!is_admin()) {
        header("Location: " . APP_URL . "landing.php");
        exit;
    }
// Eliminar pedido
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
}
    // Obtener detalle del pedido usando JOIN para traer nombre del producto
    $sentencia = $conexion->prepare("SELECT oi.*, COALESCE(p.product_name, '') AS producto
                                     FROM order_items oi
                                     LEFT JOIN products p ON p.product_id = oi.product_id
                                     WHERE oi.order_id = :order_id");
    $sentencia->bindParam(':order_id', $txtID);
    $sentencia->execute();
    $lista_pedidos_detalle = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    function restarPorcentaje($valor, $porcentaje){
        return number_format($valor - ($valor *($porcentaje* 100)/100), 2, '.', ',');
    }
?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-secondary" href="index.php" role="button"><i class= "bi bi-plus-circle me-2"></i>Atr&aacute;s</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">Item</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Descuento</th>
                        <th scope="col">Sub Total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_pedidos_detalle as $pedido) { $item=1; ?>
                    <tr class="">
                        <td scope="row"><?php echo $item; ?></td>
                        <td><?php echo $pedido['producto']; ?></td>
                        <td><?php echo $pedido['quantity']; ?></small></td>
                        <td><?php echo $pedido['price']; ?></td>
                        <td><?php echo $pedido['discount']; ?></td>
                        <td><?php echo restarPorcentaje($pedido['quantity']* $pedido['price'], 
                                                        $pedido['discount']); $item++; ?></td>
                        
                        
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
    </div>
    <div class="card-footer text-muted">
        <?php 
        if(isset($_GET['mensaje'])) {
            echo $_GET['mensaje'];
        }
        ?>
    </div>
</div>

<?php include("../../templates/footer.php") ?>