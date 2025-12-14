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
        alert('Solo el Administrador General puede eliminar clientes.');
        window.location.href = 'client-dashboard-index.php';
    </script>";
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: client-dashboard-index.php');
    exit();
}

$id_cliente = intval($_GET['id']);

// Obtener información del cliente
$query = "SELECT c.*, u.nombre, u.apellido, u.correo
          FROM cliente c
          INNER JOIN usuario u ON c.id_cliente = u.id_usuario
          WHERE c.id_cliente = $id_cliente";

$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: client-dashboard-index.php');
    exit();
}

$cliente = mysqli_fetch_assoc($result);

// Obtener estadísticas para la confirmación
$estadisticas = [
    'carritos' => 0,
    'productos_carrito' => 0,
    'pedidos' => 0,
    'detalles_pedido' => 0,
    'direcciones' => 0,
    'total_gastado' => 0
];

// Carritos activos
$query_carritos = "SELECT COUNT(*) as total FROM carrito WHERE id_cliente = $id_cliente";
$result_carritos = mysqli_query($connect, $query_carritos);
$estadisticas['carritos'] = mysqli_fetch_assoc($result_carritos)['total'];

// Productos en carrito
$query_productos = "SELECT SUM(cantidad) as total FROM carrito_producto cp
                   INNER JOIN carrito c ON cp.id_carrito = c.id_carrito
                   WHERE c.id_cliente = $id_cliente";
$result_productos = mysqli_query($connect, $query_productos);
$estadisticas['productos_carrito'] = mysqli_fetch_assoc($result_productos)['total'] ?? 0;

// Pedidos
$query_pedidos = "SELECT COUNT(*) as total FROM pedido WHERE id_cliente = $id_cliente";
$result_pedidos = mysqli_query($connect, $query_pedidos);
$estadisticas['pedidos'] = mysqli_fetch_assoc($result_pedidos)['total'];

// Detalles de pedido
$query_detalles = "SELECT COUNT(*) as total FROM detalle_pedido dp
                   INNER JOIN pedido p ON dp.id_pedido = p.id_pedido
                   WHERE p.id_cliente = $id_cliente";
$result_detalles = mysqli_query($connect, $query_detalles);
$estadisticas['detalles_pedido'] = mysqli_fetch_assoc($result_detalles)['total'];

// Direcciones
$query_direcciones = "SELECT COUNT(*) as total FROM direccion_envio WHERE id_cliente = $id_cliente";
$result_direcciones = mysqli_query($connect, $query_direcciones);
$estadisticas['direcciones'] = mysqli_fetch_assoc($result_direcciones)['total'];

// Total gastado
$query_total = "SELECT SUM(dp.precio_unitario * dp.cantidad) as total 
               FROM detalle_pedido dp
               INNER JOIN pedido p ON dp.id_pedido = p.id_pedido
               WHERE p.id_cliente = $id_cliente AND p.estado = 'completado'";
$result_total = mysqli_query($connect, $query_total);
$estadisticas['total_gastado'] = mysqli_fetch_assoc($result_total)['total'] ?? 0;

