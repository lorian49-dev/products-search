<?php
ob_start(); 
session_start();

// --- ZONA DE DEBUGGING CRÍTICO ---
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
// ---------------------------------

// Ruta de conexión a la base de datos
if (!include('../shortCuts/connect.php')) {
    die("Error Fatal: No se pudo encontrar o incluir el archivo de conexión.");
}

// Validación de sesión y rol
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

// 1. FUNCIÓN DE MANEJO DE IMAGENES


/**
 * Procesa la subida de una imagen al servidor.
 * * @param array $file_array La variable $_FILES['nombre_del_campo']
 * @param string|null $current_image_path La ruta de la imagen actual (para no borrarla si no hay nueva subida).
 * @param object $connect Objeto de conexión a MySQLi.
 * @return string|null La nueva ruta de la imagen o la ruta actual.
 */
function handleImageUpload($file_array, $current_image_path, $connect) {
    // Definición de parámetros
    $upload_dir = '../uploads/productos/'; 
    $max_size = 2097152; // 2MB

    // Crear el directorio si no existe
    if (!is_dir($upload_dir)) {
        // Intentar crear la carpeta con permisos recursivos
        if (!mkdir($upload_dir, 0777, true)) {
            header("Location: products-dashboard-admin-index.php?error=ErrorSubida");
            exit();
        }
    }

    if (isset($file_array) && $file_array['error'] == 0) {
        // Validación de tamaño
        if ($file_array['size'] > $max_size) {
            header("Location: products-dashboard-admin-index.php?error=ErrorSubida");
            exit();
        }

        // Generar nombre único basado en el tiempo para evitar colisiones
        $file_name = time() . '_' . uniqid() . '.' . strtolower(pathinfo($file_array['name'], PATHINFO_EXTENSION));
        $target_file = $upload_dir . $file_name;
        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

        // Validación de tipo (extensión)
        $allowed_types = ["jpg", "png", "jpeg", "gif"];
        if (!in_array($imageFileType, $allowed_types)) {
            header("Location: products-dashboard-admin-index.php?error=TipoInvalido");
            exit();
        }
        
        // Subir el archivo
        if (move_uploaded_file($file_array['tmp_name'], $target_file)) {
            // Éxito. Borrar la imagen anterior (física) si existe una diferente
            if (!empty($current_image_path) && file_exists($current_image_path) && $current_image_path != $target_file) {
                unlink($current_image_path);
            }
            return $target_file; // Retorna la nueva ruta
        } else {
            // Error en move_uploaded_file
            header("Location: products-dashboard-admin-index.php?error=ErrorSubida");
            exit();
        }
    }

    // Si no se subió una nueva imagen, retorna la ruta que ya estaba en el formulario (si estamos editando)
    return $current_image_path; 
}



// 2. LÓGICA DEL CONTROLADOR (ACTIONS)


$action = $_GET['action'] ?? null;

