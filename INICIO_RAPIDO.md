# 🚀 GUÍA DE INICIO RÁPIDO - BIKE STORE

Sigue estos pasos para tener tu tienda funcionando en 15 minutos.

## PASO 1: Descargar PHPMailer (5 minutos)

Este es el único paso manual necesario.

### Windows con XAMPP:

1. Abre tu navegador y ve a:

   ```
   https://github.com/PHPMailer/PHPMailer/releases
   ```

2. Descarga la versión más reciente (busca `PHPMailer-X.X.X.zip`)

3. Descomprime el archivo en:

   ```
   C:\xampp\htdocs\bike_store_tw1\libs\phpmailer
   ```

4. Verifica que la estructura sea correcta:
   ```
   C:\xampp\htdocs\bike_store_tw1\libs\phpmailer\
   ├── src/
   │   ├── Exception.php
   │   ├── PHPMailer.php
   │   └── SMTP.php
   ├── composer.json
   └── ... (otros archivos)
   ```

**✅ Si ves los 3 archivos en `src/`, está correcto**

---

## PASO 2: Crear Tablas en Base de Datos (2 minutos)

### Opción A: Automática (Recomendado)

1. Abre tu navegador
2. Ve a: `http://localhost/bike_store_tw1/crear_tabla.php`
3. Deberías ver:
   ```
   ✓ Tabla payment_transactions creada
   ✓ Tabla facturas creada
   ✓ Columna comentarios agregada
   ✓ Columna factura_enviada agregada
   ```

### Opción B: Manual

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Selecciona tu base de datos `bike_store`
3. Click en "SQL"
4. Copia el contenido de `actualizarBD.sql` y ejecuta

---

## PASO 3: Configurar Gmail (5 minutos)

### Obtener Contraseña de Aplicación:

1. Ve a: https://myaccount.google.com/apppasswords
2. Si te pide verificación en 2 pasos:
   - Primero ve a: https://myaccount.google.com/security
   - Habilita "Verificación en dos pasos"
3. Vuelve a App Passwords
4. Selecciona: **Correo** y **Windows Computer**
5. Google te mostrará una contraseña de 16 caracteres, como:
   ```
   hhmpqlpwiahiihzp
   ```
6. **Cópiala** (sin espacios)

### Actualizar .env:

1. Abre: `C:\xampp\htdocs\bike_store_tw1\.env`
2. Reemplaza la línea:

   ```env
   SMTP_PASSWORD=hhmpqlpwiahiihzp
   ```

   con tu contraseña de 16 caracteres

3. Reemplaza también:

   ```env
   SMTP_USER=tu_email@gmail.com
   SMTP_FROM_EMAIL=tu_email@gmail.com
   ```

4. Guarda el archivo

---

## PASO 4: Crear Datos de Prueba (3 minutos)

Si no tienes datos en la BD:

1. Abre phpMyAdmin: `http://localhost/phpmyadmin`
2. Ejecuta este SQL para agregar categorías:

```sql
INSERT INTO categories (category_name) VALUES
('Mountain Bikes'),
('Road Bikes'),
('Electric Bikes'),
('Kids Bikes'),
('Cruiser Bikes'),
('Accessories');
```

3. Ejecuta esto para agregar un usuario de prueba:

```sql
INSERT INTO usuarios (usuario, password, email, role) VALUES
('admin', '123456', 'admin@bikestore.com', 'admin'),
('usuario', '123456', 'usuario@bikestore.com', 'user');
```

4. Agrega algunos productos con stock:

```sql
INSERT INTO products (product_name, model_year, price, category_id) VALUES
('Mountain Bike Pro', 2024, 899.99, 1),
('Road Bike Elite', 2024, 1299.99, 2),
('Electric Bike', 2024, 1999.99, 3);

-- Agregar stock
INSERT INTO stores (store_name, city) VALUES ('Tienda Principal', 'Tu Ciudad');
INSERT INTO stocks (store_id, product_id, quantity) VALUES
(1, 1, 10),
(1, 2, 5),
(1, 3, 3);
```

