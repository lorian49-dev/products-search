<?php
session_start();

include('../shortCuts/connect.php'); 

// --- Bloque de Seguridad ---
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>alert('Acceso denegado.'); window.location.href = '../../registros-inicio-sesion/admin-login.php';</script>";
    exit();
}
$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>alert('No tiene permisos suficientes.'); window.location.href = '../../home.php';</script>";
    exit();
}

// --- Lógica de Consultas (Lectura) ---

// Manejo de Búsqueda (Añadido)
$busqueda = $_GET['busqueda'] ?? '';
$where_clause = '';
if (!empty($busqueda)) {
    $search_term = mysqli_real_escape_string($connect, '%' . $busqueda . '%');
    $where_clause = " WHERE p.nombre LIKE '$search_term' 
                      OR p.descripcion LIKE '$search_term' 
                      OR c.nombre_categoria LIKE '$search_term'";
}


// 1. Consulta Principal de Productos (Añadida la columna imagen_url)
$query_productos = "SELECT 
                        p.*, 
                        IFNULL(u.nombre, 'Bodega Central') AS nombre_origen, 
                        GROUP_CONCAT(c.nombre_categoria SEPARATOR ', ') AS categorias_asignadas
                    FROM producto p
                    LEFT JOIN vendedor v ON p.id_vendedor = v.id_vendedor
                    LEFT JOIN usuario u ON v.id_vendedor = u.id_usuario
                    LEFT JOIN producto_categoria pc ON p.id_producto = pc.id_producto
                    LEFT JOIN categoria c ON pc.id_categoria = c.id_categoria
                    $where_clause
                    GROUP BY p.id_producto
                    ORDER BY p.id_producto DESC";

$result_productos = mysqli_query($connect, $query_productos);
if (!$result_productos) { 
    // Muestra el error de MySQL y detiene la ejecución.
    die("Error en consulta de productos: " . mysqli_error($connect)); 
}
$total_productos = mysqli_num_rows($result_productos);

// 2. Consulta de Categorías (para el Modal de Gestión)
$query_cats = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria ASC";
$result_cats = mysqli_query($connect, $query_cats);

// 3. Manejo de Mensajes (Alertas/Notificaciones)
$msg = $_GET['msg'] ?? '';
$error = $_GET['error'] ?? '';

$messages = [
    'producto_creado' => ['type' => 'success', 'text' => 'Producto de Bodega creado exitosamente.'],
    'producto_actualizado' => ['type' => 'success', 'text' => 'Producto actualizado correctamente.'],
    'producto_eliminado' => ['type' => 'info', 'text' => 'Producto eliminado permanentemente.'],
    'error_fk' => ['type' => 'danger', 'text' => 'ERROR: No se puede eliminar el producto/categoría. Está asociado a otros registros (pedidos, etc.).'],
    'categoria_creada' => ['type' => 'success', 'text' => 'Categoría creada exitosamente.'],
    'categoria_eliminada' => ['type' => 'info', 'text' => 'Categoría eliminada.'],
    'categoria_en_uso' => ['type' => 'danger', 'text' => 'Error: La categoría está asignada a productos y no puede ser eliminada.'],
    'ErrorBD' => ['type' => 'danger', 'text' => 'Error en la base de datos.'],
    'ErrorSubida' => ['type' => 'danger', 'text' => 'Error al subir la imagen. Revise el formato (JPG/PNG/GIF) y permisos de la carpeta.'],
];

