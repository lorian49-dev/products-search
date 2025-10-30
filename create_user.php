<?php

    include ('connect.php');

    $nombre = $_POST['name'];
    $apellido = $_POST['lastname'];
    $correo = $_POST['email'];
    $password = $_POST['password'];
    $passwordh = password_hash($password, PASSWORD_DEFAULT);
    $fecha_nacimiento = $_POST['birthday'];
    $telefono = $_POST['phone'];
    
    //Realizamos el QUERY
    $sql = "INSERT INTO usuario (nombre, apellido, correo, contrasena, fecha_nacimiento, telefono) VALUES (?,?,?,?,?,?)";

    //Preparamos la consulta
    $stmt = mysqli_prepare($connect, $sql);
    //Vinculamos los datos
    //la cadena 'ssssss' define los tipos de datos (s = string, i= integer, d= double, b = blob)
    mysqli_stmt_bind_param($stmt, 'ssssss', $nombre, $apellido,$correo,$passwordh,$fecha_nacimiento,$telefono);

    //Se ejecuta la consulta
    $query = mysqli_stmt_execute($stmt);

    //Cerramos la sentencia
    mysqli_stmt_close($stmt);

    //Manejo de redireccion
    if($query){
        header("Location: index.php");
        exit(); //detiene la ejecucion del script despues de la redirección
    }else{
        //Manejo de errores
        echo "Error al registrar el usuario: ". mysqli_error($connect);
    }

    //cerramos la conexion con la base de datos
    mysqli_close($connect);

?>