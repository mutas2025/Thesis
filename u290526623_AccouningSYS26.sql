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
-- Database: `u290526623_AccouningSYS26`
--

-- --------------------------------------------------------

--
-- Table structure for table `bank`
--

CREATE TABLE `bank` (
  `id` int(11) NOT NULL,
  `bankname` varchar(100) NOT NULL,
  `accountno` varchar(30) NOT NULL,
  `accountname` varchar(100) NOT NULL,
  `branch` varchar(50) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `transaction_type` enum('deposit','withdrawal') NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `transaction_date` date NOT NULL,
  `reference_no` varchar(30) DEFAULT NULL,
  `processed_by` int(11) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `chart_of_accounts`
--

CREATE TABLE `chart_of_accounts` (
  `id` int(11) NOT NULL,
  `account_code` varchar(10) NOT NULL,
  `account_category` varchar(100) NOT NULL DEFAULT 'Other',
  `account_name` varchar(100) NOT NULL,
  `account_type` enum('asset','liability','equity','revenue','expense') NOT NULL,
  `normal_balance` enum('debit','credit') NOT NULL,
  `parent_account_id` int(11) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `chart_of_accounts`
--

INSERT INTO `chart_of_accounts` (`id`, `account_code`, `account_category`, `account_name`, `account_type`, `normal_balance`, `parent_account_id`, `description`, `is_active`, `created_at`) VALUES
(1, '1001', 'Cash', 'Cash on Hand', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(2, '1002', 'Cash', 'Cash in Bank - BPI', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(3, '1003', 'Other Fees', 'Accounts Receivable', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(4, '1004', 'Operating Expense', 'Office Supplies Inventory', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(5, '1005', 'Operating Expense', 'Office Equipment', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(6, '1006', 'Operating Expense', 'Furniture & Fixtures', 'asset', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(7, '2001', 'Operating Expense', 'Accounts Payable', 'liability', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(8, '2002', 'Operating Expense', 'Accrued Salaries', 'liability', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(9, '3001', 'None', 'Capital / Fund Balance', 'equity', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(10, '3002', 'Other Income', 'Retained Earnings', 'equity', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(11, '4001', 'Tuition Fee', 'Tuition Fee Income', 'revenue', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(12, '4002', 'Miscellaneous Fee', 'Miscellaneous Fee Income', 'revenue', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(13, '4003', 'Other Fees', 'Laboratory Fees', 'revenue', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(14, '4004', 'Other Fees', 'Registration Fees', 'revenue', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(15, '4005', 'Other Income', 'Donations & Grants', 'revenue', 'credit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(16, '5001', 'Operating Expense', 'Salaries & Wages', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(17, '5002', 'Operating Expense', 'Employee Benefits', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(18, '5003', 'Operating Expense', 'Utilities (Meralco/Water)', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(19, '5004', 'Operating Expense', 'Repairs & Maintenance', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(20, '5005', 'Operating Expense', 'Office Supplies Expense', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(21, '5006', 'Education and Training Expense', 'Seminars & Trainings', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(22, '5007', 'Miscellaneous Expense', 'Representation Expenses', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41'),
(23, '5008', 'Other Expense', 'Depreciation Expense', 'expense', 'debit', NULL, NULL, 1, '2026-03-10 04:48:41');

-- --------------------------------------------------------

--
-- Table structure for table `collections`
--

CREATE TABLE `collections` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `receipt_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `collected_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `payment_method` varchar(50) NOT NULL,
  `voucher_no` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'active',
  `disbursed_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employee`
--

CREATE TABLE `employee` (
  `id` int(11) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `position` varchar(50) DEFAULT NULL,
  `username` varchar(30) NOT NULL,
  `email` varchar(30) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employee`
--

INSERT INTO `employee` (`id`, `lastname`, `firstname`, `position`, `username`, `email`, `password_hash`, `is_active`, `created_at`) VALUES
(1, 'Malabo', 'Francis', 'Accountant', 'francis', 'fmalabo@gmail.com', '$2y$10$zT52Ai3mFYhlHvKJqN4cD.vfZFLAdm/WGeGqA4uOS3GAzeAVt.Dea', 1, '2026-03-10 04:26:08'),
(4, 'Eslais', 'KENN', 'auditor', 'kenn', 'kenn@gmail.com', '$2y$10$/n9Fm.pQ3VLX4OFFzO0q8.tA/0xwTmDAOCLoeMRhBOh5ARovQwCkG', 1, '2026-03-10 10:23:12'),
(5, 'malabo', 'francis', 'admin', 'francis11', '', '$2y$10$kP.sNgsqmapGOBUDO3jIfOKrId.0VVWOEZZo9mimU4bxFdbClsCsC', 1, '2026-03-12 01:57:36');

-- --------------------------------------------------------

--
-- Table structure for table `system_settings`
--

CREATE TABLE `system_settings` (
  `id` int(11) NOT NULL,
  `setting_key` varchar(100) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_settings`
--

INSERT INTO `system_settings` (`id`, `setting_key`, `setting_value`, `description`, `created_at`, `updated_at`) VALUES
(1, 'company_name', 'Accounting System', NULL, '2025-08-27 11:11:03', '2026-03-10 04:39:55'),
(2, 'company_address', 'San Carlos City, Negros Island Region', NULL, '2025-08-27 11:11:03', '2026-03-10 04:40:15'),
(3, 'company_email', 'accounting@gmail.com', NULL, '2025-08-27 11:11:03', '2026-03-10 04:39:55'),
(4, 'company_phone', '09101882719', NULL, '2025-08-27 11:11:03', '2026-03-10 04:39:55'),
(5, 'currency', 'PHP', NULL, '2025-08-27 11:11:03', '2025-08-27 11:11:03'),
(6, 'currency_symbol', '₱', NULL, '2025-08-27 11:11:03', '2025-08-27 11:11:03');

-- --------------------------------------------------------

--
-- Table structure for table `transactions`
--

CREATE TABLE `transactions` (
  `id` int(11) NOT NULL,
  `transaction_date` date NOT NULL,
  `reference_no` varchar(30) NOT NULL,
  `description` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `department_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `transactions`
--

INSERT INTO `transactions` (`id`, `transaction_date`, `reference_no`, `description`, `created_by`, `created_at`, `department_id`) VALUES
(1, '2026-02-18', 'TRX-2023-001', 'Tuition Fee Payment - October', 1, '2026-03-10 04:49:16', NULL),
(2, '2026-02-23', 'TRX-2023-002', 'Salaries for October 2023', 1, '2026-03-10 04:49:16', NULL),
(3, '2026-02-26', 'TRX-2023-003', 'Meralco Bill - Sept 2023', 1, '2026-03-10 04:49:16', NULL),
(4, '2026-02-28', 'TRX-2023-004', 'Office Supplies Purchase', 1, '2026-03-10 04:49:16', NULL),
(5, '2026-03-02', 'TRX-2023-005', 'Misc Fees Collection', 1, '2026-03-10 04:49:16', NULL),
(6, '2026-03-05', 'TRX-2023-006', 'Purchase of 5 Computer Units', 1, '2026-03-10 04:49:16', NULL),
(7, '2026-03-08', 'TRX-2023-007', 'Partial Payment for Computers', 1, '2026-03-10 04:49:16', NULL),
(8, '2026-03-09', 'TRX-2023-008', 'Faculty Dev Seminar', 1, '2026-03-10 04:49:16', NULL),
(9, '2026-03-10', 'TRX-2023-009', 'Cash Deposit - Nov Collections', 1, '2026-03-10 04:49:16', NULL),
(10, '2026-03-10', 'TRX-2023-010', 'Alumni Donation - Building Fund', 1, '2026-03-10 04:49:16', NULL),
(11, '2026-02-19', 'TRX-2023-011', 'Tuition Fee - Cheque Deposit', 1, '2026-03-10 04:50:54', NULL),
(12, '2026-02-20', 'TRX-2023-012', 'Maynilad Water Bill', 1, '2026-03-10 04:50:54', NULL),
(13, '2026-02-22', 'TRX-2023-013', 'Purchase of 50 Chairs', 1, '2026-03-10 04:50:54', NULL),
(14, '2026-02-24', 'TRX-2023-014', 'Registration Fees Collection', 1, '2026-03-10 04:50:54', NULL),
(15, '2026-02-26', 'TRX-2023-015', 'AC Repair - Room 304', 1, '2026-03-10 04:50:54', NULL),
(16, '2026-02-28', 'TRX-2023-016', 'PLDT Fibr Bill', 1, '2026-03-10 04:50:54', NULL),
(17, '2026-03-02', 'TRX-2023-017', 'Remittance of Govt Benefits', 1, '2026-03-10 04:50:54', NULL),
(18, '2026-03-04', 'TRX-2023-018', 'Purchase of Math Workbooks', 1, '2026-03-10 04:50:54', NULL),
(19, '2026-03-06', 'TRX-2023-019', 'Payment for Janitorial Services', 1, '2026-03-10 04:50:54', NULL),
(20, '2026-03-08', 'TRX-2023-020', 'Lab Fees - Science Dept', 1, '2026-03-10 04:50:54', NULL),
(21, '2024-02-01', 'TRX-2024-021', 'Salaries - January 2024', 1, '2026-03-10 04:53:37', NULL),
(22, '2024-02-02', 'TRX-2024-022', 'Rent Prepayment - Feb 2024', 1, '2026-03-10 04:53:37', NULL),
(23, '2024-02-05', 'TRX-2024-023', 'Printer Toners Purchase', 1, '2026-03-10 04:53:37', NULL),
(24, '2024-02-08', 'TRX-2024-024', 'Tuition Collection - Feb Batch', 1, '2026-03-10 04:53:37', NULL),
(25, '2024-02-10', 'TRX-2024-025', 'PLDT Internet Bill', 1, '2026-03-10 04:53:37', NULL),
(26, '2024-02-14', 'TRX-2024-026', 'Valentines Event Expenses', 1, '2026-03-10 04:53:37', NULL),
(27, '2024-02-15', 'TRX-2024-027', 'ID Card Replacement Fees', 1, '2026-03-10 04:53:37', NULL),
(28, '2024-02-16', 'TRX-2024-028', 'Deposit of Misc Fees', 1, '2026-03-10 04:53:37', NULL),
(29, '2024-02-20', 'TRX-2024-029', 'Security Services Monthly', 1, '2026-03-10 04:53:37', NULL),
(30, '2024-02-25', 'TRX-2024-030', 'Whiteboards for Classrooms', 1, '2026-03-10 04:53:37', NULL),
(31, '2024-01-02', 'TRX-2024-031', 'Salaries - December 2023', 1, '2026-03-10 04:56:20', NULL),
(32, '2024-01-05', 'TRX-2024-032', 'Utility Bill - December', 1, '2026-03-10 04:56:20', NULL),
(33, '2024-01-08', 'TRX-2024-033', 'Tuition Downpayment', 1, '2026-03-10 04:56:20', NULL),
(34, '2024-01-10', 'TRX-2024-034', 'Cleaning Supplies', 1, '2026-03-10 04:56:20', NULL),
(35, '2024-01-15', 'TRX-2024-035', '13th Month Pay', 1, '2026-03-10 04:56:20', NULL),
(36, '2024-01-18', 'TRX-2024-036', 'Lab Fees Collection', 1, '2026-03-10 04:56:20', NULL),
(37, '2024-01-20', 'TRX-2024-037', 'Gate Repair & Paint', 1, '2026-03-10 04:56:20', NULL),
(38, '2024-01-22', 'TRX-2024-038', 'Epson Projector', 1, '2026-03-10 04:56:20', NULL),
(39, '2024-01-25', 'TRX-2024-039', 'Corporate Sponsorship', 1, '2026-03-10 04:56:20', NULL),
(40, '2024-01-31', 'TRX-2024-040', 'Bank Service Charges', 1, '2026-03-10 04:56:20', NULL),
(41, '2026-03-10', 'TXN-0001', 'PINNING', 1, '2026-03-10 10:21:07', NULL),
(42, '2026-03-10', 'TXN-0002', 'title hearing', 4, '2026-03-10 10:25:39', NULL),
(45, '2026-03-13', 'TXN-0003', 'tuition fee', 1, '2026-03-13 00:56:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `transaction_entries`
--

CREATE TABLE `transaction_entries` (
  `id` int(11) NOT NULL,
  `transaction_id` int(11) NOT NULL,
  `account_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `entry_type` enum('debit','credit') NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `transaction_entries`
--

INSERT INTO `transaction_entries` (`id`, `transaction_id`, `account_id`, `amount`, `entry_type`, `description`, `created_at`) VALUES
(1, 1, 1, 15000.00, 'debit', 'Cash received', '2026-03-10 04:49:16'),
(2, 1, 11, 15000.00, 'credit', 'Tuition Fee', '2026-03-10 04:49:16'),
(3, 2, 15, 150000.00, 'debit', 'Monthly Salary', '2026-03-10 04:49:16'),
(4, 2, 2, 150000.00, 'credit', 'BPI Transfer', '2026-03-10 04:49:16'),
(5, 3, 17, 12500.00, 'debit', 'Electricity Consumption', '2026-03-10 04:49:16'),
(6, 3, 2, 12500.00, 'credit', 'BPI Payment', '2026-03-10 04:49:16'),
(7, 4, 19, 3200.00, 'debit', 'Paper, Ink, Pens', '2026-03-10 04:49:16'),
(8, 4, 1, 3200.00, 'credit', 'Cash Payment', '2026-03-10 04:49:16'),
(9, 5, 2, 45000.00, 'debit', 'Deposit of Misc Fees', '2026-03-10 04:49:16'),
(10, 5, 12, 45000.00, 'credit', 'Lab, Library, ID Fees', '2026-03-10 04:49:16'),
(11, 6, 5, 250000.00, 'debit', 'Computer Lab Equipment', '2026-03-10 04:49:16'),
(12, 6, 7, 250000.00, 'credit', 'Credit with PC Express', '2026-03-10 04:49:16'),
(13, 7, 7, 50000.00, 'debit', 'Payment to Supplier', '2026-03-10 04:49:16'),
(14, 7, 2, 50000.00, 'credit', 'BPI Check Payment', '2026-03-10 04:49:16'),
(15, 8, 20, 10000.00, 'debit', 'Seminar Fees & Venue', '2026-03-10 04:49:16'),
(16, 8, 2, 10000.00, 'credit', 'Bank Transfer', '2026-03-10 04:49:16'),
(17, 9, 2, 100000.00, 'debit', 'Deposit to BPI', '2026-03-10 04:49:16'),
(18, 9, 1, 100000.00, 'credit', 'Withdrawal', '2026-03-10 04:49:16'),
(19, 10, 2, 500000.00, 'debit', 'Donation Check', '2026-03-10 04:49:16'),
(20, 10, 14, 500000.00, 'credit', 'Building Fund', '2026-03-10 04:49:16'),
(21, 11, 2, 25000.00, 'debit', 'Cheque Deposit', '2026-03-10 04:50:54'),
(22, 11, 11, 25000.00, 'credit', 'Tuition Fee', '2026-03-10 04:50:54'),
(23, 12, 18, 4500.00, 'debit', 'Water Consumption', '2026-03-10 04:50:54'),
(24, 12, 2, 4500.00, 'credit', 'BPI Payment', '2026-03-10 04:50:54'),
(25, 13, 6, 45000.00, 'debit', 'New Classroom Chairs', '2026-03-10 04:50:54'),
(26, 13, 2, 45000.00, 'credit', 'Supplier Payment', '2026-03-10 04:50:54'),
(27, 14, 1, 12000.00, 'debit', 'Cash Collection', '2026-03-10 04:50:54'),
(28, 14, 14, 12000.00, 'credit', 'Enrollment', '2026-03-10 04:50:54'),
(29, 15, 19, 8000.00, 'debit', 'AC Parts & Labor', '2026-03-10 04:50:54'),
(30, 15, 1, 8000.00, 'credit', 'Cash Payment', '2026-03-10 04:50:54'),
(31, 16, 18, 3500.00, 'debit', 'Internet & Fiber', '2026-03-10 04:50:54'),
(32, 16, 2, 3500.00, 'credit', 'Auto Debit', '2026-03-10 04:50:54'),
(33, 17, 17, 35000.00, 'debit', 'SSS, PhilHealth, Pag-IBIG', '2026-03-10 04:50:54'),
(34, 17, 2, 35000.00, 'credit', 'Bank Transfer', '2026-03-10 04:50:54'),
(35, 18, 20, 15000.00, 'debit', 'Books & Workbooks', '2026-03-10 04:50:54'),
(36, 18, 2, 15000.00, 'credit', 'Supplier Payment', '2026-03-10 04:50:54'),
(37, 19, 7, 10000.00, 'debit', 'Settling AP', '2026-03-10 04:50:54'),
(38, 19, 2, 10000.00, 'credit', 'Check Release', '2026-03-10 04:50:54'),
(39, 20, 1, 5500.00, 'debit', 'Cash Collection', '2026-03-10 04:50:54'),
(40, 20, 13, 5500.00, 'credit', 'Lab Usage', '2026-03-10 04:50:54'),
(41, 21, 15, 150000.00, 'debit', 'Monthly Salary', '2026-03-10 04:53:37'),
(42, 21, 2, 150000.00, 'credit', 'BPI Transfer', '2026-03-10 04:53:37'),
(43, 22, 4, 20000.00, 'debit', 'Prepaid Rent', '2026-03-10 04:53:37'),
(44, 22, 2, 20000.00, 'credit', 'Check Payment', '2026-03-10 04:53:37'),
(45, 23, 20, 4200.00, 'debit', 'Canon & HP Toners', '2026-03-10 04:53:37'),
(46, 23, 1, 4200.00, 'credit', 'Cash Payment', '2026-03-10 04:53:37'),
(47, 24, 2, 85000.00, 'debit', 'Bank Deposit', '2026-03-10 04:53:37'),
(48, 24, 11, 85000.00, 'credit', 'Tuition Fees', '2026-03-10 04:53:37'),
(49, 25, 18, 3500.00, 'debit', 'Internet Service', '2026-03-10 04:53:37'),
(50, 25, 2, 3500.00, 'credit', 'Auto Debit', '2026-03-10 04:53:37'),
(51, 26, 21, 5000.00, 'debit', 'Decorations & Snacks', '2026-03-10 04:53:37'),
(52, 26, 1, 5000.00, 'credit', 'Petty Cash', '2026-03-10 04:53:37'),
(53, 27, 1, 7500.00, 'debit', 'Cash Collection', '2026-03-10 04:53:37'),
(54, 27, 12, 7500.00, 'credit', 'ID Fees', '2026-03-10 04:53:37'),
(55, 28, 2, 50000.00, 'debit', 'Bank Deposit', '2026-03-10 04:53:37'),
(56, 28, 1, 50000.00, 'credit', 'Cash Withdrawal', '2026-03-10 04:53:37'),
(57, 29, 19, 12000.00, 'debit', 'Security Agency', '2026-03-10 04:53:37'),
(58, 29, 2, 12000.00, 'credit', 'BPI Transfer', '2026-03-10 04:53:37'),
(59, 30, 5, 18000.00, 'debit', '10 Whiteboards', '2026-03-10 04:53:37'),
(60, 30, 7, 18000.00, 'credit', 'Credit Purchase', '2026-03-10 04:53:37'),
(61, 31, 15, 150000.00, 'debit', 'Monthly Salary', '2026-03-10 04:56:20'),
(62, 31, 2, 150000.00, 'credit', 'BPI Transfer', '2026-03-10 04:56:20'),
(63, 32, 18, 15000.00, 'debit', 'Electricity & Water', '2026-03-10 04:56:20'),
(64, 32, 2, 15000.00, 'credit', 'BPI Payment', '2026-03-10 04:56:20'),
(65, 33, 1, 45000.00, 'debit', 'Cash Received', '2026-03-10 04:56:20'),
(66, 33, 11, 45000.00, 'credit', 'Tuition Fees', '2026-03-10 04:56:20'),
(67, 34, 20, 3500.00, 'debit', 'Detergents, Bleach', '2026-03-10 04:56:20'),
(68, 34, 1, 3500.00, 'credit', 'Cash Payment', '2026-03-10 04:56:20'),
(69, 35, 15, 300000.00, 'debit', '13th Month Bonus', '2026-03-10 04:56:20'),
(70, 35, 2, 300000.00, 'credit', 'BPI Transfer', '2026-03-10 04:56:20'),
(71, 36, 2, 12500.00, 'debit', 'Check Deposit', '2026-03-10 04:56:20'),
(72, 36, 13, 12500.00, 'credit', 'Science Lab Fees', '2026-03-10 04:56:20'),
(73, 37, 19, 6000.00, 'debit', 'Welding & Paint', '2026-03-10 04:56:20'),
(74, 37, 2, 6000.00, 'credit', 'Cash Payment', '2026-03-10 04:56:20'),
(75, 38, 5, 35000.00, 'debit', 'AV Equipment', '2026-03-10 04:56:20'),
(76, 38, 2, 35000.00, 'credit', 'Supplier Payment', '2026-03-10 04:56:20'),
(77, 39, 2, 100000.00, 'debit', 'Sponsorship Check', '2026-03-10 04:56:20'),
(78, 39, 14, 100000.00, 'credit', 'Sponsorship', '2026-03-10 04:56:20'),
(79, 40, 22, 500.00, 'debit', 'Monthly Bank Fees', '2026-03-10 04:56:20'),
(80, 40, 2, 500.00, 'credit', 'Auto Debit', '2026-03-10 04:56:20'),
(81, 41, 14, 66000.00, 'debit', '', '2026-03-10 10:21:07'),
(82, 41, 1, 33000.00, 'credit', '', '2026-03-10 10:21:07'),
(83, 41, 2, 33000.00, 'credit', '', '2026-03-10 10:21:07'),
(84, 42, 14, 27500.00, 'debit', '', '2026-03-10 10:25:39'),
(85, 42, 3, 20000.00, 'credit', '', '2026-03-10 10:25:39'),
(86, 42, 1, 7500.00, 'credit', '', '2026-03-10 10:25:39'),
(91, 45, 2, 15000.00, 'debit', '', '2026-03-13 00:56:22'),
(92, 45, 11, 15000.00, 'credit', '', '2026-03-13 00:56:22');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bank`
--
ALTER TABLE `bank`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `accountno` (`accountno`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `bank_id` (`bank_id`),
  ADD KEY `processed_by` (`processed_by`);

--
-- Indexes for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `account_code` (`account_code`),
  ADD KEY `parent_account_id` (`parent_account_id`);

--
-- Indexes for table `collections`
--
ALTER TABLE `collections`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `collected_by` (`collected_by`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `disbursed_by` (`disbursed_by`);

--
-- Indexes for table `employee`
--
ALTER TABLE `employee`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `system_settings`
--
ALTER TABLE `system_settings`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `setting_key` (`setting_key`);

--
-- Indexes for table `transactions`
--
ALTER TABLE `transactions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `reference_no` (`reference_no`),
  ADD KEY `created_by` (`created_by`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `transaction_entries`
--
ALTER TABLE `transaction_entries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `transaction_id` (`transaction_id`),
  ADD KEY `account_id` (`account_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bank`
--
ALTER TABLE `bank`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `collections`
--
ALTER TABLE `collections`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employee`
--
ALTER TABLE `employee`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `system_settings`
--
ALTER TABLE `system_settings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `transactions`
--
ALTER TABLE `transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=46;

--
-- AUTO_INCREMENT for table `transaction_entries`
--
ALTER TABLE `transaction_entries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=93;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `bank_transactions_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`),
  ADD CONSTRAINT `bank_transactions_ibfk_2` FOREIGN KEY (`bank_id`) REFERENCES `bank` (`id`),
  ADD CONSTRAINT `bank_transactions_ibfk_3` FOREIGN KEY (`processed_by`) REFERENCES `employee` (`id`);

--
-- Constraints for table `chart_of_accounts`
--
ALTER TABLE `chart_of_accounts`
  ADD CONSTRAINT `chart_of_accounts_ibfk_1` FOREIGN KEY (`parent_account_id`) REFERENCES `chart_of_accounts` (`id`);

--
-- Constraints for table `collections`
--
ALTER TABLE `collections`
  ADD CONSTRAINT `collections_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `collections_ibfk_2` FOREIGN KEY (`collected_by`) REFERENCES `employee` (`id`);

--
-- Constraints for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD CONSTRAINT `disbursements_ibfk_1` FOREIGN KEY (`transaction_id`) REFERENCES `transactions` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `disbursements_ibfk_2` FOREIGN KEY (`disbursed_by`) REFERENCES `employee` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
