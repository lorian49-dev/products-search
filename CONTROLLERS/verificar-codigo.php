<?php
session_start();

// Verificar que venga de solicitud válida
if (!isset($_SESSION['recuperacion_data'])) {
    header('Location: solicitar-recuperacion.php');
    exit;
}

$data = $_SESSION['recuperacion_data'];
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verificar Código - Hermes</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            width: 100%;
            max-width: 400px;
            animation: slideIn 0.5s;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(-20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .info {
            background: #e7f3ff;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #667eea;
        }
        .form-group {
            margin-bottom: 25px;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        .code-input {
            width: 100%;
            padding: 16px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 28px;
            text-align: center;
            letter-spacing: 15px;
            font-family: 'Courier New', monospace;
            box-sizing: border-box;
            transition: border 0.3s;
        }
        .code-input:focus {
            border-color: #667eea;
            outline: none;
        }
        .btn {
            width: 100%;
            padding: 16px;
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 18px;
            font-weight: bold;
            cursor: pointer;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .message {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            text-align: center;
            font-weight: bold;
        }
        .error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .back-link {
            text-align: center;
            margin-top: 25px;
        }
        .back-link a {
            color: #667eea;
            text-decoration: none;
            font-weight: bold;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Verificar Código</h2>
        
        <div class="info">
            Se ha generado un código para:<br>
            <strong><?php echo htmlspecialchars($data['correo']); ?></strong>
        </div>
        
        <?php if (isset($_SESSION['error_codigo'])): ?>
            <div class="message error">
                <?php 
                echo $_SESSION['error_codigo'];
                unset($_SESSION['error_codigo']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="procesar-cambio.php" method="POST">
            <div class="form-group">
                <label for="codigo">Ingresa el código de 6 dígitos</label>
                <input type="text" id="codigo" name="codigo" 
                       class="code-input" required maxlength="6" 
                       pattern="[0-9]{6}" placeholder="000000"
                       title="Ingresa el código de 6 dígitos"
                       oninput="this.value = this.value.replace(/[^0-9]/g, '')">
            </div>
            
            <button type="submit" class="btn">Verificar Código</button>
        </form>
        
        <div class="back-link">
            <a href="solicitar-recuperacion.php">← Volver atrás</a>
        </div>
        
        <script>
            // Auto-focus en el input
            document.getElementById('codigo').focus();
            
            // Auto-avanzar entre inputs si tuvieras 6 inputs separados
            // Esta versión usa un solo input
        </script>
    </div>
</body>
</html>