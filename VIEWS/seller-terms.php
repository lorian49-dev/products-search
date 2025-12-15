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
    <title>Terminos para los vendedores</title>
    <link rel="stylesheet" href="../styles/admin-user-crud.css">
         <link rel="shortcut icon" href="../SOURCES/ICONOS-LOGOS/ico.ico" type="image/x-icon">
    <link rel="stylesheet" href="../SOURCES/ICONOS-LOGOS/fontawesome-free-7.1.0-web/css/all.css"> 
</head>
<body>
    <nav id="navegation">
        <a href="../CONTROLLERS/user-dashboard-admin.php"><i class="fas fa-home" id="iconHome"></i></a>
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
                <a href="politics-admin.php">
                    <li>politicas de privacidad y uso</li>
                </a>
                 <li class="current-page">Terminos para vendedores</li>
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
    <h1>POLÍTICAS COMERCIALES - PROGRAMA DE VENDEDORES HERMES</h1>
    
    <div class="seccion">
        <h2>1. PROGRAMA DE BIENVENIDA PARA NUEVOS VENDEDORES</h2>
        
        <h3>1.1 Bonos Generosos de Inicio</h3>
        <p>Todo vendedor nuevo que se registre en HERMES Click and Go recibe beneficios especiales diseñados para facilitar su inicio en la plataforma.</p>
        
        <ul>
            <li><strong>Bono de Registro:</strong> $50,000 COP otorgados automáticamente al completar el proceso de verificación</li>
            <li><strong>Bono de Primera Venta:</strong> $20,000 COP adicionales al realizar su primera transacción exitosa</li>
            <li><strong>Crédito Promocional:</strong> Hasta $100,000 COP en crédito para publicidad dentro de la plataforma</li>
            <li><strong>Asesoría Gratuita:</strong> 3 sesiones de consultoría comercial con expertos de HERMES</li>
        </ul>
        
        <div class="importante">
            <p><strong>IMPORTANTE:</strong> Los bonos son acumulables y se acreditan automáticamente en la cuenta del vendedor dentro de las 24 horas posteriores a cumplir los requisitos.</p>
        </div>
        
        <h3>1.2 Requisitos para Acceder a los Bonos</h3>
        <p>Para recibir los beneficios del programa de bienvenida, el vendedor debe:</p>
        <ul>
            <li>Completar el 100% del perfil de vendedor con información verificable</li>
            <li>Subir mínimo 5 productos activos en su catálogo</li>
            <li>Validar su identidad con documento oficial</li>
            <li>Aceptar los términos y condiciones comerciales</li>
            <li>Configurar método de pago para retiros</li>
        </ul>
    </div>
    
    <div class="seccion">
        <h2>2. ESTRUCTURA DE COMISIONES POR TIEMPO</h2>
        
        <h3>2.1 Período de Inicio (0-3 meses)</h3>
        <p>Durante los primeros 90 días como vendedor en HERMES, se aplica una tarifa reducida de comisión:</p>
        
        <div class="importante">
            <p><strong>COMISIÓN ESPECIAL DE INICIO: 3%</strong></p>
            <ul>
                <li>Aplicable a todas las ventas realizadas durante los primeros 3 meses</li>
                <li>No incluye costos adicionales de envío o procesamiento de pago</li>
                <li>Calculada sobre el valor total de la transacción (antes de impuestos)</li>
                <li>Deducida automáticamente al momento del pago al vendedor</li>
            </ul>
        </div>
        
        <h3>2.2 Período Estándar (a partir del 4° mes)</h3>
        <p>Una vez superado el período de inicio, se aplica la tarifa estándar de comisión:</p>
        
        <div class="importante">
            <p><strong>COMISIÓN ESTÁNDAR: 8%</strong></p>
            <ul>
                <li>Entra en vigor automáticamente al día 91 de actividad</li>
                <li>Se aplica retroactivamente desde el primer día del mes 4</li>
                <li>Incluye mantenimiento de perfil, soporte comercial y acceso a herramientas</li>
                <li>Los bonos iniciales NO son recuperables al cambiar a esta comisión</li>
            </ul>
        </div>
        
        <h3>2.3 Excepciones y Casos Especiales</h3>
        <ul>
            <li><strong>Vendedores Premium:</strong> Quienes superen $10,000,000 COP mensuales en ventas podrán negociar comisión del 6%</li>
            <li><strong>Categorías Especiales:</strong> Productos digitales y servicios tienen comisión fija del 5%</li>
            <li><strong>Promociones Temporales:</strong> Durante eventos especiales, HERMES podrá reducir temporalmente las comisiones</li>
            <li><strong>Ventas Internacionales:</strong> Aplican comisiones del 10% por mayor complejidad logística</li>
        </ul>
    </div>
    
    <div class="seccion">
        <h2>3. PROCESO DE REGISTRO Y VERIFICACIÓN</h2>
        
        <h3>3.1 Flujo de Alta de Nuevo Vendedor</h3>
        <p>Como administrador, debe seguir este proceso para registrar nuevos vendedores:</p>
        
        <ul>
            <li><strong>Paso 1:</strong> Recopilar documentación completa (cédula, RUT, certificado bancario)</li>
            <li><strong>Paso 2:</strong> Crear perfil en sistema con datos básicos</li>
            <li><strong>Paso 3:</strong> Asignar categoría "Nuevo Vendedor - Período de Inicio"</li>
            <li><strong>Paso 4:</strong> Configurar comisión automática al 3% por 90 días</li>
            <li><strong>Paso 5:</strong> Activar bonos automáticos según calendario establecido</li>
            <li><strong>Paso 6:</strong> Programar notificación de cambio a comisión del 8% (día 85)</li>
        </ul>
        
        <h3>3.2 Documentación Requerida</h3>
        <div class="advertencia">
            <p><strong>DOCUMENTACIÓN OBLIGATORIA PARA VENDEDORES:</strong></p>
            <ul>
                <li>Cédula de ciudadanía vigente (ambos lados)</li>
                <li>Registro Único Tributario (RUT) actualizado</li>
                <li>Certificación bancaria con cuenta corriente o de ahorros</li>
                <li>Formulario de declaración de origen de productos</li>
                <li>Permisos sanitarios (para categorías específicas)</li>
                <li>Autorización de tratamiento de datos personales</li>
            </ul>
        </div>
    </div>
    
    <div class="seccion">
        <h2>4. GESTIÓN DE COMISIONES Y PAGOS</h2>
        
        <h3>4.1 Cálculo Automático de Comisiones</h3>
        <p>El sistema HERMES calcula automáticamente las comisiones según el período del vendedor:</p>
        
        <ul>
            <li><strong>Período de Inicio (días 1-90):</strong> Ventas × 0.03 = Comisión</li>
            <li><strong>Período Estándar (día 91+):</strong> Ventas × 0.08 = Comisión</li>
            <li><strong>Ejemplo Práctico:</strong> Venta de $500,000 COP en mes 2 → Comisión: $15,000 COP</li>
            <li><strong>Ejemplo Práctico:</strong> Venta de $500,000 COP en mes 4 → Comisión: $40,000 COP</li>
        </ul>
        
        <h3>4.2 Proceso de Pago a Vendedores</h3>
        <p>Los pagos se realizan bajo el siguiente esquema:</p>
        
        <div class="importante">
            <p><strong>CICLO DE PAGOS:</strong></p>
            <ul>
                <li><strong>Frecuencia:</strong> Pagos quincenales (días 15 y 30 de cada mes)</li>
                <li><strong>Mínimo para retiro:</strong> $100,000 COP acumulados</li>
                <li><strong>Procesamiento:</strong> 3-5 días hábiles después de solicitud</li>
                <li><strong>Métodos:</strong> Transferencia bancaria, Nequi, Daviplata</li>
                <li><strong>Comisión por retiro:</strong> $2,500 COP por transacción (excepto primera del mes)</li>
            </ul>
        </div>
        
        <h3>4.3 Manejo de Transiciones de Período</h3>
        <p>Cuando un vendedor completa sus 90 días iniciales:</p>
        
        <ul>
            <li>Sistema envía notificación automática 5 días antes del cambio</li>
            <li>Administrador debe verificar que no haya ventas pendientes de procesar</li>
            <li>Se genera reporte de transición con resumen de período inicial</li>
            <li>Se actualiza automáticamente la categoría a "Vendedor Estándar"</li>
            <li>Se modifica la tasa de comisión en todos los productos activos</li>
        </ul>
    </div>
    
    <div class="seccion">
        <h2>5. BENEFICIOS ADICIONALES PARA VENDEDORES</h2>
        
        <h3>5.1 Herramientas Incluidas</h3>
        <p>Todos los vendedores reciben acceso a:</p>
        
        <ul>
            <li><strong>Dashboard Analítico:</strong> Métricas de ventas, clientes y productos</li>
            <li><strong>Gestor de Inventario:</strong> Control de stock y alertas de reposición</li>
            <li><strong>Generador de Etiquetas:</strong> Creación automática de etiquetas de envío</li>
            <li><strong>Chat Integrado:</strong> Comunicación directa con compradores</li>
            <li><strong>Plantillas Profesionales:</strong> Diseños para descripciones de productos</li>
            <li><strong>API de Integración:</strong> Para vendedores con sistemas propios</li>
        </ul>
        
        <h3>5.2 Soporte y Capacitación</h3>
        <ul>
            <li><strong>Soporte Prioritario:</strong> Atención en menos de 2 horas para nuevos vendedores</li>
            <li><strong>Webinars Semanales:</strong> Capacitación en marketing digital y ventas online</li>
            <li><strong>Biblioteca de Recursos:</strong> Guías, tutoriales y plantillas descargables</li>
            <li><strong>Asesor Personal:</strong> Asignado durante los primeros 30 días</li>
            <li><strong>Grupo de Networking:</strong> Comunidad exclusiva de vendedores HERMES</li>
        </ul>
        
        <div class="importante">
            <p><strong>NOTA PARA ADMINISTRADORES:</strong> Es su responsabilidad informar a los vendedores sobre todos los beneficios disponibles y asegurar que los utilicen correctamente.</p>
        </div>
    </div>
    
    <div class="seccion">
        <h2>6. SEGUIMIENTO Y MÉTRICAS DE DESEMPEÑO</h2>
        
        <h3>6.1 Indicadores Clave (KPIs) para Nuevos Vendedores</h3>
        <p>Monitoree estos indicadores en el dashboard administrativo:</p>
        
        <ul>
            <li><strong>Tasa de Conversión Inicial:</strong> % de visitas que se convierten en ventas (meta: 2%)</li>
            <li><strong>Tiempo a Primera Venta:</strong> Días desde registro hasta primera transacción (meta: 7 días)</li>
            <li><strong>Valor Promedio de Ticket:</strong> Monto promedio por venta (meta: $150,000 COP)</li>
            <li><strong>Retención a 90 días:</strong> % de vendedores activos después de 3 meses (meta: 65%)</li>
            <li><strong>Satisfacción del Comprador:</strong> Calificación promedio recibida (meta: 4.5/5)</li>
        </ul>
        
        <h3>6.2 Alertas Automáticas del Sistema</h3>
        <p>HERMES genera alertas cuando:</p>
        
        <div class="advertencia">
            <ul>
                <li>Un vendedor está por cumplir 85 días (cambio próximo de comisión)</li>
                <li>Bono de primera venta no ha sido reclamado en 15 días</li>
                <li>Vendedor tiene ventas por debajo del promedio de su categoría</li>
                <li>Se detectan patrones sospechosos de actividad</li>
                <li>Documentación está por vencer o está incompleta</li>
            </ul>
        </div>
        
        <h3>6.3 Reportes Mensuales Obligatorios</h3>
        <p>Cada administrador debe generar y revisar:</p>
        
        <ul>
            <li><strong>Reporte de Nuevos Vendedores:</strong> Análisis mensual de incorporaciones</li>
            <li><strong>Eficiencia de Bonos:</strong> ROI de bonos otorgados vs ventas generadas</li>
            <li><strong>Transiciones Exitosas:</strong> % de vendedores que mantienen actividad después del cambio a 8%</li>
            <li><strong>Incidentes y Reclamos:</strong> Reporte de problemas con nuevos vendedores</li>
            <li><strong>Proyección de Comisiones:</strong> Estimado de ingresos por comisiones futuras</li>
        </ul>
    </div>
    
    <div class="seccion">
        <h2>7. POLÍTICAS ESPECIALES Y EXCEPCIONES</h2>
        
        <h3>7.1 Extensión del Período de Inicio</h3>
        <p>En casos excepcionales, se puede extender el período al 3% de comisión:</p>
        
        <ul>
            <li><strong>Causales Aprobables:</strong> Fuerza mayor comprobada, problemas de salud, desastres naturales</li>
            <li><strong>Proceso:</strong> Solicitud formal del vendedor + documentación soporte + aprobación de gerencia</li>
            <li><strong>Límite:</strong> Máximo 30 días adicionales (120 días total)</li>
            <li><strong>Condición:</strong> Vendedor debe tener al menos 3 ventas en período inicial</li>
            <li><strong>Documentación:</strong> Acta de extensión firmada por ambas partes</li>
        </ul>
        
        <h3>7.2 Devolución de Bonos</h3>
        <div class="prohibido">
            <p><strong>CONDICIONES PARA DEVOLUCIÓN DE BONOS:</strong></p>
            <ul>
                <li>Si el vendedor abandona la plataforma antes de 6 meses</li>
                <li>En caso de actividades fraudulentas o violación de términos</li>
                <li>Si se detecta creación de múltiples cuentas para recibir bonos</li>
                <li>Cuando el vendedor no completa la documentación requerida</li>
                <li>En situaciones de incumplimiento contractual grave</li>
            </ul>
            <p>La devolución se realiza deduciendo el monto de los pagos pendientes o generando factura por el saldo.</p>
        </div>
        
        <h3>7.3 Actualización de Políticas Comerciales</h3>
        <p>Estas políticas para vendedores se revisan trimestralmente y pueden ser actualizadas. Los cambios serán comunicados con 30 días de anticipación a los vendedores afectados. Los administradores deben estar atentos a las actualizaciones y aplicarlas consistentemente.</p>
        
        <div class="aceptacion">
            <p><strong>Última actualización:</strong> <?php echo date('d/m/Y'); ?></p>
            <p><strong>Vigencia:</strong> Estas políticas aplican para todos los vendedores registrados a partir del 01/01/2024</p>
            <p><strong>Responsable:</strong> Departamento Comercial HERMES Click and Go</p>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Cálculo interactivo de comisiones
    const calcularComisiones = () => {
        const ventaInput = document.getElementById('venta-ejemplo');
        const periodoSelect = document.getElementById('periodo-ejemplo');
        const resultadoDiv = document.getElementById('resultado-calculo');
        
        if (ventaInput && periodoSelect && resultadoDiv) {
            const monto = parseFloat(ventaInput.value) || 0;
            const periodo = periodoSelect.value;
            
            let comisionPorcentaje, comisionMonto;
            
            if (periodo === 'inicio') {
                comisionPorcentaje = 3;
                comisionMonto = monto * 0.03;
            } else {
                comisionPorcentaje = 8;
                comisionMonto = monto * 0.08;
            }
            
            const pagoVendedor = monto - comisionMonto;
            
            resultadoDiv.innerHTML = `
                <h4>Resultado del cálculo:</h4>
                <p><strong>Venta:</strong> $${monto.toLocaleString('es-CO')} COP</p>
                <p><strong>Comisión (${comisionPorcentaje}%):</strong> $${comisionMonto.toLocaleString('es-CO')} COP</p>
                <p><strong>Pago al vendedor:</strong> $${pagoVendedor.toLocaleString('es-CO')} COP</p>
                <p><strong>Período:</strong> ${periodo === 'inicio' ? 'Inicio (0-3 meses)' : 'Estándar (4+ meses)'}</p>
            `;
        }
    };
    
    // Agregar calculadora si no existe
    if (!document.getElementById('calculadora-comisiones')) {
        const seccionComisiones = document.querySelector('[id*="comision"]');
        if (seccionComisiones) {
            const calculadoraHTML = `
                <div class="importante" id="calculadora-comisiones">
                    <h3>Calculadora de Comisiones</h3>
                    <div style="display: flex; gap: 15px; margin: 15px 0; flex-wrap: wrap;">
                        <div>
                            <label>Monto de venta (COP):</label><br>
                            <input type="number" id="venta-ejemplo" value="500000" min="0" style="padding: 8px; width: 200px;">
                        </div>
                        <div>
                            <label>Período del vendedor:</label><br>
                            <select id="periodo-ejemplo" style="padding: 8px; width: 200px;">
                                <option value="inicio">Inicio (3%)</option>
                                <option value="estandar">Estándar (8%)</option>
                            </select>
                        </div>
                        <div style="align-self: flex-end;">
                            <button onclick="calcularComisiones()" style="padding: 8px 15px; background: #1a237e; color: white; border: none; border-radius: 4px; cursor: pointer;">
                                Calcular
                            </button>
                        </div>
                    </div>
                    <div id="resultado-calculo" style="background: white; padding: 15px; border-radius: 4px; margin-top: 10px;"></div>
                </div>
            `;
            seccionComisiones.insertAdjacentHTML('beforeend', calculadoraHTML);
            
            // Calcular ejemplo inicial
            setTimeout(calcularComisiones, 100);
        }
    }
    
    // Hacer la función disponible globalmente
    window.calcularComisiones = calcularComisiones;
});
</script>
    <script src="../scripts/admin.js"></script>
</body>
</html>