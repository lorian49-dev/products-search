<?php
session_start();
require "../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Verificar si el usuario es vendedor
$sql = "SELECT v.*, u.nombre, u.apellido, u.correo, u.telefono 
        FROM vendedor v 
        JOIN usuario u ON v.id_vendedor = u.id_usuario 
        WHERE v.id_vendedor = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        window.location.href = 'seller-apart-main-view.php';
    </script>";
    exit;
}

$negocio = $result->fetch_assoc();

// Obtener estadísticas del vendedor
$sqlStats = "SELECT 
    (SELECT COUNT(*) FROM producto WHERE id_vendedor = ?) as total_productos,
    (SELECT COUNT(*) FROM catalogo WHERE id_vendedor = ?) as total_catalogos,
    (SELECT COUNT(DISTINCT p.id_pedido) 
     FROM pedido p 
     JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido 
     JOIN producto pr ON dp.id_producto = pr.id_producto 
     WHERE pr.id_vendedor = ?) as total_ventas";

$stmtStats = $connect->prepare($sqlStats);
$stmtStats->bind_param("iii", $idUsuario, $idUsuario, $idUsuario);
$stmtStats->execute();
$stats = $stmtStats->get_result()->fetch_assoc();

// Obtener productos del vendedor (últimos 5)
$sqlProductos = "SELECT * FROM producto WHERE id_vendedor = ? ORDER BY fecha_creacion DESC LIMIT 5";
$stmtProductos = $connect->prepare($sqlProductos);
$stmtProductos->bind_param("i", $idUsuario);
$stmtProductos->execute();
$productos = $stmtProductos->get_result();

// Obtener pedidos del vendedor
$sqlPedidos = "SELECT DISTINCT p.*, u.nombre, u.apellido 
               FROM pedido p 
               JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido 
               JOIN producto pr ON dp.id_producto = pr.id_producto 
               JOIN usuario u ON p.id_usuario = u.id_usuario 
               WHERE pr.id_vendedor = ? 
               ORDER BY p.fecha_pedido DESC 
               LIMIT 5";
$stmtPedidos = $connect->prepare($sqlPedidos);
$stmtPedidos->bind_param("i", $idUsuario);
$stmtPedidos->execute();
$pedidos = $stmtPedidos->get_result();

// Obtener catalogos del vendedor
$sqlCatalogos = "SELECT * FROM catalogo WHERE id_vendedor = ? ORDER BY fecha_creacion DESC";
$stmtCatalogos = $connect->prepare($sqlCatalogos);
$stmtCatalogos->bind_param("i", $idUsuario);
$stmtCatalogos->execute();
$catalogos = $stmtCatalogos->get_result();

// Calcular ventas totales
$sqlVentas = "SELECT SUM(dp.precio_total) as total_ingresos 
              FROM detalle_pedido dp 
              JOIN producto p ON dp.id_producto = p.id_producto 
              WHERE p.id_vendedor = ?";
