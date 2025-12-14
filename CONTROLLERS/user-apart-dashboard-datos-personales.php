<?php
session_start();
include("../shortCuts/connect.php");

// Verificar que el usuario haya iniciado sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión activa. Inicia sesión nuevamente.");
}

$usuario_id = intval($_SESSION['usuario_id']); // seguridad

// Obtener datos del usuario
$sql = "SELECT * FROM usuario WHERE id_usuario = $usuario_id";
$result = mysqli_query($connect, $sql);
if (!$result) {
    die("Error en la consulta: " . mysqli_error($connect));
}
$usuario = mysqli_fetch_assoc($result);

// OBTENER TODAS LAS DIRECCIONES DEL USUARIO desde tabla direcciones
$sqlDirecciones = "SELECT * FROM direcciones 
                   WHERE id_usuario = $usuario_id 
                   ORDER BY es_principal DESC, fecha_creacion DESC";
$resultDirecciones = mysqli_query($connect, $sqlDirecciones);

$direcciones = [];
if ($resultDirecciones && mysqli_num_rows($resultDirecciones) > 0) {
    while ($row = mysqli_fetch_assoc($resultDirecciones)) {
        $direcciones[] = $row;
    }
}

// Obtener dirección principal
$direccionPrincipal = null;
foreach ($direcciones as $dir) {
    if ($dir['es_principal'] == 1) {
        $direccionPrincipal = $dir;
        break;
    }
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .perfil-container {
            width: 90%;
            max-width: 1000px;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
        }

        .personal-info p {
            font-size: 16px;
            margin: 10px 0;
            padding: 8px 0;
            border-bottom: 1px solid #eee;
        }

        .principal {
            color: #28a745;
            font-weight: bold;
        }

        .direcciones-lista {
            margin-top: 30px;
        }

        .direccion-card {
            background: #f8f9fa;
            padding: 15px;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            margin-bottom: 15px;
            position: relative;
            transition: all 0.3s ease;
        }

        .direccion-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            transform: translateY(-2px);
        }

        .direccion-card.primary {
            border-left: 4px solid #28a745;
            background: #f0fff4;
        }

        .badge-primary {
            background: #28a745;
            color: #fff;
            padding: 4px 10px;
            font-size: 12px;
            border-radius: 20px;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .btn-sm {
            padding: 5px 12px;
            font-size: 14px;
            margin-right: 5px;
        }

        .btn-set-primary {
            background: #1976d2;
            color: white;
            border: none;
        }

        .btn-set-primary:hover {
            background: #1565c0;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
            border: none;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        .form-direccion {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-top: 20px;
        }

        .form-direccion .form-group {
            margin-bottom: 15px;
        }

        .form-direccion label {
            font-weight: 500;
            margin-bottom: 5px;
            color: #333;
        }

        .form-direccion input,
        .form-direccion textarea,
        .form-direccion select {
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            width: 100%;
        }

        .form-direccion input:focus,
        .form-direccion textarea:focus,
        .form-direccion select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        h2,
        h3 {
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #eee;
        }

        .empty-state {
            text-align: center;
            padding: 30px;
            color: #6c757d;
        }

        .address-info {
            margin-bottom: 10px;
        }

        .address-info p {
            margin: 3px 0;
        }

        .reference-text {
            font-size: 13px;
            color: #666;
            font-style: italic;
            margin-top: 5px;
        }

        .form-check-input:checked {
            background-color: #28a745;
            border-color: #28a745;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            padding: 10px 25px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .check-icon {
            color: #28a745;
            margin-right: 5px;
        }
    </style>
</head>

<body>
    <?php include '../TEMPLATES/header.php' ?>

    <section class="dashboard-menu">
        <ul>
            <li>
                <a href="user-apart-dashboard.php">
                    <i class="fa-solid fa-user"></i> Mi perfil
                    <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                </a>
            </li>
        </ul>
    </section>

    <div class="perfil-container">
        <h2><i class="fa-solid fa-user-circle"></i> Datos personales</h2>
        <div class="personal-info">
            <p><strong><i class="fa-solid fa-user"></i> Nombre completo:</strong> <?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido']) ?></p>
            <p><strong><i class="fa-solid fa-envelope"></i> Correo:</strong> <?= htmlspecialchars($usuario['correo']) ?></p>
            <p><strong><i class="fa-solid fa-phone"></i> Teléfono:</strong> <?= htmlspecialchars($usuario['telefono']) ?></p>
            <p><strong><i class="fa-solid fa-cake-candles"></i> Fecha de nacimiento:</strong> <?= htmlspecialchars($usuario['fecha_nacimiento']) ?></p>

            <?php if ($direccionPrincipal): ?>
                <p><strong><i class="fa-solid fa-home"></i> Dirección principal:</strong>
                    <span class="principal"><?= htmlspecialchars($direccionPrincipal['direccion']) ?></span>
                    <?php if ($direccionPrincipal['ciudad']): ?>
                        <br><small class="text-muted"><?= htmlspecialchars($direccionPrincipal['ciudad'] . ', ' . $direccionPrincipal['departamento']) ?></small>
                    <?php endif; ?>
                </p>
            <?php else: ?>
                <p><strong><i class="fa-solid fa-home"></i> Dirección principal:</strong>
                    <span class="text-muted">No hay dirección principal configurada</span>
                </p>
            <?php endif; ?>
        </div>

        <h3><i class="fa-solid fa-map-location-dot"></i> Todas tus direcciones</h3>
        <div class="direcciones-lista">
            <?php if (count($direcciones) > 0): ?>
                <?php foreach ($direcciones as $dir): ?>
                    <div class="direccion-card <?php echo $dir['es_principal'] == 1 ? 'primary' : ''; ?>">
                        <div class="address-info">
                            <p><strong><?php echo htmlspecialchars($dir['direccion']); ?></strong></p>
                            <?php if (!empty($dir['ciudad'])): ?>
                                <p><i class="fa-solid fa-city"></i> <?php echo htmlspecialchars($dir['ciudad'] . ', ' . $dir['departamento']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($dir['codigo_postal'])): ?>
                                <p><i class="fa-solid fa-mail-bulk"></i> Código Postal: <?php echo htmlspecialchars($dir['codigo_postal']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($dir['telefono'])): ?>
                                <p><i class="fa-solid fa-phone"></i> <?php echo htmlspecialchars($dir['telefono']); ?></p>
                            <?php endif; ?>

                            <?php if (!empty($dir['referencia'])): ?>
                                <p class="reference-text"><i class="fa-solid fa-info-circle"></i> <?php echo htmlspecialchars($dir['referencia']); ?></p>
                            <?php endif; ?>
                        </div>

                        <?php if ($dir['es_principal'] == 1): ?>
                            <span class="badge-primary"><i class="fa-solid fa-star"></i> Principal</span>
                        <?php else: ?>
                            <div class="mt-3">
                                <form action="user-apart-dashboard-cambiar-principal.php" method="POST" style="display: inline;">
                                    <input type="hidden" name="direccion_id" value="<?php echo $dir['id_direccion']; ?>">
                                    <button type="submit" class="btn btn-sm btn-set-primary">
                                        <i class="fa-solid fa-star"></i> Establecer como principal
                                    </button>
                                </form>
                                <form action="user-apart-dashboard-eliminar-direccion.php" method="POST" style="display: inline;"
                                    onsubmit="return confirm('¿Estás seguro de eliminar esta dirección?');">
                                    <input type="hidden" name="direccion_id" value="<?php echo $dir['id_direccion']; ?>">
                                    <button type="submit" class="btn btn-sm btn-delete">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="empty-state">
                    <i class="fa-solid fa-map-marker-alt fa-3x text-muted mb-3"></i>
                    <p>No tienes direcciones almacenadas.</p>
                </div>
            <?php endif; ?>
        </div>

        <h3><i class="fa-solid fa-plus-circle"></i> Agregar nueva dirección</h3>
        <form class="form-direccion" action="user-apart-dashboard-agregar-direccion.php" method="POST">
            <div class="row">
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="direccion">Dirección completa *</label>
                        <input type="text" id="direccion" name="direccion" placeholder="Calle, número, colonia" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="ciudad">Ciudad *</label>
                        <input type="text" id="ciudad" name="ciudad" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="departamento">Departamento/Estado *</label>
                        <input type="text" id="departamento" name="departamento" required>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="codigo_postal">Código Postal</label>
                        <input type="text" id="codigo_postal" name="codigo_postal">
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="form-group">
                        <label for="telefono">Teléfono de contacto</label>
                        <input type="tel" id="telefono" name="telefono">
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        <label for="referencias">Referencias adicionales</label>
                        <textarea id="referencias" name="referencias" rows="2"
                            placeholder="Ej: Casa color blanco, portón negro, entre calles..."></textarea>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="es_principal" name="es_principal" value="1">
                        <label class="form-check-label" for="es_principal">
                            <i class="fa-solid fa-star check-icon"></i> Establecer como dirección principal
                        </label>
                    </div>
                </div>

                <div class="col-md-12">
                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Guardar Dirección
                    </button>
                </div>
            </div>
        </form>
    </div>

</body>

<script src="../scripts/user-apart-dashboard.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // Validación del formulario
    document.querySelector('.form-direccion').addEventListener('submit', function(e) {
        const direccion = document.getElementById('direccion').value.trim();
        const ciudad = document.getElementById('ciudad').value.trim();
        const departamento = document.getElementById('departamento').value.trim();

        if (!direccion || !ciudad || !departamento) {
            e.preventDefault();
            alert('Por favor, completa los campos obligatorios (*)');
            return false;
        }
        return true;
    });

    // Mostrar mensajes de éxito/error si existen
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        alert('¡Operación realizada con éxito!');
        window.history.replaceState({}, document.title, window.location.pathname);
    }
    if (urlParams.has('error')) {
        alert('Error: ' + urlParams.get('error'));
        window.history.replaceState({}, document.title, window.location.pathname);
    }
</script>

</html>