<?php
// CONTROLLERS/cart.php
session_start();
require_once "../shortCuts/connect.php";

// Incluir funciones del carrito desde includes/
require_once "cart-functions.php";

// Mensajes del carrito
$message = '';
if (isset($_SESSION['cart_message'])) {
    $message = $_SESSION['cart_message'];
    unset($_SESSION['cart_message']);
}

// Procesar actualización del carrito
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $product_id => $quantity) {
        updateCartQuantity($product_id, (int)$quantity);
    }
    header("Location: cart.php");
    exit;
}

// Procesar eliminación
if (isset($_GET['remove'])) {
    $product_id = (int)$_GET['remove'];
    removeFromCart($product_id);
    header("Location: cart.php");
    exit;
}

// Procesar vaciar carrito
if (isset($_GET['clear'])) {
    clearCart();
    header("Location: cart.php");
    exit;
}

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
    <title>Carrito de Compras - HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ESTILOS DEL CARRITO - los mismos que te envié antes */
        /* Te los puedo enviar completos si los necesitas */
    </style>
</head>

<body>
    <!-- Header aquí (tu header normal) -->

    <div class="container">
        <div class="cart-header">
            <h1 class="cart-title"><i class="fas fa-shopping-cart"></i> Carrito de Compras</h1>
            <div class="cart-count">
                <i class="fas fa-box"></i> <?php echo $cart_count; ?> productos
            </div>
        </div>

        <?php if (!empty($_SESSION['cart'])): ?>
            <form method="POST" action="">
                <!-- Contenido del carrito -->
                <div class="cart-actions">
                    <a href="checkout.php" class="checkout-btn">
                        <i class="fas fa-lock"></i> Proceder al Pago
                    </a>
                </div>
            </form>
        <?php endif; ?>
    </div>
</body>

</html>