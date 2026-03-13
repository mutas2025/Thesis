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
-- Database: `u290526623_GuidanceSYS26`
--

-- --------------------------------------------------------

--
-- Table structure for table `alumni`
--

CREATE TABLE `alumni` (
  `alumni_id` int(11) NOT NULL,
  `student_number` varchar(50) NOT NULL,
  `full_name` varchar(255) NOT NULL,
  `gender` varchar(20) DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(50) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `graduation_date` date DEFAULT NULL,
  `year_graduated` varchar(20) DEFAULT NULL,
  `employment_status` varchar(50) DEFAULT NULL,
  `degree_earned` varchar(100) DEFAULT NULL,
  `social_media_account` varchar(100) DEFAULT NULL,
  `salary` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `alumni`
--

INSERT INTO `alumni` (`alumni_id`, `student_number`, `full_name`, `gender`, `date_of_birth`, `address`, `contact_number`, `email`, `graduation_date`, `year_graduated`, `employment_status`, `degree_earned`, `social_media_account`, `salary`, `created_at`) VALUES
(9, '2001022203', 'ABELLO, FAITH KRISHA CALSA', 'Female', '2002-05-12', '123 City A, Sample City', '09171234001', 'f.abello@sample.edu', '2025-03-25', '2025', 'Employed', 'BSIT', 'facebook.com/f.abello', '25000', '2026-03-10 08:46:19'),
(10, '2001022204', 'ALEJO, RESHEL P.', 'Male', '2002-08-20', '456 City B, Sample City', '09171234002', 'r.alejo@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSHRM', 'instagram.com/r.alejo', '0', '2026-03-10 08:46:19'),
(11, '2001022205', 'BALBUENA, KHYLE', 'Male', '2001-11-15', '789 City C, Sample City', '09171234003', 'k.balbuena@sample.edu', '2025-03-25', '2025', 'Employed', 'BSCS', 'twitter.com/k.balbuena', '28000', '2026-03-10 08:46:19'),
(12, '2001022206', 'BATARILAN, CYRUS L. JR', 'Male', '2002-02-10', '101 City D, Sample City', '09171234004', 'c.batarilan@sample.edu', '2025-03-25', '2025', 'Further Studies', 'BSBA', NULL, '0', '2026-03-10 08:46:19'),
(13, '2001022207', 'BONGO, BRIAN RIC', 'Male', '2001-07-22', '202 City E, Sample City', '09171234005', 'b.bongo@sample.edu', '2025-03-25', '2025', 'Employed', 'BSIT', 'facebook.com/b.bongo', '26000', '2026-03-10 08:46:19'),
(14, '2001022208', 'CABALLERO, LOUIS CRAIG DYLAN R.', 'Male', '2002-09-05', '303 City F, Sample City', '09171234006', 'l.caballero@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSCE', 'instagram.com/l.caballero', '0', '2026-03-10 08:46:19'),
(15, '2001022209', 'CABELLON, JOHN CARLO', 'Male', '2002-01-30', '404 City G, Sample City', '09171234007', 'j.cabellon@sample.edu', '2025-03-25', '2025', 'Self-Employed', 'BSME', NULL, '30000', '2026-03-10 08:46:19'),
(16, '2001022210', 'CALINAWAGAN, ALEA K.', 'Female', '2002-04-18', '505 City H, Sample City', '09171234008', 'a.calinawagan@sample.edu', '2025-03-25', '2025', 'Employed', 'BSN', 'facebook.com/a.calinawagan', '22000', '2026-03-10 08:46:19'),
(17, '2001022211', 'CANOY, JOSE III', 'Male', '2001-12-12', '606 City I, Sample City', '09171234009', 'j.canoy@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSCRIM', 'twitter.com/j.canoy', '0', '2026-03-10 08:46:19'),
(18, '2001022212', 'CENABRE, ALLUENA', 'Female', '2002-06-25', '707 City J, Sample City', '09171234010', 'a.cenabre@sample.edu', '2025-03-25', '2025', 'Employed', 'BSA', 'instagram.com/a.cenabre', '24000', '2026-03-10 08:46:19'),
(19, '2001022213', 'DEJOS, BRIAN EMANUEL BALDOMAR', 'Male', '2002-03-08', '808 City K, Sample City', '09171234011', 'b.dejos@sample.edu', '2025-03-25', '2025', 'Employed', 'BSIT', 'facebook.com/b.dejos', '25500', '2026-03-10 08:46:19'),
(20, '2001022214', 'DELIMA, PAOLO M.', 'Male', '2001-10-14', '909 City L, Sample City', '09171234012', 'p.delima@sample.edu', '2025-03-25', '2025', 'Further Studies', 'BSEd', NULL, '0', '2026-03-10 08:46:19'),
(21, '2001022215', 'DESCARTIN, HESAH S.', 'Female', '2002-05-19', '110 City M, Sample City', '09171234013', 'h.descartin@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSHM', 'twitter.com/h.descartin', '0', '2026-03-10 08:46:19'),
(22, '2001022216', 'DESPI, JOMARI T.', 'Male', '2001-08-30', '221 City N, Sample City', '09171234014', 'j.despi@sample.edu', '2025-03-25', '2025', 'Employed', 'BSEE', 'facebook.com/j.despi', '27000', '2026-03-10 08:46:19'),
(23, '2001022217', 'JARABE, IRIES', 'Female', '2002-02-14', '332 City O, Sample City', '09171234015', 'i.jarabe@sample.edu', '2025-03-25', '2025', 'Self-Employed', 'BSBA', 'instagram.com/i.jarabe', '18000', '2026-03-10 08:46:19'),
(24, '2001022218', 'MACATOL, CATHERINE ANNE E.', 'Female', '2002-07-07', '443 City P, Sample City', '09171234016', 'c.macatol@sample.edu', '2025-03-25', '2025', 'Employed', 'BS Psychology', 'facebook.com/c.macatol', '23000', '2026-03-10 08:46:19'),
(25, '2001022219', 'MANOLONG, SHERYN MAE R.', 'Female', '2001-11-01', '554 City Q, Sample City', '09171234017', 's.manolong@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSIT', 'twitter.com/s.manolong', '0', '2026-03-10 08:46:19'),
(26, '2001022220', 'NELLAS, JESSICA M.', 'Female', '2002-09-28', '665 City R, Sample City', '09171234018', 'j.nellas@sample.edu', '2025-03-25', '2025', 'Employed', 'BSA', 'instagram.com/j.nellas', '24500', '2026-03-10 08:46:19'),
(27, '2001022221', 'OMPAR, RAVEN CARL', 'Male', '2001-04-15', '776 City S, Sample City', '09171234019', 'r.ompar@sample.edu', '2025-03-25', '2025', 'Further Studies', 'BS Math', NULL, '0', '2026-03-10 08:46:19'),
(28, '2001022222', 'PALAY, ZEN ANGELO TILOY', 'Male', '2002-01-22', '887 City T, Sample City', '09171234020', 'z.palay@sample.edu', '2025-03-25', '2025', 'Employed', 'BSIT', 'facebook.com/z.palay', '26500', '2026-03-10 08:46:19'),
(29, '2001022223', 'PANDORO, RALPH R.', 'Male', '2001-06-10', '998 City U, Sample City', '09171234021', 'r.pandoro@sample.edu', '2025-03-25', '2025', 'Self-Employed', 'BS Entrepreneurship', NULL, '32000', '2026-03-10 08:46:19'),
(30, '2001022224', 'PANERIO, CHERISSA MARI', 'Female', '2002-12-05', '199 City V, Sample City', '09171234022', 'c.panerio@sample.edu', '2025-03-25', '2025', 'Employed', 'BSHRM', 'instagram.com/c.panerio', '21000', '2026-03-10 08:46:19'),
(31, '2001022225', 'PATRIA, KEN HAROLD', 'Male', '2001-03-17', '288 City W, Sample City', '09171234023', 'k.patria@sample.edu', '2025-03-25', '2025', 'Unemployed', 'BSME', 'twitter.com/k.patria', '0', '2026-03-10 08:46:19'),
(32, '2001022226', 'PESIAO, FRANCIS ASHER JIMENA', 'Male', '2002-08-11', '377 City X, Sample City', '09171234024', 'f.pesiao@sample.edu', '2025-03-25', '2025', 'Employed', 'BSCS', 'facebook.com/f.pesiao', '27500', '2026-03-10 08:46:19'),
(33, '2001022227', 'RIGOR, DAN MICHAELANGELO M,', 'Male', '2001-10-23', '466 City Y, Sample City', '09171234025', 'd.rigor@sample.edu', '2025-03-25', '2025', 'Further Studies', 'BS Physics', NULL, '0', '2026-03-10 08:46:19'),
(34, '2001022228', 'SANTILLAN, KRISTAL', 'Female', '2002-05-02', '555 City Z, Sample City', '09171234026', 'k.santillan@sample.edu', '2025-03-25', '2025', 'Employed', 'BS English', 'instagram.com/k.santillan', '22500', '2026-03-10 08:46:19'),
(35, '2000103015', 'ESLAIS, KENN JAY ABADIES', 'Male', '2000-01-01', 'N/A', 'N/A', 'kenn@gmail.com', '2026-03-10', '2025', 'Employed', 'BSIT', '', '15000', '2026-03-10 11:15:11');

-- --------------------------------------------------------

--
-- Table structure for table `appointments`
--

CREATE TABLE `appointments` (
  `appointment_id` int(11) NOT NULL,
  `student_id` int(11) NOT NULL,
  `counselor_id` int(11) NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `purpose` varchar(255) DEFAULT NULL,
  `status` enum('Pending','Approved','Cancelled','Completed') DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `appointments`
--

INSERT INTO `appointments` (`appointment_id`, `student_id`, `counselor_id`, `appointment_datetime`, `purpose`, `status`, `created_at`) VALUES
(5, 2, 10, '2023-10-25 09:00:00', 'Consultation regarding thesis proposal', 'Pending', '2026-03-10 07:54:17'),
(6, 4, 10, '2023-10-25 10:30:00', 'Follow-up on previous session', 'Approved', '2026-03-10 07:54:17'),
(7, 6, 10, '2023-10-26 13:00:00', 'Request for certificate of good moral', 'Pending', '2026-03-10 07:54:17'),
(8, 9, 10, '2023-10-26 14:30:00', 'Discussion about internship requirements', 'Approved', '2026-03-10 07:54:17'),
(9, 11, 10, '2023-10-27 08:00:00', 'Personal problem consultation', 'Completed', '2026-03-10 07:54:17'),
(10, 14, 10, '2023-10-27 09:30:00', 'Grade concern discussion', 'Cancelled', '2026-03-10 07:54:17'),
(11, 16, 10, '2023-10-28 11:00:00', 'Career guidance request', 'Pending', '2026-03-10 07:54:17'),
(12, 19, 10, '2023-10-28 15:00:00', 'Conflict with classmate mediation', 'Approved', '2026-03-10 07:54:17'),
(13, 22, 10, '2023-10-29 10:00:00', 'Academic advising', 'Completed', '2026-03-10 07:54:17'),
(14, 25, 10, '2023-10-30 14:00:00', 'Mental health check-in', 'Pending', '2026-03-10 07:54:17');

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
(2, 1, 'Personality Test', '2023-08-01', 'Introvert', 'Student prefers solitary activities and reflection.', 'Encourage participation in small groups.', '2026-03-10 07:54:37'),
(3, 3, 'Aptitude Test', '2023-08-02', 'High Analytical Skill', 'Strong potential in problem-solving and logic.', 'Suggested to take advanced math subjects.', '2026-03-10 07:54:37'),
(4, 5, 'Interest Inventory', '2023-08-03', 'Artistic & Social', ' leaning towards creative and people-oriented careers.', 'Consider career in UI/UX Design.', '2026-03-10 07:54:37'),
(5, 8, 'Stress Scale', '2023-08-04', 'Moderate Stress', 'Experiencing some academic pressure.', 'Time management workshop recommended.', '2026-03-10 07:54:37'),
(6, 12, 'Depression Anxiety Stress Scales (DASS)', '2023-08-05', 'Normal', 'No significant signs of depression or anxiety.', 'Maintain current lifestyle.', '2026-03-10 07:54:37'),
(7, 15, 'Learning Style Assessment', '2023-08-06', 'Visual Learner', 'Learns best through images and spatial understanding.', 'Use charts and diagrams in studying.', '2026-03-10 07:54:37'),
(8, 18, 'Career Personality Test', '2023-08-07', 'Enterprising', 'Suitable for leadership and business roles.', 'Encourage to lead student organizations.', '2026-03-10 07:54:37'),
(9, 21, 'Study Habits Inventory', '2023-08-08', 'Poor Study Habits', 'Lacks consistent schedule and focus.', 'Study skills coaching needed.', '2026-03-10 07:54:37'),
(10, 24, 'Emotional Intelligence (EQ)', '2023-08-09', 'High EQ', 'Excellent self-awareness and social skills.', 'Potential for peer counseling.', '2026-03-10 07:54:37'),
(11, 27, 'Aptitude Test (Verbal)', '2023-08-10', 'Average', 'Communcation skills are adequate.', 'Practice public speaking to improve.', '2026-03-10 07:54:37');

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
(1, 1, 10, '2023-10-01', 'Academic', 'Low academic performances', 'Instructor', 'Student struggling with Advanced Algorithms.', 'Recommend tutoring sessions.', '2023-10-08', 'Completed', '2026-03-10 07:53:49'),
(2, 3, 10, '2023-10-02', 'Personal', 'Family Problem', 'Self', 'Student expressing anxiety about family financial situation.', 'Refer to financial aid office.', '2023-10-09', 'Ongoing', '2026-03-10 07:53:49'),
(3, 5, 10, '2023-10-03', 'Behavioral', 'Missbehavior', 'Discipline Officer', 'Disruptive behavior in laboratory class.', 'Implement behavioral contract.', '2023-10-10', 'Completed', '2026-03-10 07:53:49'),
(4, 8, 10, '2023-10-04', 'Career', 'Lack of interest in studying', 'Self', 'Unsure about career path after BSIT.', 'Career assessment and planning.', '2023-10-11', 'Completed', '2026-03-10 07:53:49'),
(5, 12, 10, '2023-10-05', 'Academic', 'Frequent absences', 'Instructor', 'Missing 3 consecutive classes.', 'Verify reasons and monitor attendance.', '2023-10-12', 'Referred', '2026-03-10 07:53:49'),
(6, 15, 10, '2023-10-06', 'Personal', 'Mental Health Problem', 'Friend', 'Signs of burnout and stress.', 'Refer to external psychologist.', '2023-10-13', 'Completed', '2026-03-10 07:53:49'),
(7, 18, 10, '2023-10-07', 'Academic', 'Not Wearing complete/proper uniform', 'Security', 'Repeated violation of dress code.', 'Reminder of school policies.', '2023-10-14', 'Completed', '2026-03-10 07:53:49'),
(8, 21, 10, '2023-10-08', 'Behavioral', 'Cutting Classes', 'Instructor', 'Caught leaving campus during class hours.', 'Parent conference required.', '2023-10-15', 'Ongoing', '2026-03-10 07:53:49'),
(9, 24, 10, '2023-10-09', 'Career', 'Indifference towards school work', 'Self', 'Lacks motivation for current major.', 'Explore shifting to other programs.', '2023-10-16', 'Ongoing', '2026-03-10 07:53:49'),
(10, 27, 10, '2023-10-10', 'Personal', 'Timidity, Shyness, Withdrawal', 'Instructor', 'Difficulty participating in group projects.', 'Encourage joining clubs.', '2023-10-17', 'Completed', '2026-03-10 07:53:49'),
(11, 15, 10, '2026-03-10', 'Personal', 'Missbehavior', 'Erick Jason Batuto', '', '', '2026-03-20', 'Ongoing', '2026-03-10 13:52:34'),
(12, 8, 10, '2026-03-11', 'Behavioral', 'Lack of interest in studying', 'Mr. Abellar', 'The student has lack of interest in studying', 'weekly follow up on his academic performance. ', '2026-03-14', 'Ongoing', '2026-03-11 03:37:12');

-- --------------------------------------------------------

--
-- Table structure for table `exam_results`
--

CREATE TABLE `exam_results` (
  `exam_id` int(11) NOT NULL,
  `department_name` varchar(255) NOT NULL,
  `student_name` varchar(255) NOT NULL,
  `std_raw_score` varchar(50) DEFAULT NULL,
  `std_percentile_rank` varchar(50) DEFAULT NULL,
  `std_verbal_desc` varchar(255) DEFAULT NULL,
  `tmt_raw_score` varchar(50) DEFAULT NULL,
  `tmt_interpretation` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_uca1400_ai_ci;

--
-- Dumping data for table `exam_results`
--

INSERT INTO `exam_results` (`exam_id`, `department_name`, `student_name`, `std_raw_score`, `std_percentile_rank`, `std_verbal_desc`, `tmt_raw_score`, `tmt_interpretation`) VALUES
(1, 'College of Computer Studies', 'NELLAS, JESSICA M.', '35', '85', 'Above Average', '45', 'High Potential'),
(2, 'College of Computer Studies', 'OMPAR, RAVEN CARL', '40', '92', 'Superior', '48', 'High Potential'),
(3, 'College of Computer Studies', 'PALAY, ZEN ANGELO TILOY', '30', '75', 'Average', '38', 'Moderate'),
(4, 'College of Computer Studies', 'PANDORO, RALPH R.', '42', '95', 'Superior', '50', 'Very High Potential'),
(5, 'College of Computer Studies', 'PANERIO, CHERISSA MARI', '28', '65', 'Low Average', '32', 'Needs Improvement'),
(6, 'College of Computer Studies', 'PATRIA, KEN HAROLD', '33', '80', 'Above Average', '40', 'Moderate'),
(7, 'College of Teachers Education', 'PESIAO, FRANCIS ASHER JIMENA', '38', '88', 'Above Average', '42', 'High Potential'),
(8, 'College of Teachers Education', 'RIGOR, DAN MICHAELANGELO M', '36', '82', 'Above Average', '39', 'Moderate'),
(9, 'College of Teachers Education', 'SANTILLAN, KRISTAL', '41', '90', 'Superior', '46', 'High Potential'),
(10, 'College of Teachers Education', 'SINGULAR, JUDYMAR P.', '39', '89', 'Above Average', '44', 'High Potential');

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
  `year_graduated` varchar(10) NOT NULL,
  `gender` varchar(20) NOT NULL,
  `birthday` date NOT NULL,
  `civil_status` varchar(20) DEFAULT NULL,
  `spouse_name` varchar(100) DEFAULT NULL,
  `children_count` varchar(20) DEFAULT NULL,
  `address` text NOT NULL,
  `contact` varchar(50) DEFAULT NULL,
  `programs` text DEFAULT NULL COMMENT 'Stored as comma-separated string',
  `post_grad` varchar(255) DEFAULT NULL,
  `honors` varchar(255) DEFAULT NULL,
  `board_exam` varchar(255) DEFAULT NULL,
  `other_schools` text DEFAULT NULL,
  `occupation` varchar(100) DEFAULT NULL,
  `company` varchar(255) NOT NULL,
  `position` varchar(100) NOT NULL,
  `company_address` text NOT NULL,
  `employment_date` date NOT NULL,
  `salary` text DEFAULT NULL COMMENT 'Stored as comma-separated string',
  `prev_company` varchar(255) DEFAULT NULL,
  `prev_position` varchar(100) DEFAULT NULL,
  `prev_address` text DEFAULT NULL,
  `employment_time` varchar(50) NOT NULL,
  `success_story` text NOT NULL,
  `consent` varchar(20) NOT NULL DEFAULT 'Disagreed',
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `graduate_tracer`
--

INSERT INTO `graduate_tracer` (`id`, `email`, `family_name`, `first_name`, `middle_name`, `year_graduated`, `gender`, `birthday`, `civil_status`, `spouse_name`, `children_count`, `address`, `contact`, `programs`, `post_grad`, `honors`, `board_exam`, `other_schools`, `occupation`, `company`, `position`, `company_address`, `employment_date`, `salary`, `prev_company`, `prev_position`, `prev_address`, `employment_time`, `success_story`, `consent`, `submitted_at`) VALUES
(2, 'faith.abello@sample.edu', 'ABELLO', 'FAITH KRISHA', 'CALSA', '2025', 'Female', '2002-05-12', 'Single', '', '', '123 City A, San Carlos', '09171234001', 'BSIT', 'MAED - Guidance and Counseling', 'Cum Laude', 'None', 'None', 'Junior Web Developer', 'Tech Solutions Inc.', 'Associate Developer', 'Cebu IT Park', '2025-06-15', '20,000-25,000', 'None', 'None', 'None', '1–3 months', 'Landed my dream job within 2 months thanks to the skills I learned in college.', 'Agreed', '2026-03-10 08:48:03'),
(3, 'reshel.alejo@sample.edu', 'ALEJO', 'RESHEL', 'P.', '2025', 'Female', '2002-08-20', 'Single', '', '', '456 City B, San Carlos', '09171234002', 'BSHRM', 'None', 'Academic Distinction', 'None', 'None', 'Hotel Staff', 'Shangri-La Mactan', 'Front Desk Officer', 'Punta Engaño, Mactan', '2025-07-01', '15,000-20,000', 'None', 'None', 'None', '4–6 months', 'My internship prepared me well for the fast-paced environment of the hospitality industry.', 'Agreed', '2026-03-10 08:48:03'),
(4, 'khyle.balbuena@sample.edu', 'BALBUENA', 'KHYLE', '', '2025', 'Male', '2001-11-15', 'Single', '', '', '789 City C, San Carlos', '09171234003', 'BSCS', 'MAED - Educational Management', 'Academic Distinction', 'None', 'None', 'Freelance Programmer', 'Upwork', 'Full Stack Developer', 'Remote', '2025-05-20', '25,000-30,000', 'None', 'None', 'None', 'Less than 1 month', 'I immediately started taking freelance projects which helped me gain experience quickly.', 'Agreed', '2026-03-10 08:48:03'),
(5, 'cyrus.batarilan@sample.edu', 'BATARILAN', 'CYRUS', 'L. JR', '2025', 'Male', '2002-02-10', 'Single', '', '', '101 City D, San Carlos', '09171234004', 'BSBA', 'None', 'Loyalty Award', 'Civil Service Review', 'None', 'Business Owner', 'Balbuena Trading', 'Proprietor', 'City Public Market', '2025-08-01', '30,000-35,000', 'None', 'None', 'None', '7–12 months', 'It took time to save capital, but now I run my own family business successfully.', 'Agreed', '2026-03-10 08:48:03'),
(6, 'brian.bongo@sample.edu', 'BONGO', 'BRIAN', 'RIC', '2025', 'Male', '2001-07-22', 'Married', 'Maria Bongo', '1', '202 City E, San Carlos', '09171234005', 'BSIT', 'None', 'None', 'None', 'None', 'IT Support', 'LGU San Carlos', 'Tech Staff', 'City Hall', '2025-06-10', '20,000-25,000', 'None', 'None', 'None', '1–3 months', 'Working in the government gives me stability and a chance to serve my community.', 'Agreed', '2026-03-10 08:48:03'),
(7, 'louis.caballero@sample.edu', 'CABALLERO', 'LOUIS CRAIG DYLAN', 'R.', '2025', 'Male', '2002-09-05', 'Single', '', '', '303 City F, San Carlos', '09171234006', 'BSCE', 'None', 'None', 'Board Exam Reviewee', 'None', 'Construction Inspector', 'DMCI', 'Jr. Engineer', 'Mandaue City', '2025-07-15', '25,000-30,000', 'None', 'None', 'None', '4–6 months', 'Passed the board exam on my first try and got hired immediately.', 'Agreed', '2026-03-10 08:48:03'),
(8, 'john.cabellon@sample.edu', 'CABELLON', 'JOHN', 'CARLO', '2025', 'Male', '2002-01-30', 'Single', '', '', '404 City G, San Carlos', '09171234007', 'BSME', 'None', 'None', 'Mechanical Board Exam', 'None', 'Mechanic', 'Toyota San Carlos', 'Senior Technician', 'National Highway', '2025-06-01', '20,000-25,000', 'None', 'None', 'None', '1–3 months', 'My hands-on training in college was a huge advantage during the job interview.', 'Agreed', '2026-03-10 08:48:03'),
(9, 'alea.calinawagan@sample.edu', 'CALINAWAGAN', 'ALEA', 'K.', '2025', 'Female', '2002-04-18', 'Single', '', '', '505 City H, San Carlos', '09171234008', 'BSN', 'None', 'Cum Laude', 'Nursing Board Exam', 'None', 'Staff Nurse', 'Chong Hua Hospital', 'RN', 'Cebu City', '2025-08-10', '30,000-35,000', 'None', 'None', 'None', 'More than 1 year', 'It took a year to pass the board, but it was worth the wait to work in a top hospital.', 'Agreed', '2026-03-10 08:48:03'),
(10, 'jose.canoy@sample.edu', 'CANOY', 'JOSE', 'III', '2025', 'Male', '2001-12-12', 'Married', 'Anna Canoy', '0', '606 City I, San Carlos', '09171234009', 'BSCRIM', 'None', 'None', 'Criminologist Licensure Exam', 'None', 'Police Officer', 'PNP', 'PO3', 'Police Station', '2025-09-01', '35,000-40,000', 'None', 'None', 'None', '7–12 months', 'Serving the community has always been my dream.', 'Agreed', '2026-03-10 08:48:03'),
(11, 'alluena.cenabre@sample.edu', 'CENABRE', 'ALLUENA', '', '2025', 'Female', '2002-06-25', 'Single', '', '', '707 City J, San Carlos', '09171234010', 'BSA', 'MAED - Administration and Supervision', 'Magna Cum Laude', 'CPA Board Exam', 'None', 'Auditor', 'SGV & Co.', 'Associate Auditor', 'Cebu Business Park', '2025-07-20', '40,000-50,000', 'None', 'None', 'None', 'Less than 1 month', 'Graduating with honors opened many doors for me in the accounting field.', 'Agreed', '2026-03-10 08:48:03');

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
(1, 1, '2023-09-15', 'Vandalism', 'Caught drawing on classroom walls.', 'Called parents and required to clean walls.', 'Student showed remorse.', 'Resolved', '2026-03-10 07:54:25'),
(2, 7, '2023-09-18', 'Physical Altercation', 'Involved in a fistfight inside the campus.', 'Suspension for 3 days.', 'Anger management recommended.', 'Resolved', '2026-03-10 07:54:25'),
(3, 10, '2023-09-20', 'Theft', 'Accused of stealing a classmates laptop charger.', 'Investigation cleared student of charges.', 'False accusation caused distress.', 'Resolved', '2026-03-10 07:54:25'),
(4, 13, '2023-09-22', 'Dress Code Violation', 'Wearing prohibited attire during flag ceremony.', 'Warning issued and asked to change.', 'Repeated offense.', 'Pending', '2026-03-10 07:54:25'),
(5, 17, '2023-09-25', 'Disrespect to Authority', 'Shouted at a security guard.', 'Community service and apology letter.', 'Attitude needs adjustment.', 'Resolved', '2026-03-10 07:54:25'),
(6, 20, '2023-09-28', 'Cheating', 'Caught using notes during major exam.', 'Grade of 5.0 in the subject.', 'Academic dishonesty discussed.', 'Resolved', '2026-03-10 07:54:25'),
(7, 23, '2023-10-02', 'Possession of Prohibited Item', 'Found with alcohol in dormitory.', 'Confiscation and parental notification.', 'Counseling on substance abuse.', 'Pending', '2026-03-10 07:54:25'),
(8, 26, '2023-10-05', 'Cyberbullying', 'Posted offensive comments about a peer online.', 'Blocked access and social probation.', 'Digital citizenship seminar required.', 'Resolved', '2026-03-10 07:54:25'),
(9, 29, '2023-10-08', 'Skipping Mandatory Event', 'Did not attend the university foundation day.', 'Written explanation required.', 'Excused due to valid reason.', 'Resolved', '2026-03-10 07:54:25'),
(10, 32, '2023-10-12', 'Property Damage', 'Broke laboratory equipment due to negligence.', 'Required to pay for replacement.', 'More careful handling urged.', 'Resolved', '2026-03-10 07:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `staff_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('Admin','Guidance Counselor','Staff') NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `office_hours` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `photo` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`staff_id`, `full_name`, `role`, `email`, `contact_number`, `username`, `password_hash`, `office_hours`, `created_at`, `photo`) VALUES
(1, 'Jomar Mutas', 'Admin', 'mutas@csr-scc.edu.ph', '09101882719', 'joms', '$2y$10$PoZQ54Qq9SsBozYSLfTPvOB9iBx.m0al60tS3a3QHpVD0YV/OjBeS', '8:00 AM- 12:00 PM', '2025-12-18 11:13:41', 'uploads/staff_photos/6972d58378a21.jpg'),
(10, 'Kenn Eslais', 'Guidance Counselor', 'kenn@csr.com', '09101882719', 'kenn', '$2y$10$.dYIQrwuYQdkGDXUbUmZ5edzscV6Iwm2bC3HpZDjxt/JnhNREb6pq', '', '2026-03-10 05:55:24', 'uploads/staff_photos/kenn.png');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `student_id` int(11) NOT NULL,
  `student_number` varchar(30) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `address` text DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `year_level` varchar(20) DEFAULT NULL,
  `course_or_strand` varchar(50) DEFAULT NULL,
  `section` varchar(50) DEFAULT NULL,
  `enrollment_status` enum('Active','Inactive','Graduated','Dropped') DEFAULT 'Active',
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_contact` varchar(20) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`student_id`, `student_number`, `full_name`, `gender`, `date_of_birth`, `address`, `contact_number`, `email`, `year_level`, `course_or_strand`, `section`, `enrollment_status`, `guardian_name`, `guardian_contact`, `created_at`) VALUES
(1, '2000103001', 'ALCANSARE, TRACY GWYNNETH', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(2, '2000103002', 'ALPITCHE, JOEY C.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(3, '2000103003', 'ALTUBAR, KISSABELLE ESTRIBA', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(4, '2000103004', 'BARBON, ARJHAY', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(5, '2000103005', 'BEQUILLA, ARJAY', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(6, '2000103006', 'BORDO, GERNEIL GRACE', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(7, '2000103007', 'BRIONES, JAMES RYAN BASTARRICHE', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(8, '2000103008', 'CAYAO, PRECIOUS MAE', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(9, '2000103009', 'DE ASIS, RANIEL JOHN C.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(10, '2000103010', 'DIANON, MARC CHRISTIAN H', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(11, '2000103011', 'DONAN, BILLY JOSEPH D.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(12, '2000103012', 'ENTIENZA, JASTER F.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(13, '2000103013', 'ERE-ER, LAURENCE DAVE', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(14, '2000103014', 'ESGUERRA, EARL VINCENT SON M.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(15, '2000103015', 'ESLAIS, KENN JAY ABADIES', 'Male', '2000-01-01', 'N/A', 'N/A', 'kenn@gmail.com', '3', 'BSIT', 'A', 'Graduated', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(16, '2000103016', 'GORDOVE, MAECAELLA RAMOS', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(17, '2000103017', 'HUGO, JOHN DAVE T.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(18, '2000103018', 'LAPUZ, MIKAELA', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(19, '2000103019', 'LUSABIA, JOHN CAROLL', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(20, '2000103020', 'MALABO, FRANCIS P.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(21, '2000103021', 'MUTAS, JOMAR MANGAO', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(22, '2000103022', 'NAJARRO, ROBETH S.', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(23, '2000103023', 'PEQUE, MIGUEL EVAN B.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(24, '2000103024', 'PEREZ, DRUNREB V. JR', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(25, '2000103025', 'POSADAS, KYLE EDHISSON', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(26, '2000103026', 'RAMAS, FRANK JULIUS Y.', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(27, '2000103027', 'RASONABLE, JOSHUA', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(28, '2000103028', 'RINGIA, AISAH P.', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(29, '2000103029', 'ROBLE, ANDREI MARGARETTE L.', 'Female', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(30, '2000103030', 'TOLEDO, GERRY BOY', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(31, '2000103031', 'VARGAS, VON CARLO', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57'),
(32, '2000103032', 'VIDAL, RALPH ZOREN', 'Male', '2000-01-01', 'N/A', 'N/A', 'N/A', '3', 'BSIT', 'A', 'Active', 'N/A', 'N/A', '2026-03-10 07:51:57');

-- --------------------------------------------------------

--
-- Table structure for table `system_logs`
--

CREATE TABLE `system_logs` (
  `log_id` int(11) NOT NULL,
  `staff_id` int(11) DEFAULT NULL,
  `action` varchar(255) DEFAULT NULL,
  `log_timestamp` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `system_logs`
--

INSERT INTO `system_logs` (`log_id`, `staff_id`, `action`, `log_timestamp`) VALUES
(1, 10, 'Updated student: Mutas, Jomar Mangao (1997022201)', '2026-03-10 06:22:46'),
(2, 10, 'Added new student: Eslais, Kenn Jay (2004070201)', '2026-03-10 06:24:58'),
(3, 10, 'Updated student: Mutas, Jomar Mangao (1997022201)', '2026-03-10 06:25:03'),
(4, 10, 'Updated student: Eslais, Kenn Jay (2004070201)', '2026-03-10 06:25:49'),
(5, 10, 'Updated student: Mutas, Jomar Mangao (1997022201)', '2026-03-10 06:54:13'),
(6, 10, 'Updated student: Mutas, Jomar Mangao (1997022201)', '2026-03-10 07:15:55'),
(7, 10, 'Updated alumni', '2026-03-10 07:19:28'),
(8, 10, 'Updated alumni', '2026-03-10 07:19:35'),
(9, 10, 'Updated graduate tracer record for: Jomar Mutas', '2026-03-10 07:24:01'),
(10, 10, 'Failed to send email to Mutas, Jomar Mangao (mutas@csr-scc.edu.ph): mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', '2026-03-10 07:29:34'),
(11, 10, 'Added counseling session', '2026-03-10 07:29:34'),
(12, 10, 'Failed to send email to Mutas, Jomar Mangao (mutas@csr-scc.edu.ph): mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', '2026-03-10 07:30:18'),
(13, 10, 'Added appointment', '2026-03-10 07:30:18'),
(14, 10, 'Failed to send email to Mutas, Jomar Mangao (mutas@csr-scc.edu.ph): mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', '2026-03-10 07:30:39'),
(15, 10, 'Added incident report', '2026-03-10 07:30:39'),
(16, 10, 'Updated graduate tracer record for: Jomar Mutas', '2026-03-10 07:41:32'),
(17, 10, 'Failed to send email to Mutas, Jomar Mangao (mutas@csr-scc.edu.ph): mail(): Failed to connect to mailserver at &quot;localhost&quot; port 25, verify your &quot;SMTP&quot; and &quot;smtp_port&quot; setting in php.ini or use ini_set()', '2026-03-10 07:43:10'),
(18, 10, 'Updated student: ESLAIS, KENN JAY ABADIES (2000103015)', '2026-03-10 11:15:11'),
(19, 1, 'Updated staff member: Kenn Eslais (Guidance Counselor)', '2026-03-10 13:33:23'),
(20, 1, 'Updated staff member: Jomar Mutas (Admin)', '2026-03-10 13:40:11'),
(21, 10, 'Email sent to ESLAIS, KENN JAY ABADIES (kenn@gmail.com)', '2026-03-10 13:52:35'),
(22, 10, 'Added counseling session', '2026-03-10 13:52:35'),
(23, 10, 'Updated alumni', '2026-03-10 13:55:26'),
(24, 10, 'Added counseling session', '2026-03-11 03:37:12'),
(25, 10, 'Updated counseling session', '2026-03-11 03:37:40');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `alumni`
--
ALTER TABLE `alumni`
  ADD PRIMARY KEY (`alumni_id`),
  ADD KEY `idx_alumni_student_number` (`student_number`),
  ADD KEY `idx_alumni_full_name` (`full_name`),
  ADD KEY `idx_alumni_graduation_date` (`graduation_date`);

--
-- Indexes for table `appointments`
--
ALTER TABLE `appointments`
  ADD PRIMARY KEY (`appointment_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `counselor_id` (`counselor_id`);

--
-- Indexes for table `assessments`
--
ALTER TABLE `assessments`
  ADD PRIMARY KEY (`assessment_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD PRIMARY KEY (`session_id`),
  ADD KEY `student_id` (`student_id`),
  ADD KEY `counselor_id` (`counselor_id`);

--
-- Indexes for table `exam_results`
--
ALTER TABLE `exam_results`
  ADD PRIMARY KEY (`exam_id`);

--
-- Indexes for table `graduate_tracer`
--
ALTER TABLE `graduate_tracer`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `incidents`
--
ALTER TABLE `incidents`
  ADD PRIMARY KEY (`incident_id`),
  ADD KEY `student_id` (`student_id`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`staff_id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`student_id`),
  ADD UNIQUE KEY `student_number` (`student_number`);

--
-- Indexes for table `system_logs`
--
ALTER TABLE `system_logs`
  ADD PRIMARY KEY (`log_id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `alumni`
--
ALTER TABLE `alumni`
  MODIFY `alumni_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `appointments`
--
ALTER TABLE `appointments`
  MODIFY `appointment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `assessments`
--
ALTER TABLE `assessments`
  MODIFY `assessment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  MODIFY `session_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `exam_results`
--
ALTER TABLE `exam_results`
  MODIFY `exam_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `graduate_tracer`
--
ALTER TABLE `graduate_tracer`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `incidents`
--
ALTER TABLE `incidents`
  MODIFY `incident_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `staff_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `student_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `system_logs`
--
ALTER TABLE `system_logs`
  MODIFY `log_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointments`
--
ALTER TABLE `appointments`
  ADD CONSTRAINT `appointments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `appointments_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `staff` (`staff_id`);

--
-- Constraints for table `assessments`
--
ALTER TABLE `assessments`
  ADD CONSTRAINT `assessments_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`);

--
-- Constraints for table `counseling_sessions`
--
ALTER TABLE `counseling_sessions`
  ADD CONSTRAINT `counseling_sessions_ibfk_1` FOREIGN KEY (`student_id`) REFERENCES `students` (`student_id`),
  ADD CONSTRAINT `counseling_sessions_ibfk_2` FOREIGN KEY (`counselor_id`) REFERENCES `staff` (`staff_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
