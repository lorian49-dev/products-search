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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['cambiar_password'])) {
        $password_actual = $_POST['password_actual'];
        $nuevo_password = $_POST['nuevo_password'];
        $confirmar_password = $_POST['confirmar_password'];

        // Obtener contraseña actual del usuario
        $sql_password = "SELECT contrasena FROM usuario WHERE id_usuario = $usuario_id";
        $result_password = mysqli_query($connect, $sql_password);
        $user_data = mysqli_fetch_assoc($result_password);

        // Verificar contraseña actual
        if (password_verify($password_actual, $user_data['contrasena'])) {
            // Validar nueva contraseña
            if ($nuevo_password === $confirmar_password) {
                if (strlen($nuevo_password) >= 8) {
                    // Hash de la nueva contraseña
                    $nuevo_password_hash = password_hash($nuevo_password, PASSWORD_DEFAULT);

                    // Actualizar en base de datos
                    $update_sql = "UPDATE usuario SET contrasena = '$nuevo_password_hash' WHERE id_usuario = $usuario_id";

                    if (mysqli_query($connect, $update_sql)) {
                        $mensaje = 'Contraseña actualizada correctamente';
                        $tipo_mensaje = 'success';

                        // Registrar actividad (opcional)
                        $actividad_sql = "INSERT INTO actividades_usuario (id_usuario, actividad, fecha) 
                                         VALUES ('$usuario_id', 'Cambio de contraseña', NOW())";
                        mysqli_query($connect, $actividad_sql);
                    } else {
                        $mensaje = 'Error al actualizar la contraseña';
                        $tipo_mensaje = 'error';
                    }
                } else {
                    $mensaje = 'La nueva contraseña debe tener al menos 8 caracteres';
                    $tipo_mensaje = 'error';
                }
            } else {
                $mensaje = 'Las nuevas contraseñas no coinciden';
                $tipo_mensaje = 'error';
            }
        } else {
            $mensaje = 'La contraseña actual es incorrecta';
            $tipo_mensaje = 'error';
        }
    }

    // Procesar verificación en dos pasos
    if (isset($_POST['toggle_2fa'])) {
        $habilitar_2fa = isset($_POST['habilitar_2fa']) ? 1 : 0;

        $update_2fa = "UPDATE usuario SET two_factor_auth = '$habilitar_2fa' WHERE id_usuario = $usuario_id";
        if (mysqli_query($connect, $update_2fa)) {
            $mensaje = $habilitar_2fa ? 'Verificación en dos pasos habilitada' : 'Verificación en dos pasos deshabilitada';
            $tipo_mensaje = 'success';
        } else {
            $mensaje = 'Error al actualizar la verificación en dos pasos';
            $tipo_mensaje = 'error';
        }
    }
}

// Obtener configuración actual de 2FA
$sql_2fa = "SELECT two_factor_auth FROM usuario WHERE id_usuario = $usuario_id";
$result_2fa = mysqli_query($connect, $sql_2fa);
$config_2fa = mysqli_fetch_assoc($result_2fa);
$two_factor_enabled = $config_2fa['two_factor_auth'] ?? 0;

// Obtener sesiones activas (si tienes tabla de sesiones)
$sesiones_activas = [];
$sql_sesiones = "SELECT * FROM sesiones_usuario WHERE id_usuario = $usuario_id ORDER BY fecha_inicio DESC LIMIT 5";
$result_sesiones = mysqli_query($connect, $sql_sesiones);
if ($result_sesiones) {
    while ($row = mysqli_fetch_assoc($result_sesiones)) {
        $sesiones_activas[] = $row;
    }
}

