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
    <link rel="stylesheet" href="../styles/main-seller-view.css">
    <script src="https://unpkg.com/scrollreveal"></script>
</head>
<body>
    <div class="body-container">
    <header>
        <span id="logo-hermes-home"><h1>HERMES</h1></span>
        <span><i class="far fa-home"></i></span>
    </header>

    <div class="container">
      <div class="slogan">
        <h2>Con Hermes Click&Sell Impulsa Tu negocio y comienza a vender sin costos iniciales</h2>
        <ul>
          <li>Registro gratuito</li>
          <li>Pagos seguros</li>
          <li>Pagas el 3% de comision los primeros 2 meses</li>
          <li>Creditos para Impulsar tus ventas</li>
        </ul>
        <h3>Y mucho mas...</h2>
      </div>
        <div class="content">

            <!-- NO ERES VENDEDOR -->
            <div class="register">
                <h2>¿Que estas esperando?</h2>

                <!-- ESTE ES EL BOTÓN QUE PEDISTE -->
                <a href="seller-apart-create-bussiness.php" class="newBussines">Registrate y abre tu tienda HOY</a>
            </div>
        </div>
    </div>

    <main>
    
    <div class="incentives">
      <section class="incentives-container-section">
       <div class="incentives-left">

       </div>
       <div class="incentives-right">
        <div class="right-cloud"><span><img src="../SOURCES/ICONOS-LOGOS/Logotype-reward.png" width="30px" alt=""></span><p>Recibes 100.000 COP por publicar tus primeros 10 productos</p></div>
        <div class="right-cloud"><span><img src="../SOURCES/ICONOS-LOGOS/Logotype-reward.png" width="30px" alt=""></span><p>No te cobramos comision tus primeras 5 ventas</p></div>
        <div class="right-cloud"><span><img src="../SOURCES/ICONOS-LOGOS/Logotype-reward.png" width="30px" alt=""></span><p>Tokens para visibilizar productos</p></div>
       </div>
      </section>
    </div>

     <div class="scrolling">
     <section class="scrolling-container-section">    
      <h2>Hermes Click&Sell</h2>
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
        <img src="../SOURCES/IMAGENES/ILUSTRACIONES/girl-pc.png" alt="imagen de señorita frente a un portatil" width="300px">
     </aside>
     </div>

     <div class="scrolling">
     <section class="scrolling-container-section">
        <h2>¿Por donde comienzo?</h2>
      <p>
       Si aun no estas familiarizado con nuestra plataforma, te invitamos a leer nuestro <a href="#">Manual de Usuario</a> , el cual tambien podrás encontrar en la pestaña de <a href="#">soporte</a> .
      <p>
      <p>
       Luego de registrar los datos de tu negocio o empresa, serás redirigido a un apartado especialmente diseñado para ti como vendedor, podras elegir entre un panel con distribucion simple o mas profesional, dependiendo de lo que mas se adapte a tus necesidades.
      </p>
      <p>
        En el panel de administracion para mi como vendedor gestionaras tus productos, catalogos, las categorias que manejaras, pagos y transacciones, ademas de una seccion personalizada de chat directamente con el cliente, con el cual este podra resolver sus dudas de forma mas personal, este chat se limitara con su apertura desde que se envia el producto hasta este mismo llega a las manos del comprador.
      </p>
      <p>
        Los productos podran ser editados, eliminados(Si alguien anteriormente compro el producto y se elimina, este pasara a ya no estar disponible hasta que llegue a las manos del comprador), 
      </p>
     </section>
     <aside class="scrolling-container-aside">
        <img src="../SOURCES/IMAGENES/ILUSTRACIONES/notebook.png" alt="imagen de señorita frente a un portatil" width="300px">
     </aside>
     </div>
     
    </main>
    <?php include '../TEMPLATES/footer.php'?>
    </div>
    <script src="../scripts/seller-main-view.js"></script>
</body>
</html>

