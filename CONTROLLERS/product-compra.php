<?php
session_start();
include('../shortCuts/connect.php');

// Obligatorio estar logueado
if (!isset($_SESSION['usuario_logueado']) || !$_SESSION['usuario_logueado']) {
    $return = urlencode("http://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    header("Location: ../registros-inicio-sesion/login.php?return_url=$return");
    exit();
}

$usuario_id = $_SESSION['usuario_id'];

// Obtener datos del usuario
$stmt = $connect->prepare("SELECT nombre, apellido, correo, telefono, direccion_principal FROM usuario WHERE id_usuario = ?");
$stmt->bind_param("i", $usuario_id);
$stmt->execute();
$usuario = $stmt->get_result()->fetch_assoc();

// Obtener items del carrito (asumiendo que tienes una tabla carrito o usas sesión)
$carrito = [];
$total = 0;

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalizar Compra - Hermes</title>
    <link rel="stylesheet" href="../styles/checkout.css">
    <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.min.css">
    <style>
        :root {
            --primary: #3498db;
            --success: #2ecc71;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }

        body {
            font-family: 'Segoe UI', sans-serif;
            background: #f5f7fa;
            margin: 0;
            padding: 20px;
        }

        .container {
            max-width: 1100px;
            margin: 0 auto;
            display: grid;
            grid-template-columns: 1fr 400px;
            gap: 30px;
        }

        .card {
            background: white;
            border-radius: 12px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.08);
        }

        h1 {
            color: var(--dark);
            text-align: center;
            margin-bottom: 30px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #2c3e50;
        }

        input,
        select,
        textarea {
            width: 100%;
            padding: 12px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
        }

        .btn {
            padding: 14px 30px;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            cursor: pointer;
            transition: 0.3s;
        }

        .btn-primary {
            background: var(--primary);
            color: white;
        }

        .btn-primary:hover {
            background: #2980b9;
        }

        .btn-success {
            background: var(--success);
            color: white;
            font-size: 18px;
            width: 100%;
            margin-top: 20px;
        }

        .resumen {
            position: sticky;
            top: 20px;
        }

        .item {
            display: flex;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #eee;
        }

        .item img {
            width: 80px;
            height: 80px;
            object-fit: cover;
            border-radius: 8px;
        }

        .total {
            font-size: 24px;
            font-weight: bold;
            text-align: right;
            margin-top: 20px;
            color: var(--dark);
        }

        .metodos-pago {
            display: flex;
            gap: 20px;
            flex-wrap: wrap;
            margin-top: 10px;
        }

        .metodo {
            flex: 1;
            min-width: 120px;
            text-align: center;
            padding: 15px;
            border: 2px solid #ddd;
            border-radius: 10px;
            cursor: pointer;
            transition: 0.3s;
        }

        .metodo.active {
            border-color: var(--primary);
            background: #ebf3fd;
        }

        .metodo i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }
    </style>
</head>

