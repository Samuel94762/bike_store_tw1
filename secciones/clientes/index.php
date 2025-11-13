<?php include("../../bd.php");

// Eliminar cliente
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Buscar el archivo relacionado con el cliente
    $sentencia = $conexion->prepare("SELECT imagen FROM customers WHERE customer_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro_recuperado = $sentencia->fetch(PDO::FETCH_LAZY);

    // Buscar el registro imagen para borrar
    if(isset($registro_recuperado['imagen']) && $registro_recuperado['imagen'] != ""){
        if(file_exists("./imagen/" . $registro_recuperado['imagen'])){
            unlink("./imagen/" . $registro_recuperado['imagen']);
        }
    }

    // Verificar si hay órdenes asociadas a este cliente
    $sentencia = $conexion->prepare("SELECT COUNT(*) as total FROM orders WHERE customer_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if($resultado['total'] > 0){
        $mensaje = "Error: No se puede eliminar el cliente porque tiene órdenes asociadas";
        header("Location: index.php?mensaje=".$mensaje);
        exit();
    }
    
    // Borrar datos del cliente
    $sentencia = $conexion->prepare("DELETE FROM customers WHERE customer_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Cliente eliminado";
    header("Location: index.php?mensaje=".$mensaje);
}

// Consulta para obtener todos los clientes ORDENADOS POR ID ASCENDENTE
$sentencia = $conexion->prepare("SELECT * FROM customers ORDER BY customer_id ASC");
$sentencia->execute();
$lista_clientes = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">Nuevo Cliente</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary" id="tabla_id">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Imagen</th>
                        <th scope="col">Nombre</th>
                        <th scope="col">Apellido</th>
                        <th scope="col">Teléfono</th>
                        <th scope="col">Email</th>
                        <th scope="col">Calle</th>
                        <th scope="col">Ciudad</th>
                        <th scope="col">Estado</th>
                        <th scope="col">Código Postal</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_clientes as $cliente) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $cliente['customer_id']; ?></td>
                        
                        <td><img width="50" src="./imagen/<?php echo $cliente['imagen']; ?>"
                            class="img-fluid rounded" alt="Imagen del cliente"/>
                        </td>
                        <td><?php echo $cliente['first_name']; ?></td>
                        <td><?php echo $cliente['last_name']; ?></td>
                        <td><?php echo $cliente['phone'] ? $cliente['phone'] : '<span class="text-muted">N/A</span>'; ?></td>
                        <td><?php echo $cliente['email']; ?></td>
                        <td><?php echo $cliente['street'] ? $cliente['street'] : '<span class="text-muted">N/A</span>'; ?></td>
                        <td><?php echo $cliente['city'] ? $cliente['city'] : '<span class="text-muted">N/A</span>'; ?></td>
                        <td><?php echo $cliente['state'] ? $cliente['state'] : '<span class="text-muted">N/A</span>'; ?></td>
                        <td><?php echo $cliente['zip_code'] ? $cliente['zip_code'] : '<span class="text-muted">N/A</span>'; ?></td>
                        <td>
                            <a class="btn btn-outline-primary" href="editar.php?txtID=<?php echo $cliente['customer_id']; ?>" role="button">Editar</a>
                            <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $cliente['customer_id']; ?>" role="button"
                            onclick="return confirm('¿Estás seguro de que quieres eliminar este cliente?')">Eliminar</a>
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