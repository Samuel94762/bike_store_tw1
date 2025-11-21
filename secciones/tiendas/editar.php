<?php include("../../bd.php");

// Recuperar el ID del producto a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales del producto
    $sentencia = $conexion->prepare("SELECT * FROM stores WHERE store_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $registro = $sentencia->fetch(PDO::FETCH_ASSOC);
    //print_r($registro);

    // Asignar los valores actuales a variables
    $store_name = $registro['store_name'];
    $phone = $registro['phone'];
    $email = $registro['email'];
    $street = $registro['street'];
    $city = $registro['city'];
    $state = $registro['state'];
    $status = $registro['status'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    // Recolectando los datos del método POST
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $store_name = (isset($_POST["store_name"])) ? $_POST["store_name"] : "";
    $phone = (isset($_POST["phone"])) ? $_POST["phone"] : "";
    $email = (isset($_POST["email"])) ? $_POST["email"] : "";
    $street = (isset($_POST["street"])) ? $_POST["street"] : "";
    $city = (isset($_POST["city"])) ? $_POST["city"] : "";
    $state = (isset($_POST["state"])) ? $_POST["state"] : "";
    $status = (isset($_POST["status"])) ? $_POST["status"] : "";
    
    // Preparar la actualización de los datos
    $sentencia = $conexion->prepare("UPDATE stores 
                                    SET store_name = :store_name, 
                                        phone = :phone, 
                                        email = :email, 
                                        street = :street, 
                                        city = :city,
                                        state = :state, 
                                        status = :status 
                                    WHERE store_id = :id");

    // Asignamos los valores que hacen uso de la :variable 
    $sentencia ->bindParam(":store_name", $store_name);
    $sentencia ->bindParam(":phone",$phone);
    $sentencia ->bindParam(":email",$email);
    $sentencia ->bindParam(":street",$street);
    $sentencia ->bindParam(":city",$city);
    $sentencia ->bindParam(":state",$state);
    $sentencia ->bindParam(":status",$status);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    
    $mensaje = "Registro actualizado";
    header("Location: index.php?mensaje=" . $mensaje);
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Producto</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID del producto -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            

            <div class="mb-3">
                <label for="store_name" class="form-label">Nombre de la Tienda</label>
                <input type="text" class="form-control" name="store_name"  id="store_name" 
                aria-describedby="helpId" placeholder="Nombre"
                value="<?php echo $store_name; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de la Tienda</small>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Tel&eacute;fono</label>
                <input type = "text" class="form-control" name="phone"  id="phone" aria-describedby="helpId"
                    placeholder="Tel&eacute;fono"
                    value="<?php echo $phone; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Tel&eacute;fono </small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type = "text" class="form-control" name="email"  id="email" aria-describedby="helpId"
                    placeholder="Email"
                    value="<?php echo $email; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Tel&eacute;fono </small>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Calle</label>
                <input type = "text" class="form-control" name="street"  id="street" aria-describedby="helpId"
                    placeholder="Calle"
                    value="<?php echo $street; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la Calle </small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Ciudad</label>
                <input type = "text" class="form-control" name="city"  id="city" aria-describedby="helpId"
                    placeholder="Ciudad"
                    value="<?php echo $city; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la ciudad</small>
            </div>

            <div class="mb-3">
                <label for="state" class="form-label">Departamento</label>
                <input type = "text" class="form-control" name="state"  id="state" aria-describedby="helpId"
                    placeholder="Departamento"
                    value="<?php echo $state; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Departamento</small>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" class="form-control" id="status" required>
                    <option value="Abierta" <?php echo ($status == 'Abierta') ? 'selected' : ''; ?>>Abierta</option>
                    <option value="Cerrada" <?php echo ($status == 'Cerrada') ? 'selected' : ''; ?>>Cerrada</option>
                    <option value="Anulado" <?php echo ($status == 'Anulado') ? 'selected' : ''; ?>>Anulado</option>
                </select>
                <small class="form-text text-muted">Seleccione el estado de la Tienda</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Registro</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>