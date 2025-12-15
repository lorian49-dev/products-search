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
        
        // Validar que sea una URL segura y no sea una página de autenticación
        if (isValidRedirectUrl($url)) {
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

            // REDIRECCIÓN: siempre a home.php después de recuperar contraseña
            // Si venimos de recuperar contraseña, forzar home.php
            if (isset($_SESSION['came_from_password_recovery']) && $_SESSION['came_from_password_recovery']) {
                unset($_SESSION['came_from_password_recovery']);
                $redirect = '../home.php';
            }
            
            header("Location: $redirect");
            exit();
        }
    }

    // Si falla el login
    $error = "Correo o contraseña incorrectos";
}

// Función para validar URLs de redirección
function isValidRedirectUrl($url) {
    // Lista de páginas a las que NO se debe redirigir
    $forbidden_patterns = [
        'login.php',
        'logout.php',
        'recuperar-contrasena',
        'register',
        'auth/',
        'signin',
        'signup'
    ];
    
    // Verificar que no contenga patrones prohibidos
    foreach ($forbidden_patterns as $pattern) {
        if (stripos($url, $pattern) !== false) {
            return false;
        }
    }
    
    // Verificar que sea una URL válida y segura
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        // Si no es una URL completa, verificar que sea una ruta relativa segura
        if (preg_match('/\.\.\//', $url) || preg_match('/[<>"\']/', $url)) {
            return false;
        }
    }
    
    return true;
}

// =================================================
// 2. DETECTAR SI VIENES DE RECUPERACIÓN DE CONTRASEÑA
// =================================================
// Guardar en sesión si venimos de recuperación de contraseña
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'recuperar-contrasena') !== false) {
    $_SESSION['came_from_password_recovery'] = true;
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
            </div>

            <?php if (isset($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form method="post" action="login.php">
                <!-- SOLUCIÓN DEFINITIVA: No usar HTTP_REFERER para redirección -->
                <input type="hidden" name="return_url" value="<?php 
                    // OPCIÓN A: Solo usar parámetro GET (más seguro)
                    if (!empty($_GET['return_url'])) {
                        $url = $_GET['return_url'];
                        // Validar que sea segura
                        if (isValidRedirectUrl($url)) {
                            echo htmlspecialchars($url);
                        } else {
                            echo '../home.php';
                        }
                    } 
                    // OPCIÓN B: Forzar siempre home.php después de recuperación
                    elseif (isset($_SESSION['came_from_password_recovery']) && $_SESSION['came_from_password_recovery']) {
                        echo '../home.php';
                    }
                    // OPCIÓN C: Home por defecto
                    else {
                        echo '../home.php';
                    }
                ?>">

                <input type="email" name="email-user" placeholder="Correo electrónico" required 
                       value="<?php echo isset($_POST['email-user']) ? htmlspecialchars($_POST['email-user']) : ''; ?>">

                <input type="password" name="pass" placeholder="Contraseña" required>
                <p>¿No tienes cuenta? <a href="register.html">Registrarse</a></p>
                <p>¿Olvidaste Tu Contraseña? <a href="../CONTROLLERS/recuperar-contrasena.PHP" id="recovery-link">Recuperar Contraseña</a></p>

                <button type="submit" class="send-info"><span>Iniciar Sesión</span></button>
            </form>
        </div>
    </main>
    
    <script>
    // Solución adicional con JavaScript
    document.getElementById('recovery-link').addEventListener('click', function(e) {
        // Limpiar cualquier return_url almacenado
        sessionStorage.removeItem('return_url');
        
        // Opcional: limpiar el campo hidden del formulario
        document.querySelector('input[name="return_url"]').value = '../home.php';
    });
    </script>
</body>
</html>