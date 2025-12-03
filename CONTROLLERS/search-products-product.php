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
</head>

<body>

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
    <a href="pago.php?id=<?php echo $producto['id_producto']; ?>"  
        style="padding:10px 20px; background:#8B4513; color:white; text-decoration:none; border-radius:5px;"> <!--CAMBIAR RUTAS-->
        Comprar ahora
    </a>

    <br><br>

    <a class="btn-volver" href="search-products.php?search-product=<?php echo urlencode($_GET['search-product'] ?? ''); ?>">← Volver a resultados</a>



</body>

</html>