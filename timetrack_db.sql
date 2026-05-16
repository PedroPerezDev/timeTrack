-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 10-05-2026 a las 21:29:12
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
-- Base de datos: `timetrack_db`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `festivos_locales`
--

CREATE TABLE `festivos_locales` (
  `id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `fichajes`
--

CREATE TABLE `fichajes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `hora_fichaje` time NOT NULL,
  `tipo` enum('entrada_1','salida_1','entrada_2','salida_2') NOT NULL,
  `minutos_diferencia` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `fichajes`
--

INSERT INTO `fichajes` (`id`, `usuario_id`, `fecha`, `hora_fichaje`, `tipo`, `minutos_diferencia`) VALUES
(1, 4, '2026-05-04', '08:30:00', 'entrada_1', 0),
(2, 4, '2026-05-04', '14:00:00', 'salida_1', 0),
(3, 4, '2026-05-04', '16:00:00', 'entrada_2', 0),
(4, 4, '2026-05-04', '18:30:00', 'salida_2', 0),
(5, 4, '2026-05-05', '08:30:00', 'entrada_1', 0),
(6, 4, '2026-05-05', '14:00:00', 'salida_1', 0),
(7, 4, '2026-05-05', '16:00:00', 'entrada_2', 0),
(8, 4, '2026-05-05', '18:30:00', 'salida_2', 0),
(9, 4, '2026-05-06', '08:30:00', 'entrada_1', 0),
(10, 4, '2026-05-06', '14:00:00', 'salida_1', 0),
(11, 4, '2026-05-06', '16:00:00', 'entrada_2', 0),
(12, 4, '2026-05-06', '18:30:00', 'salida_2', 0),
(13, 4, '2026-05-07', '08:30:00', 'entrada_1', 0),
(14, 4, '2026-05-07', '14:00:00', 'salida_1', 0),
(15, 4, '2026-05-07', '16:00:00', 'entrada_2', 0),
(16, 4, '2026-05-07', '18:30:00', 'salida_2', 0),
(17, 4, '2026-05-08', '08:30:00', 'entrada_1', 0),
(18, 4, '2026-05-08', '14:00:00', 'salida_1', 0),
(19, 4, '2026-05-08', '16:00:00', 'entrada_2', 0),
(20, 4, '2026-05-08', '18:30:00', 'salida_2', 0),
(21, 5, '2026-05-04', '09:15:00', 'entrada_1', -15),
(22, 5, '2026-05-04', '14:30:00', 'salida_1', 0),
(23, 5, '2026-05-04', '16:00:00', 'entrada_2', 0),
(24, 5, '2026-05-04', '19:00:00', 'salida_2', 0),
(25, 5, '2026-05-05', '09:00:00', 'entrada_1', 0),
(26, 5, '2026-05-05', '14:30:00', 'salida_1', 0),
(27, 5, '2026-05-05', '16:00:00', 'entrada_2', 0),
(28, 5, '2026-05-05', '19:00:00', 'salida_2', 0),
(29, 5, '2026-05-06', '09:20:00', 'entrada_1', -20),
(30, 5, '2026-05-06', '14:30:00', 'salida_1', 0),
(31, 5, '2026-05-06', '16:00:00', 'entrada_2', 0),
(32, 5, '2026-05-06', '19:00:00', 'salida_2', 0),
(33, 5, '2026-05-07', '09:00:00', 'entrada_1', 0),
(34, 5, '2026-05-07', '14:30:00', 'salida_1', 0),
(35, 5, '2026-05-07', '16:00:00', 'entrada_2', 0),
(36, 5, '2026-05-07', '19:00:00', 'salida_2', 0),
(37, 5, '2026-05-08', '09:10:00', 'entrada_1', -10),
(38, 5, '2026-05-08', '14:30:00', 'salida_1', 0),
(39, 5, '2026-05-08', '16:00:00', 'entrada_2', 0),
(40, 5, '2026-05-08', '19:00:00', 'salida_2', 0),
(41, 6, '2026-05-04', '07:45:00', 'entrada_1', 15),
(42, 6, '2026-05-04', '14:00:00', 'salida_1', 0),
(43, 6, '2026-05-04', '15:30:00', 'entrada_2', 0),
(44, 6, '2026-05-04', '18:30:00', 'salida_2', 30),
(45, 6, '2026-05-05', '07:50:00', 'entrada_1', 10),
(46, 6, '2026-05-05', '14:00:00', 'salida_1', 0),
(47, 6, '2026-05-05', '15:30:00', 'entrada_2', 0),
(48, 6, '2026-05-05', '18:15:00', 'salida_2', 15),
(49, 6, '2026-05-06', '08:00:00', 'entrada_1', 0),
(50, 6, '2026-05-06', '14:00:00', 'salida_1', 0),
(51, 6, '2026-05-06', '15:30:00', 'entrada_2', 0),
(52, 6, '2026-05-06', '18:00:00', 'salida_2', 0),
(53, 6, '2026-05-07', '07:45:00', 'entrada_1', 15),
(54, 6, '2026-05-07', '14:00:00', 'salida_1', 0),
(55, 6, '2026-05-07', '15:30:00', 'entrada_2', 0),
(56, 6, '2026-05-07', '18:30:00', 'salida_2', 30),
(57, 6, '2026-05-08', '08:00:00', 'entrada_1', 0),
(58, 6, '2026-05-08', '14:00:00', 'salida_1', 0),
(59, 6, '2026-05-08', '15:30:00', 'entrada_2', 0),
(60, 6, '2026-05-08', '18:00:00', 'salida_2', 0),
(61, 7, '2026-05-04', '08:30:00', 'entrada_1', 0),
(62, 7, '2026-05-04', '13:45:00', 'salida_1', -15),
(63, 7, '2026-05-04', '16:00:00', 'entrada_2', 0),
(64, 7, '2026-05-04', '18:30:00', 'salida_2', 0),
(65, 7, '2026-05-05', '08:30:00', 'entrada_1', 0),
(66, 7, '2026-05-05', '14:00:00', 'salida_1', 0),
(67, 7, '2026-05-05', '16:00:00', 'entrada_2', 0),
(68, 7, '2026-05-05', '18:15:00', 'salida_2', -15),
(69, 7, '2026-05-06', '08:30:00', 'entrada_1', 0),
(70, 7, '2026-05-06', '14:00:00', 'salida_1', 0),
(71, 7, '2026-05-06', '16:00:00', 'entrada_2', 0),
(72, 7, '2026-05-06', '18:30:00', 'salida_2', 0),
(73, 7, '2026-05-07', '08:45:00', 'entrada_1', -15),
(74, 7, '2026-05-07', '14:00:00', 'salida_1', 0),
(75, 7, '2026-05-07', '16:00:00', 'entrada_2', 0),
(76, 7, '2026-05-07', '18:30:00', 'salida_2', 0),
(77, 7, '2026-05-08', '08:30:00', 'entrada_1', 0),
(78, 7, '2026-05-08', '14:00:00', 'salida_1', 0),
(79, 7, '2026-05-08', '16:00:00', 'entrada_2', 0),
(80, 7, '2026-05-08', '18:30:00', 'salida_2', 0),
(81, 8, '2026-05-04', '09:00:00', 'entrada_1', 0),
(82, 8, '2026-05-04', '14:00:00', 'salida_1', 0),
(83, 8, '2026-05-04', '16:00:00', 'entrada_2', 0),
(84, 8, '2026-05-04', '19:00:00', 'salida_2', 0),
(85, 8, '2026-05-05', '09:00:00', 'entrada_1', 0),
(86, 8, '2026-05-05', '14:00:00', 'salida_1', 0),
(87, 8, '2026-05-05', '16:00:00', 'entrada_2', 0),
(88, 8, '2026-05-05', '19:00:00', 'salida_2', 0),
(89, 8, '2026-05-06', '09:00:00', 'entrada_1', 0),
(90, 8, '2026-05-06', '14:00:00', 'salida_1', 0),
(91, 8, '2026-05-06', '16:00:00', 'entrada_2', 0),
(92, 8, '2026-05-06', '19:00:00', 'salida_2', 0),
(93, 8, '2026-05-07', '09:00:00', 'entrada_1', 0),
(94, 8, '2026-05-07', '14:00:00', 'salida_1', 0),
(95, 8, '2026-05-07', '16:00:00', 'entrada_2', 0),
(96, 8, '2026-05-07', '19:00:00', 'salida_2', 0),
(97, 8, '2026-05-08', '09:00:00', 'entrada_1', 0),
(98, 8, '2026-05-08', '14:00:00', 'salida_1', 0),
(99, 8, '2026-05-08', '16:00:00', 'entrada_2', 0),
(100, 8, '2026-05-08', '19:00:00', 'salida_2', 0),
(101, 9, '2026-05-04', '08:45:00', 'entrada_1', -15),
(102, 9, '2026-05-04', '14:00:00', 'salida_1', 0),
(103, 9, '2026-05-04', '16:00:00', 'entrada_2', 0),
(104, 9, '2026-05-04', '18:45:00', 'salida_2', 15),
(105, 9, '2026-05-05', '08:30:00', 'entrada_1', 0),
(106, 9, '2026-05-05', '14:00:00', 'salida_1', 0),
(107, 9, '2026-05-05', '16:00:00', 'entrada_2', 0),
(108, 9, '2026-05-05', '18:30:00', 'salida_2', 0),
(109, 9, '2026-05-06', '08:20:00', 'entrada_1', 10),
(110, 9, '2026-05-06', '14:00:00', 'salida_1', 0),
(111, 9, '2026-05-06', '16:00:00', 'entrada_2', 0),
(112, 9, '2026-05-06', '18:30:00', 'salida_2', 0),
(113, 9, '2026-05-07', '08:50:00', 'entrada_1', -20),
(114, 9, '2026-05-07', '14:00:00', 'salida_1', 0),
(115, 9, '2026-05-07', '16:00:00', 'entrada_2', 0),
(116, 9, '2026-05-07', '18:30:00', 'salida_2', 0),
(117, 9, '2026-05-08', '08:30:00', 'entrada_1', 0),
(118, 9, '2026-05-08', '14:00:00', 'salida_1', 0),
(119, 9, '2026-05-08', '16:00:00', 'entrada_2', 0),
(120, 9, '2026-05-08', '18:30:00', 'salida_2', 0),
(121, 10, '2026-05-04', '07:30:00', 'entrada_1', 30),
(122, 10, '2026-05-04', '14:00:00', 'salida_1', 0),
(123, 10, '2026-05-04', '15:30:00', 'entrada_2', 0),
(124, 10, '2026-05-04', '19:00:00', 'salida_2', 30),
(125, 10, '2026-05-05', '07:45:00', 'entrada_1', 15),
(126, 10, '2026-05-05', '14:00:00', 'salida_1', 0),
(127, 10, '2026-05-05', '15:30:00', 'entrada_2', 0),
(128, 10, '2026-05-05', '19:15:00', 'salida_2', 45),
(129, 10, '2026-05-06', '08:00:00', 'entrada_1', 0),
(130, 10, '2026-05-06', '14:00:00', 'salida_1', 0),
(131, 10, '2026-05-06', '15:30:00', 'entrada_2', 0),
(132, 10, '2026-05-06', '18:30:00', 'salida_2', 0),
(133, 10, '2026-05-07', '07:30:00', 'entrada_1', 30),
(134, 10, '2026-05-07', '14:00:00', 'salida_1', 0),
(135, 10, '2026-05-07', '15:30:00', 'entrada_2', 0),
(136, 10, '2026-05-07', '19:00:00', 'salida_2', 30),
(137, 10, '2026-05-08', '08:00:00', 'entrada_1', 0),
(138, 10, '2026-05-08', '14:00:00', 'salida_1', 0),
(139, 10, '2026-05-08', '15:30:00', 'entrada_2', 0),
(140, 10, '2026-05-08', '18:30:00', 'salida_2', 0),
(141, 11, '2026-05-04', '09:00:00', 'entrada_1', 0),
(142, 11, '2026-05-04', '14:30:00', 'salida_1', 0),
(143, 11, '2026-05-04', '16:00:00', 'entrada_2', 0),
(144, 11, '2026-05-04', '19:00:00', 'salida_2', 0),
(145, 11, '2026-05-05', '09:10:00', 'entrada_1', -10),
(146, 11, '2026-05-05', '14:30:00', 'salida_1', 0),
(147, 11, '2026-05-05', '16:00:00', 'entrada_2', 0),
(148, 11, '2026-05-05', '19:00:00', 'salida_2', 0),
(149, 11, '2026-05-06', '09:00:00', 'entrada_1', 0),
(150, 11, '2026-05-06', '14:30:00', 'salida_1', 0),
(151, 11, '2026-05-06', '16:00:00', 'entrada_2', 0),
(152, 11, '2026-05-06', '19:00:00', 'salida_2', 0),
(153, 11, '2026-05-07', '09:00:00', 'entrada_1', 0),
(154, 11, '2026-05-07', '14:30:00', 'salida_1', 0),
(155, 11, '2026-05-07', '16:00:00', 'entrada_2', 0),
(156, 11, '2026-05-07', '18:45:00', 'salida_2', -15),
(157, 11, '2026-05-08', '09:15:00', 'entrada_1', -15),
(158, 11, '2026-05-08', '14:30:00', 'salida_1', 0),
(159, 11, '2026-05-08', '16:00:00', 'entrada_2', 0),
(160, 11, '2026-05-08', '19:00:00', 'salida_2', 0),
(161, 12, '2026-05-04', '08:45:00', 'entrada_1', -15),
(162, 12, '2026-05-04', '14:00:00', 'salida_1', 0),
(163, 12, '2026-05-04', '16:00:00', 'entrada_2', 0),
(164, 12, '2026-05-04', '18:00:00', 'salida_2', -30),
(165, 12, '2026-05-05', '08:20:00', 'entrada_1', 10),
(166, 12, '2026-05-05', '14:00:00', 'salida_1', 0),
(167, 12, '2026-05-05', '16:00:00', 'entrada_2', 0),
(168, 12, '2026-05-05', '19:00:00', 'salida_2', 30),
(169, 12, '2026-05-06', '09:00:00', 'entrada_1', -30),
(170, 12, '2026-05-06', '14:00:00', 'salida_1', 0),
(171, 12, '2026-05-06', '16:00:00', 'entrada_2', 0),
(172, 12, '2026-05-06', '18:30:00', 'salida_2', 0),
(173, 12, '2026-05-07', '08:30:00', 'entrada_1', 0),
(174, 12, '2026-05-07', '14:00:00', 'salida_1', 0),
(175, 12, '2026-05-07', '16:00:00', 'entrada_2', 0),
(176, 12, '2026-05-07', '18:30:00', 'salida_2', 0),
(177, 12, '2026-05-08', '08:40:00', 'entrada_1', -10),
(178, 12, '2026-05-08', '14:00:00', 'salida_1', 0),
(179, 12, '2026-05-08', '16:00:00', 'entrada_2', 0),
(180, 12, '2026-05-08', '18:30:00', 'salida_2', 0),
(181, 13, '2026-05-04', '08:00:00', 'entrada_1', 0),
(182, 13, '2026-05-04', '14:00:00', 'salida_1', 0),
(183, 13, '2026-05-04', '15:30:00', 'entrada_2', 0),
(184, 13, '2026-05-04', '17:45:00', 'salida_2', -15),
(185, 13, '2026-05-05', '08:00:00', 'entrada_1', 0),
(186, 13, '2026-05-05', '14:00:00', 'salida_1', 0),
(187, 13, '2026-05-05', '15:30:00', 'entrada_2', 0),
(188, 13, '2026-05-05', '18:00:00', 'salida_2', 0),
(189, 13, '2026-05-06', '08:00:00', 'entrada_1', 0),
(190, 13, '2026-05-06', '14:00:00', 'salida_1', 0),
(191, 13, '2026-05-06', '15:30:00', 'entrada_2', 0),
(192, 13, '2026-05-06', '17:45:00', 'salida_2', -15),
(193, 13, '2026-05-07', '08:00:00', 'entrada_1', 0),
(194, 13, '2026-05-07', '14:00:00', 'salida_1', 0),
(195, 13, '2026-05-07', '15:30:00', 'entrada_2', 0),
(196, 13, '2026-05-07', '18:00:00', 'salida_2', 0),
(197, 13, '2026-05-08', '08:00:00', 'entrada_1', 0),
(198, 13, '2026-05-08', '14:00:00', 'salida_1', 0),
(199, 13, '2026-05-08', '15:30:00', 'entrada_2', 0),
(200, 13, '2026-05-08', '18:00:00', 'salida_2', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios`
--

CREATE TABLE `horarios` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `dia_semana` tinyint(4) NOT NULL COMMENT '1=Lunes, 7=Domingo',
  `hora_entrada_1` time DEFAULT NULL,
  `hora_salida_1` time DEFAULT NULL,
  `hora_entrada_2` time DEFAULT NULL,
  `hora_salida_2` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios`
--

INSERT INTO `horarios` (`id`, `usuario_id`, `dia_semana`, `hora_entrada_1`, `hora_salida_1`, `hora_entrada_2`, `hora_salida_2`) VALUES
(51, 4, 1, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(52, 4, 2, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(53, 4, 3, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(54, 4, 4, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(55, 4, 5, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(56, 5, 1, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(57, 5, 2, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(58, 5, 3, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(59, 5, 4, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(60, 5, 5, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(61, 6, 1, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(62, 6, 2, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(63, 6, 3, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(64, 6, 4, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(65, 6, 5, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(66, 7, 1, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(67, 7, 2, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(68, 7, 3, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(69, 7, 4, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(70, 7, 5, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(71, 8, 1, '09:00:00', '14:00:00', '16:00:00', '19:00:00'),
(72, 8, 2, '09:00:00', '14:00:00', '16:00:00', '19:00:00'),
(73, 8, 3, '09:00:00', '14:00:00', '16:00:00', '19:00:00'),
(74, 8, 4, '09:00:00', '14:00:00', '16:00:00', '19:00:00'),
(75, 8, 5, '09:00:00', '14:00:00', '16:00:00', '19:00:00'),
(76, 9, 1, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(77, 9, 2, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(78, 9, 3, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(79, 9, 4, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(80, 9, 5, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(81, 10, 1, '08:00:00', '14:00:00', '15:30:00', '18:30:00'),
(82, 10, 2, '08:00:00', '14:00:00', '15:30:00', '18:30:00'),
(83, 10, 3, '08:00:00', '14:00:00', '15:30:00', '18:30:00'),
(84, 10, 4, '08:00:00', '14:00:00', '15:30:00', '18:30:00'),
(85, 10, 5, '08:00:00', '14:00:00', '15:30:00', '18:30:00'),
(86, 11, 1, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(87, 11, 2, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(88, 11, 3, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(89, 11, 4, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(90, 11, 5, '09:00:00', '14:30:00', '16:00:00', '19:00:00'),
(91, 12, 1, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(92, 12, 2, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(93, 12, 3, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(94, 12, 4, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(95, 12, 5, '08:30:00', '14:00:00', '16:00:00', '18:30:00'),
(96, 13, 1, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(97, 13, 2, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(98, 13, 3, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(99, 13, 4, '08:00:00', '14:00:00', '15:30:00', '18:00:00'),
(100, 13, 5, '08:00:00', '14:00:00', '15:30:00', '18:00:00');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `horarios_especiales`
--

CREATE TABLE `horarios_especiales` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('cambio_horario','vacaciones','festivo','libre') NOT NULL,
  `hora_entrada_1` time DEFAULT NULL,
  `hora_salida_1` time DEFAULT NULL,
  `hora_entrada_2` time DEFAULT NULL,
  `hora_salida_2` time DEFAULT NULL,
  `observaciones` varchar(255) DEFAULT NULL,
  `creado_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `horarios_especiales`
--

INSERT INTO `horarios_especiales` (`id`, `usuario_id`, `fecha`, `tipo`, `hora_entrada_1`, `hora_salida_1`, `hora_entrada_2`, `hora_salida_2`, `observaciones`, `creado_por`) VALUES
(1, 4, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(2, 4, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(3, 4, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(4, 4, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(5, 4, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(6, 4, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(7, 4, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(8, 4, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(9, 4, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(10, 4, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(11, 4, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(12, 4, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(13, 4, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(14, 5, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(15, 5, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(16, 5, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(17, 5, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(18, 5, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(19, 5, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(20, 5, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(21, 5, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(22, 5, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(23, 5, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(24, 5, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(25, 5, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(26, 5, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(27, 6, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(28, 6, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(29, 6, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(30, 6, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(31, 6, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(32, 6, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(33, 6, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(34, 6, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(35, 6, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(36, 6, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(37, 6, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(38, 6, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(39, 6, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(40, 7, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(41, 7, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(42, 7, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(43, 7, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(44, 7, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(45, 7, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(46, 7, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(47, 7, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(48, 7, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(49, 7, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(50, 7, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(51, 7, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(52, 7, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(53, 8, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(54, 8, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(55, 8, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(56, 8, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(57, 8, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(58, 8, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(59, 8, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(60, 8, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(61, 8, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(62, 8, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(63, 8, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(64, 8, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(65, 8, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(66, 9, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(67, 9, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(68, 9, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(69, 9, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(70, 9, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(71, 9, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(72, 9, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(73, 9, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(74, 9, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(75, 9, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(76, 9, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(77, 9, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(78, 9, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(79, 10, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(80, 10, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(81, 10, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(82, 10, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(83, 10, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(84, 10, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(85, 10, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(86, 10, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(87, 10, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(88, 10, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(89, 10, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(90, 10, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(91, 10, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(92, 11, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(93, 11, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(94, 11, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(95, 11, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(96, 11, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(97, 11, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(98, 11, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(99, 11, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(100, 11, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(101, 11, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(102, 11, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(103, 11, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(104, 11, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(105, 12, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(106, 12, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(107, 12, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(108, 12, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(109, 12, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(110, 12, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(111, 12, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(112, 12, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(113, 12, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(114, 12, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(115, 12, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(116, 12, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(117, 12, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3),
(118, 13, '2026-01-01', 'festivo', NULL, NULL, NULL, NULL, 'Año Nuevo', 3),
(119, 13, '2026-01-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de Reyes / Epifanía del Señor', 3),
(120, 13, '2026-04-03', 'festivo', NULL, NULL, NULL, NULL, 'Viernes Santo', 3),
(121, 13, '2026-04-06', 'festivo', NULL, NULL, NULL, NULL, 'Lunes de Pascua', 3),
(122, 13, '2026-05-01', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta del trabajo', 3),
(123, 13, '2026-06-24', 'festivo', NULL, NULL, NULL, NULL, 'Sant Joan', 3),
(124, 13, '2026-08-15', 'festivo', NULL, NULL, NULL, NULL, 'Asunción', 3),
(125, 13, '2026-10-09', 'festivo', NULL, NULL, NULL, NULL, 'Dia de la Comunitat Valenciana', 3),
(126, 13, '2026-10-12', 'festivo', NULL, NULL, NULL, NULL, 'Fiesta Nacional de España', 3),
(127, 13, '2026-11-01', 'festivo', NULL, NULL, NULL, NULL, 'Día de todos los Santos', 3),
(128, 13, '2026-12-06', 'festivo', NULL, NULL, NULL, NULL, 'Día de la Constitución', 3),
(129, 13, '2026-12-08', 'festivo', NULL, NULL, NULL, NULL, 'Inmaculada Concepción', 3),
(130, 13, '2026-12-25', 'festivo', NULL, NULL, NULL, NULL, 'Navidad', 3);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `incidencias`
--

CREATE TABLE `incidencias` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha` date NOT NULL,
  `tipo` enum('retraso','ausencia','horas_extra','medico','permiso','vacaciones','baja','festivo_local') NOT NULL,
  `minutos` int(11) DEFAULT 0,
  `observaciones` text DEFAULT NULL,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mensajes`
--

CREATE TABLE `mensajes` (
  `id` int(11) NOT NULL,
  `remitente_id` int(11) NOT NULL,
  `destinatario_id` int(11) NOT NULL,
  `asunto` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `leido` tinyint(1) DEFAULT 0,
  `fecha` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `solicitudes`
--

CREATE TABLE `solicitudes` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `tipo` enum('vacaciones','permiso','medico','asuntos_propios') NOT NULL,
  `motivo` text DEFAULT NULL,
  `estado` enum('pendiente','aprobada','denegada') DEFAULT 'pendiente',
  `respuesta_admin` text DEFAULT NULL,
  `fecha_solicitud` datetime DEFAULT current_timestamp(),
  `fecha_respuesta` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `apellidos` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `rol` enum('admin','trabajador') DEFAULT 'trabajador',
  `activo` tinyint(1) DEFAULT 1,
  `dni` varchar(20) DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `direccion` varchar(255) DEFAULT NULL,
  `fecha_nacimiento` date DEFAULT NULL,
  `fecha_incorporacion` date DEFAULT NULL,
  `foto` varchar(255) DEFAULT NULL,
  `departamento` varchar(100) DEFAULT NULL,
  `puesto` varchar(100) DEFAULT NULL,
  `dias_vacaciones_totales` int(11) DEFAULT 22,
  `dias_vacaciones_gastados` int(11) DEFAULT 0,
  `fecha_alta` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `nombre`, `apellidos`, `email`, `password`, `rol`, `activo`, `dni`, `telefono`, `direccion`, `fecha_nacimiento`, `fecha_incorporacion`, `foto`, `departamento`, `puesto`, `dias_vacaciones_totales`, `dias_vacaciones_gastados`, `fecha_alta`) VALUES
(3, 'admin', 'TimeTrack', 'admin@timetrack.com', 'admin', 'admin', 1, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 22, 0, '2026-04-25 12:08:56'),
(4, 'Carlos', 'García López', 'carlos@timetrack.com', '1234', 'trabajador', 1, '12345678A', '600111222', 'Calle Mayor 1, Alicante', '1985-03-15', '2020-01-10', '1778413736_06.jpg', 'Administración', 'Contable', 22, 0, '2026-05-10 12:45:16'),
(5, 'María', 'Martínez Pérez', 'maria@timetrack.com', '1234', 'trabajador', 1, '23456789B', '600222333', 'Avenida del Mar 5, Elche', '1990-07-22', '2019-05-15', '1778413797_10.jpg', 'Recursos Humanos', 'Técnico RRHH', 22, 0, '2026-05-10 12:45:16'),
(6, 'José', 'Sánchez Ruiz', 'jose@timetrack.com', '1234', 'trabajador', 1, '34567890C', '600333444', 'Calle del Sol 12, Murcia', '1988-11-30', '2021-03-01', '1778413884_11.jpg', 'Informática', 'Programador', 22, 0, '2026-05-10 12:45:16'),
(7, 'Ana', 'López Torres', 'ana@timetrack.com', '1234', 'trabajador', 1, '45678901D', '600444555', 'Plaza España 3, Valencia', '1992-04-18', '2018-09-01', '1778413783_04.jpg', 'Ventas', 'Comercial', 22, 0, '2026-05-10 12:45:16'),
(8, 'Pedro', 'Fernández Gil', 'pedro@timetrack.com', '1234', 'trabajador', 1, '56789012E', '600555666', 'Calle Nueva 8, Alicante', '1987-09-05', '2022-01-15', '1778413724_05.jpg', 'Informática', 'Técnico de soporte', 22, 0, '2026-05-10 12:45:16'),
(9, 'Laura', 'González Mora', 'laura@timetrack.com', '1234', 'trabajador', 1, '67890123F', '600666777', 'Avenida Libertad 20, Elda', '1995-01-27', '2021-06-01', '1778413746_02.jpg', 'Administración', 'Administrativo', 22, 0, '2026-05-10 12:45:16'),
(10, 'Miguel', 'Rodríguez Vega', 'miguel@timetrack.com', '1234', 'trabajador', 1, '78901234G', '600777888', 'Calle Colón 15, Benidorm', '1983-06-14', '2017-11-01', '1778413848_09.jpg', 'Ventas', 'Jefe de ventas', 22, 0, '2026-05-10 12:45:16'),
(11, 'Sofía', 'Díaz Castillo', 'sofia@timetrack.com', '1234', 'trabajador', 1, '89012345H', '600888999', 'Calle Cervantes 7, Torrevieja', '1993-08-09', '2020-07-01', '1778413708_01.jpg', 'Recursos Humanos', 'Responsable RRHH', 22, 0, '2026-05-10 12:45:16'),
(12, 'Javier', 'Moreno Blanco', 'javier@timetrack.com', '1234', 'trabajador', 1, '90123456I', '600999000', 'Avenida del Puerto 33, Cartagena', '1989-12-03', '2019-02-15', '1778413819_08.jpg', 'Informática', 'Analista', 22, 0, '2026-05-10 12:45:16'),
(13, 'Elena', 'Jiménez Rubio', 'elena@timetrack.com', '1234', 'trabajador', 1, '01234567J', '601000111', 'Calle Mayor 45, Orihuela', '1991-05-21', '2023-03-01', '1778413762_03.jpg', 'Administración', 'Recepcionista', 22, 0, '2026-05-10 12:45:16');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vacaciones_empresa`
--

CREATE TABLE `vacaciones_empresa` (
  `id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `descripcion` varchar(255) NOT NULL,
  `creado_por` int(11) NOT NULL,
  `fecha_creacion` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `festivos_locales`
--
ALTER TABLE `festivos_locales`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `fichajes`
--
ALTER TABLE `fichajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `remitente_id` (`remitente_id`),
  ADD KEY `destinatario_id` (`destinatario_id`);

--
-- Indices de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indices de la tabla `vacaciones_empresa`
--
ALTER TABLE `vacaciones_empresa`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `festivos_locales`
--
ALTER TABLE `festivos_locales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `fichajes`
--
ALTER TABLE `fichajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=201;

--
-- AUTO_INCREMENT de la tabla `horarios`
--
ALTER TABLE `horarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=101;

--
-- AUTO_INCREMENT de la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=131;

--
-- AUTO_INCREMENT de la tabla `incidencias`
--
ALTER TABLE `incidencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `mensajes`
--
ALTER TABLE `mensajes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `vacaciones_empresa`
--
ALTER TABLE `vacaciones_empresa`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `fichajes`
--
ALTER TABLE `fichajes`
  ADD CONSTRAINT `fichajes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios`
--
ALTER TABLE `horarios`
  ADD CONSTRAINT `horarios_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `horarios_especiales`
--
ALTER TABLE `horarios_especiales`
  ADD CONSTRAINT `horarios_especiales_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `incidencias`
--
ALTER TABLE `incidencias`
  ADD CONSTRAINT `incidencias_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `mensajes`
--
ALTER TABLE `mensajes`
  ADD CONSTRAINT `mensajes_ibfk_1` FOREIGN KEY (`remitente_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `mensajes_ibfk_2` FOREIGN KEY (`destinatario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `solicitudes`
--
ALTER TABLE `solicitudes`
  ADD CONSTRAINT `solicitudes_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
