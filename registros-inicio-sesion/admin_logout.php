<?php
session_start();
include('../shortCuts/connect.php');
// Eliminar solo variables de sesión ADMIN
unset($_SESSION['admin_logueado']);
unset($_SESSION['admin_id']);
unset($_SESSION['admin_username']);
unset($_SESSION['admin_email']);
unset($_SESSION['admin_rol']);
unset($_SESSION['admin_nombre']);
unset($_SESSION['admin_nombre_rol']);

// Eliminar cookie admin
setcookie("admin_id", "", time() - 3600, "/");

// Redirigir al login administrativo
header("Location: admin-login.php");
exit();
?>