<?php
include('../registros-inicio-sesion/session-check.php');
include("../registros-inicio-sesion/connect.php");

$sql = "SELECT * FROM producto";
$resultado = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <link rel="stylesheet" href="index.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
</head>
<body>

<nav>
 
</nav>

    <h1 style="text-align:center;">Lista de Productos</h1>
    <p style="text-align:center;"><a href="agregar.php">Agregar nuevo producto</a></p>

    <table>
        <thead>
            <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>
        </thead>

        <?php
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                echo "<tr>";
                echo "<td>" . $fila["id_producto"] . "</td>";
                echo "<td>" . $fila["nombre"] . "</td>";
                echo "<td>" . $fila["descripcion"] . "</td>";
                echo "<td>" . $fila["precio"] . "</td>";
                echo "<td>" . $fila["stock"] . "</td>";
                echo "<td>
                        <a href='editar.php?id=" . $fila["id_producto"] . "'>Editar</a> |
                        <a class='borrar' href='eliminar.php?id=" . $fila["id_producto"] . "' 
                           onclick=\"return confirm('¿Estás seguro de eliminar este producto?');\">Eliminar</a>
                      </td>";
                echo "</tr>";
            }
        } else {
            echo "<tr><td colspan='6'>No hay productos registrados</td></tr>";
        }
        ?>
    </table>
</body>
</html>
