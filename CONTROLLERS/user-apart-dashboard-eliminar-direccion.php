<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id']) || !isset($_POST['direccion_id'])) {
    header("Location: user-apart-dashboard-datos-personales.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$direccion_id = intval($_POST['direccion_id']);

// Verificar que la dirección pertenece al usuario
$check_sql = "SELECT es_principal FROM direcciones WHERE id = $direccion_id AND id_usuario = $usuario_id";
$check_result = mysqli_query($connect, $check_sql);

if (mysqli_num_rows($check_result) == 0) {
    header("Location: user-apart-dashboard-datos-personales.php?error=Dirección no encontrada");
    exit;
}

$direccion = mysqli_fetch_assoc($check_result);

// No permitir eliminar la dirección principal si es la única
if ($direccion['es_principal'] == 1) {
    // Contar cuántas direcciones tiene el usuario
    $count_sql = "SELECT COUNT(*) as total FROM direcciones WHERE id_usuario = $usuario_id";
    $count_result = mysqli_query($connect, $count_sql);
    $count = mysqli_fetch_assoc($count_result);

    if ($count['total'] == 1) {
        header("Location: user-apart-dashboard-datos-personales.php?error=No puedes eliminar tu única dirección principal");
        exit;
    }
}

// Eliminar la dirección
$delete_sql = "DELETE FROM direcciones WHERE id_direccion = $direccion_id AND id_usuario = $usuario_id";

if (mysqli_query($connect, $delete_sql)) {
    // Si se eliminó la dirección principal, establecer otra como principal
    if ($direccion['es_principal'] == 1) {
        $new_principal_sql = "UPDATE direcciones SET es_principal = 1 
                              WHERE id_usuario = $usuario_id 
                              LIMIT 1";
        mysqli_query($connect, $new_principal_sql);
    }

    header("Location: user-apart-dashboard-datos-personales.php?success=Dirección eliminada correctamente");
    exit;
} else {
    header("Location: user-apart-dashboard-datos-personales.php?error=Error al eliminar la dirección");
    exit;
}
