<?php include("../../bd.php");
include("../../config.php");
    
    // Verificar si es admin
    if (!is_admin()) {
        header("Location: " . APP_URL . "landing.php");
        exit;
    }
// Recuperar el ID del usuario a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales del usuario
    $sentencia = $conexion->prepare("SELECT * FROM usuarios WHERE user_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $usuario_data = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Asignar los valores actuales a variables
    $usuario = $usuario_data['usuario'];
    $email = $usuario_data['email'];
    $role = $usuario_data['role'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $usuario = (isset($_POST["usuario"])) ? $_POST["usuario"] : "";
    $password = (isset($_POST["password"])) ? $_POST["password"] : "";
    $email = (isset($_POST["email"])) ? $_POST["email"] : "";
    $role = (isset($_POST["role"])) ? $_POST["role"] : "";
    
    // Preparar la actualización de los datos
    if(!empty($password)) {
        // Si se proporciona una nueva contraseña, actualizarla
        $password_encriptada = md5($password);
        $sentencia = $conexion->prepare("UPDATE usuarios 
                                        SET usuario = :usuario, 
                                            password = :password, 
                                            email = :email, 
                                            role = :role 
                                        WHERE user_id = :id");
        $sentencia->bindParam(":password", $password_encriptada);
    } else {
        // Si no se proporciona contraseña, mantener la actual
        $sentencia = $conexion->prepare("UPDATE usuarios 
                                        SET usuario = :usuario, 
                                            email = :email, 
                                            role = :role 
                                        WHERE user_id = :id");
    }

    // Asignamos los valores
    $sentencia->bindParam(":usuario", $usuario);
    $sentencia->bindParam(":email", $email);
    $sentencia->bindParam(":role", $role);
    $sentencia->bindParam(":id", $txtID);
    
    try {
        $sentencia->execute();
        $mensaje = "Usuario actualizado correctamente";
        header("Location: index.php?mensaje=" . $mensaje);
    } catch(PDOException $e) {
        $mensaje = "Error: El usuario o email ya existen";
    }
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Usuario</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID del usuario -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" 
                    aria-describedby="helpId" placeholder="Nombre de usuario"
                    value="<?php echo $usuario; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de usuario (único)</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password" 
                    aria-describedby="helpId" placeholder="Dejar en blanco para mantener la actual"
                />
                <small id="helpId" class="form-text text-muted">Deje en blanco si no desea cambiar la contraseña</small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" 
                    aria-describedby="helpId" placeholder="Email"
                    value="<?php echo $email; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el email del usuario (único)</small>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Rol</label>
                <select class="form-select form-select-sm" name="role" id="role" required>
                    <option value="user" <?php echo ($role == 'user') ? 'selected' : ''; ?>>Usuario</option>
                    <option value="vendedor" <?php echo ($role == 'vendedor') ? 'selected' : ''; ?>>Vendedor</option>
                    <option value="admin" <?php echo ($role == 'admin') ? 'selected' : ''; ?>>Administrador</option>
                </select>
                <small id="helpId" class="form-text text-muted">Seleccione el rol del usuario</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Usuario</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
        
        <?php if(isset($mensaje) && strpos($mensaje, 'Error') !== false) { ?>
            <div class="alert alert-danger mt-3" role="alert">
                <?php echo $mensaje; ?>
            </div>
        <?php } ?>
    </div>
</div>

<?php include("../../templates/footer.php") ?>