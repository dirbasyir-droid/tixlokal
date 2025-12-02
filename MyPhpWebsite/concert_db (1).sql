-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 06:43 PM
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
(3, 1, 1, 1, 75.00, '2025-12-01 17:06:59', 'rejected', NULL);

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
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `concerts`
--

INSERT INTO `concerts` (`id`, `artist`, `venue`, `event_date`, `price`, `capacity`, `description`, `image_url`) VALUES
(1, '53 Universe - Salam Terakhir', 'Vox Live, KL', '2025-08-23 00:08:00', 75.00, 100, '3 Universe invites you to 𝓢𝓪𝓵𝓪𝓶 𝓣𝓮𝓻𝓪𝓴𝓱𝓲𝓻 — an overdue presentation celebrating their breakout album ”𝕾𝖊𝖒𝖔𝖌𝖆 𝕯𝖎𝖕𝖊𝖗𝖒𝖚𝖉𝖆𝖍𝖐𝖆𝖓”. Step into the sonic cosmos that elevates the mind and soul through a one-of-a-kind experience.\r\n\r\nDate: Saturday, 23 August 2025\r\nTime: 8PM (doors open 7PM)\r\nVenue: VOX Live, Kuala Lumpur\r\nPrice: RM75\r\n\r\nExpect the full album front-to-back, surprise remixes and performances from Pele L., Fuego, YBJ, Ryb3na, A2M, AMK, Madmuz, Faiz Ruzayn, and IMRN & more. Limited tix, zero reruns. See u there Ⓜ️🐉', 'img_692dbe4c69e0e0.79070002.jpg'),
(2, 'MidLyfe - MORELYFE 2025', 'JioSpace, Petaling Jaya', '2025-12-02 00:16:00', 85.00, 100, 'll', 'img_692dbf6858c547.93272095.jpg');

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
(3, 3, 'receipt_692dcb402cfe70.45459344.png', '2025-12-01 17:07:12', 'invalid');

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
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `role`, `created_at`) VALUES
(1, 'Chief Admin', 'admin@concert.com', '$2y$10$VU1MYyTz/9O4rf8BH6nYnO/FWbKdViIqrn9xAkxZ5B9cut2LS3EaC', 'admin', '2025-12-01 16:05:21'),
(2, 'Basyir', 'dir.basyir@gmail.com', '$2y$10$MAEFGYsYEdFkapeGu8gr/OXICFBhh/goXeGbgUKAZc5oBaqsnMhGO', 'user', '2025-12-01 16:51:56');

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
-- Indexes for table `concerts`
--
ALTER TABLE `concerts`
  ADD PRIMARY KEY (`id`);

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
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `concerts`
--
ALTER TABLE `concerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
