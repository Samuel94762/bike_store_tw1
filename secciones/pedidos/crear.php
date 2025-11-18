<?php include("../../bd.php");


$customers =$conexion->query("SELECT customer_id, CONCAT(first_name, ' ', last_name) as name
FROM customers ORDER BY name ")->fetchAll(PDO::FETCH_ASSOC);
$products = $conexion->query("SELECT product_id, product_name, price FROM products 
ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
//Creamos un arreglo asociativo para JS (product_id => list_price)
$productPrices=[];
foreach($products as $p){
    $productPrices[$p['product_id']] = $p['price'];
}
//Programamos un bloque de transacción 
if($_SERVER['REQUEST_METHOD']==='POST'){
    try {
        $conexion->beginTransaction();
        //Insercion en la tabla orders 
        $stmtOrder = $conexion ->prepare("INSERT INTO orders(customer_id, order_date, user_id, estado, total_amount)
        VALUES(?,?,?,?,?)");
        $stmtOrder->execute([
            $_POST['customer_id'],
            $_POST['order_date'],
            $_POST['user_id'],
            $_POST['estado'],
            $_POST['total_amount']
        ]);

        $order_id = $conexion->lastInsertId();
        //Insercion en order_items
        
        $stmtItem = $conexion->prepare("INSERT INTO order_items(order_id, product_id, quantity, price, discount)
        VALUES(?,?,?,?,?)");
        foreach($_POST['items'] as $item){
            $stmtItem->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['discount']
            ]);
        }

        $conexion->commit();
        $mensaje="Pedido registrado satisfactoriamente"; 
    }catch(Exception $e){
        $conexion->rollBack();
        $mensaje = "Error registrando el Pedido: ".$e->getMessage();
    }

}
?>
<?php include("../../templates/header.php") ?>

<br>
<h3>Registrar Nuevo Pedido</h3>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-secondary" href="index.php" role="button"><i class= "bi bi-skip-backward-btn me-2"></i>Atr&aacute;s</a>
    </div>
    <div class="card-body">
        <div>
            <?php if(!empty($mensaje)):
            ?>
            <p><strong><?= htmlspecialchars($mensaje) ?></strong></p>
            <?php endif; ?>
        </div>

        <form id = "orderForm" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="" class="form-label">Cliente</label>
                <input type="text" class="form-control" name="customer_id" id="first_name" 
                    aria-describedby="helpId" placeholder="Nombre"
                />
                <select name="customer_id" required>
                    <option value="">Seleccione un cliente</option>
                    <?php foreach($customers as $c):?>
                        <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                        <?php endforeach; ?>
                </select>
                <small id="helpId" class="form-text text-muted">Ingrese el nombre del cliente</small>
            </div>

            <div class="mb-3">
                <label for="" class="form-label">Fecha Pedido</label>
                <input type="text" class="form-control" name="order_date" id="order_date" required 
                    aria-describedby="helpId" placeholder="Apellido" />
                <small id="helpId" class="form-text text-muted">Ingrese la fecha del pedido</small>
            </div>

            <div class="mb-3">
                <label for="" class="form-label">Usuario</label>
                 <select name="usuario_id">
                    <option value=""> -- Seleccione un usuario --</option>
                    <option value="<?= $_SESSION['usuario_id'] ?>"><?= htmlspecialchars($_SESSION['usuario']) ?></option>
                </select>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Estado</label>
                <input type="text" value = "Pendiente" class="form-control" name="estado" id="estado" 
                    aria-describedby="helpId"/>
                <small id="helpId" class="form-text text-muted">Ingrese el teléfono del cliente</small>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Calle</label>
                <input type="text" class="form-control" name="street" id="street" 
                    aria-describedby="helpId" placeholder="Calle"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la calle</small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="city" id="city" 
                    aria-describedby="helpId" placeholder="Ciudad"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la ciudad</small>
            </div>

            <div class="mb-3">
                <label for="state" class="form-label">Estado</label>
                <input type="text" class="form-control" name="state" id="state" 
                    aria-describedby="helpId" placeholder="Estado"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el estado</small>
            </div>

        <h3>Pedido Detalle</h3>

        <div class="table-responsive-sm">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Precio Unidad</th>
                        <th scope="col">Descuento</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td>
                            <select name="items[0][product_id]" onchange="setPrice(this)" required>
                                <option value=""> -- Seleccione un producto --</option>
                                <?php foreach($products as $p):?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" min="1" required oninput="calculateTotal()"></td>
                        <td><input type="number" step="0.01" name="items[0][price]" min="0" required oninput="calculateTotal()"></td>
                        <td><input type="number" step="0.01" name="discount" min="0" required oninput="calculateTotal()"></td>
                        <td class = "subtotal"> 0.00 </td>
                        <td><button type="button" class="remove_row" onclick="removeRow(this)"><i class= "bi bi-trash"></i></button></td>
                        
                        
                    </tr>
                </tbody>
            </table>
        </div>
        
        <br>
        <button type="button" class="add_row" onclick="addRow(this)"><i class= "bi bi-plus-circle me-2"></i>Agregar Item</button>

        <div class="total-box">
            Monto Total: Bs. <span id="totalAmount">0.00</span>
        </div>
            <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
            <br>
            <button type="submit" class="btn btn-outline-primary">Guardar Orden</button>

            </form>
    </div>
    
</div>

<?php include("../../templates/footer.php") ?>

<script>
    let itemindex = 1;
    //Cargar precio del producto desde PHP
    const productPrices = <?= json_encode($productPrices) ?>;
    
    function addRow(){

    }
</script>