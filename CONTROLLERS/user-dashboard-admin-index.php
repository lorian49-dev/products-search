<?php
// ==================== PROTECCIÓN DE ACCESO ====================
session_start();
include('../shortCuts/connect.php');


// Verificar si está logueado como ADMIN
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';  // ← CORREGIDO
    </script>";
    exit();
}

// Verificar rol de administrador (1 = administrador, 2 = admin_colaborador)
$rolesPermitidos = [1, 2];
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}
//  FIN PROTECCIÓN DE ACCESO 

// TU CÓDIGO ORIGINAL SE MANTIENE INTACTO
$query = "SELECT * FROM usuario";
$ejec = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
</head>

<body>
    <nav id="navegation">
        <a href="#"><i class="fas fa-home" id="iconHome"></i></a>
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
            <li id="liSearch"><input type="text" name="search-profile" id="inputSearchProfile" placeholder="Buscar Usuario por Correo...">
                <button id="btnSearch">Consultar</button>
            </li>
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <li class="current-page">Usuarios</li>
               <a href="dashboard-index.php"><li>Clientes</li></a>
                <a href="seller-dashboard-admin-index.php"><li>Vendedores</li></a>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li>
                    <a href="products-dashboard-admin-index.php">Productos</a>
                    
                </li>
                 <li>
                   
                    <a href="products-dashboard-admin-index.php">Categorias</a>
                </li>
                      <li>
                   
                    <a href="orders-admin-index.php">Pedidos</a>
                </li>
            </ul>
            <li id="liGets">Gestion de pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <li>Listado de ventas por vendedor</li>
                <li>Disputas</li>
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
  <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708"/>
</svg>
</button>
                <button class="dark-mode"><i class="fa-solid fa-moon"></i></button>
                </div>
                </span>
    </nav>
    <div id="container">
        <form action="user-dashboard-admin-create.php" method="POST"> <!--Correcion de ortografia en el metodo POST-->
            <h2>Crear Usuario</h2>

            <input type="text" name="name" placeholder="Nombres" class="inputCreate">
            <input type="text" name="lastname" placeholder="Apellidos" class="inputCreate">
            <input type="text" name="email" placeholder="Correo Electronico" class="inputCreate">
            <input type="text" name="password" placeholder="Contraseña" class="inputCreate">
            <input type="text" name="birthday" placeholder="Fecha de nacimiento"
                onfocus="(this.type='date')"
                onblur="if(!this.value)this.type='text'" class="inputCreate">

            <input type="text" name="phone" placeholder="Telefono" class="inputCreate">

            <div class="buttonBox">
                <button id="submitButton" type="submit" class="toChangeColor">Enviar</button>
                <button id="clearButton" class="toChangeColor">Limpiar</button>
            </div>
        </form>
    </div>
    <div id="view">
        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>contraseña</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Telefono</th>
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = mysqli_fetch_array($ejec)): ?>
                    <tr>

                        <td><?= $row['nombre'] ?></td>
                        <td><?= $row['apellido'] ?></td>
                        <td><?= $row['correo'] ?></td>
                        <td>
                            <input type="password" value="<?= htmlspecialchars($row['contrasena']) ?>" readonly id="pass<?= $row['id_usuario'] ?>">
                            <button type="button" onclick="togglePass('pass<?= $row['id_usuario'] ?>')"><i class="fa-solid fa-eye"></i></button>
                        </td>
                        <td><?= $row['fecha_nacimiento'] ?></td>
                        <td><?= $row['telefono'] ?></td>

                        <!--Línea 55 Corregida: Debes añadir 'id=' y cerrar la etiqueta la Columna ID_Usuario esta distinta</a>-->
                        <td><a href="#"
                                class="btn-edit"
                                data-id="<?= $row['id_usuario'] ?>"
                                data-name="<?= $row['nombre'] ?>"
                                data-lastname="<?= $row['apellido'] ?>"
                                data-email="<?= $row['correo'] ?>"
                                data-password="<?= $row['contrasena'] ?>"
                                data-birthday="<?= $row['fecha_nacimiento'] ?>"
                                data-phone="<?= $row['telefono'] ?>">
                                Editar
                            </a></td> <!--Modificacion en la ruta de acceso de id a ID_usuario-->
                        <!--Línea 57 Corregida: Debes añadir 'id=' y cerrar la etiqueta, la Columna ID_Usuario esta distinta </a>-->
                        <td><a href="user-dashboard-admin-delete.php?id_usuario=<?= $row['id_usuario'] ?>">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="modalWindow">
        <div class="modalContainer">
            <span class="back-icon"><img src="../SOURCES/ICONOS-LOGOS/return.svg"></span>
            <form action="user-dashboard-admin-update.php" method="POST" class="formUpdateUser">
                <h2>Actualizar Datos</h2>
                <!--Input invisible, simplemente sirve de guia para lo que se muestra al lado-->
                <input type="hidden" name="id_usuario">
                <!--Campos editables-->
                <input type="text" name="name" placeholder="Nombres">
                <input type="text" name="lastname" placeholder="Apellidos">
                <input type="text" name="email" placeholder="E-mail">
                <input type="text" name="password" placeholder="Clave">
                <input type="date" name="birthday" placeholder="Fecha de nacimiento">
                <input type="text" name="phone" placeholder="Telefono">
                <!-- Botón para enviar el formulario -->
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <script src="../scripts/admin.js"></script>

</body>

</html>