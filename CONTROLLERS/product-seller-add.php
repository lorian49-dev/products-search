<?php include('../registros-inicio-sesion/session-check.php'); ?>
<?php include("../shortCuts/connect.php"); ?>

<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql = "INSERT INTO producto (nombre, descripcion, precio, stock)
        VALUES ('$nombre', '$descripcion', '$precio', '$stock')";
    $connect->query($sql);

    header("Location: product-seller-index.php");
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Agregar Producto</title>
</head>
<body>
  <h1>Agregar Nuevo Producto</h1>
  <form method="POST">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" required><br><br>

    <label>Descripción:</label><br>
    <textarea name="descripcion" rows="4"></textarea><br><br>

    <label>Precio:</label><br>
    <input type="number" name="precio" step="0.01" required><br><br>

    <label>Stock:</label><br>
    <input type="number" name="stock" required><br><br>

    <input type="submit" value="Guardar">
  </form>
  <br>
  <a href="product-seller-index.php">⬅️ Volver</a>
</body>
</html>
