-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Aug 15, 2025 at 06:29 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ramzy`
--

-- --------------------------------------------------------

--
-- Table structure for table `class`
--

CREATE TABLE `class` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `class`
--

INSERT INTO `class` (`id`, `name`, `school_id`) VALUES
(17, '7B', 1),
(18, '9B', 2),
(19, '9R', 2),
(20, '7B', 2),
(21, '7R', 2),
(22, '8B', 2),
(23, '8R', 2);

-- --------------------------------------------------------

--
-- Table structure for table `school`
--

CREATE TABLE `school` (
  `id` int(11) NOT NULL,
  `school_name` varchar(100) NOT NULL,
  `school_code` varchar(50) NOT NULL,
  `address` text NOT NULL,
  `phone` varchar(25) NOT NULL,
  `email` varchar(100) NOT NULL,
  `created_at` date NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `school`
--

INSERT INTO `school` (`id`, `school_name`, `school_code`, `address`, `phone`, `email`, `created_at`) VALUES
(1, 'BOWA JUNIOR SECONDARY', 'BOWA001', 'KOMBANI', '098765789', 'BOWA@GMAIL.COM', '2025-08-11'),
(2, 'ZIBANI JUNIOR SECONDARY', '1234567', 'NGOMBENI', '0987655433', 'kingi@gmail.com', '2025-08-14');

-- --------------------------------------------------------

--
-- Table structure for table `score`
--

CREATE TABLE `score` (
  `id` int(30) NOT NULL,
  `std_id` int(30) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `term` varchar(20) DEFAULT NULL,
  `exam_type` varchar(20) DEFAULT NULL,
  `class_id` int(11) NOT NULL,
  `Score` double NOT NULL,
  `performance` varchar(100) NOT NULL,
  `tcomments` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `score`
--

INSERT INTO `score` (`id`, `std_id`, `subject_id`, `term`, `exam_type`, `class_id`, `Score`, `performance`, `tcomments`, `school_id`, `teacher_id`, `created_at`) VALUES
(13, 15, 16, 'Term 1', 'Mid Term', 22, 60, 'M.E', 'Good', 2, 15, '2025-08-15 15:36:14'),
(14, 16, 20, 'Term 1', 'Mid Term', 18, 70, 'E.E', 'Excellent', 2, 16, '2025-08-15 16:23:09');

-- --------------------------------------------------------

--
-- Table structure for table `student`
--

CREATE TABLE `student` (
  `id` int(11) NOT NULL,
  `firstname` varchar(100) DEFAULT NULL,
  `lastname` varchar(50) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `guardian_name` varchar(100) DEFAULT NULL,
  `guardian_phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `status` enum('active','inactive','transferred','graduated') DEFAULT 'active',
  `photo` varchar(255) DEFAULT NULL,
  `admno` int(100) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student`
--

INSERT INTO `student` (`id`, `firstname`, `lastname`, `gender`, `dob`, `guardian_name`, `guardian_phone`, `address`, `status`, `photo`, `admno`, `school_id`) VALUES
(15, 'REHEMA', 'NGALAA', 'Female', '2025-08-11', 'ABDALLA', '0765432350', 'KWALE', 'active', 'uploads/students/1755263470_1755067844_IHSAN0.png', 3456, 2),
(16, 'SWABRI', 'MOHAMED', 'Male', '2025-08-04', 'KKK', '56967076674', 'KOMBANI', 'active', 'uploads/students/1755274838_1755067844_IHSAN0.png', 5679, 2);

-- --------------------------------------------------------

--
-- Table structure for table `student_subject`
--

CREATE TABLE `student_subject` (
  `id` int(11) NOT NULL,
  `student_id` int(11) DEFAULT NULL,
  `school_id` int(11) NOT NULL,
  `subject_id` int(11) DEFAULT NULL,
  `class_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `student_subject`
--

