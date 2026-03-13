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
-- Database: `u290526623_SmartSchool26`
--

-- --------------------------------------------------------

--
-- Table structure for table `academic_years`
--

CREATE TABLE `academic_years` (
  `id` int(11) NOT NULL,
  `academic_year` varchar(50) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `is_active` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `academic_years`
--

INSERT INTO `academic_years` (`id`, `academic_year`, `semester`, `is_active`) VALUES
(1, '2025-2026', '', 1),
(3, '2026-2027', '', 0),
(4, '2027-2028', '', 0),
(5, '2028-2029', '', 0),
(6, '2029-2030', '', 0),
(7, '2024-2025', '', 0);

-- --------------------------------------------------------

--
-- Table structure for table `activities`
--

CREATE TABLE `activities` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `type` enum('activity','exam','participation') NOT NULL,
  `max_score` decimal(10,2) NOT NULL,
  `quarter` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `section` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `activities`
--

INSERT INTO `activities` (`id`, `teacher_id`, `subject_id`, `title`, `description`, `type`, `max_score`, `quarter`, `created_at`, `updated_at`, `section`) VALUES
(8, 1, 43, 'Enrollment Form', '2nd Semester Enrollement Form', 'activity', 100.00, '1st Quarter', '2025-12-17 06:55:17', '2025-12-17 07:38:16', 'A'),
(9, 1, 43, 'Enrollment Form', '2nd Semester Enrollment Form', 'activity', 100.00, '1st Quarter', '2025-12-17 06:59:08', '2025-12-17 07:38:21', 'B'),
(14, 1, 33, 'ENROLLMENT FORM 2ND SEMESTER', 'ENROLLMENT FORM 2ND SEMESTER', 'activity', 50.00, '1st Quarter', '2025-12-29 01:40:56', '2025-12-29 01:40:56', 'A'),
(15, 1, 33, 'WORDPRESS', 'WORDPRESS', 'activity', 50.00, '1st Quarter', '2025-12-29 01:49:01', '2025-12-29 01:49:01', 'A'),
(16, 1, 32, 'Array C++ WEB', 'Array C++ WEB', 'activity', 150.00, '1st Quarter', '2025-12-29 01:57:18', '2025-12-29 01:57:18', 'A'),
(17, 1, 33, 'Enrollment Form', '2nd Semester Enrollment Form', 'activity', 50.00, '1st Quarter', '2025-12-29 02:05:03', '2025-12-29 02:05:03', 'B'),
(18, 1, 33, 'Word Press', 'Word Press Website', 'activity', 50.00, '1st Quarter', '2025-12-29 02:07:20', '2025-12-29 02:07:20', 'B'),
(19, 1, 32, 'Array C++ Web', 'Array C++ Web', 'activity', 100.00, '1st Quarter', '2025-12-29 02:07:46', '2025-12-29 02:07:46', 'B'),
(20, 1, 33, 'Enrollment Form', '2nd Semester Enrollment Form', 'activity', 50.00, '1st Quarter', '2025-12-29 02:28:49', '2025-12-29 02:28:49', 'C'),
(21, 1, 33, 'WordPress', 'WordPress', 'activity', 100.00, '1st Quarter', '2025-12-29 02:32:08', '2025-12-29 02:32:08', 'D'),
(22, 1, 33, 'Astra', 'Astra', 'activity', 100.00, '1st Quarter', '2025-12-29 02:32:28', '2025-12-29 02:32:28', 'D'),
(23, 1, 18, 'Enrollment Form', '2nd Semester Enrollment Form', 'activity', 50.00, '1st Quarter', '2025-12-29 04:03:04', '2025-12-29 04:03:04', 'D'),
(24, 1, 18, 'Database Shell', 'Database Shell', 'activity', 100.00, '1st Quarter', '2025-12-29 04:26:40', '2025-12-29 04:26:40', 'B'),
(25, 1, 18, 'Create Table', 'Create Table', 'activity', 100.00, '1st Quarter', '2025-12-29 04:27:00', '2025-12-29 04:27:00', 'B'),
(26, 1, 18, 'Database Shell', 'Database Shell', 'activity', 50.00, '1st Quarter', '2025-12-29 04:37:17', '2025-12-29 04:37:17', 'A'),
(27, 1, 18, 'Create Database and Create Table', 'Create Database and Create Table', 'activity', 100.00, '1st Quarter', '2025-12-29 04:37:52', '2025-12-29 04:37:52', 'A');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Cancelled','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `student_id`, `appointment_datetime`, `purpose`, `status`, `created_at`) VALUES
(3, 4, '2026-03-13 10:26:00', 'Counseling', 'Pending', '2026-03-12 14:26:42');

-- --------------------------------------------------------

--
-- Table structure for table `assessments`
--

CREATE TABLE `assessments` (
  `assessment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `assessment_type` varchar(100) DEFAULT NULL,
  `assessment_date` date DEFAULT NULL,
  `result` text DEFAULT NULL,
  `interpretation` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assessments`
--

INSERT INTO `assessments` (`assessment_id`, `student_id`, `assessment_type`, `assessment_date`, `result`, `interpretation`, `recommendations`, `created_at`) VALUES
(2, 4, 'Psychological', '2026-03-12', 'Based on the counseling interview and behavioral observation, the student appeared to experience academic pressure and anxiety related to performance during examinations. The student showed signs of low confidence in his/her academic ability, which may have influenced the decision to engage in dishonest behavior. No serious behavioral or psychological disorder was observed, but the student may benefit from guidance in developing positive coping strategies and improved study habits.', 'The cheating incident appears to be situational and influenced by academic stress and fear of poor performance. The student’s behavior may indicate difficulty managing academic expectations and pressure. With proper guidance, support, and development of responsible study habits, the student is capable of improving behavior and academic performance.', 'The student should attend follow-up counseling sessions to reinforce academic honesty and personal responsibility.\r\n\r\nThe student should develop better study and time-management strategies to reduce academic stress.\r\n\r\nTeachers may provide guidance and encouragement to help build the student’s confidence in completing academic tasks independently.\r\n\r\nThe student is encouraged to seek help from teachers or counselors whenever experiencing academic difficulties.\r\n\r\nParents/guardians may be informed to support the student in maintaining positive study habits and responsible behavior at home.', '2026-03-12 14:24:55');

-- --------------------------------------------------------

--
-- Table structure for table `banks`
--

CREATE TABLE `banks` (
  `id` int(11) NOT NULL,
  `bank_name` varchar(255) NOT NULL,
  `branch` varchar(255) NOT NULL,
  `account_number` varchar(255) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `banks`
--

INSERT INTO `banks` (`id`, `bank_name`, `branch`, `account_number`, `created_at`) VALUES
(1, 'Bank of Philippine Island', 'San Carlos', '123456789', '2026-02-25 09:05:06');

-- --------------------------------------------------------

--
-- Table structure for table `bank_transactions`
--

CREATE TABLE `bank_transactions` (
  `id` int(11) NOT NULL,
  `bank_id` int(11) NOT NULL,
  `transaction_type` varchar(50) NOT NULL COMMENT 'Deposit, Withdrawal, Check Deposit, Check Payment',
  `reference_no` varchar(50) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `check_number` varchar(50) DEFAULT NULL,
  `check_date` date DEFAULT NULL,
  `transaction_date` date NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `counseling_sessions`
--

CREATE TABLE `counseling_sessions` (
  `session_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `session_date` date NOT NULL,
  `counseling_type` enum('Academic','Personal','Career','Behavioral') NOT NULL,
  `reason` text DEFAULT NULL,
  `referred_by` varchar(255) DEFAULT NULL,
  `session_notes` text DEFAULT NULL,
  `recommendations` text DEFAULT NULL,
  `follow_up_date` date DEFAULT NULL,
  `session_status` enum('Ongoing','Completed','Referred') DEFAULT 'Ongoing',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `counseling_sessions`
--

INSERT INTO `counseling_sessions` (`session_id`, `student_id`, `counselor_id`, `session_date`, `counseling_type`, `reason`, `referred_by`, `session_notes`, `recommendations`, `follow_up_date`, `session_status`, `created_at`) VALUES
(5, 4, 14, '2026-03-12', 'Academic', 'Frequent absences', 'Erick Jason', 'The student was referred for counseling due to frequent absences from classes. During the session, the student shared several personal and academic concerns that have contributed to the irregular attendance. The counselor discussed the importance of consistent attendance, time management, and maintaining communication with teachers. The student was encouraged to develop strategies to manage responsibilities and avoid further absences. The student showed willingness to improve attendance and committed to making necessary adjustments. Follow-up counseling sessions may be recommended to monitor the student’s progress and provide additional support if needed.', 'It is recommended that the student improve class attendance and maintain regular participation in academic activities. The student is encouraged to practice better time management, prioritize school responsibilities, and communicate with teachers when difficulties arise. Continuous guidance and support from teachers, parents, and the guidance office are also recommended to help the student stay motivated and focused on academic goals. A follow-up counseling session may be conducted to monitor the student’s progress and provide further assistance if necessary.', '2026-03-28', 'Completed', '2026-03-12 14:23:31');

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `coursename` varchar(50) NOT NULL,
  `courselevel` varchar(15) NOT NULL DEFAULT '1',
  `coursedescription` text NOT NULL,
  `department_id` int(15) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `coursename`, `courselevel`, `coursedescription`, `department_id`) VALUES
(1, 'Bachelor of Science in Information Teachnology', '3', 'BSIT-3RD YEAR', 1),
(2, 'Bachelor of Science in Information Teachnology', '1', 'BSIT-1ST YEAR', 1),
(3, 'Bachelor of Science in Information Teachnology', '2', 'BSIT-2ND YEAR', 1),
(4, 'Bachelor of Science in Information Teachnology', '4', 'BSIT-4TH YEAR', 1);

-- --------------------------------------------------------

--
-- Table structure for table `customer_payments`
--

CREATE TABLE `customer_payments` (
  `id` int(11) NOT NULL,
  `customer_name` varchar(255) NOT NULL,
  `customer_type` enum('individual','organization','company','other') NOT NULL,
  `service_id` int(11) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `or_number` varchar(50) NOT NULL,
  `payment_method` enum('Cash','Check','Bank Transfer','Credit Card','Debit Card','Other') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `customer_payment_items`
--

CREATE TABLE `customer_payment_items` (
  `id` int(11) NOT NULL,
  `customer_payment_id` int(11) NOT NULL,
  `service_id` int(11) NOT NULL,
  `service_name` varchar(255) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

-- --------------------------------------------------------

--
-- Table structure for table `default_fees`
--

CREATE TABLE `default_fees` (
  `id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `fee_type` enum('miscellaneous','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(15) NOT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `category` varchar(10) NOT NULL DEFAULT 'Both'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `default_fees`
--

INSERT INTO `default_fees` (`id`, `fee_name`, `fee_type`, `amount`, `academic_year`, `semester`, `is_active`, `created_at`, `category`) VALUES
(5, 'Pinning Contribution', 'other', 1200.00, '2025-2026', '2nd', 1, '2026-03-06 00:56:33', 'College'),
(6, 'Title Hearing', 'other', 500.00, '2025-2026', '2nd', 1, '2026-03-06 00:57:32', 'College');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(100) NOT NULL,
  `dept_description` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `dept_description`) VALUES
(1, 'College of Computer Studies', 'Bachelor of Science in Information Technology'),
(5, 'Institute of Caregiving and Midwifery', 'Bachelor of Science in Midwifery &\r\nCaregiving');

-- --------------------------------------------------------

--
-- Table structure for table `disbursements`
--

CREATE TABLE `disbursements` (
  `id` int(11) NOT NULL,
  `payee_name` varchar(255) NOT NULL,
  `category` varchar(100) NOT NULL,
  `custom_category` varchar(255) DEFAULT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_date` date NOT NULL,
  `voucher_no` varchar(100) NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `payment_mode` varchar(20) NOT NULL DEFAULT 'Cash',
  `bank_name` varchar(100) DEFAULT NULL,
  `bank_account_number` varchar(100) DEFAULT NULL,
  `check_number` varchar(50) DEFAULT NULL,
  `check_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `enrollments`
--

CREATE TABLE `enrollments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `semester` varchar(11) NOT NULL,
  `enrollment_date` date NOT NULL,
  `status` enum('Registered','Enrolled','Dropped','Completed') DEFAULT NULL,
  `year_level` varchar(20) NOT NULL,
  `section` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `enrollments`
--

INSERT INTO `enrollments` (`id`, `student_id`, `course_id`, `academic_year`, `semester`, `enrollment_date`, `status`, `year_level`, `section`) VALUES
(2, 81, 1, '2025-2026', '2nd', '2025-12-17', 'Enrolled', '3', 'A'),
(3, 28, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(4, 185, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(5, 171, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(6, 191, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(7, 187, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(8, 62, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(9, 177, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(10, 189, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(11, 341, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(12, 190, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(13, 182, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(14, 117, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(15, 105, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(16, 40, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(17, 167, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(18, 45, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(19, 157, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(20, 127, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(21, 36, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(22, 29, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(23, 165, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(24, 66, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(25, 164, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(26, 49, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(27, 78, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(28, 25, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(29, 115, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(30, 48, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(31, 129, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(32, 46, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'B'),
(33, 4, 1, '2025-2026', '2nd', '2025-12-04', 'Completed', '3', 'A'),
(34, 44, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(36, 77, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(37, 33, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(38, 200, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(39, 27, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(40, 255, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(41, 236, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'C'),
(42, 221, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(43, 240, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'C'),
(44, 43, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(45, 42, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(46, 263, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(47, 202, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(48, 233, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'C'),
(49, 20, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(50, 225, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(51, 18, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(52, 219, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(53, 30, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(54, 252, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(55, 307, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(57, 273, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(58, 64, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(59, 306, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(60, 222, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(61, 265, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(62, 262, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(63, 207, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(64, 211, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(65, 218, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(66, 12, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(67, 253, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(69, 213, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(70, 212, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(71, 14, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(72, 204, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(73, 201, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(74, 224, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(75, 19, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(76, 206, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(77, 13, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(78, 257, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(79, 47, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(80, 197, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(81, 196, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(82, 195, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(83, 31, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(84, 37, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(85, 15, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(86, 205, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(87, 223, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(88, 256, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(89, 214, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(90, 254, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(91, 38, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(92, 203, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(93, 261, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(94, 199, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(95, 259, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(96, 241, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'C'),
(97, 32, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(98, 23, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(99, 209, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(100, 17, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(101, 308, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'D'),
(102, 50, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(103, 65, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(105, 217, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(106, 208, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(107, 22, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(108, 103, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(109, 279, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(110, 98, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(112, 188, 1, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '3', 'A'),
(113, 139, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(114, 82, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(115, 109, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(116, 269, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(117, 97, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(118, 85, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(119, 296, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(120, 281, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(121, 277, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(122, 295, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(123, 106, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(124, 99, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(125, 114, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(126, 280, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(127, 268, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(128, 159, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(129, 94, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(132, 86, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(133, 113, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(134, 92, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(137, 278, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(138, 108, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(139, 146, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(144, 90, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(145, 104, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'A'),
(148, 118, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(149, 128, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(152, 88, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(154, 107, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(155, 147, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(156, 79, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(158, 96, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(159, 292, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '2', 'B'),
(160, 290, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(161, 266, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(162, 95, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(164, 303, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', ''),
(165, 83, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(166, 144, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(167, 100, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(168, 93, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(169, 112, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(170, 110, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(171, 300, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(173, 282, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(174, 174, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(175, 286, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(176, 176, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(177, 102, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(178, 285, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(179, 168, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(180, 135, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(182, 302, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(183, 291, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(184, 294, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(185, 111, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(186, 288, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(187, 304, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(188, 149, 3, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(189, 293, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'B'),
(190, 161, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'D'),
(191, 84, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', 'A'),
(192, 297, 2, '2025-2026', '2nd', '2025-12-04', 'Enrolled', '1', ''),
(193, 35, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'B'),
(194, 181, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(195, 26, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(196, 63, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(197, 53, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(198, 52, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(199, 130, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(200, 124, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'B'),
(201, 193, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'B'),
(202, 24, 1, '2025-2026', '1st', '2026-01-09', 'Enrolled', '3', 'A'),
(203, 183, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(204, 186, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(205, 192, 1, '2025-2026', '2nd', '2026-01-09', 'Enrolled', '3', 'A'),
(206, 348, 1, '2025-2026', '2nd', '2025-12-17', 'Enrolled', '3', ''),
(207, 349, 1, '2025-2026', '2nd', '2025-12-17', 'Enrolled', '3', ''),
(208, 345, 1, '2025-2026', '2nd', '2025-12-18', 'Enrolled', '3', ''),
(209, 346, 1, '2025-2026', '2nd', '2025-12-17', 'Enrolled', '3', ''),
(210, 347, 1, '2025-2026', '2nd', '2025-12-18', 'Enrolled', '3', ''),
(211, 178, 1, '2025-2026', '2nd', '2026-03-13', 'Enrolled', '3', '');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `exam_id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `std_raw_score` decimal(10,2) DEFAULT NULL,
  `std_percentile_rank` decimal(5,2) DEFAULT NULL,
  `std_verbal_desc` varchar(100) DEFAULT NULL,
  `tmt_raw_score` decimal(10,2) DEFAULT NULL,
  `tmt_interpretation` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`exam_id`, `department_name`, `student_name`, `std_raw_score`, `std_percentile_rank`, `std_verbal_desc`, `tmt_raw_score`, `tmt_interpretation`, `created_at`) VALUES
(1, 'CCS', 'Mutas, Jomar M.', 57.00, 3.00, 'Good', 85.00, 'Passed', '2026-03-12 15:12:57');

-- --------------------------------------------------------

--
-- Table structure for table `fees`
--

CREATE TABLE `fees` (
  `id` int(11) NOT NULL,
  `fee_name` varchar(100) NOT NULL,
  `fee_type` enum('tuition','miscellaneous','other') NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `base_amount` decimal(10,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `fees`
--

INSERT INTO `fees` (`id`, `fee_name`, `fee_type`, `amount`, `description`, `created_at`, `updated_at`, `base_amount`) VALUES
(1, 'Pinning Contribution', 'other', 1200.00, NULL, '2026-03-06 01:04:22', '2026-03-06 01:04:22', NULL),
(2, 'Title Hearing', 'other', 500.00, NULL, '2026-03-06 01:04:22', '2026-03-06 01:04:22', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `graduate_tracer`
--

CREATE TABLE `graduate_tracer` (
  `id` int(11) NOT NULL,
  `email` varchar(255) NOT NULL,
  `family_name` varchar(100) NOT NULL,
  `first_name` varchar(100) NOT NULL,
  `middle_name` varchar(100) DEFAULT NULL,
  `year_graduated` varchar(10) DEFAULT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `birthday` date DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `spouse_name` varchar(200) DEFAULT NULL,
  `children_count` int(11) DEFAULT 0,
  `address` text DEFAULT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `programs` text DEFAULT NULL,
  `post_grad` varchar(200) DEFAULT NULL,
  `honors` varchar(200) DEFAULT NULL,
  `board_exam` varchar(200) DEFAULT NULL,
  `other_schools` text DEFAULT NULL,
  `occupation` varchar(150) DEFAULT NULL,
  `company` varchar(200) DEFAULT NULL,
  `position` varchar(150) DEFAULT NULL,
  `company_address` text DEFAULT NULL,
  `employment_date` date DEFAULT NULL,
  `salary` varchar(200) DEFAULT NULL,
  `prev_company` varchar(200) DEFAULT NULL,
  `prev_position` varchar(150) DEFAULT NULL,
  `prev_address` text DEFAULT NULL,
  `employment_time` varchar(50) DEFAULT NULL,
  `success_story` text DEFAULT NULL,
  `consent` varchar(20) DEFAULT '0',
  `submitted_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `graduate_tracer`
--

INSERT INTO `graduate_tracer` (`id`, `email`, `family_name`, `first_name`, `middle_name`, `year_graduated`, `gender`, `birthday`, `civil_status`, `spouse_name`, `children_count`, `address`, `contact`, `programs`, `post_grad`, `honors`, `board_exam`, `other_schools`, `occupation`, `company`, `position`, `company_address`, `employment_date`, `salary`, `prev_company`, `prev_position`, `prev_address`, `employment_time`, `success_story`, `consent`, `submitted_at`) VALUES
(2, 'mutas@csr-scc.edu.ph', 'Mutas', 'Jomar', 'Mangao', '2025', 'Male', '1997-02-22', 'Single', '', 0, 'San Carlos City', '09101882719', 'BSIT', '', 'Loyalty Award', '', '', 'Web Developer', 'Code Warriors, Inc.', 'Software Developer', 'San Carlos City, NIR', '2026-01-01', '', '', '', '', 'Less than 1 month', 'As a Bachelor of Science in Information Technology (BSIT) student, I started my journey with a strong curiosity about computers and how websites and systems work. At first, learning programming and web development was challenging, and there were many times when I struggled to understand codes and fix errors. However, I stayed determined to improve by practicing regularly, creating small projects, and learning from every mistake I made. Through dedication and continuous learning, I gradually developed my skills in designing and building websites. After years of effort and perseverance, I was able to turn my passion into a career and become a **Web Developer**, using my knowledge to create useful and user-friendly web applications. My journey proves that with hard work, patience, and passion for technology, dreams in the IT field can become a reality. ', 'Yes', '2026-03-12 14:58:53');

-- --------------------------------------------------------

--
-- Table structure for table `incidents`
--

CREATE TABLE `incidents` (
  `incident_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `incident_date` date NOT NULL,
  `incident_type` varchar(100) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `action_taken` text DEFAULT NULL,
  `counselor_remarks` text DEFAULT NULL,
  `resolution_status` enum('Pending','Resolved') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `incidents`
--

INSERT INTO `incidents` (`incident_id`, `student_id`, `incident_date`, `incident_type`, `description`, `action_taken`, `counselor_remarks`, `resolution_status`, `created_at`) VALUES
(4, 4, '2026-03-12', 'Minor Offense', 'The student was caught cheating during an academic assessment. The teacher observed the student using unauthorized materials while answering the test. The incident was immediately addressed, and the student was asked to explain the situation. The case was then referred for proper documentation and counseling to address the behavior and ensure that the student understands the seriousness of the offense.', 'The student was called for a meeting to discuss the incident of cheating during the assessment. The situation was explained to the student, and the importance of honesty and academic integrity was emphasized. The student was reminded of the school rules and the consequences of dishonest behavior. A warning was given, and the student was advised not to repeat the same action in the future. The teacher and counselor also informed the student that continued violations may result in stricter disciplinary measures.', 'The student acknowledged the mistake and expressed understanding of the consequences of cheating. During the counseling session, the student was encouraged to develop better study habits and to value honesty in academic work. Guidance was provided on managing academic pressure and improving self-discipline. The student was advised to focus on personal growth and to demonstrate responsible behavior in future academic activities.', 'Resolved', '2026-03-12 14:24:34');

-- --------------------------------------------------------

--
-- Table structure for table `mystudents`
--

CREATE TABLE `mystudents` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `mystudents`
--

INSERT INTO `mystudents` (`id`, `teacher_id`, `student_id`, `subject_id`, `created_at`) VALUES
(1, 1, 45, 43, '2025-12-17 06:53:52'),
(2, 1, 4, 43, '2025-12-17 06:53:54'),
(3, 1, 185, 43, '2025-12-17 06:53:56'),
(4, 1, 177, 43, '2025-12-17 06:53:59'),
(5, 1, 165, 43, '2025-12-17 06:57:41'),
(6, 1, 167, 43, '2025-12-17 06:57:44'),
(7, 1, 182, 43, '2025-12-17 06:57:47'),
(8, 1, 341, 43, '2025-12-17 06:57:52'),
(9, 1, 191, 43, '2025-12-17 06:57:56'),
(10, 1, 115, 43, '2025-12-17 06:57:59'),
(11, 1, 189, 43, '2025-12-17 06:58:04'),
(12, 1, 28, 43, '2025-12-17 06:58:06'),
(13, 1, 48, 43, '2025-12-17 06:58:08'),
(14, 1, 105, 43, '2025-12-17 06:58:10'),
(15, 1, 66, 43, '2025-12-17 06:58:12'),
(16, 1, 25, 43, '2025-12-17 06:58:14'),
(17, 1, 36, 43, '2025-12-17 06:58:16'),
(18, 1, 117, 43, '2025-12-17 06:58:19'),
(19, 1, 81, 43, '2025-12-17 06:58:20'),
(20, 1, 46, 43, '2025-12-17 06:58:22'),
(21, 1, 40, 43, '2025-12-17 06:58:25'),
(22, 1, 78, 43, '2025-12-17 06:58:27'),
(23, 1, 190, 43, '2025-12-17 06:58:29'),
(24, 1, 157, 43, '2025-12-17 06:58:30'),
(25, 1, 127, 43, '2025-12-17 06:58:31'),
(26, 1, 49, 43, '2025-12-17 06:58:33'),
(27, 1, 129, 43, '2025-12-17 06:58:35'),
(28, 1, 187, 43, '2025-12-17 06:58:36'),
(29, 1, 62, 43, '2025-12-17 06:58:37'),
(30, 1, 29, 43, '2025-12-17 06:58:38'),
(31, 1, 164, 43, '2025-12-17 06:58:40'),
(32, 1, 20, 32, '2025-12-29 01:33:55'),
(33, 1, 20, 33, '2025-12-29 01:33:59'),
(34, 1, 17, 32, '2025-12-29 01:34:04'),
(35, 1, 17, 33, '2025-12-29 01:34:08'),
(36, 1, 79, 18, '2025-12-29 01:34:14'),
(37, 1, 254, 32, '2025-12-29 01:35:14'),
(38, 1, 273, 32, '2025-12-29 01:35:17'),
(39, 1, 252, 32, '2025-12-29 01:35:21'),
(40, 1, 224, 32, '2025-12-29 01:35:25'),
(41, 1, 44, 32, '2025-12-29 01:35:28'),
(42, 1, 222, 32, '2025-12-29 01:35:33'),
(43, 1, 254, 33, '2025-12-29 01:35:44'),
(44, 1, 273, 33, '2025-12-29 01:35:46'),
(45, 1, 252, 33, '2025-12-29 01:35:47'),
(46, 1, 128, 18, '2025-12-29 01:35:50'),
(47, 1, 147, 18, '2025-12-29 01:35:52'),
(48, 1, 224, 33, '2025-12-29 01:35:53'),
(49, 1, 44, 33, '2025-12-29 01:35:54'),
(50, 1, 135, 18, '2025-12-29 01:35:58'),
(51, 1, 222, 33, '2025-12-29 01:36:01'),
(52, 1, 50, 32, '2025-12-29 01:36:03'),
(53, 1, 50, 33, '2025-12-29 01:36:04'),
(54, 1, 108, 18, '2025-12-29 01:36:05'),
(55, 1, 263, 32, '2025-12-29 01:36:07'),
(56, 1, 263, 33, '2025-12-29 01:36:08'),
(57, 1, 219, 32, '2025-12-29 01:36:09'),
(58, 1, 219, 33, '2025-12-29 01:36:10'),
(59, 1, 262, 32, '2025-12-29 01:36:11'),
(60, 1, 262, 33, '2025-12-29 01:36:12'),
(61, 1, 288, 18, '2025-12-29 01:36:13'),
(62, 1, 268, 18, '2025-12-29 01:36:14'),
(63, 1, 114, 18, '2025-12-29 01:36:16'),
(64, 1, 37, 32, '2025-12-29 01:36:17'),
(65, 1, 37, 33, '2025-12-29 01:36:17'),
(66, 1, 302, 18, '2025-12-29 01:36:18'),
(67, 1, 206, 32, '2025-12-29 01:36:19'),
(68, 1, 206, 33, '2025-12-29 01:36:21'),
(69, 1, 211, 32, '2025-12-29 01:36:23'),
(70, 1, 211, 33, '2025-12-29 01:36:24'),
(71, 1, 223, 32, '2025-12-29 01:36:27'),
(72, 1, 223, 33, '2025-12-29 01:36:28'),
(73, 1, 30, 32, '2025-12-29 01:36:29'),
(74, 1, 30, 33, '2025-12-29 01:36:30'),
(75, 1, 278, 18, '2025-12-29 01:36:30'),
(76, 1, 23, 32, '2025-12-29 01:36:32'),
(77, 1, 23, 33, '2025-12-29 01:36:33'),
(78, 1, 200, 32, '2025-12-29 01:36:35'),
(79, 1, 200, 33, '2025-12-29 01:36:36'),
(80, 1, 27, 32, '2025-12-29 01:36:37'),
(81, 1, 27, 33, '2025-12-29 01:36:39'),
(82, 1, 15, 32, '2025-12-29 01:36:40'),
(83, 1, 15, 33, '2025-12-29 01:36:41'),
(84, 1, 208, 32, '2025-12-29 01:36:43'),
(85, 1, 208, 33, '2025-12-29 01:36:44'),
(86, 1, 90, 18, '2025-12-29 01:36:46'),
(87, 1, 218, 32, '2025-12-29 01:36:47'),
(88, 1, 218, 33, '2025-12-29 01:36:48'),
(89, 1, 306, 32, '2025-12-29 01:36:49'),
(90, 1, 306, 33, '2025-12-29 01:36:50'),
(91, 1, 195, 32, '2025-12-29 01:36:50'),
(92, 1, 195, 33, '2025-12-29 01:36:51'),
(93, 1, 201, 32, '2025-12-29 01:36:52'),
(94, 1, 201, 33, '2025-12-29 01:36:52'),
(95, 1, 255, 32, '2025-12-29 01:36:55'),
(96, 1, 255, 33, '2025-12-29 01:36:55'),
(97, 1, 290, 18, '2025-12-29 01:36:56'),
(98, 1, 209, 32, '2025-12-29 01:36:57'),
(99, 1, 209, 33, '2025-12-29 01:36:57'),
(100, 1, 14, 32, '2025-12-29 01:36:59'),
(101, 1, 14, 33, '2025-12-29 01:37:00'),
(102, 1, 265, 32, '2025-12-29 01:37:01'),
(103, 1, 265, 33, '2025-12-29 01:37:04'),
(104, 1, 196, 32, '2025-12-29 01:37:05'),
(105, 1, 196, 33, '2025-12-29 01:37:06'),
(106, 1, 43, 32, '2025-12-29 01:37:06'),
(107, 1, 43, 33, '2025-12-29 01:37:07'),
(108, 1, 221, 32, '2025-12-29 01:37:08'),
(109, 1, 221, 33, '2025-12-29 01:37:09'),
(110, 1, 204, 32, '2025-12-29 01:37:09'),
(111, 1, 204, 33, '2025-12-29 01:37:13'),
(112, 1, 225, 32, '2025-12-29 01:37:15'),
(113, 1, 225, 33, '2025-12-29 01:37:16'),
(114, 1, 149, 32, '2025-12-29 01:37:19'),
(115, 1, 149, 33, '2025-12-29 01:37:19'),
(116, 1, 203, 32, '2025-12-29 01:37:20'),
(117, 1, 203, 33, '2025-12-29 01:37:21'),
(118, 1, 281, 18, '2025-12-29 01:37:23'),
(119, 1, 111, 18, '2025-12-29 01:37:24'),
(120, 1, 236, 32, '2025-12-29 01:37:25'),
(121, 1, 236, 33, '2025-12-29 01:37:27'),
(122, 1, 233, 32, '2025-12-29 01:37:28'),
(123, 1, 233, 33, '2025-12-29 01:37:28'),
(124, 1, 31, 32, '2025-12-29 01:37:29'),
(125, 1, 31, 33, '2025-12-29 01:37:30'),
(126, 1, 202, 32, '2025-12-29 01:37:31'),
(127, 1, 202, 33, '2025-12-29 01:37:32'),
(128, 1, 85, 18, '2025-12-29 01:37:33'),
(129, 1, 307, 32, '2025-12-29 01:37:34'),
(130, 1, 307, 33, '2025-12-29 01:37:35'),
(131, 1, 214, 32, '2025-12-29 01:37:38'),
(132, 1, 214, 33, '2025-12-29 01:37:40'),
(133, 1, 207, 32, '2025-12-29 01:37:40'),
(134, 1, 207, 33, '2025-12-29 01:37:41'),
(135, 1, 38, 32, '2025-12-29 01:37:42'),
(136, 1, 38, 33, '2025-12-29 01:37:43'),
(137, 1, 199, 32, '2025-12-29 01:37:43'),
(138, 1, 199, 33, '2025-12-29 01:37:44'),
(139, 1, 241, 32, '2025-12-29 01:37:45'),
(140, 1, 241, 33, '2025-12-29 01:37:46'),
(141, 1, 32, 32, '2025-12-29 01:37:48'),
(142, 1, 32, 33, '2025-12-29 01:37:49'),
(143, 1, 33, 32, '2025-12-29 01:37:50'),
(144, 1, 33, 33, '2025-12-29 01:37:51'),
(145, 1, 18, 32, '2025-12-29 01:37:52'),
(146, 1, 18, 33, '2025-12-29 01:37:53'),
(147, 1, 100, 18, '2025-12-29 01:37:54'),
(148, 1, 83, 18, '2025-12-29 01:37:55'),
(149, 1, 47, 32, '2025-12-29 01:37:55'),
(150, 1, 47, 33, '2025-12-29 01:37:56'),
(151, 1, 213, 32, '2025-12-29 01:37:59'),
(152, 1, 213, 33, '2025-12-29 01:38:00'),
(153, 1, 109, 18, '2025-12-29 01:38:01'),
(154, 1, 240, 32, '2025-12-29 01:38:01'),
(155, 1, 240, 33, '2025-12-29 01:38:04'),
(156, 1, 292, 32, '2025-12-29 01:38:05'),
(157, 1, 292, 33, '2025-12-29 01:38:06'),
(158, 1, 144, 18, '2025-12-29 01:38:06'),
(159, 1, 256, 32, '2025-12-29 01:38:08'),
(160, 1, 256, 33, '2025-12-29 01:38:08'),
(161, 1, 253, 32, '2025-12-29 01:38:11'),
(162, 1, 253, 33, '2025-12-29 01:38:12'),
(163, 1, 42, 32, '2025-12-29 01:38:12'),
(164, 1, 42, 33, '2025-12-29 01:38:13'),
(165, 1, 65, 32, '2025-12-29 01:38:14'),
(166, 1, 65, 33, '2025-12-29 01:38:14'),
(167, 1, 19, 32, '2025-12-29 01:38:16'),
(168, 1, 19, 33, '2025-12-29 01:38:17'),
(169, 1, 77, 32, '2025-12-29 01:38:18'),
(170, 1, 77, 33, '2025-12-29 01:38:19'),
(171, 1, 22, 32, '2025-12-29 01:38:22'),
(172, 1, 22, 33, '2025-12-29 01:38:23'),
(173, 1, 159, 18, '2025-12-29 01:38:24'),
(174, 1, 197, 32, '2025-12-29 01:38:24'),
(175, 1, 197, 33, '2025-12-29 01:38:25'),
(176, 1, 64, 32, '2025-12-29 01:38:26'),
(177, 1, 64, 33, '2025-12-29 01:38:28'),
(178, 1, 212, 32, '2025-12-29 01:38:28'),
(179, 1, 212, 33, '2025-12-29 01:38:29'),
(180, 1, 139, 18, '2025-12-29 01:38:30'),
(181, 1, 13, 32, '2025-12-29 01:38:31'),
(182, 1, 13, 33, '2025-12-29 01:38:33'),
(183, 1, 205, 32, '2025-12-29 01:38:33'),
(184, 1, 205, 33, '2025-12-29 01:38:34'),
(185, 1, 286, 18, '2025-12-29 01:38:35'),
(186, 1, 176, 18, '2025-12-29 01:38:35'),
(187, 1, 217, 32, '2025-12-29 01:38:36'),
(188, 1, 217, 33, '2025-12-29 01:38:37'),
(189, 1, 86, 18, '2025-12-29 01:38:38'),
(190, 1, 261, 32, '2025-12-29 01:38:40'),
(191, 1, 261, 33, '2025-12-29 01:38:42'),
(192, 1, 266, 18, '2025-12-29 01:38:43'),
(195, 1, 300, 18, '2025-12-29 01:38:45'),
(196, 1, 257, 32, '2025-12-29 01:38:46'),
(197, 1, 257, 33, '2025-12-29 01:38:47'),
(198, 1, 291, 18, '2025-12-29 01:38:48'),
(199, 1, 304, 18, '2025-12-29 01:38:48'),
(200, 1, 12, 32, '2025-12-29 01:38:49'),
(201, 1, 12, 33, '2025-12-29 01:38:51'),
(202, 1, 308, 32, '2025-12-29 01:38:52'),
(203, 1, 308, 33, '2025-12-29 01:38:52'),
(204, 1, 259, 32, '2025-12-29 01:38:53'),
(205, 1, 259, 33, '2025-12-29 01:38:54'),
(206, 1, 97, 18, '2025-12-29 01:38:54'),
(207, 1, 99, 18, '2025-12-29 01:38:55'),
(208, 1, 88, 18, '2025-12-29 01:38:56'),
(209, 1, 102, 18, '2025-12-29 01:38:57'),
(210, 1, 103, 18, '2025-12-29 01:38:58'),
(211, 1, 84, 18, '2025-12-29 01:39:01'),
(212, 1, 112, 18, '2025-12-29 01:39:02'),
(213, 1, 295, 18, '2025-12-29 01:39:03'),
(214, 1, 107, 18, '2025-12-29 01:39:03'),
(215, 1, 93, 18, '2025-12-29 01:39:04'),
(216, 1, 296, 32, '2025-12-29 01:39:05'),
(217, 1, 296, 33, '2025-12-29 01:39:05'),
(218, 1, 95, 18, '2025-12-29 01:39:06'),
(219, 1, 110, 18, '2025-12-29 01:39:07'),
(220, 1, 96, 18, '2025-12-29 01:39:08'),
(221, 1, 106, 18, '2025-12-29 01:39:13'),
(222, 1, 269, 18, '2025-12-29 01:39:14'),
(223, 1, 285, 18, '2025-12-29 01:39:15'),
(224, 1, 168, 18, '2025-12-29 01:39:15'),
(225, 1, 277, 18, '2025-12-29 01:39:16'),
(226, 1, 174, 18, '2025-12-29 01:39:17'),
(227, 1, 113, 18, '2025-12-29 01:39:17'),
(228, 1, 294, 18, '2025-12-29 01:39:18'),
(229, 1, 94, 18, '2025-12-29 01:39:19'),
(230, 1, 161, 18, '2025-12-29 01:39:20'),
(231, 1, 280, 18, '2025-12-29 01:39:22'),
(234, 1, 118, 18, '2025-12-29 01:39:26'),
(235, 1, 146, 18, '2025-12-29 01:39:26'),
(236, 1, 293, 18, '2025-12-29 01:39:27'),
(237, 1, 279, 18, '2025-12-29 01:39:28'),
(240, 1, 82, 18, '2025-12-29 01:39:31'),
(241, 1, 282, 18, '2025-12-29 01:39:34'),
(242, 1, 92, 18, '2025-12-29 01:39:34'),
(243, 1, 98, 18, '2025-12-29 03:33:55'),
(244, 1, 104, 32, '2026-01-09 02:50:50'),
(245, 1, 104, 33, '2026-01-09 02:50:52');

-- --------------------------------------------------------

--
-- Table structure for table `password_resets`
--

CREATE TABLE `password_resets` (
  `id` int(11) NOT NULL,
  `email` varchar(100) NOT NULL,
  `token` varchar(255) NOT NULL,
  `expiry` datetime NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `password_resets`
--

INSERT INTO `password_resets` (`id`, `email`, `token`, `expiry`, `created_at`) VALUES
(3, 'mutas@csr-scc.edu.ph', 'cb966c85f96636486a95caa4824c7a5c67692ae1fc8c5e8e39f7577f0c8229d2', '2025-12-17 20:35:29', '2025-12-17 11:35:29'),
(4, 'jason@csr-scc.edu.ph', '6275b487cd4ea4c83674b612b38886e600d92d304583fb2608536d1c5ba16354', '2025-12-18 17:15:02', '2025-12-18 08:15:02'),
(6, 'jaedancantojos023@gmail.com', '06775eb918360358128f9cf9119831ddff36f106660380d38ceb91a281e1a2e8', '2025-12-27 17:55:26', '2025-12-27 08:55:26'),
(12, 'rjdeasis@csr-scc.edu.ph', 'ddf6d85bdec3ae80f4b9e65ec3001f03e9d1361c507effd46e3cb26daebb4418', '2026-01-05 17:10:45', '2026-01-05 08:10:45'),
(14, 'bayalasalthea7@gmail.com', '2bff68701de023372ff67cecd9b156033e2f9fb915580bb34912366be1d00e04', '2026-01-06 07:54:28', '2026-01-05 22:54:28'),
(15, 'jkbarbon@csr-scc.edu.ph', '929426233993aed4b7d17103edafb421d1d808c65b5a629e6f78953683dce865', '2026-01-11 16:13:29', '2026-01-11 07:13:29'),
(25, 'catherine10@csr-scc.edu.ph', '4cb63601c5e2d340265d34840ab9d39eb3f88fd1fb812679578895a1c99f9176', '2026-02-27 12:29:30', '2026-02-27 03:29:30');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `discount` decimal(10,2) NOT NULL DEFAULT 0.00,
  `payment_date` date NOT NULL,
  `or_number` varchar(50) NOT NULL,
  `payment_method` enum('Cash','Check','Bank Transfer/Deposit') NOT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`id`, `student_id`, `amount`, `discount`, `payment_date`, `or_number`, `payment_method`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 81, 1200.00, 0.00, '2026-03-06', 'CCS2026-01', 'Cash', '', '2026-03-06 01:04:58', '2026-03-06 01:04:58'),
(2, 4, 1200.00, 0.00, '2026-03-06', 'CCS2026-02', 'Cash', '', '2026-03-06 01:09:14', '2026-03-06 01:09:14'),
(3, 35, 1200.00, 0.00, '2026-03-07', 'RCP-003', 'Cash', '', '2026-03-07 01:14:21', '2026-03-07 01:14:21'),
(4, 180, 1200.00, 0.00, '2026-03-07', 'RCP-004', 'Cash', '', '2026-03-07 01:15:03', '2026-03-07 01:15:03'),
(5, 117, 1200.00, 0.00, '2026-03-07', 'RCP-005', 'Cash', '', '2026-03-07 01:16:01', '2026-03-07 01:16:01'),
(6, 189, 1200.00, 0.00, '2026-03-07', 'RCP-006', 'Cash', '', '2026-03-07 01:16:45', '2026-03-07 01:16:45'),
(7, 28, 1200.00, 0.00, '2026-03-07', 'RCP-007', 'Cash', '', '2026-03-07 01:17:39', '2026-03-07 01:17:39'),
(8, 105, 1200.00, 0.00, '2026-03-07', 'RCP-008', 'Cash', '', '2026-03-07 01:18:24', '2026-03-07 01:18:24'),
(9, 126, 1200.00, 0.00, '2026-03-07', 'RCP-009', 'Cash', '', '2026-03-07 01:19:27', '2026-03-07 01:19:27'),
(10, 40, 1200.00, 0.00, '2026-03-07', 'RCP-010', 'Cash', '', '2026-03-07 01:20:27', '2026-03-07 01:20:27'),
(11, 167, 1200.00, 0.00, '2026-03-07', 'RCP=012', 'Cash', '', '2026-03-07 01:22:19', '2026-03-07 01:22:19'),
(12, 166, 1200.00, 0.00, '2026-03-07', 'RCP-011', 'Cash', '', '2026-03-07 01:22:55', '2026-03-07 01:22:55'),
(13, 181, 1200.00, 0.00, '2026-03-07', 'RCP-013', 'Cash', '', '2026-03-07 01:23:32', '2026-03-07 01:23:32'),
(14, 163, 1200.00, 0.00, '2026-03-07', 'RCP-014', 'Cash', '', '2026-03-07 01:24:31', '2026-03-07 01:24:31'),
(15, 45, 1200.00, 0.00, '2026-03-07', 'RCP-015', 'Cash', '', '2026-03-07 01:25:12', '2026-03-07 01:25:12'),
(16, 157, 1200.00, 0.00, '2026-03-07', 'RCP-016', 'Cash', '', '2026-03-07 01:25:54', '2026-03-07 01:25:54'),
(17, 185, 1200.00, 0.00, '2026-03-07', 'RCP-017', 'Cash', '', '2026-03-07 01:27:22', '2026-03-07 01:27:22'),
(18, 127, 1200.00, 0.00, '2026-03-07', 'RCP-018', 'Cash', '', '2026-03-07 01:31:54', '2026-03-07 01:31:54'),
(19, 188, 1200.00, 0.00, '2026-03-07', 'RCP-019', 'Cash', '', '2026-03-07 01:32:45', '2026-03-07 01:32:45'),
(20, 36, 1200.00, 0.00, '2026-03-07', 'RCP-020', 'Cash', '', '2026-03-07 01:33:29', '2026-03-07 01:33:29'),
(21, 29, 1200.00, 0.00, '2026-03-07', 'RCP-021', 'Cash', '', '2026-03-07 01:34:08', '2026-03-07 01:34:08'),
(22, 165, 1200.00, 0.00, '2026-03-07', 'RCP-022', 'Cash', '', '2026-03-07 01:38:34', '2026-03-07 01:38:34'),
(23, 66, 1200.00, 0.00, '2026-03-07', 'RCP-023', 'Cash', '', '2026-03-07 01:40:09', '2026-03-07 01:40:09'),
(24, 26, 1200.00, 0.00, '2026-03-07', 'RCP-024', 'Cash', '', '2026-03-07 01:41:09', '2026-03-07 01:41:09'),
(25, 171, 1200.00, 0.00, '2026-03-07', 'RCP-025', 'Cash', '', '2026-03-07 01:41:50', '2026-03-07 01:41:50'),
(26, 63, 1200.00, 0.00, '2026-03-07', 'RCP-026', 'Cash', '', '2026-03-07 01:42:31', '2026-03-07 01:42:31'),
(27, 53, 1200.00, 0.00, '2026-03-07', 'RCP-027', 'Cash', '', '2026-03-07 01:43:22', '2026-03-07 01:43:22'),
(28, 52, 1200.00, 0.00, '2026-03-07', 'RCP-028', 'Cash', '', '2026-03-07 01:44:02', '2026-03-07 01:44:02'),
(29, 341, 1200.00, 0.00, '2026-03-07', 'RCP-029', 'Cash', '', '2026-03-07 01:44:57', '2026-03-07 01:44:57'),
(30, 130, 1200.00, 0.00, '2026-03-07', 'RCP-030', 'Cash', '', '2026-03-07 01:45:56', '2026-03-07 01:45:56'),
(31, 191, 1200.00, 0.00, '2026-03-07', 'RCP-031', 'Cash', '', '2026-03-07 01:46:56', '2026-03-07 01:46:56'),
(32, 46, 1200.00, 0.00, '2026-03-07', 'RCP-032', 'Cash', '', '2026-03-07 01:47:34', '2026-03-07 01:47:34'),
(33, 187, 1200.00, 0.00, '2026-03-07', 'RCP-033', 'Cash', '', '2026-03-07 01:48:13', '2026-03-07 01:48:13'),
(34, 124, 1200.00, 0.00, '2026-03-07', 'RCP-034', 'Cash', '', '2026-03-07 01:48:50', '2026-03-07 01:48:50'),
(35, 164, 1200.00, 0.00, '2026-03-07', 'RCP-035', 'Cash', '', '2026-03-07 01:49:42', '2026-03-07 01:49:42'),
(36, 49, 1200.00, 0.00, '2026-03-07', 'RCP-036', 'Cash', '', '2026-03-07 01:50:34', '2026-03-07 01:50:34'),
(37, 193, 1200.00, 0.00, '2026-03-07', 'RCP-037', 'Cash', '', '2026-03-07 01:51:06', '2026-03-07 01:51:06'),
(38, 78, 1200.00, 0.00, '2026-03-07', 'RCP-038', 'Cash', '', '2026-03-07 01:51:45', '2026-03-07 01:51:45'),
(39, 48, 1200.00, 0.00, '2026-03-07', 'RCP-039', 'Cash', '', '2026-03-07 01:52:18', '2026-03-07 01:52:18'),
(40, 62, 1200.00, 0.00, '2026-03-07', 'RCP-040', 'Cash', '', '2026-03-07 01:53:02', '2026-03-07 01:53:02'),
(41, 25, 1200.00, 0.00, '2026-03-07', 'RCP-041', 'Cash', '', '2026-03-07 01:53:41', '2026-03-07 01:53:41'),
(42, 24, 1200.00, 0.00, '2026-03-07', 'RCP-042', 'Cash', '', '2026-03-07 01:54:16', '2026-03-07 01:54:16'),
(43, 190, 1200.00, 0.00, '2026-03-07', 'RCP-043', 'Cash', '', '2026-03-07 01:55:00', '2026-03-07 01:55:00'),
(44, 177, 1200.00, 0.00, '2026-03-07', 'RCP-044', 'Cash', '', '2026-03-07 01:55:39', '2026-03-07 01:55:39'),
(45, 183, 1200.00, 0.00, '2026-03-07', 'RCP-045', 'Cash', '', '2026-03-07 01:56:35', '2026-03-07 01:56:35'),
(46, 186, 1200.00, 0.00, '2026-03-07', 'RCP-046', 'Cash', '', '2026-03-07 01:57:08', '2026-03-07 01:57:08'),
(47, 129, 1200.00, 0.00, '2026-03-07', 'RCP-047', 'Cash', '', '2026-03-07 01:57:47', '2026-03-07 01:57:47'),
(48, 182, 1200.00, 0.00, '2026-03-07', 'RCP-048', 'Cash', '', '2026-03-07 01:58:40', '2026-03-07 01:58:40'),
(49, 115, 1200.00, 0.00, '2026-03-07', 'RCP-049', 'Cash', '', '2026-03-07 01:59:14', '2026-03-07 01:59:14'),
(50, 192, 1200.00, 0.00, '2026-03-07', 'RCP-050', 'Cash', '', '2026-03-07 02:00:10', '2026-03-07 02:00:10'),
(51, 345, 1200.00, 0.00, '2026-03-07', 'RCP-55', 'Cash', '', '2026-03-07 04:23:41', '2026-03-07 04:23:41'),
(52, 349, 1200.00, 0.00, '2026-03-07', 'RCP-56', 'Cash', '', '2026-03-07 04:24:18', '2026-03-07 04:24:18'),
(53, 348, 1200.00, 0.00, '2026-03-07', 'RCP-58', 'Cash', '', '2026-03-07 04:24:52', '2026-03-07 04:24:52'),
(54, 346, 1200.00, 0.00, '2026-03-07', 'RCP-60', 'Cash', '', '2026-03-07 04:25:36', '2026-03-07 04:25:36'),
(55, 347, 1200.00, 0.00, '2026-03-07', 'RCP-061', 'Cash', '', '2026-03-07 04:37:46', '2026-03-07 04:37:46'),
(56, 187, 500.00, 0.00, '2026-03-10', 'RCP-71', 'Cash', '', '2026-03-10 02:02:23', '2026-03-10 02:02:23'),
(57, 53, 500.00, 0.00, '2026-03-10', 'RCP-72', 'Cash', '', '2026-03-10 02:03:21', '2026-03-10 02:03:21'),
(58, 4, 500.00, 0.00, '2026-03-10', 'RCP-73', 'Cash', '', '2026-03-10 02:03:46', '2026-03-10 02:03:46');

-- --------------------------------------------------------

--
-- Table structure for table `payment_allocations`
--

CREATE TABLE `payment_allocations` (
  `id` int(11) NOT NULL,
  `payment_id` int(11) NOT NULL,
  `student_fee_id` int(11) DEFAULT NULL,
  `allocated_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `payment_allocations`
--

INSERT INTO `payment_allocations` (`id`, `payment_id`, `student_fee_id`, `allocated_amount`, `created_at`, `updated_at`) VALUES
(1, 1, 1, 1200.00, '2026-03-06 01:04:58', '2026-03-06 01:04:58'),
(2, 2, 3, 1200.00, '2026-03-06 01:09:14', '2026-03-06 01:09:14'),
(3, 3, 5, 1200.00, '2026-03-07 01:14:21', '2026-03-07 01:14:21'),
(4, 4, 7, 1200.00, '2026-03-07 01:15:03', '2026-03-07 01:15:03'),
(5, 5, 9, 1200.00, '2026-03-07 01:16:01', '2026-03-07 01:16:01'),
(6, 6, 11, 1200.00, '2026-03-07 01:16:45', '2026-03-07 01:16:45'),
(7, 7, 13, 1200.00, '2026-03-07 01:17:39', '2026-03-07 01:17:39'),
(8, 8, 15, 1200.00, '2026-03-07 01:18:24', '2026-03-07 01:18:24'),
(9, 9, 17, 1200.00, '2026-03-07 01:19:27', '2026-03-07 01:19:27'),
(10, 10, 19, 1200.00, '2026-03-07 01:20:27', '2026-03-07 01:20:27'),
(11, 11, 21, 1200.00, '2026-03-07 01:22:19', '2026-03-07 01:22:19'),
(12, 12, 23, 1200.00, '2026-03-07 01:22:55', '2026-03-07 01:22:55'),
(13, 13, 25, 1200.00, '2026-03-07 01:23:32', '2026-03-07 01:23:32'),
(14, 14, 27, 1200.00, '2026-03-07 01:24:31', '2026-03-07 01:24:31'),
(15, 15, 29, 1200.00, '2026-03-07 01:25:12', '2026-03-07 01:25:12'),
(16, 16, 31, 1200.00, '2026-03-07 01:25:54', '2026-03-07 01:25:54'),
(17, 17, 33, 1200.00, '2026-03-07 01:27:22', '2026-03-07 01:27:22'),
(18, 18, 35, 1200.00, '2026-03-07 01:31:54', '2026-03-07 01:31:54'),
(19, 19, 37, 1200.00, '2026-03-07 01:32:45', '2026-03-07 01:32:45'),
(20, 20, 39, 1200.00, '2026-03-07 01:33:29', '2026-03-07 01:33:29'),
(21, 21, 41, 1200.00, '2026-03-07 01:34:08', '2026-03-07 01:34:08'),
(22, 22, 43, 1200.00, '2026-03-07 01:38:34', '2026-03-07 01:38:34'),
(23, 23, 45, 1200.00, '2026-03-07 01:40:09', '2026-03-07 01:40:09'),
(24, 24, 47, 1200.00, '2026-03-07 01:41:09', '2026-03-07 01:41:09'),
(25, 25, 49, 1200.00, '2026-03-07 01:41:50', '2026-03-07 01:41:50'),
(26, 26, 51, 1200.00, '2026-03-07 01:42:31', '2026-03-07 01:42:31'),
(27, 27, 53, 1200.00, '2026-03-07 01:43:22', '2026-03-07 01:43:22'),
(28, 28, 55, 1200.00, '2026-03-07 01:44:02', '2026-03-07 01:44:02'),
(29, 29, 57, 1200.00, '2026-03-07 01:44:57', '2026-03-07 01:44:57'),
(30, 30, 59, 1200.00, '2026-03-07 01:45:56', '2026-03-07 01:45:56'),
(31, 31, 61, 1200.00, '2026-03-07 01:46:56', '2026-03-07 01:46:56'),
(32, 32, 63, 1200.00, '2026-03-07 01:47:34', '2026-03-07 01:47:34'),
(33, 33, 65, 1200.00, '2026-03-07 01:48:13', '2026-03-07 01:48:13'),
(34, 34, 67, 1200.00, '2026-03-07 01:48:50', '2026-03-07 01:48:50'),
(35, 35, 69, 1200.00, '2026-03-07 01:49:42', '2026-03-07 01:49:42'),
(36, 36, 71, 1200.00, '2026-03-07 01:50:34', '2026-03-07 01:50:34'),
(37, 37, 73, 1200.00, '2026-03-07 01:51:06', '2026-03-07 01:51:06'),
(38, 38, 75, 1200.00, '2026-03-07 01:51:45', '2026-03-07 01:51:45'),
(39, 39, 77, 1200.00, '2026-03-07 01:52:18', '2026-03-07 01:52:18'),
(40, 40, 79, 1200.00, '2026-03-07 01:53:02', '2026-03-07 01:53:02'),
(41, 41, 81, 1200.00, '2026-03-07 01:53:41', '2026-03-07 01:53:41'),
(42, 42, 83, 1200.00, '2026-03-07 01:54:16', '2026-03-07 01:54:16'),
(43, 43, 85, 1200.00, '2026-03-07 01:55:00', '2026-03-07 01:55:00'),
(44, 44, 87, 1200.00, '2026-03-07 01:55:39', '2026-03-07 01:55:39'),
(45, 45, 89, 1200.00, '2026-03-07 01:56:35', '2026-03-07 01:56:35'),
(46, 46, 91, 1200.00, '2026-03-07 01:57:08', '2026-03-07 01:57:08'),
(47, 47, 93, 1200.00, '2026-03-07 01:57:47', '2026-03-07 01:57:47'),
(48, 48, 95, 1200.00, '2026-03-07 01:58:40', '2026-03-07 01:58:40'),
(49, 49, 97, 1200.00, '2026-03-07 01:59:14', '2026-03-07 01:59:14'),
(50, 50, 99, 1200.00, '2026-03-07 02:00:10', '2026-03-07 02:00:10'),
(51, 51, 101, 1200.00, '2026-03-07 04:23:41', '2026-03-07 04:23:41'),
(52, 52, 103, 1200.00, '2026-03-07 04:24:18', '2026-03-07 04:24:18'),
(53, 53, 105, 1200.00, '2026-03-07 04:24:52', '2026-03-07 04:24:52'),
(54, 54, 107, 1200.00, '2026-03-07 04:25:36', '2026-03-07 04:25:36'),
(55, 55, 109, 1200.00, '2026-03-07 04:37:46', '2026-03-07 04:37:46'),
(56, 56, 66, 500.00, '2026-03-10 02:02:23', '2026-03-10 02:02:23'),
(57, 57, 54, 500.00, '2026-03-10 02:03:21', '2026-03-10 02:03:21'),
(58, 58, 4, 500.00, '2026-03-10 02:03:46', '2026-03-10 02:03:46');

-- --------------------------------------------------------

--
-- Table structure for table `program_heads`
--

CREATE TABLE `program_heads` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `department_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `profile_photo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_heads`
--

INSERT INTO `program_heads` (`id`, `name`, `username`, `password`, `department_id`, `created_at`, `profile_photo`) VALUES
(1, 'Erick Jason Batuto', 'jason', '$2y$10$4w6e6Y8opJveximb0BwiY.8.UXoT7meOZQ/fdGk98gHbQH0.K13v.', 1, '2025-12-16 13:49:34', '1765965767_School Logo.png');

-- --------------------------------------------------------

--
-- Table structure for table `services`
--

CREATE TABLE `services` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `description` text DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT 0.00,
  `is_active` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `services`
--

INSERT INTO `services` (`id`, `name`, `description`, `price`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'ID Card Printing', 'Replacement for lost ID cards', 50.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(2, 'Photocopy (Black & White)', 'Per page', 1.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(3, 'Photocopy (Colored)', 'Per page', 5.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(4, 'Transcript of Records', 'Official request', 100.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(5, 'Certificate of Enrollment', 'For scholarship/employment purposes', 50.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(6, 'Diploma Request', 'Re-issuance of diploma', 500.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32'),
(7, 'Library Fine Payment', 'Standard penalty fee', 20.00, 1, '2026-02-25 02:57:32', '2026-02-25 02:57:32');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `id_number` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `gender` enum('Male','Female') DEFAULT NULL,
  `birth_date` date DEFAULT NULL,
  `age` int(11) DEFAULT NULL,
  `birth_place` varchar(100) DEFAULT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `nationality` varchar(50) DEFAULT NULL,
  `religion` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `home_address` text DEFAULT NULL,
  `father_name` varchar(100) DEFAULT NULL,
  `father_occupation` varchar(100) DEFAULT NULL,
  `mother_name` varchar(100) DEFAULT NULL,
  `mother_occupation` varchar(100) DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_address` varchar(255) DEFAULT NULL,
  `other_support` varchar(100) DEFAULT NULL,
  `is_boarding` tinyint(1) DEFAULT 0,
  `with_family` tinyint(1) DEFAULT 1,
  `family_address` text DEFAULT NULL,
  `elem_address` varchar(255) DEFAULT NULL,
  `elem_year` varchar(20) DEFAULT NULL,
  `sec_address` varchar(255) DEFAULT NULL,
  `sec_year` varchar(20) DEFAULT NULL,
  `college_address` varchar(255) DEFAULT NULL,
  `college_year` varchar(20) DEFAULT NULL,
  `voc_address` varchar(255) DEFAULT NULL,
  `voc_year` varchar(20) DEFAULT NULL,
  `others_address` varchar(255) DEFAULT NULL,
  `others_year` varchar(20) DEFAULT NULL,
  `form138` tinyint(1) DEFAULT 0,
  `moral_cert` tinyint(1) DEFAULT 0,
  `birth_cert` tinyint(1) DEFAULT 0,
  `good_moral` tinyint(1) DEFAULT 0,
  `others1` tinyint(1) DEFAULT 0,
  `others2` tinyint(1) DEFAULT 0,
  `others3` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_active` tinyint(4) NOT NULL,
  `profile_photo` text NOT NULL,
  `lrn_no` varchar(15) NOT NULL,
  `contact_person` varchar(150) NOT NULL,
  `form137` tinyint(1) NOT NULL,
  `parents_marriage_cert` tinyint(1) NOT NULL,
  `baptism_cert` tinyint(1) NOT NULL,
  `proof_income` tinyint(1) NOT NULL,
  `brown_envelope` tinyint(1) NOT NULL,
  `white_folder` tinyint(1) NOT NULL,
  `id_picture` tinyint(1) NOT NULL,
  `esc_app_form` tinyint(1) NOT NULL,
  `esc_contract` tinyint(1) NOT NULL,
  `esc_cert` tinyint(1) NOT NULL,
  `shsvp_cert` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `id_number`, `last_name`, `first_name`, `middle_name`, `gender`, `birth_date`, `age`, `birth_place`, `civil_status`, `nationality`, `religion`, `email`, `password`, `contact_number`, `home_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_address`, `other_support`, `is_boarding`, `with_family`, `family_address`, `elem_address`, `elem_year`, `sec_address`, `sec_year`, `college_address`, `college_year`, `voc_address`, `voc_year`, `others_address`, `others_year`, `form138`, `moral_cert`, `birth_cert`, `good_moral`, `others1`, `others2`, `others3`, `notes`, `created_at`, `updated_at`, `is_active`, `profile_photo`, `lrn_no`, `contact_person`, `form137`, `parents_marriage_cert`, `baptism_cert`, `proof_income`, `brown_envelope`, `white_folder`, `id_picture`, `esc_app_form`, `esc_contract`, `esc_cert`, `shsvp_cert`) VALUES
(4, '1997022201', 'Mutas', 'Jomar', 'Mangao', 'Male', '1997-02-22', 28, 'Romblon', 'Single', 'Filipino', 'Roman Catholic', 'mutas@csr-scc.edu.ph', '$2y$10$qtx258hSm/lSC8TEn0fiyuMUczQSdaEwPzrpla6OD.y4.D1e3wCuS', '09101882719', 'San Carlos City', 'Silverio Mutas', 'Decease', 'Fe Iban Mangao', 'Housewife', 'Alma Mangao', 'San Carlos', '', 1, 1, 'San Carlos', 'Cobrador Elementary School', '2010', 'Julio Ledesma National High Scool', '2022', '', '', '', '', 'Romblon National High School', '2014', 1, 1, 1, 1, 1, 1, 1, 'No PSA', '2025-08-16 09:33:42', '2025-12-17 08:04:26', 1, 'uploads/profile_photos/profile_4_6942640ac7e989.02753270.jpg', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(12, '2006102601', 'Espadilla', 'Deniel James', 'Dumarig', 'Male', '2006-10-26', 18, 'BRGY. ANI_E', 'Single', 'Filipino', 'Roman Chatolic', 'espadilla@csr-scc.edu.ph', '$2y$10$aXnZRT3L88S4M/yuu0m8cuX5tw3dpUgU3I4kBzlnuVnB.ShQb6onW', '09369008058', 'Brgy. Ani-e, Calatrava Negros Occidental, 6126', 'Elizar Espadilla', 'N/a', 'Denisa Dumarig', 'Salon', 'Pacita Dumarig', 'Brgy. Ani-e, Calatrava Negros Occidental, 6126', 'Tarsing Duamrig', 1, 1, 'Brgy. Ani-e, Calatrava Negros Occidental, 6126', 'Ani-e Elementary', '2016-2017', 'Laga-an national HighSchool', '2023-2024', 'Colegio De Santa Rita San Carlos inc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:20:00', '2025-08-17 23:20:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(13, '2006080801', 'Lorezo', 'Mary Ann', 'Bitoon', 'Female', '2006-08-08', 19, 'Calatrava Health Center', 'Single', 'Filipino', 'Roman Catholic', 'marylorezo@csr-scc.edu.ph', '$2y$10$kFy/tHZM0fFlZhEkWi07F.K.fWiQv9G/pRxWLO1Z/2sMSlII4a86C', '09516077595', 'Zone 3, Sitio Tubod, Brgy. Patun-an, Calatrava, Negros Occidental', 'Edmar B. Lorezo', 'Tricycle Driver', 'Annalyn B. Lorezo', 'Store Vendor', 'Annalyn B. Lorezo', 'Zone 3, Sitio Tubod, Brgy. Patun-an, Calatrava, Negros Occidental', 'Edmar B. Lorezo', 1, 1, 'Zone 3, Sitio Tubod, Brgy. Patun-an, Calatrava, Negros Occidental', 'Calatrava II Central School', '2016-2017', 'Calatrava Senior High School - Stand Alone', '2023-2024', 'Colegio de Santa Rita de San Carlos, Inc.', '2024-2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:22:27', '2025-08-17 23:22:27', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(14, '2005072801', 'Gilarman', 'Bea Grace', 'Timtim', 'Female', '2005-07-28', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'gilarman@csr-scc.edu.ph', '$2y$10$rbewDjogqyFK80V7Z4vrWe1uTcwfJvLQC0JfelTey2BXH96Ku8yN6', '09162956703', 'hda.Caridad Brgy.Patun-an Calatrava Neg.Occ', 'Primitivo Gilarman', 'n/a', 'Emelisa T. Gilarman', 'cashier in palau', 'Emelisa T. Gilarman', 'hda.Caridad Brgy.Patun-an Calatrava Neg.Occ', 'Emelisa T. Gilarman', 1, 1, 'hda.Caridad Brgy.Patun-an Calatrava Neg.Occ', 'Patun-an Elementary School', '2016-2017', 'Calatrava Senior High School - Stand Alone', '2023-2024', 'Colegio de Santa Rita de San Carlos,Inc.', '2024-2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:23:08', '2025-12-21 04:44:44', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(15, '2005041801', 'Mesterio', 'Kurt Kane ', 'Goc-Ong', 'Male', '2005-04-18', 20, 'cebu city', 'Single', 'filipino', 'lds', 'mesterio@csr-scc.edu.ph', '$2y$10$bUx3J/bQa3O1Q9dU.eG.Ru2qhsuz/EzittY2mEH0ArfKFvqQAa0kG', '09056000128', 'Barangay Suba, Amihan 2, Calatrava, Negros .Occ', 'Reynaldo mesterio', 'single', 'Meta mesterio', 'deceased', 'Reynaldo mesterio', 'brgy suba, amihan 2, calatrava, neg.occ', 'step mother', 1, 1, ' Barangay Suba,  Amihan, 2, Calatrava, Negros. Occ', 'Yati Elementary school', '2017-2018', 'Banza National High school', '  2021-2022', 'collegio de sta.rita', '2025-2026', 'N/A', '', 'N/A', '', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:33:00', '2025-08-17 23:33:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(16, '2004061701', 'Ortega', 'Ed Bryan', 'Caballes', 'Male', '2004-06-17', 21, 'San Carlos City Neg. Occ.', 'Single', 'Filipino', 'Roman Catholic', 'edortega@csr-scc.edu.ph', '$2y$10$YV0dow1aYIChWsHkW92Nu.II2vhNeSslIMlX9GaEvyqQBw5swYhpu', '09850549656', 'Brgy. Rizal Phase 6, Blk 15. SCC.', 'Efren M. Ortega', 'Laborer', 'Gloria C. Ortega', 'Housewife', 'Demna Marie Alingasa', 'Brgy. Rizal Phase 4 ', 'Ian Paul C. Ortega', 1, 1, 'BRGY. RIZAL, PHASE 6, BLK 15. SCC.', 'Ramon Magsaysay Elementary School', '2016-2017', 'Julio Ledesma National High School', '2023-2024', 'Colegio De Santa Rita De San Carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:35:50', '2025-08-17 23:35:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(17, '1998121801', 'Tabuelog', 'John Phillip', 'Rigor', 'Male', '1998-12-18', 26, 'Cebu City', 'Single', 'Filipino', 'Roman Catholic', 'rigorjohn7@gmail.com', '$2y$10$z/kWfA1JGno6XcLp5MF8buvDpnkls/MpyYw70NmgXNKcOxwNztqBS', '09102970151', 'Ylagan Ext. Brgy 6 San Carlos City Neg. Occidental', 'Felipe Tabuelog Jr.', 'tricycle driver', 'Othella Rigor Tabuelog', 'housewife', 'Othella Rigor Tabuelog', 'Ylagan ext brgy 6 san carlos city', 'Aubrey Mae Rigor', 1, 1, 'Brgy 1 Villarante San Carlos City', 'CVGSMS ', '2008', 'Colegio de Santa Rita', '2018', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:38:29', '2025-08-17 23:38:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(18, '2006022701', 'Benigay', 'Mary Jell', 'Villacencio', 'Female', '2006-02-27', 19, 'so.suwa bargy quezon', 'Single', 'pilipino', '6', 'mjbenigay@csr-scc.edu.ph', '$2y$10$7JFjeEv6/CVgnvtkaplCpO8RC6ve9UIncFULvj7QHhazcUQUeg0/a', '09751965540', 'so.suwa bargy quezon san carlos city', 'Donaciano Benigay', 'farmer', 'Zenaida Benigay', 'farmer', 'Donaciano Benigay', 'so.suwa bargy quezon', 'Mae Ann Benigay', 1, 1, 'so.suwa bargy quezon', 'Burlad Elementary School', '2016-2017', 'Quezon National High School', '2023-2024', 'Colegio de Santa Rita de San Carlos', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:39:22', '2025-08-17 23:39:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(19, '2006062701', 'Leonard', 'Brent', 'Rigor', 'Male', '2006-06-27', 19, 'San Carlos City Negros Occ', 'Single', 'Pilipino', 'Catholic', 'leonard@csr-scc.edu.ph', '$2y$10$yvYvEvV26A4jSKQyg04FSuVKdTY9diaDrelpRRC7WBDxix0pC48ze', '09453619525', 'San Carlos City', 'Unkown', 'Unkown', 'Roselyn Rigor Bartolome', 'Housewife', 'Roselyn Rigor Bartolome', 'Ylagan Extension Brgy 6', 'Shirley R Leonard', 1, 1, 'San Carlos City', 'CVGSMS', '2017', 'Julio Ledesma National High School', '2023-2024', 'Collegio De Santa Rita', '2024-2025', 'Information Technology', '2024-2025', 'Information Technology', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:41:41', '2025-08-17 23:41:41', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(20, '1998041201', 'Baybayanon', 'Almar', 'Wasawas', 'Male', '1998-04-12', 27, 'Brgy. Nataban', 'Single', 'Filipino', 'Roman Catholic', 'baybayano@csr-scc.edu.ph', '$2y$10$jFc9/9wOmIGdrTKEIQmWuO/f5dvp.4j1bLAqlL6iRIWXMGuQsOIO6', '09519708233', 'Brgy. Nataban San Carlos City Neg. Occ', 'NA', 'NA', 'Thelma Baybayanon', 'Housewife', 'Maritel Torion', 'Brgy. Nataban San Carlos City', 'Ariel Torion', 1, 1, 'Brgy. Nataban San Carlos City', 'Marago-os Elementary School', '2008', 'Julio Ledesma National High School', '2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:42:21', '2025-08-17 23:42:21', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(21, '2005032201', 'Simblante ', 'Jerome ', 'Villegas', 'Male', '2005-03-22', 20, 'San Carlos', 'Separated', 'N/A', 'Catholic', 'simblante@csr-scc.edu.ph', '$2y$10$MdNE.uUDjzr8ug1ZnGNkM.bO5w7b.zhS7Gn.SPnHdlgNf.Sype.Fu', '09154928436', 'Fatima Village Brgy Rizal', 'Glenn Simblante', 'N/A', 'Jerelyn Simblante', 'N/A', 'Jerelyn Simblante', 'Fatima Village Urban', 'Glenn Simblante', 1, 1, 'Fatima Village Urban', 'Greenville Elementary School', '2016', 'Julio Ledesma National High School', '2003', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:43:07', '2025-08-17 23:43:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(22, '2006063001', 'Tinasas', 'Jasper', 'Allacre', 'Male', '2006-06-30', 19, 'Bocaue Bulacan', 'Single', 'Pilipino', 'catholic', 'tinasas@csr-scc.edu.ph', '$2y$10$b19TEWGtJPn4lbwSR9oA3.4Lqmf4xjCzMt3EJ0A9gUO226TUTbm92', '09704951535', 'Brgy 1 purok ipil-ipil', 'Edmer Tinasas', 'inspector ', 'Jocille Tinasas', 'Housewife', 'Jocille Tinasas', 'Brgy 1 purok ipil-ipil', 'Mother', 1, 1, 'Brgy 1 purok ipil-ipil', 'Parara Elementary School', '2017', 'Julio Ledesma National High School', '2023-2024', 'Collegio De Santa Rita De ', '2024-2025', 'information Technology', '2024-2025', 'Computer', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:43:28', '2025-08-17 23:43:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(23, '2005011701', 'Siaboc', 'Aira Mae', 'Pantonial', 'Female', '2005-01-17', 20, 'Sta.Maria Bulacan, Manila', 'Single', 'Filipino', 'Catholic', 'siaboc@csr-scc.edu.ph', '$2y$10$BW31zd6aESGXs2vTxJCwXOHTAehTQd10zEv0ky6B2fazKhJqloype', '09973347812', 'Brgy Pinowayan, Don Salvador Benidicto Negros Occidental', 'Joenifer SiABOC', 'Farmer', 'Mary Ann Siaboc', 'Housewife', 'Joenifer Siaboc', 'Don Salvador', 'Patrick John Pantonial', 1, 1, 'Don Salvador', 'PINOWAYAN ELEMTARY SCHOOL', '2015-2016', 'JULIO LEDESMA NATIONAL HIGHSCHOOL', '2022-2023', 'CSR', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:44:04', '2025-08-17 23:44:04', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(24, '2001112601', 'Posadas', 'Kyle Edhisson', 'B.', 'Male', '2001-11-26', 23, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Catholic', 'posadas@csr-scc.edu.ph', '$2y$10$hcAFWoU6eBL1XYsGrZPFJ.IjNAnFzjMXWvvuNjInZGtV5wbfhxh12', '09928577456', 'Brgy. Panubigan Canlaon City, Negros Oriental', 'Emmanuel E. Posadas', 'Farmer', 'Ma. Socorro B. Posadas', 'House Wife', 'Ma. Socorro B. Posadas', 'masocorroposadas@gmail.com', 'Maria E. Posadas', 1, 1, 'Brgy. Panubigan Canlaon City, Negros Oriental', 'Brgy. Panubigan Elementary School', '2007 - 2013', 'Colegio de Sto. Tomas-Recoletos', '2014 - 2020', 'Colegio de Sta. Rita de San Carlos Inc.', '2023 - Present', 'None', 'Present', 'None', 'Present', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:46:17', '2025-08-17 23:46:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(25, '2003100401', 'Pesiao', 'Francis Asher ', 'Jimena', 'Male', '2003-10-04', 21, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'pesiao@csr-scc.edu.ph', '$2y$10$u72G6Vb19C6Ux7eh2sy5LO0pbvBlgP8TG7g98VXO2IQr.VKZqHNfC', '09706117620', 'Teachers village, San Carlos City Negros Occidental ', 'None', 'None', 'Shelly G. Jimena', 'Government employee', 'Manolito M. Jimena ', 'San Julio Subd.', 'None', 1, 1, 'Teachers Village ', 'School of the Future ', '2015-2016', 'Colegio De Sto. Tomas Recolletos', '2021-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:46:52', '2025-08-17 23:46:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(26, '2005041101', 'Donan', 'Billy Joseph', 'Deluyas', 'Male', '2005-04-11', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'donan@csr-scc.edu.ph', '$2y$10$wAEVEAEMEa5TxQ59as59teMs8hUgKyn5pYSL.4wp/fRRtvNzpf7IO', '09552281831', 'So. Lawis\r\nBrgy. Buluangan\r\nSan Carlos City\r\nNegros Occidental', 'Alejandro Sabanal Donan', 'Construction Worker', 'Lorna Deluyas Donan', 'Business Woman', 'Lorna Deluyas Donan', 'So. Lawis Brgy. Buluangan San Carlos City Negros Occidental', 'N/A', 1, 1, 'So. Lawis Brgy. Buluangan San Carlos City Negros ', 'Katingal-an Elementary School', '2015-2016', 'Don Carlos Ledesma National High School ', '2022-2023', 'Colegio de San Rita de San Carlos Inc.', '3rd Year', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:47:46', '2025-08-17 23:47:46', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(27, '2005040901', 'Alquezar', 'Elena Marie ', 'Satingasin', 'Female', '2005-04-09', 20, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'alquezar@csr-scc.edu.ph', '$2y$10$VyhmRrIuJ9IXXZ2.YuCKcOpXOsct7AE/HeBVPvu42ZCE1df0twnMq', '09525546751', 'so. calarion ', 'N/A', 'N/A', 'Anna Marie S. Alquezar', 'OFW', 'Ana Lisa Masaba ', 'So. calarion', 'N/A', 1, 1, 'So. Calarion', 'Brgy Rizal proper Elementary School', '2017-2018', 'Julio Ledesma national high school', '2024-2025', 'Colegio De Santa Rita De San Carlos inc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:47:46', '2025-08-17 23:47:46', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(28, '2003032801', 'Altubar ', 'Kissabelle ', 'Estriba', 'Female', '2003-03-28', 22, 'Canlaon City Negros Oriental ', 'Single', 'Filipino ', 'Roman Catholic ', 'altubar@csr-scc.edu.ph', '$2y$10$zIeo75ceW2EQdCY.1zxb1.jn5MSOMyKlyO3oTYbq4AsOTmNRdJwbe', '09631701512', 'Brgy. Lumapao, Canlaon City, Negros Oriental', 'Alfredo Altubar', 'Farmer', 'Alicia Altubar', 'House Wife', 'Alicia Altubar', 'Canlaon city, Negros Oriental ', 'Dona Liza mayagma', 1, 1, 'Brgy, Lumapao. Canlaon City, Negros Oriental ', 'Lower Lumapao Elementary School ', '2007-2012', 'Malaiba High School ', '2013-2019', 'Colegio De Santa Rita De San Carlos, Inc', 'Third Year', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:49:12', '2025-08-17 23:49:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(29, '2005082301', 'Delima', 'Paolo ', 'Morcillos ', 'Male', '2005-08-23', 19, 'Bacolod ', 'Single', 'Filipino ', 'Roman Catholic ', 'delima@csr-scc.edu.ph', '$2y$10$rA9M9AAQ3ABY/faNviL1HuNwQo5/Kwid4p8lFnKI8ZsktMTggmp82', '09610195769', 'Hda. Florida, Barangay Guadalupe, San Carlos City, Negros Occidental ', 'Jeorge Delima', 'Security Guard ', 'Evelyn Delima', 'Housewife', 'Evelyn Delima', 'Hda. Florida, Barangay Guadalupe, San Carlos City, Negros Occidental', 'Jeorge Delima', 1, 1, 'Hda. Florida, Barangay Guadalupe, San Carlos City, Negros Occidental', 'Guadalupe Elementary School', '2016-2017', 'Julio Ledesma National Highschool', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:49:17', '2025-08-17 23:49:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(30, '2004103101', 'Cabataña', 'Rey Andree', 'A', 'Male', '2004-10-31', 20, 'Barangay Bantayanon Calatrava Neg Occ', 'Single', 'Filipino', 'Roman Catholic', 'rcabatana@csr-scc.edu.ph', '$2y$10$VixupNPCwjeAsVoTZXBmS.qknw9HhMKRal/SBi87Jmh3Tj1Rxg1Ge', '09105028369', 'Barangay Bantayanon Calatrava Neg Occ ', 'N/A', 'N/A', 'Lialen A CabatañA', 'House Wife', 'Lialen A Cabataña', 'Barangay Bantayanon Calatrava Neg Occ', 'Dolly A. Cabataña', 1, 1, 'Barangay Bantayanon Calatrava Neg Occ', 'CALATRAVA CHILD DEVELOPMENT CENTER', '2017-20018', 'CALATRAVA NATIONAL HIGH SCHOOL', '2021-2022', 'COLEGIO DE SANTA RITA SANCARLOS,INC', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:49:25', '2025-08-17 23:49:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(31, '2005121201', 'Melancolico', 'Sarah', 'Mentoy', 'Female', '2005-12-12', 19, 'San Carlos City Hospital', 'Single', 'pilipino', 'Baptist', 'melancolico@csr-scc.edu.ph', '$2y$10$DUbP.SYTQqG3EcVmdkMnRu7u98qrqwU.QWUddCAPEDHENZx8nqDeq', '09525073652', 'Purok Kawayan, Sitio Carmen, Brgy.Panubigan, Canla-on City', 'Rosedie G. Melancolico', 'None', 'Mema M. Melancolico', 'Teacher', 'None', 'None', 'None', 1, 1, 'Purok Kawayan, Sitio Carmen, Brgy.Panubigan, Canla-on City', 'Tu-ong Elementary School', '2016-2017', 'Colegio de Sto.Tomas,Inc.', '2023-2024', 'Colegio de Santa Rita de San Carlos,Inc', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:50:49', '2025-08-17 23:50:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(32, '2006022101', 'Saromines', 'Christian', 'Panerio', 'Male', '2006-02-21', 19, 'So. Natuyay Brgy. Codcod San Carlos City Neg. Occ.', 'Single', 'Filipino', 'Catholic', 'csaromines@csr-scc.edu.ph', '$2y$10$LPVJ28UYlMwC7FE7fTVDLeL6/byxXdCuwncBQJIOA.bTfzSe91Qf.', '09813440662', 'So. Natuyay Brgy. Codcod San Carlos City Neg. Occ.', 'Domingo D Saromines', 'Farmer', 'Wenifreda Saromines', 'House wife', 'Wenifreda Saromines', 'So. Natuyay Brgy. Codcod San Carlos City Neg. Occ.', 'None', 1, 1, 'So. Natuyay Brgy. Codcod San Carlos City Neg. Occ.', 'Natuyay Elementary School', '2016-2017', 'Our Lady of the Mountains Mission School', '2023-2024', 'Colegio De Santa Rita De San Carlos', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:51:25', '2025-08-17 23:51:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(33, '2006022201', 'Alinio', 'Mon Jean', 'Sumilhig', 'Female', '2006-02-22', 19, 'Sipaway Island', 'Single', 'Pilipino', 'Catholic', 'alinio@csr-scc-edu.ph', '$2y$10$FzF4blmCtvy/cxT/WL5mgenDb0RADucD4HPL.QdtkLmlNLqE84bMe', '09542575833', 'Sipaway Island', 'N/A', 'N/A', 'Ivy Alinio', 'Housewife', 'Ivy Alinio', 'Ivy Alinio', 'Crisivel Alinio', 1, 1, 'Sipaway Island', 'San Juan Elementary School', '2017-2018', 'Sipaway National High School', '2024-2025', 'Colegio de Sta Rita de San Carlos inc', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:53:33', '2025-08-17 23:53:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(35, '2004041101', 'Abello', 'Faith Krisha', 'Calsa', 'Female', '2004-04-11', 21, 'San Carlos City Neg. Occ.', 'Single', 'Philipino', 'UCC', 'fkabello@csr-scc.edu.ph', '$2y$10$5gVKkYKf.gzckSz5hnmtrO7NMfZrB7eFTr8KLIHsPuoliQRDZ3gK6', '09053420489', 'San Juan Tunga Brgy.5 San Carlos City Neg. Occ.', 'Rey Abello', 'Body guard ', 'Irene Abello', 'Supervisor ', 'Irene Abello', 'San Juan Tunga San Carlos City', 'Christine Mae Abello', 1, 1, 'San Juan Tunga Begy.5 San Carlos City', 'Tandang Sora Elementary School', '2011-2016', 'Julio Ledesma National Highschool ', '2017-2020', 'Colrgio de Santa Rita de San Carlos', '2021-2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:55:42', '2025-08-17 23:55:42', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(36, '2003110101', 'Dejos', 'Brianemanuel', 'Baldomar', 'Male', '2003-11-01', 21, 'San carlos, city hospital', 'Single', 'Filipino', 'Catholic', 'dejos@csr-scc.edu.ph', '$2y$10$EDMcnMkWzV8CwC/j0K5PeOVpy7VLO2gbTDCWqDO/95XyiM9nFjoBO', '09945227161', 'Brgy patun-an Calatrava Negros Occdental', 'Alejandro dejos', 'N/A', 'Ellen dejos', 'BHW', 'April ara dejos', 'Brgy patun-an Calatrava Negros Occdental', 'Sister', 1, 1, 'Brgy patun-an Calatrava Negros Occdental', 'Patun-an elementary school', '2015-2016', 'Colegio de san. colegio de santo tomas recoletos', '2017-2020', 'colegio de santa rita de san carlos inc', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:55:50', '2025-08-17 23:55:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(37, '2004071001', 'Mendoza', 'Christian', 'Rigor', 'Male', '2004-07-10', 21, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Protestant ', 'mendoza@csr-scc.edu.ph', '$2y$10$SnENefLGLHGfxKtfLM.s0OAl/79rtKTZEE.p4K2AXxQZyJ2hOTEaa', '09222615929', 'Chico St San Julio Subd Barangay 2', 'Eric M. Mendoza', 'Deseased', 'Mila R. Mendoza', 'House Wife', 'Lito C. Rigor', 'Chico St San Julio Subd Barangay 2 ', 'Maribel R. Taiño', 1, 1, 'Chico St San Julio Subd Barangay 2', 'Tandang Sora Elementary School ', '2017-2018', 'Julio Ledesma National High School', '2023-2024', 'Collegio De Santa Rita San Carlos Inc.', '2024-2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:56:33', '2025-08-17 23:56:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(38, '2006020201', 'PANTON', 'JOMAR', 'VELASCO', 'Male', '2006-02-02', 19, 'Sancarlos', 'Single', 'Filipino', 'catholic', 'panton@csr-scc.edu.ph', '$2y$10$ao9vSNc089MvWvO05VqAa.8m9vYQwPXgBkafaTMG6PqBZ/xKE/L7i', '09516075615', 'Purok Calumpang brgy1', 'JORDAN', 'N/A', 'MARIA LUZ', 'N/A', 'MARJORIE', 'PUROK CALUMPANG BRGY1', 'N/A', 1, 1, 'PUROK CALUMPANG BRGY1', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:57:30', '2025-08-17 23:57:30', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(39, '2004111001', 'Artaba', 'John Louie Angelo', 'Fajardo', 'Male', '2004-11-10', 20, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'artaba@csr-scc.edu.ph', '$2y$10$6fklBJPow6PiO5IMXSIHaOF5zg6eC/RE86eOsgIIHHsMQF1gDo7UC', '09358660826', 'Don Juan, Barangay 2, San Carlos City, Negros Occidental ', 'n/a', 'n/a', 'desease', 'n/a', 'Vivencia ', 'Urban Phase 3, Barangay Rizal', 'Jerelyn Rapadas', 1, 1, 'don juan, barangay 2, san carlos city', 'Ramon Magsaysay', '2015-2016', 'julio ledesma national high school', '2022-2023', 'colegio de santa rita de san carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-17 23:59:28', '2025-08-17 23:59:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(40, '2004072901', 'Batarilan Jr.', 'Cyrus', 'L.', 'Male', '2004-07-29', 21, 'Brgy.Maaswa, Toboso, Neg. Occ.', 'Single', 'Filipino ', 'Seventh Day Adventist ', 'batarilan@csr-scc.edu.ph', '$2y$10$ZCkBrMGVzyxHe0uixnKjy.wg1rZMl5FYX8TM1eFKHSbhqd/nzemde', '09922855153', 'Brgy.Lipat-on, Calatrava, Neg. Occ.\r\n', 'Cyrus B. Batarilan Sr.', 'Draftsman ', 'Emelie L. Batarilan', 'Business ', 'Emelie L. Batarilan ', 'Brgy. Lipat-on, Calatrava, Neg. Occ', 'None', 1, 1, 'Brgy.Lipat-on, Calatrava, Neg. \r\nOcc.', 'Lemery Elementary School', '1-6', 'East Negros Academy ', '7-9', 'Collegio de St. Rita', '1-3', 'N/A', 'N/A', 'Pres.Diosdado Macapagal High School ', '10-11', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:00:41', '2025-08-18 00:00:41', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(41, '2004082201', 'Artaba', 'AxlJay', 'Fajardo', 'Male', '2004-08-22', 20, 'Brgy Quezon, Sitio Maglunod Purok Pinamantawan San Carlos CIty Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'axlartaba@csr-scc.edu.ph', '$2y$10$viC4pRtWD6hSiPweJtSbo.N52PZ7hHaP7YCsDRBmS3I1ToUnCM7eO', '09262958597', 'Barangay Rizal Phase 3 Fatima Village San Carlos City Negros Occidental', 'Aldios Lama', 'None', 'Jovelyn Artaba ', 'House wife', 'Vivencia Artaba', 'Barangay Rizal Phase 3 Fatima Village San Carlos City Negros Occidental', 'Danilo Artaba', 1, 1, 'Zamboanga City, Sibugay Municipality of Ipil', 'Ramon Magsaysay Elementary School', '2015-2016', 'Julio Ledesma National Highschool', '2022-2023', 'Colegio De Santa Rita De San Carlos, Inc', 'none', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:02:00', '2025-08-18 00:02:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(42, '2006060501', 'Bargayo ', 'Ma. Clouie Junes ', 'Sabandija', 'Female', '2006-06-05', 19, 'San Carlos City Hospital ', 'Single', 'Filipino', 'Roman Catholic ', 'mcjbargayo@csr-scc.edu.ph', '$2y$10$NA9p79R8i0FjGk.ilwrlDenr7.NFxoY5amRW4uSmmvTjxZuXyPCdS', '09949902155', 'Purok Daisy, Brgy. Bagonbon San Carlos City Neg. Occ.', 'N/A', 'N/A', 'Bargayo, Rocel Sabandija ', 'DSWD Teacher ', 'Sorote, Maria Lourdes Bargayo ', 'Brgy. Bagonbon ', 'N/A', 1, 1, 'Brgy. IV D\'rec Press ', 'Bagonbon Elementary School ', '2011-2016', 'Bagonbon National High School ', '2017-2022', 'Colegio de Santa Rita ', '2023-2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 00:02:35', '2025-08-18 02:23:59', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(43, '2005092801', 'Bargamento', 'Miko', 'Torres', 'Male', '2005-09-28', 19, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'bargamento@csr-scc.edu.ph', '$2y$10$1xAWwkfOvssaytwjQwHPwOfZyv2wISl3D9rOTVrQ0A9Cs.QC206jm', '09187094714', 'Don Juan Subd. Brgy.2 San Carlos City Negros Occidental', 'Antonio E. Bargamento', 'Laborer', 'Meredith T. Bargamento', 'Brgy. Secretary', 'N/A', 'N/A', 'N/A', 1, 1, 'Don Juan Subd. Brgy.2 San Carlos City Negros Occidental', 'Ramon Magsaysay Elementary School', '2016-2017', 'Julio Ledesma National High School', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:02:43', '2025-08-18 00:02:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(44, '2002082501', 'Albarico', 'King Agostos', 'Cullamar', 'Male', '2002-08-25', 22, 'Sancarlos City', 'Single', 'Filipino', 'Catholic', 'albarico@csr-scc.edu.ph', '$2y$10$zcR7VO5ZyreBIeu06WnBuuo.Mz.icpcS5Jz.BXx6D9Jz0s3K6Ka6u', '09102971690', 'Barangay Look Calatrava Negros Occidental', 'Luis D Albarico', 'Driver', 'Desiree C Albarico', 'Nurse', 'Virginia C Lumanog', 'Calatrava Negros Occidental', 'Luis C Albarico Jr.', 1, 1, 'Barangay Look Calatrava Negros Occidental', 'Calatrava I Central School', '2010-2011', 'Calatrava National High School', '2021-2022', 'Colegio De Santarita', 'none', 'none', 'none', 'none', 'none', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:09:29', '2025-08-18 00:09:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(45, '1994091401', 'Cabellon', 'John Carlo', 'Akol', 'Male', '1994-09-14', 31, 'Marilao bulacan', 'Single', 'Filipino ', 'Roman Catholic ', 'cabellon@csr-scc.edu.ph', '$2y$10$w79LZu8F36RIBk4.1PSxpum4CnswBCj5i50RK/yDFL.9mqh6Bz.OS', '09811796714', 'Urban phase 4, block 4, lot 17 Brgy Rizal, San Carlos City Negros Occidental', 'John Edison M. Cabellon', 'Electrical government employee ', 'Eva A. Cabellon', 'House wife', 'None', 'None', 'None', 1, 1, 'Urban phase 4, block 4 - lot 17, Brgy Rizal, San Carlos City Negros Occidental ', 'Ramon Magsaysay Elementary School', '2006-2007', 'Julio Ledesma National High School', '2011-2012', 'Collegio de sta Rita Inc', '2024-2025', 'Tesda Smaw Nc1-Nc2', '2015', '', '', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 00:10:16', '2025-12-21 05:19:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(46, '2004061101', 'Macatol', 'Catherine Anne', 'Estrada', 'Female', '2004-06-11', 21, 'Cagayan de Oro City Misamis Oriental', 'Single', 'Filipino', 'Catholic', 'catherine10@csr-scc.edu.ph', '$2y$10$n2zbiKaD7rKFnqJ5THgT0e9v1lV28DCro.CtW/BvYGJrGq8AZJniy', '09542511040', 'Campanilla St. San julio Subd. ', 'Geoffrey C. Macatol', 'Deceased', 'Mariebique E. Macatol', 'Stay at home', 'Mariebique E. Macatol', 'Campanilla St. San Julio Subd. ', 'Kenneth Carmellita M. Enriquez', 1, 1, 'Campanilla St. San Julio Subd. ', 'Colegio de Sta. Rita San Carlos Inc. ', '2012-2017', 'Colegio de Sta. Rita San Carlos Inc. ', '2017-2022', 'Colegio de Sta. Rita San Carlos Inc. ', '2025-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:17:27', '2026-02-27 03:30:13', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(47, '2006042201', 'Maglasang', 'Reghine Faith', 'N/A', 'Female', '2006-04-22', 19, 'Toboso', 'Single', 'Filipino', 'Catholic', 'maglasang@csr-scc.edu.ph', '$2y$10$mq2ntWiF4y4eBjTQhEc6PuZokd.kZqmC6f07JzI9B8p.TJHiQWGZ.', '09948608556', 'Baranggay Lo-ok, Calatrava, Negros Occidental', 'N/A', 'N/A', 'Ghadgiven M. Baba', 'Call Center Agent', 'Ghadgiven M. Baba', 'Brgy Lo-ok  Calatrava, Negros Occidental', 'Reiyzabelle Maglasang ', 1, 1, ' Negros Occidental', 'Calatrava 1 Central School, Negros Occidental', '2016-2017', 'Calatrava National High School ', '2021-2022', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:21:00', '2025-08-18 00:21:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(48, '2003072201', 'PATRIA', 'KEN HAROLD', 'N/a', 'Male', '2003-07-22', 22, 'Manila,Pasay City', 'Single', 'Filipino ', 'ROMAN CATHOLIC ', 'kpatria@csr-scc.edu.ph', '$2y$10$UDFzxc2eyf0k9oSLZ1wJo.ZxSQ314rUF3/XDbqagMoDk3qhlhVigG', '09659533745', 'So. Lawis BRGY. Buluangan San Carlos City Negros Occidental ', 'N/a', 'N/a', 'Florinda Patria ', 'Unemployed', 'Juvylyn Parlocha', 'So. Lawis BRGY. Buluangan San Carlos City Negros Occidental ', 'N/a', 1, 1, 'So. Lawis BRGY. Buluangan San Carlos City Negros Occidental ', 'Katingal an Elementary School ', '2015-2016', 'Don Carlos Ledesma National High School ', '2022-2023', 'Colegio De Sta. Rita De San Carlos City', '2024-2025', 'N/a', 'N/a', 'N/a', 'N/a', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:21:10', '2025-08-18 00:21:10', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(49, '2005032301', 'Palay', 'Zen Angelo', 'Tiloy', 'Male', '2005-03-23', 20, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Protestant ', 'palay@csr-scc.edu.ph', '$2y$10$Wsz9fyOa/6a70YH.yzcCOeIccPpbowKtJUzaq/sws6Y3r6OPRD9au', '09166448113', 'Campo Siete, Brgy. V, San Carlos City, Negros Occidental', 'Manolo L. Palay', 'N/A', 'Liez May T. Palay', 'Teacher', 'Liez May T. Palay', 'Liez May T. Palay', 'Fe Faith T. Quiatchon ', 1, 1, 'Campo Siete, Brgy. V, San Carlos City, Negros Occidental', 'Andres Bonifacio Central School ', '2016-2017', 'Tañon College', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:22:36', '2025-08-18 00:22:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(50, '2003112801', 'Temlor', 'John Fel', 'Suson', 'Male', '2003-11-28', 21, 'Calatrava', 'Single', 'Pilipino', 'Catholic', 'jtemlor@csr-scc.edu.ph', '$2y$10$vms.INjV2b5vqyF61kxhyehGl1777a7ry7IAUc0OsE/CtlnuOD1b6', '09656200814', 'Urban phase 1, Fatima Vill. Brgy Rizal', 'Federico Temlor', 'Ukay-ukay', 'Junalyn Temlor', 'OFW', 'Junalyn Temlor', 'Urban phase 1, Fatima Vill Brgy Rizal', 'Tapdasan Family', 1, 1, 'Urban Phase 1, FAtima Vill. Brgy Rizal', 'Greenville Elem. School', '2010', 'Julio Ledesma National High School', '2021-2022', 'Collegio De Sta Rita ', '2024-2025', 'Information Technology', '2024-2025', 'Computer ', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:23:27', '2025-08-18 00:23:27', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(52, '2003060101', 'Gordove ', 'Maecaella ', 'Ramos ', 'Female', '2003-06-01', 22, 'General Mariano Cavite Avenue ', 'Single', 'Filipino ', 'VI', 'maecaella@csr-scc.edu.ph', '$2y$10$IHSG6QbiZv8YwwL5S2ImSebKke7qVeiGRDc4HYmqBcq6YTADiCLzm', '09859408467', 'San Juan baybay Brgy.6 San Carlos City Negros Occidental ', 'Camilo Padicer Gordove ', 'Motordriver', 'Marites Ramos Gordove ', 'House wife', 'N/A', 'N/A', 'Maribel Ramos Gordove ', 1, 1, 'San Juan baybay Brgy.6 San Carlos City Negros Occidental ', 'Congressman Vincent Gustillo Senior Memorial School ', 'June 2016 - April 20', 'Julio Ledesma National High School ', 'June 2017 - April 20', 'Colegio de Santa Rita de San Carlos, Inc. ', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 00:59:45', '2025-08-18 00:59:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(53, '2004070301', 'Eslais', 'Kenn Jay', 'Abadies ', 'Male', '2004-07-03', 21, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'eslaiskennjay@gmail.com', '$2y$10$ZE3WV6s2QQM26U5NMgGwNeN3jdAljhS.7OfHStSkMablKW16eaa8W', '09933864853', 'Barangay punao,\r\n San Carlos City, NIR', 'Ronaldo A. Eslais', 'Mechanic ', 'Jeramie A. Eslais', 'House wife ', 'None', 'None', 'None', 1, 1, 'Barangay Punao, San Carlos, City NIR ', 'Talave elementary school ', '2014-2015', 'Colegio de san rita', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:00:33', '2025-08-18 01:00:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(54, '2003021001', 'Mariano', 'James Emmanuel', 'Bustillo', 'Male', '2003-02-10', 22, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'jmariano@csr-scc.edu.ph', '$2y$10$ZVRdUyWmNmRuLLDF0.AV/eT7S3NnTWNshTXshOCjiUNZwXdqbREOu', '09278847605', 'Ruby St. Teachers Village San Carlos City', 'Melvin James M. Mariano', 'Businessman', 'Monina Cleone B. Mariano', 'businesswoman ', 'Nin Camille B. Mariano', 'Ruby St. Teachers Village', 'Sister', 1, 1, 'Ruby St. Teachers Village San Carlos City', 'Ramon Magsaysay Elementary School', '2014-2015', 'Colegio de Sto. Tomas Recoletos', '2021-2022', 'Colegio de Sta. Rita de San Carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:13:09', '2025-08-18 01:13:09', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(55, '2002041301', 'Villari ', 'Adrian Mark', 'Perez', 'Male', '2002-04-13', 23, 'San Carlos City ', 'Single', 'Filipino', 'Roman Catholic', 'villarin@csr-scc.edu.ph', '$2y$10$R4kIOWG5Aad8TUevApEnxOA4IcAa9Jtp0nTPkedLKerMo/qlKOoeS', '09703343566', 'Ledesma Heights Barangay Palampas', 'Aligen Villarin', 'Overseer( GHFMPC)', 'Ma. Jenalie Perez', 'Housewife', 'Aligen Villarin', 'Ledesma Heights Barangay Palampas', 'Brother', 1, 1, 'Ledesma Heights Barangay Palampas', 'Ramon Magsaysay Elementary School ', '2013-2014', 'Julio Ledesma National High School', '2019-2020', 'Colegio de Sta. Rita de San Carlos Inc.', '2025-2026', 'Smaw NC 2 & Automative Servicing NC 1', '2017 & 2020', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:15:26', '2025-08-18 01:15:26', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(56, '2004042201', 'Hojilla', 'Wacky', 'Delfin', 'Male', '2004-04-22', 21, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'hojilla@csr-scc.edu.ph', '$2y$10$vwVkoVEGfKqcPyfQSJTiGepFgshdC.vnIqCrAih.PG5TgkhomHVz.', '09305620165', 'Prk. Gemelina Brgy 1, San Carlos City', 'Jimmy Hojilla', 'Retired', 'Ma. Cecilia Hojilla', 'HomeMaker', 'N/A', 'N/A', 'N/A', 1, 1, 'Prk. Gemelina Brgy 1, San Carlos City', 'Florentina Ledesma Elementary School', '2015-2016', 'Julio Ledesma National Highschool', '2019–2020', 'Colegio de Santa Rita', '2025–2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:18:04', '2025-08-18 01:18:04', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(60, '2003082901', 'Bargasa', 'Cyril', 'Leopardas ', 'Male', '2003-08-29', 21, 'Manila City', 'Single', 'pilipino', 'Catholic ', 'bargasa@csr-scc.edu.ph', '$2y$10$CwxDUwQkktqp.32WNTlNT.tW9c3KuUk2Q3OsKAUW9SdW8JbX1YUxm', '09663924006', 'So.Trozo Brgy Buluangan San Carlos City Neg Occ', 'Christopher Bargasa', 'N/A', 'Rona Leopardas ', 'N/A', 'Melojane Fajardo', 'So. Trozo Brgy Buluangan San Carlos City Neg Occ', 'Sister', 1, 1, 'So.Trozo Brgy Buluangan \r\n', 'Trozo Elementary School ', '2015 - 2016', 'Don Carlos Ledesma National High School ', '2021 - 2022', 'Colegio de Santa Rita De San Carlos Inc.', '2025 - 2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:18:38', '2025-08-18 01:18:38', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(61, '2003020901', 'Bolo', 'Wilson', 'Estrelloso', 'Male', '2003-02-09', 22, 'Looc 2, Poblacion Vallehermoso', 'Single', 'Filipino', 'Catholic', 'wilsonbolo09@gmail.com', '$2y$10$0HTmC6O2VW/LCSzhcOnTpeKi1ZsRcMeN43vKN3Q/l3N1GcqRW1jtC', '09306183277', 'Poblacion Vallehermoso, Negros Oriental', 'Franciso Bolo', 'Currently Farmer', 'Analy Bolo', 'Idk', 'Nina Sedoriosa', 'Poblacion Vallehermoso, Negros Oriental', 'Nifrea Bolo', 1, 1, 'Poblacion Vallhermoso, Neg. Or', 'Vallehermoso Elementary School, Suay Baguio Ren̈a elem school, Davao City', '2008-2013', 'Saint Francis Highschool Vallehermoso, Colegio De St. Rita SCC', '2014-2019', 'Colegio De Sta. Rita', '2020-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:20:07', '2025-08-18 01:20:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(62, '2005073001', 'Perez', 'Drunreb', 'Villacampa', 'Male', '2005-07-30', 20, 'So Pano olan', 'Single', 'Filipino', 'Roman Catholic', 'perez@csr-scc.edu.ph', '$2y$10$a3DboDB7G3BzQv7ZiqZzpOC9.UM7WvO6NfVla7/xivEDD8kS1OoNu', '09672287562', 'So Pano olan Brgy Guadalupe\r\n', 'N/A', 'N/A', 'Mary Jan Villacampa', 'Ofw', 'Freddie Villacampa', 'So Pano olan', 'Rizalyn Villacampa', 1, 1, 'So pano olan', 'Pano olan elementary school', '2016-2017', 'Don Carlos Ledesma National High School', '2022-2026', 'Colegio de Santa rita de Sancarlos Inc', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:20:28', '2025-08-18 01:20:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(63, '2001111501', 'Ere-er ', 'Laurence Dave ', 'Lacumba', 'Male', '2001-11-15', 23, 'San Carlos City Negros Occ', 'Single', 'Filipino', 'Roman Catholic ', 'ere-er@csr-scc.edu.ph', '$2y$10$byILRK9CW43lnSUtutL36OfLrqB777Z/Jw1RGnIh8BC0VWuaobPsC', '09360748077', 'Greenville subd Brgy Rizal San Carlos City Negros Occ', 'Rodgemer B. Ere-er', 'Vendor', 'Michelle L. Ere-er', 'Vendor', 'Rodgeme B. Ere-er', 'Greenville subd Brgy Rizal San Carlos City Negros Occ', 'Merlinda Ere-er', 1, 1, 'Greenville subd Brgy Rizal San Carlos City Negros Occ', 'Tandang Sora Elementary School ', '2007', 'Julio Ledesma National High School ', '2016', 'Colegio de Sta Rita de San Carlos,INC.', '2020', 'Colegio de Sta Rita de San Carlos,INC.', '2020', 'Colegio de Sta Rita de San Carlos,INC.', '2020', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:20:34', '2025-08-18 01:20:34', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(64, '2006071401', 'Cosipag ', 'Jamella', 'Venting ', 'Female', '2006-07-14', 19, 'San Carlos City Hospital ', 'Single', 'Philippines ', 'Catholic ', 'jellacosipag@gmail.com', '$2y$10$x9FPOufdsWj52xWpsWVKp.QOcu9v.inYhjmeQfRslCLlUqOnC2yVK', '09850530278', 'Metro village habitat palampas ', 'Jose Rem Cosipag', 'Driver ', 'Carmela Venting ', 'Caregiver ', 'Carmela venting ', 'Metro village habitat ', 'Carmela Venting ', 1, 1, 'Habitat metro village, palampas', 'San Carlos milling incorporated elementary school ', 'N/A', 'Julio ledsma National highschool ', '2023-2024', 'Colegio de Santa rita ', '2024-2025', 'Information technology ', '2024-2025', 'Information technology ', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:22:35', '2025-08-18 01:22:35', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(65, '2006061401', 'Temlor', 'Rodsel', 'Baldon', 'Male', '2006-06-14', 19, 'Brgy.San juan', 'Single', 'filipino', 'uccp', 'rtemlor@csr-scc.edu.ph', '$2y$10$vw5bpgInzcKbUzoACpY/XefY.NpQaqOaBw5G5Bj3mjP5FpOcEbqSK', '09056762998', 'Brgy.San Juan', 'Roderick Temlor', 'Cashier', 'Fresel baldon', 'Brgy worker', 'Felomina temlor', 'Brgy. San Juan', 'Mother', 1, 1, 'Brgy. San Juan', 'Valencia Central Elementary  School', '2016-2017', 'Sipaway National High School', '2023-2024', 'Colegio De Santa Rita', '2024-2025', 'Information Technology', '2024-2025', 'Computer', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:29:51', '2025-08-18 01:29:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(66, '2003093001', 'Despi', 'Jomari', 'Tuso', 'Male', '2003-09-30', 21, 'San Carlos city', 'Single', 'Filipino', 'Roman Catholic ', 'jdespi@csr-scc.edu.ph', '$2y$10$1jKf5dlMJk49LyjRM1QINebjPt1UsqJRCEngeqjhFYSQzt5xDlNmy', '09308559945', 'Purok lubi, brgy.1, San Carlos city, Negros Occidental ', 'Juandro S. Despi', 'Driver', 'Marisol T. Despi', 'Housewife', 'N/A', 'N/A', 'N/A', 1, 1, 'Purok lubi, brgy.1, San Carlos city, Negros Occidental', 'FLORENTINA LEDESMA ELEMENTARY SCHOOL', '2015-2016', 'JULIO LEDESMA NATIONAL HIGHSCHOOL', '2019-2020', 'COLEGIO DE SANTA RITA DE SAN CARLOS,INC', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:30:37', '2025-08-18 01:30:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(67, '2002092601', 'Roca', 'Kidrick', 'Aguanta', 'Male', '2002-09-26', 22, 'San Carlos City ', 'Single', 'Filipino', 'Catholic', 'roca@csr-scc.edu.ph', '$2y$10$BYaRK.1JMtk3hRqTOy4TYu0UHtNTqP77PYMce7nkyItgOvCOfy3Wa', '09102024925', 'Brgy. Palampas San Carlos City Negros Occidental ', 'Gary A. Roca', 'N/A', 'Marivic A. Roca', 'Sari sari store', 'Marivic Roca', 'Brgy. Palampas San Carlos City ', 'N/A', 1, 1, 'Brgy. Palampas San Carlos City ', 'Dulong bayan bacoor cavite elementary school ', '2015-2016', 'Julio ledesma national high school ', '2020-2021', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:45:28', '2025-08-18 01:45:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(68, '2004080301', 'Larida ', 'John mark ', 'Silvano ', 'Male', '2004-08-03', 21, 'Don Salvador ', 'Single', 'Filipino ', 'Born again ', 'laridajohnmark77@gmail.com', '$2y$10$2T/FpJ/2qnJFSqCl13pdDulRccmajFu3zxbIyBT4JDHu/peVPY4..', '09129611354 ', 'Kainggat tower brgy 1', 'Johnie larida', 'Farming ', 'Rhesa Larida ', 'House wife ', 'Rhesa Larida ', 'Prk citrus brgy bunga don Salvador ', 'Sarnel Silvano ', 1, 1, 'Prk citrus brgy bunga don Salvador Benedicto Negros Occidental ', 'Benejiwan elementary school ', '2015-2016', 'Sofronio Carmona memorial national high school ', '2019-2020', 'Colegio de santa rita ', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:47:00', '2025-08-18 01:47:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(69, '2002050801', 'Solitario ', 'Mikko', 'Rojo', 'Male', '2002-05-08', 23, 'Brgy. Refugio Calatrava neg occ', 'Single', 'Filipino', 'Catholic', 'solitario@csr-scc.edu.ph', '$2y$10$pXSaXwxceY3gNM3osn9W1OFtq9XyJCt434SKV4a1JnfQmtmUGVfwi', '09917103956', 'San carlos city neg occ. brgy.1 newtown subd.', 'Jose ruel solitario', 'Time keeper', 'Marivit solitario', 'Ofw', 'Marielle solitario cabunagan', 'Brgy.1 newtown subd', 'Maruel solitario', 1, 1, 'Brgy.1 newtown subd', 'Menchaca ', '2014-2015', 'Julio ledesma national high school', '2018-2019', 'Colegio de sta. Rita', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:47:02', '2025-08-18 01:47:02', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(71, '2003090101', 'Amoy', 'Christian marc', 'Batolina', 'Male', '2003-09-01', 21, 'Kabankalan City ', 'Single', 'Filipino ', 'Catholic', 'amoy@csr-scc.edu.ph', '$2y$10$ijC9OxfdEr9rdgqnBCPm6ehCfjqsYmWYE/66nnBV8QUu7XwgMfHA.', '09166147621', 'Broce street San Carlos City', 'Marc Eggdon Amoy', 'NA', 'Karen Grace batolina', 'NA', 'Jocelyn Algarme Amoy', 'Broce Street San Carlos City', 'NA', 1, 1, 'Broce Street San Carlos City', 'Tandang Sora Elementary School', '2015-2016', 'Tañon', '2019-2020', 'Colegio de Sta. Rita', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:47:32', '2025-08-18 01:47:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(72, '2001100501', 'Baniquid ', 'James', 'Pelarion ', 'Male', '2001-10-05', 23, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'baniquid@csr-scc.edu.ph', '$2y$10$TU/hGyZPioi/aQhJzdjm9..pCtw7fU/Cwa3HMywwPoms47v45VJRy', '09701979146', 'Broce Street', 'Anecito Lorilla Amoy', 'Retired Bank Manager ', 'Jocelyn Algarme Amoy', 'Retired Nurse ', 'Anecito Lorilla Amoy ', 'Broce Street ', 'None', 1, 1, 'Broce Street ', 'Tandang Sora Elementary School ', '2015-2016', 'Tañon College ', '2019-2020', 'College of Saint Rita', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 01:50:51', '2025-08-18 01:50:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(73, '1998092201', 'Empalmado', 'Rico', 'Hilbuena', 'Male', '1998-09-22', 26, 'Zamboanga city', 'Single', 'Filipino ', 'Roman Catholic ', 'empalmado@csr-scc.edu.ph', '$2y$10$Ye59DYHoL9b30FhPNbi/UOiwtVjdATvtRalCtZuSzpqWLkwMGMTtG', '09451370464', 'Burgost st. , brgy6 ', 'Noli ', 'Factory worker ', 'Sabrina ', 'House wife ', 'Sabrina', 'Burgos st., brgy 6', 'Noli', 1, 1, 'Burgos st. , brgy 6', 'CVGSMS', '2012-2013', 'Julio ledesma national high school ', '2016-2017', 'Colegio de Santa Rita de San Carlos ', '2025', 'Nan', '2025', 'Nan', '2025', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 01:53:16', '2025-08-18 06:35:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(74, '2003031101', 'Ramsey', 'John Herbert', 'Costanilla', 'Male', '2003-03-11', 22, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'ramseyj879@gmail.com', '$2y$10$ryEgEn/tff2r3Cf1.gml5OtQ2hiA.TAnEtWr1uPEW.laX/.9jF0ii', '09632176765', 'Urban phase 4', 'Hernane C, Ramsey', 'Government employee', 'Elena C, Ramsey', 'OFW', 'Lina C, Calungsag', 'Araneta', 'Jimmy Ramsey', 1, 1, 'Urban phase 4 Barangay Rizal San Carlos City Negros Occidental', 'Tandang Sora Elementary School', 'Grade 6', 'Julio Ledesma National Highschool', 'Grade 12', 'Colegio de Santa Rita de San Carlos, Inc.', '4th year', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:00:06', '2025-08-18 02:00:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(75, '2004010401', 'Romano', 'Janvee', 'Martisano', 'Male', '2004-01-04', 21, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'janveeromano44@gmail.com', '$2y$10$Rr4./OM9BNLtt3HB6/gen.RIwgWEc/w/cy11HJMZUMvQEH7bcLPJy', '09667482935', 'Rovirih Heights, Barangay Palampas, San Carlos City, Negros Occidental', 'Ryan Lantin', 'N/A', 'Ivy Romano', 'N/A', 'Adora Romano', 'Rovirih Heights, Brgy. Palampas, SCC, Neg. Occ.', 'N/A', 1, 1, 'Rovirih Heights, Brgy. Palampas, SCC, Neg. Occ.', 'Ramon Magsaysay Elementary School', '2016 - 2017', 'Tañon College', '2021 - 2022', 'Colegio de Santa Rita de San Carlos, Inc.', '2025 - 2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:04:43', '2025-08-18 02:04:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(76, '2004072101', 'Bagacay', 'Joemarie Jeff', 'Bayato', 'Male', '2004-07-21', 21, 'Canlaon City', 'Single', 'Philippines ', 'Roman Catholic ', 'bagacay@csr-scc.edu.ph', '$2y$10$bhMbpYMk0bnMIwzoieg/6u92bB3iYY0uoWpYFJTRtlxkuIZIY24z6', '09451367317', 'Canlaon City', 'Antonio Ponsica Bagacay Jr.', 'Politician', 'Marivel Bayato Bagacay', 'Housewife ', 'Marivel Bagacay', 'Canlaon City', 'Brother\'s', 1, 1, 'Canlaon City', 'Brgy. Panubigan Canlaon City', '2014-2015', 'Saint Joseph College of Canlaon Inc.', '2021-2022', '.', '.', '.', '.', '.', '.', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:14:56', '2025-08-18 02:14:56', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(77, '2006062801', 'Alegado', 'John Vincent', 'Mayagma', 'Male', '2006-06-28', 19, 'San Carlos City Hospital', 'Single', 'Filipino', 'Roman Catholic', 'jalegado@csr-scc.edu.ph', '$2y$10$9WXGRgGxoj4.AVOMk/aPX.kWmvE9Jq0eIun3qfEMbWi/pSL7tAvte', '09515729853', 'Urban Phase 6, SCC', 'Joselito P. Alegado', 'Working', 'Vickai I. Mayagma', 'HouseWife', 'Vickai I. Mayagma', 'Urban Phase 6, SCC', 'N/A', 1, 1, 'Urban Phase 6, SCC', 'Ramon Magsaysay Elementary School', '2016-2017', 'Tanon College', '2017-2024', 'Colegio De Santa Rita', '2023-Present', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:17:18', '2025-08-18 02:17:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(78, '2004100501', 'Panerio', 'Cherissa Mari', 'Morden', 'Female', '2004-10-05', 20, 'Barangay Quezon', 'Single', 'Filipino', 'Roman Catholic', 'cmpanerio@csr-scc.edu.ph', '$2y$10$GHQ30I1/3G9xMhx2BorKLuot.c.aV796mAGuJ/bs/ZP1CHrfey9ce', '09666374071', 'Sitio Natutay, Brgy. Codcod, San Carlos City', 'Erwin Panerio', 'Farmer', 'Mirasol Panerio', 'Housewife', 'Mirasol Panerio', 'Sitio Natuyay, Brgy. Codcod, San Carlos City', 'N/A', 1, 1, 'Sitio Natuyay, Brgy. Codcod, San Carlos City', 'Natuyay Elementary School', '2011-2016', 'Quezon National High School', '2017-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:18:08', '2025-08-18 02:18:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(79, '2000030801', 'Hammerton', 'Robert Bruce', 'Canlobo', 'Male', '2000-03-08', 25, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'rhammerton078@gmail.com', '$2y$10$dW0RNzgfowUCcAJrgwUA8emz7jUW0KTwHXDXwKExhzjpkIXpsXxKG', '09671785466', 'Purok 1, Barangay Ermita, Sipaway Island\r\nSan Carlos City, Negros Occidental.', 'Robert Bruce Hammerton', 'English Mentor', 'Cinesa C. Hammerton', 'Deceased', 'Teresita Dacumos', 'Purok 1, Barangay Ermita, Sipaway Island, San Carlos City. Negros Occidental', 'Sir Bubong', 1, 1, 'Purok 1, Barangay Ermita, Sipaway Island, San Carlos City, Negros Occidental', 'Majayjay Elementary School', '2006 – 2012', 'Sipaway National High School', '2012 - 2018', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:34:48', '2025-08-18 02:34:48', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(80, '2002093001', 'Mamac', 'Francis Angelo', 'Pancho', 'Male', '2002-09-30', 22, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'angelo12@csr-scc.edu.ph', '$2y$10$Gse5z1VTEnK7LlR3q0RdBuJB5NS0obwakzMN.NwSXQ69Pap6Ki7AS', '09672545035', 'Locsin St. Brgy. IV, San Carlos City, Negros Occidental', 'Francisco Mamac Jr.', 'Head Teacher', 'Mona Mercedes Mamac', 'Teacher', 'N/A', 'N/A', 'N/A', 1, 1, 'Locsin St. San Carlos City, Neg. Occ.', 'Ramon Magsaysay Elementary School', '2014-2015', 'Juloio Ledesma National High School', '2018-2019', NULL, NULL, 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:35:10', '2025-08-18 02:35:10', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(81, '2004041401', 'Esguerra', 'Earl Vincent Son', 'Marcial', 'Male', '2004-04-14', 21, 'Canlaon City', 'Single', 'Filipino', 'Roman Catholic', 'esguerra@csr-scc.edu.ph', '$2y$10$2yNoFkMUJH4xuWrp1RpmL.cWk2qyjerKBT6TNj7wJh6wuYmc3utzy', '09064180230', 'Ulay, Vallehermoso Negros Oriental', 'Sonny P. Esguerra', 'Driver', 'Ma. Chona M. Esguerra', 'HouseWife', 'Milagros C. Marcial', 'Ulay, Vallehermoso Negros Oriental', 'N/A', 1, 1, 'Ulay, Vallehermoso Negros Oriental', 'Ulay Elementary School', '2015-2016', 'St. Francis High School Inc.', '2021-2022', 'Colegio De Santa Rita De San Carlos Inc.', '2023-Present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:37:03', '2025-08-18 02:37:03', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `students` (`id`, `id_number`, `last_name`, `first_name`, `middle_name`, `gender`, `birth_date`, `age`, `birth_place`, `civil_status`, `nationality`, `religion`, `email`, `password`, `contact_number`, `home_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_address`, `other_support`, `is_boarding`, `with_family`, `family_address`, `elem_address`, `elem_year`, `sec_address`, `sec_year`, `college_address`, `college_year`, `voc_address`, `voc_year`, `others_address`, `others_year`, `form138`, `moral_cert`, `birth_cert`, `good_moral`, `others1`, `others2`, `others3`, `notes`, `created_at`, `updated_at`, `is_active`, `profile_photo`, `lrn_no`, `contact_person`, `form137`, `parents_marriage_cert`, `baptism_cert`, `proof_income`, `brown_envelope`, `white_folder`, `id_picture`, `esc_app_form`, `esc_contract`, `esc_cert`, `shsvp_cert`) VALUES
(82, '2007101201', 'Arsuento', 'Jhunrey', 'Alcala', 'Male', '2007-10-12', 17, 'Hospitality of San Carlos, San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'jhunrey@csr-scc.edu.ph', '$2y$10$L3NPXuC3M0Aq.8UYz86r5.zhrN6rM5grQiAWhvDeQUB89IPu3eRRi', '09606170373', 'Brgy Suba, Calatrava, Negros Occidental', 'Jesus C Arsuento', 'Vendor', 'Estrellita A Arsuento', 'Midwife', 'Estrellita A Arsuento', 'Brgy Suba, Calatrava Negros Occidental', 'Jesseca A Arsuento', 1, 1, 'Brgy Suba, Calatrava Negros Occidental', 'Calatrava 1 Central School', '2013-2019 ', 'Calatrava National High School', '2019-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:37:40', '2025-08-18 02:37:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(83, '2006042101', 'Solitario ', 'Kier', 'Salapa', 'Male', '2006-04-21', 19, 'Negros Occidental San Carlos City ', 'Single', 'Pilipino', 'Catholic ', 'kensolitario62@gmail.com', '$2y$10$sBuVX9xmViWGmVG9VyeRGOWFlLz8YeBHP3fWrRRg./A/6upCZFIlO', '09306074700', 'Sta.Felomina takas upper', 'No father ', 'None', 'Mary Grace Solitario ', 'House wife', 'Ma.teresa repel', 'San Carlos City Sta.Felomina', 'Lai Solitario ', 1, 1, 'Negros Occidental San Carlos City \r\n', 'Ramon Magsaysay elementary school ', '1-6', 'Julio Ledesma national high school ', '7-12', 'Colegio de Santa Rita de San Carlos inc.', 'Year 1', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:38:29', '2025-08-18 02:38:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(84, '2006121901', 'Dela Cruz', 'John Emmanuel', 'Bawin', 'Male', '2006-12-19', 18, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Catholic', 'jcruz@csr-scc.edu.ph', '$2y$10$uNq9JrsRSZHJLeK.YCbezOo79/VmQdrIywiOlyzAgrfXvgdVYtB8W', '09608584351', 'St Charles Block 4 Lot 16 Brgy 1', 'Cesar Dela Cruz', 'Welder', 'Julieta Dela Cruz', 'Housewife', 'Julieta Dela Cruz', 'St Charles Block 4 Lot 16 Brgy 1', 'none', 1, 1, 'St Charles Subd Block 4 Lot 16', 'School Of The Future ', '2018', 'Tanon College', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:40:53', '2025-08-18 02:40:53', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(85, '2005122401', 'Bitoca', 'Sherry Mae', 'Pacaldo', 'Female', '2005-12-24', 19, 'San carlos city', 'Single', 'Filipino', 'Roman Catholic', 'dearmonsheri@gmail.com', '$2y$10$4C7fDYQa/gDBPbNihnb62uAQ.CY7cIAZhBUsAtTXdOJ0SRs3OsRJy', '09921586244', 'Ylagan Extension Street San Carlos City Negros Occidental\r\n', 'Medel G. Bitoca', 'Self Employed ', 'Charlyn P. Bitoca', 'Housewife ', 'Charlyn P. Bitoca', 'Ylagan Extension Street San Carlos City Negros Occidental', 'Medel G. Bitoca', 1, 1, 'Ylagan Extension Street San Carlos City Negros Occidental\r\n', 'Tandang Sora Elementary School', '2016-2017', 'Tañon College', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:41:18', '2025-08-18 02:41:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(86, '2006082301', 'Malaay', 'Clyde', 'Canillo', 'Male', '2006-08-23', 18, 'Vallehermoso ', 'Single', 'Filipino', 'Roman Catholic', 'clyde@csr-scc.edu.ph', '$2y$10$ybuD8keJ8yyRsrE60pGOMeLdx1iqO05RsUu5NIEQmLvAXphxnjdv6', '09930036716', 'Brgy Maglahos Vallehermoso Nagros Oriental ', 'Judito ', 'None', 'Herminigilda ', 'Sari Sari store Owner', 'Herminigilda ', 'Brgy Maglahos ', 'Lou Malaay', 1, 1, 'Bragy Maglahos Vallehermoso ', 'Maglahos Elementary School', '2012-2013', 'Tagbino National High School ', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:42:08', '2025-08-18 02:42:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(87, '2001122901', 'Peromingan', 'Rojhon', 'Lorezo', 'Male', '2001-12-29', 23, 'Calatrava Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'perominganrojhon9@gmail.com', '$2y$10$fWDFKRGRU1OlN6FRIe84Puqva0JgbS1SVM5AGrUDsXszCT3Ns7uMa', '09955721032', 'Don Roberto, Llantada, Street brgy Suba', 'Allan L. Peromingan', 'Retired Noneco', 'Girlie L. Peromingan ', 'Market Vendor', 'Girlie L. Peromingan', 'Don Roberto, Llantada Street brgy Suba', 'Kevin Klaine L. Peromingan', 1, 1, 'Barangay Suba, (Pob)', 'Calatrava 2 Central School', '2014-2015', 'Calatrava National High School ', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:42:22', '2025-08-18 02:42:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(88, '2006120601', 'Genon', 'Christi James', 'Rigor', 'Male', '2006-12-06', 18, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Catholic', 'cgenon@csr-scc.edu.ph', '$2y$10$Aw.JGQKkqAQMcrkDcAuYFuS.yuZTb00R2A3vNADQifxqHyjaqYgYm', '09945107012', 'Hope Street Brgy. V', 'Ryan Ybanez Genon', 'Motorcycle Driver', 'Ma. Roniza Rigor Genon', 'Private Employee', 'Ma. Roniza Rigor Genon', 'Hope Street Brgy. V', 'Ryan Ybanez Genon', 1, 1, 'Hope Street Brgy. V', 'CVGSMS', '2018', 'Tanon College', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:42:51', '2025-08-18 02:42:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(89, '2007072101', 'VILLANUEVA', 'SAMERA ', 'VILLAVER', 'Female', '2007-07-21', 18, 'BRGY.LOOK CALATRAVA', 'Single', 'FILIPINO', 'ROMAN CATHOLIC', 'svillanueva@csr-scc.edu.ph', '$2y$10$yGyREfaFn/hKPhe2iQW6Ke4cN/NP3S4o25dn.bYHOJUTDGFf3Pc82', '09510699789', 'BRGY 1 SAINT VINCENT SUBD. SAN CARLOS CITY NEG. OCC', 'FROILAN S. VILLANUEVA', 'RETIRED POLICE', 'MAROLITA V. VILLANUEVA', 'OFW AGENT', 'MAROLITA V. VILLANUEVA', 'BRGY 1 SAINT VINCENT SUBD.', 'N/A', 1, 1, 'BRGY. 1 SAINT VINCENT SUBD. SCC NEG. OCC', 'TANDANG SORA ELEMENTARY SCHOOL', '2018', 'TANON COLLEGE', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:44:23', '2025-08-18 02:44:23', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(90, '2005042701', 'CARASAQUIT', 'HONEYLYN', 'PARDILLO', 'Female', '2005-04-27', 20, 'BRGY PINOWAYAN DON SALVADOR BENEDICTO', 'Single', 'FILIPINO', 'ROMAN CATHOLIC', 'carasaquit@csr-scc.edu.ph', '$2y$10$kbIAIAlkduIi4hdiePeTp.vftym1cqEwrgK0ftRBVzFJr9ZwzGVGi', '09207956964', 'BRGY PINOWAYAN DON SALVADOR BENEDICTO', 'JOVEN E. CARASAQUIT', 'RICE FARMER', 'MARY ANN P. CARASAQUIT', 'HOUSE WIFE', 'JOVEN E. CARASAQUIT', 'BRGY PINOWAYAN DON SALVADOR BENEDICTO', 'HENRY R. ALCORIN', 1, 1, 'BRGY PINOWAYAN DON SALVADOR BENEDICTO', 'PINOWAYAN ELEMENTARY SCHOOL', '2017-2018', 'JULIO LEDESMA NATIONAL HIGH SCHOOL', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:44:26', '2025-08-18 02:44:26', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(92, '2007102601', 'Martinez', 'Robemie', 'Ponce', 'Female', '2007-10-26', 17, 'San Carlos City Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic', 'robmartinez@csr-scc.edu.ph', '$2y$10$aPh0sTSDUJYhORPc.qfF5etsucFMq3BjGlGbif9JdSIYizBs48w6O', '09707200588', 'Prk. Gemelina Brgy. 1', 'Robelito D. Martinez', 'Laborer', 'Geramie P. Martinez', 'Housewife', 'Geramie P. Martinez', 'Geramie P. Martinez', 'Robelito D. Martinez', 1, 1, 'Prk. Gemelina Brgy. 1', 'Florentina Ledesma Elementary School', '2018-2019', 'Julio Ledesma National High School', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:44:33', '2025-08-18 02:44:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(93, '2007011801', 'Talandron', 'Jay Ann ', 'Gonzales ', 'Female', '2007-01-18', 18, 'Prk Rambutan Brgy Bunga Don Salvador Benedicto Negros Occidental', 'Single', 'Filipino', 'Jesus Vision For Global Transformation Ministry', 'jtalandron@csr-scc.edu.ph', '$2y$10$QsXLR/GliNRqRmQnWUVmo.64IHnJ2N/x2I8NG8jHhNwsKIxgsWcEC', '09753210250', 'Prk Rambutan Brgy Bunga Don Salvador Benedicto Negros Occidental', 'Lito B. Talandron', 'Farmer', 'Arlene G. Talandron', 'House Wife', 'Jay G. Talandron', 'Prk Rambutan Brgy Bunga Don Salvador Benedicto', 'N/A', 1, 1, 'Prk Rambutan Brgy Bunga Don Salvador Benedicto ', 'Benejiwan Elementary School ', '2018-2019', 'Julio Ledesma National High School', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:44:36', '2025-08-18 02:44:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(94, '2007061101', 'Limpangog ', 'Vince Gabriel ', 'B', 'Male', '2007-06-11', 18, 'San Carlos City,NegrosOccidental', 'Single', 'Filipino ', 'Catholic ', 'vlimpangog@csr-scc.edu.ph', '$2y$10$r7ThWtrW3X5xUCQfWF63CugVvopmf0NW3pdrQ1/ST3BBPPZlfLa.2', '09984598661', 'Algers block 7\r\n', 'Vangelito Limpangog ', 'Cashier ', 'Grace Ann Bluza Limpangog ', 'Housewife ', 'Grace ann Bluza Limpangog ', 'Algers block 7', 'N/A', 1, 1, 'Algers block 7 ', 'Tandang Sora Elementary ', '2012-2018', 'Tanon College ', '2019-2023', 'Collegio De Santa Rita incorporated ', 'N/A', 'None ', 'N/A', 'None', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:44:40', '2025-08-18 02:44:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(95, '2007022501', 'Roble', 'Paul vincent', 'Logatiman ', 'Male', '2007-02-25', 18, 'danao city cebu', 'Single', 'Filipino', 'catholic', 'paulvin2516@gmail.com', '$2y$10$QdCVJbOYOuVd0dYfb9ANNO9/SV0YvhyQ/JozYLgcpKEu2RyaRgZ.u', '09070030559', 'carmona st. san carlos city, negros Occidental', 'Andrew L. roble', 'Security Officer', 'Angela L. Roble', 'Midwife', 'Andrew L. roble', 'carmona st sancarlos city', 'N\\A', 1, 1, 'carmona st sancarlos city', 'Conggressman gostilo memorial school', '2014-2018', 'julio ledesma national high school', '2018-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:45:49', '2025-08-18 02:45:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(96, '2007030401', 'Hipanao', 'Eugene', 'Alsada', 'Male', '2007-03-04', 18, 'San Carlos City Neg Occ', 'Single', 'Pilipino', 'Catholic', 'hipanao@csr-scc.edu.ph', '$2y$10$6ljutkAggmAXkHBGCMCmJupXlZmDMqDyqSGScKDawkdgODCtfny26', '09854428493', 'Sto Rosario Brgy Rizal San Carlos City Negros Occidental', 'Edwin Hipanao', 'Contrator', 'Elisa Hipanao', 'Sari Sari Store', 'Edwin Hipanao', 'Sto Rosario', 'N/A', 1, 1, 'Sto Rosario Brgy Rizal San Carlos City Negros Occidental', 'Ramon Magsaysay Elemetary School', '2014-2018', 'Julio Ledesma National High School', '2018-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:45:55', '2025-08-18 02:45:55', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(97, '2006112001', 'BESAÑES ', 'JOHN KENNETH ', 'BAYOT ', 'Male', '2006-11-20', 18, 'Polomolok south cotabato ', 'Single', 'Filipino ', 'Ramon catholic ', 'kennethb@csr-scc.edu.ph', '$2y$10$m0Eam1LxdSXoWZNWyaLgD.Vafi97S0CATH0HUCSdEi2JRhKccCnG.', '09936425902', 'Lower tonggo baranggay palampas San Carlos city negros occidental ', 'Erwin Q besañes ', 'N/A', 'Mary Louise D Bayot ', 'Unitop ', 'Nicole B besañes ', 'Lower tonggo baranggay palampas ', 'N/A', 1, 1, 'Lower tonggo baranggay palampas San Carlos city negros occidental ', 'N/A', 'N/A', 'Julio ledesma national high school ', '2023-2024', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:46:01', '2025-08-18 02:46:01', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(98, '2007100901', 'Alegado', 'Gerald', 'Allones', 'Male', '2007-10-09', 17, 'San juan', 'Single', 'Filipino', 'Catholic', 'alegado@csr-edu.ph', '$2y$10$Biv0o7sKMwGEDc84gG0VKuJ7kRFOKvb2B.3k/Ure2zTr.Sm2H2RYe', '09631665416', 'San juan,Ylagan st, San Carlos City, Negros Occidental ', 'Rolando Alegado', 'Tricyle Driver', 'Jonah Allones', 'House Wife', 'Jonah Allones', 'San Juan', 'N/A', 1, 1, 'alegadogerald15@gmail.com', 'Bonifacio Elemetary School', '2014-2018', 'Julio Ledesma National High School', '2018-2024', 'CSR', 'BSIT-2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:46:11', '2025-08-18 02:46:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(99, '2006112201', 'Canopin', 'Chrismar', 'Erediano', 'Male', '2006-11-22', 18, 'Calatrava, Negros Occidental ', 'Single', 'Filipino ', 'Catholic ', 'canopin@csr-scc.edu.ph', '$2y$10$PT4eAqWXeg9h2EZFlZCwBuwRumynBRH1tr5ME65uTREkwvsoPPP26', '09165444862', 'Calatrava bantayanon Negros Occidental ', 'N/A', 'N/A', 'Magelyn Canopin ', 'Cooking', 'Chris Marie Canopin', 'Calatrava bantayanon Negros Occidental ', 'Magelyn canopin ', 1, 1, 'Calatrava bantayanon Negros Occidental ', 'Calatrava II Central School ', '2014-2015', 'Calatrava national high school ', '2019-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:46:16', '2025-08-18 02:46:16', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(100, '2006040101', 'Tajale', 'Ej', 'Dumip-ig', 'Male', '2006-04-01', 19, 'San Carlos City\\', 'Single', 'Filipino', 'Roman Catholic', 'tajale@csr-scc.edu.ph', '$2y$10$opp7JHljj2elb/epUUuxs.m3yVUaTdxBfyb1I7.Hx.gq8BywmwR2C', '09262711485', 'Calatrava Negros Occidental, Brgy. Lo-ok, Castellanes Str.', 'Evaristo R. Tajale', 'Retired Army', 'Jocelyn D. Tajale', 'Sari Sari Store Owner', 'Evaristo R. Tajale', 'Calatrava Negros Occidental, Brgy. Lo-ok, Castellanes Str.', 'Ejade D. Tajale', 1, 1, 'Calatrava Negros Occidental, Brgy. Lo-ok, Castellanes Str.', 'Calatrava 1 Central School', '2013-2019', 'Calatarava National High School / Calatrava Senior High School - Stand Alone', '2019-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:46:56', '2025-08-18 02:46:56', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(102, '2006121101', 'Lovero', 'Marla Joy', 'Dada', 'Female', '2006-12-11', 19, 'Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'lovero@csr-scc.edu.ph', '$2y$10$Jszul9JVxeIEQL0Sm4Bv6OFXomdsvuId7lW7yGlq1NKzU.tj0OBbO', '09356289115', 'Calatrava Suba Amihan 2', 'Jerry C. Lovero', 'Shoe Maker', 'Emma D. Lovero', 'OFW', 'Marvin G. Dada', 'N\\A', 'N\\A', 1, 1, 'brgy. Suba Amihan 2 Calatrava neg, Occ.', 'Calatrava 1 Central School', '2018 2019', 'Calatrava Senior high School- Stand Alone', '2024 2025', 'N\\A', 'N\\A', 'N\\A', 'N\\A', 'N\\A', 'N\\A', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 02:48:43', '2025-12-21 06:05:53', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(103, '2006121601', 'Abadies', 'Jack', 'Paler', 'Male', '2006-12-16', 18, 'leyte', 'Single', 'Filipino', 'Roman Catholic', 'jabadies@csr-scc.edu.ph', '$2y$10$v8b7IoiJMbllZt/W2K9keO8zplH1cVFgfQDaWdzu6vRdI3UAt7HSu', '09852114503', 'BRGY. PUNAO SAN CARLOS CITY', 'Gevovevo B. Abadies', 'constraction', 'Liza P. Abadies', 'N/A', 'Jeza P. Abadies', 'BRGY. punao ', 'N/A', 1, 1, 'BRGY. Punao San carlos city', 'Talave elementary school', '2013-2019', 'JULIO LEDESMA NATIONAL HIGH SCHOOL', '2019-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:49:25', '2025-08-18 02:49:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(104, '2007072001', 'Caruscay', 'Samantha Louise', 'Vega', 'Female', '2007-07-20', 18, 'San Carlos Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'samnthaa24@gmail.com', '$2y$10$2q948zjxr4nNfvvJ4sOZk.uv6avcFMv6A32Y1w8Sdcxdh4BqY/qp.', '09304441047', 'Teachers Village, Barangay 2 San Carlos City Negros Occidental', 'Jeffrey M. Caruscay', 'N/A', 'Maylyn M. Vega ', 'N/A', 'Maylyn M. Vega ', 'San Carlos City Negros Occidental', 'N/A', 1, 1, 'Teachers Village, Barangay 2 San Carlos City Negros Occidental', 'Ramon Magsaysay Elementary School', '2013-2019', 'Julio Ledesma National Highschool', '2019-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:49:27', '2025-08-18 02:49:27', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(105, '2003081901', 'Balbuena', 'Khyle', 'Morden', 'Male', '2003-08-19', 21, 'Sitio natuyay barangay cod cod san carlos city', 'Single', 'Filipino', 'Catholic ', 'balbuena@csr-scc.edu.ph', '$2y$10$K6sM1jObWQqwBQ4LZdjWm.nHVpvtkuTkrQb1xxA26YJA5ZlXmF48C', '09319154825', 'Sitio natuyay barangay codcod san carlos city Negros Occidental ', 'Clarito Balbuena', 'Driver', 'Marissa Balbuena', 'House wife', 'Marissa Balbuena', 'Sitio natuyay barangay codcod san carlos city Negros Occidental ', 'Clarito Balbuena', 1, 1, 'Sitio natuyay barangay codcod san carlos city Negros Occidental ', 'Cabagtasan elementary school ', '2015-2016', 'Quezon national highschool', '2020-2021', 'Colegio de santa rita de san carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:50:17', '2025-08-18 02:50:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(106, '2007030601', 'Cañete', 'John Rechie ', 'Yorpo', 'Male', '2007-03-06', 18, 'Calatrava Centro Hospital ', 'Single', 'Filipino', 'Roman Catholic ', 'rechie@csr-scc.edu.ph', '$2y$10$h9y6kTasptdY.ZXWe1g2fO/8ijRgansOd84BOk6nzYxuJWS6BPnEe', '09947718982', 'Barangay, Bantayanon ', 'Mario D. Cañete', 'Driver ', 'Mary Jovy Cañete ', 'Midwife', 'Mary Jovy Cañete', 'barangay, Bantayanon ', 'Jean Malunhao', 1, 1, 'Barangay, Bantayanon ', 'Calatrava ll Elementary Central School ', '2013-2019', 'Calatrava National High School ', '2019-2023', 'Colegio De Santa Rita Tibuco', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:50:31', '2025-08-18 02:50:31', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(107, '2007010101', 'Guillen', 'Jhon Wrey', 'Canoy', 'Male', '2007-01-01', 18, 'San Carlos City OCC', 'Single', 'Philippine', 'Roman Catolic', 'jguillen@csr-scc.eduph', '$2y$10$nHOAy9i8ecvtGU2HVmLzB.80iO0WaKQ9W7FiVHYPKnIfgw5eaAUJ.', '09488564843', 'BRGY. PALAMPAS SO. PAMAHAWAN', 'Jonthan F. Guillen', 'Tricycle Driver', 'Teresita C. Guillen', 'Housewife', 'Jonthan F. Guillen', 'BRGY. PALAMPAS SO. PAMAHAWAN', 'NA', 1, 1, 'BRGY PALAMPAS SO. PAMHAWAN', 'Ramon magsaysay school', '2018', 'JULIO LEDSMA NATIONAL HIGH SCHOOL', '2025', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:51:00', '2025-08-18 02:51:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(108, '2003120801', 'Omaña ', 'Frances ', 'Labrador ', 'Male', '2003-12-08', 21, 'Vallehermoso ', 'Single', 'Vallehermoso ', 'Katoliko ', 'frances@csr-scc.edu.ph', '$2y$10$qz1q/MKZFlo1Heb41B.OweGyMu0o3CeTsb3cX5frdqNxOWDZNKfxG', '09269756310', 'Maglahos Vallehermoso Negros Oriental ', 'Aquino Omaña ', 'Farmers ', 'Juditha Omaña ', 'Farmers ', 'Sheizel Omaña ', 'Vallehermoso ', 'NA', 1, 1, 'Maglahos Vallehermoso Negros Oriental ', 'Maglahos ', '2013', 'Tagbeno ', '2022', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:51:06', '2025-08-18 02:51:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(109, '2006051101', 'Bardon', 'Joshua', 'Gepitulan ', 'Male', '2006-05-11', 19, 'San Carlos city ', 'Single', 'Filipino ', 'Born again ', 'joshuabardon@csr-scc.edu.ph', '$2y$10$xkvB5.AOJJAcUQsegWPvE.2Nux7uPEE89VgLeRUR7Ho.8XlqzHIdC', '09153998329', 'St Vincent subdivision baranggay 1 10street San Carlos city negros Occidental ', 'None', 'None ', 'Mejulyn G bardon ', 'Call center ', 'Julieta Gepitulan bardon ', 'St Vincent subdivision San Carlos city negros Occidental ', 'N/A', 1, 1, 'St Vincent subdivision baranggay 1 10street San Carlos city negros Occidental ', 'Ramon Magsaysay elementary school ', '2016-27', 'Tañon college ', '2023-24', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:51:26', '2025-08-18 02:51:26', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(110, '2007022801', 'Tejones lll ', 'Antonio ', 'Tumala', 'Male', '2007-02-28', 18, 'San Carlos City', 'Single', 'Philippine', 'Roman Catholic ', 'atejones@csr-scc.edu.ph', '$2y$10$.j7IDUrH9J3NVicSLxZ3uen0DxNMUOCdnrTdrqQTdId/VmhJqk.46', '09707178505', 'Sto. Pamahawan BRGY Palampas', 'Antonio M. Tejones jr', 'Tricycle driver ', 'Esperanza T. Tejones', 'House wife', 'Esperanza T. Tejones', 'Brgy palampas ', 'None', 1, 1, 'So pamahawan brgy palampas san carlos city negros occidental ', 'Lina dela veña elementary school ', '2018', 'Julio ledesma national high school ', '2025', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:51:47', '2025-08-18 02:51:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(111, '2005111301', 'Helly', 'Rilan', 'Lapasaran', 'Male', '2005-11-13', 20, 'calatrava', 'Single', 'filipino', 'bible baptist church', 'helly@csr-scc.edu.ph', '$2y$10$TbQU4Mk1VUBLhtWJOQbYhOmFe69iSQ6cQCahTZI6N3LBpP4InIMNu', '09923165423', 'properbantayanon', 'Zaqueo caballero helly', 'forman', 'marilou pataytay lapasaran', 'BHWD', 'marilou helly', 'calatrava', 'brother', 1, 1, 'Hellyfamily', 'caltravaIIcentralschool', '2013-2018', 'calatrava national high school', '2018-2021', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 02:52:59', '2025-12-21 06:13:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(112, '2006122201', 'Tanelon ', 'John Paul', 'Repaso', 'Male', '2006-12-22', 18, 'Lapaz, Ilo-Ilo City', 'Single', 'FILIPINO ', 'ROMAN CATHOLIC ', 'tanelon@csr-scc.edu.ph', '$2y$10$eYvArSvnC2Z5FldSObf8LelfiVciEDHQgeh9EWE.nS5LoC7aDibHy', '09276505692', 'Brgy.Suba, Calatrava, Negros Occidental ', 'Roberto ', 'Decreased ', 'Remy R. Tanelon', 'Sari-sari store owner', 'Remy R. Tanelon', 'Brgy.Suba, Calatrava, NEGROS OCCIDENTAL ', 'N/A', 1, 1, 'Brgy.Suba, Calatrava, NEGROS OCCIDENTAL ', 'Calatrava 2 Central School ', '2013-2019', 'Calatrava National High School ', '2019-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:54:19', '2025-08-18 02:54:19', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(113, '2007050801', 'Mancia', 'Hanjyn Mae', 'Timtim', 'Female', '2007-05-08', 18, 'Brgy Punao', 'Single', 'Filipino', 'Roman Catholic', 'hmancia@csr-scc.edu.ph', '$2y$10$hKdtCVyWgobQVj2bZIcMauGmDh8DAP.ixdFY1zCIEMbOoeK7gffHu', '09539581862', 'Brgy Punao', 'Christopher Mancia', 'Driver', 'Cristy Mancia', 'House Wife', 'Christopher Mancia', 'Brgy Punao', 'N/A', 1, 1, 'Brgy Punao', 'Talave Elementary School', '2018-2019', 'Julio Ledesma National High School', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:54:50', '2025-08-18 02:54:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(114, '2004062501', 'Carabandes ', 'John Rhey ', 'Bertoldo ', 'Male', '2004-06-25', 21, 'San Carlos City negrose Occidental ', 'Single', 'Philippine ', 'Catholic ', 'carabandes@csr-scc.edu.ph', '$2y$10$/88Ya4HMNexqYP9NILD5KeqbdgL87zOu3906tAFJ2IAAbft9XGzD.', '09972918470', 'So pamahawan Brgy palampas San Carlos City negrose Occidental ', 'Alihandro Carabandes ', 'NA', 'Teresita B. Carabandes ', 'Water work ', 'Teresita Carabandes San Carlos City ', 'So pamahawan Brgy palampas ', 'NA', 1, 1, 'So pamahawan Brgy palampas San Carlos City negrose Occidental ', 'Linadelabinya ', '2016', 'JULIO LEDESMA NATIONAL ', '2025', 'Na', 'Na', 'Na', 'Na', 'Na', 'Na', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 02:56:03', '2025-08-18 02:56:03', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(115, '2001090801', 'Trazona', 'Christian Jay', 'Alob', 'Male', '2001-09-08', 23, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic ', 'trazona@csr-scc.edu.ph', '$2y$10$ApKxD.bYv/psHEVIHOW.1uEwHjOcYJe.FGZf2NWtukx3.RYBQkAzW', '09358405322', 'Metrovel, brgy palampas San Carlos City', 'Timoteo S. Trazona Jr.', 'house husband ', 'Grace A. Trazona', 'OFW', 'None', 'None', 'None', 1, 1, 'Metrovel brgy palampas San Carlos City ', 'SCMCIES Elementary School ', '2008-2014', 'Calatrava National Highschool ', '2015-2019', 'Colegio de Santa Rita', '2022-2025', 'None', 'Nono', 'Calatrava Senior High School', '2020-2022', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:00:06', '2025-08-18 03:00:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(116, '2003022801', 'Ducay ', 'Swift Rasa', 'N/A', 'Male', '2003-02-28', 22, 'Japan', 'Single', 'Filipino', 'Jehovah\'s Witnesses ', 'ducay@csr-scc.edu.ph', '$2y$10$k/42CLY5CtsslR43.WSs8uHEuMDeXQlK2Sje.Rt6e8kzIKpsE/qcS', '0966504229', 'Brgy. Guadalupe, San Carlos City, Negros Occidental ', 'N/A', 'N/A', 'Jasmin Ducay', 'Online Seller', 'Jasmin Ducay', 'Brgy. Guadalupe, San Carlos City, Negros Occidental ', 'N/A', 1, 1, 'N/A', 'Industrial Valley Complex Elementary School ', '2010-2016', 'Tanong High School ', '2016-2022', 'Colegio de Santa Rita de San Carlos, Inc', '2022-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:00:38', '2025-08-18 03:00:38', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(117, '2004033101', 'Alejo', 'Reshel', 'Pilapil', 'Female', '2004-03-31', 21, 'So. Bugarak Brgy. Bagonbon', 'Single', 'Filipino', 'Baptist', 'reshelshel2@gmail.com', '$2y$10$wkjhShFO/mjNSebNzUX5BuAhRszVGDhuW0g37PQMCFTE0ba0QdALC', '09663528192', 'So. Landingan Brgy. Bagonbon San Carlos City', 'Fanny Alejo', 'Farmer', 'Helen Alejo', 'House Wife', 'Helen Alejo', 'So. Landingan Bgry. Bagonbon', 'None', 1, 1, 'So. Landingan Brgy. Bagonbon', 'Bagonbon Elementary School', '2015-2016', 'Bagonbon National High School ', '2021-2022', 'Collegio de Sta. Rita de San Carlos Inc.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:01:51', '2025-08-18 03:01:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(118, '2007080501', 'DELA TORRE', 'CLYTE IVAN', 'CARIGAY', 'Male', '2007-08-05', 18, 'VALLEHERMOSO NEGROS ORIENTAL', 'Single', 'FILIPINO', 'CATHOLIC', 'cdelatorre@csr-scc.edu.ph', '$2y$10$j2lOG9V4V5rLhYSY1Emc1eRkXNm8LSf7wyv4Ohsn8PNj8GnQevg6q', '09912983034', 'TAGBINO VALLEHERMOSO NEGROS ORIENTAL', 'ROBERT A. DELA TORRE', 'BUS CONDUCTOR', 'NANCY C. DELA TORRE', 'MIDWIFERY', 'PERLA DELA TORRE', 'TAGBINO VALLEHERMOSO NEGROS ORIENTAL', 'MARRY ANN CARIGAY', 1, 1, 'TAGBINO VALLEHERMOSO NEGROS ORIENTAL', 'DAPMES', '2017-2018', 'TAGBINO NATIONAL HIGH SCHOOL', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:04:48', '2025-08-18 03:04:48', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(124, '2005090401', 'Manolong ', 'Sheryn Mae ', 'Recapor ', 'Female', '2005-09-04', 19, 'Sitio Maglangit Brgy Malangsa Negros Oriental ', 'Single', 'Filipino ', 'Catholic ', 'manolong@csr-scc.edu.ph', '$2y$10$dbzbpa6deCcklC.6XW1qO.bbYtzg49DO/n44iXHERM7aEGtvKn.zu', '09852109048', 'Poblacion Vallehermoso ', 'Felecisimo ', 'Farmer', 'Susana', 'House wife ', 'Susana ', 'Malangsa Vallehermoso Negros Oriental ', 'Demetria Manolong ', 1, 1, 'Malangsa Vallehermoso Negros Oriental ', 'Malangsa Elementary School ', '2015-2016', 'Vallehermoso National High School ', '2022-2023', 'Colegio de Santa Rita de San Carlos ', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:05:11', '2025-08-18 03:05:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(126, '2002082001', 'Barbon', 'Arjhay', 'Na', 'Male', '2002-08-20', 22, 'Paranaque city manila', 'Single', 'Ph', 'Catholic ', 'jhay_bars0820@yahoo.com', '$2y$10$D1Sfy/Ha04cJ85hsgEhLrum8NEbEZAWFdu/Lds3nGXxs63JLAEoDC', '09107790442', 'Brgy 2 pres quirino don juan subdivision 00067 negros occ scc', 'Rapael m sta cruz', '.', 'Jessa b miura', 'OFW', 'Mia antoniette c. Cabuguas', 'Zenia st san julio', 'Hisashi miura', 1, 1, 'Don juan subd. Pres quirino brgy 2', 'Daisy\'s Abc school', '2015-16', 'Colegio De Sta Rita D Inc', '2018-19', '.', '.', '.', '.', '.', '.', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:45:56', '2025-08-18 03:45:56', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(127, '2005021701', 'Cenabre', 'Alluena', 'Pagusara', 'Female', '2005-02-17', 20, 'Cebu City', 'Single', 'Filipino', 'Roman Catholic', 'cenabre@csr-scc.edu.ph', '$2y$10$kp37Oad5eKMUW9ZjV3Z/NuLetgtaKzOj/QOR/xrWGaqMdUtRvbimC', '09951610388', 'Hope extension bgry 5', 'Emmanuel Cenabre', 'Deceased', 'Sylvia Pagusara', 'Housewife', 'Kimberly Cenabre', 'Hope extension Brgy 5', 'Renz Cenabre', 1, 1, 'Hope extension', 'Don Carlos A. Gothong Elementary School', '2011-2016', 'Julio Ledesma National Highschool', '2017-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:52:12', '2025-08-18 03:52:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(128, '2001121601', 'Donato', 'Jesa', 'Bayot', 'Female', '2001-12-16', 23, 'hacienda medina barangay rizal', 'Single', 'filipino', 'Catholic', 'donato@csr-scc.edu.ph', '$2y$10$hgddFqxAzTkCDYVhL/es7..ld/so0.XYef8YebTDaKGPr7SXTxgYK', '0277157149', 'hacienda medina barangay rizal', 'Rafaelito V donato', 'N\\A', 'melbe donato', 'N\\A', 'melbe donato', 'melbe donato', 'family', 1, 1, 'hacienda medina barangay rizal', 'Medina elementary school', '2012', 'Julio Ledesma National High School', '2029', NULL, NULL, 'colegio de santa rita ', '2022', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 03:52:25', '2025-08-18 03:52:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(129, '2005042703', 'Santillan ', 'Kristal ', 'Marcellana ', 'Female', '2005-04-27', 20, 'San Carlos City, Negros Occidental ', 'Single', 'Filipino ', 'Catholic', 'santillan@csr-scc.edu.ph', '$2y$10$xKAfeP50p/2yXlfZ8yfzReefrYVBgsTogQp4QJezqUMxgfdPrx26C', '09650938010', 'Ylgan extension, San Juan baybay, brgy. 6 ', 'Nestor S. Gudienes', 'Fisherman', 'Madonna M. Santillan', 'Housewife', 'Madonna M. Santillan ', 'Ylgan extension, San Juan baybay, brgy 6 ', 'Nestor S. Gudienes ', 1, 1, 'Ylgan extension, San Juan baybay , brgy. 6 ', 'Andres Bonifacio Central School', 'Grade 1-6', 'Julio Ledesma National High School ', 'Grade 7 - 12 ', 'Colegio de sta. Rita inc. ', '1st - 3rd ', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 04:37:39', '2025-08-18 04:37:39', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(130, '2002051201', 'Lapuz', 'Mikaela', 'Batomalaque', 'Female', '2002-05-12', 23, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Baptist', 'lapuzmikaela49@gmail.com', '$2y$10$2WSjMXABZBnkUi56TTzvJuIv5WaNHoyQvZ7DKU/fPAfWluM1Ypi.i', '09266622573', 'Burgos St. Brgy.6', 'Elizar Lapuz', 'none', 'Teresita Lpauz', 'none', 'Elizar Lapuz', 'Burgos St.', 'sister', 1, 1, 'Burgos St. Brgy.6', 'Congressman Vicente Gustilo Sr. Memorial School', '2012-2017', 'Julio Ledesma National High School', '2017-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 04:54:50', '2025-08-18 04:54:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(131, '2004030501', 'Tejana', 'John Henry', 'Panuncillo', 'Male', '2004-03-05', 21, 'Pasay City, Metro Manila', 'Single', 'Filipino', 'Protestant', 'tejana@csr-scc.edu.ph', '$2y$10$xYq3G.qSTbSZHopwzSg.qudQQqygiGd8S03rXaGGCriZLIeYIKXRC', '09632194425', 'SO. MABUNI, BRGY. GUADALUPE, SAN CARLOS CITY, NEGROS OCCIDENTAL', 'Henry T. Tejana', 'PWD/Senior Citizen', 'Maria Jovita P. Tejana', 'OFW', 'Maria Janine P. Galupo', 'Barrio Dos, Guadalupe, San Carlos City, Negros Occidental', 'Alyssa Marie P. Tejana', 1, 1, 'Barrio Dos, Guadalupe, San Carlos City, Negros Occidental', 'Guadalupe Elementary School ', '2008-2017', 'Tañon College', '2017-2022', 'Colegio de Sta. Rita de San Carlos, Inc.', '2022-2026', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 05:02:58', '2025-08-18 09:27:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(132, '2003081902', 'Trespuentes ', 'Kenneth ', 'Berjolano ', 'Male', '2003-08-19', 21, 'San Carlos City Negros Occidental ', 'Single', 'Filipino ', 'Roman Catholic ', 'trespuentes@csr-scc.edu.ph', '$2y$10$2ZtXeuusHMgN5uT0o2FAAOzcBVwNbeTP1eyeCWdzeRMP7HAYaA8hS', '09093376284', 'Pres. Laurel St. Don Juan Subdivision ', 'Joejim S. Trespuentes ', 'Driver', 'Ofelia B. Trespuentes ', 'BHW', 'Ofelia B. Trespuentes ', 'Pres. Laurel St. Don Juan Subdivision ', 'Joejim S. Trespuentes ', 1, 1, 'Pres. Laurel St. Don Juan Subdivision San Carlos City Negros Occidental ', 'Tandang Sora Elementary School ', '2008-2016', 'Julio Ledesma National Highschool ', '2017-2022', 'Colegio de Santa Rita de San Carlos, Inc.', '2022-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:09:00', '2025-08-18 05:09:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(133, '2006073101', 'Pando', 'Bjorn', 'Empedrado', 'Male', '2006-07-31', 19, 'Sancarlos City negros occidental ', 'Single', 'Filipino', 'Roman Catholic', 'bpando@csr-scc.edu.ph', '$2y$10$.LOuTOF8KbAAJ6z3xvUR1OGaqsQ3kNG0pw2sGzwS58i9BG6kCZkIq', '09930384247', 'S carmona street brgy 6', 'Marvin pando', 'diceased', 'Mernolyn', 'overseas worker', 'Farahjoy Empedrado', 'Mercedes Empedrado', 'N/A', 1, 1, 'Pantalan brgy 6 ', 'Ramon Magsaysay Elementary school', '2017-18', 'Tañon College', '2024-25', 'CSR', 'present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:12:38', '2025-08-18 05:12:38', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(134, '2006091401', 'Davis ', 'Francis Victor ', 'Esparcia ', 'Male', '2006-09-14', 18, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'fdavis@csr-scc.edu.ph', '$2y$10$JFJPJw0JYgYFQtsCAjFeJ./Izz02QYahsbrEj201PypdFvJpHadb6', '09708223753', 'Don Juan Subd. Brgy 2, San Carlos City, Negros Occidental ', 'Vener B. Davis', 'Driver ', 'Marlin E. Davis', 'Pharmacy Clerk', 'N/A', 'N/A', 'N/A', 1, 1, 'Don Juan Subd. Brgy 2, San Carlos City, Negros Occidental ', 'School of the Future ', '2017-2018', 'Tañon College Inc.', '2024-2025', 'CSR', 'present ', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:13:44', '2025-08-18 05:13:44', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(135, '2003030901', 'Costanilla', 'James Matthew', 'Calacday', 'Male', '2003-03-09', 22, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'costanillajamesmatthew@gmail.com', '$2y$10$SBnkz36VKJvcQDPGTsBWOOrgN0GmIrUPFgN/QoU2E11K88vwF3z9e', '09185517308', 'Block 1 Lot 11, St. Vincent Subdivision', 'N/a', 'N/a', 'Maureen Costanilla', 'Kitchen Staff', 'Maureen Costanilla', 'Villarante, Brgy. 1', 'Jason Costanilla', 1, 1, 'St. Vincent, Brgy. 1', 'TSES', '2015-2016', 'JLNHS', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:14:18', '2026-02-19 09:52:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(136, '2007052901', 'Lazaga', 'Gabriele', 'Patiño', 'Male', '2007-05-29', 18, 'San Carlos City Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic', 'gabriele@csr-scc.edu.ph', '$2y$10$NXMHz67UGFxlWNVfH0HsM.8FXsD8Oh92yesSILio3OFa4LubJdaLe', '09933864854', 'Ylagan Extension Brgy 6', 'Roel Lazaga ', 'Seaman ', 'Yvonne Patiño', 'House Wife', 'N/A', 'N/A', 'N/A', 1, 1, 'Ylagan Extension Brgy 6', 'Ramon Magsaysay Elementary School', '2017-2018', 'Tañon College INC', '2019-2024', 'CSR', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:14:26', '2025-08-18 05:14:26', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(137, '2006121501', 'De Leon', 'Khart', 'Sayson', 'Male', '2006-12-15', 18, 'San Carlos City Neg. Occ.', 'Single', 'Filipino', 'Roman Catholic ', 'deleon@csr-scc.edu.ph', '$2y$10$7hzBme1IXviYNvURUv79ieZ1l9l6kMjhS7TGmVbtrHJuupXBTVb1a', '09264280689', 'So. Talave Brgy. Punao', 'Rogelio De Leon', 'Lgu City Hall', 'Arlene De Leon', 'House Wife', 'N/A', 'N/A', 'Sister', 1, 1, 'So. Talave Brgy. Punao', 'Ramon Magsaysay Elementary school ', '2017-2018', 'Tañon College ', '2019-2024', 'CSR', '2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:14:44', '2025-08-18 05:14:44', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(138, '2004052701', 'Delosa', 'Euree', 'Magbulogtong', 'Male', '2004-05-27', 21, 'Calatrava ', 'Single', 'Filipino ', 'Catholic', 'eureedelosa@gmail.com', '$2y$10$cg1lrv8GudDxD/8NXgLzouKm4toa65EAo3gwGaAkkNe2rE8OW5iry', '09940499128', 'Calatrava Brgy suba', 'Mariano T Delosa', 'Seaman', 'Minerva M Delosa', 'House wife', 'Minerva M Delosa', 'Calatrava Brgy suba', 'Mariano T Delosa', 1, 1, 'Calatrava Brgy suba', 'Calatrava 2 elementary school ', '2015-2016', 'Cstr', '2021-2022', '', '', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 05:15:03', '2025-08-18 05:18:38', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(139, '2006080301', 'Arnoco', 'John Kenneth ', 'Enisimo ', 'Male', '2006-08-03', 19, 'Toboso, Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic ', 'kennetharnoco41@gmail.com', '$2y$10$ZqnMpWnDDG6ucSL4mI3sgO4YCMmznWPyj4QR4nW5j4Z5b9UnrmUQ.', '09304222960', 'Purok Lubi Barangay 1 \r\n\r\n\r\n', 'Jesus H. Arnoco', 'Butcher ', 'Roselyn E. Arnoco ', 'N/A', 'Roselyn E. Arnoco', 'Purok Lubi', 'Jesus H. Arnoco', 1, 1, 'Purok Lubi Brgy. 1', 'FLES', '2019 - 2020', 'JLNHS', '2024 - 2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:15:32', '2026-02-19 09:53:13', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(140, '2007022301', 'Babiera', 'Allester Neil', 'Barte', 'Male', '2007-02-23', 18, 'San Carlos City Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic ', 'ababiera@csr-scc.edu.ph', '$2y$10$bUcQUtWnnkcXk27TMevmLOuKmdi.E66HLzko8B.QymnfAP/ZoO7d6', '09071222836', 'Brgy. 1 Villarante Village San Carlos City Negros Occidental', 'Neniel Babiera', 'BIO POWER', 'Ruchel Barte', 'OWF', 'N/A', 'N/A', 'N/A', 1, 1, 'Brgy. 1 Villarante Village, San Carlos City Negros Occidental', 'Tandang Sora Elementary School', '2017-2018', 'Tañon College Inc.', '2024-2025', 'Colegio de Santa Rita', 'Present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:16:08', '2025-08-18 05:16:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(141, '2006100801', 'Egonia', 'Rey Jhan', 'Abella', 'Male', '2006-10-08', 18, 'Brgy.Ma-aslob Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'reyjhan@csr-scc.edu.ph', '$2y$10$h8aSTTzM99jBln7p8ANsNuZuqVoFGLtTIgNTytUZMPTJ/.F/KAs7C', '09940899255', 'Brgy. Maaslob Calatrava Negros Occidental', 'Teresito Egonia', 'Farmers', 'Mercelodina Egonia', 'Housekeeper', 'Rosette Egonia', 'Brgy. Maaslob', 'Renan Egonia', 1, 1, 'Brgy. Maaslob Calatrava Negros Occidental', 'Bagonbon ', '2016-2017', 'Colegio de Sta. Rita ', '2024-2025', 'Colegio de sta rita', '2025-2026', 'Information Technology', '2025-2026', 'Formal education', '2025-2026', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:17:28', '2025-08-18 05:17:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(142, '2004082801', 'Dagwayan ', 'Algie', 'Abella', 'Male', '2004-08-28', 20, 'BRGY.Ma-aslob Calatrava Neg.Occ', 'Single', 'Filipino', 'Roman Catholic ', 'algie@csr-scc.edu.ph', '$2y$10$lrr48tlqL66t8VvUFKyPWOXao.4/7UwvT0ZWznX0HvtAyOaMJuePC', '09670523356', 'BRGY. Ma-aslob', 'Alejandro Dagwayan', 'Farmer', 'Rosalina Dagwayan', 'Farmer ', 'Rosalina Dagwayan', 'BRGY.Ma-aslob ', 'Erma Dagwayan', 1, 1, 'BRGY. Ma-aslob Calatrava Neg.Occ ', 'Dolis Elementary school ', '2015-2016', 'Agpangi National High School ', '2022-2024', 'Colegio de Sta.Rita', '2025-2026', 'Information Technology ', '2025-2026', 'Formal Education ', '2025-2026', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:17:47', '2025-08-18 05:17:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(143, '2003100201', 'Tihume', 'Jan Roy', 'Tabigue', 'Male', '2003-10-02', 21, 'Calatrava, Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic ', 'tihume@csr-scc.edu.ph', '$2y$10$gAnUYEtE33YP0EkRTs5e/.sYIHJoa.DxevvJ4obmADCrQ1JxBT2x6', '09615955281', 'Lot 14 block 3, sancaville, san carlos city, negros occidental ', 'Roy Tihume ', 'Fisherman', 'Renesita tabigue ', 'Housewife', 'Rica tihume', 'Lot 14 block 3, sancaville, san carlos city, negros occidental ', 'N/a', 1, 1, 'Bien unido, bohol', 'Bien unido central elementary school ', '2016', 'Holy child academy ', '2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:18:11', '2025-08-18 05:18:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(144, '2006052501', 'Taganile', 'Nestor Jaidev Alexzandrei', 'Gurbuxani', 'Male', '2006-05-25', 19, 'San Carlos City', 'Single', 'Filipino', 'Christian', 'nestor08@csr-scc.edu.ph', '$2y$10$XOZNVnF8U3SC2It/.j.VeePixQqZeBWCPJcEvwGs1Td7x2Jazu1.O', '+639171232597', 'National Highway, San Carlos City, Negros Occidental', 'Malvin Noble', 'Retired PNP', 'Parpate Noble', 'Freelancer', 'Parpate Noble', 'National Highway, San Carlos City, Negros Occidental', 'Joepheye Taganile', 1, 1, 'National Highway, San Carlos City, Negros Occidental', 'Colegio de Santa Rita de San Carlos Incorpprated', '2019', 'Colegio de Santa Rita de San Carlos Incorpprated', '2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:19:56', '2025-08-18 05:19:56', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(145, '2003051201', 'Dela Cruz ', 'Ayesha Marga ', 'Quibate ', 'Female', '2003-05-12', 22, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'delacruz@csr-scc.edu.ph', '$2y$10$gYCmzBZE27fB50kmQmIP1ebQl71wCo122kqQUWLKRrI2Ve7DXmydm', '09297986025', 'St. Rita Homes Subdivision Barangay Rizal San Carlos City ', 'Axel Dela Cruz ', 'Tricycle Driver ', 'Maida Dela Cruz ', 'Housewife ', 'Axel Dela Cruz ', 'St. Rita Homes Subdivision ', 'N/A', 1, 1, 'St. Rita Homes Subdivision ', 'Ramon Magsaysay Elementary School ', '2016-2017', 'Colegio de Santa Rita de San Carlos Inc.', '2021-2022', 'Colegio de Santa Rita de San Carlos Inc.', '2022-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:23:58', '2025-08-18 05:23:58', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(146, '2007081201', 'Padilla', 'Francis Troy', 'Chavez', 'Male', '2007-08-12', 18, 'San Carlos City Negros Occidental', 'Single', 'Philippines', 'Catholic', 'fpadilla@csr-scc.edu.ph', '$2y$10$Y.8hHEomMIXmh0POx5Nzg.62TTj5ryhdhocMOFJcIXwG0lewwHW52', '09686162744', 'Purok Gemelina, Barangay 1\r\n\r\n', 'Francis A. Padilla', 'Food Vendor', 'Diana C. Padilla', 'Housewife', 'N/A', 'N/A', 'N/A', 1, 1, 'Purok Gemelina, Baranagy 1', 'Florentina Ledesma Elementary School', '2018-2019', 'Julio Ledesma National High School', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:24:44', '2025-08-18 05:24:44', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(147, '2002011101', 'HABANEZ', 'ANGELOU', 'DALISAY', 'Female', '2002-01-11', 23, 'Malire Antipas North Catabato', 'Single', 'Filipino', 'Bible Baptist', 'angelou@csr-scc.edu.ph', '$2y$10$Ea0QMEAbov/4Sl61/0tG6uCdZO797TfkUhHhvkrsq/e41moKJ92ka', '09309018295', 'Calatrava Negros Occidental', 'Demetrio Habanez', 'farmer', 'Marlyn Habanez', 'House Wife', 'Lucy Ponce', 'Calatrava Negros Occidental', 'N/a', 1, 1, 'Negros Occidental', 'Malire Antipas Elementary School', '2014-2015', 'Silway-8 National High School', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:26:22', '2025-08-18 05:26:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(148, '2007081701', 'baldomar', 'kent jude', 'salaver', 'Male', '2007-08-17', 18, 'manila quezon city', 'Single', 'filipino', 'roman catholic', 'baldomar2025@csr-scc.edu.ph', '$2y$10$H7OGFvyUzAcL8C9AtYPX6.r5VN/fJ8ixQ2wIgok6EVKWFwiX3Xm02', '09271441066', 'brgy.patun-an calatrava', 'vic baldomar', 'security officer', 'jesusa salaver', 'OFW', 'imelda baldomar', 'BRGY. patun an', 'auntie', 1, 1, 'brgy.patun an', 'PATUN AN ELEMENTARY SCHOOL', '2014 2015', 'TANON COLLEGE', '2021-2022', 'COLEGIO DE STA.RITA DE SAN CARLOS', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:28:43', '2025-08-18 05:28:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(149, '2005110401', 'Paris', 'Eliezer', 'Lapas', 'Male', '2005-11-04', 19, 'San Carlos City, Negros Occidental.', 'Single', 'Filipino', 'Iglesia Ni Cristo', 'eparis@csr-scc.edu.ph', '$2y$10$bKm5rGohCpkHJ2mmmf1cfu7LZ3YXd4xFWT2LTeJjunzQZ/5SOP2NS', '09940472258', 'South Villa 1, Saturn St. Blk 8, San Carlos City, Negros Occidental.', 'Joel P. Paris', 'Driver', 'Jennifer L. Paris', 'House Wife', 'Joel P. Paris', 'South Villa 1, San Carlos City Negros Occidental', 'Jocylen P. Johanson', 1, 1, 'South Villa 1, San Carlos City Negros Occidental.', 'Daisy\'s ABC School', '2016-2017', 'Tanon College', '2022-2023', 'Colegio de Santa Rita de San Carlos, Inc', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:29:45', '2025-08-18 05:29:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(150, '2004051401', 'Aritalla', 'Adrian Kim', 'Damandaman', 'Male', '2004-05-14', 21, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'aritalla@csr-scc.edu.ph', '$2y$10$W3nFsVnudTdCUc6ymSb5IuiFadSVfrN0A7m01v8faeYjdFhKpTp3W', '09056664935', 'Chico Street San Julio Subdivision Brgy.2', 'Jojit B. Aritalla ', 'Globe Technician', 'Ma Gemma D. Aritalla', 'Housewife', 'Jojit B Aritalla', 'Chico Street San Julio Subdivision Brgy.2', 'N/A', 1, 1, 'Chico Street San Julio Subdivision Brgy.2 ', 'Ramon Magsaysay Elementary school', '2016-2017', 'Colegio de Sto Tomas Recoletos Inc.', '2021-2022', 'Colegio de santa rita de san carlos inc. ', '2022-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:30:49', '2025-08-18 05:30:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(151, '2005100701', 'Librodo', 'Ria', 'Albia', 'Female', '2005-10-07', 19, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'ria@csr-scc.edu.ph', '$2y$10$18guoW9vQnwgYh6VkCbnG.LdIbr77S2rQoT86vPdPKVgAdMQJlbYu', '09099242171', 'Hda.Sta.Felomina Brgy. Palampas takas upper SCC', 'Romeo Librodo', 'Contractual', 'Ritchelle Albia', 'Contractual', 'Ritchelle Albia', 'hda.sta.felomina bryg.palampas', 'N/A', 1, 1, 'Hda.Sta.Felomina Brgy.Palampas Takas Upper SCC', 'Nagpayong  Elementary School Pasig City', '2017-2018', 'Nagpayong High School Pasig City', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:31:08', '2025-08-18 05:31:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(152, '2007022802', 'Villarmia ', 'Jiecel', 'Cabatuan', 'Female', '2007-02-28', 18, 'Sto. Hilayan Brgy. Prosperidad', 'Single', 'Filipino', 'Baptist', 'jiecel@csr-scc.edu.ph', '$2y$10$QzixMNPHiEdJYh08QiIfuuGZRoN61/3yVBmBzTeKsUVMn/vO0kEuK', '09756684011', 'Brgy. Prosperidad San Carlos City', 'Solomon Villarmia', 'Farmer', 'Aileen Cabatuan', 'farmer', 'NA', 'NA', 'NA', 1, 1, 'Brgy Prosperidad', 'Prosperidad Elementary School ', '2018 2019', 'Julio Ledesma', '2024 2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:32:17', '2025-08-18 05:32:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(153, '2007090401', 'Jirasol ', 'John Mark ', 'Albios ', 'Male', '2007-09-04', 17, 'San Carlos city ', 'Single', 'Filipino ', 'Roman Catholic', 'jjirasol@csr-scc.edu.ph', '$2y$10$vgxN7PMIQXBddkShr/.nQepuYvlDCQnhfOChcIJscHIqLGgq8Q.Ke', '09707420060', 'Sitio Apog apog ', 'Alex Jirasol ', 'Farmer ', 'Emely Jirasol ', 'House wife', 'Emely ', 'Sitio Apog apog ', 'No', 1, 1, 'Sitio Apog apog', 'Cod cod Elementary school ', '2018', 'Quezon national highschool ', '2025', 'Colegio de Santa Rita San Carlos lnc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:33:45', '2025-08-18 05:33:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(154, '2007070701', 'Camoro', 'Rayveen ', 'Barba', 'Male', '2007-07-07', 18, 'San Carlos City ', 'Single', 'Filipino ', 'Catholic ', 'camoro@csr-scc.edu.ph', '$2y$10$kq0d6vpwhp6JdmuzmlO7XODrlJsU3Z9otV7MOgkNHfl0dA9sZAqbC', '09359062854', 'Ylagan street ', 'Robinson ', 'Security Guard ', 'Amy barba', 'Housewife ', 'AMY BARBA', 'Ylagan street san Carlos City ', 'Parents ', 1, 1, 'Ylagan street ', 'CVGSMS', '2017-2018', 'Tañon college ', '2023-2024', 'CSR', '2025-2026', 'Csr', '2025-2026', 'Csr', '2025-2026', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:34:05', '2025-08-18 05:34:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(155, '2003041601', 'Arceo', 'Kenji', 'Fanega', 'Male', '2003-04-16', 22, 'Sam Carlos City ', 'Single', 'Filipino', 'Roman Catholic ', 'arceo@csr-scc.edu.ph', '$2y$10$4K2fF3ROHkEqsDOSevwCLeOQGvC0oeHHFTZK5wCjlre7k0rFwuay2', '09670646733', 'Big Tibuco, Purok Molave Brgy.I San Carlos City Negros Occidental ', 'Raymundo Y. Arceo Jr.', 'Teacher', 'Annabelle F. Arceo', 'Teacher ', 'Annabelle F. Arceo', 'Big Tibuco, Purok Molave Brgy.I', 'Rans F. Arceo', 1, 1, 'Big Tibuco, Purok Molave Brgy.I San Carlos City Negros Occidental ', 'Ramon Magsaysay Elementary School', '2016-2017', 'Julio Ledesma National High School', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:34:26', '2025-08-18 05:34:26', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(156, '2006121502', 'Magoncia', 'Merry Shaine', 'Canete', 'Female', '2006-12-15', 18, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'mmagoncia@csr-scc.edu.ph', '$2y$10$2h.wHCHpPJNJtjQ2f/TDbOsaaqfmF9nOrJ4im1mK1ObSBDsSMDdoK', '09941850176', 'Endrina St', 'Alfred Magoncia', 'handler/gapher', 'Archevelle Canete', 'job order', 'Archevelle Canete', 'Endrina St', 'NA', 1, 1, 'Endrina St', 'Ramon Magsaysay Elementary School', '2018-2019', 'Tanon college', '2024-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:36:37', '2025-08-18 05:36:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `students` (`id`, `id_number`, `last_name`, `first_name`, `middle_name`, `gender`, `birth_date`, `age`, `birth_place`, `civil_status`, `nationality`, `religion`, `email`, `password`, `contact_number`, `home_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_address`, `other_support`, `is_boarding`, `with_family`, `family_address`, `elem_address`, `elem_year`, `sec_address`, `sec_year`, `college_address`, `college_year`, `voc_address`, `voc_year`, `others_address`, `others_year`, `form138`, `moral_cert`, `birth_cert`, `good_moral`, `others1`, `others2`, `others3`, `notes`, `created_at`, `updated_at`, `is_active`, `profile_photo`, `lrn_no`, `contact_person`, `form137`, `parents_marriage_cert`, `baptism_cert`, `proof_income`, `brown_envelope`, `white_folder`, `id_picture`, `esc_app_form`, `esc_contract`, `esc_cert`, `shsvp_cert`) VALUES
(157, '2004123001', 'Calinawagan', 'Alea', 'Kyamko', 'Female', '2004-12-30', 20, 'San Carlos City Neg. Occ.', 'Single', 'Filipino', 'Catholic', 'acalinawagan@csr-scc.edu.ph', '$2y$10$2gMGoenLhXGO6OmlVS5TSuKVOJg5AjJJFmpw2tsmSxVYRdE/dHdMK', '09216984851', 'Villa Consuelo Algers San Carlos City Neg. Occ.', 'N/A', 'N/A', 'Jeka Calinawagan', 'Government employee', 'N/A', 'N/A', 'N/A', 1, 1, 'Villa Consuelo Algers San Carlos Ciry Neg. Occ.', 'Ramon Magsaysay Elementary School', '2016-2017', 'Colegio De Sto Tomas Recoletos', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:37:04', '2025-08-18 05:37:04', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(158, '2002071901', 'Escanillan', 'John Alexandre', 'Gabisan', 'Male', '2002-07-19', 23, 'San Carlos city', 'Single', 'Filipino', 'Baptist', 'escanillan@csr-csr.edu.ph', '$2y$10$OzGUz0gHIlUDBRiDV4E2EesR2pJX09Y0CE/lT.Gq86ZDWB0iBRgDO', '09663544572', 'Fatima Village phase 1 Brgy. Rizal Neg.occ', 'Jonathan S. Mendoza', 'Technician', 'Jeanilyn G. Escanillan', 'OFW', 'Jenalyn Escanillan', 'Fatima Village Brgy. Rizal', 'Ofelia Escanillan', 1, 1, 'Fatima Village Brgy. Rizal', 'Congressman Vicente Gustilo Sr. Memorial School', '2009-2015', 'Julio Ledesma National High School', '2016-2022', 'Colegio de Santa Rita De Carlos', '2022-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:41:14', '2025-08-18 05:41:14', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(159, '2006070401', 'Libodlibod ', 'Khamier Shayne ', 'Amiana ', 'Female', '2006-07-04', 19, 'San Carlos City Negros Occidental ', 'Single', 'Filipino ', 'Iglesia Ni Cristo', 'khamier@csr-scc.edu.ph', '$2y$10$e.eSgsDFjL//m.xxxQ.qZOQ9kxWg9W4UARr1r9Salz7x2vwu7liI6', '09686918604', 'Mondragon Street San Carlos City Negros Occidental ', 'Arnold Libodlibod ', 'Admin aid 4 store keeper 1 ', 'Gina libodlibod ', 'Housewife ', 'Khurt Libodlibod ', 'Mondragon Street San Carlos City ', 'Quinciana Libodlibod ', 1, 1, 'Mondragon Street San Carlos City Negros Occidental ', 'School of the future ', '2019', 'Tañon College ', '2023', 'Colegio de sta rita de san Carlos incorporated ', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:41:25', '2026-01-04 10:07:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(160, '2007090601', 'Laraño ', 'John Mark ', 'Taburnal ', 'Male', '2007-09-06', 17, 'Nabua Camarines Sur ', 'Single', 'Filipino ', 'Roman Catholic ', 'johnmarklarano9@gmail.com', '$2y$10$xwLKB20mpgyR1a9UTjCwjOfaDAPu8bZqxIcjLa/euwjGe.6gGAB36', '09319787205', 'Bosque Cabulihan Vallehermoso Negros Oriental ', 'Gregorio C. Laraño ', 'Driver ', 'Marife T. Laraño ', 'Housewife ', 'Elsa Laraño ', 'Bosque Cabulihan Vallehermoso Negros Oriental ', 'My Grandma ', 1, 1, 'Bosque Cabulihan Vallehermoso Negros Oriental ', 'Cabulihan Elementary School ', '2018', 'Saint Francis High School ', '2024', 'Colegio de Santa Rita de San Carlos INC.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:41:32', '2025-08-18 05:41:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(161, '2007061901', 'Veronas', 'Brent Stephen', 'Aburido', 'Male', '2007-06-19', 18, 'Calatrava Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'veronas@csr-scc.edu.ph', '$2y$10$LIb/6quczvX6GXHWIKCDEecSG0u70m4upbi.9PJMuDnpEG51WAMxi', '09317644711', 'Brgy. Bantayanon Neg. Occ.', 'Joseph D. Veronas', 'none', 'Aiza Mae C. Aburido', 'Vendor', 'Daisy C. Aburido', 'Brgy. Bantayanon Calatrava Neg. Occ', 'none', 1, 1, 'Brgy. Bantayanon Calatrava Neg. Occ.', 'Calatrava II Central School', '2019', 'Calatrava National High School', '2022', 'Colegio de sta rita de San Carlos incorporated', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:42:50', '2025-08-18 05:42:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(162, '2003052401', 'Ortega', 'Reighnamae Ann', 'Claudian', 'Female', '2003-05-24', 22, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'reighnamae24@gmail.com', '$2y$10$PEbzgU80FObMoYcGpMW1I.cYmJdoGgs4CSJA0zokYuOlHjJcDA8Se', '09999118309', 'Santol St., San Julio Subd. Brgy 2, San Carlos City, Negros Occidental', 'Ronald Antonio A. Ortega', 'Deceased', 'Annabel C. Ortega', 'Cashier', 'Roditha O. Hopwood', 'Australia', 'N/A', 1, 1, 'Santol St., San Julio Subd. Brgy 2, San Carlos City, Negros Occidental', 'Ramon Magsaysay Elementary School', '2009-2015', 'Colegio De Sto. Tomas - Recolletos, Inc.', '2016-2022', NULL, NULL, 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:43:30', '2025-08-18 05:43:30', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(163, '2002040701', 'Caballero', 'Louis Craig Dylan', 'Rentuaya', 'Male', '2002-04-07', 23, 'Cebu City', 'Single', 'Filipino', 'Catholic', 'dcaballero@csr-scc.edu.ph', '$2y$10$OYXdfNMXNwFK1xaug24WF.F.FTUxHYIttFaXnerUqELdlZ4p01oFi', '09055414166', 'San-Isidro, Calatrava', 'Nichol Tan', 'Business Man', 'Shiaryn Caballero', 'VA', 'N/A', 'N/A', 'Shermaine Caballero', 1, 1, 'Calatrava, San-Isidro', 'Colegio de Santa Rita de San Carlos, Inc.', '2008–2014', 'Colegio de Santa Rita de San Carlos, Inc.', '2014–2018', 'Colegio de Santa Rita de San Carlos, Inc.', '2022–Present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 05:47:17', '2025-08-18 05:47:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(164, '2005090501', 'Nellas', 'Jessica ', 'Marines ', 'Female', '2005-09-05', 19, 'San Carlos City  Negros Occidental', 'Single', 'Filipino', 'Catholic', 'nellas@csr-scc.edu.ph', '$2y$10$zcertO1ne8Oz4DMhJbRgX..P./XrV1zsgheuO8mhojcEPxHnM/Vm2', '09676323765', 'Sto.Niño Village Brgy.Buluangan', 'Jason Nellas', 'Rider', 'Melinda Marines', 'Lady Guard', 'Angelina Marines', 'Sto.Niño Village Brgy.Buluangan', 'None', 1, 1, 'Sto.Niño Village Brgy.Buluangan', 'Pano Olan Elem. School', '2011-2017', 'Don Carlos Ledesma Nationa High School', '2017-2022', 'Colegio De Sta.Rita Inc', '2023-2025', 'None', 'none', 'none', 'none', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 06:00:03', '2025-08-18 06:00:03', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(165, '1999122801', 'Descartin ', 'Hesah', 'Siabo ', 'Female', '1999-12-28', 25, 'Purok Caimito Brgy. San Juan Sipaway San Carlos City Neg. Occ.', 'Single', 'Filipino ', 'United Church of Christ in the Philippines (UCCP)', 'descartin@csr-scc.edu.ph', '$2y$10$K92wA3v9LUcdYhsJ9OJYi.oEgZq7cxJ8dabCIH.05/njp7PGza4T6', '09513439551', 'Purok Caimito Brgy. San Juan Sipaway San Carlos City Neg. Occ.', 'Herson P. Descartin ', 'Fisherman ', 'Susan S. Descartin ', 'House Wife', 'Larry Descartin ', 'Purok Caimito Brgy. San Juan Sipaway San Carlos City Neg. Occ.', 'N/A', 1, 1, 'Purok Caimito Brgy. San Juan Sipaway San Carlos City Neg. Occ.', 'San Juan Elementary School', '2010-2011', 'Sipaway National High School ', '2017-2018', 'Colegio de Santa Rita dae San Carlos Inc.', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 06:14:43', '2025-08-18 06:36:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(166, '2005012301', 'Bequilla', 'Arjay', 'Arioste', 'Male', '2005-01-23', 20, 'Brgy. Bagonbon', 'Single', 'Filipino', 'Roman Catholic', 'bequilla@csr-scc.edu.ph', '$2y$10$nfil9WElm3ab8PW2dc/poeMWZ23uepD9O4R0MuiBJ7F7xLbh6Bt/u', '09950726263', 'Brgy.Bagonbon San Carlos City, Neg Occ', 'Ricardo Bequilla', 'Farmer', 'Reggie Bequilla', 'House Wife', 'Reggie Bequilla', 'Brgy.Bagonbon San Carlos City ', 'None', 1, 1, 'Brgy.Bagonbon San Carlos City, Negros Occ.', 'Bagonbon Elementary School', '2010-2016', 'Bagonbon National High School', '2017-2022', 'Collegio De Sta.Rita San Carlos', '2023-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 06:22:47', '2025-08-18 06:22:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(167, '2000122001', 'Bongo', 'Brian Ric', 'Bito-on', 'Male', '2000-12-20', 24, 'Patun-an', 'Single', 'Filipino', '6', 'Bongo@csr-scc.edu.ph', '$2y$10$qz/zbqLYuNzmbxdBU20JaOXwahAmYUUqVP8WzlX6ZT9NS5yNRUOnW', '09051959904', 'Patun-an Calatrava Negros Occidental', 'Marlon Bongo', 'Carpenter', 'Ana Maria Bongo', 'House wife', 'Ana Maria Bongo', 'Patun-an', 'Myrna Diaz Hopp', 1, 1, 'Patun-an', 'Patun-an Elementary School', '2012-2013', 'Calatrava Senior High Score', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 06:24:59', '2025-08-18 06:24:59', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(168, '2007032401', 'Pilapil', 'Micah Claire', 'Tecson', 'Female', '2007-03-24', 18, 'San Carlos City, Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic', 'pilapil@csr-scc.edu.ph', '$2y$10$So7OM9HMjGh/zI5rgr1M5.DVPXMKNvGDdwZ2y4fM7LXFzcZmlA/rG', '09763974523', 'Campo 7, Brgy. V, San Carlos City', 'Mico V. Pilapil', 'Corporate Worker', 'Chona T. Pilapil', 'Teacher', 'N/A', 'pilapilmico@gmail.com', 'N/A', 1, 1, 'Campo 7, Brgy. V, San Carlos City', 'Andres Bonifacio Central School', '2018', 'Colegio De Sto. Tomas Recoletos. Inc', '2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 06:35:35', '2025-08-18 06:35:35', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(169, '1999032301', 'Micaller', 'Christian Roy', 'Diapana', 'Male', '1999-03-23', 26, 'Olongapo City', 'Single', 'Filipino', 'Roman Catholic', 'micaller@csr-scc.edu.ph', '$2y$10$HnI52TkUKfbphMEfqgUTvuWz7WH3UpgpSMQX9TRzsPebNNxq7Yw9G', '09087571209', 'Ylagan Ext. San Carlos City, Negros Occidental', 'Charlie Micaller', 'N/A', 'Catherine Micaller', 'N/A', 'N/A', 'N/A', 'N/A', 1, 1, 'Ylagan Ext. San Carlos City, Negros Occidental', 'Daisys ABC', '2013', 'CSTR', '2018', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 07:03:42', '2025-08-18 07:03:42', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(170, '2005031601', 'Gepitulan', 'Mathew Dominc', 'Fajardo', 'Male', '2005-03-16', 20, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'mathewdominicgepitulan@gmail.com', '$2y$10$U271Q8dOzBPsJBHyJBJPKOBD7y8KbpS458tmQnJ9ay0ZvZfRx3Eoa', '09434310114', 'Margarita Village Extension, Blk.32 Lot 7&8', 'Raymund B. Gepitulan', 'Government Employee', 'Maricel B. Gepitulan', 'Government Employee', 'Raymund B. Gepitulan', 'Margarita Village Extension, Blk.32 Lot 7&8', 'none', 1, 1, 'Margarita Village Extension, Blk.32 Lot 7&8', 'Colegio de Santa Rita de San Carlos Inc.', '2016-2017', 'Colegio de Santa Rita de San Carlos Inc.', '2021-2022', 'Colegio de Santa Rita de San Carlos Inc.', '2025-2026', NULL, NULL, 'University of Negros Occidental Recolletos', '2023-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 07:10:11', '2025-08-18 07:10:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(171, '2001031601', 'Entienza ', 'Jaster', 'Flora', 'Male', '2001-03-16', 24, 'Marcelo calatrava negros occidental ', 'Single', 'Pilipino', 'Catholic ', 'jasterentienza150@gmail.com', '$2y$10$0dUd/3Y6hBwcANBc9SEhteux2QXDO36EfcrtKnB/fLBMHvgpuBKMy', '09945871370', 'Balea Marcelo calatrava negros occidental ', 'Eugene Entienza ', 'Farmers', 'Esel Entienza ', 'House wife', 'Esel Entienza ', 'Jasterentienza150@gmail.com', 'Florencia flora', 1, 1, 'Balea Marcelo calatrava negros occidental ', 'Lagaan elementary school ', '2014/2015', 'Lagaan national high school ', '2019/2020', 'Colegio de Santa Rita San Carlos inc.', 'Undergraduate ', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 07:37:05', '2025-08-18 07:37:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(172, '2007051001', 'Zalun', 'Joshua', 'Torres', 'Male', '2007-05-10', 18, 'Valenzuela City ', 'Single', 'Filipino', 'Roman Catholic ', 'zalun@csr-scc.edu.ph', '$2y$10$darklabedOsmySjoZLLTOeRQx8B2ZlY8.WcniaUVuxjBUUIkkjiWa', '09927585175', 'Sittio babao Brgy Lipat-on Calatrava Negros occidental ', 'Wendel A. Zalun', 'Business ', 'Journalyn T. Zalun ', 'N/A', 'Pelagia C. Legaspino', 'Bgry Lipat-on Calatrava Neg occ', 'Shaina T. Zalun', 1, 1, 'Sittio babao Brgy Lipat-on Calatrava Negros occidental ', 'Lemery Elementary School ', '2018-2019', 'Calatrava Senior High School Stand Alone', '2024-2025', 'Colegio de Sta. Rita', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 08:06:22', '2025-08-18 08:06:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(173, '2006022801', 'Capada', 'Laurence ', 'Caramihan ', 'Male', '2006-02-28', 19, 'Calatrava ', 'Single', 'Filipino', 'Roman Catholic ', 'capada@csr-scc.edu.ph', '$2y$10$iqc3zbGdmsKqA8L8yaCB9.Ku2sp4l1qwITPxh.9Kh845zphjsoFva', '09935718911', 'Brgy dolis Calatrava Negros Occidental ', 'Alberto Caramihan Capada', 'Farmer ', 'Mercedita  Caramihan. Capada', 'BRGY HEALTH WORKER ', 'Mercedita Caramihan Capada', 'Brgy dolis Calatrava ', '  sister', 1, 1, 'Brgy Dolis Calatrava ', 'Dolis elementary school ', '2018', 'Dolis National High school ', '2022', 'Colegio de Santa Rita', '2025 2029', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 08:14:25', '2025-08-18 08:14:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(174, '2007050301', 'Ybañez', 'Erich Gayle', 'Teo', 'Female', '2007-05-03', 18, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'gayle@csr-scc.edu.ph', '$2y$10$zZOoQOl8QqHmPoCRYDRcS..Bbm095uaUml3eLZcYVTLR1e5YqTYAq', '09625834027', 'Saint Vincent Subd. San Carlos City, Negros Occidental', 'Joseph Erly P. Ybañez', 'Driver', 'Gerlie T. Ybañez', 'Housewife', 'Joseph Erly P. Ybañez', 'Saint Vincet Subd. San Carlos City Negros Occidental', 'brother', 1, 1, 'Saint Vincent Subd. San Carlos City Negros Occidental', 'Tandand Sora Elementary School', '2019', 'Colegio de Santo Tomas-Recoletos', '2025', 'Colegio de Santa Rita ', '2029', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 08:22:29', '2025-08-18 08:22:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(175, '2002013101', 'HEMIDA', 'LLOYD HARLEY', 'CABAÑERO ', 'Male', '2002-01-31', 23, 'SAN CARLOS CITY', 'Single', 'FILIPINO ', 'CATHOLIC ', 'hemida@csr-scc.edu.ph', '$2y$10$wJuzNoox1Grf0bOjkvU.Re6kwFxvrCC/8NR4uXCkgFw.5P2I3cHS6', '09692272657', 'NEWTOWN SUBDIVISION BARANGAY 1 ', 'NA', 'NA', 'ENEMY HEMIDA LABIO', 'House Wife', 'MARICAR ESPINOSA DOMECILLO ', 'Doshermanos Street Barangay 3 ', 'NA', 1, 1, 'NEWTOWN SUBDIVISION BARANGAY 1 ', 'LAGTANG ELEMENTARY SCHOOL', '2009 - 2015', 'Julio Ledesma National High School', '2018-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 08:31:43', '2025-08-18 08:31:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(176, '2006081401', 'Llesis', 'Gemcie', 'Sarsuelo', 'Female', '2006-08-14', 19, 'san carlos city negros occidental', 'Single', 'filipino', 'roman catholic', 'llesis@csr-scc.edu.ph', '$2y$10$SbgRQE8cQqLaRHPtvK.Y4u9b41CwrDdOiZf53vv2Dc8uD2dlEPYJa', '09498085953', 'broce street laguda apartelle', 'Pedro P. llesis jr.', 'self employee', 'maritess llesis', 'police woman', 'Pedro P llesis jr', 'broce street laguda apartelle', 'pedro p llesis jr', 1, 1, 'broce street laguda apartelle', 'ramon magsaysay elementary school', '2019', 'colegio de santo tomas recolletos', '2023', 'colegio de santa rita de san carlos inc', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 08:46:42', '2025-12-21 06:05:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(177, '1999120901', 'Rasonable', 'Joshua', 'Ramas', 'Male', '1999-12-09', 25, 'Cebu City', 'Single', 'Filipino', 'Catholic', 'jrasonable@csr-scc.edu.ph', '$2y$10$5AYLifzUZY4SrA3XBRaiwuF09rf8b1d6rBEoaE6tJBjHrzxfjME7K', '09079387417', 'Brgy. 1 St john', 'Joel', 'vendor', 'Gloria', 'House Wife', 'Gloria ', 'Brgy. 1 st john', 'Helga ', 1, 1, 'Brgy. Cabungahan Calatrava ', 'Cebu City Central School', '2011-2012', 'Tañon College', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 09:05:33', '2025-08-18 09:05:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(178, '2006102901', 'Albarico III', 'Fernando', 'Tapinit', 'Male', '2006-10-29', 18, 'Calatrava Negros Occidental', 'Single', 'Filipino', 'Catholic', 'FernandoAlbarico@csr-edu.ph', '$2y$10$kL.HWpxXoTaSHbd7.l801ecU8geXGTsrh1PV7tnGtSNjr9HdQ.IoC', '09702440545', 'calatrava', 'fernando D. albarico jr.', 'TRUCK DRIVER', 'Lucy c. Albarico', 'ofw', 'fernando d. alabrico jr.', 'calatrava', 'lucy c. tapinit', 1, 1, 'calatrava', 'calatrava II central school', '2019', 'calatrava national high school', '2022', 'Colegio de Sta Rita de San Carlos Incorporated', '2025-2029', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 09:43:57', '2025-08-18 09:43:57', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(179, '2006121401', 'Degracia', 'Ritchie jose', 'Roma', 'Male', '2006-12-14', 18, 'San Carlos city ', 'Single', 'Filipino ', 'Roman Catholic ', 'rdegracia@csr-scc.edu.ph', '$2y$10$Lqc4VBso72yxeCWtthSTu.E5SplgphYQ8J0.Xho8FrBRh1i2QpYlG', 'O9910661588', 'Baranggay bagonbon san carlo city negros occidental ', 'Rechard L. Degracia ', 'Work at office ', 'Geraldine R. Degracia', 'Teacher ', 'Geraldine R Degracia ', 'Brgy. Bagonbon san carlos city negros occidental ', 'Ritchie grace R. Degracia ', 1, 1, 'Brgy. Bagonbon san carlos city negros occidental ', 'Bagonbon Elementary school ', '2018-2019', 'Tañon college ', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 10:09:06', '2025-08-18 10:09:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(180, '2004102301', 'Alcansare', 'Tracy Gwynneth', 'Cantero', 'Female', '2004-10-23', 20, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'gwentraceyg@gmail.com', '$2y$10$6RrG04S.wOXTngzVh3DDxuOqBlgN1AlNvqV1rFaKzjZ2qesx.2vGW', '09564826299', 'Newtown Subdivision lot10 Blk3', 'N/A', 'N/A', 'Joyce Cantero', 'OFW', 'Luzvisminda Cantero', 'Newtown Subdivision lot10 Blk3', 'N/A', 1, 1, 'Newtown Subdivision lot10 Blk3', 'Tandang Sora Elementary School', '2011-2017', 'Tañon College', '2017-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 10:49:01', '2025-08-18 10:49:01', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(181, '2004072801', 'Bordo', 'Gerneil Grace', 'Cabellon', 'Female', '2004-07-28', 21, 'San Carlos city, Negros Occidental', 'Single', 'Filipino', 'LDS Christian', 'Graeroswell@gmail.com', '$2y$10$1cd0Q8xMlLAAevuuZVHXJefjqdx63xk1zSO1GlXsd6YhG77iI/k1W', '09637687383', 'Phase III, Fatima Vill., Brgy. Rizal ', 'N/A', 'N/A', 'Deceased', 'N/A', 'Bernonilo Cabellon', 'Phase III, Fatima Vill,. Brgy. Rizal', 'Mary Angeline Cabellon Egos', 1, 1, 'Phase III, Fatima Vill,. Brgy. Rizal', 'Dagat-Dagatan Elementary School', '2011-2017', 'Julio Ledesma National High School', '2017-2019', 'Colegio de Sta. Rita de San Carlos, Inc.', '2023- present', 'Emmanuel John Institute of Science and Technology', '2023-2023', 'Kaunlaran High School', '2019-2023', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:00:05', '2025-08-18 11:00:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(182, '2001041501', 'Toledo', 'Gerry Boy', 'Beleganio', 'Male', '2001-04-15', 24, 'Buluangan Santo niño', 'Single', 'Filipino', 'Catholic', 'toledo@csr-scc.edu.ph', '$2y$10$V1jYTeGBeUzKXgdp2n3gYeRVV7E/ajI8PDS171ysDEaDQN..ylNru', '09076386931', 'Brg. 1, San Carlos City', 'Gerry Padre Toledo', 'Trycle Driver', 'Maria Golda Beleganio Toledo', 'House Wife', 'N/A', 'N/A', 'Lover Dawn B. Toledo', 1, 1, 'Brgy. 1, San Carlos City Neg. Occ.', 'SCMCIES', '2015-2016', 'Barrio Luz Cebu City', '2016-2017', 'Colegio de Santa Rita de San Carlos, INC', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:09:29', '2025-08-18 11:09:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(183, '2004041001', 'Ringia', 'Aisah', 'Pamanay', 'Female', '2004-04-10', 21, 'Naval Biliran', 'Single', 'Filipino', 'Islam', 'ringia@csr-scc.edu.ph', '$2y$10$wEElK4EFRff4l8C1m.i5n.IIn3ImqUo93sGph5sSDlUvsE23CX8B.', '09305482725', 'San juan bonifacio brgy 5 San Carlos City', 'Mohammad Ali Grande Ringia', 'Trycle', 'Tamoling Umpar Pamanay', 'Business', 'N/A', 'N/A', 'Janissah', 1, 1, 'San Juan Bonifacio brgy 5 San Carlos City', 'ADRESS BONIFACIO CENTRAL SCHOOL', '2015-2016', 'JULIO LEDESMA NATIONAL HIGH SCHOOL', '2016-2017', 'Colegio de Santa Rita de San Carlos, INC', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:10:05', '2025-08-18 11:10:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(184, '2003110501', 'Magnanao', 'Einjel Luh', 'Diamante', 'Female', '2003-11-05', 21, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'magnanao@csr-scc.edu.ph', '$2y$10$i4qyrRxqxELM35/QjEYnhufm54gcgzpN6GsYzqOXUpeWfr1iMCXVq', '09672544681', 'Chico St San Julio Subd', 'Eric Michael Dinsmore', 'Self-Employed', 'Luthy Grace Magnanao', 'Self-Employed', 'N/A', 'N/A', 'Fr. Erwin Magnanao', 1, 1, 'Chico St San Julio Subd', 'Colegio De Sta. Rita', '2015-2016', 'Colegio De Sto Tomas', '2019-2020', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:22:15', '2025-08-18 11:22:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(185, '1999061901', 'Cayao', 'Precious Mae', 'Cuevas', 'Female', '1999-06-19', 26, 'San Carlos City Hospital', 'Single', 'FILIPINO', 'CATHOLIC', 'cayao@csr-scc.edu.ph', '$2y$10$AXv4Tg1c/zpDxFf/mxgt.e7JxfoV4HMeBlTxuZHa9mlZyzOWbTi66', '09918311700', 'Calatrava Neg. Occ.', 'PEDRO H. CAYAO', 'deceased', 'NENA C. CAYAO', 'House Wife', 'Nena Cayao', 'Calatrava', 'me, myself and I', 1, 1, 'Calatrava Neg.Occ', 'Colegio de Sta Rita de San Carlos INC.', '2012-2013', 'Colegio de Sta Rita de San Carlos INC.', '2016-2017', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 11:39:53', '2026-01-10 00:02:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(186, '2005062701', 'Roble', 'Andrei Margarette', 'Logatiman', 'Female', '2005-06-27', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'andreimargaretteroble@gmail.com', '$2y$10$v0btB1hfspQFeJlulrVRXeld1oThRaMaBGFEVPAfGi6PxvEVETWZO', '09082317311', 'S Carmona St, San Carlos City', 'Andrew Roble', 'Security Officer', 'Angela Roble', 'House Wife', 'Andrew Roble', 'S Carmona St, San Carlos City', 'Angela Roble', 1, 1, 'S Carmona St, San Carlos City', 'Congressman Gustillo Senior Memorial School', '2011-2017', 'Collegio de Sto Tomas Recolletos', '2017-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:41:19', '2025-08-18 11:41:19', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(187, '2005070201', 'Malabo', 'Francis', 'Pangar', 'Male', '2005-07-02', 20, 'San carlos city', 'Single', 'FILIPINO', 'Roman catholic ', 'fmalabo@csr-scc.edu.ph', '$2y$10$aEcHjFOr5ISGQjIjwtAa9O3Wmqu/pakfmKBAaMh4jcXsq7J95e22m', '09519742721', 'Brgy 1 1st street san carlos city', 'Ramil', 'Radio announcer ', 'Milagros', 'House wife', 'Melissa', 'Molave street', 'N/A', 1, 1, 'Canlaon city', 'Haguimit elementary school ', '2016-2017', 'La granja national high school ', '2019-2020', 'Colegio de sta. Rita de san carlos', '2023-2024', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:51:23', '2026-01-03 09:49:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(188, '2005102801', 'De Asis ', 'Raniel John ', 'Columna ', 'Male', '2005-10-28', 19, 'Sagay city', 'Single', 'Filipino', 'Negros Occidental ', 'rjdeasis@csr-scc.edu.ph', '$2y$10$WwNBYtNKi7uaSnvYu3U4yeWsaKteiMuVkjGaCDJbg0oNKvSeKMynG', '09931574487', 'Brgy.rizal', 'Randy deasis', 'Wilder ', 'Aibeth de asis', 'House wife ', 'Rosalie allarce', 'Brgy. Rizal', 'Na', 1, 1, 'Bgry.rizal ', 'Eusebio Lopez Memorial Integrated school', '2016-2017', 'Eusebio Lopez Memorial Integrated school', '2022-2023', 'Colegio de Santa Rita De San Carlos Inc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:52:40', '2025-08-18 11:52:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(189, '2002092001', 'Alpitche', 'Joey', 'C', 'Male', '2002-09-20', 22, 'so. villarante brgy nataban', 'Single', 'Filipino', 'Roman Catholic', 'alpitche@csr-scc.edu.ph', '$2y$10$mgEv8AlvG0Xa0oAkSZUDaejZBPCBQheOsYXftvyEsC1HgrN4izHei', '09977012884', 'So.Villarante Brgy. Nataban San Carlos City Neg. Occ.', 'Jose Alpitche', 'i donno sir', 'Rosalina Alpitche', 'OFW', 'Vilma Canoy Canabano', 'so. Villarante brgy. Nataban', 'none', 1, 1, 'So. Villarante Brgy. Nataban', 'Marago-os Elementary school', '2008', 'Julio ledesma national highschool', '2017', 'Colegio de santa rita ', '2022', 'csr', '2022', 'csr', '2022', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:53:47', '2025-08-18 11:53:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(190, '2004102501', 'Ramas', 'Frank Julius', 'Ybañez', 'Male', '2004-10-25', 20, 'San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'framas@csr-scc.edu.ph', '$2y$10$ewKc8eSvjyqgbJsgAZveY.mIKlWkcSqSTMjyW7qD89PzZ41rCA1Za', '09052421934', 'Greenville Extension (Balas) San Carlos City Negros Occidental\r\n\r\n', 'Ronald F. Ramas', 'Agriculture ', 'Evangeline Y. Ramas', 'House Wife', 'N/A', 'N/A', 'N/A', 1, 1, 'Greenville Extension (Balas) San Carlos City Negros Occidental ', 'Tandang Sora Elementary School', '2011-2017', 'Colegio de Sto. Tomas Recoletos', '2017-2023', 'Colegio de Sta. Rita San Carlos City Inc.', '2023-', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 11:55:07', '2025-08-18 11:55:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(191, '2001082101', 'Lusabia', 'John Caroll', 'Palabrica', 'Male', '2001-08-21', 23, 'Buenavista Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'jlusabia@csr-scc.edu.ph', '$2y$10$62liLB3E/HZ2X/r4VODLT.znYg8aVE3M1d6Egr1A5fWOS2SvViWNi', '09947961401', 'BUENAVISTA CALATRAVA', 'Gaspar ', 'Electrician ', 'Philippine ', 'Teacher', 'Jomaica ', 'Brgy 3 Araneta', 'Auntie', 1, 1, 'BUENAVISTA CALATRAVA', 'Buenavista Elementary School', '2014-2015', 'Colegio De Santa Rita De San Carlos Inc.', '2018-2019', 'Colegio De Santa Rita De San Carlos Inc.', '2025-2026', NULL, NULL, 'Calatrava Senior High School', '2020', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 12:07:06', '2025-08-18 12:07:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(192, '2005070101', 'Vidal', 'Ralph Zoren', 'Burgos', 'Male', '2005-07-01', 20, 'Butuan City, Agusan Del Norte', 'Single', 'Filipino', 'Roman Catholic', 'ralphvidal@csr-scc.edu.ph', '$2y$10$bm.5CYCUkIE6H0ApmZTxUOYUYgyubF.GbVF3WJqklJccwzu8TLuWq', '09614812645', 'Broce Street, San Carlos City, Negros Occidental', 'Rene Vidal', 'Casual Government Employee', 'Daisy Vidal', 'Casual Government Employee', 'Rosemarie Burgos', 'Butuan City, Agusan Del Norte', 'None', 1, 1, 'Broce Street, San Carlos City', 'Congressman Vicente Gustilo Senior Memorial School (CVGSMS)', '2012-2016', 'Agusan National High School (ANHS)', '2018-2021', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 12:20:11', '2025-08-18 12:20:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(193, '2005082201', 'Pandoro', 'Ralph', 'Racaza', 'Male', '2005-08-22', 19, 'San Carlos City Negros Occidental ', 'Single', 'Filipino ', 'Catholic ', 'pandoro@csr-scc.edu.ph', '$2y$10$h.w0KPtFPn.PRwR5oqArR.4MXEiKYnwNqe6bqlyqrRkSVRBkOxSw2', '09614246834', 'Purok 1, Brgy.Ermita Sipaway Island ', 'Ricardo', 'Seaman', 'Rosalia ', 'Housewife ', 'Rosalia ', 'Purok 1, Brgy.Ermita Sipaway Island', 'None', 1, 1, 'Purok 1, Brgy.Ermita Sipaway Island', 'Ermita Elementary School ', '2016-2017', 'Sipaway National High School ', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 13:19:06', '2025-08-18 13:19:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(195, '2005052401', 'Maypa', 'Adrian James', 'Apuhin', 'Male', '2005-05-24', 20, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'maypa@csr-scc.edu.ph', '$2y$10$.D0eiFjUu76Zqy5Ft30N9OT9QXYLLl.jeZnlXfig9pa7xvJ0ytdN2', '09953125288', 'Ylagan Extension, brgy. 6, San Carlos City Negros Occidental', 'Carlos Niño J. Maypa', 'Unemployed', 'Abigail Marie A. Maypa', 'Call Center', 'Juliet A. Jimenez', 'Ylagan Extension, San Carlos City Negros Occidental ', 'none', 1, 1, 'Ylagan Extension, San Carlos City Negros Occidental', 'School Of The Future', '2018', 'Ramon Teves Pastor Memorial - Dumaguete Science High School', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:39:37', '2025-08-18 23:39:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(196, '2005082801', 'Mateo', 'Ma. Belle Castel', 'E.', 'Female', '2005-08-28', 19, 'Sancarlos City Negros Occidental ', 'Single', 'Filipino', 'Catholic ', 'mbmateo@csr-scc.edu.ph', '$2y$10$SZBYVjjyjZEyiP/dm2tSEOrb/YBJ3lUQTu3Pw1iQPJbfhVRvTcPUS', '09994063687', 'Ylagan Ext. Scc', 'Joel Mateo', 'Vendor', 'Maricel Enoya', 'House Wife', 'Joel Mateo', 'Ylagan Extension ', 'Marjorie Mateo', 1, 1, 'Ylagan Extension ', 'CVGSMS', '2018', 'Colegio De Sta.Rita', '2024', 'Colegio De Sta. Rita', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:42:29', '2025-08-18 23:42:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(197, '2006071001', 'Mandal', 'Alliana Lis', 'Rojo', 'Female', '2006-07-10', 19, 'San Carlos City', 'Single', 'Filipino ', 'Born Again Christian ', 'mandal@csr-scc.edu.ph', '$2y$10$ogiMC9cCI8fSnyiEyvVj8uo1EECTUHTTUZA9W8LxyZENeT5OTBljG', '09945106217', 'Bonifacio Brgy. 5, San Carlos City ', 'Alan Mandal', 'Private School Teacher ', 'Elisa Mandal', 'Public School Teacher ', 'Alan Mandal', 'Alan Mandal ', 'none ', 1, 1, 'Bonifacio Brgy. 5, San Carlos City ', 'Tanda Sora Elementary School ', '2018', 'Julio Ledesma National Highschool ', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:42:35', '2025-08-18 23:42:35', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(198, '2006033101', 'Babor', 'Fritz Menald', 'Cabatuan', 'Male', '2006-03-31', 19, 'San Carlos City, Negros Occidental', 'Single', 'Philippines', 'Catholic', 'babor@csr-scc.edu.ph', '$2y$10$0BGoWawfbuvzQ0IMzWvXEuROTrKnp8yWtB/ksu8OuK6U5USPU4/0O', '09614746390', 'Endrina Street, San Carlos City, Negros Occidental', 'Armen C. Babor', 'N/A', 'Aldelyn C. Babor', 'Teacher', 'Aldelyn C. Babor', 'Endrina Street, San Carlos City, Negros Occidental', 'N/A', 1, 1, 'Endrina Street, San Carlos City, Negros Occidental', 'CVGSMS', '2018', 'Colegio de Santa Rita', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:42:54', '2025-08-18 23:42:54', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(199, '2006021201', 'Rigor', 'Ma.Elizabeth ', 'Favores', 'Female', '2006-02-12', 19, 'San Carlos City Hospital', 'Single', 'Filipino ', 'Catholic ', 'mrigor@csr-scc.edu.com', '$2y$10$CwPCJ/ayjIrIBApQIXGN5eFoFCkUgNGch04vQUJqHpliXjHjHXWqq', '09952715702', 'So. Maloloy on Brgy Punao', 'Marlon Rigor', 'N/A', 'Perla Rigor', 'N/A', 'Perla Rigor', 'So. Maloloy on', 'Andrew Rigor', 1, 1, 'So. Maloloy on Brgy Punao', 'Don Juan Ledesma', '2018', 'Colegio de Santa Rita de San Carlos Inc.', '2024', 'Colegio de Santa Rita de San Carlos Inc.', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:44:12', '2025-08-18 23:44:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(200, '2005020801', 'ALPABETE', 'MARY DICK', 'LAPINOSA', 'Male', '2005-02-08', 20, 'San carlos city,negros occedintal', 'Single', 'Filipino', 'inc', 'alpabete@csr-scc.edu.ph', '$2y$10$XQwxnX8zOlB3ZwsQJ0Jxx.2Rt2lY76t1F1nR4NcwLDgl.SVd.3AGC', '09153202221', 'baranggay punao', 'DIXON ALFABETE', 'DRIVER', 'EMERITA ALFABETE', 'HOUSE WIFE', 'EMERITA ALPABETE', 'BARANGGAY PUNAO', 'DIXON ALFABETE', 1, 1, 'BARANGGAY PUNAO', 'SAN MARIANO ELEMENTARY SCHOOL', '2018', 'SAN MARIANO NATIONAL HIGH SCHOOL', '2024', 'COLLEGIO DE SANTA RITA ', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:45:28', '2025-08-18 23:45:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(201, '2005060201', 'Hermano ', 'Elizalde ', 'Boliboli', 'Male', '2005-06-02', 20, 'Calatrava ', 'Single', 'Filipino ', 'Catholic ', 'hermano@csr-scc.edu.ph', '$2y$10$4bC3DRGrx9tw33vxrnbmAOkweWY8LWXPxrdojdPFLNUK1jUQtZcxG', '09773187064', 'brgy. San Isidro, Calatrava, Negros Occidental ', 'Elizalde D. Hermano Sr.', 'N/A', 'Mercy B. Hermano ', 'N/A', 'Mercy B. Hermano ', 'brgy. San Isidro, Calatrava, Negros Occidental ', 'N/A', 1, 1, 'brgy. San Isidro, Calatrava, Negros Occidental ', 'San Isidro Elementary School ', '2015-2016', 'Calatrava National High School ', '2019-2020', 'Colegio De Santa Rita de San Carlos, Inc', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:45:30', '2025-08-18 23:45:30', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(202, '2005121601', 'Baromeda', 'Lizamae', 'Montes', 'Female', '2005-12-16', 19, 'San Carlos City ', 'Single', 'Filipino', 'Roman Chatholic', 'baromeda@csr-scc.edu.ph', '$2y$10$OIESDsPhm68feDmdce6bAOgyQLic4FyjZ/gYeE86frCVOWYDMJQ0O', '09463396102', 'So. Agbulod, brgy. Prosperidad, San Carlos City, Negros Occidental ', 'Felix Donato M. Baromeda', 'Farmer', 'Evelyn M. Baromeda ', 'House Wife', 'Evelyn M. Baromeda ', 'So. Agbulod, Brgy. Prosperidad San Carlos City ', 'None', 1, 1, 'So. Agbulod, brgy. Prosperidad San Carlos City, Negros Occidental ', 'Punod Elementary School ', '2017-2018', 'Our Lady of Peace Mission School, Inc.', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:45:47', '2026-02-11 05:16:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(203, '2005110701', 'Paradero', 'Darlene', 'Cometa', 'Female', '2005-11-07', 19, 'Filrock Buaya, Lapu-lapu City, Cebu ', 'Single', 'Filipino ', 'Roman Catholic ', 'paradero@csr-scc.edu.ph', '$2y$10$237JIi4JqteKhJGK/Ua9reQOkWS4lY0wDpP47IeGJeswpieUfzAcG', '09489599458', 'So. Agbulod, Brgy. Prosperidad, San Carlos City ', 'Gaudioso Y. Paradero ', 'Construction Worker ', 'Nanette C. Paradero ', 'Housewife ', 'Nanette C. Paradero ', 'So. Agbulod, Brgy. Prosperidad, San Carlos City ', 'None', 1, 1, 'So. Agbulod, Brgy. Prosperidad, San Carlos City ', 'Prosperidad Elementary School ', '2017-2018', 'Our Lady of Peace Mission School, Inc.', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:45:54', '2025-08-18 23:45:54', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(204, '2005101901', 'Gonzales', 'Franzin', 'Roja', 'Male', '2005-10-19', 19, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'fgonzales@csr-scc.edu.edu.ph', '$2y$10$D7gd.6sZwt5ePL/2h5.bsuJAbcrESjrkYjjLxBvoG8vEY2FgpWlwm', '09982637731', 'Urban Phase 4, Brgy. Rizal.', 'Federico C. Gonzales', 'Teacher', 'Maribel R. Gonzales', 'House maker', 'Federico C. Gonzales', 'Urban Phase 4, Brgy Rizal', 'None', 1, 1, 'Urban Phase 4, Brgy. Rizal', 'Grenville Elementary School', '2018', 'Colegio de sta rita de San Carlos', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:46:12', '2025-08-18 23:46:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(205, '2006080901', 'Carillo', 'Dandreb', 'Yap', 'Male', '2006-08-09', 19, 'San Carlos City', 'Single', 'Filipino', 'Chatholic', 'carillo@csr-scc.edu.ph', '$2y$10$0jX31S7tIYpPft55Pb5L5.0iglcWlaKNlPf9NTmI33FDnFQxFf7bK', '09851610647', 'Santo Nino, BRGY. Guadalupe, San Carlos City, Negros Occidental.', 'Danilo Carillo', 'Tricycle Driver', 'Norma Carillo', 'House Wife', 'Norma Carillo', 'Santo Nino, BRGY. Guadalupe, San Carlos City, Negros Occidental.', 'Brothers', 1, 1, 'Santo Nino, BRGY. Guadalupe, San Carlos City, Negros Occidental.', 'Guadalupe Elementary School', '2016-2017', 'Don Carlos Ledesma National High School', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:46:43', '2025-08-18 23:46:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(206, '2004090601', 'Libre', 'Erica mie', 'Zamora', 'Female', '2004-09-06', 20, 'San Carlos City', 'Single', 'Filipino ', 'Catholic ', 'elibre@csr-scc.edu.ph', '$2y$10$kiTK9KEu2Xz2aM.4OZNbRuVxwWzULpZiipGgRcrVINHnILozpHJ0e', '09542574711', 'Brgy. Bantayanon Negros Occidental ', 'Alberto Libre ', 'Driver', 'Melodia Libre ', 'House wife', 'Melodia Libre ', 'Calatrava negros Occidental ', 'Alberto Libre ', 1, 1, 'Calatrava Negros Occidental ', 'Calatrava 1 Central school ', '2017-2018', 'Calatrava national high school ', '2021-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:47:18', '2025-08-18 23:47:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(207, '2006013001', 'Dugol-dugol', 'Erl Noreen Ann', 'Jabaybay', 'Female', '2006-01-30', 19, 'Calatrava Health Center', 'Single', 'Filipino', 'Catholic', 'edugol-dugol@csr-scc.edu.ph', '$2y$10$gNs2dXp7X4sxRzzw/iQfFOZJAKLiK/guWqay68lCD7Hp/xOH8jvC2', '09919356947', 'Aqua Village, Brgy Bantayanon. Calatrava Negros Occidental', 'Ernesto Dugol-dugol', 'n\\a', 'Junalyn Jabaybay', 'beautician', 'Carolina Jabaybay', 'Calatrava negros occidental', 'Rebecca dugol-dugol', 1, 1, 'new town, Sancarlos', 'Calatrava 1 Central School', '2017-2018', 'Calatrava National High School', '2021-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:47:47', '2025-08-18 23:47:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(208, '2005041901', 'Villador', 'Christian Lloyd ', 'Tan', 'Male', '2005-04-19', 20, 'San Carlos City', 'Single', 'Filipino ', 'Catholic ', 'villador@csr-scc.edu.ph', '$2y$10$vDK0VcsYSB95Jn1O1A.iEOj6bn/jGoRUGuqeBvT0EOKq.EY2SI0uy', '09166356425', 'Emerald Street, Teachers Village, San Carlos City ', 'Roberto A. Villador Jr. ', 'Businessman ', 'Arlene T. Villador', 'Retired ', 'N/A', 'N/A', 'N/A', 1, 1, 'Emerald Street, Teachers Village, San Carlos City ', 'School of the Future ', '2017-2018', 'Colegio de Santo Tomas de Recoletos', '2023-2024', '', '', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-18 23:48:24', '2025-08-18 23:53:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(209, '2005071901', 'Suarez', 'Julea', 'Larita', 'Female', '2005-07-19', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic ', 'jsuarez@csr-scc.edu.ph', '$2y$10$gGcPalkz2ZZpiSnHPoS5kektWtOqKSiXwOJ1YdMUZk7VK7/qJhnD2', '09945871574', 'H.c. rigoR street ', 'Armin Suarez', 'N/A', 'Maribel Larita', 'Sales person', 'Janja Jane Cedaso', 'H.C. Rigor Street', 'N/A', 1, 1, 'H.c. rigoR street ', 'Tandang Sora Elementary School ', '2018', 'Julio Ledesma National High School ', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:49:49', '2025-08-18 23:49:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(210, '2004122501', 'Canoy', 'Kurt Bryan', 'Obeso', 'Male', '2004-12-25', 20, 'Brasan Bagawines Vallehermoso Negros Oriental', 'Single', 'Filipino', 'Roman Catholic', 'kcanoy@csr-scc.edu.ph', '$2y$10$oZzizJ2.j.aXxvMxZCWvFeSi2h3VklV5BRLk8Cc0TOW/K1iIwQGZW', '09928590472', 'Brasan Bagawines Vallehermoso Negros Oriental', 'Juan Canoy', 'Bus Conductor', 'Ailyn Obeso Canoy', 'OFW', 'Aurelia Canoy', 'Brasan Bagawines Vallehermoso Negros Oriental', 'Editha Canoy', 1, 1, 'Brasan Bagawines Vallehermoso Negros Oriental', 'Don Vicente Lopez Senior Memorial Elementary School', '2016-2017', 'St. Francis High School Inc.', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:50:59', '2025-08-18 23:50:59', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(211, '2004091601', 'Egonia', 'Johanna', 'Togle', 'Female', '2004-09-16', 20, 'municipality of calatrava ', 'Single', 'Filipino ', 'Catholic', 'jegonia@csr-scc.edu.ph', '$2y$10$UlsM4hZFv1ZNPrUXgCWg1eIpl5jdAKqv6eRnoTaw52QUY6IADhCJu', '09519680759', 'Brgy. San Isidro Calatrava Negros Occidental ', 'Ernesto Egonia', 'Fisherman', 'Joenalyn Egonia', 'Housewife ', 'Joenalyn Egonia', 'Brgy. San Isidro Calatrava Negros Occidental ', 'Elyn Egonia', 1, 1, 'Brgy. San Isidro Calatrava Negros Occidental ', 'Brgy. San Isidro Elementary school ', '2017-2018', 'Calatrava Senior High School-Stand Alone', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:52:29', '2025-08-18 23:52:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(212, '2006073001', 'Gerogalin', 'John Curl', 'Palabrica', 'Male', '2006-07-30', 19, 'San Carlos', 'Single', 'Pilipino', 'Roman Catholic', 'jcgerogalin@csr-scc.edu.ph', '$2y$10$xC7K06fh3zAue3U4.nucGecYF02yZUo8/tJV.5Iz7dSXv5aC1H2sO', '09937914575', 'Brgy. Buenavista Calatrava. Neg. Occ', 'Rey Gerogalin', 'Baker', 'Glonie Gerogalin', 'Office staff', 'Glonie Gerogalin', 'Brgy. Buenavista', 'Gloria Palabrica', 1, 1, 'Brgy. Buenavista', 'Brgy. Buenavista', '12-13', 'Colegio de Santa Rita de San Carlos, inc', '23-24', 'Colegio de Santa Rita de San Carlos, inc', '25-26', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:53:50', '2025-08-18 23:53:50', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(213, '2006042202', 'Gabia', 'John Paul ', 'Abanid', 'Male', '2006-04-22', 19, 'Barangay Bagonbon San Carlos City ', 'Single', 'Filipino ', 'Roman Catholic ', 'jpgabia@csr-scc.edu.ph', '$2y$10$Y3cyxv.IBjJgEP7WU8PZoOWaViTAdEe45THXWyhvnCZ5YFKfL06R2', '09622481017', 'Barangay Bagonbon San Carlos City ', 'Joverson B. Gabia', 'Construction worker ', 'Fe A. Gabia', 'House wife', 'Jove-ann Gabia', 'Barangay Bagonbon ', 'Jobelle Gabia', 1, 1, 'Barangay Bagonbon San Carlos City ', 'Bagonbon elementary school ', '2012-2013', 'Bagonbon Senior High School ', '2023-2024', 'Colegio De Santa Rita De San Carlos, INC.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:53:54', '2025-08-18 23:53:54', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(214, '2006012901', 'Palabrica ', 'Ralstien King ', 'Pasilan ', 'Male', '2006-01-29', 19, 'Muntinlupa Alabang hospital ', 'Single', 'Filipino ', 'Roman Catholic ', 'rpalabrica@csr-scc.edu.ph', '$2y$10$i0U0INH9ZQvJz8qoEWCoreQAXXx4BH/335IcfkIz/tJt8nWMLliW.', '09939698969', 'Brgy. Buenavista Calatrava Negros Occidental ', 'Luslodvin A. Palabrica ', 'Electrician ', 'Elvie P. Palabrica ', 'Housewife ', 'Elvie P. Palabrica ', 'Brgy. Buenavista Calatrava Negros Occidental ', 'Luslodvin Palabrica ', 1, 1, 'Bryg. Buenavista Calatrava Negros Occidental \r\n', 'BUENAVISTA ELEMENTARY SCHOOL ', '2012-2013', 'Calatrava Senior High-Stand Alone School ', '2023-2024', 'Colegio de Santa Rita de san Carlos, INC.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:54:31', '2025-08-18 23:54:31', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(215, '2006072901', 'Young', 'Venj', 'Yosoris', 'Male', '2006-07-29', 19, 'Guihulngan City Negros Oriental', 'Single', 'Filipino', 'Philippines', 'young@csr-scc.edu.ph', '$2y$10$uFsU6Qtt2hvSe6VQNBcJ6OB8LbSqfqTZ2o2G8M87t7NLOFlamIdpu', '09911015531', 'Planas, Guihulngan City Negros Oriental.', 'Vincent T. Young', 'Traders', 'Junadith S. Yosoris', 'Farmer', 'Princess', 'Guihulngan City', 'None', 1, 1, 'Planas, Guihulngan City Negros Oriental.', 'Villegas Elementary School', '2017-2018', 'GNHS-P Guihulngan National High School Poblacion', '2023-2024', 'Colegio De Santa Rita De San Carlos Inc.', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:55:26', '2026-02-27 02:33:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(216, '2005091501', 'Brazona', 'Ivane', 'Silador ', 'Male', '2005-09-15', 19, 'Calatrava', 'Single', 'Filipino ', 'Catholic', 'brazona@csr-scc.edu.ph', '$2y$10$nN9P2yB/PWsZR2VShuZpRe0Y9fRd9X7v/5O5MyIAkE3t8EdVDAuxS', '09617003728', 'Brngy, anie, calatrava ', 'Edralyn Brazona', 'Farmer', 'Emily  Brazona', 'House wife ', 'Christel Brazona', 'Brngy 1, sancarlos city', 'Christel brazona', 1, 1, 'Brgy anie municipality of calatrava ', 'Calatrava 2 central school ', '2017', 'Dolis national Highschool ', '2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:56:08', '2025-08-18 23:56:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(217, '2006081701', 'Traya', 'Jessabelle', 'Ornillo', 'Female', '2006-08-17', 19, 'sitio Brasan,brgy bagawines Vallehermoso Negros Oriental', 'Single', 'Filipino', 'Catholic', 'jtraya@csr-scc.edu.ph', '$2y$10$tyYwXxajuz9/mNI7otQCSunmiaTWs01W3EIgK.hwI2RwzWlYVytKO', '09770832553', 'sitio Brasan,brgy bagawines Vallehermoso Negros Oriental', 'Eugenio Traya', 'tricyle driver', 'Estelita Traya', 'house wife', 'meshel Pansoy', 'sitio Brasan,brgy bagawines Vallehermoso Negros Oriental', 'Juvileth Madugay', 1, 1, 'sitio Brasan,brgy bagawines Vallehermoso Negros Oriental', 'dvlsmes', '2017-2018', 'Vallehermoso National High School', '2023-2024', 'Colegio de Santa Rita de San Carlos Inc', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-18 23:58:36', '2025-08-18 23:58:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(218, '2005051401', 'Entienza', 'Jim Lord', 'N/A', 'Male', '2005-05-14', 20, 'Bacolod City', 'Single', 'Filipino', 'Roman Catholic', 'entienza@csr-scc.edu.ph', '$2y$10$mJE8AjEEIy7mygMZVUvbZuZgasYtuRp14tmYvoGR.cAPBaQwgJhdK', '09163939624', 'Brgy. Laga-an Calatrava Negros Occidental ', 'N/A', 'N/A', 'Alma R. Entienza', 'House Wife', 'Modesta R. Entienza', 'Brgy. Laga-an Calatrava Negros Occidental ', 'Mary Joy Entienza', 1, 1, 'Brgy. Laga-an Calatrava Negros Occidental \r\n', 'Laga-an Elementary School ', '2015-2016', 'Laga-an National High Scool', '2022-2023', 'Colegio De Santa Rita De San Carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:01:28', '2025-08-19 00:01:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(219, '2004020601', 'Bordaje', 'Jessa Mae ', 'Esula', 'Female', '2004-02-06', 21, 'Calatrava negros Occidental ', 'Single', 'Filipino ', 'Catholic ', 'bordaje@csr-scc.edu.ph', '$2y$10$BwSfm8CdH1KN/PEHuVtu5eg22j/QDAVk50ihehY1BeoVs7FIlloJ.', '09943799662 ', 'Brgy san isidro ', 'Jesus ', 'N/A', 'Genoviva ', 'House wife', 'Genoviva ', 'Brgy san isidro ', 'N', 1, 1, 'Brgy san isidro ', 'Brgy san isidro ', '2016-2017', 'Calatrava negros Occidental ', '2024-2025', 'Colegio de santa rita college de san carlos ', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:02:32', '2025-08-19 00:02:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(220, '2006072001', 'Dacumos', 'Argie', 'Villaplaza', 'Male', '2006-07-20', 19, 'Pantao Calatrava Neg. Occ.', 'Single', 'Pilipino', 'Roman catholic', 'dacumos@csr-scc.edu.ph', '$2y$10$WsOXUUQOLGK.2nWe.Z1Hme0cC7w3Tup9t4q.3aPtka1hXCOPABIAi', '09154970202', 'Mahilum Calatrava Neg. Occ', 'Argie C. Dacumos Sr.', 'NA', 'Vergieta V. Dacumos', 'Na', 'Aurelia G. Villaplaza', 'Mahilum Calatrava Neg. Occ.', 'NA', 1, 1, 'Mahilum Calatrava Neg. Occ.', 'Hp. Mahilum Elementary school', '2017', 'Toboso National High School', '2023 ', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:09:16', '2025-08-19 00:09:16', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(221, '2005093001', 'Baloyo', 'Ian Angelo', 'Rigor', 'Male', '2005-09-30', 19, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Baptist', 'baloyo@csr-scc.edu.ph', '$2y$10$XOjsSfINvkBEcLcFy3ck3OR.2nhGZgozgoXYQXx0MtKQNNY1V0Wb2', '09998675826', 'Lot 24, Blk. 16, Stage 3, Phase 4, Brgy. Rizal, San Carlos City, Negros Occidental', 'Fernan F. Baloyo', 'Businessman', 'Maricriz R. Baloyo', 'Businesswoman', 'Miralona R. Araujo', 'Greenville, Urban', 'N/A', 1, 1, 'Lot 24, Blk. 16, Stage 3, Phase 4, Brgy. Rizal, San Carlos City, Negros Occidental', 'Tandang Sora Elementary School', '2018', 'Tañon College', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:14:36', '2025-08-19 00:14:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(222, '2003062501', 'Destua', 'Ibb Andre ', 'U.', 'Male', '2003-06-25', 22, 'Calatrava Negros Occidental', 'Single', 'Filipino', 'Uccp', 'destua@csr-scc.edu.ph', '$2y$10$Endh9PzDUeArJwQXQ7PZrOjMo0VdX7I7PCvTaeJzQI...M/GF8THK', '09659536186', 'Calatrava Negros Occidental ', 'Ibarra L. Destua', 'Electrician ', 'Memie U. Destua', 'Housewife ', 'Ibarra L. Destua', 'ibarra@gmail.com', 'Memie U. Destua', 1, 1, 'Destua', 'Calatrava II Central School', '2010', 'Calatrava National Highschool ', '2020', 'Coldegio De Santa Rita', '2025', 'Calatra', '', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:15:12', '2025-08-19 00:15:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(223, '2004092801', 'Alberio ', 'Nyl Necson ', 'Hamili', 'Male', '2004-09-28', 20, 'San Carlos City Hospital ', 'Single', 'Filipino ', 'Roman Catholic ', 'nylnecsonalberio8@gmail.com', '$2y$10$YBLeMUkF8EoTRaOwbyQxCOJ9MEMTciVmY.A9nGhCDBEHYVCa/FHDq', '09943107328', 'Cotcot, bagawines, Vallehermoso, Negros Oriental ', 'NECASIO ALBERIO ', 'N/A', 'ROSLYN ALBERIO ', 'N/A', 'N/A', 'N/A', 'N/A', 1, 1, 'Cotcot, bagawines, Vallehermoso, Negros Oriental ', 'Pinucauan Elementary School ', '2017', 'Saint Francis High School ', '2023', 'Colegio de Santa Rita ', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:21:45', '2025-08-19 00:21:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `students` (`id`, `id_number`, `last_name`, `first_name`, `middle_name`, `gender`, `birth_date`, `age`, `birth_place`, `civil_status`, `nationality`, `religion`, `email`, `password`, `contact_number`, `home_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_address`, `other_support`, `is_boarding`, `with_family`, `family_address`, `elem_address`, `elem_year`, `sec_address`, `sec_year`, `college_address`, `college_year`, `voc_address`, `voc_year`, `others_address`, `others_year`, `form138`, `moral_cert`, `birth_cert`, `good_moral`, `others1`, `others2`, `others3`, `notes`, `created_at`, `updated_at`, `is_active`, `profile_photo`, `lrn_no`, `contact_person`, `form137`, `parents_marriage_cert`, `baptism_cert`, `proof_income`, `brown_envelope`, `white_folder`, `id_picture`, `esc_app_form`, `esc_contract`, `esc_cert`, `shsvp_cert`) VALUES
(224, '2002071701', 'Jara', 'Clare', 'Mana-it', 'Female', '2002-07-17', 23, 'San Carlos City Hospital ', 'Single', 'Filipino ', 'Roman Catholic ', 'jara@csr-scc.edu.ph', '$2y$10$9lSTOoch7A9pGTLChrT4QeF0geWEE9tz6Q6ZaQpegf0ijsyS4McNu', '09166236822', 'Toboso, Negros Occidental ', 'Morito M. Jara Jr.', 'Driver', 'Elizabeth M. Jara', 'Teacher', 'Elizabeth M. Jara', 'Toboso, Negros Occidental ', 'Morito M. Jara Jr.', 1, 1, 'Toboso, Negros Occidental ', 'Toboso Central School ', '2013', 'Toboso National High School ', '2019', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:33:10', '2025-08-19 00:33:10', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(225, '2005110301', 'Bayer', 'Justine', 'Auditor', 'Male', '2005-11-03', 19, 'San Carlos City', 'Single', 'Filipino', 'Baptist', 'bayer@csr-scc.edu.ph', '$2y$10$FSffS6PUADcG/HJOFHvAmeMA6jD4Lb9OCx47VB9LmVRe1cIWG7ZgS', '09685921550', 'Blk 11 Lot 17 Fatima Village Phase 1 Brgy, Rizal San Carlos City\r\n', 'Rogelio A. Bayer', 'Driver', 'Eugene A. Bayer', 'House Wife', 'Primitiva M. Rigor', 'Blk 11 Lot 17 Fatima Village Phase 1 Brgy,Rizal', 'NA', 1, 1, 'Sitio Rosario Brgy, Rizal San Carlos City', 'Dela Rosa Elementay School', '2018', 'Julio Ledesma National Hing School', '2024', 'a', 'a', 'a', 'a', 'a', 'a', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 00:47:07', '2025-08-19 00:47:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(226, '2005032202', 'SOLITARIO', 'MA. ANGELA', 'ARNOCO', 'Female', '2005-03-22', 20, 'SAN CARLOS CITY', 'Single', 'FILIPINO', 'ROMAN CATHOLIC', 'msolitario@csr-scc.edu.ph', '$2y$10$QMBWv51AbaxfNFGk19MEWe/v7erizfX0FkkTKZe8XPF6D8024ktBm', '09627156268', 'PUROK LUBI, BARANGAY 1', 'ARMANDO L. SOLITARIO SR.', 'CASUAL GOVERNMNENT EMPLOYEE', 'REYNILDA A. SOLITARIO', 'HOUSE WIFE', 'REYNILDA A. SOLITARIO', 'PUROK LUBI', 'JUDY ANN A. SOLITARIO', 1, 1, 'PUROK LUBI, BARANGAY 1', 'FLORENTINA LEDESMA ELEMENTRAY SCHOOL', '2016-2017', 'JULIO LEDESMA NATIONAL HIGH SCHOOL', '2022-2023', 'COLEGIO DE STA.RITA, INC.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:43:42', '2025-08-19 01:43:42', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(227, '2025011801', 'Calago ', 'Arnel ', 'Intig ', 'Male', '2025-01-18', 0, 'Paghumayan Calatrava negros occidental ', 'Single', 'Pilipino', 'Catholic ', 'acalago@csr-scc.edu.ph', '$2y$10$djO/0xKQ44tThNQ.2aqUce0ql.AzJOj1Gn2QF.7AAL5w2i6mQL33a', '09451360924', 'Paghumayan Calatrava negros occidental ', 'Arnel Calago Sr.', 'Taxi driver ', 'Alma Calago ', 'OFW', 'Lionila Igtig ', 'BRGY Paghumayan Calatrava negros occidental ', 'N/a', 1, 1, 'Paghumayan Calatrava negros occidental ', 'Paghumayan Elementary school ', '2010-2016', 'Paghumayan national high school ', '2016-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:45:52', '2025-08-19 01:45:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(228, '2002120301', 'Yap', 'Ben Cyron Francis', 'Labang', 'Male', '2002-12-03', 22, 'Ormoc, Leyte', 'Single', 'Filipino ', 'Roman Catholic ', 'benyap@csr-scc.edu.ph', '$2y$10$zdwQR7cBFucmrhtT9UDrYeLHoqJY7Ys4PKq7vljUMTxDoMwSgSaEe', '09956292072', 'V. Gustilo St. Brgy iii, San Carlos City, NIR', 'Romeo P. Yap', 'Businessman ', 'Dinah L. Yap', 'OFW', 'Recy Gwyn L. Bulfa', 'Brgy. Look, Calatrava', 'N/A', 1, 1, 'V. Gustilo St. Brgy iii, San Carlos City, NIR', 'Daisy\'s ABC', '2012-2015', 'Colegio de Sto. Tomas - Recoletos', '2015-2021', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:48:49', '2025-08-19 01:48:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(229, '2005112601', 'Ricafort ', 'Reymark ', 'Y', 'Male', '2005-11-26', 19, 'Sancarlos ', 'Single', 'Filing ', 'Roman Catholic ', 'ricafort@csr-scc.edu.ph', '$2y$10$qZByz4vfk2fMeF7dLHqjsu5ts1Yd9gu28o4b2/BlXoUciIR/qhlbW', '09563966975', 'Doshermanos street brgy -3', 'Larry Ricafort ', 'Farmer ', 'Gina', 'House wife ', 'Lenimae', 'Doshermanos ', 'Auntie ', 1, 1, 'Doshermanos ', 'Cabonao Elementary school ', '2016-2017', 'Julio ledesma ', '2023-2024', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:50:18', '2025-08-19 01:50:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(230, '2003090801', 'Florencondia ', 'Mary Girl ', 'Olaguer ', 'Female', '2003-09-08', 21, 'San Carlos City Hospital Ylagan St. ', 'Single', 'Filipino ', 'Roman Catholic ', 'mflorencondia@csr-scc.edu.ph', '$2y$10$Hn61PRpQYJ/Zsw3iddmrDOAiQYJ9HBlhUCJBhmpyZNoUM4mpzWxrW', '09927245060', 'Brgy. Lemery,  Calatrava,  Negros Occidental ', 'Noli P. Florencondia ', 'Carpenter ', 'Ma.Louella O. Florencondia ', 'Housewife ', 'Ma.Louella O. Florencondia ', 'Brgy. Lemery, Calatrava, Negros Occidental ', 'Donald Ansag', 1, 1, 'Brgy. Lemery, Calatrava, Negros Occidental ', 'Lemery Elementary School ', '2014', 'Subic National High School ', '2021', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:51:11', '2025-08-19 01:51:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(231, '2006061701', 'Benitez', 'June Cynth', 'A', 'Male', '2006-06-17', 19, 'Gaboc Malaiba Canlaon City Negros Oriental', 'Single', 'Filipino', 'Catholic', 'jbenitez@csr-scc.edu.ph', '$2y$10$r7KBWHutbCKyDR9XxeU6zO9wo8HO9fDCIpnN.u1B6SzKcnhcfQlEu', '09657869381', 'canlaon City', 'Pedro Benitez', 'Farmer', 'Herminigilda Benitez', 'farmer', 'herminigilda Benitez', 'Gaboc canlaon city negros oriental', 'Joefren Benitez', 1, 1, ' Gaboc Canlaon city negros Oriental', 'Gaboc Elementary School', '2013', 'Jose B. Cardenas Memorial High School', '2023', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:51:37', '2025-08-19 01:51:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(232, '2006051501', 'Barbon', 'Joannah Kaye', 'Repuela', 'Female', '2006-05-15', 19, 'San Carlos City Hospital', 'Single', 'Filipino', 'Roman Catholic', 'jkbarbon@csr-scc.edu.ph', '$2y$10$MB6stxYubbQnPjvG4cLjy.XWIv6BlKMmpJ8jXDgq6QPK585Og9Cz2', '09057475369', 'Sto.Nino Village, Brgy. Buluangan, San Carlos City, Negros Occidental, Philippines', 'Oscar A. Barbon Jr.', 'Shrimp Pond Hydrun', 'Aurelia R. Barbon', 'Fish Vendor', 'Aurelia R. Barbon', 'Sto.Nino Village, Brgy. Buluangan, San Carlos City, Negros Occidental, Philippines', 'Oscar A. Barbon Jr.', 1, 1, 'Sto.Nino Village, Brgy. Buluangan, San Carlos City, Negros Occidental, Philippines', 'Pano-olan Elementary School', '2017-2018', 'Don Carlos Ledesma National High School', '2023-2024', 'Colegio de Sta. Rita de San Carlos Inc.', 'Ongoing', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:56:22', '2025-08-19 01:56:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(233, '2005120801', 'BASCON', 'ROSE JANE', 'MAYAGMA', 'Female', '2005-12-08', 19, 'SAN CARLOS CITY NEG. OCC.', 'Single', 'FILIPINO', 'BORN AGAIN', 'rbascon@csr-scc.edu.ph', '$2y$10$b6ZPZ70BitHZPPqPBVPL6eymhWL5H8WdzbNfvi9DELsG0R0oPngua', '09709525744', 'PUROK CALUMPANG BRGY.1 SAN CARLOS CITY NEGOS OCCIDENTAL', 'JUNAVEY D. BASCON', 'TRICYCLE DRIVER', 'RONILA C. MAYAGMA', 'HOUSE WIFE', 'ANGELIE BASCON', 'PUROK CALUMPANG BRGY. 1 SAN CARLOS CITY NEG. OCC.', 'CARLOS TINGAL', 1, 1, 'PUROK CALUMPANG BRGY. 1 SAN CARLOS CITY NEG. OCC.', 'TANDANG SORA ELEMENTARY SCHOOL', '2017', 'JULIO LEDESMA NATIONAL HIGH SCHOOL', '2023', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:56:25', '2025-08-19 01:56:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(234, '2005110201', 'Caratao', 'Franchesca Sandra Mei ', 'Pescante', 'Female', '2005-11-02', 19, 'San Carlos', 'Single', 'Filipino', 'Roman Catholic', 'fcaratao@csr-scc.edu.ph', '$2y$10$DeG3pvlPtRDgZg44BexdpeUbw8VMKOr9dCwj2ZIFOyk5V3unJ563y', '09662309463', 'Brgy. Punao, San Carlos City, Neg. Occ.', 'Jeveey B. Caratao', 'Merchandiser', 'Lolita P. Caratao', 'Housewife', 'N/A', 'N/A', 'N/A', 1, 1, 'Brgy. Punao, San Carlos City, Neg. Occ.', 'Talave Elementary', '2017-2018', 'Julio Ledesma National High School', '2023-2024', 'Colegio de Sta. Rita de San Carlos Inc.', 'Ongoing', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 01:56:40', '2025-08-19 01:59:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(235, '2004101901', 'Martinez', 'Rejie', 'Destura', 'Male', '2004-10-19', 20, 'San Carlos City', 'Single', 'Pilipino', 'Catholic', 'rmartinez@csr-scc.edu.ph', '$2y$10$2Ukg/Xjk5EARynhwvbn3B.Xq18CsKVXg2pxQhCeK0WgeRiLCO4GpS', '09085196418', 'Purok Gemelina Brgy.1 San Carlos City', 'Jilson E. Ko', 'Electrician', 'Annabella D. Martinez', 'Vendor', 'Rona M. Villarmente', 'Purok Gemelina Brgy.1', 'Jerry E. Ko', 1, 1, 'Purok Gemelina Brgy.1 San Carlos City', 'Florentina Ledesma Elementary School', '2016-2017', 'Julio Ledesma National High School', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:00:51', '2025-08-19 02:00:51', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(236, '2005111801', 'Babor', 'Marlo', 'Balasababas', 'Male', '2005-11-18', 19, 'San Carlos City', 'Single', 'Pilipino', 'Seventh Day Adventest', 'mbabor@csr-scc.edu.ph', '$2y$10$KeBZEt5y7TKmVLsvZ8NLquxv5EYoNm54aL7RLigrK6vNGoPg5hUFu', '09935972379', 'Barangay Tagbino Vallehermoso Negros Oriental', 'Cerilo M. Babor', 'Farmer', 'Maria B. Babor', 'Farmer', 'Maria B. Babor', 'Barangay Tagbino Vallehermoso Negros Oriental', 'N/A', 1, 1, 'Barangay Tagbino Vallehermoso Negros Oriental ', 'Dominador A. Paras Memorial Elementary School', '2016/2017', 'Rafaela R. Labang Natinal High School', '2020/2023', 'Lolegio De Santa Rita De San Carlos.INC', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:05:25', '2025-08-19 02:05:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(237, '2004073101', 'Ortega', 'dave', 'abog', 'Male', '2004-07-31', 21, 'Sancarlos City NEG OCC', 'Single', 'Filipino', 'INC', 'daveortega@csr-scc.edu.ph', '$2y$10$zIat0.vPkRvGxKmyZ.E0sOEXVejrHB8EhJLzakrj613TZFxYDdeua', '09065116809', 'Urban phase 4 brgy Rizal', 'danilo', 'N/A', 'Annabelle', 'N/A', 'Annabelle', 'Urban Phase 4 brgy Rizal', 'N/A', 1, 1, 'Urban Phase 4 brgy Rizal', 'Green Valle ', '2016-2017', 'Julio ledesma National highSchool', '2022-2023', 'Colegio desanta Rita de sancarlos, INC', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:09:39', '2025-08-19 02:09:39', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(238, '2004020701', 'Mission', 'Christian Marc Andrey', 'Vellahermosa', 'Male', '2004-02-07', 21, 'st.egmamatay', 'Single', 'Filipino', 'INC', 'mission@csr-scc.edu.ph', '$2y$10$sMhFgbD9iAHZGipC3jAkRuRdzrHsjOLyCuK3FsxrQyjGp.yfy3Gj.', '09069308025', 'brgy.Codcod', 'Mario Caminade', 'N/A', 'Annable Mission', 'N/A', 'Mary Alma ybanez', 'brgy.Codcod', 'N/A', 1, 1, 'BRGY.Codcod', 'Codcod elemantary School', '2014-2015', 'Quezon National high School', '2022-2023', 'Colegio Desanta Rita De SanCarlos', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:09:47', '2025-08-19 02:09:47', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(239, '2005121701', 'Rafales', 'John Benedict', 'Apostol', 'Male', '2005-12-17', 19, 'Manila', 'Single', 'Pilipino', 'Catholic', 'jrafales@csr-scc.edu.ph', '$2y$10$I1qgLucq/tCNKQ63cpzGBeojKE8WcZxS1au6bEja0yt3xbnwsMWJ.', '09267021592', 'Sto. Mantulungan Brgy. Buenavista Calatrava NIR', 'Zosimo Rafales', 'N/A', 'Jovelyn Apostol', 'House wife', 'Evangeline Apostol', 'Sto. Mantulungan Brgy. Buenavista Calatrava NIR', 'Lizel Apostol', 1, 1, 'Sto. Mantulungan Brgy. Buenavista Calatrava NIR', 'Menchaca elementary ', '2016-2017', 'Tañon College ', '2022-2023', 'St.Rita College', '2024-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:10:06', '2025-08-19 02:10:06', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(240, '2006051301', 'Baran', 'C-J', 'Garcia', 'Male', '2006-05-13', 19, 'San Carlos City Neg.Occ.', 'Single', 'Filipino ', 'Roman Catholic ', 'baran@csr-scc.edu.ph', '$2y$10$1QIONPv0oacdg6L.VAfzV.cy4y6BxmkAThyhV9yGI.Rwjon9SxBJq', '09353334182', 'So.Abaca brgy.Palampas San Carlos City Neg.Occ.', 'Jonjie Baran', 'Brgy.Tanod', 'Arcily Garcia', 'House wife', 'Jonjie Baran', 'So.Abaca brgy.Palampas', 'Romel Baran', 1, 1, 'So.Abaca brgy.Palampas', 'Malindog Elementary school ', '2016-2017', 'Julio Ledesma National high school ', '2021-2022', 'Colegio De Sta.Rita San Carlos ', 'N/A', 'Colegio De Sta.Rita San Carlos ', 'N/A', 'Colegio De Sta.Rita San Carlos ', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:17:00', '2025-08-19 02:17:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(241, '2006021901', 'Sandag', 'Jierel', 'Baran', 'Male', '2006-02-19', 19, 'San carlos ', 'Single', 'Pilipino', 'Roman Catholic ', 'jerelsandag@gmail.com', '$2y$10$luzk8LH6I1m5UV6uJDCxFOzq1xGhCxJdOLw0Am6b0Dg/dlI9bqPWK', '09924565730', 'So. Abaca. Bargy Palampas, San Carlos City Neg occ.', 'Owen Sandag', 'Factory Worker', 'Edilyn Sandag', 'House wife', 'Romel Baran', 'So abaca Brgy Palampas', 'Emely Abueme', 1, 1, 'So abaca brgy Palampas', 'Nagpayon Elementary school ', 'N/A', 'Bagonbon National Highschool ', 'N/A', 'Colegio De Santa Rita de San Carlos', '', 'Colegio De Santa Rita de San Carlos ', 'N/A', 'Colegio De Santa Rita de San Carlos ', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:17:09', '2025-08-19 02:17:09', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(242, '2005011401', 'Allera', 'Claire Ann ', 'Rabadillo', 'Female', '2005-01-14', 20, 'Cagay, Brgy Palampas', 'Single', 'Pilipino ', 'Catholic ', 'allera@csr-scc.edu.ph', '$2y$10$btcXCDFCjyi3fIpqGXvR4.N4UrGPhXK.kZEAltVF0JBT2lsQeADOO', '09631666192', 'Malindog, Brgy Bagonbon ', 'Dionesio', 'Pilipino ', 'Juditha', 'Pilipino ', 'Dionesio ', 'Malindog, Brgy Bagonbon ', 'Kris - Ann Allera', 1, 1, 'Malindog, Brgy Bagonbon ', 'Malindog, Brgy Bagonbon ', '1-6 ', 'Ledesma National High School ', '11-12', 'Colegio de Santa Rita de San Carlos .Inc.', 'Second year College ', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:18:16', '2025-08-19 02:18:16', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(243, '2006051302', 'Neniel', 'Carlos John', 'Cantero', 'Male', '2006-05-13', 19, 'San Carlos City Neg.occ 6127', 'Single', 'FILIPINO', 'Roman Catholic ', 'neniel@csr-scc.edu.ph', '$2y$10$IdSq0Fex7j89pZFkYEQTy.Nkk2pkhHVHgeKosFpyJRhNKxtK2ajZ6', '09663011669', 'Fatima Village Phase 6 Relocation Site', 'Roger D NENIEL', 'Laborer ', 'Ailene C Neniel', 'OFW / house keeper ', 'Roger C Neniel', 'Fatima Village Phase 6 Relocation Site', 'N/A', 1, 1, 'Fatima Village Phase 6 Relocation Site', 'Tandang Sora Elementary School', '2017-2018', 'Julio Ledesma National Highschool', '2023-2024', 'Colegio De Sta Rita ', '2024-present ', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:21:01', '2025-08-19 02:21:01', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(245, '2004030502', 'ALOBA', 'JOSEPH BRIAN', 'DAYUDAY', 'Male', '2004-03-05', 21, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'alobajosephbrian@gmail.com', '$2y$10$QU6L2qntfyysjsvkU7ctx..boJF/9FN1YggLnDy72C9gepAG.uJ4e', '09684041290', 'So,Sto.Nino Brgy.Guadalupe', 'Joel Aloba', 'Seaman', 'Camela Dayuday', 'Kenneth Contructions Office', 'Camela Dayuday', 'Brgy Guadalupe', 'none', 1, 1, 'Brgy Guadalupe San Carlos City', 'Ramon Magsaysay Elementary School', '2014-2015', 'Colegio de Sto.Tomas inc', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:26:07', '2025-08-19 02:26:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(246, '2004123101', 'Bargayo', 'Joannes Troi Vinces', 'Manayon', 'Male', '2004-12-31', 20, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'bargayo@csr-scc.edu.ph', '$2y$10$WD0HbNN/SZIIBzrvH8b/c.y/IhSxcsvgYU1.DzNi2c3ClxguMtUEy', '09627059547', 'Brgy Bagonbon San Carlos City Negros Occidental', 'Jovie C. Bargayo', 'None', 'Annabelle M. Baragayo', 'House wife', 'N/A', 'N/A', 'N/A', 1, 1, 'Brgy Bagonbon San Carlos City Negros Occidental', 'Bagonbon Elementary School', '2016-2017', 'Bagonbon National High School', '2022-2023', 'College of Saint Rita', '2024-Present', NULL, NULL, 'Tesda(Electrical Installation and Maintenance) EIM NC2', '2024-2025', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:29:20', '2025-08-19 02:29:20', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(247, '2004122701', 'Silvano', 'Reynald', 'Bagahansol', 'Male', '2004-12-27', 20, 'So.haguimitan brgy palampas, San carlos city, Negg occ.', 'Single', 'Pilipino', 'Born again', 'rsilvano@csr-scc.edu.ph', '$2y$10$vK40jFYlEc/ttXcqv7kZEefoDEBR/2UqoMvNAAIm0TbfCpLqQdlLy', '09632608456', 'So.haguimitan brgy palampas, San carlos city, Negg occ', 'Benjie Silvano', 'tricycle driver', 'Elizabeth Silvano', 'Dietary in hospital ', 'Flora mae Reporas', 'Purok molave brgy 1', 'Elizabeth Silvano ', 1, 1, 'So. haguimitan brgy palampas ', 'Tandamg sora', '2014', 'Julio ledesma national highschool ', '2022', 'N/A', 'N/A', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:36:01', '2025-08-19 02:36:01', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(248, '2002102401', 'Sotes', 'Hyannah Mae', 'Ordaniza', 'Female', '2002-10-24', 22, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Catholic', 'hmsotes@csr-scc.edu.ph', '$2y$10$.30hgM/z2/XPCeMAapQUm.mN1zgj7uP8r6npf7ws5g5ncB9kd2pgi', '09456142080', 'Rizal St, Brgy. III, Scc, Neg. Occ.', 'Jaime O. Hino', 'Currently working as a contraction worker on Manila', 'Hanah O. Sotes', 'House Wife', 'Sandy Sotes', 'Rizal St, Brgy. III, Scc, Neg. Occ.', 'Ann Mildreth Cantones', 1, 1, 'None', 'Tandang Sora Elementary School (TSES)', '2014-2015', 'Julio Ledesma National Highschool', '2021-2022', 'Colegio de Santa Rita de San Carlos', 'Ongoing', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 02:40:05', '2025-08-19 02:40:05', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(249, '2006070201', 'Lariosa', 'J.M Arth Laurence', 'Labrador', 'Male', '2006-07-02', 19, 'brgy bagonbon', 'Single', 'filipino ', 'roman catholic', 'ljmarthlaurence@gmail.com', '$2y$10$9KTwEx7vL8K3hxCq6NJsCOVTmm9GRpuqeEEbnw8XDS33kgUXVGo3a', '09695203599', 'purok orchids brgy bagonbon san carlos city neg occ', 'johnny mar ', 'N\\A', 'maribeth', 'teacher', 'N\\A', 'N\\A', 'N\\A', 1, 1, 'brgy bagonbon san carlos city neg occ', 'bagonbon elementary school', '2017\\2018', 'julio ledesma national high school', '2023\\2024', 'colegio de santa rita de san carlos, inc', 'ongoing', '', '', '', '', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-19 02:51:35', '2025-08-20 08:53:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(250, '2006051201', 'Delima', 'Luigi', 'Luneza', 'Male', '2006-05-12', 19, 'san carlos city', 'Single', 'filipino', 'catholic', 'luigidelima@csr-scc.edu.ph', '$2y$10$O1JXeks31h3hzI/csgA7se7xof7Z4uFUJNJOgo944TSJcT8XLfL0W', '09851623000', 'BRGY.PALAMPAS', 'Abraham Delima', 'Driver', 'Lorna Delima', 'HouseWife', 'LORNA DELIMA', 'BRGY.PALAMPAS', 'N/A', 1, 1, 'BRGY.PALAMPAS', 'Tandang sora elementary school', '2017-2018', 'julio ledesma national high school', '2023-2024', 'colegio de sta rita san carlos,INC', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 03:00:53', '2025-08-19 03:00:53', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(251, '2005011601', 'Jabaybay', 'Toni Rey ', 'Cabag', 'Male', '2005-01-16', 20, 'Cebu City ', 'Single', 'Filipino', 'Roman Catholic', 'Tonireyyykun@gmail.com', '$2y$10$cUDDKF1UhuS59aWXJ9yRCO.sQVey9uDkocadYtZQeElUWuo0YK3Li', '09474600636', 'calatrava, brgy suba', 'Junry Q. Jababay', 'Teacher', 'Marilou C. Jabaybay', 'Teachear', 'NA', 'NA', 'NA', 1, 1, 'Calatrava, Brgy Suba', 'Cebu City Central School', '2015-2016', 'Calatrava National High School', '2021-2022', 'Colegio De Santa Rita De San Carlos INC.', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 03:44:35', '2025-08-19 03:44:35', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(252, '2001083001', 'Canoy', 'Cris Arro', 'Sumilhig', 'Male', '2001-08-30', 23, 'San Carlos City. Negros', 'Single', 'Filipino', 'Roman Catholic ', 'canoycris10@gmail.com', '$2y$10$vQ0cORgyPEs9C6QNx8TaXeAeDasZnE4ZHYEue2UsrmniRUiMvmZbW', '09850529059', 'nangka st, san julio subd, sancarloscity,negocc\r\n', 'Arleen Romulo Canoy', 'unemployed ', 'cristita sumilhig ', 'unemployed ', 'maria elissa canoy', 'nangka st san julio ', 'na', 1, 1, 'nangka st san julio', 'Daisy\'s abc - rmes', '2013', 'colegio de sto tomas -san beda college - far eastern u niversity - jlnhs', '2014-2023', 'colegio de sta rita', '2024-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:37:14', '2025-08-19 04:37:14', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(253, '2006052801', 'Flores', 'Clint', 'Manayon ', 'Male', '2006-05-28', 19, 'San Carlos City Negros Occidental ', 'Single', 'Filipino ', 'Roman Catholic ', 'klentflores28@gmail.com', '$2y$10$OG6yivh8SP6KILj.miMDOewBr1BImq/gboa3hOVdOTxZE.M5G2uwW', '09670342446 ', 'South Villa 3 First Street', 'N/A', 'N/A', 'Mitchie lyn M. Flores', 'Government Employee', 'Mitchie lyn M. Flores', 'South Villa 3 First Street ', 'Mercy Jade Manayon Paper ', 1, 1, 'South Villa 3 First Street ', 'Florentina Ledesma Elementary School ', '2018', 'Julio Ledesma National High School ', '2024', 'Colegio de Sta Rita ', '', 'Colegio de Sta Rita ', '2025', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:42:32', '2025-08-19 04:42:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(254, '2000091501', 'Pañares', 'Ranie', 'Bañares', 'Male', '2000-09-15', 24, 'Cebu City', 'Single', 'Filipino', 'Roman Catholic', 'rpanares@csr-scc.edu.ph', '$2y$10$Jppa9gcxNcVIv7suFm0XZupKk5BwiZRJXG4PGReUewjnAMUlYMHi2', '09154991876', 'Zone 3, Brgy. Patun-An, Calatrava', 'Ranie Pañares', 'NA', 'Cheryl Pañares', 'Housewife', 'Cheryl Pañares ', 'Zone 3, Brgy. Patun-An, Calatrava', 'Louella Bañares', 1, 1, 'NA', 'Calatarava 2 Central School', '2012 - 2013', 'Colegio De Sto. Tomas - Recoletos', '2017 - 2018', 'Colegio De St. Rita San Carlos, Inc.', '2023 - Present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:51:31', '2025-08-19 04:51:31', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(255, '2005061401', 'Arañez', 'Anthony', 'Abao', 'Male', '2005-06-14', 20, 'Quezon City Manila', 'Single', 'Filipino ', 'Catholic ', 'anthonyaranez@csr-scc.edu.ph', '$2y$10$Wa2EMy6DeeSFINLqLeWRHOh4ToA.U21GbS53IJ0VBOKyrMbDk3DsS', '09668934611', 'So.Lawis BRGY buluangan SCC. NEG. OCC.', 'Alexander ', 'Nkne', 'Anelor', 'None', 'Alier', 'Margarita extension ', 'Hannah', 1, 1, 'So.Lawis BRGY buluangan SCC. NEG. OCC.', 'Katingal-an Elementary school ', '2015-2016', 'Don Carlos Ledesma National Highschool ', '2021-2022', 'Colegio de Sta rita tubucco ', '2024-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:54:10', '2025-08-19 04:54:10', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(256, '2006052502', 'Flores', 'Nicole Anne', 'Escosar', 'Female', '2006-05-25', 19, 'San Carlos City ', 'Single', 'filipino', 'catholic', 'flores24@csr-scc.edu.ph', '$2y$10$gYIfJq7hJyD9rmF0c7RvE.CWmy32ape6Afu77x0tXqmJOnrtQfWxK', '09555589150', 'St. Vincent 7th street subd Brgy1 San Carlos City Neg. Occ.\r\n', 'Rozano R. Flores', 'N/a', 'Analiza E. Flores', 'LGU', 'Ma. Filomena R.Flores', 'St. vincent 7th street subd San Carlos City Negros Occidental ', 'None', 1, 1, 'Brgy1 St Vincent 7th street subd San Carlos city Neg Occ', 'Ramon Magsaysay Elementary School', '2016-2016', 'Colegio De sto. tomas recoletos', '2022-2023', 'Celegio de santa rita', '2024- present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:57:14', '2025-08-19 04:57:14', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(257, '2006101301', 'magbanua', 'paul', 'matia-ong', 'Male', '2006-10-13', 18, 'San carlos city negros occidental', 'Single', 'philippines', 'catholic', 'pmagbanua@csr-scc.edu.ph', '$2y$10$JAg4rSdnmnsAPfk4AimIbObmKTFUHF7ybBbA4aCy4CwmCTQXOgtNC', '09954956424', 'urban phase 4block 10-A', 'NA', 'NA', 'jenie rose magbanua', 'company', 'jenet magbanaua', 'urban phase 4 block 10-A', 'none', 1, 1, 'urban phase 4 block 10-A', 'tandang sora ', ' 2016-2017', 'Cstr', '2022-2023', 'csr', '2023-2024', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:57:18', '2025-08-19 04:57:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(258, '2006051701', 'ybañez', 'zach wesley', 'troberos', 'Male', '2006-05-17', 19, 'bayog canlaon city', 'Single', 'philippino ', 'Catholic ', 'zybanez@csr-scc.edu.ph', '$2y$10$vOipTq/Sn9qSzMTOf5nTF.RecPMUjOOKrMYqvYzPGj88UPxVkOQei', '09278118436', 'canlaon city', 'warlito ybañez ', 'farmer ', 'jocel ybañez', 'farmer', 'jocel ybañez ', 'bayog canlaon city ', 'warlito ybañez', 1, 1, 'bayog canlaon city ', 'macario Española memorial school ', '2018', 'jose B. cardinas memorial high school ', '2024', 'colegio de santa rita', '2023-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 04:59:14', '2025-08-19 04:59:14', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(259, '2006111601', 'Samillano ', 'Marie Edjelie ', 'Espares ', 'Female', '2006-11-16', 18, 'Antique city', 'Single', 'Filipino ', 'Catholic ', 'msamillano@csr-scc.edu.ph', '$2y$10$KJX3ApN0Fpkuq643AV9QKOzTZS6Vwvy3YiBGCjvUipbUJn.NOHXXy', '09677347608', 'Brgy.macapso vallehermoso neg,or', 'Judel e Samillano ', 'Construction worker', 'Edmarie e Samillano ', 'Teacher ', 'Edmarie e Samillano ', 'Macapso vallehermoso neg or', 'None', 1, 1, 'Brgy macapso vallehermoso neg or ', 'Macapso elementary school ', '2016-2017', 'St Francis highschool inc', '2022-2023', 'Colegio de Santa rita', '2024-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:07:31', '2025-08-19 05:07:31', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(260, '2004111501', 'Talandron ', 'Jay', 'Gonzales ', 'Male', '2004-11-15', 20, 'Purok. Rambutan Brgy. Bunga, Don Salvador Benedicto Negros Occidental ', 'Single', 'Filipino ', 'Jesus Vision ', 'talandron@csr-scc.edu.ph', '$2y$10$395UM4J0Olx/Re9Zoa2dv.abOCBmSvw9IN1R1eulr0zlBXuL2p5Q2', '09070106283', 'Purok. Rambutan Brgy. Bunga, Don Salvador Benedicto Negros Occidental ', 'Lito B. Talandron ', 'Farmers ', 'Arlene G. Talandron ', 'Housewife ', 'John Mark Larida ', 'Purok. Citrus Brgy. Bunga, Don Salvador Benedicto Negros Occidental ', 'N/A', 1, 1, 'Purok. Rambutan Brgy. Bunga, Don Salvador Benedicto Negros Occidental ', 'Benejiwan Elementary School ', '2017-2018', 'Julio Ledesma National High School ', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:09:21', '2025-08-19 05:09:21', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(261, '2006090301', 'Peñalosa', 'Frenz Ezyl ', 'Arnega', 'Male', '2006-09-03', 18, 'San Carlos City hospital ', 'Single', 'Filipinos', 'Roman Catholic ', 'penalosa@csr-scc.edu.ph', '$2y$10$9fJv1UzFbnrxKSZQIbepp.b//54jeo8wLvvJkaIjQ2Ac5t7k11Fd2', '09562293698', 'So.Lawis brgy Buluangan San Carlos city', 'Efren N. Peñalosa', 'Business ', 'Hensil M. Peñalosa', 'Business ', 'Efren Peñalosa', 'So.Lawis brgy Buluangan ', 'Efren Peñalosa', 1, 1, 'So.Lawis Brgy, Buluangan San Carlos City ', 'Katingal-an elementary school ', '2016-2017', 'Don Carlos ledesma national high school ', '2022-2023', 'Colegio de Santa rita', '2024', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:14:45', '2025-08-19 05:14:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(262, '2004022801', 'Donquilab', 'Kenneth Jay', 'NA', 'Male', '2004-02-28', 21, 'Vallehermoso Negros Oriental ', 'Single', 'Philippines ', 'roman catholic', 'donquilab@csr-scc.edu.ph', '$2y$10$.p8sCEvg6/.h77KVMEn/Zua4xZEqep/VqK9V7tFWrJcDbrRjbUuRG', '09169769080', 'aguinaldo street ', 'Jr lumayga', 'NORECO', 'Gina donquilab ', 'housewife', 'Gina donquilab ', 'Vallehermoso my ', 'NA', 1, 1, 'poblacion Vallehermoso ', 'Vallehermoso elementary school', '2012', 'VNHS ', '2023', 'ColegionDe St. Rita San Carlos', '2023 to Present', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, '', '2025-08-19 05:16:04', '2025-12-21 05:20:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(263, '2004010501', 'Baricuatro', 'Jose Bernard ', 'A', 'Male', '2004-01-05', 21, 'Bawines', 'Single', 'Filipino', 'Catholic', 'baricuatro@scc-csr.edu.ph', '$2y$10$Z8gS8Cb3TA2vaTxtMZiBae52TUWctEUNYbo7ZIer5./a0bCBcVBqO', '09657915452', 'Brgy.Bagawines Vallehermoso Negros Oriental', 'Bernardo Baricuatro', 'Fisherman', 'Florenda', 'Housewife', 'Florenda', 'Bagawines', 'Bernardo', 1, 1, 'Brgy Bagawines Vallehermoso Negros Oriental', 'Don vicente lopes memorial elementary school', '2009-2010', 'Highschool', '2020-2021', 'Collegio de Sana rita inc', '2023-2024', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:19:58', '2025-08-19 05:19:58', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(264, '2006042001', 'Calago', 'Jude April', 'Malusay', 'Male', '2006-04-20', 19, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'jcalago@csr-scc.edu.ph', '$2y$10$b2FJyxx5fB4JhiocO1UeVe/2jm3gTwgibFFY7ostIHi2gipoN4Uye', '09566827542', 'Macapso,Vallehermoso, Negros Oriental', 'Lemuel Calago', 'Seaman', 'Lilibeth Calago', 'House Wife', 'N/A', 'N/A', 'N/A', 1, 1, 'Macapso,Vallehermoso', 'Macapso Elementary School', '2016-2017', 'Saint Francis High School', '2023-2024', 'Colegio De Santa Rita De San Carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:23:02', '2025-08-19 05:23:02', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(265, '2005080101', 'Dolosa', 'Angelo', 'Roma ', 'Male', '2005-08-01', 20, 'San Carlos city hospital ', 'Single', 'Filipino ', 'Roman Catholic ', 'adolosa@csr-scc.edu.ph', '$2y$10$lH2.o6lZBdxunHxC8Ng/suzPc6icNyGU/LSGex9sxcHYFELxyeAgG', '09953100882', 'Poblacion vallehermoso negros oriental\r\n', 'None', 'None ', 'Maryjane R Dolosa', 'Lawyers assistant ', 'Vic Dolosa', 'Poblacion vallehermoso negros oriental ', 'None', 1, 1, 'Poblacion vallehermoso negros oriental ', 'Tolotolo elementary school ', '2016', 'St Francis high school ', '2023', 'Colegio De sta rita', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:24:16', '2025-08-19 05:24:16', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(266, '2006092201', 'Quijote', 'Louis', 'Villarino', 'Male', '2006-09-22', 18, 'San Carlos City Negros Occidental', 'Single', 'filipino', 'roman catholic', 'qlouisandreano@gmail.com', '$2y$10$aSabV3jXMFMdlY5vzFWsUe/c4ZbacvxtXfsronb7ClOJ9iD8bzre.', '09810776496', 'brgy guadalupe', 'George Quijote', 'Goverment Employee', 'Gerelyn Quijote', 'House wife', 'na', 'na', 'na', 1, 1, 'Brgy Guadalupe\r\n', 'School Of The Future', '2017', 'Julio Ledesma National Highschool', '2024', 'Colleio De Sta Rita De San Carlos', '2025', 'na', 'na', 'na', 'na', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:41:41', '2025-08-19 05:41:41', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(267, '2005090901', 'Palaubsanon', 'Justin', 'Hillado', 'Male', '2005-09-09', 19, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'justinpalaubsanon01@gmail.com', '$2y$10$mNN2wqMeD2uTnbyjISnWju4QXuiTixmROCoZkxSUcCztPxB/GBfqm', '09858668426', 'Brgy. 1', 'Raul Palaubsanon', 'Father', 'Mary Palaubsanon', 'Mother', 'Mary Palaubsanon', 'Brgy. 1', 'Maryjoy Palaubsanon', 1, 1, 'Brgy. 1', 'Florentina Ledesma Elementary School', 'Gr. 1 to gr. 6', 'Julio Ledesma Natio', 'Gr. 7 gr. 12', 'Colegio de Santa Rita de San Carlos Inc.', '1st Year', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:42:17', '2025-08-19 05:42:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(268, '2004060401', 'Libaton', 'Christian', 'Togalon', 'Male', '2004-06-04', 21, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'christianlibaton@csr-scc.edu.ph', '$2y$10$/QVecnbZoxLR8aNAYxH6DeC2t8t74oewagUgZW..7XiaJ0lO6WnEC', '09153570537', 'Brgy.Bayog Canlaon City Negros Oriental', 'Lorenzo S. Libaton', 'Business Man', 'Lorena T. Libaton', 'Business Woman', 'Lorena T. Libaton', 'Brgy. Bayog Canlaon City Negros Oriental', 'Lorenzo T. Libaton', 1, 1, 'Brgy. Bayog Canlaon City Negros Oriental', 'Bayog Elementary School', '2012', 'Jose B. Cardenas Memorial Highscool', '2016', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:42:18', '2025-08-19 05:42:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(269, '2007031101', 'Bayalas', 'Althea Angel', 'J. ', 'Female', '2007-03-11', 18, 'San Carlos City', 'Single', 'Filipino ', 'Bible Baptist ', 'bayalasalthea7@gmail.com', '$2y$10$Qs5Qb48C0947x2td2RfmLeq.wGi87RO42tb2bdJEhaYhGi7cq1tQO', '09931953988', 'Teachers Village, Gold Street', 'Antomer Y. Bayalas', 'N/a', 'Elgin T. Jacolbe', 'N/a', 'N/a', 'N/a', 'N/a', 1, 1, 'Teachers Village, Gold Street', 'Calatrava 1 Central School ', '2015', 'Calatrava Junior High School ', '2020', 'Colegio de Santa Rita de San Carlos Inc.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:43:52', '2026-01-05 22:54:20', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(270, '2006121402', 'Illusorio', 'Herman ll', 'Melliza', 'Male', '2006-12-14', 18, 'Talisay Bacolod City Negros Occidental ', 'Single', 'Filipino', 'Catholic', 'herman@csr-scc.edu.ph', '$2y$10$zXveIlqVXmEOUgKY4B4qD.GSXs0y.YpKUm8eDb9WVFC3l8kIDWrKK', '09561379015', 'Purok Gemelina Brgy 1 San Carlos City Negros Occidental ', 'Mark Arven M. Illusorio ', 'Government employee ', 'Karen M. Illusorio ', 'House wife', 'Jesusa M. Illusorio ', 'Purok Gemelina Brgy 1 San Carlos City ', 'Guardian', 1, 1, 'Purok Gemelina Brgy 1 San Carlos City ', 'Florentina Ledesma Elementary school ', '2018-2019', 'Colegio de sto Tomas elementary school ', '2023-2024', 'Colegio de sta Rita ', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:44:22', '2025-08-19 05:44:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(271, '2004020101', 'Hernandez ', 'John Ricardo ', 'Esconde ', 'Male', '2004-02-01', 21, 'San carlos city negros Occidental ', 'Single', 'Filipino', 'Catholic ', 'jhernandez@csr-scc.edu.ph', '$2y$10$2ELj1Do1vKfukr45dM.it.bCQvjJjrpxKg1FB0cMypgPY8nmfkq9y', '639163080206', 'Poblacion Vallehermoso Negros Oriental ', 'Carlos Hernandez ', 'Former driver ', 'Rowena Hernandez ', 'House wife ', 'Rodina Esconde ', 'San carlos city negros Occidental ', 'Rodelyn Penonal ', 1, 1, 'San carlos city negros Occidental ', 'Vallehermoso central elementary school ', '2010-2016', 'Vallehermoso National High school ', '2016-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:44:53', '2025-08-19 05:44:53', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(272, '2006111201', 'Golez', 'Olyxis Ace ', 'Dela Penña', 'Male', '2006-11-12', 18, 'Calatrava Neg.Occ', 'Single', 'Filipino', 'Roman Chatolic', 'golezace123@gmail.com', '$2y$10$m4C32Aopr6q02kDo6YJuseok/wPoIUA6e3Q6HfeD9pMliWD96yZjm', '09054618544', 'Brgy. Laga-an, Calatrava Neg.Occ', 'Orlando T. Golez', 'Driver', 'Wentesa D. Golez', 'House Wife', 'Wentesa D. Golez', 'Brgy. Laga-an Calatrava', 'Orlando T. Golez', 1, 1, 'Brgy. Laga-an Calatrava Neg.Occ', 'Harbort Elementary School', '2018-2019', 'Calatrava Senior High School ', '2024-2025', 'Collegio De Santa Rita De San Carlos', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:45:07', '2025-08-19 05:45:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(273, '2001080301', 'Cernada', 'Arvin', 'Basalan', 'Male', '2001-08-03', 24, 'Brgy,Prosperidad', 'Single', 'Filipino ', 'Baptist', 'cernada@csr-scc.edu.ph', '$2y$10$ZEuCLvFyZgQ0/WmbQOWCh.HclWvl.B.drRDc3cKYnwlye1C/hntum', '09187369630', 'Brgy,Prosperidad San Carlos City', 'Rene cernada', 'Farmer', 'Maribeth Cernada', 'House keep', 'Rene Cernada', 'Brgy,Prosperidad', 'Maribeth Cernada', 1, 1, 'Brgy,Prosperidad SCC', 'Cod cod ', '2007-2012', 'OLPMS', '2016-2021', 'Colegio de sta Rita', '2024-2026', 'Mirai empower inc', '2022', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:45:59', '2025-08-19 05:45:59', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(274, '2003121801', 'Araujo', 'Adriane', 'Rigor', 'Male', '2003-12-18', 21, 'San Carlos City Neg.Occ.', 'Single', 'Pilipino', 'Baptist', 'ranch11tanix11@gmail.com', '$2y$10$kRcVQmAkge1IQhMpSycIhOgJLMgeoZY5QVXd.EzWJf0/khplr2Yzi', '09055412702', 'Greenville highway ', 'Jerome', 'NA', 'Miray ', 'NA', 'Miray Araujo', 'Greenville highway ', 'Na cess', 1, 1, 'Greenville highway ', 'Tandang sora elementary school', 'Gr. 1 to gr. 6', 'Julio ledesma national highschool ', 'Gr.7 to Gr.12', 'Colegio de Santa Rita ', '1st year ', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:46:30', '2025-08-19 05:46:30', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(275, '2006091402', 'Misperos', 'Aslie ', 'Dakop', 'Female', '2006-09-14', 18, 'Iligan City, Lanao del Norte', 'Single', 'Filipino', 'Roman Catholic', 'asliedmisperos@gmail.com', '$2y$10$dsaIwAdMScXsUkymXYnkCOr30FACEbV8fy1IiWkH9CK50.FzyrsHW', '09517182825', 'Margarita Village 1st Street, San Carlos City, Negros Occidental.\r\n', 'Tirso A. Misperos', 'Manager of Company', 'Elisa D. Misperos', 'Secrectary of Company', 'Elisa D. Misperos', 'Margarita Village 1st Street, San Carlos City, Negros Occidental.', 'None', 1, 1, 'Margarita Village 1st Streeet, San Carlos City, Negros Occidental.', 'Ramon Magsaysay Elementary School', '2014', 'Colegio de Sto.Tomas-Recoletos', '2021', 'Colegio de Sta.Rita de San Carlos Inc.', '', 'none', 'none', 'none', 'none', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:19', '2025-08-19 05:48:19', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(276, '2005070501', 'Talledo', 'Cayen Mark Roland', 'E.', 'Male', '2005-07-05', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic ', 'cayen2005@gmail.com', '$2y$10$HSNtfk/WLZl2jGqLHCID6OpAiNVNc1hZ5AzUjnQ8iFZzRJ/BcIPqu', '09690421165', 'Dos Hermanos St. Brgy3, San Carlos City, Negros Occidental ', 'Mark Anthony Talledo', 'N/A', 'Bernadith E. Talledo', 'Pharmacist ', 'Bernadith E. Talledo', 'Dos Hermanos St. Brgy3', 'N/A', 1, 1, 'Dos Hermanos St. Brgy3, San Carlos City, Negros Occidental ', 'Ramon Magsaysay Elementary School', '2017-2018', 'Julio Ledesma national highschool', '2022-2024', 'Colegio de Santa Rita ', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:29', '2025-08-19 05:48:29', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(277, '2007032501', 'Cabalog', 'Baby Jane', 'Mission', 'Female', '2007-03-25', 18, 'Cebu City', 'Single', 'Filipino', 'Catholic', 'babyjanecabalog2@gmail.com', '$2y$10$vaxcub4ju/yqKxG4qQWDLe/LOqYr2K3hSMhAk3O/ZWsMnh/b51.wS', '09953943973', 'Brgy. Codcod, San Carlos, Negros Occidental', 'Aaron Carl M. Cabalog', 'Farmer', 'Brenda M. Cabalog', 'OFW', 'Mary Alma M. Ybanez', 'Brgy. Codcod', 'N/A', 1, 1, 'Brgy. Codcod, San Carlos, Negros Occ.', ' Tabunok Elementary School', '2012-2018', 'Quezon National High School', '2022-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:49', '2025-08-19 05:48:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(278, '2004111401', 'Obenieta', 'Reaven Christian', 'Tribunalo', 'Male', '2004-11-14', 20, 'San Carlos City Public Hospital', 'Single', 'Filipino', 'Roman Catholic', 'robenieta@csr-scc.edu.ph', '$2y$10$cdh8rjG4x27vue3YQtVSGOO9ntCUJIyfc2tOdXBBwsw0v40F0bsMC', '09166246929', 'H. C. Rigor St. Brgy III, San Carlos City, Negros Occidental', 'Rey Y. Obenieta', 'Education Teacher', 'Eva T. Obenieta', 'None', 'Eva T. Obenieta', 'H. C. Rigor St. Brgy III, San Carlos City, Negros Occidental', 'None', 1, 1, 'H. C. Rigor St. Brgy III, San Carlos City, Negros Occidental', 'Ramon Magsaysay Elementary School', '2015', 'Julio Ledesma National High School', '2022', 'Colegio de Santa Rita de San Carlos, Inc.', '2025', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:54', '2025-08-19 05:48:54', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(279, '2007092401', 'Abondiente ', 'Kae Cyrine ', 'Villagracia ', 'Female', '2007-09-24', 17, 'Calatrava ', 'Single', 'Filipino', 'Roman Catholic ', 'kaecyrine@csr-scc.edu.ph', '$2y$10$1jxfm/jmYgse1nCcrpObVu4GdWGtgNIcNWJ7rOPeSa48lLPflqHMW', '09129754241', 'Brgy. Bagacay calatrava negros Occidental ', 'Jondel Abondiente ', 'Ofw', 'Emily Abondiente ', 'Housewife ', 'Emily Abondiente ', 'Brgy, bagacay calatrava ', 'Mother', 1, 1, 'Brgy, bagacay calatrava Negros Occidental ', 'Buenavista ', '2018', 'Calatrava National High school ', '2021', 'Collegio de Santa Rita San Carlos boarding inc.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:54', '2025-08-19 05:48:54', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(280, '2007062801', 'Labandero', 'Ivan Kharl', 'Mission', 'Male', '2007-06-28', 18, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'ilabandero@csr-scc.edu.ph', '$2y$10$/XJ483I0ohORpeIQnY.gmuO9GwuDqbbMDNvq/fg6igsdMqRt069Ty', '09535294764', 'Fatima Village, Barangay Rizal, San Carlos Cit, Negros Occidental', 'Richard B. Labandero', 'Welder', 'Lydia B. Labandero', 'Teachers', 'N\\A', 'N\\A', 'N\\A', 1, 1, 'Fatima Village, Barangay Rizal, San Carlos City\r\n', 'Burlad Elementary School', '2019', 'Tañon College', '2023', 'Colegio De Santa Rita  ', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:48:58', '2025-08-19 05:48:58', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(281, '2005111001', 'Caballero', 'Ruffa Mae', 'Marcellana', 'Female', '2005-11-10', 19, 'San Carlos City', 'Single', 'Filipino', 'Roman Chatholic', 'rcaballero@csr-scc.ede.ph', '$2y$10$MzshBYJvAGyVzqngh2nU0OsA4YNNq9h646up16Kv6vLTLpbhWUr9i', '09487944647', 'Sitio Panoolan brgy. Guadalupe sancarlos city', 'Wilfredo Caballero', 'none', 'Janeth Marcellana', 'none', 'Marilou Descatiar', 'PANOOLAN', 'NONE', 1, 1, 'Panoolan', 'Panoolan Elementary School', '2018-2332', 'tanon college', '2019-2022', 'csr', '2025-2026', 'none', 'none', 'none', 'none', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:49:15', '2025-08-19 05:49:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(282, '2007101202', 'Villajos', 'Jaymar', 'N/A', 'Male', '2007-10-12', 17, 'Cabanglay Vallehermoso Negros Oriental ', 'Single', 'Filipino ', 'Roman Catholic ', 'jaymar@csr-scc.edu.ph', '$2y$10$aNKW26EiP8SWp5vWbw2mm.eAZkNr6ZGYDeWLZCnQjs3L/IiO6cM6K', '09555174493', 'Brgy. Bagawines Vallehermoso Negros Oriental ', 'Arnold S. Sotomayor ', 'Fisher man/Fish vendor ', 'Marilyn Y. Villajos', 'None', 'N/A', 'N/A', 'N/A', 1, 1, 'Brgy. Bagawines Vallehermoso Negros Oriental ', 'Don Vicente Lopez Senior Memorial Elementary School ', '2019', 'Vallehermoso National High School ', '2023', 'Colegio de Santa Rita de San Carlos, Inc.', '2025', 'NONE', 'NONE', 'NONE', 'NONE', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:49:22', '2025-08-19 05:49:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(283, '2006090601', 'Salva', 'Janrhen Mac ', 'Siason', 'Female', '2006-09-06', 18, 'San Carlos City, Negros Occidental ', 'Single', 'Filipino ', 'Baptist ', 'mackyysalva@gmail.com', '$2y$10$IgBgVY/P6ArGiaNL3oUN5.usaSVHDiqh1/3lsXBJ74k4gEi9h22Cm', '09959122612', 'Fatima Village Phase 1 Blk 24', 'Renato Dingcong Salva', 'None', 'Janice Gabisan Siason', 'OFW', 'Renato Dingcong Salva ', 'Fatima Village Phase 1 ', 'Janice Siason Salva', 1, 1, 'Fatima Village Phase 1 Blk 24 ', 'Tandang Sora Elementary School ', '2017-2018', 'Julio Ledesma National Highschool ', '2024-2025', 'Colegio de Santa Rita de San Carlos Inc. ', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:49:28', '2025-08-19 05:49:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(284, '2006060401', 'Flores', 'Hanna Shane ', 'Tolo', 'Female', '2006-06-04', 19, 'Santa Rosa City of Laguna ,Balibago Nia Road 1', 'Single', 'Filipino ', 'Roman Catholic ', 'hflores@csr-scc.edu.ph', '$2y$10$R3hGKTlBCdtmFKldT97ReeElAnBHAM5UYbiqzcCnmFsqYnqbwKG2C', '09934863012', 'SITIO PARAISO, BARANGAY REFUGIO,CALATRAVA,NEGROS OCCIDENTAL ', 'DONATO FLORES JR.', 'Carpenter ', 'JOY T. FLORES', 'OFW', 'Nancy Flores', 'Sitio paraiso, barangay Refugio, CALATRAVA ', 'None', 1, 1, 'SITIO PARAISO BARANGAY REFUGIO CALATRAVA ', 'Menchaca Elementary school ', '2017- 2018', 'Julio Ledesma National High school ', '2024-2025', 'Collegio De Santa Rita', '2025-2026', 'None', 'None', 'None', 'None', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:49:36', '2025-08-19 05:49:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(285, '2007031102', 'Marzon', 'Rhazzel Jean', 'Villacampa', 'Female', '2007-03-11', 18, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'rmarzon@csr-scc.edu.ph', '$2y$10$oCz45ydOaXm5PQvLE32LJ.6GeLFHPWjKW.Xs3fF6RnKzxVgmNrwRe', '09667358550', 'So. Pano-olan Brgy. Guadalupe San Carlos City', 'Crispin R. Marzon Jr.', 'Line Man', 'Cresel V. Marzon', 'OFW', 'Crispin R. Marzon', 'So. Pano-olan Brgy. Guadalupe San Carlos City', 'N/A', 1, 1, 'So. Pano-olan Brgy. Guadalupe San Carlos City', 'Pano-olan Elementary School', '2018-2019', 'Colegio De Santa Rita de San Carlos Inc.', '2024-2025', 'Colegio De Santa Rita de San Carlos Inc.', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:49:52', '2025-08-19 05:49:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(286, '2006081201', 'Ygot', 'Nick john', 'Peñalosa', 'Male', '2006-08-12', 19, 'Masbate city', 'Single', 'Filipino ', 'Roman catholic ', 'nickygot@csr-scc.edu.ph', '$2y$10$X3EJwUt4UWOeW4QPdWgnr.NgKbjpnBeqlWG9FC593I4CdjYHZ5wAK', '09668321327', 'Cupang compound brgy rizal', 'Lance kerwin M. Ygot', 'N/A', 'Jhona Marie R. Peñalosa', 'Self employed', 'N/A', 'N/A', 'N/A', 1, 1, 'Cupang compound Brgy rizal', 'Jose zurbito senior elementary school', '2016-2017', 'Tañon college Inc', '2024-2025', 'Coliheyo de santa rita de san carlos ', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:50:39', '2025-08-19 05:50:39', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(287, '2004082301', 'ESQUERIDA', 'Randel', 'Caminade', 'Male', '2004-08-23', 20, 'San carlos city negros occidental', 'Single', 'Philippines', 'Chatolic ', 'esqueridarandel088@gmail.com', '$2y$10$gPx4jH2aFTI9Q0iGBcRVLupMVByl.i.agmCj0H7CHu.FUxnkSKyxK', '09670725458', 'Brgyb codcod  san carlos city negros occidental\r\n', 'Efren esquerida caminade ', 'N/A', 'Paulina esquerida caminade ', 'N/A ', 'Paulina esquerida caminade', 'Brgy codcod san carlos city negros occidental', 'N/A', 1, 1, 'Bgry codcod san carlos city  negros occidental ', 'Codcod elemntary school', '2015-2016', 'Quezon national high school ', '2022-2023', 'College de sta reta san carlos inc.', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:51:28', '2025-08-19 05:51:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(288, '2004042601', 'Padilla', 'Ivan Dwight ', 'Quiñones', 'Male', '2004-04-26', 21, 'sancarlos city negros occidental ', 'Single', 'Filipino ', 'Catholic ', 'padillaivandwight26@gmail.com', '$2y$10$e0Q.8Y2o10AMv7sqAPl14..dLwm831XCQIuK4Km3k9lilJNWmG9sW', '09519546269', 'barangay codcod sancarlos city negros occidental ', 'Roderick L padilla', 'security guard ', 'liza Q padilla', 'OFW', 'rosalinda L quiñones', 'barangay codcod ', 'N/A', 1, 1, 'barangay codcod sancarlos city negros occidental ', 'codcod elementary school ', '2013-2019', 'Quezon national highschool ', '2023-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, '', '2025-08-19 05:51:29', '2025-12-21 06:11:37', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(289, '2007071301', 'Timtim', 'Juliana Marie', 'Lumanog', 'Female', '2007-07-13', 18, 'Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'jtimtim@csr-scc.edu.ph', '$2y$10$9AXmOXd6OXP9hfY4Kk5LEO8/xchBG4vd1CgK1r8w95Rky0n90GdRi', '09154947532', 'Bantayanon Homeowners , Calatrava Negros, occidental', 'Julius Timtim', 'N/A', 'Ana Marie Lumanog', 'call center', 'Ana Marie Lumanog', 'Bantayanon Homeowners , Calatrava Negros, occidental', 'N/A', 1, 1, 'Bantayanon Homeowners , Calatrava Negros, occidental', 'Calatrava 1 Central School', '2017-2018', 'Bayambang National High School', '2021-2022', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:51:43', '2025-08-19 05:51:43', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(290, '2005070102', 'Jimenez', 'Jerico', 'Solitario', 'Male', '2005-07-01', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'jericosolitario19@gmail.com', '$2y$10$4gR6z3CJWBtEIdOPBY3VqOHHMvcbIU4/11mQr8BhqMV4YwMz6eXqO', '09924273890', 'Sto. Paraiso Brgy Refugio', 'Jerry B. Jimenez', 'Technician', 'Ma. Elena S. jimenez', 'N\\A', 'Ma. Elena S. Jimenez', 'Sto. Paraiso Brgy Refugio', 'N\\A', 1, 1, 'STO. Paraiso Brgy Refugio ', 'Menchaca Elementary School', '2017/2018', 'Julio Ledesma National High School', '2023/2024', 'Colegio De Santa Rita', '2025/2026', 'N\\A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:51:49', '2025-08-19 05:51:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);
INSERT INTO `students` (`id`, `id_number`, `last_name`, `first_name`, `middle_name`, `gender`, `birth_date`, `age`, `birth_place`, `civil_status`, `nationality`, `religion`, `email`, `password`, `contact_number`, `home_address`, `father_name`, `father_occupation`, `mother_name`, `mother_occupation`, `guardian_name`, `guardian_address`, `other_support`, `is_boarding`, `with_family`, `family_address`, `elem_address`, `elem_year`, `sec_address`, `sec_year`, `college_address`, `college_year`, `voc_address`, `voc_year`, `others_address`, `others_year`, `form138`, `moral_cert`, `birth_cert`, `good_moral`, `others1`, `others2`, `others3`, `notes`, `created_at`, `updated_at`, `is_active`, `profile_photo`, `lrn_no`, `contact_person`, `form137`, `parents_marriage_cert`, `baptism_cert`, `proof_income`, `brown_envelope`, `white_folder`, `id_picture`, `esc_app_form`, `esc_contract`, `esc_cert`, `shsvp_cert`) VALUES
(291, '2006101302', 'Ferolino', 'Mark julian', 'Soltero', 'Male', '2006-10-13', 18, 'St. Lucks ', 'Single', 'Filipino ', 'Roman catholic ', 'mferolino@csr-scc.edu.ph', '$2y$10$sg8SE7shbJ9gWFs5Fn5E3uwTUAgnLeabuRfbgvvyEa0lMcwnjE5ne', '09648998051', 'So. Cabagtasan brgy codcod', 'Johney Ferolino', 'Construction worker', 'Marites soltero', 'OFW', 'Johney ferolino', 'Sitio cabagtasa', 'Marites soltero', 1, 1, 'Sitio cabagtasan brgy codcod', 'Sitio cabagtasan elementary school ', '2013-2019', 'Our Lady of the mountains mission school ', '2018-2023', 'Colegio de santa rita de san carlos', '', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:51:52', '2025-08-19 05:51:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(292, '2006051801', 'Jarina', 'Joshua', 'Cotejo', 'Male', '2006-05-18', 19, 'calatrava', 'Single', 'filipino', 'roman catholic', 'joshuajarina55@gmail.com', '$2y$10$tJjPWeShgSnl9FLdWZGFSeTZM1bo7Yjx.CKK1zqT9XKaaYi6Kk6Gy', '09942840451', 'brgy lemery', 'Jesty Jirna', 'Farmer', 'Merli Jarina', 'BHW', 'na', 'na', 'na', 1, 1, 'Brgy Lemery', 'Rufino Castellano elementary school', '2017', 'Calatrava Senior High School', '2024', 'Colleio De Sta Rita De San Carlos', '2025', 'na', 'na', 'na', 'na', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:52:15', '2025-08-19 05:52:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(293, '2007081401', 'Sante', 'Code Daniel', 'Agraviador', 'Male', '2007-08-14', 18, 'Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'sante@csr-scc.edu.ph', '$2y$10$/UF32os7Mpdmhr6aMuBaT.zjYmUjFJnI0EZsQJWM/.jFbq51rdCpi', '09953448153', 'Brgy. Bantayanon', 'Danny Jr.', 'Lending Company', 'Juanale', 'Managing Restaurant', 'Juanale', 'Brgy.Calatrava', 'Danny Jr.', 1, 1, 'Brgy.Bantayanon', 'CALATRAVA CENTRAL SCHOOL District - I', '2017', 'Calatrava Nation High School', '2021', 'Collegio De Santa rita inc.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:53:03', '2025-08-19 05:53:03', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(294, '2007051201', 'Geronimo', 'Jimbert', 'Loberanes', 'Male', '2007-05-12', 18, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Catholic', 'geronimojmbert@gmail.com', '$2y$10$dY9I67wC//tvUEwWZAamLOhUxwbW29bAj6Z3zhhylSjU0FpirBtwy', '09183236364', 'Brgy,CodCod San carlos city negros Occidental', 'Benjie Abe Geronimo', 'N/A', 'Gina Intrampas Geronimo', 'Factory Worker', 'Lorna Abe Geronimo', 'Brgy, CodCod San Carlos City Negros Occidental ', 'N/A', 1, 1, 'Brgy, CodCod San Carlos City Negros Occidental', 'Codcod Elementary School', '2018', 'Quezon National High School', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:53:11', '2025-08-19 05:53:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(295, '2006123101', 'Cañete', 'Ar-jay', 'Bialen', 'Male', '2006-12-31', 18, 'Calatrava', 'Single', 'Filipino', 'Roman Catholic', 'arjaycanete@csr-scc.edu.ph', '$2y$10$fdVXK9LZxNYT2I39jTMj5.thW/4BdGNhM/VAkx75Btmp5yOr6nG0e', '09533647211', 'Brgy.Suba Calatrava Negros Occidental', 'Rory B. Cañete', 'Tricycle Driver', 'Jonalyn B. Cañete', 'Vendor', 'Jonalyn B. Cañete', 'Brgy.Suba Calatrava Negros Occidental', 'Rory B. Cañete', 1, 1, 'Brgy.Suba Calatrava Negros Occidental', 'Calatrava ll Central School', '2018', 'Caltrava National High School', '2021', 'Collegio de Santa Rita INC.', '2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:54:10', '2025-08-19 05:54:10', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(296, '2007011802', 'Bulalhog', 'Jeraldine', 'Tayuco', 'Female', '2007-01-18', 18, 'Poblacion, Toledo City, Cebu', 'Single', 'Filipino', 'Roman Catholic', 'bulalhog@csr-scc.edu.ph', '$2y$10$oh8Wjxkwn.689xOD654JVOMfAvTwj2OAKKd.vmve6b8QMchZM2P.i', '09705715727', 'Ylagan St. Brgy. 5, San Carlos City Negros Occidental', 'Alquin B. Bulalhog', 'Seaman ', 'Jenelyn L. Tayuco', 'Homebase Baker', 'Jenelyn L. Tayuco', 'Ylagan St. Brgy. 5', 'Alquin B. Bulalhog', 1, 1, 'Ylagan St. Brgy.5, San Carlos City Negros Occidental', 'Tandang Sora Elementary School', '2018-2019', 'Tañon College Inc.', '2024-2025', 'Colegio de Sta. Rita de San Carlos, Inc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:55:07', '2025-08-19 05:55:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(297, '2006092202', 'Morte', 'Syvel Keith', 'Salillas', 'Male', '2006-09-22', 18, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'Morte@csr-scc.edu.ph', '$2y$10$JS0kCK0mjSUlezjuhuW2fe1ZFM0x4k56t3nQJ4lmGqhbYNwNeKFI6', '9956 228 7302', 'Oval exodus avenue brgy, Panubigan Canlaon City Negros Oriental', 'Montano E. Morte', 'Business', 'Sheryl S. Morte', 'Sari sari store', 'Shevyl John S. Morte', 'Oval exodus avenue brgy, Panubigan Canlaon City Negros Oriental', 'NA', 1, 1, 'Oval exodus avenue brgy, Panubigan Canlaon City Negros Oriental', 'Macario Española Memorial School', '2018-2019', 'Jose B. Cardenas Memorial High School', '2022-2023', 'NA', 'NA', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:55:07', '2025-08-19 05:55:07', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(299, '2025112401', 'Bustillo', 'Yoshinori', 'Diacamos', 'Male', '2025-11-24', 0, 'San Carlos', 'Single', 'Pilipino', 'Catholic', 'yoshediacamos@gmail.com', '$2y$10$0NGMojchLZrfIkX0.1ZJ/eh46mU3iPUwfKuhG6bPXDvMXsjMZ2RbC', '09707222519', 'St: Tal ot bry Guadalupe', 'Michael Bustillo', 'Driver', 'Gina Lyn Bustillo', 'Ofw', 'Michael Bustillo', 'St tal ot bry Guadalupe', 'Gina Lyn Bustillo', 1, 1, 'St tal ot bry Guadalupe\r\n', 'Tal ot elementary school', '2016', 'Bulwangan', '2023 2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:57:18', '2025-08-19 05:57:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(300, '2006100201', 'Tundag', 'Ian Probe', ' Encoy', 'Male', '2006-10-02', 18, 'san carlos city negros Occdental', 'Single', 'Filipino', 'Roman Catholic', 'itundag@scr-scc.edu.ph', '$2y$10$KNCCR7P3IsLHy0jrNMIdY.2C9QaB5p8HnKpRqQfZwe7HSC9Vd/cvK', '09153235503', 'Teachers village', 'Romeo Tundag', 'N/A', 'Genalyn Tundag', 'flower vendors', 'Rogen Tundag', ' Teachers village', 'N/A', 1, 1, 'Teachers village', ' congressman Vicente gustilo senior memorial school', ' 2017-2018', 'Tañon College', ' 2024-2025', 'Colegio de santa rita', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 05:57:40', '2025-08-19 05:57:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(301, '2004021301', 'Tabuada', 'Carlos Jr', 'Flores', 'Male', '2004-02-13', 21, 'Phase 4 10-A Fatima Village, Rizal, San Carlos City ', 'Single', 'PHL', '6', 'tabuada@csr-scc.edu.ph', '$2y$10$g0FlKWdx5imqfufw4/OYr.uucVIQId7EmnpOfTdDVzKiurHHtn3AC', '09066594647', 'Phase 4 10-A', 'Carlos', 'pensioner', 'Cecilia', 'House wife ', 'Cecilia', 'Phase 4 10-A Fatima Village, Rizal, San Carlos City', 'Siblings', 1, 1, 'Phase 4 10-A Fatima Village, Rizal, San Carlos City', 'Greenville Elementary School', '2016-2017', 'Julio Ledesma National School', '2023-2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 06:07:13', '2025-08-19 06:07:13', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(302, '2004081001', 'Ferolino ', 'Anthony ', 'V', 'Male', '2004-08-10', 21, 'Toboso', 'Single', 'Filipino ', 'Seventh day Adventist ', 'anthony.ferolino.712@gmail.com', '$2y$10$PzQpl2SLgaA4e3iyejQloefOjC2KBlkaVFe/RCOQzFaM0upgf.IeC', '09973366236', 'Brgy codcod San Carlos city ', 'Narciso ferolino ', 'Construction ', 'Elsie ferolino ', 'Housewife ', 'N/A', 'N/A', 'N/A', 1, 1, 'So cabagtasan brgy codcod ', 'Cabagtasan elementary school ', '2010-2016', 'QUEZON NATIONAL HIGH SCHOOL ', '2022-2023', 'CSR', '2025-2029', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 06:07:40', '2025-08-19 06:07:40', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(303, '2007041201', 'Singh', 'Amarpreet ', 'Siarot ', 'Male', '2007-04-12', 18, 'San Carlos City ', 'Single', 'Filipino ', 'Catholic ', '4mar5ingh2007@gmail.com', '$2y$10$kXDE9mb/yqUuckCAfzfpGenxFQdOPkZHWuwJY6hYkdnXU7zXZ23u6', '09124797328', 'Newtown Subdivision, Barangay 1 San Carlos City', 'Ranjit Singh ', 'Goods Vendor', 'Jennylyn B. Siarot', 'Housewife', 'Jennylyn B. Siarot ', 'San Carlos City ', 'N/A', 1, 1, 'Newtown Subdivision Barangay 1 San Carlos City ', 'Ramon Magsaysay Elementary School ', '2013-2019', 'Colegio de Santo Tomas Recoletos', '2019-2025', 'Colegio de Santa Rita Recoletos', '2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 06:08:25', '2025-08-19 06:08:25', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(304, '2006101801', 'Palay', 'Arvin', 'Alforte', 'Male', '2006-10-18', 19, 'Calatrava Neg.occ', 'Single', 'filipino', 'roman catholic', 'apalay@csr-scc.edu.ph', '$2y$10$NnnK3VsMYbbbXOLVjDqjWu/RG0hnuziWER1AyA5y2HoKI5QJpPMHG', '09942859828', 'Tigbao Calatrava Neg.Occ', 'Ben S Palay', 'foreman', 'Arlene Palay', 'House wife', 'Arlene Palay', 'Tigbao Calatrava Neg.Occ', 'N\\A', 1, 1, 'Tigbao Calatrava Neg.Occ', 'Tigbao Elementary School', '2018', 'Tugbao National High School', '2024-2025', 'Colleio De Sta Rita De San Carlos', '2025', 'N\\A', 'N\\A', 'N\\A', 'N\\A', 0, 0, 0, 0, 0, 0, 0, '', '2025-08-19 06:10:36', '2025-12-21 06:11:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(305, '2006090401', 'Pesalbon ', 'Frenzy Emmanuel ', 'Memes', 'Male', '2006-09-04', 18, 'San Carlos City ', 'Single', 'Filipino ', 'Roman catholic ', 'fpesalbon@csr-scc.edu.ph', '$2y$10$w/gf.rf9wos9mmCsRG0i6.JhKmfzo8bX/RUPJRIB3DR0SeoTIkCwW', '09366981224', 'Macapso vallehermoso negros oriental ', 'Edison B. Pesalbon ', 'None', 'Irene M. Pesalbon ', 'Public Schools Teacher 3', 'None', 'None', 'None', 1, 1, 'Macapso vallehermoso negros oriental ', 'Macapso elementary school ', 'Grade 6 - Sy 2017-20', 'Saint francisco school vallehermoso negros oriental ', ' grade 10 -SY 2021-2', NULL, NULL, 'None', '', 'None', '', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 06:31:15', '2025-08-19 06:31:15', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(306, '2005051901', 'Dela Fuente', 'Rylle', 'Balderama', 'Male', '2005-05-19', 20, 'San Carlos City, Negros Occidental', 'Single', 'Filipino', 'Catholic', 'delafuente@csr-scc.edu.ph', '$2y$10$FAt6PMNBzE/hgIVXnX0P1OfXaY8RWfLx4/FIhN8Sfex6yJTPko6Aa', '09952729291', 'Fatima Village, Brgy. Rizal', 'Roy Dela Fuente', 'Landscaper', 'Necel Dela Fuente', 'Teacher', 'N/A', 'N/A', 'N/A', 1, 1, 'Fatima Village, Brgy. Rizal', 'Ramon Magsaysay Elementary School', '2018', 'Tañon College', '2024', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 08:15:46', '2025-08-19 08:15:46', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(307, '2005123001', 'Cantojos', 'Jaedan ', 'Duroca', 'Male', '2005-12-30', 19, 'San Carlos City ', 'Single', 'Filipino', 'Catholic ', 'jaedancantojos023@gmail.com', '$2y$10$uk1vviPJO6A/zfYgHWnIruTeit5XQlSF6s3H9WO2KI8mbNr4emdZu', '+639919907391', 'San Carlos City ', 'Severino Cantojos ', 'Farmers ', 'Marilyn Duroca', 'housewife ', 'Marilyn Duroca ', 'San Carlos City ', 'Severino Cantojos ', 1, 1, 'San Carlos City ', 'Greenville Elementary School ', '2016-2017', 'Julio Ledesma National High School ', '2022-2023', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 08:51:57', '2025-12-27 09:00:08', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(308, '2006111101', 'Tejones ', 'Louie ', 'Bayot', 'Male', '2006-11-11', 18, 'SAN CARLOS CITY', 'Single', 'Filipino ', 'Catholic ', 'ljtejones@csr-scc.edu.ph', '$2y$10$etHqoT2A6sb1i7Rs4VRpN.mUufOKw/cn99kzHRbU/3ItcGuPmviI.', '09704669142', 'Greenville balas brgy Rizal San Carlos city negross Occidental ', 'Leonidis R Tejones', 'Government Employee ', 'Angelita Tejones ', 'House wife', 'Simplicia R. Tejones', 'Margarita extension ', 'Marivic R Tejones', 1, 1, 'Greenville balas brgy Rizal san Carlos city negross Occidental ', 'Medina Elementary school', 'Grade 6 ', 'Colegio De Santo Tomas Recoletos ', 'Grade 12 ', 'Colegio De Santa Rita Inc', 'Second Year ', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-19 09:21:22', '2025-08-19 09:21:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(309, '2006082801', 'Siaboc', 'Russel Hans', 'Dañoso', 'Male', '2006-08-28', 18, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Iglesia Ni Cristo', 'rsiaboc@csr-scc.edu.ph', '$2y$10$Ni5Skfl/63flQfiwMwuBHO7.gi1VXSyP.V3MFHx6oI8nfX8QgHKiu', '09614849888', 'Dos Hermanos St. Brgy III, San Carlos City, Negros Occidental', 'Remus T. Siaboc Sr.', 'Deceased', 'Mae D. Siaboc', 'Cook', 'Mae D. Siaboc', 'Dos Hermanos St.', 'N/A', 1, 1, 'Dos Hermanos St. San Carlos City', 'Congresman Vicente Gustilo Senior Memorial School', '2015-2016', 'Colegio de Santa Rita de San Carlos Inc.', '2024-2025', 'Colegio de Santa Rita de San Carlos Inc.', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:28:27', '2025-08-20 08:28:27', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(310, '2007032701', 'Castellano', 'Joseph', 'Valencia', 'Male', '2007-03-27', 18, 'San Carlos City Neg. Occ.', 'Single', 'Filipino', 'Roman Catholic', 'castellanojoseph13@gmail.com', '$2y$10$Dx.2Z4OzHWwPb8QYnWtw3.t2g54aBsX0fyOhiKUT3FW2HhwYT67S2', '0909170840', 'Nangka Street San Julio Subd.', 'Michael Dymosco', 'NA', 'Feliz Rose Castellano', 'NA', 'Enriqueta V. Castellano', 'Nangka Street San Julio Subd', 'NA', 1, 1, 'Nangka Street San Julio Subd', 'Congresman Vicente Gustilo SR. Memorial School', '2012', 'Colegio De sta Rita De San Carlos INC.', '2019', 'Colegio De sta Rita De San Carlos INC.', '2024', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:29:53', '2025-08-20 08:29:53', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(311, '2007062501', 'Baritua', 'John Aaron Gil ', 'Adellete', 'Male', '2007-06-25', 18, 'San Carlos City, Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic ', 'jbaritua@csr-scc.edu.ph', '$2y$10$qBGSbKR2mIM/JGWhp5SKwuzrhRa8Dh2LNgx4vQBQuAiMs/okPcBAu', '09261243515', 'Sto. Tunga, Brgy. Palampas, San Carlos City Negros Occidental ', 'Gilbert Baritua ', 'Motorcycle Driver', 'Irene Baritua ', 'Housewife', 'Gilbert Baritua ', 'Sto. Tunga, Brgy. Palampas, San Carlos City Negros Occidental ', 'N/A', 1, 1, 'Sto. Tunga, Brgy. Palampas, San Carlos City Negros Occidental ', 'Tandang Sora Elementary School', 'N/A', 'Julio Ledesma National High School ', 'N/A', 'Colegio De Sta. Rita San Carlos Inc.', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:30:22', '2025-08-20 08:30:22', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(312, '2006082501', 'APURADO', 'JOHN MARK', 'TALO-TALO', 'Male', '2006-08-25', 18, 'SAN CARLOS CITY, NEGROS OCCIDENTAL', 'Single', 'FILIPINO', 'ROMAN CATHOLIC', 'japurado@csr-scc.edu.ph', '$2y$10$DIjZh3SSwr1vrLQEVkLqnu19M7kIvWj86mkXB4C.tzignj619LvwO', '09622377924', 'MAGSAYSAY ST, CABALLERO SUBD. SAN CARLOS CITY, NEGROS OCCIDENTAL', 'JIMMY S. APURADO', 'N/A', 'MARISSA E. TALO-TALO', 'HOUSEWIFE', 'CHRISTOPHER BORNEA', 'MAGSAYSAY ST. CABALLERO SUBD. SCC, NEG. OCC.', 'NONE', 1, 1, 'MAGSAYSAY ST. CABALLERO SUBD. SCC, NEG. OCC.', 'TANDANG SORA ELEMENTARY SCHOOL', '2013-2019', 'JULIO LEDESMA NATIONAL HIGHSCHOOL', '2023-2025', 'Colegio de Sta. Rita de San Carlos Inc.', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:32:14', '2025-08-20 08:32:14', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(313, '2005092101', 'Romano', 'Vincent', 'Valencia', 'Male', '2005-09-21', 19, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Roman Catholic', 'vromano@csr-scc.edu.ph', '$2y$10$nXf3Ni.I3ObumFGG1eOD7.M.MX/TZiYMiSaY.WsEKxT1.A.rs8AMW', '09919500528', 'Don Juan Subdivision Barangay 2', 'Melvin Romano', 'Welder', 'Junessa Romano', 'Housewife', 'Adora Romano', 'Rovirih Heights', 'N/A', 1, 1, 'Don Juan Subdivision Barangay 2', 'Ramon Magsaysay Elementary School', '2013-2018', 'Julio Ledesma National High School', '2023-2024', 'Colegio de Santa Rita de San Carlos Inc.', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:33:02', '2025-08-20 08:33:02', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(314, '2007041601', 'Bantoy ', 'Jessa Mae ', 'Jarabe ', 'Female', '2007-04-16', 18, 'Toledo city ', 'Single', 'Pilipino', 'Roman Catholic ', 'jessamaebantoy1@gmail.com', '$2y$10$qVkG7.EtyD2svM/C6O6ky.KyXdz7/z6wPnVcVuedSKAh4ObwIW2dm', '09630102876', 'Brgy.Prosperidad', 'Bantoy ', 'Truck boy', 'Bantoy ', 'Ofw', 'Judy Ann ', 'Brgy.Prosperidad', 'Jeffrey J. Bantoy', 1, 1, 'Brgy.Prosperidad', 'Prosperidad Elementary school ', '2018/2019', 'Our lady of peace mission school inc.', '2024/2025', 'Colegio de Santa Rita de San Carlos City,Inc.', '2025/2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:34:03', '2025-08-20 08:34:03', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(315, '2003040401', 'AGRAVIADOR', 'FEATJ OHN', 'RECILLA', 'Male', '2003-04-04', 22, 'LAPU-LAPU CEBU CITY', 'Single', 'FILIPINO', 'CHATHOLIC', 'fagraviador@csr-scc.edu.ph', '$2y$10$y3W8nBZjym8SO1EzwO4cvufh8X81xJihY56UxWgcCCy3oHOYvJb6y', '09936200908', 'brgy lipat on negros occidental', 'joebert', 'teacher', 'florefes', 'vendor', 'belin', 'brgy lipat-on negros occidental', 'N/A', 1, 1, 'calatrava negros occidental', 'calatrava 1 centra scholl', 'N/A', 'east negros academy inc', 'N/A', 'collegio de santa rita', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:34:39', '2025-08-20 08:34:39', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(316, '2005071601', 'Ling', 'Ram Davis', 'Cano', 'Male', '2005-07-16', 20, 'San Carlos City', 'Single', 'Filipino', 'Roman Catholic', 'ling@csr-scc.edu.ph', '$2y$10$S9BvTE8VS4O/sfXYsQK60OKo4Vmuz3EIi8u3fT/CeC/hdREn2Hdai', '09109495329', 'Brgy. Bantayanon Negros Occidental', 'Raul Bulfa', 'N/A', 'Merissa Ling', 'house wife', 'Ram Ashley C. Ling', 'Brgy. Bantayanon', 'Raymart C. Ling ', 1, 1, 'Calatrava Brgy.Bantayanon Negros Occidental ', 'Calatrava  2 Central School', '2017-2018', 'Calatrava National Highschool', '2020-2021', 'Colegio De Santa Rita de San Carlos, Inc.', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:34:45', '2025-08-20 08:34:45', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(317, '2007090301', 'Lawas', 'C-jay', 'Dequiso', 'Male', '2007-09-03', 17, 'San Carlos City Hospital ', 'Single', 'Filipino', 'Church of Christ', 'clawas@csr-scc.edu.ph', '$2y$10$vH445K.LgZf7.Gc61FlSTuladZx/nYIHcVjZMxES21liDRXmO3MR.', '09926648524', 'Brgy 1 Villarante Village', 'Jose Jason ', 'Receptionist', 'Cicillia', 'Receptionist', 'na', 'na', 'na', 1, 1, 'Brgy 1', 'Florinta Ledesma Elementary School', '2013-2018', 'Colegio De Santa Rita Inc', '2023-2024', 'Colegi0 De Santa Rita Inc', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:34:48', '2025-08-20 08:34:48', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(318, '2005030801', 'Almaras ', 'Crishel ', 'Requiron ', 'Female', '2005-03-08', 20, 'Laspiñas ', 'Single', 'Filipino ', 'Catholic ', 'crisherequironalmaras@gmail.com', '$2y$10$DrDYeRdA6uJHFX7xzptkVegx4G2SPbYT2Scza8xNLsCctBq/sp2pK', '09753671915', 'Brgy.Macapso Vallehermoso Negros Oriental ', 'Eusebio Almaras ', 'Farmer', 'Libreta Almaras ', 'House Wife', 'Eusebio Almaras ', 'Brgy.Macapso Vallehermoso ', 'Libreta Almaras ', 1, 1, 'Brgy.Macapso Vallehermoso Negros Oriental ', 'Macapso Elementary School ', '2018-2019', 'Saint Francis High School ', '2023-2024', 'Collegio de Santa Rita de San Carlos', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:37:21', '2025-08-20 08:37:21', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(319, '2007093001', 'Bucal', 'James Cidrick', 'Alingasa', 'Male', '2007-09-30', 17, 'Cebu City', 'Single', 'Filipino', 'Catholic', 'bucal@csr-scc.edu.ph', '$2y$10$uEz4hYkywAb1f3pn3a99MuOpxAN7QDTdncW59VV17juGz9AYiqU8a', '09927583468', 'Brgy Macasilao', 'Jesus L. Bucal', 'BusinessMan', 'Joan A. Bucal', 'OFW', 'Josephine Alingasa', 'Brgy Macasilao', 'Amelita Alingasa', 1, 1, 'Brgy Macasilao', 'Macasilao Elementary School', '2013-2019', 'Paghumayan National High School', '2019-2023', 'Collegio de Santa Rita de San Carlos', '2025-2029', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:38:11', '2025-08-20 08:38:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(320, '2007101203', 'Duran ', 'Kimberly ', 'Adolfo', 'Female', '2007-10-12', 17, 'San Carlos city ', 'Single', 'Filipino ', 'Catholic ', 'duran@csr-scc.edu.ph', '$2y$10$hT.2nrWtPtOoPxM5DdBzruTqN/Zpe0DBA7RNVVNLeXvcFarlBDusq', '09810776796', 'So. Malatamban brgy. Guadalupe ', 'Conrado Duran', 'Trycicle driver ', 'Rosevilla Duran', 'Housewife ', 'Shiela Mae Duran', 'So. Malatamban brgy Guadalupe ', 'Rosemay Baranggan', 1, 1, 'So. Malatamban brgy Guadalupe ', 'Gigalaman elementary school', '2013-2019', 'Don Carlos Ledesma national high school ', '2024-2025', 'Colegio de Santa Rita de San Carlos inc.', '2025-2026', 'NA', 'Na', 'Na', 'Na', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:38:18', '2025-08-20 08:38:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(321, '2006090402', 'Gabutero', 'Joesh Michael', 'Albiar', 'Male', '2006-09-04', 18, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Catholic', 'joeshgabutero3@gmail.com', '$2y$10$v8Xxti9T3yTw.HX4b.240.7Zck65syqus9rZlL1D1N..Y9OeSqmmK', '09947764676', 'San Juan BRGY 6', 'Jose Marie S. Gabutero', 'Networking neo atoms', 'Precy A. Gabutero', 'housewife', 'N/A', 'N/A', 'N/A', 1, 1, 'San Juan BRGY 6', 'Ramon magsaysay elementary school', '2013-2018', 'Tanon College ', '2019 - 2024', 'Colegio de santa rita inc', '2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:40:00', '2025-08-20 08:40:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(322, '2007032301', 'Suerto', 'Kirk Aiken', 'Taneca', 'Male', '2007-03-23', 18, 'San Carlos City Hospital', 'Single', 'Filipino', 'Roman Catholic', 'kirkaiken723@gmail.com', '$2y$10$Z8/.HIfecbGeiCGvlupQKed.161gggVeNqtPp0taXKlR2p9kY0kD.', '09518069202', 'Barangay 1, St.Vincent, 9th street, San Carlos City, Negros Occidental', 'Aldwin R. Suerto', 'Food delivery', 'Ma.Theresa T. Suerto', 'N/A', 'Sheina R. Suerto', 'Barangay 1, St.Vincent, 9th street, San Carlos City, Negros Occidental', 'N/A', 1, 1, 'Barangay 1, St.Vincent, 9th street, San Carlos City, Negros Occidental', 'Ramon Magsaysay Elementary School', '2013-2019', 'Colegio de Santa Rita de San Carlos, Inc.', '2023-2025', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:40:17', '2025-08-20 08:40:17', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(323, '2007082301', 'Parreno', 'Monicka Marie', 'Asegurado', 'Female', '2007-08-23', 17, 'San Carlos City Negros Occidental', 'Single', 'Filipino', 'Catholic', 'monickamarieaparreno@gmail.com', '$2y$10$p61bgt2403DDWo1xTFeY5OZyFYKz.SivcUvig6NvSjV.fCGIpfTym', '09368509611', 'Phase 4 brgy Rizal san Carlos City Negros Occidental', 'N/A', 'N/A', 'Sharon Parreno', 'Phase 4 brgy Rizal San Carlos City', 'Marissa A. Parreno ', 'Phase 4 brgy Rizal San Carlos City', 'Sharon Parreno', 1, 1, 'Phase 4 brgy Rizal san Carlos City Neg. Occ', 'Ramon Magsaysay Elementary School', '2013-2019', 'Colegio de Sto. Tomas Recolletos Inc.', '2019-2023', 'Colegio de Sta. Rita ', '2025-2029', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:42:21', '2025-08-20 08:42:21', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(324, '2007062301', 'Damandaman', 'Hanna Grace ', 'Amparo', 'Female', '2007-06-23', 18, 'Buluangan', 'Single', 'Filipino', 'catholic', 'hannagracedamandaman099@gmail.com', '$2y$10$5u/saeVH7//46wlX7dLw/.ClE2QrF.rekMsA6F7dUKR4P7TSmtOwy', '09549802686', 'Brgy. Buluangan San Casrlos City Negros Occidental', 'Herman Q. Damandaman', 'embalmer', 'Teresa Amparo Damandaman', 'house wife', 'Ethel Grace D. Arniga', 'Brgy. Buluangan San Casrlos City Negros Occidental', 'Sister', 1, 1, 'Brgy. Buluangan San Casrlos City Negros Occidental', 'katingal-an elementary school', '2013-2019', 'don carlos ledesma national high school', '2019-2023', 'Collegio de Santa Rita de San Carlos', '2025-2029', 'N/A', '', NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:43:55', '2025-08-20 08:43:55', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(325, '2006090602', 'simbajon', 'jose jeed', 'larida', 'Male', '2006-09-06', 18, 'sancarlos', 'Single', 'filipino', 'roman catholic', 'josejeeds@gmail.com', '$2y$10$oYbBF70wlFYHj2.04wKAuOA78B0NJcn8VhyrG/FgqN3ZH0a3gaeOS', '09276575858', 'brgy1 newtown', 'angelito ', 'simbajon', 'belinda', 'simbajon', 'belinda ', 'brgy 1 newtown', 'N/A', 1, 1, 'brgy1 newtown', 'ramon magsaysay', '2015-2016', 'santarita', '2023-2024', 'santarita', '2025', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:43:57', '2025-08-20 08:43:57', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(326, '2005061701', 'LIM', 'John Michael Angelo', 'Baldomar', 'Male', '2005-06-17', 20, 'Calatrava Central Hospital', 'Single', 'Filipino', 'Roman Catholic', 'johnlim@csr-scc.edu.ph', '$2y$10$mAQf8DmrETZmerYeORip8OCn1U74esSxct8LYiBWdIphf1YYVkC/q', '09912770452', 'Calatrava, Brgy. Calampisawan Negros Occidental', 'Rowel Enoc', 'Construction worker', 'Leah Lim', 'House Wife', 'Leah Lim', 'Calatrava Negros Occidenatal ', 'Rowel Enoc', 1, 1, 'Calatrava, Brgy. Calampisawan Negros Occidental', 'Calatrava 2 Central School', '2018-2019', 'Calatrava National Highschool', '2021-2022', 'Colegio de Santa Rita de San Carlos, Inc', '2025-2026', 'NA', 'NA', 'NA', 'NA', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:44:28', '2025-08-20 08:44:28', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(327, '2007071701', 'Escabas', 'Vest Psyche', 'Escultor', 'Male', '2007-07-17', 18, 'Brgy. Bunga don salvador benedicto', 'Single', 'filipino', 'Catholic', 'escabas@csr-scc.edu.ph', '$2y$10$ddYRSOamGjhYjq2y6fkhYOuKqf4v6WcNKRHYrOiEosDGKaLC1fBc6', '09701876890', 'Prk.Manihan\r\n\r\n', 'Selvestre P. Escabas', 'Eletronics', 'Teresita E. Escabas', 'OFW', 'Selvestre P. Escabas', 'Prk. Manihan', 'mother', 1, 1, 'Prk. Manihan', 'Spur 16', '2013/2019', 'OUR LADY OF PEACE MISSION SCHOOL ', '2024/2025', 'Colegio de santa rita', '2025/2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:45:36', '2025-08-20 08:45:36', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(328, '2007100601', 'Poseliro', 'Ashley Joy', 'Piñero', 'Female', '2007-10-06', 17, 'Manila', 'Single', 'Filipino', 'Catholic ', 'aposeliro@esr-scc.edu.ph', '$2y$10$3ALL2rbAMBQG2lkKgRx0.eNBAWXM.pOrAncZIpqSC3MrlIcYYb4fO', '09556110418', 'Brgy Macapso Vallehermoso Negros Oriental', 'Dexter Poseliro', 'Factory ', 'Rosemarie Piñero', 'Housewife ', 'Rosemarie Piñero', 'Housewife ', 'Rosemarie Piñero', 1, 1, 'Brgy Macapso Vallehermoso Negros Oriental', 'Macapso elementary school', '2015-2016', 'Don carlos ledesma national high school', '2024-2025', 'Colegio de Santa Rita ', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:47:30', '2025-08-20 08:47:30', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(329, '2007060301', 'Amparo', 'Jeanie', 'Redera', 'Female', '2007-06-03', 18, 'Vallehermoso negros oriental ', 'Single', 'Filipino ', 'Catholic ', 'jeanieamparo54@gmail.com', '$2y$10$s.a7WrBjTOgq8tp7Ax4tXef14fxdGrpFqyl/svhVCbroBJfrAdvem', '09555372561', 'Vallehermoso negros oriental ', 'Renato Amparo', 'Farmer', 'Jocelyn Amparo ', 'House wife', 'Jocelyn Amparo', 'Macapso', 'Sister', 1, 1, 'Brgy, macapso vallehermoso negros oriental ', 'Brgy, macapso vallehermoso negros oriental ', '2015-2016', 'Don Carlos ledesma national highschool ', '2024-2025', 'Colegio de st. Rita de San Carlos Inc', '2024-2025', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:47:44', '2025-08-20 08:47:44', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(330, '2004100101', 'Liberal ', 'Mark Mattheu ', 'Echavez', 'Male', '2004-10-01', 20, 'San Carlos Hospital ', 'Single', 'Philippines ', 'Catholic ', 'ibatupvp1@gmail.com', '$2y$10$9EH.iMg7bNWR1VNDEaxeNOQ2JlShWBBiMRjCSg.qzTNqPQosX9F96', '09397362813', 'St. Charles, Lot 3 Block 3', 'Mark Dennis ', 'NA', 'Rowella N. echavez', 'NA', 'Mark Whedon', 'St. Charles lot 3 block 3', 'NA', 1, 1, 'St. Charles Lot 3 Block 3', 'NA', 'NA', 'National high school', '2018-2020', 'San Rita saint Charles ', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:47:48', '2025-08-20 08:47:48', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(331, '2007102401', 'Ycong', 'Lourence kenly', 'Nocos', 'Male', '2007-10-24', 17, 'lapu-lapu city', 'Single', 'Filipino', 'catholic', 'lourencekenlyycong@gmail.com', '$2y$10$vFYIingPFyx53UFruyHmr.t37lMDCUk7PrS6qlVQbNTLY7nW2Jtnu', '09300914808', 'Brgy.Prosperidad San Carlos City', 'Reynaldo', 'Ycong', 'Rosemarie', 'Nocos', 'Rosemarie', 'Brgy.Prosperidad', 'N/A', 1, 1, 'Brgy.Prosperidad', 'BRGY. PROSPERIDAD ELEMENTARY SCHOOL', '2012/2019', 'OOUR LADY OF PEACE MISSION SCHOOL INC.', '2024/2025', 'COLEGIO  DE SANTA RITA ', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:52:12', '2025-08-20 08:52:12', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(332, '2006100202', 'Ycong', 'John Reymar', 'Nocos', 'Male', '2006-10-02', 18, 'lapu-lapu city', 'Single', 'Filipino', 'catholic', 'ycongjohn775@gmail.com', '$2y$10$IydKyGPtSlKUzb0lGZOpvuDSaPosnLCgwREOYN/qNMkgGIjRPVdBS', '09707782281', 'Brgy Prosperidad San Carlos City', 'Reynaldo', 'Ycong', 'Roremarie', 'Nocos', 'Rosemarie', 'Brgy Prosperidad San Carlos City', 'N/A', 1, 1, 'Brgy Prosperidad San Carlos City', 'BRGY.PROSPERIDAD', '2013/20019', 'OUR LADY OF PEACE MISSION SCHOOL, INC.', 'N/A', 'COLEGIO DE SANTA RITA', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 08:54:00', '2025-08-20 08:54:00', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(333, '2007082101', 'Leduna ', 'Harry Louise', 'Hentil', 'Male', '2007-08-21', 17, 'San Carlos City Negros Occidental', 'Single', 'Philippines', 'Roman Catholic', 'hleduna@csr-scc.edu.ph', '$2y$10$keCaSD8994YXVyJlzsEHpOhICJUuiLRgo.le17a7LZRQ5uVy2hsp6', '09067833135', 'Campo Siete Brgy 5 San Carlos City Negros Occidental', 'Reynaldo B. Leduna', 'N/A', 'Dina H. Leduna', 'Housewife', 'Dina H. Leduna', 'N/A', 'N/A', 1, 1, 'Campo Siete Brgy 5 San Carlos City Negros Occidental', 'Bonifacio Elementary School', '2013 - 2018', 'Julio Ledesma National High School', '2019 - 2025', 'Colegio de Santa Rita de San Carlos Inc.', 'N/A', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 09:18:27', '2025-08-20 09:18:27', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(334, '2007031801', 'Descartin', 'Hector', 'Dela Fuente', 'Male', '2007-03-18', 18, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'hdescartin@csr-scc.edu.ph', '$2y$10$1pfRrbBU0Js2kGFTt6seNeeFotuFPZRxonxwv9y0yRXLgR7slrT6S', '09369417316', 'Urban Phase 6', 'Hector P. Desacartin Sr.', 'Government Worker', 'Lorena D. Descartin', 'Housewife', 'N/A', 'N\\A', 'N\\A', 1, 1, 'Urban Phase 6', 'Ramon Magsaysay Elementary School', '2017', 'COLEGIO DE SANTA RITA DE SAN CARLOS INC.', '2024-2025', 'COLEGIO DE SANTA RITA DE SAN CARLOS INC.', '2025-2026', 'N\\A', 'N\\A', 'N\\A', 'N\\A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 09:19:34', '2025-08-20 09:19:34', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(335, '2007102501', 'Macayan', 'Yuri Isaiah', 'GONZALES', 'Male', '2007-10-25', 17, 'Ma-ao Bago city', 'Single', 'FILIPINO', 'CATHOLIC', 'ymacayan@csr-scc.edu.ph', '$2y$10$Ff93dJdSQQP8p64juglUoOsL9UN47nX3sP7S4R2xQlwkSdFfkI03e', '09625450918', 'Hda.San Antonio Brgy, Guadalupe', 'REY G MACAYAN', 'BODY GUARD', 'MARRY GRACE D GONZALES', 'HOUSE WIFE', 'MA.THERESA D GONZALES', 'HDA.SAN ANTONIO BRGY, GUADALUPE', 'N/A', 1, 1, 'HDA.SAN ANTONIO BRGY, GUADALUPE', 'GUADALUPE', '2017', 'COLEGIO DE SANTA RITA DE SAN CARLOS', '2025', 'COLEGIO DE SANTA RITA DE SAN CARLOS', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 09:21:35', '2025-08-20 09:21:35', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(336, '2007033001', 'Daniel', 'Mahamh Jurinz', 'Ventura', 'Male', '2007-03-30', 18, 'san carl9os', 'Single', 'filipino', 'roman catholic', 'danielmahamh@gmail.com', '$2y$10$Rf6kdUL5WRPjKdPk3PEkZuXwuwNiIUTSXD2TAmkjDPTpIu/RrMrcK', '09629310396', 'san juan bonifacio brgy 5 san carlos city', 'julius', 'none', 'marivit', 'none', 'merlina', 'n\\a', 'n\\a', 1, 1, 'sqan juan bonifacio brgy 5 san carlos city', 'lina dela vina elementary school', '2013-2018', 'colegio de sto thomas', '2018-2024', 'colegio de sta rita', '2025-2026', 'n\\a', 'n\\a', 'n\\a', 'n\\a', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 09:30:49', '2025-08-20 09:30:49', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(337, '2007082201', 'Sevilla', 'Elias', 'G.', 'Male', '2007-08-22', 17, 'San Carlos City, Negros Occ.', 'Single', 'Filipino', 'Roman Catholic', 'sevilla@csr-scc.edu.ph', '$2y$10$i.9slBrXQCJHBjyzw8BAYeImpf5LO2psSi9l9Mz2vwR/zm.pCBghu', '09387617760', 'St. John Subdivision', 'N/A', 'N/A', 'Lyndelle G. Sevilla', 'Housewife', 'N/A', 'N/A', 'N/A', 1, 1, 'St. John Subdivision', 'Andres Bonifacio Central School', '2013-2018', 'Julio Ledesma National High School', '2019 - 2025', 'Colegio de Santa Rita de San Carlos Inc.', 'N/A', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 10:03:52', '2025-08-20 10:03:52', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(338, '2003052001', 'Arrivas', 'Tom rolivic', 'Arrivas', 'Male', '2003-05-20', 22, 'City hospital san carlos city.neg occ', 'Single', 'English', 'Roman catholic', 'tomrolivica@gmail.com', '$2y$10$MVVxNcCjq5kjOeqzHh49hefump8r4VyJ11W9G2p2QFi40zhCTuRdq', '09369007303', 'Brgy. 6 parola locsin street', 'Deceased', 'Deceased', 'Rosemarie villas arrivas', 'Waterworks ', 'Lenivic delasa', 'South villa 1', 'Malou arrivas', 1, 1, 'Brgy.6 parola locsin street', 'Ramon magsaysay elem.school', '200-2001', 'Julio ledesma national high school', '2020-2023', 'Colegio de santa rita', '2025-2026', NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-20 13:52:18', '2025-08-20 13:52:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(340, '2007040301', 'Damandaman', 'April', 'Patrimonio', 'Female', '2007-04-03', 18, 'San Carlos City', 'Single', 'Filipino', 'Catholic', 'aprildamandaman243@gmail.com', '$2y$10$zAl6vwP2E4Bwm.KvUAhrI.p/CDQ.A9L37yvZFoX3rDdYAhtVpkma2', '09972249308', 'San Juan Tunga ', 'Danilo G. Damandaman', 'N/A', 'Milogen P. Damandaman', 'N/A', 'Inday D. Melojodejo', 'San Juan Tunga', 'Jhasic Patrimonio', 1, 1, 'Brgy San Juan Tunga', 'Talisay Elementary School', '2018', 'Talisay City National High School', '2024-2025', 'Colegio de Santa Rita', '2025-2026', 'N/A', 'N/A', 'N/A', 'N/A', 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-21 12:19:23', '2025-08-21 12:19:23', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(341, '2001081901', 'Hugo', 'John Dave', 'Tirol', 'Male', '2001-08-19', 24, 'City of Manila', 'Single', 'Filipino', 'Roman Catholic', 'jhugo@csr-scc.edu.ph', '$2y$10$pBVlRG4ycK0.v2YI3qJ4eOn7dxY/FmkmPgoqfxolbDtzQXSBCWz7S', '9638947861', 'CL Ave Brgy 5 San Carlos City Negros Occidental', 'NA', 'NA', 'Debbie Tirol', 'OFW', 'NA', 'NA', 'NA', 1, 1, 'San Carlos', 'Colegio de Sta Rita', '2008 - 2014', 'Colegio de Sto Tomas - Recoletos', '2015 - 2021', NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-08-26 04:34:20', '2025-08-26 04:34:20', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(344, '2008062101', 'Batuto', 'Zha Keisha', 'Gozon', 'Female', '2008-06-21', 17, 'Cebu City', 'Single', 'Filipino', 'Roman Catholic', 'jason@csr-scc.edu.ph', '$2y$10$9Me44irhqIwljv7mUOOmQe0E5Q.mOXPIBrHfGS5JGGyfN39Sd7jj6', '09179348731', 'San Carlos', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2025-12-18 08:13:33', '2025-12-18 08:13:33', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(345, '2004102201', 'Canoy III', 'Jose', 'Mendoza ', 'Male', '2004-10-22', 21, 'San Carlos City Negros Occidental ', 'Single', 'Filipino', 'Roman Catholic ', 'jose@csr-scc.edu.ph', '$2y$10$acDcNNoO9OQsqMtUHnIVweGmnCAXX1y5/22KU2eNiVwjF3EgleRqC', '09703343439', 'San Juan Tunga Barangay V', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2026-03-07 01:49:32', '2026-03-07 01:49:32', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(346, '2001122801', 'Singular', 'JudyMar ', 'Pinggon', 'Male', '2001-12-28', 24, 'San Carlos city neg OCC.', 'Single', 'Filipino ', 'Roman Catholic ', 'singular@csr-scc.edu.ph', '$2y$10$MG6Nc/37ZillXriv3GN4Te4QCMGsLv29K7aPdYHdVPHF1xBl.czCC', '09704666229', 'Purok Calumpang brgy 1', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2026-03-07 02:21:02', '2026-03-07 02:21:02', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(347, '2004082001', 'Rigor ', 'Dan Michaelangelo ', 'Marella', 'Male', '2004-08-20', 21, 'San Carlos city Negros Occidental ', 'Single', 'Filipino ', 'Roman Catholic ', 'angelo10@csr-scc.edu.ph', '$2y$10$ze3qT431UJygmYrHl1ALWO63OuetWeLDSFVXw9ahXaJlSQhexawy.', '093 922 361 0297', 'purok ipil-ipil brgy1', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2026-03-07 02:24:18', '2026-03-07 02:24:18', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(348, '2004041901', 'Ompar', 'Raven Carl', 'Despi', 'Male', '2004-04-19', 21, 'San Carlos City NIR', 'Single', 'Filipino', 'Born again Christian ', 'ompar@csr-scc.edu.ph', '$2y$10$qh0bgzZ0ZFTfvvp7aygFgu6gsD4rrdFhW7.QDixtmoGKgnt.n8k2m', '09631719419', 'Gemelina brgy1', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2026-03-07 02:33:11', '2026-03-07 02:33:11', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0),
(349, '2004082802', 'Jarabe ', 'Iries ', 'Larita', 'Female', '2004-08-28', 21, 'Prk. Ipil-ipil brgy. Bunga DSB neg. Occ', 'Single', 'Filipino ', 'Seventh day Adventist ', 'iriesjarabe@csr-scc.edu.ph', '$2y$10$S/Fr.Dg.eeUWYC8lHJV5IezIkISjuvokTNu3RDxiC6BtL4yT/cq7a', '09977023654', 'Brgy. Bunga prk. Ipil-ipil Salvador Benedicto Neg. Occ. ', '', '', '', '', '', '', '', 0, 0, '', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, 0, 0, 0, 0, 0, 0, 0, NULL, '2026-03-07 04:01:24', '2026-03-07 04:01:24', 0, '', '', '', 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0);

-- --------------------------------------------------------

--
-- Table structure for table `student_activity_scores`
--

CREATE TABLE `student_activity_scores` (
  `id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `score` decimal(10,2) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_activity_scores`
--

INSERT INTO `student_activity_scores` (`id`, `activity_id`, `student_id`, `score`, `created_at`, `updated_at`) VALUES
(3, 8, 4, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(4, 8, 25, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(5, 8, 28, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(6, 8, 62, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(7, 8, 81, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(8, 8, 182, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(9, 8, 185, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(10, 8, 187, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(11, 8, 189, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(12, 8, 190, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(13, 8, 191, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(14, 8, 341, 100.00, '2025-12-17 06:56:00', '2025-12-17 06:56:00'),
(15, 9, 29, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(16, 9, 36, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(17, 9, 40, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(18, 9, 45, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(19, 9, 46, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(20, 9, 48, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(21, 9, 49, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(22, 9, 66, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(23, 9, 78, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(24, 9, 105, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(25, 9, 115, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(26, 9, 117, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(27, 9, 127, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(28, 9, 129, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(29, 9, 157, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(30, 9, 165, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(31, 9, 167, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(32, 9, 177, 100.00, '2025-12-17 06:59:40', '2025-12-17 07:23:33'),
(33, 9, 164, 100.00, '2025-12-17 07:03:27', '2025-12-17 07:23:33'),
(34, 9, 25, 100.00, '2025-12-17 07:23:33', '2025-12-17 07:23:33'),
(35, 14, 12, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(36, 14, 13, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(37, 14, 14, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(38, 14, 15, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(39, 14, 17, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(40, 14, 18, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(41, 14, 20, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(42, 14, 22, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(43, 14, 23, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(44, 14, 27, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(45, 14, 31, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(46, 14, 32, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(47, 14, 33, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(48, 14, 37, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(49, 14, 38, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(50, 14, 42, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(51, 14, 43, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(52, 14, 44, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(53, 14, 47, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(54, 14, 50, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(55, 14, 65, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(56, 14, 77, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(57, 14, 307, 50.00, '2025-12-29 01:44:19', '2025-12-29 01:44:19'),
(58, 15, 12, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(59, 15, 13, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(60, 15, 14, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(61, 15, 15, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(62, 15, 18, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(63, 15, 19, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(64, 15, 20, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(65, 15, 22, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(66, 15, 23, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(67, 15, 27, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(68, 15, 31, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(69, 15, 32, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(70, 15, 33, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(71, 15, 37, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(72, 15, 43, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(73, 15, 44, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(74, 15, 47, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(75, 15, 64, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(76, 15, 65, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(77, 15, 77, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(78, 15, 307, 50.00, '2025-12-29 01:52:09', '2025-12-29 01:52:09'),
(79, 16, 12, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(80, 16, 13, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(81, 16, 14, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(82, 16, 15, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(83, 16, 17, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(84, 16, 18, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(85, 16, 19, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(86, 16, 20, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(87, 16, 22, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(88, 16, 23, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(89, 16, 27, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(90, 16, 31, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(91, 16, 32, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(92, 16, 33, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(93, 16, 37, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(94, 16, 38, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(95, 16, 42, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(96, 16, 43, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(97, 16, 44, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(98, 16, 47, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(99, 16, 50, 0.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(100, 16, 64, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(101, 16, 65, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(102, 16, 77, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(103, 16, 307, 150.00, '2025-12-29 02:03:39', '2025-12-29 02:03:39'),
(104, 17, 30, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(105, 17, 149, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(106, 17, 195, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(107, 17, 196, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(108, 17, 197, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(109, 17, 199, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(110, 17, 200, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(111, 17, 201, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(112, 17, 202, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(113, 17, 203, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(114, 17, 204, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(115, 17, 205, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(116, 17, 206, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(117, 17, 207, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(118, 17, 208, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(119, 17, 209, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(120, 17, 211, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(121, 17, 212, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(122, 17, 213, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(123, 17, 214, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(124, 17, 217, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(125, 17, 218, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(126, 17, 219, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(127, 17, 221, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(128, 17, 222, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(129, 17, 223, 0.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(130, 17, 224, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(131, 17, 225, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(132, 17, 292, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(133, 17, 296, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(134, 17, 306, 50.00, '2025-12-29 02:15:40', '2025-12-29 02:28:00'),
(135, 18, 30, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(136, 18, 195, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(137, 18, 196, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(138, 18, 197, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(139, 18, 199, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(140, 18, 200, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(141, 18, 202, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(142, 18, 203, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(143, 18, 205, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(144, 18, 206, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(145, 18, 207, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(146, 18, 208, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(147, 18, 209, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(148, 18, 211, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(149, 18, 212, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(150, 18, 214, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(151, 18, 217, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(152, 18, 218, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(153, 18, 219, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(154, 18, 221, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(155, 18, 222, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(156, 18, 223, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(157, 18, 224, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(158, 18, 306, 50.00, '2025-12-29 02:24:53', '2025-12-29 02:24:53'),
(159, 19, 30, 50.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(160, 19, 195, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(161, 19, 197, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(162, 19, 199, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(163, 19, 200, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(164, 19, 201, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(165, 19, 204, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(166, 19, 206, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(167, 19, 207, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(168, 19, 208, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(169, 19, 209, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(170, 19, 218, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(171, 19, 221, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(172, 19, 222, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(173, 19, 224, 100.00, '2025-12-29 02:27:20', '2025-12-29 02:27:20'),
(174, 21, 253, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(175, 21, 254, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(176, 21, 255, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(177, 21, 256, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(178, 21, 257, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(179, 21, 259, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(180, 21, 261, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(181, 21, 262, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(182, 21, 263, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(183, 21, 265, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(184, 21, 273, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(185, 21, 308, 100.00, '2025-12-29 02:34:09', '2025-12-29 02:34:09'),
(186, 22, 253, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(187, 22, 254, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(188, 22, 255, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(189, 22, 256, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(190, 22, 257, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(191, 22, 259, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(192, 22, 261, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(193, 22, 262, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(194, 22, 263, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(195, 22, 265, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(196, 22, 273, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(197, 22, 308, 100.00, '2025-12-29 02:35:12', '2025-12-29 04:02:17'),
(198, 23, 135, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(199, 23, 139, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(200, 23, 144, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(201, 23, 146, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(202, 23, 147, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(203, 23, 159, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(204, 23, 161, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(205, 23, 168, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(206, 23, 174, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(207, 23, 176, 50.00, '2025-12-29 04:04:16', '2025-12-29 04:04:16'),
(208, 24, 266, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(209, 24, 268, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(210, 24, 269, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(211, 24, 277, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(212, 24, 278, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(213, 24, 279, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(214, 24, 280, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(215, 24, 281, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(216, 24, 282, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(217, 24, 285, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(218, 24, 286, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(219, 24, 288, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(220, 24, 290, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(221, 24, 291, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(222, 24, 293, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(223, 24, 294, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(224, 24, 295, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(225, 24, 300, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(226, 24, 302, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(227, 24, 304, 100.00, '2025-12-29 04:31:54', '2025-12-29 04:31:54'),
(228, 25, 266, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(229, 25, 268, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(230, 25, 269, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(231, 25, 277, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(232, 25, 278, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(233, 25, 279, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(234, 25, 280, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(235, 25, 281, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(236, 25, 282, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(237, 25, 285, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(238, 25, 286, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(239, 25, 288, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(240, 25, 290, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(241, 25, 291, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(242, 25, 293, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(243, 25, 294, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(244, 25, 295, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(245, 25, 300, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(246, 25, 302, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(247, 25, 304, 100.00, '2025-12-29 04:34:26', '2025-12-29 04:34:26'),
(248, 26, 79, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(249, 26, 82, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(250, 26, 83, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(251, 26, 84, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(252, 26, 85, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(253, 26, 86, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(254, 26, 88, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(255, 26, 90, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(256, 26, 92, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(257, 26, 93, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(258, 26, 94, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(259, 26, 95, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(260, 26, 96, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(261, 26, 97, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(262, 26, 98, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(263, 26, 99, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(264, 26, 100, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(265, 26, 102, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(266, 26, 103, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(267, 26, 106, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(268, 26, 107, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(269, 26, 108, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(270, 26, 109, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(271, 26, 110, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(272, 26, 111, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(273, 26, 112, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(274, 26, 113, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(275, 26, 114, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(276, 26, 118, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(277, 26, 128, 50.00, '2025-12-29 04:42:30', '2025-12-29 04:42:30'),
(278, 27, 79, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(279, 27, 82, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(280, 27, 83, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(281, 27, 84, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(282, 27, 85, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(283, 27, 86, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(284, 27, 88, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(285, 27, 90, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(286, 27, 92, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(287, 27, 93, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(288, 27, 94, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(289, 27, 95, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(290, 27, 96, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(291, 27, 97, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(292, 27, 98, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(293, 27, 99, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(294, 27, 100, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(295, 27, 102, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(296, 27, 103, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(297, 27, 106, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(298, 27, 107, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(299, 27, 108, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(300, 27, 109, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(301, 27, 110, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(302, 27, 111, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(303, 27, 112, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(304, 27, 113, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(305, 27, 114, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(306, 27, 118, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35'),
(307, 27, 128, 100.00, '2025-12-29 04:43:35', '2025-12-29 04:43:35');

-- --------------------------------------------------------

--
-- Table structure for table `student_fees`
--

CREATE TABLE `student_fees` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `fee_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_fees`
--

INSERT INTO `student_fees` (`id`, `student_id`, `fee_id`, `amount`, `academic_year`, `semester`, `created_at`, `updated_at`) VALUES
(1, 81, 1, 1200.00, '2025-2026', '2nd', '2026-03-06 01:04:22', '2026-03-06 01:04:22'),
(2, 81, 2, 500.00, '2025-2026', '2nd', '2026-03-06 01:04:22', '2026-03-06 01:04:22'),
(3, 4, 1, 1200.00, '2025-2026', '2nd', '2026-03-06 01:07:50', '2026-03-06 01:07:50'),
(4, 4, 2, 500.00, '2025-2026', '2nd', '2026-03-06 01:07:50', '2026-03-06 01:07:50'),
(5, 35, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:13:39', '2026-03-07 01:13:39'),
(6, 35, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:13:39', '2026-03-07 01:13:39'),
(7, 180, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:14:40', '2026-03-07 01:14:40'),
(8, 180, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:14:40', '2026-03-07 01:14:40'),
(9, 117, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:15:28', '2026-03-07 01:15:28'),
(10, 117, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:15:28', '2026-03-07 01:15:28'),
(11, 189, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:16:19', '2026-03-07 01:16:19'),
(12, 189, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:16:19', '2026-03-07 01:16:19'),
(13, 28, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:17:13', '2026-03-07 01:17:13'),
(14, 28, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:17:13', '2026-03-07 01:17:13'),
(15, 105, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:17:57', '2026-03-07 01:17:57'),
(16, 105, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:17:57', '2026-03-07 01:17:57'),
(17, 126, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:18:50', '2026-03-07 01:18:50'),
(18, 126, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:18:50', '2026-03-07 01:18:50'),
(19, 40, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:19:58', '2026-03-07 01:19:58'),
(20, 40, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:19:58', '2026-03-07 01:19:58'),
(21, 167, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:21:53', '2026-03-07 01:21:53'),
(22, 167, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:21:53', '2026-03-07 01:21:53'),
(23, 166, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:22:34', '2026-03-07 01:22:34'),
(24, 166, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:22:34', '2026-03-07 01:22:34'),
(25, 181, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:23:12', '2026-03-07 01:23:12'),
(26, 181, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:23:12', '2026-03-07 01:23:12'),
(27, 163, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:24:08', '2026-03-07 01:24:08'),
(28, 163, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:24:08', '2026-03-07 01:24:08'),
(29, 45, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:24:53', '2026-03-07 01:24:53'),
(30, 45, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:24:53', '2026-03-07 01:24:53'),
(31, 157, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:25:28', '2026-03-07 01:25:28'),
(32, 157, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:25:28', '2026-03-07 01:25:28'),
(33, 185, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:26:36', '2026-03-07 01:26:36'),
(34, 185, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:26:36', '2026-03-07 01:26:36'),
(35, 127, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:31:25', '2026-03-07 01:31:25'),
(36, 127, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:31:25', '2026-03-07 01:31:25'),
(37, 188, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:32:15', '2026-03-07 01:32:15'),
(38, 188, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:32:15', '2026-03-07 01:32:15'),
(39, 36, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:33:08', '2026-03-07 01:33:08'),
(40, 36, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:33:08', '2026-03-07 01:33:08'),
(41, 29, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:33:48', '2026-03-07 01:33:48'),
(42, 29, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:33:48', '2026-03-07 01:33:48'),
(43, 165, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:34:34', '2026-03-07 01:34:34'),
(44, 165, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:34:34', '2026-03-07 01:34:34'),
(45, 66, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:39:10', '2026-03-07 01:39:10'),
(46, 66, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:39:10', '2026-03-07 01:39:10'),
(47, 26, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:40:43', '2026-03-07 01:40:43'),
(48, 26, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:40:43', '2026-03-07 01:40:43'),
(49, 171, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:41:30', '2026-03-07 01:41:30'),
(50, 171, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:41:30', '2026-03-07 01:41:30'),
(51, 63, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:42:10', '2026-03-07 01:42:10'),
(52, 63, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:42:10', '2026-03-07 01:42:10'),
(53, 53, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:43:01', '2026-03-07 01:43:01'),
(54, 53, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:43:01', '2026-03-07 01:43:01'),
(55, 52, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:43:42', '2026-03-07 01:43:42'),
(56, 52, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:43:42', '2026-03-07 01:43:42'),
(57, 341, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:44:22', '2026-03-07 01:44:22'),
(58, 341, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:44:22', '2026-03-07 01:44:22'),
(59, 130, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:45:36', '2026-03-07 01:45:36'),
(60, 130, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:45:36', '2026-03-07 01:45:36'),
(61, 191, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:46:38', '2026-03-07 01:46:38'),
(62, 191, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:46:38', '2026-03-07 01:46:38'),
(63, 46, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:47:14', '2026-03-07 01:47:14'),
(64, 46, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:47:14', '2026-03-07 01:47:14'),
(65, 187, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:47:54', '2026-03-07 01:47:54'),
(66, 187, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:47:54', '2026-03-07 01:47:54'),
(67, 124, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:48:28', '2026-03-07 01:48:28'),
(68, 124, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:48:28', '2026-03-07 01:48:28'),
(69, 164, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:49:27', '2026-03-07 01:49:27'),
(70, 164, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:49:27', '2026-03-07 01:49:27'),
(71, 49, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:50:14', '2026-03-07 01:50:14'),
(72, 49, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:50:14', '2026-03-07 01:50:14'),
(73, 193, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:50:48', '2026-03-07 01:50:48'),
(74, 193, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:50:48', '2026-03-07 01:50:48'),
(75, 78, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:51:27', '2026-03-07 01:51:27'),
(76, 78, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:51:27', '2026-03-07 01:51:27'),
(77, 48, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:52:05', '2026-03-07 01:52:05'),
(78, 48, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:52:05', '2026-03-07 01:52:05'),
(79, 62, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:52:41', '2026-03-07 01:52:41'),
(80, 62, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:52:41', '2026-03-07 01:52:41'),
(81, 25, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:53:21', '2026-03-07 01:53:21'),
(82, 25, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:53:21', '2026-03-07 01:53:21'),
(83, 24, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:53:56', '2026-03-07 01:53:56'),
(84, 24, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:53:56', '2026-03-07 01:53:56'),
(85, 190, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:54:34', '2026-03-07 01:54:34'),
(86, 190, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:54:34', '2026-03-07 01:54:34'),
(87, 177, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:55:19', '2026-03-07 01:55:19'),
(88, 177, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:55:19', '2026-03-07 01:55:19'),
(89, 183, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:56:14', '2026-03-07 01:56:14'),
(90, 183, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:56:14', '2026-03-07 01:56:14'),
(91, 186, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:56:50', '2026-03-07 01:56:50'),
(92, 186, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:56:50', '2026-03-07 01:56:50'),
(93, 129, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:57:24', '2026-03-07 01:57:24'),
(94, 129, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:57:24', '2026-03-07 01:57:24'),
(95, 182, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:58:22', '2026-03-07 01:58:22'),
(96, 182, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:58:22', '2026-03-07 01:58:22'),
(97, 115, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:58:55', '2026-03-07 01:58:55'),
(98, 115, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:58:55', '2026-03-07 01:58:55'),
(99, 192, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 01:59:43', '2026-03-07 01:59:43'),
(100, 192, 2, 500.00, '2025-2026', '2nd', '2026-03-07 01:59:43', '2026-03-07 01:59:43'),
(101, 345, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 04:23:22', '2026-03-07 04:23:22'),
(102, 345, 2, 500.00, '2025-2026', '2nd', '2026-03-07 04:23:22', '2026-03-07 04:23:22'),
(103, 349, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 04:24:04', '2026-03-07 04:24:04'),
(104, 349, 2, 500.00, '2025-2026', '2nd', '2026-03-07 04:24:04', '2026-03-07 04:24:04'),
(105, 348, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 04:24:32', '2026-03-07 04:24:32'),
(106, 348, 2, 500.00, '2025-2026', '2nd', '2026-03-07 04:24:32', '2026-03-07 04:24:32'),
(107, 346, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 04:25:13', '2026-03-07 04:25:13'),
(108, 346, 2, 500.00, '2025-2026', '2nd', '2026-03-07 04:25:13', '2026-03-07 04:25:13'),
(109, 347, 1, 1200.00, '2025-2026', '2nd', '2026-03-07 04:37:27', '2026-03-07 04:37:27'),
(110, 347, 2, 500.00, '2025-2026', '2nd', '2026-03-07 04:37:27', '2026-03-07 04:37:27');

-- --------------------------------------------------------

--
-- Table structure for table `student_grades`
--

CREATE TABLE `student_grades` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `quarter1_grade` decimal(5,2) DEFAULT NULL,
  `quarter2_grade` decimal(5,2) DEFAULT NULL,
  `quarter3_grade` decimal(5,2) DEFAULT NULL,
  `average_grade` decimal(5,2) DEFAULT NULL,
  `quarter4_grade` decimal(5,2) DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_grades`
--

INSERT INTO `student_grades` (`id`, `student_id`, `subject_id`, `enrollment_id`, `quarter1_grade`, `quarter2_grade`, `quarter3_grade`, `average_grade`, `quarter4_grade`, `remarks`, `created_at`, `updated_at`) VALUES
(1, 62, 43, 8, 0.00, 0.00, 0.00, 0.00, 0.00, '', '2026-03-12 22:49:31', '2026-03-12 22:49:31');

-- --------------------------------------------------------

--
-- Table structure for table `student_subjects`
--

CREATE TABLE `student_subjects` (
  `id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `enrollment_id` int(11) NOT NULL,
  `status` varchar(50) DEFAULT 'Enrolled',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `student_subjects`
--

INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `enrollment_id`, `status`, `created_at`, `updated_at`) VALUES
(9, 81, 45, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(10, 81, 44, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(11, 81, 41, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(12, 81, 42, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(13, 81, 43, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(14, 81, 39, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(15, 81, 40, 2, 'Enrolled', '2025-12-17 02:38:25', '2025-12-17 02:38:25'),
(23, 177, 45, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(24, 177, 44, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(25, 177, 41, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(26, 177, 42, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(27, 177, 43, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(28, 177, 39, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(29, 177, 40, 9, 'Enrolled', '2025-12-17 03:15:40', '2025-12-17 03:15:40'),
(30, 167, 45, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(31, 167, 44, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(32, 167, 41, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(33, 167, 42, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(34, 167, 43, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(35, 167, 39, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(36, 167, 40, 17, 'Enrolled', '2025-12-17 03:15:51', '2025-12-17 03:15:51'),
(37, 164, 45, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(38, 164, 44, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(39, 164, 41, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(40, 164, 42, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(41, 164, 43, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(42, 164, 39, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(43, 164, 40, 25, 'Enrolled', '2025-12-17 03:16:01', '2025-12-17 03:16:01'),
(44, 189, 45, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(45, 189, 44, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(46, 189, 41, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(47, 189, 42, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(48, 189, 43, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(49, 189, 39, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(50, 189, 40, 10, 'Enrolled', '2025-12-17 03:16:08', '2025-12-17 03:16:08'),
(51, 45, 45, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(52, 45, 44, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(53, 45, 41, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(54, 45, 42, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(55, 45, 43, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(56, 45, 39, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(57, 45, 40, 18, 'Enrolled', '2025-12-17 03:16:19', '2025-12-17 03:16:19'),
(58, 49, 45, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(59, 49, 44, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(60, 49, 41, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(61, 49, 42, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(62, 49, 43, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(63, 49, 39, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(64, 49, 40, 26, 'Enrolled', '2025-12-17 03:16:27', '2025-12-17 03:16:27'),
(65, 28, 45, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(66, 28, 44, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(67, 28, 41, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(68, 28, 42, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(69, 28, 43, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(70, 28, 39, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(71, 28, 40, 3, 'Enrolled', '2025-12-17 03:16:36', '2025-12-17 03:16:36'),
(72, 341, 45, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(73, 341, 44, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(74, 341, 41, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(75, 341, 42, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(76, 341, 43, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(77, 341, 39, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(78, 341, 40, 11, 'Enrolled', '2025-12-17 03:16:43', '2025-12-17 03:16:43'),
(79, 185, 45, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(80, 185, 44, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(81, 185, 41, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(82, 185, 42, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(83, 185, 43, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(84, 185, 39, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(85, 185, 40, 4, 'Enrolled', '2025-12-17 03:17:14', '2025-12-17 03:17:14'),
(86, 117, 45, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(87, 117, 44, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(88, 117, 41, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(89, 117, 42, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(90, 117, 43, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(91, 117, 39, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(92, 117, 40, 14, 'Enrolled', '2025-12-17 03:17:27', '2025-12-17 03:17:27'),
(93, 105, 45, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(94, 105, 44, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(95, 105, 41, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(96, 105, 42, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(97, 105, 43, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(98, 105, 39, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(99, 105, 40, 15, 'Enrolled', '2025-12-17 03:17:36', '2025-12-17 03:17:36'),
(100, 40, 45, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(101, 40, 44, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(102, 40, 41, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(103, 40, 42, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(104, 40, 43, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(105, 40, 39, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(106, 40, 40, 16, 'Enrolled', '2025-12-17 03:17:46', '2025-12-17 03:17:46'),
(107, 36, 45, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(108, 36, 44, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(109, 36, 41, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(110, 36, 42, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(111, 36, 43, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(112, 36, 39, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(113, 36, 40, 21, 'Enrolled', '2025-12-17 03:18:27', '2025-12-17 03:18:27'),
(114, 157, 45, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(115, 157, 44, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(116, 157, 41, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(117, 157, 42, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(118, 157, 43, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(119, 157, 39, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(120, 157, 40, 19, 'Enrolled', '2025-12-17 03:18:37', '2025-12-17 03:18:37'),
(121, 127, 45, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(122, 127, 44, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(123, 127, 41, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(124, 127, 42, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(125, 127, 43, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(126, 127, 39, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(127, 127, 40, 20, 'Enrolled', '2025-12-17 03:19:02', '2025-12-17 03:19:02'),
(128, 29, 45, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(129, 29, 44, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(130, 29, 41, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(131, 29, 42, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(132, 29, 43, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(133, 29, 39, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(134, 29, 40, 22, 'Enrolled', '2025-12-17 03:19:18', '2025-12-17 03:19:18'),
(135, 165, 45, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(136, 165, 44, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(137, 165, 41, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(138, 165, 42, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(139, 165, 43, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(140, 165, 39, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(141, 165, 40, 23, 'Enrolled', '2025-12-17 03:19:33', '2025-12-17 03:19:33'),
(142, 66, 45, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(143, 66, 44, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(144, 66, 41, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(145, 66, 42, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(146, 66, 43, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(147, 66, 39, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(148, 66, 40, 24, 'Enrolled', '2025-12-17 03:19:48', '2025-12-17 03:19:48'),
(149, 78, 45, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(150, 78, 44, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(151, 78, 41, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(152, 78, 42, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(153, 78, 43, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(154, 78, 39, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(155, 78, 40, 27, 'Enrolled', '2025-12-17 03:21:17', '2025-12-17 03:21:17'),
(156, 25, 45, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(157, 25, 44, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(158, 25, 41, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(159, 25, 42, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(160, 25, 43, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(161, 25, 39, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(162, 25, 40, 28, 'Enrolled', '2025-12-17 03:21:29', '2025-12-17 03:21:29'),
(163, 115, 45, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(164, 115, 44, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(165, 115, 41, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(166, 115, 42, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(167, 115, 43, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(168, 115, 39, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(169, 115, 40, 29, 'Enrolled', '2025-12-17 03:21:43', '2025-12-17 03:21:43'),
(170, 48, 45, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(171, 48, 44, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(172, 48, 41, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(173, 48, 42, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(174, 48, 43, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(175, 48, 39, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(176, 48, 40, 30, 'Enrolled', '2025-12-17 03:21:55', '2025-12-17 03:21:55'),
(177, 129, 45, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(178, 129, 44, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(179, 129, 41, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(180, 129, 42, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(181, 129, 43, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(182, 129, 39, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(183, 129, 40, 31, 'Enrolled', '2025-12-17 03:22:04', '2025-12-17 03:22:04'),
(184, 46, 45, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(185, 46, 44, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(186, 46, 41, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(187, 46, 42, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(188, 46, 43, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(189, 46, 39, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(190, 46, 40, 32, 'Enrolled', '2025-12-17 03:23:10', '2025-12-17 03:23:10'),
(191, 191, 45, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(192, 191, 44, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(193, 191, 41, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(194, 191, 42, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(195, 191, 43, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(196, 191, 39, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(197, 191, 40, 6, 'Enrolled', '2025-12-17 03:24:49', '2025-12-17 03:24:49'),
(198, 187, 45, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(199, 187, 44, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(200, 187, 41, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(201, 187, 42, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(202, 187, 43, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(203, 187, 39, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(204, 187, 40, 7, 'Enrolled', '2025-12-17 03:24:59', '2025-12-17 03:24:59'),
(205, 62, 45, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(206, 62, 44, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(207, 62, 41, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(208, 62, 42, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(209, 62, 43, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(210, 62, 39, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(211, 62, 40, 8, 'Enrolled', '2025-12-17 03:25:22', '2025-12-17 03:25:22'),
(212, 190, 45, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(213, 190, 44, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(214, 190, 41, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(215, 190, 42, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(216, 190, 43, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(217, 190, 39, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(218, 190, 40, 12, 'Enrolled', '2025-12-17 03:25:39', '2025-12-17 03:25:39'),
(219, 182, 45, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(220, 182, 44, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(221, 182, 41, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(222, 182, 42, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(223, 182, 43, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(224, 182, 39, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(225, 182, 40, 13, 'Enrolled', '2025-12-17 03:25:51', '2025-12-17 03:25:51'),
(226, 4, 45, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(227, 4, 44, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(228, 4, 41, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(229, 4, 42, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(230, 4, 43, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(231, 4, 39, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(232, 4, 40, 33, 'Enrolled', '2025-12-17 03:33:53', '2025-12-17 03:33:53'),
(233, 14, 37, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(234, 14, 32, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(235, 14, 36, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(236, 14, 33, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(237, 14, 34, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(238, 14, 35, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(239, 14, 38, 71, 'Enrolled', '2025-12-21 04:44:23', '2025-12-21 04:44:23'),
(240, 103, 20, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(241, 103, 21, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(242, 103, 22, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(243, 103, 18, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(244, 103, 19, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(245, 103, 24, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(246, 103, 23, 108, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(247, 279, 20, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(248, 279, 21, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(249, 279, 22, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(250, 279, 18, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(251, 279, 19, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(252, 279, 24, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(253, 279, 23, 109, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(254, 139, 20, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(255, 139, 21, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(256, 139, 22, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(257, 139, 18, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(258, 139, 19, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(259, 139, 24, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(260, 139, 23, 113, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(261, 82, 20, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(262, 82, 21, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(263, 82, 22, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(264, 82, 18, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(265, 82, 19, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(266, 82, 24, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(267, 82, 23, 114, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(268, 109, 20, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(269, 109, 21, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(270, 109, 22, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(271, 109, 18, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(272, 109, 19, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(273, 109, 24, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(274, 109, 23, 115, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(275, 269, 20, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(276, 269, 21, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(277, 269, 22, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(278, 269, 18, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(279, 269, 19, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(280, 269, 24, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(281, 269, 23, 116, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(282, 97, 20, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(283, 97, 21, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(284, 97, 22, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(285, 97, 18, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(286, 97, 19, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(287, 97, 24, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(288, 97, 23, 117, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(289, 85, 20, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(290, 85, 21, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(291, 85, 22, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(292, 85, 18, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(293, 85, 19, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(294, 85, 24, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(295, 85, 23, 118, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(296, 281, 20, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(297, 281, 21, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(298, 281, 22, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(299, 281, 18, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(300, 281, 19, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(301, 281, 24, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(302, 281, 23, 120, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(303, 277, 20, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(304, 277, 21, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(305, 277, 22, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(306, 277, 18, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(307, 277, 19, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(308, 277, 24, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(309, 277, 23, 121, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(310, 295, 20, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(311, 295, 21, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(312, 295, 22, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(313, 295, 18, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(314, 295, 19, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(315, 295, 24, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(316, 295, 23, 122, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(317, 106, 20, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(318, 106, 21, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(319, 106, 22, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(320, 106, 18, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(321, 106, 19, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(322, 106, 24, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(323, 106, 23, 123, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(324, 99, 20, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(325, 99, 21, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(326, 99, 22, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(327, 99, 18, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(328, 99, 19, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(329, 99, 24, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(330, 99, 23, 124, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(331, 114, 20, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(332, 114, 21, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(333, 114, 22, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(334, 114, 18, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(335, 114, 19, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(336, 114, 24, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(337, 114, 23, 125, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(338, 280, 20, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(339, 280, 21, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(340, 280, 22, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(341, 280, 18, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(342, 280, 19, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(343, 280, 24, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(344, 280, 23, 126, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(345, 268, 20, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(346, 268, 21, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(347, 268, 22, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(348, 268, 18, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(349, 268, 19, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(350, 268, 24, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(351, 268, 23, 127, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(352, 159, 20, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(353, 159, 21, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(354, 159, 22, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(355, 159, 18, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(356, 159, 19, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(357, 159, 24, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(358, 159, 23, 128, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(359, 94, 20, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(360, 94, 21, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(361, 94, 22, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(362, 94, 18, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(363, 94, 19, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(364, 94, 24, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(365, 94, 23, 129, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(366, 86, 20, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(367, 86, 21, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(368, 86, 22, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(369, 86, 18, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(370, 86, 19, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(371, 86, 24, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(372, 86, 23, 132, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(373, 113, 20, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(374, 113, 21, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(375, 113, 22, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(376, 113, 18, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(377, 113, 19, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(378, 113, 24, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(379, 113, 23, 133, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(380, 92, 20, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(381, 92, 21, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(382, 92, 22, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(383, 92, 18, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(384, 92, 19, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(385, 92, 24, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(386, 92, 23, 134, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(387, 278, 20, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(388, 278, 21, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(389, 278, 22, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(390, 278, 18, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(391, 278, 19, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(392, 278, 24, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(393, 278, 23, 137, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(394, 108, 20, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(395, 108, 21, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(396, 108, 22, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(397, 108, 18, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(398, 108, 19, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(399, 108, 24, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(400, 108, 23, 138, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(401, 146, 20, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(402, 146, 21, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(403, 146, 22, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(404, 146, 18, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(405, 146, 19, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(406, 146, 24, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(407, 146, 23, 139, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(408, 90, 20, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(409, 90, 21, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(410, 90, 22, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(411, 90, 18, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(412, 90, 19, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(413, 90, 24, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(414, 90, 23, 144, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(415, 118, 20, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(416, 118, 21, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(417, 118, 22, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(418, 118, 18, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(419, 118, 19, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(420, 118, 24, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(421, 118, 23, 148, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(422, 128, 20, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(423, 128, 21, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(424, 128, 22, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(425, 128, 18, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(426, 128, 19, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(427, 128, 24, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(428, 128, 23, 149, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(429, 88, 20, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(430, 88, 21, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(431, 88, 22, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(432, 88, 18, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(433, 88, 19, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(434, 88, 24, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(435, 88, 23, 152, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(436, 107, 20, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(437, 107, 21, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(438, 107, 22, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(439, 107, 18, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(440, 107, 19, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(441, 107, 24, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(442, 107, 23, 154, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(443, 147, 20, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(444, 147, 21, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(445, 147, 22, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(446, 147, 18, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(447, 147, 19, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(448, 147, 24, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(449, 147, 23, 155, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(450, 79, 20, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(451, 79, 21, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(452, 79, 22, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(453, 79, 18, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(454, 79, 19, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(455, 79, 24, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(456, 79, 23, 156, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(457, 96, 20, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(458, 96, 21, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(459, 96, 22, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(460, 96, 18, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(461, 96, 19, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(462, 96, 24, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(463, 96, 23, 158, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(464, 290, 20, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(465, 290, 21, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(466, 290, 22, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(467, 290, 18, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(468, 290, 19, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(469, 290, 24, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(470, 290, 23, 160, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(471, 266, 20, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(472, 266, 21, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(473, 266, 22, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(474, 266, 18, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(475, 266, 19, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(476, 266, 24, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(477, 266, 23, 161, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(478, 95, 20, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(479, 95, 21, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(480, 95, 22, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(481, 95, 18, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(482, 95, 19, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(483, 95, 24, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(484, 95, 23, 162, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(485, 303, 20, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(486, 303, 21, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(487, 303, 22, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(488, 303, 18, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(489, 303, 19, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(490, 303, 24, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(491, 303, 23, 164, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(492, 83, 20, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(493, 83, 21, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(494, 83, 22, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(495, 83, 18, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(496, 83, 19, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(497, 83, 24, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(498, 83, 23, 165, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(499, 144, 20, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(500, 144, 21, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(501, 144, 22, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(502, 144, 18, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(503, 144, 19, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(504, 144, 24, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(505, 144, 23, 166, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(506, 100, 20, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(507, 100, 21, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(508, 100, 22, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(509, 100, 18, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(510, 100, 19, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(511, 100, 24, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(512, 100, 23, 167, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(513, 93, 20, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(514, 93, 21, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(515, 93, 22, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(516, 93, 18, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(517, 93, 19, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(518, 93, 24, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(519, 93, 23, 168, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(520, 112, 20, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(521, 112, 21, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(522, 112, 22, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(523, 112, 18, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(524, 112, 19, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(525, 112, 24, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(526, 112, 23, 169, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(527, 110, 20, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(528, 110, 21, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(529, 110, 22, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(530, 110, 18, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(531, 110, 19, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(532, 110, 24, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(533, 110, 23, 170, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(534, 300, 20, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(535, 300, 21, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(536, 300, 22, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(537, 300, 18, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(538, 300, 19, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(539, 300, 24, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(540, 300, 23, 171, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(541, 282, 20, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(542, 282, 21, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(543, 282, 22, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(544, 282, 18, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(545, 282, 19, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(546, 282, 24, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(547, 282, 23, 173, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(548, 174, 20, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(549, 174, 21, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(550, 174, 22, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(551, 174, 18, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(552, 174, 19, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(553, 174, 24, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(554, 174, 23, 174, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(555, 286, 20, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(556, 286, 21, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(557, 286, 22, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(558, 286, 18, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(559, 286, 19, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(560, 286, 24, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(561, 286, 23, 175, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(562, 176, 20, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(563, 176, 21, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(564, 176, 22, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(565, 176, 18, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(566, 176, 19, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(567, 176, 24, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(568, 176, 23, 176, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(569, 102, 20, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(570, 102, 21, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(571, 102, 22, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(572, 102, 18, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(573, 102, 19, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(574, 102, 24, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(575, 102, 23, 177, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(576, 285, 20, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(577, 285, 21, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(578, 285, 22, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(579, 285, 18, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(580, 285, 19, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(581, 285, 24, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(582, 285, 23, 178, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(583, 168, 20, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(584, 168, 21, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(585, 168, 22, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(586, 168, 18, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(587, 168, 19, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(588, 168, 24, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(589, 168, 23, 179, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(590, 135, 20, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(591, 135, 21, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(592, 135, 22, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(593, 135, 18, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(594, 135, 19, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(595, 135, 24, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(596, 135, 23, 180, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(597, 302, 20, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(598, 302, 21, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(599, 302, 22, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(600, 302, 18, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(601, 302, 19, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(602, 302, 24, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(603, 302, 23, 182, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(604, 291, 20, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(605, 291, 21, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(606, 291, 22, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(607, 291, 18, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(608, 291, 19, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(609, 291, 24, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(610, 291, 23, 183, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(611, 294, 20, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(612, 294, 21, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(613, 294, 22, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(614, 294, 18, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(615, 294, 19, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(616, 294, 24, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(617, 294, 23, 184, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(618, 111, 20, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(619, 111, 21, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(620, 111, 22, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(621, 111, 18, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(622, 111, 19, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(623, 111, 24, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(624, 111, 23, 185, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(625, 288, 20, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(626, 288, 21, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(627, 288, 22, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(628, 288, 18, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(629, 288, 19, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(630, 288, 24, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(631, 288, 23, 186, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(632, 304, 20, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(633, 304, 21, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(634, 304, 22, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(635, 304, 18, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(636, 304, 19, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(637, 304, 24, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(638, 304, 23, 187, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(639, 293, 20, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(640, 293, 21, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(641, 293, 22, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(642, 293, 18, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(643, 293, 19, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(644, 293, 24, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(645, 293, 23, 189, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(646, 161, 20, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(647, 161, 21, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(648, 161, 22, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(649, 161, 18, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(650, 161, 19, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(651, 161, 24, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(652, 161, 23, 190, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(653, 84, 20, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(654, 84, 21, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(655, 84, 22, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(656, 84, 18, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(657, 84, 19, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(658, 84, 24, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(659, 84, 23, 191, 'Enrolled', '2025-12-29 00:32:48', '2025-12-29 00:32:48'),
(660, 44, 37, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(661, 44, 32, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(662, 44, 36, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(663, 44, 33, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(664, 44, 34, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(665, 44, 35, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(666, 44, 38, 34, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(667, 77, 37, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(668, 77, 32, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(669, 77, 36, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(670, 77, 33, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17');
INSERT INTO `student_subjects` (`id`, `student_id`, `subject_id`, `enrollment_id`, `status`, `created_at`, `updated_at`) VALUES
(671, 77, 34, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(672, 77, 35, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(673, 77, 38, 36, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(674, 33, 37, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(675, 33, 32, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(676, 33, 36, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(677, 33, 33, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(678, 33, 34, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(679, 33, 35, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(680, 33, 38, 37, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(681, 200, 37, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(682, 200, 32, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(683, 200, 36, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(684, 200, 33, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(685, 200, 34, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(686, 200, 35, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(687, 200, 38, 38, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(688, 27, 37, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(689, 27, 32, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(690, 27, 36, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(691, 27, 33, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(692, 27, 34, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(693, 27, 35, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(694, 27, 38, 39, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(695, 255, 37, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(696, 255, 32, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(697, 255, 36, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(698, 255, 33, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(699, 255, 34, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(700, 255, 35, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(701, 255, 38, 40, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(702, 236, 37, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(703, 236, 32, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(704, 236, 36, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(705, 236, 33, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(706, 236, 34, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(707, 236, 35, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(708, 236, 38, 41, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(709, 221, 37, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(710, 221, 32, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(711, 221, 36, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(712, 221, 33, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(713, 221, 34, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(714, 221, 35, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(715, 221, 38, 42, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(716, 240, 37, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(717, 240, 32, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(718, 240, 36, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(719, 240, 33, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(720, 240, 34, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(721, 240, 35, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(722, 240, 38, 43, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(723, 43, 37, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(724, 43, 32, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(725, 43, 36, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(726, 43, 33, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(727, 43, 34, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(728, 43, 35, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(729, 43, 38, 44, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(730, 42, 37, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(731, 42, 32, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(732, 42, 36, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(733, 42, 33, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(734, 42, 34, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(735, 42, 35, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(736, 42, 38, 45, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(737, 263, 37, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(738, 263, 32, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(739, 263, 36, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(740, 263, 33, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(741, 263, 34, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(742, 263, 35, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(743, 263, 38, 46, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(744, 202, 37, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(745, 202, 32, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(746, 202, 36, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(747, 202, 33, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(748, 202, 34, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(749, 202, 35, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(750, 202, 38, 47, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(751, 233, 37, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(752, 233, 32, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(753, 233, 36, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(754, 233, 33, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(755, 233, 34, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(756, 233, 35, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(757, 233, 38, 48, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(758, 20, 37, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(759, 20, 32, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(760, 20, 36, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(761, 20, 33, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(762, 20, 34, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(763, 20, 35, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(764, 20, 38, 49, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(765, 225, 37, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(766, 225, 32, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(767, 225, 36, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(768, 225, 33, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(769, 225, 34, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(770, 225, 35, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(771, 225, 38, 50, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(772, 18, 37, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(773, 18, 32, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(774, 18, 36, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(775, 18, 33, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(776, 18, 34, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(777, 18, 35, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(778, 18, 38, 51, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(779, 219, 37, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(780, 219, 32, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(781, 219, 36, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(782, 219, 33, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(783, 219, 34, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(784, 219, 35, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(785, 219, 38, 52, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(786, 30, 37, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(787, 30, 32, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(788, 30, 36, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(789, 30, 33, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(790, 30, 34, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(791, 30, 35, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(792, 30, 38, 53, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(793, 252, 37, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(794, 252, 32, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(795, 252, 36, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(796, 252, 33, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(797, 252, 34, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(798, 252, 35, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(799, 252, 38, 54, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(800, 307, 37, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(801, 307, 32, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(802, 307, 36, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(803, 307, 33, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(804, 307, 34, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(805, 307, 35, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(806, 307, 38, 55, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(807, 273, 37, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(808, 273, 32, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(809, 273, 36, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(810, 273, 33, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(811, 273, 34, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(812, 273, 35, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(813, 273, 38, 57, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(814, 64, 37, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(815, 64, 32, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(816, 64, 36, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(817, 64, 33, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(818, 64, 34, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(819, 64, 35, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(820, 64, 38, 58, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(821, 306, 37, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(822, 306, 32, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(823, 306, 36, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(824, 306, 33, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(825, 306, 34, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(826, 306, 35, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(827, 306, 38, 59, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(828, 222, 37, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(829, 222, 32, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(830, 222, 36, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(831, 222, 33, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(832, 222, 34, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(833, 222, 35, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(834, 222, 38, 60, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(835, 265, 37, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(836, 265, 32, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(837, 265, 36, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(838, 265, 33, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(839, 265, 34, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(840, 265, 35, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(841, 265, 38, 61, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(842, 262, 37, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(843, 262, 32, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(844, 262, 36, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(845, 262, 33, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(846, 262, 34, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(847, 262, 35, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(848, 262, 38, 62, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(849, 207, 37, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(850, 207, 32, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(851, 207, 36, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(852, 207, 33, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(853, 207, 34, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(854, 207, 35, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(855, 207, 38, 63, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(856, 211, 37, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(857, 211, 32, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(858, 211, 36, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(859, 211, 33, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(860, 211, 34, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(861, 211, 35, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(862, 211, 38, 64, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(863, 218, 37, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(864, 218, 32, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(865, 218, 36, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(866, 218, 33, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(867, 218, 34, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(868, 218, 35, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(869, 218, 38, 65, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(870, 12, 37, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(871, 12, 32, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(872, 12, 36, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(873, 12, 33, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(874, 12, 34, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(875, 12, 35, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(876, 12, 38, 66, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(877, 253, 37, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(878, 253, 32, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(879, 253, 36, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(880, 253, 33, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(881, 253, 34, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(882, 253, 35, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(883, 253, 38, 67, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(884, 213, 37, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(885, 213, 32, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(886, 213, 36, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(887, 213, 33, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(888, 213, 34, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(889, 213, 35, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(890, 213, 38, 69, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(891, 212, 37, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(892, 212, 32, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(893, 212, 36, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(894, 212, 33, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(895, 212, 34, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(896, 212, 35, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(897, 212, 38, 70, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(898, 204, 37, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(899, 204, 32, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(900, 204, 36, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(901, 204, 33, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(902, 204, 34, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(903, 204, 35, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(904, 204, 38, 72, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(905, 201, 37, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(906, 201, 32, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(907, 201, 36, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(908, 201, 33, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(909, 201, 34, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(910, 201, 35, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(911, 201, 38, 73, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(912, 224, 37, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(913, 224, 32, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(914, 224, 36, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(915, 224, 33, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(916, 224, 34, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(917, 224, 35, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(918, 224, 38, 74, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(919, 19, 37, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(920, 19, 32, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(921, 19, 36, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(922, 19, 33, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(923, 19, 34, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(924, 19, 35, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(925, 19, 38, 75, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(926, 206, 37, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(927, 206, 32, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(928, 206, 36, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(929, 206, 33, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(930, 206, 34, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(931, 206, 35, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(932, 206, 38, 76, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(933, 13, 37, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(934, 13, 32, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(935, 13, 36, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(936, 13, 33, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(937, 13, 34, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(938, 13, 35, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(939, 13, 38, 77, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(940, 257, 37, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(941, 257, 32, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(942, 257, 36, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(943, 257, 33, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(944, 257, 34, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(945, 257, 35, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(946, 257, 38, 78, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(947, 47, 37, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(948, 47, 32, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(949, 47, 36, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(950, 47, 33, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(951, 47, 34, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(952, 47, 35, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(953, 47, 38, 79, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(954, 197, 37, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(955, 197, 32, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(956, 197, 36, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(957, 197, 33, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(958, 197, 34, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(959, 197, 35, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(960, 197, 38, 80, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(961, 196, 37, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(962, 196, 32, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(963, 196, 36, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(964, 196, 33, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(965, 196, 34, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(966, 196, 35, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(967, 196, 38, 81, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(968, 195, 37, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(969, 195, 32, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(970, 195, 36, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(971, 195, 33, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(972, 195, 34, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(973, 195, 35, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(974, 195, 38, 82, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(975, 31, 37, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(976, 31, 32, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(977, 31, 36, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(978, 31, 33, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(979, 31, 34, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(980, 31, 35, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(981, 31, 38, 83, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(982, 37, 37, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(983, 37, 32, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(984, 37, 36, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(985, 37, 33, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(986, 37, 34, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(987, 37, 35, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(988, 37, 38, 84, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(989, 15, 37, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(990, 15, 32, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(991, 15, 36, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(992, 15, 33, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(993, 15, 34, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(994, 15, 35, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(995, 15, 38, 85, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(996, 205, 37, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(997, 205, 32, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(998, 205, 36, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(999, 205, 33, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1000, 205, 34, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1001, 205, 35, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1002, 205, 38, 86, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1003, 223, 37, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1004, 223, 32, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1005, 223, 36, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1006, 223, 33, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1007, 223, 34, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1008, 223, 35, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1009, 223, 38, 87, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1010, 256, 37, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1011, 256, 32, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1012, 256, 36, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1013, 256, 33, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1014, 256, 34, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1015, 256, 35, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1016, 256, 38, 88, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1017, 214, 37, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1018, 214, 32, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1019, 214, 36, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1020, 214, 33, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1021, 214, 34, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1022, 214, 35, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1023, 214, 38, 89, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1024, 254, 37, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1025, 254, 32, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1026, 254, 36, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1027, 254, 33, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1028, 254, 34, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1029, 254, 35, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1030, 254, 38, 90, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1031, 38, 37, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1032, 38, 32, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1033, 38, 36, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1034, 38, 33, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1035, 38, 34, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1036, 38, 35, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1037, 38, 38, 91, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1038, 203, 37, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1039, 203, 32, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1040, 203, 36, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1041, 203, 33, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1042, 203, 34, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1043, 203, 35, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1044, 203, 38, 92, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1045, 261, 37, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1046, 261, 32, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1047, 261, 36, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1048, 261, 33, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1049, 261, 34, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1050, 261, 35, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1051, 261, 38, 93, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1052, 199, 37, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1053, 199, 32, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1054, 199, 36, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1055, 199, 33, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1056, 199, 34, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1057, 199, 35, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1058, 199, 38, 94, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1059, 259, 37, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1060, 259, 32, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1061, 259, 36, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1062, 259, 33, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1063, 259, 34, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1064, 259, 35, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1065, 259, 38, 95, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1066, 241, 37, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1067, 241, 32, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1068, 241, 36, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1069, 241, 33, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1070, 241, 34, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1071, 241, 35, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1072, 241, 38, 96, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1073, 32, 37, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1074, 32, 32, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1075, 32, 36, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1076, 32, 33, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1077, 32, 34, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1078, 32, 35, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1079, 32, 38, 97, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1080, 23, 37, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1081, 23, 32, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1082, 23, 36, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1083, 23, 33, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1084, 23, 34, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1085, 23, 35, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1086, 23, 38, 98, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1087, 209, 37, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1088, 209, 32, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1089, 209, 36, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1090, 209, 33, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1091, 209, 34, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1092, 209, 35, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1093, 209, 38, 99, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1094, 17, 37, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1095, 17, 32, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1096, 17, 36, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1097, 17, 33, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1098, 17, 34, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1099, 17, 35, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1100, 17, 38, 100, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1101, 308, 37, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1102, 308, 32, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1103, 308, 36, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1104, 308, 33, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1105, 308, 34, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1106, 308, 35, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1107, 308, 38, 101, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1108, 50, 37, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1109, 50, 32, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1110, 50, 36, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1111, 50, 33, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1112, 50, 34, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1113, 50, 35, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1114, 50, 38, 102, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1115, 65, 37, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1116, 65, 32, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1117, 65, 36, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1118, 65, 33, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1119, 65, 34, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1120, 65, 35, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1121, 65, 38, 103, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1122, 217, 37, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1123, 217, 32, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1124, 217, 36, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1125, 217, 33, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1126, 217, 34, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1127, 217, 35, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1128, 217, 38, 105, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1129, 208, 37, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1130, 208, 32, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1131, 208, 36, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1132, 208, 33, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1133, 208, 34, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1134, 208, 35, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1135, 208, 38, 106, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1136, 22, 37, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1137, 22, 32, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1138, 22, 36, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1139, 22, 33, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1140, 22, 34, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1141, 22, 35, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1142, 22, 38, 107, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1150, 296, 37, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1151, 296, 32, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1152, 296, 36, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1153, 296, 33, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1154, 296, 34, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1155, 296, 35, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1156, 296, 38, 119, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1164, 104, 37, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1165, 104, 32, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1166, 104, 36, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1167, 104, 33, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1168, 104, 34, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1169, 104, 35, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1170, 104, 38, 145, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1171, 292, 37, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1172, 292, 32, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1173, 292, 36, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1174, 292, 33, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1175, 292, 34, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1176, 292, 35, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1177, 292, 38, 159, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1178, 149, 37, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1179, 149, 32, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1180, 149, 36, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1181, 149, 33, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1182, 149, 34, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1183, 149, 35, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1184, 149, 38, 188, 'Enrolled', '2025-12-29 00:33:17', '2025-12-29 00:33:17'),
(1185, 297, 20, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1186, 297, 21, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1187, 297, 22, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1188, 297, 18, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1189, 297, 19, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1190, 297, 24, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1191, 297, 23, 192, 'Enrolled', '2025-12-29 02:19:00', '2025-12-29 02:19:00'),
(1192, 98, 20, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1193, 98, 21, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1194, 98, 22, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1195, 98, 18, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1196, 98, 19, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1197, 98, 24, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1198, 98, 23, 110, 'Enrolled', '2025-12-29 03:32:40', '2025-12-29 03:32:40'),
(1199, 346, 45, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1200, 346, 44, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1201, 346, 41, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1202, 346, 42, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1203, 346, 43, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1204, 346, 39, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1205, 346, 40, 209, 'Enrolled', '2026-03-07 04:33:51', '2026-03-07 04:33:51'),
(1206, 345, 45, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1207, 345, 44, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1208, 345, 41, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1209, 345, 42, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1210, 345, 43, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1211, 345, 39, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1212, 345, 40, 208, 'Enrolled', '2026-03-07 04:34:03', '2026-03-07 04:34:03'),
(1213, 348, 45, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1214, 348, 44, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1215, 348, 41, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1216, 348, 42, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1217, 348, 43, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1218, 348, 39, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1219, 348, 40, 206, 'Enrolled', '2026-03-07 04:34:17', '2026-03-07 04:34:17'),
(1220, 349, 45, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1221, 349, 44, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1222, 349, 41, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1223, 349, 42, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1224, 349, 43, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1225, 349, 39, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1226, 349, 40, 207, 'Enrolled', '2026-03-07 04:34:39', '2026-03-07 04:34:39'),
(1227, 347, 45, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1228, 347, 44, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1229, 347, 41, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1230, 347, 42, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1231, 347, 43, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1232, 347, 39, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1233, 347, 40, 210, 'Enrolled', '2026-03-07 04:35:14', '2026-03-07 04:35:14'),
(1234, 178, 45, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1235, 178, 44, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1236, 178, 41, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1237, 178, 42, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1238, 178, 43, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1239, 178, 39, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41'),
(1240, 178, 40, 211, 'Enrolled', '2026-03-13 01:34:41', '2026-03-13 01:34:41');

-- --------------------------------------------------------

--
-- Table structure for table `subjects`
--

CREATE TABLE `subjects` (
  `id` int(11) NOT NULL,
  `subject_code` varchar(20) NOT NULL,
  `subject_description` text NOT NULL,
  `unit` int(2) NOT NULL,
  `pre_requisite` varchar(20) DEFAULT NULL,
  `course_id` int(11) NOT NULL,
  `academic_year` varchar(9) NOT NULL,
  `semester` enum('1st','2nd','Summer') NOT NULL,
  `year_level` enum('1st Year','2nd Year','3rd Year','4th Year') NOT NULL,
  `effective_year` varchar(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subjects`
--

INSERT INTO `subjects` (`id`, `subject_code`, `subject_description`, `unit`, `pre_requisite`, `course_id`, `academic_year`, `semester`, `year_level`, `effective_year`) VALUES
(1, 'RE 1', 'Basic Doctrine and Sacraments in the Teaching of St. Augustine', 2, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(3, 'ITC 111', 'Introduction to Computing', 3, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(4, 'ITC 112', 'Computer Programming 1', 3, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(5, 'GE 1', 'Understanding the Self', 3, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(6, 'GE 2', 'Readings in the Philippine History', 3, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(7, 'GE 3', 'The Contemporary World', 3, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(8, 'PATHFit 1', 'Movement Competency Training', 2, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(9, 'NSTP 1', 'National Service Training Program', 2, '0', 2, '2023-2024', '1st', '1st Year', '2023'),
(10, 'RE 3', 'Peace Education and Christian  Morality in the Light of St. Augustine', 2, '0', 1, '2023-2024', '1st', '3rd Year', '2023'),
(12, 'ITC 311', 'Applications Development and Emerging Technology', 3, 'ITM221', 1, '2023-2024', '1st', '3rd Year', '2023'),
(13, 'ITM311', 'Fundamentals of Database Systems', 3, 'ITM221', 1, '2023-2024', '1st', '3rd Year', '2023'),
(14, 'ITM312', 'System Integration and Architecture', 3, 'ITM221 | ITM222', 1, '2023-2024', '1st', '3rd Year', '2023'),
(15, 'ITM313', 'Networking 2', 3, 'ITM222', 1, '2023-2024', '1st', '3rd Year', '2023'),
(16, 'GEElective2', 'Reading Visual Art', 3, 'None', 1, '2023-2024', '1st', '3rd Year', '2023'),
(17, 'ACCT', 'Fundamentals of Accounting', 6, 'None', 1, '2023-2024', '1st', '3rd Year', '2023'),
(18, 'ITC 121', 'Computer Programming 2', 3, 'ITC112', 2, '2023-2024', '2nd', '1st Year', '2023'),
(19, 'ITM 121', 'Introduction to Human-Computer Interaction', 3, 'ITC111', 2, '2023-2024', '2nd', '1st Year', '2023'),
(20, 'GE 4', 'Mathematics in the Modern World', 3, '', 2, '2023-2024', '2nd', '1st Year', '2023'),
(21, 'GE 5', 'Purposive Communication', 3, '', 2, '2023-2024', '2nd', '1st Year', '2023'),
(22, 'GE 6', 'Art Appreciation', 3, '', 2, '2023-2024', '2nd', '1st Year', '2023'),
(23, 'PATHFit 2', 'Exercise Based Fitness Activities', 2, 'PATHFit 1', 2, '2023-2024', '2nd', '1st Year', '2023'),
(24, 'NSTP 2', 'National Service Training Program', 3, 'NSTP 1', 2, '2023-2024', '2nd', '1st Year', '2023'),
(25, 'RE 2', 'Sacred Scriptures in the Works of Saint Augustine', 2, '', 3, '2023-2024', '1st', '2nd Year', '2023'),
(26, 'ITC 211', 'Information Management', 3, 'ITM121', 3, '2023-2024', '1st', '2nd Year', '2023'),
(27, 'ITE211', 'Platform Technologies', 3, 'ITM121', 3, '2023-2024', '1st', '2nd Year', '2023'),
(28, 'ITE 212', 'Object-Oriented Programming', 3, 'ITC121', 3, '2023-2024', '1st', '2nd Year', '2023'),
(29, 'GE7', 'Science. Technology and Society', 3, '', 3, '2023-2024', '1st', '2nd Year', '2023'),
(30, 'GEElective1', 'Living in the IT Era', 3, '', 3, '2023-2024', '1st', '2nd Year', '2023'),
(31, 'PATHFit3', 'Dance', 2, 'PATHFit2', 3, '2023-2024', '1st', '2nd Year', '2023'),
(32, 'ITC221', 'Data Structures and Algorithms', 3, 'ITC111', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(33, 'ITM221', 'Integrative Programming and Technologies', 3, 'ITE212', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(34, 'ITM222', 'Networking 1', 3, 'ITC211', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(35, 'ITM223', 'Discrete Mathematics', 3, 'ITC112', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(36, 'ITE221', 'Multimedia', 3, 'ITM121', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(37, 'GE8', 'Ethics', 3, '', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(38, 'PATHFit4', 'Sports', 2, 'PATHFit3', 3, '2023-2024', '2nd', '2nd Year', '2023'),
(39, 'ITM321', 'Information Assurance and Security 1', 3, 'ITC211', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(40, 'ITM322', 'Quantitative Methods', 3, '', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(41, 'ITE321', 'Web Systems and Technologies', 3, 'ITM313', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(42, 'ITE322', 'Technopreneurship', 3, 'ACCT', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(43, 'ITE323', 'Systems Analysis and Design', 3, 'ITM311', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(44, 'GE9', 'Rizal\'s Life and Works', 3, '', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(45, 'GE10', 'Indigenous People Education', 3, '', 1, '2023-2024', '2nd', '3rd Year', '2023'),
(46, 'ITM 3S1', 'Practicum', 6, '80% of required Subj', 3, '2023-2024', 'Summer', '3rd Year', '2023'),
(47, 'ITE 3S1', 'ITElective 1', 3, '', 3, '2023-2024', 'Summer', '3rd Year', '2023'),
(48, 'RE 4', 'Augustinian Recollect Spiritual Exercise (ARSE) & Evangelization', 2, '', 4, '2023-2024', '1st', '4th Year', '2023'),
(49, 'ITM411', 'Capstone 1', 3, 'ITM322 | ITE 323', 4, '2023-2024', '1st', '4th Year', '2023'),
(50, 'ITM412', 'Information Assurance and Security 2', 3, 'ITM321', 4, '2023-2024', '1st', '4th Year', '2023'),
(51, 'ITE411', 'IT Elective2', 3, '', 4, '2023-2024', '1st', '4th Year', '2023'),
(52, 'ITE412', 'IT Elective 3', 3, '', 4, '2023-2024', '1st', '4th Year', '2023'),
(53, 'ITM421', 'Capstone 2', 3, 'ITM 411', 4, '2023-2024', '2nd', '4th Year', '2023'),
(54, 'ITM422', 'System Administration and Maintenance', 3, 'ITM321', 4, '2023-2024', '2nd', '4th Year', '2023'),
(55, 'ITM423', 'Social and Professional Issues', 3, '', 4, '2023-2024', '2nd', '4th Year', '2023'),
(57, 'ITM411', 'Capstone1', 3, 'ITM322 | ITE323', 4, '2018-2019', '1st', '4th Year', '2018'),
(58, 'ITM412', 'Information Assurance and Security 2', 3, 'ITM321', 4, '2018-2019', '1st', '4th Year', '2018'),
(59, 'ITE411', 'IT Elective2', 3, '', 4, '2018-2019', '1st', '4th Year', '2018'),
(60, 'ITE412', 'IT Elective3', 3, '', 4, '2018-2019', '1st', '4th Year', '2018'),
(61, 'ITM421', 'Capstone 2', 3, 'ITM411', 4, '2018-2019', '2nd', '4th Year', '2018'),
(62, 'ITM422', 'System Administration and Maintenance', 3, 'ITM321', 4, '2018-2019', '2nd', '4th Year', '2018'),
(63, 'ITM423', 'Social and Professional Issues', 3, '', 4, '2018-2019', '2nd', '4th Year', '2018'),
(64, 'ITE424', 'ITElective4', 3, '', 4, '2018-2019', '2nd', '4th Year', '2018'),
(65, 'ITM 412', 'Information Assurance and Security 2', 3, 'ITM 321', 4, '2023-2024', '1st', '4th Year', '2023'),
(66, 'ITM 411', 'Capstone 1', 3, 'ITM 322, ITM 323', 4, '2023-2024', '1st', '4th Year', '2023'),
(69, 'RE 2', 'Sacred Scriptures in the Works of St. Augustine', 2, '', 3, '2023-2024', '1st', '2nd Year', '2023'),
(72, 'ITM 3S1', 'Practicum', 6, '80% of All the Subje', 1, '2023-2024', 'Summer', '3rd Year', '2023'),
(73, 'ITE 3S1', 'ITElective 1', 3, '', 1, '2023-2024', 'Summer', '3rd Year', '2023'),
(74, 'ITM 421', 'Capstone 2', 3, 'ITM411', 4, '2023-2024', '2nd', '4th Year', '2023'),
(75, 'ITM 422', 'System Asministration and Maintenance', 3, 'ITM 321', 4, '2023-2024', '2nd', '4th Year', '2023'),
(76, 'ITM 423', 'Social and Professional Issues', 3, '', 4, '2023-2024', '2nd', '4th Year', '2023'),
(77, 'ITE 424', 'ITElective 4', 3, '', 4, '2023-2024', '2nd', '4th Year', '2023');

-- --------------------------------------------------------

--
-- Table structure for table `teacherassignments`
--

CREATE TABLE `teacherassignments` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `academic_year` varchar(20) NOT NULL,
  `semester` varchar(20) NOT NULL,
  `section` varchar(20) NOT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `hours` varchar(50) NOT NULL,
  `notes` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teacherassignments`
--

INSERT INTO `teacherassignments` (`id`, `teacher_id`, `subject_id`, `course_id`, `academic_year`, `semester`, `section`, `created_at`, `updated_at`, `hours`, `notes`) VALUES
(1, 1, 43, 1, '2025-2026', '2nd', 'A', '2025-12-16 22:56:35', '2025-12-17 04:41:15', '5', 'Saturday: 7:30AM-12:30PM'),
(3, 1, 43, 1, '2025-2026', '2nd', 'B', '2025-12-17 04:42:18', '2025-12-17 06:17:17', '5', 'Saturday: 1:30PM-6:30PM'),
(4, 1, 18, 2, '2025-2026', '2nd', 'A', '2025-12-29 00:36:49', '2025-12-29 00:36:49', '5', ''),
(5, 1, 18, 2, '2025-2026', '2nd', 'B', '2025-12-29 00:37:15', '2025-12-29 00:37:15', '5', ''),
(6, 1, 18, 2, '2025-2026', '2nd', 'D', '2025-12-29 00:37:31', '2025-12-29 00:37:31', '5', ''),
(7, 1, 18, 2, '2025-2026', '2nd', 'C', '2025-12-29 00:37:55', '2025-12-29 00:37:55', '5', ''),
(8, 1, 33, 3, '2025-2026', '2nd', 'A', '2025-12-29 00:39:02', '2025-12-29 00:39:02', '5', ''),
(9, 1, 33, 3, '2025-2026', '2nd', 'B', '2025-12-29 00:39:17', '2025-12-29 00:39:17', '5', ''),
(10, 1, 33, 3, '2025-2026', '2nd', 'C', '2025-12-29 00:39:34', '2025-12-29 00:39:34', '5', ''),
(11, 1, 33, 3, '2025-2026', '2nd', 'D', '2025-12-29 00:40:22', '2025-12-29 00:40:22', '5', ''),
(12, 1, 32, 3, '2025-2026', '2nd', 'A', '2025-12-29 00:41:29', '2025-12-29 00:41:29', '5', ''),
(13, 1, 32, 3, '2025-2026', '2nd', 'B', '2025-12-29 00:41:49', '2025-12-29 00:41:49', '5', '');

-- --------------------------------------------------------

--
-- Table structure for table `teachers`
--

CREATE TABLE `teachers` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `employee_id` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `teachers`
--

INSERT INTO `teachers` (`id`, `name`, `username`, `employee_id`, `password`) VALUES
(1, 'Erick Jason Batuto', 'jason', 'CSR0214', '$2y$10$4Vn3QCycROFjBCTa9ba8.e/gusvocWGwgLGmQiq3VcVyTZ/ZcZOWO');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `fullname` varchar(100) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','registrar','cashier','treasurer','counselor') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `fullname`, `username`, `password`, `role`) VALUES
(10, 'Batuto, Erick Jason', 'ccsadmin', '$2y$10$F4zuQR.FfgnqspKmOF.1Tu1XZ/ZUSElQJDZo4eEoVZMdl3g5tCtxi', 'admin'),
(11, 'Mutas, Jomar Mangao', 'ccsregistrar', '$2y$10$Vxy2xB4bcZMSMt4jsgh6EOR7LEcQwdYS6XVXw7WiPfk0op9sN6b76', 'registrar'),
(12, 'Espadilla, Deniel James', 'ccscashier', '$2y$10$MRsEyrckNxpg8Ble6W8JruqPeiAliXj4sUQjzI1KwFFRiWaaLRFXK', 'cashier'),
(13, 'Mutas, Jomar Mangao', 'ccstreasurer', '$2y$10$4AnrupLUVVM2TuyzRfgs7ezURMHEnxDmMO1f1A37bu84bPUlJgtKq', 'treasurer'),
(14, 'Mutas, Jomar M.', 'ccscounselor', '$2y$10$ziUDvcLz1LfR3HvVTmBiFezK2x/R3Fyw7Yej5qGh4h.a2bVft2LqO', 'counselor');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `academic_years`
--
ALTER TABLE `academic_years`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `activities`
--
ALTER TABLE `activities`
  ADD PRIMARY KEY (`id`),
  ADD KEY `teacher_id` (`teacher_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`);

--
-- Indexes for table `banks`
--
ALTER TABLE `banks`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `bank_id` (`bank_id`);

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`session_id`);

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_payments`
--
ALTER TABLE `customer_payments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `customer_payment_items`
--
ALTER TABLE `customer_payment_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `default_fees`
--
ALTER TABLE `default_fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `disbursements`
--
ALTER TABLE `disbursements`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `fees`
--
ALTER TABLE `fees`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `graduate_tracer`
--
ALTER TABLE `graduate_tracer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`incident_id`);

--
-- Indexes for table `mystudents`
--
ALTER TABLE `mystudents`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_teacher_student_subject` (`teacher_id`,`student_id`,`subject_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `subject_id` (`subject_id`);

--
-- Indexes for table `password_resets`
--
ALTER TABLE `password_resets`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `or_number` (`or_number`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `payment_id` (`payment_id`),
  ADD KEY `student_fee_id` (`student_fee_id`);

--
-- Indexes for table `program_heads`
--
ALTER TABLE `program_heads`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `department_id` (`department_id`);

--
-- Indexes for table `services`
--
ALTER TABLE `services`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_number` (`id_number`);

--
-- Indexes for table `student_activity_scores`
--
ALTER TABLE `student_activity_scores`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_activity_student` (`activity_id`,`student_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `student_fees`
--
ALTER TABLE `student_fees`
  ADD PRIMARY KEY (`id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `fee_id` (`fee_id`);

--
-- Indexes for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_grade` (`student_id`,`subject_id`,`enrollment_id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `enrollment_id` (`enrollment_id`);

--
-- Indexes for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_enrollment_subject` (`enrollment_id`,`subject_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `idx_student_subjects_enrollment_id` (`enrollment_id`),
  ADD KEY `idx_student_subjects_subject_id` (`subject_id`);

--
-- Indexes for table `subjects`
--
ALTER TABLE `subjects`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `teacherassignments`
--
ALTER TABLE `teacherassignments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `subject_id` (`subject_id`),
  ADD KEY `idx_teacher_course` (`teacher_id`,`course_id`),
  ADD KEY `idx_course_subject` (`course_id`,`subject_id`),
  ADD KEY `idx_academic_semester` (`academic_year`,`semester`);

--
-- Indexes for table `teachers`
--
ALTER TABLE `teachers`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `academic_years`
--
ALTER TABLE `academic_years`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `activities`
--
ALTER TABLE `activities`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `banks`
--
ALTER TABLE `banks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `customer_payments`
--
ALTER TABLE `customer_payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `customer_payment_items`
--
ALTER TABLE `customer_payment_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `default_fees`
--
ALTER TABLE `default_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `disbursements`
--
ALTER TABLE `disbursements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `enrollments`
--
ALTER TABLE `enrollments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=212;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `fees`
--
ALTER TABLE `fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `graduate_tracer`
--
ALTER TABLE `graduate_tracer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `mystudents`
--
ALTER TABLE `mystudents`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=246;

--
-- AUTO_INCREMENT for table `password_resets`
--
ALTER TABLE `password_resets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `payment_allocations`
--
ALTER TABLE `payment_allocations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- AUTO_INCREMENT for table `program_heads`
--
ALTER TABLE `program_heads`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `services`
--
ALTER TABLE `services`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=350;

--
-- AUTO_INCREMENT for table `student_activity_scores`
--
ALTER TABLE `student_activity_scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=308;

--
-- AUTO_INCREMENT for table `student_fees`
--
ALTER TABLE `student_fees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=111;

--
-- AUTO_INCREMENT for table `student_grades`
--
ALTER TABLE `student_grades`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `student_subjects`
--
ALTER TABLE `student_subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=1241;

--
-- AUTO_INCREMENT for table `subjects`
--
ALTER TABLE `subjects`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `teacherassignments`
--
ALTER TABLE `teacherassignments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `teachers`
--
ALTER TABLE `teachers`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `activities`
--
ALTER TABLE `activities`
  ADD CONSTRAINT `activities_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`),
  ADD CONSTRAINT `activities_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`);

--
-- Constraints for table `bank_transactions`
--
ALTER TABLE `bank_transactions`
  ADD CONSTRAINT `fk_bank_trans_bank` FOREIGN KEY (`bank_id`) REFERENCES `banks` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `enrollments`
--
ALTER TABLE `enrollments`
  ADD CONSTRAINT `enrollments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `enrollments_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `student_activity_scores`
--
ALTER TABLE `student_activity_scores`
  ADD CONSTRAINT `student_activity_scores_ibfk_1` FOREIGN KEY (`activity_id`) REFERENCES `activities` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_activity_scores_ibfk_2` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`);

--
-- Constraints for table `student_grades`
--
ALTER TABLE `student_grades`
  ADD CONSTRAINT `student_grades_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grades_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `student_grades_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `student_subjects`
--
ALTER TABLE `student_subjects`
  ADD CONSTRAINT `student_subjects_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`id`),
  ADD CONSTRAINT `student_subjects_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`),
  ADD CONSTRAINT `student_subjects_ibfk_3` FOREIGN KEY (`enrollment_id`) REFERENCES `enrollments` (`id`);

--
-- Constraints for table `subjects`
--
ALTER TABLE `subjects`
  ADD CONSTRAINT `subjects_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`);

--
-- Constraints for table `teacherassignments`
--
ALTER TABLE `teacherassignments`
  ADD CONSTRAINT `teacherassignments_ibfk_1` FOREIGN KEY (`teacher_id`) REFERENCES `teachers` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacherassignments_ibfk_2` FOREIGN KEY (`subject_id`) REFERENCES `subjects` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `teacherassignments_ibfk_3` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
