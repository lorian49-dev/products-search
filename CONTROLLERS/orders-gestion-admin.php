<?php
session_start();
require_once 'conexion.php';

// Procesar actualización si se envió
if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['actualizar_estado'])) {
    $id_pedido = intval($_POST['id_pedido']);
    $nuevo_estado = $conn->real_escape_string($_POST['estado']);
    $observacion = $conn->real_escape_string($_POST['observacion'] ?? '');
    
    $query = "UPDATE pedido SET 
              estado = '$nuevo_estado',
              descripcion = CONCAT(COALESCE(descripcion, ''), '\n[Admin - ', NOW(), ']: ', '$observacion')
              WHERE id_pedido = $id_pedido";
    
    if($connect->query($query)) {
        $mensaje = "Estado actualizado correctamente";
    } else {
        $mensaje = "Error al actualizar: " . $connect->error;
    }
}

// Consulta de pedidos con filtros
$filtro_estado = isset($_GET['estado']) ? $_GET['estado'] : 'todos';
$where = "1=1";

if($filtro_estado != 'todos') {
    $where .= " AND p.estado = '" . $conn->real_escape_string($filtro_estado) . "'";
}

$query_pedidos = "
    SELECT 
        p.id_pedido,
        p.fecha_pedido,
        p.total,
        p.estado,
        p.descripcion,
        p.llegada_estimada,
        CONCAT(u.nombre, ' ', u.apellido) AS cliente,
        u.correo AS email_cliente,
        u.telefono AS telefono_cliente,
        GROUP_CONCAT(CONCAT(pr.nombre, ' (x', dp.cantidad, ')') SEPARATOR '<br>') AS productos
    FROM pedido p
    INNER JOIN usuario u ON p.id_usuario = u.id_usuario
    INNER JOIN detalle_pedido dp ON p.id_pedido = dp.id_pedido
    INNER JOIN producto pr ON dp.id_producto = pr.id_producto
    WHERE $where
    GROUP BY p.id_pedido
    ORDER BY 
        CASE p.estado 
            WHEN 'Pendiente' THEN 1
            WHEN 'Enviado' THEN 2
            WHEN 'Entregado' THEN 3
            WHEN 'Cancelado' THEN 4
        END,
        p.fecha_pedido DESC
";

