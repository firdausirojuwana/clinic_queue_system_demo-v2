-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: May 26, 2026 at 10:53 PM
-- Server version: 8.4.3
-- PHP Version: 8.3.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `clinic_queue`
--

-- --------------------------------------------------------

--
-- Table structure for table `appointment`
--

CREATE TABLE `appointment` (
  `appoint_id` int NOT NULL,
  `patient_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `queue_number` int NOT NULL,
  `appointment_datetime` datetime NOT NULL,
  `status` enum('Waiting','In Consultation','Completed','Cancelled') DEFAULT 'Waiting',
  `symptoms` text,
  `diagnosis` text,
  `blood_pressure` varchar(20) DEFAULT NULL,
  `temperature` decimal(4,1) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `appointment`
--

INSERT INTO `appointment` (`appoint_id`, `patient_id`, `doctor_id`, `queue_number`, `appointment_datetime`, `status`, `symptoms`, `diagnosis`, `blood_pressure`, `temperature`, `created_at`) VALUES
(6, 1, 1, 1, '2026-05-27 05:55:00', 'Waiting', 'Fever', NULL, NULL, NULL, '2026-05-26 21:55:24'),
(8, 5, 2, 3, '2026-04-29 06:19:00', 'Completed', 'idk', NULL, NULL, NULL, '2026-05-26 22:19:55');

-- --------------------------------------------------------

--
-- Table structure for table `doctor`
--

CREATE TABLE `doctor` (
  `doctor_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `specialty` varchar(100) DEFAULT 'General Practice',
  `phone` varchar(20) DEFAULT NULL,
  `room_number` varchar(20) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `doctor`
--

INSERT INTO `doctor` (`doctor_id`, `name`, `specialty`, `phone`, `room_number`, `created_at`) VALUES
(1, 'Dr. Farhaan', 'General Practice', '04-4412000', 'Room 1', '2026-05-26 20:34:54'),
(2, 'Dr. Nurul Ain', 'General Practice', '04-4412001', 'Room 2', '2026-05-26 20:34:54'),
(3, 'Dr. Zulkifli', 'General Practice', '04-4412002', 'Room 3', '2026-05-26 20:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `medicine`
--

CREATE TABLE `medicine` (
  `medicine_id` int NOT NULL,
  `medicine_name` varchar(100) NOT NULL,
  `dosage` varchar(50) DEFAULT NULL,
  `unit_price` decimal(8,2) NOT NULL DEFAULT '0.00',
  `stock_quantity` int NOT NULL DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `medicine`
--

INSERT INTO `medicine` (`medicine_id`, `medicine_name`, `dosage`, `unit_price`, `stock_quantity`, `created_at`) VALUES
(1, 'Panadol', '500mg', 2.00, 99, '2026-05-26 20:34:54'),
(2, 'Vitamin C', '1000mg', 5.00, 45, '2026-05-26 20:34:54'),
(3, 'Clarinase', '5mg', 3.50, 80, '2026-05-26 20:34:54'),
(4, 'Strepsils', '8.75mg', 4.00, 60, '2026-05-26 20:34:54'),
(5, 'Augmentin', '625mg', 8.00, 40, '2026-05-26 20:34:54'),
(6, 'Ibuprofen', '400mg', 2.50, 90, '2026-05-26 20:34:54');

-- --------------------------------------------------------

--
-- Table structure for table `patient`
--

CREATE TABLE `patient` (
  `patient_id` int NOT NULL,
  `name` varchar(100) NOT NULL,
  `ic_number` varchar(20) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `gender` enum('Male','Female','Other') DEFAULT NULL,
  `address` text,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `patient`
--

INSERT INTO `patient` (`patient_id`, `name`, `ic_number`, `phone`, `email`, `gender`, `address`, `created_at`) VALUES
(1, 'Adnan', '020411-02-0197', '017-5650624', 'adnan12@gmail.com', 'Male', 'No. 12, Jalan Mawar, Sungai Petani, Kedah', '2026-05-26 20:34:54'),
(2, 'Siti Aisyah', '010523-08-1234', '011-2345678', 'sitiaisyah@gmail.com', 'Female', 'No. 5, Jalan Kenanga, Alor Setar, Kedah', '2026-05-26 20:34:54'),
(3, 'Hafiz', '990314-04-5678', '019-8765432', 'hafiz99@gmail.com', 'Male', 'No. 88, Jalan Dahlia, Kulim, Kedah', '2026-05-26 20:34:54'),
(5, 'izz', '897543789435', '435345435435', '234@gmail', 'Male', 'gyure4wgyuwe', '2026-05-26 22:03:06');

-- --------------------------------------------------------

--
-- Table structure for table `prescription`
--

CREATE TABLE `prescription` (
  `prescript_id` int NOT NULL,
  `doctor_id` int NOT NULL,
  `appoint_id` int NOT NULL,
  `presc_date` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total_cost` decimal(8,2) DEFAULT '0.00',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescription`
--

INSERT INTO `prescription` (`prescript_id`, `doctor_id`, `appoint_id`, `presc_date`, `total_cost`, `created_at`) VALUES
(4, 1, 8, '2026-05-27 06:20:56', 2.00, '2026-05-26 22:20:56'),
(5, 1, 8, '2026-05-27 06:51:24', 25.00, '2026-05-26 22:51:24');

-- --------------------------------------------------------

--
-- Table structure for table `prescription_medicine`
--

CREATE TABLE `prescription_medicine` (
  `pm_id` int NOT NULL,
  `prescription_id` int NOT NULL,
  `medicine_id` int NOT NULL,
  `quantity` int NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

--
-- Dumping data for table `prescription_medicine`
--

INSERT INTO `prescription_medicine` (`pm_id`, `prescription_id`, `medicine_id`, `quantity`) VALUES
(8, 4, 1, 1),
(9, 5, 2, 5);

--
-- Triggers `prescription_medicine`
--
DELIMITER $$
CREATE TRIGGER `reduce_medicine_stock` AFTER INSERT ON `prescription_medicine` FOR EACH ROW BEGIN

    UPDATE medicine
    SET stock_quantity = stock_quantity - NEW.quantity
    WHERE medicine_id = NEW.medicine_id;

END
$$
DELIMITER ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `appointment`
--
ALTER TABLE `appointment`
  ADD PRIMARY KEY (`appoint_id`),
  ADD KEY `patient_id` (`patient_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `idx_appointment_date` (`appointment_datetime`);

--
-- Indexes for table `doctor`
--
ALTER TABLE `doctor`
  ADD PRIMARY KEY (`doctor_id`);

--
-- Indexes for table `medicine`
--
ALTER TABLE `medicine`
  ADD PRIMARY KEY (`medicine_id`),
  ADD KEY `idx_medicine_name` (`medicine_name`);

--
-- Indexes for table `patient`
--
ALTER TABLE `patient`
  ADD PRIMARY KEY (`patient_id`),
  ADD UNIQUE KEY `ic_number` (`ic_number`),
  ADD KEY `idx_patient_name` (`name`);

--
-- Indexes for table `prescription`
--
ALTER TABLE `prescription`
  ADD PRIMARY KEY (`prescript_id`),
  ADD KEY `doctor_id` (`doctor_id`),
  ADD KEY `appoint_id` (`appoint_id`);

--
-- Indexes for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  ADD PRIMARY KEY (`pm_id`),
  ADD KEY `prescription_id` (`prescription_id`),
  ADD KEY `medicine_id` (`medicine_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `appointment`
--
ALTER TABLE `appointment`
  MODIFY `appoint_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `doctor`
--
ALTER TABLE `doctor`
  MODIFY `doctor_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `medicine`
--
ALTER TABLE `medicine`
  MODIFY `medicine_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `patient`
--
ALTER TABLE `patient`
  MODIFY `patient_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescription`
--
ALTER TABLE `prescription`
  MODIFY `prescript_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  MODIFY `pm_id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `appointment`
--
ALTER TABLE `appointment`
  ADD CONSTRAINT `appointment_ibfk_1` FOREIGN KEY (`patient_id`) REFERENCES `patient` (`patient_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `appointment_ibfk_2` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE RESTRICT ON UPDATE CASCADE;

--
-- Constraints for table `prescription`
--
ALTER TABLE `prescription`
  ADD CONSTRAINT `prescription_ibfk_1` FOREIGN KEY (`doctor_id`) REFERENCES `doctor` (`doctor_id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  ADD CONSTRAINT `prescription_ibfk_2` FOREIGN KEY (`appoint_id`) REFERENCES `appointment` (`appoint_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `prescription_medicine`
--
ALTER TABLE `prescription_medicine`
  ADD CONSTRAINT `prescription_medicine_ibfk_1` FOREIGN KEY (`prescription_id`) REFERENCES `prescription` (`prescript_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `prescription_medicine_ibfk_2` FOREIGN KEY (`medicine_id`) REFERENCES `medicine` (`medicine_id`) ON DELETE RESTRICT ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
