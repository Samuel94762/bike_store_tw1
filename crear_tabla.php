<?php
include("config.php");

// Crear tabla de transacciones de PayPal
try {
    $sql = "CREATE TABLE IF NOT EXISTS payment_transactions (
      transaction_id INT AUTO_INCREMENT PRIMARY KEY,
      order_id INT NOT NULL,
      paypal_transaction_id VARCHAR(255) NOT NULL UNIQUE,
      amount DECIMAL(10,2) NOT NULL,
      status VARCHAR(50) NOT NULL DEFAULT 'pending',
      method VARCHAR(50) NOT NULL,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      CONSTRAINT fk_order_transaction FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
    ) ENGINE=InnoDB";
    
    $conexion->exec($sql);
    echo "✓ Tabla payment_transactions creada<br>";
} catch (Exception $e) {
    echo "✗ Error en payment_transactions: " . $e->getMessage() . "<br>";
}

// Crear tabla de facturas
try {
    $sql = "CREATE TABLE IF NOT EXISTS facturas (
      factura_id INT AUTO_INCREMENT PRIMARY KEY,
      order_id INT NOT NULL,
      numero_factura VARCHAR(50) UNIQUE NOT NULL,
      pdf_path VARCHAR(255),
      fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
      enviada_email BOOLEAN DEFAULT FALSE,
      CONSTRAINT fk_order_factura FOREIGN KEY (order_id) REFERENCES orders(order_id)
        ON DELETE CASCADE
        ON UPDATE CASCADE
    ) ENGINE=InnoDB";
    
    $conexion->exec($sql);
    echo "✓ Tabla facturas creada<br>";
} catch (Exception $e) {
    echo "✗ Error en facturas: " . $e->getMessage() . "<br>";
}

// Agregar columnas a orders si no existen
try {
    // Columna comentarios
    $conexion->exec("ALTER TABLE orders ADD COLUMN comentarios TEXT");
    echo "✓ Columna comentarios agregada<br>";
} catch (Exception $e) {
    // La columna probablemente ya existe
}

try {
    // Columna factura_enviada
    $conexion->exec("ALTER TABLE orders ADD COLUMN factura_enviada BOOLEAN DEFAULT FALSE");
    echo "✓ Columna factura_enviada agregada<br>";
} catch (Exception $e) {
    // La columna probablemente ya existe
}

echo "<br><strong>¡Migraciones completadas!</strong>";
?>
