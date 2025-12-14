<?php
// CONTROLLERS/cart.php
session_start();
require_once "../shortCuts/connect.php";

// Incluir funciones del carrito desde includes/
require_once "cart-functions.php";

// Mensajes del carrito
$message = '';
$message_type = '';

if (isset($_SESSION['cart_message'])) {
    if (is_array($_SESSION['cart_message'])) {
        if (isset($_SESSION['cart_message'][0]['error'])) {
            $message = 'Error en el carrito: ' . $_SESSION['cart_message'][0]['error'];
            $message_type = 'error';
        } elseif (isset($_SESSION['cart_message'][0]['product_name'])) {
            $message = 'Error de stock para: ' . $_SESSION['cart_message'][0]['product_name'];
            $message_type = 'error';
        } else {
            $message = 'Mensaje del carrito: ' . json_encode($_SESSION['cart_message']);
            $message_type = 'error';
        }
    } else {
        $message = $_SESSION['cart_message'];
        $message_type = 'success';
    }
    
    unset($_SESSION['cart_message']);
}

// Procesar actualización del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        updateCartQuantity($product_id, (int)$quantity);
    }
    $_SESSION['cart_message'] = 'Carrito actualizado correctamente';
    header("Location: cart.php");
    exit;
}

// Procesar eliminación
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    removeFromCart($product_id);
    $_SESSION['cart_message'] = 'Producto eliminado del carrito';
    header("Location: cart.php");
    exit;
}

// Procesar vaciar carrito
if (isset($_GET['clear'])) {
    clearCart();
    $_SESSION['cart_message'] = 'Carrito vaciado correctamente';
    header("Location: cart.php");
    exit;
}

// Obtener items del carrito
$cart_items = getCartItems();

