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
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <h1>HERMES</h1>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="search-products.php" method="GET" style="width:100%">
                        <li class="input-search-product-li">
                            <input
                                type="text"
                                name="search-product"
                                id="input-search-product"
                                placeholder="Buscar producto..."
                                value="" autocomplete="off">
                            <button type="submit" class="button-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <div id="results-container"></div>
                            <div id="user-data"
                                data-nombre="<?php echo $usuario['nombre']; ?>"
                                data-apellido="<?php echo $usuario['apellido']; ?>"
                                data-correo="<?php echo $usuario['correo']; ?>"
                                data-telefono="<?php echo $usuario['telefono']; ?>"
                                data-fecha="<?php echo $usuario['fecha_nacimiento']; ?>"
                                data-direccion="<?php echo $usuario['direccion_principal']; ?>">
                            </div>

                        </li>
                    </form>

                    </li>
                </div>
            </ul>
        </div>
        <div class="bottom">
            <nav>
                <ul>
                    <li><span id="span-menu-categoria">Categorias</span>
                        <div id="menu-categoria" class="menu-categoria">
                            <ul>
                                <li>Electrodomesticos</li>
                                <li>Tecnologia</li>
                                <li>Hogar</li>
                                <li>Moda</li>
                                <li>Deportes</li>
                                <li>Belleza</li>
                                <li>Jugueteria</li>
                                <li>Automotriz</li>
                                <li>Electronica</li>
                                <li>Mascotas</li>
                                <li>Arte</li>
                            </ul>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <li><span id="venderPage">Vender</span></li>
                    <?php endif; ?>
                    <li><span id="ayuda-listado">Ayuda</span>
                        <div id="menu-ayuda" class="menu-categoria">
                            <ul>
                                <li>Informacion</li>
                                <li>PQRS</li>
                                <li>Contactos</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="account-header">
                <!-- perfil usuario -->
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="perfil-menu">
                        <button class="perfil-btn"> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout-user.php">Cerrar sesión</a>
                        </div>

                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.php"><span class="sisu-buttons"> Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons"> Sign Up</span></a>
                <?php endif; ?>
                <!-- fin del menu despegable -->
            </div>
            <div class="icons-header">
                <span><img src="../SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <span><img src="../SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></span>
            </div>
        </div>
    </header>

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

            <a href="../registros-inicio-sesion/logout.php" style="text-decoration:none;color:#b30000;">
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