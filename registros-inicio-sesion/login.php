    <?php
    session_start();
    include('connect.php');

    if ($_SERVER["REQUEST_METHOD"] == "POST") {
        $email = $_POST['email-user'];
        $password = $_POST['pass'];

        // Consulta segura
        $stmt = $connect->prepare("SELECT id_usuario, nombre, correo, contrasena FROM usuario WHERE correo = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        // Verificar si el usuario existe
        if ($result->num_rows === 1) {
            $usuario = $result->fetch_assoc();

            // Verificar contraseña
            if (password_verify($password, $usuario['contrasena'])) {

                // Guardar información en sesión correctamente
                $_SESSION['usuario_id'] = $usuario['id_usuario'];   // ← CORREGIDO
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['correo'];

                // Cookie opcional
                setcookie("usuario_id", $usuario['id_usuario'], time() + (86400 * 30), "/");

                $_SESSION['flash_message'] = 'Sesion Iniciada :)';
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
