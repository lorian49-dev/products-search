<?php
session_start();
require_once "../shortCuts/connect.php";

// Validar si llega el ID
if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Producto no especificado.");
}

$id = intval($_GET['id']); // Sanitizar por seguridad

// Consultar información del producto
$sql = "SELECT * FROM producto WHERE id_producto = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Validar si existe
if ($result->num_rows === 0) {
    die("Producto no encontrado.");
}

$producto = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($producto['nombre']); ?></title>
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/home.css">
    <title>HOME | HERMES CLICK&GO</title>
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">

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
                            <a href="../CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
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
    <h1><?php echo htmlspecialchars($producto['nombre']); ?></h1>

    <div style="width:300px; height:300px; background-size:cover; background-position:center;
         background-image:url('../SOURCES/PRODUCTOS/<?php echo $producto['imagen'] ?? "default.png"; ?>');">
    </div>

    <p><strong>Descripción:</strong> <?php echo htmlspecialchars($producto['descripcion']); ?></p>
    <p><strong>Precio:</strong> $<?php echo number_format($producto['precio']); ?></p>
    <p><strong>Stock disponible:</strong> <?php echo $producto['stock']; ?></p>
    <p><strong>Origen:</strong> <?php echo htmlspecialchars($producto['origen']); ?></p>

    <br>

    <!-- BOTÓN PARA PASARELA DE PAGO (cambiar después) -->
    <a href="product-compra.php?id=<?php echo $producto['id_producto']; ?>"
        style="padding:10px 20px; background:#8B4513; color:white; text-decoration:none; border-radius:5px;"> <!--CAMBIAR RUTAS-->
        Comprar ahora
    </a>

    <br><br>

    <a class="btn-volver" href="search-products.php?search-product=<?php echo urlencode($_GET['search-product'] ?? ''); ?>">← Volver a resultados</a>


    <script src="../scripts/search-product.js" ;></script>
</body>

</html>
