-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-01-2026 a las 17:05:55
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
-- Base de datos: `onix_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `categorias`
--

CREATE TABLE `categorias` (
  `id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `categorias`
--

INSERT INTO `categorias` (`id`, `nombre`, `estado`) VALUES
(2, 'Bocadillos frescos', 'activo'),
(3, 'Bocadillos calientes', 'activo'),
(6, 'Hamburguesas', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `precio` decimal(6,2) NOT NULL,
  `imagen` varchar(255) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `id_categoria`, `nombre`, `descripcion`, `precio`, `imagen`, `estado`) VALUES
(11, 3, 'Bocadillo salchichas', 'Bocadillo de salchichas blancas.', 6.98, '../assets/imagenes/Bocadillo salchichas.jpg', 'activo'),
(12, 2, 'Bocadillo jamón serrano', 'Bocadillo de jamón serrano del bueno.', 9.99, '../assets/imagenes/Bocadillo jamón serrano.jpg', 'activo'),
(14, 2, 'Bocadillo Salmón', 'Bocadillo de salmón fresco', 6.99, '../assets/imagenes/Bocadillo Salmón.jpg', 'activo'),
(15, 3, 'Bocadillo pechuga queso', 'Bocadillo de pechuga de pollo y queso cheddar.', 6.99, '../assets/imagenes/Bocadillo pechuga queso.jpg', 'activo'),
(16, 2, 'Bocadillo pavo tomate', 'Bocadillo de lonchas de pavo y tomate a rodajas.', 4.99, '../assets/imagenes/Bocadillo pavo tomate.jpg', 'activo'),
(17, 3, 'Bocadillo tortilla', 'Bocadillo de tortilla poco hecha.', 6.99, '../assets/imagenes/Bocadillo tortilla.jpg', 'activo'),
(18, 6, 'La Monstruosa', 'Hamburguesa con 3 carnes de vacuno, triple de bacon y triple de queso cheddar.', 10.99, '../assets/imagenes/La Monstruosa.jpg', 'activo'),
(19, 6, 'La Completa', 'Hamburguesa con medallón de vacuno y bacon, cebolla, tomate, lechuga y queso cheddar.', 9.99, '../assets/imagenes/La Completa.jpg', 'activo'),
(20, 6, 'La Piba', 'Hamburguesa con medallón de vacuno, rúcula, queso de cabra, lechuga, tomate y cebolla frita.', 9.99, '../assets/imagenes/La Piba.jpg', 'activo');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `reservas`
--

CREATE TABLE `reservas` (
  `id` int(11) NOT NULL,
  `id_usuario` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora` time NOT NULL,
  `num_personas` int(11) NOT NULL,
  `estado` enum('pendiente','confirmada','cancelada') NOT NULL DEFAULT 'pendiente'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `reservas`
--

INSERT INTO `reservas` (`id`, `id_usuario`, `fecha`, `hora`, `num_personas`, `estado`) VALUES
(11, 11, '2026-01-22', '21:00:00', 12, 'pendiente'),
(12, 11, '2026-01-22', '20:00:00', 25, 'pendiente'),
(13, 11, '2026-01-22', '20:00:00', 15, 'pendiente'),
(14, 11, '2026-01-22', '20:00:00', 10, 'pendiente'),
(15, 11, '2026-01-22', '21:00:00', 10, 'pendiente'),
(16, 11, '2026-01-22', '21:00:00', 2, 'pendiente'),
(17, 12, '2026-01-24', '14:00:00', 8, 'cancelada'),
(18, 12, '2026-01-23', '09:00:00', 12, 'confirmada'),
(19, 12, '2026-01-30', '21:00:00', 12, 'cancelada'),
(20, 12, '2026-01-31', '22:00:00', 10, 'confirmada');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `email` varchar(50) NOT NULL,
  `contrasenya` varchar(255) NOT NULL,
  `telefono` varchar(20) NOT NULL,
  `rol` enum('usuario','administrador') NOT NULL DEFAULT 'usuario',
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `email`, `contrasenya`, `telefono`, `rol`, `estado`) VALUES
(11, 'Jose', 'jose@onix.com', '$2y$10$vhp2HoHDaxcf7CvUvTcgdeQRRLcow7CKj0QBLR8b8o2SHqaMMQ8bm', '123456789', 'administrador', 'activo'),
(12, 'Alicia', 'alicia@onix.com', '$2y$10$rkAjdk3xqyeR.oAMm6oK.OZkBOg6ktx/v5EJjS7iMRU6NMzbbgd2S', '987654324', 'usuario', 'activo');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `categorias`
--
ALTER TABLE `categorias`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_categoria` (`id_categoria`);

--
-- Indices de la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `categorias`
--
ALTER TABLE `categorias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `reservas`
--
ALTER TABLE `reservas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `productos`
--
ALTER TABLE `productos`
  ADD CONSTRAINT `fk_productos_categorias` FOREIGN KEY (`id_categoria`) REFERENCES `categorias` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `reservas`
--
ALTER TABLE `reservas`
  ADD CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
