<?php
session_start();
require_once "../shortCuts/connect.php";
require_once "cart-functions.php";

// Validar si llega el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Producto no especificado.");
}

$id = intval($_GET['id']);

// Consultar información del producto
$sql = "SELECT * FROM producto WHERE id_producto = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("Producto no encontrado.");
}

$producto = $result->fetch_assoc();

// PROCESAR AÑADIR AL CARRITO - SOLO PARA AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;
    $action = $_POST['action'];
    $ajax = isset($_POST['ajax']) && $_POST['ajax'] === 'true'; // Nuevo parámetro

    if ($quantity > 0 && $producto['stock'] >= $quantity) {
        $imagen_url = !empty($producto['imagen_url']) ? $producto['imagen_url'] : (!empty($producto['imagen']) ? '../SOURCES/PRODUCTOS/' . $producto['imagen'] : null);

        // Añadir al carrito
        addToCart(
            $producto['id_producto'],
            $producto['nombre'],
            $producto['precio'],
            $quantity,
            $imagen_url
        );

        // Si es petición AJAX, devolver JSON
        if ($ajax) {
            echo json_encode([
                'success' => true,
                'message' => '✓ Producto añadido al carrito correctamente',
                'cart_count' => getCartCount()
            ]);
            exit;
        }

        // Si NO es AJAX, redirigir según la acción
        if ($action === 'buy_now') {
            // "COMPRAR AHORA" → Redirigir al CHECKOUT
            header("Location: ../CONTROLLERS/checkout.php");
            exit;
        } else {
            // "AÑADIR AL CARRITO" → Redirigir al CARRITO
            $_SESSION['cart_message'] = '✓ Producto añadido al carrito correctamente';
            header("Location: ../CONTROLLERS/cart.php");
            exit;
        }
    } else {
        $message = 'Stock insuficiente. Disponible: ' . $producto['stock'];

        if ($ajax) {
            echo json_encode([
                'success' => false,
                'message' => $message
            ]);
            exit;
        } else {
            $_SESSION['cart_message'] = $message;
            header("Location: ?id=" . $id);
            exit;
        }
    }
}

$cart_message = '';
if (isset($_SESSION['cart_message'])) {
    $cart_message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}
