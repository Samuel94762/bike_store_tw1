-- Tabla para transacciones de PayPal
CREATE TABLE IF NOT EXISTS payment_transactions (
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
) ENGINE=InnoDB;

-- Agregar columna de comentarios a órdenes si no existe
ALTER TABLE orders ADD COLUMN IF NOT EXISTS comentarios TEXT;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS factura_enviada BOOLEAN DEFAULT FALSE;

-- Crear tabla para rastreo de facturas
CREATE TABLE IF NOT EXISTS facturas (
  factura_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  numero_factura VARCHAR(50) UNIQUE NOT NULL,
  pdf_path VARCHAR(255),
  fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  enviada_email BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_order_factura FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE
    ON UPDATE CASCADE
) ENGINE=InnoDB;
