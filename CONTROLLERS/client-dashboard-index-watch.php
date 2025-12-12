<?php
session_start();
include('../shortCuts/connect.php');

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    header('Location: ../home.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: client-dashboard-index.php');
    exit();
}

$id_cliente = intval($_GET['id']);

// Obtener información del cliente con usuario
$query = "SELECT c.*, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_nacimiento, 
                 u.direccion_principal, u.codigo_recuperacion, u.codigo_expira
          FROM cliente c
          INNER JOIN usuario u ON c.id_cliente = u.id_usuario
          WHERE c.id_cliente = $id_cliente";
          
$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: client-dashboard-index.php');
    exit();
}

$cliente = mysqli_fetch_assoc($result);

// Obtener estadísticas del cliente
$estadisticas = [
    'carritos' => 0,
    'pedidos' => 0,
    'direcciones' => 0,
    'productos_carrito' => 0,
    'total_gastado' => 0
];

// Carritos activos
$query_carritos = "SELECT COUNT(*) as total FROM carrito WHERE id_cliente = $id_cliente";
$result_carritos = mysqli_query($connect, $query_carritos);
$estadisticas['carritos'] = mysqli_fetch_assoc($result_carritos)['total'];

// Pedidos realizados
$query_pedidos = "SELECT COUNT(*) as total FROM pedido WHERE id_cliente = $id_cliente";
$result_pedidos = mysqli_query($connect, $query_pedidos);
$estadisticas['pedidos'] = mysqli_fetch_assoc($result_pedidos)['total'];

// Direcciones registradas
$query_direcciones = "SELECT COUNT(*) as total FROM direccion_envio WHERE id_cliente = $id_cliente";
$result_direcciones = mysqli_query($connect, $query_direcciones);
$estadisticas['direcciones'] = mysqli_fetch_assoc($result_direcciones)['total'];

// Productos en carrito
$query_productos_carrito = "SELECT SUM(cp.cantidad) as total 
                           FROM carrito_producto cp
                           INNER JOIN carrito c ON cp.id_carrito = c.id_carrito
                           WHERE c.id_cliente = $id_cliente";
$result_productos = mysqli_query($connect, $query_productos_carrito);
$estadisticas['productos_carrito'] = mysqli_fetch_assoc($result_productos)['total'] ?? 0;

// Total gastado en pedidos
$query_total_gastado = "SELECT SUM(dp.precio_unitario * dp.cantidad) as total 
                       FROM detalle_pedido dp
                       INNER JOIN pedido p ON dp.id_pedido = p.id_pedido
                       WHERE p.id_cliente = $id_cliente AND p.estado = 'completado'";
