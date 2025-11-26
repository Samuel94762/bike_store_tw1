<?php
// Script de prueba para enviar un correo usando PHPMailer y las constantes definidas en config.php
require_once __DIR__ . '/../../config.php';
// Cargar autoload de Composer
$vendor = __DIR__ . '/../../vendor/autoload.php';
if (!is_file($vendor)) {
    echo "ERROR: vendor/autoload.php no encontrado\n";
    exit(1);
}
require_once $vendor;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

try {
    $mail = new PHPMailer(true);
    $mail->isSMTP();
    $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'localhost';
    $mail->SMTPAuth = true;
    $mail->Username = defined('SMTP_USER') ? SMTP_USER : '';
    $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : '';
    // Elegir cifrado
    if (defined('SMTP_ENCRYPTION') && SMTP_ENCRYPTION !== '') {
        $mail->SMTPSecure = SMTP_ENCRYPTION;
    } elseif (defined('SMTP_ENCRYPTION_R') && SMTP_ENCRYPTION_R !== '') {
        $mail->SMTPSecure = SMTP_ENCRYPTION_R;
    }
    $mail->Port = defined('SMTP_PORT') && SMTP_PORT !== '' ? (int)SMTP_PORT : (defined('SMTP_PORT_R') ? (int)SMTP_PORT_R : 587);

    $fromEmail = defined('SMTP_FROM_EMAIL') ? SMTP_FROM_EMAIL : (defined('SMTP_USER') ? SMTP_USER : 'no-reply@localhost');
    $fromName = defined('SMTP_FROM_NAME') ? trim(SMTP_FROM_NAME, '"') : (defined('APP_NAME') ? APP_NAME : 'Bike Store');

    $mail->setFrom($fromEmail, $fromName);

    // Enviar al usuario SMTP_USER (puedes cambiar este destinatario a cualquier otro correo para pruebas)
    $to = defined('SMTP_USER') ? SMTP_USER : $fromEmail;
    $mail->addAddress($to);

    $mail->Subject = 'Prueba PHPMailer - Bike Store';
    $mail->Body = 'Este es un correo de prueba enviado por PHPMailer desde el proyecto Bike Store.';
    $mail->isHTML(false);

    $mail->send();
    echo "MAIL_SENT\n";
    exit(0);
} catch (Exception $e) {
    echo "MAIL_ERROR: " . $e->getMessage() . "\n";
    exit(2);
}
