<?php
session_start();
require "../registros-inicio-sesion/connect.php"; // Ajusta según tu ruta real

// 1. Verificar sesión activa
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];  // Este SÍ existe en tu login

// 2. Consultar si ese usuario YA ES VENDEDOR
$sql = "SELECT id_vendedor FROM vendedor WHERE id_vendedor = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

// 3. Si es vendedor → mandar al DASHBOARD de vendedor
if ($result->num_rows > 0) {
    header("Location: dashboard-vendedor.php");
    exit;
}

// 4. Si NO es vendedor → mandar al formulario para crear negocio
header("Location: crear-negocio.php");
exit;
?>
