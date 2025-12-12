<?php
ob_start(); 
session_start();

// --- ZONA DE DEBUGGING CRÍTICO ---
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------------

if (!include('../shortCuts/connect.php')) {
    die("Error Fatal: No se pudo encontrar o incluir el archivo de conexión.");
}

// Validación de sesión y rol
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

$id_producto = $_GET['id'] ?? null;
$es_edicion = !empty($id_producto);

$titulo_pagina = $es_edicion ? 'Editar Producto Existente' : 'Crear Nuevo Producto de Bodega';
$producto = [];
$categorias_asignadas = [];

// 1. Cargar datos del producto (solo en modo edición)
if ($es_edicion) {
    $id_producto_safe = mysqli_real_escape_string($connect, $id_producto);
    $query_producto = "SELECT * FROM producto WHERE id_producto = '$id_producto_safe'";
    $result_producto = mysqli_query($connect, $query_producto);

    if ($result_producto && mysqli_num_rows($result_producto) == 1) {
        $producto = mysqli_fetch_assoc($result_producto);
        
        // Cargar categorías asignadas al producto
        $query_categorias_asignadas = "SELECT id_categoria FROM producto_categoria WHERE id_producto = '$id_producto_safe'";
        $result_cats_asignadas = mysqli_query($connect, $query_categorias_asignadas);
        while ($row = mysqli_fetch_assoc($result_cats_asignadas)) {
            $categorias_asignadas[] = $row['id_categoria'];
        }
    } else {
        header('Location: products-dashboard-admin-index.php?error=ProductoNoEncontrado');
        exit();
    }
}

// 2. Cargar todas las categorías disponibles
$query_categorias = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC";
$result_categorias = mysqli_query($connect, $query_categorias);
if (!$result_categorias) {
    die("Error al cargar categorías: " . mysqli_error($connect));
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $titulo_pagina; ?> - HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <style>
/* ============================
   Importación de Fuentes
================================ */
@import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&display=swap');

/* ============================
   Estilos Globales
================================ */
body {
    font-family: 'Roboto Condensed', sans-serif;
    background-color: #2f2f2fff;
    color: #fff8f1;
}

.container {
    max-width: 800px;
    margin: 30px auto;
    padding: 3rem;
    background: #2f2f2fff;
    border-radius: 1rem;
    box-shadow:
        -5px -5px 10px rgba(255, 255, 255, 0.1),
        10px 10px 10px rgba(0, 0, 0, 0.3),
        inset -3px -3px 5px rgba(255, 255, 255, 0.1),
        inset 5px 5px 10px rgba(0, 0, 0, 0.3);
}

h1 {
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    text-align: center;
    font-weight: 300;
}

form {
    width: 100%;
}

/* ============================
   Formularios
================================ */
.form-group {
    margin-bottom: 20px;
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    text-align: center;
}

label {
    display: block;
    margin-bottom: 8px;
    font-weight: bold;
    text-align: center;
}

input[type="text"],
input[type="number"],
textarea,
input[type="file"] {
    width: 80%;
    padding: 12px;
    border-style: none;
    border-radius: 2rem;
    box-sizing: border-box;
    background-color: #fff8f1;
}

textarea {
    resize: vertical;
    min-height: 5rem;
}

#precio,
#stock {
    width: 100%;
}

/* ============================
   Botones
================================ */
.button-group {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 2rem;
    width: 80%;
}

.btn {
    padding: 12px 25px;
    border: none;
    border-radius: 25px;
    font-size: 1em;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    text-decoration: none;
    transition: all 0.3s ease;
}

