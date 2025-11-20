<?php 
    // Tercer paso
    session_start();
    $url_base="http://localhost/bike_store_tw1/";
    //Si no existe la session de usuario, redirigimos a la url_base para iniciar session
    if(!isset($_SESSION["usuario"])){
        header("Location:".$url_base."login.php");
    }
?>

<!doctype html>
<html lang="es">
    <head>
        <title>App Bike Store</title>
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
        <!-- Add Bootstrap Icons -->
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
        <!-- Jquery min v3.7.1 -->
        <script src="https://code.jquery.com/jquery-3.7.1.min.js" 
            integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" 
            crossorigin="anonymous">
        </script>
        <!-- DataTables CSS v2.3.4 -->
        <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.dataTables.css" />
        <script src="https://cdn.datatables.net/2.3.4/js/dataTables.js"></script>
        <!-- Sweeralert2 v11 -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    </head>

    <body>
        <header>
            <!-- place navbar here -->
        <nav class="navbar navbar-expand navbar-dark" style="background-color: #06356bff;">
            <ul class="nav navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" href="<?php echo $url_base;?>" aria-current="page"
                        >Bike Store <span class="visually-hidden">(current)</span></a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/categorias/">Categorias</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/clientes/">Clientes</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/empleados/">Empleados</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/pedidos/">Pedidos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/productos/">Productos</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/tiendas/">Tiendas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>secciones/usuarios/">Usuarios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo $url_base;?>cerrar.php">Cerrar Sesion</a>
                </li>
            </ul>
        </nav>
        <br>
        </header>
        <main class="container">
        <!-- Si hubo envio de mensaje entonces visualizams el mensaje -->
        <?php if(isset($_GET['mensaje'])) { ?>
        <script>
            Swal.fire({icon:"success", title:"<?php echo $_GET['mensaje']; ?>"});
        </script>
        <?php } ?>
