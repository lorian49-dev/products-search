<?php
session_start();
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
use PHPMailer\PHPMailer\SMTP;


// CONEXIÓN A LA BD
$conexion = new mysqli("localhost", "root", "", "hermes_bd");
if ($conexion->connect_error) {
    die("Error de conexión: " . $conexion->connect_error);
}

// ---------------------------------------------------
// 1. FORMULARIO: INGRESAR CORREO
// ---------------------------------------------------
if (!isset($_POST['accion'])) {
?>
    <h2>Recuperar contraseña</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="enviar_codigo">
        <label>Correo registrado:</label><br>
        <input type="email" name="correo" required><br><br>
        <button type="submit">Enviar código</button>
    </form>
<?php
    exit();
}

// ---------------------------------------------------
// 2. ENVÍO DEL CÓDIGO
// ---------------------------------------------------
if ($_POST['accion'] == "enviar_codigo") {

    $correo = $_POST['correo'];

    // Buscar usuario (verificar prepare)
    $sql = "SELECT id_usuario FROM usuario WHERE correo = ?";
    $stmt = $conexion->prepare($sql);
    if (!$stmt) {
        echo "<p>Error en la consulta: " . htmlspecialchars($conexion->error) . "</p>";
        exit();
    }
    $stmt->bind_param("s", $correo);
    if (!$stmt->execute()) {
        echo "<p>Error al ejecutar la consulta: " . htmlspecialchars($stmt->error) . "</p>";
        exit();
    }

    // Obtener resultado de forma compatible
    $res = $stmt->get_result();
    if ($res === false) {
        // Fallback si get_result() no está disponible
        $stmt->store_result();
        if ($stmt->num_rows == 0) {
            echo "<p>El correo no está registrado.</p>";
            exit();
        }
        $stmt->bind_result($id);
        $stmt->fetch();
    } else {
        if ($res->num_rows == 0) {
            echo "<p>El correo no está registrado.</p>";
            exit();
        }
        $usuario = $res->fetch_assoc();
        $id = $usuario['id_usuario'];
    }

    // Crear código OTP
    $codigo = rand(100000, 999999);
    $expira = date("Y-m-d H:i:s", time() + 300);

    // Guardar código (verificar prepare)
    $sql2 = "UPDATE usuario SET codigo_recuperacion=?, codigo_expira=? WHERE id_usuario=?";
    $stmt2 = $conexion->prepare($sql2);
    if (!$stmt2) {
        echo "<p>Error en la consulta de actualización: " . htmlspecialchars($conexion->error) . "</p>";
        exit();
    }
    // asegurar tipo: codigo como string
    $codigo_str = (string)$codigo;
    $stmt2->bind_param("ssi", $codigo_str, $expira, $id);
    if (!$stmt2->execute()) {
        echo "<p>Error al guardar el código: " . htmlspecialchars($stmt2->error) . "</p>";
        exit();
    }

    $_SESSION['user_recuperacion'] = $id;

    // -------------------------
    // ENVÍO CON MAILTRAP SMTP (PHPMailer)
    // -------------------------
    $mail = new PHPMailer(true);

    try {
        // Configuración SMTP de Mailtrap
        $mail->isSMTP();
        $mail->Host = "sandbox.smtp.mailtrap.io";
        $mail->SMTPAuth = true;
        $mail->Username = "08d11c1229304c";
        $mail->Password = "TU_CONTRASEÑA_REAL";  // reemplaza
        $mail->Port = 587;
        // usar la constante de la clase para STARTTLS (funciona si use está en el global)
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;

        $mail->setFrom("soporte@hermes.com", "Hermes");
        $mail->addAddress($correo);
        $mail->Subject = "Código de recuperación";
        $mail->Body = "Tu código de recuperación es: $codigo";

        if ($mail->send()) {
            echo "<p>El código ha sido enviado a tu correo.</p>";
        } else {
            echo "<p>No se pudo enviar el correo. Error: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
            exit();
        }

    } catch (Exception $e) {
        echo "<p>No se pudo enviar el correo. Exception: " . htmlspecialchars($mail->ErrorInfo) . "</p>";
        exit();
    }

    // Mostrar formulario para ingresar el código
    ?>
    <h2>Ingresa el código enviado a tu correo</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="validar_codigo">
        <label>Código:</label><br>
        <input type="number" name="codigo" required><br><br>
        <button type="submit">Validar</button>
    </form>
    <?php
    exit();
}

// ---------------------------------------------------
// 3. VALIDAR CÓDIGO
// ---------------------------------------------------
if ($_POST['accion'] == "validar_codigo") {

    if (!isset($_SESSION['user_recuperacion'])) {
        die("Acceso inválido.");
    }

    $id = $_SESSION['user_recuperacion'];
    $codigo_ingresado = $_POST['codigo'];

    $sql = "SELECT codigo_recuperacion, codigo_expira FROM usuario WHERE id_usuario=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $datos = $stmt->get_result()->fetch_assoc();

    if (!$datos) {
        echo "<p>Error: usuario no encontrado.</p>";
        exit();
    }

    if ($codigo_ingresado != $datos['codigo_recuperacion']) {
        echo "<p>Código incorrecto.</p>";
        exit();
    }

    if (strtotime($datos['codigo_expira']) < time()) {
        echo "<p>El código ha expirado.</p>";
        exit();
    }

    // Mostrar formulario de nueva contraseña
?>
    <h2>Crear nueva contraseña</h2>
    <form method="POST">
        <input type="hidden" name="accion" value="nueva_pass">
        <label>Nueva contraseña:</label><br>
        <input type="password" name="password" required><br><br>
        <button type="submit">Actualizar contraseña</button>
    </form>
<?php
    exit();
}

// ---------------------------------------------------
// 4. ACTUALIZAR CONTRASEÑA
// ---------------------------------------------------
if ($_POST['accion'] == "nueva_pass") {

    if (!isset($_SESSION['user_recuperacion'])) {
        die("Acceso inválido.");
    }

    $id = $_SESSION['user_recuperacion'];
    $newpass = password_hash($_POST['password'], PASSWORD_DEFAULT);

    $sql = "UPDATE usuario SET password=?, codigo_recuperacion=NULL, codigo_expira=NULL WHERE id_usuario=?";
    $stmt = $conexion->prepare($sql);
    $stmt->bind_param("si", $newpass, $id);
    $stmt->execute();

    unset($_SESSION['user_recuperacion']);

    echo "<h3>Contraseña actualizada correctamente 🎉</h3>";
    echo "<a href='login.php'>Volver al inicio de sesión</a>";
}
?>