/* ---- Botón Principal ---- */
.btn-success,
.btn-secondary {
    padding: .5rem 1rem;
    border-style: none;
    border-radius: 1.3rem;
    background: linear-gradient(135deg, #0D47A1, #0097b2);
    color: #fff8f1;
    font-family: 'Roboto Condensed', sans-serif;
    cursor: pointer;
    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
    transition: all .5s ease;
}

.btn-success:hover,
.btn-secondary:hover {
    transform: translateY(-2px);
    background: transparent;
    box-shadow:
        -5px -5px 10px rgba(255, 255, 255, 0.1),
        10px 10px 10px rgba(0, 0, 0, 0.3),
        inset -3px -3px 5px rgba(255, 255, 255, 0.1),
        inset 5px 5px 10px rgba(0, 0, 0, 0.3);
}

/* ============================
   Previsualización de Imágenes
================================ */
.image-preview {
    margin-top: 10px;
    border: 1px solid #ddd;
    padding: 10px;
    border-radius: 6px;
    text-align: center;
}

.image-preview img {
    max-width: 150px;
    height: auto;
    display: block;
    margin: 0 auto;
    border-radius: 4px;
}

/* ============================
   Caja de Categorías
================================ */
.category-box {
    border: 1px solid #ddd;
    padding: 15px;
    border-radius: 8px;
    width: 80%;
    overflow-y: auto;
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 10px;
}

.category-box label {
    font-weight: normal;
    display: flex;
    align-items: center;
    gap: 5px;
}

    </style>
</head>
<body>
    <div class="container">
        <h1><?php echo $titulo_pagina; ?></h1>
        
        <form action="producto-logic.php?action=<?php echo $es_edicion ? 'update' : 'create'; ?>" 
              method="POST" 
              enctype="multipart/form-data">

            <?php if ($es_edicion): ?>
                <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($id_producto); ?>">
            <?php endif; ?>

            <div class="form-group">
                <label for="nombre">Nombre del Producto *</label>
                <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>" required>
            </div>

            <div class="form-group">
                <label for="origen">Marca/Fabricante</label>
                <input type="text" id="origen" name="origen" value="<?php echo htmlspecialchars($producto['origen'] ?? ''); ?>">
            </div>

            <div class="form-group">
                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
            </div>

            <div class="form-group">
                <label for="imagen">Imagen del Producto (JPG, PNG, GIF - Max 2MB)</label>
                <input type="file" id="imagen" name="imagen" accept="image/*" class="form-control">
                
                <?php 
                $imagen_actual_path = $producto['imagen_url'] ?? null;
                // Mostrar la imagen actual si existe y el archivo físico existe
                if ($es_edicion && !empty($imagen_actual_path) && file_exists($imagen_actual_path)): 
                ?>
                    <div class="image-preview">
                        <p>Imagen actual:</p>
                        <img src="<?php echo htmlspecialchars($imagen_actual_path); ?>" alt="Imagen Actual">
                        <input type="hidden" name="imagen_actual" value="<?php echo htmlspecialchars($imagen_actual_path); ?>">
                    </div>
                <?php elseif ($es_edicion && !empty($imagen_actual_path)): ?>
                    <div class="image-preview">
                        <p style="color: red;">Imagen registrada en BD pero no encontrada en servidor. Suba una nueva.</p>
                        <input type="hidden" name="imagen_actual" value="">
                    </div>
                <?php endif; ?>
            </div>

            <div class="form-group" style="display: flex; gap: 20px;">
                <div style="flex: 1;">
                    <label for="precio">Precio *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio'] ?? '0.00'); ?>" required>
                </div>
                <div style="flex: 1;">
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock'] ?? '0'); ?>" required>
                </div>
            </div>
            
            <div class="form-group">
                <label>Categorías (Seleccione una o varias)</label>
                <div class="category-box">
                    <?php while ($cat = mysqli_fetch_assoc($result_categorias)): ?>
                        <label>
                            <input type="checkbox" 
                                   name="categorias[]" 
                                   value="<?php echo $cat['id_categoria']; ?>"
                                   <?php echo in_array($cat['id_categoria'], $categorias_asignadas) ? 'checked' : ''; ?>>
                            <?php echo htmlspecialchars($cat['nombre_categoria']); ?>
                        </label>
                    <?php endwhile; ?>
                </div>
            </div>

            <div class="button-group">
                <a href="products-dashboard-admin-index.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Volver al Listado
                </a>
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> <?php echo $es_edicion ? 'Guardar Cambios' : 'Crear Producto'; ?>
                </button>
            </div>
        </form>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>

