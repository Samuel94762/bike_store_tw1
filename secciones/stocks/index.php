<?php include("../../bd.php");

// Eliminar stock si llega un ID
if (isset($_GET['txtID'])) {
    $stock_id = $_GET['txtID'];

    $sentencia = $conexion->prepare("DELETE FROM stocks WHERE stock_id = :id");
    $sentencia->bindParam(":id", $stock_id);
    $sentencia->execute();

    $mensaje = "Stock eliminado";
    header("Location: index.php?mensaje=" . $mensaje);
    exit();
}

// Consulta para obtener todos los stocks con información de producto y tienda
$sentencia = $conexion->prepare("
    SELECT s.stock_id, s.quantity,
           p.product_name,
           st.store_name
    FROM stocks s
    INNER JOIN products p ON p.product_id = s.product_id
    INNER JOIN stores st ON st.store_id = s.store_id
    ORDER BY s.stock_id ASC
");
$sentencia->execute();
$lista_stocks = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php"); ?>

<br>
<div class="card">
    <div class="card-header">
        <a class="btn btn-outline-primary" href="crear.php" role="button">
            <i class="bi bi-plus-circle"></i> Nuevo Stock
        </a>
    </div>

    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-light table-hover" id="tabla_id">
                <thead>
                    <tr class="table-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Tienda</th>
                        <th scope="col">Cantidad</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_stocks as $stock) { ?>
                    <tr>
                        <td class="table-secondary"><?php echo $stock['stock_id']; ?></td>
                        <td><?php echo htmlspecialchars($stock['product_name']); ?></td>
                        <td><?php echo htmlspecialchars($stock['store_name']); ?></td>
                        <td><strong><?php echo $stock['quantity']; ?></strong></td>
                        <td>
                            <a class="btn btn-outline-warning"
                                href="editar.php?txtID=<?php echo $stock['stock_id']; ?>"
                                role="button">
                                <i class="bi bi-pencil-square"></i>
                            </a>

                            <a class="btn btn-outline-danger"
                                href="index.php?txtID=<?php echo $stock['stock_id']; ?>"
                                onclick="return confirm('¿Seguro que desea eliminar este stock?');"
                                role="button">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
    </div>

    <div class="card-footer text-muted">
        <?php 
        if (isset($_GET['mensaje'])) {
            echo $_GET['mensaje'];
        }
        ?>
    </div>
</div>

<?php include("../../templates/footer.php"); ?>
