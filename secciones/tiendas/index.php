<?php include("../../bd.php");
include("../../config.php");
    
    // Verificar si es admin
    if (!is_admin()) {
        header("Location: " . APP_URL . "landing.php");
        exit;
    }
//Envio de parametros en la URL o en el metodo GET
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID']))?$_GET['txtID']:"";
    
    //Borrar datos del producto
    $sentencia = $conexion->prepare("UPDATE stores SET status='Anulado' WHERE store_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Registro anulado";
    header("Location: index.php? mensaje=".$mensaje);

}

//Consulta para tiendas para mostrar como uni registro
$sentencia=$conexion->prepare("SELECT * FROM stores");
$sentencia->execute();
$lista_stores =$sentencia->fetchAll(PDO::FETCH_ASSOC);
//print_r($lista_stores);

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
                        <th scope="col">Tienda</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Email</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Calle</th>
                        <th scope="col">Ciudad</th>
                        <th scope="col">Departamento</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_stores as $registro) { ?>
                    <tr class="">
                        <td scope="row" class="table-secondary"><?php echo $registro['store_id']; ?></td>
                        <td><?php echo $registro['store_name']; ?></td>
                        <td><?php echo $registro['phone'] ?></td>
                        <td><?php echo $registro['email'] ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($registro['status']) {
                                    case 'Abierta': echo 'bg-primary'; break;
                                    case 'Cerrada': echo 'bg-warning'; break;
                                    case 'Anulado': echo 'bg-danger'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo $registro['status']; ?>
                            </span>
                        </td>
                        <td><?php echo $registro['street'] ?></td>
                        <td><?php echo $registro['city'] ?></td>
                        <td><?php echo $registro['state'] ?></td>
                        <td>
                            <a class="btn btn-outline-warning" href="editar.php?txtID=<?php echo $registro['store_id']; ?>" role="button"><i class= "bi bi-pencil-square"></i></a>
                            <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $registro['store_id']; ?>" role="button"><i class= "bi bi-trash"></i></a>
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