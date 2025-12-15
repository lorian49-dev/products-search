<?php
session_start();
header('Content-Type: application/json');

// PARA DEPURACIÓN - MANTENER HASTA QUE FUNCIONE
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Archivo para logs de depuración
$log_file = __DIR__ . '/recharge_debug.log';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Iniciando recarga\n", FILE_APPEND);

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - No hay sesión\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'No autorizado. Inicia sesión.']);
    exit;
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Usuario ID: " . $_SESSION['usuario_id'] . "\n", FILE_APPEND);

// Verificar método POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Método no POST\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
    exit;
}

// Obtener datos
$id_usuario = intval($_SESSION['usuario_id']);
$monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Monto recibido: " . $monto . "\n", FILE_APPEND);

// Validaciones
if ($monto < 1000) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Monto muy bajo: " . $monto . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Monto mínimo: $1.000']);
    exit;
}

if ($monto > 10000000) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Monto muy alto: " . $monto . "\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Monto máximo: $10.000.000']);
    exit;
}

// CONEXIÓN A LA BASE DE DATOS - CORREGIR RUTA
// Depende de dónde está tu archivo connect.php

// OPCIÓN 1: Si recharge-wallet.php está en CONTROLLERS/
$connect_path = dirname(__DIR__) . '/shortCuts/connect.php';
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Intentando ruta: " . $connect_path . "\n", FILE_APPEND);

if (!file_exists($connect_path)) {
    // OPCIÓN 2: Si está en otra ubicación
    $connect_path = __DIR__ . '/../../shortCuts/connect.php';
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Nueva ruta: " . $connect_path . "\n", FILE_APPEND);
}

if (!file_exists($connect_path)) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Archivo connect.php NO encontrado\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error de configuración del servidor (connect.php no encontrado)']);
    exit;
}

// Incluir el archivo de conexión
require_once $connect_path;
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Connect.php incluido\n", FILE_APPEND);

// Verificar conexión
if (!$connect) {
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conexión fallida\n", FILE_APPEND);
    echo json_encode(['success' => false, 'message' => 'Error de conexión a la base de datos']);
    exit;
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conexión exitosa\n", FILE_APPEND);

// Iniciar transacción
mysqli_begin_transaction($connect);
file_put_contents($log_file, date('Y-m-d H:i:s') . " - Transacción iniciada\n", FILE_APPEND);

try {
    // 1. Verificar si ya tiene billetera
    $sql_check = "SELECT id_metodo_pago, saldo_billetera FROM metodos_pago 
                  WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - SQL check: " . $sql_check . "\n", FILE_APPEND);
    
    $stmt_check = mysqli_prepare($connect, $sql_check);
    if (!$stmt_check) {
        throw new Exception("Error preparando consulta: " . mysqli_error($connect));
    }
    
    mysqli_stmt_bind_param($stmt_check, "i", $id_usuario);
    mysqli_stmt_execute($stmt_check);
    $result = mysqli_stmt_get_result($stmt_check);
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Filas encontradas: " . mysqli_num_rows($result) . "\n", FILE_APPEND);
    
    if (mysqli_num_rows($result) > 0) {
        // Actualizar saldo existente
        $row = mysqli_fetch_assoc($result);
        $id_metodo_pago = $row['id_metodo_pago'];
        $saldo_anterior = floatval($row['saldo_billetera']);
        $saldo_nuevo = $saldo_anterior + $monto;
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Actualizando - ID: $id_metodo_pago, Anterior: $saldo_anterior, Nuevo: $saldo_nuevo\n", FILE_APPEND);
        
        $sql_update = "UPDATE metodos_pago 
                      SET saldo_billetera = ? 
                      WHERE id_metodo_pago = ?";
        
        $stmt_update = mysqli_prepare($connect, $sql_update);
        
        if (!$stmt_update) {
            throw new Exception("Error preparando actualización: " . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt_update, "di", $saldo_nuevo, $id_metodo_pago);
        mysqli_stmt_execute($stmt_update);
        
        $affected = mysqli_stmt_affected_rows($stmt_update);
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Filas afectadas en update: " . $affected . "\n", FILE_APPEND);
        
        mysqli_stmt_close($stmt_update);
    } else {
        // Crear nueva billetera
        $saldo_anterior = 0;
        $saldo_nuevo = $monto;
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Creando nueva billetera - Nuevo: $saldo_nuevo\n", FILE_APPEND);
        
        $sql_insert = "INSERT INTO metodos_pago (id_usuario, tipo, saldo_billetera, fecha_creacion) 
                      VALUES (?, 'billetera_virtual', ?, NOW())";
        
        $stmt_insert = mysqli_prepare($connect, $sql_insert);
        
        if (!$stmt_insert) {
            throw new Exception("Error preparando inserción: " . mysqli_error($connect));
        }
        
        mysqli_stmt_bind_param($stmt_insert, "id", $id_usuario, $saldo_nuevo);
        mysqli_stmt_execute($stmt_insert);
        
        $id_metodo_pago = mysqli_insert_id($connect);
        $affected = mysqli_stmt_affected_rows($stmt_insert);
        
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Billetera creada - ID: $id_metodo_pago, Filas: $affected\n", FILE_APPEND);
        
        mysqli_stmt_close($stmt_insert);
    }
    
    mysqli_stmt_close($stmt_check);
    
    // 2. Registrar transacción
    try {
        $sql_trans = "INSERT INTO transacciones_billetera 
                     (id_usuario, id_metodo_pago, tipo, monto, saldo_anterior, saldo_nuevo, descripcion, fecha_transaccion) 
                     VALUES (?, ?, 'recarga', ?, ?, ?, 'Recarga desde panel de usuario', NOW())";
        
        $stmt_trans = mysqli_prepare($connect, $sql_trans);
        
        if ($stmt_trans) {
            mysqli_stmt_bind_param($stmt_trans, "iiddd", $id_usuario, $id_metodo_pago, $monto, $saldo_anterior, $saldo_nuevo);
            mysqli_stmt_execute($stmt_trans);
            mysqli_stmt_close($stmt_trans);
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - Transacción registrada\n", FILE_APPEND);
        } else {
            file_put_contents($log_file, date('Y-m-d H:i:s') . " - No se pudo registrar transacción: " . mysqli_error($connect) . "\n", FILE_APPEND);
        }
    } catch (Exception $e) {
        // No fallar si no se puede registrar la transacción
        file_put_contents($log_file, date('Y-m-d H:i:s') . " - Error registrando transacción: " . $e->getMessage() . "\n", FILE_APPEND);
    }
    
    // Confirmar transacción
    mysqli_commit($connect);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Transacción confirmada\n", FILE_APPEND);
    
    echo json_encode([
        'success' => true,
        'message' => '¡Recarga exitosa!',
        'saldo_anterior' => $saldo_anterior,
        'saldo_nuevo' => $saldo_nuevo
    ]);
    
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Respuesta enviada: ÉXITO\n", FILE_APPEND);
    
} catch (Exception $e) {
    // Revertir en caso de error
    mysqli_rollback($connect);
    
    $error_msg = "Error en recarga: " . $e->getMessage();
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - ERROR: " . $error_msg . "\n", FILE_APPEND);
    
    echo json_encode([
        'success' => false, 
        'message' => $error_msg
    ]);
}

// Cerrar conexión
if (isset($connect)) {
    mysqli_close($connect);
    file_put_contents($log_file, date('Y-m-d H:i:s') . " - Conexión cerrada\n", FILE_APPEND);
}

file_put_contents($log_file, date('Y-m-d H:i:s') . " - Proceso finalizado\n\n", FILE_APPEND);
?>