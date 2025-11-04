<?php
include("../registros-inicio-sesion/connect.php");

if (!isset($_GET['id'])) {
    die("No se recibió el ID del producto");
}

$id = $_GET['id'];
$sql = "SELECT * FROM producto WHERE ID_Producto = $id";
$resultado = $connect->query($sql);

if (!$resultado || $resultado->num_rows == 0) {
    die("No se encontró el producto con ID $id");
}

$fila = $resultado->fetch_assoc();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = $_POST["nombre"];
    $descripcion = $_POST["descripcion"];
    $precio = $_POST["precio"];
    $stock = $_POST["stock"];

    $sql_update = "UPDATE producto 
                   SET nombre='$nombre', descripcion='$descripcion', precio='$precio', stock='$stock'
                   WHERE ID_Producto=$id";

    if ($connect->query($sql_update)) {
        header("Location: index.php");
        exit;
    } else {
        echo "Error al actualizar: " . $connect->error;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto</title>
</head>
<body>
    <h1>Editar Producto</h1>
    <form method="POST">
        <label>Nombre:</label><br>
        <input type="text" name="nombre" value="<?php echo htmlspecialchars($fila['nombre']); ?>" required><br><br>

        <label>Descripción:</label><br>
        <textarea name="descripcion" rows="4"><?php echo htmlspecialchars($fila['descripcion']); ?></textarea><br><br>

        <label>Precio:</label><br>
        <input type="number" name="precio" step="0.01" value="<?php echo htmlspecialchars($fila['precio']); ?>" required><br><br>

        <label>Stock:</label><br>
        <input type="number" name="stock" value="<?php echo htmlspecialchars($fila['stock']); ?>" required><br><br>

        <input type="submit" value="Actualizar">
    </form>

    <p><a href="index.php">⬅️ Volver</a></p>
</body>
</html>
