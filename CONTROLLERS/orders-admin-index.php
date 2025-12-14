<?php

session_start();

/* Verifica si está logueado como admin */
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';
    </script>";
    exit();
}

/* Verifica rol permitido */
$rolesPermitidos = [1, 2];

if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "hermes_bd");
if ($conn->connect_error) {
    die("Error de conexión");
}


/*      BUSCADOR VENTAS      */

$busqueda = $_GET['busqueda'] ?? '';
$where = '';

if (!empty($busqueda)) {
    $busqueda = $conn->real_escape_string($busqueda);
    $where = "WHERE 
        v.nombre_empresa LIKE '%$busqueda%' OR
        pr.nombre LIKE '%$busqueda%' OR
        u.nombre LIKE '%$busqueda%' OR
        u.apellido LIKE '%$busqueda%' OR
        p.estado LIKE '%$busqueda%'";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
     <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css"> 
    <style>
        *{
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        main{
            margin-left: 15%;
        }
        /*Estilos de la barra de navegacion a la izquierda de la pagina*/

#navegation{
    background-color: #461d01;
    height: 100vh;
    width: 15%;
    display: flex;
    align-items: center;
    flex-direction: column;
    overflow: auto;
    padding-bottom: 5rem;
    position: fixed;
     box-shadow: 0 20px 30px rgba(0,0,0,0.4);
     transition: background-color 1s ease;
}

#navegation::-webkit-scrollbar {
    width: 8px; /* Un poco más de ancho para que sea más fácil de usar */
  }
  
  /* Fondo de la barra de desplazamiento (la pista) */
#navegation::-webkit-scrollbar-track {
    background: transparent; /* Fondo transparente */
    border-radius: 10px;
    /* El margen superior e inferior creará el espacio que buscabas */
    margin: 50px 0; 
  }
  
  /* El "pulgar" o la parte móvil de la barra de desplazamiento */
#navegation::-webkit-scrollbar-thumb {
    background: #fff8f1; /* Color del pulgar */
    border-radius: 10px;
    border: 2px solid transparent; /* Crea un padding visual alrededor del pulgar */
    background-clip: content-box;
  }
  
  /* Estilos para cuando el mouse está sobre la barra */
#navegation::-webkit-scrollbar-thumb:hover {
    background-color: #ffb000; /* Un color ligeramente más claro al pasar el mouse */
  }
  
  /* Oculte las flechas (esto generalmente no es necesario, pero lo mantenemos por si acaso) */
#navegation::-webkit-scrollbar-button {
    display: none;
  }

#iconHome{
 position: absolute;
 top: 20px;
 left: 20px;
 color: #fff8f1;
 cursor: pointer;
}

.img-logo img{
    width: 10rem;
}

#navegation a{
    text-decoration: none;
}

#navegation .listMother{
    width: 80%;
    padding: 20px 0;
    display: flex;
    flex-direction: column;
    list-style: none;
    gap: 15px;
    color:#fff8f1;
}

#liUsers, #liProducts, #liGets, #liStats, #liAbout{ /*Estos son los li que se usan para clasificar a las listas de opciones dentro del nav*/
    padding-bottom: 5px;
    border-bottom: 1px solid white;
    display: flex;
    justify-content: space-between;
    cursor: pointer;
    font-size: .8rem;
}

    #liSearch{
    display: flex;
    gap: 20px;
    flex-direction: column;
    align-items: flex-end;
}

.sheetList, .sheetListProducts, .sheetListGets, .sheetListStats, .sheetListAbout{ /*Sublista del apartado de usuarios y sus consultas*/
font-size: small;
font-weight: 300;
list-style: none;
display: grid;
grid-template-rows: repeat(3, 1fr);
gap: 10px;
transition: all .5s ease;
height: 0;
overflow: hidden;
}

.current-page{
 font-weight: 700;
}

.fa-caret-up{
    transition: all .3s ease-in-out;
}

#inputSearchProfile{
    padding: 9px 10px;
    border-radius: 15px;
    width: 100%;
    border-style: none;
    font-size: .8rem;
}

#btnSearch{
    width: 5rem;
    font-size: .8rem;
    height: 30px;
    border-radius: 25px;
    border-style: none;
    background-color: #0097b2;
    color: #fff8f1;
    cursor: pointer;
}

/*Contenedor de boton de logueo y modo de colores*/

.btn-color-mode{
    width: 100%;
    display: flex;
    justify-content: space-around;
    flex-direction: column;
    gap: 30px;
}

.btn-color-mode-choices{
    width: 100px;
    display: flex;
    justify-content: space-around;
    border: 2px solid #fff8f1;
    border-radius: 20px;
    padding: 0 10px;
    position: relative;
    overflow: hidden;
    z-index: 1;
}

.background-modes{
    position: absolute;
    left: 0;
    top: 0;
    width: 50px;
    height: 39px;
    background-color: #fff8f1;
    z-index: 0;
    transition: all 1s ease;
    border-top-right-radius: 20px;
    border-bottom-right-radius: 20px;
    z-index: 0;
}

/*Estilos para cuando se cree la clase de dark mode*/

.background-modes.light-mode-active {
  transform: translateX(0);
  border-radius: 0;
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;
}

.background-modes.dark-mode-active {
  transform: translateX(50px);
  border-top-left-radius: 20px;
  border-bottom-left-radius: 20px;
}


/*Aca termina*/

.btn-color-mode-choices button{
 padding: 7px;
 border-style: none;
 background-color: transparent;
 font-size: 15px;
 z-index: 2;
}

.btn-color-mode-choices button:nth-child(2){
    color: #461d01;
}

