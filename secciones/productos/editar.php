<?php include("../../bd.php");

// Recuperar el ID del producto a editar
if(isset($_GET['txtID'])){
    $txtID = (isset($_GET['txtID'])) ? $_GET['txtID'] : "";
    
    // Obtener los datos actuales del producto
    $sentencia = $conexion->prepare("SELECT * FROM products WHERE product_id = :id");
    $sentencia->bindParam(":id", $txtID);
    $sentencia->execute();
    $producto = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    // Asignar los valores actuales a variables
    $product_name = $producto['product_name'];
    $foto_actual = $producto['foto'];
    $model_year = $producto['model_year'];
    $price = $producto['price'];
    $category_id = $producto['category_id'];
}

// Procesar el formulario cuando se envía por POST
if($_POST){
    // Recolectando los datos del método POST
    $txtID = (isset($_POST["txtID"])) ? $_POST["txtID"] : "";
    $product_name = (isset($_POST["product_name"])) ? $_POST["product_name"] : "";
    $foto = (isset($_FILES["foto"]['name'])) ? $_FILES["foto"]['name'] : "";
    $model_year = (isset($_POST["model_year"])) ? $_POST["model_year"] : "";
    $price = (isset($_POST["price"])) ? $_POST["price"] : "";
    $category_id = (isset($_POST["category_id"])) ? $_POST["category_id"] : "";
    
    // Preparar la actualización de los datos
    $sentencia = $conexion->prepare("UPDATE products 
                                    SET product_name = :product_name, 
                                        foto = :foto, 
                                        model_year = :model_year, 
                                        price = :price, 
                                        category_id = :category_id 
                                    WHERE product_id = :id");

    // Asignamos los valores que hacen uso de la :variable 
    $sentencia->bindParam(":product_name", $product_name);
    
    // Manejo de la imagen
    $fecha_ = new DateTime();
    $nombre_archivo_foto = ($foto != '') ? $fecha_->getTimestamp() . "_" . $_FILES["foto"]['name'] : $foto_actual;
    
    // Si se sube una nueva foto
    $tmp_foto = $_FILES["foto"]["tmp_name"];
    if($tmp_foto != ''){
        // Mover la nueva imagen
        move_uploaded_file($tmp_foto, "./imagen/" . $nombre_archivo_foto);
        
        // Eliminar la imagen anterior si existe y es diferente a la nueva
        if($foto_actual != "" && $foto_actual != $nombre_archivo_foto){
            if(file_exists("./imagen/" . $foto_actual)){
                unlink("./imagen/" . $foto_actual);
            }
        }
    }
    
    $sentencia->bindParam(":foto", $nombre_archivo_foto);
    $sentencia->bindParam(":model_year", $model_year);
    $sentencia->bindParam(":price", $price);
    $sentencia->bindParam(":category_id", $category_id);
    $sentencia->bindParam(":id", $txtID);
    
    $sentencia->execute();
    
    $mensaje = "Registro actualizado";
    header("Location: index.php?mensaje=" . $mensaje);
}

// Consulta para obtener las categorías
$sentencia = $conexion->prepare("SELECT * FROM categories ORDER BY category_name ASC");
$sentencia->execute();
$lista_categorias = $sentencia->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Editar Producto</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <!-- Campo oculto para el ID del producto -->
            <input type="hidden" name="txtID" value="<?php echo $txtID; ?>">
            
            <div class="mb-3">
                <label for="product_name" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="product_name" id="product_name" 
                    aria-describedby="helpId" placeholder="Nombre"
                    value="<?php echo $product_name; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre del producto</small>
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <br/>
                <?php if($foto_actual != "") { ?>
                    <img width="100" src="./imagen/<?php echo $foto_actual; ?>" 
                         class="img-fluid rounded mb-2" alt="Foto actual del producto"/>
                    <br/>
                <?php } ?>
                <input type="file" class="form-control" name="foto" id="foto" aria-describedby="helpId"
                    placeholder="foto"
                />
                <small id="helpId" class="form-text text-muted">Seleccione una nueva imagen si desea cambiar la foto actual</small>
            </div>

            <div class="mb-3">
                <label for="model_year" class="form-label">Modelo Año</label>
                <input type="number" class="form-control" name="model_year" id="model_year" 
                    aria-describedby="helpId" placeholder="Modelo Año"
                    value="<?php echo $model_year; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el modelo año del producto</small>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="number" class="form-control" name="price" id="price" 
                    aria-describedby="helpId" placeholder="Precio" step="0.01"
                    value="<?php echo $price; ?>"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el precio del producto</small>
            </div>

            <div class="mb-3">
                <label for="category_id" class="form-label">Categoría</label>
                <select class="form-select form-select-sm" name="category_id" id="category_id">
                    <option value="">Seleccione una opción</option>
                    <?php foreach($lista_categorias as $registro) { ?>
                    <option value="<?php echo $registro['category_id']; ?>" 
                        <?php echo ($registro['category_id'] == $category_id) ? 'selected' : ''; ?>>
                        <?php echo $registro['category_name']; ?>
                    </option>
                    <?php } ?>
                </select>
            </div>

            <button type="submit" class="btn btn-outline-success">Actualizar Registro</button>
            <a name="" id="" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>

<?php include("../../templates/footer.php") ?>