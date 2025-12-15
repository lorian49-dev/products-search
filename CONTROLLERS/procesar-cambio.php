<?php
session_start();
require_once '../shortCuts/connect.php';

// Verificar que venga de proceso válido
if (!isset($_SESSION['recuperacion_data'])) {
    header('Location: solicitar-recuperacion.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: verificar-codigo.php');
    exit;
}

$codigo_ingresado = trim($_POST['codigo']);
$data = $_SESSION['recuperacion_data'];

// Usar $connect (MySQLi)
global $connect;

try {
    if ($data['tipo'] === 'usuario') {
        // Verificar código para usuario normal - MySQLi
        $stmt = $connect->prepare("SELECT id_usuario, codigo_expira 
                                  FROM usuario 
                                  WHERE id_usuario = ? 
                                  AND codigo_recuperacion = ?");
        $stmt->bind_param("is", $data['id_usuario'], $codigo_ingresado);
        $stmt->execute();
        $result = $stmt->get_result();
        $resultado = $result->fetch_assoc();
        $stmt->close();
        
        if (!$resultado) {
            $_SESSION['error_codigo'] = 'Código incorrecto';
            header('Location: verificar-codigo.php');
            exit;
        }
        
        // Verificar expiración
        if (strtotime($resultado['codigo_expira']) < time()) {
            $_SESSION['error_codigo'] = 'El código ha expirado. Solicita uno nuevo.';
            header('Location: verificar-codigo.php');
            exit;
        }
        
        // Guardar verificación exitosa
        $_SESSION['codigo_verificado'] = true;
        
    } else {
        // Para administradores (sesión temporal)
        if (!isset($_SESSION['codigo_recuperacion_admin']) ||
            $_SESSION['codigo_recuperacion_admin']['codigo'] !== $codigo_ingresado) {
            $_SESSION['error_codigo'] = 'Código incorrecto';
            header('Location: verificar-codigo.php');
            exit;
        }
        
        // Verificar expiración
        if (strtotime($_SESSION['codigo_recuperacion_admin']['expiracion']) < time()) {
            $_SESSION['error_codigo'] = 'El código ha expirado. Solicita uno nuevo.';
            header('Location: verificar-codigo.php');
            exit;
        }
        
        $_SESSION['codigo_verificado'] = true;
    }
    
    // Redirigir a formulario de nueva contraseña
    header('Location: cambiar-contrasena.php');
    exit;
    
} catch (Exception $e) {
    $_SESSION['error_codigo'] = 'Error en el sistema: ' . $e->getMessage();
    header('Location: verificar-codigo.php');
    exit;
}
?>