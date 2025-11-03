<?php
// Conexión a la base de datos
include ('../registros-inicio-sesion/connect.php');

// Verificamos si el formulario fue enviado por método POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Capturamos los datos enviados desde el formulario
    $id = intval($_POST['id_usuario']); // ID del usuario a actualizar
    $name = $_POST['name'];             // Nombre
    $lastname = $_POST['lastname'];     // Apellido
    $email = $_POST['email'];           // Correo electrónico
    $password = $_POST['password'];     // Contraseña (sin cifrar, ojo)
    $birthday = $_POST['birthday'];     // Fecha de nacimiento
    $phone = $_POST['phone'];           // Teléfono

    // Creamos la consulta SQL para actualizar el usuario
    $sql = "UPDATE usuario SET 
                nombre = '$name',
                apellido = '$lastname',
                correo = '$email',
                contrasena = '$password',
                fecha_nacimiento = '$birthday',
                telefono = '$phone'
            WHERE id_usuario = $id";

    // Ejecutamos la consulta
    $result = mysqli_query($connect, $sql);

    // Verificamos si la actualización fue exitosa
    if ($result) {
    echo "✅ Usuario actualizado correctamente.";
    echo '<script>
        setTimeout(function() {
            window.location.href = "Admin_CRUD.php";
        }, 2000); // 2000 milisegundos = 2 segundos
    </script>';
    exit;
} else {
    echo "❌ Error al actualizar: " . mysqli_error($connect);
    exit;
}

}

// Si no se ha enviado el formulario, mostramos el formulario de edición
$id = isset($_GET['id_usuario']) ? intval($_GET['id_usuario']) : null;

// Validamos que se haya recibido el ID del usuario
if ($id === null) {
    die("! id no proporcionado.");
}

// Consulta para obtener los datos actuales del usuario
$sql = "SELECT * FROM usuario WHERE id_usuario = $id";
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
        <input type="hidden" name="id_usuario" value="<?= $row['id_usuario'] ?>">

        <!-- Campos editables con los datos actuales -->
        <input type="text" name="name" placeholder="Nombres" value="<?= $row['nombre'] ?>">
        <input type="text" name="lastname" placeholder="Apellidos" value="<?= $row['apellido'] ?>">
        <input type="text" name="email" placeholder="Correo Electrónico" value="<?= $row['correo'] ?>">
        <input type="text" name="password" placeholder="Contraseña" value="<?= $row['contrasena'] ?>">
        <input type="date" name="birthday" placeholder="Fecha de Nacimiento" value="<?= $row['fecha_nacimiento'] ?>">
        <input type="text" name="phone" placeholder="Teléfono" value="<?= $row['telefono'] ?>">

        <!-- Botón para enviar el formulario -->
        <button type="submit">Editar</button>
    </form>
</body>
</html>