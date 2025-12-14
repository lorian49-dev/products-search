<?php
// CONTROLLERS/payment-process.php
session_start();
require_once "shortCuts/connect.php";

// Aquí integrarías con la pasarela de pago elegida
// Por ahora, es solo un ejemplo

$pedido = $_GET['pedido'] ?? '';

// Redirigir a confirmación (simulando pago exitoso)
$_SESSION['ultimo_pedido'] = [
    'numero_pedido' => $pedido,
    'total' => 0, // Obtener de BD
    'metodo_pago' => 'tarjeta_credito',
    'fecha' => date('d/m/Y H:i')
];

header("Location: order-confirmation.php");
exit;
?>