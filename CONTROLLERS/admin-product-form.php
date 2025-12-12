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
    <link href="https://fonts.googleapis.com/css2?family=Roboto+Condensed:wght@100..900&display=swap" rel="stylesheet">
<link href="https://fonts.googleapis.com/css2?family=Anton&display=swap" rel="stylesheet">
    <style>
        *{
            padding:0;
            margin:0;
            box-sizing:border-box;
        }
/* ============================
   Importación de Fuentes
================================ */

/* ============================
   Estilos Globales
================================ */
html{
    font-size: 1vw;
}

body {
    font-family: 'Roboto Condensed', sans-serif;
    background-color: #2f2f2fff;
    color: #fff8f1;
}

.container {
    width: 50%;
    margin: 30px auto;
    padding: 3rem;
    background: #2f2f2fff;
    border-radius: 2rem;
    box-shadow:
        -5px -5px 10px rgba(255, 255, 255, 0.1),
        10px 10px 10px rgba(0, 0, 0, 0.3),
        inset -3px -3px 5px rgba(255, 255, 255, 0.1),
        inset 5px 5px 10px rgba(0, 0, 0, 0.3);
}

aside{
    grid-column: 2 / 3;
    grid-row: 1 / 2;
    box-shadow:
        -5px -5px 10px rgba(255, 255, 255, 0.1),
        10px 10px 10px rgba(0, 0, 0, 0.3),
        inset -3px -3px 5px rgba(255, 255, 255, 0.1),
        inset 5px 5px 10px rgba(0, 0, 0, 0.3);
        border-radius:2rem;
        display:flex;
        flex-direction:column;
        align-items:center;
        padding-top:1rem;
        gap:1rem;
}


h1 {
  font-family: "Anton", sans-serif;
  font-weight: 400;
  font-style: normal;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
    margin-bottom: 20px;
    text-align: center;
}

form {
    width: 100%;
    display:grid;
    grid-template-columns:1fr 1fr;
    grid-template-rows:repeat(3, 1fr);
    gap:1rem;
    height :90vh;
}

/* ============================
   Formularios
================================ */
.main-form{
    background:linear-gradient(135deg, #0D47A1, #0097b2);
    padding:2rem;
    border-radius:1rem;
    width: 100;
    grid-column: 1 / 2;
    grid-row: 1 / 3;
    box-shadow:1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075);
    overflow-y:auto;
}

.main-form::-webkit-scrollbar {
    width: 8px; /* Un poco más de ancho para que sea más fácil de usar */
  }

  /* Fondo de la barra de desplazamiento (la pista) */
.main-form::-webkit-scrollbar-track {
    background: transparent; /* Fondo transparente */
    border-radius: 10px;
    /* El margen superior e inferior creará el espacio que buscabas */
    margin: 50px 0; 
  }
  
  /* El "pulgar" o la parte móvil de la barra de desplazamiento */
.main-form::-webkit-scrollbar-thumb {
    background: #fff8f1; /* Color del pulgar */
    border-radius: 10px;
    border: 2px solid transparent; /* Crea un padding visual alrededor del pulgar */
    background-clip: content-box;
  }
  
  /* Estilos para cuando el mouse está sobre la barra */
.main-form::-webkit-scrollbar-thumb:hover {
    background-color: #ffb000; /* Un color ligeramente más claro al pasar el mouse */
  }
  
  /* Ocultamos las flechas (esto generalmente no es necesario, pero lo mantenemos por si acaso) */
.main-form::-webkit-scrollbar-button {
    display: none;
  }

.main-form input[type="text"], textarea{
    width: 100%;
    padding: .5rem 1rem;
    border-radius:1.5rem;
    border-style: none;
    margin: 1rem 0;
    box-shadow:1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075);
      background-color:#2f2f2fff;
      color:#fff8f1;
     font-family: "Roboto Condensed", sans-serif;
}

input:focus{
    outline:none;
}

.main-image label{
 font-size:.8rem;
 margin-bottom:1rem;
}

