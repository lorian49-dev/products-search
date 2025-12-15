<?php
session_start();
require_once '../shortCuts/connect.php';

// Activar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: solicitar-recuperacion.php');
    exit;
}

$correo = trim($_POST['correo']);

// Validar email
if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['message'] = 'Correo electrónico no válido';
    $_SESSION['message_type'] = 'error';
    header('Location: solicitar-recuperacion.php');
    exit;
}

global $connect;

try {
    // Buscar en tabla 'usuario' (clientes/vendedores)
    $stmt = $connect->prepare("SELECT id_usuario, nombre, apellido FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $result = $stmt->get_result();
    $usuario = $result->fetch_assoc();
    $stmt->close();
    
    if (!$usuario) {
        // Buscar en administradores
        $stmt = $connect->prepare("SELECT id_admin, username, email FROM administradores WHERE email = ?");
        $stmt->bind_param("s", $correo);
        $stmt->execute();
        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();
        $stmt->close();
        
        if (!$admin) {
            $_SESSION['message'] = 'No existe una cuenta con ese correo electrónico';
            $_SESSION['message_type'] = 'error';
            header('Location: solicitar-recuperacion.php');
            exit;
        }
        
        $tipo = 'admin';
        $id_usuario = $admin['id_admin'];
        $nombre = $admin['username'] ?: 'Usuario';
    } else {
        $tipo = 'usuario';
        $id_usuario = $usuario['id_usuario'];
        $nombre = $usuario['nombre'] . ' ' . $usuario['apellido'];
    }
    
    // Generar código de 6 dígitos
    $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);
    
    // Establecer expiración (15 minutos)
    $expiracion = date('Y-m-d H:i:s', strtotime('+15 minutes'));
    
    if ($tipo === 'usuario') {
        // Para usuarios normales
        $stmt = $connect->prepare("UPDATE usuario SET 
                                 codigo_recuperacion = ?, 
                                 codigo_expira = ? 
                                 WHERE id_usuario = ?");
        $stmt->bind_param("ssi", $codigo, $expiracion, $id_usuario);
        $stmt->execute();
        $stmt->close();
    } else {
        // Para administradores - guardar en sesión temporal
        $_SESSION['codigo_recuperacion_admin'] = [
            'codigo' => $codigo,
            'expiracion' => $expiracion,
            'id_admin' => $id_usuario
        ];
    }
    
    // Guardar datos en sesión
    $_SESSION['recuperacion_data'] = [
        'correo' => $correo,
        'tipo' => $tipo,
        'id_usuario' => $id_usuario,
        'nombre' => $nombre,
        'codigo_enviado' => $codigo
    ];
    
    // ===== PANTALLA DE DESARROLLO CON CÓDIGO VISIBLE =====
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Código Generado - Hermes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
        }
        .dev-banner {
            background: #ff4757;
            color: white;
            padding: 15px;
            text-align: center;
            font-weight: bold;
            border-radius: 10px 10px 0 0;
            margin-bottom: 0;
        }
        .container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 500px;
            width: 100%;
        }
        .content {
            padding: 30px;
        }
        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 20px;
        }
        .code-display {
            background: #f8f9fa;
            border: 2px dashed #667eea;
            border-radius: 10px;
            padding: 25px;
            text-align: center;
            margin: 20px 0;
        }
        .code {
            font-size: 48px;
            font-weight: bold;
            color: #ff4757;
            letter-spacing: 10px;
            font-family: monospace;
        }
        .info-box {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin: 15px 0;
        }
        .info-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .label {
            color: #666;
            font-weight: bold;
        }
        .value {
            color: #333;
        }
        .btn {
            display: block;
            width: 100%;
            padding: 15px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            margin-top: 20px;
            text-decoration: none;
            text-align: center;
        }
        .btn:hover {
            background: #218838;
        }
        .warning {
            background: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px;
            margin: 15px 0;
            font-size: 14px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="dev-banner">
            🚧 MODO DESARROLLO - Código visible en pantalla
        </div>
        
        <div class="content">
            <h2>Código de Recuperación Generado</h2>
            
            <div class="code-display">
                <div class="code"><?php echo $codigo; ?></div>
            </div>
            
            <div class="info-box">
                <div class="info-row">
                    <span class="label">📧 Correo:</span>
                    <span class="value"><?php echo htmlspecialchars($correo); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">👤 Nombre:</span>
                    <span class="value"><?php echo htmlspecialchars($nombre); ?></span>
                </div>
                <div class="info-row">
                    <span class="label">⏰ Expira:</span>
                    <span class="value"><?php echo $expiracion; ?></span>
                </div>
            </div>
            
            <div class="warning">
                ⚠️ En producción, este código se enviaría por correo automáticamente.
            </div>
            
            <a href="verificar-codigo.php" class="btn">
                ✅ Continuar al formulario de verificación
            </a>
            
            <script>
                // Mostrar en consola
                console.log('🔐 CÓDIGO DE RECUPERACIÓN');
                console.log('Correo: <?php echo $correo; ?>');
                console.log('Código: <?php echo $codigo; ?>');
                console.log('Expira: <?php echo $expiracion; ?>');
                
                // Copiar al portapapeles automáticamente
                navigator.clipboard.writeText('<?php echo $codigo; ?>').then(() => {
                    console.log('📋 Código copiado al portapapeles');
                });
            </script>
        </div>
    </div>
</body>
</html>
<?php
    exit; // Salir para mostrar la página
    
} catch (Exception $e) {
    $_SESSION['message'] = 'Error en el sistema: ' . $e->getMessage();
    $_SESSION['message_type'] = 'error';
    header('Location: solicitar-recuperacion.php');
    exit;
}
?>