<?php
session_start();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Vendedor</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link rel="stylesheet" href="dashboard.css">
    <script src="https://unpkg.com/scrollreveal"></script>
</head>
<body>

    <header>
        <span id="logo-hermes-home"><h1>HERMES</h1></span>
        <span><i class="far fa-home"></i></span>
    </header>

    <div class="container">
        <div class="content">

            <!-- YA ERES VENDEDOR -->
            <div class="login">
                <h2>¿Ya eres Vendedor?</h2>

                <!-- Redirección a gestionar negocio -->
                <a href="gestionar-negocio.php" class="manageBussines">
                    Gestionar Negocio
                </a>
            </div>

            <!-- NO ERES VENDEDOR -->
            <div class="register">
                <h2>¿Aún no eres vendedor?</h2>

                <!-- ESTE ES EL BOTÓN QUE PEDISTE -->
                <a href="crear-negocio.php" class="newBussines">Comenzar a vender</a>
            </div>
        </div>
    </div>

    <main>
        
     <h2 class="scrolling">Hermes Click&Sell</h2>
     <div class="scrolling">
     <section class="scrolling-container-section">
      <p>
    <strong>Hermes Click&Sell</strong> nació con la misión de conectar a personas que desean 
    comercializar sus productos, ya sean nuevos o usados, con un público que busca siempre 
    la mejor calidad y confianza en cada compra. <br>
  </p>
  <p>
    Nuestra plataforma <strong>Hermes Click&Go</strong> ofrece un espacio dinámico y seguro 
    donde vendedores independientes y empresas pueden alojar sus tiendas virtuales, 
    facilitando la interacción directa con potenciales clientes.
  </p>
  <p>
    Con <strong>Hermes Click&Sell</strong> unificamos la experiencia de compra y venta, 
    brindando herramientas que permiten:
  </p>
  <ul>
    <li>Publicar productos de manera rápida y sencilla.</li>
    <li>Acceder a una comunidad activa de compradores interesados.</li>
    <li>Garantizar transacciones seguras y transparentes.</li>
    <li>Impulsar la visibilidad de tu negocio en un entorno digital confiable.</li>
  </ul>
  <p>
    Ya seas un emprendedor que inicia su camino o una empresa consolidada, 
    <strong>Hermes Click&Sell</strong> es tu aliado para crecer en el mundo del comercio electrónico.
  </p>
     </section>
     <aside class="scrolling-container-aside">
        <img src="../SOURCES/IMAGENES/ILUSTRACIONES/girl-pc.svg" alt="imagen de señorita frente a un portatil" width="300px">
     </aside>
     </div>

     <div class="scrolling">
     <section class="scrolling-container-section">
        <h2>Donde comienzo?</h2>
      <p>
    <strong>Hermes Click&Sell</strong> nació con la misión de conectar a personas que desean 
    comercializar sus productos, ya sean nuevos o usados, con un público que busca siempre 
    la mejor calidad y confianza en cada compra. <br>
  </p>
  <p>
    Nuestra plataforma <strong>Hermes Click&Go</strong> ofrece un espacio dinámico y seguro 
    donde vendedores independientes y empresas pueden alojar sus tiendas virtuales, 
    facilitando la interacción directa con potenciales clientes.
  </p>
  <p>
    Con <strong>Hermes Click&Sell</strong> unificamos la experiencia de compra y venta, 
    brindando herramientas que permiten:
  </p>
  <ul>
    <li>Publicar productos de manera rápida y sencilla.</li>
    <li>Acceder a una comunidad activa de compradores interesados.</li>
    <li>Garantizar transacciones seguras y transparentes.</li>
    <li>Impulsar la visibilidad de tu negocio en un entorno digital confiable.</li>
  </ul>
  <p>
    Ya seas un emprendedor que inicia su camino o una empresa consolidada, 
    <strong>Hermes Click&Sell</strong> es tu aliado para crecer en el mundo del comercio electrónico.
  </p>
     </section>
     <aside class="scrolling-container-aside">
        <img src="../SOURCES/IMAGENES/ILUSTRACIONES/notebook.svg" alt="imagen de señorita frente a un portatil" width="300px">
     </aside>
     </div>
     
    </main>

    <script src="dashboardseller2.js"></script>
</body>
</html>