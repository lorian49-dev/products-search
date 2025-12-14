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
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - Inicio</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
@import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&display=swap');

:root{

    /*Colores primarios*/
    --pastel-blanco:#fff8f1;
    --chocolate-marron:#461d01;
    --cielo-turqueza:#0097b2;
    --mostaza-amarillo:#ffb000;
    --fondo-negro:#2f2f2fff;

    /*Degradados principales*/

    --fade-blue:linear-gradient(135deg, #0D47A1, #0097b2);
    --fade-yellow:linear-gradient(135deg, #EF6C00, #ffb000);

    /*Sombras*/

    --main-shadow: 1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075);

    --dark-z-shadow:-5px -5px 10px rgba(255, 255, 255, 0.1),
                         10px 10px 10px rgba(0, 0, 0, 0.3),
                         inset -3px -3px 5px rgba(255, 255, 255, 0.1),
                         inset 5px 5px 10px rgba(0, 0, 0, 0.3);
}

*{
    padding: 0;
    margin: 0;
    box-sizing: border-box;
}

html{
    font-size: 1vw;
}

body {
    padding: 2rem;
    font-family: 'roboto condensed', sans-serif;
    font-weight: 300;
    transition: all 1s ease;
    background-color: var(--pastel-blanco);
    opacity: 0;
    }

    body.body-its-dark{
        background-color:var(--fondo-negro);
    }

    body.active{
    opacity: 1;
}

a{
    text-decoration:none;
}

        .dashboard-container {
            width: 80%;
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

        body.body-its-dark .header{
            background-color:transparent;
            box-shadow:var(--dark-z-shadow);
        }

        .welcome-message h1 {
            color: var(--fondo-negro);
            font-size: 2.5em;
            margin-bottom: 10px;
        }
body.body-its-dark .welcome-message h1{
            color:var(--pastel-blanco) !important;
}

        .user-role {
            background: var(--fade-blue);
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
            transform:scale(.8, .8);
        }

        .stat-card {
            background: rgba(255, 255, 255, 0.95);
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            color:var(--chocolate-marron);
            text-align: center;
            transition: transform 0.3s ease;
        }

        body.body-its-dark .stat-card{
            background-color:transparent;
            box-shadow:var(--dark-z-shadow);
            color:var(--pastel-blanco) !important;
        }

        .stat-card:hover {
            transform: translateY(-5px);
        }

        .stat-card i {
            font-size: 2.5em;
            color: var(--chocolate-marron);
            margin-bottom: 15px;
        }

        body.body-its-dark .stat-card i{
                        color: #c5c5c5ff;
        }

        .stat-number {
            font-size: 2em;
            font-weight: bold;
            margin: 10px 0;
        }

        .stat-label {
            color: #666;
            font-size: 0.9em;
        }

        body.body-its-dark .stat-label{
                        color: #c5c5c5ff;
        }

        .features-grid {
            margin: 0 auto;
            width: 80%;
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

        body.body-its-dark .feature-card{
                background-color: var(--pastel-blanco);
        }

        .feature-card:hover {
            transform: translateY(-5px);
            border-color: var(--chocolate-marron);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.2);
        }

        .feature-card i {
            font-size: 3em;
            color: var(--chocolate-marron);
            margin-bottom: 20px;
        }

        .feature-card h3 {
            margin-bottom: 15px;
            font-size: 1.4em;
        }

        .feature-card p {
            color: var(--fondo-negro);
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
            background: var(--fondo-negro);
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
            background: var(--fade-blue);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: .65rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: background 0.3s ease;
            transition:all .3s ease;
        }

        .btn-primary a{
            color:var(--pastel-blanco);
        }

        .btn-primary:hover {
            box-shadow:0 0 40px #00a2ffff;
            transform:scale(1.1, 1.1);
        }

        .btn-secondary {
            background: var(--fade-yellow);
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 25px;
            font-size: .7rem;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
            margin: 0 10px;
            transition: all 0.3s ease;
        }

        .btn-secondary:hover {
            background: var(--fade-blue);
            box-shadow:0 0 40px #00a2ffff;
            transform:scale(1.1, 1.1);
        }
    </style>
    
</head>
<body>
        <button class="btn-primary">
         <a href="../registros-inicio-sesion/admin_logout.php" class="logout-btn">
        <i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a>
    </button>
    <div class="dashboard-container">
        <!-- Header con información del usuario -->
        <div class="header">
            <div class="welcome-message">
                <h1>¡Bienvenido, <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?>!</h1>
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
            <a href="user-dashboard-admin-index.php" class="feature-card">
                <i class="fas fa-user-cog"></i>
                <h3>Gestión de Usuarios</h3>
                <p>Administrar usuarios del sistema, crear, editar y eliminar cuentas</p>
            </a>

            <!-- Gestión de Productos -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="products-dashboard-admin-index.php" class="feature-card admin-only"> <!--Ruta inexistente, utilizar otro nombre-->
                <i class="fas fa-boxes"></i>
                <h3>Gestión de Productos</h3>
                <p>Administrar catálogo de productos, categorías y variantes</p>
            </a>
            <?php endif; ?>

            <!-- Gestión de Pedidos -->
            <?php if ($_SESSION['admin_rol'] == 1): ?> 
            <a href="orders-admin-index.php" class="feature-card admin-only"> <!--Ruta inexistente, utilizar otro nombre-->
                <i class="fas fa-clipboard-list"></i>
                <h3>Gestión de Pedidos</h3>
                <p>Ver y administrar pedidos, estados y disputas</p>
            </a>
            <?php endif; ?>

            <!-- Configuración del Sistema -->
            <?php if ($_SESSION['admin_rol'] == 1): ?>
            <a href="#" class="feature-card admin-only"> <!--Ruta inexistente, utilizar otro nombre-->
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
            <a href="../home.php" class="btn-secondary">Hermes Click&Go</a>
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
    <script src="../scripts/admin.js"></script>
</body>
</html>
