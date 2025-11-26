<?php
// PayPal return disabled. Redirect to pago.php
include('../../config.php');
header('Location: ' . APP_URL . 'secciones/carrito/pago.php?info=paypal_disabled');
exit;
