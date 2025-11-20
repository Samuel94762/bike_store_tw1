<?php include("../../bd.php");

// Eliminar pedido
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Verificar si hay items asociados a este pedido
    $sentencia = $conexion->prepare("SELECT COUNT(*) as total FROM order_items WHERE order_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Cambiar estado del pedido
    $sentencia = $conexion->prepare("UPDATE orders SET estado='Anulado' WHERE order_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Registro anulado";
    header("Location: index.php?mensaje=".$mensaje);
}

// Consulta para obtener todos los pedidos con información del cliente
$sentencia = $conexion->prepare("SELECT *, 
                                (SELECT CONCAT(first_name, ' ', last_name) AS nombre_completo FROM customers
                                WHERE customer_id=orders.customer_id LIMIT 1) as cliente,
                                (SELECT usuario FROM usuarios WHERE user_id=orders.user_id LIMIT 1) AS usuario
                                FROM orders ");
$sentencia->execute();
$lista_pedidos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button"><i class= "bi bi-cart-plus"></i> Nuevo Pedido</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-light table-hover" id="tabla_id">
                <thead>
                    <tr class="table-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Fecha Pedido</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Monto Total</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_pedidos as $pedido) { ?>
                    <tr class="">
                        <td scope="row" class="table-secondary"><?php echo $pedido['order_id']; ?></td>
                        <td><?php echo $pedido['order_date']; ?></td>
                        <td><?php echo $pedido['cliente']; ?></small></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($pedido['estado']) {
                                    case 'Pendiente': echo 'bg-warning'; break;
                                    case 'Procesando': echo 'bg-info'; break;
                                    case 'Enviado': echo 'bg-primary'; break;
                                    case 'Entregado': echo 'bg-success'; break;
                                    case 'Anulado': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo $pedido['estado']; ?>
                            </span>
                        </td>
                        <td><?php echo $pedido['usuario']; ?></td>
                        <td>$<?php echo number_format($pedido['total_amount'], 2); ?></td>
                        <td>
                            <a class="btn btn-outline-warning" href="editar.php?txtID=<?php echo $pedido['order_id']; ?>" role="button"><i class= "bi bi-pencil-square"></i></a>
                            <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $pedido['order_id']; ?>" role="button"><i class= "bi bi-trash"></i></a>
                            <a class="btn btn-outline-primary" href="detalle.php?txtID=<?php echo $pedido['order_id']; ?>" role="button"><i class="bi bi-card-list"></i></a>
                        </td>
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