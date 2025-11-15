<?php
session_start();

// 2. Conexión a la base de datos
$conexion = new mysqli("localhost", "root", "", "hermes_bd");

if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// 3. Si el formulario fue enviado, procesar datos
if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $usuario_id = $_SESSION['id_usuario'];
    $nombre = $_POST['nombre_negocio'];
    $descripcion = $_POST['descripcion'];
    $correo = $_POST['correo'];
    $direccion = $_POST['direccion'];
    $telefono = $_POST['telefono'];

    $sql = "INSERT INTO usuario (id_usuario, nombre, correo, descripcion, direccion, telefono)
            VALUES (?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("issssss", $usuario_id, $nombre, $descripcion, $direccion, $telefono, $categoria);

    if ($stmt->execute()) {
        echo "<script>
            alert('¡Negocio creado correctamente!');
            window.location.href='dashboard-vendedor.php';
        </script>";
        exit();
    } else {
        echo "Error: " . $conexion->error;
    }

    $stmt->close();
    $conexion->close();
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Crear Negocio</title>
    <style>
        body{
            font-family: Arial;
            background: #f5f5f5;
        }
        form{
            background: white;
            width: 400px;
            margin: 40px auto;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.2);
        }
        input, textarea, select{
            width: 100%;
            padding: 10px;
            margin-top: 8px;
            margin-bottom: 15px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }
        button{
            background: #ff9800;
            border: none;
            padding: 12px;
            width: 100%;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
        }
        button:hover{
            background: #e68900;
        }
    </style>
</head>
<body>

<h2 style="text-align:center; margin-top:20px;">Crear Tu Negocio</h2>

<form method="POST">

    <label>Nombre del Negocio:</label>
    <input type="text" name="nombre_negocio" required>
    
    <label>correo:</label>
    <input type="text" name="correo" required>

    <label>Descripción:</label>
    <textarea name="descripcion" rows="4" required></textarea>

    <label>Dirección (si aplica):</label>
    <input type="text" name="direccion">

    <label>Teléfono:</label>
    <input type="text" name="telefono" required>

    <button type="submit">Crear Negocio</button>

</form>

</body>
</html>