<body>

    <div class="container">
        <!-- Formulario de envío -->
        <div>
            <h1>Finalizar Compra</h1>

            <div class="card">
                <h2>1. Dirección de Envío</h2>
                <form method="POST" action="procesar-pago.php" id="checkout-form">
                    <div class="form-group">
                        <label>Nombre completo</label>
                        <input type="text" name="nombre" value="<?= htmlspecialchars($usuario['nombre'] . ' ' . $usuario['apellido']) ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Teléfono</label>
                        <input type="tel" name="telefono" value="<?= htmlspecialchars($usuario['telefono'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Dirección completa</label>
                        <input type="text" name="direccion" value="<?= htmlspecialchars($usuario['direccion_principal'] ?? '') ?>" required>
                    </div>
                    <div class="form-group">
                        <label>Ciudad</label>
                        <input type="text" name="ciudad" value="Bogotá D.C." required>
                    </div>
                    <div class="form-group">
                        <label>Notas adicionales (opcional)</label>
                        <textarea name="notas" rows="3" placeholder="Ej: Dejar con el portero, timbre no funciona..."></textarea>
                    </div>
                </form>
            </div>

            <div class="card" style="margin-top: 20px;">
                <h2>2. Método de Pago</h2>
                <div class="metodos-pago">
                    <div class="metodo active" data-metodo="contraentrega">
                        <i class="fas fa-money-bill-wave"></i>
                        <strong>Contra entrega</strong><br>
                        <small>Paga cuando recibas</small>
                    </div>
                    <div class="metodo" data-metodo="tarjeta">
                        <i class="fas fa-credit-card"></i>
                        <strong>Tarjeta / PSE</strong><br>
                        <small>Pasarela segura</small>
                    </div>
                    <div class="metodo" data-metodo="transferencia">
                        <i class="fas fa-university"></i>
                        <strong>Transferencia</strong><br>
                        <small>Nequi, Daviplata, Bancolombia</small>
                    </div>
                </div>
                <input type="hidden" name="metodo_pago" id="metodo_pago_input" value="contraentrega" form="checkout-form">
            </div>
        </div>

        <!-- Resumen de compra -->
        <div class="resumen">
            <div class="card">
                <h2>Resumen del Pedido</h2>
                <?php foreach ($carrito as $item): ?>
                    <div class="item">
                        <img src="<?= htmlspecialchars($item['imagen'] ?? '../SOURCES/default-product.jpg') ?>" alt="producto">
                        <div style="flex:1;">
                            <strong><?= htmlspecialchars($item['nombre']) ?></strong><br>
                            <small>Cantidad: <?= $item['cantidad'] ?></small><br>
                            <span style="color:var(--primary); font-weight:bold;">
                                $<?= number_format($item['precio'] * $item['cantidad'], 0, ',', '.') ?>
                            </span>
                        </div>
                    </div>
                <?php endforeach; ?>

                <div style="border-top: 2px solid #eee; padding-top: 15px; margin-top: 15px;">
                    <div style="display:flex; justify-content:space-between; font-size:18px;">
                        <span>Subtotal</span>
                        <strong>$<?= number_format($total, 0, ',', '.') ?></strong>
                    </div>
                    <div style="display:flex; justify-content:space-between; margin:10px 0;">
                        <span>Envío</span>
                        <strong style="color:var(--success);">GRATIS</strong>
                    </div>
                    <div class="total">
                        Total: <span style="color:var(--primary);">$<?= number_format($total, 0, ',', '.') ?></span>
                    </div>
                </div>

                <button type="submit" form="checkout-form" class="btn btn-success">
                    <i class="fas fa-lock"></i> Confirmar y Pagar
                </button>

                <p style="text-align:center; margin-top:20px; font-size:14px; color:#7f8c8d;">
                    Compra protegida • Devoluciones gratis 30 días
                </p>
            </div>
            <div style="margin-bottom: 25px; display: flex; gap: 15px; flex-wrap: wrap; font-size: 15px;">
                <!-- Botón: Seguir comprando / Volver al producto -->
                <?php
                $referer = $_SERVER['HTTP_REFERER'] ?? '';

                // Detecta TODAS tus páginas de producto posibles
                $es_pagina_producto = $referer && (
                    strpos($referer, 'search-product-product.php') !== false ||
                    strpos($referer, 'producto.php') !== false ||
                    strpos($referer, 'detalle') !== false ||
                    strpos($referer, 'product') !== false
                );

                if ($es_pagina_producto && !str_contains($referer, 'checkout') && !str_contains($referer, 'login') && !str_contains($referer, 'logout')) {
                    echo '<a href="' . htmlspecialchars($referer) . '" 
                 style="color:#2c3e50; text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:12px 20px; background:#f8f9fa; border:1px solid #ddd; border-radius:8px;">
                 Volver al producto
              </a>';
                } else {
                    // Si no viene de un producto → volver al carrito
                    echo '<a href="../carrito.php" 
                 style="color:#2c3e50; text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:12px 20px; background:#f8f9fa; border:1px solid #ddd; border-radius:8px;">
                 Volver al carrito
              </a>';
                }
                ?>

                <!-- Botón: Ir al home (siempre visible) -->
                <a href="../home.php"
                    style="color:#2c3e50; text-decoration:none; display:inline-flex; align-items:center; gap:8px; padding:12px 20px; background:#f8f9fa; border:1px solid #ddd; border-radius:8px;">
                    Ir al inicio
                </a>
            </div>
        </div>
    </div>

    <script>
        // Cambiar método de pago
        document.querySelectorAll('.metodo').forEach(m => {
            m.addEventListener('click', function() {
                document.querySelectorAll('.metodo').forEach(x => x.classList.remove('active'));
                this.classList.add('active');
                document.getElementById('metodo_pago_input').value = this.dataset.metodo;
            });
        });
    </script>

</body>

</html>