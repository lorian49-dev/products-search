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
    header('Location: seller-dashboard-admin-index.php');
    exit();
}

$id_vendedor = intval($_GET['id']);

// Obtener información del vendedor con usuario
$query = "SELECT v.*, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_nacimiento, u.direccion_principal
          FROM vendedor v
          INNER JOIN usuario u ON v.id_vendedor = u.id_usuario
          WHERE v.id_vendedor = $id_vendedor";
          
$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: seller-dashboard-admin-index.php');
    exit();
}

$vendedor = mysqli_fetch_assoc($result);

// Obtener estadísticas del vendedor
$estadisticas = [
    'productos' => 0,
    'productos_activos' => 0,
    'productos_sin_stock' => 0,
    'catalogos' => 0,
    'total_valor_inventario' => 0
];

// Productos totales
$query_productos = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $id_vendedor";
$result_productos = mysqli_query($connect, $query_productos);
$estadisticas['productos'] = mysqli_fetch_assoc($result_productos)['total'];

// Productos con stock
$query_stock = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $id_vendedor AND stock > 0";
$result_stock = mysqli_query($connect, $query_stock);
$estadisticas['productos_activos'] = mysqli_fetch_assoc($result_stock)['total'];

// Productos sin stock
$query_sin_stock = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $id_vendedor AND stock = 0";
$result_sin_stock = mysqli_query($connect, $query_sin_stock);
$estadisticas['productos_sin_stock'] = mysqli_fetch_assoc($result_sin_stock)['total'];

// Catálogos
$query_catalogos = "SELECT COUNT(*) as total FROM catalogo WHERE id_vendedor = $id_vendedor";
$result_catalogos = mysqli_query($connect, $query_catalogos);
$estadisticas['catalogos'] = mysqli_fetch_assoc($result_catalogos)['total'];

