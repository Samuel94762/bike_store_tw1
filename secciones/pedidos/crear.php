<?php include("../../bd.php");
//tarer los datos de clientes y productos para las listas deplegables 
$customers=$conexion ->query("SELECT customer_id, CONCAT(first_name, ' ' ,last_name) AS name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$products =$conexion ->query("SELECT product_id, product_name, price FROM products 
ORDER BY product_name")->fetchALL(PDO::FETCH_ASSOC);
// creamos un arreglo asociativo para javascript (product_id => list_price)
$productPrices = [];
foreach ($products as $p) {
    $productPrices[$p['product_id']] = $p['price'];
}
// programamos un bloque de transacciones 
if ($_SERVER['REQUEST_METHOD'] === 'POST' ){
    try {
        $conexion->beginTransaction();
        // insercion en la tabla orders 
        $stmtOrder = $conexion->prepare("INSERT INTO orders (customer_id, order_date, user_id, estado, total_amount) 
        VALUES (?, ?, ?, ?, ?)");
        $stmtOrder->execute([
            $_POST['customer_id'],
            $_POST['order_date'],
            $_POST['usuario_id'],
            $_POST['estado'],
            $_POST['total_amount']
        ]);
        $order_id =$conexion-> lastInsertID();
        //Insercion en order_items
        $stmtItem= $conexion->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount)
        VALUES (?, ?, ?, ?, ?)");
        foreach ($_POST['items']as $item) {
            $stmtItem->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['discount']
            ]);
            $conexion->commit();
            $mensaje="Pedido registro satisfactoriamente";
        }
    } catch (Exception $e) {
        $conexion->rollBack();
        $mensaje="Error resgistrando el pedido: " . $e->getMessage();
    }
}
?>
<?php include("../../templates/header.php"); ?>
<br>
<h3>Registrar nuevo pedido </h3>
<br>
<div class="card">
    <div class="card-header">
        <a class="btn btn-outline-secondary" href="index.php" role="button">
            <i class="bi bi-plus-circle me-2"></i>Atr&aacute;s</a>
    </div>
    <div class="card-body">
        <div> 
            <?php if (!empty($mensaje)): ?>
                <p><strong><?= htmlspecialchars($mensaje)?></strong></p>
                <?php endif; ?>
        </div>
        <form id="orderForm" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="" class="form-label">Cliente</label>
                <select name="customer_id" require>
                    <option value= "">-- Seleccione un cliente --</option>
                    <?php
                    foreach($customers as $c):
                    ?>
                    <option value = "<?=  $c['customer_id'] ?>"><?= htmlspecialchars($c['name']) ?> </option>
                    <?php 
                    endforeach;
                    ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Fecha Pedido</label>
                <input type="date" class="form-control" name="order_date" id="order_date" required
                    aria-describedby="helpId"/>
                <small id="helpId" class="form-text text-muted">Ingrese la fecha del pedido</small>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Usuario</label>
                <select name="usario_id">
                    <option value= "">-- Seleccione un usuario --</option>
                    <option value = "<?=  $_SESSION['usuario_id'] ?>"><?= htmlspecialchars($_SESSION['usuario']) ?> </option>
                </select>
            </div>
            <div class="mb-3">
                <label for="" class="form-label">Estado</label>
                <input type="text" value="Pendiente" class="form-control" name="estado" id="estado"
                    aria-describedby="helpId" />
            </div>
           
        <h3>Pedido Detalle</h3>
        <div
            class="table-responsive-sm">
            <table class="table table-primary">
                <thead>
                    <tr>
                        <th scope="col">Porducto</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Precio Unidad</th>
                        <th scope="col">Descuento (%)</th>
                        <th scope="col">Sub Total</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <tr class="">
                        <td>
                            <select name="items[0][product_id]" onchange="setPrice(this)" required>
                                <option value="">-- Producto --</option>
                                <?php foreach ($products as $p): ?>
                                    <option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </td>
                        <td><input type="number" name="items[0][quantity]" min="1" required oninput="calculateTotal()"></td>
                        <td><input type="number" step="0.01" name="items[0][price]" min="0" required oninput="calculateTotal()" disabled></td>
                        <td><input type="number" step="0.01" name="items[0][discount]" min="0" oninput="calculateTotal()"></td>
                        <td class="subtotal">0.00</td>
                        <td>
                            <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>

        </div>
        <br>
        <button type="button"  class="btn btn-outline-primary" onclick="addRow(this)"><i class="bi bi-circle me-2"></i>Agregar item</button>
        <div class="total-box">
            Monto Total: Bs.<spam id="totalAmount">0.00</spam>
        </div>
        <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
        <br>
        <button type="submit" class="btn btn-outline-success">Guardar Orden</button>
        </form>   
    </div>
</div>
<?php include("../../templates/footer.php"); ?>

<script>
    let itemIndex = 1;

    // Cargar el precio del producto desde PHP
    const productPrices = <?= json_encode($productPrices) ?>;

    function addRow() {
        const products = `<?php foreach ($products as $p): ?><option value="<?= $p['product_id'] ?>"><?= htmlspecialchars($p['product_name']) ?></option><?php endforeach; ?>`;
        const table = document.querySelector("table tbody");
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>
                <select name="items[${itemIndex}][product_id]" onchange="setPrice(this)" required>
                    <option value="">-- Seleccione un Producto --</option>
                    ${products}
                </select>
            </td>
            <td>
                <input type="number" name="items[${itemIndex}][quantity]" min="1" required oninput="calculateTotal()">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][price]" min="0" required oninput="calculateTotal()">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${itemIndex}][discount]" min="0" oninput="calculateTotal()">
            </td>
            <td class="subtotal">0.00</td>
            <td>
                <button type="button" class="btn btn-outline-danger" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </td>
        `;
        table.appendChild(row);
        itemIndex++;
    }

    // Rellena el precio del producto cuando es seleccionado
    function setPrice(select) {
        const productId = select.value;
        const row = select.closest("tr");
        const priceInput = row.querySelector('input[name$="[price]"]');

        if (productPrices.hasOwnProperty(productId)) {
            const price = productPrices[productId];
            priceInput.value = parseFloat(price).toFixed(2);
        } else {
            priceInput.value = "0.00";
            console.error(`No se encontró el precio para el producto con ID: ${productId}`);
        }

        calculateTotal();
    }

    // Elimina una fila de la tabla
    function removeRow(btn) {
        btn.closest("tr").remove();
        calculateTotal();
    }

    // Calcula el total de la tabla
    function calculateTotal() {
        let total = 0;
        const rows = document.querySelectorAll("table tbody tr");

        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('input[name*="[quantity]"]').value) || 0;
            const price = parseFloat(row.querySelector('input[name*="[price]"]').value) || 0;
            const disc = parseFloat(row.querySelector('input[name*="[discount]"]').value) || 0;

            const valor = qty * price;
            const subtotal = valor - (valor * (disc / 100));
            row.querySelector('.subtotal').textContent = subtotal.toFixed(2);

            total += subtotal;
        });

        document.getElementById("totalAmount").textContent = total.toFixed(2);
        document.getElementById("totalAmountInput").value = total.toFixed(2);
    }
</script>