-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 06, 2026 at 04:41 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `concert_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `concert_id` int(11) DEFAULT NULL,
  `quantity` int(11) DEFAULT 1,
  `total_price` decimal(10,2) DEFAULT 0.00,
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending_payment','verification_pending','approved','rejected') DEFAULT 'pending_payment',
  `qr_code_data` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `concert_id`, `quantity`, `total_price`, `booking_date`, `status`, `qr_code_data`) VALUES
(1, 1, 1, 5, 375.00, '2025-12-01 16:12:45', 'approved', 'TICKET-1-692dbe9e73cc2'),
(2, 2, 2, 5, 425.00, '2025-12-01 16:52:24', 'approved', 'TICKET-2-692dcb1a0a279'),
(3, 1, 1, 1, 75.00, '2025-12-01 17:06:59', 'rejected', NULL),
(4, 1, 3, 1, 20.00, '2025-12-03 09:32:20', 'rejected', NULL),
(5, 3, 2, 1, 85.00, '2025-12-18 18:44:07', 'approved', 'TICKET-5-69444bbf06646'),
(6, 4, 1, 1, 75.00, '2025-12-18 19:30:22', 'pending_payment', NULL),
(7, 4, 3, 1, 30.00, '2025-12-18 19:56:56', 'pending_payment', NULL),
(8, 4, 2, 1, 127.50, '2025-12-18 19:57:57', 'approved', 'TICKET-8-69445da5b3de8'),
(9, 5, 2, 1, 85.00, '2025-12-19 07:53:42', 'approved', 'TICKET-9-6945051c0845e'),
(10, 5, 3, 1, 30.00, '2025-12-19 07:59:36', 'verification_pending', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `booking_seats`
--

CREATE TABLE `booking_seats` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `seat_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `booking_seats`
--

INSERT INTO `booking_seats` (`id`, `booking_id`, `seat_id`) VALUES
(1, 7, 21),
(2, 8, 119),
(3, 9, 151),
(4, 10, 23);

-- --------------------------------------------------------

--
-- Table structure for table `concerts`
--

