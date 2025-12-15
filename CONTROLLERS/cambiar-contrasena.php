<?php
session_start();

// Verificar que el código fue verificado
if (!isset($_SESSION['recuperacion_data']) || !isset($_SESSION['codigo_verificado'])) {
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
    <title>Nueva Contraseña - Hermes</title>
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
            animation: fadeIn 0.5s;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
            font-size: 24px;
        }
        .info {
            background: #d4edda;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
            text-align: center;
            font-size: 14px;
            border-left: 4px solid #28a745;
        }
        .form-group {
            margin-bottom: 25px;
            position: relative;
        }
        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-weight: bold;
        }
        input[type="password"] {
            width: 100%;
            padding: 14px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            box-sizing: border-box;
            transition: border 0.3s;
        }
        input[type="password"]:focus {
            border-color: #667eea;
            outline: none;
        }
        .password-strength {
            font-size: 12px;
            margin-top: 5px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }
        .strength-bar {
            flex-grow: 1;
            height: 4px;
            background: #ddd;
            border-radius: 2px;
            overflow: hidden;
        }
        .strength-fill {
            height: 100%;
            width: 0%;
            transition: width 0.3s, background 0.3s;
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
        .btn:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }
        .btn:disabled {
            background: #ccc;
            cursor: not-allowed;
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
        .toggle-password {
            position: absolute;
            right: 15px;
            top: 40px;
            background: none;
            border: none;
            cursor: pointer;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Nueva Contraseña</h2>
        
        <div class="info">
            Establece una nueva contraseña para<br>
            <strong><?php echo htmlspecialchars($data['correo']); ?></strong>
        </div>
        
        <?php if (isset($_SESSION['error_contrasena'])): ?>
            <div class="message error">
                <?php 
                echo $_SESSION['error_contrasena'];
                unset($_SESSION['error_contrasena']);
                ?>
            </div>
        <?php endif; ?>
        
        <form action="recuperar-contrasena.php" method="POST" id="passwordForm">
            <input type="hidden" name="finalizar_recuperacion" value="1">
            
            <div class="form-group">
                <label for="nueva_contrasena">Nueva Contraseña</label>
                <input type="password" id="nueva_contrasena" name="nueva_contrasena" 
                       required minlength="8" onkeyup="validarContrasena()"
                       placeholder="Mínimo 8 caracteres">
                <button type="button" class="toggle-password" onclick="togglePassword('nueva_contrasena')">
                    👁️
                </button>
                <div class="password-strength">
                    <span>Seguridad:</span>
                    <div class="strength-bar">
                        <div class="strength-fill" id="strengthFill"></div>
                    </div>
                    <span id="strengthText">Débil</span>
                </div>
            </div>
            
            <div class="form-group">
                <label for="confirmar_contrasena">Confirmar Contraseña</label>
                <input type="password" id="confirmar_contrasena" name="confirmar_contrasena" 
                       required minlength="8" onkeyup="validarContrasena()"
                       placeholder="Repite la contraseña">
                <button type="button" class="toggle-password" onclick="togglePassword('confirmar_contrasena')">
                    👁️
                </button>
                <div id="matchMessage" style="font-size: 12px; margin-top: 5px;"></div>
            </div>
            
            <button type="submit" class="btn" id="submitBtn" disabled>
                Establecer Nueva Contraseña
            </button>
        </form>
    </div>

    <script>
        function togglePassword(inputId) {
            const input = document.getElementById(inputId);
            input.type = input.type === 'password' ? 'text' : 'password';
        }
        
        function validarContrasena() {
            const password = document.getElementById('nueva_contrasena').value;
            const confirm = document.getElementById('confirmar_contrasena').value;
            const btn = document.getElementById('submitBtn');
            const matchMessage = document.getElementById('matchMessage');
            const strengthFill = document.getElementById('strengthFill');
            const strengthText = document.getElementById('strengthText');
            
            // Validar fortaleza
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^A-Za-z0-9]/.test(password)) strength++;
            
            // Actualizar barra de fortaleza
            const width = strength * 25;
            strengthFill.style.width = width + '%';
            
            // Color y texto según fortaleza
            if (strength === 0) {
                strengthFill.style.background = '#ff4757';
                strengthText.textContent = 'Muy débil';
            } else if (strength === 1) {
                strengthFill.style.background = '#ff4757';
                strengthText.textContent = 'Débil';
            } else if (strength === 2) {
                strengthFill.style.background = '#ffa502';
                strengthText.textContent = 'Regular';
            } else if (strength === 3) {
                strengthFill.style.background = '#2ed573';
                strengthText.textContent = 'Fuerte';
            } else {
                strengthFill.style.background = '#2ed573';
                strengthText.textContent = 'Muy fuerte';
            }
            
            // Validar coincidencia
            if (confirm.length > 0) {
                if (password === confirm) {
                    matchMessage.innerHTML = 'Las contraseñas coinciden';
                    matchMessage.style.color = '#28a745';
                } else {
                    matchMessage.innerHTML = 'Las contraseñas no coinciden';
                    matchMessage.style.color = '#ff4757';
                }
            } else {
                matchMessage.innerHTML = '';
            }
            
            // Habilitar botón si todo está bien
            if (password.length >= 8 && password === confirm) {
                btn.disabled = false;
            } else {
                btn.disabled = true;
            }
        }
        
        // Inicializar validación
        document.addEventListener('DOMContentLoaded', validarContrasena);
    </script>
</body>
</html>