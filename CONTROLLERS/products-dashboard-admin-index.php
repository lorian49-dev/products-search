<?php
session_start();
include('../shortCuts/connect.php');

// Verificar autenticación
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';
    </script>";
    exit();
}

$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}

// Si es vendedor, obtener su ID
$vendedor_id = ($_SESSION['admin_rol'] == 2) ? ($_SESSION['vendedor_id'] ?? 0) : 0;

// Obtener estadísticas básicas
$query_estadisticas = "SELECT 
    (SELECT COUNT(*) FROM producto) as total_productos,
    (SELECT COUNT(DISTINCT id_vendedor) FROM producto) as vendedores_activos,
    (SELECT COUNT(DISTINCT cp.id_producto) FROM catalogo_producto cp) as productos_hermes,
    (SELECT SUM(stock) FROM producto) as total_stock,
    (SELECT COUNT(*) FROM producto WHERE stock = 0) as sin_stock,
    (SELECT COUNT(*) FROM categoria) as total_categorias";
    
$result_estadisticas = mysqli_query($connect, $query_estadisticas);
$estadisticas = mysqli_fetch_assoc($result_estadisticas);

// Obtener total de productos para el vendedor actual
if ($vendedor_id > 0) {
    $query_mis_productos = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $vendedor_id";
    $result_mis_productos = mysqli_query($connect, $query_mis_productos);
    $mis_productos = mysqli_fetch_assoc($result_mis_productos)['total'];
} else {
    $mis_productos = $estadisticas['total_productos'];
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión Productos - HERMES Click&Go</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <style>
        /* ESTILOS ESPECÍFICOS PARA PRODUCTOS */
        .hermes-container { max-width: 1800px; margin: 0 auto; padding: 20px; }
        
        /* Header con gradiente HERMES */
        .hermes-header { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); 
            color: white; 
            padding: 25px 30px; 
            border-radius: 15px; 
            margin-bottom: 25px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
        }
        .hermes-header h1 { margin: 0 0 10px 0; font-size: 2em; }
        .hermes-badge { 
            background: rgba(255,255,255,0.2); 
            padding: 5px 15px; 
            border-radius: 20px; 
            font-size: 0.9em; 
            display: inline-block; 
            margin-top: 10px; 
        }
        
        /* Tabs unificados */
        .hermes-tabs { 
            display: flex; 
            background: white; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .hermes-tab { 
            flex: 1; 
            padding: 15px; 
            border: none; 
            background: none; 
            font-size: 16px; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            gap: 10px; 
            transition: all 0.3s;
        }
        .hermes-tab:hover { background: #f8f9fa; }
        .hermes-tab.active { 
            background: #667eea; 
            color: white; 
            font-weight: 600; 
        }
        
        /* Panel de estadísticas */
        .hermes-stats { 
            display: grid; 
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); 
            gap: 15px; 
            margin-bottom: 25px; 
        }
        .hermes-stat-card { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            box-shadow: 0 3px 15px rgba(0,0,0,0.1); 
            text-align: center; 
            border-top: 4px solid #667eea; 
        }
        .hermes-stat-icon { 
            font-size: 2em; 
            color: #667eea; 
            margin-bottom: 10px; 
        }
        .hermes-stat-number { 
            font-size: 2em; 
            font-weight: bold; 
            color: #333; 
            margin: 5px 0; 
        }
        .hermes-stat-label { color: #666; font-size: 0.9em; }
        
        /* Barra de acciones */
        .hermes-action-bar { 
            background: white; 
            padding: 20px; 
            border-radius: 10px; 
            margin-bottom: 20px; 
            display: flex; 
            gap: 15px; 
            flex-wrap: wrap; 
            align-items: center; 
            box-shadow: 0 2px 10px rgba(0,0,0,0.1); 
        }
        .hermes-search { 
            flex: 1; 
            min-width: 250px; 
            padding: 10px 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 8px; 
            font-size: 14px; 
            transition: border-color 0.3s;
        }
        .hermes-search:focus { 
            border-color: #667eea; 
            outline: none; 
        }
        .hermes-filter { 
            padding: 10px 15px; 
            border: 2px solid #e0e0e0; 
            border-radius: 8px; 
            background: white; 
            font-size: 14px; 
            min-width: 180px;
        }
        
        /* Botones HERMES */
        .hermes-btn { 
            padding: 10px 20px; 
            border: none; 
            border-radius: 8px; 
            font-size: 14px; 
            font-weight: 600; 
            cursor: pointer; 
            display: inline-flex; 
            align-items: center; 
            gap: 8px; 
            transition: all 0.3s; 
        }
        .hermes-btn-primary { 
            background: #667eea; 
            color: white; 
        }
        .hermes-btn-primary:hover { 
            background: #5a6fd8; 
            transform: translateY(-2px); 
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4); 
        }
        .hermes-btn-success { 
            background: #28a745; 
            color: white; 
        }
        .hermes-btn-success:hover { 
            background: #218838; 
            transform: translateY(-2px); 
        }
        .hermes-btn-warning { 
            background: #ffc107; 
            color: #212529; 
        }
        .hermes-btn-warning:hover { 
            background: #e0a800; 
            transform: translateY(-2px); 
        }
        .hermes-btn-danger { 
            background: #dc3545; 
            color: white; 
        }
        .hermes-btn-danger:hover { 
            background: #c82333; 
            transform: translateY(-2px); 
        }
        
        /* Tabla de productos */
        .hermes-table-container { 
            background: white; 
            border-radius: 10px; 
            overflow: hidden; 
            box-shadow: 0 5px 20px rgba(0,0,0,0.1); 
            margin-bottom: 30px; 
        }
        .hermes-table { 
            width: 100%; 
            border-collapse: collapse; 
        }
        .hermes-table th { 
            background: #f8f9fa; 
            padding: 15px; 
            text-align: left; 
            font-weight: 600; 
            color: #333; 
            border-bottom: 2px solid #e0e0e0; 
            font-size: 0.9em; 
        }
        .hermes-table td { 
            padding: 15px; 
            border-bottom: 1px solid #eee; 
            vertical-align: middle; 
        }
        .hermes-table tr:hover { 
            background: #f9f9fa; 
        }
        
        /* Imágenes de productos */
        .producto-img { 
            width: 60px; 
            height: 60px; 
            object-fit: cover; 
            border-radius: 8px; 
            border: 2px solid #eee; 
        }
        .producto-img-placeholder { 
            width: 60px; 
            height: 60px; 
            background: #f8f9fa; 
            border-radius: 8px; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            color: #ccc; 
            border: 2px dashed #ddd; 
        }
        
        /* Badges de estado */
        .badge { 
            padding: 4px 10px; 
            border-radius: 20px; 
            font-size: 0.8em; 
            font-weight: 600; 
            display: inline-flex; 
            align-items: center; 
            gap: 4px; 
        }
        .badge-hermes { 
            background: #d4edda; 
            color: #155724; 
        }
        .badge-pendiente { 
            background: #fff3cd; 
            color: #856404; 
        }
        .badge-stock-bajo { 
            background: #f8d7da; 
            color: #721c24; 
        }
        .badge-stock-normal { 
            background: #d1ecf1; 
            color: #0c5460; 
        }
        
        /* Acciones */
        .hermes-actions { 
            display: flex; 
            gap: 5px; 
        }
        .action-btn { 
            width: 32px; 
            height: 32px; 
            border-radius: 6px; 
            border: none; 
            cursor: pointer; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            transition: all 0.2s; 
        }
        .action-btn-view { 
            background: #17a2b8; 
            color: white; 
        }
        .action-btn-edit { 
            background: #28a745; 
            color: white; 
        }
        .action-btn-delete { 
            background: #dc3545; 
            color: white; 
        }
        .action-btn:hover { 
            transform: translateY(-2px); 
            box-shadow: 0 3px 10px rgba(0,0,0,0.2); 
        }
        
        /* Modal HERMES */
        .hermes-modal-overlay { 
            display: none; 
            position: fixed; 
            top: 0; left: 0; 
            right: 0; bottom: 0; 
            background: rgba(0,0,0,0.7); 
            z-index: 2000; 
            align-items: center; 
            justify-content: center; 
            padding: 20px; 
        }
        .hermes-modal { 
            background: white; 
            border-radius: 15px; 
            width: 100%; 
            max-width: 800px; 
            max-height: 90vh; 
            overflow-y: auto; 
            box-shadow: 0 20px 60px rgba(0,0,0,0.3); 
        }
        
        /* Proceso HERMES */
        .hermes-process { 
            background: #f8f9fa; 
            border-radius: 10px; 
            padding: 20px; 
            margin: 20px 0; 
        }
        .process-step { 
            display: flex; 
            align-items: center; 
            gap: 15px; 
            margin-bottom: 15px; 
            padding: 15px; 
            background: white; 
            border-radius: 8px; 
            border-left: 4px solid #667eea; 
        }
        .step-number { 
            width: 30px; 
            height: 30px; 
            background: #667eea; 
            color: white; 
            border-radius: 50%; 
            display: flex; 
            align-items: center; 
            justify-content: center; 
            font-weight: bold; 
        }
        
        /* Upload de imágenes */
        .hermes-upload-zone { 
            border: 3px dashed #ddd; 
            border-radius: 10px; 
            padding: 40px; 
            text-align: center; 
            cursor: pointer; 
            transition: all 0.3s; 
            margin: 20px 0; 
        }
        .hermes-upload-zone:hover { 
            border-color: #667eea; 
            background: #f8f9fa; 
        }
        .hermes-images-preview { 
            display: grid; 
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr)); 
            gap: 15px; 
            margin-top: 20px; 
        }
        .image-preview-item { 
            position: relative; 
            border-radius: 8px; 
            overflow: hidden; 
        }
        .image-preview-item img { 
            width: 100%; 
            height: 120px; 
            object-fit: cover; 
        }
        
        /* Responsive */
        @media (max-width: 1200px) {
            .hermes-table { display: block; overflow-x: auto; }
        }
        @media (max-width: 768px) {
            .hermes-action-bar { flex-direction: column; align-items: stretch; }
            .hermes-search, .hermes-filter { min-width: 100%; }
            .hermes-tabs { flex-direction: column; }
            .hermes-stats { grid-template-columns: repeat(2, 1fr); }
        }
        @media (max-width: 480px) {
            .hermes-stats { grid-template-columns: 1fr; }
        }
        
        /* Estado de carga */
        .loading-state { 
            padding: 50px; 
            text-align: center; 
            color: #666; 
        }
        .loading-spinner { 
            font-size: 48px; 
            margin-bottom: 20px; 
            color: #667eea; 
        }
    </style>
