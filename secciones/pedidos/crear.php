<?php include("../../templates/header.php"); ?>
<?php include("../../bd.php");
// Traer los datos de clientes y productos para las listas desplegables 
$customers = $conexion->query("SELECT customer_id, CONCAT(first_name, ' ' ,last_name) AS name FROM customers ORDER BY name")->fetchAll(PDO::FETCH_ASSOC);
$products = $conexion->query("SELECT product_id, product_name, price FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);

// Creamos un arreglo asociativo para javascript (product_id => list_price)
$productPrices = [];
foreach ($products as $p) {
    $productPrices[$p['product_id']] = $p['price'];
}

// Programamos un bloque de transacciones 
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion->beginTransaction();
        
        // Obtener el user_id de la sesión
        $user_id = $_SESSION['user_id'] ?? null;
        
        if (!$user_id) {
            throw new Exception("Usuario no autenticado");
        }
        
        // Insercion en la tabla orders 
        $stmtOrder = $conexion->prepare("INSERT INTO orders (customer_id, order_date, user_id, estado, total_amount) VALUES (?, ?, ?, ?, ?)");
        $stmtOrder->execute([
            $_POST['customer_id'],
            $_POST['order_date'],
            $user_id, // Usar el user_id de la sesión
            $_POST['estado'],
            $_POST['total_amount']
        ]);
        
        $order_id = $conexion->lastInsertId();
        
        // Insercion en order_items
        $stmtItem = $conexion->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount) VALUES (?, ?, ?, ?, ?)");
        
        foreach ($_POST['items'] as $item) {
            $stmtItem->execute([
                $order_id,
                $item['product_id'],
                $item['quantity'],
                $item['price'],
                $item['discount']
            ]);
        }
        
        $conexion->commit();
        $_SESSION['mensaje'] = "Pedido registrado satisfactoriamente";
        header("Location: crear.php");
        exit();
        
    } catch (Exception $e) {
        $conexion->rollBack();
        $mensaje = "Error registrando el pedido: " . $e->getMessage();
    }
}

// Recuperar mensaje de sesión si existe
$mensaje = $_SESSION['mensaje'] ?? '';
unset($_SESSION['mensaje']); // Limpiar el mensaje después de mostrarlo

// Obtener información del usuario para mostrar
$usuario_nombre = $_SESSION['usuario'] ?? 'Usuario';
$usuario_id = $_SESSION['user_id'] ?? 'N/A';
?>

<br>
<h3>Registrar nuevo pedido</h3>
<br>
<div class="card">
    <div class="card-header">
        <a class="btn btn-outline-secondary" href="index.php" role="button">
            <i class="bi bi-plus-circle me-2"></i>Atr&aacute;s</a>
    </div>
    <div class="card-body">
        <div> 
            <?php if (!empty($mensaje)): ?>
                <div class="alert alert-success" role="alert">
                    <strong><?= htmlspecialchars($mensaje) ?></strong>
                </div>
            <?php endif; ?>
        </div>
        <form id="orderForm" method="post" enctype="multipart/form-data">
            <div class="mb-3">
                <label for="customer_id" class="form-label">Cliente</label>
                <select name="customer_id" class="form-control" id="customer_id" required>
                    <option value="">-- Seleccione un cliente --</option>
                    <?php foreach($customers as $c): ?>
                    <option value="<?= $c['customer_id'] ?>"><?= htmlspecialchars($c['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="order_date" class="form-label">Fecha Pedido</label>
                <input type="date" class="form-control" name="order_date" id="order_date" required
                    aria-describedby="helpId" value="<?= date('Y-m-d') ?>"/>
                <small id="helpId" class="form-text text-muted">Ingrese la fecha del pedido</small>
            </div>
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($usuario_nombre) ?>" readonly>
                <small class="form-text text-muted">Usuario actual del sistema (ID: <?= $usuario_id ?>)</small>
            </div>
            <div class="mb-3">
                <label for="estado" class="form-label">Estado</label>
                <select name="estado" class="form-control" id="estado" required>
                    <option value="Pendiente" selected>Pendiente</option>
                    <option value="Procesando">Procesando</option>
                    <option value="Enviado">Enviado</option>
                    <option value="Entregado">Entregado</option>
                    <option value="Anulado">Anulado</option>
                </select>
                <small class="form-text text-muted">Seleccione el estado del pedido</small>
            </div>
            
            <div class="table-responsive-sm">
                <h3>Pedido Detalle</h3>
                <table class="table table-primary">
                    <thead>
                        <tr>
                            <th scope="col">Producto</th>
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
                                <select name="items[0][product_id]" class="form-control" onchange="setPrice(this)" required>
                                    <option value="">-- Producto --</option>
                                    <?php foreach ($products as $p): ?>
                                        <option value="<?= $p['product_id'] ?>" data-price="<?= $p['price'] ?>">
                                            <?= htmlspecialchars($p['product_name']) ?> - Bs. <?= number_format($p['price'], 2) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td><input type="number" class="form-control" name="items[0][quantity]" min="1" value="1" required oninput="calculateTotal()"></td>
                            <td><input type="number" step="0.01" class="form-control" name="items[0][price]" min="0" required oninput="calculateTotal()"></td>
                            <td><input type="number" step="0.01" class="form-control" name="items[0][discount]" min="0" max="100" value="0" oninput="calculateTotal()"></td>
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
            <button type="button" class="btn btn-outline-primary" onclick="addRow()"><i class="bi bi-plus-circle me-2"></i>Agregar item</button>
            
            <div class="total-box mt-3 p-3 bg-light rounded">
                <h4>Monto Total: Bs. <span id="totalAmount">0.00</span></h4>
            </div>
            
            <input type="hidden" name="total_amount" id="totalAmountInput" value="0.00">
            
            <br>
            <button type="submit" class="btn btn-outline-success">Guardar Orden</button>
            <a name="" id="" class="btn btn-outline-secondary" href="index.php" role="button">Cancelar</a>
        </form>   
    </div>
</div>

<?php include("../../templates/footer.php"); ?>

<script>
    let itemIndex = 1;

    // Cargar el precio del producto desde PHP
    const productPrices = <?= json_encode($productPrices) ?>;

    function addRow() {
        const products = `<?php foreach ($products as $p): ?><option value="<?= $p['product_id'] ?>" data-price="<?= $p['price'] ?>"><?= htmlspecialchars($p['product_name']) ?> - Bs. <?= number_format($p['price'], 2) ?></option><?php endforeach; ?>`;
        const table = document.querySelector("table tbody");
        const row = document.createElement("tr");
        row.innerHTML = `
            <td>
                <select name="items[${itemIndex}][product_id]" class="form-control" onchange="setPrice(this)" required>
                    <option value="">-- Seleccione un Producto --</option>
                    ${products}
                </select>
            </td>
            <td>
                <input type="number" class="form-control" name="items[${itemIndex}][quantity]" min="1" value="1" required oninput="calculateTotal()">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][price]" min="0" required oninput="calculateTotal()">
            </td>
            <td>
                <input type="number" step="0.01" class="form-control" name="items[${itemIndex}][discount]" min="0" max="100" value="0" oninput="calculateTotal()">
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
        if (document.querySelectorAll("table tbody tr").length > 1) {
            btn.closest("tr").remove();
            calculateTotal();
        } else {
            alert("Debe haber al menos un item en el pedido");
        }
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

    // Calcular total al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
        calculateTotal();
    });
</script>