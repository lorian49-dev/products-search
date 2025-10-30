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
    <link rel="stylesheet" href="crud-styles-events/admin.css">
    <link rel="shortcut icon" href="SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
</head>
<body>
    <nav id="navegation">
        <span>
            <img src="SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO" width="200px">
        </span>
        <h1>Bienvenido Administrador</h1>
    </nav>
    <div id="container">
        <form action="create_user.php" method="POST"> <!--Correcion de ortografia en el metodo POST-->
            <h2>Crear Usuario</h2>
            
            <input type="text" name="name" placeholder="Nombres" class="inputCreate">
            <input type="text" name="lastname" placeholder="Apellidos" class="inputCreate">
            <input type="text" name="email" placeholder="Correo Electronico" class="inputCreate">
            <input type="text" name="password" placeholder="Contraseña" class="inputCreate">
            <input type="text" name="birthday" placeholder="Fecha de nacimiento" 
            onfocus="(this.type='date')" 
            onblur="if(!this.value)this.type='text'" class="inputCreate">

            <input type="text" name="phone" placeholder="Telefono"  class="inputCreate">

            <div class="buttonBox">
            <button type="submit">Enviar</button>
            <button id="clearButton">Limpiar</button>
            </div>
        </form>
    </div>
    <div id="view">
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
                    <th></th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_array($ejec)):?>
                <tr>

                    <td><?= $row['nombre']?></td>
                    <td><?= $row['apellido']?></td>
                    <td><?= $row['correo']?></td>
                    <td>
  <input type="password" value="<?= htmlspecialchars($row['contrasena']) ?>" readonly id="pass<?= $row['id_usuario'] ?>">
  <button type="button" onclick="togglePass('pass<?= $row['id_usuario'] ?>')"><i class="fa-solid fa-eye"></i></button>
                    </td>
                    <td><?= $row['fecha_nacimiento']?></td>
                    <td><?= $row['telefono']?></td>

                <!--Línea 55 Corregida: Debes añadir 'id=' y cerrar la etiqueta la Columna ID_Usuario esta distinta</a>-->
<td><a href="update_user.php?id_usuario=<?= $row['id_usuario']?>" >Editar</a></td>  <!--Modificacion en la ruta de acceso de id a ID_usuario-->
                <!--Línea 57 Corregida: Debes añadir 'id=' y cerrar la etiqueta, la Columna ID_Usuario esta distinta </a>-->
<td><a href="delete_user.php?id_usuario=<?= $row['id_usuario']?>">Eliminar</a></td>
                </tr>
                <?php endwhile;?>
            </tbody>
        </table>
    </div>

<script src="crud-styles-events/admin.js"></script>

</body>
</html>