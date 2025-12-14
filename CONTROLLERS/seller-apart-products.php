<?php
session_start();
require "../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Verificar si es vendedor
$sqlVendedor = "SELECT * FROM vendedor WHERE id_vendedor = ?";
$stmtVendedor = $connect->prepare($sqlVendedor);
$stmtVendedor->bind_param("i", $idUsuario);
$stmtVendedor->execute();
$vendedor = $stmtVendedor->get_result()->fetch_assoc();

if (!$vendedor) {
    header("Location: seller-apart-create-bussiness.php");
    exit;
}

// Obtener productos del vendedor con paginación
$pagina = $_GET['pagina'] ?? 1;
$limite = 10;
$offset = ($pagina - 1) * $limite;

// Contar total de productos
$sqlCount = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = ?";
$stmtCount = $connect->prepare($sqlCount);
$stmtCount->bind_param("i", $idUsuario);
$stmtCount->execute();
$totalProductos = $stmtCount->get_result()->fetch_assoc()['total'];
$totalPaginas = ceil($totalProductos / $limite);

// Obtener productos con categorías
$sqlProductos = "SELECT p.*, c.nombre_categoria 
                 FROM producto p 
                 LEFT JOIN producto_categoria pc ON p.id_producto = pc.id_producto 
                 LEFT JOIN categoria c ON pc.id_categoria = c.id_categoria 
                 WHERE p.id_vendedor = ? 
                 ORDER BY p.fecha_creacion DESC 
                 LIMIT ? OFFSET ?";
$stmtProductos = $connect->prepare($sqlProductos);
$stmtProductos->bind_param("iii", $idUsuario, $limite, $offset);
$stmtProductos->execute();
$productos = $stmtProductos->get_result();

