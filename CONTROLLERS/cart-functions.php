<?php
// includes/cart-functions.php
// Este archivo debe crearse MANUALMENTE en la carpeta includes/

// Iniciar sesión si no está iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Obtiene la cantidad total de productos en el carrito
 * @return int Total de productos (suma de cantidades)
 */
function getCartCount()
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['quantity'];
    }
    return $total;
}

/**
 * Obtiene el subtotal del carrito (suma de precios × cantidad)
 * @return float Subtotal del carrito
 */
function getCartTotal()
{
    if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
        return 0;
    }

    $total = 0;
    foreach ($_SESSION['cart'] as $item) {
        $total += $item['price'] * $item['quantity'];
    }
    return $total;
}

/**
 * Añade un producto al carrito
 * @param int $product_id ID del producto
 * @param string $product_name Nombre del producto
 * @param float $price Precio unitario
 * @param int $quantity Cantidad (por defecto 1)
 * @param string|null $image_url URL de la imagen (opcional)
 * @return bool true si se añadió correctamente
 */
function addToCart($product_id, $product_name, $price, $quantity = 1, $image_url = null)
{
    // Inicializar carrito si no existe
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Verificar si el producto ya está en el carrito
    if (isset($_SESSION['cart'][$product_id])) {
        // Incrementar cantidad
        $_SESSION['cart'][$product_id]['quantity'] += $quantity;
    } else {
        // Añadir nuevo producto
        $_SESSION['cart'][$product_id] = [
            'product_id' => $product_id,
            'name' => $product_name,
            'price' => $price,
            'quantity' => $quantity,
            'image_url' => $image_url,
            'subtotal' => $price * $quantity
        ];
    }

    // Actualizar subtotal
    $_SESSION['cart'][$product_id]['subtotal'] =
        $_SESSION['cart'][$product_id]['price'] * $_SESSION['cart'][$product_id]['quantity'];

    return true;
}

/**
 * Elimina un producto del carrito
 * @param int $product_id ID del producto a eliminar
 * @return bool true si se eliminó, false si no existía
 */
function removeFromCart($product_id)
{
    if (isset($_SESSION['cart'][$product_id])) {
        unset($_SESSION['cart'][$product_id]);
        return true;
    }
    return false;
}

/**
 * Actualiza la cantidad de un producto en el carrito
 * @param int $product_id ID del producto
 * @param int $quantity Nueva cantidad (si es 0 o menos, elimina el producto)
 * @return bool true si se actualizó correctamente
 */
function updateCartQuantity($product_id, $quantity)
{
    if (isset($_SESSION['cart'][$product_id])) {
        if ($quantity <= 0) {
            // Si la cantidad es 0 o negativa, eliminar producto
            removeFromCart($product_id);
        } else {
            // Actualizar cantidad y subtotal
            $_SESSION['cart'][$product_id]['quantity'] = $quantity;
            $_SESSION['cart'][$product_id]['subtotal'] =
                $_SESSION['cart'][$product_id]['price'] * $quantity;
        }
        return true;
    }
    return false;
}

/**
 * Vacía completamente el carrito
 * @return bool true siempre
 */
function clearCart()
{
    unset($_SESSION['cart']);
    return true;
}

/**
 * Obtiene todos los productos del carrito
 * @return array Array con los productos del carrito
 */
function getCartItems()
{
    return isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
}

/**
 * Verifica si un producto está en el carrito
 * @param int $product_id ID del producto
 * @return bool true si está en el carrito
 */
function isInCart($product_id)
{
    return isset($_SESSION['cart'][$product_id]);
}

/**
 * Obtiene la cantidad de un producto específico en el carrito
 * @param int $product_id ID del producto
 * @return int Cantidad en el carrito (0 si no existe)
 */
function getProductQuantityInCart($product_id)
{
    if (isset($_SESSION['cart'][$product_id])) {
        return $_SESSION['cart'][$product_id]['quantity'];
    }
    return 0;
}

/**
 * Calcula el total con impuestos y envío
 * @param float $shipping Costo de envío (por defecto 10000)
 * @param float $tax_rate Tasa de impuesto (por defecto 0.19 = 19%)
 * @return array Array con los totales desglosados
 */
