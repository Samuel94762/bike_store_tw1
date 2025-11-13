<?php include("../../bd.php");

if($_POST){
    // Recolectando los datos del método POST
    $first_name = (isset($_POST["first_name"]) ? $_POST["first_name"] : "");
    $last_name = (isset($_POST["last_name"]) ? $_POST["last_name"] : "");
    $imagen = (isset($_FILES["imagen"]['name']) ? $_FILES["imagen"]['name'] : "");
    $phone = (isset($_POST["phone"]) ? $_POST["phone"] : "");
    $email = (isset($_POST["email"]) ? $_POST["email"] : "");
    $street = (isset($_POST["street"]) ? $_POST["street"] : "");
    $city = (isset($_POST["city"]) ? $_POST["city"] : "");
    $state = (isset($_POST["state"]) ? $_POST["state"] : "");
    $zip_code = (isset($_POST["zip_code"]) ? $_POST["zip_code"] : "");
    
    // Preparar la inserción de los datos
    $sentencia = $conexion->prepare("INSERT INTO customers(customer_id, first_name, last_name, imagen, phone, email, street, city, state, zip_code)
    VALUES(null, :first_name, :last_name, :imagen, :phone, :email, :street, :city, :state, :zip_code)");

    // Asignamos los valores que hacen uso de la :variable 
    $sentencia->bindParam(":first_name", $first_name);
    $sentencia->bindParam(":last_name", $last_name);
    
    // Adjuntamos la imagen con un nombre distinto de archivo
    $fecha_ = new DateTime();
    $nombre_archivo_imagen = ($imagen != '') ? $fecha_->getTimestamp() . "_" . $_FILES["imagen"]['name'] : "";
    
    // Creamos el archivo temporal de la imagen
    $tmp_imagen = $_FILES["imagen"]["tmp_name"];
    if($tmp_imagen != ''){
        move_uploaded_file($tmp_imagen, "./imagen/" . $nombre_archivo_imagen);
    }
    
    $sentencia->bindParam(":imagen", $nombre_archivo_imagen);
    $sentencia->bindParam(":phone", $phone);
    $sentencia->bindParam(":email", $email);
    $sentencia->bindParam(":street", $street);
    $sentencia->bindParam(":city", $city);
    $sentencia->bindParam(":state", $state);
    $sentencia->bindParam(":zip_code", $zip_code);
    
    $sentencia->execute();
    
    $mensaje = "Cliente agregado";
    header("Location: index.php?mensaje=".$mensaje);
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Crear Cliente</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="first_name" class="form-label">Nombre</label>
                <input type="text" class="form-control" name="first_name" id="first_name" 
                    aria-describedby="helpId" placeholder="Nombre"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre del cliente</small>
            </div>

            <div class="mb-3">
                <label for="last_name" class="form-label">Apellido</label>
                <input type="text" class="form-control" name="last_name" id="last_name" 
                    aria-describedby="helpId" placeholder="Apellido"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el apellido del cliente</small>
            </div>

            <div class="mb-3">
                <label for="imagen" class="form-label">Imagen</label>
                <input type="file" class="form-control" name="imagen" id="imagen" aria-describedby="helpId"
                    placeholder="imagen"
                />
                <small id="helpId" class="form-text text-muted">Seleccione una imagen del cliente</small>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Teléfono</label>
                <input type="text" class="form-control" name="phone" id="phone" 
                    aria-describedby="helpId" placeholder="Teléfono"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el teléfono del cliente</small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" name="email" id="email" 
                    aria-describedby="helpId" placeholder="Email"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el email del cliente</small>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Calle</label>
                <input type="text" class="form-control" name="street" id="street" 
                    aria-describedby="helpId" placeholder="Calle"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la calle</small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Ciudad</label>
                <input type="text" class="form-control" name="city" id="city" 
                    aria-describedby="helpId" placeholder="Ciudad"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la ciudad</small>
            </div>

            <div class="mb-3">
                <label for="state" class="form-label">Estado</label>
                <input type="text" class="form-control" name="state" id="state" 
                    aria-describedby="helpId" placeholder="Estado"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el estado</small>
            </div>

            <div class="mb-3">
                <label for="zip_code" class="form-label">Código Postal</label>
                <input type="text" class="form-control" name="zip_code" id="zip_code" 
                    aria-describedby="helpId" placeholder="Código Postal"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el código postal</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Agregar Cliente</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>