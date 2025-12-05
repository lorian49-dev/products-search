<?php
// ==================== PROTECCIÓN DE ACCESO ====================
session_start();
include('../shortCuts/connect.php');

// Verificar si está logueado como ADMIN
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';
    </script>";
    exit();
}

// Verificar rol de administrador (1 = administrador, 2 = admin_colaborador)
$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}
//  FIN PROTECCIÓN DE ACCESO 

// DETERMINAR QUÉ SUBAPARTADO MOSTRAR
$subapartado = isset($_GET['sub']) ? $_GET['sub'] : 'ventas';
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Pedidos - Administrador</title>
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        /* ESTILOS ADICIONALES PARA LOS MÓDULOS */
        .subapartado-content {
            display: none;
        }
        
        .subapartado-content.active {
            display: block;
        }
        
        .card-info {
            background: white;
            border-radius: 8px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 2px 5px rgba(0,0,0,0.05);
            border-top: 4px solid #3498db;
        }
        
        .stat-card h3 {
            margin: 0;
            color: #2c3e50;
            font-size: 24px;
        }
        
        .stat-card p {
            margin: 5px 0 0 0;
            color: #7f8c8d;
            font-size: 14px;
        }
        
        /* TABLAS MEJORADAS */
        .tabla-scroll {
            overflow-x: auto;
            margin-top: 20px;
        }
        
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        
        table.data-table th {
            background: #34495e;
            color: white;
            padding: 15px;
            text-align: left;
            font-weight: 600;
        }
        
        table.data-table td {
            padding: 12px 15px;
            border-bottom: 1px solid #eee;
        }
        
        table.data-table tr:hover {
            background: #f9f9f9;
        }
        
        /* BADGES DE ESTADO */
        .badge {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            display: inline-block;
        }
        
        .badge-success {
            background: #2ecc71;
            color: white;
        }
        
        .badge-warning {
            background: #f39c12;
            color: white;
        }
        
        .badge-danger {
            background: #e74c3c;
            color: white;
        }
        
        .badge-info {
            background: #3498db;
            color: white;
        }
        
        /* FORMULARIOS */
        .form-inline {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
            align-items: flex-end;
        }
        
        .form-group {
            flex: 1;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        /* BOTONES */
        .btn-action {
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            transition: 0.3s;
        }
        
        .btn-primary {
            background: #3498db;
            color: white;
        }
        
        .btn-primary:hover {
            background: #2980b9;
        }
        
        .btn-success {
            background: #2ecc71;
            color: white;
        }
        
        .btn-success:hover {
            background: #27ae60;
        }
        
        .btn-danger {
            background: #e74c3c;
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        /* CHAT PARA DISPUTAS */
        .chat-container {
            max-height: 500px;
            overflow-y: auto;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 15px;
            background: #f9f9f9;
        }
        
        .message {
            margin-bottom: 15px;
            padding: 10px 15px;
            border-radius: 8px;
            max-width: 80%;
        }
        
        .message.admin {
            background: #d4edda;
            border: 1px solid #c3e6cb;
            margin-left: auto;
        }
        
        .message.client {
            background: #d1ecf1;
            border: 1px solid #bee5eb;
        }
        
        .message.vendor {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
        }
        
        /* FILTROS */
        .filter-section {
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        
        .filter-row {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            align-items: flex-end;
        }
        
        /* MODAL */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }
        
        .modal-content {
            background: white;
            width: 90%;
            max-width: 800px;
            max-height: 90vh;
            border-radius: 10px;
            overflow-y: auto;
        }
        
        .modal-header {
            padding: 20px;
            border-bottom: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .modal-body {
            padding: 20px;
        }
        
        .close-modal {
            cursor: pointer;
            font-size: 24px;
            color: #7f8c8d;
        }
        
        /* PESTAÑAS */
        .tab-nav {
            display: flex;
            border-bottom: 2px solid #eee;
            margin-bottom: 20px;
        }
        
        .tab-btn {
            padding: 10px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 16px;
            color: #7f8c8d;
            position: relative;
        }
        
        .tab-btn.active {
            color: #3498db;
            font-weight: 600;
        }
        
        .tab-btn.active::after {
            content: '';
            position: absolute;
            bottom: -2px;
            left: 0;
            width: 100%;
            height: 2px;
            background: #3498db;
        }
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
                <li><a href="user-dashboard-admin-index.php">Usuarios</a></li>
                <li><a href="dashboard-index.php">Clientes</a></li>
                <li><a href="seller-dashboard-admin-index.php">Vendedores</a></li>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li><a href="products-dashboard-admin-index.php">Productos</a></li>
                <li><a href="categorias-dashboard-admin-index.php">Categorias</a></li>
            </ul>
            <li id="liGets" class="active">Gestión de Pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <li><a href="?sub=ventas" class="<?php echo $subapartado == 'ventas' ? 'active' : ''; ?>">Listado de ventas por vendedor</a></li>
                <li><a href="?sub=disputas" class="<?php echo $subapartado == 'disputas' ? 'active' : ''; ?>">Disputas</a></li>
                <li><a href="?sub=estados" class="<?php echo $subapartado == 'estados' ? 'active' : ''; ?>">Actualizar estados de pedidos</a></li>
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

    <div id="container">
        <!-- PESTAÑAS PARA LOS SUBAPARTADOS -->
        <div class="tab-nav">
            <button class="tab-btn <?php echo $subapartado == 'ventas' ? 'active' : ''; ?>" onclick="cambiarSubapartado('ventas')">
                📊 Ventas por Vendedor
            </button>
            <button class="tab-btn <?php echo $subapartado == 'disputas' ? 'active' : ''; ?>" onclick="cambiarSubapartado('disputas')">
                ⚖️ Disputas
            </button>
            <button class="tab-btn <?php echo $subapartado == 'estados' ? 'active' : ''; ?>" onclick="cambiarSubapartado('estados')">
                📦 Estados de Pedidos
            </button>
        </div>

        <!-- ==================== LISTADO DE VENTAS POR VENDEDOR ==================== -->
        <div id="ventas" class="subapartado-content <?php echo $subapartado == 'ventas' ? 'active' : ''; ?>">
            <h2>📊 Listado de Ventas por Vendedor</h2>
            
            <?php
            // CONSULTA PARA ESTADÍSTICAS GENERALES
            $queryStats = "
                SELECT 
                    COUNT(DISTINCT v.id_vendedor) as total_vendedores,
                    COUNT(DISTINCT p.id_pedido) as total_pedidos,
                    SUM(dp.precio_total) as ventas_totales,
                    SUM(dp.cantidad) as productos_vendidos,
                    AVG(dp.precio_total) as promedio_venta
                FROM vendedor v
                LEFT JOIN producto prod ON v.id_vendedor = prod.id_vendedor
                LEFT JOIN detalle_pedido dp ON prod.id_producto = dp.id_producto
                LEFT JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                WHERE ped.estado IN ('completado', 'entregado', 'pagado') 
                   OR ped.estado IS NULL
            ";
            
            $resultStats = mysqli_query($connect, $queryStats);
            $stats = mysqli_fetch_assoc($resultStats);
            ?>
            
            <!-- ESTADÍSTICAS -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3><?php echo $stats['total_vendedores'] ?? 0; ?></h3>
                    <p>Vendedores Activos</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['total_pedidos'] ?? 0; ?></h3>
                    <p>Pedidos Completados</p>
                </div>
                <div class="stat-card">
                    <h3>$<?php echo number_format($stats['ventas_totales'] ?? 0, 2); ?></h3>
                    <p>Ventas Totales</p>
                </div>
                <div class="stat-card">
                    <h3><?php echo $stats['productos_vendidos'] ?? 0; ?></h3>
                    <p>Productos Vendidos</p>
                </div>
            </div>
            
            <!-- FILTROS -->
            <div class="filter-section">
                <form method="GET" action="">
                    <input type="hidden" name="sub" value="ventas">
                    <div class="filter-row">
                        <div class="form-group">
                            <label>Vendedor:</label>
                            <select name="vendedor" class="form-control" onchange="this.form.submit()">
                                <option value="">Todos los vendedores</option>
                                <?php
                                $queryVendedores = "SELECT id_vendedor, nombre_empresa FROM vendedor ORDER BY nombre_empresa";
                                $vendedores = mysqli_query($connect, $queryVendedores);
                                $selectedVendedor = $_GET['vendedor'] ?? '';
                                while($v = mysqli_fetch_assoc($vendedores)) {
                                    $selected = $selectedVendedor == $v['id_vendedor'] ? 'selected' : '';
                                    echo "<option value='{$v['id_vendedor']}' $selected>{$v['nombre_empresa']}</option>";
                                }
                                ?>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha desde:</label>
                            <input type="date" name="fecha_desde" class="form-control" 
                                   value="<?php echo $_GET['fecha_desde'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <label>Fecha hasta:</label>
                            <input type="date" name="fecha_hasta" class="form-control"
                                   value="<?php echo $_GET['fecha_hasta'] ?? ''; ?>">
                        </div>
                        
                        <div class="form-group">
                            <button type="submit" class="btn-action btn-primary">Filtrar</button>
                            <a href="?sub=ventas" class="btn-action btn-danger">Limpiar</a>
                        </div>
                    </div>
                </form>
            </div>
            
            <!-- TABLA DE VENTAS -->
            <?php
            // CONSTRUIR CONSULTA CON FILTROS
            $whereConditions = ["ped.estado IN ('completado', 'entregado', 'pagado')"];
            
            if(!empty($_GET['vendedor'])) {
                $whereConditions[] = "v.id_vendedor = '" . mysqli_real_escape_string($connect, $_GET['vendedor']) . "'";
            }
            
            if(!empty($_GET['fecha_desde'])) {
                $whereConditions[] = "ped.fecha_pedido >= '" . mysqli_real_escape_string($connect, $_GET['fecha_desde']) . "'";
            }
            
            if(!empty($_GET['fecha_hasta'])) {
                $whereConditions[] = "ped.fecha_pedido <= '" . mysqli_real_escape_string($connect, $_GET['fecha_hasta']) . " 23:59:59'";
            }
            
            $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
            
            $queryVentas = "
                SELECT 
                    v.id_vendedor,
                    v.nombre_empresa,
                    v.nit,
                    v.telefono_contacto,
                    v.correo_contacto,
                    prod.id_producto,
                    prod.nombre as producto_nombre,
                    prod.precio as precio_producto,
                    dp.cantidad,
                    dp.precio_unitario,
                    dp.precio_total,
                    ped.id_pedido,
                    ped.fecha_pedido,
                    ped.estado as estado_pedido,
                    ped.total as total_pedido,
                    u.nombre as cliente_nombre,
                    u.apellido as cliente_apellido,
                    u.correo as cliente_correo,
                    pp.metodo_pago,
                    pp.estado_pago,
                    pp.fecha_pago,
                    (dp.precio_total * 0.10) as comision,
                    (dp.precio_total * 0.90) as ganancia_vendedor
                FROM vendedor v
                INNER JOIN producto prod ON v.id_vendedor = prod.id_vendedor
                INNER JOIN detalle_pedido dp ON prod.id_producto = dp.id_producto
                INNER JOIN pedido ped ON dp.id_pedido = ped.id_pedido
                INNER JOIN usuario u ON ped.id_usuario = u.id_usuario
                LEFT JOIN pasarela_pago pp ON ped.id_pedido = pp.id_pasarela
                $whereClause
                ORDER BY ped.fecha_pedido DESC
                LIMIT 100
            ";
            
            $resultVentas = mysqli_query($connect, $queryVentas);
            ?>
            
            <div class="tabla-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Vendedor</th>
                            <th>Producto</th>
                            <th>Cliente</th>
                            <th>Fecha</th>
                            <th>Cantidad</th>
                            <th>Total</th>
                            <th>Comisión</th>
                            <th>Ganancia</th>
                            <th>Estado</th>
                            <th>Pago</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        $totalVentas = 0;
                        $totalComisiones = 0;
                        $totalGanancias = 0;
                        
                        if($resultVentas) {
                            while($venta = mysqli_fetch_assoc($resultVentas)) {
                                $totalVentas += $venta['precio_total'];
                                $totalComisiones += $venta['comision'];
                                $totalGanancias += $venta['ganancia_vendedor'];
                        ?>
                        <tr>
                            <td>
                                <strong><?php echo htmlspecialchars($venta['nombre_empresa']); ?></strong><br>
                                <small><?php echo htmlspecialchars($venta['correo_contacto']); ?></small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($venta['producto_nombre']); ?><br>
                                <small>$<?php echo number_format($venta['precio_unitario'], 2); ?> c/u</small>
                            </td>
                            <td>
                                <?php echo htmlspecialchars($venta['cliente_nombre'] . ' ' . $venta['cliente_apellido']); ?><br>
                                <small><?php echo htmlspecialchars($venta['cliente_correo']); ?></small>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($venta['fecha_pedido'])); ?></td>
                            <td><?php echo $venta['cantidad']; ?></td>
                            <td>$<?php echo number_format($venta['precio_total'], 2); ?></td>
                            <td>$<?php echo number_format($venta['comision'], 2); ?></td>
                            <td>$<?php echo number_format($venta['ganancia_vendedor'], 2); ?></td>
                            <td>
                                <?php
                                $estadoClass = 'badge-warning';
                                if($venta['estado_pedido'] == 'entregado') $estadoClass = 'badge-success';
                                if($venta['estado_pedido'] == 'cancelado') $estadoClass = 'badge-danger';
                                ?>
                                <span class="badge <?php echo $estadoClass; ?>">
                                    <?php echo ucfirst($venta['estado_pedido']); ?>
                                </span>
                            </td>
                            <td>
                                <?php echo $venta['metodo_pago'] ? htmlspecialchars($venta['metodo_pago']) : 'N/A'; ?><br>
                                <small><?php echo $venta['estado_pago'] ? htmlspecialchars($venta['estado_pago']) : ''; ?></small>
                            </td>
                            <td>
                                <button onclick="verDetalleVenta(<?php echo htmlspecialchars(json_encode($venta)); ?>)" 
                                        class="btn-action btn-primary">
                                    <i class="fa-solid fa-eye"></i> Detalle
                                </button>
                            </td>
                        </tr>
                        <?php 
                            }
                        }
                        ?>
                        
                        <?php if(mysqli_num_rows($resultVentas) == 0): ?>
                        <tr>
                            <td colspan="11" style="text-align:center; padding:40px;">
                                No hay ventas registradas con los filtros seleccionados.
                            </td>
                        </tr>
                        <?php else: ?>
                        <tr style="background:#f8f9fa; font-weight:bold;">
                            <td colspan="5">TOTALES:</td>
                            <td>$<?php echo number_format($totalVentas, 2); ?></td>
                            <td>$<?php echo number_format($totalComisiones, 2); ?></td>
                            <td>$<?php echo number_format($totalGanancias, 2); ?></td>
                            <td colspan="3"></td>
                        </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== SISTEMA DE DISPUTAS ==================== -->
        <div id="disputas" class="subapartado-content <?php echo $subapartado == 'disputas' ? 'active' : ''; ?>">
            <h2>⚖️ Sistema de Disputas y Reclamos</h2>
            
            <?php
            // PROCESAR RESPUESTA A DISPUTA
            if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['responder_disputa'])) {
                $id_pedido = mysqli_real_escape_string($connect, $_POST['id_pedido']);
                $respuesta = mysqli_real_escape_string($connect, $_POST['respuesta']);
                $nuevo_estado = mysqli_real_escape_string($connect, $_POST['nuevo_estado']);
                
                $updateQuery = "
                    UPDATE pedido 
                    SET descripcion = CONCAT(descripcion, '\n\n--- RESPUESTA ADMIN ---\n', '$respuesta'),
                        estado = '$nuevo_estado'
                    WHERE id_pedido = '$id_pedido'
                ";
                
                if(mysqli_query($connect, $updateQuery)) {
                    echo '<div class="card-info" style="background:#d4edda;border-color:#c3e6cb;">
                            <i class="fa-solid fa-check-circle"></i> Respuesta enviada correctamente.
                          </div>';
                }
            }
            
            // OBTENER DISPUTAS (pedidos con problemas)
            $queryDisputas = "
                SELECT 
                    p.id_pedido,
                    p.fecha_pedido,
                    p.total,
                    p.estado,
                    p.descripcion,
                    p.llegada_estimada,
                    u.nombre as cliente_nombre,
                    u.apellido as cliente_apellido,
                    u.correo as cliente_email,
                    v.nombre_empresa as vendedor_nombre,
                    prod.nombre as producto_nombre,
                    dp.cantidad,
                    dp.precio_unitario,
                    img.url_imagen
                FROM pedido p
                INNER JOIN usuario u ON p.id_usuario = u.id_usuario
                INNER JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
                INNER JOIN producto prod ON dp.id_producto = prod.id_producto
                INNER JOIN vendedor v ON prod.id_vendedor = v.id_vendedor
                LEFT JOIN imagen_producto img ON prod.id_producto = img.id_producto AND img.es_principal = TRUE
                WHERE p.estado IN ('problema', 'reclamo', 'disputa', 'cancelado') 
                   OR p.descripcion LIKE '%problema%' 
                   OR p.descripcion LIKE '%reclamo%' 
                   OR p.descripcion LIKE '%disputa%'
                   OR p.descripcion LIKE '%queja%'
                ORDER BY p.fecha_pedido DESC
            ";
            
            $resultDisputas = mysqli_query($connect, $queryDisputas);
            $totalDisputas = $resultDisputas ? mysqli_num_rows($resultDisputas) : 0;
            ?>
            
            <div class="card-info">
                <h3>Disputas Activas: <span class="badge badge-danger"><?php echo $totalDisputas; ?></span></h3>
                <p>Aquí puede gestionar todas las quejas, reclamos y disputas entre clientes y vendedores.</p>
            </div>
            
            <div class="tabla-scroll">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Pedido</th>
                            <th>Cliente</th>
                            <th>Vendedor</th>
                            <th>Producto</th>
                            <th>Problema</th>
                            <th>Fecha</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        if($resultDisputas && mysqli_num_rows($resultDisputas) > 0) {
                            while($disputa = mysqli_fetch_assoc($resultDisputas)) { 
                                $tieneRespuesta = stripos($disputa['descripcion'], 'RESPUESTA ADMIN') !== false;
                        ?>
                        <tr>
                            <td>#<?php echo $disputa['id_pedido']; ?></td>
                            <td>
                                <?php echo htmlspecialchars($disputa['cliente_nombre'] . ' ' . $disputa['cliente_apellido']); ?><br>
                                <small><?php echo htmlspecialchars($disputa['cliente_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($disputa['vendedor_nombre']); ?></td>
                            <td>
                                <?php echo htmlspecialchars($disputa['producto_nombre']); ?><br>
                                <small><?php echo $disputa['cantidad']; ?> unidades</small>
                            </td>
                            <td>
                                <?php 
                                $descripcion = substr($disputa['descripcion'], 0, 100);
                                echo htmlspecialchars($descripcion) . (strlen($disputa['descripcion']) > 100 ? '...' : '');
                                ?>
                                <?php if($tieneRespuesta): ?>
                                    <br><span class="badge badge-success" style="margin-top:3px;">Respondido</span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($disputa['fecha_pedido'])); ?></td>
                            <td>
                                <span class="badge badge-warning">
                                    <?php echo ucfirst($disputa['estado']); ?>
                                </span>
                            </td>
                            <td>
                                <button onclick="abrirDisputa(<?php echo $disputa['id_pedido']; ?>)" 
                                        class="btn-action btn-primary">
                                    <i class="fa-solid fa-comments"></i> Gestionar
                                </button>
                            </td>
                        </tr>
                        <?php 
                            }
                        } else { 
                        ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px;">
                                🎉 No hay disputas activas en este momento.
                            </td>
                        </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ==================== ACTUALIZACIÓN DE ESTADOS ==================== -->
        <div id="estados" class="subapartado-content <?php echo $subapartado == 'estados' ? 'active' : ''; ?>">
            <h2>📦 Actualización de Estados de Pedidos</h2>
            
            <?php
           $updateQuery = "
    UPDATE pedido 
    SET estado = '$nuevo_estado',
        descripcion = CONCAT(
            COALESCE(descripcion, ''), 
            '\n\n--- ACTUALIZACIÓN ADMIN ---\n',
            'Estado cambiado a: $nuevo_estado\n',
            'Fecha: " . date('Y-m-d H:i:s') . "\n',
            'Admin: $admin_nombre\n',
            'Comentario: $comentario'
        )
    WHERE id_pedido = '$id_pedido'
";

if(mysqli_query($connect, $updateQuery)) {
    echo '<div class="card-info" style="background:#d4edda;border-color:#c3e6cb;">
            <i class="fa-solid fa-check-circle"></i> Estado actualizado correctamente.
          </div>';
} else {
    echo '<div class="card-info" style="background:#f8d7da;border-color:#f5c6cb;">
            <i class="fa-solid fa-exclamation-circle"></i> Error al actualizar estado: ' . mysqli_error($connect) . '
          </div>';
}