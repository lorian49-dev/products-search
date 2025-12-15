<?php
// CONTROLLERS/process-order.php - VERSIÓN CORREGIDA
session_start();
require_once dirname(__DIR__) . '/shortCuts/connect.php';

// Activar errores
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Verificar usuario y carrito
if (!isset($_SESSION['usuario_id'])) {
    $_SESSION['checkout_error'] = 'Debes iniciar sesión para continuar';
    header("Location: checkout.php");
    exit;
}

if (empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = 'Tu carrito está vacío';
    header("Location: cart.php");
    exit;
}

// Obtener datos
$id_cliente = $_SESSION['usuario_id'];
$nombre = trim($_POST['nombre'] ?? '');
$apellido = trim($_POST['apellido'] ?? '');
$email = trim($_POST['email'] ?? '');
$telefono = trim($_POST['telefono'] ?? '');
$direccion = trim($_POST['direccion'] ?? '');
$ciudad = trim($_POST['ciudad'] ?? '');
$departamento = trim($_POST['departamento'] ?? '');
$codigo_postal = trim($_POST['codigo_postal'] ?? '');
$metodo_pago = trim($_POST['metodo_pago'] ?? '');
$terminos = isset($_POST['terminos']);

// Validaciones básicas
if (!$terminos) {
    $_SESSION['checkout_error'] = 'Debes aceptar los términos';
    header("Location: checkout.php");
    exit;
}

// Calcular totales (función simple si no existe)
function getCartTotal() {
    $total = 0;
    if (isset($_SESSION['cart'])) {
        foreach ($_SESSION['cart'] as $item) {
            $total += ($item['price'] ?? 0) * ($item['quantity'] ?? 0);
        }
    }
    return $total;
}

