<?php
session_start();
include('../shortCuts/connect.php');

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
    header('Location: client-dashboard-index.php');
    exit();
}

$id_cliente = intval($_GET['id']);
$mensaje = '';
$error = '';

// Obtener información actual del cliente
$query = "SELECT c.*, u.nombre, u.apellido, u.correo, u.telefono, u.fecha_nacimiento, 
                 u.direccion_principal, u.codigo_recuperacion, u.codigo_expira
          FROM cliente c
          INNER JOIN usuario u ON c.id_cliente = u.id_usuario
          WHERE c.id_cliente = $id_cliente";

$result = mysqli_query($connect, $query);
if (mysqli_num_rows($result) === 0) {
    header('Location: client-dashboard-index.php');
    exit();
}

$cliente = mysqli_fetch_assoc($result);

// Procesar actualización
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $correo = trim($_POST['correo']);
    $telefono = trim($_POST['telefono']);
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $direccion_principal = trim($_POST['direccion_principal']);
    $wishlist_privada = isset($_POST['wishlist_privada']) ? 1 : 0;
    $informacion_adicional = trim($_POST['informacion_adicional']);

    // Validaciones
    if (empty($nombre) || empty($apellido) || empty($correo)) {
        $error = "Nombre, apellido y correo son obligatorios";
    } else {
        // Verificar si el correo ya existe en otro usuario
        $query_check = "SELECT id_usuario FROM usuario WHERE correo = '$correo' AND id_usuario != $id_cliente";
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
                    WHERE id_usuario = $id_cliente";

                if (!mysqli_query($connect, $query_usuario)) {
                    throw new Exception("Error al actualizar usuario: " . mysqli_error($connect));
                }

                // Actualizar cliente
                $query_cliente = "UPDATE cliente SET 
                    wishlist_privada = $wishlist_privada,
                    informacion_adicional = " . (!empty($informacion_adicional) ? "'$informacion_adicional'" : "NULL") . "
                    WHERE id_cliente = $id_cliente";

                if (!mysqli_query($connect, $query_cliente)) {
                    throw new Exception("Error al actualizar cliente: " . mysqli_error($connect));
                }

                // Confirmar transacción
                mysqli_commit($connect);

                $mensaje = "Cliente actualizado exitosamente";

                // Actualizar datos locales
                $cliente['nombre'] = $nombre;
                $cliente['apellido'] = $apellido;
                $cliente['correo'] = $correo;
                $cliente['telefono'] = $telefono;
                $cliente['fecha_nacimiento'] = $fecha_nacimiento;
                $cliente['direccion_principal'] = $direccion_principal;
                $cliente['wishlist_privada'] = $wishlist_privada;
                $cliente['informacion_adicional'] = $informacion_adicional;
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
    <title>Editar Cliente #<?php echo $id_cliente; ?> - Panel Administración</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="../styles/admin-create-delete-watch-user-crud.css">
    <style>
        /* Formulario */
        .form-container {
            background: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 25px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
            font-size: 0.9em;
        }

        .form-control {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 0.95em;
            transition: border 0.3s ease;
        }

        .form-control:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-check input[type="checkbox"] {
            width: 18px;
            height: 18px;
        }

        /* Alertas */
        .alert {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        /* Información del cliente */
        .client-info {
            display: flex;
            align-items: center;
            gap: 20px;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 2px solid #eee;
        }

        .client-avatar {
            width: 60px;
            height: 60px;
            background: #667eea;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5em;
            font-weight: bold;
        }

        .client-details h3 {
            color: #333;
            margin-bottom: 5px;
        }

        .client-details p {
            color: #666;
            font-size: 0.9em;
        }

        /* Campos requeridos */
        .required {
            color: #dc3545;
            margin-left: 3px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
                gap: 0;
            }

            .header-top {
                flex-direction: column;
                gap: 15px;
                text-align: center;
            }

            .client-info {
                flex-direction: column;
                text-align: center;
            }
        }
    </style>
</head>

<body>
    <div class="dashboard-container">
        <!-- Header -->
        <div class="header">
            <div class="header-top">
                <div>
                    <h1>Editar Cliente</h1>
                    <div class="user-role">
                        <?php
                        if ($_SESSION['admin_rol'] == 1) echo 'Administrador General';
                        elseif ($_SESSION['admin_rol'] == 2) echo 'Administrador Colaborador';
                        else echo 'Administrador';
                        ?>
                    </div>
                </div>
                <div>
                    <a href="client-dashboard-index-watch.php?id=<?php echo $id_cliente; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Cancelar
                    </a>
                    <a href="client-dashboard-index.php" class="btn btn-primary">
                        <i class="fas fa-list"></i> Lista de Clientes
                    </a>
                </div>
            </div>
            <p style="color: #666; font-size: 0.95em;">
                Modifique la información del cliente #<?php echo $id_cliente; ?>
            </p>
        </div>

        <!-- Información del cliente -->
        <div class="form-container">
            <div class="client-info">
                <div class="client-avatar">
                    <?php echo strtoupper(substr($cliente['nombre'], 0, 1)); ?>
                </div>
                <div class="client-details">
                    <h3><?php echo htmlspecialchars($cliente['nombre'] . ' ' . $cliente['apellido']); ?></h3>
                    <p>Cliente ID: #<?php echo $id_cliente; ?> | Usuario ID: #<?php echo $id_cliente; ?></p>
                </div>
            </div>

            <!-- Mensajes -->
            <?php if ($mensaje): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
                </div>
            <?php endif; ?>

            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Formulario de edición -->
            <form method="POST" action="">
                <h3 style="color: #667eea; margin-bottom: 20px; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-user-edit"></i> Información Personal
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label for="nombre">Nombre <span class="required">*</span></label>
                        <input type="text" id="nombre" name="nombre" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['nombre']); ?>"
                            required maxlength="50">
                    </div>

                    <div class="form-group">
                        <label for="apellido">Apellido <span class="required">*</span></label>
                        <input type="text" id="apellido" name="apellido" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['apellido']); ?>"
                            required maxlength="50">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="correo">Correo Electrónico <span class="required">*</span></label>
                        <input type="email" id="correo" name="correo" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['correo']); ?>"
                            required maxlength="100">
                        <small style="color: #666; font-size: 0.85em;">El correo es único para cada usuario</small>
                    </div>

                    <div class="form-group">
                        <label for="telefono">Teléfono</label>
                        <input type="tel" id="telefono" name="telefono" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['telefono'] ?? ''); ?>"
                            maxlength="15">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="fecha_nacimiento">Fecha de Nacimiento</label>
                        <input type="date" id="fecha_nacimiento" name="fecha_nacimiento" class="form-control"
                            value="<?php echo !empty($cliente['fecha_nacimiento']) && $cliente['fecha_nacimiento'] != '0000-00-00' ? $cliente['fecha_nacimiento'] : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="direccion_principal">Dirección Principal</label>
                        <input type="text" id="direccion_principal" name="direccion_principal" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['direccion_principal'] ?? ''); ?>"
                            maxlength="255">
                    </div>
                </div>

                <h3 style="color: #667eea; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-cog"></i> Configuración del Cliente
                </h3>

                <div class="form-group">
                    <div class="form-check">
                        <input type="checkbox" id="wishlist_privada" name="wishlist_privada"
                            value="1" <?php echo ($cliente['wishlist_privada'] == 1) ? 'checked' : ''; ?>>
                        <label for="wishlist_privada">Wishlist Privada</label>
                    </div>
                    <small style="color: #666; font-size: 0.85em; margin-left: 28px;">
                        Si está activada, la lista de deseos del cliente será privada
                    </small>
                </div>

                <div class="form-group">
                    <label for="informacion_adicional">Información Adicional</label>
                    <textarea id="informacion_adicional" name="informacion_adicional"
                        class="form-control" rows="4" maxlength="1000"
                        placeholder="Notas adicionales sobre el cliente..."><?php echo htmlspecialchars($cliente['informacion_adicional'] ?? ''); ?></textarea>
                    <small style="color: #666; font-size: 0.85em;">Información adicional para uso interno</small>
                </div>

                <!-- Información de sistema (solo lectura) -->
                <h3 style="color: #667eea; margin: 30px 0 20px 0; padding-bottom: 10px; border-bottom: 2px solid #eee;">
                    <i class="fas fa-info-circle"></i> Información del Sistema
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <label>Código de Recuperación</label>
                        <input type="text" class="form-control"
                            value="<?php echo htmlspecialchars($cliente['codigo_recuperacion'] ?? 'No generado'); ?>"
                            readonly style="background: #f8f9fa;">
                    </div>

                    <div class="form-group">
                        <label>Código Expira</label>
                        <input type="text" class="form-control"
                            value="<?php
                                    if (!empty($cliente['codigo_expira']) && $cliente['codigo_expira'] != '0000-00-00 00:00:00') {
                                        echo date('d/m/Y H:i', strtotime($cliente['codigo_expira']));
                                    } else {
                                        echo 'No aplica';
                                    }
                                    ?>"
                            readonly style="background: #f8f9fa;">
                    </div>
                </div>

                <!-- Botones de acción -->
                <div style="text-align: center; margin-top: 40px; padding-top: 20px; border-top: 2px solid #eee;">
                    <button type="submit" class="btn btn-success" style="padding: 12px 30px;">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                    <a href="client-dashboard-index-watch.php?id=<?php echo $id_cliente; ?>" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <a href="client-dashboard-index-delete.php?id=<?php echo $id_cliente; ?>" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Cliente
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
                <li>La información adicional es para uso administrativo interno</li>
                <li>Los campos marcados con <span class="required">*</span> son obligatorios</li>
            </ul>
        </div>

        <!-- Navegación -->
        <div style="text-align: center; margin-top: 30px;">
            <a href="client-dashboard-index.php" class="btn btn-primary">
                <i class="fas fa-arrow-left"></i> Volver a la Lista de Clientes
            </a>
            <a href="client-dashboard-index" class="btn btn-secondary">
                <i class="fas fa-home"></i> Dashboard Principal
            </a>
        </div>
    </div>

    <script>
        // Validación de formulario
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.querySelector('form');
            const correoInput = document.getElementById('correo');

            form.addEventListener('submit', function(e) {
                // Validar correo
                const correo = correoInput.value.trim();
                if (!correo) {
                    e.preventDefault();
                    alert('El correo electrónico es obligatorio');
                    correoInput.focus();
                    return false;
                }

                // Validar formato de correo
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(correo)) {
                    e.preventDefault();
                    alert('Por favor ingrese un correo electrónico válido');
                    correoInput.focus();
                    return false;
                }

                // Confirmar antes de enviar
                if (!confirm('¿Guardar los cambios realizados?')) {
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

            // Prevenir envío con Enter accidental
            document.querySelectorAll('input[type="text"], input[type="email"]').forEach(input => {
                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                    }
                });
            });
        });
    </script>
        <script src="../scripts/admin.js"></script>
</body>

</html>
