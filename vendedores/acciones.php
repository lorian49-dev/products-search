<?php
session_start();
include('../registros-inicio-sesion/connect.php');

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
$id_vendedor = isset($_GET['id']) ? intval($_GET['id']) : 0;

if (!$id_vendedor || !$action) {
    header('Location: index.php');
    exit();
}

// Verificar que el vendedor existe
$query_check = "SELECT v.*, u.nombre, u.apellido 
               FROM vendedor v 
               INNER JOIN usuario u ON v.id_vendedor = u.id_usuario 
               WHERE v.id_vendedor = $id_vendedor";
$result_check = mysqli_query($connect, $query_check);

if (mysqli_num_rows($result_check) === 0) {
    echo "<script>
        alert('Vendedor no encontrado.');
        window.location.href = 'index.php';
    </script>";
    exit();
}

$vendedor = mysqli_fetch_assoc($result_check);
$nombre_vendedor = htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']);

switch ($action) {
    case 'limpiar_catalogos':
        // Vaciar catálogos del vendedor
        $query_catalogos = "SELECT id_catalogo FROM catalogo WHERE id_vendedor = $id_vendedor";
        $result_catalogos = mysqli_query($connect, $query_catalogos);
        $total_catalogos = mysqli_num_rows($result_catalogos);
        
        if ($total_catalogos > 0) {
            mysqli_begin_transaction($connect);
            try {
                // Eliminar productos de catálogo
                while ($catalogo = mysqli_fetch_assoc($result_catalogos)) {
                    $id_catalogo = $catalogo['id_catalogo'];
                    $query_delete = "DELETE FROM catalogo_producto WHERE id_catalogo = $id_catalogo";
                    mysqli_query($connect, $query_delete);
                }
                
                // Eliminar catálogos
                $query_delete_catalogos = "DELETE FROM catalogo WHERE id_vendedor = $id_vendedor";
                mysqli_query($connect, $query_delete_catalogos);
                
                // Registrar log
                $admin_id = $_SESSION['admin_id'];
                $admin_nombre = $_SESSION['admin_nombre'];
                $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'LIMPIAR_CATALOGOS', 'Catálogos del vendedor #$id_vendedor eliminados', NOW())";
                mysqli_query($connect, $query_log);
                
                mysqli_commit($connect);
                
                echo "<script>
                    alert('✅ Se eliminaron $total_catalogos catálogo(s) del vendedor $nombre_vendedor');
                    window.location.href = 'ver.php?id=$id_vendedor';
                </script>";
                
            } catch (Exception $e) {
                mysqli_rollback($connect);
                echo "<script>
                    alert('Error al eliminar catálogos: " . addslashes($e->getMessage()) . "');
                    window.location.href = 'ver.php?id=$id_vendedor';
                </script>";
            }
        } else {
            echo "<script>
                alert('El vendedor no tiene catálogos.');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        }
        break;
        
    case 'activar_terminos':
        // Marcar como aceptó términos
        $query_update = "UPDATE vendedor SET acepto_terminos = 1 WHERE id_vendedor = $id_vendedor";
        
        if (mysqli_query($connect, $query_update)) {
            // Registrar log
            $admin_id = $_SESSION['admin_id'];
            $admin_nombre = $_SESSION['admin_nombre'];
            $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'ACTIVAR_TERMINOS', 'Vendedor #$id_vendedor aceptó términos', NOW())";
            mysqli_query($connect, $query_log);
            
            echo "<script>
                alert('✅ Términos y condiciones activados para $nombre_vendedor');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        } else {
            echo "<script>
                alert('Error al activar términos');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        }
        break;
        
    case 'desactivar_terminos':
        // Marcar como no aceptó términos
        $query_update = "UPDATE vendedor SET acepto_terminos = 0 WHERE id_vendedor = $id_vendedor";
        
        if (mysqli_query($connect, $query_update)) {
            // Registrar log
            $admin_id = $_SESSION['admin_id'];
            $admin_nombre = $_SESSION['admin_nombre'];
            $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                         VALUES ($admin_id, '$admin_nombre', 'DESACTIVAR_TERMINOS', 'Vendedor #$id_vendedor rechazó términos', NOW())";
            mysqli_query($connect, $query_log);
            
            echo "<script>
                alert('✅ Términos y condiciones desactivados para $nombre_vendedor');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        } else {
            echo "<script>
                alert('Error al desactivar términos');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        }
        break;
        
    case 'reset_password':
        // Resetear contraseña del usuario
        if ($_SESSION['admin_rol'] == 1) {
            $nueva_password = bin2hex(random_bytes(8)); // Contraseña temporal
            $password_hash = password_hash($nueva_password, PASSWORD_DEFAULT);
            
            $query_reset = "UPDATE usuario SET contrasena = '$password_hash' WHERE id_usuario = $id_vendedor";
            
            if (mysqli_query($connect, $query_reset)) {
                // Registrar log
                $admin_id = $_SESSION['admin_id'];
                $admin_nombre = $_SESSION['admin_nombre'];
                $query_log = "INSERT INTO logs_admin (admin_id, admin_nombre, accion, detalles, fecha) 
                             VALUES ($admin_id, '$admin_nombre', 'RESET_PASSWORD', 'Contraseña del vendedor #$id_vendedor reseteada', NOW())";
                mysqli_query($connect, $query_log);
                
                echo "<script>
                    alert('✅ Contraseña reseteada para $nombre_vendedor\\nNueva contraseña temporal: $nueva_password\\nRecomendamos que el usuario la cambie inmediatamente.');
                    window.location.href = 'ver.php?id=$id_vendedor';
                </script>";
            } else {
                echo "<script>
                    alert('Error al resetear contraseña');
                    window.location.href = 'ver.php?id=$id_vendedor';
                </script>";
            }
        } else {
            echo "<script>
                alert('No tiene permisos para resetear contraseñas.');
                window.location.href = 'ver.php?id=$id_vendedor';
            </script>";
        }
        break;
        
    default:
        echo "<script>
            alert('Acción no válida.');
            window.location.href = 'ver.php?id=$id_vendedor';
        </script>";
        break;
}
?>