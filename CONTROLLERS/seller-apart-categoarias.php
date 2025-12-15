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

// Crear tabla para categorías del vendedor si no existe
$sqlCreateTable = "CREATE TABLE IF NOT EXISTS categorias_vendedor (
    id_categoria_vendedor INT PRIMARY KEY AUTO_INCREMENT,
    id_vendedor INT NOT NULL,
    nombre_categoria VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (id_vendedor) REFERENCES vendedor(id_vendedor) ON DELETE CASCADE
)";
$connect->query($sqlCreateTable);

// Procesar acciones
$mensaje = '';
$accion = $_GET['accion'] ?? '';

// Crear categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_categoria'])) {
    $nombre = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion']);
    
    if (!empty($nombre)) {
        $sqlInsert = "INSERT INTO categorias_vendedor (id_vendedor, nombre_categoria, descripcion) VALUES (?, ?, ?)";
        $stmt = $connect->prepare($sqlInsert);
        $stmt->bind_param("iss", $idUsuario, $nombre, $descripcion);
        
        if ($stmt->execute()) {
            $mensaje = '<div class="success-message">Categoría creada exitosamente</div>';
        } else {
            $mensaje = '<div class="error-message">Error al crear la categoría</div>';
        }
    } else {
        $mensaje = '<div class="error-message">El nombre de la categoría es requerido</div>';
    }
}

// Editar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_categoria'])) {
    $idCategoria = $_POST['id_categoria'];
    $nombre = trim($_POST['nombre_categoria']);
    $descripcion = trim($_POST['descripcion']);
    
    // Verificar que la categoría pertenece al vendedor
    $sqlVerificar = "SELECT id_vendedor FROM categorias_vendedor WHERE id_categoria_vendedor = ?";
    $stmtVerificar = $connect->prepare($sqlVerificar);
    $stmtVerificar->bind_param("i", $idCategoria);
    $stmtVerificar->execute();
    $categoria = $stmtVerificar->get_result()->fetch_assoc();
    
    if ($categoria && $categoria['id_vendedor'] == $idUsuario) {
        $sqlUpdate = "UPDATE categorias_vendedor SET nombre_categoria = ?, descripcion = ? WHERE id_categoria_vendedor = ?";
        $stmtUpdate = $connect->prepare($sqlUpdate);
        $stmtUpdate->bind_param("ssi", $nombre, $descripcion, $idCategoria);
        
        if ($stmtUpdate->execute()) {
            $mensaje = '<div class="success-message">Categoría actualizada exitosamente</div>';
        } else {
            $mensaje = '<div class="error-message">Error al actualizar la categoría</div>';
        }
    } else {
        $mensaje = '<div class="error-message">No tienes permiso para editar esta categoría</div>';
    }
}

// Eliminar categoría
if (isset($_GET['eliminar'])) {
    $idEliminar = $_GET['eliminar'];
    
    // Verificar que la categoría pertenece al vendedor
    $sqlVerificar = "SELECT id_vendedor FROM categorias_vendedor WHERE id_categoria_vendedor = ?";
    $stmtVerificar = $connect->prepare($sqlVerificar);
    $stmtVerificar->bind_param("i", $idEliminar);
    $stmtVerificar->execute();
    $categoria = $stmtVerificar->get_result()->fetch_assoc();
    
    if ($categoria && $categoria['id_vendedor'] == $idUsuario) {
        // Verificar si la categoría está en uso
        $sqlEnUso = "SELECT COUNT(*) as total FROM producto WHERE categoria_vendedor_id = ?";
        $stmtEnUso = $connect->prepare($sqlEnUso);
        $stmtEnUso->bind_param("i", $idEliminar);
        $stmtEnUso->execute();
        $enUso = $stmtEnUso->get_result()->fetch_assoc()['total'];
        
        if ($enUso == 0) {
            $sqlEliminar = "DELETE FROM categorias_vendedor WHERE id_categoria_vendedor = ?";
            $stmtEliminar = $connect->prepare($sqlEliminar);
            $stmtEliminar->bind_param("i", $idEliminar);
            
            if ($stmtEliminar->execute()) {
                $mensaje = '<div class="success-message">Categoría eliminada exitosamente</div>';
            }
        } else {
            $mensaje = '<div class="error-message">No puedes eliminar esta categoría porque está en uso por ' . $enUso . ' producto(s)</div>';
        }
    }
}

// Obtener categorías del vendedor
$sqlCategorias = "SELECT * FROM categorias_vendedor WHERE id_vendedor = ? ORDER BY nombre_categoria";
$stmtCategorias = $connect->prepare($sqlCategorias);
$stmtCategorias->bind_param("i", $idUsuario);
$stmtCategorias->execute();
$categorias = $stmtCategorias->get_result();

