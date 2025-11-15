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