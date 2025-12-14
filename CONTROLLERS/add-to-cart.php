<?php
// add-to-cart.php
session_start();
require_once "../shortCuts/connect.php";

// Verificar si se recibieron datos POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['product_id']) && isset($_POST['quantity'])) {
        $product_id = (int)$_POST['product_id'];
        $quantity = (int)$_POST['quantity'];
        
        // Validar que la cantidad sea positiva
        if ($quantity <= 0) {
            $_SESSION['cart_message'] = 'La cantidad debe ser mayor a 0';
            header("Location: " . $_SERVER['HTTP_REFERER']);
            exit;
        }
        
        // Obtener información del producto de la base de datos
        $sql = "SELECT nombre, precio, imagen_url, cloudinary_public_id, stock FROM producto WHERE id_producto = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $producto = $result->fetch_assoc();
            
            // Verificar stock
            if ($producto['stock'] < $quantity) {
                $_SESSION['cart_message'] = 'No hay suficiente stock disponible';
                header("Location: " . $_SERVER['HTTP_REFERER']);
                exit;
            }
            
            // Incluir funciones del carrito
            require_once "cart-functions.php";
            
            // Determinar la URL de la imagen
            $image_url = '';
            if (!empty($producto['imagen_url'])) {
                $image_url = $producto['imagen_url'];
            } elseif (!empty($producto['imagen'])) {
                $image_url = '../SOURCES/PRODUCTOS/' . $producto['imagen'];
            }
            
            // Añadir al carrito usando la función
            addToCart(
                $product_id,
                $producto['nombre'],
                $producto['precio'],
                $quantity,
                $image_url
            );
            
            // Mensaje simple de éxito (NO JSON)
            $_SESSION['cart_message'] = '✓ Producto añadido al carrito correctamente';
            
        } else {
            $_SESSION['cart_message'] = 'Producto no encontrado';
        }
        
        // Redirigir de vuelta a la página anterior
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
        
    } else {
        $_SESSION['cart_message'] = 'Datos del producto incompletos';
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }
} else {
    // Si no es POST, redirigir al inicio
    header("Location: ../home.php");
    exit;
}
?>