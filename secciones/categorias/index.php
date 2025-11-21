<?php include("../../bd.php");

// Eliminar categoría
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Verificar si hay productos asociados a esta categoría
    $sentencia = $conexion->prepare("SELECT COUNT(*) as total FROM products WHERE category_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if($resultado['total'] > 0){
        $mensaje = "Error: No se puede eliminar la categoría porque tiene productos asociados";
        header("Location: index.php?mensaje=".$mensaje);
        exit();
    }
    
    // Eliminar categoría si no tiene productos asociados
    $sentencia = $conexion->prepare("DELETE FROM categories WHERE category_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Categoría eliminada";
    header("Location: index.php?mensaje=".$mensaje);
}

// Consulta para obtener todas las categorías
$sentencia = $conexion->prepare("SELECT * FROM categories");
$sentencia->execute();
$lista_categorias = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button"><i class= "bi bi-plus-circle"></i> Nueva Categoría</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-light table-hover" id="tabla_id">
                <thead>
                    <tr class="table-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Nombre de Categoría</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_categorias as $categoria) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $categoria['category_id']; ?></td>
                        <td><?php echo $categoria['category_name']; ?></td>
                        <td>
                            <a class="btn btn-outline-warning" href="editar.php?txtID=<?php echo $categoria['category_id']; ?>" role="button"><i class= "bi bi-pencil-square"></i></a>
                            <a class="btn btn-outline-danger" 
                                href="javascript:borrar(<?php echo $categoria['category_id']; ?>)" role="button"><i class= "bi bi-trash"></i></a>
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