if (!empty($msg) && isset($messages[$msg])) {
    $alert_class = $messages[$msg]['type'];
    $alert_text = $messages[$msg]['text'];
} elseif (!empty($error) && isset($messages[$error])) {
    $alert_class = $messages[$error]['type'];
    $alert_text = $messages[$error]['text'];
} else {
    $alert_class = $alert_text = '';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Bodega HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
     <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css"> 
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <style>
        /* Estilos Generales y del Contenedor */
        .dashboard-container { width: 100%; margin: 20px auto 20px 15%; padding: 0 20px; display: flex; flex-direction: column; justify-content: center; align-items: center;}
        h1 {color: #414141ff; padding-bottom: 10px; margin-bottom: 20px; }
        h4{font-weight: 400;}

        /* Estilos de Botones */
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 25px;
            font-size: 0.95em;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, #EF6C00, #ffb000);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #0D47A1, #0097b2);
            transform: translateY(-2px);
        }

        .btn-danger {
            background: #dc3545;
            color: white;
        }

        .btn-danger:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #461d01;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
            transform: translateY(-2px);
        }

        .btn-warning {
            background: #ffc107;
            color: #212529;
        }

        .btn-warning:hover {
            background: #e0a800;
            transform: translateY(-2px);
        }

        .btn-success {
            background: #28a745;
            color: white;
        }

        .btn-success:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-info{
            position: absolute;
            left: 39rem;
            font-size: .6rem;
            width: 2rem;
            height: 2rem;
            background-color: #461d01;
            color: #fff8f1;
            transition: all .5s ease;
            overflow: hidden;
            padding-left: .5rem;
        }

        .btn-info:hover{
            width: 10rem;
        }

        .btn-info i{
            font-size: 1rem !important;
            margin-right: .5rem;
        }

        .btn-info .texto{
            opacity: 0;
            white-space: nowrap;
             transition: opacity 0.5s ease;

        }

        .btn-info:hover .texto{
            opacity: 1;
        }

        body.body-its-dark .btn-info{
            background-color: transparent;
            box-shadow:  -5px -5px 10px rgba(255, 255, 255, 0.1),
                         10px 10px 10px rgba(0, 0, 0, 0.3),
                         inset -3px -3px 5px rgba(255, 255, 255, 0.1),
                         inset 5px 5px 10px rgba(0, 0, 0, 0.3);
        }

        /* Estilos de Alertas */
        .alert { padding: 15px; margin-bottom: 20px; border-radius: 8px; font-weight: bold; }
        .alert.success { background-color: #d4edda; color: #155724; border-left: 5px solid #4CAF50; }
        .alert.danger { background-color: #f8d7da; color: #721c24; border-left: 5px solid #F44336; }
        .alert.info { background-color: #d1ecf1; color: #0c5460; border-left: 5px solid #2196F3; }

        /* Estilos de Tabla */
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { padding: 12px 15px; text-align: left; font-weight: 400; font-size: 0.9em; text-align: center;}
        .data-table td { padding: 10px 15px; font-size: 0.9em; vertical-align: middle; }
        .data-table img { width: 50px; height: 50px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd; }
        .acciones { display: flex; gap: 5px; flex-wrap: wrap; }
        
        /*Busqueda*/

        .search-container { width: 80%;background:linear-gradient(135deg, #0D47A1, #0097b2); padding: 1.5rem; border-radius: 1rem; margin-bottom: 20px; display: flex; gap: 1rem; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.4);}
        .search-box { flex: 1; position: relative; }
        .search-box input { width: 90%; padding: .8rem .9rem .8rem 3rem;border-style: none; border-radius: 1.5rem; font-size: 0.85em;box-shadow:  1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075); transition: background .5s ease;}
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; }

        /* Estilos de Modal */
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; overflow: auto; background-color: rgba(0,0,0,0.4); backdrop-filter: blur(5px);}
        .modal-content { background-color: #fefefe; margin: 15% auto; padding: 20px; width: 80%; max-width: 600px; border-radius: 10px; box-shadow: 0 4px 8px rgba(0,0,0,0.2); }
        .close { float: right; font-size: 28px; font-weight: bold; cursor: pointer; }
        #nombre_categoria{width: 90%; padding: .8rem .9rem .8rem 3rem;border-style: none; border-radius: 2rem; font-size: 0.85em;box-shadow:  1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075); transition: background .5s ease; background: #2f2f2fff; color: #fff8f1; margin-top: 1rem;}
      #nombre_categoria::placeholder{font-size: .7rem; color: #fff8f1;}
    </style>
</head>
<body>
     <nav id="navegation">
        <a href="user-dashboard-admin.php"><i class="fas fa-home" id="iconHome"></i></a>
        <span>
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO" width="200px">
        </span>
        <!--bienvenida personalizada con rol-->
        <span class="welcome-admin">
            Bienvenido <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?>
            (<?php
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Colaborador';
                else echo 'Administrador';
                ?>)
        </span>
        <ul class="listMother">
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <a href="user-dashboard-admin-index.php">
                    <li>Usuarios</li>
                </a>
                <a href="client-dashboard-index.php">
                    <li>Clientes</li>
                </a>
                <a href="seller-dashboard-admin-index.php">
                    <li>Vendedores</li>
                </a>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li class="current-page">Productos</li>
                <li>Categorias</li>
                <li>Listado de ventas por vendedor</li>
            </ul>
            <li id="liGets">Gestion de pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <a href="orders-admin-index.php">
                    <li>Pedidos</li>
                </a>
                <li>Actualizar estados de pedidos</li>
            </ul>
            <li id="liStats">Reportes Generales<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListStats">
                <li>Mejores Vendedores</li>
                <li>Mas Vendidos</li>
                <li>Trafico de la plataforma</li>
            </ul>
            <li id="liAbout">Acerca de<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListAbout">
                <li>Politicas de privacidad y uso</li>
                <li>Terminos para vendedores</li>
            </ul>
            <span class="btn-color-mode">
                <form action="../registros-inicio-sesion/logout.php" method="POST">
                    <button type="submit" class="btn-close-session">Cerrar sesión</button>
                </form>
                <div class="btn-color-mode-choices">
                    <span class="background-modes"></span>
                    <button class="light-mode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-sun" viewBox="0 0 16 16">
                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                        </svg>
                    </button>
                    <button class="dark-mode"><i class="fa-solid fa-moon"></i></button>
                </div>
            </span>
    </nav>
    <div class="dashboard-container">
        <h1>Gestión de Productos 📦</h1>
        
        <?php if (!empty($alert_text)): ?>
            <div class="alert <?php echo $alert_class; ?>">
                <?php echo $alert_text; ?>
            </div>
             <script>
        // Limpia la URL después de 50 ms
        setTimeout(() => {
            const url = new URL(window.location.href);
            url.searchParams.delete('msg');
            url.searchParams.delete('error');
            window.history.replaceState({}, document.title, url.pathname);
        }, 50);
    </script>
        <?php endif; ?>

         <form action="products-dashboard-admin-index.php" method="GET" class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                 <input type="text" name="busqueda" placeholder="Buscar por producto, descripción o categoría..." value="<?php echo htmlspecialchars($busqueda); ?>">
            </div>
            <button class="btn btn-primary" id="btn-search-seller">
                <i class="fas fa-search"></i>
            </button>
             <?php if (!empty($busqueda)): ?>
                <a href="products-dashboard-admin-index.php" class="btn btn-secondary">Mostrar Todos</a>
            <?php endif; ?>
            </form>

        <div style="width:80%;display: flex; justify-content: center; margin-bottom: 20px; gap:1rem; position:relative;">
            <a href="admin-product-form.php">
                <button class="btn_add-new-user">
                    Crear Producto (Bodega)<i class="fas fa-plus"></i>
                </button> 
            </a>
            <button type="button" class="btn btn-info" onclick="document.getElementById('CategoryManagementModal').style.display='block'">
                <i class="fas fa-tags"></i><span class="texto">Gestionar Categorías</span> 
            </button>
        </div>

        <div class="table-container">
            <div style="padding: 15px;">
                <h3>Listado de Productos (Total: <?php echo $total_productos; ?>)</h3>
            </div>
            
            <?php if ($total_productos > 0): ?>
            <div style="overflow-x: auto; padding:2rem 02rem;">
                <table class="data-table">
                   <thead>
                        <tr>
                            <th>ID</th>
                            <th>Imagen</th> 
                            <th>Producto</th>
                            <th>Origen</th>
                            <th>Precio</th>
                            <th>Stock</th>
                            <th>Categorías</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($result_productos, 0); ?>
                        <?php while($producto = mysqli_fetch_assoc($result_productos)): ?>
                        <?php $id_prod = $producto['id_producto']; ?>
                        <tr>
                            <td>#<?php echo $id_prod; ?></td>
                            <td>
                                <?php if (!empty($producto['imagen_url']) && file_exists($producto['imagen_url'])): ?>
                                    <img src="<?php echo htmlspecialchars($producto['imagen_url']); ?>" alt="Imagen de Producto">
                                <?php else: ?>
                                    <i class="fas fa-image" style="color: #ccc; font-size: 2em;"></i>
                                <?php endif; ?>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($producto['nombre']); ?></strong>
                                <br><small style="color: #666;">Marca: <?php echo htmlspecialchars($producto['origen'] ?? 'N/A'); ?></small>
                            </td>
                            <td>
                                <?php if ($producto['id_vendedor'] === NULL): ?>
                                    <span style="font-weight: bold; color: green;"><i class="fas fa-warehouse"></i> Bodega Central</span>
                                <?php else: ?>
                                    <span title="ID Vendedor: <?php echo $producto['id_vendedor']; ?>">
                                        <i class="fas fa-store"></i> <?php echo htmlspecialchars($producto['nombre_origen']); ?>
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td>$<?php echo number_format($producto['precio'], 2, ',', '.'); ?></td>
                            <td style="text-align: center;">
                                <span style="font-weight: bold; color: <?php echo ($producto['stock'] > 5) ? 'green' : 'red'; ?>;">
                                    <?php echo $producto['stock']; ?>
                                </span>
                            </td>
                            <td><?php echo htmlspecialchars($producto['categorias_asignadas'] ?? 'N/A'); ?></td>
                            <td><?php echo date('d/m/Y', strtotime($producto['fecha_creacion'])); ?></td>
                            <td>
                                <div class="acciones">
                                    <a href="admin-product-form.php?id=<?php echo $id_prod; ?>" 
                                       class="btn btn-warning btn-action" title="Editar Producto">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if ($_SESSION['admin_rol'] == 1): // Solo Admin General puede eliminar ?>
                                    <button type="button" 
                                            onclick="confirmDelete(<?php echo $id_prod; ?>, '<?php echo htmlspecialchars($producto['nombre']); ?>')"
                                            class="btn btn-danger btn-action" title="Eliminar Producto">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
                <p style="padding: 20px; text-align: center; color: #999;">No se encontraron productos registrados (o no coinciden con la búsqueda).</p>
            <?php endif; ?>
        </div>
            <!--Manejo de la ventana modal para gestionar categorias-->
    <div id="CategoryManagementModal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="document.getElementById('CategoryManagementModal').style.display='none'">&times;</span>
            <h2>Gestión de Categorías</h2>
            
            <form action="producto-logic.php?action=create_cat" method="POST" style="margin-bottom: 20px;">
                <label for="nombre_categoria">Nueva Categoría:</label>
                <div style="display: flex; gap: 10px;">
                    <input type="text" id="nombre_categoria" name="nombre_categoria" required placeholder="Ej: Electrónica, Ropa, etc." autocomplete="off" style=" flex-grow: 1;">
                    <button type="submit" class="btn_add-new-user"><i class="fas fa-plus"></i> Crear</button>
                </div>
            </form>

            <h4 style="margin-top: 20px;">Categorías Existentes</h4>
            <div style="max-height: 200px; overflow-y: auto; padding:2rem;">
                <table class="data-table" style="width: 100%;">
                    <thead>
                        <tr><th>ID</th><th>Nombre</th><th>Acciones</th></tr>
                    </thead>
                    <tbody>
                        <?php mysqli_data_seek($result_cats, 0); ?>
                        <?php while($cat = mysqli_fetch_assoc($result_cats)): ?>
                        <tr>
                            <td>#<?php echo $cat['id_categoria']; ?></td>
                            <td><?php echo htmlspecialchars($cat['nombre_categoria']); ?></td>
                            <td>
                                <?php if ($_SESSION['admin_rol'] == 1): ?>
                                <a href="producto-logic.php?action=delete_cat&id_categoria=<?php echo $cat['id_categoria']; ?>"
                                   onclick="return confirm('¿Seguro que desea eliminar la categoría <?php echo htmlspecialchars($cat['nombre_categoria']); ?>? \n\nADVERTENCIA: Esto fallará si la categoría está en uso por algún producto.');"
                                   class="btn btn-danger" style="padding: 5px 10px;">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
                                <!--Fin del manejo de las categorias-->
    <script>
        function confirmDelete(id, nombre) {
            if (confirm(`ADVERTENCIA: ¿Está seguro de ELIMINAR PERMANENTEMENTE el producto "${nombre}" (#${id})?\n\nSi el producto tiene pedidos asociados, la eliminación fallará.`)) {
                // Redirige al script de lógica para el borrado.
                window.location.href = 'producto-logic.php?action=delete&id=' + id;
            }
        }
    </script>
    <script src="../scripts/admin.js"></script>
</body>
</html>

