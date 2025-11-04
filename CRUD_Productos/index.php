<?php include('registros-inicio-sesion/session-check.php'); ?>
<?php
include("../registros-inicio-sesion/connect.php");

$sql = "SELECT * FROM producto";
$resultado = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <style>
        table {
            width: 90%;
            border-collapse: collapse;
            margin: 20px auto;
        }
        th, td {
            border: 1px solid #ccc;
            padding: 10px;
            text-align: center;
        }
        th {
            background: #333;
            color: white;
        }
        a {
            text-decoration: none;
            color: blue;
        }
        a.borrar {
            color: red;
        }
    </style>
</head>
<body>
    <h1 style="text-align:center;">Lista de Productos</h1>
    <p style="text-align:center;"><a href="agregar.php">Agregar nuevo producto</a></p>

    <table>
        <tr>
            <th>ID</th>
            <th>Nombre</th>
            <th>Descripción</th>
            <th>Precio</th>
            <th>Stock</th>
            <th>Acciones</th>
        </tr>

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