$stmtVentas = $connect->prepare($sqlVentas);
$stmtVentas->bind_param("i", $idUsuario);
$stmtVentas->execute();
$ventas = $stmtVentas->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard del Vendedor - <?php echo htmlspecialchars($negocio['nombre_empresa']); ?></title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: #f3f4f6;
            color: #333;
        }

        /* Header */
        .header {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .header-left h1 {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .header-left p {
            color: #d1d5db;
            font-size: 0.9rem;
            margin-top: 5px;
        }

        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }

        .user-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .user-avatar {
            width: 45px;
            height: 45px;
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            color: white;
            font-size: 1.1rem;
        }

        /* Dashboard Container */
        .dashboard-container {
            display: flex;
            min-height: calc(100vh - 80px);
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            padding: 25px 0;
            box-shadow: 3px 0 10px rgba(0, 0, 0, 0.05);
            position: sticky;
            top: 0;
            height: 100vh;
        }

        .logo {
            text-align: center;
            padding: 0 20px 25px;
            border-bottom: 1px solid #e5e7eb;
            margin-bottom: 20px;
        }

        .logo h2 {
            color: #1f2937;
            font-size: 1.3rem;
        }

        .logo span {
            color: #3b82f6;
        }

        .nav-menu {
            list-style: none;
            padding: 0 15px;
        }

        .nav-menu li {
            margin-bottom: 8px;
        }

        .nav-menu a {
            color: #4b5563;
            text-decoration: none;
            padding: 12px 15px;
            border-radius: 8px;
            display: flex;
            align-items: center;
            gap: 12px;
            transition: all 0.3s;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: #eff6ff;
            color: #1d4ed8;
        }

        .nav-menu i {
            width: 20px;
            text-align: center;
        }

        /* Main Content */
        .main-content {
            flex: 1;
            padding: 30px;
        }

        /* Welcome Section */
        .welcome-card {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.2);
        }

        .welcome-card h2 {
            font-size: 1.8rem;
            margin-bottom: 10px;
        }

        .welcome-card p {
            opacity: 0.9;
            margin-bottom: 20px;
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .icon-1 {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
        }

        .icon-2 {
            background: linear-gradient(135deg, #10b981, #059669);
        }

        .icon-3 {
            background: linear-gradient(135deg, #f59e0b, #d97706);
        }

        .icon-4 {
            background: linear-gradient(135deg, #8b5cf6, #7c3aed);
        }

        .stat-info h3 {
            font-size: 1.8rem;
            margin-bottom: 5px;
            color: #1f2937;
        }

        .stat-info p {
            color: #6b7280;
            font-size: 0.9rem;
        }

        /* Dashboard Sections */
        .dashboard-section {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            margin-bottom: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-header h3 {
            color: #1f2937;
            font-size: 1.2rem;
        }

        /* Tables */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th {
            background: #f9fafb;
            color: #4b5563;
            padding: 15px;
            text-align: left;
            font-weight: 600;
            border-bottom: 2px solid #e5e7eb;
        }

        td {
            padding: 15px;
            border-bottom: 1px solid #f3f4f6;
        }

        tr:hover {
            background: #f9fafb;
        }

        .status {
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
        }

        .status-pendiente {
            background: #fef3c7;
            color: #92400e;
        }

        .status-enviado {
            background: #dbeafe;
            color: #1e40af;
        }

        .status-entregado {
            background: #dcfce7;
            color: #166534;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            background: #3b82f6;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 0.9rem;
            transition: all 0.3s;
            border: none;
            cursor: pointer;
        }

        .btn:hover {
            background: #2563eb;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            color: #3b82f6;
            border: 2px solid #3b82f6;
        }

        .btn-outline:hover {
            background: #3b82f6;
            color: white;
        }

        .btn-danger {
            background: #ef4444;
        }

        .btn-danger:hover {
            background: #dc2626;
        }

        .btn-sm {
            padding: 5px 10px;
            font-size: 0.8rem;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #9ca3af;
        }

        .empty-state i {
            font-size: 3rem;
            margin-bottom: 15px;
            opacity: 0.5;
        }

        /* Business Info */
        .business-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .info-item {
            padding: 15px;
            background: #f9fafb;
            border-radius: 8px;
            border-left: 4px solid #3b82f6;
        }

        .info-item strong {
            display: block;
            color: #4b5563;
            font-size: 0.9rem;
            margin-bottom: 5px;
        }

        .info-item span {
            color: #1f2937;
            font-size: 1rem;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .sidebar {
                width: 200px;
            }
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .sidebar {
                width: 100%;
                height: auto;
                position: relative;
                margin-bottom: 20px;
            }

            .stats-grid {
                grid-template-columns: 1fr;
            }

            .header {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>Panel del Vendedor</h1>
            <p><?php echo htmlspecialchars($negocio['nombre_empresa']); ?> | <?php echo date('d/m/Y H:i'); ?></p>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?php echo strtoupper(substr($negocio['nombre'], 0, 1) . substr($negocio['apellido'], 0, 1)); ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($negocio['nombre'] . ' ' . $negocio['apellido']); ?></strong>
                    <p style="font-size: 0.8rem; color: #d1d5db;"><?php echo htmlspecialchars($negocio['correo']); ?>
                    </p>
                </div>
            </div>
            <a href="../registros-inicio-sesion/logout.php" class="btn btn-danger">
                <i class="fas fa-sign-out-alt"></i> Salir
            </a>
        </div>
    </div>

    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Hermes<span>Seller</span></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="seller-apart-product-create.php"><i class="fas fa-plus-circle"></i> Crear Producto</a></li>
                <li><a href="seller-apart-products.php"><i class="fas fa-box"></i> Mis Productos</a></li>
                <li><a href="categorias-vendedor.php"><i class="fas fa-tags"></i> Mis Categorías</a></li>
                <li><a href="catalogos-vendedor.php"><i class="fas fa-book"></i> Mis Catálogos</a></li>
                <li><a href="pedidos-vendedor.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="editar-negocio.php"><i class="fas fa-store"></i> Editar Negocio</a></li>
                <li><a href="perfil-vendedor.php"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
                <li><a href="../home.php"><i class="fas fa-home"></i> Volver al Inicio</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Welcome Card -->
            <div class="welcome-card">
                <h2>¡Bienvenido, <?php echo htmlspecialchars($negocio['nombre']); ?>!</h2>
                <p>Gestiona tu negocio desde este panel. Crea productos, revisa pedidos y administra tus ventas.</p>
                <a href="seller-apart-product-create.php" class="btn">
                    <i class="fas fa-plus"></i> Crear Nuevo Producto
                </a>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon icon-1">
                        <i class="fas fa-box"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_productos'] ?? 0; ?></h3>
                        <p>Productos Activos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-2">
                        <i class="fas fa-shopping-cart"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_ventas'] ?? 0; ?></h3>
                        <p>Pedidos Totales</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-3">
                        <i class="fas fa-book"></i>
                    </div>
                    <div class="stat-info">
                        <h3><?php echo $stats['total_catalogos'] ?? 0; ?></h3>
                        <p>Catálogos</p>
                    </div>
                </div>

                <div class="stat-card">
                    <div class="stat-icon icon-4">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-info">
                        <h3>$<?php echo number_format($ventas['total_ingresos'] ?? 0, 0, ',', '.'); ?></h3>
                        <p>Ingresos Totales</p>
                    </div>
                </div>
            </div>

            <!-- Últimos Productos -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-box"></i> Últimos Productos</h3>
                    <a href="productos-vendedor.php" class="btn btn-outline">Ver Todos</a>
                </div>

                <?php if ($productos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nombre</th>
                                    <th>Precio</th>
                                    <th>Stock</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($producto = $productos->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $producto['id_producto']; ?></td>
                                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                                        <td>$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></td>
                                        <td><?php echo $producto['stock']; ?></td>
                                        <td>
                                            <a href="editar-producto.php?id=<?php echo $producto['id_producto']; ?>"
                                                class="btn btn-sm">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="eliminar-producto.php?id=<?php echo $producto['id_producto']; ?>"
                                                class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Eliminar este producto?')">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-box-open"></i>
                        <p>No tienes productos registrados</p>
                        <a href="seller-apart-product-create.php" class="btn">Crear Primer Producto</a>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Últimos Pedidos -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-shopping-cart"></i> Últimos Pedidos</h3>
                    <a href="pedidos-vendedor.php" class="btn btn-outline">Ver Todos</a>
                </div>

                <?php if ($pedidos->num_rows > 0): ?>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>ID Pedido</th>
                                    <th>Cliente</th>
                                    <th>Fecha</th>
                                    <th>Total</th>
                                    <th>Estado</th>
                                    <th>Acciones</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                                    <tr>
                                        <td>#<?php echo $pedido['id_pedido']; ?></td>
                                        <td><?php echo htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']); ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($pedido['fecha_pedido'])); ?></td>
                                        <td>$<?php echo number_format($pedido['total'], 0, ',', '.'); ?></td>
                                        <td>
                                            <span class="status status-<?php echo strtolower($pedido['estado']); ?>">
                                                <?php echo $pedido['estado']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <a href="detalle-pedido.php?id=<?php echo $pedido['id_pedido']; ?>"
                                                class="btn btn-sm">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-shopping-cart"></i>
                        <p>No tienes pedidos aún</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información del Negocio -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-store"></i> Información del Negocio</h3>
                    <a href="editar-negocio.php" class="btn">Editar Información</a>
                </div>

                <div class="business-info-grid">
                    <div class="info-item">
                        <strong>Nombre de Empresa</strong>
                        <span><?php echo htmlspecialchars($negocio['nombre_empresa']); ?></span>
                    </div>

                    <div class="info-item">
                        <strong>NIT</strong>
                        <span><?php echo htmlspecialchars($negocio['nit']); ?></span>
                    </div>

                    <div class="info-item">
                        <strong>Teléfono</strong>
                        <span><?php echo htmlspecialchars($negocio['telefono_contacto']); ?></span>
                    </div>

                    <div class="info-item">
                        <strong>Correo Contacto</strong>
                        <span><?php echo htmlspecialchars($negocio['correo_contacto']); ?></span>
                    </div>

                    <div class="info-item">
                        <strong>Ubicación</strong>
                        <span><?php echo htmlspecialchars($negocio['ubicacion']); ?></span>
                    </div>

                    <div class="info-item">
                        <strong>Fecha Registro</strong>
                        <span><?php echo date('d/m/Y', strtotime($negocio['fecha_registro'])); ?></span>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="dashboard-section">
                <div class="section-header">
                    <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
                </div>

                <div style="display: flex; gap: 15px; flex-wrap: wrap;">
                    <a href="seller-apart-product-create.php" class="btn">
                        <i class="fas fa-plus-circle"></i> Nuevo Producto
                    </a>
                    <a href="crear-catalogo.php" class="btn">
                        <i class="fas fa-book"></i> Nuevo Catálogo
                    </a>
                    <a href="productos-vendedor.php" class="btn btn-outline">
                        <i class="fas fa-list"></i> Ver Productos
                    </a>
                    <a href="pedidos-vendedor.php" class="btn btn-outline">
                        <i class="fas fa-shopping-cart"></i> Ver Pedidos
                    </a>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Actualizar hora en tiempo real
        function updateTime() {
            const now = new Date();
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit'
            };
            const timeString = now.toLocaleDateString('es-ES', options);

            // Actualizar en el header si hay un elemento con clase time-display
            const timeElements = document.querySelectorAll('.time-display');
            timeElements.forEach(el => {
                el.textContent = timeString;
            });
        }

        // Actualizar cada minuto
        setInterval(updateTime, 60000);

        // Confirmación para acciones de eliminación
        document.addEventListener('DOMContentLoaded', function () {
            const deleteLinks = document.querySelectorAll('a.btn-danger');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function (e) {
                    if (!confirm('¿Estás seguro de que quieres realizar esta acción?')) {
                        e.preventDefault();
                    }
                });
            });

            // Añadir animación a las tarjetas de estadísticas
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach(card => {
                card.addEventListener('mouseenter', function () {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function () {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>

</html>