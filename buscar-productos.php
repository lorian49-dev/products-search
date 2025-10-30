<?php
//Variables para la conexion a la base de datos
 $SERVIDORH= "localhost";
 $USUARIOH = "root";
 $PASSWORDH = "";
 $BASE = "hermes_bd";


 //Verificacion de la conexion
 $ENLACE_PRODUCTOS = mysqli_connect($SERVIDORH, $USUARIOH, $PASSWORDH, $BASE);
if (!$ENLACE_PRODUCTOS) {
    die("Conexión fallida: " . mysqli_connect_error());
}

$termino = $_GET['term'] ?? '';
// Buscamos productos cuyo nombre contenga el término de búsqueda
$sql = "SELECT Nombre_Producto FROM producto WHERE Nombre_Producto LIKE ?";
$stmt = $ENLACE_PRODUCTOS->prepare($sql);

$param = "%" . $termino . "%";
$stmt->bind_param("s", $param);

// Ejecutar la consulta
$stmt->execute();
$resultado = $stmt->get_result();

$productos = [];
while ($fila = $resultado->fetch_assoc()) {
    $productos[] = $fila['Nombre_Producto'];
}

header('Content-Type: application/json');
echo json_encode($productos);

// Cerrar conexión
$stmt->close();
$ENLACE_PRODUCTOS->close();

?>