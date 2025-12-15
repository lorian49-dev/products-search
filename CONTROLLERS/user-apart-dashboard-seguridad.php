<?php
session_start();
include("../shortCuts/connect.php");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);

// Obtener datos del usuario
$sql_usuario = "SELECT nombre, apellido, correo, telefono, fecha_nacimiento FROM usuario WHERE id_usuario = $usuario_id";
$result_usuario = mysqli_query($connect, $sql_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Procesar cambio de contraseña si se envió el formulario
$mensaje = '';
$tipo_mensaje = ''; // success, error, warning

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'];
    $nuevo_password = $_POST['nuevo_password'];
    $confirmar_password = $_POST['confirmar_password'];

    // Obtener contraseña actual del usuario
    $sql_password = "SELECT password FROM usuario WHERE id_usuario = $usuario_id";
    $result_password = mysqli_query($connect, $sql_password);
    $user_data = mysqli_fetch_assoc($result_password);

    // Verificar contraseña actual
    if (password_verify($password_actual, $user_data['password'])) {
        // Validar nueva contraseña
        if ($nuevo_password === $confirmar_password) {
            if (strlen($nuevo_password) >= 6) {
                // Hash de la nueva contraseña
                $nuevo_password_hash = password_hash($nuevo_password, PASSWORD_DEFAULT);

                // Actualizar en base de datos
                $update_sql = "UPDATE usuario SET password = '$nuevo_password_hash' WHERE id_usuario = $usuario_id";

                if (mysqli_query($connect, $update_sql)) {
                    $mensaje = '✅ Contraseña actualizada correctamente';
                    $tipo_mensaje = 'success';
                } else {
                    $mensaje = '❌ Error al actualizar la contraseña';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = '❌ La nueva contraseña debe tener al menos 6 caracteres';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = '❌ Las nuevas contraseñas no coinciden';
            $tipo_mensaje = 'error';
        }
    } else {
        $mensaje = '❌ La contraseña actual es incorrecta';
        $tipo_mensaje = 'error';
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seguridad y Contraseña</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
     <style>
        /* Estilos del dashboard (mismos que en datos-personales) */
        .dashboard-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            gap: 30px;
        }

        .dashboard-sidebar {
            width: 250px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            height: fit-content;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            gap: 12px;
        }

        .sidebar-menu a.active {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }

        /* ESTILOS ESPECÍFICOS PARA SEGURIDAD */
        .dashboard-content {
            flex: 1;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
        }

        .current-page-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        /* Mensajes */
        .alert-message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
            font-weight: 500;
        }

        .alert-success {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            color: #155724;
        }

        .alert-error {
            background: #f8d7da;
            border: 1px solid #f5c6cb;
            color: #721c24;
        }

        /* Secciones de seguridad */
        .security-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .security-section:last-child {
            border-bottom: none;
        }

        .section-title {
            font-size: 20px;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #1976d2;
        }

        /* Formularios */
        .security-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 500;
            color: #333;
        }

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
            outline: none;
        }

        .password-strength {
            height: 4px;
            background: #dee2e6;
            border-radius: 2px;
            margin-top: 5px;
            overflow: hidden;
        }

        .password-strength-bar {
            height: 100%;
            width: 0%;
            background: #dc3545;
            transition: width 0.3s, background 0.3s;
        }

        .password-strength-bar.weak {
            width: 33%;
            background: #dc3545;
        }

        .password-strength-bar.medium {
            width: 66%;
            background: #ffc107;
        }

        .password-strength-bar.strong {
            width: 100%;
            background: #28a745;
        }

        .password-requirements {
            font-size: 13px;
            color: #6c757d;
            margin-top: 5px;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 5px;
            margin-bottom: 3px;
        }

        .requirement.valid {
            color: #28a745;
        }

        .requirement i.fa-check {
            color: #28a745;
        }

        .requirement i.fa-times {
            color: #dc3545;
        }

        /* Botones */
        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        /* Información de seguridad */
        .security-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            border-left: 4px solid #1976d2;
        }

        .info-card h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 16px;
        }

        .info-card p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .info-card i {
            font-size: 24px;
            margin-bottom: 15px;
            color: #1976d2;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .dashboard-sidebar {
                width: 100%;
            }

            .security-info {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <?php include '../TEMPLATES/header.php' ?>

    <div class="dashboard-container">
        <!-- MENÚ LATERAL -->
        <div class="dashboard-sidebar">
            <div class="sidebar-title">
                <i class="fa-solid fa-user-circle"></i>
                Mi Cuenta
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="../home.php">
                        <i class="fa-solid fa-home"></i>
                        Volver al Home
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard.php">
                        <i class="fa-solid fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-datos-personales.php">
                        <i class="fa-solid fa-user"></i>
                        Datos Personales
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-compras.php">
                        <i class="fa-solid fa-shopping-bag"></i>
                        Mis Compras
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-metodos-pago.php">
                        <i class="fa-solid fa-credit-card"></i>
                        Métodos de Pago
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-seguridad.php" class="active">
                        <i class="fa-solid fa-shield-alt"></i>
                        Seguridad
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-configuracion.php">
                        <i class="fa-solid fa-cog"></i>
                        Configuración
                    </a>
                </li>

                <li class="menu-divider"></li>

                <li>
                    <a href="../registros-inicio-sesion/logout-user.php" class="logout-link">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="dashboard-content">
            <h2 class="current-page-title">
                <i class="fa-solid fa-shield-alt"></i>
                Seguridad y Contraseña
            </h2>

            <p class="text-muted mb-4">
                Cambia tu contraseña y revisa consejos de seguridad para tu cuenta.
            </p>

            <!-- Mensajes -->
            <?php if ($mensaje): ?>
                <div class="alert-message alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Sección 1: Cambiar Contraseña -->
            <div class="security-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-key"></i>
                    Cambiar Contraseña
                </h3>

                <form class="security-form" method="POST">
                    <input type="hidden" name="cambiar_password" value="1">

                    <div class="form-group">
                        <label for="password_actual">Contraseña Actual *</label>
                        <input type="password" id="password_actual" name="password_actual" required>
                    </div>

                    <div class="form-group">
                        <label for="nuevo_password">Nueva Contraseña *</label>
                        <input type="password" id="nuevo_password" name="nuevo_password" required>
                        <div class="password-strength">
                            <div class="password-strength-bar" id="passwordStrengthBar"></div>
                        </div>
                        <div class="password-requirements" id="passwordRequirements">
                            <div class="requirement" id="reqLength">
                                <i class="fa-solid fa-times"></i>
                                <span>Al menos 6 caracteres</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_password">Confirmar Nueva Contraseña *</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" required>
                        <div id="passwordMatch" class="password-requirements"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn">
                        <i class="fa-solid fa-save"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>

            <!-- Sección 2: Consejos de Seguridad -->
            <div class="security-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-info-circle"></i>
                    Consejos de Seguridad
                </h3>

                <div class="security-info">
                    <div class="info-card">
                        <i class="fa-solid fa-lock"></i>
                        <h4>Contraseñas Seguras</h4>
                        <p>Usa contraseñas únicas y complejas para cada cuenta. Cambia tu contraseña regularmente.</p>
                    </div>

                    <div class="info-card">
                        <i class="fa-solid fa-user-shield"></i>
                        <h4>No Compartas Credenciales</h4>
                        <p>Nunca compartas tu contraseña o datos de acceso con nadie, ni siquiera con soporte técnico.</p>
                    </div>

                    <div class="info-card">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        <h4>Cierra Sesión</h4>
                        <p>Siempre cierra sesión cuando uses computadoras públicas o compartidas.</p>
                    </div>

                    <div class="info-card">
                        <i class="fa-solid fa-shield-alt"></i>
                        <h4>Actualizaciones</h4>
                        <p>Mantén tu navegador y sistema operativo actualizados para mayor seguridad.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación simple de contraseña
        const nuevoPassword = document.getElementById('nuevo_password');
        const confirmarPassword = document.getElementById('confirmar_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const reqLength = document.getElementById('reqLength');

        nuevoPassword.addEventListener('input', function() {
            const password = this.value;

            // Validar longitud mínima
            const hasLength = password.length >= 6;

            // Actualizar requisito
            updateRequirement(reqLength, hasLength);

            // Calcular fortaleza simple
            passwordStrengthBar.className = 'password-strength-bar';
            if (password.length < 6) {
                passwordStrengthBar.classList.add('weak');
            } else if (password.length < 10) {
                passwordStrengthBar.classList.add('medium');
            } else {
                passwordStrengthBar.classList.add('strong');
            }
        });

        confirmarPassword.addEventListener('input', function() {
            const match = nuevoPassword.value === this.value;

            if (this.value === '') {
                document.getElementById('passwordMatch').innerHTML = '';
            } else if (match) {
                document.getElementById('passwordMatch').innerHTML =
                    '<span style="color: #28a745;"><i class="fa-solid fa-check"></i> Las contraseñas coinciden</span>';
            } else {
                document.getElementById('passwordMatch').innerHTML =
                    '<span style="color: #dc3545;"><i class="fa-solid fa-times"></i> Las contraseñas no coinciden</span>';
            }
        });

        function updateRequirement(element, isValid) {
            const icon = element.querySelector('i');
            if (isValid) {
                icon.className = 'fa-solid fa-check';
                element.classList.add('valid');
            } else {
                icon.className = 'fa-solid fa-times';
                element.classList.remove('valid');
            }
        }

        // Validación básica del formulario
        document.querySelector('.security-form').addEventListener('submit', function(e) {
            const passwordActual = document.getElementById('password_actual').value;
            const nuevoPass = document.getElementById('nuevo_password').value;
            const confirmarPass = document.getElementById('confirmar_password').value;

            if (nuevoPass.length < 6) {
                e.preventDefault();
                alert('La nueva contraseña debe tener al menos 6 caracteres');
                return false;
            }

            if (nuevoPass !== confirmarPass) {
                e.preventDefault();
                alert('Las contraseñas no coinciden');
                return false;
            }

            return true;
        });
    </script>
    <script src="../scripts/user-apart-dashboard.js"></script>
    <?php include '../TEMPLATES/footer.php' ?>
</body>

</html>