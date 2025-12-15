-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 15-12-2025 a las 01:03:43
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `hermes_bd`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividades_usuario`
--

CREATE TABLE `actividades_usuario` (
  `id_actividad` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `actividad` varchar(255) NOT NULL,
  `fecha` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `actividades_usuario`
--

INSERT INTO `actividades_usuario` (`id_actividad`, `id_usuario`, `actividad`, `fecha`) VALUES
(1, 2, 'Cambio de contraseña', '2025-12-14 19:50:47');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `administradores`
--

CREATE TABLE `administradores` (
  `id_admin` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `id_rol` int(11) DEFAULT NULL,
  `nombre_completo` varchar(100) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultimo_acceso` timestamp NULL DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `administradores`
--

INSERT INTO `administradores` (`id_admin`, `username`, `password`, `email`, `id_rol`, `nombre_completo`, `activo`, `ultimo_acceso`, `fecha_creacion`) VALUES
(1, 'admin_general', '$2y$10$BkiBc1f.PLPLYbo/pi3rbu65od3i/4UUtZAG1eR0Ci69YK3xzio.y', 'admin@hermes.com', 1, 'Administrador General', 1, '2025-12-14 02:29:02', '2025-11-28 05:39:28'),
(2, 'admin_colaborador', '$2y$10$gj/0iBf8jrU2M.mLt7GbKuYqCD7eDjbGULCCplVK2X46l901kI/8K', 'colab@hermes.com', 2, 'Administrador Colaborador', 1, '2025-12-02 15:36:39', '2025-11-28 05:39:28'),
(4, 'admin_general1', '$2y$10$U80eW8ZldM9Cvujb55Kl8OdHaXefmzHaozKxn2ppzpjKUiUqWm8Ki', 'admin@hermes.com', 1, NULL, 1, '2025-12-08 22:53:12', '2025-11-28 06:49:44'),
(5, 'Andres_David', '$2y$10$XP/d7usLEKm440y21xLp..nHpA/FXBhYq3rSGQW2t5pRW7x7h6Z0O', 'andr@gmail.com', 2, 'Andres David Carvajal Gutierrez', 1, '2025-12-02 15:28:25', '2025-11-28 18:16:14'),
(6, 'Andres_David1', '$2y$10$bhWhCISRPldYKdTC0tJoBORnyG1kESdYrryyrzPn7n8M6v3WZ70s2', 'andres@hgf.com', 2, 'Abdres Carvajal', 1, '2025-12-11 16:57:41', '2025-12-17 15:31:32');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billetera`
--

CREATE TABLE `billetera` (
  `id_billetera` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo_usuario` enum('cliente','vendedor','admin') DEFAULT 'cliente',
  `saldo_disponible` decimal(12,2) DEFAULT 0.00,
  `saldo_pendiente` decimal(12,2) DEFAULT 0.00,
  `saldo_bloqueado` decimal(12,2) DEFAULT 0.00,
  `moneda` varchar(3) DEFAULT 'COP',
  `estado` enum('activa','inactiva','bloqueada') DEFAULT 'activa',
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `billetera`
--

