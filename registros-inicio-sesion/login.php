<?php
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email-user'];
    $password = $_POST['pass'];

    // Consulta preparada para obtener el hash de la contraseña
    $stmt = $connect->prepare("SELECT contrasena FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($hash);
        $stmt->fetch();

        // Verifica si la contraseña ingresada coincide con el hash
        if (password_verify($password, $hash)) {
            echo "<script>alert('Inicio de sesión exitoso'); window.location.href='../home.html';</script>";
        } else {
            echo "<script>alert('Contraseña incorrecta'); window.location.href='login.html';</script>";
        }
    } else {
        echo "<script>alert('Correo no encontrado'); window.location.href='login.html';</script>";
    }

    $stmt->close();
    $connect->close();
}
?>
