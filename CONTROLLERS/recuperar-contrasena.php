<?php
session_start();
require_once '../shortCuts/connect.php';

// Página principal que maneja todo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_recuperacion'])) {
    // Procesar el cambio final de contraseña
    
    if (!isset($_SESSION['recuperacion_data']) || !isset($_SESSION['codigo_verificado'])) {
        header('Location: solicitar-recuperacion.php');
        exit;
    }
    
    $nueva_contrasena = $_POST['nueva_contrasena'];
    $confirmar_contrasena = $_POST['confirmar_contrasena'];
    $data = $_SESSION['recuperacion_data'];
    
    // Validaciones
    if (strlen($nueva_contrasena) < 8) {
        $_SESSION['error_contrasena'] = 'La contraseña debe tener al menos 8 caracteres';
        header('Location: cambiar-contrasena.php');
        exit;
    }
    
    if ($nueva_contrasena !== $confirmar_contrasena) {
        $_SESSION['error_contrasena'] = 'Las contraseñas no coinciden';
        header('Location: cambiar-contrasena.php');
        exit;
    }
    
    // Usar $connect (MySQLi)
    global $connect;
    
    try {
        // Hashear la nueva contraseña
        $contrasena_hash = password_hash($nueva_contrasena, PASSWORD_DEFAULT);
        
        if ($data['tipo'] === 'usuario') {
            // Actualizar contraseña para usuario - MySQLi
            $stmt = $connect->prepare("UPDATE usuario SET 
                                     contrasena = ?, 
                                     codigo_recuperacion = NULL, 
                                     codigo_expira = NULL 
                                     WHERE id_usuario = ?");
            $stmt->bind_param("si", $contrasena_hash, $data['id_usuario']);
            $stmt->execute();
            $stmt->close();
            
            // Registrar actividad - MySQLi
            $actividad = 'Cambio de contraseña mediante recuperación';
            $stmt = $connect->prepare("INSERT INTO actividades_usuario (id_usuario, actividad) VALUES (?, ?)");
            $stmt->bind_param("is", $data['id_usuario'], $actividad);
            $stmt->execute();
            $stmt->close();
            
        } else {
            // Actualizar para administrador - MySQLi
            $stmt = $connect->prepare("UPDATE administradores SET 
                                     password = ? 
                                     WHERE id_admin = ?");
            $stmt->bind_param("si", $contrasena_hash, $data['id_usuario']);
            $stmt->execute();
            $stmt->close();
        }
        
        // Limpiar sesiones
        session_destroy();
        session_start();
        
        $_SESSION['message'] = '✅ ¡Contraseña cambiada exitosamente! Ya puedes iniciar sesión con tu nueva contraseña.';
        $_SESSION['message_type'] = 'success';
        
        // Redirigir a login
        header('Location: ../login.php');
        exit;
        
    } catch (Exception $e) {
        $_SESSION['error_contrasena'] = '❌ Error al cambiar la contraseña: ' . $e->getMessage();
        header('Location: cambiar-contrasena.php');
        exit;
    }
}

// Si llega por GET, mostrar formulario inicial
header('Location: solicitar-recuperacion.php');
exit;
?>