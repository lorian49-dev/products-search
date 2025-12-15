<?php
session_start();
include("../shortCuts/connect.php");

// Verificar sesión
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../login.php");
    exit;
}

$usuario_id = intval($_SESSION['usuario_id']);

// Obtener datos del usuario
$sql_usuario = "SELECT nombre, apellido FROM usuario WHERE id_usuario = $usuario_id";
$result_usuario = mysqli_query($connect, $sql_usuario);
$usuario = mysqli_fetch_assoc($result_usuario);

// Obtener métodos de pago del usuario
$sql_metodos = "SELECT * FROM metodos_pago WHERE id_usuario = $usuario_id ORDER BY es_predeterminado DESC, fecha_creacion DESC";
$result_metodos = mysqli_query($connect, $sql_metodos);

$metodos_pago = [];
if ($result_metodos && mysqli_num_rows($result_metodos) > 0) {
    while ($row = mysqli_fetch_assoc($result_metodos)) {
        $metodos_pago[] = $row;
    }
}

// Obtener método predeterminado
$metodo_predeterminado = null;
foreach ($metodos_pago as $metodo) {
    if ($metodo['es_predeterminado'] == 1) {
        $metodo_predeterminado = $metodo;
        break;
    }
}

// Obtener saldo de billetera virtual
$sql_billetera = "SELECT saldo_billetera FROM metodos_pago WHERE id_usuario = $usuario_id AND tipo = 'billetera_virtual'";
$result_billetera = mysqli_query($connect, $sql_billetera);
$billetera = mysqli_fetch_assoc($result_billetera);
$saldo_billetera = $billetera ? $billetera['saldo_billetera'] : 0.00;

