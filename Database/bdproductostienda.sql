-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 06-07-2025 a las 00:10:49
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bdproductostienda`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `DNI` varchar(100) NOT NULL,
  `ciudad` varchar(100) NOT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `correo` varchar(100) NOT NULL,
  `direccion` varchar(250) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre`, `apellido`, `DNI`, `ciudad`, `telefono`, `correo`, `direccion`, `estado`) VALUES
(1, 'Gabriela', 'Jaimes D', '1007193080', 'Bucaramanga', '3222514188', 'gabi@gmail.com', 'calle 43 giron asssffff', 1),
(2, 'Juan', 'Rodriguez P', '1007183560', 'Bucaramanga', '3225147896', 'juand@gmail.com', 'calle 56 giron', 0),
(3, 'Andrey', 'M', '1007193050', 'Bucaramanga', '3222514185', 'a@asss', 'giron calle 45', 0),
(4, 'dsadsadsa', 'asddasasd', '1007193080', 'dsasddas', '25255', 'uts@edu.co', 'saddsa', 0),
(5, 'Andrey', 'Mantilla', '1007193050', 'Giron', '3222514185', 'andreystteven@gmail.com', 'calle 44', 0),
(6, 'sdad', 'sadad', '52522525', 'das', '23663', 'sss@gmail.com', 'dsaads', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `compras`
--

CREATE TABLE `compras` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_proveedor` int(11) NOT NULL,
  `precio_unitario_producto` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_compra` datetime NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `compras`
--

INSERT INTO `compras` (`id`, `id_producto`, `id_proveedor`, `precio_unitario_producto`, `total`, `cantidad`, `fecha_compra`, `estado`) VALUES
(1, 1, 1, 150000, 75000000, 500, '2024-10-26 17:49:56', 1),
(2, 2, 1, 150000, 7500000, 50, '2024-11-20 14:31:11', 0),
(3, 2, 1, 16616, 830800, 50, '2024-11-20 15:04:18', 1),
(4, 1, 1, 150000, 75300000, 502, '2024-11-20 15:11:02', 0),
(5, 1, 1, 150000, 60000000, 400, '2024-11-23 11:08:02', 0),
(6, 1, 1, 4500, 2250000, 500, '2024-11-23 11:08:21', 1),
(7, 2, 1, 150000, 30000000, 200, '2024-11-23 18:01:26', 0),
(8, 8, 1, 10000, 1000000, 100, '2024-11-24 15:18:31', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `stock` int(11) NOT NULL DEFAULT 0,
  `precio_unitario` int(11) NOT NULL,
  `descripcion` varchar(250) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre`, `stock`, `precio_unitario`, `descripcion`, `estado`) VALUES
(1, 'Teclado Gamer semimecanico', 999, 600000, 'sdaas', 1),
(2, 'Audifonos inalambricos ', 500, 225000, 'zxdas', 1),
(3, 'PC Gamer', 100, 10000000, 'pc Nvidia RTX 3060', 0),
(4, 'nola', 2525, 2147483647, '0', 0),
(5, 'asdsd', 400, 50000, 'zccx', 0),
(6, 'FDASFASFASs', 2000, 2147483647, 'dasdsas', 0),
(7, 'dsaads', 252552, 235255225, 'dasads', 0),
(8, 'prueba', 100, 10000, 'adssad', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `proveedores`
--

CREATE TABLE `proveedores` (
  `id` int(11) NOT NULL,
  `nombre` varchar(200) NOT NULL,
  `NIT` varchar(100) NOT NULL,
  `correo` varchar(100) NOT NULL,
  `telefono` varchar(100) DEFAULT NULL,
  `ciudad` varchar(100) NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `proveedores`
--

INSERT INTO `proveedores` (`id`, `nombre`, `NIT`, `correo`, `telefono`, `ciudad`, `estado`) VALUES
(1, 'Razer', '123456', 'razer@gmail.com', '300200400', 'Bucaramanga sgg', 1),
(2, 'Gamer Tech', '777666', 'gamertech@gmail.com', '3559874156', 'Bogota', 1),
(3, 'Hola', '777666', 'uts@edu.co', '2525', 'dsaasd', 0),
(4, 'Prueba', '777485', 'prueba@gmail.com', '32258964', 'Bucaramanga', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(110) NOT NULL,
  `descripcion` varchar(200) DEFAULT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `estado`) VALUES
(1, 'SuperAdmin', 'Ing. En Sistemas', 1),
(2, 'Admin', 'Gerente de la empresa', 1),
(3, 'Ventas', 'Encargado de CRUD ventas', 1),
(4, 'Compras', 'Encargado de CRUD compras', 1),
(5, 'Inventario', 'Encargado de CRUD clientes, proveedores y productos', 1),
(6, 'Invitado', 'Solo lectura', 1),
(11, 'Pruebas', 'asddas', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(200) NOT NULL,
  `password` varchar(100) NOT NULL,
  `intentosLogin` int(11) NOT NULL DEFAULT 0,
  `id_rol` int(11) NOT NULL DEFAULT 6,
  `estado` tinyint(1) NOT NULL DEFAULT 1,
  `ultimo_intento` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `password`, `intentosLogin`, `id_rol`, `estado`, `ultimo_intento`) VALUES
(1, 'Andrey Mantilla', 'admin@gmail.com', '$2y$10$WVhZnzeQPxBNz4CytBHGr.ojlHt0amehc3/3tws7d7ZB7qtWYtYc2', 0, 1, 1, NULL),
(2, 'Invitado', 'invitado@gmail.com', '$2y$10$xdre1wS2JiFg9b5wvStoqeJxbygNcrCgPtmPrb8XZKcCUbUcWPWc.', 0, 6, 1, NULL),
(3, 'dsadas', 'andreysss@gmail.com', '$2y$10$rlkzfW5DZDc0MI.md5/beeEgu2Dl7IpfTgszI4uHkBFspgZzjDQpu', 0, 5, 0, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `id_cliente` int(11) NOT NULL,
  `precio_unitario_producto` int(11) NOT NULL,
  `total` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_venta` datetime NOT NULL,
  `estado` tinyint(4) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_spanish_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `id_producto`, `id_cliente`, `precio_unitario_producto`, `total`, `cantidad`, `fecha_venta`, `estado`) VALUES
(1, 1, 1, 150000, 150000, 1, '2024-10-26 17:21:24', 1),
(2, 1, 2, 150000, 450000, 3, '2024-10-26 17:23:17', 1),
(3, 2, 2, 225000, 675000, 3, '0000-00-00 00:00:00', 0),
(4, 2, 2, 225000, 450000, 2, '2024-10-26 17:49:17', 1),
(5, 1, 1, 150000, 29550000, 197, '2024-10-28 10:55:46', 1),
(6, 1, 1, 150000, 150000, 1, '2024-10-28 11:00:40', 1),
(7, 2, 1, 150000, 45000000, 300, '2024-11-23 11:42:10', 0),
(8, 1, 1, 600000, 60000000, 100, '2024-11-23 16:54:47', 0),
(9, 8, 1, 10000, 1000000, 100, '2024-11-24 15:16:54', 1);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `compras`
--
ALTER TABLE `compras`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_compras_1` (`id_producto`),
  ADD KEY `fk_compras_2` (`id_proveedor`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fkRol` (`id_rol`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_ventas_1` (`id_producto`),
  ADD KEY `fk_ventas_2` (`id_cliente`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `compras`
--
ALTER TABLE `compras`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `proveedores`
--
ALTER TABLE `proveedores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `compras`
--
ALTER TABLE `compras`
  ADD CONSTRAINT `fk_compras_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_compras_2` FOREIGN KEY (`id_proveedor`) REFERENCES `proveedores` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `fkRol` FOREIGN KEY (`id_rol`) REFERENCES `roles` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD CONSTRAINT `fk_ventas_1` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ventas_2` FOREIGN KEY (`id_cliente`) REFERENCES `clientes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
