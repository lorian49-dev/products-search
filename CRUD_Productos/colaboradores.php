<?php
// admin-colaborador/index.php
require_once 'auth_middleware.php';
verificarAdminColaborador();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel - Admin Colaborador</title>
   
</head>
<body>
    <div class="header">
        <h1>👥 Panel de Admin Colaborador</h1>
        <div class="user-info">
            <strong>Bienvenido: <?php echo $_SESSION['username']; ?></strong><br>
            <small>Tipo: Admin Colaborador | <?php echo date('d/m/Y H:i'); ?></small>
        </div>
        <div style="clear: both;"></div>
    </div>

    <div class="nav-menu">
        <a href="../home.php">Dashboard</a>
        <a href="gestion-usuarios.php">👥 Gestión de Usuarios</a>
        <a href="contenido.php">📄 Gestión de Contenido</a>
        <a href="reportes.php">📊 Reportes</a>
        <a href="perfil.php">⚙️ Mi Perfil</a>
        <a href="../logout.php" style="color: #e74c3c;">🚪 Cerrar Sesión</a>
    </div>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number">150</div>
                <div>Usuarios Activos</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">24</div>
                <div>Nuevos Hoy</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">89%</div>
                <div>Actividad</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">12</div>
                <div>Tareas Pendientes</div>
            </div>
        </div>

        <div class="card">
            <h2>🚀 Acciones Rápidas</h2>
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin-top: 20px;">
                <a href="gestion-usuarios.php?action=nuevo" style="display: block; background: var(--accent-color); color: white; padding: 15px; text-align: center; border-radius: 8px; text-decoration: none;">
                    ➕ Nuevo Usuario
                </a>
                <a href="contenido.php?action=nuevo" style="display: block; background: var(--primary-color); color: white; padding: 15px; text-align: center; border-radius: 8px; text-decoration: none;">
                    📝 Crear Contenido
                </a>
                <a href="reportes.php" style="display: block; background: #f39c12; color: white; padding: 15px; text-align: center; border-radius: 8px; text-decoration: none;">
                    📈 Ver Reportes
                </a>
                <a href="perfil.php" style="display: block; background: #9b59b6; color: white; padding: 15px; text-align: center; border-radius: 8px; text-decoration: none;">
                    ⚙️ Configuración
                </a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 20px;">
            <div class="card">
                <h2>📋 Actividad Reciente</h2>
                <div style="max-height: 300px; overflow-y: auto;">
                    <div style="padding: 10px; border-bottom: 1px solid #eee;">
                        <strong>Usuario registrado:</strong> Juan Pérez<br>
                        <small>Hace 5 minutos</small>
                    </div>
                    <div style="padding: 10px; border-bottom: 1px solid #eee;">
                        <strong>Contenido actualizado:</strong> Página principal<br>
                        <small>Hace 15 minutos</small>
                    </div>
                    <div style="padding: 10px; border-bottom: 1px solid #eee;">
                        <strong>Reporte generado:</strong> Estadísticas mensuales<br>
                        <small>Hace 1 hora</small>
                    </div>
                </div>
            </div>

            <div class="card">
                <h2>✅ Tus Permisos</h2>
                <ul class="permisos-list">
                    <li>Gestión de usuarios</li>
                    <li>Edición de contenido</li>
                    <li>Generación de reportes</li>
                    <li>Vista de estadísticas</li>
                    <li>Gestión de perfiles</li>
                </ul>
                <div style="margin-top: 20px; padding: 10px; background: #e3f2fd; border-radius: 5px;">
                    <small>🔒 Permisos limitados - Admin Colaborador</small>
                </div>
            </div>
        </div>
    </div>

    <footer style="text-align: center; padding: 20px; margin-top: 40px; background: #34495e; color: white;">
        <small>
            🔒 Panel de Admin Colaborador - Acceso restringido | 
            IP: <?php echo $_SERVER['REMOTE_ADDR']; ?> | 
            Sesión iniciada: <?php echo date('H:i', $_SESSION['login_time']); ?>
        </small>
    </footer>
</body>
</html>