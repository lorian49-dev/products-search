<?php
session_start();
include('../shortCuts/connect.php');

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
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

// Obtener acción e ID
$action = $_GET['action'] ?? '';
$id_cliente = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_cliente || !$action) {
    header('Location: client-dashboard-index.php');
    exit();
}

// Verificar que el cliente existe
$query_check = "SELECT c.id_cliente, u.nombre, u.apellido 
               FROM cliente c 
               INNER JOIN usuario u ON c.id_cliente = u.id_usuario 
               WHERE c.id_cliente = $id_cliente";
$result_check = mysqli_query($connect, $query_check);

if (mysqli_num_rows($result_check) === 0) {
    echo "<script>
        alert('Cliente no encontrado.');
        window.location.href = 'client-dashboard-index.php';
    </script>";
    exit();
}

$cliente = mysqli_fetch_assoc($result_check);
$nombre_cliente = htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']);

switch ($action) {
    case 'limpiar_carrito':
        // Vaciar carritos del cliente
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
                }
                
                // Registrar log
                $admin_id = $_SESSION['admin_id'];
                $admin_nombre = $_SESSION['admin_nombre'];
                $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'LIMPIAR_CARRITO', 'Carritos del cliente #$id_cliente vaciados', NOW())";
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
        // Activar cliente (si tuviera estado)
        echo "<script>
            alert('Función de activación pendiente de implementar');
            window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
        </script>";
        break;
        
    case 'desactivar':
        // Desactivar cliente (si tuviera estado)
        echo "<script>
            alert('Función de desactivación pendiente de implementar');
            window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
        </script>";
        break;
        
    case 'reset_password':
        // Resetear contraseña del usuario
        if ($_SESSION['admin_rol'] == 1) {
            $nueva_password = bin2hex(random_bytes(8)); // Contraseña temporal
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            $query_reset = "UPDATE usuario SET contrasena = '$password_hash' WHERE id_usuario = $id_cliente";
            
            if (mysqli_query($connect, $query_reset)) {
                // Registrar log
                $admin_id = $_SESSION['admin_id'];
                $admin_nombre = $_SESSION['admin_nombre'];
                $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'RESET_PASSWORD', 'Contraseña del cliente #$id_cliente reseteada', NOW())";
                mysqli_query($connect, $query_log);
                
                echo "<script>
                    alert('✅ Contraseña reseteada para $nombre_cliente\\nNueva contraseña: $nueva_password\\n\\nGuarde esta contraseña temporal.');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
            } else {
                echo "<script>
                    alert('Error al resetear contraseña');
                    window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
                </script>";
            }
        } else {
            echo "<script>
                alert('Solo el Administrador General puede resetear contraseñas.');
                window.location.href = 'client-dashboard-index-watch.php?id=$id_cliente';
            </script>";
        }
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
