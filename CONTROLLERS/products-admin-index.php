<?php
session_start();
include('../shortCuts/connect.php');

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
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HERMES - Gestión de Productos</title>
    <link rel="stylesheet" href="../css/hermes-admin.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css"> <!-- aqui guardaremos el link del as Imagenes-->
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <style>
        /* Estilos específicos para el CRUD */
        .hermes-container {
            padding: 20px;
            background: #f8f9fa;
            min-height: 100vh;
        }
        
        .hermes-header {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        
        .hermes-tabs {
            display: flex;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 20px;
        }
        
        .hermes-tab {
            padding: 12px 24px;
            background: none;
            border: none;
            font-size: 16px;
            cursor: pointer;
            color: #666;
            position: relative;
        }
        
        .hermes-tab.active {
            color: #2c3e50;
            font-weight: 600;
        }
        
        .hermes-tab.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            right: 0;
            height: 3px;
            background: #3498db;
        }
        
        .action-bar {
            background: white;
            padding: 15px;
            border-radius: 8px;
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            align-items: center;
            margin-bottom: 20px;
            box-shadow: 0 1px 5px rgba(0,0,0,0.05);
        }
        
        .hermes-search {
            flex: 1;
            max-width: 300px;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
        
        .hermes-select {
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 6px;
            background: white;
        }
        
        .hermes-btn {
            padding: 8px 16px;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .hermes-btn-primary {
            background: #3498db;
            color: white;
        }
        
        .hermes-btn-primary:hover {
            background: #2980b9;
        }
        
        .hermes-table-container {
            background: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
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
            color: #2c3e50;
            border-bottom: 2px solid #e0e0e0;
        }
        
        .hermes-table td {
            padding: 15px;
            border-bottom: 1px solid #eee;
            vertical-align: middle;
        }
        
        .hermes-table tr:hover {
            background: #f9f9f9;
        }
        
        .product-img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 6px;
            border: 1px solid #eee;
        }
        
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status-pendiente {
            background: #fff3cd;
            color: #856404;
        }
        
        .status-activo {
            background: #d4edda;
            color: #155724;
        }
        
        .status-inactivo {
            background: #f8d7da;
            color: #721c24;
        }
        
        .actions-cell {
            display: flex;
            gap: 8px;
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
        
        .edit-btn {
            background: #e3f2fd;
            color: #1976d2;
        }
        
        .edit-btn:hover {
            background: #bbdefb;
        }
        
        .delete-btn {
            background: #ffebee;
            color: #d32f2f;
        }
        
        .delete-btn:hover {
            background: #ffcdd2;
        }
        
        /* Modal */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            border-radius: 12px;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .action-bar {
                flex-direction: column;
                align-items: stretch;
            }
            
            .hermes-search, .hermes-select {
                max-width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="hermes-container">
        <!-- Header -->
        <div class="hermes-header">
            <h1><i class="fas fa-box"></i> Gestión de Productos - HERMES Click&Go</h1>
            <p>Administra el catálogo de productos y categorías</p>
        </div>
        
        <!-- Tabs -->
        <div class="hermes-tabs">
            <button class="hermes-tab active" onclick="showTab('productos')">
                <i class="fas fa-box"></i> Productos
            </button>
            <button class="hermes-tab" onclick="showTab('categorias')">
                <i class="fas fa-tags"></i> Categorías
            </button>
        </div>
        
        <!-- Contenido de Productos -->
        <div id="tab-productos" class="tab-content active">
            <!-- Barra de Acciones -->
            <div class="action-bar">
                <input type="text" class="hermes-search" placeholder="🔍 Buscar producto..." id="search-input">
                
                <select class="hermes-select" id="filter-vendedor">
                    <option value="">Todos los vendedores</option>
                    <?php
                    $queryVendedores = "SELECT id_vendedor, nombre_empresa FROM vendedor WHERE estado = 'activo'";
                    if ($admin_rol == 2) {
                        $queryVendedores .= " AND id_vendedor = ?";
                        $stmt = $pdo->prepare($queryVendedores);
                        $stmt->execute([$vendedor_id]);
                    } else {
                        $stmt = $pdo->query($queryVendedores);
                    }
                    while ($vendedor = $stmt->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$vendedor['id_vendedor']}'>{$vendedor['nombre_empresa']}</option>";
                    }
                    ?>
                </select>
                
                <select class="hermes-select" id="filter-categoria">
                    <option value="">Todas las categorías</option>
                    <?php
                    $categorias = $pdo->query("SELECT id_categoria, nombre_categoria FROM categoria ORDER BY nombre_categoria");
                    while ($cat = $categorias->fetch(PDO::FETCH_ASSOC)) {
                        echo "<option value='{$cat['id_categoria']}'>{$cat['nombre_categoria']}</option>";
                    }
                    ?>
                </select>
                
                <button class="hermes-btn hermes-btn-primary" onclick="openProductModal()">
                    <i class="fas fa-plus"></i> Nuevo Producto HERMES
                </button>
            </div>
            
            <!-- Tabla de Productos -->
            <div class="hermes-table-container">
                <table class="hermes-table">
                    <thead>
                        <tr>
                            <th width="50"><input type="checkbox" id="select-all"></th>
                            <th width="80">ID</th>
                            <th width="80">Imagen</th>
                            <th>Producto</th>
                            <th width="100">Precio</th>
                            <th width="100">Stock</th>
                            <th>Categorías</th>
                            <th width="100">HERMES</th>
                            <th width="150">Vendedor</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="productos-table-body">
                        <?php include('ajax/productos_listar.php'); ?>
                    </tbody>
                </table>
            </div>
            
            <!-- Paginación -->
            <div id="pagination" class="pagination" style="margin-top: 20px; text-align: center;">
                <!-- La paginación se carga via AJAX -->
            </div>
        </div>
        
        <!-- Contenido de Categorías -->
        <div id="tab-categorias" class="tab-content" style="display: none;">
            <div class="action-bar">
                <input type="text" class="hermes-search" placeholder="🔍 Buscar categoría..." id="search-categoria">
                <button class="hermes-btn hermes-btn-primary" onclick="openCategoryModal()">
                    <i class="fas fa-plus"></i> Nueva Categoría
                </button>
            </div>
            
            <div class="hermes-table-container">
                <table class="hermes-table">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>Nombre Categoría</th>
                            <th width="100">Productos</th>
                            <th width="120">Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="categorias-table-body">
                        <?php include('ajax/categorias_listar.php'); ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal para Productos -->
    <div id="product-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3 id="modal-title">Nuevo Producto HERMES</h3>
                <button onclick="closeModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <!-- El contenido del modal se carga via AJAX -->
                <div id="modal-content"></div>
            </div>
        </div>
    </div>
    
    <!-- Modal para Categorías -->
    <div id="category-modal" class="modal-overlay">
        <div class="modal-content">
            <div class="modal-header" style="padding: 20px; border-bottom: 1px solid #eee;">
                <h3>Gestión de Categorías</h3>
                <button onclick="closeCategoryModal()" style="background: none; border: none; font-size: 24px; cursor: pointer;">×</button>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <form id="category-form" onsubmit="saveCategory(event)">
                    <input type="hidden" id="category_id" name="category_id">
                    <div class="form-group" style="margin-bottom: 15px;">
                        <label style="display: block; margin-bottom: 5px; font-weight: 500;">Nombre de Categoría *</label>
                        <input type="text" id="category_name" name="category_name" required 
                               style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 6px;">
                    </div>
                    <div style="text-align: right;">
                        <button type="button" class="hermes-btn" onclick="closeCategoryModal()" 
                                style="margin-right: 10px;">Cancelar</button>
                        <button type="submit" class="hermes-btn hermes-btn-primary">Guardar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="../js/hermes-crud.js"></script>
    <script>
        // Funciones básicas del CRUD
        function showTab(tabName) {
            // Ocultar todas las pestañas
            document.querySelectorAll('.tab-content').forEach(tab => {
                tab.style.display = 'none';
            });
            
            // Desactivar todos los tabs
            document.querySelectorAll('.hermes-tab').forEach(tabBtn => {
                tabBtn.classList.remove('active');
            });
            
            // Mostrar tab seleccionado
            document.getElementById('tab-' + tabName).style.display = 'block';
            
            // Activar botón del tab
            event.target.classList.add('active');
        }
        
        function openProductModal(productId = 0) {
            const modal = document.getElementById('product-modal');
            const modalContent = document.getElementById('modal-content');
            const modalTitle = document.getElementById('modal-title');
            
            if (productId === 0) {
                modalTitle.textContent = 'Nuevo Producto HERMES';
                // Cargar formulario de creación via AJAX
                fetch('ajax/producto_form.php?action=new')
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                        modal.style.display = 'flex';
                    });
            } else {
                modalTitle.textContent = 'Editar Producto';
                // Cargar formulario de edición via AJAX
                fetch(`ajax/producto_form.php?action=edit&id=${productId}`)
                    .then(response => response.text())
                    .then(html => {
                        modalContent.innerHTML = html;
                        modal.style.display = 'flex';
                    });
            }
        }
        
        function closeModal() {
            document.getElementById('product-modal').style.display = 'none';
        }
        
        function openCategoryModal(categoryId = 0) {
            const modal = document.getElementById('category-modal');
            const form = document.getElementById('category-form');
            
            if (categoryId === 0) {
                // Modo creación
                form.reset();
                document.getElementById('category_id').value = '';
                document.getElementById('category_name').value = '';
            } else {
                // Modo edición - cargar datos via AJAX
                fetch(`ajax/categoria_get.php?id=${categoryId}`)
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('category_id').value = data.id_categoria;
                        document.getElementById('category_name').value = data.nombre_categoria;
                    });
            }
            
            modal.style.display = 'flex';
        }
        
        function closeCategoryModal() {
            document.getElementById('category-modal').style.display = 'none';
        }
        
        // Buscar productos en tiempo real
        document.getElementById('search-input').addEventListener('input', function() {
            buscarProductos();
        });
        
        function buscarProductos() {
            const search = document.getElementById('search-input').value;
            const vendedor = document.getElementById('filter-vendedor').value;
            const categoria = document.getElementById('filter-categoria').value;
            
            fetch(`ajax/productos_listar.php?search=${search}&vendedor=${vendedor}&categoria=${categoria}`)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('productos-table-body').innerHTML = html;
                });
        }
        
        // Cerrar modal con ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeCategoryModal();
            }
        });
    </script>
</body>
</html>