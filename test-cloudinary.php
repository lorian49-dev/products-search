<?php
// test-cloudinary.php

// Cargar la configuración y obtener la instancia
$cloudinary = require_once "shortCuts/cloudinary-config.php";

echo "<h1>¡CLOUDINARY V3 FUNCIONA!</h1>";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['foto'])) {
    try {
        $result = $cloudinary->uploadApi()->upload(
            $_FILES['foto']['tmp_name'],
            [
                'folder' => 'hermes/pruebas/',
                'use_filename' => true,
                'overwrite' => true
            ]
        );
        echo "<h2>¡Imagen subida con éxito!</h2>";
        echo "<img src='{$result['secure_url']}' width='600'>";
        echo "<br><br>URL: <code>{$result['secure_url']}</code>";
    } catch (Exception $e) {
        echo "<p style='color:red;'>Error: " . $e->getMessage() . "</p>";
    }
}
?>

<form method="POST" enctype="multipart/form-data">
    <input type="file" name="foto" accept="image/*" required>
    <br><br>
    <button type="submit" style="padding:10px 20px; font-size:16px;">Subir imagen</button>
</form>