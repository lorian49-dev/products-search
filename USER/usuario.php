<?php
session_start();
include("../registros-inicio-sesion/connect.php");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión iniciada.");
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$sql = "SELECT * FROM usuario WHERE id_usuario = $usuario_id";
$result = mysqli_query($connect, $sql);
$usuario = mysqli_fetch_assoc($result);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <title>Usuario</title>
    <link rel="stylesheet" href="../home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <style>
        .modalWindow {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            opacity: 0;
            pointer-events: none;
            transition: .3s;
        }

        .modalContainer {
            width: 450px;
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            margin: 120px auto 0 auto;
            transform: translateY(-100%);
            opacity: 0;
            pointer-events: none;
            transition: .3s;
        }

        #btnEdit{
            padding: 10px 18px;
            background: red;
            color: #fff;
            border: none;
            border-radius: 8px;
            cursor: pointer;
        }

        .back-icon {
            cursor: pointer;
            font-size: 20px;
        }
    </style>
</head>

<body>
    <header>
        <div class="top">
            <span id="logo-hermes-home">
                <h1>HERMES</h1>
            </span>
            <ul style="list-style:none;">
                <div class="input-search-product-box">
                    <form action="../buscar.php" method="GET" style="width:100%">
                        <li class="input-search-product-li">
                            <input
                                type="text"
                                name="search-product"
                                id="input-search-product"
                                placeholder="Buscar producto..."
                                value="" autocomplete="off">
                                <button type="submit" class="button-search"><i class="fa-solid fa-magnifying-glass"></i></button>
                            <div id="results-container"></div>
                        </li>
                    </form>

                    </li>
                </div>
            </ul>
        </div>
        <div class="bottom">
            <nav>
                <ul>
                    <li><span id="span-menu-categoria">Categorias</span>
                        <div id="menu-categoria" class="menu-categoria">
                            <ul>
                                <li>Electrodomesticos</li>
                                <li>Tecnologia</li>
                                <li>Hogar</li>
                                <li>Moda</li>
                                <li>Deportes</li>
                                <li>Belleza</li>
                                <li>Jugueteria</li>
                                <li>Automotriz</li>
                                <li>Electronica</li>
                                <li>Mascotas</li>
                                <li>Arte</li>
                            </ul>
                        </div>
                    </li>
                    <?php if (isset($_SESSION['usuario_nombre'])): ?>
                        <li><span id="venderPage">Vender</span></li>
                    <?php endif; ?>
                    <li><span id="ayuda-listado">Ayuda</span>
                        <div id="menu-ayuda" class="menu-categoria">
                            <ul>
                                <li>Informacion</li>
                                <li>PQRS</li>
                                <li>Contactos</li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>

            <div class="account-header">
                <!-- perfil usuario -->
                <?php if (isset($_SESSION['usuario_nombre'])): ?>
                    <div class="perfil-menu">
                        <button class="perfil-btn"> <?php echo htmlspecialchars($_SESSION['usuario_nombre']); ?></button>
                        <div class="dropdown-content">
                            <a href="../USER/usuario.php">Mi cuenta</a>
                            <a href="../registros-inicio-sesion/logout.php">Cerrar sesión</a>
                        </div>
                    </div>
                <?php else: ?>
                    <a href="../registros-inicio-sesion/login.html"><span class="sisu-buttons"> Sign In</span></a>
                    <a href="../registros-inicio-sesion/register.html"><span class="sisu-buttons"> Sign Up</span></a>
                <?php endif; ?>
                <!-- fin del menu despegable -->
            </div>
            <div class="icons-header">
                <span><img src="../SOURCES/ICONOS-LOGOS/bookmark.svg" alt="wishlist"></span>
                <span><img src="../SOURCES/ICONOS-LOGOS/shopping_bag.svg" alt="Shopping Cart"></span>
            </div>
        </div>
    </header>

    <h1>Perfil del Usuario</h2>
        <div class="perfil-container">
            <p><strong>Nombre: </strong><?= $usuario['nombre'] . " " . $usuario['apellido']; ?></p>
            <p><strong>Correo:</strong> <?= $usuario['correo'] ?></p>
            <p><strong>Fecha de nacimiento:</strong> <?= $usuario['fecha_nacimiento'] ?></p>
            <p><strong>Teléfono:</strong> <?= $usuario['telefono'] ?></p>
            <p><strong>Dirección principal:</strong> <?= $usuario['direccion_principal'] ?></p>

        </div>
        <button id="btnEdit">Editar Perfil</button>
        <a href="../home.php" class="btn" style="background:#dc2626;">volver al inicio</a>



        <!-- Modal -->
        <div class="modalWindow">
            <div class="modalContainer">

                <span class="back-icon">⟵</span>
                <h2>Editar datos</h2>

                <!-- EL FORMULARIO APUNTA A editar_usuario.php -->
                <form method="POST" action="editar_usuario.php">

                    <!-- IMPORTANTE: enviar el ID -->
                    <input type="hidden" name="id_usuario" value="<?= $usuario['id_usuario'] ?>">
                    <input type="text" name="name" value="<?= $usuario['nombre'] ?>" placeholder="Nombre">
                    <input type="text" name="lastname" value="<?= $usuario['apellido'] ?>" placeholder="Apellido">
                    <input type="email" name="email" value="<?= $usuario['correo'] ?>" placeholder="Correo">
                    <input type="password" name="password" value="<?= $usuario['contrasena'] ?>" placeholder="Contraseña">
                    <input type="date" name="birthday" value="<?= $usuario['fecha_nacimiento'] ?>">
                    <input type="text" name="phone" value="<?= $usuario['telefono'] ?>" placeholder="Teléfono">
                    <input type="direction" name="direction" value="<?= $usuario['direccion_principal'] ?>" placeholder="Direccion Principal">

                    <button type="submit">Guardar cambios</button>
                </form>

            </div>
        </div>
        <script src="usuario.js"></script>
</body>

</html>