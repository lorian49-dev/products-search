<?php
session_start();
require "../shortCuts/connect.php";

// Cargar configuración de Cloudinary
$cloudinary = require_once "../shortCuts/cloudinary-config.php";

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

// Obtener categorías globales
$sqlCategorias = "SELECT * FROM categoria";
$categorias = $connect->query($sqlCategorias);

// Procesar formulario
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $descripcion = trim($_POST['descripcion']);
    $precio = $_POST['precio'];
    $stock = $_POST['stock'];
    $origen = trim($_POST['origen']);
    $categoria_id = $_POST['categoria_id'];
    
    // Manejar imagen con Cloudinary
    $imagen_url = null;
    $public_id = null; // Guardar el public_id por si necesitas eliminarla después
    
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0) {
        // Validar tipo de archivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($_FILES['imagen']['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $mensaje = '<div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        Formato de imagen no permitido. Solo se aceptan JPG, PNG, GIF y WEBP.
                        </div>';
        } else {
            // Validar tamaño máximo (5MB)
            $maxSize = 5 * 5120 * 5120; // 5MB en bytes
            if ($_FILES['imagen']['size'] > $maxSize) {
                $mensaje = '<div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                            La imagen es demasiado grande. Tamaño máximo: 5MB.
                            </div>';
            } else {
                try {
                    // Generar nombre único para el archivo
                    $fileName = pathinfo($_FILES['imagen']['name'], PATHINFO_FILENAME);
                    $safeFileName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $fileName);
                    
                    // Subir imagen a Cloudinary
                    $uploadResult = $cloudinary->uploadApi()->upload(
                        $_FILES['imagen']['tmp_name'],
                        [
                            'folder' => 'hermes_bd/productos/vendedor_' . $idUsuario,
                            'public_id' => 'producto_' . time() . '_' . $safeFileName,
                            'use_filename' => true,
                            'unique_filename' => true,
                            'overwrite' => true,
                            'resource_type' => 'image',
                            'transformation' => [
                                'quality' => 'auto:good',
                                'fetch_format' => 'auto'
                            ]
                        ]
                    );
                    
                    // Obtener la URL segura de la imagen
                    $imagen_url = $uploadResult['secure_url'];
                    $public_id = $uploadResult['public_id'];
                    
                    // Mensaje de éxito para debug
                    error_log("Imagen subida a Cloudinary. URL: " . $imagen_url);
                    
                } catch (Exception $e) {
                    $mensaje = '<div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                                Error al subir la imagen a Cloudinary: ' . htmlspecialchars($e->getMessage()) . '
                                </div>';
                    error_log("Error Cloudinary: " . $e->getMessage());
                }
            }
        }
    }
    
    // Si no hay mensaje de error, proceder con la inserción
    if (empty($mensaje)) {
        // Insertar producto con el id_vendedor
        $sqlInsert = "INSERT INTO producto (nombre, descripcion, imagen_url, precio, stock, origen, id_vendedor, cloudinary_public_id) 
                      VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $connect->prepare($sqlInsert);
        $stmt->bind_param("ssssisss", $nombre, $descripcion, $imagen_url, $precio, $stock, $origen, $idUsuario, $public_id);
        
        if ($stmt->execute()) {
            $idProducto = $connect->insert_id;
            
            // Asignar categoría si se seleccionó
            if ($categoria_id) {
                $sqlAsignarCategoria = "INSERT INTO producto_categoria (id_producto, id_categoria) VALUES (?, ?)";
                $stmtCat = $connect->prepare($sqlAsignarCategoria);
                $stmtCat->bind_param("ii", $idProducto, $categoria_id);
                $stmtCat->execute();
            }
            
            echo "<script>
                alert('Producto creado exitosamente');
                window.location.href = 'productos-vendedor.php';
            </script>";
            exit;
        } else {
            // Si falla la inserción, eliminar la imagen de Cloudinary
            if ($public_id) {
                try {
                    $cloudinary->uploadApi()->destroy($public_id);
                } catch (Exception $e) {
                    error_log("Error al eliminar imagen de Cloudinary: " . $e->getMessage());
                }
            }
            
            $mensaje = '<div style="background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                        Error al crear el producto: ' . $connect->error . '
                        </div>';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Producto - <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></title>
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

        /* Form Styles */
        .form-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
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

        input, select, textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
            font-size: 16px;
        }

        textarea {
            resize: vertical;
            min-height: 120px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
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

        /* Preview de imagen */
        .image-preview {
            margin-top: 10px;
            display: none;
        }

        .image-preview img {
            max-width: 200px;
            max-height: 200px;
            border-radius: 8px;
            border: 2px solid #d1d5db;
            object-fit: cover;
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
                <li><a href="seller-apart-main-view.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="crear-producto.php" class="active"><i class="fas fa-plus-circle"></i> Crear Producto</a></li>
                <li><a href="seller-apart-products.php"><i class="fas fa-box"></i> Mis Productos</a></li>
                <li><a href="categorias-vendedor.php"><i class="fas fa-tags"></i> Mis Categorías</a></li>
                <li><a href="catalogos-vendedor.php"><i class="fas fa-book"></i> Mis Catálogos</a></li>
                <li><a href="pedidos-vendedor.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="editar-negocio.php"><i class="fas fa-store"></i> Editar Negocio</a></li>
                <li><a href="../home.php"><i class="fas fa-home"></i> Volver al Inicio</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <div class="section-header">
                <h2><i class="fas fa-plus-circle"></i> Crear Nuevo Producto</h2>
                <a href="seller-apart-products.php" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Ver Mis Productos
                </a>
            </div>

            <?php echo $mensaje; ?>

            <div class="form-container">
                <form method="POST" enctype="multipart/form-data" id="productForm">
                    <div class="form-group">
                        <label>Nombre del Producto *</label>
                        <input type="text" name="nombre" placeholder="Ej: Camiseta de algodón" required>
                    </div>
                    
                    <div class="form-group">
                        <label>Descripción *</label>
                        <textarea name="descripcion" placeholder="Describe tu producto..." required></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label>Precio * (COP)</label>
                            <input type="number" name="precio" step="0.01" min="0" placeholder="Ej: 50000" required>
                        </div>
                        
                        <div class="form-group">
                            <label>Stock *</label>
                            <input type="number" name="stock" min="0" placeholder="Ej: 100" required>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Origen/Marca</label>
                        <input type="text" name="origen" placeholder="Ej: Colombia, Nike, etc.">
                    </div>
                    
                    <div class="form-group">
                        <label>Categoría (Opcional)</label>
                        <select name="categoria_id">
                            <option value="">Seleccionar categoría</option>
                            <?php 
                            // Resetear el puntero del resultado
                            $categorias->data_seek(0);
                            while($categoria = $categorias->fetch_assoc()): ?>
                                <option value="<?php echo $categoria['id_categoria']; ?>">
                                    <?php echo htmlspecialchars($categoria['nombre_categoria']); ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label>Imagen del Producto</label>
                        <input type="file" name="imagen" accept="image/*" id="imagenInput">
                        <small style="color: #6b7280; display: block; margin-top: 5px;">
                            Formatos aceptados: JPG, PNG, GIF, WEBP. Tamaño máximo: 5MB
                        </small>
                        <div class="image-preview" id="imagePreview">
                            <img src="" alt="Vista previa" id="previewImage">
                        </div>
                    </div>
                    
                    <div class="form-group" style="margin-top: 30px;">
                        <button href="seller-apart-manage-bussiness.php" class="btn btn-success" style="padding: 15px 30px; font-size: 1rem;">
                            <i class="fas fa-save"></i> Crear Producto
                        </button>
                        <a href="seller-apart-manage-bussiness.php" class="btn btn-outline" style="margin-left: 10px;">
                            <i class="fas fa-times"></i> Cancelar
                        </a>
                    </div>
                </form>
                
                <div style="margin-top: 30px; padding: 15px; background: #f9fafb; border-radius: 8px; border-left: 4px solid #3b82f6;">
                    <h4 style="color: #1f2937; margin-bottom: 10px;"><i class="fas fa-info-circle"></i> Información importante:</h4>
                    <ul style="color: #4b5563; padding-left: 20px;">
                        <li>Vendedor: <strong><?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></strong></li>
                        <li>Todos los productos que crees aparecerán con tu nombre de empresa</li>
                        <li>Las imágenes se subirán automáticamente a Cloudinary</li>
                        <li>El stock se actualizará automáticamente con cada venta</li>
                        <li>Puedes editar o eliminar tus productos en cualquier momento</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Validación del formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('productForm');
            const imagenInput = document.getElementById('imagenInput');
            const imagePreview = document.getElementById('imagePreview');
            const previewImage = document.getElementById('previewImage');
            const precioInput = document.querySelector('input[name="precio"]');
            const stockInput = document.querySelector('input[name="stock"]');
            
            // Vista previa de imagen
            imagenInput.addEventListener('change', function() {
                const file = this.files[0];
                if (file) {
                    const reader = new FileReader();
                    
                    reader.addEventListener('load', function() {
                        previewImage.src = reader.result;
                        imagePreview.style.display = 'block';
                    });
                    
                    reader.readAsDataURL(file);
                    
                    // Validar tamaño
                    if (file.size > 5 * 5120 * 5120) { // 5MB
                        alert('La imagen es demasiado grande. Tamaño máximo: 5MB.');
                        this.value = '';
                        imagePreview.style.display = 'none';
                    }
                } else {
                    imagePreview.style.display = 'none';
                }
            });
            
            form.addEventListener('submit', function(e) {
                let valid = true;
                
                // Validar campos requeridos
                const requiredFields = form.querySelectorAll('[required]');
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        valid = false;
                        field.style.borderColor = '#ef4444';
                    } else {
                        field.style.borderColor = '#d1d5db';
                    }
                });
                
                // Validar precio positivo
                if (precioInput.value && parseFloat(precioInput.value) < 0) {
                    valid = false;
                    precioInput.style.borderColor = '#ef4444';
                    alert('El precio debe ser un número positivo.');
                }
                
                // Validar stock positivo
                if (stockInput.value && parseInt(stockInput.value) < 0) {
                    valid = false;
                    stockInput.style.borderColor = '#ef4444';
                    alert('El stock debe ser un número positivo.');
                }
                
                // Validar tipo de archivo si se seleccionó una imagen
                if (imagenInput.files.length > 0) {
                    const file = imagenInput.files[0];
                    const allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
                    
                    if (!allowedTypes.includes(file.type)) {
                        valid = false;
                        imagenInput.style.borderColor = '#ef4444';
                        alert('Formato de imagen no permitido. Solo se aceptan JPG, PNG, GIF y WEBP.');
                        }
                }
                
                if (!valid) {
                    e.preventDefault();
                    alert('Por favor, complete todos los campos requeridos correctamente.');
                } else {
                    // Mostrar mensaje de carga
                    const submitButton = form.querySelector('button[type="submit"]');
                    submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo imagen...';
                    submitButton.disabled = true;
                }
            });
            
            // Restaurar bordes cuando el usuario empiece a escribir
            const inputs = form.querySelectorAll('input, textarea, select');
            inputs.forEach(input => {
                input.addEventListener('input', function() {
                    this.style.borderColor = '#d1d5db';
                });
            });
        });
    </script>
</body>
</html>