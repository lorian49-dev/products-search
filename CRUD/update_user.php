<?php
    //Traemos la base de datos
    include 'connect.php';

    $id = $_GET['id'];

    $sql = "SELECT * FROM usuario WHERE id=$id";
    $query = mysqli_query($connect, $sql);
    $row= mysqli_fetch_array($query);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario</title>
</head>

<body>
    <form action="update_user.php" metohd="POST">
            <h1>Editar Usuario</h1>
            <input type="hidden" name="id" value=<?= $row['id']?>>
            <input type="text" name="name" placeholder="Nombres" value=<?= $row['nombre']?> >
            <input type="text" name="lastname" placeholder="Apellidos" value=<?= $row['apellido']?>>
            <input type="text" name="email" placeholder="Correo Electronico"value=<?= $row['correo']?>>
            <input type="text" name="password" placeholder="Contraseña"value=<?= $row['contrasena']?>>
            <input type="date" name="birthday" placeholder="Fecha de Nacimiento" value=<?= $row['fecha_nacimiento']?>>
            <input type="text" name="phone" placeholder="Telefono"value=<?= $row['telefono']?>>

            <button type="submit">Editar</button>
        </form>
    
</body>
</html>>