// Eliminar producto
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    
    // Verificar que el producto pertenece al vendedor
    $sqlVerificar = "SELECT imagen_url FROM producto WHERE id_producto = ? AND id_vendedor = ?";
    $stmtVerificar = $connect->prepare($sqlVerificar);
    $stmtVerificar->bind_param("ii", $idEliminar, $idUsuario);
    $stmtVerificar->execute();
    $productoEliminar = $stmtVerificar->get_result()->fetch_assoc();
    
    if ($productoEliminar) {
        // Eliminar imagen si existe
        if ($productoEliminar['imagen_url'] && file_exists($productoEliminar['imagen_url'])) {
            unlink($productoEliminar['imagen_url']);
        }
        
        $sqlEliminar = "DELETE FROM producto WHERE id_producto = ? AND id_vendedor = ?";
        $stmtEliminar = $connect->prepare($sqlEliminar);
        $stmtEliminar->bind_param("ii", $idEliminar, $idUsuario);
        
        if ($stmtEliminar->execute()) {
            echo "<script>
                alert('Producto eliminado exitosamente');
                window.location.href = 'productos-vendedor.php';
            </script>";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Productos - <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></title>
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

        .header {
            background: linear-gradient(135deg, #1f2937, #374151);
            color: white;
            padding: 20px 40px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
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

        .btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 10px 20px;
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

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #059669;
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

        /* Main Container */
        .main-container {
            max-width: 1200px;
            margin: 30px auto;
            padding: 0 20px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding: 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .section-header h2 {
            color: #1f2937;
            font-size: 1.5rem;
        }

        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .product-image {
            height: 180px;
            background: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .product-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .product-image .no-image {
            color: #9ca3af;
            font-size: 3rem;
        }

        .product-info {
            padding: 20px;
        }

        .product-name {
            font-size: 1.1rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .product-price {
            font-size: 1.3rem;
            font-weight: bold;
            color: #059669;
            margin-bottom: 8px;
        }

        .product-meta {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .product-stock {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .stock-high { background: #d1fae5; color: #065f46; }
        .stock-medium { background: #fef3c7; color: #92400e; }
        .stock-low { background: #fee2e2; color: #991b1b; }
        .stock-out { background: #f3f4f6; color: #6b7280; }

        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        /* Pagination */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 10px;
            margin-top: 30px;
        }

        .pagination a, .pagination span {
            padding: 8px 16px;
            border-radius: 6px;
            text-decoration: none;
            background: white;
            color: #4b5563;
            border: 1px solid #e5e7eb;
        }

        .pagination a:hover {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        .pagination .active {
            background: #3b82f6;
            color: white;
            border-color: #3b82f6;
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: #d1d5db;
            margin-bottom: 20px;
        }

        .empty-state h3 {
            color: #4b5563;
            margin-bottom: 10px;
        }

        .empty-state p {
            color: #6b7280;
            margin-bottom: 20px;
        }

        /* Stats */
        .stats-bar {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-item {
            background: white;
            padding: 15px;
            border-radius: 8px;
            text-align: center;
            box-shadow: 0 3px 10px rgba(0,0,0,0.05);
        }

        .stat-value {
            font-size: 1.5rem;
            font-weight: bold;
            color: #1f2937;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <div class="header-left">
            <h1>Mis Productos</h1>
            <p><?php echo htmlspecialchars($vendedor['nombre_empresa']); ?> | Total: <?php echo $totalProductos; ?> productos</p>
        </div>
        <div class="header-right">
            <div class="user-info">
                <div class="user-avatar">
                    <?php 
                    $nombre = $vendedor['nombre_empresa'];
                    echo strtoupper(substr($nombre, 0, 1)); 
                    ?>
                </div>
                <div>
                    <strong><?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></strong>
                    <p style="font-size: 0.8rem; color: #d1d5db;">Vendedor</p>
                </div>
            </div>
            <a href="../home.php" class="btn">
                <i class="fas fa-arrow-left"></i> Volver a la página principal
            </a>
        </div>
    </div>

    <!-- Main Container -->
    <div class="main-container">
        <!-- Stats Bar -->
        <div class="stats-bar">
            <div class="stat-item">
                <div class="stat-value"><?php echo $totalProductos; ?></div>
                <div class="stat-label">Productos Totales</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php 
                    $sqlActivos = "SELECT COUNT(*) as activos FROM producto WHERE id_vendedor = ? AND stock > 0";
                    $stmtActivos = $connect->prepare($sqlActivos);
                    $stmtActivos->bind_param("i", $idUsuario);
                    $stmtActivos->execute();
                    $activos = $stmtActivos->get_result()->fetch_assoc()['activos'];
                    echo $activos;
                ?></div>
                <div class="stat-label">Con Stock</div>
            </div>
            <div class="stat-item">
                <div class="stat-value"><?php 
                    $sqlSinStock = "SELECT COUNT(*) as sin_stock FROM producto WHERE id_vendedor = ? AND stock = 0";
                    $stmtSinStock = $connect->prepare($sqlSinStock);
                    $stmtSinStock->bind_param("i", $idUsuario);
                    $stmtSinStock->execute();
                    $sinStock = $stmtSinStock->get_result()->fetch_assoc()['sin_stock'];
                    echo $sinStock;
                ?></div>
                <div class="stat-label">Sin Stock</div>
            </div>
        </div>

        <!-- Section Header -->
        <div class="section-header">
            <h2><i class="fas fa-box"></i> Mis Productos (<?php echo $totalProductos; ?>)</h2>
            <div>
                <a href="seller-apart-product-create.php" class="btn btn-success">
                    <i class="fas fa-plus"></i> Nuevo Producto
                </a>
            </div>
        </div>

        <?php if ($productos->num_rows > 0): ?>
            <!-- Products Grid -->
            <div class="products-grid">
                <?php while($producto = $productos->fetch_assoc()): 
                    // Determinar clase de stock
                    $stockClass = '';
                    if ($producto['stock'] > 20) {
                        $stockClass = 'stock-high';
                    } elseif ($producto['stock'] > 5) {
                        $stockClass = 'stock-medium';
                    } elseif ($producto['stock'] > 0) {
                        $stockClass = 'stock-low';
                    } else {
                        $stockClass = 'stock-out';
                    }
                ?>
                <div class="product-card">
                    <div class="product-image">
                        <?php if (!empty($producto['imagen_url'])): ?>
                            <img src="<?php echo $producto['imagen_url']; ?>" alt="<?php echo htmlspecialchars($producto['nombre']); ?>">
                        <?php else: ?>
                            <div class="no-image">
                                <i class="fas fa-box"></i>
                            </div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h3 class="product-name" title="<?php echo htmlspecialchars($producto['nombre']); ?>">
                            <?php echo htmlspecialchars($producto['nombre']); ?>
                        </h3>
                        
                        <div class="product-price">
                            $<?php echo number_format($producto['precio'], 0, ',', '.'); ?>
                        </div>
                        
                        <div class="product-meta">
                            <div>
                                <i class="fas fa-tag"></i> 
                                <?php echo $producto['nombre_categoria'] ? htmlspecialchars($producto['nombre_categoria']) : 'Sin categoría'; ?>
                            </div>
                            <div>
                                <span class="product-stock <?php echo $stockClass; ?>">
                                    <i class="fas fa-cubes"></i> <?php echo $producto['stock']; ?> unidades
                                </span>
                            </div>
                        </div>
                        
                        <div class="product-actions">
                            <a href="editar-producto.php?id=<?php echo $producto['id_producto']; ?>" class="btn btn-sm">
                                <i class="fas fa-edit"></i> Editar
                            </a>
                            <a href="?eliminar=<?php echo $producto['id_producto']; ?>" 
                               class="btn btn-sm btn-danger"
                               onclick="return confirm('¿Estás seguro de eliminar este producto?')">
                                <i class="fas fa-trash"></i> Eliminar
                            </a>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($totalPaginas > 1): ?>
            <div class="pagination">
                <?php if ($pagina > 1): ?>
                    <a href="?pagina=<?php echo $pagina - 1; ?>">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                <?php endif; ?>
                
                <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                    <?php if ($i == $pagina): ?>
                        <span class="active"><?php echo $i; ?></span>
                    <?php else: ?>
                        <a href="?pagina=<?php echo $i; ?>"><?php echo $i; ?></a>
                    <?php endif; ?>
                <?php endfor; ?>
                
                <?php if ($pagina < $totalPaginas): ?>
                    <a href="?pagina=<?php echo $pagina + 1; ?>">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        <?php else: ?>
            <!-- Empty State -->
            <div class="empty-state">
                <i class="fas fa-box-open"></i>
                <h3>No tienes productos registrados</h3>
                <p>Aún no has creado ningún producto. ¡Comienza ahora!</p>
                <a href="seller-apart-product-create.php" class="btn btn-success" style="margin-top: 20px;">
                    <i class="fas fa-plus"></i> Crear Primer Producto
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
        // Confirmar eliminación de productos
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a.btn-danger');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este producto? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
            
            // Animación para las tarjetas de productos
            const productCards = document.querySelectorAll('.product-card');
            productCards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)