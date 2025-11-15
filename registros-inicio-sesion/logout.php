<?php
session_start();

// Elimina todas las variables de sesión
session_unset();

// Destruye la sesión
session_destroy();

// Borra la cookie si existe
if (isset($_COOKIE['usuario_id'])) {
    setcookie("usuario_id", "", time() - 3600, "/");
}

// Redirige al inicio de sesión
header("Location: ../home.php");
exit();
?>
