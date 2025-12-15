<?php
// test-conexion.php
echo "<h2>Prueba de conexión</h2>";

// Prueba 1: Ruta al connect.php
$ruta_connect = __DIR__ . '/shortCuts/connect.php';
echo "Ruta a connect.php: " . $ruta_connect . "<br>";

if (file_exists($ruta_connect)) {
    echo "El archivo connect.php existe<br>";
    
    // Incluir el archivo
    require_once $ruta_connect;
    
    if ($connect) {
        echo "Conexión a BD establecida<br>";
        
        // Probar consulta
        $sql = "SELECT 1 as test";
        $result = mysqli_query($connect, $sql);
        
        if ($result) {
            echo "Consulta SQL funciona<br>";
        } else {
            echo "Error en consulta SQL: " . mysqli_error($connect) . "<br>";
        }
    } else {
        echo "Error: \$connect no está definido<br>";
    }
} else {
    echo "ERROR: No se encuentra connect.php en: " . $ruta_connect . "<br>";
    
    // Buscar el archivo
    echo "Buscando connect.php...<br>";
    $files = glob(__DIR__ . '/*/connect.php');
    foreach ($files as $file) {
        echo "Encontrado: " . $file . "<br>";
    }
}

echo "<hr><h3>Prueba desde CONTROLLERS:</h3>";
$ruta_desde_controllers = __DIR__ . '/CONTROLLERS/test.php';
echo "Ruta sería: " . realpath(__DIR__ . '/../shortCuts/connect.php');
?>