CREATE TABLE `concerts` (
  `id` int(11) NOT NULL,
  `artist` varchar(100) DEFAULT NULL,
  `venue` varchar(100) DEFAULT NULL,
  `event_date` datetime DEFAULT NULL,
  `price` decimal(10,2) DEFAULT NULL,
  `capacity` int(11) DEFAULT 100,
  `description` text DEFAULT NULL,
  `spotify_url` varchar(255) DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `concerts`
--

INSERT INTO `concerts` (`id`, `artist`, `venue`, `event_date`, `price`, `capacity`, `description`, `spotify_url`, `image_url`) VALUES
(1, '53 Universe - Salam Terakhir', 'Vox Live, KL', '2025-08-23 00:08:00', 75.00, 99, '3 Universe invites you to 𝓢𝓪𝓵𝓪𝓶 𝓣𝓮𝓻𝓪𝓴𝓱𝓲𝓻 — an overdue presentation celebrating their breakout album ”𝕾𝖊𝖒𝖔𝖌𝖆 𝕯𝖎𝖕𝖊𝖗𝖒𝖚𝖉𝖆𝖍𝖐𝖆𝖓”. Step into the sonic cosmos that elevates the mind and soul through a one-of-a-kind experience.\r\n\r\nDate: Saturday, 23 August 2025\r\nTime: 8PM (doors open 7PM)\r\nVenue: VOX Live, Kuala Lumpur\r\nPrice: RM75\r\n\r\nExpect the full album front-to-back, surprise remixes and performances from Pele L., Fuego, YBJ, Ryb3na, A2M, AMK, Madmuz, Faiz Ruzayn, and IMRN & more. Limited tix, zero reruns. See u there Ⓜ️🐉', NULL, 'img_692dbe4c69e0e0.79070002.jpg'),
(2, 'MidLyfe - MORELYFE 2025', 'JioSpace, Petaling Jaya', '2025-12-02 00:16:00', 85.00, 97, 'll', NULL, 'img_692dbf6858c547.93272095.jpg'),
(3, 'MOTORMANIAC 4TH ANNUAL EVENT', 'Rooftop @ Level 7, MyTown KL', '2025-09-06 09:00:00', 20.00, 98, 'The party in the sky never stops on the rooftop! Let’s get the show started! Which one you wanna see most? 🏇🏾🏁\r\n\r\n🔊 MOTORMANIAC 4TH ANNUAL EVENT🔊\r\n\r\n🗺️ : Rooftop @ Level 7, MyTown KL\r\n📆 : Saturday, 6th of September 2025\r\n⏰ : 9AM - 12AM\r\n🎟️ : RM20 for ONE DAY ONLY!\r\n\r\n🐉 KIDS UNDER THE AGE OF 12 AND OKU CAN ENTER FOR FREE! 🐉\r\n\r\n🤯 SEE YOU GUYS THERE! ', 'https://open.spotify.com/album/4IdoG1tL7UPIG9g8G8jhiX?si=QDPUW_lmRD6swH8Jk2llew', 'img_692e992a04ecb8.66097171.jpg'),
(4, 'Puting Beliung TENXI', 'ZEPP Kuala Lumpur', '2026-01-10 20:00:00', 130.00, 300, 'Guest Artist Naykilla', '', 'img_6952a1ec3a8f10.27560920.jpg'),
(5, 'Malam - SIMFONI LUKA', 'Mega Star Arena,Kuala Lumpur', '2026-01-24 20:30:00', 188.00, 400, 'For Revenge', '', 'img_6952a2a5b67176.87622534.webp');

-- --------------------------------------------------------

--
-- Table structure for table `concert_seats`
--

CREATE TABLE `concert_seats` (
  `id` int(11) NOT NULL,
  `concert_id` int(11) NOT NULL,
  `seat_code` varchar(10) NOT NULL,
  `seat_type` enum('VIP','REGULAR') DEFAULT 'REGULAR',
  `seat_price` decimal(10,2) NOT NULL,
  `status` enum('available','held','booked') DEFAULT 'available',
  `hold_until` datetime DEFAULT NULL,
  `held_by_user_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `concert_seats`
--

INSERT INTO `concert_seats` (`id`, `concert_id`, `seat_code`, `seat_type`, `seat_price`, `status`, `hold_until`, `held_by_user_id`) VALUES
(1, 1, 'A1', 'VIP', 120.00, 'available', NULL, NULL),
(2, 1, 'A2', 'VIP', 120.00, 'available', NULL, NULL),
(3, 1, 'A3', 'VIP', 120.00, 'available', NULL, NULL),
(4, 1, 'B1', 'REGULAR', 80.00, 'available', NULL, NULL),
(5, 1, 'B2', 'REGULAR', 80.00, 'available', NULL, NULL),
(6, 1, 'B3', 'REGULAR', 80.00, 'available', NULL, NULL),
(7, 3, 'A1', 'VIP', 30.00, 'available', NULL, NULL),
(8, 3, 'A2', 'VIP', 30.00, 'available', NULL, NULL),
(9, 3, 'A3', 'VIP', 30.00, 'available', NULL, NULL),
(10, 3, 'A4', 'VIP', 30.00, 'available', NULL, NULL),
(11, 3, 'A5', 'VIP', 30.00, 'available', NULL, NULL),
(12, 3, 'A6', 'VIP', 30.00, 'available', NULL, NULL),
(13, 3, 'A7', 'VIP', 30.00, 'available', NULL, NULL),
(14, 3, 'A8', 'VIP', 30.00, 'available', NULL, NULL),
(15, 3, 'A9', 'VIP', 30.00, 'available', NULL, NULL),
(16, 3, 'A10', 'VIP', 30.00, 'available', NULL, NULL),
(17, 3, 'B1', 'VIP', 30.00, 'available', NULL, NULL),
(18, 3, 'B2', 'VIP', 30.00, 'available', NULL, NULL),
(19, 3, 'B3', 'VIP', 30.00, 'available', NULL, NULL),
(20, 3, 'B4', 'VIP', 30.00, 'available', NULL, NULL),
(21, 3, 'B5', 'VIP', 30.00, 'available', NULL, NULL),
(22, 3, 'B6', 'VIP', 30.00, 'available', NULL, NULL),
(23, 3, 'B7', 'VIP', 30.00, 'available', NULL, NULL),
(24, 3, 'B8', 'VIP', 30.00, 'available', NULL, NULL),
(25, 3, 'B9', 'VIP', 30.00, 'available', NULL, NULL),
(26, 3, 'B10', 'VIP', 30.00, 'available', NULL, NULL),
(27, 3, 'C1', 'REGULAR', 20.00, 'available', NULL, NULL),
(28, 3, 'C2', 'REGULAR', 20.00, 'available', NULL, NULL),
(29, 3, 'C3', 'REGULAR', 20.00, 'available', NULL, NULL),
(30, 3, 'C4', 'REGULAR', 20.00, 'available', NULL, NULL),
(31, 3, 'C5', 'REGULAR', 20.00, 'available', NULL, NULL),
(32, 3, 'C6', 'REGULAR', 20.00, 'available', NULL, NULL),
(33, 3, 'C7', 'REGULAR', 20.00, 'available', NULL, NULL),
(34, 3, 'C8', 'REGULAR', 20.00, 'available', NULL, NULL),
(35, 3, 'C9', 'REGULAR', 20.00, 'available', NULL, NULL),
(36, 3, 'C10', 'REGULAR', 20.00, 'available', NULL, NULL),
(37, 3, 'D1', 'REGULAR', 20.00, 'available', NULL, NULL),
(38, 3, 'D2', 'REGULAR', 20.00, 'available', NULL, NULL),
(39, 3, 'D3', 'REGULAR', 20.00, 'available', NULL, NULL),
(40, 3, 'D4', 'REGULAR', 20.00, 'available', NULL, NULL),
(41, 3, 'D5', 'REGULAR', 20.00, 'available', NULL, NULL),
(42, 3, 'D6', 'REGULAR', 20.00, 'available', NULL, NULL),
(43, 3, 'D7', 'REGULAR', 20.00, 'available', NULL, NULL),
(44, 3, 'D8', 'REGULAR', 20.00, 'available', NULL, NULL),
(45, 3, 'D9', 'REGULAR', 20.00, 'available', NULL, NULL),
(46, 3, 'D10', 'REGULAR', 20.00, 'available', NULL, NULL),
(47, 3, 'E1', 'REGULAR', 20.00, 'available', NULL, NULL),
(48, 3, 'E2', 'REGULAR', 20.00, 'available', NULL, NULL),
(49, 3, 'E3', 'REGULAR', 20.00, 'available', NULL, NULL),
(50, 3, 'E4', 'REGULAR', 20.00, 'available', NULL, NULL),
(51, 3, 'E5', 'REGULAR', 20.00, 'available', NULL, NULL),
(52, 3, 'E6', 'REGULAR', 20.00, 'available', NULL, NULL),
(53, 3, 'E7', 'REGULAR', 20.00, 'available', NULL, NULL),
(54, 3, 'E8', 'REGULAR', 20.00, 'available', NULL, NULL),
(55, 3, 'E9', 'REGULAR', 20.00, 'available', NULL, NULL),
(56, 3, 'E10', 'REGULAR', 20.00, 'available', NULL, NULL),
(57, 3, 'F1', 'REGULAR', 20.00, 'available', NULL, NULL),
(58, 3, 'F2', 'REGULAR', 20.00, 'available', NULL, NULL),
(59, 3, 'F3', 'REGULAR', 20.00, 'available', NULL, NULL),
(60, 3, 'F4', 'REGULAR', 20.00, 'available', NULL, NULL),
(61, 3, 'F5', 'REGULAR', 20.00, 'available', NULL, NULL),
(62, 3, 'F6', 'REGULAR', 20.00, 'available', NULL, NULL),
(63, 3, 'F7', 'REGULAR', 20.00, 'available', NULL, NULL),
(64, 3, 'F8', 'REGULAR', 20.00, 'available', NULL, NULL),
(65, 3, 'F9', 'REGULAR', 20.00, 'available', NULL, NULL),
(66, 3, 'F10', 'REGULAR', 20.00, 'available', NULL, NULL),
(67, 3, 'G1', 'REGULAR', 20.00, 'available', NULL, NULL),
(68, 3, 'G2', 'REGULAR', 20.00, 'available', NULL, NULL),
(69, 3, 'G3', 'REGULAR', 20.00, 'available', NULL, NULL),
(70, 3, 'G4', 'REGULAR', 20.00, 'available', NULL, NULL),
(71, 3, 'G5', 'REGULAR', 20.00, 'available', NULL, NULL),
(72, 3, 'G6', 'REGULAR', 20.00, 'available', NULL, NULL),
(73, 3, 'G7', 'REGULAR', 20.00, 'available', NULL, NULL),
(74, 3, 'G8', 'REGULAR', 20.00, 'available', NULL, NULL),
(75, 3, 'G9', 'REGULAR', 20.00, 'available', NULL, NULL),
(76, 3, 'G10', 'REGULAR', 20.00, 'available', NULL, NULL),
(77, 3, 'H1', 'REGULAR', 20.00, 'available', NULL, NULL),
(78, 3, 'H2', 'REGULAR', 20.00, 'available', NULL, NULL),
(79, 3, 'H3', 'REGULAR', 20.00, 'available', NULL, NULL),
(80, 3, 'H4', 'REGULAR', 20.00, 'available', NULL, NULL),
(81, 3, 'H5', 'REGULAR', 20.00, 'available', NULL, NULL),
(82, 3, 'H6', 'REGULAR', 20.00, 'available', NULL, NULL),
(83, 3, 'H7', 'REGULAR', 20.00, 'available', NULL, NULL),
(84, 3, 'H8', 'REGULAR', 20.00, 'available', NULL, NULL),
(85, 3, 'H9', 'REGULAR', 20.00, 'available', NULL, NULL),
(86, 3, 'H10', 'REGULAR', 20.00, 'available', NULL, NULL),
(87, 3, 'I1', 'REGULAR', 20.00, 'available', NULL, NULL),
(88, 3, 'I2', 'REGULAR', 20.00, 'available', NULL, NULL),
(89, 3, 'I3', 'REGULAR', 20.00, 'available', NULL, NULL),
(90, 3, 'I4', 'REGULAR', 20.00, 'available', NULL, NULL),
(91, 3, 'I5', 'REGULAR', 20.00, 'available', NULL, NULL),
(92, 3, 'I6', 'REGULAR', 20.00, 'available', NULL, NULL),
(93, 3, 'I7', 'REGULAR', 20.00, 'available', NULL, NULL),
(94, 3, 'I8', 'REGULAR', 20.00, 'available', NULL, NULL),
(95, 3, 'I9', 'REGULAR', 20.00, 'available', NULL, NULL),
(96, 3, 'I10', 'REGULAR', 20.00, 'available', NULL, NULL),
(97, 3, 'J1', 'REGULAR', 20.00, 'available', NULL, NULL),
(98, 3, 'J2', 'REGULAR', 20.00, 'available', NULL, NULL),
(99, 3, 'J3', 'REGULAR', 20.00, 'available', NULL, NULL),
(100, 3, 'J4', 'REGULAR', 20.00, 'available', NULL, NULL),
(101, 3, 'J5', 'REGULAR', 20.00, 'available', NULL, NULL),
(102, 3, 'J6', 'REGULAR', 20.00, 'available', NULL, NULL),
(103, 3, 'J7', 'REGULAR', 20.00, 'available', NULL, NULL),
(104, 3, 'J8', 'REGULAR', 20.00, 'available', NULL, NULL),
(105, 3, 'J9', 'REGULAR', 20.00, 'available', NULL, NULL),
(106, 3, 'J10', 'REGULAR', 20.00, 'available', NULL, NULL),
(107, 2, 'A1', 'VIP', 127.50, 'available', NULL, NULL),
(108, 2, 'A2', 'VIP', 127.50, 'available', NULL, NULL),
(109, 2, 'A3', 'VIP', 127.50, 'available', NULL, NULL),
(110, 2, 'A4', 'VIP', 127.50, 'available', NULL, NULL),
(111, 2, 'A5', 'VIP', 127.50, 'available', NULL, NULL),
(112, 2, 'A6', 'VIP', 127.50, 'available', NULL, NULL),
(113, 2, 'A7', 'VIP', 127.50, 'available', NULL, NULL),
(114, 2, 'A8', 'VIP', 127.50, 'available', NULL, NULL),
(115, 2, 'A9', 'VIP', 127.50, 'available', NULL, NULL),
(116, 2, 'A10', 'VIP', 127.50, 'available', NULL, NULL),
(117, 2, 'B1', 'VIP', 127.50, 'available', NULL, NULL),
(118, 2, 'B2', 'VIP', 127.50, 'available', NULL, NULL),
(119, 2, 'B3', 'VIP', 127.50, 'booked', NULL, NULL),
(120, 2, 'B4', 'VIP', 127.50, 'available', NULL, NULL),
(121, 2, 'B5', 'VIP', 127.50, 'available', NULL, NULL),
(122, 2, 'B6', 'VIP', 127.50, 'available', NULL, NULL),
(123, 2, 'B7', 'VIP', 127.50, 'available', NULL, NULL),
(124, 2, 'B8', 'VIP', 127.50, 'available', NULL, NULL),
(125, 2, 'B9', 'VIP', 127.50, 'available', NULL, NULL),
(126, 2, 'B10', 'VIP', 127.50, 'available', NULL, NULL),
(127, 2, 'C1', 'REGULAR', 85.00, 'available', NULL, NULL),
(128, 2, 'C2', 'REGULAR', 85.00, 'available', NULL, NULL),
(129, 2, 'C3', 'REGULAR', 85.00, 'available', NULL, NULL),
(130, 2, 'C4', 'REGULAR', 85.00, 'available', NULL, NULL),
(131, 2, 'C5', 'REGULAR', 85.00, 'available', NULL, NULL),
(132, 2, 'C6', 'REGULAR', 85.00, 'available', NULL, NULL),
(133, 2, 'C7', 'REGULAR', 85.00, 'available', NULL, NULL),
(134, 2, 'C8', 'REGULAR', 85.00, 'available', NULL, NULL),
(135, 2, 'C9', 'REGULAR', 85.00, 'available', NULL, NULL),
(136, 2, 'C10', 'REGULAR', 85.00, 'available', NULL, NULL),
(137, 2, 'D1', 'REGULAR', 85.00, 'available', NULL, NULL),
(138, 2, 'D2', 'REGULAR', 85.00, 'available', NULL, NULL),
(139, 2, 'D3', 'REGULAR', 85.00, 'available', NULL, NULL),
(140, 2, 'D4', 'REGULAR', 85.00, 'available', NULL, NULL),
(141, 2, 'D5', 'REGULAR', 85.00, 'available', NULL, NULL),
(142, 2, 'D6', 'REGULAR', 85.00, 'available', NULL, NULL),
(143, 2, 'D7', 'REGULAR', 85.00, 'available', NULL, NULL),
(144, 2, 'D8', 'REGULAR', 85.00, 'available', NULL, NULL),
(145, 2, 'D9', 'REGULAR', 85.00, 'available', NULL, NULL),
(146, 2, 'D10', 'REGULAR', 85.00, 'available', NULL, NULL),
(147, 2, 'E1', 'REGULAR', 85.00, 'available', NULL, NULL),
(148, 2, 'E2', 'REGULAR', 85.00, 'available', NULL, NULL),
(149, 2, 'E3', 'REGULAR', 85.00, 'available', NULL, NULL),
(150, 2, 'E4', 'REGULAR', 85.00, 'available', NULL, NULL),
(151, 2, 'E5', 'REGULAR', 85.00, 'booked', NULL, NULL),
(152, 2, 'E6', 'REGULAR', 85.00, 'available', NULL, NULL),
(153, 2, 'E7', 'REGULAR', 85.00, 'available', NULL, NULL),
(154, 2, 'E8', 'REGULAR', 85.00, 'available', NULL, NULL),
(155, 2, 'E9', 'REGULAR', 85.00, 'available', NULL, NULL),
(156, 2, 'E10', 'REGULAR', 85.00, 'available', NULL, NULL),
(157, 2, 'F1', 'REGULAR', 85.00, 'available', NULL, NULL),
(158, 2, 'F2', 'REGULAR', 85.00, 'available', NULL, NULL),
(159, 2, 'F3', 'REGULAR', 85.00, 'available', NULL, NULL),
(160, 2, 'F4', 'REGULAR', 85.00, 'available', NULL, NULL),
(161, 2, 'F5', 'REGULAR', 85.00, 'available', NULL, NULL),
(162, 2, 'F6', 'REGULAR', 85.00, 'available', NULL, NULL),
(163, 2, 'F7', 'REGULAR', 85.00, 'available', NULL, NULL),
(164, 2, 'F8', 'REGULAR', 85.00, 'available', NULL, NULL),
(165, 2, 'F9', 'REGULAR', 85.00, 'available', NULL, NULL),
(166, 2, 'F10', 'REGULAR', 85.00, 'available', NULL, NULL),
(167, 2, 'G1', 'REGULAR', 85.00, 'available', NULL, NULL),
(168, 2, 'G2', 'REGULAR', 85.00, 'available', NULL, NULL),
(169, 2, 'G3', 'REGULAR', 85.00, 'available', NULL, NULL),
(170, 2, 'G4', 'REGULAR', 85.00, 'available', NULL, NULL),
(171, 2, 'G5', 'REGULAR', 85.00, 'available', NULL, NULL),
(172, 2, 'G6', 'REGULAR', 85.00, 'available', NULL, NULL),
(173, 2, 'G7', 'REGULAR', 85.00, 'available', NULL, NULL),
(174, 2, 'G8', 'REGULAR', 85.00, 'available', NULL, NULL),
(175, 2, 'G9', 'REGULAR', 85.00, 'available', NULL, NULL),
(176, 2, 'G10', 'REGULAR', 85.00, 'available', NULL, NULL),
(177, 2, 'H1', 'REGULAR', 85.00, 'available', NULL, NULL),
(178, 2, 'H2', 'REGULAR', 85.00, 'available', NULL, NULL),
(179, 2, 'H3', 'REGULAR', 85.00, 'available', NULL, NULL),
(180, 2, 'H4', 'REGULAR', 85.00, 'available', NULL, NULL),
(181, 2, 'H5', 'REGULAR', 85.00, 'available', NULL, NULL),
(182, 2, 'H6', 'REGULAR', 85.00, 'available', NULL, NULL),
(183, 2, 'H7', 'REGULAR', 85.00, 'available', NULL, NULL),
(184, 2, 'H8', 'REGULAR', 85.00, 'available', NULL, NULL),
(185, 2, 'H9', 'REGULAR', 85.00, 'available', NULL, NULL),
(186, 2, 'H10', 'REGULAR', 85.00, 'available', NULL, NULL),
(187, 2, 'I1', 'REGULAR', 85.00, 'available', NULL, NULL),
(188, 2, 'I2', 'REGULAR', 85.00, 'available', NULL, NULL),
(189, 2, 'I3', 'REGULAR', 85.00, 'available', NULL, NULL),
(190, 2, 'I4', 'REGULAR', 85.00, 'available', NULL, NULL),
(191, 2, 'I5', 'REGULAR', 85.00, 'available', NULL, NULL),
(192, 2, 'I6', 'REGULAR', 85.00, 'available', NULL, NULL),
(193, 2, 'I7', 'REGULAR', 85.00, 'available', NULL, NULL),
(194, 2, 'I8', 'REGULAR', 85.00, 'available', NULL, NULL),
(195, 2, 'I9', 'REGULAR', 85.00, 'available', NULL, NULL),
(196, 2, 'I10', 'REGULAR', 85.00, 'available', NULL, NULL),
(197, 2, 'J1', 'REGULAR', 85.00, 'available', NULL, NULL),
(198, 2, 'J2', 'REGULAR', 85.00, 'available', NULL, NULL),
(199, 2, 'J3', 'REGULAR', 85.00, 'available', NULL, NULL),
(200, 2, 'J4', 'REGULAR', 85.00, 'available', NULL, NULL),
(201, 2, 'J5', 'REGULAR', 85.00, 'available', NULL, NULL),
(202, 2, 'J6', 'REGULAR', 85.00, 'available', NULL, NULL),
(203, 2, 'J7', 'REGULAR', 85.00, 'available', NULL, NULL),
(204, 2, 'J8', 'REGULAR', 85.00, 'available', NULL, NULL),
(205, 2, 'J9', 'REGULAR', 85.00, 'available', NULL, NULL),
(206, 4, 'A1', 'VIP', 195.00, 'available', NULL, NULL),
(207, 4, 'A2', 'VIP', 195.00, 'available', NULL, NULL),
(208, 4, 'A3', 'VIP', 195.00, 'available', NULL, NULL),
(209, 4, 'A4', 'VIP', 195.00, 'available', NULL, NULL),
(210, 4, 'A5', 'VIP', 195.00, 'available', NULL, NULL),
(211, 4, 'A6', 'VIP', 195.00, 'available', NULL, NULL),
(212, 4, 'A7', 'VIP', 195.00, 'available', NULL, NULL),
(213, 4, 'A8', 'VIP', 195.00, 'available', NULL, NULL),
(214, 4, 'A9', 'VIP', 195.00, 'available', NULL, NULL),
(215, 4, 'A10', 'VIP', 195.00, 'available', NULL, NULL),
(216, 4, 'B1', 'VIP', 195.00, 'available', NULL, NULL),
(217, 4, 'B2', 'VIP', 195.00, 'available', NULL, NULL),
(218, 4, 'B3', 'VIP', 195.00, 'available', NULL, NULL),
(219, 4, 'B4', 'VIP', 195.00, 'available', NULL, NULL),
(220, 4, 'B5', 'VIP', 195.00, 'available', NULL, NULL),
(221, 4, 'B6', 'VIP', 195.00, 'available', NULL, NULL),
(222, 4, 'B7', 'VIP', 195.00, 'available', NULL, NULL),
(223, 4, 'B8', 'VIP', 195.00, 'available', NULL, NULL),
(224, 4, 'B9', 'VIP', 195.00, 'available', NULL, NULL),
(225, 4, 'B10', 'VIP', 195.00, 'available', NULL, NULL),
(226, 4, 'C1', 'VIP', 195.00, 'available', NULL, NULL),
(227, 4, 'C2', 'VIP', 195.00, 'available', NULL, NULL),
(228, 4, 'C3', 'VIP', 195.00, 'available', NULL, NULL),
(229, 4, 'C4', 'VIP', 195.00, 'available', NULL, NULL),
(230, 4, 'C5', 'VIP', 195.00, 'available', NULL, NULL),
(231, 4, 'C6', 'VIP', 195.00, 'available', NULL, NULL),
(232, 4, 'C7', 'VIP', 195.00, 'available', NULL, NULL),
(233, 4, 'C8', 'VIP', 195.00, 'available', NULL, NULL),
(234, 4, 'C9', 'VIP', 195.00, 'available', NULL, NULL),
(235, 4, 'C10', 'VIP', 195.00, 'available', NULL, NULL),
(236, 4, 'D1', 'VIP', 195.00, 'available', NULL, NULL),
(237, 4, 'D2', 'VIP', 195.00, 'available', NULL, NULL),
(238, 4, 'D3', 'VIP', 195.00, 'available', NULL, NULL),
(239, 4, 'D4', 'VIP', 195.00, 'available', NULL, NULL),
(240, 4, 'D5', 'VIP', 195.00, 'available', NULL, NULL),
(241, 4, 'D6', 'VIP', 195.00, 'available', NULL, NULL),
(242, 4, 'D7', 'VIP', 195.00, 'available', NULL, NULL),
(243, 4, 'D8', 'VIP', 195.00, 'available', NULL, NULL),
(244, 4, 'D9', 'VIP', 195.00, 'available', NULL, NULL),
(245, 4, 'D10', 'VIP', 195.00, 'available', NULL, NULL),
(246, 4, 'E1', 'VIP', 195.00, 'available', NULL, NULL),
(247, 4, 'E2', 'VIP', 195.00, 'available', NULL, NULL),
(248, 4, 'E3', 'VIP', 195.00, 'available', NULL, NULL),
(249, 4, 'E4', 'VIP', 195.00, 'available', NULL, NULL),
(250, 4, 'E5', 'VIP', 195.00, 'available', NULL, NULL),
(251, 4, 'E6', 'VIP', 195.00, 'available', NULL, NULL),
(252, 4, 'E7', 'VIP', 195.00, 'available', NULL, NULL),
(253, 4, 'E8', 'VIP', 195.00, 'available', NULL, NULL),
(254, 4, 'E9', 'VIP', 195.00, 'available', NULL, NULL),
(255, 4, 'E10', 'VIP', 195.00, 'available', NULL, NULL),
(256, 4, 'F1', 'VIP', 195.00, 'available', NULL, NULL),
(257, 4, 'F2', 'VIP', 195.00, 'available', NULL, NULL),
(258, 4, 'F3', 'VIP', 195.00, 'available', NULL, NULL),
(259, 4, 'F4', 'VIP', 195.00, 'available', NULL, NULL),
(260, 4, 'F5', 'VIP', 195.00, 'available', NULL, NULL),
(261, 4, 'F6', 'VIP', 195.00, 'available', NULL, NULL),
(262, 4, 'F7', 'VIP', 195.00, 'available', NULL, NULL),
(263, 4, 'F8', 'VIP', 195.00, 'available', NULL, NULL),
(264, 4, 'F9', 'VIP', 195.00, 'available', NULL, NULL),
(265, 4, 'F10', 'VIP', 195.00, 'available', NULL, NULL),
(266, 4, 'G1', 'REGULAR', 130.00, 'available', NULL, NULL),
(267, 4, 'G2', 'REGULAR', 130.00, 'available', NULL, NULL),
(268, 4, 'G3', 'REGULAR', 130.00, 'available', NULL, NULL),
(269, 4, 'G4', 'REGULAR', 130.00, 'available', NULL, NULL),
(270, 4, 'G5', 'REGULAR', 130.00, 'available', NULL, NULL),
(271, 4, 'G6', 'REGULAR', 130.00, 'available', NULL, NULL),
(272, 4, 'G7', 'REGULAR', 130.00, 'available', NULL, NULL),
(273, 4, 'G8', 'REGULAR', 130.00, 'available', NULL, NULL),
(274, 4, 'G9', 'REGULAR', 130.00, 'available', NULL, NULL),
(275, 4, 'G10', 'REGULAR', 130.00, 'available', NULL, NULL),
(276, 4, 'H1', 'REGULAR', 130.00, 'available', NULL, NULL),
(277, 4, 'H2', 'REGULAR', 130.00, 'available', NULL, NULL),
(278, 4, 'H3', 'REGULAR', 130.00, 'available', NULL, NULL),
(279, 4, 'H4', 'REGULAR', 130.00, 'available', NULL, NULL),
(280, 4, 'H5', 'REGULAR', 130.00, 'available', NULL, NULL),
(281, 4, 'H6', 'REGULAR', 130.00, 'available', NULL, NULL),
(282, 4, 'H7', 'REGULAR', 130.00, 'available', NULL, NULL),
(283, 4, 'H8', 'REGULAR', 130.00, 'available', NULL, NULL),
(284, 4, 'H9', 'REGULAR', 130.00, 'available', NULL, NULL),
(285, 4, 'H10', 'REGULAR', 130.00, 'available', NULL, NULL),
(286, 4, 'I1', 'REGULAR', 130.00, 'available', NULL, NULL),
(287, 4, 'I2', 'REGULAR', 130.00, 'available', NULL, NULL),
(288, 4, 'I3', 'REGULAR', 130.00, 'available', NULL, NULL),
(289, 4, 'I4', 'REGULAR', 130.00, 'available', NULL, NULL),
(290, 4, 'I5', 'REGULAR', 130.00, 'available', NULL, NULL),
(291, 4, 'I6', 'REGULAR', 130.00, 'available', NULL, NULL),
(292, 4, 'I7', 'REGULAR', 130.00, 'available', NULL, NULL),
(293, 4, 'I8', 'REGULAR', 130.00, 'available', NULL, NULL),
(294, 4, 'I9', 'REGULAR', 130.00, 'available', NULL, NULL),
(295, 4, 'I10', 'REGULAR', 130.00, 'available', NULL, NULL),
(296, 4, 'J1', 'REGULAR', 130.00, 'available', NULL, NULL),
(297, 4, 'J2', 'REGULAR', 130.00, 'available', NULL, NULL),
(298, 4, 'J3', 'REGULAR', 130.00, 'available', NULL, NULL),
(299, 4, 'J4', 'REGULAR', 130.00, 'available', NULL, NULL),
(300, 4, 'J5', 'REGULAR', 130.00, 'available', NULL, NULL),
(301, 4, 'J6', 'REGULAR', 130.00, 'available', NULL, NULL),
(302, 4, 'J7', 'REGULAR', 130.00, 'available', NULL, NULL),
(303, 4, 'J8', 'REGULAR', 130.00, 'available', NULL, NULL),
(304, 4, 'J9', 'REGULAR', 130.00, 'available', NULL, NULL),
(305, 4, 'J10', 'REGULAR', 130.00, 'available', NULL, NULL),
(306, 4, 'K1', 'REGULAR', 130.00, 'available', NULL, NULL),
(307, 4, 'K2', 'REGULAR', 130.00, 'available', NULL, NULL),
(308, 4, 'K3', 'REGULAR', 130.00, 'available', NULL, NULL),
(309, 4, 'K4', 'REGULAR', 130.00, 'available', NULL, NULL),
(310, 4, 'K5', 'REGULAR', 130.00, 'available', NULL, NULL),
(311, 4, 'K6', 'REGULAR', 130.00, 'available', NULL, NULL),
(312, 4, 'K7', 'REGULAR', 130.00, 'available', NULL, NULL),
(313, 4, 'K8', 'REGULAR', 130.00, 'available', NULL, NULL),
(314, 4, 'K9', 'REGULAR', 130.00, 'available', NULL, NULL),
(315, 4, 'K10', 'REGULAR', 130.00, 'available', NULL, NULL),
(316, 4, 'L1', 'REGULAR', 130.00, 'available', NULL, NULL),
(317, 4, 'L2', 'REGULAR', 130.00, 'available', NULL, NULL),
(318, 4, 'L3', 'REGULAR', 130.00, 'available', NULL, NULL),
(319, 4, 'L4', 'REGULAR', 130.00, 'available', NULL, NULL),
(320, 4, 'L5', 'REGULAR', 130.00, 'available', NULL, NULL),
(321, 4, 'L6', 'REGULAR', 130.00, 'available', NULL, NULL),
(322, 4, 'L7', 'REGULAR', 130.00, 'available', NULL, NULL),
(323, 4, 'L8', 'REGULAR', 130.00, 'available', NULL, NULL),
(324, 4, 'L9', 'REGULAR', 130.00, 'available', NULL, NULL),
(325, 4, 'L10', 'REGULAR', 130.00, 'available', NULL, NULL),
(326, 4, 'M1', 'REGULAR', 130.00, 'available', NULL, NULL),
(327, 4, 'M2', 'REGULAR', 130.00, 'available', NULL, NULL),
(328, 4, 'M3', 'REGULAR', 130.00, 'available', NULL, NULL),
(329, 4, 'M4', 'REGULAR', 130.00, 'available', NULL, NULL),
(330, 4, 'M5', 'REGULAR', 130.00, 'available', NULL, NULL),
(331, 4, 'M6', 'REGULAR', 130.00, 'available', NULL, NULL),
(332, 4, 'M7', 'REGULAR', 130.00, 'available', NULL, NULL),
(333, 4, 'M8', 'REGULAR', 130.00, 'available', NULL, NULL),
(334, 4, 'M9', 'REGULAR', 130.00, 'available', NULL, NULL),
(335, 4, 'M10', 'REGULAR', 130.00, 'available', NULL, NULL),
(336, 4, 'N1', 'REGULAR', 130.00, 'available', NULL, NULL),
(337, 4, 'N2', 'REGULAR', 130.00, 'available', NULL, NULL),
(338, 4, 'N3', 'REGULAR', 130.00, 'available', NULL, NULL),
(339, 4, 'N4', 'REGULAR', 130.00, 'available', NULL, NULL),
(340, 4, 'N5', 'REGULAR', 130.00, 'available', NULL, NULL),
(341, 4, 'N6', 'REGULAR', 130.00, 'available', NULL, NULL),
(342, 4, 'N7', 'REGULAR', 130.00, 'available', NULL, NULL),
(343, 4, 'N8', 'REGULAR', 130.00, 'available', NULL, NULL),
(344, 4, 'N9', 'REGULAR', 130.00, 'available', NULL, NULL),
(345, 4, 'N10', 'REGULAR', 130.00, 'available', NULL, NULL),
(346, 4, 'O1', 'REGULAR', 130.00, 'available', NULL, NULL),
(347, 4, 'O2', 'REGULAR', 130.00, 'available', NULL, NULL),
(348, 4, 'O3', 'REGULAR', 130.00, 'available', NULL, NULL),
(349, 4, 'O4', 'REGULAR', 130.00, 'available', NULL, NULL),
(350, 4, 'O5', 'REGULAR', 130.00, 'available', NULL, NULL),
(351, 4, 'O6', 'REGULAR', 130.00, 'available', NULL, NULL),
(352, 4, 'O7', 'REGULAR', 130.00, 'available', NULL, NULL),
(353, 4, 'O8', 'REGULAR', 130.00, 'available', NULL, NULL),
(354, 4, 'O9', 'REGULAR', 130.00, 'available', NULL, NULL),
(355, 4, 'O10', 'REGULAR', 130.00, 'available', NULL, NULL),
(356, 4, 'P1', 'REGULAR', 130.00, 'available', NULL, NULL),
(357, 4, 'P2', 'REGULAR', 130.00, 'available', NULL, NULL),
(358, 4, 'P3', 'REGULAR', 130.00, 'available', NULL, NULL),
(359, 4, 'P4', 'REGULAR', 130.00, 'available', NULL, NULL),
(360, 4, 'P5', 'REGULAR', 130.00, 'available', NULL, NULL),
(361, 4, 'P6', 'REGULAR', 130.00, 'available', NULL, NULL),
(362, 4, 'P7', 'REGULAR', 130.00, 'available', NULL, NULL),
(363, 4, 'P8', 'REGULAR', 130.00, 'available', NULL, NULL),
(364, 4, 'P9', 'REGULAR', 130.00, 'available', NULL, NULL),
(365, 4, 'P10', 'REGULAR', 130.00, 'available', NULL, NULL),
(366, 4, 'Q1', 'REGULAR', 130.00, 'available', NULL, NULL),
(367, 4, 'Q2', 'REGULAR', 130.00, 'available', NULL, NULL),
(368, 4, 'Q3', 'REGULAR', 130.00, 'available', NULL, NULL),
(369, 4, 'Q4', 'REGULAR', 130.00, 'available', NULL, NULL),
(370, 4, 'Q5', 'REGULAR', 130.00, 'available', NULL, NULL),
(371, 4, 'Q6', 'REGULAR', 130.00, 'available', NULL, NULL),
(372, 4, 'Q7', 'REGULAR', 130.00, 'available', NULL, NULL),
(373, 4, 'Q8', 'REGULAR', 130.00, 'available', NULL, NULL),
(374, 4, 'Q9', 'REGULAR', 130.00, 'available', NULL, NULL),
(375, 4, 'Q10', 'REGULAR', 130.00, 'available', NULL, NULL),
(376, 4, 'R1', 'REGULAR', 130.00, 'available', NULL, NULL),
(377, 4, 'R2', 'REGULAR', 130.00, 'available', NULL, NULL),
(378, 4, 'R3', 'REGULAR', 130.00, 'available', NULL, NULL),
(379, 4, 'R4', 'REGULAR', 130.00, 'available', NULL, NULL),
(380, 4, 'R5', 'REGULAR', 130.00, 'available', NULL, NULL),
(381, 4, 'R6', 'REGULAR', 130.00, 'available', NULL, NULL),
(382, 4, 'R7', 'REGULAR', 130.00, 'available', NULL, NULL),
(383, 4, 'R8', 'REGULAR', 130.00, 'available', NULL, NULL),
(384, 4, 'R9', 'REGULAR', 130.00, 'available', NULL, NULL),
(385, 4, 'R10', 'REGULAR', 130.00, 'available', NULL, NULL),
(386, 4, 'S1', 'REGULAR', 130.00, 'available', NULL, NULL),
(387, 4, 'S2', 'REGULAR', 130.00, 'available', NULL, NULL),
(388, 4, 'S3', 'REGULAR', 130.00, 'available', NULL, NULL),
(389, 4, 'S4', 'REGULAR', 130.00, 'available', NULL, NULL),
(390, 4, 'S5', 'REGULAR', 130.00, 'available', NULL, NULL),
(391, 4, 'S6', 'REGULAR', 130.00, 'available', NULL, NULL),
(392, 4, 'S7', 'REGULAR', 130.00, 'available', NULL, NULL),
(393, 4, 'S8', 'REGULAR', 130.00, 'available', NULL, NULL),
(394, 4, 'S9', 'REGULAR', 130.00, 'available', NULL, NULL),
(395, 4, 'S10', 'REGULAR', 130.00, 'available', NULL, NULL),
(396, 4, 'T1', 'REGULAR', 130.00, 'available', NULL, NULL),
(397, 4, 'T2', 'REGULAR', 130.00, 'available', NULL, NULL),
(398, 4, 'T3', 'REGULAR', 130.00, 'available', NULL, NULL),
(399, 4, 'T4', 'REGULAR', 130.00, 'available', NULL, NULL),
(400, 4, 'T5', 'REGULAR', 130.00, 'available', NULL, NULL),
(401, 4, 'T6', 'REGULAR', 130.00, 'available', NULL, NULL),
(402, 4, 'T7', 'REGULAR', 130.00, 'available', NULL, NULL),
(403, 4, 'T8', 'REGULAR', 130.00, 'available', NULL, NULL),
(404, 4, 'T9', 'REGULAR', 130.00, 'available', NULL, NULL),
(405, 4, 'T10', 'REGULAR', 130.00, 'available', NULL, NULL),
(406, 4, 'U1', 'REGULAR', 130.00, 'available', NULL, NULL),
(407, 4, 'U2', 'REGULAR', 130.00, 'available', NULL, NULL),
(408, 4, 'U3', 'REGULAR', 130.00, 'available', NULL, NULL),
(409, 4, 'U4', 'REGULAR', 130.00, 'available', NULL, NULL),
(410, 4, 'U5', 'REGULAR', 130.00, 'available', NULL, NULL),
(411, 4, 'U6', 'REGULAR', 130.00, 'available', NULL, NULL),
(412, 4, 'U7', 'REGULAR', 130.00, 'available', NULL, NULL),
(413, 4, 'U8', 'REGULAR', 130.00, 'available', NULL, NULL),
(414, 4, 'U9', 'REGULAR', 130.00, 'available', NULL, NULL),
(415, 4, 'U10', 'REGULAR', 130.00, 'available', NULL, NULL),
(416, 4, 'V1', 'REGULAR', 130.00, 'available', NULL, NULL),
(417, 4, 'V2', 'REGULAR', 130.00, 'available', NULL, NULL),
(418, 4, 'V3', 'REGULAR', 130.00, 'available', NULL, NULL),
(419, 4, 'V4', 'REGULAR', 130.00, 'available', NULL, NULL),
(420, 4, 'V5', 'REGULAR', 130.00, 'available', NULL, NULL),
(421, 4, 'V6', 'REGULAR', 130.00, 'available', NULL, NULL),
(422, 4, 'V7', 'REGULAR', 130.00, 'available', NULL, NULL),
(423, 4, 'V8', 'REGULAR', 130.00, 'available', NULL, NULL),
(424, 4, 'V9', 'REGULAR', 130.00, 'available', NULL, NULL),
(425, 4, 'V10', 'REGULAR', 130.00, 'available', NULL, NULL),
(426, 4, 'W1', 'REGULAR', 130.00, 'available', NULL, NULL),
(427, 4, 'W2', 'REGULAR', 130.00, 'available', NULL, NULL),
(428, 4, 'W3', 'REGULAR', 130.00, 'available', NULL, NULL),
(429, 4, 'W4', 'REGULAR', 130.00, 'available', NULL, NULL),
(430, 4, 'W5', 'REGULAR', 130.00, 'available', NULL, NULL),
(431, 4, 'W6', 'REGULAR', 130.00, 'available', NULL, NULL),
(432, 4, 'W7', 'REGULAR', 130.00, 'available', NULL, NULL),
(433, 4, 'W8', 'REGULAR', 130.00, 'available', NULL, NULL),
(434, 4, 'W9', 'REGULAR', 130.00, 'available', NULL, NULL),
(435, 4, 'W10', 'REGULAR', 130.00, 'available', NULL, NULL),
(436, 4, 'X1', 'REGULAR', 130.00, 'available', NULL, NULL),
(437, 4, 'X2', 'REGULAR', 130.00, 'available', NULL, NULL),
(438, 4, 'X3', 'REGULAR', 130.00, 'available', NULL, NULL),
(439, 4, 'X4', 'REGULAR', 130.00, 'available', NULL, NULL),
(440, 4, 'X5', 'REGULAR', 130.00, 'available', NULL, NULL),
(441, 4, 'X6', 'REGULAR', 130.00, 'available', NULL, NULL),
(442, 4, 'X7', 'REGULAR', 130.00, 'available', NULL, NULL),
(443, 4, 'X8', 'REGULAR', 130.00, 'available', NULL, NULL),
(444, 4, 'X9', 'REGULAR', 130.00, 'available', NULL, NULL),
(445, 4, 'X10', 'REGULAR', 130.00, 'available', NULL, NULL),
(446, 4, 'Y1', 'REGULAR', 130.00, 'available', NULL, NULL),
(447, 4, 'Y2', 'REGULAR', 130.00, 'available', NULL, NULL),
(448, 4, 'Y3', 'REGULAR', 130.00, 'available', NULL, NULL),
(449, 4, 'Y4', 'REGULAR', 130.00, 'available', NULL, NULL),
(450, 4, 'Y5', 'REGULAR', 130.00, 'available', NULL, NULL),
(451, 4, 'Y6', 'REGULAR', 130.00, 'available', NULL, NULL),
(452, 4, 'Y7', 'REGULAR', 130.00, 'available', NULL, NULL),
(453, 4, 'Y8', 'REGULAR', 130.00, 'available', NULL, NULL),
(454, 4, 'Y9', 'REGULAR', 130.00, 'available', NULL, NULL),
(455, 4, 'Y10', 'REGULAR', 130.00, 'available', NULL, NULL),
(456, 4, 'Z1', 'REGULAR', 130.00, 'available', NULL, NULL),
(457, 4, 'Z2', 'REGULAR', 130.00, 'available', NULL, NULL),
(458, 4, 'Z3', 'REGULAR', 130.00, 'available', NULL, NULL),
(459, 4, 'Z4', 'REGULAR', 130.00, 'available', NULL, NULL),
(460, 4, 'Z5', 'REGULAR', 130.00, 'available', NULL, NULL),
(461, 4, 'Z6', 'REGULAR', 130.00, 'available', NULL, NULL),
(462, 4, 'Z7', 'REGULAR', 130.00, 'available', NULL, NULL),
(463, 4, 'Z8', 'REGULAR', 130.00, 'available', NULL, NULL),
(464, 4, 'Z9', 'REGULAR', 130.00, 'available', NULL, NULL),
(465, 4, 'Z10', 'REGULAR', 130.00, 'available', NULL, NULL),
(466, 4, '[1', 'REGULAR', 130.00, 'available', NULL, NULL),
(467, 4, '[2', 'REGULAR', 130.00, 'available', NULL, NULL),
(468, 4, '[3', 'REGULAR', 130.00, 'available', NULL, NULL),
(469, 4, '[4', 'REGULAR', 130.00, 'available', NULL, NULL),
(470, 4, '[5', 'REGULAR', 130.00, 'available', NULL, NULL),
(471, 4, '[6', 'REGULAR', 130.00, 'available', NULL, NULL),
(472, 4, '[7', 'REGULAR', 130.00, 'available', NULL, NULL),
(473, 4, '[8', 'REGULAR', 130.00, 'available', NULL, NULL),
(474, 4, '[9', 'REGULAR', 130.00, 'available', NULL, NULL),
(475, 4, '[10', 'REGULAR', 130.00, 'available', NULL, NULL),
(476, 4, '1', 'REGULAR', 130.00, 'available', NULL, NULL),
(477, 4, '2', 'REGULAR', 130.00, 'available', NULL, NULL),
(478, 4, '3', 'REGULAR', 130.00, 'available', NULL, NULL),
(479, 4, '4', 'REGULAR', 130.00, 'available', NULL, NULL),
(480, 4, '5', 'REGULAR', 130.00, 'available', NULL, NULL),
(481, 4, '6', 'REGULAR', 130.00, 'available', NULL, NULL),
(482, 4, '7', 'REGULAR', 130.00, 'available', NULL, NULL),
(483, 4, '8', 'REGULAR', 130.00, 'available', NULL, NULL),
(484, 4, '9', 'REGULAR', 130.00, 'available', NULL, NULL),
(485, 4, '10', 'REGULAR', 130.00, 'available', NULL, NULL),
(486, 4, ']1', 'REGULAR', 130.00, 'available', NULL, NULL),
(487, 4, ']2', 'REGULAR', 130.00, 'available', NULL, NULL),
(488, 4, ']3', 'REGULAR', 130.00, 'available', NULL, NULL),
(489, 4, ']4', 'REGULAR', 130.00, 'available', NULL, NULL),
(490, 4, ']5', 'REGULAR', 130.00, 'available', NULL, NULL),
(491, 4, ']6', 'REGULAR', 130.00, 'available', NULL, NULL),
(492, 4, ']7', 'REGULAR', 130.00, 'available', NULL, NULL),
(493, 4, ']8', 'REGULAR', 130.00, 'available', NULL, NULL),
(494, 4, ']9', 'REGULAR', 130.00, 'available', NULL, NULL),
(495, 4, ']10', 'REGULAR', 130.00, 'available', NULL, NULL),
(496, 4, '^1', 'REGULAR', 130.00, 'available', NULL, NULL),
(497, 4, '^2', 'REGULAR', 130.00, 'available', NULL, NULL),
(498, 4, '^3', 'REGULAR', 130.00, 'available', NULL, NULL),
(499, 4, '^4', 'REGULAR', 130.00, 'available', NULL, NULL),
(500, 4, '^5', 'REGULAR', 130.00, 'available', NULL, NULL),
(501, 4, '^6', 'REGULAR', 130.00, 'available', NULL, NULL),
(502, 4, '^7', 'REGULAR', 130.00, 'available', NULL, NULL),
(503, 4, '^8', 'REGULAR', 130.00, 'available', NULL, NULL),
(504, 4, '^9', 'REGULAR', 130.00, 'available', NULL, NULL),
(505, 4, '^10', 'REGULAR', 130.00, 'available', NULL, NULL),
(506, 5, 'A1', 'VIP', 282.00, 'available', NULL, NULL),
(507, 5, 'A2', 'VIP', 282.00, 'available', NULL, NULL),
(508, 5, 'A3', 'VIP', 282.00, 'available', NULL, NULL),
(509, 5, 'A4', 'VIP', 282.00, 'available', NULL, NULL),
(510, 5, 'A5', 'VIP', 282.00, 'available', NULL, NULL),
(511, 5, 'A6', 'VIP', 282.00, 'available', NULL, NULL),
(512, 5, 'A7', 'VIP', 282.00, 'available', NULL, NULL),
(513, 5, 'A8', 'VIP', 282.00, 'available', NULL, NULL),
(514, 5, 'A9', 'VIP', 282.00, 'available', NULL, NULL),
(515, 5, 'A10', 'VIP', 282.00, 'available', NULL, NULL),
(516, 5, 'B1', 'VIP', 282.00, 'available', NULL, NULL),
(517, 5, 'B2', 'VIP', 282.00, 'available', NULL, NULL),
(518, 5, 'B3', 'VIP', 282.00, 'available', NULL, NULL),
(519, 5, 'B4', 'VIP', 282.00, 'available', NULL, NULL),
(520, 5, 'B5', 'VIP', 282.00, 'available', NULL, NULL),
(521, 5, 'B6', 'VIP', 282.00, 'available', NULL, NULL),
(522, 5, 'B7', 'VIP', 282.00, 'available', NULL, NULL),
(523, 5, 'B8', 'VIP', 282.00, 'available', NULL, NULL),
(524, 5, 'B9', 'VIP', 282.00, 'available', NULL, NULL),
(525, 5, 'B10', 'VIP', 282.00, 'available', NULL, NULL),
(526, 5, 'C1', 'VIP', 282.00, 'available', NULL, NULL),
(527, 5, 'C2', 'VIP', 282.00, 'available', NULL, NULL),
(528, 5, 'C3', 'VIP', 282.00, 'available', NULL, NULL),
(529, 5, 'C4', 'VIP', 282.00, 'available', NULL, NULL),
(530, 5, 'C5', 'VIP', 282.00, 'available', NULL, NULL),
(531, 5, 'C6', 'VIP', 282.00, 'available', NULL, NULL),
(532, 5, 'C7', 'VIP', 282.00, 'available', NULL, NULL),
(533, 5, 'C8', 'VIP', 282.00, 'available', NULL, NULL),
(534, 5, 'C9', 'VIP', 282.00, 'available', NULL, NULL),
(535, 5, 'C10', 'VIP', 282.00, 'available', NULL, NULL),
(536, 5, 'D1', 'VIP', 282.00, 'available', NULL, NULL),
(537, 5, 'D2', 'VIP', 282.00, 'available', NULL, NULL),
(538, 5, 'D3', 'VIP', 282.00, 'available', NULL, NULL),
(539, 5, 'D4', 'VIP', 282.00, 'available', NULL, NULL),
(540, 5, 'D5', 'VIP', 282.00, 'available', NULL, NULL),
(541, 5, 'D6', 'VIP', 282.00, 'available', NULL, NULL),
(542, 5, 'D7', 'VIP', 282.00, 'available', NULL, NULL),
(543, 5, 'D8', 'VIP', 282.00, 'available', NULL, NULL),
(544, 5, 'D9', 'VIP', 282.00, 'available', NULL, NULL),
(545, 5, 'D10', 'VIP', 282.00, 'available', NULL, NULL),
(546, 5, 'E1', 'VIP', 282.00, 'available', NULL, NULL),
(547, 5, 'E2', 'VIP', 282.00, 'available', NULL, NULL),
(548, 5, 'E3', 'VIP', 282.00, 'available', NULL, NULL),
(549, 5, 'E4', 'VIP', 282.00, 'available', NULL, NULL),
(550, 5, 'E5', 'VIP', 282.00, 'available', NULL, NULL),
(551, 5, 'E6', 'VIP', 282.00, 'available', NULL, NULL),
(552, 5, 'E7', 'VIP', 282.00, 'available', NULL, NULL),
(553, 5, 'E8', 'VIP', 282.00, 'available', NULL, NULL),
(554, 5, 'E9', 'VIP', 282.00, 'available', NULL, NULL),
(555, 5, 'E10', 'VIP', 282.00, 'available', NULL, NULL),
(556, 5, 'F1', 'VIP', 282.00, 'available', NULL, NULL),
(557, 5, 'F2', 'VIP', 282.00, 'available', NULL, NULL),
(558, 5, 'F3', 'VIP', 282.00, 'available', NULL, NULL),
(559, 5, 'F4', 'VIP', 282.00, 'available', NULL, NULL),
(560, 5, 'F5', 'VIP', 282.00, 'available', NULL, NULL),
(561, 5, 'F6', 'VIP', 282.00, 'available', NULL, NULL),
(562, 5, 'F7', 'VIP', 282.00, 'available', NULL, NULL),
(563, 5, 'F8', 'VIP', 282.00, 'available', NULL, NULL),
(564, 5, 'F9', 'VIP', 282.00, 'available', NULL, NULL),
(565, 5, 'F10', 'VIP', 282.00, 'available', NULL, NULL),
(566, 5, 'G1', 'VIP', 282.00, 'available', NULL, NULL),
(567, 5, 'G2', 'VIP', 282.00, 'available', NULL, NULL),
(568, 5, 'G3', 'VIP', 282.00, 'available', NULL, NULL),
(569, 5, 'G4', 'VIP', 282.00, 'available', NULL, NULL),
(570, 5, 'G5', 'VIP', 282.00, 'available', NULL, NULL),
(571, 5, 'G6', 'VIP', 282.00, 'available', NULL, NULL),
(572, 5, 'G7', 'VIP', 282.00, 'available', NULL, NULL),
(573, 5, 'G8', 'VIP', 282.00, 'available', NULL, NULL),
(574, 5, 'G9', 'VIP', 282.00, 'available', NULL, NULL),
(575, 5, 'G10', 'VIP', 282.00, 'available', NULL, NULL),
(576, 5, 'H1', 'VIP', 282.00, 'available', NULL, NULL),
(577, 5, 'H2', 'VIP', 282.00, 'available', NULL, NULL),
(578, 5, 'H3', 'VIP', 282.00, 'available', NULL, NULL),
(579, 5, 'H4', 'VIP', 282.00, 'available', NULL, NULL),
(580, 5, 'H5', 'VIP', 282.00, 'available', NULL, NULL),
(581, 5, 'H6', 'VIP', 282.00, 'available', NULL, NULL),
(582, 5, 'H7', 'VIP', 282.00, 'available', NULL, NULL),
(583, 5, 'H8', 'VIP', 282.00, 'available', NULL, NULL),
(584, 5, 'H9', 'VIP', 282.00, 'available', NULL, NULL),
(585, 5, 'H10', 'VIP', 282.00, 'available', NULL, NULL),
(586, 5, 'I1', 'REGULAR', 188.00, 'available', NULL, NULL),
(587, 5, 'I2', 'REGULAR', 188.00, 'available', NULL, NULL),
(588, 5, 'I3', 'REGULAR', 188.00, 'available', NULL, NULL),
(589, 5, 'I4', 'REGULAR', 188.00, 'available', NULL, NULL),
(590, 5, 'I5', 'REGULAR', 188.00, 'available', NULL, NULL),
(591, 5, 'I6', 'REGULAR', 188.00, 'available', NULL, NULL),
(592, 5, 'I7', 'REGULAR', 188.00, 'available', NULL, NULL),
(593, 5, 'I8', 'REGULAR', 188.00, 'available', NULL, NULL),
(594, 5, 'I9', 'REGULAR', 188.00, 'available', NULL, NULL),
(595, 5, 'I10', 'REGULAR', 188.00, 'available', NULL, NULL),
(596, 5, 'J1', 'REGULAR', 188.00, 'available', NULL, NULL),
(597, 5, 'J2', 'REGULAR', 188.00, 'available', NULL, NULL),
(598, 5, 'J3', 'REGULAR', 188.00, 'available', NULL, NULL),
(599, 5, 'J4', 'REGULAR', 188.00, 'available', NULL, NULL),
(600, 5, 'J5', 'REGULAR', 188.00, 'available', NULL, NULL),
(601, 5, 'J6', 'REGULAR', 188.00, 'available', NULL, NULL),
(602, 5, 'J7', 'REGULAR', 188.00, 'available', NULL, NULL),
(603, 5, 'J8', 'REGULAR', 188.00, 'available', NULL, NULL),
(604, 5, 'J9', 'REGULAR', 188.00, 'available', NULL, NULL),
(605, 5, 'J10', 'REGULAR', 188.00, 'available', NULL, NULL),
(606, 5, 'K1', 'REGULAR', 188.00, 'available', NULL, NULL),
(607, 5, 'K2', 'REGULAR', 188.00, 'available', NULL, NULL),
(608, 5, 'K3', 'REGULAR', 188.00, 'available', NULL, NULL),
(609, 5, 'K4', 'REGULAR', 188.00, 'available', NULL, NULL),
(610, 5, 'K5', 'REGULAR', 188.00, 'available', NULL, NULL),
(611, 5, 'K6', 'REGULAR', 188.00, 'available', NULL, NULL),
(612, 5, 'K7', 'REGULAR', 188.00, 'available', NULL, NULL),
(613, 5, 'K8', 'REGULAR', 188.00, 'available', NULL, NULL),
(614, 5, 'K9', 'REGULAR', 188.00, 'available', NULL, NULL),
(615, 5, 'K10', 'REGULAR', 188.00, 'available', NULL, NULL),
(616, 5, 'L1', 'REGULAR', 188.00, 'available', NULL, NULL),
(617, 5, 'L2', 'REGULAR', 188.00, 'available', NULL, NULL),
(618, 5, 'L3', 'REGULAR', 188.00, 'available', NULL, NULL),
(619, 5, 'L4', 'REGULAR', 188.00, 'available', NULL, NULL),
(620, 5, 'L5', 'REGULAR', 188.00, 'available', NULL, NULL),
(621, 5, 'L6', 'REGULAR', 188.00, 'available', NULL, NULL),
(622, 5, 'L7', 'REGULAR', 188.00, 'available', NULL, NULL),
(623, 5, 'L8', 'REGULAR', 188.00, 'available', NULL, NULL),
(624, 5, 'L9', 'REGULAR', 188.00, 'available', NULL, NULL),
(625, 5, 'L10', 'REGULAR', 188.00, 'available', NULL, NULL),
(626, 5, 'M1', 'REGULAR', 188.00, 'available', NULL, NULL),
(627, 5, 'M2', 'REGULAR', 188.00, 'available', NULL, NULL),
(628, 5, 'M3', 'REGULAR', 188.00, 'available', NULL, NULL),
(629, 5, 'M4', 'REGULAR', 188.00, 'available', NULL, NULL),
(630, 5, 'M5', 'REGULAR', 188.00, 'available', NULL, NULL),
(631, 5, 'M6', 'REGULAR', 188.00, 'available', NULL, NULL),
(632, 5, 'M7', 'REGULAR', 188.00, 'available', NULL, NULL),
(633, 5, 'M8', 'REGULAR', 188.00, 'available', NULL, NULL),
(634, 5, 'M9', 'REGULAR', 188.00, 'available', NULL, NULL),
(635, 5, 'M10', 'REGULAR', 188.00, 'available', NULL, NULL),
(636, 5, 'N1', 'REGULAR', 188.00, 'available', NULL, NULL),
(637, 5, 'N2', 'REGULAR', 188.00, 'available', NULL, NULL),
(638, 5, 'N3', 'REGULAR', 188.00, 'available', NULL, NULL),
(639, 5, 'N4', 'REGULAR', 188.00, 'available', NULL, NULL),
(640, 5, 'N5', 'REGULAR', 188.00, 'available', NULL, NULL),
(641, 5, 'N6', 'REGULAR', 188.00, 'available', NULL, NULL),
(642, 5, 'N7', 'REGULAR', 188.00, 'available', NULL, NULL),
(643, 5, 'N8', 'REGULAR', 188.00, 'available', NULL, NULL),
(644, 5, 'N9', 'REGULAR', 188.00, 'available', NULL, NULL),
(645, 5, 'N10', 'REGULAR', 188.00, 'available', NULL, NULL),
(646, 5, 'O1', 'REGULAR', 188.00, 'available', NULL, NULL),
(647, 5, 'O2', 'REGULAR', 188.00, 'available', NULL, NULL),
(648, 5, 'O3', 'REGULAR', 188.00, 'available', NULL, NULL),
(649, 5, 'O4', 'REGULAR', 188.00, 'available', NULL, NULL),
(650, 5, 'O5', 'REGULAR', 188.00, 'available', NULL, NULL),
(651, 5, 'O6', 'REGULAR', 188.00, 'available', NULL, NULL),
(652, 5, 'O7', 'REGULAR', 188.00, 'available', NULL, NULL),
(653, 5, 'O8', 'REGULAR', 188.00, 'available', NULL, NULL),
(654, 5, 'O9', 'REGULAR', 188.00, 'available', NULL, NULL),
(655, 5, 'O10', 'REGULAR', 188.00, 'available', NULL, NULL),
(656, 5, 'P1', 'REGULAR', 188.00, 'available', NULL, NULL),
(657, 5, 'P2', 'REGULAR', 188.00, 'available', NULL, NULL),
(658, 5, 'P3', 'REGULAR', 188.00, 'available', NULL, NULL),
(659, 5, 'P4', 'REGULAR', 188.00, 'available', NULL, NULL),
(660, 5, 'P5', 'REGULAR', 188.00, 'available', NULL, NULL),
(661, 5, 'P6', 'REGULAR', 188.00, 'available', NULL, NULL),
(662, 5, 'P7', 'REGULAR', 188.00, 'available', NULL, NULL),
(663, 5, 'P8', 'REGULAR', 188.00, 'available', NULL, NULL),
(664, 5, 'P9', 'REGULAR', 188.00, 'available', NULL, NULL),
(665, 5, 'P10', 'REGULAR', 188.00, 'available', NULL, NULL),
(666, 5, 'Q1', 'REGULAR', 188.00, 'available', NULL, NULL),
(667, 5, 'Q2', 'REGULAR', 188.00, 'available', NULL, NULL),
(668, 5, 'Q3', 'REGULAR', 188.00, 'available', NULL, NULL),
(669, 5, 'Q4', 'REGULAR', 188.00, 'available', NULL, NULL),
(670, 5, 'Q5', 'REGULAR', 188.00, 'available', NULL, NULL),
(671, 5, 'Q6', 'REGULAR', 188.00, 'available', NULL, NULL),
(672, 5, 'Q7', 'REGULAR', 188.00, 'available', NULL, NULL),
(673, 5, 'Q8', 'REGULAR', 188.00, 'available', NULL, NULL),
(674, 5, 'Q9', 'REGULAR', 188.00, 'available', NULL, NULL),
(675, 5, 'Q10', 'REGULAR', 188.00, 'available', NULL, NULL),
(676, 5, 'R1', 'REGULAR', 188.00, 'available', NULL, NULL),
(677, 5, 'R2', 'REGULAR', 188.00, 'available', NULL, NULL),
(678, 5, 'R3', 'REGULAR', 188.00, 'available', NULL, NULL),
(679, 5, 'R4', 'REGULAR', 188.00, 'available', NULL, NULL),
(680, 5, 'R5', 'REGULAR', 188.00, 'available', NULL, NULL),
(681, 5, 'R6', 'REGULAR', 188.00, 'available', NULL, NULL),
(682, 5, 'R7', 'REGULAR', 188.00, 'available', NULL, NULL),
(683, 5, 'R8', 'REGULAR', 188.00, 'available', NULL, NULL),
(684, 5, 'R9', 'REGULAR', 188.00, 'available', NULL, NULL),
(685, 5, 'R10', 'REGULAR', 188.00, 'available', NULL, NULL),
(686, 5, 'S1', 'REGULAR', 188.00, 'available', NULL, NULL),
(687, 5, 'S2', 'REGULAR', 188.00, 'available', NULL, NULL),
(688, 5, 'S3', 'REGULAR', 188.00, 'available', NULL, NULL),
(689, 5, 'S4', 'REGULAR', 188.00, 'available', NULL, NULL),
(690, 5, 'S5', 'REGULAR', 188.00, 'available', NULL, NULL),
(691, 5, 'S6', 'REGULAR', 188.00, 'available', NULL, NULL),
(692, 5, 'S7', 'REGULAR', 188.00, 'available', NULL, NULL),
(693, 5, 'S8', 'REGULAR', 188.00, 'available', NULL, NULL),
(694, 5, 'S9', 'REGULAR', 188.00, 'available', NULL, NULL),
(695, 5, 'S10', 'REGULAR', 188.00, 'available', NULL, NULL),
(696, 5, 'T1', 'REGULAR', 188.00, 'available', NULL, NULL),
(697, 5, 'T2', 'REGULAR', 188.00, 'available', NULL, NULL),
(698, 5, 'T3', 'REGULAR', 188.00, 'available', NULL, NULL),
(699, 5, 'T4', 'REGULAR', 188.00, 'available', NULL, NULL),
(700, 5, 'T5', 'REGULAR', 188.00, 'available', NULL, NULL),
(701, 5, 'T6', 'REGULAR', 188.00, 'available', NULL, NULL),
(702, 5, 'T7', 'REGULAR', 188.00, 'available', NULL, NULL),
(703, 5, 'T8', 'REGULAR', 188.00, 'available', NULL, NULL),
(704, 5, 'T9', 'REGULAR', 188.00, 'available', NULL, NULL),
(705, 5, 'T10', 'REGULAR', 188.00, 'available', NULL, NULL),
(706, 5, 'U1', 'REGULAR', 188.00, 'available', NULL, NULL),
(707, 5, 'U2', 'REGULAR', 188.00, 'available', NULL, NULL),
(708, 5, 'U3', 'REGULAR', 188.00, 'available', NULL, NULL),
(709, 5, 'U4', 'REGULAR', 188.00, 'available', NULL, NULL),
(710, 5, 'U5', 'REGULAR', 188.00, 'available', NULL, NULL),
(711, 5, 'U6', 'REGULAR', 188.00, 'available', NULL, NULL),
(712, 5, 'U7', 'REGULAR', 188.00, 'available', NULL, NULL),
(713, 5, 'U8', 'REGULAR', 188.00, 'available', NULL, NULL),
(714, 5, 'U9', 'REGULAR', 188.00, 'available', NULL, NULL),
(715, 5, 'U10', 'REGULAR', 188.00, 'available', NULL, NULL),
(716, 5, 'V1', 'REGULAR', 188.00, 'available', NULL, NULL),
(717, 5, 'V2', 'REGULAR', 188.00, 'available', NULL, NULL),
(718, 5, 'V3', 'REGULAR', 188.00, 'available', NULL, NULL),
(719, 5, 'V4', 'REGULAR', 188.00, 'available', NULL, NULL),
(720, 5, 'V5', 'REGULAR', 188.00, 'available', NULL, NULL),
(721, 5, 'V6', 'REGULAR', 188.00, 'available', NULL, NULL),
(722, 5, 'V7', 'REGULAR', 188.00, 'available', NULL, NULL),
(723, 5, 'V8', 'REGULAR', 188.00, 'available', NULL, NULL),
(724, 5, 'V9', 'REGULAR', 188.00, 'available', NULL, NULL),
(725, 5, 'V10', 'REGULAR', 188.00, 'available', NULL, NULL),
(726, 5, 'W1', 'REGULAR', 188.00, 'available', NULL, NULL),
(727, 5, 'W2', 'REGULAR', 188.00, 'available', NULL, NULL),
(728, 5, 'W3', 'REGULAR', 188.00, 'available', NULL, NULL),
(729, 5, 'W4', 'REGULAR', 188.00, 'available', NULL, NULL),
(730, 5, 'W5', 'REGULAR', 188.00, 'available', NULL, NULL),
(731, 5, 'W6', 'REGULAR', 188.00, 'available', NULL, NULL),
(732, 5, 'W7', 'REGULAR', 188.00, 'available', NULL, NULL),
(733, 5, 'W8', 'REGULAR', 188.00, 'available', NULL, NULL),
(734, 5, 'W9', 'REGULAR', 188.00, 'available', NULL, NULL),
(735, 5, 'W10', 'REGULAR', 188.00, 'available', NULL, NULL),
(736, 5, 'X1', 'REGULAR', 188.00, 'available', NULL, NULL),
(737, 5, 'X2', 'REGULAR', 188.00, 'available', NULL, NULL),
(738, 5, 'X3', 'REGULAR', 188.00, 'available', NULL, NULL),
(739, 5, 'X4', 'REGULAR', 188.00, 'available', NULL, NULL),
(740, 5, 'X5', 'REGULAR', 188.00, 'available', NULL, NULL),
(741, 5, 'X6', 'REGULAR', 188.00, 'available', NULL, NULL),
(742, 5, 'X7', 'REGULAR', 188.00, 'available', NULL, NULL),
(743, 5, 'X8', 'REGULAR', 188.00, 'available', NULL, NULL),
(744, 5, 'X9', 'REGULAR', 188.00, 'available', NULL, NULL),
(745, 5, 'X10', 'REGULAR', 188.00, 'available', NULL, NULL),
(746, 5, 'Y1', 'REGULAR', 188.00, 'available', NULL, NULL),
(747, 5, 'Y2', 'REGULAR', 188.00, 'available', NULL, NULL),
(748, 5, 'Y3', 'REGULAR', 188.00, 'available', NULL, NULL),
(749, 5, 'Y4', 'REGULAR', 188.00, 'available', NULL, NULL),
(750, 5, 'Y5', 'REGULAR', 188.00, 'available', NULL, NULL),
(751, 5, 'Y6', 'REGULAR', 188.00, 'available', NULL, NULL),
(752, 5, 'Y7', 'REGULAR', 188.00, 'available', NULL, NULL),
(753, 5, 'Y8', 'REGULAR', 188.00, 'available', NULL, NULL),
(754, 5, 'Y9', 'REGULAR', 188.00, 'available', NULL, NULL),
(755, 5, 'Y10', 'REGULAR', 188.00, 'available', NULL, NULL),
(756, 5, 'Z1', 'REGULAR', 188.00, 'available', NULL, NULL),
(757, 5, 'Z2', 'REGULAR', 188.00, 'available', NULL, NULL),
(758, 5, 'Z3', 'REGULAR', 188.00, 'available', NULL, NULL),
(759, 5, 'Z4', 'REGULAR', 188.00, 'available', NULL, NULL),
(760, 5, 'Z5', 'REGULAR', 188.00, 'available', NULL, NULL),
(761, 5, 'Z6', 'REGULAR', 188.00, 'available', NULL, NULL),
(762, 5, 'Z7', 'REGULAR', 188.00, 'available', NULL, NULL),
(763, 5, 'Z8', 'REGULAR', 188.00, 'available', NULL, NULL),
(764, 5, 'Z9', 'REGULAR', 188.00, 'available', NULL, NULL),
(765, 5, 'Z10', 'REGULAR', 188.00, 'available', NULL, NULL),
(766, 5, '[1', 'REGULAR', 188.00, 'available', NULL, NULL),
(767, 5, '[2', 'REGULAR', 188.00, 'available', NULL, NULL),
(768, 5, '[3', 'REGULAR', 188.00, 'available', NULL, NULL),
(769, 5, '[4', 'REGULAR', 188.00, 'available', NULL, NULL),
(770, 5, '[5', 'REGULAR', 188.00, 'available', NULL, NULL),
(771, 5, '[6', 'REGULAR', 188.00, 'available', NULL, NULL),
(772, 5, '[7', 'REGULAR', 188.00, 'available', NULL, NULL),
(773, 5, '[8', 'REGULAR', 188.00, 'available', NULL, NULL),
(774, 5, '[9', 'REGULAR', 188.00, 'available', NULL, NULL),
(775, 5, '[10', 'REGULAR', 188.00, 'available', NULL, NULL),
(776, 5, '1', 'REGULAR', 188.00, 'available', NULL, NULL),
(777, 5, '2', 'REGULAR', 188.00, 'available', NULL, NULL),
(778, 5, '3', 'REGULAR', 188.00, 'available', NULL, NULL),
(779, 5, '4', 'REGULAR', 188.00, 'available', NULL, NULL),
(780, 5, '5', 'REGULAR', 188.00, 'available', NULL, NULL),
(781, 5, '6', 'REGULAR', 188.00, 'available', NULL, NULL),
(782, 5, '7', 'REGULAR', 188.00, 'available', NULL, NULL),
(783, 5, '8', 'REGULAR', 188.00, 'available', NULL, NULL),
(784, 5, '9', 'REGULAR', 188.00, 'available', NULL, NULL),
(785, 5, '10', 'REGULAR', 188.00, 'available', NULL, NULL),
(786, 5, ']1', 'REGULAR', 188.00, 'available', NULL, NULL),
(787, 5, ']2', 'REGULAR', 188.00, 'available', NULL, NULL),
(788, 5, ']3', 'REGULAR', 188.00, 'available', NULL, NULL),
(789, 5, ']4', 'REGULAR', 188.00, 'available', NULL, NULL),
(790, 5, ']5', 'REGULAR', 188.00, 'available', NULL, NULL),
(791, 5, ']6', 'REGULAR', 188.00, 'available', NULL, NULL),
(792, 5, ']7', 'REGULAR', 188.00, 'available', NULL, NULL),
(793, 5, ']8', 'REGULAR', 188.00, 'available', NULL, NULL),
(794, 5, ']9', 'REGULAR', 188.00, 'available', NULL, NULL),
(795, 5, ']10', 'REGULAR', 188.00, 'available', NULL, NULL),
(796, 5, '^1', 'REGULAR', 188.00, 'available', NULL, NULL),
(797, 5, '^2', 'REGULAR', 188.00, 'available', NULL, NULL),
(798, 5, '^3', 'REGULAR', 188.00, 'available', NULL, NULL),
(799, 5, '^4', 'REGULAR', 188.00, 'available', NULL, NULL),
(800, 5, '^5', 'REGULAR', 188.00, 'available', NULL, NULL),
(801, 5, '^6', 'REGULAR', 188.00, 'available', NULL, NULL),
(802, 5, '^7', 'REGULAR', 188.00, 'available', NULL, NULL),
(803, 5, '^8', 'REGULAR', 188.00, 'available', NULL, NULL),
(804, 5, '^9', 'REGULAR', 188.00, 'available', NULL, NULL),
(805, 5, '^10', 'REGULAR', 188.00, 'available', NULL, NULL),
(806, 5, '_1', 'REGULAR', 188.00, 'available', NULL, NULL),
(807, 5, '_2', 'REGULAR', 188.00, 'available', NULL, NULL),
(808, 5, '_3', 'REGULAR', 188.00, 'available', NULL, NULL),
(809, 5, '_4', 'REGULAR', 188.00, 'available', NULL, NULL),
(810, 5, '_5', 'REGULAR', 188.00, 'available', NULL, NULL),
(811, 5, '_6', 'REGULAR', 188.00, 'available', NULL, NULL),
(812, 5, '_7', 'REGULAR', 188.00, 'available', NULL, NULL),
(813, 5, '_8', 'REGULAR', 188.00, 'available', NULL, NULL),
(814, 5, '_9', 'REGULAR', 188.00, 'available', NULL, NULL),
(815, 5, '_10', 'REGULAR', 188.00, 'available', NULL, NULL),
(816, 5, '`1', 'REGULAR', 188.00, 'available', NULL, NULL),
(817, 5, '`2', 'REGULAR', 188.00, 'available', NULL, NULL),
(818, 5, '`3', 'REGULAR', 188.00, 'available', NULL, NULL),
(819, 5, '`4', 'REGULAR', 188.00, 'available', NULL, NULL),
(820, 5, '`5', 'REGULAR', 188.00, 'available', NULL, NULL),
(821, 5, '`6', 'REGULAR', 188.00, 'available', NULL, NULL),
(822, 5, '`7', 'REGULAR', 188.00, 'available', NULL, NULL),
(823, 5, '`8', 'REGULAR', 188.00, 'available', NULL, NULL),
(824, 5, '`9', 'REGULAR', 188.00, 'available', NULL, NULL),
(825, 5, '`10', 'REGULAR', 188.00, 'available', NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) DEFAULT NULL,
  `receipt_img` varchar(255) DEFAULT NULL,
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','valid','invalid') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `receipt_img`, `payment_date`, `status`) VALUES
(1, 1, 'receipt_692dbe86d6b391.05418653.jpg', '2025-12-01 16:12:54', 'valid'),
(2, 2, 'receipt_692dc7d89b8816.78166253.png', '2025-12-01 16:52:40', 'valid'),
(3, 3, 'receipt_692dcb402cfe70.45459344.png', '2025-12-01 17:07:12', 'invalid'),
(4, 4, 'receipt_693003d959a818.36156831.png', '2025-12-03 09:33:13', 'invalid'),
(5, 5, 'receipt_69444b9271a6e4.04286851.png', '2025-12-18 18:44:34', 'valid'),
(6, 8, 'receipt_69445d5120b4a9.10321454.png', '2025-12-18 20:00:17', 'valid'),
(7, 9, 'receipt_694504e3277139.34324899.png', '2025-12-19 07:55:15', 'valid'),
(8, 10, 'receipt_694505f47809a3.42999719.png', '2025-12-19 07:59:48', 'pending');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `email_verified` tinyint(1) NOT NULL DEFAULT 0,
  `verify_token` varchar(64) DEFAULT NULL,
  `verify_expires` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`, `email_verified`, `verify_token`, `verify_expires`) VALUES
(1, 'Chief Admin', 'admin@concert.com', '$2y$10$VU1MYyTz/9O4rf8BH6nYnO/FWbKdViIqrn9xAkxZ5B9cut2LS3EaC', 'admin', '2025-12-01 16:05:21', 1, NULL, NULL),
(2, 'Basyir', 'dir.basyir@gmail.com', '$2y$10$MAEFGYsYEdFkapeGu8gr/OXICFBhh/goXeGbgUKAZc5oBaqsnMhGO', 'user', '2025-12-01 16:51:56', 0, NULL, NULL),
(3, 'iman', 'iman@gmail.com', '$2y$10$S21INdexciye03EGPpCZEO/7imosXJxbvd.JvYWtVWwvPq/GEJ5zq', 'user', '2025-12-18 18:43:08', 0, NULL, NULL),
(4, 'kai', 'khairulnazim46@icloud.com', '$2y$10$oX8FY1rA3IdkIJ/Lu8TT6eAVJjDoumEBVa3DqoKYjYwhwX3BCgwnq', 'user', '2025-12-18 19:16:40', 1, NULL, NULL),
(5, 'imran', 'imrancute@gmail.com', '$2y$10$Utbq4.IEXRalMzubHuA3J.SI7M23YQzgE4pi9phNowx31M5fwmrKy', 'user', '2025-12-19 07:52:28', 1, NULL, NULL);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `concert_id` (`concert_id`);

--
-- Indexes for table `booking_seats`
--
ALTER TABLE `booking_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_booking_seat` (`booking_id`,`seat_id`);

--
-- Indexes for table `concerts`
--
ALTER TABLE `concerts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `concert_seats`
--
ALTER TABLE `concert_seats`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `concert_id` (`concert_id`,`seat_code`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_users_verify_token` (`verify_token`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `booking_seats`
--
ALTER TABLE `booking_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `concerts`
--
ALTER TABLE `concerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `concert_seats`
--
ALTER TABLE `concert_seats`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=906;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`concert_id`) REFERENCES `concerts` (`id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
