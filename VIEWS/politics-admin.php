<?php

session_start();

/* Verifica si está logueado como admin */
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    echo "<script>
        alert('Acceso denegado. Debe iniciar sesión como administrador.');
        window.location.href = '../registros-inicio-sesion/admin-login.php';
    </script>";
    exit();
}

/* Verifica rol permitido */
$rolesPermitidos = [1, 2];

if (!isset($_SESSION['admin_rol']) || !in_array($_SESSION['admin_rol'], $rolesPermitidos)) {
    echo "<script>
        alert('No tiene permisos de administrador.');
        window.location.href = '../home.php';
    </script>";
    exit();
}

$conn = new mysqli("localhost", "root", "", "hermes_bd");
if ($conn->connect_error) {
    die("Error de conexión");
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Politicas para los administradores</title>
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
         <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css"> 
</head>
<body>
    <nav id="navegation">
        <a href="user-dashboard-admin.php"><i class="fas fa-home" id="iconHome"></i></a>
        <span class="img-logo">
            <img src="../SOURCES/ICONOS-LOGOS/HERMES_LOGO_CREAM.png" alt="HERMES" title="HERMES LOGOTIPO">
        </span>
                <!--bienvenida personalizada con rol-->
            <span class="welcome-admin">
                Bienvenido <?php echo $_SESSION['admin_nombre'] ?? 'Administrador'; ?> 
            (<?php 
                if ($_SESSION['admin_rol'] == 1) echo 'Administrador';
                elseif ($_SESSION['admin_rol'] == 2) echo 'Colaborador'; 
                else echo 'Administrador';
            ?>)
            </span>
        <ul class="listMother">
            <li id="liUsers">Consultar Usuarios<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetList">
                <a href="../CONTROLLERS/user-dashboard-admin-index.php">
                    <li>Usuarios</li>
                </a>
               <a href="../CONTROLLERS/client-dashboard-index.php"><li>Clientes</li></a>
                <a href="../CONTROLLERS/seller-dashboard-admin-index.php"><li>Vendedores</li></a>
            </ul>
            <li id="liProducts">Consultar Productos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListProducts">
                <a href="../CONTROLLERS/products-dashboard-admin-index.php">
                    <li>Productos</li>
                </a>
                <li>Categorias</li>
                <li>Listado de ventas por vendedor</li>
            </ul>
            <li id="liGets">Gestion de pedidos<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListGets">
                <a href="../CONTROLLERS/orders-admin-index.php">
                    <li>Pedidos</li>
                </a>
                <li>Actualizar estados de pedidos</li>
            </ul>
            <li id="liStats">Reportes Generales<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListStats">
                <li>Mejores Vendedores</li>
                <li>Mas Vendidos</li>
                <li>Trafico de la plataforma</li>
            </ul>
            <li id="liAbout">Acerca de<i class="fa-solid fa-caret-up"></i></li>
            <ul class="sheetListAbout">
                <li class="current-page">Politicas de privacidad y uso</li>
                 <a href="seller-terms.php">
                    <li>Terminos para vendedores</li>
                </a>
            </ul>
            <span class="btn-color-mode">
                <form action="../registros-inicio-sesion/logout.php" method="POST">
                    <button type="submit" class="btn-close-session">Cerrar sesión</button>
                </form>
                <div class="btn-color-mode-choices">
                    <span class="background-modes"></span>
                    <button class="light-mode">
                        <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-sun" viewBox="0 0 16 16">
                            <path d="M8 11a3 3 0 1 1 0-6 3 3 0 0 1 0 6m0 1a4 4 0 1 0 0-8 4 4 0 0 0 0 8M8 0a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 0m0 13a.5.5 0 0 1 .5.5v2a.5.5 0 0 1-1 0v-2A.5.5 0 0 1 8 13m8-5a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2a.5.5 0 0 1 .5.5M3 8a.5.5 0 0 1-.5.5h-2a.5.5 0 0 1 0-1h2A.5.5 0 0 1 3 8m10.657-5.657a.5.5 0 0 1 0 .707l-1.414 1.415a.5.5 0 1 1-.707-.708l1.414-1.414a.5.5 0 0 1 .707 0m-9.193 9.193a.5.5 0 0 1 0 .707L3.05 13.657a.5.5 0 0 1-.707-.707l1.414-1.414a.5.5 0 0 1 .707 0m9.193 2.121a.5.5 0 0 1-.707 0l-1.414-1.414a.5.5 0 0 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .707M4.464 4.465a.5.5 0 0 1-.707 0L2.343 3.05a.5.5 0 1 1 .707-.707l1.414 1.414a.5.5 0 0 1 0 .708" />
                        </svg>
                    </button>
                    <button class="dark-mode"><i class="fa-solid fa-moon"></i></button>
                </div>
            </span>
    </nav>
    <div class="contenedor-politicas">
        <h1>POLÍTICAS DE USO Y PRIVACIDAD - SISTEMA ADMINISTRATIVO HERMES</h1>
        
        <div class="seccion">
            <h2>1. ACCESO Y AUTENTICACIÓN</h2>
            
            <h3>1.1 Credenciales de Acceso</h3>
            <p>Cada administrador recibirá credenciales únicas e intransferibles para acceder al sistema HERMES. Estas credenciales son de uso personal y exclusivo.</p>
            
            <ul>
                <li>El nombre de usuario y contraseña deben mantenerse en absoluta confidencialidad</li>
                <li>Está prohibido compartir, prestar o transferir credenciales a otros empleados</li>
                <li>La contraseña debe cambiarse cada 90 días como medida de seguridad</li>
                <li>Debe contener mínimo 8 caracteres, incluyendo mayúsculas, minúsculas y números</li>
            </ul>
            
            <div class="advertencia">
                <p><strong>ADVERTENCIA:</strong> Cualquier actividad realizada con sus credenciales será responsabilidad suya. El sistema registra todas las acciones con su usuario.</p>
            </div>
            
            <h3>1.2 Control de Sesiones</h3>
            <p>Por seguridad, las sesiones administrativas tienen las siguientes restricciones:</p>
            <ul>
                <li>Tiempo máximo de inactividad: 30 minutos</li>
                <li>Bloqueo automático tras 5 intentos fallidos de acceso</li>
                <li>Solo un inicio de sesión activo por usuario simultáneamente</li>
                <li>Registro detallado de fecha, hora e IP de cada acceso</li>
            </ul>
        </div>
        
        <div class="seccion">
            <h2>2. MANEJO DE DATOS SENSIBLES</h2>
            
            <h3>2.1 Información Confidencial</h3>
            <p>Como administrador, tendrá acceso a información clasificada como confidencial:</p>
            <ul>
                <li><strong>Datos personales de clientes:</strong> Nombres completos, documentos de identidad, direcciones, teléfonos, correos electrónicos</li>
                <li><strong>Información financiera:</strong> Historial de compras, montos de transacciones, métodos de pago, estados de cuenta</li>
                <li><strong>Datos comerciales:</strong> Estrategias de venta, proveedores, precios de costo, márgenes de ganancia</li>
                <li><strong>Información de vendedores:</strong> Datos personales, porcentajes de comisión, desempeño comercial</li>
            </ul>
            
            <div class="prohibido">
                <p><strong>PROHIBICIÓN ABSOLUTA:</strong> Extraer, copiar, descargar, compartir o utilizar información del sistema para fines personales, comerciales externos o cualquier actividad no autorizada por la empresa.</p>
            </div>
            
            <h3>2.2 Principios de Tratamiento de Datos</h3>
            <ul>
                <li><strong>Legalidad:</strong> Solo procesar datos con autorización expresa</li>
                <li><strong>Finalidad:</strong> Usar la información únicamente para funciones laborales</li>
                <li><strong>Calidad:</strong> Mantener datos actualizados, completos y precisos</li>
                <li><strong>Seguridad:</strong> Implementar medidas técnicas para proteger la información</li>
                <li><strong>Confidencialidad:</strong> Guardar secreto sobre la información manejada</li>
            </ul>
        </div>
        
        <div class="seccion">
            <h2>3. OPERACIONES CRUD (CREAR, LEER, ACTUALIZAR, ELIMINAR)</h2>
            
            <h3>3.1 Niveles de Permiso por Rol</h3>
            <p>El sistema HERMES cuenta con dos niveles administrativos:</p>
            
            <h4>Administrador General (Rol 1):</h4>
            <ul>
                <li>Acceso completo a todas las funcionalidades del sistema</li>
                <li>Capacidad de crear, leer, actualizar y eliminar cualquier registro</li>
                <li>Gestión de otros administradores y colaboradores</li>
                <li>Acceso a reportes completos y estadísticas detalladas</li>
                <li>Autorización para realizar operaciones masivas</li>
            </ul>
            
            <h4>Colaborador (Rol 2):</h4>
            <ul>
                <li>Acceso de solo lectura a la mayoría de módulos</li>
                <li>Capacidad limitada de actualización en áreas específicas</li>
                <li>Prohibición total para eliminar registros</li>
                <li>Sin acceso a información financiera sensible</li>
                <li>Supervisión de actividades por administrador general</li>
            </ul>
            
            <h3>3.2 Protocolos para Operaciones Críticas</h3>
            <p>Para ciertas operaciones, se requieren protocolos específicos:</p>
            
            <div class="importante">
                <p><strong>ELIMINACIÓN DE REGISTROS:</strong></p>
                <ul>
                    <li>Doble verificación de identidad (contraseña adicional)</li>
                    <li>Registro automático en bitácora con motivo de eliminación</li>
                    <li>Notificación inmediata al administrador supervisor</li>
                    <li>Creación de backup automático antes de eliminación</li>
                </ul>
            </div>
            
            <div class="importante">
                <p><strong>MODIFICACIÓN DE DATOS SENSIBLES:</strong></p>
                <ul>
                    <li>Registro de valor anterior y nuevo valor</li>
                    <li>Captura de motivo del cambio</li>
                    <li>En algunos casos, requiere autorización de segundo administrador</li>
                    <li>Notificación al usuario afectado (cuando aplica)</li>
                </ul>
            </div>
        </div>
        
        <div class="seccion">
            <h2>4. AUDITORÍA Y MONITOREO</h2>
            
            <h3>4.1 Registro de Actividades</h3>
            <p>Todas las acciones en el sistema son monitoreadas y registradas:</p>
            <ul>
                <li>Fecha y hora exacta de cada operación</li>
                <li>Usuario que realizó la acción</li>
                <li>Tipo de operación (consulta, creación, modificación, eliminación)</li>
                <li>Tabla y registro afectado</li>
                <li>Valores anteriores y nuevos (en modificaciones)</li>
                <li>Dirección IP desde donde se realizó la operación</li>
            </ul>
            
            <h3>4.2 Revisiones Periódicas</h3>
            <p>Se realizarán auditorías mensuales que incluyen:</p>
            <ul>
                <li>Revisión de accesos fuera de horario laboral</li>
                <li>Verificación de operaciones masivas o inusuales</li>
                <li>Análisis de intentos fallidos de acceso</li>
                <li>Revisión de eliminaciones de registros</li>
                <li>Evaluación de consultas a datos sensibles</li>
            </ul>
            
            <div class="advertencia">
                <p>El departamento de TI realizará revisiones aleatorias sin previo aviso. Todo administrador debe estar preparado para justificar sus actividades en el sistema.</p>
            </div>
        </div>
        
        <div class="seccion">
            <h2>5. SEGURIDAD INFORMÁTICA</h2>
            
            <h3>5.1 Medidas de Protección</h3>
            <ul>
                <li>Conexión cifrada SSL/TLS para todas las comunicaciones</li>
                <li>Protección contra inyecciones SQL y ataques XSS</li>
                <li>Validación de datos en frontend y backend</li>
                <li>Backups automáticos diarios de la base de datos</li>
                <li>Firewall de aplicación para prevenir accesos no autorizados</li>
                <li>Actualizaciones periódicas de seguridad</li>
            </ul>
            
            <h3>5.2 Responsabilidades del Administrador</h3>
            <ul>
                <li>No instalar software no autorizado en equipos de trabajo</li>
                <li>No acceder al sistema desde redes públicas no seguras</li>
                <li>Reportar inmediatamente cualquier comportamiento sospechoso</li>
                <li>Cerrar sesión al terminar actividades o al ausentarse del equipo</li>
                <li>No almacenar credenciales en navegadores o archivos de texto</li>
                <li>Mantener actualizado el antivirus en equipos de trabajo</li>
            </ul>
        </div>
        
        <div class="seccion">
            <h2>6. SANCIONES POR INCUMPLIMIENTO</h2>
            
            <h3>6.1 Clasificación de Infracciones</h3>
            
            <h4>Infracciones Leves:</h4>
            <ul>
                <li>Compartir credenciales temporalmente con supervisión</li>
                <li>No cambiar contraseña en plazo establecido</li>
                <li>Dejar sesión activa sin supervisión por menos de 10 minutos</li>
                <li><strong>Sanción:</strong> Amonestación verbal y capacitación obligatoria</li>
            </ul>
            
            <h4>Infracciones Graves:</h4>
            <ul>
                <li>Acceder a información fuera de sus funciones</li>
                <li>Compartir credenciales sin supervisión</li>
                <li>Realizar operaciones no autorizadas</li>
                <li>No reportar vulnerabilidades de seguridad</li>
                <li><strong>Sanción:</strong> Suspensión temporal y pérdida de privilegios</li>
            </ul>
            
            <h4>Infracciones Muy Graves:</h4>
            <ul>
                <li>Extracción masiva de datos confidenciales</li>
                <li>Modificación fraudulenta de registros</li>
                <li>Venta o distribución de información de la empresa</li>
                <li>Sabotaje o daño intencional al sistema</li>
                <li><strong>Sanción:</strong> Terminación de contrato y acciones legales</li>
            </ul>
            
            <div class="prohibido">
                <p><strong>ACUERDO DE CONFIDENCIALIDAD:</strong> Estas políticas constituyen un acuerdo de confidencialidad que se mantendrá vigente durante su empleo y por 5 años después de finalizada su relación laboral con HERMES.</p>
            </div>
        </div>
        
        <div class="seccion">
            <h2>7. CANALES DE REPORTE Y ASISTENCIA</h2>
            
            <h3>7.1 Soporte Técnico</h3>
            <ul>
                <li><strong>Correo electrónico:</strong> soporte.hermes@empresa.com</li>
                <li><strong>Teléfono interno:</strong> Extensión 505</li>
                <li><strong>Horario de atención:</strong> Lunes a Viernes, 8:00 AM - 6:00 PM</li>
                <li><strong>Emergencias:</strong> 24/7 al número corporativo</li>
            </ul>
            
            <h3>7.2 Reporte de Incidentes</h3>
            <p>En caso de detectar alguna irregularidad o vulnerabilidad:</p>
            <ul>
                <li>Reportar inmediatamente al supervisor directo</li>
                <li>Enviar correo a seguridadinformatica@empresa.com</li>
                <li>No intentar resolver problemas de seguridad por cuenta propia</li>
                <li>Documentar toda evidencia del incidente</li>
            </ul>
            
            <h3>7.3 Actualización de Políticas</h3>
            <p>Estas políticas podrán ser actualizadas periódicamente. Se notificará a todos los administradores por correo corporativo cuando ocurran cambios. Es responsabilidad de cada administrador mantenerse informado sobre las políticas vigentes.</p>
        </div>
    <script src="../scripts/admin.js"></script>
</body>
</html>