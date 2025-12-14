<?php
session_start();
require_once "../shortCuts/connect.php";

// Parámetros seguros
$busqueda = isset($_GET['search-product']) ? $connect->real_escape_string($_GET['search-product']) : "";
$precio_min = isset($_GET['precio_min']) && $_GET['precio_min'] !== "" ? (int)$_GET['precio_min'] : 0;
$precio_max = isset($_GET['precio_max']) && $_GET['precio_max'] !== "" ? (int)$_GET['precio_max'] : 999999999;
$categoria_id = isset($_GET['categoria']) ? (int)$_GET['categoria'] : null;

// Consulta SQL corregida
$sql = "SELECT DISTINCT p.*
        FROM producto p
        LEFT JOIN producto_categoria pc ON pc.id_producto = p.id_producto
        LEFT JOIN categoria c ON c.id_categoria = pc.id_categoria
        WHERE 1=1";

// Filtro por búsqueda de texto
if (!empty($busqueda)) {
    $sql .= " AND (p.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')";
}

// Filtro por rango de precio
$sql .= " AND p.precio BETWEEN $precio_min AND $precio_max";

// Filtro por categoría (si se seleccionó una)
if (!empty($categoria_id)) {
    $sql .= " AND c.id_categoria = $categoria_id";
}

// Ordenar por nombre
$sql .= " ORDER BY p.nombre ASC";

$resultado = $connect->query($sql);