$subtotal = getCartTotal();
$envio = 10000;
$iva = $subtotal * 0.19;
$total = $subtotal + $envio + $iva;

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // 1. Verificar billetera si es necesario
    if ($metodo_pago == 'billetera_virtual') {
        $sql_billetera = "SELECT saldo_billetera FROM metodos_pago 
                         WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
        $stmt = $connect->prepare($sql_billetera);
        $stmt->bind_param("i", $id_cliente);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('No tienes billetera virtual');
        }
        
        $billetera = $result->fetch_assoc();
        $saldo_actual = floatval($billetera['saldo_billetera']);
        
        if ($saldo_actual < $total) {
            throw new Exception('Saldo insuficiente en billetera');
        }
        
        // Actualizar saldo
        $nuevo_saldo = $saldo_actual - $total;
        $sql_update = "UPDATE metodos_pago SET saldo_billetera = ? WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
        $stmt2 = $connect->prepare($sql_update);
        $stmt2->bind_param("di", $nuevo_saldo, $id_cliente);
        $stmt2->execute();
        $stmt2->close();
        $stmt->close();
    }
    
    // 2. CREAR PEDIDO - CON CONTEO CORRECTO DE PARÁMETROS
    $estado = ($metodo_pago == 'contra_entrega') ? 'pendiente' : 'confirmado';
    $es_contra_entrega = ($metodo_pago == 'contra_entrega') ? 1 : 0;
    $llegada_estimada = date('Y-m-d', strtotime('+3 days'));
    
    // Versión SIMPLIFICADA - solo columnas esenciales
    $sql_pedido = "INSERT INTO pedido (
        id_cliente, 
        fecha_pedido, 
        total, 
        subtotal, 
        envio, 
        iva, 
        estado, 
        metodo_pago,
        direccion_envio,
        telefono_contacto,
        email_contacto,
        ciudad,
        departamento,
        es_contra_entrega
    ) VALUES (?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    // 14 parámetros en total
    // 1. id_cliente (i)
    // 2. total (d)
    // 3. subtotal (d)
    // 4. envio (d)
    // 5. iva (d)
    // 6. estado (s)
    // 7. metodo_pago (s)
    // 8. direccion_envio (s)
    // 9. telefono_contacto (s)
    // 10. email_contacto (s)
    // 11. ciudad (s)
    // 12. departamento (s)
    // 13. es_contra_entrega (i)
    
    $stmt_pedido = $connect->prepare($sql_pedido);
    
    if (!$stmt_pedido) {
        throw new Exception("Error preparando pedido: " . mysqli_error($connect));
    }
    
    // IMPORTANTE: 14 parámetros = 14 caracteres en bind_param
    // 'i' (1) + 'ddd' (3) + 'sssssss' (7) + 'i' (1) = 12? Vamos a contar:
    // 1. id_cliente: i (integer)
    // 2. total: d (double)
    // 3. subtotal: d (double)
    // 4. envio: d (double)
    // 5. iva: d (double)
    // 6. estado: s (string)
    // 7. metodo_pago: s (string)
    // 8. direccion_envio: s (string)
    // 9. telefono_contacto: s (string)
    // 10. email_contacto: s (string)
    // 11. ciudad: s (string)
    // 12. departamento: s (string)
    // 13. es_contra_entrega: i (integer)
    // Total: i (1) + dddd (4) + sssssss (7) + i (1) = 13 caracteres
    
    // ¡ESPERA! Tenemos 13 parámetros en la consulta SQL
    // Vamos a contar de nuevo:
    // 1. id_cliente (1)
    // 2. NOW() es función MySQL, no parámetro (0)
    // 3. total (2)
    // 4. subtotal (3)
    // 5. envio (4)
    // 6. iva (5)
    // 7. estado (6)
    // 8. metodo_pago (7)
    // 9. direccion_envio (8)
    // 10. telefono_contacto (9)
    // 11. email_contacto (10)
    // 12. ciudad (11)
    // 13. departamento (12)
    // 14. es_contra_entrega (13)
    
    // ¡SON 13 PARÁMETROS, NO 14! (porque NOW() no es parámetro PHP)
    
    $stmt_pedido->bind_param(
        "iddddsssssssi", // 13 caracteres: i + dddd + ssssss + i
        $id_cliente,     // 1. i
        $total,          // 2. d
        $subtotal,       // 3. d
        $envio,          // 4. d
        $iva,            // 5. d
        $estado,         // 6. s
        $metodo_pago,    // 7. s
        $direccion,      // 8. s
        $telefono,       // 9. s
        $email,          // 10. s
        $ciudad,         // 11. s
        $departamento,   // 12. s
        $es_contra_entrega // 13. i
    );
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error ejecutando pedido: " . $stmt_pedido->error . 
                           "\nSQL: " . $sql_pedido);
    }
    
    $id_pedido = $connect->insert_id;
    $stmt_pedido->close();
    
    if (!$id_pedido) {
        throw new Exception("Error al crear el pedido");
    }
    
    // 3. Crear detalles del pedido (versión simple)
    foreach ($_SESSION['cart'] as $product_id => $item) {
        // Insertar en detalle_pedido (versión mínima)
        $sql_detalle = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad) 
                       VALUES (?, ?, ?)";
        
        $stmt_detalle = $connect->prepare($sql_detalle);
        $stmt_detalle->bind_param("iii", $id_pedido, $product_id, $item['quantity']);
        
        if (!$stmt_detalle->execute()) {
            // Si falla, probar con precio_unitario
            $sql_detalle2 = "INSERT INTO detalle_pedido (id_pedido, id_producto, cantidad, precio_unitario) 
                            VALUES (?, ?, ?, ?)";
            
            $stmt_detalle2 = $connect->prepare($sql_detalle2);
            $precio = $item['price'] ?? 0;
            $stmt_detalle2->bind_param("iiid", $id_pedido, $product_id, $item['quantity'], $precio);
            
            if (!$stmt_detalle2->execute()) {
                throw new Exception("Error al agregar producto al detalle");
            }
            $stmt_detalle2->close();
        } else {
            $stmt_detalle->close();
        }
        
        // Actualizar stock
        $sql_stock = "UPDATE producto SET stock = stock - ? WHERE id_producto = ?";
        $stmt_stock = $connect->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $item['quantity'], $product_id);
        $stmt_stock->execute();
        $stmt_stock->close();
    }
    
    // 4. Actualizar información del usuario
    $sql_usuario = "UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id_usuario = ?";
    $stmt_usuario = $connect->prepare($sql_usuario);
    $stmt_usuario->bind_param("ssssi", $nombre, $apellido, $email, $telefono, $id_cliente);
    $stmt_usuario->execute();
    $stmt_usuario->close();
    
    // Confirmar transacción
    mysqli_commit($connect);
    
    // Limpiar carrito
    unset($_SESSION['cart']);
    
    // Redirigir
    $_SESSION['pedido_exitoso'] = true;
    $_SESSION['id_pedido'] = $id_pedido;
    
    header("Location: order-confirmation.php?id=$id_pedido");
    exit;
    
} catch (Exception $e) {
    // Revertir
    mysqli_rollback($connect);
    
    $_SESSION['checkout_error'] = 'Error: ' . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>