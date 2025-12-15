<?php
// CONTROLLERS/order-confirmation.php
session_start();

// CORREGIR la ruta de connect.php
// Si order-confirmation.php está en CONTROLLERS/, y connect.php está en shortCuts/
// La ruta correcta es:
require_once dirname(__DIR__) . '/shortCuts/connect.php';

// O también puedes usar:
// require_once __DIR__ . '/../shortCuts/connect.php';

// Verificar que hay un pedido exitoso
if (!isset($_SESSION['pedido_exitoso']) || !$_SESSION['pedido_exitoso']) {
    header("Location: checkout.php");
    exit;
}

// Obtener ID del pedido
$id_pedido = $_SESSION['id_pedido'] ?? 0;
$total_pedido = $_SESSION['total_pedido'] ?? 0;
$metodo_pago = $_SESSION['metodo_pago'] ?? '';

if ($id_pedido == 0) {
    header("Location: checkout.php");
    exit;
}

// Obtener información del pedido
$sql_pedido = "SELECT p.*, u.nombre, u.apellido, u.correo 
               FROM pedido p 
               INNER JOIN usuario u ON p.id_cliente = u.id_usuario 
               WHERE p.id_pedido = ?";
$stmt = $connect->prepare($sql_pedido);
$stmt->bind_param("i", $id_pedido);
$stmt->execute();
$result = $stmt->get_result();
$pedido = $result->fetch_assoc();
$stmt->close();

