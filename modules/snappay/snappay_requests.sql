-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 27, 2026 at 01:41 PM
-- Server version: 8.0.44-cll-lve
-- PHP Version: 8.4.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `abanfrui_shop`
--

-- --------------------------------------------------------

--
-- Table structure for table `snappay_requests`
--

CREATE TABLE `snappay_requests` (
  `id` int NOT NULL,
  `order_id` int NOT NULL,
  `user_id` int NOT NULL,
  `request_type` enum('update','cancel') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','approved','denied') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `admin_note` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `request_date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `action_date` timestamp NULL DEFAULT NULL,
  `request_snapshot` longtext COLLATE utf8mb4_unicode_ci
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `snappay_requests`
--

INSERT INTO `snappay_requests` (`id`, `order_id`, `user_id`, `request_type`, `status`, `admin_note`, `request_date`, `action_date`, `request_snapshot`) VALUES
(15, 60805, 30783, 'update', 'approved', '', '2026-05-08 22:49:08', '2026-05-09 05:37:07', '{\"type\":\"update\",\"requested_qty_map\":{\"281107\":1},\"changed_items\":[{\"soid\":281107,\"pid\":18,\"name\":\"لیمو سنگی\",\"weight\":\"50\",\"old_qty\":2,\"new_qty\":1}],\"financial\":{\"cart_price\":255000,\"cart_pure\":5100,\"sale_total\":328300,\"pay_price\":6700}}'),
(26, 60862, 30783, 'update', 'approved', '', '2026-05-27 09:58:14', '2026-05-27 10:08:54', '{\"type\":\"update\",\"requested_qty_map\":{\"281282\":1},\"changed_items\":[{\"soid\":281282,\"pid\":155,\"name\":\"لواشک هلو\",\"weight\":\"120\",\"old_qty\":2,\"new_qty\":1}],\"financial\":{\"cart_price\":296000,\"cart_pure\":5920,\"sale_total\":427280,\"pay_price\":8720}}');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `snappay_requests`
--
ALTER TABLE `snappay_requests`
  ADD PRIMARY KEY (`id`),
  ADD KEY `order_id` (`order_id`),
  ADD KEY `order_id_request_type` (`order_id`,`request_type`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `status` (`status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `snappay_requests`
--
ALTER TABLE `snappay_requests`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
