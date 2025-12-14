<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id']) || !isset($_POST['metodo_id'])) {
    header("Location: user-apart-dashboard-metodos-pago.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$metodo_id = intval($_POST['metodo_id']);

// Verificar que el método pertenece al usuario
$check_sql = "SELECT es_predeterminado FROM metodos_pago WHERE id_metodo_pago = $metodo_id AND id_usuario = $usuario_id";
$check_result = mysqli_query($connect, $check_sql);

if (mysqli_num_rows($check_result) == 0) {
    header("Location: user-apart-dashboard-metodos-pago.php?error=Método de pago no encontrado");
    exit;
}

$metodo = mysqli_fetch_assoc($check_result);

// No permitir eliminar el método predeterminado si es el único
if ($metodo['es_predeterminado'] == 1) {
    $count_sql = "SELECT COUNT(*) as total FROM metodos_pago WHERE id_usuario = $usuario_id";
    $count_result = mysqli_query($connect, $count_sql);
    $count = mysqli_fetch_assoc($count_result);

    if ($count['total'] == 1) {
        header("Location: user-apart-dashboard-metodos-pago.php?error=No puedes eliminar tu único método de pago");
        exit;
    }
}

// Eliminar el método
$delete_sql = "DELETE FROM metodos_pago WHERE id_metodo_pago = $metodo_id AND id_usuario = $usuario_id";

if (mysqli_query($connect, $delete_sql)) {
    // Si se eliminó el método predeterminado, establecer otro como predeterminado
    if ($metodo['es_predeterminado'] == 1) {
        $new_default_sql = "UPDATE metodos_pago SET es_predeterminado = 1 
                           WHERE id_usuario = $usuario_id 
                           LIMIT 1";
        mysqli_query($connect, $new_default_sql);
    }

    header("Location: user-apart-dashboard-metodos-pago.php?success=Método de pago eliminado correctamente");
    exit;
} else {
    header("Location: user-apart-dashboard-metodos-pago.php?error=Error al eliminar el método de pago");
    exit;
}
