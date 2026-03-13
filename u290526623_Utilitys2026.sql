-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1:3306
-- Generation Time: Mar 18, 2026 at 02:50 AM
-- Server version: 11.8.3-MariaDB-log
-- PHP Version: 7.2.34

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `u290526623_Utilitys2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `billers`
--

CREATE TABLE `billers` (
  `biller_id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `biller_name` varchar(100) NOT NULL,
  `category` varchar(50) DEFAULT 'Other',
  `account_format` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `billers`
--

INSERT INTO `billers` (`biller_id`, `merchant_id`, `biller_name`, `category`, `account_format`, `created_at`) VALUES
(1, 1, 'Noneco', 'Electricity', '134543', '2026-03-01 06:43:03'),
(2, 1, 'Parasat', 'WiFi', '12345', '2026-03-01 11:15:34'),
(3, 1, 'City Water', 'Water', '1235', '2026-03-01 15:30:14'),
(5, 2, 'Maynilad', 'Electricity', '09101287631', '2026-03-02 15:54:37'),
(6, 1, 'globe', 'WiFi', '1122', '2026-03-06 11:29:07'),
(7, 1, 'du-ek sam', 'Other', '521', '2026-03-09 11:16:04'),
(8, 4, 'PHOTOGRAPHY', 'Other', '123', '2026-03-16 02:30:30');

-- --------------------------------------------------------

--
-- Table structure for table `bills`
--

