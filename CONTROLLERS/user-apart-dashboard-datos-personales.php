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


// OBTENER TODAS LAS DIRECCIONES DEL USUARIO
// Aquí asumimos que en `cliente` tienes un campo `direccion`
/*$sqlDirecciones = "SELECT direccion FROM cliente WHERE id_cliente= $usuario_id";
$resultDirecciones = mysqli_query($connect, $sqlDirecciones);*/
    
$direcciones = [];
/*if ($resultDirecciones && mysqli_num_rows($resultDirecciones) > 0) {
    while ($row = mysqli_fetch_assoc($resultDirecciones)) {
        $direcciones[] = $row['direccion'];
    }
}*/

// Dirección principal viene de tabla usuario
$direccionPrincipal = $usuario['direccion_principal'] ?? "";

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        .perfil-container {
            width: 70%;
            margin: 30px auto;
            background: #fff;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
        }

        .personal-info p {
            font-size: 16px;
            margin: 8px 0;
        }

        .principal {
            color: green;
            font-weight: bold;
        }

        .direcciones-lista {
            margin-top: 20px;
        }

        .direccion-card {
            background: #fafafa;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 10px;
            margin-bottom: 10px;
            position: relative;
        }

        .badge {
            background: green;
            color: #fff;
            padding: 3px 8px;
            font-size: 12px;
            border-radius: 5px;
            position: absolute;
            top: 10px;
            right: 10px;
        }

        .btn-principal {
            margin-top: 5px;
            padding: 6px 10px;
            border: none;
            background: #1976d2;
            color: #fff;
            border-radius: 6px;
            cursor: pointer;
        }

        .btn-principal:hover {
            background: #125a9c;
        }

        .form-direccion input {
            padding: 8px;
            width: 70%;
            border: 1px solid #ccc;
            border-radius: 8px;
        }

        .form-direccion button {
            padding: 8px 15px;
            background: #28a745;
            border: none;
            color: white;
            border-radius: 8px;
            cursor: pointer;
        }

        .form-direccion button:hover {
            background: #1d7f34;
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
    <h2>Datos personales</h2>
    <div class="personal-info">
        <p><strong>Nombre completo:</strong> <?= $usuario['nombre'] . " " . $usuario['apellido'] ?></p>
        <p><strong>Correo:</strong> <?= $usuario['correo'] ?></p>
        <p><strong>Teléfono:</strong> <?= $usuario['telefono'] ?></p>
        <p><strong>Fecha de nacimiento:</strong> <?= $usuario['fecha_nacimiento'] ?></p>
        <p><strong>Dirección principal:</strong> <span class="principal"><?= $usuario['direccion_principal'] ?></span></p>
    </div>

    <h3>Todas tus direcciones</h3>
    <div class="direcciones-lista">
        <?php foreach ($direcciones as $dir): ?>
            <div class="direccion-card">

                <p><?= $dir ?></p>

                <?php if ($dir == $usuario['direccion_principal']): ?>
                    <span class="badge">Principal</span>
                <?php else: ?>
                    <form action="cambiar_principal.php" method="POST">
                        <input type="hidden" name="nueva_principal" value="<?= $dir ?>">
                        <button class="btn-principal">Establecer como principal</button>
                    </form>
                <?php endif; ?>

            </div>
        <?php endforeach; ?>
    </div>

    <h3>Agregar nueva dirección</h3>
    <form class="form-direccion" action="agregar_direccion.php" method="POST">
        <input type="text" name="nueva" placeholder="Escribe la nueva dirección" required>
        <button type="submit">Agregar</button>
    </form>

    </div>

</body>
<script src="../scripts/user-apart-dashboard.js"></script>

</html>
