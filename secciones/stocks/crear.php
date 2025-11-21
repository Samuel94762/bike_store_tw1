<?php include("../../bd.php");

// Traer los productos y tiendas
$productos = $conexion->query("SELECT product_id, product_name FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
$tiendas = $conexion->query("SELECT store_id, store_name FROM stores ORDER BY store_name")->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario cuando se envía por POST
if ($_POST) {
    $store_id = $_POST["store_id"];
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];

    // Insertar el nuevo registro de stock
    $sentencia = $conexion->prepare("INSERT INTO stocks (store_id, product_id, quantity) VALUES (:store_id, :product_id, :quantity)");
    $sentencia->bindParam(":store_id", $store_id);
    $sentencia->bindParam(":product_id", $product_id);
    $sentencia->bindParam(":quantity", $quantity);
    $sentencia->execute();

    $mensaje = "Stock registrado correctamente";
    header("Location: index.php?mensaje=" . $mensaje);
}

?>

<?php include("../../templates/header.php"); ?>

<h3>Registrar nuevo Stock</h3>

<div class="card">
    <div class="card-body">
        <form method="post">
            <div class="mb-3">
                <label for="store_id" class="form-label">Tienda</label>
                <select name="store_id" class="form-control" required>
                    <option value="">-- Seleccione una tienda --</option>
                    <?php foreach ($tiendas as $tienda) { ?>
                        <option value="<?= $tienda['store_id'] ?>"><?= htmlspecialchars($tienda['store_name']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="product_id" class="form-label">Producto</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Seleccione un producto --</option>
                    <?php foreach ($productos as $producto) { ?>
                        <option value="<?= $producto['product_id'] ?>"><?= htmlspecialchars($producto['product_name']) ?></option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Cantidad</label>
                <input type="number" class="form-control" name="quantity" min="1" required>
            </div>

            <button type="submit" class="btn btn-outline-success">Guardar Stock</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../templates/footer.php"); ?>
