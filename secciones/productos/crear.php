<?php include("../../bd.php");

if($_POST){
    //Recolectando los datos del método POST
    $product_name=(isset($_POST["product_name"])?$_POST["product_name"]:"");
    $foto=(isset($_FILES["foto"]['name'])?$_FILES["foto"]['name']:"");
    $model_year=(isset($_POST["model_year"])?$_POST["model_year"]:"");
    $price=(isset($_POST["price"])?$_POST["price"]:"");
    $category_id=(isset($_POST["category_id"])?$_POST["category_id"]:"");
    //Preparar la inserción de los datos
    $sentencia = $conexion->prepare("INSERT INTO products(product_id, product_name, foto, model_year, price, category_id)
    VALUES(null,:product_name,:foto,:model_year,:price,:category_id)");

    //Asignamos los valores que hacen uso de la :variable 
    $sentencia ->bindParam(":product_name",$product_name);
    //Adjuntamos la foto con un nombre distinto de archivo
    $fecha_=new DateTime();
    $nombre_archivo_foto= ($foto!='')?$fecha_->getTimestamp()."_".$_FILES["foto"]['name']:"";
    //Creamos el archivo temporal de la foto 
    $tmp_foto = $_FILES["foto"]["tmp_name"];
    if($tmp_foto!=''){
        move_uploaded_file($tmp_foto, "./imagen/".$nombre_archivo_foto);
    }
    $sentencia ->bindParam(":foto", $nombre_archivo_foto);
    $sentencia ->bindParam(":model_year",$model_year);
    $sentencia ->bindParam(":price",$price);
    $sentencia ->bindParam(":category_id",$category_id);
    $sentencia ->execute();
    $mensaje = "Registro agregado";
    header("Location: index.php?mensaje".$mensaje);

}

//Consulta para obtener las categorías
$sentencia = $conexion->prepare("SELECT * FROM categories ORDER BY category_name ASC");
$sentencia ->execute();
$lista_categorias = $sentencia ->fetchAll(PDO::FETCH_ASSOC);

?>

<?php include("../../templates/header.php") ?>

<div class="card">
    <div class="card-header">Datos del Producto</div>
    <div class="card-body">
        <form action="" method="post" enctype="multipart/form-data">
            
            <div class="mb-3">
                <label for="product_name" class="form-label">Nombre del Producto</label>
                <input type="text" class="form-control" name="product_name"  id="product_name" 
                aria-describedby="helpId" placeholder="Nombre"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el nombre del producto</small>
            </div>

            <div class="mb-3">
                <label for="foto" class="form-label">Foto</label>
                <input type = "file" class="form-control" name="foto"  id="foto" aria-describedby="helpId"
                    placeholder="foto"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el archivo </small>
            </div>

            <div class="mb-3">
                <label for="model_year" class="form-label">Modelo Año</label>
                <input type="number" class="form-control" name="model_year"  id="model_year" 
                aria-describedby="helpId" placeholder="Modelo Año"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el modelo año del producto</small>
            </div>

            <div class="mb-3">
                <label for="price" class="form-label">Precio</label>
                <input type="number" class="form-control" name="price"  id="price" 
                aria-describedby="helpId" placeholder="Precio"
                />
                <small id="helpId" class="form-text text-muted">Ingrese el precio del producto</small>
            </div>

                <div class="mb-3">
                    <label for="category_id" class="form-label">Categoría</label>
                    <select
                        class="form-select form-select-sm" name="category_id" id="category_id">
                        <option selected>Seleccione una opción</option>
                        <?php foreach($lista_categorias as $registro) {?>
                        <option value="<?php echo $registro['category_id']?>">
                            <?php echo $registro['category_name']?></option>
                        <?php }?>

                    </select>
                </div>

                <button type="submit" class="btn btn-outline-success">Agregar Registro</button>
                <a name ="" id= "" class="btn btn-outline-primary" href="index.php" role="button">Cancelar</a>

        </form>
    </div>
</div>


<?php include("../../templates/footer.php") ?>