INSERT INTO `billetera` (`id_billetera`, `id_usuario`, `tipo_usuario`, `saldo_disponible`, `saldo_pendiente`, `saldo_bloqueado`, `moneda`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 31, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(2, 26, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(3, 6, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(4, 30, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(5, 25, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(6, 5, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(7, 28, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(8, 23, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(9, 3, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(10, 32, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(11, 27, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(12, 7, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(13, 29, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(14, 24, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(15, 4, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(16, 2, 'cliente', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(32, 2, 'vendedor', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL),
(33, 1, 'admin', 0.00, 0.00, 0.00, 'COP', 'activa', '2025-12-14 20:35:05', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billetera_metodo_retiro`
--

CREATE TABLE `billetera_metodo_retiro` (
  `id_metodo` int(11) NOT NULL,
  `id_billetera` int(11) NOT NULL,
  `tipo` enum('bancario','nequi','daviplata','paypal') NOT NULL,
  `datos` text NOT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billetera_solicitud_retiro`
--

CREATE TABLE `billetera_solicitud_retiro` (
  `id_solicitud` int(11) NOT NULL,
  `id_billetera` int(11) NOT NULL,
  `id_metodo` int(11) NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `comision` decimal(10,2) DEFAULT 0.00,
  `monto_neto` decimal(12,2) NOT NULL,
  `estado` enum('pendiente','aprobado','procesando','completado','rechazado') DEFAULT 'pendiente',
  `motivo_rechazo` varchar(255) DEFAULT NULL,
  `referencia_pago` varchar(100) DEFAULT NULL,
  `fecha_solicitud` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_procesado` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `billetera_transaccion`
--

CREATE TABLE `billetera_transaccion` (
  `id_transaccion` bigint(20) NOT NULL,
  `id_billetera` int(11) NOT NULL,
  `tipo` enum('recarga','compra','venta','retiro','comision','reembolso','transferencia') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_anterior` decimal(12,2) NOT NULL,
  `saldo_nuevo` decimal(12,2) NOT NULL,
  `referencia` varchar(100) DEFAULT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `estado` enum('pendiente','completado','fallido','reversado') DEFAULT 'pendiente',
  `metadata` text DEFAULT NULL,
  `fecha_transaccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito`
--

CREATE TABLE `carrito` (
  `id_carrito` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito`
--

INSERT INTO `carrito` (`id_carrito`, `id_cliente`, `fecha_creacion`, `total`) VALUES
(1, 28, '2025-12-01 21:53:40', 0.00),
(2, 29, '2025-12-01 21:53:40', 0.00),
(3, 30, '2025-12-01 21:53:40', 0.00),
(4, 31, '2025-12-01 21:53:40', 0.00),
(5, 32, '2025-12-01 21:53:40', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carrito_producto`
--

CREATE TABLE `carrito_producto` (
  `id_carrito` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carrito_producto`
--

INSERT INTO `carrito_producto` (`id_carrito`, `id_producto`, `cantidad`) VALUES
(1, 2, 1),
(1, 100, 1),
(2, 2, 1),
(2, 100, 1),
(3, 2, 2),
(4, 2, 1),
(5, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo`
--

CREATE TABLE `catalogo` (
  `id_catalogo` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  `nombre_catalogo` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `catalogo_producto`
--

CREATE TABLE `catalogo_producto` (
  `id_catalogo` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categoria`
--

CREATE TABLE `categoria` (
  `id_categoria` int(11) NOT NULL,
  `nombre_categoria` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `categoria`
--

INSERT INTO `categoria` (`id_categoria`, `nombre_categoria`) VALUES
(1, 'Electrónica'),
(2, 'Ropa y Accesorios'),
(3, 'Hogar y Jardín'),
(4, 'Deportes'),
(5, 'Belleza y Cuidado Personal'),
(6, 'Juguetes y Juegos'),
(7, 'Libros y Oficina'),
(8, 'Alimentos y Bebidas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `wishlist_privada` tinyint(1) DEFAULT 1,
  `informacion_adicional` text DEFAULT NULL,
  `direccion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `wishlist_privada`, `informacion_adicional`, `direccion`) VALUES
(28, 1, 'Cliente preferencial. Le gustan los productos electrónicos.', ''),
(29, 0, 'Compra frecuente de ropa y accesorios.', ''),
(30, 1, 'Prefiere envío express. Tiene alergia a frutos secos.', ''),
(31, 0, 'Solicita factura electrónica siempre.', ''),
(32, 1, 'Cliente empresarial. Contacto: departamento.compras@empresa.com', '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_pedido`
--

CREATE TABLE `detalle_pedido` (
  `id_detalle` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL DEFAULT 1,
  `precio_unitario` decimal(12,2) NOT NULL,
  `precio_total` decimal(12,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direcciones`
--

CREATE TABLE `direcciones` (
  `id_direccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `direccion` text NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `departamento` varchar(100) NOT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `referencias` varchar(255) DEFAULT NULL,
  `es_principal` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `nombre_direccion` varchar(100) DEFAULT 'Casa/Ofi',
  `pais` varchar(100) DEFAULT 'TuPaís',
  `estado` enum('activa','inactiva') DEFAULT 'activa'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direcciones`
--

INSERT INTO `direcciones` (`id_direccion`, `id_usuario`, `direccion`, `ciudad`, `departamento`, `codigo_postal`, `telefono`, `referencias`, `es_principal`, `fecha_creacion`, `nombre_direccion`, `pais`, `estado`) VALUES
(1, 2, 'dig 58 m bis #78-29 sur', 'Bogotá D.C.', 'Cundinamanrca', '13213151', '3153123165', '', 1, '2025-12-14 18:03:26', 'Casa/Ofi', 'TuPaís', 'activa'),
(2, 2, 'sadsadad', 'asdasdasdasd', 'sadada', 'asdsadad', '2131513131', '', 0, '2025-12-14 18:03:41', 'Casa/Ofi', 'TuPaís', 'activa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `direccion_envio`
--

CREATE TABLE `direccion_envio` (
  `id_direccion` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `alias` varchar(80) DEFAULT NULL,
  `direccion` varchar(255) NOT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `pais` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `direccion_envio`
--

INSERT INTO `direccion_envio` (`id_direccion`, `id_cliente`, `alias`, `direccion`, `ciudad`, `pais`, `codigo_postal`) VALUES
(1, 28, 'Casa', 'Calle 123 #45-67, Bogotá', 'Bogotá', NULL, '110111'),
(2, 28, 'Oficina', 'Carrera 15 #88-44, Piso 5', 'Bogotá', NULL, '110112'),
(3, 29, 'Apartamento', 'Avenida Siempre Viva 742', 'Medellín', NULL, '050001');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `metodos_pago`
--

CREATE TABLE `metodos_pago` (
  `id_metodo_pago` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('tarjeta_credito','tarjeta_debito','paypal','contra_entrega','billetera_virtual') NOT NULL,
  `nombre_titular` varchar(100) DEFAULT NULL,
  `numero_tarjeta` varchar(4) DEFAULT NULL,
  `fecha_vencimiento` varchar(7) DEFAULT NULL,
  `marca_tarjeta` varchar(20) DEFAULT NULL,
  `email_paypal` varchar(100) DEFAULT NULL,
  `es_predeterminado` tinyint(1) DEFAULT 0,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp(),
  `saldo_billetera` decimal(12,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `metodos_pago`
--

INSERT INTO `metodos_pago` (`id_metodo_pago`, `id_usuario`, `tipo`, `nombre_titular`, `numero_tarjeta`, `fecha_vencimiento`, `marca_tarjeta`, `email_paypal`, `es_predeterminado`, `fecha_creacion`, `saldo_billetera`) VALUES
(1, 2, 'paypal', NULL, NULL, NULL, NULL, 'sadsadasdasdasdasdasd@asdsad.com', 0, '2025-12-14 19:29:30', 0.00),
(2, 2, 'tarjeta_credito', 'Oscar asdad', '2131', '12/31', 'Visa', NULL, 1, '2025-12-14 19:31:13', 0.00),
(3, 31, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(4, 26, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(5, 6, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(6, 33, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(7, 30, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(8, 25, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(9, 5, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(10, 28, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(11, 23, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(12, 3, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(13, 32, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(14, 27, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(15, 7, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(16, 29, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(17, 24, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(18, 4, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00),
(19, 2, 'billetera_virtual', NULL, NULL, NULL, NULL, NULL, 0, '2025-12-14 22:45:55', 0.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pasarela_pago`
--

CREATE TABLE `pasarela_pago` (
  `id_pasarela` int(11) NOT NULL,
  `id_carrito` int(11) NOT NULL,
  `metodo_pago` varchar(100) NOT NULL,
  `estado_pago` enum('Pendiente','Completado','Fallido') DEFAULT 'Pendiente',
  `fecha_pago` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido`
--

CREATE TABLE `pedido` (
  `id_pedido` int(11) NOT NULL,
  `id_usuario` int(11) DEFAULT NULL,
  `id_vendedor` int(11) NOT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `subtotal` decimal(10,2) NOT NULL,
  `envio` decimal(10,2) NOT NULL DEFAULT 0.00,
  `iva` decimal(10,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Enviado','Entregado','Cancelado') DEFAULT 'Pendiente',
  `descripcion` varchar(500) DEFAULT NULL,
  `direccion_envio` text DEFAULT NULL,
  `metodo_pago` varchar(50) DEFAULT NULL,
  `llegada_estimada` date DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `codigo_postal` varchar(20) DEFAULT NULL,
  `es_contra_entrega` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_usuario`, `id_vendedor`, `fecha_pedido`, `total`, `subtotal`, `envio`, `iva`, `estado`, `descripcion`, `direccion_envio`, `metodo_pago`, `llegada_estimada`, `telefono_contacto`, `email_contacto`, `ciudad`, `departamento`, `codigo_postal`, `es_contra_entrega`) VALUES
(3, 28, 0, '2025-12-08 22:21:58', 4300000.00, 0.00, 0.00, 0.00, 'Pendiente', 'Compra de prueba: Laptop (ID 100) y Smartphone (ID 101).', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pedido_item`
--

CREATE TABLE `pedido_item` (
  `id_item` int(11) NOT NULL,
  `id_pedido` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_vendedor` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `nombre_producto` varchar(255) NOT NULL,
  `imagen_url` varchar(500) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `cloudinary_public_id` varchar(255) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `origen` varchar(100) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre`, `descripcion`, `imagen_url`, `cloudinary_public_id`, `precio`, `stock`, `origen`, `id_vendedor`, `fecha_creacion`) VALUES
(2, 'Camiseta Negra', 'Camiseta 100% algodón color negro', NULL, NULL, 35000.00, 20, 'Colombia', NULL, '2025-11-23 17:06:13'),
(100, 'Laptop HP Pavilion TEST', 'Laptop 15.6\", Intel i5, 8GB RAM, 512GB SSD', NULL, NULL, 2500000.00, 10, NULL, NULL, '2025-12-01 21:53:40'),
(101, 'Smartphone Samsung TEST', '6.5\", 128GB, 8GB RAM, Cámara Quad', NULL, NULL, 1800000.00, 15, NULL, NULL, '2025-12-01 21:53:40'),
(102, 'Audífonos Sony TEST', 'Audífonos inalámbricos con cancelación de ruido', NULL, NULL, 350000.00, 25, NULL, NULL, '2025-12-01 21:53:40'),
(103, 'Smartwatch Apple TEST', 'Series 7, GPS, 45mm, Resistente al agua', NULL, NULL, 2200000.00, 8, NULL, NULL, '2025-12-01 21:53:40'),
(104, 'Tablet Amazon TEST', '10\", 32GB, HD, Alexa integrado', NULL, NULL, 800000.00, 20, NULL, NULL, '2025-12-01 21:53:40'),
(105, 'teclado', 'teclado para huevadas', '../uploads/productos/1765497873_693b5c1182194.jpg', NULL, 20000.00, 50, 'logitech', NULL, '2025-12-12 00:04:33'),
(106, 'asdada', 'asdasdad', 'https://res.cloudinary.com/dwetjdmaz/image/upload/v1765677813/hermes_bd/productos/vendedor_2/producto_1765677810_chart__2_.png', 'hermes_bd/productos/vendedor_2/producto_1765677810_chart__2_', 21313.00, 123132123, 'asdadasdad', 2, '2025-12-14 02:03:32'),
(107, 'asdada', 'asdasdad', 'https://res.cloudinary.com/dwetjdmaz/image/upload/v1765677815/hermes_bd/productos/vendedor_2/producto_1765677813_chart__2_.png', 'hermes_bd/productos/vendedor_2/producto_1765677813_chart__2_', 21313.00, 123132123, 'asdadasdad', 2, '2025-12-14 02:03:35');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_categoria`
--

CREATE TABLE `producto_categoria` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto_categoria`
--

INSERT INTO `producto_categoria` (`id_producto`, `id_categoria`) VALUES
(106, 7),
(107, 7);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id_rol` int(11) NOT NULL,
  `nombre_rol` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id_rol`, `nombre_rol`) VALUES
(1, 'administrador'),
(2, 'admin_colaborador'),
(3, 'cliente'),
(4, 'vendedor');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones_usuario`
--

CREATE TABLE `sesiones_usuario` (
  `id_sesion` varchar(128) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `ip` varchar(45) DEFAULT NULL,
  `dispositivo` text DEFAULT NULL,
  `fecha_inicio` timestamp NOT NULL DEFAULT current_timestamp(),
  `fecha_ultima_actividad` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transacciones_billetera`
--

CREATE TABLE `transacciones_billetera` (
  `id_transaccion` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `tipo` enum('recarga','compra','devolucion') NOT NULL,
  `monto` decimal(12,2) NOT NULL,
  `saldo_anterior` decimal(12,2) NOT NULL,
  `saldo_nuevo` decimal(12,2) NOT NULL,
  `descripcion` varchar(255) DEFAULT NULL,
  `id_pedido` int(11) DEFAULT NULL,
  `fecha_transaccion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `id_usuario` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellido` varchar(50) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `contrasena` varchar(255) NOT NULL,
  `fecha_nacimiento` date NOT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `direccion_principal` varchar(255) DEFAULT NULL,
  `codigo_recuperacion` varchar(10) DEFAULT NULL,
  `codigo_expira` datetime DEFAULT NULL,
  `two_factor_auth` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `fecha_nacimiento`, `telefono`, `direccion_principal`, `codigo_recuperacion`, `codigo_expira`, `two_factor_auth`) VALUES
(2, 'Oscar', 'asdad', 'oscar.vanegas772@gmail.com', '$2y$10$C/QYRLXVPzpaTICGwEkvwegipIdeBK3vk1y1Bla/4hOB5uL9M1QwS', '1111-11-11', '121213131', 'masmdasmdma', '392943', '2025-11-19 21:43:45', 0),
(3, 'Juan', 'Pérez', 'juan.perez@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL, 0),
(4, 'María', 'Gómez', 'maria.gomez@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL, 0),
(5, 'Carlos', 'Rodríguez', 'carlos.rod@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL, 0),
(6, 'Ana', 'Martínez', 'ana.martinez@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL, 0),
(7, 'Luis', 'Hernández', 'luis.hernandez@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL, 0),
(23, 'Juan', 'Pérez', 'juan.perez2@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL, 0),
(24, 'María', 'Gómez', 'maria.gomez2@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL, 0),
(25, 'Carlos', 'Rodríguez', 'carlos.rod2@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL, 0),
(26, 'Ana', 'Martínez', 'ana.martinez2@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL, 0),
(27, 'Luis', 'Hernández', 'luis.hernandez2@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL, 0),
(28, 'Juan', 'Pérez', 'juan.perez.test@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL, 0),
(29, 'María', 'Gómez', 'maria.gomez.test@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL, 0),
(30, 'Carlos', 'Rodríguez', 'carlos.rod.test@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL, 0),
(31, 'Ana', 'Martínez', 'ana.martinez.test@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL, 0),
(32, 'Luis', 'Hernández', 'luis.hernandez.test@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL, 0),
(33, 'andres', 'carvajal', 'andres@gmail.com', '$2y$10$8QmfFCTqtL6OG7Tta9HPUuE1Unt0KZgrP/cE7kgyrZtzx23.DLCw6', '1991-12-14', '', NULL, NULL, NULL, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_rol`
--

CREATE TABLE `usuario_rol` (
  `id_usuario` int(11) NOT NULL,
  `id_rol` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vendedor`
--

CREATE TABLE `vendedor` (
  `id_vendedor` int(11) NOT NULL,
  `nombre_empresa` varchar(150) DEFAULT NULL,
  `nit` varchar(50) DEFAULT NULL,
  `telefono_contacto` varchar(20) DEFAULT NULL,
  `ubicacion` varchar(255) DEFAULT NULL,
  `correo_contacto` varchar(150) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp(),
  `acepto_terminos` tinyint(4) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `vendedor`
--

INSERT INTO `vendedor` (`id_vendedor`, `nombre_empresa`, `nit`, `telefono_contacto`, `ubicacion`, `correo_contacto`, `fecha_registro`, `acepto_terminos`) VALUES
(2, 'sadadadad', 'asdasdadasd', '313132131', '31a3132131ads', '', '2025-11-19 05:00:00', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `wishlist`
--

CREATE TABLE `wishlist` (
  `id_cliente` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `fecha_agregado` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividades_usuario`
--
ALTER TABLE `actividades_usuario`
  ADD PRIMARY KEY (`id_actividad`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_rol` (`id_rol`);

--
-- Indices de la tabla `billetera`
--
ALTER TABLE `billetera`
  ADD PRIMARY KEY (`id_billetera`),
  ADD UNIQUE KEY `uk_usuario_tipo` (`id_usuario`,`tipo_usuario`);

--
-- Indices de la tabla `billetera_metodo_retiro`
--
ALTER TABLE `billetera_metodo_retiro`
  ADD PRIMARY KEY (`id_metodo`),
  ADD KEY `idx_billetera_metodo` (`id_billetera`);

--
-- Indices de la tabla `billetera_solicitud_retiro`
--
ALTER TABLE `billetera_solicitud_retiro`
  ADD PRIMARY KEY (`id_solicitud`);

--
-- Indices de la tabla `billetera_transaccion`
--
ALTER TABLE `billetera_transaccion`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD KEY `idx_billetera` (`id_billetera`),
  ADD KEY `idx_referencia` (`referencia`);

--
-- Indices de la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD PRIMARY KEY (`id_carrito`),
  ADD KEY `fk_carrito_cliente` (`id_cliente`);

--
-- Indices de la tabla `carrito_producto`
--
ALTER TABLE `carrito_producto`
  ADD PRIMARY KEY (`id_carrito`,`id_producto`),
  ADD KEY `fk_cp_producto2` (`id_producto`);

--
-- Indices de la tabla `catalogo`
--
ALTER TABLE `catalogo`
  ADD PRIMARY KEY (`id_catalogo`),
  ADD KEY `fk_catalogo_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `catalogo_producto`
--
ALTER TABLE `catalogo_producto`
  ADD PRIMARY KEY (`id_catalogo`,`id_producto`),
  ADD KEY `fk_cp_producto` (`id_producto`);

--
-- Indices de la tabla `categoria`
--
ALTER TABLE `categoria`
  ADD PRIMARY KEY (`id_categoria`);

--
-- Indices de la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD PRIMARY KEY (`id_cliente`);

--
-- Indices de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD PRIMARY KEY (`id_detalle`),
  ADD KEY `fk_detalle_pedido` (`id_pedido`),
  ADD KEY `fk_detalle_producto` (`id_producto`);

--
-- Indices de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `idx_usuario_principal` (`id_usuario`,`es_principal`);

--
-- Indices de la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `fk_dir_cliente` (`id_cliente`);

--
-- Indices de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD PRIMARY KEY (`id_metodo_pago`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `pasarela_pago`
--
ALTER TABLE `pasarela_pago`
  ADD PRIMARY KEY (`id_pasarela`),
  ADD UNIQUE KEY `id_carrito` (`id_carrito`);

--
-- Indices de la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD PRIMARY KEY (`id_pedido`),
  ADD KEY `fk_pedido_usuario` (`id_usuario`),
  ADD KEY `id_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `pedido_item`
--
ALTER TABLE `pedido_item`
  ADD PRIMARY KEY (`id_item`),
  ADD KEY `idx_pedido` (`id_pedido`),
  ADD KEY `idx_vendedor` (`id_vendedor`),
  ADD KEY `idx_producto` (`id_producto`);

--
-- Indices de la tabla `producto`
--
ALTER TABLE `producto`
  ADD PRIMARY KEY (`id_producto`),
  ADD KEY `fk_producto_vendedor` (`id_vendedor`);

--
-- Indices de la tabla `producto_categoria`
--
ALTER TABLE `producto_categoria`
  ADD PRIMARY KEY (`id_producto`,`id_categoria`),
  ADD KEY `fk_pc_categoria` (`id_categoria`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id_rol`);

--
-- Indices de la tabla `sesiones_usuario`
--
ALTER TABLE `sesiones_usuario`
  ADD PRIMARY KEY (`id_sesion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `transacciones_billetera`
--
ALTER TABLE `transacciones_billetera`
  ADD PRIMARY KEY (`id_transaccion`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD PRIMARY KEY (`id_usuario`,`id_rol`),
  ADD KEY `fk_ur_rol` (`id_rol`);

--
-- Indices de la tabla `vendedor`
--
ALTER TABLE `vendedor`
  ADD PRIMARY KEY (`id_vendedor`);

--
-- Indices de la tabla `wishlist`
--
ALTER TABLE `wishlist`
  ADD PRIMARY KEY (`id_cliente`,`id_producto`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividades_usuario`
--
ALTER TABLE `actividades_usuario`
  MODIFY `id_actividad` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `billetera`
--
ALTER TABLE `billetera`
  MODIFY `id_billetera` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `billetera_metodo_retiro`
--
ALTER TABLE `billetera_metodo_retiro`
  MODIFY `id_metodo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `billetera_solicitud_retiro`
--
ALTER TABLE `billetera_solicitud_retiro`
  MODIFY `id_solicitud` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `billetera_transaccion`
--
ALTER TABLE `billetera_transaccion`
  MODIFY `id_transaccion` bigint(20) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `carrito`
--
ALTER TABLE `carrito`
  MODIFY `id_carrito` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `catalogo`
--
ALTER TABLE `catalogo`
  MODIFY `id_catalogo` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `categoria`
--
ALTER TABLE `categoria`
  MODIFY `id_categoria` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  MODIFY `id_detalle` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `direcciones`
--
ALTER TABLE `direcciones`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  MODIFY `id_metodo_pago` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT de la tabla `pasarela_pago`
--
ALTER TABLE `pasarela_pago`
  MODIFY `id_pasarela` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pedido`
--
ALTER TABLE `pedido`
  MODIFY `id_pedido` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `pedido_item`
--
ALTER TABLE `pedido_item`
  MODIFY `id_item` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=108;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `transacciones_billetera`
--
ALTER TABLE `transacciones_billetera`
  MODIFY `id_transaccion` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `actividades_usuario`
--
ALTER TABLE `actividades_usuario`
  ADD CONSTRAINT `actividades_usuario_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD CONSTRAINT `administradores_ibfk_1` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`);

--
-- Filtros para la tabla `carrito`
--
ALTER TABLE `carrito`
  ADD CONSTRAINT `fk_carrito_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `carrito_producto`
--
ALTER TABLE `carrito_producto`
  ADD CONSTRAINT `fk_cp_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cp_producto2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `catalogo`
--
ALTER TABLE `catalogo`
  ADD CONSTRAINT `fk_catalogo_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedor` (`id_vendedor`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `catalogo_producto`
--
ALTER TABLE `catalogo_producto`
  ADD CONSTRAINT `fk_cp_catalogo` FOREIGN KEY (`id_catalogo`) REFERENCES `catalogo` (`id_catalogo`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_cp_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cliente`
--
ALTER TABLE `cliente`
  ADD CONSTRAINT `fk_cliente_usuario` FOREIGN KEY (`id_cliente`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `detalle_pedido`
--
ALTER TABLE `detalle_pedido`
  ADD CONSTRAINT `fk_detalle_pedido` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_detalle_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON UPDATE CASCADE;

--
-- Filtros para la tabla `direcciones`
--
ALTER TABLE `direcciones`
  ADD CONSTRAINT `direcciones_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`);

--
-- Filtros para la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  ADD CONSTRAINT `fk_dir_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `metodos_pago`
--
ALTER TABLE `metodos_pago`
  ADD CONSTRAINT `metodos_pago_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pasarela_pago`
--
ALTER TABLE `pasarela_pago`
  ADD CONSTRAINT `fk_pasarela_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido_item`
--
ALTER TABLE `pedido_item`
  ADD CONSTRAINT `pedido_item_ibfk_1` FOREIGN KEY (`id_pedido`) REFERENCES `pedido` (`id_pedido`) ON DELETE CASCADE,
  ADD CONSTRAINT `pedido_item_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`),
  ADD CONSTRAINT `pedido_item_ibfk_3` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedor` (`id_vendedor`);

--
-- Filtros para la tabla `transacciones_billetera`
--
ALTER TABLE `transacciones_billetera`
  ADD CONSTRAINT `transacciones_billetera_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