// Procesar eliminación
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $confirmar_usuario = isset($_POST['eliminar_usuario']) ? 1 : 0;
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
            // 1. Eliminar productos en carrito (cascada manual)
            if ($estadisticas['carritos'] > 0) {
                $query_delete_carrito_producto = "DELETE cp FROM carrito_producto cp
                                                 INNER JOIN carrito c ON cp.id_carrito = c.id_carrito
                                                 WHERE c.id_cliente = $id_cliente";
                mysqli_query($connect, $query_delete_carrito_producto);
            }

            // 2. Eliminar carritos
            $query_delete_carrito = "DELETE FROM carrito WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_delete_carrito);

            // 3. Eliminar direcciones de envío
            $query_delete_direcciones = "DELETE FROM direccion_envio WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_delete_direcciones);

            // 4. Eliminar detalles de pedido
            if ($estadisticas['pedidos'] > 0) {
                $query_delete_detalles = "DELETE dp FROM detalle_pedido dp
                                         INNER JOIN pedido p ON dp.id_pedido = p.id_pedido
                                         WHERE p.id_cliente = $id_cliente";
                mysqli_query($connect, $query_delete_detalles);
            }

            // 5. Eliminar pedidos
            $query_delete_pedidos = "DELETE FROM pedido WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_delete_pedidos);

            // 6. Eliminar cliente
            $query_delete_cliente = "DELETE FROM cliente WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_delete_cliente);

            // 7. Eliminar usuario (si se seleccionó)
            if ($confirmar_usuario) {
                $query_delete_usuario = "DELETE FROM usuario WHERE id_usuario = $id_cliente";
                mysqli_query($connect, $query_delete_usuario);

                // Registrar log de eliminación completa
                $log_message = "Cliente y usuario #$id_cliente eliminados completamente";
            } else {
                $log_message = "Cliente #$id_cliente eliminado (usuario conservado)";
            }

            // Registrar acción en logs
           /* $admin_nombre = $_SESSION['admin_nombre'];
            $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'ELIMINAR_CLIENTE', '$log_message', NOW())";
            mysqli_query($connect, $query_log); */

            // Confirmar transacción
            mysqli_commit($connect);

            // Redirigir con mensaje de éxito
            $_SESSION['mensaje_eliminacion'] = "Cliente eliminado exitosamente. " .
                ($confirmar_usuario ? "Usuario también eliminado." : "Usuario conservado.");
            header('Location: client-dashboard-index.php');
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
    <title>Confirmar Eliminación - Cliente #<?php echo $id_cliente; ?></title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-create-delete-watch-user-crud.css">
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
                <a href="client-dashboard-index-watch.php?id=<?php echo $id_cliente; ?>" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Cancelar y Volver
                </a>
            </div>
            <p style="font-size: 0.95em;">
                Esta acción es <strong>PERMANENTE e IRREVERSIBLE</strong>. Revise cuidadosamente.
            </p>
        </div>

        <!-- Alerta de peligro -->
        <div class="warning-advices">
            <div class="danger-alert">
            <h3><i class="fas fa-radiation"></i> ADVERTENCIA CRÍTICA</h3>
            <p>Está a punto de eliminar un cliente del sistema. Esta acción <strong>NO SE PUEDE DESHACER</strong> y eliminará todos los datos relacionados permanentemente.</p>
        </div>
        <!-- Alternativa recomendada -->
        <div class="alternative-way">
            <h4><i class="fas fa-lightbulb"></i> Alternativa recomendada</h4>
            <p>En lugar de eliminar permanentemente, considere:</p>
            <ul style="margin-left: 20px; margin-top: 10px;">
                <li><strong>Desactivar la cuenta</strong> en lugar de eliminarla</li>
                <li><strong>Mantener el usuario</strong> pero eliminar solo los datos del cliente</li>
                <li><strong>Archivar la información</strong> en lugar de borrarla</li>
            </ul>
            <div style="margin-top: 15px; position:absolute; right:1rem; bottom:20px;">
                <a href="client-dashboard-index-edit.php?id=<?php echo $id_cliente; ?>" class="btn btn-secondary" style="padding: 8px 15px;">
                    <i class="fas fa-edit"></i> Editar en lugar de Eliminar
                </a>
            </div>
        </div>
        </div>

        <!-- Información del cliente a eliminar -->
        <div class="client-summary">
            <div class="client-header">
                <div class="client-avatar">
                    <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                </div>
                <div class="client-info">
                    <h3><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h3>
                    <p><strong>Cliente ID:</strong> #<?php echo $id_cliente; ?> |
                        <strong>Correo:</strong> <?php echo htmlspecialchars($cliente['correo']); ?>
                    </p>
                </div>
            </div>

            <div style="font-size: 0.95em;">
                <p><strong>Información adicional:</strong> <?php echo !empty($cliente['informacion_adicional']) ?
                                                                htmlspecialchars(substr($cliente['informacion_adicional'], 0, 100)) .
                                                                (strlen($cliente['informacion_adicional']) > 100 ? '...' : '') :
                                                                'No especificada'; ?></p>
            </div>
        </div>

        <!-- Estadísticas de lo que se eliminará -->
        <div class="warning-container">
            <h3 style="color: #dc3545; margin-bottom: 20px; display: flex; align-items: center; gap: 10px;">
                <i class="fas fa-trash-alt"></i> Datos que serán ELIMINADOS:
            </h3>

            <div class="stats-grid">
                <div class="stat-item <?php echo $estadisticas['carritos'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Carritos de Compra</div>
                    <div class="stat-number <?php echo $estadisticas['carritos'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['carritos']; ?>
                    </div>
                </div>

                <div class="stat-item <?php echo $estadisticas['productos_carrito'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Productos en Carrito</div>
                    <div class="stat-number <?php echo $estadisticas['productos_carrito'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['productos_carrito']; ?>
                    </div>
                </div>

                <div class="stat-item <?php echo $estadisticas['pedidos'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Pedidos Realizados</div>
                    <div class="stat-number <?php echo $estadisticas['pedidos'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['pedidos']; ?>
                    </div>
                </div>

                <div class="stat-item <?php echo $estadisticas['direcciones'] > 0 ? 'danger' : ''; ?>">
                    <div class="stat-label">Direcciones de Envío</div>
                    <div class="stat-number <?php echo $estadisticas['direcciones'] > 0 ? 'danger' : ''; ?>">
                        <?php echo $estadisticas['direcciones']; ?>
                    </div>
                </div>
            </div>

            <?php if ($estadisticas['total_gastado'] > 0): ?>
                <div style="text-align: center; margin-top: 15px; padding: 10px; background: #e9ecef; border-radius: 8px;">
                    <strong>Total histórico gastado:</strong>
                    <span>$<?php echo number_format($estadisticas['total_gastado'], 2); ?></span>
                </div>
            <?php endif; ?>
        </div>

        <!-- Mensaje de error -->
        <?php if (isset($error)): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <!-- Formulario de confirmación -->
        <form method="POST" action="" class="form-container-delete">
            <h3 style=" margin-bottom: 20px; padding-bottom: 10px; border-bottom: 1px solid #dee2e6;">
                <i class="fas fa-shield-alt"></i> Confirmación de Seguridad
            </h3>

            <div class="form-group">
                <label for="password_confirm">Contraseña de Administrador *</label>
                <input type="password" id="password_confirm" name="password_confirm" class="form-control"
                    placeholder="Ingrese su contraseña para confirmar" required>
                <small>Debe ingresar su contraseña de administrador para proceder</small>
            </div>

            <div class="form-group">
                <div class="form-check">
                    <input type="checkbox" id="eliminar_usuario" name="eliminar_usuario" value="1">
                    <label for="eliminar_usuario" style="font-weight: bold; color: #dc3545;">
                        <i class="fas fa-user-slash"></i> ELIMINAR TAMBIÉN EL USUARIO ASOCIADO
                    </label>
                </div>
                <small style="margin-left: 30px;">
                    Si marca esta opción, el usuario también será eliminado permanentemente.
                    El cliente no podrá volver a iniciar sesión.
                </small>
            </div>

            <h4 style="margin: 25px 0 15px 0;">
                <i class="fas fa-exclamation-triangle"></i> Consecuencias de esta acción:
            </h4>

            <ul class="consequences-list">
                <li>Todos los <strong>carritos de compra</strong> serán eliminados</li>
                <li>Todos los <strong>productos en carrito</strong> serán removidos</li>
                <li>Todas las <strong>direcciones de envío</strong> serán borradas</li>
                <li>Todos los <strong>pedidos realizados</strong> serán eliminados</li>
                <li>Los <strong>detalles de pedido</strong> serán removidos permanentemente</li>
                <li>La <strong>información del cliente</strong> será borrada de la base de datos</li>
                <?php if ($estadisticas['total_gastado'] > 0): ?>
                    <li><strong>Estadísticas de ventas</strong> serán afectadas</li>
                <?php endif; ?>
            </ul>

            <!-- Botones de acción -->
            <div style="display: flex; gap: 15px; margin-top: 30px; justify-content: center;">
                <button type="submit" class="btn btn-danger"
                    onclick="return confirmFinal()"
                    style="font-size: 1.1em;">
                    <i class="fas fa-skull-crossbones"></i> CONFIRMAR ELIMINACIÓN
                </button>

                <a href="client-dashboard-index-watch.php?id=<?php echo $id_cliente; ?>" class="btn btn-success">
                    <i class="fas fa-times"></i> CANCELAR
                </a>
            </div>

            <div style="text-align: center; margin-top: 20px; font-size: 0.9em;">
                <i class="fas fa-info-circle"></i> Esta acción será registrada en los logs del sistema
            </div>
        </form>

        <!-- Navegación -->
        <div class="buttons-div">
            <a href="client-dashboard-index.php" class="btn btn-secondary">
                <i class="fas fa-list"></i> Volver a la Lista
            </a>
            <a href="user-dashboard-admin.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        function confirmFinal() {
            const eliminarUsuario = document.getElementById('eliminar_usuario').checked;
            const password = document.getElementById('password_confirm').value;

            if (!password) {
                alert('Debe ingresar su contraseña de administrador');
                return false;
            }

            let message = '⚠️ ⚠️ ⚠️ CONFIRMACIÓN FINAL ⚠️ ⚠️ ⚠️\n\n';
            message += '¿Está ABSOLUTAMENTE SEGURO de eliminar permanentemente:\n\n';
            message += '• ' + document.querySelector('.client-info h3').textContent + '\n';
            message += '• TODOS los carritos y productos en carrito\n';
            message += '• TODAS las direcciones de envío\n';
            message += '• TODOS los pedidos y detalles\n';

            if (eliminarUsuario) {
                message += '• EL USUARIO ASOCIADO (no podrá volver a iniciar sesión)\n\n';
                message += 'ESTA ACCIÓN ES COMPLETAMENTE IRREVERSIBLE ';
            } else {
                message += '\n⚠️ El usuario se conservará (podrá volver a iniciar sesión)\n\n';
                message += ' ESTA ACCIÓN NO SE PUEDE DESHACER ';
            }

            message += '\n\nEscriba "ELIMINAR" para confirmar:';

            const confirmText = prompt(message);

            if (confirmText === 'ELIMINAR') {
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
                    if (!confirm('ADVERTENCIA ADICIONAL 🚨\n\nAl eliminar también el usuario:\n\n• El cliente NO podrá volver a iniciar sesión NUNCA\n• Perderá acceso a todo el sistema\n• No podrá recuperar su cuenta\n\n¿Continuar?')) {
                        this.checked = false;
                    }
                }
            });
        });
    </script>
        <script src="../scripts/admin.js"></script>
</body>

</html>
