# 🚲 Bike Store - Sistema de E-commerce de Bicicletas

Un sistema completo de tienda en línea de bicicletas con carrito de compras, integración PayPal Sandbox y envío de facturas por email.

## 📋 Características

✅ **Landing Page Pública** - Catálogo de productos con filtro por categoría  
✅ **Sistema de Carrito** - Agregar/eliminar productos, gestión de cantidad  
✅ **Integración PayPal** - Pagos seguros mediante PayPal Sandbox  
✅ **Generación de PDFs** - Facturas automáticas usando DOMPDF  
✅ **Envío de Emails** - Facturas enviadas al cliente con PHPMailer  
✅ **Control de Stock** - Actualización automática al completar pago  
✅ **Sistema de Roles** - Admin y usuarios con diferentes vistas  
✅ **Autenticación** - Login seguro con sesiones PHP

## 🚀 Instalación Rápida

### 1. Configurar la Base de Datos

Ejecuta el siguiente SQL en tu cliente MySQL:

```sql
-- Ya tendrás las tablas base, solo agrega las nuevas:

CREATE TABLE IF NOT EXISTS payment_transactions (
  transaction_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  paypal_transaction_id VARCHAR(255) NOT NULL UNIQUE,
  amount DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) NOT NULL DEFAULT 'pending',
  method VARCHAR(50) NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_order_transaction FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS facturas (
  factura_id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  numero_factura VARCHAR(50) UNIQUE NOT NULL,
  pdf_path VARCHAR(255),
  fecha_emision TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  enviada_email BOOLEAN DEFAULT FALSE,
  CONSTRAINT fk_order_factura FOREIGN KEY (order_id) REFERENCES orders(order_id)
    ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB;

ALTER TABLE orders ADD COLUMN IF NOT EXISTS comentarios TEXT;
ALTER TABLE orders ADD COLUMN IF NOT EXISTS factura_enviada BOOLEAN DEFAULT FALSE;
```

O simplemente accede a: `http://localhost/bike_store_tw1/crear_tabla.php`

### 2. Configurar Variables de Entorno

Edita el archivo `.env` en la raíz:

```env
# SMTP Configuration (Gmail)
SMTP_HOST=smtp.gmail.com
SMTP_USER=tu_email@gmail.com
SMTP_PASSWORD=tu_contraseña_de_aplicación
SMTP_ENCRYPTION=ssl
SMTP_PORT=465
SMTP_FROM_EMAIL=tu_email@gmail.com
SMTP_FROM_NAME="Bike Store"

# PayPal Configuration (Ya configurado con Sandbox)
PAYPAL_MODE=sandbox
PAYPAL_CLIENT_ID=AUPFXnzHKdf61oJkarbtIzErFoGW-dEBFWUS74h-WjbQ6JDtJ5yv85XL36u04d_Jn_4nKEgRPZjxsrHB
PAYPAL_SECRET=EGusJHziEauS9yuXV4-WMK9qBX1RoUDjxrCf-3PWrJ-CJ-itFXXggtQwl7_NXmeN3PKzTSbh8e6jj06y

# Application Configuration
APP_URL=http://localhost/bike_store_tw1/
APP_NAME="Bike Store"
```

### 3. Instalar PHPMailer

**Para Windows con XAMPP:**

1. Descarga PHPMailer: https://github.com/PHPMailer/PHPMailer/releases
2. Descomprime en `libs/phpmailer/`
3. La estructura debe ser:
   ```
   libs/phpmailer/
   ├── src/
   │   ├── Exception.php
   │   ├── PHPMailer.php
   │   └── SMTP.php
   ```

**O con Composer:**

```bash
cd c:\xampp\htdocs\bike_store_tw1
composer require phpmailer/phpmailer
```

### 4. Configurar Gmail para PHPMailer

1. Abre https://myaccount.google.com/apppasswords (requiere 2FA activo)
2. Selecciona "Correo" y "Windows Computer"
3. Gmail te da una contraseña de 16 caracteres
4. Pon esa contraseña en `.env` como `SMTP_PASSWORD`

## 📁 Estructura de Archivos

```
bike_store_tw1/
├── .env                    # Variables de entorno (NO SUBIR A GIT)
├── config.php             # Configuración y conexión BD
├── bd.php                 # Conexión antigua (deprecado, usa config.php)
├── crear_tabla.php        # Script para crear tablas faltantes
├── landing.php            # Landing page pública
├── login.php              # Página de login
├── index.php              # Dashboard admin (solo para admins)
├── cerrar.php             # Cerrar sesión
│
├── libs/
│   ├── dompdf/           # Librería para generar PDFs
│   └── phpmailer/        # Librería para enviar emails
│
├── templates/
│   ├── header.php        # Navbar para admins
│   └── footer.php        # Pie de página
│
├── secciones/
│   ├── carrito/
│   │   ├── index.php              # Ver carrito
│   │   ├── agregar.php            # AJAX agregar producto
│   │   ├── contador.php           # AJAX contar items
│   │   ├── pago.php               # Seleccionar método pago
│   │   ├── procesar_paypal.php    # Crear orden en PayPal
│   │   ├── paypal_return.php      # Procesar retorno PayPal
│   │   ├── paypal_cancel.php      # Manejo cancelación
│   │   ├── exito.php              # Compra exitosa
│   │   ├── fallo.php              # Error en pago
│   │   ├── factura.php            # Generar y enviar facturas
│   │   └── facturas/              # PDFs generados (se crea auto)
│   │
│   ├── productos/        # Gestión de productos (admin)
│   ├── pedidos/          # Mis pedidos (usuario/admin)
│   └── ... (otras secciones)
```

