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
-- Database: `u290526623_BHMS2026`
--

-- --------------------------------------------------------

--
-- Table structure for table `ActivityLogs`
--

CREATE TABLE `ActivityLogs` (
  `log_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action_type` varchar(50) NOT NULL,
  `entity_type` varchar(50) NOT NULL,
  `entity_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `ActivityLogs`
--

INSERT INTO `ActivityLogs` (`log_id`, `user_id`, `action_type`, `entity_type`, `entity_id`, `description`, `ip_address`, `created_at`) VALUES
(1, 6, 'update', 'property', 1, 'Updated property: Drunreb&#039;s Residence', '103.187.245.58', '2026-03-08 04:08:13'),
(2, 6, 'create', 'rental', 4, 'Created rental agreement for Earl Esguerra in Room 1', '103.187.245.58', '2026-03-08 04:08:47'),
(3, 6, 'update', 'rental', 4, 'Updated rental details/end date', '103.62.154.99', '2026-03-08 12:23:22'),
(4, 6, 'create', 'payment', 3, 'Recorded manual payment of 2500 for Rental ID 4', '103.62.154.99', '2026-03-08 12:23:37'),
(5, 6, 'create', 'rental', 5, 'Created rental agreement for Earl Vincent Son Esguerra in Room 1', '103.62.154.99', '2026-03-08 12:24:40'),
(6, 6, 'create', 'room', 3, 'Added Room 8 to property: Drunreb&#039;s Residence', '222.127.71.148', '2026-03-08 13:51:27'),
(7, 6, 'update', 'room', 3, 'Updated Room 8 details', '222.127.71.148', '2026-03-08 13:52:06'),
(8, 6, 'update', 'room', 3, 'Updated Room 2 details', '222.127.71.148', '2026-03-08 13:52:20'),
(9, 11, 'create', 'application', 3, 'Applied for Room ID: 3', '222.127.71.148', '2026-03-08 13:53:08'),
(10, 6, 'approve', 'application', 7, 'Approved application ID: 7', '222.127.71.148', '2026-03-08 13:53:43'),
(11, 6, 'create', 'rental', 6, 'Created rental agreement for Drunreb Perez in Room 2', '222.127.71.148', '2026-03-08 13:54:26'),
(12, 6, 'create', 'payment', 4, 'Recorded manual payment of 2000 for Rental ID 6', '222.127.71.148', '2026-03-08 13:54:53'),
(13, 6, 'create', 'room', 4, 'Added Room 3 to property: Drunreb&#039;s Residence', '222.127.226.192', '2026-03-08 15:26:09'),
(14, 6, 'create', 'room', 5, 'Added Room 4 to property: Drunreb&#039;s Residence', '222.127.71.148', '2026-03-08 15:27:11'),
(15, 6, 'create', 'room', 6, 'Added Room 5 to property: Drunreb&#039;s Residence', '222.127.71.148', '2026-03-08 15:28:05'),
(16, 6, 'create', 'property', 3, 'Added new property: Drunreb Apartment ', '180.191.228.104', '2026-03-09 00:51:21'),
(17, 6, 'update', 'property', 3, 'Updated property: Cpsu BH', '222.127.76.207', '2026-03-09 00:54:06'),
(18, 6, 'create', 'room', 7, 'Added Room 1 to property: Cpsu BH', '222.127.76.207', '2026-03-09 00:56:25'),
(19, 6, 'create', 'room', 8, 'Added Room 2 to property: Cpsu BH', '222.127.76.207', '2026-03-09 00:57:00'),
(20, 6, 'update', 'payment', 4, 'Marked payment ID 4 as pending', '222.127.71.148', '2026-03-09 01:00:30'),
(21, 6, 'update', 'payment', 4, 'Marked payment ID 4 as overdue', '222.127.71.148', '2026-03-09 01:00:39'),
(22, 6, 'update', 'payment', 4, 'Marked payment ID 4 as failed', '222.127.71.148', '2026-03-09 01:00:48'),
(23, 6, 'update', 'payment', 4, 'Marked payment ID 4 as paid', '222.127.71.148', '2026-03-09 01:00:53'),
(24, 6, 'update', 'rental', 4, 'Updated rental details/end date', '222.127.71.148', '2026-03-09 01:01:58'),
(25, 6, 'update', 'rental', 3, 'Updated rental details/end date', '222.127.71.148', '2026-03-09 01:02:07'),
(26, 6, 'update', 'rental', 3, 'Updated rental details/end date', '222.127.71.148', '2026-03-09 01:02:38'),
(27, 6, 'create', 'rental', 7, 'Created rental agreement for Drun Perez in Room 1', '180.191.228.104', '2026-03-09 01:03:48'),
(28, 6, 'create', 'payment', 5, 'Recorded manual payment of 2500 for Rental ID 3', '180.191.228.104', '2026-03-09 01:04:41'),
(29, 6, 'update', 'rental', 3, 'Updated rental details/end date', '180.191.228.104', '2026-03-09 01:05:12'),
(30, 15, 'create', 'application', 6, 'Applied for Room ID: 6', '180.190.242.5', '2026-03-09 11:22:42'),
(31, 6, 'approve', 'application', 10, 'Approved application ID: 10', '180.190.242.5', '2026-03-09 11:23:15'),
(32, 6, 'create', 'rental', 8, 'Created rental agreement for Ralph Vidal in Room 5', '180.190.242.5', '2026-03-09 11:23:38'),
(33, 6, 'create', 'payment', 6, 'Recorded manual payment of 2500 for Rental ID 8', '180.190.242.5', '2026-03-09 11:23:59'),
(34, 6, 'update', 'property', 3, 'Updated property: Perez Residence', '180.190.242.5', '2026-03-09 11:25:32'),
(35, 6, 'create', 'rental', 9, 'Created rental agreement for Francis Malabo in Room 1', '180.190.242.5', '2026-03-10 10:31:02'),
(36, 6, 'create', 'payment', 7, 'Recorded manual payment of 2520 for Rental ID 9', '180.190.242.5', '2026-03-10 10:31:30'),
(37, 6, 'create', 'payment', 8, 'Recorded manual payment of 2500 for Rental ID 9', '180.191.228.104', '2026-03-11 07:53:57'),
(38, 6, 'update', 'rental', 9, 'Changed rental status to completed (Ended)', '180.191.228.104', '2026-03-11 07:54:51'),
(39, 6, 'create', 'rental', 10, 'Created rental agreement for Francis Malabo in Room 1', '180.191.228.104', '2026-03-11 07:56:03'),
(40, 6, 'create', 'rental', 11, 'Created rental agreement for Jomar Mutas in Room 1', '103.62.154.102', '2026-03-12 12:49:59'),
(41, 6, 'create', 'payment', 9, 'Recorded manual payment of 2500 for Rental ID 11', '103.62.154.102', '2026-03-12 12:51:28'),
(42, 8, 'create', 'maintenance', 1, 'Submitted maintenance request for Room 1: Electrical - Guba ang Bombilya', '103.62.154.102', '2026-03-12 12:53:02'),
(43, 6, 'update', 'maintenance', 1, 'Updated maintenance request to status: resolved', '103.62.154.102', '2026-03-12 12:53:39'),
(44, 16, 'create', 'application', 8, 'Applied for Room ID: 8', '103.62.154.102', '2026-03-12 12:55:31'),
(45, 6, 'approve', 'application', 15, 'Approved application ID: 15', '103.62.154.102', '2026-03-12 12:55:49'),
(46, 6, 'create', 'rental', 12, 'Created rental agreement for Rochelle Libra in Room 1', '103.62.154.102', '2026-03-12 12:56:25'),
(47, 16, 'create', 'payment', 10, 'Submitted payment of 2500.00 via gcash. File: 1773320262_111.png', '103.62.154.102', '2026-03-12 12:57:42'),
(48, 6, 'update', 'payment', 10, 'Marked payment ID 10 as paid', '103.62.154.102', '2026-03-12 12:58:13'),
(49, 17, 'create', 'application', 5, 'Applied for Room ID: 5', '103.62.154.102', '2026-03-12 12:59:54'),
(50, 6, 'approve', 'application', 17, 'Approved application ID: 17', '103.62.154.102', '2026-03-12 13:00:25'),
(51, 6, 'create', 'rental', 13, 'Created rental agreement for Raniel deasis in Room 4', '103.187.245.54', '2026-03-12 13:02:08'),
(52, 6, 'create', 'payment', 11, 'Recorded manual payment of 5000 for Rental ID 13', '103.187.245.54', '2026-03-12 13:03:03'),
(53, 6, 'create', 'payment', 12, 'Recorded manual payment of 2510 for Rental ID 10', '103.62.155.190', '2026-03-12 13:04:36'),
(54, 18, 'create', 'application', 4, 'Applied for Room ID: 4', '175.176.77.100', '2026-03-13 02:50:47'),
(55, 6, 'approve', 'application', 19, 'Approved application ID: 19', '175.176.77.100', '2026-03-13 02:51:23'),
(56, 6, 'create', 'rental', 14, 'Created rental agreement for code Warriors in Room 3', '175.176.77.100', '2026-03-13 02:52:43'),
(57, 6, 'create', 'payment', 13, 'Recorded manual payment of 3600 for Rental ID 14', '175.176.77.100', '2026-03-13 02:53:31'),
(58, 6, 'create', 'rental', 15, 'Created rental agreement for Francis Malabo in Room 2', '14.1.64.166', '2026-03-13 03:33:49'),
(59, 6, 'update', 'rental', 14, 'Updated rental details/end date', '14.1.64.166', '2026-03-13 03:34:52'),
(60, 6, 'create', 'payment', 14, 'Recorded manual payment of 2000 for Rental ID 15', '14.1.64.166', '2026-03-13 03:35:33'),
(61, 6, 'create', 'rental', 16, 'Created rental agreement for code Warriors in Room 2', '14.1.64.166', '2026-03-13 04:09:15'),
(62, 6, 'update', 'rental', 15, 'Changed rental status to evicted (Ended)', '14.1.64.166', '2026-03-13 04:11:59'),
(63, 6, 'update', 'rental', 6, 'Changed rental status to evicted (Ended)', '14.1.64.166', '2026-03-13 04:15:24'),
(64, 19, 'create', 'application', 3, 'Applied for Room ID: 3', '14.1.64.166', '2026-03-13 04:17:48'),
(65, 6, 'approve', 'application', 23, 'Approved application ID: 23', '14.1.64.166', '2026-03-13 04:18:43'),
(66, 8, 'create', 'maintenance', 2, 'Submitted maintenance request for Room 1: Electrical - PUNDIR', '14.1.64.166', '2026-03-13 04:22:32'),
(67, 6, 'update', 'maintenance', 2, 'Updated maintenance request to status: resolved', '14.1.64.166', '2026-03-13 04:23:20');

-- --------------------------------------------------------

--
-- Table structure for table `Addresses`
--

CREATE TABLE `Addresses` (
  `address_id` int(11) NOT NULL,
  `street_number` varchar(20) DEFAULT NULL,
  `street_name` varchar(100) NOT NULL,
  `city` varchar(50) NOT NULL,
  `state` varchar(50) NOT NULL,
  `postal_code` varchar(10) NOT NULL,
  `country` varchar(50) DEFAULT 'USA'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Addresses`
--

INSERT INTO `Addresses` (`address_id`, `street_number`, `street_name`, `city`, `state`, `postal_code`, `country`) VALUES
(1, '24', 'Pano olan', 'Sancarlos', 'NIR', '6127', 'Philippines'),
(3, '2', 'Calatrava', 'Calatrava', 'NIR', '6127', 'Philippines');

-- --------------------------------------------------------

--
-- Table structure for table `Announcements`
--

CREATE TABLE `Announcements` (
  `announcement_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `landlord_id` int(11) NOT NULL,
  `title` varchar(200) NOT NULL,
  `content` text NOT NULL,
  `is_pinned` tinyint(1) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Announcements`
--

INSERT INTO `Announcements` (`announcement_id`, `house_id`, `landlord_id`, `title`, `content`, `is_pinned`, `created_at`) VALUES
(1, 1, 6, 'Curfew', 'Curfew Hours is 10pm-4am', 1, '2026-03-05 23:09:23');

-- --------------------------------------------------------

--
-- Table structure for table `Applications`
--

CREATE TABLE `Applications` (
  `application_id` int(11) NOT NULL,
  `room_id` int(11) NOT NULL,
  `boarder_id` int(11) NOT NULL,
  `application_date` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reviewed_by` int(11) DEFAULT NULL,
  `review_date` timestamp NULL DEFAULT NULL,
  `notes` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Applications`
--

INSERT INTO `Applications` (`application_id`, `room_id`, `boarder_id`, `application_date`, `status`, `reviewed_by`, `review_date`, `notes`) VALUES
(1, 2, 13, '2026-03-08 02:37:51', 'approved', 6, NULL, NULL),
(2, 2, 8, '2026-03-08 03:02:59', 'approved', 6, NULL, NULL),
(3, 2, 8, '2026-03-08 03:16:12', 'approved', 6, NULL, NULL),
(4, 2, 12, '2026-03-08 03:26:51', 'approved', 6, NULL, NULL),
(5, 2, 8, '2026-03-08 04:08:47', 'approved', 6, NULL, NULL),
(6, 2, 8, '2026-03-08 12:24:40', 'approved', 6, NULL, NULL),
(7, 3, 11, '2026-03-08 13:53:08', 'approved', 6, NULL, NULL),
(8, 3, 11, '2026-03-08 13:54:26', 'approved', 6, NULL, NULL),
(9, 2, 12, '2026-03-09 01:03:48', 'approved', 6, NULL, NULL),
(10, 6, 15, '2026-03-09 11:22:42', 'approved', 6, NULL, NULL),
(11, 6, 15, '2026-03-09 11:23:38', 'approved', 6, NULL, NULL),
(12, 7, 14, '2026-03-10 10:31:02', 'approved', 6, NULL, NULL),
(13, 7, 14, '2026-03-11 07:56:03', 'approved', 6, NULL, NULL),
(14, 2, 13, '2026-03-12 12:49:59', 'approved', 6, NULL, NULL),
(15, 8, 16, '2026-03-12 12:55:31', 'approved', 6, NULL, NULL),
(16, 7, 16, '2026-03-12 12:56:25', 'approved', 6, NULL, NULL),
(17, 5, 17, '2026-03-12 12:59:54', 'approved', 6, NULL, NULL),
(18, 5, 17, '2026-03-12 13:02:08', 'approved', 6, NULL, NULL),
(19, 4, 18, '2026-03-13 02:50:47', 'approved', 6, NULL, NULL),
(20, 4, 18, '2026-03-13 02:52:43', 'approved', 6, NULL, NULL),
(21, 3, 14, '2026-03-13 03:33:49', 'approved', 6, NULL, NULL),
(22, 8, 18, '2026-03-13 04:09:15', 'approved', 6, NULL, NULL),
(23, 3, 19, '2026-03-13 04:17:48', 'approved', 6, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `BoardingHouses`
--

CREATE TABLE `BoardingHouses` (
  `house_id` int(11) NOT NULL,
  `landlord_id` int(11) NOT NULL,
  `address_id` int(11) NOT NULL,
  `house_name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `amenities` text DEFAULT NULL,
  `image_url` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `BoardingHouses`
--

INSERT INTO `BoardingHouses` (`house_id`, `landlord_id`, `address_id`, `house_name`, `description`, `amenities`, `image_url`, `is_active`, `created_at`) VALUES
(1, 6, 1, 'Drunreb\'s Residence', 'Two Story Building\r\n', 'Wifi/Billiard', NULL, 1, '2026-03-05 11:05:31'),
(3, 6, 3, 'Perez Residence', 'Two story apartment ', 'Wifi/Own cr/Kitchen, etc. ', NULL, 1, '2026-03-09 00:51:21');

-- --------------------------------------------------------

--
-- Table structure for table `MaintenanceRequests`
--

CREATE TABLE `MaintenanceRequests` (
  `request_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `room_id` int(11) DEFAULT NULL,
  `reported_by` int(11) NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` text NOT NULL,
  `priority` enum('low','medium','high') DEFAULT 'medium',
  `status` enum('open','in_progress','resolved','closed') DEFAULT 'open',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `resolved_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `MaintenanceRequests`
--

INSERT INTO `MaintenanceRequests` (`request_id`, `house_id`, `room_id`, `reported_by`, `assigned_to`, `category`, `description`, `priority`, `status`, `created_at`, `resolved_at`) VALUES
(1, 1, 2, 8, 6, 'Electrical', 'Guba ang Bombilya', 'medium', 'resolved', '2026-03-12 12:53:02', '2026-03-12 12:53:39'),
(2, 1, 2, 8, 6, 'Electrical', 'PUNDIR', 'medium', 'resolved', '2026-03-13 04:22:32', '2026-03-13 04:23:20');

-- --------------------------------------------------------

--
-- Table structure for table `Payments`
--

CREATE TABLE `Payments` (
  `payment_id` int(11) NOT NULL,
  `rental_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `penalty_fee` decimal(10,2) DEFAULT 0.00,
  `due_date` date NOT NULL,
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `payment_method` enum('cash','bank_transfer','gcash','check') NOT NULL,
  `proof_image_url` varchar(255) DEFAULT NULL,
  `status` enum('paid','pending','overdue','failed') DEFAULT 'pending',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Payments`
--

INSERT INTO `Payments` (`payment_id`, `rental_id`, `amount`, `penalty_fee`, `due_date`, `payment_date`, `payment_method`, `proof_image_url`, `status`, `created_at`) VALUES
(1, 3, 2500.00, 0.00, '2026-02-08', '2026-03-08 03:53:39', 'cash', NULL, 'paid', '2026-03-08 03:43:04'),
(2, 3, 2540.00, 40.00, '2026-03-01', '2026-03-08 03:54:03', 'cash', NULL, 'paid', '2026-03-08 03:54:03'),
(3, 4, 2500.00, 0.00, '2026-05-08', '2026-03-08 12:23:37', 'cash', NULL, 'paid', '2026-03-08 12:23:37'),
(4, 6, 2000.00, 0.00, '2026-05-08', '2026-03-09 01:00:53', 'cash', NULL, 'paid', '2026-03-08 13:54:53'),
(5, 3, 2500.00, 0.00, '2026-04-01', '2026-03-09 01:04:41', 'cash', NULL, 'paid', '2026-03-09 01:04:41'),
(6, 8, 2500.00, 0.00, '2026-05-09', '2026-03-09 11:23:59', 'cash', NULL, 'paid', '2026-03-09 11:23:59'),
(7, 9, 2520.00, 20.00, '2026-05-10', '2026-03-10 10:31:30', 'cash', NULL, 'paid', '2026-03-10 10:31:30'),
(8, 9, 2500.00, 0.00, '2026-06-01', '2026-03-11 07:53:57', 'cash', NULL, 'paid', '2026-03-11 07:53:57'),
(9, 11, 2500.00, 0.00, '2026-04-12', '2026-03-12 12:51:28', 'gcash', NULL, 'paid', '2026-03-12 12:51:28'),
(10, 12, 2500.00, 0.00, '2026-03-12', '2026-03-12 12:58:13', 'gcash', '1773320262_111.png', 'paid', '2026-03-12 12:57:42'),
(11, 13, 5000.00, 0.00, '2026-03-16', '2026-03-12 13:03:03', 'cash', NULL, 'paid', '2026-03-12 13:03:03'),
(12, 10, 2510.00, 10.00, '2026-04-12', '2026-03-12 13:04:36', 'cash', NULL, 'paid', '2026-03-12 13:04:36'),
(13, 14, 3600.00, 600.00, '2026-03-02', '2026-03-13 02:53:31', 'cash', NULL, 'paid', '2026-03-13 02:53:31'),
(14, 15, 2000.00, 0.00, '2026-01-13', '2026-03-13 03:35:33', 'cash', NULL, 'paid', '2026-03-13 03:35:33');

-- --------------------------------------------------------

--
-- Table structure for table `Rentals`
--

CREATE TABLE `Rentals` (
  `rental_id` int(11) NOT NULL,
  `application_id` int(11) DEFAULT NULL,
  `room_id` int(11) NOT NULL,
  `boarder_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `deposit_amount` decimal(10,2) DEFAULT 0.00,
  `status` enum('active','completed','terminated','evicted','expired') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `Rentals`
--

INSERT INTO `Rentals` (`rental_id`, `application_id`, `room_id`, `boarder_id`, `start_date`, `end_date`, `deposit_amount`, `status`, `created_at`) VALUES
(2, 3, 2, 8, '2026-01-01', '2026-04-01', 5000.00, 'completed', '2026-03-08 03:16:12'),
(3, 4, 2, 12, '2026-01-08', '2026-04-01', 5000.00, 'active', '2026-03-08 03:26:51'),
(4, 5, 2, 8, '2026-03-08', '2026-04-01', 5000.00, 'active', '2026-03-08 04:08:47'),
(5, 6, 2, 8, '2026-03-08', '2026-11-08', 5000.00, 'active', '2026-03-08 12:24:40'),
(6, 8, 3, 11, '2026-03-08', '2026-04-08', 4000.00, 'evicted', '2026-03-08 13:54:26'),
(7, 9, 2, 12, '2026-03-08', '2026-04-10', 5000.00, 'active', '2026-03-09 01:03:48'),
(8, 11, 6, 15, '2026-03-09', '2026-04-09', 5000.00, 'active', '2026-03-09 11:23:38'),
(9, 12, 7, 14, '2026-03-10', '2026-03-10', 5000.00, 'completed', '2026-03-10 10:31:02'),
(10, 13, 7, 14, '2026-03-11', '2026-04-11', 5000.00, 'active', '2026-03-11 07:56:03'),
(11, 14, 2, 13, '2026-03-12', '2026-04-12', 5000.00, 'active', '2026-03-12 12:49:59'),
(12, 16, 7, 16, '2026-03-12', '2026-04-12', 5000.00, 'active', '2026-03-12 12:56:25'),
(13, 18, 5, 17, '2026-02-12', '2026-03-15', 10000.00, 'active', '2026-03-12 13:02:08'),
(14, 20, 4, 18, '2026-01-02', '2026-06-05', 6000.00, 'active', '2026-03-13 02:52:43'),
(15, 21, 3, 14, '2025-11-13', '2026-03-13', 4000.00, 'evicted', '2026-03-13 03:33:49'),
(16, 22, 8, 18, '2026-03-13', '2026-04-13', 6000.00, 'active', '2026-03-13 04:09:15');

-- --------------------------------------------------------

--
-- Table structure for table `Rooms`
--

CREATE TABLE `Rooms` (
  `room_id` int(11) NOT NULL,
  `house_id` int(11) NOT NULL,
  `room_number` varchar(20) NOT NULL,
  `floor_number` int(11) DEFAULT 1,
  `price_per_month` decimal(10,2) NOT NULL,
  `capacity` int(11) NOT NULL,
  `current_occupancy` int(11) DEFAULT 0,
  `room_type` enum('single','shared','studio') DEFAULT 'shared',
  `amenities` text DEFAULT NULL,
  `status` enum('available','occupied','maintenance') DEFAULT 'available',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `room_image` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Rooms`
--

INSERT INTO `Rooms` (`room_id`, `house_id`, `room_number`, `floor_number`, `price_per_month`, `capacity`, `current_occupancy`, `room_type`, `amenities`, `status`, `created_at`, `room_image`) VALUES
(2, 1, '1', 2, 2500.00, 5, 5, 'shared', 'Wifi/Pool', 'occupied', '2026-03-06 11:20:34', 'uploads/rooms/1772937788_room1.jpg'),
(3, 1, '2', 2, 2000.00, 5, 0, 'shared', 'Wifi', 'available', '2026-03-08 13:51:27', 'uploads/rooms/1772977887_1000009585.jpg'),
(4, 1, '3', 2, 3000.00, 3, 1, 'shared', 'Wifi/Pool/Own Cr', 'occupied', '2026-03-08 15:26:09', 'uploads/rooms/1772983569_1000009588.jpg'),
(5, 1, '4', 2, 5000.00, 3, 1, 'shared', 'Wifi/Own Cr', 'occupied', '2026-03-08 15:27:11', 'uploads/rooms/1772983631_1000009587.jpg'),
(6, 1, '5', 1, 2500.00, 1, 1, 'single', 'Wifi/Own Cr', 'occupied', '2026-03-08 15:28:05', 'uploads/rooms/1772983685_1000009586.jpg'),
(7, 3, '1', 1, 2500.00, 4, 2, 'single', 'Wifi/Own Cr', 'occupied', '2026-03-09 00:56:25', 'uploads/rooms/1773017785_1000009592.jpg'),
(8, 3, '2', 1, 3000.00, 3, 1, 'shared', 'Wifi/Own Cr', 'occupied', '2026-03-09 00:57:00', 'uploads/rooms/1773017820_1000009591.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `Users`
--

CREATE TABLE `Users` (
  `user_id` int(11) NOT NULL,
  `first_name` varchar(50) NOT NULL,
  `last_name` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `role` enum('admin','landlord','boarder') NOT NULL,
  `status` enum('active','suspended','pending') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `Users`
--

INSERT INTO `Users` (`user_id`, `first_name`, `last_name`, `email`, `password_hash`, `phone_number`, `role`, `status`, `created_at`, `updated_at`) VALUES
(6, 'Drunreb', 'Perez', 'admin@csr.com', '1', NULL, 'landlord', 'active', '2026-03-01 07:34:08', '2026-03-01 08:28:18'),
(7, 'Drunreb', 'Perez', 'perez@admin.com', '1', '09101882719', 'admin', 'active', '2026-03-01 08:51:42', '2026-03-09 11:16:48'),
(8, 'Earl Vincent Son', 'Esguerra', 'earl@admin.com', '1', '09543200627', 'boarder', 'active', '2026-03-02 11:28:45', '2026-03-08 04:20:46'),
(11, 'Drunreb', 'Perez', 'drunreb@csr.com', '1', '09672287563', 'boarder', 'active', '2026-03-05 11:07:13', '2026-03-05 11:07:13'),
(12, 'Drun', 'Perez', 'drun@admin.com', '1', '09672287569', 'boarder', 'active', '2026-03-06 04:12:58', '2026-03-06 04:12:58'),
(13, 'Jomar', 'Mutas', 'jomar@admin.com', '1', '09672287653', 'boarder', 'active', '2026-03-06 11:18:55', '2026-03-06 11:18:55'),
(14, 'Francis', 'Malabo', 'Francis@admin.com', '1', '09672287569', 'boarder', 'active', '2026-03-08 15:33:15', '2026-03-08 15:33:15'),
(15, 'Ralph', 'Vidal', 'ralph@admin.com', '1', '09101882710', 'boarder', 'active', '2026-03-09 11:22:15', '2026-03-09 11:22:15'),
(16, 'Rochelle', 'Libra', 'rochelle@admin.com', '1', '09672272653', 'boarder', 'active', '2026-03-12 12:54:54', '2026-03-12 12:54:54'),
(17, 'Raniel', 'deasis', 'raniel@admin.com', '1', '09672272657', 'boarder', 'active', '2026-03-12 12:59:09', '2026-03-12 12:59:09'),
(18, 'code', 'Warriors', 'cw@admin.com', '1', '09672287568', 'boarder', 'active', '2026-03-13 02:50:01', '2026-03-13 02:50:01'),
(19, 'Raniel', 'De Asis', 'ran@csr.com', '1', '09672287652', 'boarder', 'active', '2026-03-13 04:16:30', '2026-03-13 04:16:30');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `ActivityLogs`
--
ALTER TABLE `ActivityLogs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `Addresses`
--
ALTER TABLE `Addresses`
  ADD PRIMARY KEY (`address_id`);

--
-- Indexes for table `Announcements`
--
ALTER TABLE `Announcements`
  ADD PRIMARY KEY (`announcement_id`),
  ADD KEY `house_id` (`house_id`),
  ADD KEY `landlord_id` (`landlord_id`);

--
-- Indexes for table `Applications`
--
ALTER TABLE `Applications`
  ADD PRIMARY KEY (`application_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `boarder_id` (`boarder_id`),
  ADD KEY `reviewed_by` (`reviewed_by`);

--
-- Indexes for table `BoardingHouses`
--
ALTER TABLE `BoardingHouses`
  ADD PRIMARY KEY (`house_id`),
  ADD KEY `landlord_id` (`landlord_id`),
  ADD KEY `address_id` (`address_id`);

--
-- Indexes for table `MaintenanceRequests`
--
ALTER TABLE `MaintenanceRequests`
  ADD PRIMARY KEY (`request_id`),
  ADD KEY `house_id` (`house_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `reported_by` (`reported_by`),
  ADD KEY `assigned_to` (`assigned_to`);

--
-- Indexes for table `Payments`
--
ALTER TABLE `Payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `rental_id` (`rental_id`);

--
-- Indexes for table `Rentals`
--
ALTER TABLE `Rentals`
  ADD PRIMARY KEY (`rental_id`),
  ADD KEY `room_id` (`room_id`),
  ADD KEY `boarder_id` (`boarder_id`),
  ADD KEY `application_id` (`application_id`);

--
-- Indexes for table `Rooms`
--
ALTER TABLE `Rooms`
  ADD PRIMARY KEY (`room_id`),
  ADD KEY `house_id` (`house_id`);

--
-- Indexes for table `Users`
--
ALTER TABLE `Users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `ActivityLogs`
--
ALTER TABLE `ActivityLogs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=68;

--
-- AUTO_INCREMENT for table `Addresses`
--
ALTER TABLE `Addresses`
  MODIFY `address_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `Announcements`
--
ALTER TABLE `Announcements`
  MODIFY `announcement_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `Applications`
--
ALTER TABLE `Applications`
  MODIFY `application_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `BoardingHouses`
--
ALTER TABLE `BoardingHouses`
  MODIFY `house_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `MaintenanceRequests`
--
ALTER TABLE `MaintenanceRequests`
  MODIFY `request_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `Payments`
--
ALTER TABLE `Payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `Rentals`
--
ALTER TABLE `Rentals`
  MODIFY `rental_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `Rooms`
--
ALTER TABLE `Rooms`
  MODIFY `room_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `Users`
--
ALTER TABLE `Users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `ActivityLogs`
--
ALTER TABLE `ActivityLogs`
  ADD CONSTRAINT `ActivityLogs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Announcements`
--
ALTER TABLE `Announcements`
  ADD CONSTRAINT `Announcements_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `BoardingHouses` (`house_id`),
  ADD CONSTRAINT `Announcements_ibfk_2` FOREIGN KEY (`landlord_id`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Applications`
--
ALTER TABLE `Applications`
  ADD CONSTRAINT `Applications_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`room_id`),
  ADD CONSTRAINT `Applications_ibfk_2` FOREIGN KEY (`boarder_id`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `Applications_ibfk_3` FOREIGN KEY (`reviewed_by`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `BoardingHouses`
--
ALTER TABLE `BoardingHouses`
  ADD CONSTRAINT `BoardingHouses_ibfk_1` FOREIGN KEY (`landlord_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `BoardingHouses_ibfk_2` FOREIGN KEY (`address_id`) REFERENCES `Addresses` (`address_id`) ON DELETE CASCADE;

--
-- Constraints for table `MaintenanceRequests`
--
ALTER TABLE `MaintenanceRequests`
  ADD CONSTRAINT `MaintenanceRequests_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `BoardingHouses` (`house_id`),
  ADD CONSTRAINT `MaintenanceRequests_ibfk_2` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`room_id`),
  ADD CONSTRAINT `MaintenanceRequests_ibfk_3` FOREIGN KEY (`reported_by`) REFERENCES `Users` (`user_id`),
  ADD CONSTRAINT `MaintenanceRequests_ibfk_4` FOREIGN KEY (`assigned_to`) REFERENCES `Users` (`user_id`);

--
-- Constraints for table `Payments`
--
ALTER TABLE `Payments`
  ADD CONSTRAINT `Payments_ibfk_1` FOREIGN KEY (`rental_id`) REFERENCES `Rentals` (`rental_id`) ON DELETE CASCADE;

--
-- Constraints for table `Rentals`
--
ALTER TABLE `Rentals`
  ADD CONSTRAINT `Rentals_ibfk_1` FOREIGN KEY (`room_id`) REFERENCES `Rooms` (`room_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Rentals_ibfk_2` FOREIGN KEY (`boarder_id`) REFERENCES `Users` (`user_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `Rentals_ibfk_3` FOREIGN KEY (`application_id`) REFERENCES `Applications` (`application_id`) ON DELETE SET NULL;

--
-- Constraints for table `Rooms`
--
ALTER TABLE `Rooms`
  ADD CONSTRAINT `Rooms_ibfk_1` FOREIGN KEY (`house_id`) REFERENCES `BoardingHouses` (`house_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
