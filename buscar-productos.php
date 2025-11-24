<?php
//Variables para la conexion a la base de datos
 $SERVIDORH= "localhost";
 $USUARIOH = "root";
 $PASSWORDH = "";
 $BASE = "hermes_bd";


 //Verificacion de la conexion
 $ENLACE = mysqli_connect($SERVIDORH, $USUARIOH, $PASSWORDH, $BASE);
if (!$ENLACE) {
    die("Conexión fallida: " . mysqli_connect_error());
}
$termino = $_GET['term'] ?? '';
// Buscamos productos cuyo nombre contenga el término de búsqueda
$sql = "SELECT nombre FROM producto WHERE nombre LIKE ?";
$stmt = $ENLACE_PRODUCTOS->prepare($sql);

$param = "%" . $termino . "%";
$stmt->bind_param("s", $param);

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();

$productos = [];
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila['nombre'];
}

header('Content-Type: application/json; charset=utf-8');
echo json_encode($productos);

// Cerrar conexión
$stmt->close();
$ENLACE->close();
?>