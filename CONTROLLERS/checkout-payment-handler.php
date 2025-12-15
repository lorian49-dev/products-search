<?php
session_start();
// CORREGIR LA RUTA
require_once __DIR__ . "/../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];
$action = $_POST['action'] ?? '';

// Verificar conexión
if (!$connect) {
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

if ($action === 'check_wallet_balance') {
    // Verificar saldo de billetera
    $sql = "SELECT saldo_billetera FROM metodos_pago 
            WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
    $stmt = $connect->prepare($sql);
    
    if (!$stmt) {
        echo json_encode(['success' => false, 'message' => 'Error preparando consulta']);
        exit;
    }
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $billetera = $result->fetch_assoc();
    
    echo json_encode([
        'success' => true,
        'saldo' => $billetera ? $billetera['saldo_billetera'] : 0,
        'tiene_billetera' => $billetera !== null
    ]);
    
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}
?>