// Contar métodos de pago (excluyendo billetera virtual)
$num_metodos_visibles = 0;
foreach ($metodos_pago as $metodo) {
    if ($metodo['tipo'] != 'billetera_virtual') {
        $num_metodos_visibles++;
    }
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Métodos de Pago</title>
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../styles/home.css">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <style>
        /* Estilos del dashboard (mismos que en datos-personales) */
        .dashboard-container {
            display: flex;
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            gap: 30px;
        }

        .dashboard-sidebar {
            width: 250px;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            padding: 20px;
            height: fit-content;
        }

        .sidebar-title {
            font-size: 18px;
            font-weight: 600;
            color: #333;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .sidebar-menu {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .sidebar-menu li {
            margin-bottom: 5px;
        }

        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 15px;
            color: #555;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            gap: 12px;
        }

        .sidebar-menu a.active {
            background: #e3f2fd;
            color: #1976d2;
            font-weight: 500;
        }

        /* ESTILOS ESPECÍFICOS PARA MÉTODOS DE PAGO */
        .dashboard-content {
            flex: 1;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 0 10px #ccc;
            padding: 30px;
        }

        .current-page-title {
            font-size: 24px;
            color: #333;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .payment-methods-container {
            margin-top: 30px;
        }

        /* Estilos para Billetera Virtual */
        .wallet-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(102, 126, 234, 0.3);
        }

        .wallet-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .wallet-title {
            font-size: 22px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .wallet-balance {
            font-size: 36px;
            font-weight: 700;
            text-align: center;
            margin: 25px 0;
        }

        .wallet-actions {
            display: flex;
            gap: 15px;
            margin-top: 20px;
        }

        .btn-wallet {
            flex: 1;
            padding: 12px;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-recharge {
            background: white;
            color: #667eea;
        }

        .btn-recharge:hover {
            background: #f8f9fa;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .btn-history {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .btn-history:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Estilos para Contra Entrega */
        .cod-section {
            background: linear-gradient(135deg, #ff9a9e 0%, #fad0c4 100%);
            color: #333;
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(255, 154, 158, 0.3);
        }

        .cod-title {
            font-size: 22px;
            font-weight: 600;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .cod-info {
            background: rgba(255, 255, 255, 0.8);
            padding: 20px;
            border-radius: 10px;
            margin-top: 15px;
        }

        /* Métodos de pago registrados */
        .method-card {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            position: relative;
            transition: all 0.3s;
        }

        .method-card:hover {
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .method-card.default {
            border-left: 4px solid #28a745;
            background: #f0fff4;
        }

        .method-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 15px;
        }

        .method-type {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .method-icon {
            font-size: 24px;
            color: #1976d2;
        }

        .method-icon.visa {
            color: #1a1f71;
        }

        .method-icon.mastercard {
            color: #eb001b;
        }

        .method-icon.amex {
            color: #2e77bc;
        }

        .method-icon.paypal {
            color: #003087;
        }

        .badge-default {
            background: #28a745;
            color: white;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .method-details {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 15px;
        }

        .method-detail {
            display: flex;
            flex-direction: column;
        }

        .method-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .method-value {
            font-weight: 500;
            color: #333;
        }

        .card-number {
            font-family: 'Courier New', monospace;
            letter-spacing: 1px;
        }

        .method-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }

        .btn-method {
            padding: 8px 15px;
            border-radius: 6px;
            border: none;
            font-size: 14px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-set-default {
            background: #1976d2;
            color: white;
        }

        .btn-set-default:hover {
            background: #1565c0;
        }

        .btn-delete {
            background: #dc3545;
            color: white;
        }

        .btn-delete:hover {
            background: #c82333;
        }

        /* Formulario para agregar método de pago */
        .add-method-form {
            background: #f8f9fa;
            padding: 25px;
            border-radius: 10px;
            margin-top: 30px;
        }

        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 15px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ced4da;
            border-radius: 6px;
            font-size: 14px;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #80bdff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, .25);
        }

        .card-images {
            display: flex;
            gap: 10px;
            margin-top: 10px;
        }

        .card-image {
            width: 40px;
            opacity: 0.5;
        }

        .card-image.active {
            opacity: 1;
        }

        .payment-types {
            display: flex;
            gap: 15px;
            margin-bottom: 20px;
        }

        .payment-type-btn {
            flex: 1;
            padding: 15px;
            text-align: center;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: white;
            cursor: pointer;
            transition: all 0.3s;
        }

        .payment-type-btn:hover {
            border-color: #1976d2;
        }

        .payment-type-btn.active {
            border-color: #1976d2;
            background: #e3f2fd;
        }

        .payment-type-btn i {
            font-size: 24px;
            margin-bottom: 10px;
            display: block;
        }

        /* Sección PayPal */
        .paypal-section {
            display: none;
        }

        .paypal-section.active {
            display: block;
        }

        .empty-state {
            text-align: center;
            padding: 40px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 48px;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        .btn-submit {
            background: #28a745;
            color: white;
            padding: 12px 30px;
            border: none;
            border-radius: 6px;
            font-weight: 500;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }

        .btn-submit:hover {
            background: #218838;
            transform: translateY(-1px);
        }

        .security-note {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            border-radius: 6px;
            padding: 15px;
            margin-top: 20px;
            font-size: 14px;
            color: #856404;
        }

        /* Modal para recarga */
        .modal-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }

        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 15px;
            width: 90%;
            max-width: 400px;
        }

        @media (max-width: 768px) {
            .dashboard-container {
                flex-direction: column;
            }

            .dashboard-sidebar {
                width: 100%;
            }

            .method-actions {
                flex-direction: column;
            }

            .wallet-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <?php include '../TEMPLATES/header.php' ?>

    <div class="dashboard-container">
        <!-- MENÚ LATERAL -->
        <div class="dashboard-sidebar">
            <div class="sidebar-title">
                <i class="fa-solid fa-user-circle"></i>
                Mi Cuenta
            </div>

            <ul class="sidebar-menu">
                <li>
                    <a href="../home.php">
                        <i class="fa-solid fa-home"></i>
                        Volver al Home
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard.php">
                        <i class="fa-solid fa-tachometer-alt"></i>
                        Dashboard
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-datos-personales.php">
                        <i class="fa-solid fa-user"></i>
                        Datos Personales
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-compras.php">
                        <i class="fa-solid fa-shopping-bag"></i>
                        Mis Compras
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-metodos-pago.php" class="active">
                        <i class="fa-solid fa-credit-card"></i>
                        Métodos de Pago
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-seguridad.php">
                        <i class="fa-solid fa-shield-alt"></i>
                        Seguridad
                    </a>
                </li>

                <li>
                    <a href="user-apart-dashboard-configuracion.php">
                        <i class="fa-solid fa-cog"></i>
                        Configuración
                    </a>
                </li>

                <li class="menu-divider"></li>

                <li>
                    <a href="../registros-inicio-sesion/logout-user.php" class="logout-link">
                        <i class="fa-solid fa-sign-out-alt"></i>
                        Cerrar Sesión
                    </a>
                </li>
            </ul>
        </div>

        <!-- CONTENIDO PRINCIPAL -->
        <div class="dashboard-content">
            <h2 class="current-page-title">
                <i class="fa-solid fa-credit-card"></i>
                Métodos de Pago
            </h2>

            <p class="text-muted mb-4">
                Gestiona tus métodos de pago para compras rápidas y seguras.
            </p>

            <!-- SECCIÓN 1: BILLETERA VIRTUAL -->
            <div class="wallet-section">
                <div class="wallet-header">
                    <div class="wallet-title">
                        <i class="fa-solid fa-wallet"></i> Billetera Virtual Hermes
                    </div>
                    <div>
                        <span class="badge-default">DISPONIBLE</span>
                    </div>
                </div>

                <div class="wallet-balance">
                    $<?php echo number_format($saldo_billetera, 0, ',', '.'); ?>
                </div>

                <p style="text-align: center; opacity: 0.9; margin-bottom: 20px;">
                    Paga con tu saldo Hermes de forma rápida y segura
                </p>

                <div class="wallet-actions">
                    <button class="btn-wallet btn-recharge" onclick="showRechargeModal()">
                        <i class="fa-solid fa-money-bill-wave"></i> Recargar Billetera
                    </button>
                    <button class="btn-wallet btn-history" onclick="window.location.href='user-apart-dashboard-wallet-history.php'">
                        <i class="fa-solid fa-history"></i> Historial de Transacciones
                    </button>
                </div>
            </div>

            <!-- SECCIÓN 2: CONTRA ENTREGA -->
            <div class="cod-section">
                <div class="cod-title">
                    <i class="fa-solid fa-truck"></i> Contra Entrega
                </div>
                <p><strong>Método:</strong> Paga al recibir tu pedido</p>
                <p><strong>Descripción:</strong> Realiza tu compra y paga en efectivo cuando recibas los productos en tu domicilio.</p>

                <div class="cod-info">
                    <p><i class="fa-solid fa-info-circle"></i> <strong>Importante:</strong> Este método se selecciona al momento de finalizar tu compra en el checkout.</p>
                </div>
            </div>

            <!-- SECCIÓN 3: MÉTODOS DE PAGO REGISTRADOS -->
            <div class="payment-methods-container">
                <h3>Mis métodos de pago registrados</h3>

                <?php if ($num_metodos_visibles > 0): ?>
                    <?php foreach ($metodos_pago as $metodo):
                        // Saltar billetera virtual que ya se mostró arriba
                        if ($metodo['tipo'] == 'billetera_virtual') continue;
                    ?>
                        <div class="method-card <?php echo $metodo['es_predeterminado'] == 1 ? 'default' : ''; ?>">
                            <div class="method-header">
                                <div class="method-type">
                                    <?php
                                    $icon_class = 'fa-credit-card';
                                    $brand_class = '';

                                    if ($metodo['tipo'] == 'paypal') {
                                        $icon_class = 'fa-paypal';
                                        $brand_class = 'paypal';
                                    } else {
                                        // Determinar marca de tarjeta
                                        $first_digit = substr($metodo['numero_tarjeta'], 0, 1);
                                        if ($first_digit == '4') {
                                            $brand_class = 'visa';
                                        } elseif ($first_digit == '5') {
                                            $brand_class = 'mastercard';
                                        } elseif ($first_digit == '3') {
                                            $icon_class = 'fa-cc-amex';
                                            $brand_class = 'amex';
                                        }
                                    }
                                    ?>
                                    <i class="fa-brands <?php echo $icon_class; ?> method-icon <?php echo $brand_class; ?>"></i>
                                    <div>
                                        <h4 class="mb-1">
                                            <?php
                                            if ($metodo['tipo'] == 'tarjeta_credito') {
                                                echo 'Tarjeta de Crédito';
                                            } elseif ($metodo['tipo'] == 'tarjeta_debito') {
                                                echo 'Tarjeta de Débito';
                                            } elseif ($metodo['tipo'] == 'paypal') {
                                                echo 'PayPal';
                                            } else {
                                                echo ucfirst($metodo['tipo']);
                                            }
                                            ?>
                                        </h4>
                                        <small class="text-muted">
                                            Agregado el <?php echo date('d/m/Y', strtotime($metodo['fecha_creacion'])); ?>
                                        </small>
                                    </div>
                                </div>

                                <?php if ($metodo['es_predeterminado'] == 1): ?>
                                    <span class="badge-default">Predeterminado</span>
                                <?php endif; ?>
                            </div>

                            <div class="method-details">
                                <?php if ($metodo['tipo'] == 'paypal'): ?>
                                    <div class="method-detail">
                                        <span class="method-label">Email de PayPal</span>
                                        <span class="method-value"><?php echo htmlspecialchars($metodo['email_paypal']); ?></span>
                                    </div>
                                <?php else: ?>
                                    <div class="method-detail">
                                        <span class="method-label">Titular</span>
                                        <span class="method-value"><?php echo htmlspecialchars($metodo['nombre_titular']); ?></span>
                                    </div>

                                    <div class="method-detail">
                                        <span class="method-label">Número de tarjeta</span>
                                        <span class="method-value card-number">
                                            **** **** **** <?php echo substr($metodo['numero_tarjeta'], -4); ?>
                                        </span>
                                    </div>

                                    <?php if ($metodo['fecha_vencimiento']): ?>
                                        <div class="method-detail">
                                            <span class="method-label">Válida hasta</span>
                                            <span class="method-value"><?php echo htmlspecialchars($metodo['fecha_vencimiento']); ?></span>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($metodo['marca_tarjeta']): ?>
                                        <div class="method-detail">
                                            <span class="method-label">Marca</span>
                                            <span class="method-value"><?php echo htmlspecialchars($metodo['marca_tarjeta']); ?></span>
                                        </div>
                                    <?php endif; ?>
                                <?php endif; ?>
                            </div>

                            <div class="method-actions">
                                <?php if ($metodo['es_predeterminado'] != 1): ?>
                                    <form action="user-apart-dashboard-metogo-pago-principal.php" method="POST" style="display: inline;">
                                        <input type="hidden" name="metodo_id" value="<?php echo $metodo['id_metodo_pago']; ?>">
                                        <button type="submit" class="btn-method btn-set-default">
                                            <i class="fa-solid fa-star"></i> Establecer como predeterminado
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <form action="user-apart-dashboard-metodo-pago-eliminar.php" method="POST" style="display: inline;"
                                    onsubmit="return confirm('¿Estás seguro de eliminar este método de pago?');">
                                    <input type="hidden" name="metodo_id" value="<?php echo $metodo['id_metodo_pago']; ?>">
                                    <button type="submit" class="btn-method btn-delete">
                                        <i class="fa-solid fa-trash"></i> Eliminar
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <i class="fa-solid fa-credit-card"></i>
                        <h4>No tienes métodos de pago guardados</h4>
                        <p>Agrega un método de pago para realizar compras más rápidas.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Formulario para agregar nuevo método de pago -->
            <div class="add-method-form">
                <h3>Agregar nuevo método de pago</h3>

                <!-- Selector de tipo de pago -->
                <div class="payment-types">
                    <button type="button" class="payment-type-btn active" data-type="tarjeta">
                        <i class="fa-solid fa-credit-card"></i>
                        Tarjeta
                    </button>
                    <button type="button" class="payment-type-btn" data-type="paypal">
                        <i class="fa-brands fa-paypal"></i>
                        PayPal
                    </button>
                </div>

                <!-- Formulario para tarjeta -->
                <form id="form-tarjeta" action="user-apart-dashboard-metodo-pago-agregar.php" method="POST">
                    <input type="hidden" name="tipo" value="tarjeta">

                    <div class="form-row">
                        <div class="form-group">
                            <label for="nombre_titular">Nombre del titular *</label>
                            <input type="text" id="nombre_titular" name="nombre_titular"
                                value="<?php echo htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']); ?>"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="numero_tarjeta">Número de tarjeta *</label>
                            <input type="text" id="numero_tarjeta" name="numero_tarjeta"
                                pattern="[0-9\s]{13,23}" maxlength="23"
                                placeholder="1234 5678 9012 3456" required>
                            <div class="card-images">
                                <img src="https://img.icons8.com/color/48/000000/visa.png" alt="Visa" class="card-image">
                                <img src="https://img.icons8.com/color/48/000000/mastercard.png" alt="MasterCard" class="card-image">
                                <img src="https://img.icons8.com/color/48/000000/american-express.png" alt="Amex" class="card-image">
                            </div>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="fecha_vencimiento">Fecha de vencimiento *</label>
                            <input type="text" id="fecha_vencimiento" name="fecha_vencimiento"
                                placeholder="MM/AA" pattern="(0[1-9]|1[0-2])\/[0-9]{2}"
                                maxlength="5" required>
                        </div>

                        <div class="form-group">
                            <label for="cvv">CVV *</label>
                            <input type="text" id="cvv" name="cvv" pattern="[0-9]{3,4}"
                                maxlength="4" placeholder="123" required>
                        </div>

                        <div class="form-group">
                            <label for="tipo_tarjeta">Tipo de tarjeta *</label>
                            <select id="tipo_tarjeta" name="tipo_tarjeta" required>
                                <option value="">Seleccionar...</option>
                                <option value="tarjeta_credito">Tarjeta de Crédito</option>
                                <option value="tarjeta_debito">Tarjeta de Débito</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="marca_tarjeta">Marca de la tarjeta</label>
                        <select id="marca_tarjeta" name="marca_tarjeta">
                            <option value="">Seleccionar...</option>
                            <option value="Visa">Visa</option>
                            <option value="MasterCard">MasterCard</option>
                            <option value="American Express">American Express</option>
                            <option value="Diners Club">Diners Club</option>
                        </select>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="es_predeterminado" name="es_predeterminado" value="1">
                        <label class="form-check-label" for="es_predeterminado">
                            Establecer como método de pago predeterminado
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-solid fa-save"></i> Guardar Tarjeta
                    </button>
                </form>

                <!-- Formulario para PayPal -->
                <form id="form-paypal" action="user-apart-dashboard-metodo-pago-agregar.php" method="POST" class="paypal-section">
                    <input type="hidden" name="tipo" value="paypal">

                    <div class="form-group">
                        <label for="email_paypal">Email de PayPal *</label>
                        <input type="email" id="email_paypal" name="email_paypal"
                            placeholder="tucuenta@paypal.com" required>
                    </div>

                    <div class="form-check mb-3">
                        <input type="checkbox" class="form-check-input" id="es_predeterminado_paypal" name="es_predeterminado" value="1">
                        <label class="form-check-label" for="es_predeterminado_paypal">
                            Establecer como método de pago predeterminado
                        </label>
                    </div>

                    <button type="submit" class="btn-submit">
                        <i class="fa-brands fa-paypal"></i> Guardar PayPal
                    </button>
                </form>

                <div class="security-note">
                    <i class="fa-solid fa-shield-alt"></i>
                    <strong>Seguridad:</strong> Los datos de tu tarjeta se encriptan y almacenan de forma segura.
                    Nunca almacenamos tu CVV completo.
                </div>
            </div>
        </div>
    </div>

    <!-- MODAL PARA RECARGAR BILLETERA -->
    <div id="rechargeModal" class="modal-overlay">
        <div class="modal-content">
            <h3 style="margin-bottom: 20px; color: #333;">
                <i class="fa-solid fa-money-bill-wave"></i> Recargar Billetera
            </h3>

            <form id="rechargeForm" onsubmit="return processRecharge(event)">
                <div style="margin-bottom: 20px;">
                    <label for="monto" style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Monto a recargar *
                    </label>
                    <div style="position: relative;">
                        <span style="position: absolute; left: 10px; top: 50%; transform: translateY(-50%); font-weight: bold; color: #667eea;">$</span>
                        <input type="number" id="monto" name="monto"
                            min="1000" max="10000000" step="1000"
                            style="width: 100%; padding: 12px 12px 12px 30px; border: 2px solid #ced4da; border-radius: 8px;"
                            placeholder="Ej: 50000" required>
                    </div>
                    <small style="color: #666; display: block; margin-top: 5px;">Mínimo: $1.000 | Máximo: $10.000.000</small>
                </div>

                <div style="margin-bottom: 20px;">
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: #333;">
                        Monto rápido
                    </label>
                    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
                        <button type="button" class="btn-amount" data-amount="10000" onclick="setAmount(10000)">$10.000</button>
                        <button type="button" class="btn-amount" data-amount="50000" onclick="setAmount(50000)">$50.000</button>
                        <button type="button" class="btn-amount" data-amount="100000" onclick="setAmount(100000)">$100.000</button>
                        <button type="button" class="btn-amount" data-amount="200000" onclick="setAmount(200000)">$200.000</button>
                    </div>
                </div>

                <div style="display: flex; gap: 10px; margin-top: 30px;">
                    <button type="button" onclick="closeRechargeModal()"
                        style="flex: 1; padding: 12px; background: #6c757d; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        Cancelar
                    </button>
                    <button type="submit" id="rechargeBtn"
                        style="flex: 1; padding: 12px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">
                        <i class="fa-solid fa-check"></i> Recargar
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Variables globales
        let currentSaldo = <?php echo $saldo_billetera; ?>;

        // Funciones del modal
        function showRechargeModal() {
            document.getElementById('rechargeModal').style.display = 'flex';
            document.getElementById('monto').focus();
        }

        function closeRechargeModal() {
            document.getElementById('rechargeModal').style.display = 'none';
            document.getElementById('monto').value = '';
        }

        function setAmount(amount) {
            document.getElementById('monto').value = amount;
        }

        // Procesar recarga
        // REEMPLAZA todo el código de processRecharge con esta versión SIMPLIFICADA:
        function processRecharge(event) {
            event.preventDefault();
            console.log('Iniciando proceso de recarga...');

            const montoInput = document.getElementById('monto');
            const monto = parseFloat(montoInput.value);
            const rechargeBtn = document.getElementById('rechargeBtn');

            console.log('Monto ingresado:', monto);

            // Validaciones básicas
            if (isNaN(monto) || monto <= 0) {
                alert('Por favor ingresa un monto válido');
                montoInput.focus();
                return false;
            }

            if (monto < 1000) {
                alert('Monto mínimo: $1.000');
                montoInput.focus();
                return false;
            }

            if (monto > 10000000) {
                alert('Monto máximo: $10.000.000');
                montoInput.focus();
                return false;
            }

            if (!confirm(`¿Confirmas recargar $${monto.toLocaleString('es-CO')} a tu billetera?`)) {
                return false;
            }

            // Deshabilitar botón y mostrar carga
            rechargeBtn.disabled = true;
            const originalText = rechargeBtn.innerHTML;
            rechargeBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Procesando...';

            // Crear FormData
            const formData = new FormData();
            formData.append('monto', monto);
            console.log('FormData creado');

            // INTENTAR DIFERENTES RUTAS - prueba una por una
            const possiblePaths = [
                './CONTROLLERS/recharge-wallet.php',
                '../CONTROLLERS/recharge-wallet.php',
                '/CONTROLLERS/recharge-wallet.php',
                'CONTROLLERS/recharge-wallet.php',
                '../../CONTROLLERS/recharge-wallet.php'
            ];

            let currentPathIndex = 0;

            function tryNextPath() {
                if (currentPathIndex >= possiblePaths.length) {
                    alert('No se pudo encontrar el archivo de recarga. Contacta al administrador.');
                    rechargeBtn.disabled = false;
                    rechargeBtn.innerHTML = originalText;
                    return;
                }

                const path = possiblePaths[currentPathIndex];
                console.log('Intentando ruta:', path);

                fetch(path, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => {
                        console.log('Respuesta recibida, status:', response.status);
                        console.log('URL solicitada:', path);

                        if (!response.ok) {
                            throw new Error(`HTTP ${response.status}`);
                        }
                        return response.text(); // Primero obtener como texto
                    })
                    .then(text => {
                        console.log('Respuesta completa:', text);

                        try {
                            const data = JSON.parse(text);
                            console.log('JSON parseado:', data);

                            if (data.success) {
                                // Éxito
                                alert(`¡Recarga exitosa!\nNuevo saldo: $${data.saldo_nuevo.toLocaleString('es-CO')}`);

                                // Actualizar saldo en pantalla
                                const balanceElement = document.querySelector('.wallet-balance');
                                if (balanceElement) {
                                    balanceElement.textContent = '$' + data.saldo_nuevo.toLocaleString('es-CO');
                                }

                                closeRechargeModal();

                            } else {
                                // Error del servidor
                                alert('Error: ' + data.message);
                                rechargeBtn.disabled = false;
                                rechargeBtn.innerHTML = originalText;
                            }

                        } catch (e) {
                            console.error('Error parseando JSON:', e);
                            console.log('Respuesta no es JSON:', text);

                            if (currentPathIndex < possiblePaths.length - 1) {
                                // Intentar siguiente ruta
                                currentPathIndex++;
                                tryNextPath();
                            } else {
                                alert('Error inesperado en el servidor');
                                rechargeBtn.disabled = false;
                                rechargeBtn.innerHTML = originalText;
                            }
                        }
                    })
                    .catch(error => {
                        console.error('Error en fetch:', error);

                        if (currentPathIndex < possiblePaths.length - 1) {
                            // Intentar siguiente ruta
                            currentPathIndex++;
                            console.log('Intentando siguiente ruta...');
                            tryNextPath();
                        } else {
                            alert('Error de conexión: ' + error.message);
                            console.log('Todas las rutas fallaron');
                            rechargeBtn.disabled = false;
                            rechargeBtn.innerHTML = originalText;
                        }
                    });
            }

            // Iniciar con la primera ruta
            tryNextPath();

            return false;
        }

        // Cerrar modal al hacer clic fuera
        document.getElementById('rechargeModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeRechargeModal();
            }
        });

        // Cerrar modal con ESC
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape' && document.getElementById('rechargeModal').style.display === 'flex') {
                closeRechargeModal();
            }
        });

        // Cambiar entre tarjeta y PayPal
        document.querySelectorAll('.payment-type-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Remover active de todos
                document.querySelectorAll('.payment-type-btn').forEach(b => b.classList.remove('active'));
                // Agregar active al clickeado
                this.classList.add('active');

                const type = this.getAttribute('data-type');

                // Mostrar formulario correspondiente
                if (type === 'tarjeta') {
                    document.getElementById('form-tarjeta').style.display = 'block';
                    document.getElementById('form-paypal').style.display = 'none';
                } else {
                    document.getElementById('form-tarjeta').style.display = 'none';
                    document.getElementById('form-paypal').style.display = 'block';
                }
            });
        });

        // Formatear número de tarjeta
        document.getElementById('numero_tarjeta').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            value = value.replace(/(\d{4})/g, '$1 ').trim();
            e.target.value = value.substring(0, 19);

            // Detectar marca de tarjeta
            const firstDigit = value.charAt(0);
            const cardImages = document.querySelectorAll('.card-image');

            cardImages.forEach(img => img.classList.remove('active'));

            if (firstDigit === '4') {
                cardImages[0].classList.add('active'); // Visa
            } else if (firstDigit === '5') {
                cardImages[1].classList.add('active'); // MasterCard
            } else if (firstDigit === '3') {
                cardImages[2].classList.add('active'); // Amex
            }
        });

        // Formatear fecha de vencimiento
        document.getElementById('fecha_vencimiento').addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length >= 2) {
                value = value.substring(0, 2) + '/' + value.substring(2, 4);
            }
            e.target.value = value.substring(0, 5);
        });

        // Validar formulario tarjeta
        document.getElementById('form-tarjeta').addEventListener('submit', function(e) {
            const cardNumber = document.getElementById('numero_tarjeta').value.replace(/\s/g, '');
            const expiry = document.getElementById('fecha_vencimiento').value;
            const cvv = document.getElementById('cvv').value;

            if (cardNumber.length < 13 || cardNumber.length > 19) {
                e.preventDefault();
                alert('El número de tarjeta debe tener entre 13 y 19 dígitos');
                return false;
            }

            if (!/^\d{2}\/\d{2}$/.test(expiry)) {
                e.preventDefault();
                alert('Formato de fecha inválido. Use MM/AA');
                return false;
            }

            if (!/^\d{3,4}$/.test(cvv)) {
                e.preventDefault();
                alert('CVV debe tener 3 o 4 dígitos');
                return false;
            }

            return true;
        });

        // Mostrar mensajes de éxito/error
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('success')) {
            alert(urlParams.get('success'));
            window.history.replaceState({}, document.title, window.location.pathname);
        }
        if (urlParams.has('error')) {
            alert(urlParams.get('error'));
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
    <script src="../scripts/user-apart-dashboard.js"></script>
    <?php include '../TEMPLATES/footer.php' ?>
</body>

</html>