<?php
session_start();
include('../shortCuts/connect.php');

if (isset($_SESSION['admin_logueado']) && $_SESSION['admin_logueado'] === true) {
    header("Location: ../main-crud.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario = $_POST['usuario'];
    $password = $_POST['contrasena'];

    try {
        $stmt = $connect->prepare("SELECT a.id_admin, a.username, a.email, a.password, a.activo, a.id_rol, r.nombre_rol 
                                    FROM administradores a 
                                    INNER JOIN rol r ON a.id_rol = r.id_rol 
                                    WHERE (a.username = ? OR a.email = ?) AND a.activo = 1");
        $stmt->bind_param("ss", $usuario, $usuario);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();

            // VERIFICACIÓN FLEXIBLE - acepta texto plano Y contraseñas hasheadas
            $login_exitoso = false;
            
            // Intentar con password_verify primero (para contraseñas hasheadas)
            if (password_verify($password, $admin['password'])) {
                $login_exitoso = true;
            } 
            // Si falla, intentar comparación directa (para texto plano)
            else if ($password === $admin['password']) {
                $login_exitoso = true;
                
                // Opcional: Hashear la contraseña en texto plano para mayor seguridad
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $update_stmt = $connect->prepare("UPDATE administradores SET password = ? WHERE id_admin = ?");
                $update_stmt->bind_param("si", $hashed_password, $admin['id_admin']);
                $update_stmt->execute();
                $update_stmt->close();
            }

            if ($login_exitoso) {
                // Actualizar último acceso
                $update_stmt = $connect->prepare("UPDATE administradores SET ultimo_acceso = NOW() WHERE id_admin = ?");
                $update_stmt->bind_param("i", $admin['id_admin']);
                $update_stmt->execute();
                $update_stmt->close();

                // Guardar sesión
                $_SESSION['admin_logueado'] = true;
                $_SESSION['admin_id'] = $admin['id_admin'];
                $_SESSION['admin_username'] = $admin['username'];
                $_SESSION['admin_email'] = $admin['email'];
                $_SESSION['admin_rol'] = $admin['id_rol'];
                $_SESSION['admin_nombre'] = $admin['username'];
                $_SESSION['admin_nombre_rol'] = $admin['nombre_rol'];

                setcookie("admin_id", $admin['id_admin'], time() + (86400 * 30), "/");

                echo "<script>alert('Acceso administrativo exitoso');</script>";
                header("Location: ../CONTROLLERS/user-dashboard-admin-index.php");
                exit();
                
            } else {
                echo "<script>
                    alert('Contraseña incorrecta');
                    window.location.href='admin-login.php';
                </script>";
            }

        } else {
            echo "<script>
                alert('Usuario administrativo no encontrado');
                window.location.href='admin-login.php';
            </script>";
        }

        $stmt->close();
    } catch (Exception $e) {
        echo "<script>
            alert('Error en el sistema: " . addslashes($e->getMessage()) . "');
            window.location.href='admin-login.php';
        </script>";
    }
    
    $connect->close();
}
?>

<!-- EL HTML PERMANECE IGUAL -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Administrativo - HERMES</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto+Condensed:ital,wght@0,100..900;1,100..900&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Anton&family=Bebas+Neue&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Playwrite+NO:wght@100..400&display=swap');
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'roboto condensed', sans-serif; }
        body { background-image:url('../SOURCES/background-1-abstract.jpg'); height: 100vh; display: flex; align-items: center; justify-content: space-around; padding: 20px; }
        h1{color:white; font-family: "Playwrite NO", cursive; font-optical-sizing: auto; font-weight: weight; font-style: normal; font-size:250px; transform:translate(400px, -200px); opacity:0; transition: all 1s ease; position: relative;}
        h1::after{content: '';position: absolute; display: inline-block; width: 50px; height: 50px; border-radius: 50%; background-color: #fff8f1; bottom: 110px;
        right: -80px; opacity: 0; transition: all 1s ease;}
        h1.show-point::after{opacity: 1;}
        .login-container { background-color:#fff8f1; padding: 40px 30px; border-radius: 15px; box-shadow: 0 10px 30px rgba(0,0,0,0.3); width: 100%; max-width: 420px; display:flex; flex-direction:column; justify-content:center; align-items:center;opacity:0; pointer-events:none; transition: all 2s ease; transform: translateX(-100px);}
        .login-header { text-align: center; margin-bottom: 35px; }
        .login-header img { width: 200px; margin-bottom: 20px; }
        .login-header h2 { color: #333; margin-bottom: 8px; font-size: 26px; }
        form{ display:flex;flex-direction:column; align-items:center;}
        .form-group { margin-bottom: 20px;}
        label { display: block; margin-bottom: 15px; color: #333; font-weight: bold; }
        input[type="text"], input[type="password"] { box-shadow: 0 10px 30px rgba(0,0,0,0.3);width: 300px; padding: 14px 16px; border-style:none; border-radius: 8px; font-size: 15px; }
        input:focus{
            outline:none;
            border:none;
        }
        .btn-login { width: 200px;margin-top:20px; padding: 14px; background: linear-gradient(135deg, #EF6C00, #ffb000); color: #461d01; border: none; border-radius: 25px; font-size: 16px; cursor: pointer; box-shadow: 0 10px 30px rgba(0,0,0,0.3);transition:all .1s ease;}
        .btn-login:hover{
            background:linear-gradient(135deg, #0D47A1, #0097b2); box-shadow:1px 1px 40px 1px #12c0df; color:#fff8f1 ;transition:all .1s ease;
        }
    </style>
</head>
<body>
    <h1>Hello</h1>
    <div class="login-container">
        <div class="login-header">
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_BROWN.png" alt="HERMES">
            <h2>Acceso Administrativo</h2>
            <p>Panel exclusivo para administradores</p>
        </div>
        <form action="admin-login.php" method="POST">
            <div class="form-group">
                <label for="usuario">Usuario Administrativo:</label>
                <input type="text" id="usuario" name="usuario" required autofocus>
            </div>
            <div class="form-group">
                <label for="contrasena">Contraseña:</label>
                <input type="password" id="contrasena" name="contrasena" required>
            </div>
            <button type="submit" class="btn-login">Ingresar</button>
        </form>
    </div>
    <script>
     const tittle_hello = document.querySelector('h1')
     const container_login = document.querySelector('.login-container')

     setTimeout(()=>{
        tittle_hello.style.opacity = '1'
        tittle_hello.style.transform = 'translate(400px, 0)'
     }, 1000)

     setTimeout(()=>{
        tittle_hello.style.transform = 'translate(50px, 0)'
        container_login.style.opacity = '1'
        container_login.style.pointerEvents = 'auto'
     }, 2000)

     setTimeout(()=>{
        document.querySelector('h1').classList.add('show-point')
     }, 3500)

    </script>
</body>
</html>