?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['nombre']); ?></title>
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/home.css">
    <title>HOME | HERMES CLICK&GO</title>
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">

    <style>
        /* ESTILOS GENERALES */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }

        /* ESTILOS DEL PRODUCTO */
        .product-container {
            display: flex;
            flex-wrap: wrap;
            gap: 40px;
            margin-bottom: 30px;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
        }

        .product-image-section {
            flex: 1;
            min-width: 300px;
        }

        .product-image {
            width: 100%;
            max-width: 500px;
            height: 400px;
            margin: 0 auto;
            background: #f9f9f9;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid #eaeaea;
            padding: 15px;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            transition: transform 0.3s;
        }

        .product-image:hover img {
            transform: scale(1.02);
        }

        .product-info-section {
            flex: 1;
            min-width: 300px;
        }

        .product-title {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 2.2rem;
            line-height: 1.2;
            font-weight: 700;
        }

        .product-price {
            font-size: 2rem;
            color: #8B4513;
            font-weight: bold;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .product-stock {
            font-size: 1rem;
            font-weight: bold;
            padding: 6px 15px;
            border-radius: 20px;
            display: inline-block;
        }

        .stock-available {
            background: #d4edda;
            color: #155724;
        }

        .stock-out {
            background: #f8d7da;
            color: #721c24;
        }

        .product-description {
            color: #666;
            line-height: 1.7;
            margin-bottom: 25px;
            font-size: 1.1rem;
        }

        .product-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 25px;
            border-left: 4px solid #8B4513;
        }

        .product-details h3 {
            color: #495057;
            margin-bottom: 15px;
            font-size: 1.3rem;
            border-bottom: 2px solid #eaeaea;
            padding-bottom: 8px;
        }

        .product-details p {
            margin-bottom: 10px;
            color: #555;
        }

        .product-details strong {
            color: #333;
        }

        /* ESTILOS DEL FORMULARIO DEL CARRITO */
        .cart-form-container {
            margin-bottom: 25px;
        }

        .quantity-selector {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }

        .quantity-btn {
            width: 40px;
            height: 40px;
            border: 2px solid #dee2e6;
            background: white;
            border-radius: 8px;
            font-size: 1.2rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .quantity-btn:hover {
            border-color: #8B4513;
            background: #f9f9f9;
        }

        .quantity-input {
            width: 70px;
            height: 40px;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            font-size: 1.1rem;
            font-weight: bold;
        }

        .quantity-input:focus {
            border-color: #8B4513;
            outline: none;
        }

        .max-stock {
            color: #6c757d;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .action-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .btn-add-to-cart {
            padding: 14px 28px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-add-to-cart:hover {
            background: #218838;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.3);
        }

        .btn-buy-now {
            padding: 14px 28px;
            background: #8B4513;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-buy-now:hover {
            background: #72370f;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }

        .btn-disabled {
            padding: 14px 28px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: not-allowed;
            display: flex;
            align-items: center;
            gap: 10px;
            opacity: 0.7;
        }

        .btn-notify {
            padding: 14px 28px;
            background: #17a2b8;
            color: white;
            border: none;
            border-radius: 10px;
            font-weight: bold;
            font-size: 1.1rem;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 10px;
            transition: all 0.3s;
        }

        .btn-notify:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        /* FORMULARIOS OCULTOS */
        .hidden-form {
            display: none;
        }

        /* MENSAJES DEL CARRITO */
        .cart-message {
            padding: 15px 20px;
            margin: 15px 0;
            border-radius: 10px;
            font-weight: 500;
            display: flex;
            align-items: center;
            justify-content: space-between;
            animation: slideIn 0.3s ease;
        }

        .message-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .message-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* BOTÓN VOLVER */
        .btn-volver {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 12px 25px;
            background: #6c757d;
            color: white;
            text-decoration: none;
            border-radius: 10px;
            font-weight: bold;
            transition: all 0.3s;
            margin-top: 30px;
        }

        .btn-volver:hover {
            background: #5a6268;
            transform: translateX(-5px);
            box-shadow: 0 5px 15px rgba(108, 117, 125, 0.3);
        }

        /* RESPONSIVE */
        @media (max-width: 768px) {
            .product-container {
                padding: 20px;
                gap: 30px;
            }

            .product-title {
                font-size: 1.8rem;
            }

            .product-price {
                font-size: 1.6rem;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn-add-to-cart,
            .btn-buy-now,
            .btn-disabled,
            .btn-notify {
                width: 100%;
                justify-content: center;
            }

            .quantity-selector {
                justify-content: center;
            }
        }

        .notification-container {
            position: fixed;
            top: 100px;
            right: 20px;
            z-index: 9999;
        }

        .notification {
            padding: 15px 20px;
            margin-bottom: 10px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 10px;
            animation: slideIn 0.3s ease-out;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            max-width: 350px;
        }

        .notification-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .notification-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        @keyframes slideIn {
            from {
                transform: translateX(100%);
                opacity: 0;
            }

            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        @keyframes slideOut {
            from {
                transform: translateX(0);
                opacity: 1;
            }

            to {
                transform: translateX(100%);
                opacity: 0;
            }
        }

        /* Estilo para el botón de añadir al carrito cuando está cargando */
        .btn-loading {
            position: relative;
            pointer-events: none;
            opacity: 0.8;
        }

        .btn-loading::after {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 20px;
            height: 20px;
            margin: -10px 0 0 -10px;
            border: 2px solid transparent;
            border-top-color: white;
            border-radius: 50%;
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            to {
                transform: rotate(360deg);
            }
        }
    </style>
</head>

<body>
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <h1>HERMES</h1>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="search-products.php" method="GET" style="width:100%">
                        <li class="input-search-product-li">
                            <input
                                type="text"
                                name="search-product"
                                id="input-search-product"
                                placeholder="Buscar producto..."
                                value="" autocomplete="off">
                            <button type="submit" class="button-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <div id="results-container"></div>
                        </li>
                    </form>
                </div>
            </ul>
        </div>
        <div class="bottom">
            <nav>
                <ul>
                    <li><span id="span-menu-categoria">Categorias</span>
                        <div id="menu-categoria" class="menu-categoria">
                            <ul>
                                <li>Electrodomesticos</li>
                                <li>Tecnologia</li>
                                <li>Hogar</li>
                                <li>Moda</li>
                                <li>Deportes</li>
                                <li>Belleza</li>
                                <li>Jugueteria</li>
                                <li>Automotriz</li>
                                <li>Electronica</li>
                                <li>Mascotas</li>
                                <li>Arte</li>
                            </ul>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <li><span id="venderPage">Vender</span></li>
                    <?php endif; ?>
                    <li><span id="ayuda-listado">Ayuda</span>
                        <div id="menu-ayuda" class="menu-categoria">
                            <ul>
                                <li>Informacion</li>
                                <li>PQRS</li>
                                <li>Contactos</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="account-header">
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="perfil-menu">
                        <button class="perfil-btn"> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="../CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.php"><span class="sisu-buttons"> Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons"> Sign Up</span></a>
                <?php endif; ?>
            </div>
            <div class="icons-header">
                <span><img src="../SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <a href="cart.php">
                    <img src="SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart">
                </a>
            </div>
        </div>
    </header>


    <div class="container">
        <div class="product-container">
            <!-- Sección de imagen -->
            <div class="product-image-section">
                <?php
                $imagen_url = !empty($producto['imagen_url']) ? $producto['imagen_url'] : (!empty($producto['imagen']) ? '../SOURCES/PRODUCTOS/' . $producto['imagen'] :
                    '../SOURCES/PRODUCTOS/default.png');
                ?>

                <div class="product-image">
                    <img src="<?php echo $imagen_url; ?>"
                        alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                        onerror="this.src='../SOURCES/PRODUCTOS/default.png'; this.style.objectFit='cover';">
                </div>
            </div>

            <!-- Sección de información -->
            <div class="product-info-section">
                <h1 class="product-title"><?php echo htmlspecialchars($producto['nombre']); ?></h1>

                <div class="product-price">
                    $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                    <span class="product-stock <?php echo $producto['stock'] > 0 ? 'stock-available' : 'stock-out'; ?>">
                        <?php echo $producto['stock'] > 0 ? '✓ Disponible' : '✗ Agotado'; ?>
                    </span>
                </div>

                <div class="product-description">
                    <?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?>
                </div>

                <div class="product-details">
                    <h3>Detalles del producto</h3>
                    <p><strong>Stock disponible:</strong> <?php echo $producto['stock']; ?> unidades</p>
                    <p><strong>Origen/Marca:</strong> <?php echo htmlspecialchars($producto['origen']); ?></p>
                </div>

                <!-- Formularios para las acciones -->
                <div class="cart-form-container">
                    <?php if ($producto['stock'] > 0): ?>
                        <!-- Selector de cantidad (compartido) -->
                        <div class="quantity-selector">
                            <button type="button" class="quantity-btn" onclick="changeQuantity(-1)">
                                <i class="fas fa-minus"></i>
                            </button>

                            <input type="number"
                                id="quantity"
                                value="1"
                                min="1"
                                max="<?php echo $producto['stock']; ?>"
                                class="quantity-input">

                            <button type="button" class="quantity-btn" onclick="changeQuantity(1)">
                                <i class="fas fa-plus"></i>
                            </button>

                            <span class="max-stock">Máximo: <?php echo $producto['stock']; ?> unidades</span>
                        </div>

                        <!-- Botones de acción -->
                        <!-- Cambia los botones en el HTML: -->
                        <div class="action-buttons">
                            <button type="button" class="btn-add-to-cart" onclick="addToCartAjax()" id="addToCartBtn">
                                <i class="fas fa-cart-plus"></i> Añadir al carrito
                            </button>

                            <button type="button" class="btn-buy-now" onclick="buyNowAjax()" id="buyNowBtn">
                                <i class="fas fa-bolt"></i> Comprar ahora
                            </button>
                        </div>

                        <!-- Contenedor para notificaciones -->
                        <div class="notification-container" id="notificationContainer"></div>

                    <?php else: ?>
                        <div class="action-buttons">
                            <button class="btn-disabled">
                                <i class="fas fa-times-circle"></i> Producto agotado
                            </button>
                            <button class="btn-notify">
                                <i class="fas fa-bell"></i> Notificarme cuando esté disponible
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Mensajes del carrito -->
                <?php if ($cart_message): ?>
                    <div class="cart-message message-<?php echo $cart_message['type']; ?>">
                        <span><?php echo htmlspecialchars($cart_message['message']); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Botón volver -->
        <a class="btn-volver" href="search-products.php?search-product=<?php echo urlencode($_GET['search-product'] ?? ''); ?>">
            <i class="fas fa-arrow-left"></i> Volver a resultados
        </a>
    </div>
    <script src="../scripts/search-product.js"></script>
    <script>
        // Función para mostrar notificación
        function showNotification(message, type) {
            const container = document.getElementById('notificationContainer');
            if (!container) return;

            const notification = document.createElement('div');
            notification.className = `notification notification-${type}`;
            notification.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'}"></i>
                <span>${message}</span>
            `;

            container.appendChild(notification);

            // Remover después de 4 segundos
            setTimeout(() => {
                notification.style.animation = 'slideOut 0.3s ease-out';
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.parentNode.removeChild(notification);
                    }
                }, 300);
            }, 4000);
        }

        // AÑADIR AL CARRITO CON AJAX
        function addToCartAjax() {
            const quantity = document.getElementById('quantity').value;
            const maxStock = <?php echo $producto['stock']; ?>;
            const productId = <?php echo $producto['id_producto']; ?>;
            const btn = document.getElementById('addToCartBtn');
            const originalText = btn.innerHTML;

            // Validar stock
            if (parseInt(quantity) > maxStock) {
                showNotification('No hay suficiente stock. Disponible: ' + maxStock, 'error');
                return false;
            }

            // Mostrar estado de carga
            btn.classList.add('btn-loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Añadiendo...';

            // Crear datos del formulario
            const formData = new FormData();
            formData.append('action', 'add_to_cart');
            formData.append('quantity', quantity);
            formData.append('ajax', 'true');

            // Enviar por AJAX
            fetch('search-products-product.php?id=<?php echo $id; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    // Restaurar botón
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                    btn.innerHTML = originalText;

                    if (data.success) {
                        showNotification(data.message, 'success');

                        // Actualizar contador del carrito si existe en el header
                        updateCartCount(data.cart_count);

                        // Opcional: Mostrar un pequeño efecto visual
                        btn.style.backgroundColor = '#218838';
                        setTimeout(() => {
                            btn.style.backgroundColor = '';
                        }, 500);
                    } else {
                        showNotification(data.message, 'error');
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al añadir al carrito', 'error');
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });

            return false;
        }

        // COMPRAR AHORA CON AJAX (primero añade al carrito, luego redirige)
        function buyNowAjax() {
            const quantity = document.getElementById('quantity').value;
            const maxStock = <?php echo $producto['stock']; ?>;
            const btn = document.getElementById('buyNowBtn');
            const originalText = btn.innerHTML;

            // Validar stock
            if (parseInt(quantity) > maxStock) {
                showNotification('No hay suficiente stock. Disponible: ' + maxStock, 'error');
                return false;
            }

            // Mostrar estado de carga
            btn.classList.add('btn-loading');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';

            // Crear datos del formulario
            const formData = new FormData();
            formData.append('action', 'buy_now');
            formData.append('quantity', quantity);
            formData.append('ajax', 'true');

            // Enviar por AJAX
            fetch('search-products-product.php?id=<?php echo $id; ?>', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // Redirigir al checkout después de añadir al carrito
                        setTimeout(() => {
                            window.location.href = '../CONTROLLERS/checkout.php';
                        }, 500);
                    } else {
                        showNotification(data.message, 'error');
                        btn.classList.remove('btn-loading');
                        btn.disabled = false;
                        btn.innerHTML = originalText;
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    showNotification('Error al procesar la compra', 'error');
                    btn.classList.remove('btn-loading');
                    btn.disabled = false;
                    btn.innerHTML = originalText;
                });

            return false;
        }

        // Función para actualizar el contador del carrito en el header
        function updateCartCount(count) {
            // Buscar elementos que puedan mostrar el contador
            const cartBadge = document.querySelector('.cart-count-badge');
            const cartIcon = document.querySelector('.icons-header a');

            if (cartBadge) {
                cartBadge.textContent = count;
            }

            // Si no hay badge, podemos añadir uno dinámicamente
            if (cartIcon && !cartBadge) {
                let badge = cartIcon.querySelector('span.badge');
                if (!badge) {
                    badge = document.createElement('span');
                    badge.className = 'cart-count-badge';
                    badge.style.cssText = `
                        position: absolute;
                        top: -5px;
                        right: -5px;
                        background: #e74c3c;
                        color: white;
                        font-size: 12px;
                        font-weight: bold;
                        width: 20px;
                        height: 20px;
                        border-radius: 50%;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                    `;
                    cartIcon.style.position = 'relative';
                    cartIcon.appendChild(badge);
                }
                badge.textContent = count;
            }
        }

        // Controlar cantidad (tus funciones existentes)
        function changeQuantity(change) {
            const input = document.getElementById('quantity');
            const current = parseInt(input.value);
            const max = parseInt(input.getAttribute('max'));
            const min = parseInt(input.getAttribute('min'));

            let newValue = current + change;

            if (newValue < min) newValue = min;
            if (newValue > max) newValue = max;

            input.value = newValue;
        }

        // Validar cantidad al cambiar manualmente
        document.getElementById('quantity').addEventListener('change', function() {
            const max = parseInt(this.getAttribute('max'));
            const min = parseInt(this.getAttribute('min'));
            let value = parseInt(this.value);

            if (isNaN(value)) value = min;
            if (value < min) value = min;
            if (value > max) value = max;

            this.value = value;
        });

        // Auto-ocultar mensajes de éxito del servidor (si los hay)
        <?php if ($cart_message): ?>
            showNotification('<?php echo addslashes($cart_message); ?>', 'success');
        <?php endif; ?>
    </script>
    <?php include '../TEMPLATES/footer.php' ?>
</body>

</html>