<?php
session_start();
include("../shortCuts/connect.php");

if (!isset($_SESSION['usuario_id'])) {
    header("Location: user-apart-dashboard-metodos-pago.php?error=No autorizado");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);
$tipo = mysqli_real_escape_string($connect, $_POST['tipo']);

// Iniciar transacción
mysqli_begin_transaction($connect);

try {
    // Si se marca como predeterminado, quitar predeterminado de otros métodos
    if (isset($_POST['es_predeterminado']) && $_POST['es_predeterminado'] == 1) {
        $update_sql = "UPDATE metodos_pago SET es_predeterminado = 0 WHERE id_usuario = $usuario_id";
        if (!mysqli_query($connect, $update_sql)) {
            throw new Exception("Error al actualizar métodos de pago");
        }
    }

    $es_predeterminado = isset($_POST['es_predeterminado']) ? 1 : 0;

    if ($tipo == 'paypal') {
        // Insertar PayPal
        $email_paypal = mysqli_real_escape_string($connect, $_POST['email_paypal']);

        $insert_sql = "INSERT INTO metodos_pago (id_usuario, tipo, email_paypal, es_predeterminado) 
                      VALUES ('$usuario_id', 'paypal', '$email_paypal', '$es_predeterminado')";
    } else {
        // Insertar tarjeta
        $nombre_titular = mysqli_real_escape_string($connect, $_POST['nombre_titular']);
        $numero_tarjeta = mysqli_real_escape_string($connect, $_POST['numero_tarjeta']);
        $fecha_vencimiento = mysqli_real_escape_string($connect, $_POST['fecha_vencimiento']);
        $cvv = mysqli_real_escape_string($connect, $_POST['cvv']);
        $tipo_tarjeta = mysqli_real_escape_string($connect, $_POST['tipo_tarjeta']);
        $marca_tarjeta = isset($_POST['marca_tarjeta']) ? mysqli_real_escape_string($connect, $_POST['marca_tarjeta']) : '';

        // Solo guardar últimos 4 dígitos por seguridad
        $ultimos_digitos = substr($numero_tarjeta, -4);

        $insert_sql = "INSERT INTO metodos_pago (id_usuario, tipo, nombre_titular, numero_tarjeta, 
                       fecha_vencimiento, marca_tarjeta, es_predeterminado) 
                      VALUES ('$usuario_id', '$tipo_tarjeta', '$nombre_titular', '$ultimos_digitos', 
                      '$fecha_vencimiento', '$marca_tarjeta', '$es_predeterminado')";
    }

    if (mysqli_query($connect, $insert_sql)) {
        mysqli_commit($connect);
        header("Location: user-apart-dashboard-metodos-pago.php?success=Método de pago agregado correctamente");
        exit;
    } else {
        throw new Exception("Error al agregar método de pago: " . mysqli_error($connect));
    }
} catch (Exception $e) {
    mysqli_rollback($connect);
    header("Location: user-apart-dashboard-metodos-pago.php?error=" . urlencode($e->getMessage()));
    exit;
}
