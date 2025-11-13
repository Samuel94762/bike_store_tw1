<?php include("../../bd.php");

// Eliminar pedido
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Verificar si hay items asociados a este pedido
    $sentencia = $conexion->prepare("SELECT COUNT(*) as total FROM order_items WHERE order_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if($resultado['total'] > 0){
        $mensaje = "Error: No se puede eliminar el pedido porque tiene items asociados";
        header("Location: index.php?mensaje=".$mensaje);
        exit();
    }
    
    // Borrar datos del pedido
    $sentencia = $conexion->prepare("DELETE FROM orders WHERE order_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Pedido eliminado";
    header("Location: index.php?mensaje=".$mensaje);
}

// Consulta para obtener todos los pedidos con información del cliente
$sentencia = $conexion->prepare("SELECT o.*, 
                                c.first_name, 
                                c.last_name 
                                FROM orders o 
                                LEFT JOIN customers c ON o.customer_id = c.customer_id 
                                ORDER BY o.order_id ASC");
$sentencia->execute();
$lista_pedidos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">Nuevo Pedido</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary" id="tabla_id">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Cliente</th>
                        <th scope="col">Fecha Pedido</th>
                        <th scope="col">ID Usuario</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Monto Total</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_pedidos as $pedido) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $pedido['order_id']; ?></td>
                        <td>
                            <?php echo $pedido['first_name'] . ' ' . $pedido['last_name']; ?>
                            <br>
                            <small class="text-muted">ID: <?php echo $pedido['customer_id']; ?></small>
                        </td>
                        <td><?php echo $pedido['order_date']; ?></td>
                        <td><?php echo $pedido['user_id']; ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($pedido['estado']) {
                                    case 'Pendiente': echo 'bg-warning'; break;
                                    case 'Procesando': echo 'bg-info'; break;
                                    case 'Enviado': echo 'bg-primary'; break;
                                    case 'Entregado': echo 'bg-success'; break;
                                    case 'Cancelado': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo $pedido['estado']; ?>
                            </span>
                        </td>
                        <td>$<?php echo number_format($pedido['total_amount'], 2); ?></td>
                        <td>
                            <a class="btn btn-outline-primary" href="editar.php?txtID=<?php echo $pedido['order_id']; ?>" role="button">Editar</a>
                            <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $pedido['order_id']; ?>" role="button"
                            onclick="return confirm('¿Estás seguro de que quieres eliminar este pedido?')">Eliminar</a>
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