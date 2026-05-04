-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 21-04-2026 a las 08:27:56
-- Versión del servidor: 8.0.45
-- Versión de PHP: 8.2.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `pitlane_f1`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `carreras`
--

CREATE TABLE `carreras` (
  `id_carrera` int NOT NULL,
  `id_temporada` int NOT NULL,
  `numero_carrera` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `circuito` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `carreras`
--

INSERT INTO `carreras` (`id_carrera`, `id_temporada`, `numero_carrera`, `nombre`, `circuito`, `fecha`) VALUES
(1, 1, 1, 'Gran Premio de Australia', 'Albert Park', '2026-03-08'),
(2, 1, 2, 'Gran Premio de China', 'Shanghái', '2026-03-15'),
(3, 1, 3, 'Gran Premio de Japón', 'Suzuka', '2026-03-29'),
(4, 1, 4, 'Gran Premio de Miami', 'Miami International', '2026-05-03'),
(5, 1, 5, 'Gran Premio de Canadá', 'Gilles Villeneuve', '2026-05-24'),
(6, 1, 6, 'Gran Premio de Mónaco', 'Mónaco', '2026-06-07'),
(7, 1, 7, 'Gran Premio de Barcelona-Catalunya', 'Montmeló', '2026-06-14'),
(8, 1, 8, 'Gran Premio de Austria', 'Red Bull Ring', '2026-06-28'),
(9, 1, 9, 'Gran Premio de Gran Bretaña', 'Silverstone', '2026-07-05'),
(10, 1, 10, 'Gran Premio de Bélgica', 'Spa-Francorchamps', '2026-07-19'),
(11, 1, 11, 'Gran Premio de Hungría', 'Hungaroring', '2026-07-26'),
(12, 1, 12, 'Gran Premio de Países Bajos', 'Zandvoort', '2026-08-23'),
(13, 1, 13, 'Gran Premio de Italia', 'Monza', '2026-09-06'),
(14, 1, 14, 'Gran Premio de España', 'Madring', '2026-09-13'),
(15, 1, 15, 'Gran Premio de Azerbaiyán', 'Bakú', '2026-09-26'),
(16, 1, 16, 'Gran Premio de Singapur', 'Marina Bay', '2026-10-11'),
(17, 1, 17, 'Gran Premio de Austin', 'Circuit of the Americas', '2026-10-25'),
(18, 1, 18, 'Gran Premio de México', 'Hermanos Rodríguez', '2026-11-01'),
(19, 1, 19, 'Gran Premio de Brasil', 'Interlagos', '2026-11-08'),
(20, 1, 20, 'Gran Premio de Las Vegas', 'Las Vegas Strip', '2026-11-21'),
(21, 1, 21, 'Gran Premio de Qatar', 'Lusail', '2026-11-29'),
(22, 1, 22, 'Gran Premio de Abu Dabi', 'Yas Marina', '2026-12-06');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clasificacion`
--

CREATE TABLE `clasificacion` (
  `id_clasificacion` int NOT NULL,
  `id_equipo` int NOT NULL,
  `puntos_totales` int DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `equipos_fantasy`
--

CREATE TABLE `equipos_fantasy` (
  `id_equipo` int NOT NULL,
  `id_usuario` int NOT NULL,
  `nombre_equipo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `presupuesto` int DEFAULT '40000000',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `puntos_jornada` int DEFAULT '0' COMMENT 'Puntos en la última carrera',
  `cambios_disponibles` int DEFAULT '3' COMMENT 'Cambios gratuitos restantes',
  `cambios_usados` int DEFAULT '0' COMMENT 'Cambios usados esta ventana'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `equipos_fantasy`
--

INSERT INTO `equipos_fantasy` (`id_equipo`, `id_usuario`, `nombre_equipo`, `presupuesto`, `fecha_creacion`, `puntos_jornada`, `cambios_disponibles`, `cambios_usados`) VALUES
(1, 1, 'Equipo de Daniel', 5000000, '2026-03-23 09:57:26', 5, 3, 0),
(2, 2, 'Equipo de usuario', 40000000, '2026-04-21 10:02:33', 0, 3, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escuderias`
--

CREATE TABLE `escuderias` (
  `id_escuderia` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `precio_base` int NOT NULL,
  `logo_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `escuderias`
--

INSERT INTO `escuderias` (`id_escuderia`, `nombre`, `precio_base`, `logo_url`) VALUES
(1, 'Oracle Red Bull Racing', 20000000, NULL),
(2, 'Scuderia Ferrari HP', 19000000, NULL),
(3, 'Mercedes-AMG Petronas F1 Team', 18000000, NULL),
(4, 'McLaren Mastercard F1 Team', 18500000, NULL),
(5, 'Aston Martin Aramco F1 Team', 14000000, NULL),
(6, 'BWT Alpine F1 Team', 12000000, NULL),
(7, 'Atlassian Williams F1 Team', 11000000, NULL),
(8, 'Visa Cash App Racing Bulls F1 Team', 11500000, NULL),
(9, 'TGR Haas F1 Team', 10000000, NULL),
(10, 'Audi Revolut F1 Team', 13000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2026/audi.png'),
(11, 'Cadillac Formula 1 Team', 12000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2026/cadillac.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `escuderia_equipo_fantasy`
--

CREATE TABLE `escuderia_equipo_fantasy` (
  `id_relacion` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_escuderia` int NOT NULL,
  `fecha_inclusion` datetime DEFAULT CURRENT_TIMESTAMP,
  `slot` int DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_plantilla`
--

CREATE TABLE `historial_plantilla` (
  `id` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_carrera` int NOT NULL COMMENT 'Próxima carrera cuando se hizo el cambio',
  `accion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'fichar|liberar|cambiar_capitan|fichar_escuderia|liberar_escuderia',
  `id_piloto` int DEFAULT NULL,
  `id_escuderia` int DEFAULT NULL,
  `coste_cambio` int DEFAULT '0' COMMENT 'Puntos de penalización si cambio extra',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_precios`
--

CREATE TABLE `historial_precios` (
  `id` int NOT NULL,
  `id_piloto` int NOT NULL,
  `id_carrera` int NOT NULL,
  `precio` int NOT NULL,
  `variacion` int NOT NULL DEFAULT '0' COMMENT 'Diferencia respecto al precio anterior',
  `puntos_carrera` int NOT NULL DEFAULT '0' COMMENT 'Puntos que generó el cambio',
  `fecha` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `historial_precios`
--

INSERT INTO `historial_precios` (`id`, `id_piloto`, `id_carrera`, `precio`, `variacion`, `puntos_carrera`, `fecha`) VALUES
(1, 1, 3, 28400000, 400000, 22, '2026-04-21 10:23:07'),
(2, 2, 3, 8300000, -700000, 1, '2026-04-21 10:23:07'),
(3, 3, 3, 24600000, 600000, 25, '2026-04-21 10:23:07'),
(4, 4, 3, 19500000, -2500000, 15, '2026-04-21 10:23:07'),
(5, 5, 3, 17000000, -3000000, 15, '2026-04-21 10:23:07'),
(6, 6, 3, 13200000, 2200000, 47, '2026-04-21 10:23:07'),
(7, 7, 3, 26900000, 1900000, 17, '2026-04-21 10:23:07'),
(8, 8, 3, 22000000, 3000000, 28, '2026-04-21 10:23:07'),
(9, 9, 3, 16000000, 0, -10, '2026-04-21 10:23:07'),
(10, 10, 3, 10000000, 0, -10, '2026-04-21 10:23:07'),
(11, 11, 3, 14600000, 1600000, 13, '2026-04-21 10:23:07'),
(12, 12, 3, 11200000, 2200000, 2, '2026-04-21 10:23:07'),
(13, 13, 3, 11800000, -200000, -10, '2026-04-21 10:23:07'),
(14, 14, 3, 17000000, 0, 0, '2026-04-21 10:23:07'),
(15, 15, 3, 10500000, 1500000, 1, '2026-04-21 10:23:07'),
(16, 16, 3, 15000000, 3000000, 31, '2026-04-21 10:23:07'),
(17, 17, 3, 7000000, -3000000, -10, '2026-04-21 10:23:07'),
(18, 18, 3, 14000000, 3000000, 11, '2026-04-21 10:23:07'),
(19, 19, 3, 16000000, 3000000, 12, '2026-04-21 10:23:07'),
(20, 20, 3, 10700000, 1700000, 1, '2026-04-21 10:23:07'),
(21, 21, 3, 17000000, 3000000, 8, '2026-04-21 10:23:07'),
(22, 22, 3, 10000000, 0, -10, '2026-04-21 10:23:07'),
(23, 1, 2, 25400000, -3000000, -5, '2026-04-21 10:23:12'),
(24, 2, 2, 11300000, 3000000, 14, '2026-04-21 10:23:12'),
(25, 3, 2, 23400000, -1200000, 19, '2026-04-21 10:23:12'),
(26, 4, 2, 17300000, -2200000, 22, '2026-04-21 10:23:12'),
(27, 5, 2, 14600000, -2400000, 25, '2026-04-21 10:23:12'),
(28, 6, 2, 16200000, 3000000, 47, '2026-04-21 10:23:12'),
(29, 7, 2, 23900000, -3000000, -5, '2026-04-21 10:23:12'),
(30, 8, 2, 22000000, 0, -5, '2026-04-21 10:23:12'),
(31, 9, 2, 16000000, 0, -10, '2026-04-21 10:23:12'),
(32, 10, 2, 10000000, 0, -10, '2026-04-21 10:23:12'),
(33, 11, 2, 17600000, 3000000, 18, '2026-04-21 10:23:12'),
(34, 12, 2, 11600000, 400000, -8, '2026-04-21 10:23:12'),
(35, 13, 2, 11400000, -400000, -10, '2026-04-21 10:23:12'),
(36, 14, 2, 17000000, 0, 0, '2026-04-21 10:23:12'),
(37, 15, 2, 9900000, -600000, -8, '2026-04-21 10:23:12'),
(38, 16, 2, 18000000, 3000000, 35, '2026-04-21 10:23:12'),
(39, 17, 2, 10000000, 3000000, 42, '2026-04-21 10:23:12'),
(40, 18, 2, 14000000, 0, -8, '2026-04-21 10:23:12'),
(41, 19, 2, 16000000, 0, -8, '2026-04-21 10:23:12'),
(42, 20, 2, 9700000, -1000000, -10, '2026-04-21 10:23:12'),
(43, 21, 2, 17000000, 0, -10, '2026-04-21 10:23:12'),
(44, 22, 2, 10000000, 0, -10, '2026-04-21 10:23:12'),
(45, 1, 1, 28400000, 3000000, 45, '2026-04-21 10:23:25'),
(46, 2, 1, 10300000, -1000000, -5, '2026-04-21 10:23:25'),
(47, 3, 1, 26400000, 3000000, 25, '2026-04-21 10:23:25'),
(48, 4, 1, 20300000, 3000000, 33, '2026-04-21 10:23:25'),
(49, 5, 1, 17600000, 3000000, 37, '2026-04-21 10:23:25'),
(50, 6, 1, 19200000, 3000000, 25, '2026-04-21 10:23:25'),
(51, 7, 1, 26900000, 3000000, 20, '2026-04-21 10:23:25'),
(52, 8, 1, 21000000, -1000000, -5, '2026-04-21 10:23:25'),
(53, 9, 1, 14000000, -2000000, -10, '2026-04-21 10:23:25'),
(54, 10, 1, 8000000, -2000000, -10, '2026-04-21 10:23:25'),
(55, 11, 1, 16000000, -1600000, -8, '2026-04-21 10:23:25'),
(56, 12, 1, 9600000, -2000000, -10, '2026-04-21 10:23:25'),
(57, 13, 1, 9800000, -1600000, -8, '2026-04-21 10:23:25'),
(58, 14, 1, 17000000, 0, 0, '2026-04-21 10:23:25'),
(59, 15, 1, 8900000, -1000000, -5, '2026-04-21 10:23:25'),
(60, 16, 1, 17000000, -1000000, -5, '2026-04-21 10:23:25'),
(61, 17, 1, 8400000, -1600000, -8, '2026-04-21 10:23:25'),
(62, 18, 1, 12400000, -1600000, -8, '2026-04-21 10:23:25'),
(63, 19, 1, 14400000, -1600000, -8, '2026-04-21 10:23:25'),
(64, 20, 1, 8700000, -1000000, -5, '2026-04-21 10:23:25'),
(65, 21, 1, 15000000, -2000000, -10, '2026-04-21 10:23:25'),
(66, 22, 1, 8000000, -2000000, -10, '2026-04-21 10:23:25');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ligas`
--

CREATE TABLE `ligas` (
  `id_liga` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `descripcion` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `codigo_invitacion` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `id_creador` int NOT NULL COMMENT 'id_usuario del creador',
  `fecha_creacion` datetime DEFAULT CURRENT_TIMESTAMP,
  `activa` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ligas`
--

INSERT INTO `ligas` (`id_liga`, `nombre`, `descripcion`, `codigo_invitacion`, `id_creador`, `fecha_creacion`, `activa`) VALUES
(1, 'Liga de ejemplo', 'Ejemplo', 'FE0EC86C', 1, '2026-04-21 10:24:26', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `liga_miembros`
--

CREATE TABLE `liga_miembros` (
  `id` int NOT NULL,
  `id_liga` int NOT NULL,
  `id_equipo` int NOT NULL,
  `fecha_union` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `liga_miembros`
--

INSERT INTO `liga_miembros` (`id`, `id_liga`, `id_equipo`, `fecha_union`) VALUES
(1, 1, 1, '2026-04-21 10:24:26'),
(2, 1, 2, '2026-04-21 10:25:09');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pilotos`
--

CREATE TABLE `pilotos` (
  `id_piloto` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `numero` int NOT NULL,
  `nacionalidad` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `id_escuderia` int NOT NULL,
  `precio` int NOT NULL,
  `precio_inicial` int NOT NULL DEFAULT '0' COMMENT 'Precio al inicio de temporada, no cambia',
  `precio_anterior` int NOT NULL DEFAULT '0' COMMENT 'Precio antes de la última actualización',
  `imagen_url` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pilotos`
--

INSERT INTO `pilotos` (`id_piloto`, `nombre`, `numero`, `nacionalidad`, `id_escuderia`, `precio`, `precio_inicial`, `precio_anterior`, `imagen_url`) VALUES
(1, 'Max Verstappen', 3, 'Neerlandés', 1, 28400000, 28000000, 25400000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/M/MAXVER01_Max_Verstappen/maxver01.png'),
(2, 'Isack Hadjar', 6, 'Francés', 1, 10300000, 9000000, 11300000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/I/ISAHAD01_Isack_Hadjar/isahad01.png'),
(3, 'Charles Leclerc', 16, 'Monegasco', 2, 26400000, 24000000, 23400000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/C/CHALEC01_Charles_Leclerc/chalec01.png'),
(4, 'Lewis Hamilton', 44, 'Británico', 2, 20300000, 22000000, 17300000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/L/LEWHAM01_Lewis_Hamilton/lewham01.png'),
(5, 'George Russell', 63, 'Británico', 3, 17600000, 20000000, 14600000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/G/GEORUS01_George_Russell/georus01.png'),
(6, 'Andrea Kimi Antonelli', 12, 'Italiano', 3, 19200000, 11000000, 16200000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/v1740000001/common/f1/2026/mercedes/andant01/2026mercedesandant01right.webp'),
(7, 'Lando Norris', 1, 'Británico', 4, 26900000, 25000000, 23900000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/L/LANNOR01_Lando_Norris/lannor01.png'),
(8, 'Oscar Piastri', 81, 'Australiano', 4, 21000000, 19000000, 22000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/O/OSCPIA01_Oscar_Piastri/oscpia01.png'),
(9, 'Fernando Alonso', 14, 'Español', 5, 14000000, 16000000, 16000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/F/FERALO01_Fernando_Alonso/feralo01.png'),
(10, 'Lance Stroll', 18, 'Canadiense', 5, 8000000, 10000000, 10000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/L/LANSTR01_Lance_Stroll/lanstr01.png'),
(11, 'Pierre Gasly', 10, 'Francés', 6, 16000000, 13000000, 17600000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/P/PIEGAS01_Pierre_Gasly/piegas01.png'),
(12, 'Franco Colapinto', 43, 'Argentino', 6, 9600000, 9000000, 11600000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/F/FRACOL01_Franco_Colapinto/fracol01.png'),
(13, 'Alexander Albon', 23, 'Tailandés', 7, 9800000, 12000000, 11400000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/A/ALEALB01_Alexander_Albon/alealb01.png'),
(14, 'Carlos Sainz Jr.', 55, 'Español', 7, 17000000, 17000000, 17000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/C/CARSAI01_Carlos_Sainz/carsai01.png'),
(15, 'Arvid Lindblad', 41, 'Británico', 8, 8900000, 9000000, 9900000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/v1740000001/common/f1/2026/racingbulls/arvlin01/2026racingbullsarvlin01right.webp'),
(16, 'Liam Lawson', 30, 'Neozelandés', 8, 17000000, 12000000, 18000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/L/LIALAW01_Liam_Lawson/lialaw01.png'),
(17, 'Oliver Bearman', 87, 'Británico', 9, 8400000, 10000000, 10000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/O/OLIBEA01_Oliver_Bearman/olibea01.png'),
(18, 'Esteban Ocon', 31, 'Francés', 9, 12400000, 11000000, 14000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/E/ESTOCO01_Esteban_Ocon/estoco01.png'),
(19, 'Nico Hülkenberg', 27, 'Alemán', 10, 14400000, 13000000, 16000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/N/NICHUL01_Nico_Hulkenberg/nichul01.png'),
(20, 'Gabriel Bortoleto', 5, 'Brasileño', 10, 8700000, 9000000, 9700000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/G/GABBOR01_Gabriel_Bortoleto/gabbor01.png'),
(21, 'Sergio Pérez', 11, 'Mexicano', 11, 15000000, 14000000, 17000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/S/SERPER01_Sergio_Perez/serper01.png'),
(22, 'Valtteri Bottas', 77, 'Finlandés', 11, 8000000, 10000000, 10000000, 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/drivers/V/VALBOT01_Valtteri_Bottas/valbot01.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pilotos_equipo_fantasy`
--

CREATE TABLE `pilotos_equipo_fantasy` (
  `id_piloto_equipo` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_piloto` int NOT NULL,
  `fecha_inclusion` datetime DEFAULT CURRENT_TIMESTAMP,
  `es_capitan` tinyint(1) DEFAULT '0' COMMENT '1 = capitán (puntos x2)',
  `slot` int DEFAULT NULL COMMENT 'Posición en plantilla 1-5'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `pilotos_equipo_fantasy`
--

INSERT INTO `pilotos_equipo_fantasy` (`id_piloto_equipo`, `id_equipo`, `id_piloto`, `fecha_inclusion`, `es_capitan`, `slot`) VALUES
(1, 1, 9, '2026-03-23 10:13:44', 1, 1),
(2, 1, 3, '2026-03-23 10:13:50', 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_desglose`
--

CREATE TABLE `puntos_desglose` (
  `id` int NOT NULL,
  `id_resultado` int NOT NULL,
  `criterio` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL COMMENT 'posicion|pole|q3|q2|vuelta_rapida|sector|adelantamiento|bonus_supero|retroceso|abandono|dsq|bandera_amarilla|bandera_roja|penalizacion|termino',
  `puntos` int NOT NULL COMMENT 'Puede ser negativo',
  `descripcion` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puntos_desglose`
--

INSERT INTO `puntos_desglose` (`id`, `id_resultado`, `criterio`, `puntos`, `descripcion`) VALUES
(502, 190, 'posicion', 25, 'P1 en carrera'),
(503, 190, 'pole', 10, 'Pole position'),
(504, 190, 'termino', 2, 'Completó la carrera'),
(505, 190, 'vuelta_rapida', 10, 'Vuelta rápida (+10 pts)'),
(506, 191, 'posicion', 18, 'P2 en carrera'),
(507, 191, 'q3', 5, 'Q3 — P3 parrilla'),
(508, 191, 'termino', 2, 'Completó la carrera'),
(509, 191, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(510, 192, 'posicion', 15, 'P3 en carrera'),
(511, 192, 'q3', 5, 'Q3 — P4 parrilla'),
(512, 192, 'termino', 2, 'Completó la carrera'),
(513, 192, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(514, 193, 'posicion', 12, 'P4 en carrera'),
(515, 193, 'q3', 5, 'Q3 — P2 parrilla'),
(516, 193, 'termino', 2, 'Completó la carrera'),
(517, 193, 'retroceso', -4, '2 pos. perdida(s) (-4 pts)'),
(518, 194, 'posicion', 10, 'P5 en carrera'),
(519, 194, 'q3', 5, 'Q3 — P5 parrilla'),
(520, 194, 'termino', 2, 'Completó la carrera'),
(521, 195, 'posicion', 8, 'P6 en carrera'),
(522, 195, 'q3', 5, 'Q3 — P6 parrilla'),
(523, 195, 'termino', 2, 'Completó la carrera'),
(524, 196, 'posicion', 6, 'P7 en carrera'),
(525, 196, 'q3', 5, 'Q3 — P7 parrilla'),
(526, 196, 'termino', 2, 'Completó la carrera'),
(527, 197, 'posicion', 4, 'P8 en carrera'),
(528, 197, 'q2', 2, 'Q2 — P11 parrilla'),
(529, 197, 'termino', 2, 'Completó la carrera'),
(530, 197, 'adelantamiento', 9, '3 pos. ganada(s) (+9 pts)'),
(531, 197, 'bonus_supero', 5, 'Bonus: superó expectativas 3-4 pos (+5 pts)'),
(532, 198, 'posicion', 2, 'P9 en carrera'),
(533, 198, 'q2', 2, 'Q2 — P14 parrilla'),
(534, 198, 'termino', 2, 'Completó la carrera'),
(535, 198, 'adelantamiento', 15, '5 pos. ganada(s) (+15 pts)'),
(536, 198, 'bonus_supero', 10, 'Bonus: superó expectativas ≥5 pos (+10 pts)'),
(537, 199, 'posicion', 1, 'P10 en carrera'),
(538, 199, 'q2', 2, 'Q2 — P12 parrilla'),
(539, 199, 'termino', 2, 'Completó la carrera'),
(540, 199, 'adelantamiento', 6, '2 pos. ganada(s) (+6 pts)'),
(541, 200, 'posicion', 2, 'P11 en carrera'),
(542, 200, 'q2', 2, 'Q2 — P13 parrilla'),
(543, 200, 'termino', 2, 'Completó la carrera'),
(544, 200, 'adelantamiento', 6, '2 pos. ganada(s) (+6 pts)'),
(545, 201, 'posicion', 2, 'P12 en carrera'),
(546, 201, 'q3', 5, 'Q3 — P8 parrilla'),
(547, 201, 'termino', 2, 'Completó la carrera'),
(548, 201, 'retroceso', -8, '4 pos. perdida(s) (-8 pts)'),
(549, 202, 'posicion', 2, 'P13 en carrera'),
(550, 202, 'q3', 5, 'Q3 — P9 parrilla'),
(551, 202, 'termino', 2, 'Completó la carrera'),
(552, 202, 'retroceso', -8, '4 pos. perdida(s) (-8 pts)'),
(553, 203, 'posicion', 2, 'P14 en carrera'),
(554, 203, 'q3', 5, 'Q3 — P10 parrilla'),
(555, 203, 'termino', 2, 'Completó la carrera'),
(556, 203, 'retroceso', -8, '4 pos. perdida(s) (-8 pts)'),
(557, 204, 'posicion', 0, 'P16 en carrera'),
(558, 204, 'q2', 2, 'Q2 — P15 parrilla'),
(559, 204, 'termino', 2, 'Completó la carrera'),
(560, 204, 'retroceso', -2, '1 pos. perdida(s) (-2 pts)'),
(561, 205, 'posicion', 0, 'P17 en carrera'),
(562, 205, 'termino', 2, 'Completó la carrera'),
(563, 205, 'adelantamiento', 6, '2 pos. ganada(s) (+6 pts)'),
(564, 206, 'abandono', -10, 'Abandono / DNF'),
(565, 207, 'abandono', -10, 'Abandono / DNF'),
(566, 208, 'abandono', -10, 'Abandono / DNF'),
(567, 209, 'abandono', -10, 'Abandono / DNF'),
(568, 210, 'abandono', -10, 'Abandono / DNF'),
(569, 211, 'posicion', 25, 'P1 en carrera'),
(570, 211, 'pole', 10, 'Pole position'),
(571, 211, 'termino', 2, 'Completó la carrera'),
(572, 211, 'vuelta_rapida', 10, 'Vuelta rápida (+10 pts)'),
(573, 212, 'posicion', 18, 'P2 en carrera'),
(574, 212, 'q3', 5, 'Q3 — P2 parrilla'),
(575, 212, 'termino', 2, 'Completó la carrera'),
(576, 213, 'posicion', 15, 'P3 en carrera'),
(577, 213, 'q3', 5, 'Q3 — P3 parrilla'),
(578, 213, 'termino', 2, 'Completó la carrera'),
(579, 214, 'posicion', 12, 'P4 en carrera'),
(580, 214, 'q3', 5, 'Q3 — P4 parrilla'),
(581, 214, 'termino', 2, 'Completó la carrera'),
(582, 215, 'posicion', 10, 'P5 en carrera'),
(583, 215, 'q3', 5, 'Q3 — P10 parrilla'),
(584, 215, 'termino', 2, 'Completó la carrera'),
(585, 215, 'adelantamiento', 15, '5 pos. ganada(s) (+15 pts)'),
(586, 215, 'bonus_supero', 10, 'Bonus: superó expectativas ≥5 pos (+10 pts)'),
(587, 216, 'posicion', 8, 'P6 en carrera'),
(588, 216, 'q3', 5, 'Q3 — P7 parrilla'),
(589, 216, 'termino', 2, 'Completó la carrera'),
(590, 216, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(591, 217, 'posicion', 6, 'P7 en carrera'),
(592, 217, 'q2', 2, 'Q2 — P14 parrilla'),
(593, 217, 'termino', 2, 'Completó la carrera'),
(594, 217, 'adelantamiento', 15, '7 pos. ganada(s) (+15 pts)'),
(595, 217, 'bonus_supero', 10, 'Bonus: superó expectativas ≥5 pos (+10 pts)'),
(596, 218, 'posicion', 4, 'P8 en carrera'),
(597, 218, 'q3', 5, 'Q3 — P9 parrilla'),
(598, 218, 'termino', 2, 'Completó la carrera'),
(599, 218, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(600, 219, 'q2', 2, 'Q2 — P12 parrilla'),
(601, 219, 'abandono', -10, 'Abandono / DNF'),
(602, 220, 'q2', 2, 'Q2 — P11 parrilla'),
(603, 220, 'abandono', -10, 'Abandono / DNF'),
(604, 221, 'q2', 2, 'Q2 — P15 parrilla'),
(605, 221, 'abandono', -10, 'Abandono / DNF'),
(606, 222, 'abandono', -10, 'Abandono / DNF'),
(607, 223, 'q2', 2, 'Q2 — P13 parrilla'),
(608, 223, 'abandono', -10, 'Abandono / DNF'),
(609, 224, 'abandono', -10, 'Abandono / DNF'),
(610, 225, 'q3', 5, 'Q3 — P8 parrilla'),
(611, 225, 'abandono', -10, 'Abandono / DNF'),
(612, 226, 'abandono', -10, 'Abandono / DNF'),
(613, 227, 'abandono', -10, 'Abandono / DNF'),
(614, 228, 'q3', 5, 'Q3 — P5 parrilla'),
(615, 228, 'abandono', -10, 'Abandono / DNF'),
(616, 229, 'q3', 5, 'Q3 — P6 parrilla'),
(617, 229, 'abandono', -10, 'Abandono / DNF'),
(618, 230, 'abandono', -10, 'Abandono / DNF'),
(619, 231, 'abandono', -10, 'Abandono / DNF'),
(620, 232, 'posicion', 25, 'P1 en carrera'),
(621, 232, 'pole', 10, 'Pole position'),
(622, 232, 'termino', 2, 'Completó la carrera'),
(623, 233, 'posicion', 18, 'P2 en carrera'),
(624, 233, 'q3', 5, 'Q3 — P2 parrilla'),
(625, 233, 'termino', 2, 'Completó la carrera'),
(626, 234, 'posicion', 15, 'P3 en carrera'),
(627, 234, 'q3', 5, 'Q3 — P4 parrilla'),
(628, 234, 'termino', 2, 'Completó la carrera'),
(629, 234, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(630, 235, 'posicion', 12, 'P4 en carrera'),
(631, 235, 'q3', 5, 'Q3 — P7 parrilla'),
(632, 235, 'termino', 2, 'Completó la carrera'),
(633, 235, 'adelantamiento', 9, '3 pos. ganada(s) (+9 pts)'),
(634, 235, 'bonus_supero', 5, 'Bonus: superó expectativas 3-4 pos (+5 pts)'),
(635, 236, 'posicion', 10, 'P5 en carrera'),
(636, 236, 'q3', 5, 'Q3 — P6 parrilla'),
(637, 236, 'termino', 2, 'Completó la carrera'),
(638, 236, 'adelantamiento', 3, '1 pos. ganada(s) (+3 pts)'),
(639, 237, 'posicion', 8, 'P6 en carrera'),
(640, 237, 'termino', 2, 'Completó la carrera'),
(641, 237, 'vuelta_rapida', 10, 'Vuelta rápida (+10 pts)'),
(642, 237, 'adelantamiento', 15, '14 pos. ganada(s) (+15 pts)'),
(643, 237, 'bonus_supero', 10, 'Bonus: superó expectativas ≥5 pos (+10 pts)'),
(644, 238, 'q2', 2, 'Q2 — P12 parrilla'),
(645, 238, 'abandono', -10, 'Abandono / DNF'),
(646, 239, 'q3', 5, 'Q3 — P9 parrilla'),
(647, 239, 'abandono', -10, 'Abandono / DNF'),
(648, 240, 'q3', 5, 'Q3 — P10 parrilla'),
(649, 240, 'abandono', -10, 'Abandono / DNF'),
(650, 241, 'q2', 2, 'Q2 — P14 parrilla'),
(651, 241, 'abandono', -10, 'Abandono / DNF'),
(652, 242, 'q2', 2, 'Q2 — P13 parrilla'),
(653, 242, 'abandono', -10, 'Abandono / DNF'),
(654, 243, 'q2', 2, 'Q2 — P15 parrilla'),
(655, 243, 'abandono', -10, 'Abandono / DNF'),
(656, 244, 'q3', 5, 'Q3 — P8 parrilla'),
(657, 244, 'abandono', -10, 'Abandono / DNF'),
(658, 245, 'abandono', -10, 'Abandono / DNF'),
(659, 246, 'abandono', -10, 'Abandono / DNF'),
(660, 247, 'abandono', -10, 'Abandono / DNF'),
(661, 248, 'abandono', -10, 'Abandono / DNF'),
(662, 249, 'abandono', -10, 'Abandono / DNF'),
(663, 250, 'q3', 5, 'Q3 — P3 parrilla'),
(664, 250, 'abandono', -10, 'Abandono / DNF'),
(665, 251, 'q3', 5, 'Q3 — P5 parrilla'),
(666, 251, 'abandono', -10, 'Abandono / DNF'),
(667, 252, 'q2', 2, 'Q2 — P11 parrilla'),
(668, 252, 'abandono', -10, 'Abandono / DNF');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_escuderia_fantasy`
--

CREATE TABLE `puntos_escuderia_fantasy` (
  `id` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_escuderia` int NOT NULL,
  `id_carrera` int NOT NULL,
  `puntos` int NOT NULL DEFAULT '0',
  `detalle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_fantasy`
--

CREATE TABLE `puntos_fantasy` (
  `id_punto` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_piloto` int NOT NULL,
  `id_carrera` int NOT NULL,
  `puntos` int NOT NULL,
  `detalle` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `es_capitan` tinyint(1) DEFAULT '0' COMMENT '1 si era capitán (puntos ya duplicados)',
  `puntos_base` int DEFAULT '0' COMMENT 'Puntos antes de x2',
  `es_escuderia` tinyint(1) DEFAULT '0' COMMENT 'Reservado (no usado)'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puntos_fantasy`
--

INSERT INTO `puntos_fantasy` (`id_punto`, `id_equipo`, `id_piloto`, `id_carrera`, `puntos`, `detalle`, `es_capitan`, `puntos_base`, `es_escuderia`) VALUES
(19, 1, 3, 3, 25, 'P3 en carrera | Q3 — P4 parrilla | Completó la carrera | 1 pos. ganada(s) (+3 pts)', 0, 25, 0),
(20, 1, 9, 3, -20, 'Abandono / DNF', 1, -10, 0),
(21, 1, 3, 2, 19, 'P4 en carrera | Q3 — P4 parrilla | Completó la carrera', 0, 19, 0),
(22, 1, 9, 2, -20, 'Abandono / DNF', 1, -10, 0),
(23, 1, 3, 1, 25, 'P3 en carrera | Q3 — P4 parrilla | Completó la carrera | 1 pos. ganada(s) (+3 pts)', 0, 25, 0),
(24, 1, 9, 1, -20, 'Abandono / DNF', 1, -10, 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `puntos_fantasy_carrera`
--

CREATE TABLE `puntos_fantasy_carrera` (
  `id` int NOT NULL,
  `id_equipo` int NOT NULL,
  `id_carrera` int NOT NULL,
  `puntos_brutos` int DEFAULT '0' COMMENT 'Suma pilotos + escudería sin capitán',
  `bonus_capitan` int DEFAULT '0' COMMENT 'Puntos extra por capitán (pts_cap * 1)',
  `penalizacion` int DEFAULT '0' COMMENT 'Puntos perdidos por cambios extra',
  `puntos_total` int DEFAULT '0' COMMENT 'Total final de la jornada',
  `posicion_jornada` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `puntos_fantasy_carrera`
--

INSERT INTO `puntos_fantasy_carrera` (`id`, `id_equipo`, `id_carrera`, `puntos_brutos`, `bonus_capitan`, `penalizacion`, `puntos_total`, `posicion_jornada`) VALUES
(1, 1, 3, 15, -10, 0, 5, 1),
(2, 1, 2, 9, -10, 0, -1, 2),
(3, 1, 1, 15, -10, 0, 5, 1),
(11, 2, 3, 0, 0, 0, 0, 2),
(13, 2, 2, 0, 0, 0, 0, 1),
(15, 2, 1, 0, 0, 0, 0, 2);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `resultados_carrera`
--

CREATE TABLE `resultados_carrera` (
  `id_resultado` int NOT NULL,
  `id_carrera` int NOT NULL,
  `id_piloto` int NOT NULL,
  `posicion` int DEFAULT NULL,
  `puntos_oficiales` int DEFAULT '0',
  `vuelta_rapida` tinyint(1) DEFAULT '0',
  `estado` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL,
  `posicion_salida` int DEFAULT NULL COMMENT 'Posición en parrilla / qualy',
  `adelantamientos` int DEFAULT '0' COMMENT 'Posiciones ganadas en carrera',
  `banderas_amarillas` int DEFAULT '0' COMMENT 'Causadas por el piloto',
  `banderas_rojas` int DEFAULT '0' COMMENT 'Causadas por el piloto',
  `penalizaciones` int DEFAULT '0' COMMENT 'Número de penalizaciones recibidas',
  `mejor_sector` tinyint(1) DEFAULT '0' COMMENT 'Consiguió el mejor sector de la carrera',
  `pole_position` tinyint(1) DEFAULT '0' COMMENT 'Salió desde la pole',
  `fuente_datos` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'manual' COMMENT 'manual|api_jolpica|api_ergast',
  `puntos_fantasy` int NOT NULL DEFAULT '0' COMMENT 'Puntos fantasy calculados para este piloto en esta carrera'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `resultados_carrera`
--

INSERT INTO `resultados_carrera` (`id_resultado`, `id_carrera`, `id_piloto`, `posicion`, `puntos_oficiales`, `vuelta_rapida`, `estado`, `posicion_salida`, `adelantamientos`, `banderas_amarillas`, `banderas_rojas`, `penalizaciones`, `mejor_sector`, `pole_position`, `fuente_datos`, `puntos_fantasy`) VALUES
(190, 3, 6, 1, 25, 1, 'finished', 1, 0, 0, 0, 0, 0, 1, 'api_jolpica', 47),
(191, 3, 8, 2, 18, 0, 'finished', 3, 1, 0, 0, 0, 0, 0, 'api_jolpica', 28),
(192, 3, 3, 3, 15, 0, 'finished', 4, 1, 0, 0, 0, 0, 0, 'api_jolpica', 25),
(193, 3, 5, 4, 12, 0, 'finished', 2, 0, 0, 0, 0, 0, 0, 'api_jolpica', 15),
(194, 3, 7, 5, 10, 0, 'finished', 5, 0, 0, 0, 0, 0, 0, 'api_jolpica', 17),
(195, 3, 4, 6, 8, 0, 'finished', 6, 0, 0, 0, 0, 0, 0, 'api_jolpica', 15),
(196, 3, 11, 7, 6, 0, 'finished', 7, 0, 0, 0, 0, 0, 0, 'api_jolpica', 13),
(197, 3, 1, 8, 4, 0, 'finished', 11, 3, 0, 0, 0, 0, 0, 'api_jolpica', 22),
(198, 3, 16, 9, 2, 0, 'finished', 14, 5, 0, 0, 0, 0, 0, 'api_jolpica', 31),
(199, 3, 18, 10, 1, 0, 'finished', 12, 2, 0, 0, 0, 0, 0, 'api_jolpica', 11),
(200, 3, 19, 11, 0, 0, 'finished', 13, 2, 0, 0, 0, 0, 0, 'api_jolpica', 12),
(201, 3, 2, 12, 0, 0, 'finished', 8, 0, 0, 0, 0, 0, 0, 'api_jolpica', 1),
(202, 3, 20, 13, 0, 0, 'finished', 9, 0, 0, 0, 0, 0, 0, 'api_jolpica', 1),
(203, 3, 15, 14, 0, 0, 'finished', 10, 0, 0, 0, 0, 0, 0, 'api_jolpica', 1),
(204, 3, 12, 16, 0, 0, 'finished', 15, 0, 0, 0, 0, 0, 0, 'api_jolpica', 2),
(205, 3, 21, 17, 0, 0, 'finished', 19, 2, 0, 0, 0, 0, 0, 'api_jolpica', 8),
(206, 3, 9, 18, 0, 0, 'dnf', 21, 3, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(207, 3, 22, 19, 0, 0, 'dnf', 20, 1, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(208, 3, 13, 20, 0, 0, 'dnf', 17, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(209, 3, 10, 21, 0, 0, 'dnf', 22, 1, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(210, 3, 17, 22, 0, 0, 'dnf', 18, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(211, 2, 6, 1, 25, 1, 'finished', 1, 0, 0, 0, 0, 0, 1, 'api_jolpica', 47),
(212, 2, 5, 2, 18, 0, 'finished', 2, 0, 0, 0, 0, 0, 0, 'api_jolpica', 25),
(213, 2, 4, 3, 15, 0, 'finished', 3, 0, 0, 0, 0, 0, 0, 'api_jolpica', 22),
(214, 2, 3, 4, 12, 0, 'finished', 4, 0, 0, 0, 0, 0, 0, 'api_jolpica', 19),
(215, 2, 17, 5, 10, 0, 'finished', 10, 5, 0, 0, 0, 0, 0, 'api_jolpica', 42),
(216, 2, 11, 6, 8, 0, 'finished', 7, 1, 0, 0, 0, 0, 0, 'api_jolpica', 18),
(217, 2, 16, 7, 6, 0, 'finished', 14, 7, 0, 0, 0, 0, 0, 'api_jolpica', 35),
(218, 2, 2, 8, 4, 0, 'finished', 9, 1, 0, 0, 0, 0, 0, 'api_jolpica', 14),
(219, 2, 12, 10, 1, 0, 'dnf', 12, 2, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(220, 2, 19, 11, 0, 0, 'dnf', 11, 0, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(221, 2, 15, 12, 0, 0, 'dnf', 15, 3, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(222, 2, 22, 13, 0, 0, 'dnf', 20, 6, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(223, 2, 18, 14, 0, 0, 'dnf', 13, 0, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(224, 2, 21, 15, 0, 0, 'dnf', 22, 6, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(225, 2, 1, 16, 0, 0, 'dnf', 8, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(226, 2, 9, 17, 0, 0, 'dnf', 19, 1, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(227, 2, 10, 18, 0, 0, 'dnf', 21, 2, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(228, 2, 8, 19, 0, 0, 'dnf', 5, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(229, 2, 7, 20, 0, 0, 'dnf', 6, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(230, 2, 20, 21, 0, 0, 'dnf', 16, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(231, 2, 13, 22, 0, 0, 'dnf', 18, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(232, 1, 5, 1, 25, 0, 'finished', 1, 0, 0, 0, 0, 0, 1, 'api_jolpica', 37),
(233, 1, 6, 2, 18, 0, 'finished', 2, 0, 0, 0, 0, 0, 0, 'api_jolpica', 25),
(234, 1, 3, 3, 15, 0, 'finished', 4, 1, 0, 0, 0, 0, 0, 'api_jolpica', 25),
(235, 1, 4, 4, 12, 0, 'finished', 7, 3, 0, 0, 0, 0, 0, 'api_jolpica', 33),
(236, 1, 7, 5, 10, 0, 'finished', 6, 1, 0, 0, 0, 0, 0, 'api_jolpica', 20),
(237, 1, 1, 6, 8, 1, 'finished', 20, 14, 0, 0, 0, 0, 0, 'api_jolpica', 45),
(238, 1, 17, 7, 6, 0, 'dnf', 12, 5, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(239, 1, 15, 8, 4, 0, 'dnf', 9, 1, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(240, 1, 20, 9, 2, 0, 'dnf', 10, 1, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(241, 1, 11, 10, 1, 0, 'dnf', 14, 4, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(242, 1, 18, 11, 0, 0, 'dnf', 13, 2, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(243, 1, 13, 12, 0, 0, 'dnf', 15, 3, 0, 0, 0, 0, 0, 'api_jolpica', -8),
(244, 1, 16, 13, 0, 0, 'dnf', 8, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(245, 1, 12, 14, 0, 0, 'dnf', 16, 2, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(246, 1, 21, 16, 0, 0, 'dnf', 18, 2, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(247, 1, 10, 17, 0, 0, 'dnf', 22, 5, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(248, 1, 9, 18, 0, 0, 'dnf', 17, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(249, 1, 22, 19, 0, 0, 'dnf', 19, 0, 0, 0, 0, 0, 0, 'api_jolpica', -10),
(250, 1, 2, 20, 0, 0, 'dnf', 3, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(251, 1, 8, 21, 0, 0, 'dnf', 5, 0, 0, 0, 0, 0, 0, 'api_jolpica', -5),
(252, 1, 19, 22, 0, 0, 'dnf', 11, 0, 0, 0, 0, 0, 0, 'api_jolpica', -8);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sync_log`
--

CREATE TABLE `sync_log` (
  `id` int NOT NULL,
  `id_carrera` int NOT NULL,
  `fecha_sync` datetime DEFAULT CURRENT_TIMESTAMP,
  `estado` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT 'ok' COMMENT 'ok|error|parcial',
  `mensaje` text CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sync_log`
--

INSERT INTO `sync_log` (`id`, `id_carrera`, `fecha_sync`, `estado`, `mensaje`) VALUES
(1, 3, '2026-04-20 10:21:30', 'ok', 'Procesados: 21, Errores: 0'),
(2, 2, '2026-04-20 10:21:31', 'ok', 'Procesados: 21, Errores: 0'),
(3, 1, '2026-04-20 10:21:32', 'ok', 'Procesados: 21, Errores: 0'),
(4, 3, '2026-04-21 10:00:37', 'ok', 'Procesados: 21, Errores: 0'),
(5, 2, '2026-04-21 10:00:38', 'ok', 'Procesados: 21, Errores: 0'),
(6, 1, '2026-04-21 10:01:02', 'ok', 'Procesados: 21, Errores: 0'),
(7, 1, '2026-04-21 10:01:17', 'ok', 'Procesados: 21, Errores: 0'),
(8, 2, '2026-04-21 10:01:22', 'ok', 'Procesados: 21, Errores: 0'),
(9, 3, '2026-04-21 10:01:26', 'ok', 'Procesados: 21, Errores: 0'),
(10, 3, '2026-04-21 10:23:07', 'ok', 'Procesados: 21, Errores: 0'),
(11, 2, '2026-04-21 10:23:12', 'ok', 'Procesados: 21, Errores: 0'),
(12, 1, '2026-04-21 10:23:25', 'ok', 'Procesados: 21, Errores: 0');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temporadas`
--

CREATE TABLE `temporadas` (
  `id_temporada` int NOT NULL,
  `anio` int NOT NULL,
  `activa` tinyint DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `temporadas`
--

INSERT INTO `temporadas` (`id_temporada`, `anio`, `activa`) VALUES
(1, 2026, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id_usuario` int NOT NULL,
  `nombre` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `correo` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `contrasena_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
  `fecha_registro` datetime DEFAULT CURRENT_TIMESTAMP,
  `rol` enum('admin','usuario') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL DEFAULT 'usuario'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id_usuario`, `nombre`, `correo`, `contrasena_hash`, `fecha_registro`, `rol`) VALUES
(1, 'Daniel', 'ejemplo@ejemplo.com', '$2y$12$nynXzVUzSSmush58tA1gzOk8/FCFEYbNx9ZgCWI08H9yCPRrKSqOi', '2026-03-23 09:57:26', 'admin'),
(2, 'usuario', 'usuario@usuario.com', '$2y$12$O9FNGBjuA.qyflZ54piRFefOVJDnUfB9hPeBtckRDAPRJJeKEs5VC', '2026-04-21 10:02:33', 'usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventana_mercado`
--

CREATE TABLE `ventana_mercado` (
  `id` int NOT NULL,
  `id_carrera_desde` int NOT NULL COMMENT 'Abierta después de esta carrera',
  `id_carrera_hasta` int NOT NULL COMMENT 'Cierra antes de esta carrera',
  `abierto` tinyint(1) DEFAULT '1',
  `cambios_gratis` int DEFAULT '3' COMMENT 'Cambios gratuitos en esta ventana',
  `coste_extra` int DEFAULT '4' COMMENT 'Puntos perdidos por cambio extra'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventana_mercado`
--

INSERT INTO `ventana_mercado` (`id`, `id_carrera_desde`, `id_carrera_hasta`, `abierto`, `cambios_gratis`, `coste_extra`) VALUES
(1, 1, 2, 1, 3, 4),
(2, 2, 3, 1, 3, 4),
(3, 3, 4, 1, 3, 4),
(4, 4, 5, 1, 3, 4),
(5, 5, 6, 1, 3, 4),
(6, 6, 7, 1, 3, 4),
(7, 7, 8, 1, 3, 4),
(8, 8, 9, 1, 3, 4),
(9, 9, 10, 1, 3, 4),
(10, 10, 11, 1, 3, 4),
(11, 11, 12, 1, 3, 4),
(12, 12, 13, 1, 3, 4),
(13, 13, 14, 1, 3, 4),
(14, 14, 15, 1, 3, 4),
(15, 15, 16, 1, 3, 4),
(16, 16, 17, 1, 3, 4),
(17, 17, 18, 1, 3, 4),
(18, 18, 19, 1, 3, 4),
(19, 19, 20, 1, 3, 4),
(20, 20, 21, 1, 3, 4),
(21, 21, 22, 1, 3, 4),
(22, 22, 23, 1, 3, 4),
(23, 23, 24, 1, 3, 4);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD PRIMARY KEY (`id_carrera`),
  ADD KEY `id_temporada` (`id_temporada`);

--
-- Indices de la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  ADD PRIMARY KEY (`id_clasificacion`),
  ADD UNIQUE KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `equipos_fantasy`
--
ALTER TABLE `equipos_fantasy`
  ADD PRIMARY KEY (`id_equipo`),
  ADD KEY `id_usuario` (`id_usuario`);

--
-- Indices de la tabla `escuderias`
--
ALTER TABLE `escuderias`
  ADD PRIMARY KEY (`id_escuderia`);

--
-- Indices de la tabla `escuderia_equipo_fantasy`
--
ALTER TABLE `escuderia_equipo_fantasy`
  ADD PRIMARY KEY (`id_relacion`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_escuderia` (`id_escuderia`);

--
-- Indices de la tabla `historial_plantilla`
--
ALTER TABLE `historial_plantilla`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `piloto_carrera` (`id_piloto`,`id_carrera`),
  ADD KEY `id_piloto` (`id_piloto`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD PRIMARY KEY (`id_liga`),
  ADD UNIQUE KEY `codigo_invitacion` (`codigo_invitacion`),
  ADD KEY `id_creador` (`id_creador`);

--
-- Indices de la tabla `liga_miembros`
--
ALTER TABLE `liga_miembros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `liga_equipo` (`id_liga`,`id_equipo`),
  ADD KEY `id_liga` (`id_liga`),
  ADD KEY `id_equipo` (`id_equipo`);

--
-- Indices de la tabla `pilotos`
--
ALTER TABLE `pilotos`
  ADD PRIMARY KEY (`id_piloto`),
  ADD KEY `id_escuderia` (`id_escuderia`);

--
-- Indices de la tabla `pilotos_equipo_fantasy`
--
ALTER TABLE `pilotos_equipo_fantasy`
  ADD PRIMARY KEY (`id_piloto_equipo`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_piloto` (`id_piloto`);

--
-- Indices de la tabla `puntos_desglose`
--
ALTER TABLE `puntos_desglose`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_resultado` (`id_resultado`);

--
-- Indices de la tabla `puntos_escuderia_fantasy`
--
ALTER TABLE `puntos_escuderia_fantasy`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_escuderia` (`id_escuderia`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `puntos_fantasy`
--
ALTER TABLE `puntos_fantasy`
  ADD PRIMARY KEY (`id_punto`),
  ADD KEY `id_equipo` (`id_equipo`),
  ADD KEY `id_piloto` (`id_piloto`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `puntos_fantasy_carrera`
--
ALTER TABLE `puntos_fantasy_carrera`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `equipo_carrera` (`id_equipo`,`id_carrera`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `resultados_carrera`
--
ALTER TABLE `resultados_carrera`
  ADD PRIMARY KEY (`id_resultado`),
  ADD KEY `id_carrera` (`id_carrera`),
  ADD KEY `id_piloto` (`id_piloto`);

--
-- Indices de la tabla `sync_log`
--
ALTER TABLE `sync_log`
  ADD PRIMARY KEY (`id`),
  ADD KEY `id_carrera` (`id_carrera`);

--
-- Indices de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  ADD PRIMARY KEY (`id_temporada`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id_usuario`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `ventana_mercado`
--
ALTER TABLE `ventana_mercado`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `carreras`
--
ALTER TABLE `carreras`
  MODIFY `id_carrera` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  MODIFY `id_clasificacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `equipos_fantasy`
--
ALTER TABLE `equipos_fantasy`
  MODIFY `id_equipo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `escuderias`
--
ALTER TABLE `escuderias`
  MODIFY `id_escuderia` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT de la tabla `escuderia_equipo_fantasy`
--
ALTER TABLE `escuderia_equipo_fantasy`
  MODIFY `id_relacion` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_plantilla`
--
ALTER TABLE `historial_plantilla`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_precios`
--
ALTER TABLE `historial_precios`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=67;

--
-- AUTO_INCREMENT de la tabla `ligas`
--
ALTER TABLE `ligas`
  MODIFY `id_liga` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `liga_miembros`
--
ALTER TABLE `liga_miembros`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `pilotos`
--
ALTER TABLE `pilotos`
  MODIFY `id_piloto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT de la tabla `pilotos_equipo_fantasy`
--
ALTER TABLE `pilotos_equipo_fantasy`
  MODIFY `id_piloto_equipo` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `puntos_desglose`
--
ALTER TABLE `puntos_desglose`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=669;

--
-- AUTO_INCREMENT de la tabla `puntos_escuderia_fantasy`
--
ALTER TABLE `puntos_escuderia_fantasy`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `puntos_fantasy`
--
ALTER TABLE `puntos_fantasy`
  MODIFY `id_punto` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT de la tabla `puntos_fantasy_carrera`
--
ALTER TABLE `puntos_fantasy_carrera`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT de la tabla `resultados_carrera`
--
ALTER TABLE `resultados_carrera`
  MODIFY `id_resultado` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=253;

--
-- AUTO_INCREMENT de la tabla `sync_log`
--
ALTER TABLE `sync_log`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `temporadas`
--
ALTER TABLE `temporadas`
  MODIFY `id_temporada` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id_usuario` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `ventana_mercado`
--
ALTER TABLE `ventana_mercado`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `carreras`
--
ALTER TABLE `carreras`
  ADD CONSTRAINT `carreras_ibfk_1` FOREIGN KEY (`id_temporada`) REFERENCES `temporadas` (`id_temporada`);

--
-- Filtros para la tabla `clasificacion`
--
ALTER TABLE `clasificacion`
  ADD CONSTRAINT `clasificacion_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos_fantasy` (`id_equipo`);

--
-- Filtros para la tabla `equipos_fantasy`
--
ALTER TABLE `equipos_fantasy`
  ADD CONSTRAINT `equipos_fantasy_ibfk_1` FOREIGN KEY (`id_usuario`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `escuderia_equipo_fantasy`
--
ALTER TABLE `escuderia_equipo_fantasy`
  ADD CONSTRAINT `escuderia_equipo_fantasy_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos_fantasy` (`id_equipo`),
  ADD CONSTRAINT `escuderia_equipo_fantasy_ibfk_2` FOREIGN KEY (`id_escuderia`) REFERENCES `escuderias` (`id_escuderia`);

--
-- Filtros para la tabla `ligas`
--
ALTER TABLE `ligas`
  ADD CONSTRAINT `ligas_ibfk_1` FOREIGN KEY (`id_creador`) REFERENCES `usuarios` (`id_usuario`);

--
-- Filtros para la tabla `liga_miembros`
--
ALTER TABLE `liga_miembros`
  ADD CONSTRAINT `liga_miembros_ibfk_1` FOREIGN KEY (`id_liga`) REFERENCES `ligas` (`id_liga`) ON DELETE CASCADE,
  ADD CONSTRAINT `liga_miembros_ibfk_2` FOREIGN KEY (`id_equipo`) REFERENCES `equipos_fantasy` (`id_equipo`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pilotos`
--
ALTER TABLE `pilotos`
  ADD CONSTRAINT `pilotos_ibfk_1` FOREIGN KEY (`id_escuderia`) REFERENCES `escuderias` (`id_escuderia`);

--
-- Filtros para la tabla `pilotos_equipo_fantasy`
--
ALTER TABLE `pilotos_equipo_fantasy`
  ADD CONSTRAINT `pilotos_equipo_fantasy_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos_fantasy` (`id_equipo`),
  ADD CONSTRAINT `pilotos_equipo_fantasy_ibfk_2` FOREIGN KEY (`id_piloto`) REFERENCES `pilotos` (`id_piloto`);

--
-- Filtros para la tabla `puntos_desglose`
--
ALTER TABLE `puntos_desglose`
  ADD CONSTRAINT `desglose_ibfk_1` FOREIGN KEY (`id_resultado`) REFERENCES `resultados_carrera` (`id_resultado`) ON DELETE CASCADE;

--
-- Filtros para la tabla `puntos_fantasy`
--
ALTER TABLE `puntos_fantasy`
  ADD CONSTRAINT `puntos_fantasy_ibfk_1` FOREIGN KEY (`id_equipo`) REFERENCES `equipos_fantasy` (`id_equipo`),
  ADD CONSTRAINT `puntos_fantasy_ibfk_2` FOREIGN KEY (`id_piloto`) REFERENCES `pilotos` (`id_piloto`),
  ADD CONSTRAINT `puntos_fantasy_ibfk_3` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id_carrera`);

--
-- Filtros para la tabla `resultados_carrera`
--
ALTER TABLE `resultados_carrera`
  ADD CONSTRAINT `resultados_carrera_ibfk_1` FOREIGN KEY (`id_carrera`) REFERENCES `carreras` (`id_carrera`),
  ADD CONSTRAINT `resultados_carrera_ibfk_2` FOREIGN KEY (`id_piloto`) REFERENCES `pilotos` (`id_piloto`);

-- Actualizar logo_url para equipos sin imagen
UPDATE `escuderias` SET `logo_url` = 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2026/audi.png' WHERE `nombre` = 'Audi Revolut F1 Team' AND (`logo_url` IS NULL OR `logo_url` = '');
UPDATE `escuderias` SET `logo_url` = 'https://media.formula1.com/image/upload/f_auto,c_limit,q_auto,w_1320/content/dam/fom-website/teams/2026/cadillac.png' WHERE `nombre` = 'Cadillac Formula 1 Team' AND (`logo_url` IS NULL OR `logo_url` = '');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
