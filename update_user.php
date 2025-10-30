<?php
// Conexión a la base de datos
include ('registros-inicio-sesion/connect.php');

// Verificamos si el formulario fue enviado por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturamos los datos enviados desde el formulario
    $id = intval($_POST['ID_Usuario']); // ID del usuario a actualizar
    $name = $_POST['name'];             // Nombre
    $lastname = $_POST['lastname'];     // Apellido
    $email = $_POST['email'];           // Correo electrónico
    $password = $_POST['password'];     // Contraseña (sin cifrar, ojo)
    $birthday = $_POST['birthday'];     // Fecha de nacimiento
    $phone = $_POST['phone'];           // Teléfono

    // Creamos la consulta SQL para actualizar el usuario
    $sql = "UPDATE usuario SET 
                Nombre = '$name',
                Apellido = '$lastname',
                correo = '$email',
                contrasena = '$password',
                fecha_nacimiento = '$birthday',
                Telefono = '$phone'
            WHERE ID_Usuario = $id";

    // Ejecutamos la consulta
    $result = mysqli_query($connect, $sql);

    // Verificamos si la actualización fue exitosa
    if ($result) {
        echo "✅ Usuario actualizado correctamente.";
        // redireccionamiento
        header("Location: admin_crud.php");
        exit;
    } else {
        // Mostramos el error si la consulta falló
        echo "❌ Error al actualizar: " . mysqli_error($connect);
        exit;
    }
}

// Si no se ha enviado el formulario, mostramos el formulario de edición
$id = isset($_GET['ID_Usuario']) ? intval($_GET['ID_Usuario']) : null;

// Validamos que se haya recibido el ID del usuario
if ($id === null) {
    die("! ID_Usuario no proporcionado.");
}

// Consulta para obtener los datos actuales del usuario
$sql = "SELECT * FROM usuario WHERE ID_Usuario = $id";
$query = mysqli_query($connect, $sql);

// Validamos que la consulta se haya ejecutado correctamente
if (!$query) {
    die("X Error en la consulta: " . mysqli_error($connect));
}

// Obtenemos los datos del usuario en forma de array
$row = mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
</head>
<body>
    <!-- Formulario para editar los datos del usuario -->
    <form action="update_user.php" method="POST">
        <h1>Editar Usuario</h1>

        <!-- Campo oculto con el ID del usuario -->
        <input type="hidden" name="ID_Usuario" value="<?= $row['ID_Usuario'] ?>">

        <!-- Campos editables con los datos actuales -->
        <input type="text" name="name" placeholder="Nombres" value="<?= $row['Nombre'] ?>">
        <input type="text" name="lastname" placeholder="Apellidos" value="<?= $row['Apellido'] ?>">
        <input type="text" name="email" placeholder="Correo Electrónico" value="<?= $row['correo'] ?>">
        <input type="text" name="password" placeholder="Contraseña" value="<?= $row['contrasena'] ?>">
        <input type="date" name="birthday" placeholder="Fecha de Nacimiento" value="<?= $row['fecha_nacimiento'] ?>">
        <input type="text" name="phone" placeholder="Teléfono" value="<?= $row['Telefono'] ?>">

        <!-- Botón para enviar el formulario -->
        <button type="submit">Editar</button>
    </form>
</body>
</html>