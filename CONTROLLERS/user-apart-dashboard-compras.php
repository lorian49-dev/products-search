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
// Obtener el historial de compras del usuario
$sqlPedidos = "SELECT * FROM pedido WHERE id_usuario = $usuario_id ORDER BY fecha_pedido DESC";
$resultPedidos = mysqli_query($connect, $sqlPedidos);

if (!$resultPedidos) {
    die("Error al obtener pedidos: " . mysqli_error($connect));
}

?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        /* contenedor */
        .dashboard-content {
            max-width: 1100px;
            margin: 24px auto;
            padding: 0 16px;
            font-family: "Inter", system-ui, Arial;
            color: #333;
        }

        /* lista y tarjetas */
        .lista-compras {
            display: grid;
            grid-template-columns: 1fr;
            gap: 14px;
            margin-top: 20px;
        }

        @media (min-width: 720px) {
            .lista-compras {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (min-width: 1100px) {
            .lista-compras {
                grid-template-columns: 1fr 1fr 1fr;
            }
        }

        .compra-item {
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 6px 18px rgba(18, 25, 30, 0.06);
            display: flex;
            flex-direction: column;
            gap: 10px;
            transition: .15s ease;
        }

        .compra-item:hover {
            transform: translateY(-4px);
            box-shadow: 0 12px 28px rgba(18, 25, 30, 0.09);
        }

        /* encabezado */
        .compra-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .compra-header h3 {
            margin: 0;
            font-size: 16px;
            font-weight: 600;
            color: #111;
        }

        /* badge de estado */
        .badge {
            font-size: 12px;
            padding: 6px 10px;
            border-radius: 999px;
            font-weight: 600;
            color: #fff;
        }

        /* colores según estado */
        .estado-entregado {
            background: #2ecc71;
        }

        .estado-en-camino {
            background: #f39c12;
        }

        .estado-procesando {
            background: #3498db;
        }

        .estado-cancelado {
            background: #e74c3c;
        }

        /* cuerpo */
        .compra-body {
            display: flex;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 12px;
        }

        .compra-info p,
        .compra-desc p {
            margin: 0;
            font-size: 14px;
            color: #444;
        }

        /* footer */
        .compra-footer {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 6px;
            flex-wrap: wrap;
        }

        .llegada {
            font-size: 13px;
            color: #555;
        }

        /* botón */
        .btn-detalle {
            text-decoration: none;
            padding: 8px 12px;
            background: #111827;
            color: #fff;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
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
    <main>
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

        <section class="dashboard-content">
            <h2>Mis Compras</h2>
            <p>Aquí podrás ver el historial de tus compras realizadas en HERMES.</p>

            <?php if (mysqli_num_rows($resultPedidos) === 0): ?>
                <p>No tienes compras registradas.</p>

            <?php else: ?>

                <div class="lista-compras">
                    <?php while ($pedido = mysqli_fetch_assoc($resultPedidos)): ?>

                        <?php
                        // clase visual según estado
                        $estadoClass = strtolower($pedido['estado']);
                        $estadoClass = str_replace(" ", "-", $estadoClass);
                        ?>

                        <article class="compra-item">

                            <div class="compra-header">
                                <h3>Pedido #<?php echo $pedido['id_pedido']; ?></h3>
                                <span class="badge estado-<?php echo $estadoClass; ?>">
                                    <?php echo $pedido['estado']; ?>
                                </span>
                            </div>

                            <div class="compra-body">
                                <div class="compra-info">
                                    <p><strong>Fecha:</strong> <?php echo $pedido['fecha_pedido']; ?></p>
                                    <p><strong>Total:</strong> $<?php echo number_format($pedido['total'], 0, ',', '.'); ?></p>
                                </div>

                                <div class="compra-desc">
                                    <p><?php echo $pedido['descripcion']; ?></p>
                                </div>
                            </div>

                            <div class="compra-footer">
                                <?php if (!empty($pedido['llegada_estimada'])): ?>
                                    <span class="llegada">
                                        Llegada estimada: <?php echo $pedido['llegada_estimada']; ?>
                                    </span>
                                <?php endif; ?>

                                <a href="#" class="btn-detalle">Ver detalle</a>
                            </div>

                        </article>

                    <?php endwhile; ?>
                </div>

            <?php endif; ?>

        </section>

    </main>

</body>
<script src="../scripts/user-apart-dashboard.js"></script>

</html>