</head>
<body>
    <!-- Navegación - VERSIÓN BÁSICA SIN ERROR -->
    <nav id="navegation">
        <a href="../index.php"><i class="fas fa-home" id="iconHome"></i></a>
        <span>
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO" width="200px">
        </span>
        <span class="welcome-admin">
            Bienvenido <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?> 
            (<?php 
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Vendedor'; 
                else echo 'Usuario';
            ?>)
        </span>
        <ul class="listMother">
            <li id="liSearch">
                <input type="text" name="search-profile" id="inputSearchProfile" placeholder="Buscar Producto...">
                <button id="btnSearch">Consultar</button>
            </li>
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <a href="user-dashboard-admin-index.php"><li>Usuarios</li></a>
                <a href="dashboard-index.php"><li>Clientes</li></a>
                <a href="seller-dashboard-admin-index.php"><li>Vendedores</li></a>
            </ul>
            <li id="liProducts" class="current-section">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li class="current-page">Productos</li>
                <a href="#" onclick="showTab('categorias')"><li>Categorias</li></a>
                <li>Variantes</li>
            </ul>
            <li id="liGets">Gestion de pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <li>Listado de ventas por vendedor</li>
                <li>Disputas</li>
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
                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
                        </svg>
                    </button>
                    <button class="dark-mode"><i class="fa-solid fa-moon"></i></button>
                </div>
            </span>
        </ul>
    </nav>
    
    <div class="hermes-container">
        <!-- Header -->
        <div class="hermes-header">
            <h1><i class="fas fa-box"></i> Gestión de Productos - HERMES Click&Go</h1>
            <p>Administra el catálogo completo de productos y publicaciones en bodega</p>
            <div class="hermes-badge">
                <i class="fas fa-user-shield"></i>
                <?php echo $_SESSION['admin_nombre'] ?? 'Admin'; ?>
                (<?php echo $_SESSION['admin_rol'] == 1 ? 'Administrador' : 'Vendedor'; ?>)
                <?php if ($_SESSION['admin_rol'] == 2): ?>
                | <i class="fas fa-store"></i> Vendedor ID: <?php echo $vendedor_id; ?>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Tabs unificados -->
        <div class="hermes-tabs">
            <button class="hermes-tab active" onclick="showTab('productos')">
                <i class="fas fa-box"></i> Productos
                <span id="productos-count" class="hermes-badge" style="background: rgba(255,255,255,0.3);"><?php echo $mis_productos; ?></span>
            </button>
            <button class="hermes-tab" onclick="showTab('categorias')">
                <i class="fas fa-tags"></i> Categorías
                <span id="categorias-count" class="hermes-badge" style="background: rgba(255,255,255,0.3);"><?php echo $estadisticas['total_categorias']; ?></span>
            </button>
        </div>
        
        <!-- CONTENIDO PRODUCTOS -->
        <div id="tab-productos-content">
            <!-- Estadísticas iniciales -->
            <div class="hermes-stats">
                <div class="hermes-stat-card">
                    <div class="hermes-stat-icon"><i class="fas fa-box"></i></div>
                    <div class="hermes-stat-number"><?php echo $estadisticas['total_productos']; ?></div>
                    <div class="hermes-stat-label">Productos Totales</div>
                </div>
                <div class="hermes-stat-card">
                    <div class="hermes-stat-icon"><i class="fas fa-shipping-fast"></i></div>
                    <div class="hermes-stat-number"><?php echo $estadisticas['productos_hermes']; ?></div>
                    <div class="hermes-stat-label">En HERMES</div>
                </div>
                <div class="hermes-stat-card">
                    <div class="hermes-stat-icon"><i class="fas fa-store"></i></div>
                    <div class="hermes-stat-number"><?php echo $estadisticas['vendedores_activos']; ?></div>
                    <div class="hermes-stat-label">Vendedores Activos</div>
                </div>
                <div class="hermes-stat-card">
                    <div class="hermes-stat-icon"><i class="fas fa-boxes"></i></div>
                    <div class="hermes-stat-number"><?php echo number_format($estadisticas['total_stock']); ?></div>
                    <div class="hermes-stat-label">Stock Total</div>
                </div>
                <div class="hermes-stat-card">
                    <div class="hermes-stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                    <div class="hermes-stat-number"><?php echo $estadisticas['sin_stock']; ?></div>
                    <div class="hermes-stat-label">Sin Stock</div>
                </div>
            </div>
            
            <!-- Barra de acciones -->
            <div class="hermes-action-bar">
                <input type="text" class="hermes-search" id="search-product" placeholder="🔍 Buscar producto por nombre, descripción...">
                
                <select class="hermes-filter" id="filter-categoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $query_categorias = "SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria";
                    $result_categorias = mysqli_query($connect, $query_categorias);
                    while ($cat = mysqli_fetch_assoc($result_categorias)) {
                        echo "<option value='{$cat['id_categoria']}'>{$cat['nombre_categoria']}</option>";
                    }
                    ?>
                </select>
                
                <?php if ($_SESSION['admin_rol'] == 1): ?>
                <select class="hermes-filter" id="filter-vendedor">
                    <option value="">Todos los vendedores</option>
                    <?php
                    $query_vendedores = "SELECT id_vendedor, nombre_empresa FROM vendedor ORDER BY nombre_empresa";
                    $result_vendedores = mysqli_query($connect, $query_vendedores);
                    while ($vend = mysqli_fetch_assoc($result_vendedores)) {
                        echo "<option value='{$vend['id_vendedor']}'>{$vend['nombre_empresa']}</option>";
                    }
                    ?>
                </select>
                <?php endif; ?>
                
                <select class="hermes-filter" id="filter-hermes">
                    <option value="">Estado HERMES</option>
                    <option value="1">En HERMES</option>
                    <option value="0">No en HERMES</option>
                </select>
                
                <select class="hermes-filter" id="filter-stock">
                    <option value="">Stock</option>
                    <option value="bajo">Stock bajo (< 10)</option>
                    <option value="agotado">Agotado</option>
                </select>
                
                <button class="hermes-btn hermes-btn-success" onclick="openProductModal()">
                    <i class="fas fa-plus"></i> Nuevo Producto HERMES
                </button>
            </div>
            
            <!-- Tabla de productos -->
            <div class="hermes-table-container">
                <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: #333;">Catálogo de Productos</h3>
                    <div>
                        <button class="hermes-btn" onclick="exportarProductos()" style="background: #6c757d;">
                            <i class="fas fa-file-export"></i> Exportar
                        </button>
                        <button class="hermes-btn" onclick="actualizarTabla()" style="background: #17a2b8;">
                            <i class="fas fa-sync-alt"></i> Actualizar
                        </button>
                    </div>
                </div>
                
                <div id="tabla-productos-container" class="loading-state">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <p>Cargando productos...</p>
                </div>
            </div>
        </div>
        
        <!-- CONTENIDO CATEGORÍAS -->
        <div id="tab-categorias-content" style="display: none;">
            <!-- Gestión de categorías -->
            <div class="hermes-table-container">
                <div style="padding: 20px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                    <h3 style="margin: 0; color: #333;">Gestión de Categorías</h3>
                    <button class="hermes-btn hermes-btn-success" onclick="openCategoryModal()">
                        <i class="fas fa-plus"></i> Nueva Categoría
                    </button>
                </div>
                
                <div id="tabla-categorias-container" class="loading-state">
                    <div class="loading-spinner">
                        <i class="fas fa-spinner fa-spin"></i>
                    </div>
                    <p>Cargando categorías...</p>
                </div>
            </div>
        </div>
    </div>
    
    <!-- MODAL PARA PRODUCTOS -->
    <div id="hermes-product-modal" class="hermes-modal-overlay">
        <div class="hermes-modal">
            <div style="padding: 25px; border-bottom: 1px solid #eee; display: flex; justify-content: space-between; align-items: center;">
                <h3 id="modal-product-title" style="margin: 0; color: #333;">Nuevo Producto HERMES</h3>
                <button onclick="closeProductModal()" style="background: none; border: none; font-size: 24px; cursor: pointer; color: #666;">&times;</button>
            </div>
            
            <div id="modal-product-content" style="padding: 25px;">
                <form id="product-form" onsubmit="guardarProducto(event)">
                    <input type="hidden" id="product_id" name="product_id" value="0">
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Nombre del Producto *</label>
                        <input type="text" id="product_name" name="nombre" required
                               style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                               placeholder="Ej: Camiseta de algodón premium">
                    </div>
                    
                    <div style="margin-bottom: 20px;">
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Descripción *</label>
                        <textarea id="product_description" name="descripcion" required rows="4"
                                  style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                                  placeholder="Describa el producto..."></textarea>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-bottom: 20px;">
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Precio *</label>
                            <input type="number" id="product_price" name="precio" required step="0.01" min="0"
                                   style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                                   placeholder="0.00">
                        </div>
                        <div>
                            <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">Stock *</label>
                            <input type="number" id="product_stock" name="stock" required min="0"
                                   style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px; font-size: 14px;"
                                   placeholder="0">
                        </div>
                    </div>
                    
                    <!-- Proceso HERMES Click&Go -->
                    <div class="hermes-process">
                        <h4 style="margin: 0 0 15px 0; color: #667eea;">
                            <i class="fas fa-shipping-fast"></i> Proceso de Publicación HERMES Click&Go
                        </h4>
                        
                        <div class="process-step">
                            <div class="step-number">1</div>
                            <div>
                                <strong>Validación de Información</strong>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                                    Revisamos que todos los datos sean correctos y cumplan con nuestras políticas.
                                </p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">2</div>
                            <div>
                                <strong>Asignación al Perfil del Vendedor</strong>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                                    El producto será vinculado automáticamente a tu perfil de vendedor.
                                </p>
                            </div>
                        </div>
                        
                        <div class="process-step">
                            <div class="step-number">3</div>
                            <div>
                                <strong>Publicación en Bodega HERMES</strong>
                                <p style="margin: 5px 0 0 0; color: #666; font-size: 0.9em;">
                                    Disponible para Click&Go en 24-48 horas hábiles.
                                </p>
                            </div>
                        </div>
                        
                        <div style="margin-top: 20px; padding: 15px; background: #e8f4fc; border-radius: 8px;">
                            <label style="display: flex; align-items: center; gap: 10px; font-weight: 600; cursor: pointer;">
                                <input type="checkbox" id="publicar_hermes" name="publicar_hermes" value="1" checked>
                                <span>Publicar este producto en HERMES Click&Go Bodega</span>
                            </label>
                            <p style="margin: 10px 