<?php
session_start();
require_once "shortCuts/connect.php";

use Cloudinary\Transformation\Resize;

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
    <style>
        .carousel-container {
            position: relative;
            max-width: 100%;
            margin: 20px auto;
            overflow: hidden;
        }

        .offerts-carousel {
            display: flex;
            gap: 20px;
            overflow-x: auto;
            scroll-behavior: smooth;
            padding: 20px 0;
            scrollbar-width: none;
            /* Firefox */
        }

        .offerts-carousel::-webkit-scrollbar {
            display: none;
            /* Chrome, Safari */
        }

        .carousel-card {
            min-width: 250px;
            text-decoration: none;
            color: inherit;
        }

        .carousel-btn {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: rgba(0, 0, 0, 0.5);
            color: white;
            border: none;
            width: 50px;
            height: 50px;
            font-size: 36px;
            cursor: pointer;
            border-radius: 50%;
            z-index: 10;
            transition: 0.3s;
        }

        .prev-btn {
            left: 10px;
            display: flex;
            justify-content: center;
        }

        .next-btn {
            right: 10px;
            display: flex;
            justify-content: center;
        }

        .carousel-btn:hover {
            background: rgba(0, 0, 0, 0.8);
        }

        .preview-image {
            height: 14rem;
            background-size: cover !important;
            background-position: center !important;
        }

        /* Estilos para el carrusel - IMAGEN COMPLETA */
        .preview-image {
            width: 100%;
            height: 9rem;
            /* Altura fija para todas las imágenes */
            background-size: contain;
            /* Muestra la imagen completa */
            background-position: center;
            background-repeat: no-repeat;
            background-color: #f9f9f9;
            /* Fondo neutro para imágenes con transparencia */
            transition: all 0.3s ease;
        }

        /* Para mantener la proporción de las imágenes */
        .card {
            border-radius: 2rem;
            overflow: hidden;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            background: #461d01;
            height: 100%;
            display: flex;
            flex-direction: column;
            border: #461d01 0.5px solid;
        }

        .card:hover {
            transform: translateY(-5px);
            box-shadow: 1px 1px hsl(0deg 0% 0% / 0.075),
      0 2px 2px hsl(0deg 0% 0% / 0.075),
      0 4px 4px hsl(0deg 0% 0% / 0.075),
      0 8px 8px hsl(0deg 0% 0% / 0.075),
      0 16px 16px hsl(0deg 0% 0% / 0.075);
        }

        .card:hover .preview-image {
            transform: scale(1.05);
            /* Efecto sutil al hacer hover */
        }

        .card-preview {
            flex: 1;
            padding: 15px;
            display: flex;
            flex-direction: column;
        }

        .preview-description {
            padding: 15px 10px;
            text-align: center;
            flex-shrink: 0;
        }

        .card-info {
            background: #461d01;
            padding: 10px;
            text-align: center;
        }
    </style>
</head>

