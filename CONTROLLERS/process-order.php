<?php
// CONTROLLERS/process-order.php - VERSIÓN CORREGIDA PARA TU BD
session_start();
require_once "../shortCuts/connect.php";

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
$id_usuario = $_SESSION['usuario_id'];
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

// Calcular totales
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
    $saldo_actual = 0;
    if ($metodo_pago == 'billetera_virtual') {
        $sql_billetera = "SELECT saldo_billetera FROM metodos_pago 
                         WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
        $stmt = $connect->prepare($sql_billetera);
        $stmt->bind_param("i", $id_usuario);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows == 0) {
            throw new Exception('No tienes billetera virtual configurada');
        }
        
        $billetera = $result->fetch_assoc();
        $saldo_actual = floatval($billetera['saldo_billetera']);
        
        if ($saldo_actual < $total) {
            throw new Exception('Saldo insuficiente en billetera. Saldo actual: $' . 
                               number_format($saldo_actual, 0, ',', '.') . 
                               ', Total: $' . number_format($total, 0, ',', '.'));
        }
        
        // Actualizar saldo del cliente (restar)
        $nuevo_saldo = $saldo_actual - $total;
        $sql_update = "UPDATE metodos_pago SET saldo_billetera = ? 
                       WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
        $stmt2 = $connect->prepare($sql_update);
        $stmt2->bind_param("di", $nuevo_saldo, $id_usuario);
        $stmt2->execute();
        $stmt2->close();
        $stmt->close();
    }
    
    // 2. IDENTIFICAR VENDEDOR DE LOS PRODUCTOS
    $id_vendedor_principal = 0;
    $productos_info = [];
    
    foreach ($_SESSION['cart'] as $product_id => $item) {
        $sql_producto = "SELECT p.*, v.id_vendedor FROM producto p 
                        LEFT JOIN vendedor v ON p.id_vendedor = v.id_vendedor 
                        WHERE p.id_producto = ?";
        $stmt_prod = $connect->prepare($sql_producto);
        $stmt_prod->bind_param("i", $product_id);
        $stmt_prod->execute();
        $result_prod = $stmt_prod->get_result();
        
        if ($result_prod->num_rows > 0) {
            $producto_data = $result_prod->fetch_assoc();
            
            // Guardar información del producto
            $productos_info[$product_id] = [
                'producto' => $producto_data,
                'item' => $item,
                'id_vendedor' => $producto_data['id_vendedor'] ?? 0
            ];
            
            // Usar el primer vendedor encontrado
            if ($id_vendedor_principal == 0 && $producto_data['id_vendedor']) {
                $id_vendedor_principal = $producto_data['id_vendedor'];
            }
        }
        $stmt_prod->close();
    }
    
    // 3. CREAR PEDIDO - Según tu estructura de BD
    $estado = 'Pendiente';
    $es_contra_entrega = ($metodo_pago == 'contra_entrega') ? 1 : 0;
    
    // IMPORTANTE: Según tu BD, la tabla pedido tiene id_cliente (no id_usuario)
    $sql_pedido = "INSERT INTO pedido (
        id_cliente, 
        id_vendedor,
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
    ) VALUES (?, ?, NOW(), ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt_pedido = $connect->prepare($sql_pedido);
    
    if (!$stmt_pedido) {
        throw new Exception("Error preparando pedido: " . mysqli_error($connect));
    }
    
    $stmt_pedido->bind_param(
        "iiddddsssssssi",
        $id_usuario,           // id_cliente (tu usuario ID 2)
        $id_vendedor_principal, // id_vendedor (0 si no tiene, o el ID del vendedor)
        $total,
        $subtotal,
        $envio,
        $iva,
        $estado,
        $metodo_pago,
        $direccion,
        $telefono,
        $email,
        $ciudad,
        $departamento,
        $es_contra_entrega
    );
    
    if (!$stmt_pedido->execute()) {
        throw new Exception("Error ejecutando pedido: " . $stmt_pedido->error);
    }
    
    $id_pedido = $connect->insert_id;
    $stmt_pedido->close();
    
    if (!$id_pedido) {
        throw new Exception("Error al crear el pedido");
    }
    
    // 4. CREAR DETALLES DEL PEDIDO Y ACTUALIZAR STOCK
    foreach ($productos_info as $product_id => $data) {
        $producto = $data['producto'];
        $item = $data['item'];
        $id_vendedor_producto = $data['id_vendedor'] ?? 0;
        
        // Verificar stock
        if ($producto['stock'] < $item['quantity']) {
            throw new Exception("Stock insuficiente para: " . $producto['nombre'] . 
                               " (Disponible: " . $producto['stock'] . ", Solicitado: " . $item['quantity'] . ")");
        }
        
        // Calcular precios
        $precio_unitario = $producto['precio'];
        $precio_total = $precio_unitario * $item['quantity'];
        
        // Insertar en detalle_pedido
        $sql_detalle = "INSERT INTO detalle_pedido (
            id_pedido, 
            id_producto, 
            cantidad, 
            precio_unitario, 
            precio_total
        ) VALUES (?, ?, ?, ?, ?)";
        
        $stmt_detalle = $connect->prepare($sql_detalle);
        $stmt_detalle->bind_param("iiidd", $id_pedido, $product_id, $item['quantity'], $precio_unitario, $precio_total);
        
        if (!$stmt_detalle->execute()) {
            throw new Exception("Error al agregar producto al detalle: " . $stmt_detalle->error);
        }
        $stmt_detalle->close();
        
        // Actualizar stock del producto
        $sql_stock = "UPDATE producto SET stock = stock - ? WHERE id_producto = ?";
        $stmt_stock = $connect->prepare($sql_stock);
        $stmt_stock->bind_param("ii", $item['quantity'], $product_id);
        $stmt_stock->execute();
        $stmt_stock->close();
        
        // =============================================
        // TRANSFERIR DINERO A LA BILLETERA DEL VENDEDOR
        // =============================================
        if ($metodo_pago == 'billetera_virtual' && $id_vendedor_producto > 0) {
            // Calcular comisión (10% para plataforma, 90% para vendedor)
            $comision = $precio_total * 0.10; // 10% para Hermes
            $monto_vendedor = $precio_total - $comision;
            
            // Verificar si el vendedor tiene billetera
            $sql_check_vendedor = "SELECT id_metodo_pago FROM metodos_pago 
                                   WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
            $stmt_check = $connect->prepare($sql_check_vendedor);
            $stmt_check->bind_param("i", $id_vendedor_producto);
            $stmt_check->execute();
            $result_check = $stmt_check->get_result();
            
            if ($result_check->num_rows == 0) {
                // Crear billetera para el vendedor si no existe
                $sql_crear = "INSERT INTO metodos_pago (id_usuario, tipo, saldo_billetera) 
                              VALUES (?, 'billetera_virtual', 0)";
                $stmt_crear = $connect->prepare($sql_crear);
                $stmt_crear->bind_param("i", $id_vendedor_producto);
                $stmt_crear->execute();
                $stmt_crear->close();
            }
            $stmt_check->close();
            
            // Sumar dinero a la billetera del vendedor
            $sql_sumar_vendedor = "UPDATE metodos_pago 
                                   SET saldo_billetera = saldo_billetera + ? 
                                   WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
            $stmt_sumar = $connect->prepare($sql_sumar_vendedor);
            $stmt_sumar->bind_param("di", $monto_vendedor, $id_vendedor_producto);
            $stmt_sumar->execute();
            $stmt_sumar->close();
            
            // Sumar comisión a la billetera de Hermes (admin ID 1)
            $sql_check_admin = "SELECT id_metodo_pago FROM metodos_pago 
                               WHERE id_usuario = 1 AND tipo = 'billetera_virtual'";
            $result_admin = $connect->query($sql_check_admin);
            
            if ($result_admin->num_rows == 0) {
                // Crear billetera para admin si no existe
                $sql_crear_admin = "INSERT INTO metodos_pago (id_usuario, tipo, saldo_billetera) 
                                   VALUES (1, 'billetera_virtual', 0)";
                $connect->query($sql_crear_admin);
            }
            
            // Sumar comisión
            $sql_sumar_admin = "UPDATE metodos_pago 
                                SET saldo_billetera = saldo_billetera + ? 
                                WHERE id_usuario = 1 AND tipo = 'billetera_virtual'";
            $stmt_admin = $connect->prepare($sql_sumar_admin);
            $stmt_admin->bind_param("d", $comision);
            $stmt_admin->execute();
            $stmt_admin->close();
            
            // Registrar transacción del vendedor
            $sql_transaccion = "INSERT INTO transacciones_billetera (
                id_usuario, 
                tipo, 
                monto, 
                saldo_anterior, 
                saldo_nuevo, 
                descripcion, 
                id_pedido
            ) VALUES (?, 'venta', ?, ?, ?, ?, ?)";
            
            // Obtener saldo anterior del vendedor
            $sql_saldo_anterior = "SELECT saldo_billetera FROM metodos_pago 
                                  WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
            $stmt_saldo = $connect->prepare($sql_saldo_anterior);
            $stmt_saldo->bind_param("i", $id_vendedor_producto);
            $stmt_saldo->execute();
            $result_saldo = $stmt_saldo->get_result();
            $saldo_data = $result_saldo->fetch_assoc();
            $saldo_anterior = $saldo_data ? $saldo_data['saldo_billetera'] - $monto_vendedor : 0;
            $saldo_nuevo = $saldo_anterior + $monto_vendedor;
            $stmt_saldo->close();
            
            $descripcion = "Venta producto ID " . $product_id . " (Pedido #" . $id_pedido . ")";
            $stmt_trans = $connect->prepare($sql_transaccion);
            $stmt_trans->bind_param("idddsi", 
                $id_vendedor_producto,
                $monto_vendedor,
                $saldo_anterior,
                $saldo_nuevo,
                $descripcion,
                $id_pedido
            );
            $stmt_trans->execute();
            $stmt_trans->close();
        }
    }
    
    // 5. Registrar transacción del cliente (si pagó con billetera)
    if ($metodo_pago == 'billetera_virtual') {
        $sql_trans_cliente = "INSERT INTO transacciones_billetera (
            id_usuario, 
            tipo, 
            monto, 
            saldo_anterior, 
            saldo_nuevo, 
            descripcion, 
            id_pedido
        ) VALUES (?, 'compra', ?, ?, ?, ?, ?)";
        
        $descripcion_cliente = "Compra pedido #" . $id_pedido;
        $saldo_nuevo_cliente = $saldo_actual - $total;
        
        $stmt_trans_cli = $connect->prepare($sql_trans_cliente);
        $stmt_trans_cli->bind_param("idddsi", 
            $id_usuario,
            $total,
            $saldo_actual,
            $saldo_nuevo_cliente,
            $descripcion_cliente,
            $id_pedido
        );
        $stmt_trans_cli->execute();
        $stmt_trans_cli->close();
    }
    
    // 6. Actualizar información del usuario
    $sql_usuario = "UPDATE usuario SET nombre = ?, apellido = ?, correo = ?, telefono = ? WHERE id_usuario = ?";
    $stmt_usuario = $connect->prepare($sql_usuario);
    $stmt_usuario->bind_param("ssssi", $nombre, $apellido, $email, $telefono, $id_usuario);
    $stmt_usuario->execute();
    $stmt_usuario->close();
    
    // 7. Vaciar carrito de sesión y BD
    unset($_SESSION['cart']);
    
    // Vaciar carrito de la BD si existe
    $sql_vaciar_carrito = "DELETE FROM carrito_producto WHERE id_carrito IN 
                          (SELECT id_carrito FROM carrito WHERE id_cliente = ?)";
    $stmt_vaciar = $connect->prepare($sql_vaciar_carrito);
    $stmt_vaciar->bind_param("i", $id_usuario);
    $stmt_vaciar->execute();
    $stmt_vaciar->close();
    
    // Confirmar transacción
    mysqli_commit($connect);
    
    // Redirigir a confirmación
    $_SESSION['pedido_exitoso'] = true;
    $_SESSION['ultimo_pedido'] = $id_pedido;
    
    header("Location: order-confirmation.php?id=$id_pedido");
    exit;
    
} catch (Exception $e) {
    // Revertir transacción en caso de error
    mysqli_rollback($connect);
    
    $_SESSION['checkout_error'] = 'Error: ' . $e->getMessage();
    header("Location: checkout.php");
    exit;
}
?>