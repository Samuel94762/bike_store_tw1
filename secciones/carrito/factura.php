<?php
include("../../config.php");

// Función para enviar factura por email
function enviar_factura_email($order_id, $conexion) {
    // Obtener datos de la orden
    $sentencia = $conexion->prepare("SELECT o.*, c.email, c.first_name, c.last_name 
                                    FROM orders o
                                    LEFT JOIN customers c ON o.customer_id = c.customer_id
                                    WHERE o.order_id = :order_id");
    $sentencia->bindParam(':order_id', $order_id);
    $sentencia->execute();
    $orden = $sentencia->fetch(PDO::FETCH_ASSOC);
    
    if (!$orden) {
        return false;
    }
    
    // Obtener items
    $sentencia = $conexion->prepare("SELECT oi.*, p.product_name FROM order_items oi
                                    LEFT JOIN products p ON oi.product_id = p.product_id
                                    WHERE oi.order_id = :order_id");
    $sentencia->bindParam(':order_id', $order_id);
    $sentencia->execute();
    $items = $sentencia->fetchAll(PDO::FETCH_ASSOC);
    
    // Generar HTML de la factura
    $html = generar_html_factura($orden, $items);
    
    // Guardar factura como PDF usando DOMPDF
    require_once(__DIR__ . '/../../libs/dompdf/autoload.inc.php');
    
    use Dompdf\Dompdf;
    
    $dompdf = new Dompdf();
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A4', 'portrait');
    $dompdf->render();
    
    // Guardar PDF
    $pdf_filename = 'FACTURA-' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '-' . time() . '.pdf';
    $pdf_path = __DIR__ . '/facturas/' . $pdf_filename;
    
    // Crear directorio si no existe
    if (!is_dir(__DIR__ . '/facturas')) {
        mkdir(__DIR__ . '/facturas', 0755, true);
    }
    
    file_put_contents($pdf_path, $dompdf->output());
    
    // Enviar por email usando PHPMailer
    require_once(__DIR__ . '/../../libs/phpmailer/src/Exception.php');
    require_once(__DIR__ . '/../../libs/phpmailer/src/PHPMailer.php');
    require_once(__DIR__ . '/../../libs/phpmailer/src/SMTP.php');
    
    use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;
    
    $mail = new PHPMailer(true);
    
    try {
        // Configurar SMTP
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USER;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port = SMTP_PORT;
        
        // Configurar remitente y destinatario
        $mail->setFrom(SMTP_FROM_EMAIL, SMTP_FROM_NAME);
        $mail->addAddress($orden['email'], $orden['first_name'] . ' ' . $orden['last_name']);
        
        // Asunto y contenido
        $mail->isHTML(true);
        $mail->Subject = 'Factura de tu compra en ' . APP_NAME . ' - #' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        $mail->Body = generar_email_body($orden);
        $mail->AltBody = 'Tu factura está adjunta.';
        
        // Adjuntar PDF
        $mail->addAttachment($pdf_path, 'FACTURA-' . str_pad($order_id, 6, '0', STR_PAD_LEFT) . '.pdf');
        
        // Enviar
        $mail->send();
        
        // Guardar referencia en la base de datos
        $numero_factura = 'FACT-' . date('Ymd') . '-' . str_pad($order_id, 6, '0', STR_PAD_LEFT);
        
        $sentencia = $conexion->prepare("INSERT INTO facturas (order_id, numero_factura, pdf_path, enviada_email) 
                                        VALUES (:order_id, :numero_factura, :pdf_path, TRUE)");
        $sentencia->bindParam(':order_id', $order_id);
        $sentencia->bindParam(':numero_factura', $numero_factura);
        $sentencia->bindParam(':pdf_path', $pdf_filename);
        $sentencia->execute();
        
        // Actualizar orden
        $sentencia = $conexion->prepare("UPDATE orders SET factura_enviada = TRUE WHERE order_id = :order_id");
        $sentencia->bindParam(':order_id', $order_id);
        $sentencia->execute();
        
        return true;
    } catch (Exception $e) {
        error_log('Error al enviar email: ' . $mail->ErrorInfo);
        return false;
    }
}

// Generar HTML de la factura
function generar_html_factura($orden, $items) {
    $total_items = 0;
    $total_price = 0;
    
    foreach ($items as $item) {
        $total_items += $item['quantity'];
        $total_price += $item['price'] * $item['quantity'];
    }
    
    $total = $total_price
    
    $html = '
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <style>
            * { margin: 0; padding: 0; }
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 800px; margin: 0 auto; padding: 20px; }
            .header { border-bottom: 3px solid #06356b; padding-bottom: 20px; margin-bottom: 20px; }
            .title { font-size: 28px; color: #06356b; font-weight: bold; }
            .subtitle { color: #666; margin-top: 5px; }
            .invoice-number { font-size: 18px; color: #06356b; font-weight: bold; margin-top: 10px; }
            .info-row { display: flex; justify-content: space-between; margin-bottom: 20px; }
            .info-section { flex: 1; }
            .info-section h3 { color: #06356b; font-size: 12px; margin-bottom: 5px; text-transform: uppercase; }
            .info-section p { font-size: 12px; line-height: 1.6; }
            .items-table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
            .items-table th { background-color: #06356b; color: white; padding: 10px; text-align: left; font-size: 12px; }
            .items-table td { padding: 10px; border-bottom: 1px solid #ddd; font-size: 12px; }
            .items-table tr:nth-child(even) { background-color: #f9f9f9; }
            .totals { float: right; width: 40%; margin-bottom: 20px; }
            .total-row { display: flex; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid #ddd; font-size: 12px; }
            .total-row.final { border-bottom: 2px solid #06356b; font-weight: bold; font-size: 14px; color: #06356b; }
            .footer { border-top: 2px solid #06356b; padding-top: 15px; text-align: center; font-size: 11px; color: #666; margin-top: 40px; }
            .clear { clear: both; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="title">📦 ' . APP_NAME . '</div>
                <div class="subtitle">Factura de Compra</div>
                <div class="invoice-number">Factura #' . str_pad($orden['order_id'], 6, '0', STR_PAD_LEFT) . '</div>
            </div>
            
            <div class="info-row">
                <div class="info-section">
                    <h3>Cliente</h3>
                    <p>' . htmlspecialchars($orden['first_name'] . ' ' . $orden['last_name']) . '</p>
                    <p>' . htmlspecialchars($orden['email']) . '</p>
                </div>
                <div class="info-section">
                    <h3>Detalles de la Factura</h3>
                    <p><strong>Fecha:</strong> ' . date('d/m/Y', strtotime($orden['order_date'])) . '</p>
                    <p><strong>Estado:</strong> ' . $orden['estado'] . '</p>
                </div>
            </div>
            
            <table class="items-table">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Cantidad</th>
                        <th>Precio Unitario</th>
                        <th>Total</th>
                    </tr>
                </thead>
                <tbody>';
    
    foreach ($items as $item) {
        $item_total = $item['price'] * $item['quantity'];
        $html .= '
                    <tr>
                        <td>' . htmlspecialchars($item['product_name']) . '</td>
                        <td>' . $item['quantity'] . '</td>
                        <td>$' . number_format($item['price'], 2) . '</td>
                        <td>$' . number_format($item_total, 2) . '</td>
                    </tr>';
    }
    
    $html .= '
                </tbody>
            </table>
            
            <div class="totals">
                <div class="total-row">
                    <span>Subtotal:</span>
                    <span>$' . number_format($total_price, 2) . '</span>
                </div>
                <div class="total-row">
                    <span>Impuesto (16%):</span>
                    <span>$' . number_format($tax, 2) . '</span>
                </div>
                <div class="total-row final">
                    <span>Total:</span>
                    <span>$' . number_format($total, 2) . '</span>
                </div>
            </div>
            
            <div class="clear"></div>
            
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. Todos los derechos reservados.</p>
                <p>Gracias por tu compra. Si tienes preguntas, contáctanos.</p>
            </div>
        </div>
    </body>
    </html>';
    
    return $html;
}

// Generar cuerpo del email
function generar_email_body($orden) {
    return '
    <html>
    <head>
        <style>
            body { font-family: Arial, sans-serif; color: #333; }
            .container { max-width: 600px; margin: 0 auto; }
            .header { background-color: #06356b; color: white; padding: 20px; text-align: center; }
            .content { padding: 20px; }
            .footer { background-color: #f9f9f9; padding: 15px; text-align: center; font-size: 12px; color: #666; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <h2>¡Compra completada!</h2>
            </div>
            <div class="content">
                <p>Hola ' . htmlspecialchars($orden['first_name']) . ',</p>
                <p>Tu pago ha sido procesado exitosamente. Tu factura está adjunta a este email.</p>
                <p><strong>Número de Orden:</strong> #' . str_pad($orden['order_id'], 6, '0', STR_PAD_LEFT) . '</p>
                <p><strong>Monto Total:</strong> $' . number_format($orden['total_amount'], 2) . '</p>
                <p>Si tienes alguna pregunta, no dudes en contactarnos.</p>
                <p>Saludos,<br>' . APP_NAME . '</p>
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' ' . APP_NAME . '. Todos los derechos reservados.</p>
            </div>
        </div>
    </body>
    </html>';
}
?>
