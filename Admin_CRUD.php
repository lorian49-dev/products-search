<?php
    include ('registros-inicio-sesion/connect.php');
    
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
        <form action="create_user.php" method="POST"> <!--Correcion de ortografia en el metodo POST-->
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

                <!--Línea 55 Corregida: Debes añadir 'id=' y cerrar la etiqueta la Columna ID_Usuario esta distinta</a>-->
<td><a href="update_user.php?id_usuario=<?= $row['id_usuario']?>" >Editar</a></td>  <!--Modificacion en la ruta de acceso de id a ID_usuario-->
                <!--Línea 57 Corregida: Debes añadir 'id=' y cerrar la etiqueta, la Columna ID_Usuario esta distinta </a>-->
<td><a href="delete_user.php?id_usuario=<?= $row['id_usuario']?>">Eliminar</a></td>
                </tr>
                <?php endwhile;?>
            </tbody>
        </table>
    </div>



</body>
</html>