<?php
session_start();
require "../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

// Obtener datos actuales del negocio
$sql = "SELECT * FROM vendedor WHERE id_vendedor = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$negocio = $stmt->get_result()->fetch_assoc();

if (!$negocio) {
    header("Location: seller-apart-create-bussiness.php");
    exit;
}

// Procesar actualización
$mensaje = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre_empresa = $_POST['nombre_empresa'];
    $nit = $_POST['nit'];
    $telefono = $_POST['telefono'];
    $ubicacion = $_POST['ubicacion'];
    $correo_contacto = $_POST['correo_contacto'];
    
    $sqlUpdate = "UPDATE vendedor SET 
                  nombre_empresa = ?,
                  nit = ?,
                  telefono_contacto = ?,
                  ubicacion = ?,
                  correo_contacto = ?
                  WHERE id_vendedor = ?";
    
    $stmtUpdate = $connect->prepare($sqlUpdate);
    $stmtUpdate->bind_param("sssssi", $nombre_empresa, $nit, $telefono, $ubicacion, $correo_contacto, $idUsuario);
    
    if ($stmtUpdate->execute()) {
        $mensaje = '<div class="success">Información actualizada exitosamente</div>';
        // Actualizar datos locales
        $negocio['nombre_empresa'] = $nombre_empresa;
        $negocio['nit'] = $nit;
        $negocio['telefono_contacto'] = $telefono;
        $negocio['ubicacion'] = $ubicacion;
        $negocio['correo_contacto'] = $correo_contacto;
    } else {
        $mensaje = '<div class="error">Error al actualizar: ' . $connect->error . '</div>';
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Negocio</title>
    <style>
        .form-container {
            max-width: 600px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
        }
        
        input {
            width: 100%;
            padding: 12px;
            border: 2px solid #d1d5db;
            border-radius: 6px;
        }
        
        .btn-submit {
            background: #3b82f6;
            color: white;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }
        
        .success { background: #d1fae5; color: #065f46; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
        .error { background: #fee2e2; color: #991b1b; padding: 15px; border-radius: 8px; margin-bottom: 20px; }
    </style>
</head>
<body>
    <?php include 'seller-apart-manage-bussiness.php'; ?>
    
    <div class="main-content">
        <div class="form-container">
            <h2><i class="fas fa-edit"></i> Editar Información del Negocio</h2>
            
            <?php echo $mensaje; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Nombre de la Empresa *</label>
                    <input type="text" name="nombre_empresa" value="<?php echo htmlspecialchars($negocio['nombre_empresa']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>NIT *</label>
                    <input type="text" name="nit" value="<?php echo htmlspecialchars($negocio['nit']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Teléfono de Contacto *</label>
                    <input type="text" name="telefono" value="<?php echo htmlspecialchars($negocio['telefono_contacto']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Ubicación *</label>
                    <input type="text" name="ubicacion" value="<?php echo htmlspecialchars($negocio['ubicacion']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Correo de Contacto</label>
                    <input type="email" name="correo_contacto" value="<?php echo htmlspecialchars($negocio['correo_contacto']); ?>">
                </div>
                
                <button type="submit" class="btn-submit">Guardar Cambios</button>
            </form>
        </div>
    </div>
</body>
</html>