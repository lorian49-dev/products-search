<?php
include('../registros-inicio-sesion/session-check.php');
include("../shortCuts/connect.php");

$sql = "SELECT * FROM producto";
$resultado = $connect->query($sql);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Lista de Productos</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
@import url('https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&display=swap');

*{
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body{
    font-family: 'roboto condensed', sans-serif;
    background-color:#fff8f1;
    font-weight: 300;
}

table {
    border-collapse: separate;
    border-spacing: 0;
    table-layout: fixed;
    text-align: center;
    border-radius: 25px;
    overflow: hidden;
    box-shadow: 1px 1px 5px rgba(0, 0, 0, 0.514);
}
    
thead{
    background-color: #461d01;
    color: #fff8f1;    
}

th{
  font-family: 'roboto condensed';
  font-weight: 300;
  padding: 15px;
}

td{
    padding: 10px;
    font-size: small;
}
        
a {
color: gray;

}
    </style>
</head>
<body>

<nav>
 
</nav>

    <h1 style="text-align:center;">Lista de Productos</h1>
    <p style="text-align:center;"><a href="product-seller-add.php">Agregar nuevo producto</a></p>

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
                        <a href='product-seller-edit.php?id=" . $fila["id_producto"] . "'>Editar</a> |
                        <a class='borrar' href='product-seller-delete.php?id=" . $fila["id_producto"] . "' 
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