#imagen{
    width: 100%;
    background-color:#2f2f2fff;
    padding:1rem;
    border-radius:1rem;
    overflow:hidden;
    box-shadow:1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075);
}

label {
    display: block;
    text-align: center;
     font-family: "Roboto Condensed", sans-serif;
}



textarea {
    resize: vertical;
    min-height: 5rem;
}

#precio,
#stock {
    width: 50%;
    padding:.5rem;
    border-radius:2rem;
    border-style:none;
    background-color:#fff8f1;
}

/* ============================
   Botones
================================ */
.button-group {
    display: flex;
    justify-content: space-around;
    align-items: center;
    margin-top: 2rem;
    width: 100%;
    grid-column: 1 / 3;
    grid-row: 3 / 4;
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
.category-box-parent{
  grid-column: 2 / 3;
    grid-row: 2 / 3;
  display:flex;
  flex-direction:column;
  align-items:center;
  padding:2rem 0 0 2rem;
}

.category-box {
  box-shadow: -5px -5px 10px rgba(255, 255, 255, 0.1),
              10px 10px 10px rgba(0, 0, 0, 0.3),
              inset -3px -3px 5px rgba(255, 255, 255, 0.1),
              inset 5px 5px 10px rgba(0, 0, 0, 0.3);
  padding:1rem;
  border-radius: 1rem;
  width: 90%;
  max-height: 15rem;
  overflow-y: auto;
  display: grid;
  grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
  gap: 10px;
}


.category-box::-webkit-scrollbar {
    width: 8px; /* Un poco más de ancho para que sea más fácil de usar */
  }

  /* Fondo de la barra de desplazamiento (la pista) */
.category-box::-webkit-scrollbar-track {
    background: transparent; /* Fondo transparente */
    border-radius: 10px;
    /* El margen superior e inferior creará el espacio que buscabas */
    margin: 50px 0; 
  }
  
  /* El "pulgar" o la parte móvil de la barra de desplazamiento */
.category-box::-webkit-scrollbar-thumb {
    background: #fff8f1; /* Color del pulgar */
    border-radius: 10px;
    border: 2px solid transparent; /* Crea un padding visual alrededor del pulgar */
    background-clip: content-box;
  }
  
  /* Estilos para cuando el mouse está sobre la barra */
.category-box::-webkit-scrollbar-thumb:hover {
    background-color: #ffb000; /* Un color ligeramente más claro al pasar el mouse */
  }
  
  /* Ocultamos las flechas (esto generalmente no es necesario, pero lo mantenemos por si acaso) */
.category-box::-webkit-scrollbar-button {
    display: none;
  }

.category-box label {
    font-weight: 100;
    display: flex;
    justify-content:flex-start;
    text-align:left;
    align-items: center;
    gap: 5px;
    font-size:.7rem;
    margin-bottom:.5rem;
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
                <input type="hidden" name="id_producto" value="<?php echo htmlspecialchars($id_producto); ?>" autocomplete="off">
            <?php endif; ?>
<main class="main-form">

                <label for="nombre">Nombre del Producto <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto['nombre'] ?? ''); ?>" required autocomplete="off"></label>
                <label for="origen">Marca/Fabricante <input type="text" id="origen" name="origen" value="<?php echo htmlspecialchars($producto['origen'] ?? ''); ?>" autocomplete="off"></label>

                <label for="descripcion">Descripción</label>
                <textarea id="descripcion" name="descripcion"><?php echo htmlspecialchars($producto['descripcion'] ?? ''); ?></textarea>
<section class="main-image">
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
</section>
</main>
<aside>
                    <label for="precio">Precio *</label>
                    <input type="number" id="precio" name="precio" step="0.01" min="0" value="<?php echo htmlspecialchars($producto['precio'] ?? '0.00'); ?>" required>

 
                    <label for="stock">Stock *</label>
                    <input type="number" id="stock" name="stock" min="0" value="<?php echo htmlspecialchars($producto['stock'] ?? '0'); ?>" required>
</aside>
<section class="category-box-parent">
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
</section>
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

</div>  
</body>
</html>
<?php ob_end_flush(); ?>

