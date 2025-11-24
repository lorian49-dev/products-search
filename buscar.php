<?php
session_start();
require_once "registros-inicio-sesion/connect.php";

// Si viene una búsqueda
$productos = [];

if (isset($_GET['search-product']) && !empty($_GET['search-product'])) {
    $busqueda = $connect->real_escape_string($_GET['search-product']);

    $sql = "SELECT * FROM producto 
            WHERE nombre LIKE '%$busqueda%' 
            OR descripcion LIKE '%$busqueda%'";
    $resultado = $connect->query($sql);
} else {
    $resultado = null;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="home.css">
    <title>Resultados de búsqueda</title>
    <link rel="stylesheet" href="home.css"> <!-- Para mantener TU diseño -->
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
                    <form action="buscar.php" method="GET" style="width:100%">
                        <li>
                            <input
                                type="text"
                                name="search-product"
                                id="input-search-product"
                                placeholder="Buscar producto..."
                                value="">
                            <div id="results-container"></div>
                            <button type="submit">Buscar</button>
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
            <div class="icons-header">
                <span><img src="SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <span><img src="SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></span>
            </div>
        </div>
    </header>

    <div class="container" style="display:flex; gap:20px;">

        <aside class="sidebar" style="width:250px; border:1px solid #ccc; padding:15px; background:#f9f9f9; border-radius:8px;">
            <h3>Categorías</h3>
            <ul>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&categoria=Electrodomesticos">Electrodomésticos</a></li>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&categoria=Tecnologia">Tecnología</a></li>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&categoria=Hogar">Hogar</a></li>
                <!-- Añade más categorías aquí -->
            </ul>

            <h3>Rango de precio</h3>
            <ul>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&precio=0-100">Hasta $100</a></li>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&precio=100-500">$100 - $500</a></li>
                <li><a href="buscar.php?q=<?php echo urlencode($busqueda); ?>&precio=500-1000">$500 - $1000</a></li>
            </ul>
        </aside>

        <section class="main-content" style="flex:1;">
            <div class="result-container">

                <?php if ($resultado !== null): ?>

                    <?php if ($resultado->num_rows > 0): ?>

                        <h2>Resultados para: "<?php echo htmlspecialchars($_GET['search-product']); ?>"</h2>
                        <div class="productos-grid">

                            <?php while ($row = $resultado->fetch_assoc()): ?>

                                <div class="producto-card">
                                    <div
                                        class="producto-img"
                                        style="background-image: url('SOURCES/PRODUCTOS/<?php echo $row['imagen'] ?? "default.png"; ?>');">
                                    </div>
                                    <h3>
                                        <a href="producto.php?id=<?php echo $row['id_producto']; ?>&search-product=<?php echo urlencode($_GET['search-product']); ?>" class="producto-link">
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
    <script>
        //Script de peticion al servidor para busqueda en el buscador

        const input_request = document.getElementById('input-search-product');
        const box_result = document.getElementById('results-container');

        input_request.addEventListener('input', async (event) => {
            const textoDelUsuario = event.target.value;
            //limpiamos los resultados anteriores
            box_result.innerHTML = '';

            if (textoDelUsuario.length < 2) { //opcional: buscar solo a partir de 2 caracteres
                box_result.style.display = 'none';
                return;
            }
            //1. HACER LA PETICION AL SERVIDOR PHP
            try {
                //hacemos la llamada al php dandole el valor por medio del ?termn=
                const respuesta_producto = await fetch(`buscar-productos.php?term=${textoDelUsuario}`);
                //desempacamos la respuesta del server
                const productos = await respuesta_producto.json(); // .json convierte la respuesta venida del servidor(texto Plano) y lo convierte en un array de oroducto listo para utilizar
                if (productos.length > 0) {
                    box_result.style.display = 'block';
                    productos.forEach(producto => { //se peude utilizar otro nombre en lugar de producto, no afecta en nada ya que es un nombrte de la funcion temporal
                        const item = document.createElement('div') //esto creara varios divs que correspondan al elemento puesto en la barra de busqueda
                        item.classList.add('result-item')
                        item.textContent = producto // asignamos al div, un texto a partir del array recorrido, utilizamos para eso el nombre de la funcion productos
                        item.addEventListener('click', () => {
                            input_request.value = producto;
                            box_result.style.display = 'none';
                        });
                        //poner el div item creado en el div contenedor que se creo posteriormente
                        box_result.appendChild(item)

                    });
                } else {
                    box_result.style.display = 'none';
                }
            } catch (error) {
                console.error("Error al buscar productos:", error);
                box_result.style.display = 'none';

            };
        });

        // vamos a ocultar la barra si hace click fuera de la busqueda
        document.addEventListener('click', (event_close) => {
            if (!input_request.contains(event.target) && !box_result.contains(event.target)) {
                box_result.style.display = 'none'
            }
        })
    </script>
    <script src="home.js"></script>
</body>

</html>