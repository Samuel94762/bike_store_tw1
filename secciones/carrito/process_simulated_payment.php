<?php
include("../../config.php");
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['success' => false, 'message' => 'Usuario no autenticado']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$method = isset($input['method']) ? $input['method'] : 'unknown';

$pending_order_id = isset($_SESSION['pending_order_id']) ? $_SESSION['pending_order_id'] : null;
$pending_total = isset($_SESSION['pending_order_total']) ? $_SESSION['pending_order_total'] : 0;
$carrito_items = isset($_SESSION['carrito']) ? $_SESSION['carrito'] : [];

try {
    // Si no existe orden pendiente, crear una ahora (para compatibilidad)
    if (!$pending_order_id) {
        // Buscar o crear cliente
        $sentencia = $conexion->prepare("SELECT c.* FROM customers c
            INNER JOIN orders o ON c.customer_id = o.customer_id
            WHERE o.user_id = :user_id
            LIMIT 1");
        $sentencia->bindValue(':user_id', $_SESSION['user_id']);
        $sentencia->execute();
        $cliente = $sentencia->fetch(PDO::FETCH_ASSOC);
        if (!$cliente) {
            $email = $_SESSION['email'];
            $sentencia = $conexion->prepare("INSERT INTO customers (first_name, last_name, email) VALUES (:first_name, :last_name, :email)");
            $sentencia->bindValue(':first_name', $_SESSION['usuario']);
            $sentencia->bindValue(':last_name', '');
            $sentencia->bindValue(':email', $email);
            $sentencia->execute();
            $customer_id = $conexion->lastInsertId();
        } else {
            $customer_id = $cliente['customer_id'];
        }

        // CALCULAR EL TOTAL CORRECTAMENTE
        $total_amount = 0;
        foreach ($carrito_items as $item) {
            $total_amount += $item['price'] * $item['quantity'];
        }

        $order_date = date('Y-m-d');
        $sentencia = $conexion->prepare("INSERT INTO orders (customer_id, order_date, user_id, estado, total_amount) VALUES (:customer_id, :order_date, :user_id, 'Pendiente', :total_amount)");
        $sentencia->bindValue(':customer_id', $customer_id);
        $sentencia->bindValue(':order_date', $order_date);
        $sentencia->bindValue(':user_id', $_SESSION['user_id']);
        // CORREGIDO: Usar el total_amount calculado en lugar de $pending_total
        $sentencia->bindValue(':total_amount', $total_amount);
        $sentencia->execute();
        $pending_order_id = $conexion->lastInsertId();

        // Agregar items
        foreach ($carrito_items as $item) {
            $sentencia = $conexion->prepare("INSERT INTO order_items (order_id, product_id, quantity, price, discount) VALUES (:order_id, :product_id, :quantity, :price, 0)");
            $sentencia->bindValue(':order_id', $pending_order_id);
            $sentencia->bindValue(':product_id', $item['product_id']);
            $sentencia->bindValue(':quantity', $item['quantity']);
            $sentencia->bindValue(':price', $item['price']);
            $sentencia->execute();
        }
    }

    // Marcar orden como pagada
    $stmt = $conexion->prepare("UPDATE orders SET estado = 'Pagado' WHERE order_id = ?");
    $stmt->bindValue(1, $pending_order_id, PDO::PARAM_INT);
    $stmt->execute();

    // Registrar transacción (usar columnas reales de la tabla)
    $stmt = $conexion->prepare("INSERT INTO payment_transactions (order_id, paypal_transaction_id, amount, status, method, created_at) VALUES (?, ?, ?, 'COMPLETED', ?, NOW())");
    $stmt->bindValue(1, $pending_order_id, PDO::PARAM_INT);
    $stmt->bindValue(2, 'SIM-' . time(), PDO::PARAM_STR);
    $stmt->bindValue(3, $pending_total, PDO::PARAM_STR);
    $stmt->bindValue(4, $method, PDO::PARAM_STR);
    $stmt->execute();

    // ===== Generar factura PDF (usando la plantilla `secciones/pedidos/factura.php`) y enviar por correo =====
    try {
        // Preparar para que la plantilla `factura.php` genere la factura del pedido
        $_GET['txtID'] = $pending_order_id;

        // Indicar a la plantilla que queremos capturar el PDF en lugar de hacer stream
        if (!defined('CAPTURE_PDF_OUTPUT')) define('CAPTURE_PDF_OUTPUT', true);

        // Buffer para capturar el PDF que genera la plantilla
        ob_start();
        $factura_path = __DIR__ . '/../pedidos/factura.php';
        if (is_file($factura_path)) {
            include $factura_path; // factura.php generará el PDF y lo imprimirá al buffer
            $pdfContent = ob_get_clean();
            // Quitar posibles headers de PDF que la plantilla haya enviado
            if (function_exists('header_remove')) {
                @header_remove('Content-Type');
                @header_remove('Content-Disposition');
            }

            // Enviar por correo usando PHPMailer si está disponible
            $vendor = __DIR__ . '/../../vendor/autoload.php';
            $toEmail = null;
            // intentar obtener email del cliente desde la BD
            try {
                $qq = $conexion->prepare("SELECT c.email FROM orders o JOIN customers c ON o.customer_id = c.customer_id WHERE o.order_id = :oid");
                $qq->bindValue(':oid', $pending_order_id, PDO::PARAM_INT);
                $qq->execute();
                $row = $qq->fetch(PDO::FETCH_ASSOC);
                if ($row && !empty($row['email'])) $toEmail = $row['email'];
            } catch (Exception $ee) {
                error_log('No se pudo obtener email del cliente: ' . $ee->getMessage());
            }

            if ($toEmail && is_file($vendor)) {
                require_once $vendor;
                try {
                    $mail = new PHPMailer\PHPMailer\PHPMailer(true);
                    $mail->isSMTP();
                    $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
                    $mail->SMTPAuth = true;
                    $mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
                    $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
                    $mail->SMTPSecure = defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION !== '' ? SMTP_ENCRYPTION : (defined('SMTP_ENCRYPTION_R') ? SMTP_ENCRYPTION_R : 'tls');
                    $mail->Port = defined('SMTP_PORT') && SMTP_PORT !== '' ? (int)SMTP_PORT : (defined('SMTP_PORT_R') ? (int)SMTP_PORT_R : 587);

                    $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (defined('SMTP_USER') ? SMTP_USER : 'no-reply@localhost');
                    $fromName = defined('SMTP_FROM_NAME') ? trim(SMTP_FROM_NAME, '"') : (defined('APP_NAME') ? APP_NAME : 'Bike Store');

                    $mail->setFrom($fromEmail, $fromName);
                    $mail->addAddress($toEmail);
                    $mail->Subject = 'Factura - Orden #' . $pending_order_id;
                    $mail->Body = 'Adjunto encontrará la factura de su compra.';
                    $mail->isHTML(false);
                    $mail->addStringAttachment($pdfContent, 'Factura_' . $pending_order_id . '.pdf', 'base64', 'application/pdf');
                    $mail->send();
                } catch (Exception $e) {
                    error_log('PHPMailer error enviando factura: ' . $e->getMessage());
                }
            } elseif ($toEmail) {
                // Fallback a mail() con attachment
                $subject = 'Factura - Orden #' . $pending_order_id;
                $message = 'Adjunto encontrará la factura de su compra.';
                $separator = md5(time());
                $eol = PHP_EOL;
                $filename = 'Factura_' . $pending_order_id . '.pdf';
                $attachment = chunk_split(base64_encode($pdfContent));

                $headers  = "From: " . (defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : 'no-reply@localhost') . $eol;
                $headers .= "MIME-Version: 1.0" . $eol;
                $headers .= "Content-Type: multipart/mixed; boundary=\"" . $separator . "\"" . $eol . $eol;

                $body = "--" . $separator . $eol;
                $body .= "Content-Type: text/plain; charset=iso-8859-1" . $eol;
                $body .= "Content-Transfer-Encoding: 7bit" . $eol . $eol;
                $body .= $message . $eol . $eol;

                $body .= "--" . $separator . $eol;
                $body .= "Content-Type: application/pdf; name=\"" . $filename . "\"" . $eol;
                $body .= "Content-Transfer-Encoding: base64" . $eol;
                $body .= "Content-Disposition: attachment" . $eol . $eol;
                $body .= $attachment . $eol . $eol;
                $body .= "--" . $separator . "--";

                @mail($toEmail, $subject, $body, $headers);
            }
        } else {
            error_log('Plantilla de factura no encontrada: ' . $factura_path);
            ob_end_clean();
        }
    } catch (Exception $e) {
        error_log('Error generando/enviando factura (plantilla): ' . $e->getMessage());
    }

    // Decrementar stock usando la tabla `stocks` (consume cantidades en registros de almacén)
    foreach ($carrito_items as $item) {
        $product_id = isset($item['product_id']) ? $item['product_id'] : (isset($item['id']) ? $item['id'] : null);
        $quantity_needed = isset($item['quantity']) ? (int)$item['quantity'] : 0;
        if (!$product_id || $quantity_needed <= 0) continue;

        // Seleccionar los registros de stock disponibles para el producto (cantidad > 0), ordenados por stock_id
        $stmtStocks = $conexion->prepare("SELECT stock_id, quantity FROM stocks WHERE product_id = ? AND quantity > 0 ORDER BY stock_id ASC");
        $stmtStocks->bindValue(1, $product_id, PDO::PARAM_INT);
        $stmtStocks->execute();
        $stocks = $stmtStocks->fetchAll(PDO::FETCH_ASSOC);

        $remaining = $quantity_needed;
        foreach ($stocks as $s) {
            if ($remaining <= 0) break;
            $available = (int)$s['quantity'];
            if ($available <= 0) continue;

            $take = min($available, $remaining);
            $newQty = $available - $take;

            $upd = $conexion->prepare("UPDATE stocks SET quantity = ? WHERE stock_id = ?");
            $upd->bindValue(1, $newQty, PDO::PARAM_INT);
            $upd->bindValue(2, $s['stock_id'], PDO::PARAM_INT);
            $upd->execute();

            $remaining -= $take;
        }

        // Si no se pudo satisfacer completamente, dejamos en 0 los stocks disponibles (no más acción).
    }

    // Limpiar sesión
    unset($_SESSION['carrito']);
    unset($_SESSION['pending_order_id']);
    unset($_SESSION['pending_order_total']);

    echo json_encode(['success' => true, 'message' => 'Pago simulado procesado', 'order_id' => $pending_order_id]);
    exit;

} catch (Exception $e) {
    error_log('Error procesando pago simulado: ' . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
    exit;
}
?>