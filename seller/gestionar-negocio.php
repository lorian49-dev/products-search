<?php
session_start();
require "../conexion.php"; // tu archivo de conexión

// 1. Obtener ID del usuario logueado
if (!isset($_SESSION['id_usuario'])) {
    header("Location: ../login.php");
    exit;
}

$idUsuario = $_SESSION['id_usuario'];

// 2. Consultar si ese usuario ya está registrado como vendedor
$sql = "SELECT * FROM vendedor WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

// 3. Si SI es vendedor → mandarlo al CRUD
if ($result->num_rows > 0) {
    header("Location: http://localhost/products-search/CRUD/Admin_CRUD.php");
    exit;
}

// 4. Si NO es vendedor → mandarlo a crear su negocio
header("Location: crear-negocio.php");
exit;

?>