// Obtener categorías para el sidebar
$cats = $connect->query("SELECT * FROM categoria ORDER BY nombre_categoria ASC");
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <title>Resultados de búsqueda</title>
    <style>
        /* Tus estilos CSS actualizados */
        .result-container {
            padding: 20px;
        }

        .productos-grid {
            display: flex;
            flex-wrap: wrap;
            gap: 25px;
        }

        .producto-card {
            width: 220px;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            padding: 10px;
            background: #fff;
        }

        .producto-img {
            width: 100%;
            height: 170px;
            background-color: #f2f2f2;
            background-size: cover;
            background-position: center;
            border-radius: 10px;
        }

        .btn-volver {
            display: inline-block;
            margin-top: 25px;
            padding: 10px 18px;
            background: #000;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
        }

        .producto-link {
            color: #000;
            text-decoration: none;
            font-weight: bold;
        }

        .producto-link:hover {
            color: #8B4513;
            text-decoration: none;
        }

        .container {
            display: flex;
            gap: 20px;
            padding: 20px;
        }

        .sidebar {
            width: 250px;
            border: 1px solid #ccc;
            padding: 15px;
            background: #f9f9f9;
            border-radius: 8px;
            font-family: Arial, sans-serif;
        }

        .sidebar h3 {
            margin-top: 0;
            margin-bottom: 10px;
            font-size: 18px;
            color: #333;
        }

        .sidebar ul {
            list-style: none;
            padding-left: 0;
        }

        .sidebar ul li {
            margin-bottom: 8px;
        }

        .sidebar ul li a {
            color: #8B4513;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
            display: block;
            padding: 5px 10px;
            border-radius: 4px;
        }

        .sidebar ul li a:hover {
            color: #5a2e00;
            background-color: #f0f0f0;
        }

        .sidebar ul li a.active {
            background-color: #8B4513;
            color: white;
        }

        .main-content {
            flex: 1;
        }

        .filter-form {
            margin-top: 20px;
        }

        .filter-form label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        .filter-form input {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }

        .filter-form button {
            margin-top: 15px;
            padding: 10px 20px;
            background-color: #8B4513;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            width: 100%;
        }

        .filter-form button:hover {
            background-color: #5a2e00;
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
                                value="<?php echo htmlspecialchars($busqueda); ?>"
                                autocomplete="off">
                            <button type="submit" class="button-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <div id="results-container"></div>
                        </li>
                    </form>
                </div>
            </ul>
        </div>
        <div class="bottom">
            <nav>
                <ul>
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
                        <button class="perfil-btn"><?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="../CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.php"><span class="sisu-buttons">Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons">Sign Up</span></a>
                <?php endif; ?>
                <!-- fin del menu despegable -->
            </div>
            <div class="icons-header">
                <span><img src="../SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <a href="cart.php">
                    <img src="SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart">
                </a>
            </div>
        </div>
    </header>
    <div class="container" style="display:flex; gap:20px;">

        <aside class="sidebar" style="width:250px; border:1px solid #ccc; padding:15px; background:#f9f9f9; border-radius:8px;">

            <!-- Categorías -->
            <h3>Categorías</h3>
            <ul>
                <!-- Opción "Todas las categorías" -->
                <li>
                    <a href="search-products.php?search-product=<?php echo urlencode($busqueda); ?>"
                        class="<?php echo empty($categoria_id) ? 'active' : ''; ?>">
                        Todas las categorías
                    </a>
                </li>

                <?php while ($c = $cats->fetch_assoc()): ?>
                    <li>
                        <a href="search-products.php?search-product=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $c['id_categoria']; ?>&precio_min=<?php echo $precio_min; ?>&precio_max=<?php echo $precio_max; ?>"
                            class="<?php echo ($categoria_id == $c['id_categoria']) ? 'active' : ''; ?>">
                            <?php echo htmlspecialchars($c['nombre_categoria']); ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>

            <!-- Filtro de precio -->
            <h3>Rango de precio</h3>
            <form action="search-products.php" method="GET" class="filter-form">
                <input type="hidden" name="search-product" value="<?php echo htmlspecialchars($busqueda); ?>">
                <?php if ($categoria_id): ?>
                    <input type="hidden" name="categoria" value="<?php echo $categoria_id; ?>">
                <?php endif; ?>

                <label>Mínimo:</label>
                <input type="number" name="precio_min" min="0" value="<?php echo $precio_min; ?>">

                <label>Máximo:</label>
                <input type="number" name="precio_max" min="0" value="<?php echo $precio_max; ?>">

                <button type="submit">Filtrar</button>
                <button type="button" onclick="resetFilters()">Limpiar filtros</button>
            </form>

        </aside>

        <section class="main-content" style="flex:1;">
            <div class="result-container">
                <?php if ($resultado->num_rows > 0): ?>

                    <?php if (!empty($busqueda)): ?>
                        <h2>Resultados para: "<?php echo htmlspecialchars($busqueda); ?>"</h2>
                    <?php elseif ($categoria_id): ?>
                        <?php
                        $cat_name = $connect->query("SELECT nombre_categoria FROM categoria WHERE id_categoria = $categoria_id");
                        $cat_row = $cat_name->fetch_assoc();
                        ?>
                        <h2>Productos en: <?php echo htmlspecialchars($cat_row['nombre_categoria']); ?></h2>
                    <?php else: ?>
                        <h2>Todos los productos</h2>
                    <?php endif; ?>

                    <p>Mostrando <?php echo $resultado->num_rows; ?> producto(s)</p>

                    <div class="productos-grid">
                        <?php while ($row = $resultado->fetch_assoc()): ?>
                            <div class="producto-card">
                                <?php
                                if (!empty($row['imagen_url'])) {
                                    $imagen_url = $row['imagen_url'];
                                } elseif (!empty($row['imagen'])) {
                                    $imagen_url = '../SOURCES/PRODUCTOS/' . $row['imagen'];
                                } else {
                                    $imagen_url = '../SOURCES/PRODUCTOS/default.png';
                                }
                                ?>

                                <div class="producto-img" style="background-image: url('<?php echo $imagen_url; ?>');">
                                </div>

                                <h3>
                                    <a href="search-products-product.php?id=<?php echo $row['id_producto']; ?>&search-product=<?php echo urlencode($busqueda); ?>"
                                        class="producto-link">
                                        <?php echo htmlspecialchars($row['nombre']); ?>
                                    </a>
                                </h3>

                                <p class="descripcion-corta">
                                    <?php
                                    echo strlen($row['descripcion']) > 100
                                        ? substr(htmlspecialchars($row['descripcion']), 0, 100) . '...'
                                        : htmlspecialchars($row['descripcion']);
                                    ?>
                                </p>
                                <p class="precio"><strong>$<?php echo number_format($row['precio'], 0, ',', '.'); ?></strong></p>
                                <p class="stock">Disponible: <?php echo $row['stock']; ?> unidades</p>

                                <!-- Dentro de cada producto: -->
                                <form method="POST" action="add-to-cart.php">
                                    <input type="hidden" name="product_id" value="<?php echo $row['id_producto']; ?>">
                                    <input type="hidden" name="quantity" value="1">
                                    <button type="submit" class="btn-add-to-cart">
                                        <i class="fas fa-cart-plus"></i> Añadir al Carrito
                                    </button>
                                </form>
                            </div>
                        <?php endwhile; ?>
                    </div>

                <?php else: ?>
                    <h2>No se encontraron productos</h2>
                    <p>No se encontraron productos que coincidan con tu búsqueda.</p>
                    <p>Intenta:</p>
                    <ul>
                        <li>Cambiar los términos de búsqueda</li>
                        <li>Seleccionar otra categoría</li>
                        <li>Ajustar el rango de precio</li>
                    </ul>
                <?php endif; ?>

                <a class="btn-volver" href="../home.php">← Volver al inicio</a>
            </div>
        </section>
    </div>

    <script>
        function resetFilters() {
            window.location.href = 'search-products.php';
        }
    </script>
    <script src="../scripts/search-product.js" ;>
    </script>
</body>

</html>