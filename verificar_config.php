<?php
include("config.php");

echo "<h1>🔍 VERIFICACIÓN DE CONFIGURACIÓN - BIKE STORE</h1>";
echo "<hr>";

// 1. Verificar config.php
echo "<h3>1️⃣ Configuración Base</h3>";
if (defined('APP_URL')) {
    echo "✅ APP_URL: " . APP_URL . "<br>";
} else {
    echo "❌ APP_URL no definida<br>";
}

if (defined('APP_NAME')) {
    echo "✅ APP_NAME: " . APP_NAME . "<br>";
} else {
    echo "❌ APP_NAME no definida<br>";
}

// 2. Verificar Base de Datos
echo "<h3>2️⃣ Base de Datos</h3>";
try {
    $result = $conexion->query("SELECT VERSION() as version");
    $row = $result->fetch(PDO::FETCH_ASSOC);
    echo "✅ Conexión BD OK - MySQL " . $row['version'] . "<br>";
    
    // Verificar tablas
    $tables = ['users', 'products', 'categories', 'orders', 'customers', 'stocks'];
    foreach ($tables as $table) {
        $result = $conexion->query("SHOW TABLES LIKE '$table'");
        if ($result->rowCount() > 0) {
            echo "✅ Tabla `$table` existe<br>";
        } else {
            echo "⚠️ Tabla `$table` NO EXISTE<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ Error BD: " . $e->getMessage() . "<br>";
}

// 3. Verificar SMTP
echo "<h3>3️⃣ Configuración SMTP (Email)</h3>";
if (defined('SMTP_HOST')) {
    echo "✅ SMTP_HOST: " . SMTP_HOST . "<br>";
} else {
    echo "❌ SMTP_HOST no definida<br>";
}

if (defined('SMTP_USER')) {
    echo "✅ SMTP_USER: " . SMTP_USER . "<br>";
} else {
    echo "❌ SMTP_USER no definida<br>";
}

if (defined('SMTP_PORT')) {
    echo "✅ SMTP_PORT: " . SMTP_PORT . "<br>";
} else {
    echo "❌ SMTP_PORT no definida<br>";
}

// 4. Verificar PayPal
echo "<h3>4️⃣ Configuración PayPal</h3>";
if (defined('PAYPAL_MODE')) {
    echo "✅ PAYPAL_MODE: " . PAYPAL_MODE . "<br>";
} else {
    echo "❌ PAYPAL_MODE no definida<br>";
}

if (defined('PAYPAL_CLIENT_ID')) {
    echo "✅ PAYPAL_CLIENT_ID: " . substr(PAYPAL_CLIENT_ID, 0, 10) . "... (truncado)<br>";
} else {
    echo "❌ PAYPAL_CLIENT_ID no definida<br>";
}

// 5. Verificar PHPMailer
echo "<h3>5️⃣ PHPMailer</h3>";
if (file_exists(__DIR__ . '/libs/phpmailer/src/PHPMailer.php')) {
    echo "✅ PHPMailer encontrado en libs/phpmailer/src/<br>";
} else {
    echo "❌ PHPMailer NO ENCONTRADO. Descárgalo de:<br>";
    echo "   https://github.com/PHPMailer/PHPMailer/releases<br>";
}

// 6. Verificar DOMPDF
echo "<h3>6️⃣ DOMPDF (PDF)</h3>";
if (file_exists(__DIR__ . '/libs/dompdf/autoload.inc.php')) {
    echo "✅ DOMPDF encontrado<br>";
} else {
    echo "❌ DOMPDF NO ENCONTRADO<br>";
}

// 7. Verificar carpetas
echo "<h3>7️⃣ Carpetas Necesarias</h3>";
$dirs = [
    'secciones/carrito/facturas',
    'secciones/productos/imagen',
    'secciones/clientes/imagen'
];

foreach ($dirs as $dir) {
    $path = __DIR__ . '/' . $dir;
    if (is_dir($path)) {
        echo "✅ Carpeta `$dir` existe<br>";
    } else {
        echo "⚠️ Carpeta `$dir` NO existe (se creará automáticamente)<br>";
    }
}

// 8. Verificar archivos críticos
echo "<h3>8️⃣ Archivos Críticos</h3>";
$files = [
    'landing.php',
    'secciones/carrito/index.php',
    'secciones/carrito/agregar.php',
    'secciones/carrito/pago.php',
    'secciones/carrito/factura.php',
    '.env'
];

foreach ($files as $file) {
    $path = __DIR__ . '/' . $file;
    if (file_exists($path)) {
        $size = filesize($path);
        echo "✅ `$file` (" . number_format($size) . " bytes)<br>";
    } else {
        echo "❌ `$file` NO ENCONTRADO<br>";
    }
}

// 9. Verificar extensiones PHP
echo "<h3>9️⃣ Extensiones PHP</h3>";
$extensions = ['curl', 'mbstring', 'pdo_mysql', 'json'];
foreach ($extensions as $ext) {
    if (extension_loaded($ext)) {
        echo "✅ Extensión `$ext` habilitada<br>";
    } else {
        echo "❌ Extensión `$ext` NO HABILITADA<br>";
    }
}

// 10. Test de sesión
echo "<h3>🔟 Sesión PHP</h3>";
session_start();
if (isset($_SESSION)) {
    echo "✅ Sesiones habilitadas<br>";
    $_SESSION['test'] = true;
    if (isset($_SESSION['test'])) {
        echo "✅ Variables de sesión funcionan<br>";
    }
} else {
    echo "❌ Sesiones NO habilitadas<br>";
}

// Resumen
echo "<hr>";
echo "<h2>📋 RESUMEN</h2>";
echo "<p>Si ves principalmente ✅, tu configuración está lista.</p>";
echo "<p>Si ves ❌ o ⚠️, sigue los pasos en INICIO_RAPIDO.md</p>";

echo "<br><br>";
echo "<a href='landing.php' class='btn btn-primary'>→ Ir a Landing Page</a>";
echo "&nbsp;&nbsp;";
echo "<a href='login.php' class='btn btn-secondary'>→ Ir a Login</a>";
?>

<style>
    body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
    h1 { color: #06356b; }
    h3 { color: #333; margin-top: 20px; }
    hr { border: none; border-top: 2px solid #06356b; }
    .btn { padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; }
    .btn-primary { background: #06356b; color: white; }
    .btn-secondary { background: #666; color: white; }
    .btn:hover { opacity: 0.8; }
</style>