function calculateOrderTotals($shipping = 10000, $tax_rate = 0.19)
{
    $subtotal = getCartTotal();
    $tax = $subtotal * $tax_rate;
    $total = $subtotal + $shipping + $tax;

    return [
        'subtotal' => $subtotal,
        'shipping' => $shipping,
        'tax' => $tax,
        'total' => $total
    ];
}

/**
 * Obtiene los productos del carrito agrupados por vendedor
 * @param mysqli $connect Conexión a la base de datos
 * @return array Productos agrupados por vendedor
 */
function getCartItemsGroupedBySeller($connect)
{
    if (empty($_SESSION['cart'])) {
        return [];
    }

    $grouped = [];

    foreach ($_SESSION['cart'] as $product_id => $item) {
        // Obtener información del producto y vendedor
        $sql = "SELECT p.*, v.id_vendedor, v.nombre_empresa 
                FROM producto p 
                LEFT JOIN vendedor v ON p.id_vendedor = v.id_vendedor 
                WHERE p.id_producto = ?";

        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        if ($producto) {
            $seller_id = $producto['id_vendedor'];

            if (!isset($grouped[$seller_id])) {
                $grouped[$seller_id] = [
                    'seller_id' => $seller_id,
                    'seller_name' => $producto['nombre_empresa'],
                    'items' => [],
                    'subtotal' => 0
                ];
            }

            $grouped[$seller_id]['items'][] = [
                'cart_item' => $item,
                'product_info' => $producto
            ];
            $grouped[$seller_id]['subtotal'] += $item['subtotal'];
        }
    }

    return $grouped;
}

/**
 * Valida el stock de los productos en el carrito
 * @param mysqli $connect Conexión a la base de datos
 * @return array|bool Array con errores de stock, o true si todo está bien
 */
function validateCartStock($connect)
{
    if (empty($_SESSION['cart'])) {
        return true;
    }

    $errors = [];

    foreach ($_SESSION['cart'] as $product_id => $item) {
        $sql = "SELECT nombre, stock FROM producto WHERE id_producto = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();

        if ($producto) {
            if ($producto['stock'] < $item['quantity']) {
                $errors[] = [
                    'product_id' => $product_id,
                    'product_name' => $producto['nombre'],
                    'requested' => $item['quantity'],
                    'available' => $producto['stock']
                ];
            }
        } else {
            $errors[] = [
                'product_id' => $product_id,
                'error' => 'Producto no encontrado'
            ];
        }
    }

    return empty($errors) ? true : $errors;
}

/**
 * Guarda el carrito en la base de datos (para usuarios registrados)
 * @param mysqli $connect Conexión a la base de datos
 * @param int $user_id ID del usuario
 * @return bool true si se guardó correctamente
 */
function saveCartToDatabase($connect, $user_id)
{
    if (empty($_SESSION['cart'])) {
        return true;
    }

    // Eliminar carrito anterior del usuario
    $sql_delete = "DELETE FROM carrito_guardado WHERE id_usuario = ?";
    $stmt_delete = $connect->prepare($sql_delete);
    $stmt_delete->bind_param("i", $user_id);
    $stmt_delete->execute();

    // Guardar cada producto
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $sql_insert = "INSERT INTO carrito_guardado (id_usuario, id_producto, cantidad) VALUES (?, ?, ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        $stmt_insert->bind_param("iii", $user_id, $product_id, $item['quantity']);
        $stmt_insert->execute();
    }

    return true;
}

/**
 * Carga el carrito desde la base de datos (para usuarios registrados)
 * @param mysqli $connect Conexión a la base de datos
 * @param int $user_id ID del usuario
 * @return bool true si se cargó correctamente
 */
function loadCartFromDatabase($connect, $user_id)
{
    $sql = "SELECT c.*, p.nombre, p.precio, p.imagen_url, p.imagen 
            FROM carrito_guardado c
            JOIN producto p ON c.id_producto = p.id_producto
            WHERE c.id_usuario = ?";

    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $_SESSION['cart'] = [];

        while ($row = $result->fetch_assoc()) {
            $image_url = !empty($row['imagen_url']) ? $row['imagen_url'] : (!empty($row['imagen']) ? '../SOURCES/PRODUCTOS/' . $row['imagen'] : null);

            addToCart(
                $row['id_producto'],
                $row['nombre'],
                $row['precio'],
                $row['cantidad'],
                $image_url
            );
        }

        return true;
    }

    return false;
}
