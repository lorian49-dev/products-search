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

if ($result->num_rows > 0) {
    echo "<script>
        alert('Ya tienes un negocio registrado.');
        window.location.href = 'seller-apart-manage-bussiness.php'
    </script>";
    exit;
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nombre_empresa = $_POST['nombre_empresa'];
    $nit = $_POST['nit'];
    $telefono = $_POST['telefono'];
    $ubicacion = $_POST['ubicacion'];
    $correo_contacto = $_POST['correo_contacto'];
    $fecha_registro = date("Y-m-d");

    $sqlInsert = "INSERT INTO vendedor 
        (id_vendedor, nombre_empresa, nit, telefono_contacto, ubicacion, correo_contacto, fecha_registro)
        VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmtInsert = $connect->prepare($sqlInsert);
    $stmtInsert->bind_param(
        "issssss",
        $idUsuario,
        $nombre_empresa,
        $nit,
        $telefono,
        $ubicacion,
        $correo_contacto,
        $fecha_registro
    );

    if ($stmtInsert->execute()) {
        echo "<script>
            alert('Negocio creado exitosamente');
            window.location.href = 'seller-apart-manage-bussiness.php';
        </script>";
        exit;
    } else {
        echo "<script>alert('Error al registrar el negocio');</script>";
    }
}

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Negocio</title>

    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f3f4f6;
            padding: 40px;
        }

        .form-container {
            width: 450px;
            margin: auto;
            background: #ffffff;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0px 4px 10px rgba(0,0,0,0.1);
        }

        h2 {
            text-align: center;
            margin-bottom: 25px;
            color: #1f2937;
        }

        label {
            display: block;
            font-weight: bold;
            margin-top: 15px;
            color: #374151;
        }

        input[type="text"],
        input[type="email"] {
            width: 100%;
            padding: 10px;
            border: 1px solid #d1d5db;
            border-radius: 6px;
            margin-top: 5px;
            font-size: 14px;
        }

        .btn {
            margin-top: 20px;
            width: 100%;
            padding: 12px;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-size: 16px;
            transition: .2s;
        }

        .btn:hover {
            background: #1e40af;
        }

        .terms {
            margin-top: 15px;
            font-size: 14px;
        }

        .terms a {
            color: #2563eb;
            text-decoration: none;
        }

        .terms a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Registrar Nuevo Negocio</h2>

    <form action="" method="POST">

        <label>Nombre de la Empresa</label>
        <input type="text" name="nombre_empresa" required>

        <label>NIT</label>
        <input type="text" name="nit" required>

        <label>Teléfono</label>
        <input type="text" name="telefono" required>

        <label>Ubicación</label>
        <input type="text" name="ubicacion" required>

        <label>Correo de Contacto</label>
        <input type="email" name="correo_contacto">

        <div class="terms">
            <input type="checkbox" name="terminos" required>
            Acepto los <a href="terminos.php">términos y condiciones</a> <!--Aun no existe dicho documento de redireccion, debe cambiarse el nombre-->
        </div>

        <button type="submit" class="btn">Registrar Negocio</button>
    </form>
</div>

</body>
</html>
