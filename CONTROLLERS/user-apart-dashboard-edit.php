<?php
session_start();
include('../shortCuts/connect.php');

// Validar sesión
if (!isset($_SESSION['usuario_id'])) {
    die("Error: No hay sesión iniciada.");
}

$usuario_id = $_SESSION['usuario_id'];

// ---------------------------
// Cuando envía el formulario
// ---------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Obtener datos actuales del usuario
    $sql_actual = "SELECT * FROM usuario WHERE id_usuario = ?";
    $q_actual = $connect->prepare($sql_actual);
    $q_actual->bind_param("i", $usuario_id);
    $q_actual->execute();
    $actual = $q_actual->get_result()->fetch_assoc();
    $q_actual->close();

    // Datos enviados por el form
    $name      = trim($_POST['name']);
    $lastname  = trim($_POST['lastname']);
    $email     = trim($_POST['email']);
    $password  = $_POST['password'];
    $birthday  = $_POST['birthday'];
    $phone     = trim($_POST['phone']);
    $direccion = trim($_POST['direccion_principal']);

    // --------------- EVITAR QUE LOS VACÍOS BORREN DATOS ---------------

    $name      = $name      !== "" ? $name      : $actual['nombre'];
    $lastname  = $lastname  !== "" ? $lastname  : $actual['apellido'];
    $email     = $email     !== "" ? $email     : $actual['correo'];
    $birthday  = $birthday  !== "" ? $birthday  : $actual['fecha_nacimiento'];
    $phone     = $phone     !== "" ? $phone     : $actual['telefono'];
    $direccion = $direccion !== "" ? $direccion : $actual['direccion_principal'];

    // Contraseña: si está vacía se conserva
    if ($password === "") {
        $password_final = $actual['contrasena'];
    } else {
        $password_final = $password; // Poner hashing si deseas
    }

    // UPDATE seguro
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

        // Nuevo nombre completo
        $_SESSION['nombre_completo'] = $name . " " . $lastname;

        echo "<script>
                alert('Perfil actualizado correctamente.');
                window.location.href = 'user-apart-dashboard.php';
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

$nombre_completo = $usuario["nombre"] . " " . $usuario["apellido"];