CREATE TABLE `bills` (
  `bill_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('UNPAID','PAID','OVERDUE') DEFAULT 'UNPAID',
  `bill_date` datetime NOT NULL,
  `due_date` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bills`
--

INSERT INTO `bills` (`bill_id`, `account_id`, `amount`, `status`, `bill_date`, `due_date`) VALUES
(1, 1, 1000.00, 'PAID', '2026-03-01 16:17:36', '2026-03-08 16:17:36'),
(2, 2, 3500.00, 'PAID', '2026-03-01 16:27:24', '2026-03-08 16:27:24'),
(3, 3, 450.00, 'PAID', '2026-03-01 16:28:25', '2026-03-08 16:28:25'),
(4, 3, 450.00, 'PAID', '2026-03-01 16:28:26', '2026-03-08 16:28:26'),
(5, 4, 254.00, 'PAID', '2026-03-01 16:37:23', '2026-03-08 16:37:23'),
(6, 5, 3000.00, 'PAID', '2026-03-02 08:04:15', '2026-03-09 08:04:15'),
(7, 6, 400.00, 'PAID', '2026-03-02 08:07:54', '2026-03-09 08:07:54'),
(8, 7, 3000.00, 'PAID', '2026-03-02 11:22:57', '2026-03-09 11:22:57'),
(9, 8, 500.00, 'PAID', '2026-03-02 13:28:00', '2026-03-09 13:28:00'),
(10, 2, 300.00, 'PAID', '2026-03-02 14:58:11', '2026-03-09 14:58:11'),
(11, 9, 800.00, 'PAID', '2026-03-03 00:17:29', '2026-03-10 00:17:29'),
(12, 10, 800.00, 'PAID', '2026-03-05 11:40:25', '2026-03-12 11:40:25'),
(13, 11, 900.00, 'PAID', '2026-03-06 11:27:47', '2026-03-13 11:27:47'),
(14, 12, 800.00, 'PAID', '2026-03-10 09:46:31', '2026-03-17 09:46:31'),
(15, 13, 300.00, 'PAID', '2026-03-10 09:51:28', '2026-03-17 09:51:28'),
(16, 14, 5050.00, 'PAID', '2026-03-12 13:14:02', '2026-03-19 13:14:02'),
(17, 15, 1500.00, 'PAID', '2026-03-16 02:32:08', '2026-03-23 02:32:08'),
(18, 16, 1500.00, 'PAID', '2026-03-16 09:31:09', '2026-03-23 09:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `ewallet_transactions`
--

CREATE TABLE `ewallet_transactions` (
  `ewallet_id` int(11) NOT NULL,
  `merchant_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `customer_name` varchar(255) DEFAULT NULL,
  `transaction_type` enum('CASH_IN','CASH_OUT') NOT NULL,
  `provider` varchar(50) NOT NULL COMMENT 'e.g. GCASH, MAYA, PAYPAL',
  `mobile_number` varchar(20) NOT NULL,
  `account_name` varchar(100) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL COMMENT 'The principal amount',
  `fee` decimal(10,2) DEFAULT 0.00 COMMENT 'Service charge',
  `total_amount` decimal(10,2) NOT NULL COMMENT 'Amount + Fee',
  `reference_number` varchar(100) DEFAULT NULL,
  `status` enum('PENDING','COMPLETED','CANCELLED') DEFAULT 'COMPLETED',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `ewallet_transactions`
--

INSERT INTO `ewallet_transactions` (`ewallet_id`, `merchant_id`, `customer_id`, `customer_name`, `transaction_type`, `provider`, `mobile_number`, `account_name`, `amount`, `fee`, `total_amount`, `reference_number`, `status`, `created_at`) VALUES
(1, 1, NULL, NULL, 'CASH_IN', 'GCASH', '09101882719', 'Jomar Mutas', 100.00, 1.00, 101.00, 'EW-IN-1772382918', 'COMPLETED', '2026-03-01 16:35:18'),
(2, 1, NULL, NULL, 'CASH_OUT', 'GCASH', '09101882719', 'Raniel John  De Asis', 300.00, 10.00, 310.00, 'EW-OUT-1772382964', 'COMPLETED', '2026-03-01 16:36:04'),
(3, 1, NULL, NULL, 'CASH_IN', 'GCASH', '09101882719', 'Jomar  Mutas', 500.00, 5.00, 505.00, 'EW-IN-1772458752', 'COMPLETED', '2026-03-02 13:39:12'),
(4, 1, NULL, NULL, 'CASH_IN', 'MAYA', '09101882719', 'Jomar Mutas', 500.00, 5.00, 505.00, 'EW-IN-1772460207', 'COMPLETED', '2026-03-02 14:03:27'),
(5, 1, NULL, NULL, 'CASH_OUT', 'GCASH', '09101882719', 'Billy Donan', 300.00, 10.00, 310.00, 'EW-OUT-1772461183', 'COMPLETED', '2026-03-02 14:19:43'),
(6, 1, NULL, NULL, 'CASH_IN', 'GCASH', '09101882719', 'Jomar Mutas', 300.00, 6.00, 306.00, 'EW-IN-1772463561', 'COMPLETED', '2026-03-02 14:59:21'),
(7, 2, NULL, NULL, 'CASH_IN', 'GCASH', '091720536358', 'Earl', 500.00, 10.00, 510.00, 'EW-IN-1772466900', 'COMPLETED', '2026-03-02 15:55:00'),
(8, 1, NULL, NULL, 'CASH_IN', 'GCASH', '09385940147', 'Jomar', 300.00, 6.00, 306.00, 'EW-IN-1772496552', 'COMPLETED', '2026-03-03 00:09:12');

-- --------------------------------------------------------

--
-- Table structure for table `merchants`
--

CREATE TABLE `merchants` (
  `merchant_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `business_name` varchar(150) NOT NULL,
  `status` enum('PENDING','APPROVED','REJECTED') DEFAULT 'PENDING',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `merchants`
--

INSERT INTO `merchants` (`merchant_id`, `user_id`, `business_name`, `status`, `created_at`) VALUES
(1, 1, 'RanShie Supermarket', 'APPROVED', '2026-03-01 06:42:21'),
(2, 7, 'Udoy Merchandise', 'PENDING', '2026-03-02 15:53:48'),
(3, 9, 'Dodoystore', 'PENDING', '2026-03-12 15:39:27'),
(4, 10, 'Moment Capturers by raniel', 'PENDING', '2026-03-16 02:26:53');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `bill_id` int(11) NOT NULL,
  `customer_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `status` enum('SUCCESS','PENDING','FAILED') DEFAULT 'SUCCESS',
  `paid_at` datetime DEFAULT current_timestamp(),
  `reference_number` varchar(100) NOT NULL,
  `payment_method` enum('Cash','GCASH','MAYA','Others') DEFAULT 'Cash'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `bill_id`, `customer_id`, `amount`, `status`, `paid_at`, `reference_number`, `payment_method`) VALUES
(1, 1, 3, 1030.00, 'SUCCESS', '2026-03-01 16:17:36', 'M1-1772381856921', 'MAYA'),
(2, 2, 2, 3605.00, 'SUCCESS', '2026-03-01 16:27:24', 'M1-1772382444967', 'GCASH'),
(3, 3, NULL, 463.50, 'SUCCESS', '2026-03-01 16:28:25', 'M1-1772382505613', 'Cash'),
(5, 5, 5, 261.62, 'SUCCESS', '2026-03-01 16:37:23', 'M1-1772383043305', 'GCASH'),
(10, 10, 4, 309.00, 'SUCCESS', '2026-03-02 14:58:11', 'M1-1772463491878', ''),
(11, 11, NULL, 824.00, 'SUCCESS', '2026-03-03 00:17:29', 'M1-1772497049513', ''),
(12, 12, NULL, 824.00, 'SUCCESS', '2026-03-05 11:40:25', 'M1-1772710825508', ''),
(13, 13, NULL, 927.00, 'SUCCESS', '2026-03-06 11:27:47', 'M1-1772796467375', ''),
(14, 14, 1, 824.00, 'SUCCESS', '2026-03-10 09:46:31', 'M1-1773135991175', ''),
(15, 15, 3, 309.00, 'SUCCESS', '2026-03-10 09:51:28', 'M1-1773136288770', ''),
(16, 16, NULL, 5201.50, 'SUCCESS', '2026-03-12 13:14:02', 'M1-1773321242583', ''),
(17, 17, 10, 1545.00, 'SUCCESS', '2026-03-16 02:32:08', 'M4-1773628328832', ''),
(18, 18, 10, 1545.00, 'SUCCESS', '2026-03-16 09:31:09', 'M4-1773653469169', '');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `role` enum('ADMIN','CUSTOMER','MERCHANT') NOT NULL,
  `full_name` varchar(150) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` text NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `status` enum('ACTIVE','SUSPENDED') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `role`, `full_name`, `email`, `password_hash`, `phone`, `status`, `created_at`) VALUES
(1, 'MERCHANT', 'Raniel De Asis', 'rjdeasis@admin.com', '$2y$10$btJFExP948R3m0.qvlb3aOEXbtAQZbcmeIPt0cIdU/YrUgTlqZ.DW', '', 'ACTIVE', '2026-03-01 06:03:19'),
(2, 'ADMIN', 'Jomar Mutas', 'mutas@admin.com', '$2y$10$w5F6OVI9.1Vlf4a6cpZjE.D4aZIFF33yYyk5VNaHp/jtoWzxS/kpm', NULL, 'ACTIVE', '2026-03-01 06:05:07'),
(3, 'CUSTOMER', 'Kenn Jay Eslais', 'eslais@admin.com', '$2y$10$I0/y.v6pzQKbyTWHkxXT/.gd4KgutF5fB4nvq4Pn5aQN/WJSANE3K', '', 'ACTIVE', '2026-03-01 06:35:34'),
(4, 'CUSTOMER', 'Drunreb Perez', 'perez@csr-scc.edu.ph', '$2y$10$43EbmnjYOMlYxA.dOAf0EOebC6snX9QDfAkGIWAc7ghuTJyD9VJxG', '', 'ACTIVE', '2026-03-01 08:14:49'),
(5, 'CUSTOMER', 'Drunreb Perez II', 'dodoy@admin.com', '$2y$10$Btzu0JMjkb6ZbNe0ZupN5O1.OsBuLwCSaHFMFRWmW4zHn8OtqgKTC', '', 'ACTIVE', '2026-03-01 11:21:40'),
(7, 'ADMIN', 'Raniel', 'raniel@gmail.com', '$2y$10$udUtVPHGhIIOncOdk4mKXuY.rd4GexEZ0QOK0N.uvABHL74B8ah6G', '', 'ACTIVE', '2026-03-02 15:53:48'),
(8, 'CUSTOMER', 'Raniel@admin.com', 'rjdeasis@gmail.com', '$2y$10$qxqfMG9OGN683S1PM3Pjge/6g/YhwfSr9JAE8EODt0PWzclBg3ozO', '', 'ACTIVE', '2026-03-04 00:48:36'),
(9, 'MERCHANT', 'Dodoy', 'dodoy@csr.com', '$2y$10$DXfqk2SUqRprOtp1ZNROEegP6uxbmDMHfloSB1hWzd/shlLvj4j4y', '', 'ACTIVE', '2026-03-12 15:39:27'),
(10, 'MERCHANT', 'Moment Capturers by raniel', 'Moment@admin.com', '$2y$10$Kds9a9QlI9jhFallCWJ6ae8V3tHpq/xnHJv/2x0oc8UZ3TgPFNDRm', '', 'ACTIVE', '2026-03-16 02:26:53');

-- --------------------------------------------------------

--
-- Table structure for table `utility_accounts`
--

CREATE TABLE `utility_accounts` (
  `account_id` int(11) NOT NULL,
  `biller_id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `account_number` varchar(50) NOT NULL,
  `account_holder_name` varchar(100) DEFAULT NULL,
  `status` enum('ACTIVE','INACTIVE') DEFAULT 'ACTIVE',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `utility_accounts`
--

INSERT INTO `utility_accounts` (`account_id`, `biller_id`, `user_id`, `account_number`, `account_holder_name`, `status`, `created_at`) VALUES
(1, 3, 3, '265685654', 'Kenn Jay Eslais', 'ACTIVE', '2026-03-01 16:17:36'),
(2, 3, 4, '09101882719', 'Jomar Mutas', 'ACTIVE', '2026-03-01 16:27:24'),
(3, 2, NULL, '25466369', 'Billy Donan', 'ACTIVE', '2026-03-01 16:28:25'),
(4, 3, 5, '10258516', 'Drunreb Perez II', 'ACTIVE', '2026-03-01 16:37:23'),
(5, 3, 4, '123245', 'water', 'ACTIVE', '2026-03-02 08:04:15'),
(6, 1, 4, '0999', '300', 'ACTIVE', '2026-03-02 08:07:54'),
(7, 3, 5, '09914521000', 'raniel', 'ACTIVE', '2026-03-02 11:22:57'),
(8, 3, 4, '091027986354', 'Billy', 'ACTIVE', '2026-03-02 13:28:00'),
(9, 1, NULL, '0984156789', 'Billy Donan', 'ACTIVE', '2026-03-03 00:17:29'),
(10, 1, NULL, '098984114', 'earl', 'ACTIVE', '2026-03-05 11:40:25'),
(11, 1, NULL, '123645987', 'billy', 'ACTIVE', '2026-03-06 11:27:47'),
(12, 1, 1, '245444', 'Raniel', 'ACTIVE', '2026-03-10 09:46:31'),
(13, 6, 3, '1425', 'Eslais', 'ACTIVE', '2026-03-10 09:51:28'),
(14, 7, NULL, '4153', 'Earl', 'ACTIVE', '2026-03-12 13:14:02'),
(15, 8, 10, '09511653052', 'Moment Capturers by raniel', 'ACTIVE', '2026-03-16 02:32:08'),
(16, 8, 10, '0122', 'Moment Capturers by raniel', 'ACTIVE', '2026-03-16 09:31:09');

-- --------------------------------------------------------

--
-- Table structure for table `walkin_customers`
--

CREATE TABLE `walkin_customers` (
  `walkin_id` int(11) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `email` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `walkin_customers`
--

INSERT INTO `walkin_customers` (`walkin_id`, `full_name`, `email`, `created_at`) VALUES
(1, 'Billy Donan', '', '2026-03-01 16:28:25'),
(2, 'Billy Donan', '', '2026-03-01 16:28:26'),
(3, 'Billy Donan', 'donan@admin.com', '2026-03-03 00:17:29'),
(4, 'earl', 'earl@admin.com', '2026-03-05 11:40:25'),
(5, 'billy', 'billy@admin.com', '2026-03-06 11:27:47'),
(6, 'Earl Esguerra', '', '2026-03-12 13:14:02');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `billers`
--
ALTER TABLE `billers`
  ADD PRIMARY KEY (`biller_id`),
  ADD KEY `merchant_id` (`merchant_id`);

--
-- Indexes for table `bills`
--
ALTER TABLE `bills`
  ADD PRIMARY KEY (`bill_id`),
  ADD KEY `account_id` (`account_id`);

--
-- Indexes for table `ewallet_transactions`
--
ALTER TABLE `ewallet_transactions`
  ADD PRIMARY KEY (`ewallet_id`),
  ADD KEY `idx_merchant` (`merchant_id`),
  ADD KEY `idx_ref` (`reference_number`),
  ADD KEY `idx_ewallet_customer` (`customer_id`);

--
-- Indexes for table `merchants`
--
ALTER TABLE `merchants`
  ADD PRIMARY KEY (`merchant_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `bill_id` (`bill_id`),
  ADD KEY `customer_id` (`customer_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `utility_accounts`
--
ALTER TABLE `utility_accounts`
  ADD PRIMARY KEY (`account_id`),
  ADD KEY `biller_id` (`biller_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `walkin_customers`
--
ALTER TABLE `walkin_customers`
  ADD PRIMARY KEY (`walkin_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `billers`
--
ALTER TABLE `billers`
  MODIFY `biller_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `bills`
--
ALTER TABLE `bills`
  MODIFY `bill_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `ewallet_transactions`
--
ALTER TABLE `ewallet_transactions`
  MODIFY `ewallet_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `merchants`
--
ALTER TABLE `merchants`
  MODIFY `merchant_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `utility_accounts`
--
ALTER TABLE `utility_accounts`
  MODIFY `account_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `walkin_customers`
--
ALTER TABLE `walkin_customers`
  MODIFY `walkin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `billers`
--
ALTER TABLE `billers`
  ADD CONSTRAINT `billers_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`merchant_id`) ON DELETE CASCADE;

--
-- Constraints for table `bills`
--
ALTER TABLE `bills`
  ADD CONSTRAINT `bills_ibfk_1` FOREIGN KEY (`account_id`) REFERENCES `utility_accounts` (`account_id`) ON DELETE CASCADE;

--
-- Constraints for table `ewallet_transactions`
--
ALTER TABLE `ewallet_transactions`
  ADD CONSTRAINT `ewallet_transactions_ibfk_1` FOREIGN KEY (`merchant_id`) REFERENCES `merchants` (`merchant_id`) ON DELETE CASCADE;

--
-- Constraints for table `merchants`
--
ALTER TABLE `merchants`
  ADD CONSTRAINT `merchants_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`bill_id`) REFERENCES `bills` (`bill_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`customer_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;

--
-- Constraints for table `utility_accounts`
--
ALTER TABLE `utility_accounts`
  ADD CONSTRAINT `utility_accounts_ibfk_1` FOREIGN KEY (`biller_id`) REFERENCES `billers` (`biller_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `utility_accounts_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
