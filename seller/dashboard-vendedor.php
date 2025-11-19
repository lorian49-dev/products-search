<?php
session_start();

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.html");
    exit();
}

// Conexión a BD
include('../registros-inicio-sesion/connect.php');

// ID del usuario = ID del vendedor también
$usuario_id = $_SESSION['usuario_id'];

// Verificar si ya es vendedor
$stmt = $connect->prepare("SELECT id_vendedor FROM vendedor WHERE id_vendedor = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$result = $stmt->get_result();

$vendedor = $result->fetch_assoc();
$id_vendedor = $vendedor['id_vendedor'] ?? null;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel del Vendedor</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f4f4f4;
            margin: 0;
            padding: 0;
        }
        header {
            background: #333;
            color: white;
            padding: 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }
        header h1 { margin: 0; }
        .container {
            width: 90%;
            max-width: 700px;
            background: white;
            margin: 40px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0px 0px 6px rgba(0,0,0,0.2);
            text-align: center;
        }
        .btn {
            display: block;
            width: 80%;
            margin: 15px auto;
            padding: 12px;
            background: #007bff;
            color: white;
            text-decoration: none;
            border-radius: 8px;
            font-size: 18px;
        }
        .btn:hover { background: #0056b3; }
        .info {
            font-size: 18px;
            padding: 10px;
            margin-bottom: 20px;
        }
    </style>

</head>
<body>

<header>
    <h1>HERMES - Vendedor</h1>
    <a href="../home.php" style="color:white;"><i class="fas fa-home"></i></a>
</header>

<div class="container">
    <h2>Panel del Vendedor</h2>

    <?php if ($esVendedor): ?>
        <!-- YA ES VENDEDOR -->
        <div class="info">Ya tienes un negocio registrado.</div>

        <!-- BOTÓN PARA IR AL CRUD -->
        <a class="btn" href="../CRUD/Admin_CRUD.php">Gestionar mi Negocio</a>

    <?php else: ?>
        <!-- NO ES VENDEDOR -->
        <div class="info">
            Aún no has registrado tu negocio. <br>
            Crea tu negocio para comenzar a vender.
        </div>

        <!-- BOTÓN PARA CREAR NEGOCIO -->
        <a class="btn" href="crear-negocio.php">Crear mi Negocio</a>
    <?php endif; ?>

</div>

</body>
</html>
