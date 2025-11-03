<?php
    include('../registros-inicio-sesion/connect.php');

    // Validamos que el parámetro id_usuario esté presente en la URL
    if (isset($_GET['id_usuario']) && is_numeric($_GET['id_usuario'])) {
        $id_usuario = $_GET['id_usuario'];

        // Preparamos la consulta DELETE
        $sql = "DELETE FROM usuario WHERE id_usuario = ?";
        $stmt = mysqli_prepare($connect, $sql);

        // Vinculamos el parámetro
        mysqli_stmt_bind_param($stmt, 'i', $id_usuario);

        // Ejecutamos la consulta
        $result = mysqli_stmt_execute($stmt);

        // Cerramos la sentencia
        mysqli_stmt_close($stmt);

        // Mostramos resultado y redireccionamos
        if ($result) {
            echo "✅ Usuario eliminado correctamente.";
            echo '<script>
                setTimeout(function() {
                    window.location.href = "Admin_CRUD.php";
                }, 2000);
            </script>';
        } else {
            echo "❌ Error al eliminar: " . mysqli_error($connect);
        }
    } else {
        echo "⚠️ ID de usuario inválido o no proporcionado.";
    }

    // Cerramos la conexión
    mysqli_close($connect);
?>