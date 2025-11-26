<?php include("../../bd.php");
include("../../config.php");
    
    // Verificar si es admin
    if (!is_admin()) {
        header("Location: " . APP_URL . "landing.php");
        exit;
    }
// Recuperar el ID del pedido a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales del pedido
    $sentencia = $conexion->prepare("SELECT * FROM orders WHERE order_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $pedido = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Asignar los valores actuales a variables
    $customer_id = $pedido['customer_id'];
    $order_date = $pedido['order_date'];
    $user_id = $pedido['user_id'];
    $estado = $pedido['estado'];
    $total_amount = $pedido['total_amount'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $customer_id = (isset($_POST["customer_id"])) ? $_POST["customer_id"] : "";
    $order_date = (isset($_POST["order_date"])) ? $_POST["order_date"] : "";
    $user_id = (isset($_POST["user_id"])) ? $_POST["user_id"] : "";
    $estado = (isset($_POST["estado"])) ? $_POST["estado"] : "";
    $total_amount = (isset($_POST["total_amount"])) ? $_POST["total_amount"] : "";
    
    // Preparar la actualización de los datos
    $sentencia = $conexion->prepare("UPDATE orders 
                                    SET customer_id = :customer_id, 
                                        order_date = :order_date, 
                                        user_id = :user_id, 
                                        estado = :estado, 
                                        total_amount = :total_amount 
                                    WHERE order_id = :id");

    // Asignamos los valores
    $sentencia->bindParam(":customer_id", $customer_id);
    $sentencia->bindParam(":order_date", $order_date);
    $sentencia->bindParam(":user_id", $user_id);
    $sentencia->bindParam(":estado", $estado);
    $sentencia->bindParam(":total_amount", $total_amount);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    
    $mensaje = "Pedido actualizado";
    header("Location: index.php?mensaje=" . $mensaje);
}

// Consulta para obtener los clientes
$sentencia_clientes = $conexion->prepare("SELECT * FROM customers ORDER BY customer_id ASC");
$sentencia_clientes->execute();
$lista_clientes = $sentencia_clientes->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Pedido</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID del pedido -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            
            <div class="mb-3">
                <label for="customer_id" class="form-label">Cliente</label>
                <select class="form-select form-select-sm" name="customer_id" id="customer_id" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach($lista_clientes as $cliente) { ?>
                    <option value="<?php echo $cliente['customer_id']; ?>" 
                        <?php echo ($cliente['customer_id'] == $customer_id) ? 'selected' : ''; ?>>
                        <?php echo $cliente['first_name'] . ' ' . $cliente['last_name']; ?>
                    </option>
                    <?php } ?>
                </select>
                <small id="helpId" class="form-text text-muted">Seleccione el cliente</small>
            </div>

            <div class="mb-3">
                <label for="order_date" class="form-label">Fecha del Pedido</label>
                <input type="date" class="form-control" name="order_date" id="order_date" 
                    aria-describedby="helpId" 
                    value="<?php echo $order_date; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Seleccione la fecha del pedido</small>
            </div>

            <div class="mb-3">
                <label for="user_id" class="form-label">ID de Usuario</label>
                <input type="number" class="form-control" name="user_id" id="user_id" 
                    aria-describedby="helpId" placeholder="ID de usuario"
                    value="<?php echo $user_id; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el ID del usuario que registra el pedido</small>
            </div>

            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select class="form-select form-select-sm" name="estado" id="estado" required>
                    <option value="Pendiente" <?php echo ($estado == 'Pendiente') ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="Procesando" <?php echo ($estado == 'Procesando') ? 'selected' : ''; ?>>Procesando</option>
                    <option value="Enviado" <?php echo ($estado == 'Enviado') ? 'selected' : ''; ?>>Enviado</option>
                    <option value="Entregado" <?php echo ($estado == 'Entregado') ? 'selected' : ''; ?>>Entregado</option>
                    <option value="Anulado" <?php echo ($estado == 'Anulado') ? 'selected' : ''; ?>>Anulado</option>
                </select>
                <small id="helpId" class="form-text text-muted">Seleccione el estado del pedido</small>
            </div>

            <div class="mb-3">
                <label for="total_amount" class="form-label">Monto Total</label>
                <input type="number" class="form-control" name="total_amount" id="total_amount" 
                    aria-describedby="helpId" placeholder="Monto total" step="0.01"
                    value="<?php echo $total_amount; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el monto total del pedido</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Pedido</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>