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

// Calcular el total sumando todos los subtotales
$total = 0;
foreach($lista_pedidos_detalle as $registro) {
    $total += $registro['subtotal'];
}

// Función para generar QR
function generarQRFactura($pedido, $items, $totalGeneral) {
    // Crear lista de productos para el QR
    $productosQR = "";
    $contador = 1;
    foreach($items as $item) {
        $productosQR .= "{$contador}. {$item['product_name']} - {$item['quantity']} x Bs " . number_format($item['price'], 2) . "\n";
        $contador++;
    }
    
    $qrData = "FACTURA BEST BIKES EVER\n" .
              "========================\n" .
              "N° FACTURA: 4561\n" .
              "NIT: 3139568974\n" .
              "AUTORIZACIÓN: 1234567890123456789CUH9999\n" .
              "========================\n" .
              "CLIENTE: {$pedido['cliente']}\n" .
              "NIT/CI: 79456123\n" .
              "COD CLIENTE: {$pedido['customer_id']}\n" .
              "FECHA: {$pedido['order_date']}\n" .
              "========================\n" .
              "PRODUCTOS:\n" . $productosQR .
              "========================\n" .
              "TOTAL: Bs " . number_format($totalGeneral, 2) . "\n" .
              "========================\n" .
              "CASA MATRIZ - SANTA CRUZ\n" .
              "Tel: 68689162\n" .
              "Gracias por su compra!";
    
    // Codificar datos para URL
    $datosCodificados = urlencode($qrData);
    
    // Usar API gratuita de QR Code
    $urlAPI = "https://api.qrserver.com/v1/create-qr-code/?size=150x150&data=" . $datosCodificados;
    
    return $urlAPI;
}

// GENERAR CÓDIGO QR
$qrURL = generarQRFactura($cliente, $lista_pedidos_detalle, $total);

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
        <style>
            .footer-text {
                text-align: center;
                font-size: 10px;
                margin-top: 20px;
                padding: 10px;
                border-top: 1px solid #000;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #ddd;
                padding: 8px;
                text-align: left;
            }
            th {
                background-color: #f2f2f2;
            }
            .total-row {
                font-weight: bold;
                background-color: #f8f9fa;
            }
            .qr-container {
                text-align: center;
                margin: 20px 0;
                padding: 15px;
                border: 1px solid #ddd;
                background-color: #f9f9f9;
                border-radius: 5px;
            }
            .qr-title {
                font-size: 14px;
                font-weight: bold;
                margin-bottom: 10px;
                color: #333;
            }
            .qr-subtitle {
                font-size: 10px;
                color: #666;
                margin-top: 5px;
            }
        </style>
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
                <p>Fecha Emision :  <?php echo $cliente["order_date"]?></p>
            </div>
            <hr>
            
            <!-- Sección del código QR -->
            <div class="qr-container">
                <div class="qr-title">CÓDIGO QR - FACTURA ELECTRÓNICA</div>
                <img src="<?php echo $qrURL; ?>" alt="Código QR Factura" style="width: 150px; height: 150px;">
                <div class="qr-subtitle">
                    Escanee para ver los detalles de la factura
                </div>
            </div>
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
                            <td><?php echo number_format($registro['price'], 2); ?></td>
                            <td><?php echo $registro['discount']; ?></td>
                            <td><?php echo number_format($registro['subtotal'], 2); ?></td>
                        </tr>
                        <?php $item++; } ?>
                        <!-- Fila del total -->
                        <tr class="total-row">
                            <td colspan="5" style="text-align: right;"><strong>TOTAL:</strong></td>
                            <td><strong><?php echo number_format($total, 2); ?></strong></td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </main>
        <footer>
            <div class="footer-text">
                <p>Esta factura contribuye al desarrollo del país, el uso ilícito de esta será sancionado de acuerdo a la ley</p>
            </div>
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