<?php
// shortCuts/cloudinary-config.php

require_once __DIR__ . '/../vendor/autoload.php';

use Cloudinary\Cloudinary;
use Cloudinary\Configuration\Configuration;

// FORMA CORRECTA PARA LA VERSIÓN 3.x
$cloudinary = new Cloudinary([
    'cloud' => [
        'cloud_name' => 'dwetjdmaz',
        'api_key'    => '256388527739759',
        'api_secret' => 'xfQG_uXuGYuZPg4fAypkuK0I7zs'
    ],
    'url' => [
        'secure' => true
    ]
]);

// Exportamos la instancia para usarla en otros archivos
return $cloudinary;
?>