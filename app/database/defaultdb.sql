-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: haarlemproject-service-haarlemfestivaldb.e.aivencloud.com:25069
-- Generation Time: Apr 10, 2026 at 07:51 PM
-- Server version: 8.0.45
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `defaultdb`
--

-- --------------------------------------------------------

--
-- Table structure for table `Artist`
--

CREATE TABLE `Artist` (
  `artist_id` int UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL,
  `page_id` int DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Artist`
--

INSERT INTO `Artist` (`artist_id`, `name`, `page_id`, `created_at`, `updated_at`) VALUES
(1, 'Gumbo Kings', 11, '2026-03-11 13:15:29', '2026-03-12 11:49:29'),
(2, 'Wicked Jazz Sounds', 2, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(3, 'Evolve', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(4, 'Wouter Hamel', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(5, 'Ntjam Rosie', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(6, 'Jonna Frazer', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(7, 'Eric Vloeimans and Hotspot!', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(8, 'Karsu', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(9, 'Myles Sanko', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(10, 'Uncle Sue', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(11, 'Ilse Huizinga', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(12, 'Chris Alain', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(13, 'Gare du Nord', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(14, 'Han Bennink', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(15, 'Rilan & TheBombadiers', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(16, 'The Nordanians', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(17, 'Soul Six', 14, '2026-03-11 13:15:29', '2026-04-01 10:31:13'),
(18, 'Lilith Merloth', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(19, 'Ruis SoundSystem', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(20, 'Kamran', NULL, '2026-03-11 13:15:29', '2026-03-11 13:15:29'),
(32, 'YEs', NULL, '2026-03-11 13:18:45', '2026-03-11 13:18:45'),
(33, 'Great', 6, '2026-03-11 13:28:22', '2026-03-11 13:28:22'),
(34, 'Nikita', 7, '2026-03-11 13:32:52', '2026-03-11 13:33:38'),
(35, 'Hardwell', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47'),
(36, 'Armin van Buuren', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47'),
(37, 'Martin Garrix', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47'),
(38, 'Tiësto', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47'),
(39, 'Nicky Romero', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47'),
(40, 'Afrojack', NULL, '2026-03-12 12:01:47', '2026-03-12 12:01:47');

-- --------------------------------------------------------

--
-- Table structure for table `DanceEvent`
--

CREATE TABLE `DanceEvent` (
  `event_id` int NOT NULL,
  `venue_id` int UNSIGNED DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `session_tag` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tag_special` tinyint(1) NOT NULL DEFAULT '0',
  `row_kind` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'session',
  `sort_order` int NOT NULL DEFAULT '0',
  `start_date` datetime DEFAULT NULL,
  `end_date` datetime DEFAULT NULL,
  `event_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `DanceEvent`
--

INSERT INTO `DanceEvent` (`event_id`, `venue_id`, `price`, `session_tag`, `tag_special`, `row_kind`, `sort_order`, `start_date`, `end_date`, `event_date`) VALUES
(46, 7, 75.00, 'B2B', 0, 'session', 10, '2026-07-25 20:00:00', '2026-07-26 02:00:00', '2026-07-25'),
(47, 8, 60.00, 'CLUB', 0, 'session', 11, '2026-07-25 22:00:00', '2026-07-25 23:30:00', '2026-07-25'),
(48, 10, 60.00, 'CLUB', 0, 'session', 12, '2026-07-25 22:00:00', '2026-07-26 02:00:00', '2026-07-25'),
(49, 11, 60.00, 'CLUB', 0, 'session', 13, '2026-07-25 22:00:00', '2026-07-26 02:00:00', '2026-07-25'),
(50, 9, 60.00, 'CLUB', 0, 'session', 14, '2026-07-25 23:00:00', '2026-07-26 00:30:00', '2026-07-25'),
(51, 12, 110.00, 'B2B', 0, 'session', 30, '2026-07-26 14:00:00', '2026-07-26 23:00:00', '2026-07-26'),
(52, 7, 75.00, 'TIËSTOWORLD', 1, 'session', 31, '2026-07-26 21:00:00', '2026-07-27 01:00:00', '2026-07-26'),
(53, 9, 60.00, 'CLUB', 0, 'session', 32, '2026-07-26 22:00:00', '2026-07-26 23:30:00', '2026-07-26'),
(54, 8, 60.00, 'CLUB', 0, 'session', 33, '2026-07-26 23:00:00', '2026-07-27 00:30:00', '2026-07-26'),
(55, 12, 110.00, 'B2B', 0, 'session', 50, '2026-07-27 14:00:00', '2026-07-27 23:00:00', '2026-07-27'),
(56, 8, 60.00, 'CLUB', 0, 'session', 51, '2026-07-27 18:00:00', '2026-07-27 19:30:00', '2026-07-27'),
(57, 9, 60.00, 'CLUB', 0, 'session', 52, '2026-07-27 19:00:00', '2026-07-27 20:30:00', '2026-07-27'),
(58, 10, 90.00, 'CLUB', 0, 'session', 53, '2026-07-27 21:00:00', '2026-07-27 22:30:00', '2026-07-27'),
(59, NULL, 250.00, NULL, 0, 'all_access', 0, '2026-07-25 00:00:00', '2026-07-27 23:59:59', '2026-07-25'),
(60, NULL, 125.00, NULL, 0, 'day_pass', 1, '2026-07-25 00:00:00', '2026-07-25 23:59:59', '2026-07-25'),
(61, NULL, 125.00, NULL, 0, 'day_pass', 20, '2026-07-26 00:00:00', '2026-07-26 23:59:59', '2026-07-26'),
(62, NULL, 125.00, NULL, 0, 'day_pass', 40, '2026-07-27 00:00:00', '2026-07-27 23:59:59', '2026-07-27');

-- --------------------------------------------------------

--
-- Table structure for table `Event`
--

CREATE TABLE `Event` (
  `event_id` int NOT NULL,
  `title` varchar(255) NOT NULL,
  `event_type` enum('jazz','stories','dance','yummy','pass','history') NOT NULL,
  `availability` int NOT NULL DEFAULT '300'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Event`
--

INSERT INTO `Event` (`event_id`, `title`, `event_type`, `availability`) VALUES
(1, 'Gumbo Kings', 'jazz', 287),
(2, 'Wicked Jazz Sounds', 'jazz', 294),
(3, 'Evolve', 'jazz', 298),
(4, 'Wouter Hamel', 'jazz', 299),
(5, 'Ntjam Rosie', 'jazz', 299),
(6, 'Jonna Frazer', 'jazz', 299),
(7, 'Eric Vloeimans and Hotspot!', 'jazz', 299),
(8, 'Karsu', 'jazz', 298),
(9, 'Myles Sanko', 'jazz', 298),
(10, 'Uncle Sue', 'jazz', 298),
(11, 'Ilse Huizinga', 'jazz', 298),
(12, 'Chris Alain', 'jazz', 198),
(13, 'Gare du Nord', 'jazz', 300),
(14, 'Han Bennink', 'jazz', 300),
(15, 'Rilan & TheBombadiers', 'jazz', 300),
(16, 'The Nordanians', 'jazz', 300),
(17, 'Soul Six', 'jazz', 299),
(18, 'Lilith Merloth', 'jazz', 300),
(19, 'Ruis SoundSystem', 'jazz', 300),
(20, 'Wicked Jazz Sounds', 'jazz', 300),
(21, 'Evolve', 'jazz', 300),
(22, 'The Nordanians', 'jazz', 300),
(23, 'Gumbo Kings', 'jazz', 300),
(30, 'The Stories of Winnie De Pooh', 'stories', 300),
(31, 'Stories From The Omdenken Podcast', 'stories', 300),
(32, 'The story of Buurderij Haarlem', 'stories', 300),
(33, 'The Stories of Corrie voor kinderen', 'stories', 300),
(34, 'Winners of the Story-Telling Competition', 'stories', 300),
(35, 'Het verhaal van de Oeserzwammerij', 'stories', 300),
(36, 'The Flip Thinking Podcast', 'stories', 300),
(37, 'Verhalen van Meneer Anansi', 'stories', 300),
(38, 'The History of Family ten Boom', 'stories', 300),
(39, 'Podcastlast Haarlem Special', 'stories', 300),
(40, 'The Stories of Mister Anansi', 'stories', 300),
(41, 'The Stories of Mister Anansi', 'stories', 300),
(42, 'The History of Family ten Boom', 'stories', 300),
(43, 'Verhalen van Meneer Anansi', 'stories', 300),
(44, 'Winners of the Story-Telling Competition', 'stories', 300),
(46, 'NICKY ROMERO / AFROJACK (FRIDAY)', 'dance', 296),
(47, 'TIËSTO (FRIDAY)', 'dance', 298),
(48, 'ARMIN VAN BUUREN (FRIDAY)', 'dance', 299),
(49, 'MARTIN GARRIX (FRIDAY)', 'dance', 298),
(50, 'HARDWELL (FRIDAY)', 'dance', 298),
(51, 'HARDWELL / MARTIN GARRIX / ARMIN VAN BUUREN (SATURDAY)', 'dance', 298),
(52, 'TIËSTOWORLD (SATURDAY)', 'dance', 298),
(53, 'AFROJACK (SATURDAY)', 'dance', 299),
(54, 'NICKY ROMERO (SATURDAY)', 'dance', 299),
(55, 'AFROJACK / TIËSTO / NICKY ROMERO (SUNDAY)', 'dance', 298),
(56, 'MARTIN GARRIX (SUNDAY)', 'dance', 297),
(57, 'ARMIN VAN BUUREN (SUNDAY)', 'dance', 298),
(58, 'HARDWELL (SUNDAY)', 'dance', 298),
(59, 'All-Access Pass 3 Days', 'dance', 300),
(60, 'Day Pass Friday', 'dance', 298),
(61, 'Day Pass Saturday', 'dance', 300),
(62, 'Day Pass Sunday\r\n', 'dance', 299),
(65, 'Café de Roemer', 'yummy', 100),
(67, 'Restaurant Fris', 'yummy', 13),
(68, 'Restaurant Fris', 'yummy', 14),
(69, 'Restaurant Fris', 'yummy', 33),
(70, 'Jazz Day Pass', 'pass', 300),
(71, 'Dance Day Pass', 'pass', 300),
(72, 'Dance All Days Pass', 'pass', 300),
(73, 'Jazz All Days Pass', 'pass', 300),
(108, 'A Stroll Through History', 'history', 12),
(109, 'A Stroll Through History', 'history', 3),
(110, 'A Stroll Through History', 'history', 12),
(111, 'A Stroll Through History', 'history', 12),
(112, 'A Stroll Through History', 'history', 12),
(113, 'A Stroll Through History', 'history', 12),
(114, 'A Stroll Through History', 'history', 12),
(115, 'A Stroll Through History', 'history', 12),
(116, 'A Stroll Through History', 'history', 12),
(117, 'A Stroll Through History', 'history', 12),
(118, 'A Stroll Through History', 'history', 12),
(119, 'A Stroll Through History', 'history', 12),
(120, 'A Stroll Through History', 'history', 12),
(121, 'A Stroll Through History', 'history', 12),
(122, 'A Stroll Through History', 'history', 12),
(123, 'A Stroll Through History', 'history', 12),
(124, 'A Stroll Through History', 'history', 12),
(125, 'A Stroll Through History', 'history', 12),
(126, 'A Stroll Through History', 'history', 12),
(127, 'A Stroll Through History', 'history', 12),
(128, 'A Stroll Through History', 'history', 12),
(129, 'A Stroll Through History', 'history', 12),
(130, 'A Stroll Through History', 'history', 12),
(131, 'A Stroll Through History', 'history', 12),
(132, 'A Stroll Through History', 'history', 12),
(133, 'A Stroll Through History', 'history', 12),
(134, 'A Stroll Through History', 'history', 12),
(135, 'A Stroll Through History', 'history', 12),
(136, 'A Stroll Through History', 'history', 12),
(137, 'A Stroll Through History', 'history', 12),
(138, 'A Stroll Through History', 'history', 12),
(139, 'A Stroll Through History', 'history', 12),
(140, 'A Stroll Through History', 'history', 12),
(141, 'A Stroll Through History', 'history', 12),
(142, 'A Stroll Through History', 'history', 12),
(143, 'A Stroll Through History', 'history', 12),
(144, 'A Stroll Through History', 'history', 12),
(145, 'A Stroll Through History', 'history', 10);

-- --------------------------------------------------------

--
-- Table structure for table `HistoryEvent`
--

CREATE TABLE `HistoryEvent` (
  `event_id` int NOT NULL,
  `language` enum('NL','EN','CH') COLLATE utf8mb4_unicode_ci NOT NULL,
  `start_date` datetime NOT NULL,
  `location` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '',
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `family_price` decimal(10,2) NOT NULL DEFAULT '60.00'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `HistoryEvent`
--

INSERT INTO `HistoryEvent` (`event_id`, `language`, `start_date`, `location`, `price`, `family_price`) VALUES
(108, 'NL', '2025-07-24 10:00:00', 'Historic city centre', 17.50, 60.00),
(109, 'EN', '2025-07-24 10:00:00', 'Historic city centre', 17.50, 60.00),
(110, 'NL', '2025-07-24 13:00:00', 'Historic city centre', 17.50, 60.00),
(111, 'EN', '2025-07-24 13:00:00', 'Historic city centre', 17.50, 60.00),
(112, 'NL', '2025-07-24 16:00:00', 'Historic city centre', 17.50, 60.00),
(113, 'EN', '2025-07-24 16:00:00', 'Historic city centre', 17.50, 60.00),
(114, 'NL', '2025-07-25 10:00:00', 'Historic city centre', 17.50, 60.00),
(115, 'EN', '2025-07-25 10:00:00', 'Historic city centre', 17.50, 60.00),
(116, 'NL', '2025-07-25 13:00:00', 'Historic city centre', 17.50, 60.00),
(117, 'EN', '2025-07-25 13:00:00', 'Historic city centre', 17.50, 60.00),
(118, 'CH', '2025-07-25 13:00:00', 'Historic city centre', 17.50, 60.00),
(119, 'NL', '2025-07-25 16:00:00', 'Historic city centre', 17.50, 60.00),
(120, 'NL', '2025-07-25 16:00:00', 'Historic city centre', 17.50, 60.00),
(121, 'EN', '2025-07-25 16:00:00', 'Historic city centre', 17.50, 60.00),
(122, 'EN', '2025-07-25 16:00:00', 'Historic city centre', 17.50, 60.00),
(123, 'NL', '2025-07-26 13:00:00', 'Historic city centre', 17.50, 60.00),
(124, 'NL', '2025-07-26 13:00:00', 'Historic city centre', 17.50, 60.00),
(125, 'EN', '2025-07-26 13:00:00', 'Historic city centre', 17.50, 60.00),
(126, 'EN', '2025-07-26 13:00:00', 'Historic city centre', 17.50, 60.00),
(127, 'CH', '2025-07-26 13:00:00', 'Historic city centre', 17.50, 60.00),
(128, 'NL', '2025-07-26 16:00:00', 'Historic city centre', 17.50, 60.00),
(129, 'EN', '2025-07-26 16:00:00', 'Historic city centre', 17.50, 60.00),
(130, 'CH', '2025-07-26 16:00:00', 'Historic city centre', 17.50, 60.00),
(131, 'NL', '2025-07-27 10:00:00', 'Historic city centre', 17.50, 60.00),
(132, 'NL', '2025-07-27 10:00:00', 'Historic city centre', 17.50, 60.00),
(133, 'EN', '2025-07-27 10:00:00', 'Historic city centre', 17.50, 60.00),
(134, 'EN', '2025-07-27 10:00:00', 'Historic city centre', 17.50, 60.00),
(135, 'CH', '2025-07-27 10:00:00', 'Historic city centre', 17.50, 60.00),
(136, 'NL', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(137, 'NL', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(138, 'NL', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(139, 'EN', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(140, 'EN', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(141, 'EN', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(142, 'CH', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(143, 'CH', '2025-07-27 13:00:00', 'Historic city centre', 17.50, 60.00),
(144, 'NL', '2025-07-27 16:00:00', 'Historic city centre', 17.50, 60.00),
(145, 'EN', '2025-07-27 16:00:00', 'Historic city centre', 17.50, 60.00);

-- --------------------------------------------------------

--
-- Table structure for table `JazzEvent`
--

CREATE TABLE `JazzEvent` (
  `event_id` int NOT NULL,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `venue_id` int UNSIGNED DEFAULT NULL,
  `artist_id` int UNSIGNED NOT NULL,
  `img_background` varchar(255) DEFAULT NULL,
  `price` decimal(10,2) NOT NULL,
  `page_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `JazzEvent`
--

INSERT INTO `JazzEvent` (`event_id`, `start_date`, `end_date`, `venue_id`, `artist_id`, `img_background`, `price`, `page_id`) VALUES
(1, '2026-07-23 18:00:00', '2026-07-23 19:30:00', 1, 1, 'assets/img/jazz/event/d26570d0964b27c6621014d3a112996d.png', 10.00, NULL),
(2, '2026-07-23 18:00:00', '2026-07-23 19:30:00', 2, 2, 'assets/img/jazz/event/wicked-jazz-sounds.jpg', 10.00, 2),
(3, '2026-07-23 19:30:00', '2026-07-23 21:00:00', 1, 3, 'assets/img/jazz/event/1236a94c9e6b2f31cb166366436523c3.png', 10.00, NULL),
(4, '2026-07-23 19:30:00', '2026-07-23 21:00:00', 2, 4, 'assets/img/jazz/event/wouter-hamel.jpg', 10.00, NULL),
(5, '2026-07-23 21:00:00', '2026-07-23 22:30:00', 1, 5, 'assets/img/jazz/event/ntjam-rosie.jpg', 10.00, NULL),
(6, '2026-07-23 21:00:00', '2026-07-23 22:30:00', 2, 6, 'assets/img/jazz/event/jonna-frazer.jpg', 10.00, NULL),
(7, '2026-07-23 21:00:00', '2026-07-23 22:30:00', 2, 7, 'assets/img/jazz/event/eric-vloeimans-and-hotspot.jpg', 10.00, NULL),
(8, '2026-07-24 19:30:00', '2026-07-24 21:00:00', 1, 8, 'assets/img/jazz/event/karsu.jpg', 10.00, NULL),
(9, '2026-07-24 18:00:00', '2026-07-24 19:30:00', 2, 9, 'assets/img/jazz/event/myles-sanko.jpg', 10.00, NULL),
(10, '2026-07-24 19:30:00', '2026-07-24 21:00:00', 1, 10, 'assets/img/jazz/event/uncle-sue.jpg', 10.00, NULL),
(11, '2026-07-24 19:30:00', '2026-07-24 21:00:00', 2, 11, 'assets/img/jazz/event/ilse-huizinga.jpg', 10.00, NULL),
(12, '2026-07-24 21:00:00', '2026-07-24 22:30:00', 1, 12, 'assets/img/jazz/event/chris-alain.jpg', 10.00, NULL),
(13, '2026-07-25 18:00:00', '2026-07-25 19:30:00', 1, 13, 'assets/img/jazz/event/gare-du-nord.jpg', 15.00, NULL),
(14, '2026-07-25 18:00:00', '2026-07-25 19:30:00', 3, 14, 'assets/img/jazz/event/han-bennink.jpg', 10.00, NULL),
(15, '2026-07-25 19:30:00', '2026-07-25 21:00:00', 1, 15, 'assets/img/jazz/event/rilan-and-thebombadiers.jpg', 15.00, NULL),
(16, '2026-07-25 19:30:00', '2026-07-25 21:00:00', 3, 16, 'assets/img/jazz/event/the-nordanians.jpg', 10.00, NULL),
(17, '2026-07-25 21:00:00', '2026-07-25 22:30:00', 1, 17, 'assets/img/jazz/event/soul-six.jpg', 15.00, 14),
(18, '2026-07-25 21:00:00', '2026-07-25 22:30:00', 3, 18, 'assets/img/jazz/event/lilith-merloth.jpg', 15.00, NULL),
(19, '2026-07-26 15:00:00', '2026-07-26 16:30:00', 4, 19, 'assets/img/jazz/event/ruis-soundsystem.jpg', 10.00, NULL),
(20, '2026-07-26 16:00:00', '2026-07-26 17:30:00', 4, 2, 'assets/img/jazz/event/wicked-jazz-sounds-grote-markt.jpg', 10.00, 2),
(21, '2026-07-26 17:00:00', '2026-07-26 18:30:00', 4, 3, 'assets/img/jazz/event/evolve-grote-markt.jpg', 10.00, NULL),
(22, '2026-07-26 18:00:00', '2026-07-26 19:30:00', 4, 16, 'assets/img/jazz/event/the-nordanians-grote-markt.jpg', 10.00, NULL),
(23, '2026-07-26 19:00:00', '2026-07-26 20:30:00', 4, 1, 'assets/img/jazz/event/gumbo-kings-grote-markt.jpg', 10.00, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `Location`
--

CREATE TABLE `Location` (
  `location_id` int UNSIGNED NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(512) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `orders`
--

CREATE TABLE `orders` (
  `order_id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `order_status` enum('pending','payed','cancelled') NOT NULL DEFAULT 'pending',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `payment_deadline_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `orders`
--

INSERT INTO `orders` (`order_id`, `user_id`, `order_status`, `created_at`, `payment_deadline_at`) VALUES
(15, 19, 'pending', '2026-03-11 11:29:55', NULL),
(52, 18, 'payed', '2026-03-19 13:21:01', NULL),
(54, 18, 'payed', '2026-03-19 14:18:36', NULL),
(55, 18, 'payed', '2026-03-19 15:08:26', NULL),
(56, 18, 'payed', '2026-03-19 15:09:28', NULL),
(58, 1, 'payed', '2026-03-26 09:07:15', NULL),
(59, 18, 'payed', '2026-03-26 09:09:46', NULL),
(60, 18, 'payed', '2026-03-26 09:10:42', NULL),
(63, 1, 'payed', '2026-03-26 09:51:20', NULL),
(64, 18, 'payed', '2026-03-26 10:03:21', NULL),
(67, 1, 'payed', '2026-03-26 10:51:56', NULL),
(69, 1, 'payed', '2026-03-26 12:42:55', NULL),
(70, 18, 'payed', '2026-03-26 13:02:09', NULL),
(71, 1, 'payed', '2026-03-26 13:29:42', NULL),
(72, 1, 'payed', '2026-03-26 13:30:57', NULL),
(74, 1, 'payed', '2026-04-01 10:31:59', NULL),
(75, 31, 'payed', '2026-04-09 17:20:47', NULL),
(76, 2, 'payed', '2026-04-09 17:31:32', NULL),
(77, 18, 'payed', '2026-04-09 17:32:24', NULL),
(78, 1, 'payed', '2026-04-09 17:33:52', NULL),
(79, 1, 'payed', '2026-04-09 17:34:59', NULL),
(80, 1, 'payed', '2026-04-09 17:36:22', NULL),
(81, 31, 'payed', '2026-04-09 17:50:07', NULL),
(82, 18, 'payed', '2026-04-09 18:00:50', NULL),
(83, 18, 'payed', '2026-04-09 18:10:11', NULL),
(85, 28, 'payed', '2026-04-09 18:25:13', NULL),
(86, 30, 'pending', '2026-04-09 18:27:17', NULL),
(89, 1, 'payed', '2026-04-09 18:59:17', NULL),
(90, 1, 'payed', '2026-04-09 19:01:08', NULL),
(91, 1, 'payed', '2026-04-09 19:11:47', NULL),
(92, 32, 'payed', '2026-04-09 19:28:00', NULL),
(93, 32, 'payed', '2026-04-09 19:34:20', NULL),
(94, 31, 'payed', '2026-04-09 19:39:30', NULL),
(95, 32, 'payed', '2026-04-09 19:47:16', NULL),
(96, 33, 'payed', '2026-04-09 19:53:13', NULL),
(97, 14, 'payed', '2026-04-09 19:56:16', NULL),
(99, 34, 'pending', '2026-04-09 20:01:11', NULL),
(100, 14, 'payed', '2026-04-09 20:07:22', NULL),
(101, 18, 'cancelled', '2026-04-09 20:15:58', NULL),
(102, 18, 'payed', '2026-04-09 20:41:04', NULL),
(103, 1, 'payed', '2026-04-10 11:10:39', NULL),
(104, 32, 'payed', '2026-04-10 11:23:50', NULL),
(110, 18, 'payed', '2026-04-10 12:42:24', NULL),
(111, 18, 'cancelled', '2026-04-10 12:56:20', NULL),
(114, 2, 'payed', '2026-04-10 14:18:16', NULL),
(115, 2, 'payed', '2026-04-10 14:25:39', NULL),
(116, 2, 'payed', '2026-04-10 14:36:25', NULL),
(117, 2, 'payed', '2026-04-10 15:53:04', NULL),
(118, 31, 'payed', '2026-04-10 16:01:04', NULL),
(119, 18, 'payed', '2026-04-10 16:09:04', NULL),
(120, 31, 'payed', '2026-04-10 16:22:59', NULL),
(121, 18, 'payed', '2026-04-10 16:24:52', NULL),
(123, 18, 'payed', '2026-04-10 16:35:54', NULL),
(124, 35, 'payed', '2026-04-10 16:38:20', NULL),
(125, 35, 'payed', '2026-04-10 16:42:41', NULL),
(127, 35, 'payed', '2026-04-10 17:59:31', NULL),
(128, 35, 'payed', '2026-04-10 18:57:34', NULL),
(130, 35, 'payed', '2026-04-10 19:41:20', NULL),
(131, 2, 'payed', '2026-04-10 19:42:09', NULL),
(133, 35, 'payed', '2026-04-10 19:43:24', NULL),
(134, 35, 'pending', '2026-04-10 19:46:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `order_items`
--

CREATE TABLE `order_items` (
  `order_item_id` int NOT NULL,
  `order_id` int NOT NULL,
  `event_id` int NOT NULL,
  `pass_date_key` date NOT NULL DEFAULT '1000-01-01',
  `quantity` int NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `order_items`
--

INSERT INTO `order_items` (`order_item_id`, `order_id`, `event_id`, `pass_date_key`, `quantity`, `created_at`) VALUES
(91, 52, 47, '1000-01-01', 1, '2026-03-19 13:21:01'),
(92, 52, 46, '1000-01-01', 1, '2026-03-19 13:22:59'),
(93, 52, 50, '1000-01-01', 1, '2026-03-19 13:23:05'),
(94, 52, 49, '1000-01-01', 1, '2026-03-19 13:23:09'),
(97, 54, 49, '1000-01-01', 5, '2026-03-19 14:18:36'),
(98, 55, 46, '1000-01-01', 2, '2026-03-19 15:08:26'),
(99, 56, 59, '1000-01-01', 1, '2026-03-19 15:09:28'),
(100, 56, 60, '1000-01-01', 1, '2026-03-19 15:09:29'),
(103, 59, 55, '1000-01-01', 2, '2026-03-26 09:09:46'),
(110, 63, 70, '2026-03-19', 1, '2026-03-26 09:51:20'),
(111, 64, 46, '1000-01-01', 2, '2026-03-26 10:03:21'),
(118, 69, 70, '2026-07-24', 1, '2026-03-26 12:42:55'),
(121, 70, 50, '1000-01-01', 1, '2026-03-26 13:02:09'),
(122, 71, 70, '2026-07-23', 1, '2026-03-26 13:29:43'),
(123, 72, 60, '1000-01-01', 1, '2026-03-26 13:30:57'),
(125, 74, 17, '1000-01-01', 1, '2026-04-01 10:31:59'),
(126, 75, 2, '1000-01-01', 1, '2026-04-09 17:20:47'),
(128, 76, 47, '1000-01-01', 1, '2026-04-09 17:31:32'),
(129, 77, 60, '1000-01-01', 1, '2026-04-09 17:32:24'),
(131, 78, 70, '2026-07-24', 1, '2026-04-09 17:33:55'),
(132, 79, 70, '2026-07-24', 1, '2026-04-09 17:34:59'),
(133, 80, 60, '1000-01-01', 1, '2026-04-09 17:36:22'),
(134, 81, 2, '1000-01-01', 1, '2026-04-09 17:50:07'),
(135, 82, 62, '1000-01-01', 1, '2026-04-09 18:00:50'),
(136, 83, 62, '1000-01-01', 1, '2026-04-09 18:10:11'),
(140, 85, 109, '1000-01-01', 4, '2026-04-09 18:25:13'),
(141, 86, 70, '2026-07-23', 1, '2026-04-09 18:27:17'),
(142, 86, 1, '1000-01-01', 1, '2026-04-09 18:27:38'),
(148, 89, 1, '1000-01-01', 2, '2026-04-09 18:59:18'),
(149, 90, 70, '2026-07-23', 1, '2026-04-09 19:01:08'),
(150, 91, 109, '1000-01-01', 4, '2026-04-09 19:11:47'),
(151, 92, 1, '1000-01-01', 1, '2026-04-09 19:28:00'),
(152, 93, 1, '1000-01-01', 1, '2026-04-09 19:34:20'),
(153, 94, 1, '1000-01-01', 1, '2026-04-09 19:39:30'),
(154, 95, 1, '1000-01-01', 1, '2026-04-09 19:47:16'),
(155, 96, 1, '1000-01-01', 1, '2026-04-09 19:53:13'),
(156, 97, 1, '1000-01-01', 1, '2026-04-09 19:56:16'),
(159, 99, 2, '1000-01-01', 2, '2026-04-09 20:01:11'),
(160, 100, 1, '1000-01-01', 1, '2026-04-09 20:07:22'),
(161, 101, 46, '1000-01-01', 1, '2026-04-09 20:15:59'),
(162, 102, 2, '1000-01-01', 1, '2026-04-09 20:41:04'),
(163, 103, 1, '1000-01-01', 2, '2026-04-10 11:10:39'),
(164, 104, 3, '1000-01-01', 1, '2026-04-10 11:23:50'),
(188, 110, 46, '1000-01-01', 1, '2026-04-10 12:42:24'),
(189, 111, 47, '1000-01-01', 1, '2026-04-10 12:56:20'),
(190, 111, 2, '1000-01-01', 1, '2026-04-10 12:56:27'),
(198, 114, 67, '1000-01-01', 2, '2026-04-10 14:18:16'),
(199, 86, 37, '1000-01-01', 4, '2026-04-10 14:22:33'),
(200, 115, 67, '1000-01-01', 1, '2026-04-10 14:25:39'),
(201, 116, 69, '1000-01-01', 1, '2026-04-10 14:36:25'),
(207, 119, 49, '1000-01-01', 1, '2026-04-10 16:09:04'),
(208, 120, 109, '1000-01-01', 1, '2026-04-10 16:22:59'),
(209, 121, 56, '1000-01-01', 1, '2026-04-10 16:24:52'),
(211, 123, 46, '1000-01-01', 1, '2026-04-10 16:35:54'),
(212, 124, 59, '1000-01-01', 1, '2026-04-10 16:38:20'),
(213, 125, 2, '1000-01-01', 1, '2026-04-10 16:42:41'),
(216, 127, 2, '1000-01-01', 1, '2026-04-10 17:59:31'),
(217, 127, 50, '1000-01-01', 1, '2026-04-10 17:59:38'),
(218, 127, 46, '1000-01-01', 1, '2026-04-10 17:59:44'),
(219, 128, 51, '1000-01-01', 1, '2026-04-10 18:57:34'),
(221, 130, 1, '1000-01-01', 1, '2026-04-10 19:41:20'),
(222, 130, 52, '1000-01-01', 1, '2026-04-10 19:41:40'),
(223, 131, 67, '1000-01-01', 1, '2026-04-10 19:42:09'),
(225, 133, 145, '1000-01-01', 2, '2026-04-10 19:43:24'),
(226, 134, 46, '1000-01-01', 2, '2026-04-10 19:46:53'),
(227, 134, 2, '1000-01-01', 1, '2026-04-10 19:47:06'),
(228, 134, 109, '1000-01-01', 1, '2026-04-10 19:48:28');

-- --------------------------------------------------------

--
-- Table structure for table `Page`
--

CREATE TABLE `Page` (
  `Page_ID` int NOT NULL,
  `Page_Title` varchar(1000) NOT NULL,
  `Page_Type` enum('Homepage','Jazz_Homepage','Jazz_Detail_Page','Yummy_Homepage','Yummy_Detail_Page','Dance_Homepage','Dance_Detail_Page','Stories_Homepage','Stories_Detail_Page','History_Homepage','History_Detail_Page','Payment_Page','Personal_Program_Page','Cart_Page','Dance_Location_Page') NOT NULL,
  `Content` json NOT NULL,
  `Updated_At` datetime DEFAULT NULL,
  `Created_At` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Page`
--

INSERT INTO `Page` (`Page_ID`, `Page_Title`, `Page_Type`, `Content`, `Updated_At`, `Created_At`) VALUES
(1, 'Jazz Home', 'Jazz_Homepage', '{\"hero\": {\"title\": \"JAZZ EVENT\", \"kicker\": \"HAARLEM\", \"subtitle_html\": \"<p>Feel the rhythm of Haarlem\'s jazz scene</p>\\r\\n<p>Enjoy inspiring performances from top artists</p>\\r\\n<p>Immerse yourself in the city\'s timeless musical spirit</p>\", \"primary_button\": {\"label\": \"Buy ticket\"}, \"background_image\": {\"alt\": \"\", \"src\": \"assets/img/jazz/homepage/hero_sax.jpg\"}}, \"intro\": {\"heading\": \"Let Haarlem music welcome you in.\", \"body_html\": \"<p>The <strong>Jazz</strong> Event offers a unique blend of local artistry, international influences, and the city\'s unmistakable atmosphere.</p>\\r\\n<p>Whether you\'re a longtime jazz enthusiast or discovering the genre for the first time, the festival invites you to experience live music that moves, surprises, and inspires.</p>\\r\\n<p>Across historic halls and open-air stages, musicians bring new energy to Haarlem\'s rich cultural landscape, filling the streets with rhythm, creativity, and connection.</p>\\r\\n<p>It\'s a celebration where every performance tells a story, every moment invites you closer, and every note becomes part of the city\'s living soundtrack.</p>\"}, \"schedule\": {\"title\": \"SCHEDULE\", \"filters\": {\"days\": [], \"group_label\": \"By date\"}, \"venue_title\": \"PATRONTAT\", \"all_events_button\": {\"href\": \"/jazz/events\", \"label\": \"All Events\"}}, \"day_ticket_pass\": {\"title\": \"Day Ticket Pass\"}}', '2026-03-26 08:45:05', '2026-03-03 20:57:13'),
(2, 'Wicked Jazz Sounds', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>Wicked Jazz Sounds is a club concept and event platform built around groove-based music and dancefloor energy. The collective curates line-ups and collaborations that connect audiences and artists through jazz, soul, funk, hip hop, house and more.</p>\", \"text\": \"\", \"title\": \"About the Artist:\"}, \"albums\": [{\"image\": {\"alt\": \"\", \"src\": \"assets/img/jazz/album/kris-berry-marbles.jpg\", \"caption\": \"\"}, \"title\": \"Marbles\", \"artist\": \"Kris Berry\", \"description\": \"\", \"description_html\": \"<p>Contains tracks: Holy Joe, Second Beast, Waiting for the Rain, Always or Never, Mountain or a Molehill, Love Trip, Silver Balloon, Crazy Days, Going Somewhere, Meadow Song, Crystal Ball, Marbles, Stick to the Recipe, Morning Sun.</p>\"}], \"artist\": {\"name\": \"Wicked Jazz Sounds\", \"kicker\": \"Haarlem Jazz\", \"breadcrumb\": {\"current\": \"Wicked Jazz\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/jazz/detail/wicked-logo.jpg\"}, \"secondary\": [{\"image\": \"assets/img/jazz/detail/wicked-founders.jpg\", \"caption\": \"Wicked Jazz Founders\"}, {\"image\": \"assets/img/jazz/detail/kris-berry.jpg\", \"caption\": \"Kris Berry in wicked Jazz\"}]}, \"hero_title\": \"Wicked Jazz\", \"cover_image\": \"assets/img/jazz/detail/wicked-hero-bg.jpg\", \"hero_subtitle\": \"Haarlem Jazz\"}, \"events\": {\"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"Phil Horneman\", \"Manne van der Zee\"], \"title\": \"Band Members:\"}, \"career_highlights\": {\"left\": [], \"right\": [\"ahhh?¿???\"], \"left_html\": \"<ul>\\r\\n<li>Founded in Amsterdam as a weekly club night, Wicked Jazz Sounds redefined jazz as dance-driven, contemporary club music.</li>\\r\\n<li>Known for blending DJs with live musicians, merging jazz, soul, funk, broken beat, and electronic music into one hybrid experience.</li>\\r\\n<li>Their long-running Sunday residency grew into a cultural movement, attracting a large and dedicated audience over many years.</li>\\r\\n</ul>\", \"right_html\": \"<ul>\\r\\n<li>Expanded from club nights to major festivals, orchestral collaborations, and international showcases.</li>\\r\\n<li>Launched the Wicked Jazz Sounds Festival, establishing the collective as a major force in Dutch music culture.</li>\\r\\n<li>With over two decades of events, recordings, and collaborations, WJS continues to connect jazz tradition with modern nightlife.</li>\\r\\n</ul>\"}}', '2026-03-20 12:44:07', '2026-03-04 16:20:33'),
(3, 'Haarlem Festival Homepage', 'Homepage', '{\"hero\": {\"title\": \"Discover Haarlem Festival\", \"images\": [\"assets/img/homepage/dance.png\", \"assets/img/homepage/city.png\", \"assets/img/homepage/fireworks.png\", \"assets/img/homepage/church.png\", \"assets/img/homepage/crowd.png\"], \"subtitle_html\": \"<p>Explore jazz and dance events, discover Haarlem\'s stories and history.</p><p>Find the best places to eat during the festival.</p>\"}, \"categories\": [{\"name\": \"Dance\", \"image\": \"assets/svg/Dance.svg\"}, {\"name\": \"Jazz\", \"image\": \"assets/svg/Jazz.svg\"}, {\"name\": \"Yummy\", \"image\": \"assets/svg/Yummy.svg\"}, {\"name\": \"Stories\", \"image\": \"assets/svg/Stories.svg\"}, {\"name\": \"History\", \"image\": \"assets/svg/History.svg\"}], \"newsletter\": {\"logo\": \"assets/svg/logo.svg\", \"title\": \"Stay Updated\", \"preferences\": [\"Dance Events\", \"Jazz Events\", \"Restaurants\", \"Stories Events\", \"History Events\"], \"privacy_text\": \"By subscribing, you agree to our Privacy Policy.\", \"description_html\": \"<p>Subscribe to our newsletter for the latest news and <strong>exclusive offers</strong>.</p>\"}, \"introduction\": {\"title\": \"Welcome to the Haarlem Festival!\", \"body_html\": \"<p>Where the city\'s <strong>historic charm</strong> meets contemporary culture in one unforgettable celebration.</p><p>For four vibrant days, Haarlem transforms into a living stage filled with music, stories, food, and experiences.</p>\", \"statistics\": [{\"label\": \"Days\", \"value\": \"4\"}, {\"label\": \"Top DJs\", \"value\": \"6\"}, {\"label\": \"Restaurants\", \"value\": \"7\"}, {\"label\": \"Performances\", \"value\": \"40+\"}]}, \"highlighted_events\": [{\"date\": \"Fri 25 Jul\", \"image\": \"assets/img/homepage/afrojack.png\", \"title\": \"Nicky Romero / Afrojack\", \"location\": \"Lichtfabriek\"}, {\"date\": \"Thu 24 Jul\", \"image\": \"assets/img/homepage/ratatouille.png\", \"title\": \"Patatouille\", \"location\": \"Spaarne 96\"}, {\"date\": \"Sat 26 Jul\", \"image\": \"assets/img/homepage/gumbo.png\", \"title\": \"Gumbo Kings\", \"location\": \"Patronaat\"}, {\"date\": \"Sun 27 Jul\", \"image\": \"assets/img/homepage/history.png\", \"title\": \"Stroll Through History\", \"location\": \"Janskerk\"}, {\"date\": \"Sun 27 Jul\", \"image\": \"assets/img/homepage/anansi.png\", \"title\": \"Mister Anansi\", \"location\": \"Theater Elswout\"}]}', '2026-03-11 14:14:09', '2026-03-10 17:17:37'),
(4, 'Dance Home', 'Dance_Homepage', '{\"hero\": {\"title\": \"HAARLEM DANCE EVENT\", \"strip_text\": \"\", \"subtitle_html\": \"<p>Discover Haarlem&rsquo;s vibrant nightlife<br>Experience top international DJs<br>Celebrate dance culture in the heart of the city</p>\", \"primary_button\": {\"label\": \"\"}, \"background_image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/c13d6e987baaab1d0c9dd34570768cac.png\"}}, \"intro\": {\"stats\": [], \"kicker\": \"Let Haarlem\'s music welcome you in\", \"body_html\": \"<p><strong>The</strong> Dance Event is where Haarlem truly comes alive. As the sun goes down, the city switches into a completely different mode &mdash; neon lights, deep bass, and a crowd that&rsquo;s ready to move. World-class DJs, immersive light shows, and the city&rsquo;s vibrant nightlife all come together to create nights that feel electric.<br><br>Here, it doesn&rsquo;t matter if you&rsquo;re a die-hard rave lover or someone who&rsquo;s just curious about the scene. Maybe you come for the heavy drops, maybe for the atmosphere, or maybe you just want to dance with friends until your legs can&rsquo;t keep up &mdash; either way, you&rsquo;ll fit right in.<br><br>Across the festival&rsquo;s 3 days, Haarlem transforms into a playground for rhythmic energy: back-to-back DJ sets, intimate experimental sessions, and massive stages that pull you in with sound you can feel straight in your chest. For returning festival-goers, it feels like coming home. For first-timers, it&rsquo;s the start of something unforgettable.<br><br>So dive into the lights, join the crowd, and let yourself get carried by the rhythm. This is Dance &mdash; where excitement, connection, and pure nightlife energy meet.</p>\", \"side_image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/eb4660dccdedd6862cb53c12e30ab097.png\"}}, \"lineup\": {\"title\": \"Headliners\", \"artists\": [{\"name\": \"Martin Garrix\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/b8ab3af3c239f738179120688f672653.png\"}}, {\"name\": \"Armin van Buuren\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/a15c3b0ade744586fd11f7f76ad66d11.png\"}}, {\"name\": \"Tiësto\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/68c4d6418da3d6ba98fa53fb2ec103d3.png\"}}, {\"name\": \"Hardwell\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/1ef68cbc5866e8aefe012b4b3ff187a6.png\"}}, {\"name\": \"Afrojack\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/e079f7e7374c617d3ad46adce17e2c77.png\"}}, {\"name\": \"Nicky Romero\", \"image\": {\"alt\": \"\", \"src\": \"assets/img/page/dance_homepage/d798ce332360852ed8bb428d905e5b56.png\"}}]}, \"timetable\": {\"title\": \"Plan your night\", \"passes\": [{\"note\": \"\", \"label\": \"All-Access Pass 3 Days\"}, {\"note\": \"\", \"label\": \"Day Pass Friday\"}, {\"note\": \"\", \"label\": \"Day Pass Saturday\"}, {\"note\": \"\", \"label\": \"Day Pass Sunday\"}], \"date_range\": \"\"}}', '2026-04-10 14:29:40', '2026-03-10 21:07:19'),
(6, 'Great Jazz Detail', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>tes</p>\", \"title\": \"About the Artist:\"}, \"albums\": [{\"image\": \"assets/img/jazz/album/c27e11597fd090b2d61198e353f65928.jpg\", \"title\": \"asfasf\", \"artist\": \"asfasf\", \"description_html\": \"<p>asfasf</p>\"}, {\"image\": \"assets/img/jazz/album/4c3719badda5c17ad1efa77b9f598a36.png\", \"title\": \"asfasf\", \"artist\": \"asfasf\", \"description_html\": \"<p>sfasfasf</p>\"}, {\"image\": \"assets/img/jazz/album/131653fc3df0b4beef79a9176481950f.png\", \"title\": \"asfasf\", \"artist\": \"asfasfasfasf\", \"description_html\": \"<p>asfafasf</p>\"}], \"artist\": {\"name\": \"Great\", \"kicker\": \"Yes i am\", \"breadcrumb\": {\"current\": \"Great\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/jazz/detail/f9504486d1f16dff8dbcfe90a19a5405.jpg\", \"caption\": \"yes\"}, \"secondary\": [{\"image\": \"assets/img/jazz/detail/139b4076d790cfb710ef53041a138982.png\", \"caption\": \"yes\"}, {\"image\": \"assets/img/jazz/detail/b6813bd5e266716dec05bd1d73e82dd2.png\", \"caption\": \"yes\"}]}, \"hero_title\": \"Best of the world\", \"cover_image\": \"assets/img/jazz/detail/c1f0033ab020ca3a9eb00ed9bac40358.png\", \"hero_subtitle\": \"Haarlem Jazz\"}, \"events\": {\"event_ids\": [\"3\", \"4\"], \"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"idk\"], \"title\": \"Band Members:\"}, \"career_highlights\": {\"left_html\": \"<p>yes</p>\", \"right_html\": \"<p>yes</p>\"}}', '2026-03-11 13:28:22', '2026-03-11 13:28:22'),
(7, 'Nikita Jazz Detail', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\", \"title\": \"About the Artist:\"}, \"albums\": [{\"image\": \"assets/img/jazz/album/9edde7def3cbfa2c7ba15071f008fffe.png\", \"title\": \"yesyes\", \"artist\": \"yesyesyesyesyes\", \"description_html\": \"<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\"}, {\"image\": \"\", \"title\": \"\", \"artist\": \"\", \"description_html\": \"<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\"}], \"artist\": {\"name\": \"Nikita\", \"kicker\": \"Hello\", \"breadcrumb\": {\"current\": \"Nikita\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"\", \"caption\": \"hello\"}, \"secondary\": [{\"image\": \"assets/img/jazz/detail/31d89ad73a06e486d77f70f9df2ec816.png\", \"caption\": \"yes\"}, {\"image\": \"assets/img/jazz/detail/09e301edea9bdcc154165671e6734195.png\", \"caption\": \"yes\"}]}, \"hero_title\": \"Hello\", \"cover_image\": \"assets/img/jazz/detail/5725f48048126feb3e9b2e3c625c09d0.png\", \"hero_subtitle\": \"Hello\"}, \"events\": {\"event_ids\": [\"26\"], \"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"yesyesyes\"], \"title\": \"Band Members:\"}, \"career_highlights\": {\"left_html\": \"<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\", \"right_html\": \"<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\\r\\n<p>yes</p>\"}}', '2026-03-11 13:33:38', '2026-03-11 13:32:52'),
(8, 'Haarlem Stories Events', 'Stories_Homepage', '{\"hero\": {\"title\": \"HAARLEM STORIES EVENTS\", \"subtitle\": \"Discover the unique stories told at the Haarlem Festival\", \"image_path\": \"assets/img/stories/microphone.png\"}, \"schedule\": {\"filters\": {\"days\": [\"All Days\", \"Thursday\", \"Friday\", \"Saturday\", \"Sunday\"], \"tabs\": [\"NL\", \"ENG\", \"NL/ENG\"], \"group_label\": \"By language\"}}, \"introduction\": {\"title\": \"A CITY FULL OF STORIES\", \"body_html\": \"<p class=\\\"mb-4\\\">From family stories to stories with impact and live podcast recordings, the Haarlem Festival brings voices together at unique locations across the city.</p>\\r\\n<p class=\\\"mb-4\\\">Stories are told in Dutch and English and take place across four days. Browse the timetable below and create your own program.</p>\", \"image_path\": \"assets/img/stories/verhalenhuis.png\"}}', '2026-03-26 09:34:05', '2026-03-11 16:50:26'),
(9, 'Haarlem Yummy Event', 'Yummy_Homepage', '{\"map\": {\"image\": \"/assets/img/yummy/homepage/map_img.png\", \"contentHtml\": \"<h2>Find restaurant near your event</h2>\\r\\n<p>Use our interactive map to find and effortlessly navigate your culinary journey with our interactive festival map. Check the map to see restaurant pinpoints located right next to Jazz stages, Dance venues, and Historical landmarks. Our map ensures you are never far from great food or great entertainment!</p>\"}, \"hero\": {\"bgImage\": \"/assets/img/yummy/homepage/hero_cover.png\", \"caption\": \"Grote Markt, Haarlem Festival 2019\", \"titleHtml\": \"<p>HAARLEM<br>YUMMY<br>EVENT</p>\\r\\n<p>&nbsp;</p>\", \"description\": \"From the smooth notes of Jazz to the high energy of Dance, every great festival moment begins with a great meal!\"}, \"intro\": {\"contentHtml\": \"<h2>Taste your way through the Festival</h2>\\r\\n<p>Explore Haarlem\'s rich culinary history through local favorites and world-class dining. Treat your palate to a lineup as diverse as our stages&mdash;because in Haarlem, the food is an event in itself.</p>\"}, \"gallery\": {\"images\": [\"/assets/img/yummy/homepage/try_pic_1.png\", \"/assets/img/yummy/homepage/try_pic_2.png\", \"/assets/img/yummy/homepage/try_pic_4.png\", \"/assets/img/yummy/homepage/try_pic_3.png\"], \"captions\": [\"Restaruant Fris Food\", \"Ratatouille Food\", \"Café de Roemer Food\", \"Café de Roemer Food\"]}, \"pageTitle\": \"Haarlem Yummy Home page\", \"restaurants\": {\"headingHtml\": \"<h2>Participating Restaurants</h2>\"}}', '2026-03-17 22:11:07', '2026-03-11 18:51:52'),
(10, 'Jazz Detail Page', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>yes he is great</p>\", \"title\": \"About the Artist:\"}, \"albums\": [{\"image\": \"assets/img/jazz/album/8383dd517b625acbd4920362fb1aeb50.jpg\", \"title\": \"gumbo\", \"artist\": \"gumbo\", \"description_html\": \"<p>idk</p>\"}], \"artist\": {\"name\": \"Gumbo Kings\", \"kicker\": \"Haarlem Jazz\", \"breadcrumb\": {\"current\": \"Gumbo Kings\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/jazz/detail/9e29e0b7481ad723702152d20468c81d.png\", \"caption\": null}, \"secondary\": [{\"image\": \"assets/img/jazz/detail/5f836c1579a169b8e10b4d88776f9808.png\", \"caption\": \"\"}, {\"image\": \"assets/img/jazz/detail/5dd0491b3709bf5deb46af72963f27f4.png\", \"caption\": \"\"}]}, \"hero_title\": \"Gumbo Kings\", \"cover_image\": \"\", \"hero_subtitle\": \"Haarlem Jazz\"}, \"events\": {\"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"yes\", \"no\", \"\", \"\"], \"title\": \"Band Members:\"}, \"career_highlights\": {\"left_html\": \"<p>failure</p>\", \"right_html\": \"<p>second failure</p>\"}}', NULL, '2026-03-12 11:48:31'),
(11, 'Gumbo Kings', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>idk</p>\", \"title\": \"About the Artist:\"}, \"albums\": [{\"image\": \"assets/img/jazz/album/5c6cc0e7e26847ca824c66dc2f20342a.png\", \"title\": \"gumbo\", \"artist\": \"idk\", \"description_html\": \"<p>dik</p>\"}], \"artist\": {\"name\": \"Gumbo Kings\", \"kicker\": \"Haarlem Jazz\", \"breadcrumb\": {\"current\": \"Gumbo Kings\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/jazz/detail/829b8696fad24610c5e78b793d277eb8.png\", \"caption\": null}, \"secondary\": [{\"image\": \"assets/img/jazz/detail/b6f581d3351de54bf4c1da0ae7bd72d8.jpg\", \"caption\": \"\"}, {\"image\": \"assets/img/jazz/detail/70faf778e4667ef47e0411bd32659f63.png\", \"caption\": \"\"}]}, \"hero_title\": \"Gumbo Kings\", \"cover_image\": \"assets/img/jazz/detail/411280858c8fc3f302bb732c9e8d42bc.png\", \"hero_subtitle\": \"Haarlem Jazz\"}, \"events\": {\"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"\", \"\", \"\", \"\"], \"title\": \"Band Members:\"}, \"career_highlights\": {\"left_html\": \"<p>dik</p>\", \"right_html\": \"<p>dik</p>\"}}', NULL, '2026-03-12 11:49:29'),
(12, 'Restaurant Fris', 'Yummy_Detail_Page', '{\"chefSection\": {\"html\": \"<h3>Meet Chef Rick May</h3>\\r\\n<p>Having led the kitchen since 2014, Rick pours his heart into every menu, which changes every eight weeks to capture the best of the season. Under his leadership, Restaurant Fris has been awarded a Michelin Bib Gourmand, proving that high quality and a warm welcome go hand in hand.</p>\", \"imagePath\": \"/assets/img/yummy/detail/restaurantFris/chef_fris.png\"}, \"heroSection\": {\"images\": [{\"path\": \"/assets/img/yummy/detail/restaurantFris/fris_hero_1.png\", \"caption\": \"Softly cooked cod with spicy shrimp and green herbs\"}, {\"path\": \"/assets/img/yummy/detail/restaurantFris/fris_hero_2.png\", \"caption\": \"Restaurant Fris interior\"}, {\"path\": \"/assets/img/yummy/detail/restaurantFris/fris_hero_3.png\", \"caption\": \"Softly cooked cod with spicy shrimp and green herbs\"}]}, \"menuSection\": {\"html\": \"<h3>The Festival Menu</h3>\\r\\n<p>No fuss, just exceptional taste. For the Haarlem Festival, we are serving a special menu that showcases our kitchen\'s creativity. From the first bite to the last, expect pure flavors and beautiful presentations that reflect our love for the product.</p>\", \"imagePath\": \"/assets/img/yummy/detail/restaurantFris/menu_fris.png\"}, \"aboutSection\": {\"descriptionHtml\": \"<h2>A modern restaurant serving classic French cuisine with surprising global influences here and there</h2>\\r\\n<p>Passion and respect for the product are our top priorities, and you can taste it. No fuss, just pure flavours</p>\"}, \"contentSection1\": {\"html\": \"<h3>Special <em>amuse-bouche</em> during the festival</h3>\\r\\n<p>Kick off your evening at Restaurant Fris with an exclusive festival amuse-bouche, specially crafted by chef Rick May. Expect a refined blend of classic French technique with a worldly twist.</p>\", \"images\": [{\"path\": \"/assets/img/yummy/detail/restaurantFris/amuse_1.png\", \"caption\": \"Special amuse-bouche\"}, {\"path\": \"/assets/img/yummy/detail/restaurantFris/amuse_2.png\", \"caption\": \"Special amuse-bouche\"}]}, \"informationBlock\": \"<div class=\\\"info-block\\\">\\r\\n<h3>Information:</h3>\\r\\n<ul>\\r\\n<li><strong>Location:</strong> Twijnderslaan 7, 2012 BG Haarlem</li>\\r\\n<li><strong>Chef:</strong> Rick May</li>\\r\\n<li><strong>Duration:</strong> 1.5 hours</li>\\r\\n<li><strong>Rating:</strong> 4</li>\\r\\n<li><strong>Price:</strong> 45 &euro;</li>\\r\\n<li><strong>Price for Child(&lt;12):</strong> 22,50 &euro;</li>\\r\\n</ul>\\r\\n</div>\"}', '2026-03-19 08:46:49', '2026-03-17 20:36:34'),
(14, 'Soul six', 'Jazz_Detail_Page', '{\"tabs\": {\"labels\": {\"album\": \"Album\", \"career\": \"Career Highlights\", \"events\": \"Events\"}, \"default\": \"events\"}, \"about\": {\"html\": \"<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\", \"text\": \"\", \"title\": \"About\"}, \"albums\": [{\"image\": {\"alt\": \"\", \"src\": \"assets/img/page/jazz_detail_page/708353ee8deb86d6338339bf21fbc2f1.png\", \"caption\": \"\"}, \"title\": \"nonononononononoonononnononononononononoonononno\", \"artist\": \"nonononononononoonononnononononononononoonononno\", \"description\": \"\", \"description_html\": \"<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\"}], \"artist\": {\"name\": \"Soul Six\", \"kicker\": \"Haarlem Jazz\", \"breadcrumb\": {\"current\": \"Soul Six\", \"back_href\": \"/jazz\", \"back_label\": \"Jazz Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/page/jazz_detail_page/c2fdaeb6b591763f736e0759aac81250.png\"}, \"secondary\": [{\"image\": \"assets/img/page/jazz_detail_page/4bb30574ac4e8d5c4c948976e2ed24a8.png\", \"caption\": \"\"}, {\"image\": \"assets/img/page/jazz_detail_page/3a052a83ab0702c4f5835da065716dd7.png\", \"caption\": \"\"}]}, \"hero_title\": \"Soul Six\", \"cover_image\": \"\", \"hero_subtitle\": \"Haarlem Jazz\"}, \"events\": {\"ticket_button_label\": \"Tickets\"}, \"band_members\": {\"items\": [\"nonononononononoonononnononononononononoonononnononononononononoonononnononononononononoonononno\", \"nonononononononoonononnononononononononoonononnononononononononoonononno\"], \"title\": \"Band Members\"}, \"career_highlights\": {\"left\": [], \"right\": [], \"left_html\": \"<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\", \"right_html\": \"<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\\r\\n<p>nonononononononoonononno</p>\"}}', NULL, '2026-04-01 10:31:13'),
(15, 'A Stroll Through History', 'History_Homepage', '{\"map\": {\"title\": \"Route Map\", \"description_html\": \"<p>The map section introduces the main landmarks on the route and links through to each detail page so editors can manage every stop separately through the CMS.</p>\", \"card_button_label\": \"Bekijk locatie\"}, \"hero\": {\"title\": \"A Stroll Through History\", \"kicker\": \"Walk the city through time\", \"subtitle_html\": \"<p>Discover Haarlem through a guided walking tour that moves from the Grote Markt to the Church of St. Bavo and beyond. The route combines architecture, civic history, and memorable stories into one accessible festival experience.</p>\", \"background_image\": \"assets/img/homepage/history.png\"}, \"booking\": {\"title\": \"Choose your tour\", \"date_label\": \"Select Date\", \"time_label\": \"Select Time\", \"tickets_label\": \"Tickets\", \"language_label\": \"Select Language\", \"description_html\": \"<p>Select a date, time, and language to reserve a spot on the history walk. Timetable availability is sourced from the HistoryEvent table so the schedule remains tied to the actual bookable tours.</p>\", \"family_price_label\": \"Family (max 4)\", \"family_price_value\": \"60\", \"single_price_label\": \"Single\", \"single_price_value\": \"17.50\", \"reserve_button_label\": \"Reserve now\", \"availability_note_html\": \"<p>Availability updates automatically from the bookable history tours stored in the database.</p>\"}, \"overview\": {\"lead_html\": \"<p>This route invites visitors to explore the city centre through the places that shaped Haarlem\'s identity. Designed for a broad audience, it combines clear storytelling with historic landmarks and visual cues from the cityscape.</p>\", \"break_html\": \"<p>Midway through the route, visitors pause at a historic stop for a short break before continuing toward the final section of the city. The tour closes with a view of Haarlem\'s heritage as something still visible in daily life.</p>\", \"route_html\": \"<p>Starting at the Grote Markt and continuing past the Grote Kerk, the walk highlights political, religious, and cultural milestones. Each stop explains how buildings, streets, and public spaces connected to larger moments in Haarlem\'s development.</p>\"}}', NULL, '2026-04-09 16:03:34'),
(16, 'Grote Markt', 'History_Detail_Page', '{\"hero\": {\"title\": \"Grote Markt\", \"kicker\": \"Historic landmark\", \"gallery\": [{\"image\": \"assets/img/homepage/history.png\", \"caption\": \"Historic street scene\"}, {\"image\": \"assets/img/homepage/church.png\", \"caption\": \"Market square view\"}, {\"image\": \"assets/img/jazz/event/wicked-jazz-sounds-grote-markt.jpg\", \"caption\": \"The square today\"}], \"main_image\": \"assets/img/homepage/city.png\"}, \"meta\": {\"slug\": \"grote-markt\", \"map_marker\": {\"x\": 48, \"y\": 58}, \"navigation\": {\"back_href\": \"/history\", \"back_label\": \"Back to route\"}, \"sort_order\": 1, \"listing_image\": \"assets/img/homepage/city.png\", \"listing_title\": \"Grote Markt\", \"listing_summary\": \"The Grote Markt anchors Haarlem\'s historic centre and serves as one of the key stops on the walking route.\"}, \"map_card\": {\"title\": \"Grote Markt\", \"summary\": \"Use this stop as one of the central points on the route map and as the place where the city\'s public life is easiest to read.\", \"button_label\": \"Bekijk locatie\"}, \"story_blocks\": [{\"image\": \"assets/img/homepage/city.png\", \"title\": \"The civic heart of Haarlem\", \"body_html\": \"<p>The Grote Markt has long been the social and commercial centre of Haarlem. Traders, performers, public announcements, and celebrations all converged here, making the square a natural starting point for a guided walk through the city.</p>\", \"image_position\": \"left\"}, {\"image\": \"assets/img/homepage/history.png\", \"title\": \"A square shaped by trade\", \"body_html\": \"<p>During the Middle Ages the square connected Haarlem to regional trade routes. Markets expanded around the Town Hall and church buildings, while nearby facades reflected the prosperity of the Dutch Golden Age and the city\'s civic pride.</p>\", \"image_position\": \"right\"}, {\"image\": \"assets/img/jazz/event/wicked-jazz-sounds-grote-markt.jpg\", \"title\": \"A living public space\", \"body_html\": \"<p>Today the Grote Markt remains one of Haarlem\'s most recognizable places. Its surrounding buildings preserve layers of civic identity, making the location an essential stop for understanding the city and for orienting visitors along the history route.</p>\", \"image_position\": \"left\"}]}', NULL, '2026-04-09 16:03:34'),
(17, 'Church of St. Bavo', 'History_Detail_Page', '{\"hero\": {\"title\": \"Church of St. Bavo\", \"kicker\": \"Historic church\", \"gallery\": [{\"image\": \"assets/img/homepage/history.png\", \"caption\": \"Historic painting of St. Bavo\"}, {\"image\": \"assets/img/homepage/church.png\", \"caption\": \"View across the square\"}, {\"image\": \"assets/img/homepage/city.png\", \"caption\": \"Church interior and surrounding streets\"}], \"main_image\": \"assets/img/homepage/church.png\"}, \"meta\": {\"slug\": \"church-of-st-bavo\", \"map_marker\": {\"x\": 42, \"y\": 52}, \"navigation\": {\"back_href\": \"/history\", \"back_label\": \"Back to route\"}, \"sort_order\": 2, \"listing_image\": \"assets/img/homepage/church.png\", \"listing_title\": \"Church of St. Bavo\", \"listing_summary\": \"The Grote Kerk of St. Bavo reveals Haarlem\'s religious, civic, and architectural history in a single landmark.\"}, \"map_card\": {\"title\": \"Church of St. Bavo\", \"summary\": \"This stop explains how religion, patronage, architecture, and music helped shape Haarlem\'s identity over time.\", \"button_label\": \"Bekijk locatie\"}, \"story_blocks\": [{\"image\": \"assets/img/homepage/church.png\", \"title\": \"A landmark at the heart of Haarlem\", \"body_html\": \"<p>The Church of St. Bavo, also known as the Grote Kerk, stands at the centre of Haarlem\'s historic core. Its scale and construction history reflect the city\'s growth in wealth, faith, and urban ambition from the late medieval period onward.</p>\", \"image_position\": \"left\"}, {\"image\": \"assets/img/homepage/history.png\", \"title\": \"Architecture and civic memory\", \"body_html\": \"<p>As the city expanded, the church evolved from a smaller stone structure into the large basilica seen today. Details in the nave, chapels, and stained glass reveal the influence of guilds, wealthy patrons, and the civic identity that shaped Haarlem for centuries.</p>\", \"image_position\": \"right\"}, {\"image\": \"assets/img/homepage/city.png\", \"title\": \"Music, faith, and continuity\", \"body_html\": \"<p>Inside, visitors encounter the famous Muller organ and a layered interior that connects Catholic origins, Protestant adaptation, and modern heritage use. The church remains one of the route\'s clearest windows into Haarlem\'s long cultural continuity.</p>\", \"image_position\": \"left\"}]}', NULL, '2026-04-09 16:03:34'),
(18, 'Mister Anansi', 'Stories_Detail_Page', '{\"intro\": {\"html\": \"<p>Mister Anansi reveals that storytelling is more than entertainment&mdash;it&rsquo;s a way to pass on wisdom, share laughter, and truly bring people together.</p>\", \"image\": \"assets/img/stories/mranansi.png\", \"bullets\": [\"discover timeless stories passed down through generations\", \"experience humor and wisdom woven into every tale\", \"learn how stories connect cultures and people\", \"leave inspired by the power of storytelling\"]}, \"story\": {\"name\": \"Mister Anansi\", \"kicker\": \"Stories\", \"breadcrumb\": {\"current\": \"Mister Anansi\", \"back_href\": \"/stories\", \"back_label\": \"Stories Event\"}, \"hero_media\": {\"main\": {\"image\": \"assets/img/stories/anansihero.jpg\", \"caption\": \"\"}, \"secondary\": [{\"image\": \"assets/img/stories/mranansi.png\", \"caption\": \"\"}]}, \"hero_title\": \"Mister Anansi\", \"cover_image\": \"assets/img/stories/anansihero.jpg\", \"hero_subtitle\": \"Stories travel further than words.\", \"hero_body_html\": \"<p>Mister Anansi brings ancient tales to life, weaving humor, wisdom, and imagination into a storytelling experience that connects generations.</p>\"}, \"video\": {\"title\": \"Watch Mister Anansi in Action\", \"embed_url\": \"https://www.youtube.com/embed/OYQNvjaxo38\", \"thumbnail\": \"assets/img/stories/storytelling.png\", \"description\": \"Get a taste of the storytelling magic with this video. See how Mister Anansi brings humor, heart, and imagination to life on stage.\"}, \"origin\": {\"html\": \"<p>Mister Anansi is the stage name of Dutch storyteller and theater maker Wijnand Stomp, one of the most beloved performers in the Netherlands. Drawing on his Afro-Caribbean heritage and more than 30 years of experience, he creates playful, humorous, and imaginative performances that captivate audiences of all ages.</p>\\r\\n<p>His work blends storytelling, comedy, and theatricality, earning national acclaim including Verteller van het Jaar and the Pearl of the Dutch Caribbean in Art &amp; Literature.</p>\", \"image\": \"assets/img/stories/image 123.png\", \"title\": \"Where the Stories Began\"}, \"event_card\": {\"about_text\": \"Each ticket grants access to this story at the listed time and location. Tickets are limited.\", \"about_title\": \"About tickets\", \"meta_labels\": {\"date\": \"date\", \"time\": \"time\", \"place\": \"place\", \"language\": \"language\", \"age_group\": \"age group\"}, \"price_label\": \"Ticket price:\", \"total_label\": \"Total:\", \"button_label\": \"Add tickets to cart\", \"price_suffix\": \"per person\", \"reserve_title\": \"Reserve tickets\", \"quantity_label\": \"How many people are coming?\"}}', '2026-04-10 12:38:01', '2026-04-09 20:09:07'),
(19, 'Test Restaurant', 'Yummy_Detail_Page', '{\"chefSection\": {\"html\": \"<p>kdwakdkl kladkdwkl kdlwkawlkd dkdkk kmda dmamd mmdmd dimd idmin</p>\", \"imagePath\": \"assets/img/page/yummy_detail_page/3953487d9434768769e73bb915a5b06e.jpg\"}, \"heroSection\": {\"images\": [{\"path\": \"assets/img/page/yummy_detail_page/8576d4d58ae5ac70faaa2f52fb4c7145.jpeg\", \"caption\": \"\"}, {\"path\": \"assets/img/page/yummy_detail_page/5b172da9a8a00c35cf42d8e7936c1c7c.jpg\", \"caption\": \"\"}, {\"path\": \"assets/img/page/yummy_detail_page/65bcd0d2e20a9f252a5444de0b5abc7b.jpg\", \"caption\": \"\"}]}, \"menuSection\": {\"html\": \"<p>mamout poawpfo&nbsp; fopakfpkof&nbsp;</p>\", \"imagePath\": \"assets/img/page/yummy_detail_page/7e300c33a684b6bd70c0ee8e86f75ee0.jpeg\"}, \"aboutSection\": {\"descriptionHtml\": \"<p>test test</p>\"}, \"contentSection1\": {\"html\": \"<p>fawkfm&nbsp; m;afwmafm m llfalfw w[faf flppfffwa</p>\", \"images\": [{\"path\": \"assets/img/page/yummy_detail_page/2e1326c460e31c566cdd79918a20b199.jpg\", \"caption\": \"\"}]}, \"informationBlock\": \"<p>kfkf awo<br>flwflawalf<br>lfwlflaw&nbsp;<br>flalf aw<br>flfla</p>\"}', '2026-04-09 20:40:08', '2026-04-09 20:18:31'),
(20, 'Dance Artist Detail Page', 'Dance_Detail_Page', '{\"story\": {\"intro_html\": \"<p>&nbsp;Martin Garrix is one of the most influential young producers of the modern dance era, redefining what it means to be a global electronic artist. He burst onto the world stage at just 17 years old with Animals, a track that reshaped festival music and cemented his position as a prodigy. Since then, Garrix has evolved into a versatile creator&mdash;balancing chart-topping hits, innovative productions, and collaborations across pop, dance, and electronic genres.</p>\", \"hero_bullets\": [], \"highlights_html\": \"<p>Youngest ever DJ Mag World&rsquo;s No.1 DJ winner (2016).<br>Breakthrough global hit Animals changed modern EDM.<br>Founder of STMPD RCRDS, pushing innovative electronic music.<br>Performed the 2021 UEFA Euro Opening Ceremony.<br>Official resident artist at Ushua&iuml;a Ibiza.</p>\"}, \"artist\": {\"name\": \"Martin Garrix\", \"kicker\": \"Haarlem Dance\", \"back_href\": \"/dance\", \"back_label\": \"Dance Event\", \"hero_title\": \"Martin Garrix\", \"cover_image\": \"assets/img/page/dance_detail_page/9c7ef3bca8971c825620644baf7881c5.png\", \"hero_subtitle\": \"\", \"portrait_image\": \"assets/img/page/dance_detail_page/4f13d320775b1811344290043eba73b6.png\"}, \"tracks\": {\"ep\": {\"name\": \"Sentio (2022)\", \"label\": \"EP\", \"cover_image\": \"assets/img/page/dance_detail_page/ebe6cf843db1ff2c03655fbc68a94a12.jpg\", \"description\": \"<p>A festival-driven project released as Garrix returned to major stages post-pandemic. Each track gained massive live traction, and the EP generated millions of plays within days, sparking excitement among fans for his new era of festival music.</p>\"}, \"title\": \"Important Tracks / Albums\", \"tracks\": [{\"name\": \"Animals\", \"cover_image\": \"assets/img/page/dance_detail_page/6f391b9b223b0eb4a2e0baaf53831477.jpg\", \"description\": \"<p>A historic EDM breakthrough hit. With over 1.7 billion YouTube views and enormous streaming numbers, Animals transformed Martin Garrix into a global superstar at age 17. It is often credited with reshaping festival music and inspiring a new wave of young producers.</p>\"}, {\"name\": \"Scared To Be Lonely (w/ Dua Lipa)\", \"cover_image\": \"assets/img/page/dance_detail_page/ad59889542545161aed6cded258316de.jpg\", \"description\": \"<p>A beautifully emotional dance-pop collaboration that became a worldwide hit. Its combined streams reach multiple billions, and it remains one of the most-played electronic songs in recent years. The track solidified Garrix&rsquo;s crossover success.</p>\"}, {\"name\": \"In The Name of Love (w/ Bebe Rexha)\", \"cover_image\": \"assets/img/page/dance_detail_page/16ca2de155702c206f198fdaa7cacee1.jpg\", \"description\": \"<p>Charted internationally and became one of Garrix&rsquo;s signature melodic releases. With hundreds of millions of streams, this track marked his expansion into more radio-friendly electronic pop while maintaining his festival appeal.</p>\"}]}, \"feature\": {\"text_html\": \"<p>&nbsp;As founder of STMPD RCRDS, he has become a driving force in contemporary EDM, nurturing new talent while pushing his own sound forward. Garrix&rsquo;s music is known for its powerful emotional impact, blending soaring melodies with dynamic drops and pristine production.<br><br>Whether performing at Ibiza residencies, major festivals, or global broadcasts like the UEFA Euro Opening Ceremony, Martin Garrix brings a rare combination of technical excellence, musical sensitivity, and pure explosive energy. His performance at Haarlem Dance promises a rush of euphoric moments, unforgettable melodies, and the unmistakable charisma that defines his sound.</p>\", \"main_image\": \"assets/img/page/dance_detail_page/f6ed5102b8a6bb90bcdb09682d6511d6.jpg\", \"overlay_image\": \"assets/img/page/dance_detail_page/e83afcf85bcb1c138c12373139a4c534.jpg\"}, \"gallery\": [], \"tickets\": {\"title\": \"Available Dance Sets\", \"events\": [{\"title\": \"\", \"event_id\": 49, \"location\": \"\", \"day_label\": \"\", \"time_label\": \"\", \"price_label\": \"\"}, {\"title\": \"\", \"event_id\": 51, \"location\": \"\", \"day_label\": \"\", \"time_label\": \"\", \"price_label\": \"\"}, {\"title\": \"\", \"event_id\": 56, \"location\": \"\", \"day_label\": \"\", \"time_label\": \"\", \"price_label\": \"\"}], \"ticket_button_label\": \"ADD\"}}', '2026-04-10 14:52:51', '2026-04-10 14:15:02'),
(21, 'Lichtfabriek', 'Dance_Location_Page', '{\"story\": {\"intro_html\": \"\"}, \"venue\": {\"name\": \"Lichtfabriek\", \"phone\": \"0233030500\", \"kicker\": \"Haarlem Dance\", \"address\": \"Rockplein 6, 2033 KK Haarlem\", \"venue_id\": 7, \"back_href\": \"/dance\", \"back_label\": \"Dance event\", \"hero_title\": \"\", \"cover_image\": \"assets/img/page/dance_location_page/9fbd266938da626b916aac6212e02aee.png\", \"website_url\": \"http://www.lichtfabriek.nl/\", \"google_maps_url\": \"https://www.google.com/maps/place/De+Lichtfabriek+%7C+Haarlem/@52.3863512,4.4993178,12z/data=!3m1!5s0x47c5ef64afa1604f:0xef17f1b96a1b6518!4m10!1m2!2m1!1sLichtfabriek!3m6!1s0x47c5ef63abd6db51:0x1a67ea388cf3c163!8m2!3d52.3863512!4d4.6517531!15sCgxMaWNodGZhYnJpZWuSAQtldmVudF92ZW51ZeABAA!16s%2Fg%2F1v9l9zq5?entry=ttu&g_ep=EgoyMDI2MDQwNy4wIKXMDSoASAFQAw%3D%3D\"}, \"tickets\": {\"title\": \"TOTAL EVENTS\", \"ticket_button_label\": \"ADD\"}}', NULL, '2026-04-10 17:29:26');

-- --------------------------------------------------------

--
-- Table structure for table `PassEvent`
--

CREATE TABLE `PassEvent` (
  `event_id` int NOT NULL,
  `festival_type` enum('jazz','dance') NOT NULL,
  `pass_scope` enum('day','all_days') NOT NULL,
  `base_price` decimal(10,2) NOT NULL,
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `PassEvent`
--

INSERT INTO `PassEvent` (`event_id`, `festival_type`, `pass_scope`, `base_price`, `active`, `created_at`, `updated_at`) VALUES
(70, 'jazz', 'day', 35.00, 1, '2026-03-19 12:36:24', '2026-03-19 12:36:24'),
(71, 'dance', 'day', 125.00, 1, '2026-03-19 12:37:05', '2026-03-19 12:37:05'),
(72, 'dance', 'all_days', 250.00, 1, '2026-03-19 12:40:09', '2026-03-19 12:40:09'),
(73, 'jazz', 'all_days', 80.00, 1, '2026-03-19 12:40:32', '2026-03-19 12:40:32');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int UNSIGNED NOT NULL,
  `user_id` int NOT NULL,
  `token_hash` char(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `used_at` datetime DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `user_id`, `token_hash`, `expires_at`, `used_at`, `created_at`) VALUES
(1, 20, 'd2cdb8ddcd5dfc7d8d436a024e549442f6d747332753b342158a59abff27bc8a', '2026-03-11 17:48:44', '2026-03-11 16:49:11', '2026-03-11 16:48:44'),
(2, 20, 'c8d85aaa926d9fd5463afd7184b36cebea081436fee62328ee2fe933fc89d8fa', '2026-03-11 17:50:19', '2026-03-11 16:51:51', '2026-03-11 16:50:19'),
(5, 31, '89a1d272acaff8feaa7e3cde092096b3a14cded1518c9f55afc9ba2159a8faf4', '2026-04-09 18:17:48', '2026-04-09 17:18:15', '2026-04-09 17:17:48');

-- --------------------------------------------------------

--
-- Table structure for table `phinxlog`
--

CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `phinxlog`
--

INSERT INTO `phinxlog` (`version`, `migration_name`, `start_time`, `end_time`, `breakpoint`) VALUES
(20260312120000, 'CreateLocationTable', '2026-03-18 22:06:48', '2026-03-18 22:06:48', 0),
(20260312120100, 'CreateDanceEventTable', '2026-03-18 22:06:48', '2026-03-18 22:06:48', 0),
(20260312120200, 'SeedDanceLocationsAndDanceEvents', '2026-03-18 22:06:48', '2026-03-18 22:06:48', 0),
(20260313100000, 'RebuildDanceEventForVenue', '2026-03-18 22:08:12', '2026-03-18 22:08:12', 0),
(20260315120000, 'EnsureDanceEventVenueSchema', '2026-03-18 22:08:12', '2026-03-18 22:08:13', 0),
(20260409000100, 'AddHistoryPagesAndEvents', '2026-04-09 16:03:34', '2026-04-09 16:03:34', 0),
(20260409000200, 'SeedHistoryEventSchedule', '2026-04-09 16:06:38', '2026-04-09 16:06:39', 0),
(20260409000300, 'ReseedHistoryEventTourCapacity', '2026-04-09 16:38:38', '2026-04-09 16:38:39', 0),
(20260409000400, 'AddHistoryEventFamilyPrice', '2026-04-09 18:10:47', '2026-04-09 18:10:47', 0),
(20260410000500, 'ExtendReservationTracking', '2026-04-10 10:29:36', '2026-04-10 10:29:37', 0);

-- --------------------------------------------------------

--
-- Table structure for table `Reservation`
--

CREATE TABLE `Reservation` (
  `id` int NOT NULL,
  `user_id` int UNSIGNED DEFAULT NULL,
  `yummy_event_id` int NOT NULL,
  `adult_count` int NOT NULL DEFAULT '0',
  `children_count` int NOT NULL DEFAULT '0',
  `note` text,
  `created_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Reservation`
--

INSERT INTO `Reservation` (`id`, `user_id`, `yummy_event_id`, `adult_count`, `children_count`, `note`, `created_at`) VALUES
(1, 1, 2, 2, 0, '', '2026-04-10 11:12:32'),
(2, 2, 3, 2, 0, 'test note', '2026-04-10 14:09:33'),
(3, 2, 2, 2, 0, '', '2026-04-10 14:18:16'),
(4, 2, 2, 1, 0, 'this is note', '2026-04-10 14:25:39'),
(5, 2, 4, 1, 0, '', '2026-04-10 14:36:24'),
(8, 2, 2, 1, 0, '', '2026-04-10 19:42:09');

-- --------------------------------------------------------

--
-- Table structure for table `StoriesEvent`
--

CREATE TABLE `StoriesEvent` (
  `event_id` int NOT NULL,
  `language` enum('NL','ENG','NL/ENG') NOT NULL,
  `age_group` varchar(50) NOT NULL,
  `story_type` varchar(255) NOT NULL,
  `location` varchar(255) NOT NULL,
  `description` text,
  `start_date` datetime NOT NULL,
  `end_date` datetime NOT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `page_id` int DEFAULT NULL,
  `img_background` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `StoriesEvent`
--

INSERT INTO `StoriesEvent` (`event_id`, `language`, `age_group`, `story_type`, `location`, `description`, `start_date`, `end_date`, `price`, `page_id`, `img_background`) VALUES
(30, 'NL', '4+', 'Stories for the whole family', 'Verhalenhuis Haarlem', NULL, '2026-07-23 16:00:00', '2026-07-23 17:00:00', 6.00, NULL, 'assets/img/stories/pooh.png'),
(31, 'NL', '16+', 'Recording podcast with audience', 'De Schuur', NULL, '2026-07-23 19:00:00', '2026-07-23 20:15:00', 12.50, NULL, 'assets/img/stories/podcastlast.png'),
(32, 'ENG', '16+', 'Stories with impact', 'Kweekcafé', NULL, '2026-07-23 20:30:00', '2026-07-23 21:45:00', 0.00, NULL, 'assets/img/stories/Buurderij (2).png'),
(33, 'NL', '10+', 'Stories for the whole family', 'Het Corrie ten Boomhuis', NULL, '2026-07-24 16:00:00', '2026-07-24 17:00:00', 0.00, NULL, 'assets/img/stories/corrie.png'),
(34, 'NL', '12+', 'Best off', 'Verhalenhuis Haarlem', NULL, '2026-07-24 19:00:00', '2026-07-24 20:30:00', 12.50, NULL, 'assets/img/stories/storytelling.png'),
(35, 'NL', '16+', 'Stories with impact', 'Kweekcafé', NULL, '2026-07-24 19:00:00', '2026-07-24 20:15:00', 0.00, NULL, 'assets/img/stories/zwammerij.png'),
(36, 'ENG', '16+', 'Recording podcast with audience', 'De Schuur', NULL, '2026-07-24 20:30:00', '2026-07-24 21:45:00', 12.50, NULL, 'assets/img/stories/flipthinking.png'),
(37, 'NL', '2+', 'Stories for the whole family', 'Theater Elswout', NULL, '2026-07-25 10:00:00', '2026-07-25 11:00:00', 10.00, 18, 'assets/img/stories/maranansi.png'),
(38, 'NL', '12+', 'Stories with impact', 'Het Corrie ten Boomhuis', NULL, '2026-07-25 13:00:00', '2026-07-25 14:30:00', 0.00, NULL, 'assets/img/stories/tenboom.png'),
(39, 'NL', '12+', 'Recording podcast with audience', 'De Schuur', NULL, '2026-07-25 14:00:00', '2026-07-25 15:15:00', 12.50, NULL, 'assets/img/stories/podcastlast.png'),
(40, 'ENG', '2+', 'Stories for the whole family', 'Theater Elswout', NULL, '2026-07-25 15:00:00', '2026-07-25 16:00:00', 10.00, NULL, 'assets/img/stories/maranansi.png'),
(41, 'ENG', '2+', 'Stories for the whole family', 'Theater Elswout', NULL, '2026-07-26 10:00:00', '2026-07-26 11:00:00', 10.00, NULL, 'assets/img/stories/maranansi.png'),
(42, 'ENG', '12+', 'Stories with impact', 'Het Corrie ten Boomhuis', NULL, '2026-07-26 13:00:00', '2026-07-26 14:30:00', 0.00, NULL, 'assets/img/stories/tenboom.png'),
(43, 'NL', '2+', 'Stories for the whole family', 'Theater Elswout', NULL, '2026-07-26 15:00:00', '2026-07-26 16:00:00', 10.00, NULL, 'assets/img/stories/maranansi.png'),
(44, 'ENG', '12+', 'Best off', 'Verhalenhuis Haarlem', NULL, '2026-07-26 16:00:00', '2026-07-26 17:30:00', 12.50, NULL, 'assets/img/stories/storytelling.png');

-- --------------------------------------------------------

--
-- Table structure for table `Ticket`
--

CREATE TABLE `Ticket` (
  `ticket_id` int NOT NULL,
  `order_item_id` int NOT NULL,
  `user_id` int UNSIGNED NOT NULL,
  `qr` varchar(255) DEFAULT NULL,
  `is_scanned` tinyint(1) NOT NULL DEFAULT '0',
  `event_id` int DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Ticket`
--

INSERT INTO `Ticket` (`ticket_id`, `order_item_id`, `user_id`, `qr`, `is_scanned`, `event_id`) VALUES
(2, 92, 18, 'TICKET_69bc05687267b2.86039116', 1, NULL),
(3, 91, 18, 'TICKET_69bc0568753ee5.65653392', 0, NULL),
(4, 94, 18, 'TICKET_69bc0568775e53.11007213', 0, NULL),
(5, 93, 18, 'TICKET_69bc05687ab121.24224390', 0, NULL),
(6, 97, 18, 'TICKET_69bc05e855e7e3.75784967', 0, NULL),
(7, 97, 18, 'TICKET_69bc05e8576068.12387421', 0, NULL),
(8, 97, 18, 'TICKET_69bc05e8594651.98329279', 0, NULL),
(9, 97, 18, 'TICKET_69bc05e85af457.41297127', 0, NULL),
(10, 97, 18, 'TICKET_69bc05e85c97d8.89846481', 0, NULL),
(11, 98, 18, 'TICKET_69bc11945d4204.22296303', 0, NULL),
(12, 98, 18, 'TICKET_69bc11945fa422.67339381', 0, NULL),
(13, 99, 18, 'TICKET_69c488b64b7e92.37606334', 0, NULL),
(14, 100, 18, 'TICKET_69c488b656df11.88661430', 0, NULL),
(15, 103, 18, 'TICKET_69c4f816db79b7.80307105', 0, NULL),
(16, 103, 18, 'TICKET_69c4f816dda716.65230686', 0, NULL),
(22, 110, 1, 'TICKET_69c506945b9d07.55055282', 0, NULL),
(23, 111, 18, 'TICKET_69c50708262319.08595582', 0, NULL),
(24, 111, 18, 'TICKET_69c5070827da15.84078212', 0, NULL),
(26, 121, 18, 'TICKET_69c52e92e8dbd4.22776712', 0, NULL),
(27, 118, 1, 'TICKET_69c5339d8211b7.25369445', 0, 9),
(28, 118, 1, 'TICKET_69c5339d8543c8.86191562', 0, 8),
(29, 118, 1, 'TICKET_69c5339d87b8c2.03929280', 0, 10),
(30, 118, 1, 'TICKET_69c5339d8aa199.85808902', 0, 11),
(31, 118, 1, 'TICKET_69c5339d8cd481.19896559', 0, 12),
(32, 122, 1, 'TICKET_69c534fbe50533.60216372', 0, 1),
(33, 122, 1, 'TICKET_69c534fbeaa2a5.06857214', 0, 2),
(34, 122, 1, 'TICKET_69c534fbed5500.39432478', 0, 3),
(35, 122, 1, 'TICKET_69c534fbf00a58.46333466', 0, 4),
(36, 122, 1, 'TICKET_69c534fbf28978.60973951', 0, 5),
(37, 122, 1, 'TICKET_69c534fc00fdf9.12208699', 0, 6),
(38, 122, 1, 'TICKET_69c534fc03c468.57784213', 0, 7),
(39, 123, 1, 'TICKET_69c5354ac8ad69.58846205', 0, 60),
(40, 125, 1, 'TICKET_3b37a8305381b1e447558a41917eec19', 0, 17),
(41, 129, 18, 'TICKET_24dc6fde0815d5d6e7cad723442c4759', 0, 60),
(42, 131, 1, 'TICKET_cf77c43b93e67cd7fec8e4fedc66c155', 0, 9),
(43, 131, 1, 'TICKET_f329736e9bbc68ff215f36edd6485ad8', 0, 8),
(44, 131, 1, 'TICKET_e4c7cd086b5bfba6ce06fdca31878ea7', 0, 10),
(45, 131, 1, 'TICKET_2eca567dcf611db49f8011aeb505bc9a', 0, 11),
(46, 131, 1, 'TICKET_24c153a2df4dbc655c85698f4fa81109', 0, 12),
(47, 132, 1, 'TICKET_c1ed5f3a9d5cd0400b5cd2498c81096a', 0, 9),
(48, 132, 1, 'TICKET_d33f595bba02a8d80a452c65c1197f46', 0, 8),
(49, 132, 1, 'TICKET_90c4997262b204588c2b12e61733f44b', 0, 10),
(50, 132, 1, 'TICKET_ec567be8a458aef39dd5e264e95d62cf', 0, 11),
(51, 132, 1, 'TICKET_d79443998a9785c7ccea6c09f7eb4756', 0, 12),
(52, 133, 1, 'TICKET_5b91f346ca8a725ec54477c7620b4f64', 0, 60),
(53, 126, 31, 'TICKET_afc8930075d07010f8ab8f2a6667a261', 0, 2),
(54, 128, 2, 'TICKET_58738593c83f6c0a141b0a3ddbe41420', 0, 47),
(55, 134, 31, 'TICKET_2dce52a66ffd51eb8c65c9884a7d806b', 0, 2),
(56, 135, 18, 'TICKET_d128d9d70a699752087bdf027d406ab2', 0, 62),
(57, 136, 18, 'TICKET_9f7f353cea56e0e9956f23d9b13875c2', 0, 55),
(58, 136, 18, 'TICKET_26f782034909a8901cf588d842ab12bb', 0, 56),
(59, 136, 18, 'TICKET_8e6de62af62db5987064038274f361b1', 0, 57),
(60, 136, 18, 'TICKET_7d72105e4af9b04b80612fcd9a301e4d', 0, 58),
(61, 140, 28, 'TICKET_e725bec0d18028175e79729ce14f4285', 0, 109),
(62, 140, 28, 'TICKET_5cc205d2f5a08fa85f23a656b718b323', 0, 109),
(63, 140, 28, 'TICKET_11436c2ef373e41be6d80c8ede93fce0', 0, 109),
(64, 140, 28, 'TICKET_954746d509d0c0bc1f5f4ca1b56e4ab2', 0, 109),
(65, 148, 1, 'TICKET_6499e25d044b4680c3ac04f6de9b200e', 0, 1),
(66, 148, 1, 'TICKET_5794c2bf6ea94063732e53aae8745b60', 0, 1),
(67, 149, 1, 'TICKET_3f68e362b2fd4eff85164642875edcce', 0, 1),
(68, 149, 1, 'TICKET_f903ae87a47d701be024693eb903871f', 0, 2),
(69, 149, 1, 'TICKET_885808f1843728f077836fde83b4cc6d', 0, 3),
(70, 149, 1, 'TICKET_e477e9e0eedc031c774f94a7f784956e', 0, 4),
(71, 149, 1, 'TICKET_12530e66bab10692030077614ae03afb', 0, 5),
(72, 149, 1, 'TICKET_2353941cc62d9a08da342c40d2e978d3', 0, 6),
(73, 149, 1, 'TICKET_62d51a57acec03de5c912a4f32ae1339', 0, 7),
(74, 150, 1, 'TICKET_a3b2553569437f54dfcb0577396a9a35', 0, 109),
(75, 150, 1, 'TICKET_8b6f2406106274aba96ecc826007eea3', 0, 109),
(76, 150, 1, 'TICKET_c62c39040835ccc3238d4d1d11ac3ceb', 0, 109),
(77, 150, 1, 'TICKET_1ba96febe5fa618d8f1e55af84ac7168', 0, 109),
(78, 151, 32, 'TICKET_8e08183773f8e13d70dcd9b376eb8d1b', 0, 1),
(79, 152, 32, 'TICKET_14451400cd1cf652aaff6cfb185bd354', 0, 1),
(80, 153, 31, 'TICKET_f3416224dd067d4cc565aa71c24bee6f', 0, 1),
(81, 154, 32, 'TICKET_1972149aad5da4bbf8ed130c42b77359', 0, 1),
(82, 155, 33, 'TICKET_3dbdc8b42c1decc44ee974a45bcd837a', 0, 1),
(83, 156, 14, 'TICKET_5055ce29c880ff94a05c0bc22abd7ec1', 0, 1),
(84, 160, 14, 'TICKET_cdfd3fc637d65229dd554270dd4265d8', 1, 1),
(85, 164, 32, 'TICKET_04c50d0b3357665832f1115b63606657', 0, 3),
(86, 162, 18, 'TICKET_d1830568fe5fafab4f60cbd12f9d3d20', 0, 2),
(87, 188, 18, 'TICKET_9806dd8f1c74c77a2c41f2d1bac967c6', 0, 46),
(88, 163, 1, 'TICKET_3ada8ce5ac7d7f995f07f67885d808aa', 0, 1),
(89, 163, 1, 'TICKET_ebe07cfbf3f5b54efcbd3023d2ed3430', 0, 1),
(90, 198, 2, 'TICKET_f7e0188d72a7e8564c3fb94a2b177ab6', 1, 67),
(91, 198, 2, 'TICKET_2f1295c67ad32c1b109d598f7274dda3', 0, 67),
(92, 200, 2, 'TICKET_d14cd07ed3c146e3cc24a7e88ad0c9fc', 0, 67),
(93, 201, 2, 'TICKET_dcdd9084fb72e67cdea1ed6bc09df998', 0, 69),
(98, 207, 18, 'TICKET_c2d7de8acc9c7995bdcd060635216f36', 0, 49),
(99, 208, 31, 'TICKET_841bed86f1a30e92179f646d5d2d93b0', 0, 109),
(100, 209, 18, 'TICKET_62c89252599ec9a10a6557d980fa24f6', 0, 56),
(101, 211, 18, 'TICKET_3b32dbe3123e2d75e423cdc0408e8634', 0, 46),
(102, 212, 35, 'TICKET_53a9504ba739ca78c428e19fad7c2581', 0, 46),
(103, 212, 35, 'TICKET_734b9f66d88f01b08445f1a07bd26bf8', 0, 47),
(104, 212, 35, 'TICKET_cbecd5566ce5c7d45373c5a3a9079823', 0, 48),
(105, 212, 35, 'TICKET_cbaede00515e8a5207787b23ef2ed894', 0, 49),
(106, 212, 35, 'TICKET_8d95a39b4644a22cd1f300a21b350f22', 0, 50),
(107, 212, 35, 'TICKET_83ad3b34b670dbb5ab1b71dd9e833894', 0, 51),
(108, 212, 35, 'TICKET_bd67e081188d7e94a4eae7ce7156cb3d', 0, 52),
(109, 212, 35, 'TICKET_e63b488521e3991657294c17f83bc940', 0, 53),
(110, 212, 35, 'TICKET_97c03b58303730fe5b04634777a90f30', 0, 54),
(111, 212, 35, 'TICKET_61950468a837831153a66230c1e136d1', 0, 55),
(112, 212, 35, 'TICKET_a0c883e9750df4eb017261775acb4e1f', 0, 56),
(113, 212, 35, 'TICKET_cbf9161fe5cc881991cd8c694ab1179a', 0, 57),
(114, 212, 35, 'TICKET_74796330105d185d25482ec28defed5d', 0, 58),
(115, 213, 35, 'TICKET_ccb134a41c0eca91c312afb75f223d41', 0, 2),
(116, 216, 35, 'TICKET_882631dac2e4979385fe697c2b87d399', 0, 2),
(117, 218, 35, 'TICKET_b19229be59a1c1a7d195912f9eebb6f8', 0, 46),
(118, 217, 35, 'TICKET_be2e925036b9102f8463b9ab56adb152', 0, 50),
(119, 219, 35, 'TICKET_a0396aff4d56a7aaede628c36e209074', 0, 51),
(120, 221, 35, 'TICKET_92b235d1fa50615f2e49abad36c4ddbf', 0, 1),
(121, 222, 35, 'TICKET_a8e43be3dd0e518dd197861a909229f1', 0, 52),
(122, 223, 2, 'TICKET_c808b120e34d817f6762e8bed308d326', 0, 67),
(123, 225, 35, 'TICKET_fb385fdc1053c9d8ea51045fedd8fc9e', 0, 145),
(124, 225, 35, 'TICKET_e77046f6015216496d33b26cb5513e9b', 0, 145);

-- --------------------------------------------------------

--
-- Table structure for table `User`
--

CREATE TABLE `User` (
  `id` int UNSIGNED NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `last_name` varchar(100) NOT NULL,
  `user_name` varchar(100) NOT NULL,
  `email` varchar(191) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(30) NOT NULL,
  `role` enum('admin','user','employee') CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT 'user',
  `profile_picture_path` varchar(255) DEFAULT '/assets/img/profiles/default-user.png',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `User`
--

INSERT INTO `User` (`id`, `first_name`, `last_name`, `user_name`, `email`, `password_hash`, `phone_number`, `role`, `profile_picture_path`, `created_at`, `updated_at`) VALUES
(1, 'Maksym', 'Volkov', 'Wolfik1', 'mak@gmail.com', '$2y$12$qwbwM8xJjLPewWcwbPQT5./zdonS2kN36vxiKEiA5YUjHE8fAKxIe', '12332434234', 'admin', '/assets/img/profiles/5780570413a52b7001870b180ab92690dd8dd356ca45cd43a54c670fc073940b.jpg', '2026-03-03 13:31:12', '2026-04-10 18:15:51'),
(2, 'Arthur', 'Timinyuk', 'Admin1', 'volkomaksym@gmail.com', '$2y$12$r9mYLxGv3UkQM3bURrWRr.aWhosjOAFo1u2WLDDBwbgHziCQs./rm', '', 'admin', NULL, '2026-03-04 11:46:08', '2026-04-10 14:21:57'),
(11, 'Maksyk', 'Volkov', 'Volkov1822', 'testEmailEdit@gmail.com', '$2y$12$Dai2JOfqJSENl10IDSRtr.bDewyMlDFgsqLJ8wpR4lsyHfXSBaHWq', '', 'user', NULL, '2026-03-09 18:20:16', '2026-03-16 13:31:28'),
(13, 'Maksym', 'Volkov', 'dqdqfqfgwqf', 'wolfik.maksim.1822@gmail.com', '$2y$12$FWtGEPQv7korNkA.lajWNuBamQQeeSq7ZLZoj.sed023NGdjoxRhO', '', 'user', NULL, '2026-03-09 18:31:44', '2026-03-09 18:35:05'),
(14, 'Gabriele', 'Volkov', 'asfafassfa', 'eahgabrieltor@gmail.com', '$2y$12$MC4PSCz.Lm0jQP0qkMXz7.BesyMLc/bWW8bxtjWNgFbDDzClq4hwy', '12233444', 'user', NULL, '2026-03-09 18:34:13', '2026-03-09 18:34:13'),
(15, 'Maksym', 'Volkov', 'aWFqwfqfw211', 'volkov.maksim.1822@gmail.comd', '$2y$12$pdO5SYE5zD3HpY7.zuQdYOdK8eY9lJTur4syivxHkHRuXCjWpbqIK', '', 'user', NULL, '2026-03-09 18:39:07', '2026-03-09 18:51:10'),
(16, 'Tim', 'Defourny', 'harm46', 'timd@ziggo.nl', '$2y$12$zz7giLbv2wI78.LWOZVMy.PivHm1yzQLrgpTy1A.nWYIFvARlGlqe', '', 'user', NULL, '2026-03-09 18:43:24', '2026-03-09 18:43:24'),
(17, 'Maksym', 'Volkov', 'GWJNAGWIGAO', 'volkov.maksim.1822@gmail.com', '$2y$12$IKIAlogVQc1JrVo6G0lSbek9pqvEh4.QdedJ83O7NqiNtGkrRz8au', '', 'user', NULL, '2026-03-09 18:51:26', '2026-03-09 18:51:26'),
(18, 'YuChang', 'Huang', 'YC', 'h2447485006@gmail.com', '$2y$10$IX4YigevPPr1sHwlACi3deAOmJIIc4QCJBJv8EsRvEJV2NlEjH4rq', '0627090961', 'admin', '/assets/img/profiles/users/be421bc97c85f8c08ab8b13a61ea94fe.png', '2026-03-10 19:41:33', '2026-03-26 13:09:13'),
(19, 'Gabriele', 'Volkov', 'blahblah', 'eahgabriel.tor@gmail.com', '$2y$12$RjJQoQA4XoTgb4As9bs8LOt.Qvl6O1tdXp0iQCixxB6d6qM6p0eja', '1223424324', 'user', '/assets/img/profiles/c1a70ebbfc4b54b71eb391f3c3a80f6aae3e0d88123a653e7a6f7897e63e2fab.png', '2026-03-11 11:20:59', '2026-03-11 11:29:38'),
(20, 'test', 'test2', 'test', '0150sj@dollicons.com', '$2y$12$htA9JcSdwmapLl8IzaF7TOMzCR3qscmGSOtYEcJVJu1nPrCS9KFCG', '', 'user', NULL, '2026-03-11 16:48:27', '2026-03-11 16:52:46'),
(21, 'tea', 'tea', 'tea', 'tea@gmail.com', '$2y$12$nHZoIN1RpEmbDbZXLnbjT.frdOQi/7vOqdZ.BwLN5su6RUjQA3Ndi', '', 'user', NULL, '2026-03-11 19:48:32', '2026-03-11 19:48:32'),
(23, 'TestUserCRUD', 'CRUD', 'TestGuy', 'guy@gmail.com', '$2y$12$bCM1vAyEwsGVHqNhj1LrRehKrazTk.NVcvyWsaxIn/3d16Zm87sw6', '', 'user', NULL, '2026-03-16 13:13:18', '2026-03-16 13:13:18'),
(27, 'Rabotnik', 'Shevchenko', 'Employee1', 'employee@gmial.ocm', '$2y$12$9QrxGBGM62mSFNenuIBuNO4nkABdLXPLWhTLNuzu91kLQfoiO9c3C', '', 'employee', NULL, '2026-03-19 08:58:58', '2026-03-19 08:58:58'),
(28, 'admin', 'admin', 'admin', 'admin@admin.com', '$2y$12$uBumw9.aV37.7hheGWZUfO1BKLvv7GXW3TXpJVbbluRoyuBwx9X4q', '0612345678', 'admin', NULL, '2026-03-20 01:46:07', '2026-04-09 16:31:07'),
(29, 'tim', 'd', 'timd', 'test@gmail.com', '$2y$12$NjDEeltTcQWkCwmSK664AuxoeB5phwKSVQL/JYflsbwH0WSmly94C', '', 'admin', NULL, '2026-03-25 20:29:04', '2026-03-25 20:31:47'),
(30, 'Tim', 'Defourny', 'tim', 'pdwanidnawo@gmail.com', '$2y$12$QI5FK81BRQgzBhY2DpoFHeoe80ei2EQ1pONvM6zou1wgf/c7J9x5S', '0631950262', 'admin', NULL, '2026-04-09 15:04:47', '2026-04-09 15:06:05'),
(31, 'bob', 'bob', '711510', '711510@student.inholland.nl', '$2y$12$s.yEnFVYXmBDTpj3TN2xI.GC/QNjK7wWSE5/GxFiJJd7ntZJ2YwTW', '', 'user', NULL, '2026-04-09 17:14:23', '2026-04-09 17:18:15'),
(32, 'Maksym', 'Volkov', 'Noplease', 'gabriele.amorosi@outlook.com', '$2y$12$c6c0KksQSFY/zO4zB4mV0.bz5idH6a29tst4gRnqWDG1MO378eoxK', '1212121212', 'user', NULL, '2026-04-09 19:27:04', '2026-04-09 19:27:04'),
(33, '707845', 'no', '707845', '707845@student.inholland.nl', '$2y$12$xEe7Nqo6SMdThokUN6YeLOlNLHGIOJDLgwwlG1NHSNil7L76fQVtS', '1212121212', 'user', NULL, '2026-04-09 19:52:35', '2026-04-09 19:52:35'),
(34, 'Tim', 'Defourny', 'timtest', 'vowopag211@lealking.com', '$2y$12$eLbOI9fckXhTP76j5eYiMudZxZSrC1/akjpcl/z4LcvGtrfCSCe5e', '', 'user', NULL, '2026-04-09 19:56:28', '2026-04-09 19:56:28'),
(35, 'YuChang', 'Huang', 'YC2', 'yuchanghuang112@gmail.com', '$2y$12$OkTm01v5GSzYfX/YpA/lt.w2JrTAxbUEq1gH4rmfJB8kO8qFDmHVy', '0627090961', 'user', NULL, '2026-04-10 16:38:00', '2026-04-10 16:38:00'),
(36, 'Maksym', 'Volkov', 'wolfik', 'volkov@gmail.com', '$2y$12$9hr52sPJVgrN2cg9RgI1WeHbf2tnstbBdAH5rd/c6mtJJQKOAzaMW', '', 'user', '/assets/img/profiles/users/046d7cd03b168208fb2360e510d6e8ab.jpeg', '2026-04-10 18:16:17', '2026-04-10 18:17:16');

-- --------------------------------------------------------

--
-- Table structure for table `Venue`
--

CREATE TABLE `Venue` (
  `venue_id` int UNSIGNED NOT NULL,
  `name` varchar(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `Venue`
--

INSERT INTO `Venue` (`venue_id`, `name`) VALUES
(5, 'asfasfasf'),
(12, 'Caprera Openluchttheater'),
(4, 'Grote Markt'),
(9, 'Jopenkerk'),
(7, 'Lichtfabriek'),
(1, 'Main Hall'),
(11, 'Puncher comedy club'),
(2, 'Second Hall'),
(8, 'Slachthuis'),
(3, 'Third Hall'),
(10, 'XO the Club'),
(6, 'zxczxczxc');

-- --------------------------------------------------------

--
-- Table structure for table `YummyEvent`
--

CREATE TABLE `YummyEvent` (
  `id` int NOT NULL,
  `event_id` int NOT NULL,
  `start_time` datetime DEFAULT NULL,
  `end_time` datetime DEFAULT NULL,
  `thumbnail_path` varchar(255) DEFAULT NULL,
  `page_id` int DEFAULT NULL,
  `cuisine` varchar(255) DEFAULT NULL,
  `star_rating` int DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `YummyEvent`
--

INSERT INTO `YummyEvent` (`id`, `event_id`, `start_time`, `end_time`, `thumbnail_path`, `page_id`, `cuisine`, `star_rating`, `price`) VALUES
(2, 67, '2026-07-24 17:00:00', '2026-07-24 19:00:00', '/assets/img/yummy/homepage/events/restaurant_fris.png', 12, 'Dutch - French - European', 4, 45.00),
(3, 68, '2026-07-24 19:00:00', '2026-07-24 21:00:00', '/assets/img/yummy/homepage/events/restaurant_fris.png', 12, 'Dutch - French - European', 4, 45.00),
(4, 69, '2026-07-24 21:00:00', '2026-07-24 23:00:00', '/assets/img/yummy/homepage/events/restaurant_fris.png', 12, 'Dutch - French - European', 4, 45.00);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `Artist`
--
ALTER TABLE `Artist`
  ADD PRIMARY KEY (`artist_id`),
  ADD KEY `idx_artist_name` (`name`),
  ADD KEY `idx_artist_page_id` (`page_id`);

--
-- Indexes for table `DanceEvent`
--
ALTER TABLE `DanceEvent`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_dance_row_kind` (`row_kind`),
  ADD KEY `idx_dance_sort` (`sort_order`),
  ADD KEY `fk_dance_ev_venue` (`venue_id`);

--
-- Indexes for table `Event`
--
ALTER TABLE `Event`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `HistoryEvent`
--
ALTER TABLE `HistoryEvent`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_history_event_start_date` (`start_date`),
  ADD KEY `idx_history_event_language` (`language`);

--
-- Indexes for table `JazzEvent`
--
ALTER TABLE `JazzEvent`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_jazzevent_artist_id` (`artist_id`),
  ADD KEY `fk_jazzevent_venue` (`venue_id`),
  ADD KEY `idx_jazz_start_date` (`start_date`);

--
-- Indexes for table `Location`
--
ALTER TABLE `Location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `name` (`name`);

--
-- Indexes for table `orders`
--
ALTER TABLE `orders`
  ADD PRIMARY KEY (`order_id`),
  ADD KEY `idx_order_user_status` (`user_id`,`order_status`);

--
-- Indexes for table `order_items`
--
ALTER TABLE `order_items`
  ADD PRIMARY KEY (`order_item_id`),
  ADD UNIQUE KEY `uq_order_item_order_event_day` (`order_id`,`event_id`,`pass_date_key`),
  ADD KEY `idx_order_item_order` (`order_id`),
  ADD KEY `idx_order_item_event` (`event_id`);

--
-- Indexes for table `Page`
--
ALTER TABLE `Page`
  ADD PRIMARY KEY (`Page_ID`);

--
-- Indexes for table `PassEvent`
--
ALTER TABLE `PassEvent`
  ADD PRIMARY KEY (`event_id`),
  ADD KEY `idx_pass_lookup` (`festival_type`,`pass_scope`,`active`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_password_resets_token_hash` (`token_hash`),
  ADD KEY `idx_password_resets_user_id` (`user_id`),
  ADD KEY `idx_password_resets_expires_at` (`expires_at`);

--
-- Indexes for table `phinxlog`
--
ALTER TABLE `phinxlog`
  ADD PRIMARY KEY (`version`);

--
-- Indexes for table `Reservation`
--
ALTER TABLE `Reservation`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_reservation_user_id` (`user_id`),
  ADD KEY `idx_reservation_yummy_event_id` (`yummy_event_id`);

--
-- Indexes for table `StoriesEvent`
--
ALTER TABLE `StoriesEvent`
  ADD PRIMARY KEY (`event_id`);

--
-- Indexes for table `Ticket`
--
ALTER TABLE `Ticket`
  ADD PRIMARY KEY (`ticket_id`),
  ADD UNIQUE KEY `qr` (`qr`),
  ADD KEY `order_item_id_idx` (`order_item_id`),
  ADD KEY `user_id_idx` (`user_id`),
  ADD KEY `fk_ticket_event` (`event_id`);

--
-- Indexes for table `User`
--
ALTER TABLE `User`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `user_name` (`user_name`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `Venue`
--
ALTER TABLE `Venue`
  ADD PRIMARY KEY (`venue_id`),
  ADD UNIQUE KEY `uq_venue_name` (`name`);

--
-- Indexes for table `YummyEvent`
--
ALTER TABLE `YummyEvent`
  ADD PRIMARY KEY (`id`),
  ADD KEY `event_id` (`event_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `Artist`
--
ALTER TABLE `Artist`
  MODIFY `artist_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=41;

--
-- AUTO_INCREMENT for table `Event`
--
ALTER TABLE `Event`
  MODIFY `event_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=148;

--
-- AUTO_INCREMENT for table `Location`
--
ALTER TABLE `Location`
  MODIFY `location_id` int UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `orders`
--
ALTER TABLE `orders`
  MODIFY `order_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=135;

--
-- AUTO_INCREMENT for table `order_items`
--
ALTER TABLE `order_items`
  MODIFY `order_item_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=229;

--
-- AUTO_INCREMENT for table `Page`
--
ALTER TABLE `Page`
  MODIFY `Page_ID` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `Reservation`
--
ALTER TABLE `Reservation`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Ticket`
--
ALTER TABLE `Ticket`
  MODIFY `ticket_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=125;

--
-- AUTO_INCREMENT for table `User`
--
ALTER TABLE `User`
  MODIFY `id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=37;

--
-- AUTO_INCREMENT for table `Venue`
--
ALTER TABLE `Venue`
  MODIFY `venue_id` int UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `YummyEvent`
--
ALTER TABLE `YummyEvent`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `Artist`
--
ALTER TABLE `Artist`
  ADD CONSTRAINT `fk_artist_page` FOREIGN KEY (`page_id`) REFERENCES `Page` (`Page_ID`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `DanceEvent`
--
ALTER TABLE `DanceEvent`
  ADD CONSTRAINT `fk_dance_ev_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_dance_ev_venue` FOREIGN KEY (`venue_id`) REFERENCES `Venue` (`venue_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `HistoryEvent`
--
ALTER TABLE `HistoryEvent`
  ADD CONSTRAINT `fk_history_event_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `JazzEvent`
--
ALTER TABLE `JazzEvent`
  ADD CONSTRAINT `fk_jazz_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_jazzevent_artist` FOREIGN KEY (`artist_id`) REFERENCES `Artist` (`artist_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_jazzevent_venue` FOREIGN KEY (`venue_id`) REFERENCES `Venue` (`venue_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `orders`
--
ALTER TABLE `orders`
  ADD CONSTRAINT `fk_order_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `order_items`
--
ALTER TABLE `order_items`
  ADD CONSTRAINT `fk_order_item_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_order_item_order` FOREIGN KEY (`order_id`) REFERENCES `orders` (`order_id`) ON DELETE CASCADE;

--
-- Constraints for table `PassEvent`
--
ALTER TABLE `PassEvent`
  ADD CONSTRAINT `fk_pass_event_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `Reservation`
--
ALTER TABLE `Reservation`
  ADD CONSTRAINT `fk_reservation_user` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `Reservation_ibfk_1` FOREIGN KEY (`yummy_event_id`) REFERENCES `YummyEvent` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `StoriesEvent`
--
ALTER TABLE `StoriesEvent`
  ADD CONSTRAINT `StoriesEvent_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE;

--
-- Constraints for table `Ticket`
--
ALTER TABLE `Ticket`
  ADD CONSTRAINT `fk_ticket_event` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_order_item_fk` FOREIGN KEY (`order_item_id`) REFERENCES `order_items` (`order_item_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ticket_user_fk` FOREIGN KEY (`user_id`) REFERENCES `User` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `YummyEvent`
--
ALTER TABLE `YummyEvent`
  ADD CONSTRAINT `YummyEvent_ibfk_1` FOREIGN KEY (`event_id`) REFERENCES `Event` (`event_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
