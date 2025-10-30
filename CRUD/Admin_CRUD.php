<?php
    include ('connect.php');
    
    $query= "SELECT * FROM usuario";
    $ejec= mysqli_query($connect, $query);  
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Administrador</title>
</head>
<body>
    <div>
        <form action="create_user.php" metohd="POST">
            <h1>Crear Usuario</h1>
            
            <input type="text" name="name" placeholder="Nombres">
            <input type="text" name="lastname" placeholder="Apellidos">
            <input type="text" name="email" placeholder="Correo Electronico">
            <input type="text" name="password" placeholder="Contraseña">
            <input type="date" name="birthday" placeholder="Fecha de Nacimiento">
            <input type="text" name="phone" placeholder="Telefono">

            <button type="submit">Enviar</button>
        </form>
    </div>
    <div>
        <h2>Usuarios Registrados</h2>
        <table>
            <thead>
                <tr>
                    <th>Nombres</th>
                    <th>Apellidos</th>
                    <th>Correo</th>
                    <th>contraseña</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Telefono</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($ejec)):?>
                <tr>

                    <th><?= $row['nombre']?></th>
                    <th><?= $row['apellido']?></th>
                    <th><?= $row['correo']?></th>
                    <th><?= $row['contrasena']?></th>
                    <th><?= $row['fecha_nacimiento']?></th>
                    <th><?= $row['telefono']?></th>

                    <th><a href="edit_user.php?<?= $row['id']?>" >Editar</th>
                    <th><a href="delete_user.php?<?= $row['id']?>">Eliminar</th>
                </tr>
                <?php endwhile;?>
            </tbody>
        </table>
    </div>



</body>
</html>