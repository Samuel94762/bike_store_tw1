<?php include("../../bd.php");
//Envio de parametros en la URL o en el metodo GET
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID']))?$_GET['txtID']:"";
    //Buscar el archivo relacionado con el produto

    $sentencia = $conexion -> prepare("SELECT foto FROM products WHERE product_id = :id");
    $sentencia ->bindParam(":id", $txtID);
    $sentencia ->execute();
    $registro_recuperado = $sentencia->fetch(PDO::FETCH_LAZY);

    //Buscar el registro foto para borrar
    if(isset($registro_recuperado['foto']) && $registro_recuperado['foto']!=""){
        if(file_exists("./imagen/".$registro_recuperado['foto'])){
            unlink("./imagen/".$registro_recuperado['foto']);
        }
    }

    //Borrar datos del producto
    $sentencia =$conexion ->prepare("DELETE FROM products WHERE product_id =:id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia ->execute();

    $mensaje = "Registro eliminado";
    header("Location: index.php? mensaje=".$mensaje);

}
//Consulta para productos para mostrar como uni registro
$sentencia=$conexion->prepare("SELECT *, 
(SELECT category_name FROM categories WHERE categories.category_id =products.category_id LIMIT 1 ) as category
FROM products");
$sentencia->execute();
$lista_productos =$sentencia->fetchAll(PDO::FETCH_ASSOC);
//print_r($lista_productos);

?>
<?php include("../../templates/header.php") ?>
<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button"> <i class= "bi bi-plus-circle"></i> Nuevo</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-light table-hover" id="tabla_id">
                <thead>
                    <tr class="table-dark">
                        <th scope="col">ID</th>
                        <th scope="col">Producto</th>
                        <th scope="col">Foto</th>
                        <th scope="col">Modelo año</th>
                        <th scope="col">Precio</th>
                        <th scope="col">Categoria</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_productos as $registro) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $registro['product_id']; ?></td>
                        <td><?php echo $registro['product_name']; ?></td>
                        <td><img width="50" src="./imagen/<?php echo $registro['foto']; ?>"
                            class="img-fluid rounded" alt="Foto del producto"/>
                        </td>
                        <td><?php echo $registro['model_year'] ?></td>
                        <td><?php echo $registro['price'] ?></td>
                        <td><?php echo $registro['category'] ?></td>
                        <td>
                            <a class="btn btn-outline-warning" href="editar.php?txtID=<?php echo $registro['product_id']; ?>" role="button"><i class= "bi bi-pencil-square"></i></a>
                            <a class="btn btn-outline-danger" 
                                href="javascript:borrar(<?php echo $registro['product_id']; ?>)" role="button"><i class= "bi bi-trash"></i></a>
                        
                        </td>
                    </tr>
                    <?php } ?>
                </tbody>
            </table>
        </div>
        
    </div>
    <div class="card-footer text-muted">Footer</div>
</div>


<?php include("../../templates/footer.php") ?>