<?php
if (!isset($conexion)) {
    include("../../bd.php");
}
//Envio de parametros en la URL o en el metodo GET
if (isset($_GET['txtID'])) {
    $txtID = $_GET['txtID'];
} else {
    $txtID = null;
}

// Consulta parametrizada para traer los pedidos y mostrarlos como unico registro
$sentencia = $conexion->prepare("SELECT CONCAT(c.first_name,' ',c.last_name) as cliente,c.customer_id,
o.order_date,u.usuario,o.total_amount,p.product_name,oi.quantity,oi.price,oi.discount,
((oi.quantity * oi.price) - ((oi.quantity * oi.price) * oi.discount * 100 /100)) as subtotal
FROM orders o
INNER JOIN order_items oi ON o.order_id = oi.order_id
INNER JOIN customers c ON o.customer_id = c.customer_id
INNER JOIN usuarios u ON o.user_id = u.user_id
INNER JOIN products p ON oi.product_id = p.product_id
WHERE o.order_id = :txtID");
$sentencia->bindValue(':txtID', $txtID, PDO::PARAM_INT);
$sentencia->execute();
$lista_pedidos_detalle = $sentencia->fetchAll(PDO::FETCH_ASSOC);

if (!$lista_pedidos_detalle || count($lista_pedidos_detalle) === 0) {
    echo '<p>Error: no se encontró la orden o no hay detalles para el pedido.</p>';
    exit;
}

$cliente = $lista_pedidos_detalle[0];

//marcamos el HTML
ob_start();
?>
<!doctype html>
<html lang="es">
    <head>
        <title>Factura Pedido</title>
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
            <div style="text-align:center;">
                <h2>Factura</h2>
                <p>(CON DERECHO A CREDITO FISCAL)</p>
                <h4>Best Bikes Ever</h4>
                <p>CASA MATRIZ</p>
                <p>No punto de venta 0</p>
                <p>ZONA NORTE OCTAVO ANILLO ENTRE AVENIDA CRISTO REDENTOR Y AVENIDA RADIAL 26 Nro S/N</p>
                <p>Tel&eacute;fono: 68689162</p>
                <p>SANTA CRUZ</p>
            </div>
            <hr>
            <div>
                <p>NIT :                 3139568974</p>
                <P>N° FACTURA :          4561</P>
                <p>CODIGO AUTORIZACION : 1234567890123456789CUH9999</p>
            </div>
            <hr>
            <div>
                <p>Razon Social :   <?php echo $cliente["cliente"]?></p>
                <p>NIT/CI/CEX :     79456123</p>
                <P>Codigo Cliente : <?php echo $cliente["customer_id"]?></P>
                <p>Fecha Emision :  fechaPedido</p>
            </div>
            <hr>
        </header>
        <main>
            <div>
                <h4 style="text-align: center;"><b>DETALLE</b></h4>
                <table>
                    <thead>
                        <tr>
                            <th scope="col">Item</th>
                            <th scope="col">Producto</th>
                            <th scope="col">Cantidad</th>
                            <th scope="col">Precio</th>
                            <th scope="col">Descuento</th>
                            <th scope="col">Sub Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $item = 1; foreach($lista_pedidos_detalle as $registro) { ?>
                        <tr class="">
                            <td scope="row"><?php echo $item; ?></td>
                            <td><?php echo $registro['product_name']; ?></td>
                            <td><?php echo $registro['quantity']; ?></td>
                            <td><?php echo $registro['price']; ?></td>
                            <td><?php echo $registro['discount']; ?></td>
                            <td><?php echo $registro['subtotal']; ?></td>
                        </tr>
                        <?php $item++; } ?>
                    </tbody>
                </table>
            </div>
        </main>
        <footer>
            <!-- place footer here -->
        </footer>
    </body>
</html>
<?php 
    //Hasta aqui para convertir en PDF
    $HTML=ob_get_clean();
    require_once("../../libs/dompdf/autoload.inc.php");
    use Dompdf\Dompdf;
    $dompdf = new Dompdf();
    $opciones = $dompdf->getOptions();
    $opciones->set(array("isRemoteEnabled"=>true));
    $dompdf->setOptions($opciones);

    $dompdf->loadHTML($HTML);
    $dompdf->setPaper('letter');
    $dompdf->render();
    // Si se define CAPTURE_PDF_OUTPUT, devolver el PDF como salida (sin forzar exit ni cabeceras),
    // de lo contrario hacer el stream normal para descarga/visualización.
    if (defined('CAPTURE_PDF_OUTPUT') && CAPTURE_PDF_OUTPUT) {
        echo $dompdf->output();
    } else {
        $dompdf->stream("Factura.pdf", array("Attachment"=>false));
    }
?>