switch ($action) {
    

    // CRUD DE PRODUCTOS

    
    case 'create':
    case 'update':
        
        // 1. Obtener datos y sanitizar
        $nombre = mysqli_real_escape_string($connect, $_POST['nombre'] ?? '');
        $origen = mysqli_real_escape_string($connect, $_POST['origen'] ?? '');
        $descripcion = mysqli_real_escape_string($connect, $_POST['descripcion'] ?? '');
        $precio = mysqli_real_escape_string($connect, $_POST['precio'] ?? 0.00);
        $stock = mysqli_real_escape_string($connect, $_POST['stock'] ?? 0);
        $categorias = $_POST['categorias'] ?? []; // Array de IDs de categorías
        
        // El producto creado/editado desde este panel debe ser de la Bodega Central (id_vendedor = NULL)
        $id_vendedor = 'NULL'; 
        $id_producto = $_POST['id_producto'] ?? null;
        
        // 2. Manejo de la Imagen
        $imagen_actual = $_POST['imagen_actual'] ?? null;
        $image_path = handleImageUpload($_FILES['imagen'] ?? [], $imagen_actual, $connect);
        
        // Preparar valor para SQL: si hay path, lo escapamos y lo ponemos entre comillas; si no, es NULL.
        $image_sql_value = $image_path ? "'" . mysqli_real_escape_string($connect, $image_path) . "'" : 'NULL';

        if ($action == 'create') {
            // --- CREAR PRODUCTO (INSERT) ---
            $query = "INSERT INTO producto (nombre, descripcion, origen, precio, stock, imagen_url, id_vendedor, fecha_creacion) 
                        VALUES ('$nombre', '$descripcion', '$origen', $precio, $stock, $image_sql_value, $id_vendedor, NOW())";
            
            if (mysqli_query($connect, $query)) {
                $id_producto = mysqli_insert_id($connect); 
                $msg = 'producto_creado';
            } else {
                // Redirecciona en caso de fallo de BD
                header("Location: products-dashboard-admin-index.php?error=ErrorBD");
                exit();
            }
            
        } else { // update
            // --- ACTUALIZAR PRODUCTO (UPDATE) ---
            if (empty($id_producto)) {
                header("Location: products-dashboard-admin-index.php?error=ProductoIDVacio");
                exit();
            }
            
            $query = "UPDATE producto SET 
                        nombre = '$nombre', 
                        descripcion = '$descripcion', 
                        origen = '$origen', 
                        precio = $precio, 
                        stock = $stock, 
                        imagen_url = $image_sql_value
                      WHERE id_producto = '$id_producto'";
            
            if (!mysqli_query($connect, $query)) {
                header("Location: products-dashboard-admin-index.php?error=ErrorBD");
                exit();
            }
            $msg = 'producto_actualizado';
        }
        
        // 3. Manejo de Categorías (Relación Many-to-Many)
        // Solo si hay un ID de producto válido (creado o actualizado)
        if (!empty($id_producto)) {
            // 3.1. Borrar todas las asignaciones existentes para este producto
            $query_delete_cats = "DELETE FROM producto_categoria WHERE id_producto = '$id_producto'";
            mysqli_query($connect, $query_delete_cats);

            // 3.2. Re-insertar las categorías seleccionadas
            if (!empty($categorias)) {
                $values = [];
                foreach ($categorias as $cat_id) {
                    $cat_id_safe = mysqli_real_escape_string($connect, $cat_id);
                    $values[] = "('$id_producto', '$cat_id_safe')";
                }
                $query_insert_cats = "INSERT INTO producto_categoria (id_producto, id_categoria) VALUES " . implode(', ', $values);
                mysqli_query($connect, $query_insert_cats);
            }
        }
        
        header("Location: products-dashboard-admin-index.php?msg=$msg");
        exit();

    case 'delete':
        // --- ELIMINAR PRODUCTO (DELETE) ---
        if ($_SESSION['admin_rol'] != 1) { // Solo Admin General puede eliminar
            header("Location: products-dashboard-admin-index.php?error=NoPermisos");
            exit();
        }
        
        $id_producto = mysqli_real_escape_string($connect, $_GET['id'] ?? null);
        
        if (empty($id_producto)) {
            header("Location: products-dashboard-admin-index.php");
            exit();
        }
        
        // 1. Obtener ruta de imagen para borrar el archivo (físico)
        $query_get_img = "SELECT imagen_url FROM producto WHERE id_producto = '$id_producto'";
        $result_img = mysqli_query($connect, $query_get_img);
        $row_img = mysqli_fetch_assoc($result_img);
        $image_to_delete = $row_img['imagen_url'] ?? null;

        // 2. Iniciar Transacción para asegurar atomicidad
        mysqli_begin_transaction($connect);

        try {
            // 3. Eliminar relaciones de categorías primero
            $query_delete_cats = "DELETE FROM producto_categoria WHERE id_producto = '$id_producto'";
            mysqli_query($connect, $query_delete_cats);
            
            // 4. Eliminar producto
            $query_delete_prod = "DELETE FROM producto WHERE id_producto = '$id_producto'";
            if (!mysqli_query($connect, $query_delete_prod)) {
                // Si falla aquí, es probablemente por otra FK (ej. pedidos, carrito)
                throw new Exception("Error al eliminar producto (posible FK).");
            }
            
            // 5. Commit de la BD y borrado físico de la imagen
            mysqli_commit($connect);
            if (!empty($image_to_delete) && file_exists($image_to_delete)) {
                unlink($image_to_delete);
            }

            header("Location: products-dashboard-admin-index.php?msg=producto_eliminado");
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($connect);
            header("Location: products-dashboard-admin-index.php?error=error_fk");
            exit();
        }

    // ------------------------------------------------
    // CRUD DE CATEGORÍAS
    // ------------------------------------------------
    
    case 'create_cat':
        // --- CREAR CATEGORÍA ---
        $nombre_categoria = mysqli_real_escape_string($connect, $_POST['nombre_categoria'] ?? '');
        
        if (!empty($nombre_categoria)) {
            $query = "INSERT INTO categoria (nombre_categoria) VALUES ('$nombre_categoria')";
            if (mysqli_query($connect, $query)) {
                header("Location: products-dashboard-admin-index.php?msg=categoria_creada");
            } else {
                header("Location: products-dashboard-admin-index.php?error=ErrorBD");
            }
        }
        exit();

    case 'delete_cat':
        // --- ELIMINAR CATEGORÍA ---
        if ($_SESSION['admin_rol'] != 1) { 
            header("Location: products-dashboard-admin-index.php?error=NoPermisos");
            exit();
        }

        $id_categoria = mysqli_real_escape_string($connect, $_GET['id_categoria'] ?? null);
        
        // Primero, eliminar referencias en producto_categoria
        $query_del_ref = "DELETE FROM producto_categoria WHERE id_categoria = '$id_categoria'";
        mysqli_query($connect, $query_del_ref);
        
        // Luego, intentar eliminar la categoría
        $query_del_cat = "DELETE FROM categoria WHERE id_categoria = '$id_categoria'";
        
        if (mysqli_query($connect, $query_del_cat)) {
            header("Location: products-dashboard-admin-index.php?msg=categoria_eliminada");
        } else {
            // Si falla, es posible que haya otra FK (menos común, pero posible)
            header("Location: products-dashboard-admin-index.php?error=categoria_en_uso");
        }
        exit();
        
    default:
        // Si se accede sin una acción válida, redirigir al listado
        header("Location: products-dashboard-admin-index.php");
        exit();
}

ob_end_flush();
?>

