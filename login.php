<?php 
session_start();
if ($_POST){
    include("../../bd.php");
    $sentencia=$conexion->prepare("SELECT*, count(*) as n_usuario FROM usuarios
        WHERE usuario=usuario AND clave=clave");
    $usuario=$_POST['usuario'];
    $clave=$_POST['clave'];

    $sentencia->bindParam("usuario", $usuario);
    $sentencia->bindParam("clave", $clave);
    $sentencia->execute();
    $registro=$sentencia->fetch(PDO::FETCH_LAZY);

    if($registro['n_usuarios']>0){
        $_SESSION['usuario']=$registro["usuario"];
        $_SESSION['usuario_id']=$registro["usuario_id"];
        $_SESSION['email']=$registro["email"];
        $_SESSION['role']=$registro["role"];
        $_SESSION["logueado"]=true;
        
    }
}
?>
<!doctype html>
<html lang="es">
    <head>
        <title>Identificación de Usuarios</title>
        <!-- Required meta tags -->
        <meta charset="utf-8" />
        <meta
            name="viewport"
            content="width=device-width, initial-scale=1, shrink-to-fit=no"
        />

        <!-- Bootstrap CSS v5.2.1 -->
        <link
            href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css"
            rel="stylesheet"
            integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN"
            crossorigin="anonymous"
        />
    </head>

    <body>
        <header>
            <!-- place navbar here -->
             <divclass="container">
                <div class="row" >
                <div class="col-md-4"></div>
                <div class ="col-md-4">
                <br><br><br><br><br><br>
                <div class="card">
                    <div class="card-header" style="text-align: center"><h5>Iniciar Sesión</h5></div>
                    <div class="card-body">
                        <?php if(isset($mensaje)){?>
                            <div
                                class="alert alert-danger"
                                role="alert"
                            >
                                <strong><?php echo $mensaje?></strong>
                            </div>
                            
                        <?php 
                    }?>
                    
                        <h4 class="card-title">Title</h4>
                        <p class="card-text">Text</p>
                    </div>
                    <div class="card-footer text-muted">Footer</div>
                </div>
                
             </div>
             
        </header>
        <main></main>
        <footer>
            <!-- place footer here -->
        </footer>
        <!-- Bootstrap JavaScript Libraries -->
        <script
            src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"
            integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r"
            crossorigin="anonymous"
        ></script>

        <script
            src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js"
            integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+"
            crossorigin="anonymous"
        ></script>
    </body>
</html>
