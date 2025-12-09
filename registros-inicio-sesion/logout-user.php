<?php
session_start();

// Cerrar sesión
$_SESSION = array();
session_destroy();
setcookie("usuario_id", "", time() - 3600, "/");

// Siempre al home cuando cierras sesión desde área privada
header("Location: ../home.php");
exit();
?>