<?php include("../../bd.php");

// Eliminar usuario
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Verificar si es el usuario admin (podemos prevenir eliminar el admin principal)
    if($txtID == 1) {
        $mensaje = "Error: No se puede eliminar el usuario administrador principal";
        header("Location: index.php?mensaje=".$mensaje);
        exit();
    }
    
    // Verificar si hay órdenes asociadas a este usuario
    $sentencia = $conexion->prepare("SELECT COUNT(*) as total FROM orders WHERE user_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $resultado = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if($resultado['total'] > 0){
        $mensaje = "Error: No se puede eliminar el usuario porque tiene órdenes asociadas";
        header("Location: index.php?mensaje=".$mensaje);
        exit();
    }
    
    // Borrar datos del usuario
    $sentencia = $conexion->prepare("DELETE FROM usuarios WHERE user_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    
    $mensaje = "Usuario eliminado";
    header("Location: index.php?mensaje=".$mensaje);
}

// Consulta para obtener todos los usuarios ORDENADOS POR ID ASCENDENTE
$sentencia = $conexion->prepare("SELECT * FROM usuarios ORDER BY user_id ASC");
$sentencia->execute();
$lista_usuarios = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<br>
<div class="card">
    <div class="card-header">
        <a name="" id="" class="btn btn-outline-primary" href="crear.php" role="button">Nuevo Usuario</a>
    </div>
    <div class="card-body">
        <div class="table-responsive-sm">
            <table class="table table-primary" id="tabla_id">
                <thead>
                    <tr>
                        <th scope="col">ID</th>
                        <th scope="col">Usuario</th>
                        <th scope="col">Email</th>
                        <th scope="col">Rol</th>
                        <th scope="col">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($lista_usuarios as $usuario) { ?>
                    <tr class="">
                        <td scope="row"><?php echo $usuario['user_id']; ?></td>
                        <td><?php echo $usuario['usuario']; ?></td>
                        <td><?php echo $usuario['email']; ?></td>
                        <td>
                            <span class="badge 
                                <?php 
                                switch($usuario['role']) {
                                    case 'admin': echo 'bg-danger'; break;
                                    case 'vendedor': echo 'bg-warning'; break;
                                    case 'user': echo 'bg-info'; break;
                                    default: echo 'bg-secondary';
                                }
                                ?>">
                                <?php echo ucfirst($usuario['role']); ?>
                            </span>
                        </td>
                        <td>
                            <a class="btn btn-outline-primary" href="editar.php?txtID=<?php echo $usuario['user_id']; ?>" role="button">Editar</a>
                            <?php if($usuario['user_id'] != 1) { ?>
                                <a class="btn btn-outline-danger" href="index.php?txtID=<?php echo $usuario['user_id']; ?>" role="button"
                                onclick="return confirm('¿Estás seguro de que quieres eliminar este usuario?')">Eliminar</a>
                            <?php } else { ?>
                                <button class="btn btn-outline-secondary" disabled>No eliminar</button>
                            <?php } ?>
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