<?php
session_start();
include('../registros-inicio-sesion/connect.php');

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    header('Location: ../home.php');
    exit();
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit();
}

$id_vendedor = intval($_GET['id']);
$mensaje = '';
$error = '';

// Obtener información actual del vendedor
$query = "SELECT v.*, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_nacimiento, u.direccion_principal
          FROM vendedor v
          INNER JOIN usuario u ON v.id_vendedor = u.id_usuario
          WHERE v.id_vendedor = $id_vendedor";
          
$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: index.php');
    exit();
}

$vendedor = mysqli_fetch_assoc($result);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion_principal = trim($_POST['direccion_principal']);
    $nombre_empresa = trim($_POST['nombre_empresa']);
    $nit = trim($_POST['nit']);
    $telefono_contacto = trim($_POST['telefono_contacto']);
    $ubicacion = trim($_POST['ubicacion']);
    $correo_contacto = trim($_POST['correo_contacto']);
    $acepto_terminos = isset($_POST['acepto_terminos']) ? 1 : 0;
    
    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Nombre, apellido y correo personal son obligatorios";
    } elseif (empty($nombre_empresa)) {
        $error = "El nombre de la empresa es obligatorio";
    } else {
        // Verificar si el correo ya existe en otro usuario
        $query_check = "SELECT id_usuario FROM usuario WHERE correo = '$correo' AND id_usuario != $id_vendedor";
        $result_check = mysqli_query($connect, $query_check);
        
        if (mysqli_num_rows($result_check) > 0) {
            $error = "El correo electrónico ya está registrado por otro usuario";
        } else {
            // Iniciar transacción
            mysqli_begin_transaction($connect);
            
            try {
                // Actualizar usuario
                $query_usuario = "UPDATE usuario SET 
                    nombre = '$nombre',
                    apellido = '$apellido',
                    correo = '$correo',
                    telefono = " . (!empty($telefono) ? "'$telefono'" : "NULL") . ",
                    fecha_nacimiento = " . (!empty($fecha_nacimiento) ? "'$fecha_nacimiento'" : "NULL") . ",
                    direccion_principal = " . (!empty($direccion_principal) ? "'$direccion_principal'" : "NULL") . "
                    WHERE id_usuario = $id_vendedor";
                
                if (!mysqli_query($connect, $query_usuario)) {
                    throw new Exception("Error al actualizar usuario: " . mysqli_error($connect));
                }
                
                // Actualizar vendedor
                $query_vendedor = "UPDATE vendedor SET 
                    nombre_empresa = '$nombre_empresa',
                    nit = " . (!empty($nit) ? "'$nit'" : "NULL") . ",
                    telefono_contacto = " . (!empty($telefono_contacto) ? "'$telefono_contacto'" : "NULL") . ",
                    ubicacion = " . (!empty($ubicacion) ? "'$ubicacion'" : "NULL") . ",
                    correo_contacto = " . (!empty($correo_contacto) ? "'$correo_contacto'" : "NULL") . ",
                    acepto_terminos = $acepto_terminos,
                    fecha_actualizacion = NOW()
                    WHERE id_vendedor = $id_vendedor";
                
                if (!mysqli_query($connect, $query_vendedor)) {
                    throw new Exception("Error al actualizar vendedor: " . mysqli_error($connect));
                }
                
                // Confirmar transacción
                mysqli_commit($connect);
                
                $mensaje = "Vendedor actualizado exitosamente";
                
                // Actualizar datos locales
                $vendedor['nombre'] = $nombre;
                $vendedor['apellido'] = $apellido;
                $vendedor['correo'] = $correo;
                $vendedor['telefono'] = $telefono;
                $vendedor['fecha_nacimiento'] = $fecha_nacimiento;
                $vendedor['direccion_principal'] = $direccion_principal;
                $vendedor['nombre_empresa'] = $nombre_empresa;
                $vendedor['nit'] = $nit;
                $vendedor['telefono_contacto'] = $telefono_contacto;
                $vendedor['ubicacion'] = $ubicacion;
                $vendedor['correo_contacto'] = $correo_contacto;
                $vendedor['acepto_terminos'] = $acepto_terminos;
                
            } catch (Exception $e) {
                mysqli_rollback($connect);
                $error = $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Vendedor #<?php echo $id_vendedor; ?> - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; padding: 20px; }
        .dashboard-container { max-width: 900px; margin: 0 auto; }
        
        /* Header */
        .header { background: rgba(255, 255, 255, 0.95); padding: 25px 30px; border-radius: 20px; margin-bottom: 25px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .header-top { display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px; }
        .header h1 { color: #333; font-size: 1.8em; margin-bottom: 5px; }
        .user-role { background: #667eea; color: white; padding: 4px 12px; border-radius: 20px; font-size: 0.85em; font-weight: bold; }
        
        /* Botones */
        .btn { padding: 10px 20px; border: none; border-radius: 25px; font-size: 0.95em; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 8px; transition: all 0.3s ease; }
        .btn-primary { background: #667eea; color: white; } .btn-primary:hover { background: #5a6fd8; transform: translateY(-2px); }
        .btn-success { background: #28a745; color: white; } .btn-success:hover { background: #218838; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; color: white; } .btn-secondary:hover { background: #5a6268; transform: translateY(-2px); }
        .btn-danger { background: #dc3545; color: white; } .btn-danger:hover { background: #c82333; transform: translateY(-2px); }
        
        /* Formulario */
        .form-container { background: rgba(255, 255, 255, 0.95); padding: 30px; border-radius: 15px; box-shadow: 0 5px 15px rgba(0,0,0,0.1); margin-bottom: 25px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 600; color: #555; font-size: 0.9em; }
        .form-control { width: 100%; padding: 12px 15px; border: 1px solid #ddd; border-radius: 8px; font-size: 0.95em; transition: border 0.3s ease; }
        .form-control:focus { outline: none; border-color: #667eea; box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 20px; }
        .form-check { display: flex; align-items: center; gap: 10px; }
        .form-check input[type="checkbox"] { width: 18px; height: 18px; }
        
        /* Alertas */
        .alert { padding: 15px; border-radius: 8px; margin-bottom: 20px; display: flex; align-items: center; gap: 10px; }
        .alert-success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alert-danger { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        
        /* Información del vendedor */
        .client-info { display: flex; align-items: center; gap: 20px; margin-bottom: 20px; padding-bottom: 20px; border-bottom: 2px solid #eee; }
        .client-avatar { width: 60px; height: 60px; background: #28a745; color: white; border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 1.5em; font-weight: bold; }
        .client-details h3 { color: #333; margin-bottom: 5px; }
        .client-details p { color: #666; font-size: 0.9em; }
        
        /* Campos requeridos */
        .required { color: #dc3545; margin-left: 3px; }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row { grid-template-columns: 1fr; gap: 0; }
            .header-top { flex-direction: column; gap: 15px; text-align: center; }
            .client-info { flex-direction: column; text-align: center; }
        }
    </style>
</head>
<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1>Editar Vendedor</h1>
                    <div class="user-role">
                        <?php 
                            if ($_SESSION['admin_rol'] == 1) echo 'Administrador General';
                            elseif ($_SESSION['admin_rol'] == 2) echo 'Administrador Colaborador'; 
                            else echo 'Administrador';
                        ?>
                    </div>
                </div>
                <div>
                    <a href="ver.php?id=<?php echo $id_vendedor; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <a href="index.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lista de Vendedores
                    </a>
                </div>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Modifique la información del vendedor #<?php echo $id_vendedor; ?>
            </p>
        </div>

        <!-- Información del vendedor -->
        <div class="form-container">
            <div class="client-info">
                <div class="client-avatar">
                    <?php echo strtoupper(substr($vendedor['nombre'], 0, 1)); ?>
                </div>
                <div class="client-details">
                    <h3><?php echo htmlspecialchars($vendedor['nombre'] . ' ' . $vendedor['apellido']); ?></h3>
                    <p>Vendedor ID: #<?php echo $id_vendedor; ?> | Empresa: <?php echo htmlspecialchars($vendedor['nombre_empresa']); ?></p>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if($mensaje): ?>
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
            </div>
            <?php endif; ?>
            
            <?php if($error): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
            </div>
            <?php endif; ?>

            <!-- Formulario de edición -->
            <form method="POST" action="">
                <h3 style="color: #667eea; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-user"></i> Información Personal
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['nombre']); ?>" 
                               required maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['apellido']); ?>" 
                               required maxlength="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="correo">Correo Electrónico <span class="required">*</span></label>
                        <input type="email" id="correo" name="correo" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['correo']); ?>" 
                               required maxlength="100">
                        <small style="color: #666; font-size: 0.85em;">El correo es único para cada usuario</small>
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono">Teléfono Personal</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['telefono'] ?? ''); ?>" 
                               maxlength="15">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control" 
                               value="<?php echo !empty($vendedor['fecha_nacimiento']) && $vendedor['fecha_nacimiento'] != '0000-00-00' ? $vendedor['fecha_nacimiento'] : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="direccion_principal">Dirección Principal</label>
                        <input type="text" id="direccion_principal" name="direccion_principal" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['direccion_principal'] ?? ''); ?>" 
                               maxlength="255">
                    </div>
                </div>

                <h3 style="color: #667eea; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-building"></i> Información de la Empresa
                </h3>

                <div class="form-group">
                    <label for="nombre_empresa">Nombre de la Empresa <span class="required">*</span></label>
                    <input type="text" id="nombre_empresa" name="nombre_empresa" class="form-control" 
                           value="<?php echo htmlspecialchars($vendedor['nombre_empresa']); ?>" 
                           required maxlength="150">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nit">NIT</label>
                        <input type="text" id="nit" name="nit" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['nit'] ?? ''); ?>" 
                               maxlength="50">
                    </div>
                    
                    <div class="form-group">
                        <label for="telefono_contacto">Teléfono de Contacto Empresarial</label>
                        <input type="tel" id="telefono_contacto" name="telefono_contacto" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['telefono_contacto'] ?? ''); ?>" 
                               maxlength="20">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="ubicacion">Ubicación</label>
                        <input type="text" id="ubicacion" name="ubicacion" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['ubicacion'] ?? ''); ?>" 
                               maxlength="255">
                    </div>
                    
                    <div class="form-group">
                        <label for="correo_contacto">Correo de Contacto Empresarial</label>
                        <input type="email" id="correo_contacto" name="correo_contacto" class="form-control" 
                               value="<?php echo htmlspecialchars($vendedor['correo_contacto'] ?? ''); ?>" 
                               maxlength="150">
                    </div>
                </div>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="acepto_terminos" name="acepto_terminos" 
                               value="1" <?php echo ($vendedor['acepto_terminos'] == 1) ? 'checked' : ''; ?>>
                        <label for="acepto_terminos">Aceptó Términos y Condiciones</label>
                    </div>
                    <small style="color: #666; font-size: 0.85em; margin-left: 28px;">
                        Marque si el vendedor aceptó los términos y condiciones de la plataforma
                    </small>
                </div>

                <!-- Información de sistema (solo lectura) -->
                <h3 style="color: #667eea; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h3>
                
                <div class="form-row">
                    <div class="form-group">
                        <label>Fecha de Registro</label>
                        <input type="text" class="form-control" 
                               value="<?php 
                                   if (!empty($vendedor['fecha_registro']) && $vendedor['fecha_registro'] != '0000-00-00 00:00:00') {
                                       echo date('d/m/Y H:i', strtotime($vendedor['fecha_registro']));
                                   } else {
                                       echo 'No registrada';
                                   }
                               ?>" 
                               readonly style="background: #f8f9fa;">
                    </div>
                    
                    <div class="form-group">
                        <label>Última Actualización</label>
                        <input type="text" class="form-control" 
                               value="<?php echo date('d/m/Y H:i'); ?>" 
                               readonly style="background: #f8f9fa;">
                    </div>
                </div>

                <!-- Botones de acción -->
                <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 30px;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="ver.php?id=<?php echo $id_vendedor; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a href="eliminar.php?id=<?php echo $id_vendedor; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Vendedor
                    </a>
                </div>
            </form>
        </div>

        <!-- Advertencia -->
        <div style="background: #fff3cd; color: #856404; padding: 15px; border-radius: 10px; margin-top: 20px; border-left: 4px solid #ffc107;">
            <h4><i class="fas fa-exclamation-triangle"></i> Notas importantes</h4>
            <ul style="margin-left: 20px; margin-top: 10px; font-size: 0.9em;">
                <li>Los cambios se aplicarán inmediatamente después de guardar</li>
                <li>El correo electrónico debe ser único en el sistema</li>
                <li>La información de empresa es visible para los clientes</li>
                <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
            </ul>
        </div>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista de Vendedores
            </a>
            <a href="../index.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        // Validación de formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const correoInput = document.getElementById('correo');
            const empresaInput = document.getElementById('nombre_empresa');
            
            form.addEventListener('submit', function(e) {
                // Validar campos obligatorios
                if (!correoInput.value.trim()) {
                    e.preventDefault();
                    alert('El correo electrónico es obligatorio');
                    correoInput.focus();
                    return false;
                }
                
                if (!empresaInput.value.trim()) {
                    e.preventDefault();
                    alert('El nombre de la empresa es obligatorio');
                    empresaInput.focus();
                    return false;
                }
                
                // Validar formato de correo
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(correoInput.value)) {
                    e.preventDefault();
                    alert('Por favor ingrese un correo electrónico válido');
                    correoInput.focus();
                    return false;
                }
                
                // Validar correo de contacto si está lleno
                const correoContacto = document.getElementById('correo_contacto').value;
                if (correoContacto && !emailRegex.test(correoContacto)) {
                    e.preventDefault();
                    alert('Por favor ingrese un correo de contacto válido');
                    document.getElementById('correo_contacto').focus();
                    return false;
                }
                
                // Confirmar antes de enviar
                if (!confirm('¿Guardar los cambios realizados al vendedor?')) {
                    e.preventDefault();
                    return false;
                }
            });
            
            // Validar fecha de nacimiento (no puede ser futura)
            const fechaNacimiento = document.getElementById('fecha_nacimiento');
            if (fechaNacimiento) {
                fechaNacimiento.addEventListener('change', function() {
                    const fecha = new Date(this.value);
                    const hoy = new Date();
                    
                    if (fecha > hoy) {
                        alert('La fecha de nacimiento no puede ser futura');
                        this.value = '';
                    }
                });
            }
        });
    </script>
</body>
</html>