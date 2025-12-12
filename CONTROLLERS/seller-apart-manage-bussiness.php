<?php
session_start();
require "../shortCuts/connect.php";

if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit;
}

$idUsuario = $_SESSION['usuario_id'];

$sql = "SELECT * FROM vendedor WHERE id_vendedor = ?";
$stmt = $connect->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<script>
        window.location.href = 'seller-apart-main-view.php';
    </script>";
    exit;
}

$negocio = $result->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard del Vendedor</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            margin: 0;
            padding: 0;
        }

        .header {
            background: #1f2937;
            color: white;
            padding: 20px 30px;
            font-size: 22px;
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 40px auto;
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            margin-top: 0;
            color: #111827;
        }

        .info-box {
            background: #f9fafb;
            border-left: 4px solid #2563eb;
            padding: 15px 20px;
            margin-bottom: 20px;
            border-radius: 6px;
        }

        .btn {
            display: inline-block;
            padding: 12px 18px;
            margin-top: 20px;
            background: #2563eb;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            font-size: 15px;
            transition: 0.2s;
        }

        .btn:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>

    <div class="header">
        Panel del Vendedor 
    </div>

<<<<<<< HEAD
    <!-- Dashboard Container -->
    <div class="dashboard-container">
        <!-- Sidebar -->
        <div class="sidebar">
            <div class="logo">
                <h2>Hermes<span>Seller</span></h2>
            </div>
            <ul class="nav-menu">
                <li><a href="#" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="crear-producto.php"><i class="fas fa-plus-circle"></i> Crear Producto</a></li>
                <li><a href="seller-apart-products.php"><i class="fas fa-box"></i> Mis Productos</a></li>
                <li><a href="categorias-vendedor.php"><i class="fas fa-tags"></i> Mis Categorías</a></li>
                <li><a href="catalogos-vendedor.php"><i class="fas fa-book"></i> Mis Catálogos</a></li>
                <li><a href="pedidos-vendedor.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="editar-negocio.php"><i class="fas fa-store"></i> Editar Negocio</a></li>
                <li><a href="perfil-vendedor.php"><i class="fas fa-user-cog"></i> Mi Perfil</a></li>
                <li><a href="../home.php"><i class="fas fa-home"></i> Volver al Inicio</a></li>
            </ul>
=======
    <div class="container">
        <h2>Información del Negocio</h2>

        <div class="info-box">
            <strong>Nombre de Empresa:</strong> <?php echo $negocio['nombre_empresa']; ?><br>
            <strong>NIT:</strong> <?php echo $negocio['nit']; ?><br>
            <strong>Teléfono:</strong> <?php echo $negocio['telefono_contacto']; ?><br>
            <strong>Correo de Contacto:</strong> <?php echo $negocio['correo_contacto']; ?><br>
            <strong>Ubicación:</strong> <?php echo $negocio['ubicacion']; ?><br>
            <strong>Fecha Registro:</strong> <?php echo $negocio['fecha_registro']; ?>
>>>>>>> parent of 33688e0 (apartado vendedor)
        </div>

        <a href="editar-negocio.php" class="btn">Editar Información</a>
        <a href="../home.php" class="btn" style="background:#dc2626;">volver al inicio</a>
    </div>

</body>
</html>

