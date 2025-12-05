<?php
session_start();
require_once "shortCuts/connect.php";


$productos = [];

// PROCESAR BÚSQUEDA
if (isset($_GET['search-product']) && !empty($_GET['search-product'])) {
    $busqueda = $connect->real_escape_string($_GET['search-product']);

    $sql = "SELECT * FROM producto 
            WHERE nombre LIKE '%$busqueda%' 
            OR descripcion LIKE '%$busqueda%'";

    $resultadoBusqueda = $connect->query($sql);

    if ($resultadoBusqueda && $resultadoBusqueda->num_rows > 0) {
        while ($fila = $resultadoBusqueda->fetch_assoc()) {
            $productos[] = $fila;
        }
    }
} else {
    $resultadoBusqueda = null;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="styles/home.css">
    <title>HOME | HERMES CLICK&GO</title>
    <link rel="stylesheet" href="SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
</head>

<body>
    <?php if(isset($_SESSION['flash_message'])): ?>
    <div class="message-session">
        <?php
        echo $_SESSION['flash_message'];
        unset($_SESSION['flash_message']);
        ?>
    </div>
<?php endif; ?>
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <h1>HERMES</h1>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="CONTROLLERS/search-products.php" method="GET" style="width:100%">
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
                            <a href="CONTROLLERS/user-apart-dashboard.php">Mi cuenta</a>
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
    <main>
        <!-- RESULTADOS DE LA BÚSQUEDA -->
        <?php if ($resultadoBusqueda !== null): ?>
            <div class="products-box" style="padding:20px;">
                <h2>Resultados de búsqueda:</h2>

                <?php if ($resultadoBusqueda->num_rows > 0): ?>
                    <div class="lista-resultados" style="display:flex; flex-wrap:wrap; gap:20px;">
                        <?php while ($row = $resultadoBusqueda->fetch_assoc()): ?>

                            <div class="producto-card" style="width:200px; border:1px solid #ccc; border-radius:10px; padding:10px;">
                                <h3><?php echo $row['nombre']; ?></h3>
                                <p><?php echo $row['descripcion']; ?></p>
                                <p><strong>$<?php echo $row['precio']; ?></strong></p>
                                <p>Stock: <?php echo $row['stock']; ?></p>
                            </div>

                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <p>No se encontraron productos.</p>
                <?php endif; ?>

                <hr style="margin-top:30px;">
            </div>
        <?php endif; ?>
        <div class="header-box">
            <div class="slides">
                <div class="slides-box"></div>
                <div class="slides-box"></div>
                <div class="slides-box"></div>
                <div class="slides-box"></div>
            </div>
        </div>
        <div class="offerts-box">
            <div class="offerts-box-targets">
                <div class="offerts-text">
                    <h2>SOLO POR HOY !</h2>
                </div>
                <div class="offerts-box-targets-cards">
                    <div class="card">
                        <div class="card-preview">
                            <div class="preview-description">
                                <span><strong>NIKE</strong></span>
                                <span>Air Force One Supreme</span>
                            </div>
                            <div class="preview-image">

                            </div>
                            <div class="preview-color">

                            </div>
                        </div>
                        <div class="card-info">

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-preview">
                            <div class="preview-description">

                            </div>
                            <div class="preview-image">

                            </div>
                            <div class="preview-color">

                            </div>
                        </div>
                        <div class="card-info">

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-preview">
                            <div class="preview-description">

                            </div>
                            <div class="preview-image">

                            </div>
                            <div class="preview-color">

                            </div>
                        </div>
                        <div class="card-info">

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-preview">
                            <div class="preview-description">

                            </div>
                            <div class="preview-image">

                            </div>
                            <div class="preview-color">

                            </div>
                        </div>
                        <div class="card-info">

                        </div>
                    </div>
                    <div class="card">
                        <div class="card-preview">
                            <div class="preview-description">

                            </div>
                            <div class="preview-image">

                            </div>
                            <div class="preview-color">

                            </div>
                        </div>
                        <div class="card-info">

                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="products-box">

        </div>
        <div class="our-stores">

        </div>
    </main>
    <footer>

    </footer>
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
                const respuesta_producto = await fetch(`CONTROLLERS/search-products-bar.php?term=${textoDelUsuario}`);
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
    <script src="scripts/home.js"></script> 
</body>

</html>