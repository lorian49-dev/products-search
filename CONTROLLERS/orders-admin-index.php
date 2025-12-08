<?php

session_start();

/* Verifica si está logueado como admin */
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';
    </script>";
    exit();
}

/* Verifica rol permitido */
$rolesPermitidos = [1, 2];

if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "hermes_bd");
if ($conn->connect_error) {
    die("Error de conexión");
}

/* ========================= */
/*      BUSCADOR VENTAS      */
/* ========================= */
$busqueda = $_GET['busqueda'] ?? '';
$where = '';

if (!empty($busqueda)) {
    $busqueda = $conn->real_escape_string($busqueda);
    $where = "WHERE 
        v.nombre_empresa LIKE '%$busqueda%' OR
        pr.nombre LIKE '%$busqueda%' OR
        u.nombre LIKE '%$busqueda%' OR
        u.apellido LIKE '%$busqueda%' OR
        p.estado LIKE '%$busqueda%'";
}

?>

<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Panel Admin</title>
</head>
<body>

<h1>PANEL ADMINISTRATIVO</h1>

<hr>

<!-- =============================== -->
<!-- 1. LISTADO DE VENTAS POR VENDEDOR -->
<!-- =============================== -->

<h2>Listado de Ventas por Vendedor</h2>

<!-- BUSCADOR -->
<form method="GET">
    <input type="text" name="busqueda" placeholder="Buscar ventas..."
           value="<?= htmlspecialchars($busqueda) ?>">
    <button type="submit">Buscar</button>
</form>

<br>

<table border="1">
<tr>
    <th>Vendedor</th>
    <th>Producto</th>
    <th>Cliente</th>
    <th>Cantidad</th>
    <th>Total</th>
    <th>Estado Pedido</th>
</tr>

<?php
$sql = "
SELECT 
    v.nombre_empresa,
    pr.nombre AS producto,
    u.nombre,
    u.apellido,
    dp.cantidad,
    dp.precio_total,
    p.estado
FROM pedido p
INNER JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
INNER JOIN producto pr ON dp.id_producto = pr.id_producto
INNER JOIN vendedor v ON pr.id_vendedor = v.id_vendedor
INNER JOIN cliente c ON p.id_cliente = c.id_cliente
INNER JOIN usuario u ON c.id_cliente = u.id_usuario
$where
";

$res = $conn->query($sql);

if ($res) {
    while ($row = $res->fetch_assoc()) {
        echo "<tr>
            <td>{$row['nombre_empresa']}</td>
            <td>{$row['producto']}</td>
            <td>{$row['nombre']} {$row['apellido']}</td>
            <td>{$row['cantidad']}</td>
            <td>{$row['precio_total']}</td>
            <td>{$row['estado']}</td>
        </tr>";
    }
}
?>
</table>

<hr>

<!-- =============================== -->
<!-- 2. ACTUALIZAR ESTADO DE PEDIDOS -->
<!-- =============================== -->

<h2>Actualizar Estados de Pedidos</h2>

<table border="1">
<tr>
    <th>ID Pedido</th>
    <th>Estado Actual</th>
    <th>Nuevo Estado</th>
    <th>Guardar</th>
</tr>

<?php
$ped = $conn->query("SELECT * FROM pedido");

if ($ped) {
    while ($p = $ped->fetch_assoc()) {
        echo "
        <tr>
            <form method='POST'>
            <td>{$p['id_pedido']}</td>
            <td>{$p['estado']}</td>
            <td>
                <select name='estado'>
                    <option>Pendiente</option>
                    <option>Enviado</option>
                    <option>Entregado</option>
                    <option>Cancelado</option>
                </select>
            </td>
            <td>
                <input type='hidden' name='id' value='{$p['id_pedido']}'>
                <button name='actualizar'>Guardar</button>
            </td>
            </form>
        </tr>";
    }
}

if (isset($_POST["actualizar"])) {
    $id = $_POST["id"];
    $estado = $_POST["estado"];

    if ($conn->query("UPDATE pedido SET estado='$estado' WHERE id_pedido=$id")) {
        echo "<script>
            alert('Estado actualizado correctamente.');
            window.location.href = window.location.href;
        </script>";
    }
}
?>
</table>

</body>
</html>
