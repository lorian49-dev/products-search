<?php
session_start();
include('../shortCuts/connect.php');

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

// Solo Administrador General puede eliminar
if ($_SESSION['admin_rol'] != 1) {
    echo "<script>
        alert('Solo el Administrador General puede eliminar vendedores.');
        window.location.href = 'seller-dashboard-admin-index.php';
    </script>";
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: seller-dashboard-admin-index.php');
    exit();
}

$id_vendedor = intval($_GET['id']);

// Obtener información del vendedor
$query = "SELECT v.*, u.nombre, u.apellido, u.correo
          FROM vendedor v
          INNER JOIN usuario u ON v.id_vendedor = u.id_usuario
          WHERE v.id_vendedor = $id_vendedor";
          
$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: seller-dashboard-admin-index.php');
    exit();
}

$vendedor = mysqli_fetch_assoc($result);

// Obtener estadísticas para la confirmación
$estadisticas = [
    'productos' => 0,
    'catalogos' => 0,
    'productos_catalogo' => 0,
    'total_valor_inventario' => 0
];

// Productos totales
$query_productos = "SELECT COUNT(*) as total FROM producto WHERE id_vendedor = $id_vendedor";
$result_productos = mysqli_query($connect, $query_productos);
$estadisticas['productos'] = mysqli_fetch_assoc($result_productos)['total'];

// Catálogos
$query_catalogos = "SELECT COUNT(*) as total FROM catalogo WHERE id_vendedor = $id_vendedor";
$result_catalogos = mysqli_query($connect, $query_catalogos);
$estadisticas['catalogos'] = mysqli_fetch_assoc($result_catalogos)['total'];

// Productos en catálogos
$query_productos_catalogo = "SELECT COUNT(*) as total FROM catalogo_producto cp
                            INNER JOIN catalogo c ON cp.id_catalogo = c.id_catalogo
                            WHERE c.id_vendedor = $id_vendedor";
$result_productos_catalogo = mysqli_query($connect, $query_productos_catalogo);
$estadisticas['productos_catalogo'] = mysqli_fetch_assoc($result_productos_catalogo)['total'] ?? 0;

