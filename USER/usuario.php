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
    <title>Perfil del Usuario</title>

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

        input {
            width: 100%;
            padding: 10px;
            margin: 7px 0;
        }

        button {
            padding: 10px 18px;
            background: brown;
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

    <h2>Perfil del Usuario</h2>
    <div class="perfil-container">  
        <p><strong>Nombre: </strong><?= $usuario['nombre'] . " " . $usuario['apellido']; ?></p>
        <p><strong>Correo:</strong> <?= $usuario['correo'] ?></p>
        <p><strong>Fecha de nacimiento:</strong> <?= $usuario['fecha_nacimiento'] ?></p>
        <p><strong>Teléfono:</strong> <?= $usuario['telefono'] ?></p>
        <p><strong>Dirección principal:</strong> <?= $usuario['direccion_principal'] ?></p>

    </div>
    <button id="btnEdit">Editar Perfil</button>



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

    <script>
        let btnEdit = document.getElementById('btnEdit');
        let modalWindow = document.querySelector('.modalWindow');
        let modalContainer = document.querySelector('.modalContainer');
        let buttonBack = document.querySelector('.back-icon');

        btnEdit.addEventListener('click', () => {
            modalWindow.style.opacity = "1";
            modalWindow.style.pointerEvents = "auto";
            modalContainer.style.opacity = "1";
            modalContainer.style.pointerEvents = "auto";
            modalContainer.style.transform = "translateY(0)";
        });

        buttonBack.addEventListener('click', () => {
            modalContainer.style.transform = "translateY(-100%)";
            modalContainer.style.opacity = "0";
            modalWindow.style.opacity = "0";
            modalWindow.style.pointerEvents = "none";
        });
    </script>

</body>

</html>