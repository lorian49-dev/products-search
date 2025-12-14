<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id']) || !isset($_POST['direccion_id'])) {
    header("Location: user-apart-dashboard-datos-personales.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$direccion_id = intval($_POST['direccion_id']);

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // 1. Quitar principal de todas las direcciones del usuario
    $update_all = "UPDATE direcciones SET es_principal = 0 WHERE id_usuario = $usuario_id";
    if (!mysqli_query($connect, $update_all)) {
        throw new Exception("Error al actualizar direcciones");
    }

    // 2. Establecer la nueva dirección como principal
    $update_principal = "UPDATE direcciones SET es_principal = 1 WHERE id_direccion = $direccion_id AND id_usuario = $usuario_id";

    if (mysqli_query($connect, $update_principal) && mysqli_affected_rows($connect) > 0) {
        mysqli_commit($connect);
        header("Location: user-apart-dashboard-datos-personales.php?success=Dirección principal actualizada");
        exit;
    } else {
        throw new Exception("No se pudo establecer como dirección principal");
    }
} catch (Exception $e) {
    mysqli_rollback($connect);
    header("Location: user-apart-dashboard-datos-personales.php?error=" . urlencode($e->getMessage()));
    exit;
}
