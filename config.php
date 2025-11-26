<?php
// Cargar variables de entorno desde .env
$env_file = __DIR__ . '/.env';
if (file_exists($env_file)) {
    $env = parse_ini_file($env_file);
    foreach ($env as $key => $value) {
        if (!defined($key)) {
            define($key, $value);
        }
    }
}

// Valores por defecto si no existen en .env
if (!defined('APP_URL')) define('APP_URL', 'http://localhost/bike_store_tw1/');
if (!defined('APP_NAME')) define('APP_NAME', 'Bike Store');

// Configuración de base de datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'bike_store');
define('DB_USER', 'root');
define('DB_PASS', '');

// Intentar conectar a la base de datos
try {
    $conexion = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME,
        DB_USER,
        DB_PASS,
        array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4")
    );
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die('Error de conexión: ' . $e->getMessage());
}

// Configuración de sesión
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Configuración de zona horaria
date_default_timezone_set('America/Caracas');

// Función para obtener la URL base
function get_url_base() {
    return APP_URL;
}

// Función para verificar si el usuario está logueado
function is_logged_in() {
    return isset($_SESSION['usuario']) && isset($_SESSION['logueado']) && $_SESSION['logueado'] === true;
}

// Función para verificar si es admin
function is_admin() {
    return is_logged_in() && isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

// Función para obtener el ID del usuario actual
function get_user_id() {
    return isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
}

// Función para obtener el rol del usuario
function get_user_role() {
    return isset($_SESSION['role']) ? $_SESSION['role'] : null;
}
?>
