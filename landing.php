<?php
include("config.php");

// Si es admin, redirigir al dashboard admin
if (is_admin()) {
    header("Location: " . APP_URL . "index.php");
    exit;
}

// Obtener productos con stock disponible
$sentencia = $conexion->prepare("
    SELECT p.*, c.category_name,
    COALESCE(SUM(s.quantity), 0) as stock_total
    FROM products p 
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN stocks s ON p.product_id = s.product_id
    WHERE p.foto IS NOT NULL AND p.foto != ''
    GROUP BY p.product_id
    ORDER BY p.product_id DESC 
    LIMIT 12
");
$sentencia->execute();
$productos = $sentencia->fetchAll(PDO::FETCH_ASSOC);

// Obtener categorías para el filtro
$sentencia_categorias = $conexion->prepare("SELECT * FROM categories ORDER BY category_name");
$sentencia_categorias->execute();
$categorias = $sentencia_categorias->fetchAll(PDO::FETCH_ASSOC);

// Obtener 5 productos más vendidos (solo órdenes pagadas)
$sentencia_top = $conexion->prepare(
    "SELECT p.product_id, p.product_name, p.foto, COALESCE(SUM(oi.quantity),0) AS sold_qty
     FROM order_items oi
     JOIN orders o ON oi.order_id = o.order_id AND o.estado = 'Pagado'
     JOIN products p ON oi.product_id = p.product_id
     GROUP BY p.product_id
     ORDER BY sold_qty DESC
     LIMIT 5"
);
$sentencia_top->execute();
$top_products = $sentencia_top->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bike Store - Tienda de Bicicletas</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .hero-section {
            background: linear-gradient(rgba(0,0,0,0.5), rgba(0,0,0,0.5)), url('https://images.unsplash.com/photo-1485965120184-e220f721d03e?ixlib=rb-4.0.3&auto=format&fit=crop&w=1950&q=80');
            background-size: cover;
            background-position: center;
            color: white;
            padding: 120px 0;
            text-align: center;
        }
        .product-card {
            transition: transform 0.3s, box-shadow 0.3s;
            border: none;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
            height: 100%;
        }
        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.2);
        }
        .product-img {
            height: 200px;
            object-fit: cover;
            width: 100%;
        }
        .category-badge {
            position: absolute;
            top: 10px;
            left: 10px;
            z-index: 1;
        }
        .price {
            color: #198754;
            font-weight: bold;
            font-size: 1.2em;
        }
        .btn-add-cart {
            transition: all 0.3s;
        }
        .btn-add-cart:hover {
            transform: scale(1.05);
        }
        .navbar-brand {
            font-weight: bold;
            font-size: 1.5rem;
        }
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            font-size: 0.7em;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        main {
            flex: 1;
        }
        /* Mejorar visibilidad de controles del carrusel Top Sellers */
        #carouselTopSellers .carousel-control-prev,
        #carouselTopSellers .carousel-control-next {
            width: 4rem;
            opacity: 1;
            transition: transform .15s ease-in-out;
            z-index: 5;
        }
        #carouselTopSellers .carousel-control-prev:hover,
        #carouselTopSellers .carousel-control-next:hover {
            transform: scale(1.05);
        }
        #carouselTopSellers .carousel-control-prev-icon,
        #carouselTopSellers .carousel-control-next-icon {
            background-color: rgba(6,53,107,0.95);
            width: 3.2rem;
            height: 3.2rem;
            border-radius: 50%;
            box-shadow: 0 6px 18px rgba(6,53,107,0.35);
            background-size: 1.6rem 1.6rem;
            background-position: center;
            background-repeat: no-repeat;
        }
        /* Asegurar que el icono SVG se vea blanco sobre el fondo */
        #carouselTopSellers .carousel-control-prev-icon::after,
        #carouselTopSellers .carousel-control-next-icon::after {
            content: '';
        }
    </style>