INSERT INTO `student_subject` (`id`, `student_id`, `school_id`, `subject_id`, `class_id`) VALUES
(14, 15, 2, 16, 22),
(15, 15, 2, 17, 22),
(16, 15, 2, 18, 22),
(17, 15, 2, 19, 22),
(18, 15, 2, 20, 22),
(19, 15, 2, 21, 22),
(20, 15, 2, 22, 22),
(21, 15, 2, 25, 22),
(22, 16, 2, 16, 18),
(23, 16, 2, 17, 18),
(24, 16, 2, 18, 18),
(25, 16, 2, 19, 18),
(26, 16, 2, 20, 18),
(27, 16, 2, 21, 18),
(28, 16, 2, 22, 18),
(29, 16, 2, 25, 18);

-- --------------------------------------------------------

--
-- Table structure for table `subject`
--

CREATE TABLE `subject` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `subject`
--

INSERT INTO `subject` (`id`, `name`, `school_id`) VALUES
(1, 'MATHEMATICS', 1),
(11, 'ENGLISH', 1),
(14, 'KISWAHILI', 1),
(15, 'PRE TECHNICAL', 1),
(16, 'MATHEMATICS', 2),
(17, 'ENGLISH', 2),
(18, 'KISWAHILI', 2),
(19, 'PRE-TECHNICALS', 2),
(20, 'INT-SCIENCE', 2),
(21, 'GEOGRAPHY', 2),
(22, 'AGRICULTURE', 2),
(23, 'HOMESCIENCE', 2),
(24, 'CRE', 2),
(25, 'IRE', 2);

-- --------------------------------------------------------

--
-- Table structure for table `teacher`
--

CREATE TABLE `teacher` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `school_id` int(11) DEFAULT NULL,
  `enrolment_no` varchar(50) NOT NULL,
  `date_hired` date DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teacher`
--

INSERT INTO `teacher` (`id`, `user_id`, `name`, `school_id`, `enrolment_no`, `date_hired`) VALUES
(14, 164, 'ISSA Mwikali', 2, '123456', '2025-08-15'),
(15, 166, 'Taabu Mafimbo', 2, '34578', '2025-08-15'),
(16, 165, 'Omar Sufiani', 2, '123456', '2025-08-20');

-- --------------------------------------------------------

--
-- Table structure for table `tsubject_class`
--

CREATE TABLE `tsubject_class` (
  `id` int(11) NOT NULL,
  `teacher_id` int(11) NOT NULL,
  `subject_id` int(11) NOT NULL,
  `class_id` int(11) NOT NULL,
  `school_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tsubject_class`
--

INSERT INTO `tsubject_class` (`id`, `teacher_id`, `subject_id`, `class_id`, `school_id`) VALUES
(14, 14, 16, 19, 2),
(15, 14, 16, 20, 2),
(16, 14, 16, 21, 2),
(17, 14, 20, 19, 2),
(18, 14, 20, 20, 2),
(19, 14, 20, 21, 2),
(20, 15, 16, 22, 2),
(21, 15, 16, 23, 2),
(22, 15, 17, 22, 2),
(23, 15, 17, 23, 2),
(24, 15, 18, 22, 2),
(25, 15, 18, 23, 2),
(26, 16, 16, 18, 2),
(27, 16, 16, 19, 2),
(28, 16, 20, 18, 2),
(29, 16, 20, 19, 2);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `FirstName` varchar(100) DEFAULT NULL,
  `LastName` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `role` enum('admin','user','Superadmin') DEFAULT 'user',
  `school_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `FirstName`, `LastName`, `email`, `password`, `role`, `school_id`, `created_at`) VALUES
