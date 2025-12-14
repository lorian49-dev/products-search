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
    
        <style>
    .btn-volver:hover {
        background: #5a6268;
        transform: translateX(-3px);
        transition: all 0.3s;
    }
    
    .product-image-gallery {
        display: flex;
        gap: 10px;
        margin-top: 10px;
    }
    
    .thumbnail {
        width: 80px;
        height: 80px;
        object-fit: cover;
        border-radius: 5px;
        border: 2px solid transparent;
        cursor: pointer;
    }
    
    .thumbnail:hover {
        border-color: #8B4513;
    }
    
    .product-info-section {
        margin-bottom: 30px;
    }
    
    .product-info-section h3 {
        color: #495057;
        border-bottom: 2px solid #8B4513;
        padding-bottom: 5px;
        margin-bottom: 15px;
    }
    
    .stock-badge {
        display: inline-block;
        padding: 5px 10px;
        border-radius: 20px;
        font-size: 0.9rem;
        font-weight: bold;
    }
    
    .stock-available {
        background: #d4edda;
        color: #155724;
    }
    
    .stock-low {
        background: #fff3cd;
        color: #856404;
    }
    
    .stock-out {
        background: #f8d7da;
        color: #721c24;
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
   <div style="max-width:1200px; margin:0 auto; padding:20px;">
    <div style="display:flex; flex-wrap:wrap; gap:40px; margin-bottom:30px;">
        <!-- Sección de imagen -->
        <div style="flex:1; min-width:300px;">
            <?php
            // Determinar la URL de la imagen
            if (!empty($producto['imagen_url'])) {
                $imagen_url = $producto['imagen_url'];
            } elseif (!empty($producto['imagen'])) {
                $imagen_url = '../SOURCES/PRODUCTOS/' . $producto['imagen'];
            } else {
                $imagen_url = '../SOURCES/PRODUCTOS/default.png';
            }
            ?>
            
            <div style="width:100%; max-width:500px; height:400px; margin:0 auto;">
                <img src="<?php echo $imagen_url; ?>" 
                     alt="<?php echo htmlspecialchars($producto['nombre']); ?>"
                     style="width:100%; height:100%; object-fit:contain; border-radius:10px; border:1px solid #eee; padding:10px; background:#f9f9f9;"
                     onerror="this.src='../SOURCES/PRODUCTOS/default.png'; this.style.objectFit='cover';">
            </div>
        </div>
        
        <!-- Sección de información -->
        <div style="flex:1; min-width:300px;">
            <h1 style="color:#333; margin-bottom:15px; font-size:2rem;"><?php echo htmlspecialchars($producto['nombre']); ?></h1>
            
            <div style="margin-bottom:20px;">
                <span style="font-size:1.8rem; color:#8B4513; font-weight:bold;">$<?php echo number_format($producto['precio'], 0, ',', '.'); ?></span>
                <span style="margin-left:10px; color:#28a745; font-weight:bold;">
                    <?php echo $producto['stock'] > 0 ? '✓ Disponible' : '✗ Agotado'; ?>
                </span>
            </div>
            
            <div style="margin-bottom:25px;">
                <p style="color:#666; line-height:1.6;"><?php echo nl2br(htmlspecialchars($producto['descripcion'])); ?></p>
            </div>
            
            <div style="background:#f8f9fa; padding:15px; border-radius:8px; margin-bottom:25px;">
                <h3 style="margin-top:0; color:#495057;">Detalles del producto</h3>
                <p><strong>Stock disponible:</strong> <?php echo $producto['stock']; ?> unidades</p>
                <p><strong>Origen/Marca:</strong> <?php echo htmlspecialchars($producto['origen']); ?></p>
                
                <?php if (isset($producto['nombre_empresa'])): ?>
                    <p><strong>Vendedor:</strong> <?php echo htmlspecialchars($producto['nombre_empresa']); ?></p>
                <?php endif; ?>
                
                <?php if (isset($producto['categorias'])): ?>
                    <p><strong>Categorías:</strong> <?php echo htmlspecialchars($producto['categorias']); ?></p>
                <?php endif; ?>
            </div>
            
            <!-- Botones de acción -->
            <div style="display:flex; gap:15px; flex-wrap:wrap;">
                <?php if ($producto['stock'] > 0): ?>
                    <a href="product-compra.php?id=<?php echo $producto['id_producto']; ?>"
                        style="padding:15px 30px; background:#8B4513; color:white; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-shopping-bag"></i> Comprar ahora
                    </a>
                    
                    <a href="agregar-carrito.php?id=<?php echo $producto['id_producto']; ?>&cantidad=1"
                        style="padding:15px 30px; background:#28a745; color:white; text-decoration:none; border-radius:5px; font-weight:bold; display:inline-flex; align-items:center; gap:8px;">
                        <i class="fas fa-cart-plus"></i> Agregar al carrito
                    </a>
                <?php else: ?>
                    <button style="padding:15px 30px; background:#6c757d; color:white; border:none; border-radius:5px; font-weight:bold; cursor:not-allowed;">
                        <i class="fas fa-times-circle"></i> Producto agotado
                    </button>
                    <button style="padding:15px 30px; background:#17a2b8; color:white; border:none; border-radius:5px; font-weight:bold;">
                        <i class="fas fa-bell"></i> Notificarme cuando esté disponible
                    </button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Botón volver -->
    <div style="margin-top:40px;">
        <a class="btn-volver" href="search-products.php?search-product=<?php echo urlencode($_GET['search-product'] ?? ''); ?>"
           style="display:inline-flex; align-items:center; gap:8px; padding:10px 20px; background:#6c757d; color:white; text-decoration:none; border-radius:5px;">
            <i class="fas fa-arrow-left"></i> Volver a resultados
        </a>
    </div>
</div>
    <script src="../scripts/search-product.js" ;></script>
</body>

</html>
