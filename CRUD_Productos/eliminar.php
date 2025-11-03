<?php include("../registros-inicio-sesion/connect.php"); ?>

<?php
$id = $_GET["id"];
$conexion->query("DELETE FROM producto WHERE ID_Producto=$id");
header("Location: index.php");
?>