.btn-color-mode-choices button:nth-child(3){
    color: #fff8f1;
}

.bi-sun, .dark-mode{
    transition: all .5s ease-in-out;
    cursor: pointer;
}

.bi-sun:hover{
    transform: scale(130%);
}

.dark-mode:hover{
    transform: scale(130%);
}

/*Boton de logueo*/

.btn-close-session{
    padding: 10px;
    border-radius: 20px;
    background:linear-gradient(135deg, #EF6C00, #ffb000);
    color: #461d01;
    border-style: none;
    transition: all .5s ease;
    cursor: pointer;
}

.btn-close-session:hover{
 transform: translateY(-2px);
 color: #fff8f1;
}
    </style>
</head>
<body>
 <nav id="navegation">
        <a href="user-dashboard-admin.php"><i class="fas fa-home" id="iconHome"></i></a>
        <span>
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO" width="200px">
        </span>
        <!--bienvenida personalizada con rol-->
        <span class="welcome-admin">
            Bienvenido <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?>
            (<?php
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Colaborador';
                else echo 'Administrador';
                ?>)
        </span>
        <ul class="listMother">
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <a href="user-dashboard-admin-index.php">
                    <li>Usuarios</li>
                </a>
                <a href="client-dashboard-index.php">
                    <li>Clientes</li>
                </a>
                <a href="seller-dashboard-admin-index.php">
                    <li>Vendedores</li>
                </a>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <a href="products-dashboard-admin-index.php">
                    <li>Productos</li>
                </a>
                <li>Categorias</li>
                <li>Listado de ventas por vendedor</li>
            </ul>
            <li id="liGets">Gestion de pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <li class="current-page">Pedidos</li>
                <li>Actualizar estados de pedidos</li>
            </ul>
            <li id="liStats">Reportes Generales<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListStats">
                <li>Mejores Vendedores</li>
                <li>Mas Vendidos</li>
                <li>Trafico de la plataforma</li>
            </ul>
            <li id="liAbout">Acerca de<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListAbout">
                <li>Politicas de privacidad y uso</li>
                <li>Terminos para vendedores</li>
            </ul>
            <span class="btn-color-mode">
                <form action="../registros-inicio-sesion/logout.php" method="POST">
                    <button type="submit" class="btn-close-session">Cerrar sesión</button>
                </form>
                <div class="btn-color-mode-choices">
                    <span class="background-modes"></span>
                    <button class="light-mode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-sun" viewBox="0 0 16 16">
                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                        </svg>
                    </button>
                    <button class="dark-mode"><i class="fa-solid fa-moon"></i></button>
                </div>
            </span>
    </nav>  
<main>
    <h1>PANEL ADMINISTRATIVO</h1>

<hr>

    <h2>Listado de Ventas por Vendedor</h2>

<!-- BUSCADOR -->
<form method="GET">
    <input type="text" name="busqueda" placeholder="Buscar ventas..."
           value="<?= htmlspecialchars($busqueda) ?>">
    <button type="submit">Buscar</button>
</form>

<br>

<table border="1">
<tr>
    <th>Vendedor</th>
    <th>Producto</th>
    <th>Cliente</th>
    <th>Cantidad</th>
    <th>Total</th>
    <th>Estado Pedido</th>
</tr>

<?php
$sql = "
SELECT 
    v.nombre_empresa,
    pr.nombre AS producto,
    u.nombre,
    u.apellido,
    dp.cantidad,
    dp.precio_total,
    p.estado
FROM pedido p
INNER JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
INNER JOIN producto pr ON dp.id_producto = pr.id_producto
INNER JOIN vendedor v ON pr.id_vendedor = v.id_vendedor
INNER JOIN cliente c ON p.id_cliente = c.id_cliente
INNER JOIN usuario u ON c.id_cliente = u.id_usuario
$where
";

$res = $conn->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$row['nombre_empresa']}</td>
            <td>{$row['producto']}</td>
            <td>{$row['nombre']} {$row['apellido']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['precio_total']}</td>
            <td>{$row['estado']}</td>
        </tr>";
    }
}
?>
</table>

<hr>

<!-- =============================== -->
<!-- 2. ACTUALIZAR ESTADO DE PEDIDOS -->
<!-- =============================== -->

<h2>Actualizar Estados de Pedidos</h2>

<table border="1">
<tr>
    <th>ID Pedido</th>
    <th>Estado Actual</th>
    <th>Nuevo Estado</th>
    <th>Guardar</th>
</tr>

<?php
$ped = $conn->query("SELECT * FROM pedido");

if ($ped) {
    while ($p = $ped->fetch_assoc()) {
        echo "
        <tr>
            <form method='POST'>
            <td>{$p['id_pedido']}</td>
            <td>{$p['estado']}</td>
            <td>
                <select name='estado'>
                    <option>Pendiente</option>
                    <option>Enviado</option>
                    <option>Entregado</option>
                    <option>Cancelado</option>
                </select>
            </td>
            <td>
                <input type='hidden' name='id' value='{$p['id_pedido']}'>
                <button name='actualizar'>Guardar</button>
            </td>
            </form>
        </tr>";
    }
}

if (isset($_POST["actualizar"])) {
    $id = $_POST["id"];
    $estado = $_POST["estado"];

    if ($conn->query("UPDATE pedido SET estado='$estado' WHERE id_pedido=$id")) {
        echo "<script>
            alert('Estado actualizado correctamente.');
            window.location.href = window.location.href;
        </script>";
    }
}
?>
</table>
</main>
<script src="../scripts/admin.js"></script>
</body>
</html>

