<?php
session_start();
include('../registros-inicio-sesion/connect.php');

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión iniciada.");
}

$usuario_id = $_SESSION['usuario_id'];

// ---------------------------
// Cuando envía el formulario
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $name      = trim($_POST['name']);
    $lastname  = trim($_POST['lastname']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $birthday  = $_POST['birthday'];
    $phone     = trim($_POST['phone']);
    $direccion = trim($_POST['direccion_principal']);

    // Generar nombre completo
    $nombre_completo = $name . " " . $lastname;

    // Si NO cambia la contraseña, se conserva la actual
    if ($password === "") {
        $q = $connect->prepare("SELECT contrasena FROM usuario WHERE id_usuario = ?");
        $q->bind_param("i", $usuario_id);
        $q->execute();
        $res = $q->get_result()->fetch_assoc();
        $password_final = $res["contrasena"];
        $q->close();
    } else {
        $password_final = $password; // Puedes aplicar hashing si quieres
    }

    // UPDATE seguro con prepared statements
    $sql = "UPDATE usuario SET 
                nombre = ?, 
                apellido = ?, 
                correo = ?, 
                contrasena = ?, 
                fecha_nacimiento = ?, 
                telefono = ?, 
                direccion_principal = ?
            WHERE id_usuario = ?";

    $stmt = $connect->prepare($sql);

    $stmt->bind_param(
        "sssssssi",
        $name,
        $lastname,
        $email,
        $password_final,
        $birthday,
        $phone,
        $direccion,
        $usuario_id
    );

    if ($stmt->execute()) {
        // Guardar nombre completo en sesión (opcional, pero útil)
        $_SESSION['nombre_completo'] = $nombre_completo;

        echo "<script>
                alert('Perfil actualizado correctamente.');
                window.location.href = 'usuario.php';
              </script>";
        exit;
    } else {
        die("Error al actualizar: " . $stmt->error);
    }

}

// ---------------------------
// Cuando solo abre el form
// ---------------------------
$sql = "SELECT * FROM usuario WHERE id_usuario = $usuario_id";
$r = mysqli_query($connect, $sql);
$usuario = mysqli_fetch_assoc($r);

// Crear nombre completo para mostrar
$nombre_completo = $usuario["nombre"] . " " . $usuario["apellido"];

?>
