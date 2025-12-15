<?php
session_start();
// CORREGIR LA RUTA
require_once __DIR__ . "/../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$numero_pedido = $_POST['numero_pedido'] ?? '';
$total = floatval($_POST['total'] ?? 0);

// Verificar conexión
if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Obtener pedido
$sql_pedido = "SELECT id_pedido, total, estado FROM pedido 
               WHERE numero_pedido = ? AND id_usuario = ?";
$stmt = $connect->prepare($sql_pedido);

if (!$stmt) {
    echo json_encode(['success' => false, 'message' => 'Error preparando consulta de pedido']);
    exit;
}

$stmt->bind_param("si", $numero_pedido, $id_usuario);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();

if (!$pedido) {
    echo json_encode(['success' => false, 'message' => 'Pedido no encontrado']);
    exit;
}

// Verificar que el pedido esté pendiente
if ($pedido['estado'] != 'pendiente') {
    echo json_encode(['success' => false, 'message' => 'El pedido ya fue procesado']);
    exit;
}

$connect->begin_transaction();

try {
    // 1. Verificar saldo con bloqueo
    $sql_saldo = "SELECT saldo_billetera FROM metodos_pago 
                  WHERE id_usuario = ? AND tipo = 'billetera_virtual' 
                  FOR UPDATE";
    $stmt_saldo = $connect->prepare($sql_saldo);
    
    if (!$stmt_saldo) {
        throw new Exception("Error preparando consulta de saldo");
    }
    
    $stmt_saldo->bind_param("i", $id_usuario);
    $stmt_saldo->execute();
    $result_saldo = $stmt_saldo->get_result();
    $billetera = $result_saldo->fetch_assoc();
    
    if (!$billetera) {
        // Crear billetera si no existe
        $sql_insert = "INSERT INTO metodos_pago (id_usuario, tipo, saldo_billetera) 
                      VALUES (?, 'billetera_virtual', 0)";
        $stmt_insert = $connect->prepare($sql_insert);
        
        if (!$stmt_insert) {
            throw new Exception("Error preparando inserción de billetera");
        }
        
        $stmt_insert->bind_param("i", $id_usuario);
        $stmt_insert->execute();
        $saldo_anterior = 0;
    } else {
        $saldo_anterior = $billetera['saldo_billetera'];
    }
    
    // Verificar saldo suficiente
    if ($saldo_anterior < $pedido['total']) {
        throw new Exception("Saldo insuficiente. Disponible: $" . 
                          number_format($saldo_anterior, 0, ',', '.') . 
                          " | Necesitas: $" . number_format($pedido['total'], 0, ',', '.'));
    }
    
    $saldo_nuevo = $saldo_anterior - $pedido['total'];
    
    // 2. Descontar de billetera
    $sql_update = "UPDATE metodos_pago 
                   SET saldo_billetera = ? 
                   WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
    $stmt_update = $connect->prepare($sql_update);
    
    if (!$stmt_update) {
        throw new Exception("Error preparando actualización de saldo");
    }
    
    $stmt_update->bind_param("di", $saldo_nuevo, $id_usuario);
    $stmt_update->execute();
    
    // 3. Registrar transacción
    $sql_transaccion = "INSERT INTO transacciones_billetera 
                       (id_usuario, tipo, monto, saldo_anterior, saldo_nuevo, descripcion, id_pedido) 
                       VALUES (?, 'compra', ?, ?, ?, ?, ?)";
    $stmt_trans = $connect->prepare($sql_transaccion);
    
    if (!$stmt_trans) {
        throw new Exception("Error preparando registro de transacción");
    }
    
    $descripcion = "Compra pedido #" . $numero_pedido;
    $stmt_trans->bind_param("idddsi", $id_usuario, $pedido['total'], 
                           $saldo_anterior, $saldo_nuevo, $descripcion, $pedido['id_pedido']);
    $stmt_trans->execute();
    
    // 4. Actualizar estado del pedido
    $sql_update_pedido = "UPDATE pedido 
                         SET estado = 'pagado', 
                             metodo_pago = 'billetera_virtual',
                             fecha_pago = NOW() 
                         WHERE id_pedido = ?";
    $stmt_pedido = $connect->prepare($sql_update_pedido);
    
    if (!$stmt_pedido) {
        throw new Exception("Error preparando actualización de pedido");
    }
    
    $stmt_pedido->bind_param("i", $pedido['id_pedido']);
    $stmt_pedido->execute();
    
    mysqli_commit($connect);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Pago exitoso con billetera virtual!',
        'numero_pedido' => $numero_pedido,
        'total' => $pedido['total'],
        'saldo_restante' => $saldo_nuevo
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($connect);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>