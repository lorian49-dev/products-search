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
// =========================
// BUSCADOR DE VENDEDORES
// =========================
$busqueda = $_GET['busqueda'] ?? '';
$where = '';

if (!empty($busqueda)) {
    $busqueda = mysqli_real_escape_string($connect, $busqueda);
    $where = "WHERE 
        u.nombre LIKE '%$busqueda%' OR 
        u.apellido LIKE '%$busqueda%' OR
        u.correo LIKE '%$busqueda%' OR
        v.nombre_empresa LIKE '%$busqueda%' OR
        v.nit LIKE '%$busqueda%'";
}


// Consulta de vendedores con información de usuario
$query = "SELECT v.*, u.nombre, u.apellido, u.correo, u.telefono
          FROM vendedor v
          INNER JOIN usuario u ON v.id_vendedor = u.id_usuario
          $where
          ORDER BY v.id_vendedor DESC";


$result = mysqli_query($connect, $query);
if (!$result) {
    die("Error en consulta: " . mysqli_error($connect));
}

$total_vendedores = mysqli_num_rows($result);

// Obtener estadísticas generales - ¡CORREGIDO!
$query_estadisticas = "SELECT 
    (SELECT COUNT(*) FROM vendedor) as total_vendedores,
    (SELECT COUNT(*) FROM producto) as total_productos,
    (SELECT COUNT(*) FROM catalogo) as total_catalogos,
    (SELECT COUNT(*) FROM vendedor WHERE acepto_terminos = 1) as aceptaron_terminos";
    
