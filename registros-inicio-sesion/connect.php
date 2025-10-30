<?php
// Configuración de conexión
$host = "localhost";
$username = "root";
$password = "";
$db = "modelo_sgbd";

// Intentar conexión
$connect = mysqli_connect($host, $username, $password, $db);

// Validación con mensaje de error detallado
if (!$connect) {
    die("Error al conectar a la base de datos: " . mysqli_connect_error());
}

// Opcional: establecer codificación UTF-8
mysqli_set_charset($connect, "utf8");
?>