<body>
    <div class="body-container">
    <?php if (isset($_SESSION['flash_message'])): ?>
        <div class="message-session">
            <?php
            echo $_SESSION['flash_message'];
            unset($_SESSION['flash_message']);
            ?>
        </div>
    <?php endif; ?>

    <?php include 'TEMPLATES/header-home.php'?> <!--Inclusion de la plantilla para la cabecera-->

    <main class="main-home">
        <!-- RESULTADOS DE LA BÚSQUEDA -->
        <?php if ($resultadoBusqueda !== null): ?>
            <div class="products-box" style="padding:20px;">
                <h2>Resultados de búsqueda:</h2>

                <?php if ($resultadoBusqueda->num_rows > 0): ?>
                    <div class="lista-resultados" style="display:flex; flex-wrap:wrap; gap:20px;">
                        <?php while ($row = $resultadoBusqueda->fetch_assoc()): ?>

                            <div class="producto-card"
                                style="width:200px; border:1px solid ; border-radius:10px; padding:10px;">
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
        <!-- SOLO POR HOY - Carrusel deslizable -->
        <div class="offerts-box">
            <div class="offerts-box-targets">
                <div class="offerts-text">
                    <h2>Seleccion de hoy</h2>
                </div>

                <div class="carousel-container">
                    <!-- Flecha izquierda -->
                    <button class="carousel-btn prev-btn">&#8249;</button>

                    <div class="offerts-carousel" id="offertsCarousel">
                        <?php
                        $sql = "SELECT id_producto, nombre, precio, stock, imagen_url 
                        FROM producto 
                        WHERE stock > 0 AND precio <= 100000
                        ORDER BY RAND() 
                        LIMIT 12";

                        $resultado = $connect->query($sql);

                        if ($resultado && $resultado->num_rows > 0):
                            while ($p = $resultado->fetch_assoc()):
                                // URL de la imagen
                                if (!empty($p['imagen_url'])) {
                                    $imagen = $p['imagen_url'];
                                }
                                // Compatibilidad con imágenes locales antiguas
                                elseif (!empty($p['imagen']) && file_exists('../SOURCES/PRODUCTOS/' . $p['imagen'])) {
                                    $imagen = '../SOURCES/PRODUCTOS/' . $p['imagen'];
                                }
                                // Imagen por defecto
                                else {
                                    $imagen = "https://via.placeholder.com/280x280/f0f0f0/999?text=Sin+Foto";
                                }

                                $nombre_corto = strlen($p['nombre']) > 35 ? substr($p['nombre'], 0, 32) . '...' : $p['nombre'];
                        ?>
                                <a href="CONTROLLERS/search-products-product.php?id=<?php echo $p['id_producto']; ?>"
                                    class="carousel-card">
                                    <div class="card">
                                        <div class="card-preview">
                                            <div class="preview-image" style="background-image: url('<?php echo htmlspecialchars($imagen); ?>'); 
                                                background-size: contain; /* CAMBIADO: de cover a contain */
                                                background-position: center;
                                                background-repeat: no-repeat;
                                                background-color: #f9f9f9;"></div>
                                            <div class="preview-description">
                                                <?php echo htmlspecialchars($nombre_corto); ?><br>
                                                <span style="color:#2f2f2fff; font-size:1.5rem; font-family:'Anton';">
                                                    $<?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-info">
                                            <small style="color:<?php echo $p['stock'] < 10 ? '#e74c3c' : '#ffffffff'; ?>">
                                                <?php echo $p['stock'] < 10 ? "¡Solo {$p['stock']}!" : "En stock"; ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                        <?php
                            endwhile;
                        else:
                            echo "<p style='color:#999; padding:40px;'>No hay ofertas hoy</p>";
                        endif;
                        ?>
                    </div>

                    <!-- Flecha derecha -->
                    <button class="carousel-btn next-btn">&#8250;</button>
                </div>
            </div>
        </div>
        <div class="slides-partners-1">
                    <!--Apartado especial de carrusel continuo que muestre marcas asociadas-->
        </div>
        <div class="products-box">
 <div class="offerts-box">
            <div class="offerts-box-targets offerts-box-targets-second">
                <div class="offerts-text">
                    <h2>Nuestros Productos</h2>
                </div>

                <div class="carousel-container">
                    <!-- Flecha izquierda -->
                    <button class="carousel-btn prev-btn">&#8249;</button>

                    <div class="offerts-carousel" id="offertsCarousel">
                        <?php
                        $sql = "SELECT id_producto, nombre, precio, stock, imagen_url 
                        FROM producto 
                        WHERE stock > 0
                        ORDER BY id_producto DESC
                        LIMIT 12";

                        $resultado = $connect->query($sql);

                        if ($resultado && $resultado->num_rows > 0):
                            while ($p = $resultado->fetch_assoc()):
                                // URL de la imagen
                                if (!empty($p['imagen_url'])) {
                                    $imagen = $p['imagen_url'];
                                }
                                // Compatibilidad con imágenes locales antiguas
                                elseif (!empty($p['imagen']) && file_exists('../SOURCES/PRODUCTOS/' . $p['imagen'])) {
                                    $imagen = '../SOURCES/PRODUCTOS/' . $p['imagen'];
                                }
                                // Imagen por defecto
                                else {
                                    $imagen = "https://via.placeholder.com/280x280/f0f0f0/999?text=Sin+Foto";
                                }

                                $nombre_corto = strlen($p['nombre']) > 35 ? substr($p['nombre'], 0, 32) . '...' : $p['nombre'];
                        ?>
                                <a href="CONTROLLERS/search-products-product.php?id=<?php echo $p['id_producto']; ?>"
                                    class="carousel-card">
                                    <div class="card">
                                        <div class="card-preview">
                                            <div class="preview-image" style="background-image: url('<?php echo htmlspecialchars($imagen); ?>'); 
                                                background-size: contain; /* CAMBIADO: de cover a contain */
                                                background-position: center;
                                                background-repeat: no-repeat;
                                                background-color: #f9f9f9;"></div>
                                            <div class="preview-description">
                                                <?php echo htmlspecialchars($nombre_corto); ?><br>
                                                                                         <span style="color:#2f2f2fff; font-size:1.5rem; font-family:'Anton';">
                                                    $<?php echo number_format($p['precio'], 0, ',', '.'); ?>
                                                </span>
                                            </div>
                                        </div>
                                        <div class="card-info">
                                            <small style="color:<?php echo $p['stock'] < 10 ? '#e74c3c' : '#ffffffff'; ?>">
                                                <?php echo $p['stock'] < 10 ? "¡Solo {$p['stock']}!" : "En stock"; ?>
                                            </small>
                                        </div>
                                    </div>
                                </a>
                        <?php
                            endwhile;
                        else:
                            echo "<p style='color:#999; padding:40px;'>No hay ofertas hoy</p>";
                        endif;
                        ?>
                    </div>

                    <!-- Flecha derecha -->
                    <button class="carousel-btn next-btn">&#8250;</button>
                </div>
            </div>
        </div>
        </div>
        <div class="our-stores">

        </div>
    </main>
    <?php include 'TEMPLATES/footer-home.php'?>
    </div>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Seleccionar todos los carruseles
const carousels = document.querySelectorAll('.offerts-carousel');
if (!carousels.length) return;

// Ancho aproximado de cada tarjeta + gap (ajústalo si cambias el diseño)
const cardWidth = 270 * 2;

// Mover a la derecha - SOLUCIÓN 1: Manejar cada carrusel individualmente
const nextBtns = document.querySelectorAll('.next-btn');
nextBtns.forEach((btn, index) => {
    btn.addEventListener('click', () => {
        // Cada botón controla su carrusel correspondiente
        if (carousels[index]) {
            carousels[index].scrollBy({
                left: cardWidth,
                behavior: 'smooth'
            });
        }
    });
});

// Mover a la izquierda
const prevBtns = document.querySelectorAll('.prev-btn');
prevBtns.forEach((btn, index) => {
    btn.addEventListener('click', () => {
        if (carousels[index]) {
            carousels[index].scrollBy({
                left: -cardWidth,
                behavior: 'smooth'
            });
        }
    });
});
            //  ocultar flechas cuando llegas al final
            carousel.addEventListener('scroll', () => {
                prevBtn.style.display = carousel.scrollLeft <= 0 ? 'none' : 'block';
                nextBtn.style.display = (carousel.scrollLeft + carousel.clientWidth >= carousel.scrollWidth - 10) ? 'none' : 'block';
            });
        });
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