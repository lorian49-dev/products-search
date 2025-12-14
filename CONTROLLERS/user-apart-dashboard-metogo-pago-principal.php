<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id']) || !isset($_POST['metodo_id'])) {
    header("Location: user-apart-dashboard-metodos-pago.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$metodo_id = intval($_POST['metodo_id']);

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // 1. Quitar predeterminado de todas las direcciones del usuario
    $update_all = "UPDATE metodos_pago SET es_predeterminado = 0 WHERE id_usuario = $usuario_id";
    if (!mysqli_query($connect, $update_all)) {
        throw new Exception("Error al actualizar métodos de pago");
    }
    
    // 2. Establecer nuevo método como predeterminado
    $update_default = "UPDATE metodos_pago SET es_predeterminado = 1 
                       WHERE id_metodo_pago = $metodo_id AND id_usuario = $usuario_id";
    
    if (mysqli_query($connect, $update_default) && mysqli_affected_rows($connect) > 0) {
        mysqli_commit($connect);
        header("Location: user-apart-dashboard-metodos-pago.php?success=Método de pago establecido como predeterminado");
        exit;
    } else {
        throw new Exception("No se pudo establecer como método predeterminado");
    }
} catch (Exception $e) {
    mysqli_rollback($connect);
    header("Location: user-apart-dashboard-metodos-pago.php?error=" . urlencode($e->getMessage()));
    exit;
}
?>