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

// CONSULTA CORRECTA: cliente.id_cliente = usuario.id_usuario
$query = "SELECT c.*, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_nacimiento, u.direccion_principal
          FROM cliente c
          INNER JOIN usuario u ON c.id_cliente = u.id_usuario
          ORDER BY c.id_cliente DESC";

$result = mysqli_query($connect, $query);
if (!$result) {
    die("Error en consulta: " . mysqli_error($connect));
}

$total_clientes = mysqli_num_rows($result);

// Obtener estadísticas generales
$query_estadisticas = "SELECT 
    (SELECT COUNT(*) FROM cliente) as total_clientes,
    (SELECT COUNT(DISTINCT id_cliente) FROM carrito) as con_carrito,
    (SELECT COUNT(DISTINCT id_cliente) FROM pedido) as con_pedidos,
    (SELECT COUNT(DISTINCT id_cliente) FROM direccion_envio) as con_direcciones";

$result_estadisticas = mysqli_query($connect, $query_estadisticas);
$estadisticas = mysqli_fetch_assoc($result_estadisticas);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Clientes - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <style>
        .dashboard-container {
            width: 100%;
            margin-left: 15%;
            display: flex;
            justify-content: center;
            flex-direction: column;
            align-items: center;
            padding:5rem;
        }

        /* Header */
        .header {
            width: 70%;
            background: rgba(255, 255, 255, 0.95);
            padding: 25px 30px;
            border-radius: 20px;
            margin-bottom: 25px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all .5s ease;
        }

        .header-top {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .header h1 {
            color: #333;
            font-size: 1.8em;
            margin-bottom: 5px;
        }

        .user-role {
            background: linear-gradient(135deg, #0D47A1, #0097b2);
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.85em;
            font-weight: bold;
        }

        /* Botones */
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

        .btn-info {
            background: #17a2b8;
            color: white;
        }

        .btn-info:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        /* Estadísticas */
        .stats {
            width: 80%;
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 20px;
            border-radius: 12px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            transition: all .5s ease;
        }

        .stat-icon {
            font-size: 1.8em;
            color: #461d01;
            margin-bottom: 10px;
            transition: all .5s ease;

        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin: 5px 0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        /* Tabla */
        .table-container {
            background: transparent;
            border-radius: 15px;
            margin-bottom: 1.5rem;
            max-height: 800px;
            overflow-y: auto;
        }

        /*Scrollbar para el contenedor de la tabla*/
        .table-container::-webkit-scrollbar {
            width: 10px;
        }

        .table-container::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 10px;
            margin: 50px 0;
        }

        .table-container::-webkit-scrollbar-thumb {
            background: gray;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .table-container::-webkit-scrollbar-thumb:hover {
            background: gray;
        }

        .table-container::-webkit-scrollbar-button {
            display: none;
        }

        .table-header {
            padding: 20px 20px 20px 100px;
            display: flex;
            justify-content: left;
            align-items: center;
            gap: 50px;
        }

        .table-header h3 {
            color: #333;
            font-size: 1.2em;
        }

        .data-table { /*La tabla en cuestion*/
            width: 90%;
            border-collapse: collapse;
            margin: 0 auto;
            table-layout: fixed;
        }

        .data-table th {
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-weight: 600;
            font-size: 0.9em;
            text-align: center;
        }

        .data-table.data-table.table-dark thead{
    background:linear-gradient(135deg, #0D47A1, #0097b2);

        }

        .data-table.table-dark th {
            transition: all 1s ease;
            
        }

        .data-table td {
            padding: 12px 15px;
            ;
            font-size: 0.9em;
            overflow-x: auto;
            /* oculta exceso */
            /*text-overflow: ellipsis;*/
            /* puntos suspensivos */
            white-space: nowrap;
            /* evita salto de línea */
        }

.data-table a{text-decoration: none;}

        /*Tabla:scroll*/

        .data-table td::-webkit-scrollbar {
            height: 8px;
        }

        .data-table td::-webkit-scrollbar-track {
            background: transparent;
            border-radius: 10px;
            margin: 0 10px;
        }

        .data-table td::-webkit-scrollbar-thumb {
            background: gray;
            border-radius: 10px;
            border: 2px solid transparent;
            background-clip: content-box;
        }

        .data-table td::-webkit-scrollbar-button {
            display: none;
        }

        /* Badges */
        .badge {
            padding: 4px 10px;
            border-radius: 12px;
            font-size: 0.8em;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        /* Acciones */
        .acciones {
            display: flex;
            gap: 5px;
            flex-wrap: wrap;
        }

        .btn-action {
            padding: 6px 10px;
            border-radius: 6px;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-width: 30px;
        }

        .btn-view {
            background: #17a2b8;
            color: white;
        }

        .btn-view:hover {
            background: #138496;
            transform: translateY(-2px);
        }

        .btn-edit {
            background: #28a745;
            color: white;
        }

        .btn-edit:hover {
            background: #218838;
            transform: translateY(-2px);
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
            transform: translateY(-2px);
        }

        /* Búsqueda */

        .search-container { width: 80%;background:linear-gradient(135deg, #0D47A1, #0097b2); padding: 1.5rem; border-radius: 1rem; margin-bottom: 20px; display: flex; gap: 1rem; align-items: center; justify-content: center; box-shadow: 0 10px 30px rgba(0,0,0,0.4);}
        .search-box { flex: 1; position: relative; }
        .search-box input { width: 90%; padding: .8rem .9rem .8rem 3rem;border-style: none; border-radius: 1.5rem; font-size: 0.85em;box-shadow:  1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075); transition: background .5s ease;}
        .search-box i { position: absolute; left: 15px; top: 50%; transform: translateY(-50%); color: #999; }

        /* Responsive */
        @media (max-width: 768px) {
            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .stats {
                grid-template-columns: 1fr;
            }

            .search-container {
                flex-direction: column;
            }

            .data-table {
                display: block;
                overflow-x: auto;
            }

            .acciones {
                flex-direction: column;
                gap: 3px;
            }
        }

        /* Advertencia */
        .advertencia {
            width: 50%;
            background: #ffffffff;
            color: #721c24;
            padding: 1rem;
            border-radius: 10px;
            margin-top: 20px;
            border-left: 4px solid #dc3545;
            position: fixed;
            bottom: 0;
            display: flex;
            justify-content: space-between;
            right: 0;
            box-shadow: 0 10px 40px 10px rgba(0, 0, 0, 0.2);
            transition: all .7s ease;
        }

        .advertencia h4 {
            margin-bottom: 8px;
        }

        .advertencia ul {
            margin-left: 20px;
            margin-bottom: 10px;
            font-size: 10px
        }

        .advise-right {
            display: flex;
            justify-content: flex-end;
            gap: .5rem;
        }

        .advise-right button {
            height: 2rem;
            padding: .5rem;
            border-radius: .7rem;
            border-style: none;
            cursor: pointer;
            background-color: #721c24;
            transition: all .7s ease;
            color: white;
        }

        .advise-right button:hover {
            background-color: #c2c2c2ff;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.49);
            transform: translateY(-2px);
        }

        .advertencia.dark-mode-active-advise {
            color: #131313ff;
            border-color: #131313ff;
        }

        .advertencia.dark-mode-active-advise button {
            background-color: #2f2f2fff;
        }
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
                <li class="current-page">Clientes</li>
                <a href="seller-dashboard-admin-index.php">
                    <li>Vendedores</li>
                </a>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <a href="products-dashboard-admin-index.php">
                    <li>Productos</li>
                </a>
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
    <!--Aqui termina el Menu o barra de navegacion-->
    <!--Aqui comienza el contenido de la pagina-->
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1>Gestión de Clientes</h1>
                    <div class="user-role">
                        <?php
                        if ($_SESSION['admin_rol'] == 1) echo 'Administrador General';
                        elseif ($_SESSION['admin_rol'] == 2) echo 'Administrador Colaborador';
                        else echo 'Administrador';
                        ?>
                    </div>
                </div>
                <div>
                    <a href="user-dashboard-admin.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Dashboard
                    </a>
                    <a href="client-dashboard-index-watch.php?action=estadisticas" class="btn btn-primary">
                        <i class="fas fa-chart-pie"></i> Estadísticas
                    </a>
                </div>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Administra los clientes registrados en el sistema.
                <span class="badge badge-success">
                    <i class="fas fa-link"></i> Relación: cliente.id_cliente = usuario.id_usuario
                </span>
            </p>
        </div>

        <!-- Estadísticas -->
        <div class="stats">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-users"></i></div>
                <div class="stat-number"><?php echo $estadisticas['total_clientes']; ?></div>
                <div class="stat-label">Clientes Totales</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-number"><?php echo $estadisticas['con_carrito']; ?></div>
                <div class="stat-label">Con Carrito</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-number"><?php echo $estadisticas['con_pedidos']; ?></div>
                <div class="stat-label">Con Pedidos</div>
            </div>

            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-map-marker-alt"></i></div>
                <div class="stat-number"><?php echo $estadisticas['con_direcciones']; ?></div>
                <div class="stat-label">Con Direcciones</div>
            </div>
        </div>

            <!-- Búsqueda -->
        <div class="search-container">
            <div class="search-box">
                <i class="fas fa-search"></i>
                <input type="text" id="busqueda" placeholder="Buscar cliente por nombre o correo...">
            </div>
            <div class="search-box-button">
                <button class="btn btn-primary" onclick="buscarClientes()" id="btn-search-seller">
                <i class="fas fa-search"></i>
            </button>
            </div>
        </div>

        <!-- Tabla de Clientes -->
        <div class="table-container">
            <div class="table-header">
                <h3>Lista de Clientes Registrados</h3>
                <span style="color: #adadadff; font-size: 0.9em;">
                    Total: <?php echo $total_clientes; ?> cliente(s)
                </span>
            </div>

            <?php if ($total_clientes > 0): ?>
                <div style="overflow-x: auto; padding-bottom: 2rem; padding-top: 2rem;">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Cliente / Usuario</th>
                                <th>Correo</th>
                                <th>Teléfono</th>
                                <th>Wishlist</th>
                                <th>Info Adicional</th>
                                <th>Carritos</th>
                                <th>Pedidos</th>
                                <th>Direcciones</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cliente = mysqli_fetch_assoc($result)):
                                $id_cliente = $cliente['id_cliente'];

                                // Obtener estadísticas específicas de este cliente
                                $query_carritos = "SELECT COUNT(*) as total FROM carrito WHERE id_cliente = $id_cliente";
                                $result_carritos = mysqli_query($connect, $query_carritos);
                                $carritos = mysqli_fetch_assoc($result_carritos);

                                $query_pedidos = "SELECT COUNT(*) as total FROM pedido WHERE id_cliente = $id_cliente";
                                $result_pedidos = mysqli_query($connect, $query_pedidos);
                                $pedidos = mysqli_fetch_assoc($result_pedidos);

                                $query_direcciones = "SELECT COUNT(*) as total FROM direccion_envio WHERE id_cliente = $id_cliente";
                                $result_direcciones = mysqli_query($connect, $query_direcciones);
                                $direcciones = mysqli_fetch_assoc($result_direcciones);
                            ?>
                                <tr>
                                    <td><strong>#<?php echo $id_cliente; ?></strong></td>
                                    <td>
                                        <strong><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></strong>
                                        <?php if (!empty($cliente['fecha_nacimiento']) && $cliente['fecha_nacimiento'] != '0000-00-00'): ?>
                                            <br><small style="color: #666;">Nac: <?php echo date('d/m/Y', strtotime($cliente['fecha_nacimiento'])); ?></small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($cliente['correo'])): ?>
                                            <a href="mailto:<?php echo $cliente['correo']; ?>" style="text-decoration: none;">
                                                <?php echo htmlspecialchars($cliente['correo']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span class="badge badge-warning">Sin email</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if (!empty($cliente['telefono'])): ?>
                                            <a href="tel:<?php echo $cliente['telefono']; ?>" style="text-decoration: none;">
                                                <?php echo htmlspecialchars($cliente['telefono']); ?>
                                            </a>
                                        <?php else: ?>
                                            <span style="color: #999;">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php
                                        $wishlist = $cliente['wishlist_privada'] ?? 0;
                                        if ($wishlist == 1) {
                                            echo '<span class="badge badge-success" title="Wishlist privada"><i class="fas fa-lock"></i></span>';
                                        } else {
                                            echo '<span class="badge badge-info" title="Wishlist pública"><i class="fas fa-unlock"></i></span>';
                                        }
                                        ?>
                                    </td>
                                    <td>
                                        <?php
                                        $info = $cliente['informacion_adicional'] ?? '';
                                        if (!empty($info)) {
                                            if (strlen($info) > 20) {
                                                echo '<span title="' . htmlspecialchars($info) . '">' .
                                                    htmlspecialchars(substr($info, 0, 20)) . '...</span>';
                                            } else {
                                                echo htmlspecialchars($info);
                                            }
                                        } else {
                                            echo '<span style="color: #999; font-style: italic;">Sin info</span>';
                                        }
                                        ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($carritos['total'] > 0): ?>
                                            <span class="badge badge-success"><?php echo $carritos['total']; ?></span>
                                        <?php else: ?>
                                            <span class="badge">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($pedidos['total'] > 0): ?>
                                            <span class="badge badge-success"><?php echo $pedidos['total']; ?></span>
                                        <?php else: ?>
                                            <span class="badge">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td style="text-align: center;">
                                        <?php if ($direcciones['total'] > 0): ?>
                                            <span class="badge badge-success"><?php echo $direcciones['total']; ?></span>
                                        <?php else: ?>
                                            <span class="badge">0</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="acciones">
                                            <a href="client-dashboard-index-watch.php?id=<?php echo $id_cliente; ?>"
                                                class="btn-action btn-view" title="Ver Detalles">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="client-dashboard-index-edit.php?id=<?php echo $id_cliente; ?>"
                                                class="btn-action btn-edit" title="Editar">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <?php if ($_SESSION['admin_rol'] == 1): ?>
                                                <a href="client-dashboard-index-delete.php?id=<?php echo $id_cliente; ?>"
                                                    class="btn-action btn-delete"
                                                    title="Eliminar Cliente">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            <?php endif; ?>
                                            <?php if ($carritos['total'] > 0): ?>
                                                <a href="client-dashboard-index-actions.php?action=limpiar_carrito&id=<?php echo $id_cliente; ?>"
                                                    class="btn-action btn-warning"
                                                    title="Vaciar Carrito"
                                                    onclick="return confirm('¿Vaciar carrito del cliente?')">
                                                    <i class="fas fa-shopping-cart"></i>
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
                    <i class="fas fa-user-slash" style="font-size: 3em; margin-bottom: 20px; color: #ddd;"></i>
                    <h3>No hay clientes registrados</h3>
                    <p style="margin-top: 10px;">No se encontraron clientes en el sistema.</p>
                </div>
            <?php endif; ?>
        </div>

        <!-- Advertencia de Eliminación -->
        <?php if ($_SESSION['admin_rol'] == 1): ?>
            <div class="advertencia">
                <div class="advise-left">
                    <h4><i class="fas fa-exclamation-triangle"></i> Eliminaciones</h4>
                    <p><strong>La eliminación de un cliente eliminará PERMANENTEMENTE:</strong></p>
                    <ul>
                        <li><strong>Todos los carritos</strong> y productos en carrito asociados</li>
                        <li><strong>Todas las direcciones de envío</strong> registradas</li>
                        <li><strong>Todos los pedidos</strong> realizados y sus detalles</li>
                        <li><strong>Información del cliente</strong> en la tabla cliente</li>
                        <li><strong>Posiblemente el usuario asociado</strong> (se pedirá confirmación)</li>
                    </ul>
                    <p><strong>Recomendación:</strong> Considere desactivar la cuenta en lugar de eliminar.</p>
                </div>
                <div class="advise-right"><button id="allow-btn">De acuerdo</button><button id="dont-show-btn">No mostrar nuevamente</button></div>
            </div>
        <?php endif; ?>
    </div>
    <script src="../scripts/admin.js"></script>
    <script>
        function buscarClientes() {
            const busqueda = document.getElementById('busqueda').value.trim();
            if (busqueda) {
                window.location.href = 'client-dashboard-index.php?busqueda=' + encodeURIComponent(busqueda);
            }
        }

        // Búsqueda con Enter
        document.getElementById('busqueda').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                buscarClientes();
            }
        });

        // Confirmación para eliminación
        document.addEventListener('DOMContentLoaded', function() {
            const deleteButtons = document.querySelectorAll('.btn-delete');
            deleteButtons.forEach(button => {
                button.addEventListener('click', function(e) {
                    if (!confirm('⚠ ADVERTENCIA DE ELIMINACIÓN EN CASCADA ⚠\n\n¿Está ABSOLUTAMENTE seguro de eliminar este cliente?\n\nSe eliminarán TODOS los datos relacionados:\n• Carritos y productos en carrito\n• Direcciones de envío\n• Pedidos y detalles de pedido\n• Información del cliente\n\n¿Continuar con la eliminación PERMANENTE?')) {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
</body>

</html>
