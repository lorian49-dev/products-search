<?php
// ==================== PROTECCIÓN DE ACCESO ====================
session_start();
include('../registros-inicio-sesion/connect.php');


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
// ==================== FIN PROTECCIÓN ====================

// ✅ TU CÓDIGO ORIGINAL SE MANTIENE INTACTO
$query = "SELECT * FROM usuario";
$ejec = mysqli_query($connect, $query);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrativo - HERMES</title>
    <link rel="stylesheet" href="../crud-styles-events/admin.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
</head>

<body>
    <nav id="navegation">
        <a href="#"><i class="fas fa-home" id="iconHome"></i></a>
        <span>
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO" width="200px">
        </span>
        
        <!-- ✅ MODIFICADO: Muestra info del admin logueado -->
        <h1>Bienvenido <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?> 
            (<?php 
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Admin Colaborador'; 
                else echo 'Administrador';
            ?>)
        </h1>
        
        <ul class="listMother">
            <li id="liSearch"><input type="text" name="search-profile" id="inputSearchProfile" placeholder="Buscar Usuario por Correo...">
                <button id="btnSearch">Consultar</button>
            </li>
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <li>Usuarios</li>
                <li>Clientes</li>
                <li>Vendedores</li>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <li>Productos</li>
                <li>Categorias</li>
                <li>Variantes</li>
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
            
            <!-- ✅ NUEVO: Botón de cerrar sesión admin -->
            <li id="liLogout" style="margin-left: auto;">
                <a href="admin_logaut.php" style="color: #ff4444; text-decoration: none; font-weight: bold;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión Admin
                </a>
            </li>
        </ul>
    </nav>
    
    <!-- ✅ TODO TU CÓDIGO ORIGINAL DEL CRUD SE MANTIENE IGUAL -->
    <div id="container">
        <form action="create_user.php" method="POST">
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
                <button type="submit">Enviar</button>
                <button id="clearButton">Limpiar</button>
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
                            </a></td>
                        <td><a href="delete_user.php?id_usuario=<?= $row['id_usuario'] ?>">Eliminar</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <div class="modalWindow">
        <div class="modalContainer">
            <span class="back-icon"><img src="../SOURCES/ICONOS-LOGOS/return.svg"></span>
            <form action="update_user.php" method="POST" class="formUpdateUser">
                <h2>Actualizar Datos</h2>
                <input type="hidden" name="id_usuario">
                <input type="text" name="name" placeholder="Nombres">
                <input type="text" name="lastname" placeholder="Apellidos">
                <input type="text" name="email" placeholder="E-mail">
                <input type="text" name="password" placeholder="Clave">
                <input type="date" name="birthday" placeholder="Fecha de nacimiento">
                <input type="text" name="phone" placeholder="Telefono">
                <button type="submit">Guardar</button>
            </form>
        </div>
    </div>

    <script src="../crud-styles-events/admin.js"></script>

</body>

</html>