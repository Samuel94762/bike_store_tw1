<?php include("../../bd.php");

if($_POST){
    //Recolectando los datos del método POST
    $store_name=(isset($_POST["store_name"])?$_POST["store_name"]:"");
    $phone=(isset($_POST["phone"])?$_POST["phone"]:"");
    $email=(isset($_POST["email"])?$_POST["email"]:"");
    $street=(isset($_POST["street"])?$_POST["street"]:"");
    $city=(isset($_POST["city"])?$_POST["state"]:"");
    $state=(isset($_POST["state"])?$_POST["state"]:"");
    $status=(isset($_POST["status"])?$_POST["status"]:"");
    //Preparar la inserción de los datos
    $sentencia = $conexion->prepare("INSERT INTO stores(store_id, store_name, phone, email, street, city, state, status)
    VALUES(null, :store_name, :phone, :email, :street, :city, :state, :status)");

    $sentencia ->bindParam(":store_name", $store_name);
    $sentencia ->bindParam(":phone",$phone);
    $sentencia ->bindParam(":email",$email);
    $sentencia ->bindParam(":street",$street);
    $sentencia ->bindParam(":city",$city);
    $sentencia ->bindParam(":state",$state);
    $sentencia ->bindParam(":status",$status);
    $sentencia ->execute();
    $mensaje = "Registro agregado";
    header("Location: index.php?mensaje".$mensaje);

}
?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Datos de la Tienda</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="store_name" class="form-label">Nombre de la Tienda</label>
                <input type="text" class="form-control" name="store_name"  id="store_name" 
                aria-describedby="helpId" placeholder="Nombre"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de la Tienda</small>
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Tel&eacute;fono</label>
                <input type = "text" class="form-control" name="phone"  id="phone" aria-describedby="helpId"
                    placeholder="Tel&eacute;fono"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Tel&eacute;fono </small>
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type = "text" class="form-control" name="email"  id="email" aria-describedby="helpId"
                    placeholder="Email"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Tel&eacute;fono </small>
            </div>

            <div class="mb-3">
                <label for="street" class="form-label">Calle</label>
                <input type = "text" class="form-control" name="street"  id="street" aria-describedby="helpId"
                    placeholder="Calle"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la Calle </small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Ciudad</label>
                <input type = "text" class="form-control" name="city"  id="city" aria-describedby="helpId"
                    placeholder="Ciudad"
                />
                <small id="helpId" class="form-text text-muted">Ingrese la ciudad</small>
            </div>

            <div class="mb-3">
                <label for="city" class="form-label">Estado</label>
                <input type = "text" class="form-control" name="state"  id="state" aria-describedby="helpId"
                    placeholder="Ciudad"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el Departamento</small>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label">Estado</label>
                <select name="status" class="form-control" id="status" required>
                    <option value="Abierta" selected>Abierta</option>
                    <option value="Cerrada">Cerrada</option>
                </select>
                <small class="form-text text-muted">Seleccione el estado de la Tienda</small>
            </div>

                <button type="submit" class="btn btn-outline-success">Agregar Registro</button>
                <a name ="" id= "" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>


<?php include("../../templates/footer.php") ?>