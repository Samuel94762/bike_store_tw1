<?php include("../../bd.php");

if($_POST){
    // Recolectando los datos del método POST
    $usuario = (isset($_POST["usuario"]) ? $_POST["usuario"] : "");
    $password = (isset($_POST["password"]) ? $_POST["password"] : "");
    $email = (isset($_POST["email"]) ? $_POST["email"] : "");
    $role = (isset($_POST["role"]) ? $_POST["role"] : "");
    
    // Validar que la contraseña no esté vacía
    if(empty($password)) {
        $mensaje = "Error: La contraseña no puede estar vacía";
    } else {
        // Encriptar la contraseña con MD5
        $password_encriptada = md5($password);
        
        // Preparar la inserción de los datos
        $sentencia = $conexion->prepare("INSERT INTO usuarios(user_id, usuario, password, email, role)
        VALUES(null, :usuario, :password, :email, :role)");

        // Asignamos los valores que hacen uso de la :variable 
        $sentencia->bindParam(":usuario", $usuario);
        $sentencia->bindParam(":password", $password_encriptada);
        $sentencia->bindParam(":email", $email);
        $sentencia->bindParam(":role", $role);
        
        try {
            $sentencia->execute();
            $mensaje = "Usuario agregado correctamente";
            header("Location: index.php?mensaje=".$mensaje);
        } catch(PDOException $e) {
            $mensaje = "Error: El usuario o email ya existen";
        }
    }
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Crear Usuario</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="usuario" class="form-label">Usuario</label>
                <input type="text" class="form-control" name="usuario" id="usuario" 
                    aria-describedby="helpId" placeholder="Nombre de usuario"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de usuario (único)</small>
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Contraseña</label>
                <input type="password" class="form-control" name="password" id="password" 
                    aria-describedby="helpId" placeholder="Contraseña"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese la contraseña (se encriptará con MD5)</small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" 
                    aria-describedby="helpId" placeholder="Email"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el email del usuario (único)</small>
            </div>

            <div class="mb-3">
                <label for="role" class="form-label">Rol</label>
                <select class="form-select form-select-sm" name="role" id="role" required>
                    <option value="user">Usuario</option>
                    <option value="vendedor">Vendedor</option>
                    <option value="admin">Administrador</option>
                </select>
                <small id="helpId" class="form-text text-muted">Seleccione el rol del usuario</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Agregar Usuario</button>
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