<?php
// CONTROLLERS/checkout-payment-handler.php
session_start();
require_once "../shortCuts/connect.php";

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

// Verificar acción
if (!isset($_POST['action'])) {
    echo json_encode(['success' => false, 'message' => 'Acción no especificada']);
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

if ($_POST['action'] == 'check_wallet_balance') {
    // Obtener saldo de billetera
    $sql = "SELECT saldo_billetera FROM metodos_pago WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'success' => true,
            'saldo' => floatval($row['saldo_billetera'])
        ]);
    } else {
        echo json_encode([
            'success' => true,
            'saldo' => 0.00
        ]);
    }
    
    $stmt->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
}

$connect->close();
?>