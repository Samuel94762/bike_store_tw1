<?php include("../../bd.php");

// Recuperar el ID del cliente a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales del cliente
    $sentencia = $conexion->prepare("SELECT * FROM customers WHERE customer_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $cliente = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Asignar los valores actuales a variables
    $first_name = $cliente['first_name'];
    $last_name = $cliente['last_name'];
    $imagen_actual = $cliente['imagen'];
    $phone = $cliente['phone'];
    $email = $cliente['email'];
    $street = $cliente['street'];
    $city = $cliente['city'];
    $state = $cliente['state'];
    $zip_code = $cliente['zip_code'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $first_name = (isset($_POST["first_name"])) ? $_POST["first_name"] : "";
    $last_name = (isset($_POST["last_name"])) ? $_POST["last_name"] : "";
    $imagen = (isset($_FILES["imagen"]['name'])) ? $_FILES["imagen"]['name'] : "";
    $phone = (isset($_POST["phone"])) ? $_POST["phone"] : "";
    $email = (isset($_POST["email"])) ? $_POST["email"] : "";
    $street = (isset($_POST["street"])) ? $_POST["street"] : "";
    $city = (isset($_POST["city"])) ? $_POST["city"] : "";
    $state = (isset($_POST["state"])) ? $_POST["state"] : "";
    $zip_code = (isset($_POST["zip_code"])) ? $_POST["zip_code"] : "";
    
    // Preparar la actualización de los datos
    $sentencia = $conexion->prepare("UPDATE customers 
                                    SET first_name = :first_name, 
                                        last_name = :last_name, 
                                        imagen = :imagen, 
                                        phone = :phone, 
                                        email = :email, 
                                        street = :street, 
                                        city = :city, 
                                        state = :state, 
                                        zip_code = :zip_code 
                                    WHERE customer_id = :id");

    // Asignamos los valores
    $sentencia->bindParam(":first_name", $first_name);
    $sentencia->bindParam(":last_name", $last_name);
    
    // Manejo de la imagen
    $fecha_ = new DateTime();
    $nombre_archivo_imagen = ($imagen != '') ? $fecha_->getTimestamp() . "_" . $_FILES["imagen"]['name'] : $imagen_actual;
    
    // Si se sube una nueva imagen
    $tmp_imagen = $_FILES["imagen"]["tmp_name"];
    if($tmp_imagen != ''){
        // Mover la nueva imagen
        move_uploaded_file($tmp_imagen, "./imagen/" . $nombre_archivo_imagen);
        
        // Eliminar la imagen anterior si existe y es diferente a la nueva
        if($imagen_actual != "" && $imagen_actual != $nombre_archivo_imagen){
            if(file_exists("./imagen/" . $imagen_actual)){
                unlink("./imagen/" . $imagen_actual);
            }
        }
    }
    
    $sentencia->bindParam(":imagen", $nombre_archivo_imagen);
    $sentencia->bindParam(":phone", $phone);
    $sentencia->bindParam(":email", $email);
    $sentencia->bindParam(":street", $street);
    $sentencia->bindParam(":city", $city);
    $sentencia->bindParam(":state", $state);
    $sentencia->bindParam(":zip_code", $zip_code);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    
    $mensaje = "Cliente actualizado";
    header("Location: index.php?mensaje=" . $mensaje);
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Cliente</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID del cliente -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            
            <div class="mb-3">
                <label for="first_name" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="first_name" id="first_name" 
                    aria-describedby="helpId" placeholder="Nombre"
                    value="<?php echo $first_name; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre del cliente</small>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Apellido</label>
                <input type="text" class="form-control" name="last_name" id="last_name" 
                    aria-describedby="helpId" placeholder="Apellido"
                    value="<?php echo $last_name; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el apellido del cliente</small>
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen</label>
                <br/>
                <?php if($imagen_actual != "") { ?>
                    <img width="100" src="./imagen/<?php echo $imagen_actual; ?>" 
                         class="img-fluid rounded mb-2" alt="Imagen actual del cliente"/>
                    <br/>
                <?php } ?>
                <input type="file" class="form-control" name="imagen" id="imagen" aria-describedby="helpId"
                    placeholder="imagen"
                />
                <small id="helpId" class="form-text text-muted">Seleccione una nueva imagen si desea cambiar la actual</small>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="phone" id="phone" 
                    aria-describedby="helpId" placeholder="Teléfono"
                    value="<?php echo $phone; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el teléfono del cliente</small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" 
                    aria-describedby="helpId" placeholder="Email"
                    value="<?php echo $email; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el email del cliente</small>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Calle</label>
                <input type="text" class="form-control" name="street" id="street" 
                    aria-describedby="helpId" placeholder="Calle"
                    value="<?php echo $street; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la calle</small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="city" id="city" 
                    aria-describedby="helpId" placeholder="Ciudad"
                    value="<?php echo $city; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la ciudad</small>
            </div>

            <div class="mb-3">
                <label for="state" class="form-label">Estado</label>
                <input type="text" class="form-control" name="state" id="state" 
                    aria-describedby="helpId" placeholder="Estado"
                    value="<?php echo $state; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el estado</small>
            </div>

            <div class="mb-3">
                <label for="zip_code" class="form-label">Código Postal</label>
                <input type="text" class="form-control" name="zip_code" id="zip_code" 
                    aria-describedby="helpId" placeholder="Código Postal"
                    value="<?php echo $zip_code; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el código postal</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Cliente</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>