<?php include("../../bd.php");

if($_POST){
    // Recolectando los datos del método POST
    $category_name = (isset($_POST["category_name"]) ? $_POST["category_name"] : "");
    
    // Preparar la inserción de los datos
    $sentencia = $conexion->prepare("INSERT INTO categories(category_id, category_name)
    VALUES(null, :category_name)");

    // Asignamos los valores que hacen uso de la :variable 
    $sentencia->bindParam(":category_name", $category_name);
    $sentencia->execute();
    
    $mensaje = "Categoría agregada";
    header("Location: index.php?mensaje=".$mensaje);
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Crear Categoría</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="category_name" class="form-label">Nombre de la Categoría</label>
                <input type="text" class="form-control" name="category_name" id="category_name" 
                    aria-describedby="helpId" placeholder="Nombre de la categoría"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de la categoría</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Agregar Categoría</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>