// Obtener categoría para editar
$categoriaEditar = null;
if (isset($_GET['editar'])) {
    $idEditar = $_GET['editar'];
    $sqlEditar = "SELECT * FROM categorias_vendedor WHERE id_categoria_vendedor = ? AND id_vendedor = ?";
    $stmtEditar = $connect->prepare($sqlEditar);
    $stmtEditar->bind_param("ii", $idEditar, $idUsuario);
    $stmtEditar->execute();
    $categoriaEditar = $stmtEditar->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mis Categorías - <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></title>
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

        input, textarea {
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

        /* Categories Table */
        .categories-container {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
        }

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
                <h2><i class="fas fa-tags"></i> Mis Categorías</h2>
                <a href="seller-apart-manage-bussiness.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Volver al Dashboard
                </a>
            </div>

            <?php echo $mensaje; ?>

            <!-- Formulario para crear/editar categoría -->
            <div class="form-container">
                <h3 style="color: #1f2937; margin-bottom: 20px;">
                    <i class="fas fa-<?php echo $categoriaEditar ? 'edit' : 'plus'; ?>"></i>
                    <?php echo $categoriaEditar ? 'Editar Categoría' : 'Crear Nueva Categoría'; ?>
                </h3>
                
                <form method="POST">
                    <?php if ($categoriaEditar): ?>
                        <input type="hidden" name="id_categoria" value="<?php echo $categoriaEditar['id_categoria_vendedor']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label>Nombre de la Categoría *</label>
                        <input type="text" name="nombre_categoria" 
                               value="<?php echo $categoriaEditar ? htmlspecialchars($categoriaEditar['nombre_categoria']) : ''; ?>" 
                               placeholder="Ej: Ropa de Invierno" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción (Opcional)</label>
                        <textarea name="descripcion" placeholder="Describe esta categoría..."><?php echo $categoriaEditar ? htmlspecialchars($categoriaEditar['descripcion']) : ''; ?></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 10px;">
                        <button type="submit" name="<?php echo $categoriaEditar ? 'editar_categoria' : 'crear_categoria'; ?>" 
                                class="btn btn-success">
                            <i class="fas fa-save"></i> 
                            <?php echo $categoriaEditar ? 'Actualizar Categoría' : 'Crear Categoría'; ?>
                        </button>
                        
                        <?php if ($categoriaEditar): ?>
                            <a href="categorias-vendedor.php" class="btn btn-outline">
                                <i class="fas fa-times"></i> Cancelar
                            </a>
                        <?php endif; ?>
                    </div>
                </form>
            </div>

            <!-- Lista de categorías -->
            <div class="categories-container">
                <h3 style="color: #1f2937; margin-bottom: 20px;">
                    <i class="fas fa-list"></i> Mis Categorías (<?php echo $categorias->num_rows; ?>)
                </h3>
                
                <?php if ($categorias->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Descripción</th>
                                <th>Fecha Creación</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while($categoria = $categorias->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($categoria['nombre_categoria']); ?></strong>
                                </td>
                                <td>
                                    <?php echo $categoria['descripcion'] ? htmlspecialchars($categoria['descripcion']) : '<span style="color: #9ca3af;">Sin descripción</span>'; ?>
                                </td>
                                <td>
                                    <?php echo date('d/m/Y', strtotime($categoria['fecha_creacion'])); ?>
                                </td>
                                <td>
                                    <a href="?editar=<?php echo $categoria['id_categoria_vendedor']; ?>" class="btn btn-sm">
                                        <i class="fas fa-edit"></i> Editar
                                    </a>
                                    <a href="?eliminar=<?php echo $categoria['id_categoria_vendedor']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('¿Estás seguro de eliminar esta categoría?')">
                                        <i class="fas fa-trash"></i> Eliminar
                                    </a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fas fa-tags"></i>
                        <h3>No tienes categorías creadas</h3>
                        <p>Crea categorías personalizadas para organizar mejor tus productos.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Información -->
            <div style="margin-top: 30px; padding: 20px; background: #f0f9ff; border-radius: 8px; border-left: 4px solid #0ea5e9;">
                <h4 style="color: #0369a1; margin-bottom: 10px;"><i class="fas fa-lightbulb"></i> ¿Por qué usar categorías personales?</h4>
                <ul style="color: #4b5563; padding-left: 20px;">
                    <li>Organiza tus productos de manera personalizada</li>
                    <li>Facilita la búsqueda y filtrado de tus productos</li>
                    <li>Crea catálogos temáticos con productos de la misma categoría</li>
                    <li>Mejora la experiencia de navegación para tus clientes</li>
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
                    if (!confirm('¿Estás seguro de que quieres eliminar esta categoría? Esta acción no se puede deshacer.')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>