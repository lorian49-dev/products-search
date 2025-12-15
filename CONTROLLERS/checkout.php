<?php
// CONTROLLERS/checkout.php
session_start();
require_once "../shortCuts/connect.php";
require_once "cart-functions.php";

// Verificar que el usuario esté logueado y tenga carrito
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../registros-inicio-sesion/login.php?redirect=checkout");
    exit;
}

if (empty($_SESSION['cart'])) {
    header("Location: cart.php");
    exit;
}

$id_usuario = $_SESSION['usuario_id'];

// Obtener información del usuario
$sql_usuario = "SELECT * FROM usuario WHERE id_usuario = ?";
$stmt = $connect->prepare($sql_usuario);
$stmt->bind_param("i", $id_usuario);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Verificar si ya tiene dirección guardada
$sql_direccion = "SELECT * FROM direcciones WHERE id_usuario = ? AND es_principal = 1 LIMIT 1";
$stmt_dir = $connect->prepare($sql_direccion);
$stmt_dir->bind_param("i", $id_usuario);
$stmt_dir->execute();
$direccion = $stmt_dir->get_result()->fetch_assoc();

// Obtener saldo de billetera virtual
$sql_billetera = "SELECT saldo_billetera FROM metodos_pago WHERE id_usuario = ? AND tipo = 'billetera_virtual'";
$stmt_billetera = $connect->prepare($sql_billetera);
$stmt_billetera->bind_param("i", $id_usuario);
$stmt_billetera->execute();
$result_billetera = $stmt_billetera->get_result();
$billetera = $result_billetera->fetch_assoc();
$saldo_billetera = $billetera ? $billetera['saldo_billetera'] : 0.00;

