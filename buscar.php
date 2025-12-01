<?php
session_start();
require_once "registros-inicio-sesion/connect.php";

// Parámetros seguros
$busqueda   = isset($_GET['search-product']) ? $connect->real_escape_string($_GET['search-product']) : "";
$min        = isset($_GET['min']) ? intval($_GET['min']) : 0;
$max        = isset($_GET['max']) ? intval($_GET['max']) : 999999999;
$categoria  = isset($_GET['categoria']) ? intval($_GET['categoria']) : null;

$sql = "
    SELECT producto.*
    FROM producto
    LEFT JOIN producto_categoria pc ON producto.id_producto = pc.id_producto
    LEFT JOIN categoria c ON pc.id_categoria = c.id_categoria
    WHERE 1
";

// Búsqueda por texto
if (!empty($busqueda)) {
    $sql .= " AND (producto.nombre LIKE '%$busqueda%' OR producto.descripcion LIKE '%$busqueda%') ";
}

// Rango de precio
$sql .= " AND producto.precio BETWEEN $min AND $max ";

// Categoría
if (!empty($categoria)) {
    $sql .= " AND c.id_categoria = $categoria ";
}

$resultado = $connect->query($sql);
$busqueda = $connect->real_escape_string($_GET['search-product'] ?? "");
$categoria = $_GET['categoria'] ?? "";
$precio_min = isset($_GET['precio_min']) && $_GET['precio_min'] !== ""
    ? (int)$_GET['precio_min']
    : 0;
$precio_max = isset($_GET['precio_max']) && $_GET['precio_max'] !== ""
    ? (int)$_GET['precio_max']
    : 999999999; // máximo por defecto si está vacío
$sql = "SELECT p.*
        FROM producto p
        LEFT JOIN producto_categoria pc ON pc.id_producto = p.id_producto
        LEFT JOIN categoria c ON c.id_categoria = pc.id_categoria
        WHERE (p.nombre LIKE '%$busqueda%' OR p.descripcion LIKE '%$busqueda%')
        AND p.precio >= $precio_min
        AND p.precio <= $precio_max";


if (!empty($categoria)) {
    $categoria = $connect->real_escape_string($categoria);
    $sql .= " AND c.nombre_categoria = '$categoria'";
}

$resultado = $connect->query($sql);

?>


<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="home.css">
    <link rel="stylesheet" href="SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <title>Resultados de búsqueda</title>
    <style>
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
            /* opcional para separar del borde */
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
        }

        .sidebar ul li a:hover {
            color: #5a2e00;
            text-decoration: underline;
        }

        .main-content {
            flex: 1;
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
            font-family: Arial, sans-serif;
        }

        .producto-img {
            width: 100%;
            height: 170px;
            background-color: #f2f2f2;
            background-size: cover;
            background-position: center;
            border-radius: 10px;
            margin-bottom: 10px;
        }

        .producto-link {
            color: #000;
            text-decoration: none;
            font-weight: bold;
        }

        .producto-link:hover {
            color: #8B4513;
            text-decoration: underline;
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

        /* css sobre el slider */

        .range-slider {
            position: relative;
            width: 100%;
            height: 40px;
        }

        .range-slider input[type=range] {
            position: absolute;
            width: 100%;
        }


        .range-slider input::-webkit-slider-thumb {
            pointer-events: auto;
            width: 16px;
            height: 16px;
            background: #3b82f6;
            border-radius: 50%;
            cursor: pointer;
        }

        .range-values {
            margin-top: 10px;
            display: flex;
            justify-content: space-between;
            width: 300px;
        }

        .filtro-precio {
            margin: 20px 0;
            padding-bottom: 40px;
            /* da espacio y evita que tape */
            position: relative;
            width: 320px;
        }

        #results-container {
            position: absolute;
            top: 45px;
            left: 0;
            min-width: 400px;
            max-width: 700px;
            background: #fff;
            border: 1px solid #eee;
            border-radius: 10px;
            z-index: 100;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            display: none;
            max-height: 250px;
            overflow-y: auto;
            padding: 10px;
        }

        .result-item {
            padding: 20px 20px;
            cursor: pointer;
            background: #fff;
            border-bottom: 1px solid #eee;
            transition: background 0.2s;
            border-radius: 10px;
        }

        .result-item:hover {
            background: #461d01;
            color: #fff8f1;
        }

        .input-search-product-li {
            position: relative;
        }

        .button-search {
            right: 10px;
            padding: 10px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 18px;
            position: absolute;
            color: #727272
        }

        .ml-user-menu {
            background: #fff;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            border: 1px solid #e6e6e6;
            font-family: Arial;
        }

        .ml-user-menu h3 {
            margin-top: 0;
            font-size: 18px;
            margin-bottom: 12px;
            color: #333;
        }

        .ml-user-menu ul {
            list-style: none;
            padding-left: 0;
        }

        .ml-user-menu ul li {
            margin-bottom: 10px;
        }

        .ml-user-menu ul li a {
            text-decoration: none;
            color: #0077cc;
            font-weight: 600;
            padding: 5px 0;
            display: block;
            transition: .2s;
        }

        .ml-user-menu ul li a:hover {
            color: #005599;
            text-decoration: underline;
        }
    </style>