(83, 'Abdalla', 'Juma', 'sudi@gmail.com', '$2y$10$e9ilZwRO9SvXg8JjzS3Cd.6roVI6qK1DJX0rYDiw5MscET.PT8j3W', 'admin', 1, '2025-08-14 07:25:55'),
(164, 'ISSA', 'Mwikali', 'issa@gmail.com', '$2y$10$35s9Xho7nNrQo81rW0TXH.9yAqEsyIbiQbqBiSsUEM2jOKh8W3h3a', 'user', 2, '2025-08-14 09:39:49'),
(165, 'Omar', 'Sufiani', 'hommiedelaco@gmail.com', '$2y$10$Y7NXG4eKbXldOnU6Dxd3W.zgyg5Ud5GWy2RWUz/d7kOde.QLDkn8W', 'user', 2, '2025-08-14 09:44:15'),
(166, 'Taabu', 'Mafimbo', 'T@GMAIL.COM', '$2y$10$uGibWi0TPFV4g4bUMWvEdeK3OMuefnymi.Nn3aYFOqMB/LkUlrX3W', 'admin', 2, '2025-08-14 11:29:53');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `class`
--
ALTER TABLE `class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch8` (`school_id`);

--
-- Indexes for table `school`
--
ALTER TABLE `school`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `score`
--
ALTER TABLE `score`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_score` (`std_id`),
  ADD KEY `frk_class` (`class_id`),
  ADD KEY `frk_subject3` (`subject_id`),
  ADD KEY `frk_sch2` (`school_id`),
  ADD KEY `frk_teach` (`teacher_id`);

--
-- Indexes for table `student`
--
ALTER TABLE `student`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch3` (`school_id`);

--
-- Indexes for table `student_subject`
--
ALTER TABLE `student_subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_std` (`student_id`),
  ADD KEY `frk_sbj` (`subject_id`),
  ADD KEY `frk_sch4` (`school_id`),
  ADD KEY `frk_class67` (`class_id`);

--
-- Indexes for table `subject`
--
ALTER TABLE `subject`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch5` (`school_id`);

--
-- Indexes for table `teacher`
--
ALTER TABLE `teacher`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_users` (`user_id`),
  ADD KEY `frk_sCH0` (`school_id`);

--
-- Indexes for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_class2` (`class_id`),
  ADD KEY `frk_subject2` (`subject_id`),
  ADD KEY `frk_teacher2` (`teacher_id`),
  ADD KEY `frk_sch90` (`school_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `frk_sch1` (`school_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `class`
--
ALTER TABLE `class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT for table `school`
--
ALTER TABLE `school`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `score`
--
ALTER TABLE `score`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- AUTO_INCREMENT for table `student`
--
ALTER TABLE `student`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `student_subject`
--
ALTER TABLE `student_subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `subject`
--
ALTER TABLE `subject`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- AUTO_INCREMENT for table `teacher`
--
ALTER TABLE `teacher`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=30;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=167;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `class`
--
ALTER TABLE `class`
  ADD CONSTRAINT `frk_sch8` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `score`
--
ALTER TABLE `score`
  ADD CONSTRAINT `frk_class` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch2` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_score` FOREIGN KEY (`std_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_subject3` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_teach` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student`
--
ALTER TABLE `student`
  ADD CONSTRAINT `frk_sch3` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `student_subject`
--
ALTER TABLE `student_subject`
  ADD CONSTRAINT `frk_class67` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sbj` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch4` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_std` FOREIGN KEY (`student_id`) REFERENCES `student` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `subject`
--
ALTER TABLE `subject`
  ADD CONSTRAINT `frk_sch5` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `teacher`
--
ALTER TABLE `teacher`
  ADD CONSTRAINT `frk_sCH0` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_users` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `tsubject_class`
--
ALTER TABLE `tsubject_class`
  ADD CONSTRAINT `frk_class2` FOREIGN KEY (`class_id`) REFERENCES `class` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_sch90` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_subject2` FOREIGN KEY (`subject_id`) REFERENCES `subject` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `frk_teacher2` FOREIGN KEY (`teacher_id`) REFERENCES `teacher` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `frk_sch1` FOREIGN KEY (`school_id`) REFERENCES `school` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