// Valor total del inventario
$query_inventario = "SELECT SUM(precio * stock) as total FROM producto WHERE id_vendedor = $id_vendedor";
$result_inventario = mysqli_query($connect, $query_inventario);
$estadisticas['total_valor_inventario'] = mysqli_fetch_assoc($result_inventario)['total'] ?? 0;

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmar_usuario = isset($_POST['eliminar_usuario']) ? 1 : 0;
    $eliminar_productos = isset($_POST['eliminar_productos']) ? 1 : 0;
    $password_confirm = trim($_POST['password_confirm']);
    
    // Verificar contraseña del administrador
    $admin_id = $_SESSION['admin_id'];
    $query_admin = "SELECT password FROM administradores WHERE id_admin = $admin_id";
    $result_admin = mysqli_query($connect, $query_admin);
    $admin = mysqli_fetch_assoc($result_admin);
    
    if (!password_verify($password_confirm, $admin['password'])) {
        $error = "Contraseña de administrador incorrecta";
    } else {
        // Iniciar transacción
        mysqli_begin_transaction($connect);
        
        try {
            // 1. Eliminar productos en catálogos (si hay catálogos)
            if ($estadisticas['catalogos'] > 0) {
                $query_delete_catalogo_producto = "DELETE cp FROM catalogo_producto cp
                                                  INNER JOIN catalogo c ON cp.id_catalogo = c.id_catalogo
                                                  WHERE c.id_vendedor = $id_vendedor";
                mysqli_query($connect, $query_delete_catalogo_producto);
            }
            
            // 2. Eliminar catálogos
            $query_delete_catalogos = "DELETE FROM catalogo WHERE id_vendedor = $id_vendedor";
            mysqli_query($connect, $query_delete_catalogos);
            
            // 3. Eliminar productos (si se seleccionó)
            if ($eliminar_productos) {
                $query_delete_productos = "DELETE FROM producto WHERE id_vendedor = $id_vendedor";
                mysqli_query($connect, $query_delete_productos);
            } else {
                // Si no se eliminan productos, establecer id_vendedor a NULL
                $query_update_productos = "UPDATE producto SET id_vendedor = NULL WHERE id_vendedor = $id_vendedor";
                mysqli_query($connect, $query_update_productos);
            }
            
            // 4. Eliminar vendedor
            $query_delete_vendedor = "DELETE FROM vendedor WHERE id_vendedor = $id_vendedor";
            mysqli_query($connect, $query_delete_vendedor);
            
            // 5. Eliminar usuario (si se seleccionó)
            if ($confirmar_usuario) {
                $query_delete_usuario = "DELETE FROM usuario WHERE id_usuario = $id_vendedor";
                mysqli_query($connect, $query_delete_usuario);
                
                $log_message = "Vendedor y usuario #$id_vendedor eliminados completamente. ";
            } else {
                $log_message = "Vendedor #$id_vendedor eliminado (usuario conservado). ";
            }
            
            $log_message .= $eliminar_productos ? "Productos eliminados." : "Productos desasociados.";
            
            // Registrar acción en logs
            $admin_nombre = $_SESSION['admin_nombre'];
            $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'ELIMINAR_VENDEDOR', '$log_message', NOW())";
            mysqli_query($connect, $query_log);
            
            // Confirmar transacción
            mysqli_commit($connect);
            
            // Redirigir con mensaje de éxito
            $_SESSION['mensaje_eliminacion'] = "Vendedor eliminado exitosamente. " . 
                                               ($confirmar_usuario ? "Usuario también eliminado. " : "Usuario conservado. ") .
                                               ($eliminar_productos ? "Productos eliminados." : "Productos desasociados.");
            header('Location: seller-dashboard-admin-index.php');
            exit();
            
        } catch (Exception $e) {
            mysqli_rollback($connect);
            $error = "Error durante la eliminación: " . $e->getMessage();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Confirmar Eliminación - Vendedor #<?php echo $id_vendedor; ?></title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .dashboard-container { max-width: 800px; margin: 0 auto; }
        
        /* Header */
        .header { background: rgba(255, 255, 255, 0.95); padding: 25px 30px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .header h1 { color: #333; font-size: 1.8em; margin-bottom: 5px; }
        .user-role { background: #667eea; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        
        /* Contenedor principal */
        .warning-container { background: rgba(255, 255, 255, 0.95); padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 25px; }
        
        /* Alerta de peligro */
        .danger-alert { background: #f8d7da; color: #721c24; padding: 20px; border-radius: 10px; margin-bottom: 25px; border-left: 6px solid #dc3545; }
        .danger-alert h3 { margin-bottom: 10px; display: flex; align-items: center; gap: 10px; }
        
        /* Información del vendedor */
        .vendor-summary { background: #f8f9fa; padding: 20px; border-radius: 10px; margin-bottom: 25px; border: 1px solid #dee2e6; }
        .vendor-header { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; }
        .vendor-avatar { width: 70px; height: 70px; background: #dc3545; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.8em; font-weight: bold; }
        .vendor-info h3 { color: #333; margin-bottom: 5px; }
        
        /* Estadísticas de eliminación */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: 15px; margin-bottom: 25px; }
        .stat-item { background: white; padding: 15px; border-radius: 8px; text-align: center; border: 1px solid #dee2e6; }
        .stat-item.warning { border-color: #ffc107; background: #fff3cd; }
        .stat-item.danger { border-color: #dc3545; background: #f8d7da; }
        .stat-number { font-size: 1.8em; font-weight: bold; margin: 5px 0; }
        .stat-number.warning { color: #856404; }
        .stat-number.danger { color: #721c24; }
        .stat-label { font-size: 0.85em; color: #666; }
        
        /* Formulario */
        .form-container { background: white; padding: 25px; border-radius: 10px; border: 2px solid #dee2e6; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em; }
        .form-check { display: flex; align-items: center; gap: 10px; margin-bottom: 15px; }
        .form-check input[type="checkbox"] { width: 20px; height: 20px; }
        
        /* Botones */
        .btn { padding: 12px 25px; border: none; border-radius: 25px; font-size: 1em; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-danger { background: #dc3545; color: white; } .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; } .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; } .btn-success:hover { background: #218838; transform: translateY(-2px); }
        
        /* Lista de consecuencias */
        .consequences-list { list-style: none; margin-left: 20px; }
        .consequences-list li { margin-bottom: 10px; padding-left: 25px; position: relative; }
        .consequences-list li:before { content: '⚠️'; position: absolute; left: 0; }
        
        /* Mensaje de error */
        .error-message { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px; border-left: 4px solid #dc3545; }
        
        @media (max-width: 768px) {
            .header-top { flex-direction: column; gap: 15px; text-align: center; }
            .vendor-header { flex-direction: column; text-align: center; }
            .stats-grid { grid-template-columns: repeat(2, 1fr); }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1>Confirmar Eliminación</h1>
                    <div class="user-role">
                        Administrador General
                    </div>
                </div>
                <a href="ver.php?id=<?php echo $id_vendedor; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar y Volver
                </a>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Esta acción es <strong>PERMANENTE e IRREVERSIBLE</strong>. Revise cuidadosamente.
            </p>
        </div>

        <!-- Alerta de peligro -->
        <div class="danger-alert">
            <h3><i class="fas fa-radiation"></i> ADVERTENCIA CRÍTICA</h3>
            <p>Está a punto de eliminar un vendedor del sistema. Esta acción <strong>NO SE PUEDE DESHACER</strong> y eliminará todos los datos relacionados permanentemente.</p>
        </div>

        <!-- Información del vendedor a eliminar -->
        <div class="vendor-summary">
            <div class="vendor-header">
                <div class="vendor-avatar">
                    <?php echo strtoupper(substr($vendedor['nombre'], 0, 1)); ?>
                </div>
                <div class="vendor-info">
                    <h3><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']); ?></h3>
                    <p><strong>Vendedor ID:</strong> #<?php echo $id_vendedor; ?> | 
                       <strong>Empresa:</strong> <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></p>
                    <p><strong>Correo:</strong> <?php echo htmlspecialchars($vendedor['correo']); ?> | 
                       <strong>NIT:</strong> <?php echo !empty($vendedor['nit']) ? htmlspecialchars($vendedor['nit']) : 'No especificado'; ?></p>
                </div>
            </div>
        </div>

        <!-- Estadísticas de lo que se eliminará -->
        <div class="warning-container">
            <h3 style="color: #dc3545; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-trash-alt"></i> Datos que serán AFECTADOS:
            </h3>
            
            <div class="stats-grid">
                <div class="stat-item <?php echo $estadisticas['productos'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Productos</div>
                    <div class="stat-number <?php echo $estadisticas['productos'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['productos']; ?>
                    </div>
                </div>
                
                <div class="stat-item <?php echo $estadisticas['catalogos'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Catálogos</div>
                    <div class="stat-number <?php echo $estadisticas['catalogos'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['catalogos']; ?>
                    </div>
                </div>
                
                <div class="stat-item <?php echo $estadisticas['productos_catalogo'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Productos en Catálogos</div>
                    <div class="stat-number <?php echo $estadisticas['productos_catalogo'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['productos_catalogo']; ?>
                    </div>
                </div>
                
                <div class="stat-item <?php echo $estadisticas['total_valor_inventario'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Valor del Inventario</div>
                    <div class="stat-number <?php echo $estadisticas['total_valor_inventario'] > 0 ? 'danger' : ''; ?>">
                        $<?php echo number_format($estadisticas['total_valor_inventario'], 2); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Mensaje de error -->
        <?php if(isset($error)): ?>
        <div class="error-message">
            <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>

        <!-- Formulario de confirmación -->
        <form method="POST" action="" class="form-container">
            <h3 style="color: #333; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                <i class="fas fa-shield-alt"></i> Confirmación de Seguridad
            </h3>
            
            <div class="form-group">
                <label for="password_confirm">Contraseña de Administrador *</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control" 
                       placeholder="Ingrese su contraseña para confirmar" required>
                <small style="color: #666; font-size: 0.85em;">Debe ingresar su contraseña de administrador para proceder</small>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="eliminar_productos" name="eliminar_productos" value="1" checked>
                    <label for="eliminar_productos" style="font-weight: bold; color: #dc3545;">
                        <i class="fas fa-box"></i> ELIMINAR TODOS LOS PRODUCTOS (<?php echo $estadisticas['productos']; ?> productos)
                    </label>
                </div>
                <small style="color: #666; font-size: 0.85em; margin-left: 30px;">
                    Si NO marca esta opción, los productos se desasociarán del vendedor (id_vendedor = NULL) pero permanecerán en el sistema.
                </small>
            </div>
            
            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="eliminar_usuario" name="eliminar_usuario" value="1">
                    <label for="eliminar_usuario" style="font-weight: bold; color: #dc3545;">
                        <i class="fas fa-user-slash"></i> ELIMINAR TAMBIÉN EL USUARIO ASOCIADO
                    </label>
                </div>
                <small style="color: #666; font-size: 0.85em; margin-left: 30px;">
                    Si marca esta opción, el usuario también será eliminado permanentemente. 
                    El vendedor no podrá volver a iniciar sesión.
                </small>
            </div>
            
            <h4 style="color: #721c24; margin: 25px 0 15px 0;">
                <i class="fas fa-exclamation-triangle"></i> Consecuencias de esta acción:
            </h4>
            
            <ul class="consequences-list">
                <li>Todos los <strong>catálogos</strong> serán eliminados</li>
                <li>Todas las <strong>relaciones de productos en catálogos</strong> serán borradas</li>
                <?php if($estadisticas['productos'] > 0): ?>
                <li><strong><?php echo $estadisticas['productos']; ?> productos</strong> serán 
                    <span id="consecuencia-productos">eliminados</span>
                </li>
                <?php endif; ?>
                <li>La <strong>información del vendedor</strong> será borrada de la base de datos</li>
                <?php if($estadisticas['total_valor_inventario'] > 0): ?>
                <li><strong>Estadísticas de inventario</strong> serán afectadas</li>
                <?php endif; ?>
                <li>Esta acción será <strong>registrada en los logs</strong> del sistema</li>
            </ul>
            
            <!-- Botones de acción -->
            <div style="display: flex; gap: 15px; margin-top: 30px; justify-content: center;">
                <button type="submit" class="btn btn-danger" 
                        onclick="return confirmFinal()"
                        style="padding: 15px 30px; font-size: 1.1em;">
                    <i class="fas fa-skull-crossbones"></i> CONFIRMAR ELIMINACIÓN
                </button>
                
                <a href="ver.php?id=<?php echo $id_vendedor; ?>" class="btn btn-success">
                    <i class="fas fa-times"></i> CANCELAR
                </a>
            </div>
        </form>

        <!-- Alternativa recomendada -->
        <div style="background: #d1ecf1; color: #0c5460; padding: 20px; border-radius: 10px; margin-top: 25px; border-left: 4px solid #17a2b8;">
            <h4><i class="fas fa-lightbulb"></i> Alternativas recomendadas</h4>
            <p>En lugar de eliminar permanentemente, considere:</p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Desactivar la cuenta</strong> en lugar de eliminarla</li>
                <li><strong>Desasociar productos</strong> en lugar de eliminarlos</li>
                <li><strong>Archivar la información</strong> en lugar de borrarla</li>
            </ul>
            <div style="margin-top: 15px;">
                <a href="seller-dashboard-admin-edit.php?id=<?php echo $id_vendedor; ?>" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-edit"></i> Editar en lugar de Eliminar
                </a>
            </div>
        </div>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px; padding-top: 20px; border-top: 1px solid #dee2e6;">
            <a href="index.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Volver a la Lista
            </a>
            <a href="../index.php" class="btn btn-secondary"> <!--Ruta no existente, debe llamarse de otra forma-->
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        // Actualizar texto de consecuencia según checkbox
        document.getElementById('eliminar_productos').addEventListener('change', function() {
            const consecuencia = document.getElementById('consecuencia-productos');
            if (this.checked) {
                consecuencia.textContent = 'eliminados';
            } else {
                consecuencia.textContent = 'desasociados (sin vendedor)';
            }
        });

        function confirmFinal() {
            const eliminarUsuario = document.getElementById('eliminar_usuario').checked;
            const eliminarProductos = document.getElementById('eliminar_productos').checked;
            const password = document.getElementById('password_confirm').value;
            
            if (!password) {
                alert('Debe ingresar su contraseña de administrador');
                return false;
            }
            
            let message = '⚠️ ⚠️ ⚠️ CONFIRMACIÓN FINAL ⚠️ ⚠️ ⚠️\n\n';
            message += '¿Está ABSOLUTAMENTE SEGURO de eliminar permanentemente:\n\n';
            message += '• Vendedor: ' + document.querySelector('.vendor-info h3').textContent + '\n';
            message += '• Empresa: ' + document.querySelector('.vendor-info p:nth-child(2) strong:nth-child(2)').nextSibling.textContent.trim() + '\n';
            message += '• ' + <?php echo $estadisticas['catalogos']; ?> + ' catálogos y sus relaciones\n';
            
            if (eliminarProductos) {
                message += '• ' + <?php echo $estadisticas['productos']; ?> + ' PRODUCTOS (ELIMINADOS PERMANENTEMENTE)\n';
            } else {
                message += '• ' + <?php echo $estadisticas['productos']; ?> + ' productos (DESASOCIADOS, permanecen en sistema)\n';
            }
            
            if (eliminarUsuario) {
                message += '• EL USUARIO ASOCIADO (no podrá volver a iniciar sesión)\n\n';
                message += '🚨 ESTA ACCIÓN ES COMPLETAMENTE IRREVERSIBLE 🚨';
            } else {
                message += '\n⚠️ El usuario se conservará (podrá volver a iniciar sesión)\n\n';
                message += '🚨 ESTA ACCIÓN NO SE PUEDE DESHACER 🚨';
            }
            
            message += '\n\nEscriba "ELIMINAR VENDEDOR" para confirmar:';
            
            const confirmText = prompt(message);
            
            if (confirmText === 'ELIMINAR VENDEDOR') {
                return true;
            } else {
                alert('Eliminación cancelada. El texto no coincidió.');
                return false;
            }
        }
        
        document.addEventListener('DOMContentLoaded', function() {
            // Prevenir envío accidental con Enter
            document.getElementById('password_confirm').addEventListener('keypress', function(e) {
                if (e.key === 'Enter') {
                    e.preventDefault();
                }
            });
            
            // Advertencia al marcar eliminar usuario
            document.getElementById('eliminar_usuario').addEventListener('change', function() {
                if (this.checked) {
                    if (!confirm('ADVERTENCIA ADICIONAL 🚨\n\nAl eliminar también el usuario:\n\n• El vendedor NO podrá volver a iniciar sesión NUNCA\n• Perderá acceso a todo el sistema\n• No podrá recuperar su cuenta\n\n¿Continuar?')) {
                        this.checked = false;
                    }
                }
            });
        });
    </script>
</body>
</html>