// Limpiar sesión del pedido
unset($_SESSION['pedido_exitoso']);
unset($_SESSION['id_pedido']);
unset($_SESSION['total_pedido']);
unset($_SESSION['metodo_pago']);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>¡Pedido Confirmado! - HERMES</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .confirmation-container {
            max-width: 800px;
            width: 100%;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.15);
            overflow: hidden;
            animation: fadeIn 0.8s ease;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .confirmation-header {
            background: linear-gradient(135deg, #8B4513 0%, #a0522d 100%);
            color: white;
            padding: 40px;
            text-align: center;
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
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100" preserveAspectRatio="none" opacity="0.1"><path d="M0,0 L100,0 L100,100 Z" fill="white"/></svg>');
            background-size: cover;
        }

        .check-icon {
            font-size: 80px;
            margin-bottom: 20px;
            animation: bounce 1s ease infinite alternate;
        }

        @keyframes bounce {
            from {
                transform: scale(1);
            }

            to {
                transform: scale(1.1);
            }
        }

        .confirmation-header h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            font-weight: 700;
        }

        .confirmation-header p {
            font-size: 1.2rem;
            opacity: 0.9;
        }

        .order-details {
            padding: 40px;
        }

        .detail-section {
            margin-bottom: 30px;
            padding-bottom: 20px;
            border-bottom: 2px solid #f0f0f0;
        }

        .detail-section:last-child {
            border-bottom: none;
            margin-bottom: 0;
        }

        .section-title {
            font-size: 1.3rem;
            color: #333;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: #8B4513;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
        }

        .info-item {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }

        .info-label {
            font-size: 0.9rem;
            color: #666;
            font-weight: 600;
        }

        .info-value {
            font-size: 1.1rem;
            color: #333;
            font-weight: 500;
        }

        .highlight-box {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            padding: 25px;
            border-radius: 15px;
            border-left: 5px solid #28a745;
            margin-top: 20px;
        }

        .next-steps {
            background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%);
            padding: 30px;
            border-radius: 15px;
            margin-top: 30px;
        }

        .steps-list {
            list-style: none;
            padding: 0;
        }

        .steps-list li {
            padding: 15px 0;
            display: flex;
            align-items: center;
            gap: 15px;
            border-bottom: 1px dashed #ccc;
        }

        .steps-list li:last-child {
            border-bottom: none;
        }

        .step-number {
            width: 30px;
            height: 30px;
            background: #8B4513;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            flex-shrink: 0;
        }

        .confirmation-actions {
            padding: 30px 40px;
            background: #f8f9fa;
            display: flex;
            gap: 20px;
            border-top: 2px solid #eee;
        }

        .btn {
            padding: 15px 30px;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            flex: 1;
            border: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, #8B4513 0%, #a0522d 100%);
            color: white;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, #72370f 0%, #8B4513 100%);
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(139, 69, 19, 0.3);
        }

        .btn-secondary {
            background: white;
            color: #333;
            border: 2px solid #ddd;
        }

        .btn-secondary:hover {
            background: #f8f9fa;
            border-color: #8B4513;
        }

        .status-badge {
            display: inline-block;
            padding: 8px 20px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-left: 10px;
        }

        .status-confirmed {
            background: #d4edda;
            color: #155724;
        }

        .status-pending {
            background: #fff3cd;
            color: #856404;
        }

        @media (max-width: 768px) {
            .confirmation-container {
                max-width: 100%;
            }

            .confirmation-header {
                padding: 30px 20px;
            }

            .confirmation-header h1 {
                font-size: 2rem;
            }

            .order-details {
                padding: 30px 20px;
            }

            .confirmation-actions {
                flex-direction: column;
                padding: 30px 20px;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 480px) {
            .confirmation-header h1 {
                font-size: 1.6rem;
            }

            .info-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>

<body>
    <div class="confirmation-container">
        <div class="confirmation-header">
            <div class="check-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>¡Pedido Confirmado!</h1>
            <p>Tu compra ha sido procesada exitosamente</p>
        </div>

        <div class="order-details">
            <?php if ($pedido): ?>
                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-receipt"></i>
                        Detalles del Pedido
                    </h2>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Número de Pedido</span>
                            <span class="info-value">#<?php echo htmlspecialchars($pedido['id_pedido']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Fecha y Hora</span>
                            <span class="info-value"><?php echo date('d/m/Y H:i', strtotime($pedido['fecha_pedido'])); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Estado</span>
                            <span class="info-value">
                                <?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?>
                                <span class="status-badge status-<?php echo $pedido['estado']; ?>">
                                    <?php echo htmlspecialchars(ucfirst($pedido['estado'])); ?>
                                </span>
                            </span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Método de Pago</span>
                            <span class="info-value">
                                <?php
                                $metodos = [
                                    'tarjeta_credito' => 'Tarjeta de Crédito',
                                    'tarjeta_debito' => 'Tarjeta de Débito',
                                    'pse' => 'PSE',
                                    'billetera_virtual' => 'Billetera Virtual',
                                    'contra_entrega' => 'Contra Entrega'
                                ];
                                echo $metodos[$pedido['metodo_pago']] ?? htmlspecialchars($pedido['metodo_pago']);
                                ?>
                            </span>
                        </div>
                    </div>

                    <div class="highlight-box">
                        <div style="text-align: center;">
                            <div style="font-size: 0.9rem; color: #666; margin-bottom: 5px;">Total Pagado</div>
                            <div style="font-size: 2.5rem; font-weight: bold; color: #8B4513;">
                                $<?php echo number_format($pedido['total'], 0, ',', '.'); ?>
                            </div>
                            <div style="font-size: 0.9rem; color: #666; margin-top: 10px;">
                                Incluye: Subtotal: $<?php echo number_format($pedido['subtotal'], 0, ',', '.'); ?> •
                                Envío: $<?php echo number_format($pedido['envio'], 0, ',', '.'); ?> •
                                IVA: $<?php echo number_format($pedido['iva'], 0, ',', '.'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-user"></i>
                        Información del Cliente
                    </h2>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Nombre</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['nombre'] . ' ' . $pedido['apellido']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Email</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['correo']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Teléfono de Contacto</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['telefono_contacto']); ?></span>
                        </div>

                        <?php if ($pedido['llegada_estimada']): ?>
                            <div class="info-item">
                                <span class="info-label">Llegada Estimada</span>
                                <span class="info-value"><?php echo date('d/m/Y', strtotime($pedido['llegada_estimada'])); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="detail-section">
                    <h2 class="section-title">
                        <i class="fas fa-truck"></i>
                        Dirección de Envío
                    </h2>

                    <div class="info-grid">
                        <div class="info-item">
                            <span class="info-label">Dirección</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['direccion_envio']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Ciudad</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['ciudad']); ?></span>
                        </div>

                        <div class="info-item">
                            <span class="info-label">Departamento</span>
                            <span class="info-value"><?php echo htmlspecialchars($pedido['departamento']); ?></span>
                        </div>

                        <?php if ($pedido['codigo_postal']): ?>
                            <div class="info-item">
                                <span class="info-label">Código Postal</span>
                                <span class="info-value"><?php echo htmlspecialchars($pedido['codigo_postal']); ?></span>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="next-steps">
                    <h2 class="section-title">
                        <i class="fas fa-clipboard-list"></i>
                        ¿Qué sigue?
                    </h2>

                    <ul class="steps-list">
                        <li>
                            <div class="step-number">1</div>
                            <div>
                                <strong>Confirmación por email</strong><br>
                                Hemos enviado un correo con los detalles de tu pedido a <?php echo htmlspecialchars($pedido['correo']); ?>
                            </div>
                        </li>

                        <li>
                            <div class="step-number">2</div>
                            <div>
                                <strong>Procesamiento del pedido</strong><br>
                                Estamos preparando tus productos para el envío
                            </div>
                        </li>

                        <li>
                            <div class="step-number">3</div>
                            <div>
                                <strong>Envío y seguimiento</strong><br>
                                Recibirás un email con el número de guía para rastrear tu pedido
                            </div>
                        </li>

                        <li>
                            <div class="step-number">4</div>
                            <div>
                                <strong>Entrega</strong><br>
                                Tu pedido llegará aproximadamente el <?php
                                                                        $llegada = $pedido['llegada_estimada'] ? date('d/m/Y', strtotime($pedido['llegada_estimada'])) : '3-5 días hábiles';
                                                                        echo $llegada;
                                                                        ?>
                            </div>
                        </li>
                    </ul>
                </div>
            <?php else: ?>
                <div style="text-align: center; padding: 40px;">
                    <div style="font-size: 60px; color: #e0e0e0; margin-bottom: 20px;">
                        <i class="fas fa-exclamation-triangle"></i>
                    </div>
                    <h2 style="color: #666; margin-bottom: 15px;">No se encontró el pedido</h2>
                    <p style="color: #999;">El pedido solicitado no existe o ha expirado.</p>
                </div>
            <?php endif; ?>
        </div>

        <div class="confirmation-actions">
            <a href="../home.php" class="btn btn-secondary">
                <i class="fas fa-home"></i> Volver al Inicio
            </a>

            <a href="user-apart-dashboard-compras.php" class="btn btn-primary">
                <i class="fas fa-shopping-bag"></i> Ver mis compras
            </a>

            <a href="download-invoice.php?id=<?php echo $id_pedido; ?>" class="btn btn-secondary">
                <i class="fas fa-download"></i> Descargar Factura (JSON)
            </a>
        </div>
    </div>

    <script>
        // Animación de confeti (opcional)
        document.addEventListener('DOMContentLoaded', function() {
            // Mostrar mensaje de éxito
            console.log('¡Pedido confirmado exitosamente!');

            // Auto-scroll al inicio
            window.scrollTo(0, 0);

            // Opcional: Agregar confeti después de 1 segundo
            setTimeout(function() {
                if (typeof confetti === 'function') {
                    confetti({
                        particleCount: 100,
                        spread: 70,
                        origin: {
                            y: 0.6
                        }
                    });
                }
            }, 1000);
        });
    </script>

    <!-- Opcional: Script de confeti -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.5.1/dist/confetti.browser.min.js"></script>
</body>

</html>