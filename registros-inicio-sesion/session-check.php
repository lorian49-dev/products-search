<?php
session_start();
include('../shortCuts/connect.php');

// Si no hay sesión pero sí cookie, recrearla
if (!isset($_SESSION['usuario_id']) && isset($_COOKIE['usuario_id'])) {
    $id = $_COOKIE['usuario_id'];
    $query = "SELECT * FROM usuario WHERE id = '$id'";
    $result = mysqli_query($connect, $query);

    if ($usuario = mysqli_fetch_assoc($result)) {
        $_SESSION['usuario_id'] = $usuario['id'];
        $_SESSION['usuario_nombre'] = $usuario['nombre'];
        $_SESSION['usuario_email'] = $usuario['email'];
    }
}
?>