// Obtener actividades recientes (si tienes tabla de actividades)
$actividades_recientes = [];
$sql_actividades = "SELECT * FROM actividades_usuario WHERE id_usuario = $usuario_id ORDER BY fecha DESC LIMIT 10";
$result_actividades = mysqli_query($connect, $sql_actividades);
if ($result_actividades) {
    while ($row = mysqli_fetch_assoc($result_actividades)) {
        $actividades_recientes[] = $row;
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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

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

        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
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

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        /* Configuración de 2FA */
        .two-factor-settings {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 20px;
            background: #f8f9fa;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .two-factor-info {
            flex: 1;
        }

        .two-factor-info h4 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .two-factor-info p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
        }

        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .toggle-switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }

        .toggle-slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            transition: .4s;
            border-radius: 34px;
        }

        .toggle-slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked+.toggle-slider {
            background-color: #28a745;
        }

        input:checked+.toggle-slider:before {
            transform: translateX(26px);
        }

        /* Sesiones activas */
        .sessions-list {
            margin-top: 20px;
        }

        .session-item {
            display: flex;
            align-items: center;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
            border-left: 4px solid #1976d2;
        }

        .session-item.current {
            border-left-color: #28a745;
            background: #f0fff4;
        }

        .session-icon {
            font-size: 24px;
            margin-right: 15px;
            color: #1976d2;
        }

        .session-details {
            flex: 1;
        }

        .session-details h5 {
            margin: 0 0 5px 0;
            color: #333;
        }

        .session-details p {
            margin: 0;
            color: #6c757d;
            font-size: 13px;
        }

        .session-actions {
            display: flex;
            gap: 10px;
        }

        .btn-session {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            font-size: 12px;
            cursor: pointer;
        }

        .btn-logout-session {
            background: #dc3545;
            color: white;
        }

        .btn-logout-session:hover {
            background: #c82333;
        }

        /* Actividades recientes */
        .activity-list {
            margin-top: 20px;
        }

        .activity-item {
            display: flex;
            align-items: center;
            padding: 12px;
            border-bottom: 1px solid #eee;
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 40px;
            height: 40px;
            background: #e3f2fd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
            color: #1976d2;
        }

        .activity-details {
            flex: 1;
        }

        .activity-details h5 {
            margin: 0 0 3px 0;
            font-size: 14px;
            color: #333;
        }

        .activity-details p {
            margin: 0;
            font-size: 12px;
            color: #6c757d;
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

            .two-factor-settings {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
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
                    <a href="../index.php">
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
                    <a href="user-apart-dashboard-mis-compras.php">
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
                    <a href="../logout.php" class="logout-link">
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
                Gestiona la seguridad de tu cuenta, cambia tu contraseña y revisa la actividad.
            </p>

            <!-- Mensajes -->
            <?php if ($mensaje): ?>
                <div class="alert-message alert-<?php echo $tipo_mensaje; ?>">
                    <i class="fa-solid <?php echo $tipo_mensaje == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle'; ?>"></i>
                    <?php echo htmlspecialchars($mensaje); ?>
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
                                <span>Al menos 8 caracteres</span>
                            </div>
                            <div class="requirement" id="reqUppercase">
                                <i class="fa-solid fa-times"></i>
                                <span>Al menos una mayúscula</span>
                            </div>
                            <div class="requirement" id="reqLowercase">
                                <i class="fa-solid fa-times"></i>
                                <span>Al menos una minúscula</span>
                            </div>
                            <div class="requirement" id="reqNumber">
                                <i class="fa-solid fa-times"></i>
                                <span>Al menos un número</span>
                            </div>
                            <div class="requirement" id="reqSpecial">
                                <i class="fa-solid fa-times"></i>
                                <span>Al menos un carácter especial</span>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="confirmar_password">Confirmar Nueva Contraseña *</label>
                        <input type="password" id="confirmar_password" name="confirmar_password" required>
                        <div id="passwordMatch" class="password-requirements"></div>
                    </div>

                    <button type="submit" class="btn-submit" id="submitBtn" disabled>
                        <i class="fa-solid fa-save"></i> Cambiar Contraseña
                    </button>
                </form>
            </div>

            <!-- Sección 2: Verificación en Dos Pasos -->
            <div class="security-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-mobile-alt"></i>
                    Verificación en Dos Pasos (2FA)
                </h3>

                <div class="two-factor-settings">
                    <div class="two-factor-info">
                        <h4>Autenticación de Dos Factores</h4>
                        <p>Añade una capa extra de seguridad a tu cuenta. Se te pedirá un código especial cada vez que inicies sesión.</p>
                    </div>

                    <form method="POST">
                        <input type="hidden" name="toggle_2fa" value="1">
                        <label class="toggle-switch">
                            <input type="checkbox" name="habilitar_2fa" value="1"
                                <?php echo $two_factor_enabled ? 'checked' : ''; ?>
                                onchange="this.form.submit()">
                            <span class="toggle-slider"></span>
                        </label>
                    </form>
                </div>

                <?php if ($two_factor_enabled): ?>
                    <div class="alert-message alert-success">
                        <i class="fa-solid fa-check-circle"></i>
                        La verificación en dos pasos está activada para tu cuenta.
                    </div>
                <?php else: ?>
                    <div class="alert-message alert-warning">
                        <i class="fa-solid fa-exclamation-triangle"></i>
                        La verificación en dos pasos no está activada. Te recomendamos activarla para mayor seguridad.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Sección 3: Sesiones Activas -->
            <div class="security-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-laptop"></i>
                    Sesiones Activas
                </h3>

                <p class="text-muted">Estas son las sesiones activas en tu cuenta. Si reconoces alguna actividad sospechosa, cierra la sesión.</p>

                <div class="sessions-list">
                    <!-- Sesión actual -->
                    <div class="session-item current">
                        <div class="session-icon">
                            <i class="fa-solid fa-laptop"></i>
                        </div>
                        <div class="session-details">
                            <h5>Sesión Actual</h5>
                            <p>
                                <strong>Dispositivo:</strong> <?php echo $_SERVER['HTTP_USER_AGENT']; ?><br>
                                <strong>IP:</strong> <?php echo $_SERVER['REMOTE_ADDR']; ?><br>
                                <strong>Iniciada:</strong> <?php echo date('d/m/Y H:i:s'); ?>
                            </p>
                        </div>
                        <div class="session-actions">
                            <button class="btn-session btn-logout-session" disabled>
                                Actual
                            </button>
                        </div>
                    </div>

                    <!-- Otras sesiones (si las hay) -->
                    <?php if (count($sesiones_activas) > 0): ?>
                        <?php foreach ($sesiones_activas as $sesion): ?>
                            <?php if ($sesion['id_sesion'] != session_id()): ?>
                                <div class="session-item">
                                    <div class="session-icon">
                                        <i class="fa-solid fa-mobile-alt"></i>
                                    </div>
                                    <div class="session-details">
                                        <h5><?php echo htmlspecialchars($sesion['dispositivo'] ?? 'Dispositivo desconocido'); ?></h5>
                                        <p>
                                            <strong>IP:</strong> <?php echo htmlspecialchars($sesion['ip']); ?><br>
                                            <strong>Última actividad:</strong> <?php echo date('d/m/Y H:i', strtotime($sesion['fecha_inicio'])); ?>
                                        </p>
                                    </div>
                                    <div class="session-actions">
                                        <form method="POST" action="cerrar-sesion-remota.php" style="display: inline;">
                                            <input type="hidden" name="sesion_id" value="<?php echo $sesion['id_sesion']; ?>">
                                            <button type="submit" class="btn-session btn-logout-session">
                                                Cerrar Sesión
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No hay otras sesiones activas.</p>
                        </div>
                    <?php endif; ?>
                </div>

                <form method="POST" action="cerrar-todas-sesiones.php">
                    <button type="submit" class="btn-secondary" onclick="return confirm('¿Estás seguro de cerrar todas las sesiones excepto la actual?');">
                        <i class="fa-solid fa-sign-out-alt"></i> Cerrar Todas las Otras Sesiones
                    </button>
                </form>
            </div>

            <!-- Sección 4: Actividad Reciente -->
            <div class="security-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-history"></i>
                    Actividad Reciente
                </h3>

                <div class="activity-list">
                    <?php if (count($actividades_recientes) > 0): ?>
                        <?php foreach ($actividades_recientes as $actividad): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fa-solid 
                                        <?php
                                        if (strpos($actividad['actividad'], 'inició') !== false) {
                                            echo 'fa-sign-in-alt';
                                        } elseif (strpos($actividad['actividad'], 'contraseña') !== false) {
                                            echo 'fa-key';
                                        } elseif (strpos($actividad['actividad'], 'pago') !== false) {
                                            echo 'fa-credit-card';
                                        } elseif (strpos($actividad['actividad'], 'dirección') !== false) {
                                            echo 'fa-map-marker-alt';
                                        } else {
                                            echo 'fa-user';
                                        }
                                        ?>">
                                    </i>
                                </div>
                                <div class="activity-details">
                                    <h5><?php echo htmlspecialchars($actividad['actividad']); ?></h5>
                                    <p><?php echo date('d/m/Y H:i:s', strtotime($actividad['fecha'])); ?></p>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="empty-state">
                            <p>No hay actividad registrada recientemente.</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Sección 5: Información de Seguridad -->
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
                        <h4>Verificación en Dos Pasos</h4>
                        <p>Activa la verificación en dos pasos para añadir una capa extra de seguridad.</p>
                    </div>

                    <div class="info-card">
                        <i class="fa-solid fa-eye-slash"></i>
                        <h4>Privacidad</h4>
                        <p>No compartas tus credenciales de inicio de sesión con nadie.</p>
                    </div>

                    <div class="info-card">
                        <i class="fa-solid fa-bell"></i>
                        <h4>Alertas de Seguridad</h4>
                        <p>Habilita las notificaciones para recibir alertas de actividad sospechosa.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Validación de fortaleza de contraseña
        const nuevoPassword = document.getElementById('nuevo_password');
        const confirmarPassword = document.getElementById('confirmar_password');
        const passwordStrengthBar = document.getElementById('passwordStrengthBar');
        const submitBtn = document.getElementById('submitBtn');

        // Elementos de requisitos
        const reqLength = document.getElementById('reqLength');
        const reqUppercase = document.getElementById('reqUppercase');
        const reqLowercase = document.getElementById('reqLowercase');
        const reqNumber = document.getElementById('reqNumber');
        const reqSpecial = document.getElementById('reqSpecial');
        const passwordMatch = document.getElementById('passwordMatch');

        let passwordValid = false;
        let passwordsMatch = false;

        nuevoPassword.addEventListener('input', function() {
            const password = this.value;

            // Validar requisitos
            const hasLength = password.length >= 8;
            const hasUppercase = /[A-Z]/.test(password);
            const hasLowercase = /[a-z]/.test(password);
            const hasNumber = /[0-9]/.test(password);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(password);

            // Actualizar íconos de requisitos
            updateRequirement(reqLength, hasLength);
            updateRequirement(reqUppercase, hasUppercase);
            updateRequirement(reqLowercase, hasLowercase);
            updateRequirement(reqNumber, hasNumber);
            updateRequirement(reqSpecial, hasSpecial);

            // Calcular fortaleza
            let strength = 0;
            if (hasLength) strength++;
            if (hasUppercase) strength++;
            if (hasLowercase) strength++;
            if (hasNumber) strength++;
            if (hasSpecial) strength++;

            // Actualizar barra de fortaleza
            passwordStrengthBar.className = 'password-strength-bar';
            if (strength <= 2) {
                passwordStrengthBar.classList.add('weak');
            } else if (strength <= 4) {
                passwordStrengthBar.classList.add('medium');
            } else {
                passwordStrengthBar.classList.add('strong');
            }

            // Verificar si la contraseña es válida
            passwordValid = hasLength && hasUppercase && hasLowercase && hasNumber && hasSpecial;
            updateSubmitButton();
        });

        confirmarPassword.addEventListener('input', function() {
            const match = nuevoPassword.value === this.value;
            passwordsMatch = match;

            if (this.value === '') {
                passwordMatch.innerHTML = '';
            } else if (match) {
                passwordMatch.innerHTML = '<span style="color: #28a745;"><i class="fa-solid fa-check"></i> Las contraseñas coinciden</span>';
            } else {
                passwordMatch.innerHTML = '<span style="color: #dc3545;"><i class="fa-solid fa-times"></i> Las contraseñas no coinciden</span>';
            }

            updateSubmitButton();
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

        function updateSubmitButton() {
            if (passwordValid && passwordsMatch) {
                submitBtn.disabled = false;
                submitBtn.style.opacity = '1';
                submitBtn.style.cursor = 'pointer';
            } else {
                submitBtn.disabled = true;
                submitBtn.style.opacity = '0.6';
                submitBtn.style.cursor = 'not-allowed';
            }
        }

        // Mostrar/ocultar contraseña (opcional)
        function togglePasswordVisibility(inputId) {
            const input = document.getElementById(inputId);
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }

        // Inicializar
        updateSubmitButton();
    </script>
</body>

</html>