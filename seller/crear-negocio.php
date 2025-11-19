<?php
session_start();

// -- 1) Verificar sesión de usuario --
if (!isset($_SESSION['usuario_id'])) {
    // Si no hay sesión, redirigir al login
    header("Location: ../registros-inicio-sesion/login.html");
    exit();
}

$id_usuario = intval($_SESSION['usuario_id']); // id del usuario logueado

// -- 2) Conexión a la BD (ajusta credenciales si es necesario) --
$conexion = new mysqli("localhost", "root", "", "hermes_bd");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// -- 3) Si se recibió el formulario, procesar --
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["registrar_negocio"])) {

    // datos del formulario (sanitiza si quieres)
    $nombre = trim($_POST['nombre_empresa'] ?? '');
    $NIT = trim($_POST['nit'] ?? '');
    $telefono = trim($_POST['telefono_contacto'] ?? '');
    $ubicacion = trim($_POST['ubicacion'] ?? '');
    $correo = trim($_POST['correo_contacto'] ?? '');
    $fecha_reg = trim($_POST['fecha_registro'] ?? '');

    // validación mínima
    if ($nombre === '' || $NIT === '' || $telefono === '' || $correo === '' || $fecha_reg === '') {
        $error_msg = "Por favor completa todos los campos obligatorios.";
    } else {
        // 3.1) Comprobar si ya existe un registro de vendedor para este usuario
        $checkStmt = $conexion->prepare("SELECT id_vendedor FROM vendedor WHERE id_vendedor = ?");
        $checkStmt->bind_param("i", $id_usuario);
        $checkStmt->execute();
        $res = $checkStmt->get_result();

        if ($res && $res->num_rows > 0) {
            // Ya existe → hacemos UPDATE (evita duplicados)
            $updateStmt = $conexion->prepare("UPDATE vendedor SET nombre_empresa = ?, nit = ?, telefono_contacto = ?, ubicacion = ?, correo_contacto = ?, fecha_registro = ? WHERE id_vendedor = ?");
            $updateStmt->bind_param("ssssssi", $nombre, $NIT, $telefono, $ubicacion, $correo, $fecha_reg, $id_usuario);
            if ($updateStmt->execute()) {
                // éxito al actualizar
                header("Location: dashboard-vendedor.php");
                exit();
            } else {
                $error_msg = "Error al actualizar: " . $conexion->error;
            }
            $updateStmt->close();
        } else {
            // No existe → hacemos INSERT (usamos id_vendedor = id_usuario para respetar FK)
            $insertStmt = $conexion->prepare("INSERT INTO vendedor (id_vendedor, nombre_empresa, nit, telefono_contacto, ubicacion, correo_contacto, fecha_registro) VALUES (?, ?, ?, ?, ?, ?, ?)");
            $insertStmt->bind_param("issssss", $id_usuario, $nombre, $NIT, $telefono, $ubicacion, $correo, $fecha_reg);
            if ($insertStmt->execute()) {
                // éxito al insertar
                header("Location: dashboard-vendedor.php");
                exit();
            } else {
                $error_msg = "Error al insertar: " . $conexion->error;
            }
            $insertStmt->close();
        }
        $checkStmt->close();
    }
}

// Cerrar conexión al final (se cierra luego del HTML si quieres)
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Crear Negocio</title>
    <style>
        /* estilos simples (puedes usar los tuyos) */
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }

        .container {
            width: 420px;
            margin: 40px auto;
            background: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }

        label {
            font-weight: bold;
        }

        input,
        textarea,
        select {
            width: 100%;
            padding: 8px;
            margin: 8px 0 14px 0;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .btn {
            background: #007bff;
            color: #fff;
            padding: 12px;
            border: none;
            width: 100%;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn:hover {
            background: #0056b3;
        }

        .notice {
            color: #b00;
            text-align: center;
            margin-bottom: 10px;
        }

        .back {
            display: block;
            text-align: center;
            margin-top: 12px;
            background: #666;
            color: #fff;
            text-decoration: none;
            padding: 10px;
            border-radius: 6px;
        }
    </style>
</head>

<body>

    <div class="container">
        <h2>Registrar / Actualizar Negocio</h2>

        <?php if (!empty($error_msg)): ?>
            <p class="notice"><?php echo htmlspecialchars($error_msg); ?></p>
        <?php endif; ?>

        <form method="POST" novalidate>
            <label for="nombre_empresa">Nombre de la Empresa</label>
            <input id="nombre_empresa" name="nombre_empresa" required value="<?php echo isset($nombre) ? htmlspecialchars($nombre) : ''; ?>">

            <label for="nit">NIT</label>
            <input id="nit" name="nit" required value="<?php echo isset($NIT) ? htmlspecialchars($NIT) : ''; ?>">

            <label for="telefono_contacto">Teléfono de Contacto</label>
            <input id="telefono_contacto" name="telefono_contacto" required value="<?php echo isset($telefono) ? htmlspecialchars($telefono) : ''; ?>">

            <label for="ubicacion">Ubicación</label>
            <input id="ubicacion" name="ubicacion" required value="<?php echo isset($ubicacion) ? htmlspecialchars($ubicacion) : ''; ?>">

            <label for="correo_contacto">Correo de Contacto</label>
            <input id="correo_contacto" type="email" name="correo_contacto" required value="<?php echo isset($correo) ? htmlspecialchars($correo) : ''; ?>">

            <label for="fecha_registro">Fecha de Registro</label>
            <input id="fecha_registro" type="date" name="fecha_registro" required value="<?php echo isset($fecha_reg) ? htmlspecialchars($fecha_reg) : ''; ?>">

            <div style="display:flex;align-items:center;gap:8px;margin-bottom:12px;">
                <input id="acepto" type="checkbox" required>
                <label for="acepto">Acepto términos y condiciones</label>
            </div>

            <button class="btn" type="submit" name="registrar_negocio">Guardar negocio</button>
        </form>

        <a class="back" href="dashboard-vendedor.php">⟵ Volver al panel</a>
    </div>

</body>

</html>
<?php
$conexion->close();
?>