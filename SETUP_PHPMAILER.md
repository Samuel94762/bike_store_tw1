# Configuración de PHPMailer para Bike Store

## Instalación de PHPMailer

PHPMailer es una biblioteca para enviar emails desde PHP. Sigue estos pasos:

### Opción 1: Usando Composer (Recomendado)

```bash
cd c:\xampp\htdocs\bike_store_tw1
composer require phpmailer/phpmailer
```

### Opción 2: Descarga Manual

1. Descarga PHPMailer desde: https://github.com/PHPMailer/PHPMailer/releases
2. Descomprime el archivo en la carpeta `libs/`
3. La estructura debe quedar así:
   ```
   libs/
   ├── phpmailer/
   │   ├── src/
   │   │   ├── Exception.php
   │   │   ├── PHPMailer.php
   │   │   └── SMTP.php
   │   └── ...
   ```

## Configuración de Variables de Entorno

Edita el archivo `.env` en la raíz del proyecto con tus credenciales de Gmail:

```env
# SMTP Configuration
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu_email@gmail.com
SMTP_PASSWORD=tu_contraseña_de_aplicación
SMTP_ENCRYPTION=ssl
SMTP_PORT=465
SMTP_FROM_EMAIL=tu_email@gmail.com
SMTP_FROM_NAME="Bike Store"
```

### Obtener la Contraseña de Aplicación de Gmail

1. Ve a https://myaccount.google.com/security
2. Habilita la verificación en dos pasos
3. En "Contraseñas de aplicación", genera una nueva para "Correo"
4. Gmail te proporciona una contraseña de 16 caracteres
5. Usa esa contraseña en `.env`

## Archivos Importantes

- `config.php` - Archivo de configuración principal
- `.env` - Variables de entorno (no subir a Git)
- `secciones/carrito/factura.php` - Funciones para generar y enviar facturas
- `libs/dompdf/` - Librería para generar PDFs

## Tablas de Base de Datos Necesarias

Ejecuta el siguiente SQL en tu base de datos:

```sql
-- Crear tabla de transacciones
CREATE TABLE payment_transactions (
  transaction_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  paypal_transaction_id VARCHAR(255) NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  method VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_transaction FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Crear tabla de facturas
CREATE TABLE facturas (
  factura_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  numero_factura VARCHAR(50) UNIQUE NOT NULL,
  pdf_path VARCHAR(255),
  fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  enviada_email BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_order_factura FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
);

-- Agregar columnas a la tabla orders
ALTER TABLE orders ADD COLUMN IF NOT EXISTS comentarios TEXT;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS factura_enviada BOOLEAN DEFAULT FALSE;
```

## Estructura de Carpetas

La carpeta `secciones/carrito/` contiene:

- `index.php` - Ver carrito
- `agregar.php` - AJAX para agregar productos
- `contador.php` - AJAX para contar items
- `pago.php` - Página de selección de método de pago
- `procesar_paypal.php` - Crear orden en PayPal
- `paypal_return.php` - Procesar aprobación de PayPal
- `paypal_cancel.php` - Manejar cancelación
- `exito.php` - Mostrar orden completada
- `fallo.php` - Mostrar error de pago
- `factura.php` - Generar y enviar facturas
- `facturas/` - Carpeta de facturas en PDF

## Puntos de Control

1. ✅ Landing page pública con productos
2. ✅ Sistema de carrito con sesión
3. ✅ Integración con PayPal Sandbox
4. ✅ Generación de PDF con DOMPDF
5. ✅ Envío de facturas por email con PHPMailer
6. ✅ Actualización de stock
7. ✅ Control de roles (admin vs usuario)