$result_estadisticas = mysqli_query($connect, $query_estadisticas);
$estadisticas = mysqli_fetch_assoc($result_estadisticas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Vendedores - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <style>
        .dashboard-container { max-width: 1400px; margin: 0 auto; }
        
        /* Header */
        .header { background: rgba(255, 255, 255, 0.95); padding: 25px 30px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .header h1 { color: #333; font-size: 1.8em; margin-bottom: 5px; }
        .user-role { background: #667eea; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        
        /* Botones */
        .btn { padding: 10px 20px; border: none; border-radius: 25px; font-size: 0.95em; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-primary { background: #667eea; color: white; } .btn-primary:hover { background: #5a6fd8; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; } .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; } .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; } .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-warning { background: #ffc107; color: #212529; } .btn-warning:hover { background: #e0a800; transform: translateY(-2px); }
        .btn-info { background: #17a2b8; color: white; } .btn-info:hover { background: #138496; transform: translateY(-2px); }
        
        /* Estadísticas */
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-card { background: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 12px; text-align: center; box-shadow: 0 3px 10px rgba(0,0,0,0.1); }
        .stat-icon { font-size: 1.8em; color: #667eea; margin-bottom: 10px; }
        .stat-number { font-size: 2em; font-weight: bold; color: #333; margin: 5px 0; }
        .stat-label { color: #666; font-size: 0.9em; }
        
        /* Tabla */
        .table-container { background: rgba(255, 255, 255, 0.95); border-radius: 15px; overflow: hidden; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .table-header { padding: 20px; border-bottom: 2px solid #eee; display: flex; justify-content: space-between; align-items: center; }
        .table-header h3 { color: #333; font-size: 1.2em; }
        .data-table { width: 100%; border-collapse: collapse; }
        .data-table th { background: #667eea; color: white; padding: 12px 15px; text-align: left; font-weight: 600; font-size: 0.9em; }
        .data-table td { padding: 12px 15px; border-bottom: 1px solid #eee; font-size: 0.9em; }
        .data-table tr:hover { background: #f8f9fa; }
        
        /* Badges */
        .badge { padding: 4px 10px; border-radius: 12px; font-size: 0.8em; font-weight: bold; }
        .badge-success { background: #d4edda; color: #155724; }
        .badge-warning { background: #fff3cd; color: #856404; }
        .badge-danger { background: #f8d7da; color: #721c24; }
        .badge-info { background: #d1ecf1; color: #0c5460; }
        
        /* Acciones */
        .acciones { display: flex; gap: 5px; flex-wrap: wrap; }
        .btn-action { padding: 6px 10px; border-radius: 6px; border: none; cursor: pointer; transition: all 0.3s ease; text-decoration: none; display: inline-flex; align-items: center; justify-content: center; min-width: 30px; }
        .btn-view { background: #17a2b8; color: white; } .btn-view:hover { background: #138496; transform: translateY(-2px); }
        .btn-edit { background: #28a745; color: white; } .btn-edit:hover { background: #218838; transform: translateY(-2px); }
        .btn-delete { background: #dc3545; color: white; } .btn-delete:hover { background: #c82333; transform: translateY(-2px); }
        
        /* Búsqueda */
        .search-container { background: rgba(255, 255, 255, 0.95); padding: 20px; border-radius: 15px; margin-bottom: 20px; display: flex; gap: 15px; align-items: center; }
        .search-box { flex: 1; position: relative; }
        .search-box input { width: 100%; padding: 12px 15px 12px 40px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em; }
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .header-top { flex-direction: column; gap: 15px; text-align: center; }
            .stats { grid-template-columns: 1fr; }
            .search-container { flex-direction: column; }
            .data-table { display: block; overflow-x: auto; }
            .acciones { flex-direction: column; gap: 3px; }
        }
        
        /* Advertencia */
        .advertencia { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #dc3545; }
        .advertencia h4 { margin-bottom: 10px; }
    </style>
</head>
<body>
    <nav id="navegation">
        <a href="#"><i class="fas fa-home" id="iconHome"></i></a>
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
            <li id="liSearch"><input type="text" name="search-profile" id="inputSearchProfile" placeholder="Buscar Usuario por Correo...">
                <button id="btnSearch">Consultar</button>
            </li>
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <a href="user-dashboard-admin-index.php"><li>Usuarios</li></a>
               <a href="client-dashboard-index.php"><li>Clientes</li></a>
                <li class="current-page">Vendedores</li>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li>Productos</li>
                <li>Categorias</li>
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
    </nav>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1>Gestión de Vendedores</h1>
                    <div class="user-role">
                        <?php 
                            if ($_SESSION['admin_rol'] == 1) echo 'Administrador General';
                            elseif ($_SESSION['admin_rol'] == 2) echo 'Administrador Colaborador'; 
                            else echo 'Administrador';
                        ?>
                    </div>
                </div>
                <div>
                    <a href="../index.php" class="btn btn-secondary"> <!--Ruta inexistente, debe contener otro nombre-->
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <a href="seller-dashboard-admin-watch.php?action=estadisticas" class="btn btn-primary">
                        <i class="fas fa-chart-pie"></i> Estadísticas
                    </a>
                </div>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Administra los vendedores registrados en la plataforma. 
                <span class="badge badge-success">
                    <i class="fas fa-link"></i> Relación: vendedor.id_vendedor = usuario.id_usuario
                </span>
            </p>
        </div>

        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-store"></i></div>
                <div class="stat-number"><?php echo $estadisticas['total_vendedores']; ?></div>
                <div class="stat-label">Vendedores Totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-number"><?php echo $estadisticas['total_productos']; ?></div>
                <div class="stat-label">Productos Totales</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-book"></i></div>
                <div class="stat-number"><?php echo $estadisticas['total_catalogos']; ?></div>
                <div class="stat-label">Catálogos</div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-file-contract"></i></div>
                <div class="stat-number"><?php echo $estadisticas['aceptaron_terminos']; ?></div>
                <div class="stat-label">Aceptaron Términos</div>
            </div>
        </div>

        <!-- Búsqueda -->
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="busqueda" placeholder="Buscar vendedor por nombre, empresa o NIT...">
            </div>
            <button class="btn btn-primary" onclick="buscarVendedores()">
                <i class="fas fa-search"></i> Buscar
            </button>
            <a href="seller-dashboard-admin-index.php" class="btn btn-secondary">Mostrar Todos</a>
        </div>

        <!-- Tabla de Vendedores -->
        <div class="table-container">
            <div class="table-header">
                <h3>Lista de Vendedores Registrados</h3>
                <span style="color: #666; font-size: 0.9em;">
                    Total: <?php echo $total_vendedores; ?> vendedor(es)
                </span>
            </div>
            
            <?php if ($total_vendedores > 0): ?>
            <div style="overflow-x: auto;">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Vendedor / Contacto</th>
                            <th>Empresa</th>
                            <th>NIT</th>
                            <th>Ubicación</th>
                            <th>Términos</th>
                            <th>Productos</th>
                            <th>Catálogos</th>
                            <th>Registro</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while($vendedor = mysqli_fetch_assoc($result)): 
                            $id_vendedor = $vendedor['id_vendedor'];
                            
                            // Obtener estadísticas de productos
                            $query_productos = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $id_vendedor";
                            $result_productos = mysqli_query($connect, $query_productos);
                            $productos = mysqli_fetch_assoc($result_productos);
                            
                            // Obtener estadísticas de catálogos
                            $query_catalogos = "SELECT COUNT(*) as total FROM catalogo WHERE id_vendedor = $id_vendedor";
                            $result_catalogos = mysqli_query($connect, $query_catalogos);
                            $catalogos = mysqli_fetch_assoc($result_catalogos);
                        ?>
                        <tr>
                            <td><strong>#<?php echo $id_vendedor; ?></strong></td>
                            <td>
                                <strong><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']); ?></strong>
                                <br>
                                <small style="color: #666;">
                                    <?php if(!empty($vendedor['correo'])): ?>
                                        <i class="fas fa-envelope"></i> <?php echo htmlspecialchars($vendedor['correo']); ?>
                                    <?php endif; ?>
                                    <?php if(!empty($vendedor['telefono_contacto'])): ?>
                                        <br><i class="fas fa-phone"></i> <?php echo htmlspecialchars($vendedor['telefono_contacto']); ?>
                                    <?php endif; ?>
                                </small>
                            </td>
                            <td>
                                <strong><?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></strong>
                                <?php if(!empty($vendedor['correo_contacto'])): ?>
                                    <br><small><i class="fas fa-at"></i> <?php echo htmlspecialchars($vendedor['correo_contacto']); ?></small>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($vendedor['nit'])): ?>
                                    <code><?php echo htmlspecialchars($vendedor['nit']); ?></code>
                                <?php else: ?>
                                    <span style="color: #999;">No especificado</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($vendedor['ubicacion'])): ?>
                                    <span title="<?php echo htmlspecialchars($vendedor['ubicacion']); ?>">
                                        <?php echo strlen($vendedor['ubicacion']) > 30 ? 
                                            substr(htmlspecialchars($vendedor['ubicacion']), 0, 30) . '...' : 
                                            htmlspecialchars($vendedor['ubicacion']); ?>
                                    </span>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($vendedor['acepto_terminos'] == 1): ?>
                                    <span class="badge badge-success" title="Aceptó términos y condiciones">
                                        <i class="fas fa-check"></i> Sí
                                    </span>
                                <?php else: ?>
                                    <span class="badge badge-danger" title="No aceptó términos y condiciones">
                                        <i class="fas fa-times"></i> No
                                    </span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($productos['total'] > 0): ?>
                                    <span class="badge badge-success"><?php echo $productos['total']; ?></span>
                                <?php else: ?>
                                    <span class="badge">0</span>
                                <?php endif; ?>
                            </td>
                            <td style="text-align: center;">
                                <?php if($catalogos['total'] > 0): ?>
                                    <span class="badge badge-success"><?php echo $catalogos['total']; ?></span>
                                <?php else: ?>
                                    <span class="badge">0</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if(!empty($vendedor['fecha_registro']) && $vendedor['fecha_registro'] != '0000-00-00 00:00:00'): ?>
                                    <?php echo date('d/m/Y', strtotime($vendedor['fecha_registro'])); ?>
                                <?php else: ?>
                                    <span style="color: #999;">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="acciones">
                                    <a href="seller-dashboard-admin-watch.php?id=<?php echo $id_vendedor; ?>" 
                                       class="btn-action btn-view" title="Ver Detalles">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="seller-dashboard-admin-edit.php?id=<?php echo $id_vendedor; ?>" 
                                       class="btn-action btn-edit" title="Editar">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <?php if($_SESSION['admin_rol'] == 1): ?>
                                    <a href="seller-dashboard-admin-delete.php?id=<?php echo $id_vendedor; ?>" 
                                       class="btn-action btn-delete" 
                                       title="Eliminar Vendedor">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
            <?php else: ?>
            <div style="padding: 50px 20px; text-align: center; color: #666;">
                <i class="fas fa-store-slash" style="font-size: 3em; margin-bottom: 20px; color: #ddd;"></i>
                <h3>No hay vendedores registrados</h3>
                <p style="margin-top: 10px;">No se encontraron vendedores en el sistema.</p>
                <a href="client-dashboard-index.php" class="btn btn-primary" style="margin-top: 20px;">
                    <i class="fas fa-users"></i> Ver Clientes
                </a>
            </div>
            <?php endif; ?>
        </div>

        <!-- Advertencia de Eliminación -->
        <?php if($_SESSION['admin_rol'] == 1): ?>
        <div class="advertencia">
            <h4><i class="fas fa-exclamation-triangle"></i> ADVERTENCIA CRÍTICA - ELIMINACIÓN EN CASCADA</h4>
            <p><strong>La eliminación de un vendedor eliminará PERMANENTEMENTE:</strong></p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>TODOS los productos</strong> creados por este vendedor</li>
                <li><strong>TODOS los catálogos</strong> asociados al vendedor</li>
                <li><strong>Relaciones catalogo_producto</strong> correspondientes</li>
                <li><strong>Información del vendedor</strong> en la tabla vendedor</li>
                <li><strong>Posiblemente el usuario asociado</strong> (se pedirá confirmación)</li>
            </ul>
            <p style="margin-top: 10px;"><strong>Recomendación:</strong> Considere desactivar la cuenta en lugar de eliminar.</p>
        </div>
        <?php endif; ?>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="../index.php" class="btn btn-primary"> <!--Ruta inexistente, cambiar el nombre de la ruta-->
                <i class="fas fa-home"></i> Volver al Dashboard
            </a>
            <a href="client-dashboard-index.php" class="btn btn-success">
                <i class="fas fa-users"></i> Gestionar Clientes
            </a>
            <?php if($_SESSION['admin_rol'] == 1): ?>
            <a href="../usuarios/index.php" class="btn btn-info"> <!--Ruta inexistente, utilizar otros nombres-->
                <i class="fas fa-users-cog"></i> Gestionar Usuarios
            </a>
            <?php endif; ?>
        </div>
    </div>
    <script src="../scripts/admin.js"></script>
    <script>
        function buscarVendedores() {
            const busqueda = document.getElementById('busqueda').value.trim();
            if (busqueda) {
                window.location.href = 'seller-dashboard-admin-index.php?busqueda=' + encodeURIComponent(busqueda);
            }
        }
        
        // Búsqueda con Enter
        document.getElementById('busqueda').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarVendedores();
            }
        });

        // Confirmación para eliminación
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const empresa = this.closest('tr').querySelector('td:nth-child(3) strong').textContent;
                    const productos = this.closest('tr').querySelector('td:nth-child(7) span').textContent;
                    const catalogos = this.closest('tr').querySelector('td:nth-child(8) span').textContent;
                    
                    const mensaje = `⚠️ ADVERTENCIA DE ELIMINACIÓN EN CASCADA ⚠️\n\n¿Está seguro de eliminar permanentemente a:\n\n• Empresa: ${empresa}\n• Productos: ${productos} productos\n• Catálogos: ${catalogos} catálogos\n\nSe eliminarán TODOS los productos y catálogos asociados.\n\n¿Continuar con la eliminación PERMANENTE?`;
                    
                    if (!confirm(mensaje)) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>
</html>
