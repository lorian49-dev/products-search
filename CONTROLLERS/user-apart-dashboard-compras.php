<?php
session_start();
include("../shortCuts/connect.php");

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión activa. Inicia sesión nuevamente.");
}

$usuario_id = intval($_SESSION['usuario_id']); // seguridad

// Obtener datos del usuario
$sql = "SELECT * FROM usuario WHERE id_usuario = $usuario_id";
$result = mysqli_query($connect, $sql);
if (!$result) {
    die("Error en la consulta: " . mysqli_error($connect));
}
$usuario = mysqli_fetch_assoc($result);
// Obtener el historial de compras del usuario
$sqlPedidos = "SELECT * FROM pedido WHERE id_cliente = $usuario_id ORDER BY fecha_pedido DESC";
$resultPedidos = mysqli_query($connect, $sqlPedidos);

if (!$resultPedidos) {
    die("Error al obtener pedidos: " . mysqli_error($connect));
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        /* contenedor */
        .dashboard-content {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
            font-family: "Inter", system-ui, Arial;
            color: #333;
        }

        /* lista y tarjetas */
        .lista-compras {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-top: 20px;
        }

        @media (min-width: 720px) {
            .lista-compras {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 1100px) {
            .lista-compras {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .compra-item {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 6px 18px rgba(18, 25, 30, 0.06);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: .15s ease;
        }

        .compra-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(18, 25, 30, 0.09);
        }

        /* encabezado */
        .compra-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .compra-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #111;
        }

        /* badge de estado */
        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 600;
            color: #fff;
        }

        /* colores según estado */
        .estado-entregado {
            background: #2ecc71;
        }

        .estado-en-camino {
            background: #f39c12;
        }

        .estado-procesando {
            background: #3498db;
        }

        .estado-cancelado {
            background: #e74c3c;
        }

        /* cuerpo */
        .compra-body {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .compra-info p,
        .compra-desc p {
            margin: 0;
            font-size: 14px;
            color: #444;
        }

        /* footer */
        .compra-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .llegada {
            font-size: 13px;
            color: #555;
        }

        /* botón */
        .btn-detalle {
            text-decoration: none;
            padding: 8px 12px;
            background: #111827;
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
    </style>
    <style>
        /* NUEVOS ESTILOS PARA EL DASHBOARD */
        .dashboard-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            gap: 30px;
        }

        /* Menú lateral */
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

        .sidebar-title i {
            color: #1976d2;
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

        .sidebar-menu a i {
            width: 20px;
            text-align: center;
            color: #666;
        }

        .sidebar-menu a:hover {
            background: #f5f5f5;
            color: #1976d2;
        }

        .sidebar-menu a:hover i {
            color: #1976d2;
        }

        .sidebar-menu a.active {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }

        .sidebar-menu a.active i {
            color: #1976d2;
        }

        /* Separador para cerrar sesión */
        .menu-divider {
            height: 1px;
            background: #eee;
            margin: 15px 0;
        }

        /* Logout link styling */
        .logout-link {
            color: #dc3545 !important;
        }

        .logout-link:hover {
            background: #f8d7da !important;
            color: #c82333 !important;
        }

        .logout-link i {
            color: #dc3545 !important;
        }

        /* Contenido principal */
        .dashboard-content {
            flex: 1;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
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

        /* Estilo para la página actual */
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

        .current-page-title i {
            color: #1976d2;
        }
    </style>
</head>

<body>
    <?php include '../TEMPLATES/header.php' ?>
    <main>
        <div class="dashboard-container">
            <!-- MENÚ LATERAL -->
            <div class="dashboard-sidebar">
                <div class="sidebar-title">
                    <i class="fa-solid fa-user-circle"></i>
                    Mi Cuenta
                </div>

                <ul class="sidebar-menu">
                    <!-- Volver al Home -->
                    <li>
                        <a href="../home.php">
                            <i class="fa-solid fa-home"></i>
                            Volver al Home
                        </a>
                    </li>
                    <!-- DASHBOARD PRINCIPAL -->
                    <li>
                        <a href="user-apart-dashboard.php">
                            <i class="fa-solid fa-tachometer-alt"></i>
                            Dashboard
                        </a>
                    </li>
                    <!-- Datos Personales (Activo actualmente) -->
                    <li>
                        <a href="user-apart-dashboard-datos-personales.php">
                            <i class="fa-solid fa-user"></i>
                            Datos Personales
                        </a>
                    </li>

                    <!-- Mis Compras -->
                    <li>
                        <a href="user-apart-dashboard-compras.php" class="active">
                            <i class="fa-solid fa-shopping-bag"></i>
                            Mis Compras
                        </a>
                    </li>

                    <!-- Métodos de Pago -->
                    <li>
                        <a href="user-apart-dashboard-metodos-pago.php">
                            <i class="fa-solid fa-credit-card"></i>
                            Métodos de Pago
                        </a>
                    </li>

                    <!-- Seguridad y Contraseña -->
                    <li>
                        <a href="user-apart-dashboard-seguridad.php">
                            <i class="fa-solid fa-shield-alt"></i>
                            Seguridad y Contraseña
                        </a>
                    </li>

                    <!-- Configuración -->
                    <li>
                        <a href="user-apart-dashboard-configuracion.php">
                            <i class="fa-solid fa-cog"></i>
                            Configuración
                        </a>
                    </li>

                    <!-- Separador -->
                    <li class="menu-divider"></li>

                    <!-- Cerrar Sesión -->
                    <li>
                        <a href="../registros-inicio-sesion/logout-user.php" class="logout-link">
                            <i class="fa-solid fa-sign-out-alt"></i>
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>

            <section class="dashboard-content">
                <h2>Mis Compras</h2>
                <p>Aquí podrás ver el historial de tus compras realizadas en HERMES.</p>

                <?php if (mysqli_num_rows($resultPedidos) === 0): ?>
                    <p>No tienes compras registradas.</p>

                <?php else: ?>

                    <div class="lista-compras">
                        <?php while ($pedido = mysqli_fetch_assoc($resultPedidos)): ?>

                            <?php
                            // clase visual según estado
                            $estadoClass = strtolower($pedido['estado']);
                            $estadoClass = str_replace(" ", "-", $estadoClass);
                            ?>

                            <article class="compra-item">

                                <div class="compra-header">
                                    <h3>Pedido #<?php echo $pedido['id_pedido']; ?></h3>
                                    <span class="badge estado-<?php echo $estadoClass; ?>">
                                        <?php echo $pedido['estado']; ?>
                                    </span>
                                </div>

                                <div class="compra-body">
                                    <div class="compra-info">
                                        <p><strong>Fecha:</strong> <?php echo $pedido['fecha_pedido']; ?></p>
                                        <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 0, ',', '.'); ?></p>
                                    </div>

                                    <div class="compra-desc">
                                        <p><?php echo $pedido['descripcion']; ?></p>
                                    </div>
                                </div>

                                <div class="compra-footer">
                                    <?php if (!empty($pedido['llegada_estimada'])): ?>
                                        <span class="llegada">
                                            Llegada estimada: <?php echo $pedido['llegada_estimada']; ?>
                                        </span>
                                    <?php endif; ?>

                                    <a href="#" class="btn-detalle">Ver detalle</a>
                                </div>

                            </article>

                        <?php endwhile; ?>
                    </div>

                <?php endif; ?>

            </section>

    </main>
    <?php include '../TEMPLATES/footer.php' ?>
</body>
<script src="../scripts/user-apart-dashboard.js"></script>

</html>