// Valor total del inventario
$query_inventario = "SELECT SUM(precio * stock) as total FROM producto WHERE id_vendedor = $id_vendedor";
$result_inventario = mysqli_query($connect, $query_inventario);
$estadisticas['total_valor_inventario'] = mysqli_fetch_assoc($result_inventario)['total'] ?? 0;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor #<?php echo $id_vendedor; ?> - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .dashboard-container { max-width: 1200px; margin: 0 auto; }
        
        /* Header */
        .header { background: rgba(255, 255, 255, 0.95); padding: 25px 30px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .header h1 { color: #333; font-size: 1.8em; margin-bottom: 5px; }
        .user-role { background: #667eea; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        
        /* Botones */
        .btn { padding: 10px 20px; border: none; border-radius: 25px; font-size: 0.95em; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-primary { background: #667eea; color: white; } .btn-primary:hover { background: #5a6fd8; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; } .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; } .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; } .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #212529; } .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; } .btn-info:hover { background: #138496; transform: translateY(-2px); }
        
        /* Tarjetas de información */
        .info-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px; margin-bottom: 30px; }
        .info-card { background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .info-card h3 { color: #667eea; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee; }
        .info-row { display: flex; justify-content: space-between; margin-bottom: 12px; padding-bottom: 12px; border-bottom: 1px solid #f0f0f0; }
        .info-label { font-weight: 600; color: #555; }
        .info-value { color: #333; text-align: right; }
        
        /* Estadísticas */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 30px; }
        .stat-mini { background: rgba(255, 255, 255, 0.95); padding: 15px; border-radius: 10px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .stat-mini .number { font-size: 1.8em; font-weight: bold; color: #667eea; margin: 5px 0; }
        .stat-mini .label { color: #666; font-size: 0.85em; }
        
        /* Secciones */
        .section { background: rgba(255, 255, 255, 0.95); padding: 25px; border-radius: 15px; margin-bottom: 25px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); }
        .section h3 { color: #333; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        
        /* Listas */
        .data-list { list-style: none; }
        .data-list li { padding: 12px 15px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .data-list li:hover { background: #f8f9fa; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* Avatar */
        .avatar-large { width: 80px; height: 80px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 2em; font-weight: bold; margin-right: 20px; }
        
        @media (max-width: 768px) {
            .header-top { flex-direction: column; gap: 15px; text-align: center; }
            .info-grid { grid-template-columns: 1fr; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div style="display: flex; align-items: center;">
                    <div class="avatar-large">
                        <?php echo strtoupper(substr($vendedor['nombre'], 0, 1)); ?>
                    </div>
                    <div>
                        <h1><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']); ?></h1>
                        <div class="user-role">
                            Vendedor #<?php echo $id_vendedor; ?>
                        </div>
                    </div>
                </div>
                <div style="display: flex; gap: 10px;">
                    <a href="seller-dashboard-admin-index.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver
                    </a>
                    <a href="seller-dashboard-admin-edit.php?id=<?php echo $id_vendedor; ?>" class="btn btn-primary">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <?php if($_SESSION['admin_rol'] == 1): ?>
                    <a href="seller-dashboard-admin-delete.php?id=<?php echo $id_vendedor; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Información detallada del vendedor y estadísticas de productos
            </p>
        </div>

        <!-- Estadísticas rápidas -->
        <div class="stats-grid">
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['productos']; ?></div>
                <div class="label">Productos Totales</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['productos_activos']; ?></div>
                <div class="label">Con Stock</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['productos_sin_stock']; ?></div>
                <div class="label">Sin Stock</div>
            </div>
            <div class="stat-mini">
                <div class="number"><?php echo $estadisticas['catalogos']; ?></div>
                <div class="label">Catálogos</div>
            </div>
            <div class="stat-mini">
                <div class="number">$<?php echo number_format($estadisticas['total_valor_inventario'], 2); ?></div>
                <div class="label">Valor Inventario</div>
            </div>
        </div>

        <!-- Información del Vendedor -->
        <div class="info-grid">
            <!-- Información Personal -->
            <div class="info-card">
                <h3><i class="fas fa-user"></i> Información Personal</h3>
                <div class="info-row">
                    <span class="info-label">Nombre Completo:</span>
                    <span class="info-value"><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']); ?></span>
                </div>
                <div class="info-row">
                    <span class="info-label">Correo Personal:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['correo'])): ?>
                            <a href="mailto:<?php echo $vendedor['correo']; ?>"><?php echo htmlspecialchars($vendedor['correo']); ?></a>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['telefono'])): ?>
                            <a href="tel:<?php echo $vendedor['telefono']; ?>"><?php echo htmlspecialchars($vendedor['telefono']); ?></a>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Nacimiento:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['fecha_nacimiento']) && $vendedor['fecha_nacimiento'] != '0000-00-00'): ?>
                            <?php echo date('d/m/Y', strtotime($vendedor['fecha_nacimiento'])); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificada</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Dirección Principal:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['direccion_principal'])): ?>
                            <?php echo htmlspecialchars($vendedor['direccion_principal']); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificada</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>

            <!-- Información de la Empresa -->
            <div class="info-card">
                <h3><i class="fas fa-building"></i> Información de la Empresa</h3>
                <div class="info-row">
                    <span class="info-label">Nombre de la Empresa:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['nombre_empresa'])): ?>
                            <strong><?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></strong>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">NIT:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['nit'])): ?>
                            <code><?php echo htmlspecialchars($vendedor['nit']); ?></code>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Correo de Contacto:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['correo_contacto'])): ?>
                            <a href="mailto:<?php echo $vendedor['correo_contacto']; ?>"><?php echo htmlspecialchars($vendedor['correo_contacto']); ?></a>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Teléfono de Contacto:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['telefono_contacto'])): ?>
                            <?php echo htmlspecialchars($vendedor['telefono_contacto']); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificado</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Ubicación:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['ubicacion'])): ?>
                            <?php echo htmlspecialchars($vendedor['ubicacion']); ?>
                        <?php else: ?>
                            <span class="badge badge-warning">No especificada</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Aceptó Términos:</span>
                    <span class="info-value">
                        <?php if($vendedor['acepto_terminos'] == 1): ?>
                            <span class="badge badge-success"><i class="fas fa-check"></i> Sí</span>
                        <?php else: ?>
                            <span class="badge badge-danger"><i class="fas fa-times"></i> No</span>
                        <?php endif; ?>
                    </span>
                </div>
                <div class="info-row">
                    <span class="info-label">Fecha de Registro:</span>
                    <span class="info-value">
                        <?php if(!empty($vendedor['fecha_registro']) && $vendedor['fecha_registro'] != '0000-00-00 00:00:00'): ?>
                            <?php echo date('d/m/Y H:i', strtotime($vendedor['fecha_registro'])); ?>
                        <?php else: ?>
                            <span class="badge">No registrada</span>
                        <?php endif; ?>
                    </span>
                </div>
            </div>
        </div>

        <!-- Productos del Vendedor -->
        <?php if($estadisticas['productos'] > 0): ?>
        <div class="section">
            <h3><i class="fas fa-box"></i> Productos del Vendedor (<?php echo $estadisticas['productos']; ?>)</h3>
            <?php 
                $query_productos_detalle = "SELECT * FROM producto 
                                           WHERE id_vendedor = $id_vendedor 
                                           ORDER BY fecha_creacion DESC 
                                           LIMIT 10";
                $result_productos_detalle = mysqli_query($connect, $query_productos_detalle);
            ?>
            <?php if(mysqli_num_rows($result_productos_detalle) > 0): ?>
            <ul class="data-list">
                <?php while($producto = mysqli_fetch_assoc($result_productos_detalle)): ?>
                <li>
                    <div>
                        <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                        <div style="font-size: 0.9em; color: #666;">
                            <?php echo htmlspecialchars(substr($producto['descripcion'] ?? '', 0, 50)); ?>
                            <?php if(strlen($producto['descripcion'] ?? '') > 50): ?>...<?php endif; ?>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <div style="font-weight: bold; color: #28a745;">$<?php echo number_format($producto['precio'], 2); ?></div>
                        <div>
                            <?php if($producto['stock'] > 0): ?>
                                <span class="badge badge-success">Stock: <?php echo $producto['stock']; ?></span>
                            <?php else: ?>
                                <span class="badge badge-danger">Sin Stock</span>
                            <?php endif; ?>
                        </div>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php if($estadisticas['productos'] > 10): ?>
            <div style="text-align: center; margin-top: 15px;">
                <a href="#" class="btn btn-secondary">Ver todos los productos</a>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No hay productos registrados</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Catálogos del Vendedor -->
        <?php if($estadisticas['catalogos'] > 0): ?>
        <div class="section">
            <h3><i class="fas fa-book"></i> Catálogos del Vendedor (<?php echo $estadisticas['catalogos']; ?>)</h3>
            <?php 
                $query_catalogos_detalle = "SELECT * FROM catalogo 
                                           WHERE id_vendedor = $id_vendedor 
                                           ORDER BY fecha_creacion DESC 
                                           LIMIT 5";
                $result_catalogos_detalle = mysqli_query($connect, $query_catalogos_detalle);
            ?>
            <?php if(mysqli_num_rows($result_catalogos_detalle) > 0): ?>
            <ul class="data-list">
                <?php while($catalogo = mysqli_fetch_assoc($result_catalogos_detalle)): ?>
                <li>
                    <div>
                        <strong><?php echo htmlspecialchars($catalogo['nombre_catalogo']); ?></strong>
                        <div style="font-size: 0.9em; color: #666;">
                            Creado: <?php echo date('d/m/Y', strtotime($catalogo['fecha_creacion'])); ?>
                            <?php if(!empty($catalogo['fecha_actualizacion']) && $catalogo['fecha_actualizacion'] != '0000-00-00 00:00:00'): ?>
                                | Actualizado: <?php echo date('d/m/Y', strtotime($catalogo['fecha_actualizacion'])); ?>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div>
                        <a href="#" class="btn-action btn-view" title="Ver Catálogo">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </li>
                <?php endwhile; ?>
            </ul>
            <?php if($estadisticas['catalogos'] > 5): ?>
            <div style="text-align: center; margin-top: 15px;">
                <a href="#" class="btn btn-secondary">Ver todos los catálogos</a>
            </div>
            <?php endif; ?>
            <?php else: ?>
            <p style="color: #666; text-align: center; padding: 20px;">No hay catálogos registrados</p>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <!-- Acciones Rápidas -->
        <div class="section">
            <h3><i class="fas fa-bolt"></i> Acciones Rápidas</h3>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                <a href="editar.php?id=<?php echo $id_vendedor; ?>" class="btn btn-primary">
                    <i class="fas fa-edit"></i> Editar Información
                </a>
                <a href="../productos/index.php?vendedor=<?php echo $id_vendedor; ?>" class="btn btn-info"> <!--RUTAS INEXISTENTES-->
                    <i class="fas fa-boxes"></i> Ver Productos
                </a>
                <a href="../catalogo/index.php?vendedor=<?php echo $id_vendedor; ?>" class="btn btn-warning"> <!--RUTAS INEXISTENTES-->
                    <i class="fas fa-book"></i> Ver Catálogos
                </a>
                <?php if($_SESSION['admin_rol'] == 1): ?>
                <a href="sellet-dashboard-admin-delete.php?id=<?php echo $id_vendedor; ?>" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Eliminar Vendedor
                </a>
                <?php endif; ?>
                <a href="../usuarios/editar.php?id=<?php echo $id_vendedor; ?>" class="btn btn-secondary"> <!--RUTAS INEXISTENTES-->
                    <i class="fas fa-user-edit"></i> Editar Usuario
                </a>
            </div>
        </div>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #eee;">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista
            </a>
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        // Confirmación para acciones peligrosas
        document.addEventListener('DOMContentLoaded', function() {
            const dangerousLinks = document.querySelectorAll('a[href*="eliminar"]');
            dangerousLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('⚠️ ¿Está seguro de ELIMINAR PERMANENTEMENTE este vendedor y TODOS sus productos y catálogos?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
