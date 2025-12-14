<?php
// CONTROLLERS/cart.php
session_start();
require_once "../shortCuts/connect.php";

// Incluir funciones del carrito desde includes/
require_once "cart-functions.php";
// Verificar que sea una solicitud POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../home.php");
    exit;
}

// Obtener datos del producto
$product_id = isset($_POST['product_id']) ? (int)$_POST['product_id'] : 0;
$quantity = isset($_POST['quantity']) ? (int)$_POST['quantity'] : 1;

if ($product_id <= 0 || $quantity <= 0) {
    $_SESSION['cart_message'] = [
        'type' => 'error',
        'message' => 'Datos inválidos'
    ];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../home.php'));
    exit;
}

// Obtener información del producto
$sql = "SELECT nombre, precio, imagen_url, cloudinary_public_id, stock FROM producto WHERE id_producto = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $product_id);
$stmt->execute();
$producto = $stmt->get_result()->fetch_assoc();

if (!$producto) {
    $_SESSION['cart_message'] = [
        'type' => 'error',
        'message' => 'Producto no encontrado'
    ];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../home.php'));
    exit;
}

// Verificar stock
if ($producto['stock'] < $quantity) {
    $_SESSION['cart_message'] = [
        'type' => 'error',
        'message' => 'Stock insuficiente. Disponible: ' . $producto['stock']
    ];
    header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../home.php'));
    exit;
}

// Obtener URL de la imagen
$image_url = !empty($producto['imagen_url']) ? $producto['imagen_url'] : (!empty($producto['imagen']) ? '../SOURCES/PRODUCTOS/' . $producto['imagen'] : null);

// Añadir al carrito
addToCart(
    $product_id,
    $producto['nombre'],
    $producto['precio'],
    $quantity,
    $image_url
);

// Mensaje de éxito
$_SESSION['cart_message'] = [
    'type' => 'success',
    'message' => '✓ Producto añadido al carrito'
];

// Redirigir de vuelta
header("Location: " . ($_SERVER['HTTP_REFERER'] ?? '../home.php'));
exit;
