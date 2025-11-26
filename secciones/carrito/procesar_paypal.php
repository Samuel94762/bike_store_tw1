<?php
// PayPal integration removed. Redirect back to pago page.
include('../../config.php');
header('Location: ' . APP_URL . 'secciones/carrito/pago.php?info=paypal_disabled');
exit;


