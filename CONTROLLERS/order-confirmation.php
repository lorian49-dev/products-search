<?php
// CONTROLLERS/order-confirmation.php
session_start();
require_once "shortCuts/connect.php";

if (!isset($_SESSION['checkout_success']) || !$_SESSION['checkout_success']) {
    header("Location: cart.php");
    exit;
}

$pedido_info = $_SESSION['ultimo_pedido'] ?? [];

// Limpiar sesión
unset($_SESSION['checkout_success']);
unset($_SESSION['ultimo_pedido']);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Compra Exitosa! - HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        :root {
            --primary: #8B4513;
            --secondary: #28a745;
            --dark: #2c3e50;
            --light: #f8f9fa;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #333;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .confirmation-container {
            background: white;
            border-radius: 25px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.3);
            overflow: hidden;
            max-width: 800px;
            width: 100%;
            animation: slideUp 0.8s ease;
        }
        
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
        
        .confirmation-header {
            background: linear-gradient(135deg, var(--primary), #a0522d);
            padding: 40px;
            text-align: center;
            color: white;
            position: relative;
            overflow: hidden;
        }
        
        .confirmation-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url("data:image/svg+xml,%3Csvg width='100' height='100' viewBox='0 0 100 100' xmlns='http://www.w3.org/2000/svg'%3E%3Cpath d='M11 18c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm48 25c3.866 0 7-3.134 7-7s-3.134-7-7-7-7 3.134-7 7 3.134 7 7 7zm-43-7c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm63 31c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM34 90c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zm56-76c1.657 0 3-1.343 3-3s-1.343-3-3-3-3 1.343-3 3 1.343 3 3 3zM12 86c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm28-65c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm23-11c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-6 60c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm29 22c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zM32 63c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm57-13c2.76 0 5-2.24 5-5s-2.24-5-5-5-5 2.24-5 5 2.24 5 5 5zm-9-21c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM60 91c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM35 41c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2zM12 60c1.105 0 2-.895 2-2s-.895-2-2-2-2 .895-2 2 .895 2 2 2z' fill='%23ffffff' fill-opacity='0.1' fill-rule='evenodd'/%3E%3C/svg%3E");
            opacity: 0.1;
        }
        
        .confirmation-icon {
            font-size: 100px;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite alternate;
        }
        
        @keyframes bounce {
            from { transform: translateY(0); }
            to { transform: translateY(-20px); }
        }
        
        .confirmation-title {
            font-size: 2.8rem;
            margin-bottom: 15px;
            font-weight: 700;
        }
        
        .confirmation-subtitle {
            font-size: 1.2rem;
            opacity: 0.9;
        }
        
        .confirmation-content {
            padding: 40px;
        }
        
        .order-details {
            background: var(--light);
            padding: 30px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 5px solid var(--secondary);
        }
        
        .detail-item {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding-bottom: 20px;
            border-bottom: 1px solid #e0e0e0;
        }
        
        .detail-item:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }
        
        .detail-label {
            font-weight: 600;
            color: var(--dark);
            font-size: 1.1rem;
        }
        
        .detail-value {
            font-weight: bold;
            color: var(--primary);
            font-size: 1.1rem;
            text-align: right;
        }
        
        .detail-value.pedido {
            font-size: 1.4rem;
            color: var(--secondary);
        }
        
        .next-steps {
            background: #e8f4fd;
            padding: 25px;
            border-radius: 15px;
            margin-bottom: 30px;
            border-left: 5px solid #3498db;
        }
        
        .steps-title {
            font-size: 1.4rem;
            color: var(--dark);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .step {
            display: flex;
            align-items: flex-start;
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .step-number {
            width: 35px;
            height: 35px;
            background: #3498db;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }
        
        .step-content {
            flex: 1;
        }
        
        .step-title {
            font-weight: 600;
            color: var(--dark);
            margin-bottom: 5px;
        }
        
        .step-desc {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.5;
        }
        
        .actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 40px;
        }
        
        .btn {
            padding: 18px 25px;
            border-radius: 12px;
            font-size: 1.1rem;
            font-weight: bold;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 12px;
            transition: all 0.3s;
            text-align: center;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), #a0522d);
            color: white;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #72370f, var(--primary));
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 69, 19, 0.3);
        }
        
        .btn-secondary {
            background: var(--light);
            color: var(--dark);
            border: 2px solid #e0e0e0;
        }
        
        .btn-secondary:hover {
            background: #e9ecef;
            transform: translateY(-3px);
        }
        
        .btn-whatsapp {
            background: linear-gradient(135deg, #25D366, #128C7E);
            color: white;
        }
        
        .btn-whatsapp:hover {
            background: linear-gradient(135deg, #128C7E, #25D366);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(37, 211, 102, 0.3);
        }
        
        .confetti {
            position: fixed;
            width: 15px;
            height: 15px;
            background: var(--primary);
            opacity: 0;
            pointer-events: none;
            border-radius: 50%;
        }
        
        @media (max-width: 768px) {
            .confirmation-header {
                padding: 30px 20px;
            }
            
            .confirmation-title {
                font-size: 2rem;
            }
            
            .confirmation-content {
                padding: 25px;
            }
            
            .detail-item {
                flex-direction: column;
                gap: 5px;
            }
            
            .detail-value {
                text-align: left;
            }
            
            .actions {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .confirmation-title {
                font-size: 1.6rem;
            }
            
            .confirmation-icon {
                font-size: 70px;
            }
        }
    </style>
</head>
<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="confirmation-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1 class="confirmation-title">¡Compra Exitosa!</h1>
            <p class="confirmation-subtitle">Tu pedido ha sido procesado correctamente</p>
        </div>
        
        <div class="confirmation-content">
            <div class="order-details">
                <div class="detail-item">
                    <span class="detail-label">Número de Pedido:</span>
                    <span class="detail-value pedido"><?php echo htmlspecialchars($pedido_info['numero_pedido'] ?? 'N/A'); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Fecha y Hora:</span>
                    <span class="detail-value"><?php echo htmlspecialchars($pedido_info['fecha'] ?? date('d/m/Y H:i')); ?></span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Total Pagado:</span>
                    <span class="detail-value">$<?php echo number_format($pedido_info['total'] ?? 0, 0, ',', '.'); ?> COP</span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Método de Pago:</span>
                    <span class="detail-value">
                        <?php 
                        $metodos = [
                            'tarjeta_credito' => 'Tarjeta de Crédito',
                            'tarjeta_debito' => 'Tarjeta de Débito',
                            'pse' => 'PSE',
                            'contra_entrega' => 'Contra Entrega'
                        ];
                        echo htmlspecialchars($metodos[$pedido_info['metodo_pago'] ?? ''] ?? $pedido_info['metodo_pago'] ?? 'N/A');
                        ?>
                    </span>
                </div>
                
                <div class="detail-item">
                    <span class="detail-label">Dirección de Envío:</span>
                    <span class="detail-value"><?php echo nl2br(htmlspecialchars($pedido_info['direccion'] ?? 'No especificada')); ?></span>
                </div>
            </div>
            
            <div class="next-steps">
                <h3 class="steps-title">
                    <i class="fas fa-list-check"></i>
                    Próximos pasos
                </h3>
                
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <div class="step-title">Confirmación del pedido</div>
                        <div class="step-desc">
                            Recibirás un correo electrónico con todos los detalles de tu compra 
                            en los próximos minutos.
                        </div>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <div class="step-title">Procesamiento y envío</div>
                        <div class="step-desc">
                            Tu pedido será preparado y enviado en un plazo de 24-48 horas hábiles.
                            Recibirás un correo con el número de seguimiento.
                        </div>
                    </div>
                </div>
                
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <div class="step-title">Entrega</div>
                        <div class="step-desc">
                            El tiempo de entrega estimado es de 3-5 días hábiles. 
                            El repartidor te contactará antes de la entrega.
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="actions">
                <a href="../home.php" class="btn btn-primary">
                    <i class="fas fa-home"></i> Volver al Inicio
                </a>
                
                <a href="user-apart-dashboard.php" class="btn btn-secondary">
                    <i class="fas fa-clipboard-list"></i> Ver Mis Pedidos
                </a>
                
                <a href="https://wa.me/573001234567?text=Hola,%20acabo%20de%20hacer%20un%20pedido%20en%20HERMES%20con%20número%20<?php echo urlencode($pedido_info['numero_pedido'] ?? ''); ?>"
                   target="_blank" 
                   class="btn btn-whatsapp">
                    <i class="fab fa-whatsapp"></i> Contactar Soporte
                </a>
            </div>
        </div>
    </div>
    
    <script>
    // Efecto confetti
    document.addEventListener('DOMContentLoaded', function() {
        const colors = ['#8B4513', '#28a745', '#3498db', '#f39c12', '#e74c3c'];
        
        for (let i = 0; i < 100; i++) {
            setTimeout(() => {
                const confetti = document.createElement('div');
                confetti.className = 'confetti';
                confetti.style.left = Math.random() * 100 + 'vw';
                confetti.style.top = '-20px';
                confetti.style.background = colors[Math.floor(Math.random() * colors.length)];
                confetti.style.opacity = Math.random() * 0.7 + 0.3;
                confetti.style.transform = `scale(${Math.random() * 0.5 + 0.5})`;
                
                document.body.appendChild(confetti);
                
                const animation = confetti.animate([
                    { transform: 'translateY(0) rotate(0deg)', opacity: 1 },
                    { transform: `translateY(${window.innerHeight + 100}px) rotate(${Math.random() * 360}deg)`, opacity: 0 }
                ], {
                    duration: Math.random() * 3000 + 2000,
                    easing: 'cubic-bezier(0.215, 0.61, 0.355, 1)'
                });
                
                animation.onfinish = () => confetti.remove();
            }, i * 30);
        }
        
        // Guardar en localStorage para tracking
        const pedidoNum = '<?php echo $pedido_info['numero_pedido'] ?? ''; ?>';
        if (pedidoNum) {
            const historial = JSON.parse(localStorage.getItem('historial_compras') || '[]');
            historial.push({
                numero: pedidoNum,
                fecha: new Date().toISOString(),
                total: <?php echo $pedido_info['total'] ?? 0; ?>
            });
            localStorage.setItem('historial_compras', JSON.stringify(historial));
        }
    });
    </script>
</body>
</html>