## 🔄 Flujo de Compra

```
1. Usuario no logueado ve landing.php
2. Click en "Agregar al Carrito" → Se pide login
3. Usuario se loguea → Redirigido a landing.php
4. Agregar productos → Se guardan en $_SESSION['carrito']
5. Ver carrito → secciones/carrito/index.php
6. Proceder a pago → secciones/carrito/pago.php
7. Seleccionar PayPal → procesar_paypal.php crea orden
8. Usuario aprueba en PayPal → paypal_return.php captura
9. Orden guardada → Se descuenta stock
10. Email enviado → exito.php muestra orden
```

## 🔐 Seguridad

- ✅ Validación de sesiones en todas las páginas
- ✅ Protección contra inyección SQL (prepared statements)
- ✅ Sanitización de entrada de usuario
- ✅ Control de acceso por roles
- ✅ HTTPS recomendado para producción

## 🧪 Pruebas con PayPal Sandbox

1. Crea cuenta en https://developer.paypal.com
2. Crea una aplicación para obtener Client ID y Secret
3. En Sandbox, use credenciales de prueba:
   - **Buyer Account:** buyer_email@sandbox.paypal.com / Buyer123!
   - **Seller Account:** merchant_email@sandbox.paypal.com / Merchant123!

## 📧 Pruebas de Email

Antes de enviar a producción, prueba el email:

```php
// En terminal
cd c:\xampp\htdocs\bike_store_tw1
php -r "include('secciones/carrito/factura.php'); enviar_factura_email(1, \$conexion);"
```

## ⚙️ Configuración de Servidor

### Apache (XAMPP)

Ya viene configurado. Solo asegúrate de tener habilitadas las extensiones:

- php_curl (para PayPal)
- php_mbstring (para UTF-8)
- php_mysqli o php_pdo (para BD)

### Permisos de Carpetas

```bash
# Windows (en PowerShell como Admin)
icacls "C:\xampp\htdocs\bike_store_tw1\secciones\carrito\facturas" /grant Everyone:F
```

## 🐛 Troubleshooting

### No se envían emails

- ✓ Verificar `.env` con credenciales correctas
- ✓ Gmail requiere "Contraseña de aplicación" (16 caracteres)
- ✓ PHPMailer instalado en `libs/phpmailer/src/`

### PayPal error

- ✓ Verificar Client ID y Secret en `.env`
- ✓ Modo debe ser "sandbox" para pruebas
- ✓ cURL habilitado en PHP

### Stock no se descuenta

- ✓ Verificar tabla `stocks` tiene datos
- ✓ `paypal_return.php` se ejecutó correctamente
- ✓ Revisar logs de error

## 📊 Base de Datos

### Tablas principales:

- `products` - Catálogo de bicicletas
- `categories` - Categorías
- `customers` - Clientes
- `orders` - Órdenes
- `order_items` - Items en órdenes
- `stocks` - Stock por tienda
- `usuarios` - Usuarios (login)
- `payment_transactions` - Historial pagos PayPal
- `facturas` - Facturas generadas

## 🎨 Personalización

### Cambiar colores

Edita el color `#06356b` (azul) en los archivos HTML/CSS

### Cambiar nombre tienda

Edita `APP_NAME` en `.env` y se actualizará automáticamente

### Cambiar impuesto

En `secciones/carrito/index.php` y `landing.php`, busca `* 0.16` (16% IVA)

## 📝 Notas para Producción

1. **Cambiar a producción PayPal:**

   ```env
   PAYPAL_MODE=production
   PAYPAL_CLIENT_ID=tu_client_id_producción
   PAYPAL_SECRET=tu_secret_producción
   ```

2. **HTTPS obligatorio**
3. **Habilitar SMTP seguro en Gmail**
4. **Respaldar base de datos regularmente**
5. **Monitorear logs en `error_log`**

## 📞 Soporte

Para problemas específicos, revisa:

- `SETUP_PHPMAILER.md` - Instalación de PHPMailer
- Logs de error en Apache/PHP
- Consola de desarrollador (F12) para errores JS

## ✅ Checklist Final

- [ ] Base de datos creada con todas las tablas
- [ ] `.env` configurado con SMTP y PayPal
- [ ] PHPMailer instalado en `libs/phpmailer/`
- [ ] Carpeta `secciones/carrito/facturas/` creada
- [ ] Permisos de escritura en `facturas/`
- [ ] Usuarios de prueba creados
- [ ] Categorías y productos en BD
- [ ] Stock configurado en tabla `stocks`
- [ ] Probado flujo completo de compra
- [ ] Emails llegando correctamente

---

**¡Listo para vender bicicletas! 🚲**
