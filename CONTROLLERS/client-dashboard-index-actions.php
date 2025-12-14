<?php
session_start();
include('../shortCuts/connect.php');

// --- VALIDACIÓN DE SESIÓN ADAPTADA ---
if (!isset($_SESSION['user_id']) || $_SESSION['user_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/login.php');
    exit();
}

// Verificar roles permitidos (1=admin, 2=admin_colaborador)
$rolesPermitidos = [1, 2];
if (!isset($_SESSION['id_rol']) || !in_array($_SESSION['id_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}

// Obtener acción e ID
$action = $_GET['action'] ?? '';
$id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_cliente || !$action) {
    header('Location: client-dashboard-index.php');
    exit();
}

// --- VERIFICAR QUE EL CLIENTE EXISTE (ADAPTADO) ---
$query_check = "SELECT c.id_cliente, u.nombre, u.apellido, u.email 
               FROM cliente c 
               INNER JOIN usuario u ON c.id_cliente = u.id_usuario 
               WHERE c.id_cliente = $id_cliente AND c.activo = 1";
$result_check = mysqli_query($connect, $query_check);

if (mysqli_num_rows($result_check) === 0) {
    echo "<script>
        alert('Cliente no encontrado o inactivo.');
        window.location.href = 'client-dashboard-index.php';
    </script>";
    exit();
}

$cliente = mysqli_fetch_assoc($result_check);
$nombre_cliente = htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']);
$email_cliente = htmlspecialchars($cliente['email']);

switch ($action) {
    case 'limpiar_carrito':
        // Vaciar carritos del cliente - ADAPTADO
        $query_carritos = "SELECT id_carrito FROM carrito WHERE id_cliente = $id_cliente";
        $result_carritos = mysqli_query($connect, $query_carritos);
        $total_carritos = mysqli_num_rows($result_carritos);
        
        if ($total_carritos > 0) {
            mysqli_begin_transaction($connect);
            try {
                // Eliminar productos de carrito
                while ($carrito = mysqli_fetch_assoc($result_carritos)) {
                    $id_carrito = $carrito['id_carrito'];
                    $query_delete = "DELETE FROM carrito_producto WHERE id_carrito = $id_carrito";
                    mysqli_query($connect, $query_delete);
                    
                    // También resetear total del carrito
                    $query_reset_total = "UPDATE carrito SET total = 0.00 WHERE id_carrito = $id_carrito";
                    mysqli_query($connect, $query_reset_total);
                }
                
                // --- REGISTRAR LOG (NUEVA TABLA) ---
                $admin_id = $_SESSION['user_id'];
                $admin_nombre = $_SESSION['user_nombre'] ?? 'Administrador';
                $query_log = "INSERT INTO logs_admin (id_admin, nombre_admin, accion, detalles, ip_address, user_agent, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'LIMPIAR_CARRITO', 'Carritos del cliente #$id_cliente ($nombre_cliente) vaciados', 
                             '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', NOW())";
                mysqli_query($connect, $query_log);
                
                mysqli_commit($connect);
                
                echo "<script>
                    alert('✅ Se vaciaron $total_carritos carrito(s) del cliente $nombre_cliente');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
                
            } catch (Exception $e) {
                mysqli_rollback($connect);
                echo "<script>
                    alert('Error al vaciar carritos: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
            }
        } else {
            echo "<script>
                alert('El cliente no tiene carritos activos.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
        break;
        
    case 'activar':
        // Activar cliente - ADAPTADO (usando columna 'activo')
        mysqli_begin_transaction($connect);
        try {
            // Activar en tabla cliente
            $query_activar = "UPDATE cliente SET activo = 1 WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_activar);
            
            // Activar también en usuario si es necesario
            $query_activar_usuario = "UPDATE usuario SET activo = 1 WHERE id_usuario = $id_cliente";
            mysqli_query($connect, $query_activar_usuario);
            
            // Activar billetera si existe
            $query_activar_billetera = "UPDATE billetera SET activa = 1 WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_activar_billetera);
            
            // Registrar log
            $admin_id = $_SESSION['user_id'];
            $admin_nombre = $_SESSION['user_nombre'] ?? 'Administrador';
            $query_log = "INSERT INTO logs_admin (id_admin, nombre_admin, accion, detalles, ip_address, user_agent, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'ACTIVAR_CLIENTE', 'Cliente #$id_cliente ($nombre_cliente) activado', 
                         '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', NOW())";
            mysqli_query($connect, $query_log);
            
            mysqli_commit($connect);
            
            echo "<script>
                alert('✅ Cliente $nombre_cliente activado correctamente.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
            
        } catch (Exception $e) {
            mysqli_rollback($connect);
            echo "<script>
                alert('Error al activar cliente: " . addslashes($e->getMessage()) . "');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
        break;
        
    case 'desactivar':
        // Desactivar cliente - ADAPTADO (usando columna 'activo')
        mysqli_begin_transaction($connect);
        try {
            // Desactivar en tabla cliente
            $query_desactivar = "UPDATE cliente SET activo = 0 WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_desactivar);
            
            // Desactivar también en usuario
            $query_desactivar_usuario = "UPDATE usuario SET activo = 0 WHERE id_usuario = $id_cliente";
            mysqli_query($connect, $query_desactivar_usuario);
            
            // Desactivar billetera
            $query_desactivar_billetera = "UPDATE billetera SET activa = 0 WHERE id_cliente = $id_cliente";
            mysqli_query($connect, $query_desactivar_billetera);
            
            // Registrar log
            $admin_id = $_SESSION['user_id'];
            $admin_nombre = $_SESSION['user_nombre'] ?? 'Administrador';
            $query_log = "INSERT INTO logs_admin (id_admin, nombre_admin, accion, detalles, ip_address, user_agent, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'DESACTIVAR_CLIENTE', 'Cliente #$id_cliente ($nombre_cliente) desactivado', 
                         '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', NOW())";
            mysqli_query($connect, $query_log);
            
            mysqli_commit($connect);
            
            echo "<script>
                alert('✅ Cliente $nombre_cliente desactivado correctamente.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
            
        } catch (Exception $e) {
            mysqli_rollback($connect);
            echo "<script>
                alert('Error al desactivar cliente: " . addslashes($e->getMessage()) . "');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
        break;
        
    case 'reset_password':
        // Resetear contraseña del usuario - ADAPTADO (ahora en tabla usuario)
        if ($_SESSION['id_rol'] == 1) { // Solo administrador general
            $nueva_password = bin2hex(random_bytes(8)); // Contraseña temporal
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            $query_reset = "UPDATE usuario SET password = '$password_hash' WHERE id_usuario = $id_cliente";
            
            if (mysqli_query($connect, $query_reset)) {
                // Registrar log
                $admin_id = $_SESSION['user_id'];
                $admin_nombre = $_SESSION['user_nombre'] ?? 'Administrador';
                $query_log = "INSERT INTO logs_admin (id_admin, nombre_admin, accion, detalles, ip_address, user_agent, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'RESET_PASSWORD', 'Contraseña del cliente #$id_cliente ($nombre_cliente) reseteada', 
                             '" . $_SERVER['REMOTE_ADDR'] . "', '" . $_SERVER['HTTP_USER_AGENT'] . "', NOW())";
                mysqli_query($connect, $query_log);
                
                // --- OPCIONAL: Enviar email al cliente con nueva contraseña ---
                // $asunto = "Restablecimiento de contraseña - HERMES";
                // $mensaje = "Hola $nombre_cliente,\n\nTu contraseña ha sido restablecida por un administrador.\nNueva contraseña temporal: $nueva_password\n\nPor favor, cambia tu contraseña después de iniciar sesión.\n\nSaludos,\nEquipo HERMES";
                // mail($email_cliente, $asunto, $mensaje);
                
                echo "<script>
                    alert('✅ Contraseña reseteada para $nombre_cliente\\nNueva contraseña temporal: $nueva_password\\n\\nEsta contraseña ha sido enviada al email: $email_cliente');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
            } else {
                echo "<script>
                    alert('Error al resetear contraseña: " . mysqli_error($connect) . "');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
            }
        } else {
            echo "<script>
                alert('⚠️ Solo el Administrador General puede resetear contraseñas.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
        break;
        
    case 'ver_billetera':
        // Ver saldo de billetera del cliente - NUEVA FUNCIÓN
        $query_billetera = "SELECT saldo, saldo_bloqueado, (saldo + saldo_bloqueado) as saldo_total, 
                           fecha_creacion, fecha_actualizacion 
                           FROM billetera 
                           WHERE id_cliente = $id_cliente AND activa = 1";
        $result_billetera = mysqli_query($connect, $query_billetera);
        
        if (mysqli_num_rows($result_billetera) > 0) {
            $billetera = mysqli_fetch_assoc($result_billetera);
            $saldo = number_format($billetera['saldo'], 2, ',', '.');
            $saldo_bloqueado = number_format($billetera['saldo_bloqueado'], 2, ',', '.');
            $saldo_total = number_format($billetera['saldo_total'], 2, ',', '.');
            
            echo "<script>
                alert('💰 Billetera de $nombre_cliente\\n\\nSaldo disponible: \$$saldo COP\\nSaldo bloqueado: \$$saldo_bloqueado COP\\nSaldo total: \$$saldo_total COP');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        } else {
            echo "<script>
                alert('El cliente no tiene billetera activa.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
        break;
        
    case 'ver_pedidos':
        // Ver resumen de pedidos del cliente - NUEVA FUNCIÓN
        $query_pedidos = "SELECT COUNT(*) as total_pedidos, 
                         SUM(CASE WHEN estado = 'Entregado' THEN 1 ELSE 0 END) as entregados,
                         SUM(CASE WHEN estado = 'Pendiente' THEN 1 ELSE 0 END) as pendientes,
                         SUM(CASE WHEN estado = 'Cancelado' THEN 1 ELSE 0 END) as cancelados,
                         SUM(total) as total_gastado
                         FROM pedido 
                         WHERE id_cliente = $id_cliente";
        $result_pedidos = mysqli_query($connect, $query_pedidos);
        $pedidos = mysqli_fetch_assoc($result_pedidos);
        
        $total_gastado = number_format($pedidos['total_gastado'] ?? 0, 2, ',', '.');
        
        echo "<script>
            alert('📦 Historial de Pedidos - $nombre_cliente\\n\\nTotal de pedidos: " . ($pedidos['total_pedidos'] ?? 0) . "\\nPedidos entregados: " . ($pedidos['entregados'] ?? 0) . "\\nPedidos pendientes: " . ($pedidos['pendientes'] ?? 0) . "\\nPedidos cancelados: " . ($pedidos['cancelados'] ?? 0) . "\\nTotal gastado: \$$total_gastado COP');
            window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
        </script>";
        break;
        
    default:
        echo "<script>
            alert('Acción no válida.');
            window.location.href = 'client-dashboard-index.php';
        </script>";
        break;
}
?>

<!-- Página simple de procesamiento -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesando acción...</title>
    <style>
        body { 
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .loader-container {
            background: white;
            padding: 40px;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
            max-width: 400px;
        }
        .loader {
            border: 5px solid #f3f3f3;
            border-top: 5px solid #667eea;
            border-radius: 50%;
            width: 50px;
            height: 50px;
            animation: spin 1s linear infinite;
            margin: 0 auto 20px;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }
        h2 { color: #333; margin-bottom: 10px; }
        p { color: #666; }
    </style>
</head>
<body>
    <div class="loader-container">
        <div class="loader"></div>
        <h2>Procesando acción...</h2>
        <p>Por favor, espere un momento.</p>
        <p><small>Si la página no redirige automáticamente, <a href="client-dashboard-index.php">haga clic aquí</a></small></p>
    </div>
    
    <script>
        // Redirección automática después de 5 segundos como respaldo
        setTimeout(function() {
            window.location.href = 'client-dashboard-index.php';
        }, 5000);
    </script>
</body>
</html>