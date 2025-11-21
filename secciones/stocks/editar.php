<?php include("../../bd.php");

// Recuperar el ID de stock a editar
if (isset($_GET['txtID'])) {
    $stock_id = $_GET['txtID'];

    // Obtener los datos actuales del stock
    $sentencia = $conexion->prepare("SELECT * FROM stocks WHERE stock_id = :id");
    $sentencia->bindParam(":id", $stock_id);
    $sentencia->execute();
    $stock = $sentencia->fetch(PDO::FETCH_ASSOC);

    // Si no se encuentra el stock, redirigir
    if (!$stock) {
        header("Location: index.php");
        exit();
    }

    $store_id = $stock['store_id'];
    $product_id = $stock['product_id'];
    $quantity = $stock['quantity'];
}

// Traer los productos y tiendas para las listas desplegables
$productos = $conexion->query("SELECT product_id, product_name FROM products ORDER BY product_name")->fetchAll(PDO::FETCH_ASSOC);
$tiendas = $conexion->query("SELECT store_id, store_name FROM stores ORDER BY store_name")->fetchAll(PDO::FETCH_ASSOC);

// Procesar el formulario cuando se envía por POST
if ($_POST) {
    $store_id = $_POST["store_id"];
    $product_id = $_POST["product_id"];
    $quantity = $_POST["quantity"];

    // Actualizar el registro de stock
    $sentencia = $conexion->prepare("UPDATE stocks SET store_id = :store_id, product_id = :product_id, quantity = :quantity WHERE stock_id = :id");
    $sentencia->bindParam(":store_id", $store_id);
    $sentencia->bindParam(":product_id", $product_id);
    $sentencia->bindParam(":quantity", $quantity);
    $sentencia->bindParam(":id", $stock_id);
    $sentencia->execute();

    $mensaje = "Stock actualizado correctamente";
    header("Location: index.php?mensaje=" . $mensaje);
}

?>

<?php include("../../templates/header.php"); ?>

<h3>Editar Stock</h3>

<div class="card">
    <div class="card-body">
        <form method="post">
            <input type="hidden" name="txtID" value="<?= $stock_id ?>">

            <div class="mb-3">
                <label for="store_id" class="form-label">Tienda</label>
                <select name="store_id" class="form-control" required>
                    <option value="">-- Seleccione una tienda --</option>
                    <?php foreach ($tiendas as $tienda) { ?>
                        <option value="<?= $tienda['store_id'] ?>" <?= $store_id == $tienda['store_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($tienda['store_name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="product_id" class="form-label">Producto</label>
                <select name="product_id" class="form-control" required>
                    <option value="">-- Seleccione un producto --</option>
                    <?php foreach ($productos as $producto) { ?>
                        <option value="<?= $producto['product_id'] ?>" <?= $product_id == $producto['product_id'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($producto['product_name']) ?>
                        </option>
                    <?php } ?>
                </select>
            </div>

            <div class="mb-3">
                <label for="quantity" class="form-label">Cantidad</label>
                <input type="number" class="form-control" name="quantity" min="1" value="<?= $quantity ?>" required>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Stock</button>
            <a href="index.php" class="btn btn-outline-secondary">Cancelar</a>
        </form>
    </div>
</div>

<?php include("../../templates/footer.php"); ?>
