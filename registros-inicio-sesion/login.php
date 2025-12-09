<?php
session_start();
include('../shortCuts/connect.php');

// =================================================
// 1. PROCESAR EL INICIO DE SESIÓN
// =================================================
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $email = trim($_POST['email-user']);
    $password = $_POST['pass'];

    // Obtener la página a donde volver después del login
    $redirect = '../home.php'; // por defecto

    if (!empty($_POST['return_url'])) {
        $url = $_POST['return_url'];
        // Evitar redirecciones peligrosas
        if (strpos($url, 'login.php') === false && strpos($url, 'logout.php') === false) {
            $redirect = $url;
        }
    }

    $stmt = $connect->prepare("SELECT id_usuario, nombre, correo, contrasena FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['contrasena'])) {
            session_regenerate_id(true);

            $_SESSION['usuario_id']       = $user['id_usuario'];
            $_SESSION['usuario_nombre']   = $user['nombre'];
            $_SESSION['usuario_email']    = $user['correo'];
            $_SESSION['usuario_logueado'] = true;

            setcookie("usuario_id", $user['id_usuario'], time() + 86400 * 30, "/");

            // Mensaje opcional
            $_SESSION['flash_message'] = "¡Bienvenido de vuelta, {$user['nombre']}!";

            // REDIRECCIÓN MÁGICA: vuelve exactamente donde estaba
            header("Location: $redirect");
            exit();
        }
    }

    // Si falla el login
    $error = "Correo o contraseña incorrectos";
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="login.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <style>
        .error { color: #e74c3c; background: #fadad7; padding: 10px; border-radius: 5px; margin: 15px 0; text-align: center; }
    </style>
</head>
<body>
    <main>
        <div class="contenedor">
            <span class="back-icon">
                <a href="../home.php"><img src="../SOURCES/ICONOS-LOGOS/return.svg" alt="Volver"></a>
            </span>

            <div class="tittle-text">
                <h1>Inicio de Sesión</h1>
                <p>¿No tienes cuenta? <a href="register.html">Registrarse</a></p>
            </div>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <!-- ESTA LÍNEA GUARDA AUTOMÁTICAMENTE DÓNDE ESTABA EL USUARIO -->
                <input type="hidden" name="return_url" value="<?php 
                    echo htmlspecialchars(
                        $_GET['return_url'] ?? 
                        ($_SERVER['HTTP_REFERER'] ?? '../home.php')
                    ); 
                ?>">

                <input type="email" name="email-user" placeholder="Correo electrónico" required 
                       value="<?php echo isset($_POST['email-user']) ? htmlspecialchars($_POST['email-user']) : ''; ?>">

                <input type="password" name="pass" placeholder="Contraseña" required>

                <button type="submit" class="send-info"><span>Iniciar Sesión</span></button>
            </form>
        </div>
    </main>
</body>
</html>