-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 11-12-2025 a las 18:17:53
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
(1, 'admin_general', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@hermes.com', 1, 'Administrador General', 1, NULL, '2025-11-28 05:39:28'),
(2, 'admin_colaborador', '$2y$10$gj/0iBf8jrU2M.mLt7GbKuYqCD7eDjbGULCCplVK2X46l901kI/8K', 'colab@hermes.com', 2, 'Administrador Colaborador', 1, '2025-12-02 15:36:39', '2025-11-28 05:39:28'),
(4, 'admin_general1', '$2y$10$U80eW8ZldM9Cvujb55Kl8OdHaXefmzHaozKxn2ppzpjKUiUqWm8Ki', 'admin@hermes.com', 1, NULL, 1, '2025-12-08 22:53:12', '2025-11-28 06:49:44'),
(5, 'Andres_David', '$2y$10$XP/d7usLEKm440y21xLp..nHpA/FXBhYq3rSGQW2t5pRW7x7h6Z0O', 'andr@gmail.com', 2, 'Andres David Carvajal Gutierrez', 1, '2025-12-02 15:28:25', '2025-11-28 18:16:14'),
(6, 'Andres_David1', '$2y$10$bhWhCISRPldYKdTC0tJoBORnyG1kESdYrryyrzPn7n8M6v3WZ70s2', 'andres@hgf.com', 2, 'Abdres Carvajal', 1, '2025-12-11 16:57:41', '2025-12-17 15:31:32');

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
(8, 'Alimentos y Bebidas'),
(9, 'Electrónica'),
(10, 'Ropa y Accesorios'),
(11, 'Hogar y Jardín'),
(12, 'Deportes'),
(13, 'Belleza y Cuidado Personal'),
(14, 'Juguetes y Juegos'),
(15, 'Libros y Oficina'),
(16, 'Alimentos y Bebidas');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cliente`
--

CREATE TABLE `cliente` (
  `id_cliente` int(11) NOT NULL,
  `wishlist_privada` tinyint(1) DEFAULT 1,
  `informacion_adicional` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `cliente`
--

INSERT INTO `cliente` (`id_cliente`, `wishlist_privada`, `informacion_adicional`) VALUES
(28, 1, 'Cliente preferencial. Le gustan los productos electrónicos.'),
(29, 0, 'Compra frecuente de ropa y accesorios.'),
(30, 1, 'Prefiere envío express. Tiene alergia a frutos secos.'),
(31, 0, 'Solicita factura electrónica siempre.'),
(32, 1, 'Cliente empresarial. Contacto: departamento.compras@empresa.com');

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
  `id_cliente` int(11) DEFAULT NULL,
  `fecha_pedido` timestamp NOT NULL DEFAULT current_timestamp(),
  `total` decimal(12,2) NOT NULL DEFAULT 0.00,
  `estado` enum('Pendiente','Enviado','Entregado','Cancelado') DEFAULT 'Pendiente',
  `descripcion` varchar(500) DEFAULT NULL,
  `llegada_estimada` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pedido`
--

INSERT INTO `pedido` (`id_pedido`, `id_usuario`, `id_cliente`, `fecha_pedido`, `total`, `estado`, `descripcion`, `llegada_estimada`) VALUES
(3, 28, 28, '2025-12-08 22:21:58', 4300000.00, 'Pendiente', 'Compra de prueba: Laptop (ID 100) y Smartphone (ID 101).', NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto`
--

CREATE TABLE `producto` (
  `id_producto` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `descripcion` varchar(500) DEFAULT NULL,
  `imagen_url` varchar(255) DEFAULT NULL,
  `precio` decimal(12,2) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `origen` varchar(100) DEFAULT NULL,
  `id_vendedor` int(11) DEFAULT NULL,
  `fecha_creacion` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `producto`
--

INSERT INTO `producto` (`id_producto`, `nombre`, `descripcion`, `imagen_url`, `precio`, `stock`, `origen`, `id_vendedor`, `fecha_creacion`) VALUES
(2, 'Camiseta Negra', 'Camiseta 100% algodón color negro', NULL, 35000.00, 20, 'Colombia', NULL, '2025-11-23 17:06:13'),
(100, 'Laptop HP Pavilion TEST', 'Laptop 15.6\", Intel i5, 8GB RAM, 512GB SSD', NULL, 2500000.00, 10, NULL, NULL, '2025-12-01 21:53:40'),
(101, 'Smartphone Samsung TEST', '6.5\", 128GB, 8GB RAM, Cámara Quad', NULL, 1800000.00, 15, NULL, NULL, '2025-12-01 21:53:40'),
(102, 'Audífonos Sony TEST', 'Audífonos inalámbricos con cancelación de ruido', NULL, 350000.00, 25, NULL, NULL, '2025-12-01 21:53:40'),
(103, 'Smartwatch Apple TEST', 'Series 7, GPS, 45mm, Resistente al agua', NULL, 2200000.00, 8, NULL, NULL, '2025-12-01 21:53:40'),
(104, 'Tablet Amazon TEST', '10\", 32GB, HD, Alexa integrado', NULL, 800000.00, 20, NULL, NULL, '2025-12-01 21:53:40');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_categoria`
--

CREATE TABLE `producto_categoria` (
  `id_producto` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

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
  `codigo_expira` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`id_usuario`, `nombre`, `apellido`, `correo`, `contrasena`, `fecha_nacimiento`, `telefono`, `direccion_principal`, `codigo_recuperacion`, `codigo_expira`) VALUES
(2, 'Oscar', 'asdad', 'oscar.vanegas772@gmail.com', '$2y$10$O7xH90JGWzknLOOEM.tWluXA8kvwbOqwTbTbKzW9ANvALgHjkeNWG', '1111-11-11', '121213131', NULL, '392943', '2025-11-19 21:43:45'),
(3, 'Juan', 'Pérez', 'juan.perez@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL),
(4, 'María', 'Gómez', 'maria.gomez@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL),
(5, 'Carlos', 'Rodríguez', 'carlos.rod@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL),
(6, 'Ana', 'Martínez', 'ana.martinez@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL),
(7, 'Luis', 'Hernández', 'luis.hernandez@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL),
(23, 'Juan', 'Pérez', 'juan.perez2@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL),
(24, 'María', 'Gómez', 'maria.gomez2@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL),
(25, 'Carlos', 'Rodríguez', 'carlos.rod2@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL),
(26, 'Ana', 'Martínez', 'ana.martinez2@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL),
(27, 'Luis', 'Hernández', 'luis.hernandez2@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL),
(28, 'Juan', 'Pérez', 'juan.perez.test@email.com', '$2y$10$TuHashDeContraseña', '1990-05-15', '3101234567', 'Calle 123 #45-67, Bogotá', NULL, NULL),
(29, 'María', 'Gómez', 'maria.gomez.test@email.com', '$2y$10$TuHashDeContraseña', '1985-08-22', '3209876543', 'Avenida Siempre Viva 742, Medellín', NULL, NULL),
(30, 'Carlos', 'Rodríguez', 'carlos.rod.test@email.com', '$2y$10$TuHashDeContraseña', '1995-02-10', '3155551234', 'Carrera 7 #23-45, Cali', NULL, NULL),
(31, 'Ana', 'Martínez', 'ana.martinez.test@email.com', '$2y$10$TuHashDeContraseña', '1992-11-30', '3189998888', 'Diagonal 80 #12-34, Barranquilla', NULL, NULL),
(32, 'Luis', 'Hernández', 'luis.hernandez.test@email.com', '$2y$10$TuHashDeContraseña', '1988-07-18', '3001112233', 'Transversal 45 #56-78, Cartagena', NULL, NULL);

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
-- Indices de la tabla `administradores`
--
ALTER TABLE `administradores`
  ADD PRIMARY KEY (`id_admin`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `id_rol` (`id_rol`);

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
-- Indices de la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  ADD PRIMARY KEY (`id_direccion`),
  ADD KEY `fk_dir_cliente` (`id_cliente`);

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
  ADD KEY `fk_pedido_cliente` (`id_cliente`);

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
-- AUTO_INCREMENT de la tabla `administradores`
--
ALTER TABLE `administradores`
  MODIFY `id_admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT de la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  MODIFY `id_direccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- AUTO_INCREMENT de la tabla `producto`
--
ALTER TABLE `producto`
  MODIFY `id_producto` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=105;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id_rol` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `id_usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- Restricciones para tablas volcadas
--

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
-- Filtros para la tabla `direccion_envio`
--
ALTER TABLE `direccion_envio`
  ADD CONSTRAINT `fk_dir_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pasarela_pago`
--
ALTER TABLE `pasarela_pago`
  ADD CONSTRAINT `fk_pasarela_carrito` FOREIGN KEY (`id_carrito`) REFERENCES `carrito` (`id_carrito`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pedido`
--
ALTER TABLE `pedido`
  ADD CONSTRAINT `fk_pedido_cliente` FOREIGN KEY (`id_cliente`) REFERENCES `cliente` (`id_cliente`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pedido_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto`
--
ALTER TABLE `producto`
  ADD CONSTRAINT `fk_producto_vendedor` FOREIGN KEY (`id_vendedor`) REFERENCES `vendedor` (`id_vendedor`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Filtros para la tabla `producto_categoria`
--
ALTER TABLE `producto_categoria`
  ADD CONSTRAINT `fk_pc_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria` (`id_categoria`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pc_producto` FOREIGN KEY (`id_producto`) REFERENCES `producto` (`id_producto`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_rol`
--
ALTER TABLE `usuario_rol`
  ADD CONSTRAINT `fk_ur_rol` FOREIGN KEY (`id_rol`) REFERENCES `rol` (`id_rol`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ur_usuario` FOREIGN KEY (`id_usuario`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `vendedor`
--
ALTER TABLE `vendedor`
  ADD CONSTRAINT `fk_vendedor_usuario` FOREIGN KEY (`id_vendedor`) REFERENCES `usuario` (`id_usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
