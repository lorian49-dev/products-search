<?php
// CONTROLLERS/download-invoice-simple.php
session_start();
require_once dirname(__DIR__) . '/shortCuts/connect.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$id_pedido = $_GET['id'] ?? 0;
if ($id_pedido == 0) {
    header("Location: mis-pedidos.php");
    exit;
}

// Obtener pedido
$sql_pedido = "SELECT * FROM pedido WHERE id_pedido = ? AND id_cliente = ?";
$stmt = $connect->prepare($sql_pedido);
$stmt->bind_param("ii", $id_pedido, $_SESSION['usuario_id']);
$stmt->execute();
$pedido = $stmt->get_result()->fetch_assoc();
$stmt->close();

if (!$pedido) {
    header("Location: mis-pedidos.php");
    exit;
}

// Obtener productos
$sql_productos = "SELECT * FROM detalle_pedido WHERE id_pedido = ?";
$stmt = $connect->prepare($sql_productos);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$productos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->close();

// Estructura JSON simplificada
$factura = [
    'id_pedido' => (int)$pedido['id_pedido'],
    'id_cliente' => (int)$pedido['id_cliente'],
    'id_vendedor' => !empty($pedido['id_vendedor']) ? (int)$pedido['id_vendedor'] : null,
    'fecha_pedido' => $pedido['fecha_pedido'],
    'estado' => $pedido['estado'],
    'descripcion' => $pedido['descripcion'] ?? '',
    'direccion_envio' => $pedido['direccion_envio'],
    'metodo_pago' => $pedido['metodo_pago'],
    'llegada_estimada' => $pedido['llegada_estimada'],
    'telefono_contacto' => $pedido['telefono_contacto'],
    'email_contacto' => $pedido['email_contacto'],
    'ciudad' => $pedido['ciudad'],
    'departamento' => $pedido['departamento'],
    'codigo_postal' => $pedido['codigo_postal'] ?? '',
    'es_contra_entrega' => (bool)$pedido['es_contra_entrega'],
    'totales' => [
        'subtotal' => (float)$pedido['subtotal'],
        'envio' => (float)$pedido['envio'],
        'iva' => (float)$pedido['iva'],
        'total' => (float)$pedido['total']
    ],
    'productos' => array_map(function($p) {
        return [
            'id_detalle' => (int)$p['id_detalle'],
            'id_producto' => (int)$p['id_producto'],
            'cantidad' => (int)$p['cantidad'],
            'precio_unitario' => (float)$p['precio_unitario'],
            'precio_total' => (float)$p['precio_total']
        ];
    }, $productos),
    'fecha_generacion' => date('Y-m-d H:i:s'),
    'numero_items' => count($productos),
    'total_cantidad' => array_sum(array_column($productos, 'cantidad'))
];

// Descargar JSON
header('Content-Type: application/json');
header('Content-Disposition: attachment; filename="pedido-' . $id_pedido . '.json"');
echo json_encode($factura, JSON_PRETTY_PRINT);
exit;