</head>

<body>

    <header>
        <div class="top">
            <span id="logo-hermes-home" href="home.php">
                <h1>HERMES</h1>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="buscar.php" method="GET" style="width:100%">
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
            <div class="icons-header">
                <span><img src="SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <span><img src="SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></span>
            </div>
            <div class="account-header">
                <!-- perfil usuario -->
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="perfil-menu">
                        <button class="perfil-btn"> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="USER/usuario.php">Mi cuenta</a>
                            <a href="registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="registros-inicio-sesion/login.html"><span class="sisu-buttons"> Sign In</span></a>
                    <a href="registros-inicio-sesion/register.html"><span class="sisu-buttons"> Sign Up</span></a>
                <?php endif; ?>
                <!-- fin del menu despegable -->
            </div>
            <div class="icons-header">
                <span><img src="SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <span><img src="SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></span>
            </div>
        </div>

    </header>

    <div class="container" style="display:flex; gap:20px;">

        <aside class="sidebar" style="width:250px; border:1px solid #ccc; padding:15px; background:#f9f9f9; border-radius:8px;">
            <div class="ml-user-menu">
                <h3>Mi cuenta</h3>
                <ul>
                    <li><a href="USER/usuario.php">Mis datos personales</a></li>
                    <li><a href="USER/compras.php">Mis compras</a></li>
                    <li><a href="USER/direcciones.php">Mis direcciones</a></li>
                    <li><a href="USER/publicaciones.php">Mis publicaciones</a></li>
                    <li><a href="USER/favoritos.php">Favoritos</a></li>
                    <li><a href="USER/notificaciones.php">Notificaciones</a></li>
                    <li><a href="USER/seguridad.php">Seguridad y privacidad</a></li>
                    <li><a href="USER/configuracion.php">Configuración</a></li>
                    <li><a href="registros-inicio-sesion/logout.php" style="color:#b30000;">Cerrar sesión</a></li>
                </ul>
            </div>

            <!-- Categorias -->
            <h3>Categorías</h3>
            <ul>
                <?php
                $cats = $connect->query("SELECT * FROM categoria ORDER BY nombre_categoria ASC");
                while ($c = $cats->fetch_assoc()):
                ?>
                    <li>
                        <a href="buscar.php?search-product=<?php echo urlencode($busqueda); ?>&categoria=<?php echo $c['id_categoria']; ?>">
                            <?php echo $c['nombre_categoria']; ?>
                        </a>
                    </li>
                <?php endwhile; ?>
            </ul>


            <h3>Rango de precio</h3>

            <form action="buscar.php" method="GET">

                <label>Mínimo:</label>
                <input type="number" name="precio_min" min="0" value="<?php echo $_GET['precio_min'] ?? 0; ?>">

                <label>Máximo:</label>
                <input type="number" name="precio_max" min="0" value="<?php echo $_GET['precio_max'] ?? 1000000; ?>">

                <input type="hidden" name="search-product" value="<?php echo htmlspecialchars($busqueda); ?>">

                <button type="submit">Filtrar</button>
            </form>

        </aside>

        <section class="main-content" style="flex:1;">
            <div class="result-container">

                <?php if ($resultado !== null): ?>

                    <?php if ($resultado->num_rows > 0): ?>

                        <h2>Resultados para: "<?php echo htmlspecialchars($busqueda); ?>"</h2>

                        <div class="productos-grid">

                            <?php while ($row = $resultado->fetch_assoc()): ?>

                                <div class="producto-card">
                                    <?php
                                    $imagen = (!empty($row['imagen'])) ? $row['imagen'] : "default.png";
                                    ?>
                                    <div
                                        class="producto-img"
                                        style="background-image: url('SOURCES/PRODUCTOS/<?php echo $imagen; ?>');">
                                    </div>


                                    <h3>
                                        <a href="producto.php?id=<?php echo $row['id_producto']; ?>&search-product=<?php echo urlencode($busqueda); ?>"
                                            class="producto-link">
                                            <?php echo htmlspecialchars($row['nombre']); ?>
                                        </a>
                                    </h3>

                                    <p><?php echo htmlspecialchars($row['descripcion']); ?></p>
                                    <p><strong>$<?php echo number_format($row['precio']); ?></strong></p>
                                    <p>Stock: <?php echo $row['stock']; ?></p>
                                </div>

                            <?php endwhile; ?>

                        </div>

                    <?php else: ?>
                        <p>No se encontraron productos que coincidan.</p>
                    <?php endif; ?>

                <?php else: ?>
                    <p>No se ingresó ningún término de búsqueda.</p>
                <?php endif; ?>

                <a class="btn-volver" href="home.php">← Volver al inicio</a>
            </div>
        </section>
    </div>
    <script src="buscar.js" ;>
    </script>
</body>

</html>