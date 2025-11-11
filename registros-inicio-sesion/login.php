<?php
session_start();
include('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email-user'];
    $password = $_POST['pass'];

    // Consulta segura (evita SQL Injection)
    $stmt = $connect->prepare("SELECT id_usuario, nombre, correo, contrasena FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    // Verificamos si el usuario existe
    if ($result->num_rows === 1) {
        $usuario = $result->fetch_assoc();

        // Verificamos la contraseña encriptada
        if (password_verify($password, $usuario['contrasena'])) {
            
            // Guardamos datos en sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_nombre'] = $usuario['nombre'];
            $_SESSION['usuario_email'] = $usuario['correo'];

            // Creamos cookie para recordar sesión 30 días
            setcookie("usuario_id", $usuario['id'], time() + (86400 * 30), "/");
            // Redirigimos a la página principal (home.php)
            echo "<script>
                alert('Inicio de sesion exitoso');
            </script>";
            header("Location: ../home.php");
            exit();
            
        } else {
            echo "<script>
                alert('Contraseña incorrecta');
                window.location.href='login.html';
            </script>";
        }

    } else {
        echo "<script>
            alert('Correo no encontrado');
            window.location.href='login.html';
        </script>";
    }

    $stmt->close();
    $connect->close();
}
?>
