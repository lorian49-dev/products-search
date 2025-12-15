<?php
session_start();
require "../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Verificar si es vendedor
$sqlVendedor = "SELECT v.*, u.nombre, u.apellido FROM vendedor v 
                JOIN usuario u ON v.id_vendedor = u.id_usuario 
                WHERE v.id_vendedor = ?";
$stmtVendedor = $connect->prepare($sqlVendedor);
$stmtVendedor->bind_param("i", $idUsuario);
$stmtVendedor->execute();
$vendedor = $stmtVendedor->get_result()->fetch_assoc();

if (!$vendedor) {
    header("Location: seller-apart-create-bussiness.php");
    exit;
}

// Verificar que la tabla catalogo exista
$sqlCheckTable = "SHOW TABLES LIKE 'catalogo'";
$tableExists = $connect->query($sqlCheckTable)->num_rows > 0;

if (!$tableExists) {
    // Crear tabla catalogo si no existe
    $sqlCreateCatalogo = "CREATE TABLE catalogo (
        id_catalogo INT PRIMARY KEY AUTO_INCREMENT,
        id_vendedor INT NOT NULL,
        fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        fecha_actualizacion TIMESTAMP NULL ON UPDATE CURRENT_TIMESTAMP,
        nombre_catalogo VARCHAR(150) DEFAULT NULL,
        descripcion TEXT,
        FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor) ON DELETE CASCADE
    )";
    $connect->query($sqlCreateCatalogo);
}

// Procesar acciones
$mensaje = '';

// Crear catálogo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_catalogo'])) {
    $nombre = trim($_POST['nombre_catalogo']);
    $descripcion = trim($_POST['descripcion']);
    
    if (!empty($nombre)) {
        $sqlInsert = "INSERT INTO catalogo (id_vendedor, nombre_catalogo, descripcion) VALUES (?, ?, ?)";
        $stmt = $connect->prepare($sqlInsert);
        $stmt->bind_param("iss", $idUsuario, $nombre, $descripcion);
        
        if ($stmt->execute()) {
            $idCatalogo = $connect->insert_id;
            
            // Si hay productos seleccionados, agregarlos al catálogo
            if (isset($_POST['productos']) && is_array($_POST['productos'])) {
                foreach ($_POST['productos'] as $idProducto) {
                    // Verificar que el producto pertenece al vendedor
                    $sqlVerificarProducto = "SELECT id_vendedor FROM producto WHERE id_producto = ?";
                    $stmtVerificar = $connect->prepare($sqlVerificarProducto);
                    $stmtVerificar->bind_param("i", $idProducto);
                    $stmtVerificar->execute();
                    $producto = $stmtVerificar->get_result()->fetch_assoc();
                    
                    if ($producto && $producto['id_vendedor'] == $idUsuario) {
                        $sqlAgregarProducto = "INSERT INTO catalogo_producto (id_catalogo, id_producto) VALUES (?, ?)";
                        $stmtAgregar = $connect->prepare($sqlAgregarProducto);
                        $stmtAgregar->bind_param("ii", $idCatalogo, $idProducto);
                        $stmtAgregar->execute();
                    }
                }
            }
            
            $mensaje = '<div class="success-message">Catálogo creado exitosamente</div>';
        } else {
            $mensaje = '<div class="error-message">Error al crear el catálogo</div>';
        }
    } else {
        $mensaje = '<div class="error-message">El nombre del catálogo es requerido</div>';
    }
}

// Eliminar catálogo
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    
    // Verificar que el catálogo pertenece al vendedor
    $sqlVerificar = "SELECT id_vendedor FROM catalogo WHERE id_catalogo = ?";
    $stmtVerificar = $connect->prepare($sqlVerificar);
    $stmtVerificar->bind_param("i", $idEliminar);
    $stmtVerificar->execute();
    $catalogo = $stmtVerificar->get_result()->fetch_assoc();
    
    if ($catalogo && $catalogo['id_vendedor'] == $idUsuario) {
        // Eliminar productos del catálogo primero
        $sqlEliminarProductos = "DELETE FROM catalogo_producto WHERE id_catalogo = ?";
        $stmtProductos = $connect->prepare($sqlEliminarProductos);
        $stmtProductos->bind_param("i", $idEliminar);
        $stmtProductos->execute();
        
        // Eliminar el catálogo
        $sqlEliminarCatalogo = "DELETE FROM catalogo WHERE id_catalogo = ?";
        $stmtEliminar = $connect->prepare($sqlEliminarCatalogo);
        $stmtEliminar->bind_param("i", $idEliminar);
        
        if ($stmtEliminar->execute()) {
            echo "<script>
                alert('Catálogo eliminado exitosamente');
                window.location.href = 'catalogos-vendedor.php';
            </script>";
            exit;
        }
    }
}