$pedidos = $conn->query($query_pedidos);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Pedidos</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1400px; margin: 0 auto; }
        .header { background: #2c3e50; color: white; padding: 20px; border-radius: 8px; margin-bottom: 20px; }
        .filtros { background: white; padding: 15px; border-radius: 8px; margin-bottom: 20px; box-shadow: 0 2px 5px rgba(0,0,0,0.1); }
        table { width: 100%; background: white; border-collapse: collapse; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        th, td { padding: 12px 15px; text-align: left; border-bottom: 1px solid #ddd; vertical-align: top; }
        th { background: #34495e; color: white; }
        .estado-pendiente { background: #fff3cd; color: #856404; padding: 5px 10px; border-radius: 4px; }
        .estado-enviado { background: #d1ecf1; color: #0c5460; padding: 5px 10px; border-radius: 4px; }
        .estado-entregado { background: #d4edda; color: #155724; padding: 5px 10px; border-radius: 4px; }
        .estado-cancelado { background: #f8d7da; color: #721c24; padding: 5px 10px; border-radius: 4px; }
        .btn { padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; }
        .btn-editar { background: #3498db; color: white; }
        .btn-editar:hover { background: #2980b9; }
        .btn-guardar { background: #27ae60; color: white; }
        .btn-guardar:hover { background: #219653; }
        .btn-cancelar { background: #e74c3c; color: white; }
        .btn-cancelar:hover { background: #c0392b; }
        .modal { display: none; position: fixed; z-index: 1000; left: 0; top: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); }
        .modal-content { background: white; margin: 5% auto; padding: 20px; width: 90%; max-width: 500px; border-radius: 8px; }
        .cerrar { float: right; font-size: 28px; cursor: pointer; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; font-weight: bold; }
        select, textarea { width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .alerta { padding: 15px; border-radius: 4px; margin-bottom: 20px; }
        .alerta-exito { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .alerta-error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Gestión de Pedidos</h1>
            <p>Actualiza el estado de los pedidos y agrega observaciones</p>
        </div>

        <?php if(isset($mensaje)): ?>
            <div class="alerta <?php echo strpos($mensaje, '✅') !== false ? 'alerta-exito' : 'alerta-error'; ?>">
                <?php echo $mensaje; ?>
            </div>
        <?php endif; ?>

        <div class="filtros">
            <form method="GET">
                <label>Filtrar por estado:</label>
                <select name="estado" onchange="this.form.submit()">
                    <option value="todos" <?php echo $filtro_estado == 'todos' ? 'selected' : ''; ?>>Todos los estados</option>
                    <option value="Pendiente" <?php echo $filtro_estado == 'Pendiente' ? 'selected' : ''; ?>>Pendiente</option>
                    <option value="Enviado" <?php echo $filtro_estado == 'Enviado' ? 'selected' : ''; ?>>Enviado</option>
                    <option value="Entregado" <?php echo $filtro_estado == 'Entregado' ? 'selected' : ''; ?>>Entregado</option>
                    <option value="Cancelado" <?php echo $filtro_estado == 'Cancelado' ? 'selected' : ''; ?>>Cancelado</option>
                </select>
            </form>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID Pedido</th>
                    <th>Fecha</th>
                    <th>Cliente</th>
                    <th>Productos</th>
                    <th>Total</th>
                    <th>Estado</th>
                    <th>Llegada Estimada</th>
                    <th>Observaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while($pedido = $pedidos->fetch_assoc()): 
                    $clase_estado = 'estado-' . strtolower($pedido['estado']);
                ?>
                <tr>
                    <td>#<?php echo $pedido['id_pedido']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></td>
                    <td>
                        <strong><?php echo htmlspecialchars($pedido['cliente']); ?></strong><br>
                        📧 <?php echo htmlspecialchars($pedido['email_cliente']); ?><br>
                        📞 <?php echo htmlspecialchars($pedido['telefono_cliente']); ?>
                    </td>
                    <td><?php echo $pedido['productos']; ?></td>
                    <td>$<?php echo number_format($pedido['total'], 2); ?></td>
                    <td><span class="<?php echo $clase_estado; ?>"><?php echo $pedido['estado']; ?></span></td>
                    <td><?php echo $pedido['llegada_estimada'] ? date('d/m/Y', strtotime($pedido['llegada_estimada'])) : 'No definida'; ?></td>
                    <td><small><?php echo nl2br(htmlspecialchars($pedido['descripcion'] ?: 'Sin observaciones')); ?></small></td>
                    <td>
                        <button class="btn btn-editar" onclick="editarPedido(<?php echo $pedido['id_pedido']; ?>)">
                            ✏️ Editar
                        </button>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <!-- Modal para editar pedido -->
    <div id="modalEditar" class="modal">
        <div class="modal-content">
            <span class="cerrar" onclick="cerrarModal()">&times;</span>
            <h2>Editar Estado del Pedido</h2>
            <form id="formEditar" method="POST">
                <input type="hidden" name="id_pedido" id="id_pedido">
                <input type="hidden" name="actualizar_estado" value="1">
                
                <div class="form-group">
                    <label>Estado actual:</label>
                    <span id="estado_actual" style="font-weight:bold;"></span>
                </div>
                
                <div class="form-group">
                    <label>Nuevo estado:</label>
                    <select name="estado" id="nuevo_estado" required>
                        <option value="Pendiente">Pendiente</option>
                        <option value="Enviado">Enviado</option>
                        <option value="Entregado">Entregado</option>
                        <option value="Cancelado">Cancelado</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Observación adicional:</label>
                    <textarea name="observacion" rows="3" placeholder="Agregar nota para el cliente/vendedor..."></textarea>
                </div>
                
                <button type="submit" class="btn btn-guardar">💾 Guardar Cambios</button>
                <button type="button" class="btn btn-cancelar" onclick="cerrarModal()">Cancelar</button>
            </form>
        </div>
    </div>

    <script>
        function editarPedido(idPedido) {
            // Obtener datos del pedido (podrías usar AJAX para más datos)
            document.getElementById('id_pedido').value = idPedido;
            
            // En un caso real, aquí harías una petición AJAX para obtener el estado actual
            // Por ahora, solo mostramos el modal
            document.getElementById('modalEditar').style.display = 'block';
        }

        function cerrarModal() {
            document.getElementById('modalEditar').style.display = 'none';
        }

        // Cerrar modal al hacer clic fuera
        window.onclick = function(event) {
            var modal = document.getElementById('modalEditar');
            if (event.target == modal) {
                cerrarModal();
            }
        }
    </script>
</body>
</html>