// Calcular totales
$subtotal = getCartTotal();
$envio = 10000; // Costo fijo de envío
$iva = $subtotal * 0.19; // 19% IVA en Colombia
$total = $subtotal + $envio + $iva;
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        /* ESTILOS GENERALES */
        :root {
            --primary: #8B4513;
            --secondary: #28a745;
            --danger: #e74c3c;
            --warning: #f39c12;
            --dark: #2c3e50;
            --light: #f8f9fa;
            --gray: #6c757d;
            --wallet: #667eea;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            color: #333;
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 30px;
        }
        
        /* HEADER CHECKOUT */
        .checkout-header {
            text-align: center;
            margin-bottom: 40px;
            padding: 30px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }
        
        .checkout-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary), var(--secondary));
        }
        
        .checkout-title {
            font-size: 2.5rem;
            color: var(--dark);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 15px;
        }
        
        .checkout-steps {
            display: flex;
            justify-content: center;
            gap: 50px;
            margin-top: 30px;
            position: relative;
        }
        
        .checkout-steps::before {
            content: '';
            position: absolute;
            top: 25px;
            left: 15%;
            right: 15%;
            height: 3px;
            background: #e0e0e0;
            z-index: 1;
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 2;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: white;
            border: 3px solid #e0e0e0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--gray);
            transition: all 0.3s;
        }
        
        .step.active .step-number {
            background: var(--primary);
            border-color: var(--primary);
            color: white;
            transform: scale(1.1);
            box-shadow: 0 5px 15px rgba(139, 69, 19, 0.3);
        }
        
        .step-label {
            font-weight: 600;
            color: var(--gray);
            font-size: 0.9rem;
            text-align: center;
        }
        
        .step.active .step-label {
            color: var(--primary);
        }
        
        /* CONTENIDO CHECKOUT */
        .checkout-content {
            display: flex;
            gap: 30px;
            margin-bottom: 50px;
        }
        
        .checkout-form-section {
            flex: 2;
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        }
        
        .order-summary-section {
            flex: 1;
            background: white;
            padding: 30px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.08);
            height: fit-content;
            position: sticky;
            top: 30px;
        }
        
        /* FORMULARIOS */
        .form-section {
            margin-bottom: 40px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f0f0f0;
        }
        
        .form-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .section-title {
            font-size: 1.5rem;
            color: var(--dark);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }
        
        .section-title i {
            color: var(--primary);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 25px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--dark);
            font-size: 1rem;
        }
        
        .form-label .required {
            color: var(--danger);
        }
        
        .form-control {
            width: 100%;
            padding: 14px 18px;
            border: 2px solid #e0e0e0;
            border-radius: 12px;
            font-size: 1rem;
            transition: all 0.3s;
            background: #fafafa;
        }
        
        .form-control:focus {
            border-color: var(--primary);
            background: white;
            outline: none;
            box-shadow: 0 0 0 3px rgba(139, 69, 19, 0.1);
        }
        
        textarea.form-control {
            min-height: 100px;
            resize: vertical;
        }
        
        select.form-control {
            appearance: none;
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%238B4513' viewBox='0 0 16 16'%3E%3Cpath d='M7.247 11.14 2.451 5.658C1.885 5.013 2.345 4 3.204 4h9.592a1 1 0 0 1 .753 1.659l-4.796 5.48a1 1 0 0 1-1.506 0z'/%3E%3C/svg%3E");
            background-repeat: no-repeat;
            background-position: right 18px center;
            background-size: 16px;
            padding-right: 45px;
        }
        
        /* MÉTODOS DE PAGO */
        .payment-methods {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        
        .payment-method {
            border: 2px solid #e0e0e0;
            padding: 25px;
            border-radius: 15px;
            cursor: pointer;
            transition: all 0.3s;
            text-align: center;
            background: #fafafa;
            position: relative;
            overflow: hidden;
        }
        
        .payment-method:hover {
            border-color: var(--primary);
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0,0,0,0.1);
        }
        
        .payment-method.selected {
            border-color: var(--primary);
            background: linear-gradient(135deg, rgba(139, 69, 19, 0.05), rgba(40, 167, 69, 0.05));
            box-shadow: 0 10px 25px rgba(139, 69, 19, 0.15);
        }
        
        .payment-method.selected::before {
            content: '✓';
            position: absolute;
            top: 10px;
            right: 10px;
            width: 25px;
            height: 25px;
            background: var(--primary);
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 0.9rem;
        }
        
        .payment-icon {
            font-size: 48px;
            margin-bottom: 15px;
            height: 60px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .payment-name {
            font-size: 1.2rem;
            font-weight: bold;
            color: var(--dark);
            margin-bottom: 8px;
        }
        
        .payment-desc {
            font-size: 0.9rem;
            color: var(--gray);
            line-height: 1.4;
        }
        
        /* Saldo de billetera */
        .wallet-balance {
            font-size: 0.85rem;
            margin-top: 8px;
            padding: 6px 10px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 6px;
            color: var(--wallet);
            font-weight: 600;
        }
        
        .wallet-balance.insufficient {
            background: rgba(220, 53, 69, 0.1);
            color: var(--danger);
        }
        
        /* DETALLES DE PAGO (dinámicos) */
        .payment-details {
            margin-top: 25px;
            padding: 25px;
            background: #f8f9fa;
            border-radius: 12px;
            border-left: 4px solid var(--primary);
            display: none;
            animation: fadeIn 0.5s ease;
        }
        
        .payment-details.show {
            display: block;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        /* RESUMEN DEL PEDIDO */
        .summary-title {
            font-size: 1.8rem;
            color: var(--dark);
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f0f0f0;
            display: flex;
            align-items: center;
            gap: 12px;
        }
        
        .cart-review {
            max-height: 400px;
            overflow-y: auto;
            padding-right: 10px;
            margin-bottom: 25px;
        }
        
        .cart-review::-webkit-scrollbar {
            width: 6px;
        }
        
        .cart-review::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 3px;
        }
        
        .cart-review::-webkit-scrollbar-thumb {
            background: var(--primary);
            border-radius: 3px;
        }
        
        .cart-item-summary {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px;
            margin-bottom: 15px;
            background: #f8f9fa;
            border-radius: 10px;
            border-left: 3px solid var(--primary);
        }
        
        .item-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e0e0e0;
        }
        
        .item-info {
            flex: 1;
        }
        
        .item-name {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
            font-size: 1rem;
        }
        
        .item-details {
            font-size: 0.9rem;
            color: var(--gray);
        }
        
        .item-price {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.1rem;
            min-width: 100px;
            text-align: right;
        }
        
        .summary-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
            padding-bottom: 15px;
            border-bottom: 1px solid #eee;
        }
        
        .summary-total {
            font-size: 1.5rem;
            font-weight: bold;
            color: var(--primary);
            margin-top: 20px;
            padding-top: 20px;
            border-top: 2px solid var(--primary);
        }
        
        /* BOTONES */
        .checkout-actions {
            display: flex;
            gap: 20px;
            margin-top: 40px;
            padding-top: 30px;
            border-top: 2px solid #f0f0f0;
        }
        
        .btn {
            padding: 18px 35px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s;
            border: none;
            text-decoration: none;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #a0522d);
            color: white;
            flex: 1;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #72370f, var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 69, 19, 0.3);
        }
        
        .btn-secondary {
            background: #f8f9fa;
            color: var(--dark);
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            border-color: var(--gray);
        }
        
        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none !important;
            box-shadow: none !important;
        }
        
        /* INFORMACIÓN DE SEGURIDAD */
        .security-info {
            margin-top: 30px;
            padding: 25px;
            background: linear-gradient(135deg, #e3f2fd, #f3e5f5);
            border-radius: 15px;
            border-left: 5px solid var(--secondary);
        }
        
        .security-title {
            font-size: 1.3rem;
            color: var(--dark);
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .security-features {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
        }
        
        .security-feature {
            display: flex;
            align-items: center;
            gap: 10px;
            color: var(--gray);
            font-size: 0.95rem;
        }
        
        .security-feature i {
            color: var(--secondary);
            font-size: 1.2rem;
        }
        
        /* ALERTAS */
        .alert {
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 15px;
            animation: slideIn 0.5s ease;
        }
        
        .alert-danger {
            background: linear-gradient(135deg, #fee, #fdd);
            color: #721c24;
            border: 2px solid #f5c6cb;
        }
        
        .alert-warning {
            background: linear-gradient(135deg, #fff3cd, #ffeaa7);
            color: #856404;
            border: 2px solid #ffeaa7;
        }
        
        /* RESPONSIVE */
        @media (max-width: 992px) {
            .checkout-content {
                flex-direction: column;
            }
            
            .order-summary-section {
                position: static;
            }
            
            .checkout-steps {
                gap: 30px;
            }
            
            .checkout-steps::before {
                left: 10%;
                right: 10%;
            }
        }
        
        @media (max-width: 768px) {
            .container {
                padding: 20px;
            }
            
            .checkout-header {
                padding: 20px;
            }
            
            .checkout-title {
                font-size: 2rem;
            }
            
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .payment-methods {
                grid-template-columns: 1fr;
            }
            
            .checkout-steps {
                flex-wrap: wrap;
                gap: 20px;
            }
            
            .checkout-steps::before {
                display: none;
            }
            
            .checkout-actions {
                flex-direction: column;
            }
            
            .btn {
                width: 100%;
            }
        }
        
        @media (max-width: 480px) {
            .checkout-title {
                font-size: 1.6rem;
                flex-direction: column;
                gap: 10px;
            }
            
            .checkout-form-section,
            .order-summary-section {
                padding: 25px;
            }
            
            .section-title {
                font-size: 1.3rem;
            }
        }
    </style>
</head>
<body>
    
    
    <div class="container">
        <div class="checkout-header">
            <h1 class="checkout-title">
                <i class="fas fa-shopping-bag"></i>
                Finalizar Compra
            </h1>
            <p>Completa tus datos para completar tu pedido</p>
            
            <div class="checkout-steps">
                <div class="step active">
                    <div class="step-number">1</div>
                    <span class="step-label">Carrito</span>
                </div>
                <div class="step active">
                    <div class="step-number">2</div>
                    <span class="step-label">Información</span>
                </div>
                <div class="step active">
                    <div class="step-number">3</div>
                    <span class="step-label">Pago</span>
                </div>
                <div class="step">
                    <div class="step-number">4</div>
                    <span class="step-label">Confirmación</span>
                </div>
            </div>
        </div>
        
        <?php if (isset($_SESSION['checkout_error'])): ?>
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-triangle"></i>
                <div>
                    <strong>Error:</strong> <?php echo htmlspecialchars($_SESSION['checkout_error']); ?>
                </div>
                <?php unset($_SESSION['checkout_error']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($_SESSION['cart'])): ?>
        <form method="POST" action="process-order.php" id="checkoutForm" class="checkout-content">
            <div class="checkout-form-section">
                <!-- Información de Envío -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        Información Personal
                    </h2>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                Nombre <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="nombre" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['nombre'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Apellido <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="apellido" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['apellido'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                Email <span class="required">*</span>
                            </label>
                            <input type="email" 
                                   name="email" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['email'] ?? ''); ?>" 
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Teléfono <span class="required">*</span>
                            </label>
                            <input type="tel" 
                                   name="telefono" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($usuario['telefono'] ?? $direccion['telefono'] ?? ''); ?>" 
                                   required>
                        </div>
                    </div>
                </div>
                
                <!-- Dirección de Envío -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-truck"></i>
                        Dirección de Envío
                    </h2>
                    
                    <div class="form-group">
                        <label class="form-label">
                            Dirección Completa <span class="required">*</span>
                        </label>
                        <textarea name="direccion" 
                                  class="form-control" 
                                  rows="3" 
                                  placeholder="Calle, Número, Barrio, Apartamento, etc."
                                  required><?php echo htmlspecialchars($direccion['direccion'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                Ciudad <span class="required">*</span>
                            </label>
                            <input type="text" 
                                   name="ciudad" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($direccion['ciudad'] ?? ''); ?>" 
                                   placeholder="Ej: Medellín"
                                   required>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">
                                Departamento <span class="required">*</span>
                            </label>
                            <select name="departamento" class="form-control" required>
                                <option value="">Seleccionar departamento</option>
                                <option value="Antioquia" <?php echo (($direccion['departamento'] ?? '') == 'Antioquia') ? 'selected' : ''; ?>>Antioquia</option>
                                <option value="Bogotá D.C." <?php echo (($direccion['departamento'] ?? '') == 'Bogotá D.C.') ? 'selected' : ''; ?>>Bogotá D.C.</option>
                                <option value="Valle del Cauca" <?php echo (($direccion['departamento'] ?? '') == 'Valle del Cauca') ? 'selected' : ''; ?>>Valle del Cauca</option>
                                <option value="Santander" <?php echo (($direccion['departamento'] ?? '') == 'Santander') ? 'selected' : ''; ?>>Santander</option>
                                <option value="Atlántico" <?php echo (($direccion['departamento'] ?? '') == 'Atlántico') ? 'selected' : ''; ?>>Atlántico</option>
                                <!-- Agrega más departamentos según necesites -->
                            </select>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Código Postal</label>
                            <input type="text" 
                                   name="codigo_postal" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($direccion['codigo_postal'] ?? ''); ?>" 
                                   placeholder="Ej: 050001">
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Referencias adicionales</label>
                            <input type="text" 
                                   name="referencia" 
                                   class="form-control" 
                                   value="<?php echo htmlspecialchars($direccion['referencia'] ?? ''); ?>" 
                                   placeholder="Ej: Casa blanca portón negro">
                        </div>
                    </div>
                </div>
                
                <!-- Método de Pago -->
                <div class="form-section">
                    <h2 class="section-title">
                        <i class="fas fa-credit-card"></i>
                        Método de Pago
                    </h2>
                    
                    <div class="payment-methods" id="paymentMethods">
                        <!-- Tarjeta de Crédito -->
                        <div class="payment-method" onclick="selectPaymentMethod('tarjeta_credito', this)">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card" style="color: #3498db;"></i>
                            </div>
                            <div class="payment-name">Tarjeta de Crédito</div>
                            <div class="payment-desc">Visa, Mastercard, Amex</div>
                            <input type="radio" name="metodo_pago" value="tarjeta_credito" hidden required>
                        </div>
                        
                        <!-- Tarjeta de Débito -->
                        <div class="payment-method" onclick="selectPaymentMethod('tarjeta_debito', this)">
                            <div class="payment-icon">
                                <i class="fas fa-credit-card" style="color: #2ecc71;"></i>
                            </div>
                            <div class="payment-name">Tarjeta de Débito</div>
                            <div class="payment-desc">Débito directo</div>
                            <input type="radio" name="metodo_pago" value="tarjeta_debito" hidden>
                        </div>
                        
                        <!-- PSE -->
                        <div class="payment-method" onclick="selectPaymentMethod('pse', this)">
                            <div class="payment-icon">
                                <i class="fas fa-university" style="color: #9b59b6;"></i>
                            </div>
                            <div class="payment-name">PSE</div>
                            <div class="payment-desc">Pagos en línea seguros</div>
                            <input type="radio" name="metodo_pago" value="pse" hidden>
                        </div>
                        
                        <!-- Billetera Virtual (NUEVO) -->
                        <div class="payment-method" onclick="selectPaymentMethod('billetera_virtual', this)" id="billeteraVirtualBtn">
                            <div class="payment-icon">
                                <i class="fas fa-wallet" style="color: #667eea;"></i>
                            </div>
                            <div class="payment-name">Billetera Virtual</div>
                            <div class="payment-desc">Paga con tu saldo Hermes</div>
                            <div id="walletBalance" class="wallet-balance">
                                Cargando saldo...
                            </div>
                            <input type="radio" name="metodo_pago" value="billetera_virtual" hidden>
                        </div>
                        
                        <!-- Contra Entrega (NUEVO) -->
                        <div class="payment-method" onclick="selectPaymentMethod('contra_entrega', this)">
                            <div class="payment-icon">
                                <i class="fas fa-money-bill-wave" style="color: #e74c3c;"></i>
                            </div>
                            <div class="payment-name">Contra Entrega</div>
                            <div class="payment-desc">Paga al recibir tu pedido</div>
                            <input type="radio" name="metodo_pago" value="contra_entrega" hidden>
                        </div>
                    </div>
                    
                    <!-- Detalles de Pago (se muestran dinámicamente) -->
                    <div id="paymentDetails" class="payment-details">
                        <!-- Se llena con JavaScript según el método seleccionado -->
                    </div>
                </div>
                
                <!-- Términos y Condiciones -->
                <div class="form-section">
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                            <input type="checkbox" 
                                   id="terminos" 
                                   name="terminos" 
                                   required 
                                   style="margin-top: 3px; transform: scale(1.2);">
                            <span style="color: #555; line-height: 1.5;">
                                Acepto los <a href="../terminos-condiciones.php" target="_blank" style="color: var(--primary); font-weight: bold;">Términos y Condiciones</a> 
                                y la <a href="../politica-privacidad.php" target="_blank" style="color: var(--primary); font-weight: bold;">Política de Privacidad</a>. 
                                <span class="required">*</span>
                            </span>
                        </label>
                    </div>
                    
                    <div class="form-group">
                        <label style="display: flex; align-items: flex-start; gap: 12px; cursor: pointer; padding: 15px; background: #f8f9fa; border-radius: 10px;">
                            <input type="checkbox" 
                                   id="newsletter" 
                                   name="newsletter" 
                                   style="margin-top: 3px; transform: scale(1.2);">
                            <span style="color: #555; line-height: 1.5;">
                                Deseo recibir ofertas especiales, novedades y consejos útiles por correo electrónico.
                            </span>
                        </label>
                    </div>
                </div>
                
                <!-- Botones de acción -->
                <div class="checkout-actions">
                    <a href="cart.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Volver al carrito
                    </a>
                    
                    <button type="submit" class="btn btn-primary" id="submitBtn">
                        <i class="fas fa-lock"></i> Confirmar y Pagar
                        <span id="totalAmount">$<?php echo number_format($total, 0, ',', '.'); ?></span>
                    </button>
                </div>
                
                <!-- Información de seguridad -->
                <div class="security-info">
                    <h3 class="security-title">
                        <i class="fas fa-shield-alt"></i>
                        Compra 100% segura
                    </h3>
                    <div class="security-features">
                        <div class="security-feature">
                            <i class="fas fa-lock"></i>
                            <span>Datos protegidos con encriptación SSL</span>
                        </div>
                        <div class="security-feature">
                            <i class="fas fa-credit-card"></i>
                            <span>No almacenamos datos de tarjetas</span>
                        </div>
                        <div class="security-feature">
                            <i class="fas fa-check-circle"></i>
                            <span>Garantía de devolución en 30 días</span>
                        </div>
                        <div class="security-feature">
                            <i class="fas fa-headset"></i>
                            <span>Soporte 24/7 para cualquier duda</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Resumen del Pedido -->
            <div class="order-summary-section">
                <h2 class="summary-title">
                    <i class="fas fa-receipt"></i>
                    Resumen del Pedido
                </h2>
                
                <div class="cart-review">
                    <?php 
                    $productos_por_vendedor = [];
                    foreach ($_SESSION['cart'] as $product_id => $item):
                        // Obtener info del producto
                        $sql_producto = "SELECT p.*, v.nombre_empresa 
                                        FROM producto p 
                                        LEFT JOIN vendedor v ON p.id_vendedor = v.id_vendedor 
                                        WHERE p.id_producto = ?";
                        $stmt_producto = $connect->prepare($sql_producto);
                        $stmt_producto->bind_param("i", $product_id);
                        $stmt_producto->execute();
                        $producto_info = $stmt_producto->get_result()->fetch_assoc();
                        
                        if ($producto_info):
                            $vendedor_id = $producto_info['id_vendedor'];
                            if (!isset($productos_por_vendedor[$vendedor_id])) {
                                $productos_por_vendedor[$vendedor_id] = [
                                    'vendedor' => $producto_info['nombre_empresa'],
                                    'productos' => []
                                ];
                            }
                            $productos_por_vendedor[$vendedor_id]['productos'][] = [
                                'item' => $item,
                                'info' => $producto_info
                            ];
                        endif;
                    endforeach;
                    
                    foreach ($productos_por_vendedor as $vendedor_id => $data):
                        if (count($productos_por_vendedor) > 1): ?>
                            <div style="margin: 15px 0 10px; padding: 10px 15px; background: #e9ecef; border-radius: 8px; font-weight: bold; color: var(--dark);">
                                <i class="fas fa-store"></i> <?php echo htmlspecialchars($data['vendedor']); ?>
                            </div>
                        <?php endif;
                        
                        foreach ($data['productos'] as $producto_data): 
                            $item = $producto_data['item'];
                            $info = $producto_data['info'];
                            
                            $imagen_url = !empty($info['imagen_url']) ? $info['imagen_url'] : 
                                         (!empty($info['imagen']) ? '../SOURCES/PRODUCTOS/' . $info['imagen'] : '');
                    ?>
                            <div class="cart-item-summary">
                                <?php if ($imagen_url): ?>
                                    <img src="<?php echo htmlspecialchars($imagen_url); ?>" 
                                         alt="<?php echo htmlspecialchars($item['name']); ?>" 
                                         class="item-image">
                                <?php else: ?>
                                    <div class="item-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; border: none;">
                                        <i class="fas fa-image" style="color: #ccc; font-size: 24px;"></i>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="item-info">
                                    <div class="item-name"><?php echo htmlspecialchars($item['name']); ?></div>
                                    <div class="item-details">
                                        $<?php echo number_format($item['price'], 0, ',', '.'); ?> × 
                                        <?php echo $item['quantity']; ?> unidades
                                    </div>
                                </div>
                                
                                <div class="item-price">
                                    $<?php echo number_format($item['subtotal'], 0, ',', '.'); ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endforeach; ?>
                </div>
                
                <!-- Totales -->
                <div class="summary-row">
                    <span>Subtotal</span>
                    <span>$<?php echo number_format($subtotal, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>Envío</span>
                    <span>$<?php echo number_format($envio, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row">
                    <span>IVA (19%)</span>
                    <span>$<?php echo number_format($iva, 0, ',', '.'); ?></span>
                </div>
                
                <div class="summary-row summary-total">
                    <span>Total a pagar</span>
                    <span>$<?php echo number_format($total, 0, ',', '.'); ?></span>
                </div>
                
                <!-- Información adicional -->
                <div style="margin-top: 25px; padding: 20px; background: #f8f9fa; border-radius: 10px; border-left: 4px solid var(--secondary);">
                    <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 10px; color: var(--dark);">
                        <i class="fas fa-truck" style="color: var(--secondary);"></i>
                        <span style="font-weight: bold;">Envío rápido</span>
                    </div>
                    <p style="color: var(--gray); font-size: 0.95rem; margin-bottom: 15px;">
                        Entrega en 3-5 días hábiles a toda Colombia.
                    </p>
                    
                    <div style="display: flex; align-items: center; gap: 10px; color: var(--dark);">
                        <i class="fas fa-undo" style="color: var(--secondary);"></i>
                        <span style="font-weight: bold;">Devolución fácil</span>
                    </div>
                    <p style="color: var(--gray); font-size: 0.95rem;">
                        Tienes 30 días para devolver tu compra si no estás satisfecho.
                    </p>
                </div>
            </div>
        </form>
        <?php else: ?>
            <div style="text-align: center; padding: 50px; background: white; border-radius: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.08);">
                <div style="font-size: 100px; color: #e0e0e0; margin-bottom: 20px;">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2 style="color: var(--dark); margin-bottom: 15px;">Tu carrito está vacío</h2>
                <p style="color: var(--gray); margin-bottom: 30px;">Agrega productos para continuar con la compra.</p>
                <a href="../home.php" class="btn btn-primary" style="display: inline-flex;">
                    <i class="fas fa-home"></i> Volver al inicio
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script>
    // Variables globales
    let selectedPaymentMethod = null;
    const totalAmount = <?php echo $total; ?>;
    const paymentDetails = {
        'tarjeta_credito': `
            <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fas fa-credit-card"></i> Datos de la tarjeta
            </h3>
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Número de tarjeta <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="card_number" 
                           placeholder="1234 5678 9012 3456" 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;"
                           pattern="[0-9\s]{13,19}"
                           maxlength="19">
                </div>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                            Fecha expiración <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="card_expiry" 
                               placeholder="MM/AA" 
                               style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;"
                               pattern="(0[1-9]|1[0-2])\/[0-9]{2}">
                    </div>
                    
                    <div>
                        <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                            CVV <span class="required">*</span>
                        </label>
                        <input type="text" 
                               id="card_cvv" 
                               placeholder="123" 
                               style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;"
                               pattern="[0-9]{3,4}"
                               maxlength="4">
                    </div>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Nombre en la tarjeta <span class="required">*</span>
                    </label>
                    <input type="text" 
                           id="card_name" 
                           placeholder="JUAN PEREZ" 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <i class="fas fa-lock" style="color: var(--secondary);"></i>
                    <span style="color: var(--gray); font-size: 0.9rem;">
                        Tus datos de tarjeta están protegidos y no se almacenan en nuestros servidores.
                    </span>
                </div>
            </div>
        `,
        
        'tarjeta_debito': `
            <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fas fa-credit-card"></i> Datos de la tarjeta de débito
            </h3>
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Número de tarjeta <span class="required">*</span>
                    </label>
                    <input type="text" 
                           placeholder="1234 5678 9012 3456" 
                           style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Banco emisor
                    </label>
                    <select style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <option value="">Seleccionar banco</option>
                        <option>Bancolombia</option>
                        <option>Davivienda</option>
                        <option>Banco de Bogotá</option>
                        <option>BBVA</option>
                        <option>Banco Popular</option>
                    </select>
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <i class="fas fa-shield-alt" style="color: var(--secondary);"></i>
                    <span style="color: var(--gray); font-size: 0.9rem;">
                        Transacción segura mediante certificación PCI-DSS.
                    </span>
                </div>
            </div>
        `,
        
        'pse': `
            <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fas fa-university"></i> Pago por PSE
            </h3>
            <div style="display: grid; gap: 20px;">
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Banco <span class="required">*</span>
                    </label>
                    <select id="pse_bank" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;" required>
                        <option value="">Seleccionar banco</option>
                        <option value="Bancolombia">Bancolombia</option>
                        <option value="Davivienda">Davivienda</option>
                        <option value="BancoBogota">Banco de Bogotá</option>
                        <option value="BBVA">BBVA Colombia</option>
                        <option value="Popular">Banco Popular</option>
                        <option value="Occidente">Banco de Occidente</option>
                        <option value="AVVillas">AV Villas</option>
                        <option value="CajaSocial">Banco Caja Social</option>
                    </select>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Tipo de cuenta <span class="required">*</span>
                    </label>
                    <select id="pse_account_type" style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;" required>
                        <option value="">Seleccionar tipo</option>
                        <option value="ahorros">Cuenta de ahorros</option>
                        <option value="corriente">Cuenta corriente</option>
                    </select>
                </div>
                
                <div style="background: #e8f4fd; padding: 15px; border-radius: 8px; border-left: 4px solid #3498db;">
                    <p style="color: var(--dark); margin: 0; line-height: 1.5;">
                        <i class="fas fa-info-circle" style="color: #3498db;"></i>
                        Serás redirigido a la plataforma segura de tu banco para completar el pago.
                    </p>
                </div>
            </div>
        `,
        
        'contra_entrega': `
            <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fas fa-money-bill-wave"></i> Pago contra entrega
            </h3>
            <div style="display: grid; gap: 20px;">
                <div style="background: #fff3cd; padding: 20px; border-radius: 10px; border: 2px solid #ffeaa7;">
                    <div style="display: flex; align-items: flex-start; gap: 15px;">
                        <i class="fas fa-exclamation-triangle" style="color: #f39c12; font-size: 1.5rem; margin-top: 2px;"></i>
                        <div>
                            <h4 style="color: #856404; margin-bottom: 10px;">Importante</h4>
                            <p style="color: #856404; margin-bottom: 10px;">
                                El pago se realizará en efectivo al momento de recibir tu pedido. 
                                Por favor ten el dinero exacto o aproximado disponible.
                            </p>
                            <p style="color: #856404; font-weight: bold;">
                                Total a pagar: $${totalAmount.toLocaleString('es-CO')}
                            </p>
                        </div>
                    </div>
                </div>
                
                <div>
                    <label style="display: block; margin-bottom: 8px; font-weight: 600; color: var(--dark);">
                        Tipo de pago preferido
                    </label>
                    <select style="width: 100%; padding: 12px; border: 2px solid #e0e0e0; border-radius: 8px;">
                        <option value="efectivo_exacto">Efectivo exacto</option>
                        <option value="efectivo_aproximado">Efectivo aproximado (con vueltas)</option>
                        <option value="dataphone">Datafono móvil (tarjeta)</option>
                    </select>
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <i class="fas fa-clock" style="color: var(--secondary);"></i>
                    <span style="color: var(--gray); font-size: 0.9rem;">
                        El repartidor te llamará 30 minutos antes de la entrega para confirmar.
                    </span>
                </div>
            </div>
        `,
        
        'billetera_virtual': `
            <h3 style="color: var(--dark); margin-bottom: 20px; font-size: 1.3rem;">
                <i class="fas fa-wallet"></i> Pago con Billetera Virtual
            </h3>
            <div style="display: grid; gap: 20px;">
                <div id="walletPaymentDetails">
                    <!-- Se llena con JavaScript -->
                </div>
                
                <div style="display: flex; align-items: center; gap: 10px; margin-top: 10px;">
                    <i class="fas fa-shield-alt" style="color: var(--secondary);"></i>
                    <span style="color: var(--gray); font-size: 0.9rem;">
                        El pago se procesa de forma instantánea y segura desde tu saldo disponible.
                    </span>
                </div>
            </div>
        `
    };
    
    // Función para verificar saldo de billetera
    function checkWalletBalance() {
        fetch('checkout-payment-handler.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: 'action=check_wallet_balance'
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const walletBalance = document.getElementById('walletBalance');
                const walletBtn = document.getElementById('billeteraVirtualBtn');
                const walletRadio = walletBtn.querySelector('input[type="radio"]');
                
                if (data.saldo >= totalAmount) {
                    walletBalance.innerHTML = `Saldo: <strong>$${data.saldo.toLocaleString('es-CO')}</strong>`;
                    walletBalance.className = 'wallet-balance';
                    walletRadio.disabled = false;
                    
                    // Actualizar detalles del pago
                    const detailsDiv = document.getElementById('walletPaymentDetails');
                    if (detailsDiv) {
                        detailsDiv.innerHTML = `
                            <div style="background: #e8f5e9; padding: 15px; border-radius: 8px; border-left: 4px solid #28a745;">
                                <p style="color: #155724; margin: 0;">
                                    <i class="fas fa-check-circle"></i>
                                    <strong>Saldo suficiente:</strong> Puedes pagar con tu billetera.
                                </p>
                                <p style="color: #155724; margin: 10px 0 0 0;">
                                    Saldo disponible: <strong>$${data.saldo.toLocaleString('es-CO')}</strong><br>
                                    Total a pagar: <strong>$${totalAmount.toLocaleString('es-CO')}</strong><br>
                                    Saldo restante: <strong>$${(data.saldo - totalAmount).toLocaleString('es-CO')}</strong>
                                </p>
                            </div>
                        `;
                    }
                } else {
                    walletBalance.innerHTML = `Saldo insuficiente: <strong>$${data.saldo.toLocaleString('es-CO')}</strong>`;
                    walletBalance.className = 'wallet-balance insufficient';
                    walletRadio.disabled = true;
                    
                    // Actualizar detalles del pago
                    const detailsDiv = document.getElementById('walletPaymentDetails');
                    if (detailsDiv) {
                        detailsDiv.innerHTML = `
                            <div style="background: #f8d7da; padding: 15px; border-radius: 8px; border-left: 4px solid #dc3545;">
                                <p style="color: #721c24; margin: 0;">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <strong>Saldo insuficiente:</strong> No tienes suficiente saldo.
                                </p>
                                <p style="color: #721c24; margin: 10px 0 0 0;">
                                    Saldo disponible: <strong>$${data.saldo.toLocaleString('es-CO')}</strong><br>
                                    Total a pagar: <strong>$${totalAmount.toLocaleString('es-CO')}</strong><br>
                                    Faltan: <strong>$${(totalAmount - data.saldo).toLocaleString('es-CO')}</strong>
                                </p>
                                <button onclick="window.location.href='../user-apart-dashboard-metodos-pago.php'" 
                                        style="margin-top: 10px; padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 6px; cursor: pointer;">
                                    <i class="fas fa-money-bill-wave"></i> Recargar Billetera
                                </button>
                            </div>
                        `;
                    }
                }
            }
        })
        .catch(error => {
            console.error('Error:', error);
            document.getElementById('walletBalance').innerHTML = 'Error cargando saldo';
        });
    }
    
    // Seleccionar método de pago
    function selectPaymentMethod(method, element) {
        // Deseleccionar todos
        document.querySelectorAll('.payment-method').forEach(el => {
            el.classList.remove('selected');
            el.querySelector('input[type="radio"]').checked = false;
        });
        
        // Seleccionar este
        element.classList.add('selected');
        element.querySelector('input[type="radio"]').checked = true;
        selectedPaymentMethod = method;
        
        // Mostrar detalles del pago
        showPaymentDetails(method);
        
        // Si es billetera virtual, verificar saldo
        if (method === 'billetera_virtual') {
            checkWalletBalance();
        }
        
        // Habilitar botón de enviar
        document.getElementById('submitBtn').disabled = false;
    }
    
    // Mostrar detalles del pago
    function showPaymentDetails(method) {
        const detailsDiv = document.getElementById('paymentDetails');
        
        if (paymentDetails[method]) {
            detailsDiv.innerHTML = paymentDetails[method];
            detailsDiv.classList.add('show');
            
            // Agregar máscaras a los inputs si es tarjeta
            if (method === 'tarjeta_credito' || method === 'tarjeta_debito') {
                setTimeout(() => {
                    const cardNumberInput = document.getElementById('card_number');
                    if (cardNumberInput) {
                        cardNumberInput.addEventListener('input', formatCardNumber);
                    }
                    
                    const cardExpiryInput = document.getElementById('card_expiry');
                    if (cardExpiryInput) {
                        cardExpiryInput.addEventListener('input', formatCardExpiry);
                    }
                }, 100);
            }
        } else {
            detailsDiv.classList.remove('show');
        }
    }
    
    // Formatear número de tarjeta (4444 4444 4444 4444)
    function formatCardNumber(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        let formatted = '';
        
        for (let i = 0; i < value.length; i++) {
            if (i > 0 && i % 4 === 0) {
                formatted += ' ';
            }
            formatted += value[i];
        }
        
        e.target.value = formatted.substring(0, 19);
    }
    
    // Formatear fecha de expiración (MM/AA)
    function formatCardExpiry(e) {
        let value = e.target.value.replace(/\s+/g, '').replace(/[^0-9]/gi, '');
        
        if (value.length >= 2) {
            value = value.substring(0, 2) + '/' + value.substring(2, 4);
        }
        
        e.target.value = value.substring(0, 5);
    }
    
    // Validar formulario antes de enviar
    document.getElementById('checkoutForm').addEventListener('submit', function(e) {
        const submitBtn = document.getElementById('submitBtn');
        const terminos = document.getElementById('terminos');
        
        // Validar términos
        if (!terminos.checked) {
            e.preventDefault();
            alert('Debes aceptar los términos y condiciones para continuar.');
            terminos.focus();
            return false;
        }
        
        // Validar método de pago
        if (!selectedPaymentMethod) {
            e.preventDefault();
            alert('Por favor selecciona un método de pago.');
            document.querySelector('.payment-methods').scrollIntoView({ behavior: 'smooth' });
            return false;
        }
        
        // Validaciones específicas por método de pago
        let isValid = true;
        let errorMessage = '';
        
        switch(selectedPaymentMethod) {
            case 'tarjeta_credito':
                const cardNumber = document.getElementById('card_number');
                const cardExpiry = document.getElementById('card_expiry');
                const cardCvv = document.getElementById('card_cvv');
                const cardName = document.getElementById('card_name');
                
                if (!cardNumber || !cardNumber.value.trim() || cardNumber.value.replace(/\s/g, '').length < 16) {
                    isValid = false;
                    errorMessage = 'Número de tarjeta inválido';
                } else if (!cardExpiry || !cardExpiry.value.match(/^(0[1-9]|1[0-2])\/[0-9]{2}$/)) {
                    isValid = false;
                    errorMessage = 'Fecha de expiración inválida (MM/AA)';
                } else if (!cardCvv || !cardCvv.value.match(/^[0-9]{3,4}$/)) {
                    isValid = false;
                    errorMessage = 'CVV inválido (3-4 dígitos)';
                } else if (!cardName || !cardName.value.trim()) {
                    isValid = false;
                    errorMessage = 'Nombre en la tarjeta requerido';
                }
                break;
                
            case 'pse':
                const pseBank = document.getElementById('pse_bank');
                const pseAccountType = document.getElementById('pse_account_type');
                
                if (!pseBank || !pseBank.value) {
                    isValid = false;
                    errorMessage = 'Por favor selecciona un banco';
                } else if (!pseAccountType || !pseAccountType.value) {
                    isValid = false;
                    errorMessage = 'Por favor selecciona el tipo de cuenta';
                }
                break;
                
            case 'billetera_virtual':
                // Verificar saldo nuevamente antes de enviar
                const walletRadio = document.querySelector('input[value="billetera_virtual"]');
                if (walletRadio.disabled) {
                    isValid = false;
                    errorMessage = 'Saldo insuficiente en billetera virtual';
                }
                break;
        }
        
        if (!isValid) {
            e.preventDefault();
            alert('Error en el método de pago: ' + errorMessage);
            return false;
        }
        
        // Mostrar confirmación
        const confirmar = confirm(`¿Confirmar compra por $${totalAmount.toLocaleString('es-CO')}?\n\nMétodo: ${getPaymentMethodName(selectedPaymentMethod)}`);
        
        if (!confirmar) {
            e.preventDefault();
            return false;
        }
        
        // Deshabilitar botón y mostrar carga
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando pago...';
        
        return true;
    });
    
    // Obtener nombre del método de pago
    function getPaymentMethodName(method) {
        const names = {
            'tarjeta_credito': 'Tarjeta de Crédito',
            'tarjeta_debito': 'Tarjeta de Débito',
            'pse': 'PSE',
            'contra_entrega': 'Contra Entrega',
            'billetera_virtual': 'Billetera Virtual'
        };
        return names[method] || method;
    }
    
    // Auto-completar fecha mínima (hoy + 3 días)
    document.addEventListener('DOMContentLoaded', function() {
        // Establecer fecha mínima para envío (3 días desde hoy)
        const today = new Date();
        const minDate = new Date(today);
        minDate.setDate(today.getDate() + 3);
        
        const dateInput = document.querySelector('input[type="date"]');
        if (dateInput) {
            const minDateStr = minDate.toISOString().split('T')[0];
            dateInput.min = minDateStr;
            dateInput.value = minDateStr;
        }
        
        // Validar campos requeridos en tiempo real
        const requiredFields = document.querySelectorAll('[required]');
        requiredFields.forEach(field => {
            field.addEventListener('input', function() {
                if (this.value.trim()) {
                    this.style.borderColor = '#e0e0e0';
                }
            });
            
            field.addEventListener('blur', function() {
                if (!this.value.trim()) {
                    this.style.borderColor = 'var(--danger)';
                }
            });
        });
        
        // Auto-seleccionar departamento si ya existe
        const departamentoSelect = document.querySelector('select[name="departamento"]');
        if (departamentoSelect && departamentoSelect.value) {
            // Ya está seleccionado por PHP
        }
        
        // Verificar saldo de billetera al cargar la página
        checkWalletBalance();
    });
    </script>
</body>
</html>