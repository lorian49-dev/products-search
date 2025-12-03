<?php include("../shortCuts/connect.php"); ?>

<?php
$id = $_GET["id"];
$connect->query("DELETE FROM producto WHERE id_producto=$id");
header("Location: product-seller-index.php");
?>
