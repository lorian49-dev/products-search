<?php
// PROTECCIÓN DE ACCESO - AGREGAR AL INICIO de admin.php
session_start();
include('../shortCuts/connect.php');

// Verificar si está logueado como ADMIN
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = 'admin-login.php';
    </script>";
    exit();
}

// Verificar rol de administrador (1 = administrador, 2 = admin_colaborador)
$rolesPermitidos = [1, 2]; // Solo roles 1 y 2 pueden acceder
if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador. Su rol no está autorizado.');
        window.location.href = '../index.php';
    </script>";
    exit();
}

// CONTINÚA TU CÓDIGO ORIGINAL...
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

        <!-- MODIFICADO: Mostrar info del admin según tus roles -->
        <h1>Bienvenido <?php echo $_SESSION['admin_nombre']; ?>
            (<?php
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Admin Colaborador';
                else echo 'Usuario';
                ?>)
        </h1>

        <ul class="listMother">
            <!-- TUS ELEMENTOS EXISTENTES... -->

            <li id="liSearch"><input type="text" name="search-profile" id="inputSearchProfile" placeholder="Buscar Usuario por Correo...">
                <button id="btnSearch">Consultar</button>
            </li>
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <!-- ... resto de tu código ... -->

            <!-- AGREGAR BOTÓN DE CERRAR SESIÓN ADMIN -->
            <li id="liLogout" style="margin-left: auto;">
                <a href="admin-logout.php" style="color: #ff4444; text-decoration: none; font-weight: bold;">
                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión Admin
                </a>
            </li>
        </ul>
    </nav>