// Calcular totales
$cart_count = getCartCount();
$cart_total = getCartTotal();
$envio = 10000;
$iva = $cart_total * 0.19;
$total = $cart_total + $envio + $iva;
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Carrito de Compras - HERMES</title>
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link href="https://fonts.googleapis.com/css2?family=Anton&family=Roboto+Condensed:wght@300;400;700&display=swap" rel="stylesheet">
    <style>
        /* ===== HEADER - CSS EXACTAMENTE DEL HOME ===== */
        /* Logo */
        #logo-hermes-home {
            width: auto;
            height: 100%;
            text-align: center;
            display: flex;
            align-items: center;
        }

        .logo-text {
            font-family: 'Anton', sans-serif;
            font-optical-sizing: auto;
            font-size: 2.5rem;
            color: #fff8f1;
            margin: 1rem auto;
            letter-spacing: 5px;
            cursor: pointer;
        }

        /* Barra de navegacion y demas */
        nav {
            width: auto;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-grow: 1;
        }

        nav>ul {
            list-style: none;
            display: flex;
            align-items: center;
            height: 100%;
            gap: 1rem;
        }

        nav ul li {
            position: relative;
        }

        nav ul li span {
            font-size: 1rem;
            color: #fff8f1;
            cursor: pointer;
            text-align: center;
            width: 5rem;
            padding: 1rem 1rem 1rem 1rem;
            border-radius: 3rem;
            transition: background-color 0.7s ease, transform 0.7s ease;
        }

        nav ul li span:hover {
            background-color: #fff8f1;
            transition: 0.7s;
            color: #461d01;
        }

        /* Estilos de la barra de Busqueda */
        #input-search-product {
            height: 40px;
            border: 2px solid #461d01;
            border-radius: 25px;
            padding-left: 10px;
            font-family: 'Roboto Condensed', sans-serif;
            font-weight: 100;
            min-width: 400px;
            max-width: 700px;
            position: relative;
            background-color: white;
        }

        #results-container {
            position: absolute;
            top: 45px;
            left: 0;
            min-width: 400px;
            max-width: 700px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: none;
            max-height: 250px;
            overflow-y: auto;
            padding: 10px;
        }

        .result-item {
            padding: 20px 20px;
            cursor: pointer;
            background: #fff;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
            border-radius: 10px;
        }

        .result-item:hover {
            background: #461d01;
            color: #fff8f1;
        }

        .input-search-product-li {
            position: relative;
        }

        .button-search {
            right: 10px;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            position: absolute;
            color: #727272;
            top: 50%;
            transform: translateY(-50%);
        }

        /* Iconos a la derecha del header */
        .icons-header {
            width: auto;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 1rem;
        }

        .account-header {
            height: 100%;
            display: flex;
            justify-content: center;
            margin-right: 20px;
            gap: 1rem;
            align-items: center;
        }

        .icons-header span {
            cursor: pointer;
            width: 20px;
        }

        .icons-header span:hover {
            filter: brightness(0) saturate(100%) invert(60%) sepia(30%) saturate(500%) hue-rotate(160deg);
            transition: .5s;
        }

        .account-header span.sisu-buttons {
            width: 80px;
            filter: none;
            background-color: #ffb000;
            text-align: center;
            padding: 8px 15px;
            border-radius: 20px;
            transition: all .5s ease;
            color: #461d01;
            font-weight: bold;
            text-decoration: none;
            display: inline-block;
        }

        .account-header span.sisu-buttons:hover {
            background: linear-gradient(135deg, #0D47A1, #0097b2);
            box-shadow: 1px 1px 40px 1px #12c0df;
            color: #fff8f1;
            transition: all .1s ease;
        }

        header a {
            text-decoration: none;
            color: #461d01;
        }

        /* Menu desplegable de las categorias */
        .menu-categoria {
            width: 10rem;
            position: absolute;
            top: 50px;
            left: -30px;
            display: block;
            background-color: #fff8f1;
            overflow: scroll;
            overflow-x: hidden;
            min-height: 300px;
            max-height: 400px;
            border-radius: 40px;
            z-index: 1;
            box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.514);
            transition: all .5s ease;
            opacity: 0;
            pointer-events: none;
        }

        .menu-categoria ul {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
            list-style: none;
            font-size: .8rem;
            text-align: center;
            padding: 1rem 0 1rem 0;
            align-items: center;
        }

        /* Menu desplegable perfil */
        .perfil-menu {
            position: relative;
        }

        .perfil-btn {
            background: none;
            border: none;
            color: #fff8f1;
            font-size: 1rem;
            cursor: pointer;
            padding: 1rem;
            font-weight: bold;
        }

        .dropdown-content {
            position: absolute;
            top: 100%;
            right: 0;
            background-color: #fff8f1;
            min-width: 160px;
            box-shadow: 0 8px 16px 0 rgba(0,0,0,0.2);
            z-index: 1;
            border-radius: 10px;
            opacity: 0;
            pointer-events: none;
            transition: opacity 0.3s;
        }

        .perfil-menu:hover .dropdown-content {
            opacity: 1;
            pointer-events: all;
        }

        .dropdown-content a {
            color: #461d01;
            padding: 12px 16px;
            text-decoration: none;
            display: block;
            transition: background-color 0.3s;
        }

        .dropdown-content a:hover {
            background-color: #ffb000;
            color: #fff8f1;
        }

        /* ===== ESTILOS GENERALES ===== */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Roboto Condensed', sans-serif;
            background-color: #f5f5f5;
            color: #333;
        }

        /* ===== HEADER LAYOUT ===== */
        header {
            background: linear-gradient(135deg, #461d01 0%, #8B4513 100%);
            padding: 10px 40px;
            position: sticky;
            top: 0;
            z-index: 1000;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .bottom {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        /* ===== CONTENIDO DEL CARRITO ===== */
        .container {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .cart-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            padding: 25px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            border: 2px solid #461d01;
        }

        .cart-title {
            font-family: 'Anton', sans-serif;
            font-size: 2rem;
            color: #461d01;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .cart-title i {
            color: #ffb000;
            font-size: 1.8rem;
        }

        .cart-count {
            background: linear-gradient(135deg, #ffb000, #ff9800);
            color: white;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: bold;
            font-size: 1.1rem;
            display: flex;
            align-items: center;
            gap: 10px;
            box-shadow: 0 4px 10px rgba(255, 176, 0, 0.3);
        }

        /* Mensajes */
        .message {
            padding: 15px 20px;
            margin-bottom: 25px;
            border-radius: 10px;
            text-align: center;
            font-weight: bold;
            font-size: 1rem;
        }

        .message.success {
            background-color: #d4edda;
            color: #155724;
            border: 2px solid #c3e6cb;
        }

        .message.error {
            background-color: #f8d7da;
            color: #721c24;
            border: 2px solid #f5c6cb;
        }

        /* Layout del carrito */
        .cart-container {
            display: flex;
            gap: 30px;
            margin-top: 20px;
        }

        .cart-items {
            flex: 3;
            min-width: 300px;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }

        .cart-summary {
            flex: 1;
            min-width: 300px;
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }

        /* Carrito vacío */
        .empty-cart {
            text-align: center;
            padding: 60px 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            margin: 20px 0;
        }

        .empty-cart i {
            font-size: 5rem;
            color: #ffb000;
            margin-bottom: 20px;
            opacity: 0.7;
        }

        .empty-cart h2 {
            color: #461d01;
            margin-bottom: 15px;
            font-size: 2rem;
            font-family: 'Anton', sans-serif;
        }

        .empty-cart p {
            color: #666;
            margin-bottom: 30px;
            font-size: 1.1rem;
        }

        /* Tabla de productos */
        .cart-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }

        .cart-table th {
            background-color: #fff8f1;
            padding: 18px 15px;
            text-align: left;
            color: #461d01;
            font-weight: bold;
            font-size: 1rem;
            border-bottom: 3px solid #ffb000;
        }

        .cart-table td {
            padding: 20px 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }

        .cart-table tr:hover td {
            background-color: #fff8f1;
        }

        .product-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .product-image {
            width: 100px;
            height: 100px;
            object-fit: cover;
            border-radius: 10px;
            border: 2px solid #ffb000;
            background-color: #fff8f1;
        }

        .product-name {
            font-weight: 600;
            color: #461d01;
            margin-bottom: 5px;
            font-size: 1.1rem;
        }

        .product-price {
            color: #ffb000;
            font-weight: bold;
            font-size: 1.2rem;
        }

        /* Control de cantidad */
        .quantity-control {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .quantity-input {
            width: 80px;
            padding: 12px;
            border: 2px solid #461d01;
            border-radius: 8px;
            text-align: center;
            font-size: 1rem;
            font-weight: bold;
            color: #461d01;
        }

        .quantity-input:focus {
            outline: none;
            border-color: #ffb000;
        }

        /* Botones */
        .btn-remove {
            background-color: #dc3545;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: background-color 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            text-decoration: none;
            font-size: 0.9rem;
        }

        .btn-remove:hover {
            background-color: #c82333;
        }

        /* Acciones del carrito */
        .cart-actions {
            display: flex;
            gap: 15px;
            margin-top: 25px;
        }

        .update-btn {
            background-color: #461d01;
            color: white;
            border: none;
            padding: 16px 30px;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            font-size: 1rem;
            transition: background-color 0.3s;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .update-btn:hover {
            background-color: #8B4513;
        }

        .clear-btn {
            background-color: #6c757d;
            color: white;
            text-decoration: none;
            padding: 16px 30px;
            border-radius: 8px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            font-weight: bold;
            flex: 1;
            transition: background-color 0.3s;
        }

        .clear-btn:hover {
            background-color: #545b62;
        }

        /* Resumen del pedido */
        .summary-title {
            color: #461d01;
            font-size: 1.4rem;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #ffb000;
            font-family: 'Anton', sans-serif;
        }

        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 1px solid #eee;
        }

        .summary-label {
            color: #666;
        }

        .summary-value {
            color: #461d01;
            font-weight: bold;
        }

        .summary-total {
            display: flex;
            justify-content: space-between;
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid #461d01;
            font-size: 1.3rem;
            font-weight: bold;
            color: #461d01;
        }

        /* Botones checkout */
        .checkout-btn {
            background-color: #28a745;
            color: white;
            padding: 18px;
            text-align: center;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
            transition: background-color 0.3s;
            display: block;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .checkout-btn:hover {
            background-color: #218838;
        }

        .btn-continue {
            display: block;
            text-align: center;
            background-color: #461d01;
            color: white;
            padding: 15px;
            text-decoration: none;
            border-radius: 8px;
            font-weight: bold;
            transition: background-color 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .btn-continue:hover {
            background-color: #8B4513;
        }

        .btn-shopping {
            display: inline-block;
            margin-top: 25px;
            padding: 12px 30px;
            background: #ffb000;
            color: #461d01;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: background-color 0.3s;
            font-size: 1rem;
        }

        .btn-shopping:hover {
            background-color: #ff9800;
            color: white;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .cart-container {
                flex-direction: column;
            }
            
            .cart-summary {
                position: static;
            }
        }

        @media (max-width: 768px) {
            header {
                padding: 10px 20px;
            }
            
            .top {
                flex-direction: column;
                gap: 15px;
            }
            
            #input-search-product {
                min-width: 300px;
                max-width: 400px;
            }
            
            .bottom {
                flex-direction: column;
                gap: 15px;
            }
            
            nav ul {
                flex-direction: column;
                align-items: center;
            }
            
            .account-header {
                margin-right: 0;
            }
            
            .cart-table {
                display: block;
                overflow-x: auto;
            }
            
            .cart-table th:nth-child(2),
            .cart-table td:nth-child(2) {
                display: none;
            }
            
            .product-info {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
            
            .cart-actions {
                flex-direction: column;
            }
            
            .update-btn,
            .clear-btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .container {
                padding: 0 15px;
            }
            
            .cart-title {
                font-size: 1.6rem;
            }
            
            .cart-count {
                padding: 10px 20px;
                font-size: 1rem;
            }
            
            #input-search-product {
                min-width: 250px;
                max-width: 300px;
            }
            
            .logo-text {
                font-size: 2rem;
            }
        }

        /* Clases adicionales para el header */
        .sisu-buttons {
            background-color: #ffb000 !important;
            color: #461d01 !important;
            padding: 8px 15px !important;
            border-radius: 20px !important;
            text-decoration: none !important;
            font-weight: bold !important;
        }
        
        .perfil-btn {
            color: #fff8f1 !important;
            background: none !important;
            border: none !important;
            font-size: 1rem !important;
            cursor: pointer !important;
            font-weight: bold !important;
        }
    </style>
</head>

<body>
    <!-- HEADER - EXACTAMENTE COMO EL HOME -->
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <h1 class="logo-text">HERMES</h1>
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
                                autocomplete="off">
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
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <li><span id="venderPage">Vender</span></li>
                    <?php endif; ?>
                    <li>
                        <span id="ayuda-listado">Ayuda</span>
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
                        <button class="perfil-btn"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="../CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.php"><span class="sisu-buttons">Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons">Sign Up</span></a>
                <?php endif; ?>
            </div>
            
            <div class="icons-header">
                <span><img src="../SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <a href="cart.php">
                    <img src="../SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart">
                </a>
            </div>
        </div>
    </header>

    <!-- CONTENIDO DEL CARRITO -->
    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title"><i class="fas fa-shopping-cart"></i> Carrito de Compras</h1>
            <div class="cart-count">
                <i class="fas fa-box"></i> <?php echo $cart_count; ?> productos
            </div>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $message_type; ?>">
                <?php echo htmlspecialchars($message, ENT_QUOTES, 'UTF-8'); ?>
            </div>
        <?php endif; ?>

        <?php if (empty($cart_items)): ?>
            <div class="empty-cart">
                <i class="fas fa-shopping-cart"></i>
                <h2>Tu carrito está vacío</h2>
                <p>Agrega productos para comenzar a comprar</p>
                <a href="search-products.php" class="btn-shopping">
                    <i class="fas fa-shopping-bag"></i> Continuar Comprando
                </a>
            </div>
        <?php else: ?>
            <form method="POST" action="">
                <div class="cart-container">
                    <div class="cart-items">
                        <table class="cart-table">
                            <thead>
                                <tr>
                                    <th>Producto</th>
                                    <th>Precio</th>
                                    <th>Cantidad</th>
                                    <th>Subtotal</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($cart_items as $product_id => $item): ?>
                                    <?php 
                                    $product_name = isset($item['name']) ? (string)$item['name'] : 'Producto sin nombre';
                                    $price = isset($item['price']) ? (float)$item['price'] : 0;
                                    $quantity = isset($item['quantity']) ? (int)$item['quantity'] : 1;
                                    $subtotal = isset($item['subtotal']) ? (float)$item['subtotal'] : 0;
                                    $image_url = isset($item['image_url']) ? (string)$item['image_url'] : '../SOURCES/PRODUCTOS/default.png';
                                    ?>
                                    <tr>
                                        <td>
                                            <div class="product-info">
                                                <img src="<?php echo htmlspecialchars($image_url, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     alt="<?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?>" 
                                                     class="product-image">
                                                <div>
                                                    <div class="product-name"><?php echo htmlspecialchars($product_name, ENT_QUOTES, 'UTF-8'); ?></div>
                                                    <div class="product-price">$<?php echo number_format($price, 0, ',', '.'); ?></div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="product-price">
                                            $<?php echo number_format($price, 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <div class="quantity-control">
                                                <input type="number" 
                                                       name="quantity[<?php echo (int)$product_id; ?>]" 
                                                       value="<?php echo (int)$quantity; ?>" 
                                                       min="1" 
                                                       max="99"
                                                       class="quantity-input">
                                            </div>
                                        </td>
                                        <td class="product-price">
                                            $<?php echo number_format($subtotal, 0, ',', '.'); ?>
                                        </td>
                                        <td>
                                            <a href="?remove=<?php echo (int)$product_id; ?>" 
                                               class="btn-remove" 
                                               onclick="return confirm('¿Eliminar producto del carrito?')">
                                                <i class="fas fa-trash"></i> Eliminar
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                        
                        <div class="cart-actions">
                            <button type="submit" name="update_cart" class="update-btn">
                                <i class="fas fa-sync-alt"></i> Actualizar Carrito
                            </button>
                            <a href="?clear=1" class="clear-btn" onclick="return confirm('¿Vaciar todo el carrito?')">
                                <i class="fas fa-trash-alt"></i> Vaciar Carrito
                            </a>
                        </div>
                    </div>
                    
                    <div class="cart-summary">
                        <h3 class="summary-title">Resumen del Pedido</h3>
                        
                        <div class="summary-row">
                            <span class="summary-label">Subtotal:</span>
                            <span class="summary-value">$<?php echo number_format($cart_total, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">Envío:</span>
                            <span class="summary-value">$<?php echo number_format($envio, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span class="summary-label">IVA (19%):</span>
                            <span class="summary-value">$<?php echo number_format($iva, 0, ',', '.'); ?></span>
                        </div>
                        
                        <div class="summary-total">
                            <span>Total:</span>
                            <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
                        </div>
                        
                        <a href="checkout.php" class="checkout-btn">
                            <i class="fas fa-lock"></i> Proceder al Pago
                        </a>
                        
                        <a href="search-products.php" class="btn-continue">
                            <i class="fas fa-arrow-left"></i> Seguir Comprando
                        </a>
                    </div>
                </div>
            </form>
        <?php endif; ?>
    </div>

    <script>
        // Validación de cantidad en tiempo real
        document.querySelectorAll('.quantity-input').forEach(input => {
            input.addEventListener('change', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > 99) this.value = 99;
            });
            
            input.addEventListener('input', function() {
                if (this.value < 1) this.value = 1;
                if (this.value > 99) this.value = 99;
            });
        });
        
        // Confirmación antes de eliminar o vaciar
        document.querySelectorAll('.btn-remove, .clear-btn').forEach(btn => {
            btn.addEventListener('click', function(e) {
                const message = this.classList.contains('btn-remove') 
                    ? '¿Eliminar producto del carrito?' 
                    : '¿Vaciar todo el carrito?';
                
                if (!confirm(message)) {
                    e.preventDefault();
                }
            });
        });
        
        // Menu de ayuda
        const ayudaListado = document.getElementById('ayuda-listado');
        const menuAyuda = document.getElementById('menu-ayuda');
        
        if (ayudaListado && menuAyuda) {
            ayudaListado.addEventListener('mouseenter', () => {
                menuAyuda.style.opacity = '1';
                menuAyuda.style.pointerEvents = 'all';
            });
            
            ayudaListado.addEventListener('mouseleave', () => {
                menuAyuda.style.opacity = '0';
                menuAyuda.style.pointerEvents = 'none';
            });
            
            menuAyuda.addEventListener('mouseenter', () => {
                menuAyuda.style.opacity = '1';
                menuAyuda.style.pointerEvents = 'all';
            });
            
            menuAyuda.addEventListener('mouseleave', () => {
                menuAyuda.style.opacity = '0';
                menuAyuda.style.pointerEvents = 'none';
            });
        }
        
        // Menu perfil
        const perfilMenu = document.querySelector('.perfil-menu');
        const dropdownContent = document.querySelector('.dropdown-content');
        
        if (perfilMenu && dropdownContent) {
            perfilMenu.addEventListener('mouseenter', () => {
                dropdownContent.style.opacity = '1';
                dropdownContent.style.pointerEvents = 'all';
            });
            
            perfilMenu.addEventListener('mouseleave', () => {
                dropdownContent.style.opacity = '0';
                dropdownContent.style.pointerEvents = 'none';
            });
            
            dropdownContent.addEventListener('mouseenter', () => {
                dropdownContent.style.opacity = '1';
                dropdownContent.style.pointerEvents = 'all';
            });
            
            dropdownContent.addEventListener('mouseleave', () => {
                dropdownContent.style.opacity = '0';
                dropdownContent.style.pointerEvents = 'none';
            });
        }
    </script>
</body>

</html>