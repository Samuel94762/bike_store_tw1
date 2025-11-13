<?php include("../../bd.php");

// Recuperar el ID de la categoría a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales de la categoría
    $sentencia = $conexion->prepare("SELECT * FROM categories WHERE category_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $categoria = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Asignar los valores actuales a variables
    $category_name = $categoria['category_name'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $category_name = (isset($_POST["category_name"])) ? $_POST["category_name"] : "";
    
    // Preparar la actualización de los datos
    $sentencia = $conexion->prepare("UPDATE categories 
                                    SET category_name = :category_name 
                                    WHERE category_id = :id");

    // Asignamos los valores
    $sentencia->bindParam(":category_name", $category_name);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    
    $mensaje = "Categoría actualizada";
    header("Location: index.php?mensaje=" . $mensaje);
}

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Categoría</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID de la categoría -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            
            <div class="mb-3">
                <label for="category_name" class="form-label">Nombre de la Categoría</label>
                <input type="text" class="form-control" name="category_name" id="category_name" 
                    aria-describedby="helpId" placeholder="Nombre de la categoría"
                    value="<?php echo $category_name; ?>"
                    required
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre de la categoría</small>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Categoría</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>