// Obtener catálogos del vendedor
$sqlCatalogos = "SELECT c.*, 
                (SELECT COUNT(*) FROM catalogo_producto cp WHERE cp.id_catalogo = c.id_catalogo) as total_productos
                FROM catalogo c 
                WHERE c.id_vendedor = ? 
                ORDER BY c.fecha_creacion DESC";
$stmtCatalogos = $connect->prepare($sqlCatalogos);
$stmtCatalogos->bind_param("i", $idUsuario);
$stmtCatalogos->execute();
$catalogos = $stmtCatalogos->get_result();

// Obtener productos del vendedor para selector
$sqlProductos = "SELECT id_producto, nombre FROM producto WHERE id_vendedor = ? ORDER BY nombre";
$stmtProductos = $connect->prepare($sqlProductos);
$stmtProductos->bind_param("i", $idUsuario);
$stmtProductos->execute();
$productos = $stmtProductos->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Catálogos - <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></title>
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

        .dashboard-container {
            display: flex;
            min-height: 100vh;
        }

        /* Sidebar */
        .sidebar {
            width: 250px;
            background: white;
            padding: 25px 0;
            box-shadow: 3px 0 10px rgba(0,0,0,0.05);
            position: fixed;
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

        .nav-menu a:hover, .nav-menu a.active {
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
            margin-left: 250px;
            padding: 30px;
        }

        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f3f4f6;
        }

        .section-header h2 {
            color: #1f2937;
            font-size: 1.5rem;
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

        .btn-success {
            background: #10b981;
        }

        .btn-success:hover {
            background: #059669;
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

        /* Form Styles */
        .form-container {
            max-width: 600px;
            margin: 0 auto 40px;
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #374151;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 100px;
        }

        select[multiple] {
            height: 200px;
        }

        .success-message {
            background: #d1fae5;
            color: #065f46;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #10b981;
        }

        .error-message {
            background: #fee2e2;
            color: #991b1b;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            border-left: 4px solid #ef4444;
        }

        /* Catalogos Grid */
        .catalogos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .catalogo-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            transition: transform 0.3s;
        }

        .catalogo-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .catalogo-header {
            padding: 20px;
            background: #f8fafc;
            border-bottom: 1px solid #e5e7eb;
        }

        .catalogo-title {
            font-size: 1.2rem;
            font-weight: 600;
            color: #1f2937;
            margin-bottom: 8px;
        }

        .catalogo-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.9rem;
            color: #6b7280;
        }

        .catalogo-body {
            padding: 20px;
        }

        .catalogo-descripcion {
            color: #4b5563;
            margin-bottom: 15px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .productos-count {
            display: inline-block;
            padding: 5px 12px;
            background: #e0e7ff;
            color: #3730a3;
            border-radius: 20px;
            font-size: 0.9rem;
            font-weight: 500;
        }

        .catalogo-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
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

        /* Help Text */
        .help-text {
            font-size: 0.85rem;
            color: #6b7280;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Sidebar -->
                <div class="sidebar">
            <div class="logo">
                <h2>Hermes<span>Seller</span></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="seller-apart-manage-bussiness.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="seller-apart-product-create.php" class="active"><i class="fas fa-plus-circle"></i> Crear Producto</a></li>
                <li><a href="seller-apart-products.php"><i class="fas fa-box"></i> Mis Productos</a></li>
                <li><a href="categorias-vendedor.php"><i class="fas fa-tags"></i> Mis Categorías</a></li>
                <li><a href="seller-apart-catalogo.php"><i class="fas fa-book"></i> Mis Catálogos</a></li>
                <li><a href="seller-apart-negocio-editar.php"><i class="fas fa-store"></i> Editar Negocio</a></li>
                <li><a href="../home.php"><i class="fas fa-home"></i> Volver al Inicio</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="section-header">
                <h2><i class="fas fa-book"></i> Mis Catálogos</h2>
                <a href="seller-apart-manage-bussiness.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            <?php echo $mensaje; ?>

            <!-- Formulario para crear catálogo -->
            <div class="form-container">
                <h3 style="color: #1f2937; margin-bottom: 20px;">
                    <i class="fas fa-plus"></i> Crear Nuevo Catálogo
                </h3>
                
                <form method="POST">
                    <div class="form-group">
                        <label>Nombre del Catálogo *</label>
                        <input type="text" name="nombre_catalogo" 
                               placeholder="Ej: Colección de Verano 2024" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción (Opcional)</label>
                        <textarea name="descripcion" placeholder="Describe este catálogo..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label>Seleccionar Productos (Opcional)</label>
                        <select name="productos[]" multiple class="productos-select">
                            <?php if ($productos->num_rows > 0): ?>
                                <?php while($producto = $productos->fetch_assoc()): ?>
                                    <option value="<?php echo $producto['id_producto']; ?>">
                                        <?php echo htmlspecialchars($producto['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <option disabled>No tienes productos creados</option>
                            <?php endif; ?>
                        </select>
                        <div class="help-text">
                            Mantén presionada la tecla Ctrl (Cmd en Mac) para seleccionar múltiples productos
                        </div>
                    </div>
                    
                    <button type="submit" name="crear_catalogo" class="btn btn-success">
                        <i class="fas fa-save"></i> Crear Catálogo
                    </button>
                </form>
            </div>

            <!-- Lista de catálogos -->
            <div style="margin-top: 30px;">
                <h3 style="color: #1f2937; margin-bottom: 20px;">
                    <i class="fas fa-list"></i> Mis Catálogos (<?php echo $catalogos->num_rows; ?>)
                </h3>
                
                <?php if ($catalogos->num_rows > 0): ?>
                    <div class="catalogos-grid">
                        <?php while($catalogo = $catalogos->fetch_assoc()): ?>
                        <div class="catalogo-card">
                            <div class="catalogo-header">
                                <h4 class="catalogo-title"><?php echo htmlspecialchars($catalogo['nombre_catalogo']); ?></h4>
                                <div class="catalogo-meta">
                                    <span>
                                        <i class="far fa-calendar"></i> 
                                        <?php echo date('d/m/Y', strtotime($catalogo['fecha_creacion'])); ?>
                                    </span>
                                    <span class="productos-count">
                                        <i class="fas fa-box"></i> <?php echo $catalogo['total_productos']; ?> productos
                                    </span>
                                </div>
                            </div>
                            
                            <div class="catalogo-body">
                                <?php if ($catalogo['descripcion']): ?>
                                    <p class="catalogo-descripcion"><?php echo htmlspecialchars($catalogo['descripcion']); ?></p>
                                <?php else: ?>
                                    <p class="catalogo-descripcion" style="color: #9ca3af; font-style: italic;">
                                        Sin descripción
                                    </p>
                                <?php endif; ?>
                                
                                <div class="catalogo-actions">
                                    <a href="ver-catalogo.php?id=<?php echo $catalogo['id_catalogo']; ?>" class="btn btn-sm">
                                        <i class="fas fa-eye"></i> Ver
                                    </a>
                                    <a href="editar-catalogo.php?id=<?php echo $catalogo['id_catalogo']; ?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="?eliminar=<?php echo $catalogo['id_catalogo']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Estás seguro de eliminar este catálogo?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </div>
                            </div>
                        </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h3>No tienes catálogos creados</h3>
                        <p>Crea catálogos para organizar y mostrar tus productos de manera temática.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información -->
            <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                <h4 style="color: #0369a1; margin-bottom: 10px;"><i class="fas fa-lightbulb"></i> ¿Qué son los catálogos?</h4>
                <ul style="color: #4b5563; padding-left: 20px;">
                    <li>Agrupa productos relacionados en colecciones temáticas</li>
                    <li>Facilita que los clientes encuentren productos similares</li>
                    <li>Puedes crear catálogos por temporada, categoría, tipo de producto, etc.</li>
                    <li>Mejora la experiencia de compra y aumenta las ventas cruzadas</li>
                </ul>
            </div>
        </div>
    </div>

    <script>
        // Confirmar eliminaciones
        document.addEventListener('DOMContentLoaded', function() {
            const deleteLinks = document.querySelectorAll('a.btn-danger');
            deleteLinks.forEach(link => {
                link.addEventListener('click', function(e) {
                    if (!confirm('¿Estás seguro de que quieres eliminar este catálogo? Todos los productos serán removidos del catálogo.')) {
                        e.preventDefault();
                    }
                });
            });

            // Mejorar la selección de productos
            const productosSelect = document.querySelector('.productos-select');
            if (productosSelect) {
                // Añadir búsqueda en el select
                productosSelect.addEventListener('keyup', function(e) {
                    const searchTerm = e.target.value.toLowerCase();
                    const options = this.querySelectorAll('option');
                    
                    options.forEach(option => {
                        const text = option.textContent.toLowerCase();
                        option.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            }
        });
    </script>
</body>
</html>