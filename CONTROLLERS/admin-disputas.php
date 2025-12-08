<?php
ob_start(); 
session_start();

// --- ZONA DE DEBUGGING CRÍTICO ---
ini_set('display_errors', 1);
error_reporting(E_ALL);
// ---------------------------------

if (!include('../shortCuts/connect.php')) {
    die("Error Fatal: No se pudo encontrar o incluir el archivo de conexión.");
}

if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header('Location: ../registros-inicio-sesion/admin-login.php');
    exit();
}

// ----------------------------------------------------
// CONSULTA: LISTADO DE PEDIDOS CANCELADOS (SIMULACIÓN DE DISPUTAS)
// ----------------------------------------------------

$query_cancelados = "
    SELECT 
        p.id_pedido,
        p.fecha_pedido,
        p.total AS monto_total,
        p.descripcion, 
        uc.nombre AS nombre_cliente,
        uc.apellido AS apellido_cliente
    FROM 
        pedido p
    JOIN 
        usuario uc ON p.id_cliente = uc.id_usuario 
    WHERE 
        p.estado = 'Cancelado'
    ORDER BY 
        p.fecha_pedido DESC
";

$result_cancelados = mysqli_query($connect, $query_cancelados);
if (!$result_cancelados) {
    die("Error al consultar pedidos cancelados: " . mysqli_error($connect));
}

function format_currency($amount) {
    return '$' . number_format($amount, 2, ',', '.');
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disputas Simuladas (Cancelados) - HERMES Admin</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; color: #333; margin: 0; padding: 20px; }
        .container { max-width: 95%; margin: 20px auto; padding: 30px; background: #fff; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        h1 { color: #dc3545; border-bottom: 2px solid #eee; padding-bottom: 10px; margin-bottom: 20px; }
        .tabla-disputas { width: 100%; border-collapse: collapse; margin-top: 20px; }
        .tabla-disputas th, .tabla-disputas td { border: 1px solid #ddd; padding: 12px; text-align: left; vertical-align: middle; font-size: 0.95em; }
        .tabla-disputas th { background-color: #dc3545; color: white; font-weight: 600; }
        .btn-secondary { background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; display: inline-block; border-radius: 5px; }
        .btn-warning { background: #ffc107; color: #333; }
    </style>
</head>
<body>
    <div class="container">
        <a href="admin_index.php" class="btn btn-secondary"><i class="fas fa-arrow-left"></i> Volver al Panel</a>
        
        <h1><i class="fas fa-gavel"></i> Disputas/Reclamos (Pedidos Cancelados)</h1>
        
        <p style="color: #666;"><i class="fas fa-info-circle"></i> **SIMULACIÓN:** Dado que no hay tablas de disputas, se muestran los pedidos cancelados para su revisión.</p>

        <table class="tabla-disputas">
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Cliente</th>
                    <th>Fecha Cancelación</th>
                    <th>Monto Total</th>
                    <th>Descripción / Razón</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (mysqli_num_rows($result_cancelados) > 0): ?>
                    <?php while ($row = mysqli_fetch_assoc($result_cancelados)): ?>
                        <tr>
                            <td>#<?php echo htmlspecialchars($row['id_pedido']); ?></td>
                            <td><?php echo htmlspecialchars($row['nombre_cliente'] . ' ' . $row['apellido_cliente']); ?></td>
                            <td><?php echo date('Y-m-d H:i', strtotime($row['fecha_pedido'])); ?></td>
                            <td><?php echo format_currency($row['monto_total']); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion'] ?? 'Sin descripción de cancelación.'); ?></td>
                            <td>
                                <a href="admin_pedidos_update.php?id=<?php echo htmlspecialchars($row['id_pedido']); ?>" class="btn btn-warning">
                                    <i class="fas fa-search"></i> Revisar y Actualizar Estado
                                </a>
                            </td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="6" style="text-align: center;">No hay pedidos cancelados que requieran revisión.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</body>
</html>
<?php ob_end_flush(); ?>