</head>
<body>
    <!-- Navbar Pública -->
    <nav class="navbar navbar-expand-lg navbar-dark" style="background-color: #06356bff;">
        <div class="container">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>landing.php">
                <i class="bi bi-bicycle"></i> <?php echo APP_NAME; ?>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="#inicio">Inicio</a></li>
                    <li class="nav-item"><a class="nav-link" href="#productos">Productos</a></li>
                    <li class="nav-item"><a class="nav-link" href="#categorias">Categorías</a></li>
                    <li class="nav-item"><a class="nav-link" href="#contacto">Contacto</a></li>
                </ul>
                <ul class="navbar-nav">
                    <?php if(is_logged_in()): ?>
                        <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                                <i class="bi bi-person-circle"></i> <?php echo $_SESSION['usuario']; ?>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>secciones/pedidos/">Mis Pedidos</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="<?php echo APP_URL; ?>cerrar.php">Cerrar Sesión</a></li>
                            </ul>
                        </li>
                        <li class="nav-item position-relative">
                            <a class="nav-link" href="<?php echo APP_URL; ?>secciones/carrito/">
                                <i class="bi bi-cart3"></i> Carrito
                                <span id="cart-count" class="badge bg-primary cart-badge" style="display: none;">0</span>
                            </a>
                        </li>
                    <?php else: ?>
                        <li class="nav-item">
                            <a class="nav-link" href="<?php echo APP_URL; ?>login.php">
                                <i class="bi bi-box-arrow-in-right"></i> Iniciar Sesión
                            </a>
                        </li>
                    <?php endif; ?>
                </ul>
            </div>
        </div>
    </nav>

    <main>
        <!-- Sección Hero -->
        <section id="inicio" class="hero-section">
            <div class="container">
                <h1 class="display-4 fw-bold mb-4">Encuentra Tu Bicicleta Perfecta</h1>
                <p class="lead mb-4">Descubre nuestra amplia selección de bicicletas y accesorios de calidad para todos los estilos</p>
                <a href="#productos" class="btn btn-primary btn-lg me-2">Ver Productos</a>
                <a href="#categorias" class="btn btn-outline-light btn-lg">Explorar Categorías</a>
            </div>
        </section>

        <!-- Carrusel de Top Sellers -->
        <?php if (!empty($top_products)): ?>
        <section id="top-sellers" class="py-4">
            <div class="container">
                <div class="row mb-3">
                    <div class="col-12 text-center">
                        <h3 class="fw-bold">Top 5 Más Vendidos</h3>
                        <p class="text-muted">Nuestros productos más populares</p>
                    </div>
                </div>
                <div id="carouselTopSellers" class="carousel slide" data-bs-ride="carousel" data-bs-interval="4000">
                    <!-- Indicadores -->
                    <div class="carousel-indicators">
                        <?php foreach($top_products as $i => $tp): ?>
                            <button type="button" data-bs-target="#carouselTopSellers" data-bs-slide-to="<?php echo $i; ?>" class="<?php echo $i === 0 ? 'active' : ''; ?>" aria-current="<?php echo $i === 0 ? 'true' : 'false'; ?>" aria-label="Slide <?php echo $i+1; ?>"></button>
                        <?php endforeach; ?>
                    </div>
                    <div class="carousel-inner">
                        <?php foreach($top_products as $idx => $tp): ?>
                        <div class="carousel-item <?php echo $idx === 0 ? 'active' : ''; ?>">
                            <div class="d-flex justify-content-center align-items-center" style="min-height:320px;">
                                <div class="card" style="width: 40rem; border:none;">
                                    <img src="secciones/productos/imagen/<?php echo htmlspecialchars($tp['foto']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($tp['product_name']); ?>" style="height:320px;object-fit:cover;">
                                    <div class="card-body text-center">
                                        <h5 class="card-title"><?php echo htmlspecialchars($tp['product_name']); ?></h5>
                                        <p class="text-muted">Vendidos: <?php echo intval($tp['sold_qty']); ?></p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <button class="carousel-control-prev" type="button" data-bs-target="#carouselTopSellers" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#carouselTopSellers" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Siguiente</span>
                    </button>
                </div>
            </div>
        </section>
        <?php endif; ?>

        <!-- Productos Destacados -->
        <section id="productos" class="py-5 bg-light">
            <div class="container">
                <div class="row mb-4">
                    <div class="col-12 text-center">
                        <h2 class="display-5 fw-bold">Productos Destacados</h2>
                        <p class="lead text-muted">Los mejores productos seleccionados para ti</p>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div class="row align-items-center">
                                    <div class="col-md-4">
                                        <h6 class="mb-0">Filtrar por categoría:</h6>
                                    </div>
                                    <div class="col-md-8">
                                        <div class="btn-group" role="group" style="flex-wrap: wrap; gap: 5px;">
                                            <button type="button" class="btn btn-outline-primary active filter-btn" data-category="all">
                                                Todas
                                            </button>
                                            <?php foreach($categorias as $categoria): ?>
                                            <button type="button" class="btn btn-outline-primary filter-btn" data-category="<?php echo $categoria['category_id']; ?>">
                                                <?php echo $categoria['category_name']; ?>
                                            </button>
                                            <?php endforeach; ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Grid de Productos -->
                <div class="row" id="productos-grid">
                    <?php foreach($productos as $producto): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4 producto-item" data-category="<?php echo $producto['category_id']; ?>">
                        <div class="card product-card h-100">
                            <span class="badge bg-success category-badge"><?php echo $producto['category_name']; ?></span>
                            <?php if($producto['stock_total'] <= 0): ?>
                                <span class="badge bg-danger position-absolute top-0 end-0 m-2">Agotado</span>
                            <?php endif; ?>
                            <img src="secciones/productos/imagen/<?php echo htmlspecialchars($producto['foto']); ?>" 
                                 class="card-img-top product-img" 
                                 alt="<?php echo htmlspecialchars($producto['product_name']); ?>"
                                 onerror="this.src='https://via.placeholder.com/300x200?text=No+disponible'">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($producto['product_name']); ?></h5>
                                <p class="text-muted small">Modelo <?php echo $producto['model_year']; ?></p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="price">$<?php echo number_format($producto['price'], 2); ?></span>
                                        <?php if($producto['stock_total'] > 0): ?>
                                            <span class="badge bg-info">En stock</span>
                                        <?php endif; ?>
                                    </div>
                                    <button class="btn btn-primary w-100 btn-add-cart" 
                                            data-product-id="<?php echo $producto['product_id']; ?>"
                                            data-product-name="<?php echo htmlspecialchars($producto['product_name']); ?>"
                                            data-product-price="<?php echo $producto['price']; ?>"
                                            data-require-login="<?php echo is_logged_in() ? 'false' : 'true'; ?>"
                                            <?php echo ($producto['stock_total'] <= 0) ? 'disabled' : ''; ?>>
                                        <i class="bi bi-cart-plus"></i> Agregar al Carrito
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Mensaje cuando no hay productos -->
                <div id="no-products" class="text-center" style="display: none;">
                    <div class="py-5">
                        <i class="bi bi-search display-1 text-muted"></i>
                        <h3 class="text-muted">No se encontraron productos</h3>
                        <p class="text-muted">Intenta con otros filtros</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Categorías -->
        <section id="categorias" class="py-5">
            <div class="container">
                <div class="row mb-5">
                    <div class="col-12 text-center">
                        <h2 class="display-5 fw-bold">Nuestras Categorías</h2>
                        <p class="lead text-muted">Encuentra lo que necesitas</p>
                    </div>
                </div>
                <div class="row">
                    <?php foreach($categorias as $categoria): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card text-center h-100">
                            <div class="card-body">
                                <div class="mb-3">
                                    <i class="bi bi-bicycle display-6 text-primary"></i>
                                </div>
                                <h5 class="card-title"><?php echo $categoria['category_name']; ?></h5>
                                <p class="text-muted">Descubre nuestra selección</p>
                                <button class="btn btn-outline-primary filter-category" 
                                        data-category="<?php echo $categoria['category_id']; ?>">
                                    Ver Productos
                                </button>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </section>
    </main>

    <!-- Footer -->
    <footer id="contacto" class="bg-dark text-white py-5 mt-5">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>
                        <i class="bi bi-bicycle"></i> <?php echo APP_NAME; ?>
                    </h5>
                    <p>Tu tienda de confianza para bicicletas y accesorios de calidad.</p>
                    <div class="social-links">
                        <a href="#" class="text-white me-3"><i class="bi bi-facebook"></i></a>
                        <a href="#" class="text-white me-3"><i class="bi bi-instagram"></i></a>
                        <a href="#" class="text-white"><i class="bi bi-twitter"></i></a>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contacto</h5>
                    <p><i class="bi bi-geo-alt"></i> Av. Principal #123, Ciudad</p>
                    <p><i class="bi bi-telephone"></i> +1 234 567 890</p>
                    <p><i class="bi bi-envelope"></i> info@bikestore.com</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Horario de Atención</h5>
                    <p>Lunes - Viernes: 9:00 - 18:00</p>
                    <p>Sábados: 9:00 - 14:00</p>
                    <p>Domingos: Cerrado</p>
                </div>
            </div>
            <hr>
            <div class="text-center">
                <p>&copy; 2024 <?php echo APP_NAME; ?>. Todos los derechos reservados.</p>
            </div>
        </div>
    </footer>

    <!-- Modal de Confirmación -->
    <div class="modal fade" id="cartModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Producto Agregado</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-check-circle-fill text-success display-4 mb-3"></i>
                    <h5 id="modal-product-name"></h5>
                    <p class="text-muted">Se ha agregado al carrito correctamente</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Seguir Comprando</button>
                    <a href="<?php echo APP_URL; ?>secciones/carrito/" class="btn btn-primary">Ver Carrito</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Login requerido -->
    <div class="modal fade" id="loginModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Inicia Sesión</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="bi bi-box-arrow-in-right display-4 mb-3"></i>
                    <p>Para agregar productos al carrito, debes iniciar sesión primero.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <a href="<?php echo APP_URL; ?>login.php" class="btn btn-primary">Ir a Login</a>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        const APP_URL = "<?php echo APP_URL; ?>";

        // Función para agregar al carrito
        async function agregarAlCarrito(productId, productName, productPrice) {
            try {
                const response = await fetch(APP_URL + 'secciones/carrito/agregar.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `product_id=${productId}&quantity=1`
                });
                
                const data = await response.json();
                
                if (data.success) {
                    // Mostrar modal de confirmación
                    document.getElementById('modal-product-name').textContent = productName;
                    const modal = new bootstrap.Modal(document.getElementById('cartModal'));
                    modal.show();
                    
                    // Actualizar contador del carrito
                    actualizarContadorCarrito();
                } else if (data.error === 'login_required') {
                    const modal = new bootstrap.Modal(document.getElementById('loginModal'));
                    modal.show();
                } else {
                    Swal.fire('Error', data.message || 'No se pudo agregar el producto', 'error');
                }
            } catch (error) {
                console.error('Error:', error);
                Swal.fire('Error', 'No se pudo agregar el producto al carrito', 'error');
            }
        }

        // Actualizar contador del carrito
        async function actualizarContadorCarrito() {
            try {
                const response = await fetch(APP_URL + 'secciones/carrito/contador.php');
                const data = await response.json();
                const cartCount = document.getElementById('cart-count');
                
                if (cartCount && data.total !== undefined) {
                    cartCount.textContent = data.total;
                    cartCount.style.display = data.total > 0 ? 'inline' : 'none';
                }
            } catch (error) {
                console.error('Error al actualizar contador:', error);
            }
        }

        // Filtrado de productos por categoría
        function filtrarProductos(categoryId) {
            const productos = document.querySelectorAll('.producto-item');
            let visibleCount = 0;
            
            productos.forEach(producto => {
                if (categoryId === 'all' || producto.getAttribute('data-category') === categoryId) {
                    producto.style.display = 'block';
                    visibleCount++;
                } else {
                    producto.style.display = 'none';
                }
            });
            
            // Mostrar mensaje si no hay productos
            const noProducts = document.getElementById('no-products');
            noProducts.style.display = visibleCount === 0 ? 'block' : 'none';
        }

        // Event Listeners
        document.addEventListener('DOMContentLoaded', function() {
            // Botones "Agregar al carrito"
            document.querySelectorAll('.btn-add-cart').forEach(button => {
                button.addEventListener('click', function() {
                    const productId = this.getAttribute('data-product-id');
                    const productName = this.getAttribute('data-product-name');
                    const productPrice = this.getAttribute('data-product-price');
                    const requireLogin = this.getAttribute('data-require-login') === 'true';
                    
                    if (requireLogin) {
                        const modal = new bootstrap.Modal(document.getElementById('loginModal'));
                        modal.show();
                    } else {
                        agregarAlCarrito(productId, productName, productPrice);
                    }
                });
            });
            
            // Filtros de categoría
            document.querySelectorAll('.filter-btn').forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category');
                    
                    // Actualizar botones activos
                    document.querySelectorAll('.filter-btn').forEach(btn => {
                        btn.classList.remove('active');
                    });
                    this.classList.add('active');
                    
                    filtrarProductos(categoryId);
                });
            });
            
            // Botones de categoría en la sección categorías
            document.querySelectorAll('.filter-category').forEach(button => {
                button.addEventListener('click', function() {
                    const categoryId = this.getAttribute('data-category');
                    
                    // Scroll a productos
                    document.getElementById('productos').scrollIntoView({
                        behavior: 'smooth'
                    });
                    
                    // Activar filtro
                    setTimeout(() => {
                        const filterButton = document.querySelector(`[data-category="${categoryId}"].filter-btn`);
                        if (filterButton) {
                            filterButton.click();
                        }
                    }, 500);
                });
            });
            
            // Cargar contador inicial del carrito si está logueado
            <?php if(is_logged_in()): ?>
            actualizarContadorCarrito();
            <?php endif; ?>
        });

        // Smooth scroll para enlaces internos
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });
    </script>
</body>
</html>
