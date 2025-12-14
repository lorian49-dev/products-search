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
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        #menu-usuario li a {
            display: flex;
            justify-content: space-between;
            align-items: left;
            color: inherit;
            text-decoration: none;
            padding: 10px;
            width: 9%;
        }
    </style>
</head>

<body>
    <?php include '../TEMPLATES/header.php'?>

    <div class="dashboard-layout">

        <!-- SIDEBAR -->
        <aside class="sidebar-ml" role="navigation" aria-label="Mi cuenta">
            <ul id="menu-usuario">
                <li>
                    <a href="user-apart-dashboard-datos-personales.php">
                        <i class="fa-solid fa-user"></i> Datos personales
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-compras.php">
                        <i class="fa-solid fa-bag-shopping"></i> Mis compras
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-metodos-pago.php">
                        <i class="fa-regular fa-credit-card"></i> Métodos de pago
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                </li>

                <li>
                    <a href="seguridad.html">
                        <i class="fa-solid fa-lock"></i> Seguridad y contraseña
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                </li>

                <li>
                    <a href="configuracion.html">
                        <i class="fa-solid fa-gear"></i> Configuración
                        <span class="chev"><i class="fa-solid fa-chevron-right"></i></span>
                    </a>
                </li>
            </ul>

            <a href="../registros-inicio-sesion/logout-user.php" style="text-decoration:none;color:#b30000;">
                <li><i class="fa-solid fa-right-from-bracket"></i> Cerrar sesión</li>
            </a>
            </ul>
        </aside>

        <!-- SUBVENTANA DINÁMICA (AQUÍ SE CARGA EL CONTENIDO) -->
        <!-- // AÑADIDO -->
        <div id="modal-overlay">
            <div id="modal-content">
                <button id="modal-close">X</button>
                <div id="modal-inner"></div>
            </div>
        </div>


        <main class="profile-panel">

            <!-- Header con avatar y nombre -->
            <div class="profile-header">
                <div class="avatar"><?= strtoupper(substr($usuario['nombre'], 0, 1)) . strtoupper(substr($usuario['apellido'], 0, 1)); ?></div>
                <div>
                    <h2 style="margin:0; color:#222; font-size:22px;"><?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido']); ?></h2>
                    <div style="color:#6b6b6b; font-weight:600; margin-top:6px;"><?= htmlspecialchars($usuario['correo']); ?></div>
                </div>
            </div>

            <!-- tarjetas tipo resumen -->
            <div class="cards">

                <!-- Card 1 - Tu Información -->
                <div class="card">
                    <strong>Tu información</strong>
                    <div style="margin-top:8px; color:#6b6b6b; font-size:13px;">
                        <?= htmlspecialchars($usuario['nombre'] . " " . $usuario['apellido']); ?>
                    </div>
                    <div style="color:#222; font-size:13px; margin-top:4px;">
                        Fecha de nacimiento: <?= htmlspecialchars($usuario['fecha_nacimiento']); ?>
                    </div>
                </div>

                <!-- Card 2 - Datos de la cuenta -->
                <div class="card">
                    <strong>Datos de la cuenta</strong>
                    <div style="margin-top:8px; color:#6b6b6b; font-size:13px;">
                        Correo: <?= htmlspecialchars($usuario['correo']); ?>
                    </div>
                    <div style="color:#222; font-size:13px; margin-top:4px;">
                        Teléfono: <?= htmlspecialchars($usuario['telefono']); ?>
                    </div>
                </div>

                <!-- Card 3 - Seguridad -->
                <div class="card">
                    <strong>Seguridad</strong>
                    <div style="margin-top:8px; color:#6b6b6b; font-size:13px;">
                        Tu cuenta está protegida.
                    </div>
                    <div style="color:#222; font-size:13px; margin-top:4px;">
                        Último cambio de contraseña: No disponible
                    </div>
                </div>

                <!-- Card 4 - Direcciones -->
                <div class="card">
                    <strong>Direcciones</strong>
                    <div style="margin-top:8px; color:#6b6b6b; font-size:13px;">
                        Dirección principal:
                    </div>
                    <div style="color:#222; font-size:13px; margin-top:4px;">
                        <?= htmlspecialchars($usuario['direccion_principal']); ?>
                    </div>
                </div>

            </div>


            <div style="margin-top:16px;">
                <a href="../home.php" class="btn btn-back">← volver al inicio</a>
            </div>

            <!-- Modal ACTUAL DE EDITAR -->

        </main>
    </div>
    <script src="../scripts/user-apart-dashboard.js"></script>


</body>

</html>