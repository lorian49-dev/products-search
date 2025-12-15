<?php
session_start();
include("../shortCuts/connect.php");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);

// Obtener datos básicos del usuario
$sql_usuario = "SELECT nombre, apellido, correo, telefono, fecha_nacimiento FROM usuario WHERE id_usuario = $usuario_id";
$result_usuario = mysqli_query($connect, $sql_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Procesar eliminación de cuenta
$mensaje = '';
$tipo_mensaje = ''; // success, error

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Procesar eliminación de cuenta
    if (isset($_POST['eliminar_cuenta'])) {
        $confirmacion = $_POST['confirmacion'];
        $password = $_POST['password_confirmacion'];

        // Verificar contraseña
        $sql_password = "SELECT password FROM usuario WHERE id_usuario = $usuario_id";
        $result_password = mysqli_query($connect, $sql_password);
        $user_data = mysqli_fetch_assoc($result_password);

        if (!password_verify($password, $user_data['password'])) {
            $mensaje = '❌ Contraseña incorrecta';
            $tipo_mensaje = 'error';
        } elseif ($confirmacion !== 'ELIMINAR') {
            $mensaje = '❌ Debes escribir "ELIMINAR" para confirmar';
            $tipo_mensaje = 'error';
        } else {
            // Aquí puedes redirigir a una página de confirmación final
            // o eliminar directamente (cuidado con datos relacionados)
            $mensaje = '⚠️ Función de eliminación de cuenta en desarrollo';
            $tipo_mensaje = 'warning';
        }
    }
}
$sql_usuario = "SELECT nombre, apellido, correo, telefono, fecha_nacimiento FROM usuario WHERE id_usuario = $usuario_id";
$result_usuario = mysqli_query($connect, $sql_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Función para descargar datos del usuario
function descargarDatosUsuario($usuario_id, $usuario, $direcciones, $metodos_pago, $pedidos)
{
    $datos_usuario = [
        'informacion_general' => [
            'id_usuario' => $usuario_id,
            'fecha_descarga' => date('Y-m-d H:i:s'),
            'formato' => 'JSON'
        ],
        'datos_personales' => [
            'nombre' => $usuario['nombre'] ?? '',
            'apellido' => $usuario['apellido'] ?? '',
            'correo' => $usuario['correo'] ?? '',
            'telefono' => $usuario['telefono'] ?? '',
            'fecha_nacimiento' => $usuario['fecha_nacimiento'] ?? ''
        ],
        'direcciones' => $direcciones,
        'metodos_pago' => $metodos_pago,
        'historial_pedidos' => $pedidos,
        'estadisticas' => [
            'total_direcciones' => count($direcciones),
            'total_metodos_pago' => count($metodos_pago),
            'total_pedidos' => count($pedidos)
        ]
    ];

    // Convertir a JSON formateado
    $json_data = json_encode($datos_usuario, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    // Configurar headers para descarga
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="mis_datos_' . date('Y-m-d') . '.json"');
    header('Content-Length: ' . strlen($json_data));
    header('Cache-Control: no-cache, must-revalidate');
    header('Expires: 0');

    // Enviar datos
    echo $json_data;
    exit;
}


// Obtener direcciones del usuario
$sql_direcciones = "SELECT * FROM direcciones WHERE id_usuario = $usuario_id";
$result_direcciones = mysqli_query($connect, $sql_direcciones);
$direcciones = [];
if ($result_direcciones) {
    while ($row = mysqli_fetch_assoc($result_direcciones)) {
        $direcciones[] = $row;
    }
}

// Obtener métodos de pago del usuario (si tienes la tabla)
$sql_metodos_pago = "SELECT * FROM metodos_pago WHERE id_usuario = $usuario_id";
$result_metodos_pago = mysqli_query($connect, $sql_metodos_pago);
$metodos_pago = [];
if ($result_metodos_pago) {
    while ($row = mysqli_fetch_assoc($result_metodos_pago)) {
        // Ocultar datos sensibles
        if (isset($row['numero_tarjeta'])) {
            $row['numero_tarjeta'] = '**** **** **** ' . substr($row['numero_tarjeta'], -4);
        }
        if (isset($row['cvv'])) {
            $row['cvv'] = '***';
        }
        $metodos_pago[] = $row;
    }
}

// Obtener pedidos/compras del usuario (si tienes la tabla)
$sql_pedidos = "SELECT * FROM pedido WHERE id_cliente = $usuario_id ORDER BY fecha_pedido DESC";
$result_pedidos = mysqli_query($connect, $sql_pedidos);
$pedidos = [];
if ($result_pedidos) {
    while ($row = mysqli_fetch_assoc($result_pedidos)) {
        $pedidos[] = $row;
    }
}

// Procesar descarga de datos
if (isset($_GET['descargar_datos']) && $_GET['descargar_datos'] == 'json') {
    descargarDatosUsuario($usuario_id, $usuario, $direcciones, $metodos_pago, $pedidos);
    exit;
}

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/header.css">
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

        /* ESTILOS ESPECÍFICOS PARA CONFIGURACIÓN */
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

        .alert-warning {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
        }

        /* Secciones */
        .config-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 1px solid #eee;
        }

        .config-section:last-child {
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
        .config-form {
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

        .btn-danger {
            background: #dc3545;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            cursor: pointer;
        }

        .btn-danger:hover {
            background: #c82333;
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

        /* Tarjetas de información */
        .info-card {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #1976d2;
        }

        .info-card h4 {
            margin: 0 0 10px 0;
            color: #333;
            font-size: 18px;
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

        /* Sección peligrosa */
        .danger-zone {
            background: #f8d7da;
            border: 2px solid #f5c6cb;
            border-radius: 10px;
            padding: 25px;
            margin-top: 30px;
        }

        .danger-zone h4 {
            color: #721c24;
            margin-bottom: 15px;
        }

        .danger-zone p {
            color: #721c24;
            margin-bottom: 20px;
        }

        .danger-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            padding: 15px;
            border-radius: 6px;
            margin: 15px 0;
            color: #856404;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .dashboard-sidebar {
                width: 100%;
            }
        }

        /* Añade esto en la sección de estilos */
        .format-option {
            transition: all 0.3s;
            height: 100%;
        }

        .format-option:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .format-option.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        .download-stats {
            display: flex;
            gap: 20px;
            margin-top: 20px;
            flex-wrap: wrap;
        }

        .stat-item {
            background: #e9ecef;
            padding: 10px 15px;
            border-radius: 6px;
            text-align: center;
            flex: 1;
            min-width: 120px;
        }

        .stat-number {
            font-size: 24px;
            font-weight: bold;
            color: #1976d2;
            display: block;
        }

        .stat-label {
            font-size: 12px;
            color: #6c757d;
        }
    </style>
</head>

<body>
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <a href="../home.php"><h1>HERMES</h1></a>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="../CONTROLLERS/search-products.php" method="GET" style="width:100%">
                        <li class="input-search-product-li">
                            <input type="text" name="search-product" id="input-search-product"
                                placeholder="Buscar producto..." value="" autocomplete="off">
                            <button type="submit" class="button-search"><i
                                    class="fa-solid fa-magnifying-glass"></i></button>
                            <div id="results-container"></div>
                        </li>
                    </form>

                    </li>
                </div>
            </ul>
        </div>
        <div class="bottom">
            <nav>
                <ul>
                    <li><span id="span-menu-categoria">Categorias</span>
                        <div id="menu-categoria" class="menu-categoria">
                            <ul>
                                <li>Electrodomesticos</li>
                                <li>Tecnologia</li>
                                <li>Hogar</li>
                                <li>Moda</li>
                                <li>Deportes</li>
                                <li>Belleza</li>
                                <li>Jugueteria</li>
                                <li>Automotriz</li>
                                <li>Electronica</li>
                                <li>Mascotas</li>
                                <li>Arte</li>
                            </ul>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <li><span id="venderPage">Vender</span></li>
                    <?php endif; ?>
                    <li><span id="ayuda-listado">Ayuda</span>
                        <div id="menu-ayuda" class="menu-categoria">
                            <ul>
                                <li>Informacion</li>
                                <li>PQRS</li>
                                <li>Contactos</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="account-header">
                <!-- perfil usuario -->
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="perfil-menu">
                        <button class="perfil-btn"> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="../CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout-user.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.php"><span class="sisu-buttons"> Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons"> Sign Up</span></a>
                <?php endif; ?>
                <!-- fin del menu despegable -->
            </div>
            <div class="icons-header">
                <!-- Carrito - ENLACE SIMPLE -->
                <span><a href="../CONTROLLERS/cart.php" style="z-index:100;"><img src="../SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></a></span>
            </div>
        </div>
    </header>

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
                    <a href="user-apart-dashboard-seguridad.php">
                        <i class="fa-solid fa-shield-alt"></i>
                        Seguridad
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-configuracion.php" class="active">
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
                <i class="fa-solid fa-cog"></i>
                Configuración
            </h2>

            <p class="text-muted mb-4">
                Gestiona las opciones avanzadas de tu cuenta.
            </p>

            <!-- Mensajes -->
            <?php if ($mensaje): ?>
                <div class="alert-message alert-<?php echo $tipo_mensaje; ?>">
                    <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <!-- Sección 1: Información de la Cuenta -->
            <div class="config-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-info-circle"></i>
                    Información de tu Cuenta
                </h3>

                <div class="info-card">
                    <i class="fa-solid fa-user"></i>
                    <h4>Datos de tu perfil</h4>
                    <p>
                        <strong>Nombre:</strong> <?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($usuario['correo']); ?><br>
                        <strong>Teléfono:</strong> <?php echo htmlspecialchars($usuario['telefono']); ?><br>
                        <strong>Miembro desde:</strong> <?php echo htmlspecialchars($usuario['fecha_nacimiento']); ?>
                    </p>
                    <a href="user-apart-dashboard.php" class="btn-secondary" style="margin-top: 15px;">
                        <i class="fa-solid fa-edit"></i> Editar Datos Personales
                    </a>
                </div>
            </div>

            <!-- Sección 2: Datos Personales -->
            <!-- Sección 2: Exportar Mis Datos -->
            <div class="config-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-download"></i>
                    Exportar Mis Datos
                </h3>

                <div class="info-card">
                    <i class="fa-solid fa-file-export"></i>
                    <h4>Descargar mis datos personales</h4>
                    <p>Descarga inmediatamente una copia de todos tus datos en formato JSON.</p>

                    <div class="row" style="margin-top: 20px;">
                        <div class="col-md-6">
                            <div class="format-option" style="text-align: center; padding: 15px; border: 2px solid #1976d2; border-radius: 8px;">
                                <i class="fa-solid fa-file-code" style="font-size: 40px; color: #1976d2; margin-bottom: 10px;"></i>
                                <h5>Formato JSON</h5>
                                <p style="font-size: 14px; color: #666;">Estructurado y fácil de leer</p>
                                <a href="?descargar_datos=json" class="btn-submit" style="display: inline-block; margin-top: 10px;">
                                    <i class="fa-solid fa-download"></i> Descargar JSON
                                </a>
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="format-option" style="text-align: center; padding: 15px; border: 2px solid #dee2e6; border-radius: 8px; opacity: 0.6;">
                                <i class="fa-solid fa-file-pdf" style="font-size: 40px; color: #dc3545; margin-bottom: 10px;"></i>
                                <h5>Formato PDF</h5>
                                <p style="font-size: 14px; color: #666;">(Próximamente)</p>
                                <button class="btn-secondary" style="display: inline-block; margin-top: 10px;" disabled>
                                    <i class="fa-solid fa-clock"></i> Próximamente
                                </button>
                            </div>
                        </div>
                    </div>

                    <div style="margin-top: 20px;">
                        <h5>¿Qué incluye la descarga?</h5>
                        <ul style="margin-left: 20px; color: #666;">
                            <li>En esta descarga se incluyen todos los datos relacionados de la cuenta, tanto de los pedidos, direcciones, metodos de pago, historial, entre otros.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Sección 3: Zona de Peligro -->
            <div>
                <h4>Eliminacion de cuenta</h4>
                <p>Estas acciones son irreversibles. Por favor, procede con precaución.</p>

                <div class="danger-note">
                    <i class="fa-solid fa-exclamation-circle"></i>
                    <strong>Eliminacion permanente</strong> La eliminación de cuenta es PERMANENTE e IRREVERSIBLE.
                    Perderás acceso a:
                    <ul style="margin: 10px 0 10px 20px;">
                        <li>Tu historial completo de compras</li>
                        <li>Todos tus métodos de pago guardados</li>
                        <li>Tus direcciones de envío</li>
                        <li>Tu perfil y preferencias</li>
                        <li>Datos personales asociados a tu cuenta</li>
                    </ul>
                </div>

                <form method="POST" onsubmit="return confirmDeleteAccount()">
                    <input type="hidden" name="eliminar_cuenta" value="1">

                    <div class="form-group">
                        <label for="confirmacion">
                            Para confirmar, escribe exactamente <strong style="color: #dc3545;">ELIMINAR</strong> en el siguiente campo:
                        </label>
                        <input type="text" id="confirmacion" name="confirmacion"
                            placeholder="ELIMINAR" required>
                    </div>

                    <div class="form-group">
                        <label for="password_confirmacion">Confirma tu contraseña *</label>
                        <input type="password" id="password_confirmacion" name="password_confirmacion"
                            placeholder="Tu contraseña actual" required>
                    </div>

                    <button type="submit" class="btn-danger">
                        <i class="fa-solid fa-trash"></i> Eliminar Mi Cuenta Permanentemente
                    </button>
                </form>
            </div>

            <!-- Sección 4: Contacto y Soporte -->
            <div class="config-section">
                <h3 class="section-title">
                    <i class="fa-solid fa-headset"></i>
                    Soporte y Ayuda
                </h3>

                <div class="info-card">
                    <i class="fa-solid fa-question-circle"></i>
                    <h4>¿Necesitas ayuda?</h4>
                    <p>Si tienes problemas con tu cuenta o necesitas asistencia, puedes:</p>

                    <div style="margin-top: 15px;">
                        <button class="btn-secondary" onclick="contactarSoporte()" style="margin-right: 10px;">
                            <i class="fa-solid fa-envelope"></i> Contactar Soporte
                        </button>

                        <a href="../faq.php" class="btn-secondary">
                            <i class="fa-solid fa-book"></i> Ver Preguntas Frecuentes
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Confirmación para eliminar cuenta
        function confirmDeleteAccount() {
            const confirmacion = document.getElementById('confirmacion').value;
            const password = document.getElementById('password_confirmacion').value;

            if (confirmacion !== 'ELIMINAR') {
                alert('Debes escribir exactamente "ELIMINAR" en el primer campo para confirmar');
                return false;
            }

            if (password.length < 6) {
                alert('Por favor, ingresa tu contraseña actual para confirmar');
                return false;
            }

            const mensaje =
                '¿seguro que quieres eliminar tu cuenta de forma permanente?\n\n'

            return confirm(mensaje);
        }

        // Función para solicitar datos (simulada)
        function solicitarDatos() {
            if (confirm('¿Deseas solicitar una copia de todos tus datos personales?\n\nTe enviaremos un enlace de descarga a tu email en las próximas 48 horas.')) {
                alert('✅ Solicitud recibida. Te enviaremos un email con el enlace de descarga pronto.');
            }
        }

        // Función para contactar soporte
        function contactarSoporte() {
            const email = '<?php echo htmlspecialchars($usuario["correo"]); ?>';
            const asunto = encodeURIComponent('Soporte - Configuración de Cuenta');
            const cuerpo = encodeURIComponent(`Hola, necesito ayuda con la configuración de mi cuenta.\n\nEmail: ${email}\nUsuario ID: <?php echo $usuario_id; ?>\n\nDescripción del problema:`);

            window.location.href = `mailto:soporte@tutienda.com?subject=${asunto}&body=${cuerpo}`;
        }

        // Validación en tiempo real del campo "ELIMINAR"
        document.getElementById('confirmacion').addEventListener('input', function() {
            const btnEliminar = document.querySelector('.btn-danger');
            if (this.value === 'ELIMINAR') {
                this.style.borderColor = '#28a745';
                this.style.backgroundColor = '#f0fff4';
            } else {
                this.style.borderColor = '#dc3545';
                this.style.backgroundColor = '#f8d7da';
            }
        });

        // Mostrar/ocultar contraseña (opcional)
        function togglePassword() {
            const input = document.getElementById('password_confirmacion');
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
        }
    </script>
    <script src="../scripts/user-apart-dashboard.js"></script>
</body>

</html>