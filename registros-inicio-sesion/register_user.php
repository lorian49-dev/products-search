<?php
    // Traemos la conexión
    include '../shortCuts/connect.php';
    
    // Variables desde POST
    $nombre           = $_POST['name-user'];
    $apellido         = $_POST['second-name-user'];
    $correo           = $_POST['email-user'];
    $password         = $_POST['pass-user'];
    $fecha_nacimiento = $_POST['birth-user'];
    $telefono         = $_POST['phone-user'];

    // Validar que los campos no estén vacíos
    if (empty($nombre) || empty($apellido) || empty($correo) || empty($password)) {
        die("Por favor complete todos los campos obligatorios.");
    }

    // Hasheamos la contraseña de forma segura
    $password_h = password_hash($password, PASSWORD_DEFAULT);

    // Validación de correo ya existente con consulta preparada
    $stmt = $connect->prepare("SELECT * FROM usuario WHERE correo = ?");
    $stmt->bind_param("s", $correo);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        // El correo ya está registrado
        echo "El correo ya está en uso, intente con otro.";
        $stmt->close();
        $connect->close();
        exit();
    }
    $stmt->close();

    // Inserción con consulta preparada
    $stmt = $connect->prepare("INSERT INTO usuario(Nombre, Apellido, correo, contrasena, fecha_nacimiento, Telefono) 
                               VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssss", $nombre, $apellido, $correo, $password_h, $fecha_nacimiento, $telefono);

    if ($stmt->execute()) {
        echo "<script>alert('Usuario registrado correctamente'); window.location.href='../home.php';</script>";
    } else {
        echo "Error al registrar usuario: " . $stmt->error;
    }

    $stmt->close();
    $connect->close();
?>