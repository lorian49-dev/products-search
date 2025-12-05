<?php
// ==================== PROTECCIÓN DE ACCESO ====================
session_start();
include('shortCuts/connect.php');

// Verificar si está logueado como ADMIN
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';  // ← CORREGIDO
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

// Verificar rol de administrador (1 = administrador, 2 = admin_colaborador)
$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = 'home.php';
    </script>";
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inicio</title>
    <button>
         <a href="../registros-inicio-sesion/admin_logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
    </button>
    <link rel="stylesheet" href="styles/admin-user-crud.css">
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 20px;
        }

        .dashboard-container {
            max-width: 1200px;
            margin: 0 auto;
        }

        .header {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            text-align: center;
        }

        .welcome-message h1 {
            color: #333;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .user-role {
            background: #667eea;
            color: white;
            padding: 8px 20px;
            border-radius: 25px;
            display: inline-block;
            font-weight: bold;
            margin-top: 10px;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            transition: transform 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5em;
            color: #667eea;
            margin-bottom: 15px;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            color: #333;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
        }

        .feature-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            text-align: center;
            text-decoration: none;
            color: #333;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: #667eea;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .feature-card i {
            font-size: 3em;
            color: #667eea;
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .feature-card p {
            color: #666;
            line-height: 1.5;
        }

        .admin-only {
            position: relative;
        }

        .admin-only::after {
            content: "SOLO ADMIN GENERAL";
            position: absolute;
            top: 10px;
            right: 10px;
            background: #ff6b6b;
            color: white;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 0.7em;
            font-weight: bold;
        }

        .navigation-buttons {
            text-align: center;
            margin-top: 40px;
        }

        .btn-primary {
            background: #667eea;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1.1em;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: background 0.3s ease;
        }

        .btn-primary:hover {
            background: #5a6fd8;
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: 1.1em;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: background 0.3s ease;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }
    </style>
    
</head>
<body>
    <div class="dashboard-container">
        <!-- Header con información del usuario -->
        <div class="header">
            <div class="welcome-message">
                <h1>¡Bienvenido, <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?>!</h1>
                <a href="registros-inicio-sesion/admin_logout.php"></a>
                <div class="user-role">
                    <?php 
                        if ($_SESSION['admin_rol'] == 1) echo 'Administrador General';
                        elseif ($_SESSION['admin_rol'] == 2) echo 'Administrador Colaborador'; 
                        else echo 'Administrador';
                    ?>
                </div>
            </div>
        </div>
        <!-- Estadísticas rápidas -->
        <div class="stats-grid">
            <div class="stat-card">
                <i class="fas fa-users"></i>
                <div class="stat-number"><?php 
                    try {
                        $query = "SELECT COUNT(*) as total FROM usuario";
                        $result = mysqli_query($connect, $query);
                        $row = mysqli_fetch_assoc($result);
                        echo $row['total'];
                    } catch (Exception $e) {
                        echo "0";
                    }
                ?></div>
            <div class="stat-label">Usuarios Registrados</div>
    </div>

    <div class="stat-card">
        <i class="fas fa-shopping-cart"></i>
        <div class="stat-number">0</div>
        <div class="stat-label">Pedidos Hoy</div>
    </div>

    <div class="stat-card">
        <i class="fas fa-chart-line"></i>
        <div class="stat-number">$0</div>
        <div class="stat-label">Ingresos del Día</div>
    </div>

    <div class="stat-card">
        <i class="fas fa-store"></i>
        <div class="stat-number"><?php 
            try {
                $query = "SELECT COUNT(*) as total FROM vendedor";
                $result = mysqli_query($connect, $query);
                $row = mysqli_fetch_assoc($result);
                echo $row['total'] ?? '0';
            } catch (Exception $e) {
                echo "0";
            }
        ?></div>
        <div class="stat-label">Vendedores Registrados</div>
    </div>
</div>
</div>
        </div>

        <!-- Panel de funcionalidades -->
        <div class="features-grid">
            <!-- Gestión de Usuarios -->
            <a href="admin.php" class="feature-card">
                <i class="fas fa-user-cog"></i>
                <h3>Gestión de Usuarios</h3>
                <p>Administrar usuarios del sistema, crear, editar y eliminar cuentas</p>
            </a>

            <!-- Gestión de Productos -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="gestion_productos.php" class="feature-card admin-only">
                <i class="fas fa-boxes"></i>
                <h3>Gestión de Productos</h3>
                <p>Administrar catálogo de productos, categorías y variantes</p>
            </a>
            <?php endif; ?>

            <!-- Gestión de Pedidos -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="gestion_pedidos.php" class="feature-card admin-only">
                <i class="fas fa-clipboard-list"></i>
                <h3>Gestión de Pedidos</h3>
                <p>Ver y administrar pedidos, estados y disputas</p>
            </a>
            <?php endif; ?>

            <!-- Reportes y Estadísticas -->
            <a href="reportes.php" class="feature-card">
                <i class="fas fa-chart-bar"></i>
                <h3>Reportes y Estadísticas</h3>
                <p>Ver reportes de ventas, tráfico y métricas importantes</p>
            </a>

            <!-- Configuración del Sistema -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="configuracion.php" class="feature-card admin-only">
                <i class="fas fa-cogs"></i>
                <h3>Configuración del Sistema</h3>
                <p>Configuraciones generales de la plataforma</p>
            </a>
            <?php endif; ?>

            <!-- Backup y Seguridad -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="http://localhost/phpmyadmin/index.php?route=/database/structure&db=hermes_bd" class="feature-card admin-only">
                <i class="fas fa-database"></i>
                <h3>Backup y Seguridad</h3>
                <p>Respaldos de base de datos y configuraciones de seguridad</p>
            </a>
            <?php endif; ?>
        </div>

        <!-- Botones de navegación -->
        <div class="navigation-buttons">
            <a href="CONTROLLERS/user-dashboard-admin-index.php" class="btn-primary">USERS</a>
            <a href="home.php" class="btn-secondary">Volver al Sitio Principal</a>
        </div>
    </div>

    <script>
        // Efectos simples de interacción
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.feature-card');
            cards.forEach(card => {
                card.addEventListener('mouseenter', function() {
                    this.style.transform = 'translateY(-5px)';
                });
                card.addEventListener('mouseleave', function() {
                    this.style.transform = 'translateY(0)';
                });
            });
        });
    </script>
</body>
</html>