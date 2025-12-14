<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: user-apart-dashboard-datos-personales.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);

// Recibir datos del formulario
$direccion = mysqli_real_escape_string($connect, $_POST['direccion']);
$ciudad = mysqli_real_escape_string($connect, $_POST['ciudad']);
$departamento = mysqli_real_escape_string($connect, $_POST['departamento']);
$codigo_postal = isset($_POST['codigo_postal']) ? mysqli_real_escape_string($connect, $_POST['codigo_postal']) : NULL;
$telefono = isset($_POST['telefono']) ? mysqli_real_escape_string($connect, $_POST['telefono']) : NULL;
$referencias = isset($_POST['referencias']) ? mysqli_real_escape_string($connect, $_POST['referencias']) : NULL;
$es_principal = isset($_POST['es_principal']) ? 1 : 0;

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // Si se marca como principal, quitar principal de otras direcciones
    if ($es_principal) {
        $update_sql = "UPDATE direcciones SET es_principal = 0 WHERE id_usuario = $usuario_id";
        if (!mysqli_query($connect, $update_sql)) {
            throw new Exception("Error al actualizar direcciones principales");
        }
    }

    // Insertar nueva dirección
    $insert_sql = "INSERT INTO direcciones (id_usuario, direccion, ciudad, departamento, codigo_postal, telefono, referencias, es_principal) 
                   VALUES ('$usuario_id', '$direccion', '$ciudad', '$departamento', '$codigo_postal', '$telefono', '$referencias', '$es_principal')";

    if (mysqli_query($connect, $insert_sql)) {
        mysqli_commit($connect);
        header("Location: user-apart-dashboard-datos-personales.php?success=Dirección agregada correctamente");
        exit;
    } else {
        throw new Exception("Error al insertar la dirección: " . mysqli_error($connect));
    }
} catch (Exception $e) {
    mysqli_rollback($connect);
    header("Location: user-apart-dashboard-datos-personales.php?error=" . urlencode($e->getMessage()));
    exit;
}
