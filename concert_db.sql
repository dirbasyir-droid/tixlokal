-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 01, 2025 at 09:01 AM
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
  `booking_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending_payment','verification_pending','approved','rejected') DEFAULT 'pending_payment',
  `qr_code_data` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `concert_id`, `booking_date`, `status`, `qr_code_data`) VALUES
(1, 1, 1, '2025-11-30 18:56:23', 'approved', 'TICKET-1-692c937acfa49'),
(2, 1, 1, '2025-11-30 18:58:51', 'rejected', NULL),
(3, 1, 1, '2025-11-30 19:34:08', 'pending_payment', NULL),
(4, 1, 2, '2025-11-30 19:43:31', 'approved', 'TICKET-4-692c9ea1afdb0'),
(5, 1, 2, '2025-11-30 19:47:56', 'pending_payment', NULL),
(6, 2, 1, '2025-12-01 07:43:03', 'approved', 'TICKET-6-692d485211563');

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
  `description` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `concerts`
--

INSERT INTO `concerts` (`id`, `artist`, `venue`, `event_date`, `price`, `description`, `image_url`) VALUES
(1, '53 Universe - Salam Terakhir', 'Vox Live, KL', '2025-08-23 20:00:00', 75.00, '53 Universe invites you to Salam Terakhir — an overdue presentation celebrating their breakout album “ Semoga Dipermudahkan”.\r\n\r\nStep into the sonic cosmos that elevates the mind and soul through a one-of-a-kind experience.\r\n\r\nDate: Saturday, 23 August 2025\r\nTime: 8PM (Door open 7PM)\r\nVenue: Vox Live, KL\r\nPrice: RM75\r\n\r\nExpect the full album front-to-back, surprise remixes and performances from Pele L., Fuego, YBJ, Ryb3na, A2M, AMK, Madmuz, Faiz Ruzayn, and IMRN & more. Limited tix, zero reruns. See u there!\r\n\r\n#SalamTerakhir #53Universe #53Stu\r\n', 'img_692c92f3b900c7.13640548.jpg'),
(2, 'MidLyfe - MORELYFE 2025', 'JioSpace, Petaling Jaya', '2025-12-28 17:00:00', 80.00, 'Back again for round 2 — Our second showcase ••• 𝗠𝗢𝗥𝗘 𝗟𝗬𝗙𝗘 𝟮𝟱’\r\n\r\nHappening on 28th of December 2025 at JioSpace, Petaling Jaya.\r\n\r\nPerformance by our very own lucidrari, Eemrun, Dannqrack, Heil Nuan, Kidsteph and ADER2K. Not to forget a very very special visual presentation by Salahznl to celebrate this sequel.\r\n\r\n', 'img_692c9dd979b9d4.98609486.jpg'),
(3, 'NUANSA: DI ANTARA SUARA', ' MENARA PT80 KL', '2025-11-23 15:00:00', 69.00, '💎 NUANSA: DI ANTARA SUARA 💎\r\n\r\nKAMI BAWAKKAN @emptypagex BERSAMA 4 ARTIS POWER YANG AKAN MEMERIAHKAN LAGI NUANSA 2025!\r\n\r\nTiket sangat limited, jangan terlepas dan rebut cepat sementara masih ada! Konsert teremosi tahun ni, luahkan segala isi hati kat sini 🫵🏼❤️‍🔥🫵🏼❤️‍🔥🫵🏼\r\n\r\nRM69 | MENARA PT80 KL | 23 NOVEMBER 2025 | 3PM\r\n\r\nFeaturing:\r\n@6ixthsenseofficial\r\n@aizatamdan\r\n@iamneeta3\r\n@hyperactband\r\n@emptypagex\r\n\r\n#initimekitashine #konsert #nuansa2025', 'img_692cad17a23120.81239187.jpg');

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
(1, 1, 'receipt_692c936e3ed5c7.88420967.png', '2025-11-30 18:56:46', 'valid'),
(2, 2, 'receipt_692c93f1c4f462.76277654.png', '2025-11-30 18:58:57', 'invalid'),
(3, 4, 'receipt_692c9e9468adc7.92957535.png', '2025-11-30 19:44:20', 'valid'),
(4, 6, 'receipt_692d47182698b4.59395200.png', '2025-12-01 07:43:20', 'valid');

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
(1, 'Chief Admin', 'admin@concert.com', 'admin123', 'admin', '2025-11-30 18:32:54'),
(2, 'DIR MUHAMAD BASYIR BIN AB HALIM @ KAMARUDDIN', 'dir.basyir@gmail.com', '123456', 'user', '2025-12-01 07:41:01');

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `concerts`
--
ALTER TABLE `concerts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

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
