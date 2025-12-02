<?php
session_start();
include("../registros-inicio-sesion/connect.php");

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
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        .modalWindow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            pointer-events: none;
            transition: .3s;
        }

        .modalContainer {
            width: 450px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin: 120px auto 0 auto;
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
            transition: .3s;
        }

        #btnEdit {
            padding: 10px 18px;
            background: red;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .back-icon {
            cursor: pointer;
            font-size: 20px;
        }

        /* Layout principal */
        .dashboard-layout {
            display: flex;
            gap: 20px;
            padding: 22px;
            align-items: flex-start;
            font-family: Arial, sans-serif;
            background: #fff7f3;
        }

        /* SIDEBAR */
        .sidebar-ml {
            width: 220px;
            background: #ffffff;
            border: 1px solid #ece6e2;
            border-radius: 10px;
            padding: 18px;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.03);
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .sidebar-ml h3 {
            margin: 0 0 14px 0;
            font-size: 16px;
            color: #222;
            font-weight: 700;
        }

        .sidebar-ml ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-ml li {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 10px 8px;
            border-radius: 8px;
            color: #1b1b1b;
            cursor: pointer;
            transition: background .15s, color .15s;
            font-weight: 600;
        }

        .sidebar-ml li i {
            width: 20px;
            text-align: center;
            color: #6b6b6b;
            font-size: 16px;
        }

        .sidebar-ml li:hover {
            background: #fff3f1;
            color: #c84a2b;
        }

        .sidebar-ml li.active {
            background: #f6efe9;
            color: #c84a2b;
        }

        .sidebar-ml li .chev {
            margin-left: auto;
            font-size: 12px;
            color: #9a9a9a;
        }

        /* PANEL DERECHO */
        .profile-panel {
            flex: 1;
            background: #ffffff;
            border-radius: 10px;
            padding: 26px;
            border: 1px solid #ece6e2;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.03);
            min-height: 260px;
        }

        /* Header del perfil */
        .profile-header {
            display: flex;
            gap: 18px;
            align-items: center;
            margin-bottom: 18px;
        }

        .avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: linear-gradient(135deg, #eee, #ddd);
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #6b4a3a;
            font-size: 26px;
        }

        /* Cards */
        .cards {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
            gap: 16px;
            margin-bottom: 20px;
        }

        .card {
            background: #fff;
            border-radius: 10px;
            border: 1px solid #f0e9e6;
            padding: 14px;
            box-shadow: 0 1px 0 rgba(0, 0, 0, 0.03);
            cursor: pointer;
            transition: .2s;
        }

        .card:hover {
            background: #f2f2f2;
        }

        /* Perfil */
        .perfil-container p {
            margin: 6px 0;
            font-size: 14px;
            color: #222;
        }

        .perfil-container p strong {
            color: #111;
        }

        /* Botones */
        .btn {
            display: inline-block;
            padding: 9px 14px;
            border-radius: 8px;
            text-decoration: none;
            font-weight: 700;
            border: none;
            cursor: pointer;
        }

        .btn-edit {
            background: #dc2626;
            color: #fff;
            margin-right: 8px;
        }

        .btn-back {
            background: transparent;
            color: #c84a2b;
            border: 1px solid #f3dede;
        }

        /* Responsive */
        @media (max-width: 900px) {
            .dashboard-layout {
                flex-direction: column;
                padding: 12px;
            }

            .sidebar-ml {
                width: 100%;
                position: relative;
                top: 0;
            }
        }

        /* Overlay */
        #modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.5);
            justify-content: center;
            align-items: center;
            z-index: 9999;
        }

        #modal-content {
            background: white;
            width: 480px;
            max-width: 90%;
            padding: 25px;
            border-radius: 12px;
            position: relative;
        }

        #modal-close {
            position: absolute;
            top: 8px;
            right: 10px;
            background: transparent;
            border: none;
            font-size: 22px;
            cursor: pointer;    
        }

        /* ⭐ AUMENTAR LETRA DE LA INFORMACIÓN DE LAS CARDS (FORZADO) */
        .card strong {
            font-size: 20px !important;
        }

        .card div {
            font-size: 15px !important;
            font-weight: bold;
            color: #000;
            margin-top: 4px;
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
                    <form action="../buscar.php" method="GET" style="width:100%">
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
                            <a href="../USER/usuario.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>

                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.html"><span class="sisu-buttons"> Sign In</span></a>
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
            <h3>Mi cuenta</h3>
            <ul id="menu-usuario">
                <li data-section="datos"><i class="fa-solid fa-user"></i> Datos personales <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

                <li data-section="compras"><i class="fa-solid fa-bag-shopping"></i> Mis compras <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

                <li data-section="direcciones"><i class="fa-solid fa-location-dot"></i> Mis direcciones <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

                <li data-section="pagos"><i class="fa-regular fa-credit-card"></i> Métodos de pago <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

                <li data-section="seguridad"><i class="fa-solid fa-lock"></i> Seguridad y contraseña <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

                <li data-section="configuracion"><i class="fa-solid fa-gear"></i> Configuración <span class="chev"><i class="fa-solid fa-chevron-right"></i></span></li>

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
    <script src="usuario.js"></script>


</body>

</html>