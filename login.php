<?php
    include("config.php");
    
    if($_POST){
        $sentencia=$conexion->prepare("SELECT *, count(*) as n_usuario FROM usuarios
            WHERE usuario=:usuario AND password=:password");
        $usuario=$_POST['usuario'];
        $password=$_POST['password'];

        $sentencia->bindParam(":usuario",$usuario);
        $sentencia->bindParam(":password",$password);
        $sentencia->execute();
        $registro=$sentencia->fetch(PDO::FETCH_LAZY);

        if($registro['n_usuario']>0){
            $_SESSION['usuario']=$registro["usuario"];
            $_SESSION['user_id']=$registro["user_id"];
            $_SESSION['email']=$registro["email"];
            $_SESSION['role']=$registro["role"];
            $_SESSION['logueado']=true;
            
            // Redirigir según el rol
            if ($registro["role"] === 'admin') {
                header("Location: index.php");
            } else {
                header("Location: landing.php");
            }
            exit;
        }else{
            $mensaje="Error: El usuario o contraseña son incorrectos";
        }
    }
?>
<!doctype html>
<html lang="es">
    <head>
        <title>Identificación de usuario</title>
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
        </header>
        <main class="container">
            <div class="row">
                <div class="col-md-4"></div>
                    <div class="col-md-4">
                        <br><br><br><br><br>
                        <div class="card">
                            <div class="card-header" style="text-align: center;"><b>Iniciar Sesión</b></div>
                            <div class="card-body">
                                <!-- Agregar una alerta para mostrar el Error -->
                                <?php if(isset($mensaje)){ ?>
                                    <div class="alert alert-danger" role="alert">
                                        <strong><?php echo $mensaje ?></strong>
                                    </div>
                                <?php } ?>
                                <form action="" method="post">
                                    <div class="mb-3">
                                        <input type="text" class="form-control" name="usuario" id="usuario"
                                            aria-describedby="helpId" placeholder="Usuario"/>
                                    </div>
                                    <div class="mb-3">
                                        <input type="password" class="form-control" name="password" id="password"
                                            placeholder="Contrase&ntilde;a"/>
                                    </div>
                                    <button type="submit" class="btn btn-outline-secondary">Ingresar</button>
                                </form>
                            </div>
                            <div class="card-footer text-muted">
                                <div class="mb-3">
                                    <a href="#">Recordar contrase&ntilde;a</a>
                                </div>
                                <div class="mb-3">
                                    <a href="./secciones/usuarios/crear.php">Crear cuenta</a>
                                </div>
                            </div>
                        </div>           
                    </div>
            </div>
        </main>
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