$result_total = mysqli_query($connect, $query_total_gastado);
$estadisticas['total_gastado'] = mysqli_fetch_assoc($result_total)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cliente #<?php echo $id_cliente; ?> - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-create-delete-watch-user-crud.css">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div style="display: flex; align-items: center;">
                    <div class="avatar-large">
                        <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h1>
                        <div class="user-role">
                            Cliente #<?php echo $id_cliente; ?>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="client-dashboard-index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="client-dashboard-index-edit.php?id=<?php echo $id_cliente; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php if($_SESSION['admin_rol'] == 1): ?>
                    <a href="client-dashboard-index-delete.php?id=<?php echo $id_cliente; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <div class="header-bot">
             <p style="color: #666; font-size: 0.95em;">
                Información detallada del cliente y estadísticas de actividad
               </p>
            </div>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="stats-grid">
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['carritos']; ?></div>
                <div class="label">Carritos Activos</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['pedidos']; ?></div>
                <div class="label">Pedidos Realizados</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['direcciones']; ?></div>
                <div class="label">Direcciones</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['productos_carrito']; ?></div>
                <div class="label">Productos en Carrito</div>
            </div>
            <div class="stat-mini">
                <div class="number">$<?php echo number_format($estadisticas['total_gastado'], 2); ?></div>
                <div class="label">Total Gastado</div>
            </div>
        </div>

        <!-- Información del Cliente -->
        <div class="info-grid">
            <!-- Información Personal -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Información Personal</h3>
                <div class="info-row">
                    <span class="info-label">Nombre Completo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Correo Electrónico:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['correo'])): ?>
                            <a href="mailto:<?php echo $cliente['correo']; ?>"><?php echo htmlspecialchars($cliente['correo']); ?></a>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['telefono'])): ?>
                            <a href="tel:<?php echo $cliente['telefono']; ?>"><?php echo htmlspecialchars($cliente['telefono']); ?></a>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Nacimiento:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['fecha_nacimiento']) && $cliente['fecha_nacimiento'] != '0000-00-00'): ?>
                            <?php echo date('d/m/Y', strtotime($cliente['fecha_nacimiento'])); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificada</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Información de Dirección -->
            <div class="info-card">
                <h3><i class="fas fa-map-marker-alt"></i> Dirección Principal</h3>
                <div class="info-row">
                    <span class="info-label">Dirección:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['direccion_principal'])): ?>
                            <?php echo htmlspecialchars($cliente['direccion_principal']); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificada</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Wishlist:</span>
                    <span class="info-value">
                        <?php if($cliente['wishlist_privada'] == 1): ?>
                            <span class="badge badge-success">Privada</span>
                        <?php else: ?>
                            <span class="badge badge-info">Pública</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Código Recuperación:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['codigo_recuperacion'])): ?>
                            <code><?php echo htmlspecialchars($cliente['codigo_recuperacion']); ?></code>
                        <?php else: ?>
                            <span class="badge">No generado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Código Expira:</span>
                    <span class="info-value">
                        <?php if(!empty($cliente['codigo_expira']) && $cliente['codigo_expira'] != '0000-00-00 00:00:00'): ?>
                            <?php echo date('d/m/Y H:i', strtotime($cliente['codigo_expira'])); ?>
                        <?php else: ?>
                            <span class="badge">N/A</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Información Adicional -->
        <?php if(!empty($cliente['informacion_adicional'])): ?>
        <div class="section section-a">
            <h3><i class="fas fa-info-circle"></i> Información Adicional</h3>
            <div style="background: #f8f9fa; padding: 20px; border-radius: 10px; border-left: 4px solid #667eea;">
                <?php echo nl2br(htmlspecialchars($cliente['informacion_adicional'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <!-- Carritos Activos -->
        <?php if($estadisticas['carritos'] > 0): ?>
        <div class="section section-b">
            <h3><i class="fas fa-shopping-cart"></i> Carritos Activos (<?php echo $estadisticas['carritos']; ?>)</h3>
            <?php 
                $query_carritos_detalle = "SELECT c.*, COUNT(cp.id_producto) as productos, SUM(cp.cantidad) as total_items
                                          FROM carrito c
                                          LEFT JOIN carrito_producto cp ON c.id_carrito = cp.id_carrito
                                          WHERE c.id_cliente = $id_cliente
                                          GROUP BY c.id_carrito
                                          ORDER BY c.fecha_creacion DESC
                                          LIMIT 5";
                $result_carritos_detalle = mysqli_query($connect, $query_carritos_detalle);
            ?>
            <?php if(mysqli_num_rows($result_carritos_detalle) > 0): ?>
            <ul class="data-list">
                <?php while($carrito = mysqli_fetch_assoc($result_carritos_detalle)): ?>
                <li>
                    <div>
                        <strong>Carrito #<?php echo $carrito['id_carrito']; ?></strong>
                        <div style="font-size: 0.9em; color: #666;">
                            Creado: <?php echo date('d/m/Y H:i', strtotime($carrito['fecha_creacion'])); ?>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-info"><?php echo $carrito['productos']; ?> productos</span>
                        <span class="badge badge-success"><?php echo $carrito['total_items']; ?> items</span>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php if($estadisticas['carritos'] > 5): ?>
            <div style="text-align: center; margin-top: 15px;">
                <a href="#" class="btn btn-secondary">Ver todos los carritos</a>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No hay carritos activos</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Pedidos Recientes -->
        <?php if($estadisticas['pedidos'] > 0): ?>
        <div class="section">
            <h3><i class="fas fa-box"></i> Pedidos Recientes (<?php echo $estadisticas['pedidos']; ?>)</h3>
            <?php 
                $query_pedidos_detalle = "SELECT p.*, COUNT(dp.id_detalle) as items, SUM(dp.cantidad * dp.precio_unitario) as total
                                         FROM pedido p
                                         LEFT JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                                         WHERE p.id_cliente = $id_cliente
                                         GROUP BY p.id_pedido
                                         ORDER BY p.fecha_pedido DESC
                                         LIMIT 5";
                $result_pedidos_detalle = mysqli_query($connect, $query_pedidos_detalle);
            ?>
            <?php if(mysqli_num_rows($result_pedidos_detalle) > 0): ?>
            <ul class="data-list">
                <?php while($pedido = mysqli_fetch_assoc($result_pedidos_detalle)): ?>
                <li>
                    <div>
                        <strong>Pedido #<?php echo $pedido['id_pedido']; ?></strong>
                        <div style="font-size: 0.9em; color: #666;">
                            Fecha: <?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?> |
                            Estado: 
                            <?php 
                                $estado = $pedido['estado'] ?? 'pendiente';
                                $badge_class = 'badge-warning';
                                if($estado == 'completado') $badge_class = 'badge-success';
                                elseif($estado == 'cancelado') $badge_class = 'badge-danger';
                            ?>
                            <span class="badge <?php echo $badge_class; ?>"><?php echo ucfirst($estado); ?></span>
                        </div>
                    </div>
                    <div>
                        <span class="badge badge-info"><?php echo $pedido['items']; ?> items</span>
                        <span class="badge badge-success">$<?php echo number_format($pedido['total'], 2); ?></span>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php if($estadisticas['pedidos'] > 5): ?>
            <div style="text-align: center; margin-top: 15px;">
                <a href="#" class="btn btn-secondary">Ver todos los pedidos</a>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No hay pedidos registrados</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="section section-c">
            <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="client-dashboard-index-edit.php?id=<?php echo $id_cliente; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Información
                </a>
                <?php if($estadisticas['carritos'] > 0): ?>
                <a href="client-dashboard-index-actions.php?action=limpiar_carrito&id=<?php echo $id_cliente; ?>" 
                   class="btn btn-warning"
                   onclick="return confirm('¿Vaciar todos los carritos de este cliente?')">
                    <i class="fas fa-trash-alt"></i> Vaciar Carritos
                </a>
                <?php endif; ?>
                <?php if($_SESSION['admin_rol'] == 1): ?>
                <a href="client-dashboard-index-delete.php?id=<?php echo $id_cliente; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar Cliente
                </a>
                <?php endif; ?>
                <a href="../usuarios/editar.php?id=<?php echo $id_cliente; ?>" class="btn btn-secondary">  <!--RUTA NO EXISTENTE-->
                    <i class="fas fa-user-edit"></i> Editar Usuario
                </a>
            </div>
        </div>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;" class="section-d">
            <a href="client-dashboard-index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
            </a>
            <a href="user-dashboard-admin.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        // Confirmación para acciones peligrosas
        document.addEventListener('DOMContentLoaded', function() {
            const dangerousLinks = document.querySelectorAll('a[href*="eliminar"], a[href*="limpiar"]');
            dangerousLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    const action = this.textContent.includes('Eliminar') ? 'eliminar' : 'limpiar';
                    const message = action === 'eliminar' 
                        ? '¿Está seguro de ELIMINAR PERMANENTEMENTE este cliente y TODOS sus datos relacionados?'
                        : '¿Vaciar todos los carritos de compra de este cliente?';
                    
                    if (!confirm(message)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
    <script src="../scripts/admin.js"></script>
</body>
</html>