---

## PASO 5: ¡Prueba tu tienda! (Listo)

### Como Usuario Público (Sin Login):

1. Ve a: `http://localhost/bike_store_tw1/landing.php`
2. Deberías ver:

   - ✅ Hero section
   - ✅ Productos con fotos
   - ✅ Filtro por categoría
   - ✅ Botón "Agregar al Carrito"

3. Click en "Agregar al Carrito"
   - Deberías ver modal pidiendo login

### Como Usuario (Con Login):

1. Click en "Iniciar Sesión" (arriba)
2. Ingresa:

   ```
   Usuario: usuario
   Contraseña: 123456
   ```

3. ¡Estás logueado! Ahora:
   - ✅ Agrega productos
   - ✅ Mira el contador en navbar
   - ✅ Abre carrito
   - ✅ Procede a pago

### Pago con PayPal:

1. Click en "Proceder al Pago"
2. Click en "Pagar con PayPal"
3. Te llevar a PayPal Sandbox
4. Usa estas credenciales de prueba:
   ```
   Email: buyer@sandbox.paypal.com
   Contraseña: cualquiera (el sandbox acepta cualquier contraseña)
   ```
5. Aprueba el pago
6. **Deberías recibir email con factura PDF**

### Como Admin:

1. Login con:

   ```
   Usuario: admin
   Contraseña: 123456
   ```

2. Será redirigido a `http://localhost/bike_store_tw1/index.php`
3. Verá el dashboard admin con opciones de gestión

---

## 🐛 PROBLEMAS COMUNES

### "No se envían emails"

- [ ] ¿Descargaste PHPMailer?
- [ ] ¿Está en `libs/phpmailer/src/`?
- [ ] ¿Gmail está con contraseña de 16 caracteres?
- [ ] ¿Los caracteres están exactamente en `.env`?

**Solución:**

```bash
# En XAMPP terminal:
cd C:\xampp\htdocs\bike_store_tw1
php -r "include('config.php'); echo 'SMTP_USER: ' . SMTP_USER . '\nSMTP_HOST: ' . SMTP_HOST;"
```

### "Error de PayPal"

- [ ] ¿Verificaste Client ID en `.env`?
- [ ] ¿PAYPAL_MODE está en 'sandbox'?
- [ ] ¿cURL está habilitado en PHP?

**Solución:**

```php
// En browser: http://localhost/test_curl.php
<?php
phpinfo();
// Busca "curl" - deberá estar habilitado
?>
```

### "Las fotos no cargan"

- [ ] ¿Agregaste la foto al crear producto?
- [ ] ¿La foto está en `secciones/productos/imagen/`?
- [ ] ¿Ruta es correcta en BD?

**Solución:**
Crea una foto de prueba en `secciones/productos/imagen/test.jpg`

---

## 📋 CHECKLIST FINAL

Antes de decir que está listo:

- [ ] PHPMailer descargado en `libs/phpmailer/`
- [ ] Tablas creadas (visitaste `crear_tabla.php`)
- [ ] Gmail configurado con contraseña de 16 caracteres
- [ ] Categorías y productos en BD
- [ ] Stock configurado
- [ ] Usuario de prueba creado
- [ ] `landing.php` se ve bien
- [ ] Login funciona
- [ ] Carrito agrega productos
- [ ] PayPal acepta pago de prueba
- [ ] Email recibido con PDF

**Si marcaste TODO ✓ = ¡FELICIDADES! 🎉**

---

## 📞 AYUDA ADICIONAL

- **README.md** - Documentación completa
- **IMPLEMENTACION_COMPLETADA.md** - Qué se hizo
- **SETUP_PHPMAILER.md** - Detalles de PHPMailer

---

**¿Algo no funciona?**

1. Revisa los logs: `C:\xampp\htdocs\bike_store_tw1\error_log`
2. Mira la consola del navegador (F12)
3. Verifica que estés usando `http://` (no HTTPS en localhost)
4. Reinicia Apache y MySQL
