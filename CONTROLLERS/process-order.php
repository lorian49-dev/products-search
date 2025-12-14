<?php
// CONTROLLERS/process-order.php
session_start();
require_once "../shortCuts/connect.php";
require_once "cart-functions.php";

// Verificar que el usuario esté logueado y tenga carrito
if (!isset($_SESSION['usuario_id']) || empty($_SESSION['cart'])) {
    $_SESSION['checkout_error'] = 'Tu carrito está vacío o no has iniciado sesión';
    header("Location: checkout.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Validar datos del formulario
$required_fields = [
    'nombre', 'apellido', 'email', 'telefono', 
    'direccion', 'ciudad', 'departamento', 'metodo_pago',
    'terminos'
];

$errors = [];
foreach ($required_fields as $field) {
    if (empty($_POST[$field])) {
        $errors[] = ucfirst($field) . ' es requerido';
    }
}

if (!empty($errors)) {
    $_SESSION['checkout_error'] = implode('<br>', $errors);
    header("Location: checkout.php");
    exit;
}

// Obtener datos del formulario
$nombre = trim($_POST['nombre']);
$apellido = trim($_POST['apellido']);
$email = trim($_POST['email']);
$telefono = trim($_POST['telefono']);
$direccion = trim($_POST['direccion']);
$ciudad = trim($_POST['ciudad']);
$departamento = trim($_POST['departamento']);
$codigo_postal = trim($_POST['codigo_postal'] ?? '');
$referencia = trim($_POST['referencia'] ?? '');
$metodo_pago = $_POST['metodo_pago'];
$newsletter = isset($_POST['newsletter']) ? 1 : 0;

// Calcular totales
$subtotal = getCartTotal();
$envio = 10000;
$iva = $subtotal * 0.19;
$total = $subtotal + $envio + $iva;

// Verificar stock antes de procesar
$stock_errors = validateCartStock($connect);
if ($stock_errors !== true) {
    $error_messages = [];
    foreach ($stock_errors as $error) {
        $error_messages[] = $error['product_name'] . ': Solicitado ' . $error['requested'] . ', Disponible ' . $error['available'];
    }
    $_SESSION['checkout_error'] = 'Stock insuficiente:<br>' . implode('<br>', $error_messages);
    header("Location: checkout.php");
    exit;
}

// Iniciar transacción
$connect->begin_transaction();

try {
    // 1. Agrupar productos por vendedor
    $pedidos_por_vendedor = [];
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $sql = "SELECT p.*, v.id_vendedor, v.nombre_empresa 
                FROM producto p 
                LEFT JOIN vendedor v ON p.id_vendedor = v.id_vendedor 
                WHERE p.id_producto = ?";
        $stmt = $connect->prepare($sql);
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $producto = $stmt->get_result()->fetch_assoc();
        
        if ($producto) {
            $id_vendedor = $producto['id_vendedor'];
            
            if (!isset($pedidos_por_vendedor[$id_vendedor])) {
                $pedidos_por_vendedor[$id_vendedor] = [
                    'vendedor_nombre' => $producto['nombre_empresa'],
                    'items' => [],
                    'subtotal' => 0
                ];
            }
            
            $pedidos_por_vendedor[$id_vendedor]['items'][] = [
                'producto' => $producto,
                'item_carrito' => $item
            ];
            $pedidos_por_vendedor[$id_vendedor]['subtotal'] += $item['price'] * $item['quantity'];
        }
    }
    
    // 2. Generar número de pedido único
    $numero_pedido = 'PED-' . date('Ymd') . '-' . strtoupper(uniqid());
    
    $pedidos_ids = [];
    $direccion_completa = "$direccion, $ciudad, $departamento" . ($codigo_postal ? " - CP: $codigo_postal" : "");
    
    // 3. Crear pedido para cada vendedor
    foreach ($pedidos_por_vendedor as $id_vendedor => $datos) {
        $subtotal_vendedor = $datos['subtotal'];
        $envio_vendedor = count($pedidos_por_vendedor) > 1 ? ($envio / count($pedidos_por_vendedor)) : $envio;
        $iva_vendedor = $subtotal_vendedor * 0.19;
        $total_vendedor = $subtotal_vendedor + $envio_vendedor + $iva_vendedor;
        
        // Crear descripción del pedido
        $descripcion = "Pedido #$numero_pedido\n";
        $descripcion .= "Cliente: $nombre $apellido\n";
        $descripcion .= "Productos: " . count($datos['items']) . " artículo(s)\n";
        $descripcion .= "Vendedor: " . $datos['vendedor_nombre'];
        
        // 4. Insertar pedido en la tabla `pedido`
        $sql_pedido = "INSERT INTO pedido (
            id_usuario, id_vendedor, fecha_pedido, 
            subtotal, envio, iva, total, estado, descripcion,
            direccion_envio, metodo_pago, telefono_contacto,
            email_contacto, ciudad, departamento, codigo_postal,
            referencia, numero_pedido
        ) VALUES (?, ?, NOW(), ?, ?, ?, ?, 'pendiente', ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $estado_inicial = 'pendiente';
        if ($metodo_pago === 'contra_entrega') {
            $estado_inicial = 'confirmado'; // Para contra entrega
        }
        
        $stmt_pedido = $connect->prepare($sql_pedido);
        $stmt_pedido->bind_param(
            "iiddddssssssssss", 
            $id_usuario, $id_vendedor,
            $subtotal_vendedor, $envio_vendedor, $iva_vendedor, $total_vendedor,
            $descripcion, $direccion_completa, $metodo_pago, $telefono,
            $email, $ciudad, $departamento, $codigo_postal,
            $referencia, $numero_pedido
        );
        
        if (!$stmt_pedido->execute()) {
            throw new Exception("Error al crear pedido: " . $connect->error);
        }
        
        $id_pedido = $connect->insert_id;
        $pedidos_ids[] = $id_pedido;
        
        // 5. Insertar items en `pedido_item` y actualizar stock
        foreach ($datos['items'] as $item_data) {
            $producto = $item_data['producto'];
            $item_carrito = $item_data['item_carrito'];
            
            // Reducir stock
            $sql_update_stock = "UPDATE producto SET stock = stock - ? WHERE id_producto = ?";
            $stmt_stock = $connect->prepare($sql_update_stock);
            $stmt_stock->bind_param("ii", $item_carrito['quantity'], $producto['id_producto']);
            
            if (!$stmt_stock->execute()) {
                throw new Exception("Error al actualizar stock del producto ID " . $producto['id_producto']);
            }
            
            // Insertar item del pedido
            $subtotal_item = $item_carrito['price'] * $item_carrito['quantity'];
            
            $sql_item = "INSERT INTO pedido_item (
                id_pedido, id_producto, id_vendedor, cantidad, 
                precio_unitario, subtotal, nombre_producto, imagen_url
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
            
            $stmt_item = $connect->prepare($sql_item);
            $stmt_item->bind_param(
                "iiiiddss", 
                $id_pedido, $producto['id_producto'], $id_vendedor,
                $item_carrito['quantity'], $item_carrito['price'], $subtotal_item,
                $producto['nombre'], $producto['imagen_url']
            );
            
            if (!$stmt_item->execute()) {
                throw new Exception("Error al guardar items del pedido: " . $connect->error);
            }
        }
        
        // 6. Notificar al vendedor (simulado - puedes implementar email después)
        $sql_notificar = "INSERT INTO notificaciones (
            id_usuario, tipo, titulo, mensaje, fecha, leida
        ) VALUES (?, 'nuevo_pedido', 'Nuevo Pedido Recibido', ?, NOW(), 0)";
        
        $stmt_notif = $connect->prepare($sql_notificar);
        $mensaje_notif = "Has recibido un nuevo pedido #$numero_pedido por $$total_vendedor";
        $stmt_notif->bind_param("is", $id_vendedor, $mensaje_notif);
        $stmt_notif->execute();
    }
    
    // 7. Guardar dirección del usuario para futuras compras
    $sql_direccion = "INSERT INTO direcciones (
        id_usuario, direccion, ciudad, departamento, 
        codigo_postal, telefono, referencia, es_principal
    ) VALUES (?, ?, ?, ?, ?, ?, ?, 1)
    ON DUPLICATE KEY UPDATE 
        direccion = VALUES(direccion),
        ciudad = VALUES(ciudad),
        departamento = VALUES(departamento),
        codigo_postal = VALUES(codigo_postal),
        telefono = VALUES(telefono),
        referencia = VALUES(referencia)";
    
    $stmt_dir = $connect->prepare($sql_direccion);
    $stmt_dir->bind_param(
        "issssss", 
        $id_usuario, $direccion, $ciudad, $departamento,
        $codigo_postal, $telefono, $referencia
    );
    $stmt_dir->execute();
    
    // 8. Suscribir a newsletter si seleccionó la opción
    if ($newsletter) {
        $sql_newsletter = "INSERT INTO newsletter_suscripciones (email, nombre, fecha_suscripcion, activo)
                          VALUES (?, ?, NOW(), 1)
                          ON DUPLICATE KEY UPDATE activo = 1";
        $stmt_news = $connect->prepare($sql_newsletter);
        $nombre_completo = "$nombre $apellido";
        $stmt_news->bind_param("ss", $email, $nombre_completo);
        $stmt_news->execute();
    }
    
    // 9. Confirmar transacción
    $connect->commit();
    
    // 10. Limpiar carrito
    unset($_SESSION['cart']);
    
    // 11. Guardar información para la confirmación
    $_SESSION['ultimo_pedido'] = [
        'numero_pedido' => $numero_pedido,
        'total' => $total,
        'metodo_pago' => $metodo_pago,
        'fecha' => date('d/m/Y H:i'),
        'direccion' => $direccion_completa,
        'pedidos_ids' => $pedidos_ids
    ];
    
    $_SESSION['checkout_success'] = true;
    
    // 12. Redirigir según método de pago
    if ($metodo_pago === 'contra_entrega') {
        // Para contra entrega, ir directo a confirmación
        header("Location: order-confirmation.php");
    } else {
        // Para pagos electrónicos, ir a procesamiento de pago
        header("Location: payment-process.php?pedido=$numero_pedido");
    }
    exit;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    $connect->rollback();
    
    error_log("Error en checkout: " . $e->getMessage());
    $_SESSION['checkout_error'] = "Error al procesar el pedido: " . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>