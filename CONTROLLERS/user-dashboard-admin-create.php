<?php
    include('../shortCuts/connect.php');

    // Capturamos los datos del formulario
    $nombre = trim($_POST['name']);
    $apellido = trim($_POST['lastname']);
    $correo = trim($_POST['email']);
    $password = trim($_POST['password']);
    $fecha_nacimiento = trim($_POST['birthday']);
    $telefono = trim($_POST['phone']);

    // Validamos que ningún campo esté vacío
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password) || empty($fecha_nacimiento) || empty($telefono)) {
        echo "⚠️ Todos los campos son obligatorios. Por favor, completa el formulario.";
        exit;
    }

    // Hasheamos la contraseña solo si no está vacía
    $passwordh = password_hash($password, PASSWORD_DEFAULT);

    //Verifcacion de Correo
    $stmt = $connect->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();
    
    // Preparamos el INSERT
    $sql = "INSERT INTO usuario (nombre, apellido, correo, contrasena, fecha_nacimiento, telefono) VALUES (?,?,?,?,?,?)";
    $stmt = mysqli_prepare($connect, $sql);

    // Vinculamos los parámetros
    mysqli_stmt_bind_param($stmt, 'ssssss', $nombre, $apellido, $correo, $passwordh, $fecha_nacimiento, $telefono);

    // Ejecutamos la consulta
    $query = mysqli_stmt_execute($stmt);

    // Cerramos la sentencia
    mysqli_stmt_close($stmt);

    // Redireccionamos o mostramos error
    if ($query) {
        echo "✅ Usuario registrado correctamente.";
        echo '<script>
            setTimeout(function() {
                window.location.href = "user-dashboard-admin-index.php";
            }, 2000);
        </script>';
    } else {
        echo "❌ Error al registrar el usuario: " . mysqli_error($connect);
    }

    // Cerramos la conexión
    mysqli_close($connect);
?>
