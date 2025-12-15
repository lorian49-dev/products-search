<?php
session_start();
// CORREGIR LA RUTA - usar ruta relativa correcta
require_once __DIR__ . "/../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$monto = floatval($_POST['monto'] ?? 0);

if ($monto < 1000) {
    echo json_encode(['success' => false, 'message' => 'Monto mínimo: $1.000']);
    exit;
}

if ($monto > 10000000) {
    echo json_encode(['success' => false, 'message' => 'Monto máximo: $10.000.000']);
    exit;
}

// Verificar conexión
if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // Verificar si ya tiene billetera (con bloqueo para evitar condiciones de carrera)
    $sql_check = "SELECT saldo_billetera FROM metodos_pago 
                  WHERE id_usuario = ? AND tipo = 'billetera_virtual' 
                  FOR UPDATE";
    $stmt_check = $connect->prepare($sql_check);
    
    if (!$stmt_check) {
        throw new Exception("Error preparando consulta: " . mysqli_error($connect));
    }
    
    $stmt_check->bind_param("i", $id_usuario);
    $stmt_check->execute();
    $result = $stmt_check->get_result();
    
    if ($result->num_rows > 0) {
        // Actualizar saldo existente
        $row = $result->fetch_assoc();
        $saldo_anterior = $row['saldo_billetera'];
        $saldo_nuevo = $saldo_anterior + $monto;
        
        $sql_update = "UPDATE metodos_pago 
                      SET saldo_billetera = ? 
                      WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
        $stmt_update = $connect->prepare($sql_update);
        
        if (!$stmt_update) {
            throw new Exception("Error preparando actualización: " . mysqli_error($connect));
        }
        
        $stmt_update->bind_param("di", $saldo_nuevo, $id_usuario);
        $stmt_update->execute();
    } else {
        // Crear nueva billetera
        $saldo_anterior = 0;
        $saldo_nuevo = $monto;
        
        $sql_insert = "INSERT INTO metodos_pago (id_usuario, tipo, saldo_billetera) 
                      VALUES (?, 'billetera_virtual', ?)";
        $stmt_insert = $connect->prepare($sql_insert);
        
        if (!$stmt_insert) {
            throw new Exception("Error preparando inserción: " . mysqli_error($connect));
        }
        
        $stmt_insert->bind_param("id", $id_usuario, $saldo_nuevo);
        $stmt_insert->execute();
    }
    
    // Registrar transacción
    $sql_trans = "INSERT INTO transacciones_billetera 
                 (id_usuario, tipo, monto, saldo_anterior, saldo_nuevo, descripcion) 
                 VALUES (?, 'recarga', ?, ?, ?, ?)";
    $stmt_trans = $connect->prepare($sql_trans);
    
    if (!$stmt_trans) {
        throw new Exception("Error preparando registro de transacción: " . mysqli_error($connect));
    }
    
    $descripcion = "Recarga de saldo";
    $stmt_trans->bind_param("iddds", $id_usuario, $monto, $saldo_anterior, $saldo_nuevo, $descripcion);
    $stmt_trans->execute();
    
    mysqli_commit($connect);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Recarga exitosa!',
        'saldo_anterior' => $saldo_anterior,
        'saldo_nuevo' => $saldo_nuevo
    ]);
    
} catch (Exception $e) {
    mysqli_rollback($connect);
    echo json_encode(['success' => false, 'message' => 'Error: ' . $e->getMessage()]);
}
?>