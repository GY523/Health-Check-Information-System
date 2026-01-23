-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Jan 23, 2026 at 10:02 AM
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
-- Database: `server_loaning_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `assets`
--

CREATE TABLE `assets` (
  `asset_id` int(11) NOT NULL,
  `asset_type` enum('Server','Security Appliance','Network Device','Other') NOT NULL,
  `manufacturer` varchar(100) DEFAULT NULL,
  `model` varchar(100) NOT NULL,
  `serial_number` varchar(100) DEFAULT NULL,
  `specifications` text DEFAULT NULL,
  `status` enum('Available','On Loan','Maintenance','Retired') DEFAULT 'Available',
  `location` varchar(200) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `assets`
--

INSERT INTO `assets` (`asset_id`, `asset_type`, `manufacturer`, `model`, `serial_number`, `specifications`, `status`, `location`, `notes`, `created_at`, `updated_at`) VALUES
(1, 'Server', 'Dell', 'PowerEdge R740', 'SN001234567', 'Intel Xeon, 64GB RAM, 2TB SSD', 'Available', 'Server Room A', NULL, '2026-01-19 02:17:29', '2026-01-19 02:17:29'),
(2, 'Server', 'HP', 'ProLiant DL380', 'SN987654321', 'Intel Xeon, 128GB RAM, 4TB HDD', 'Available', 'Server Room A', NULL, '2026-01-19 02:17:29', '2026-01-19 02:17:29'),
(3, 'Security Appliance', 'Cisco', 'ASA 5516-X', 'SN456789123', 'Firewall, 8 Ports, 1Gbps', 'Available', 'Network Lab', NULL, '2026-01-19 02:17:29', '2026-01-19 02:17:29'),
(4, 'Network Device', 'Juniper', 'EX4300-48T', 'SN789123456', '48-Port Switch, 10G Uplinks', 'Available', 'Storage Room', NULL, '2026-01-19 02:17:29', '2026-01-22 07:38:26'),
(5, 'Server', 'Sangfor', 'aServer-2100', '9C1C000138', 'CPU: 8 core(s) 16 Thread X 2 (Intel(R) Xeon(R) CPU E5-2630 v3 @ 2.40GHz\r\nRam: 384 GB\r\nDisk: 1*7.3TB HDD + 1*1.7TB SSD + 1*119.2GB SSD', 'Available', 'on the floor', 'helloi', '2026-01-19 07:37:30', '2026-01-22 06:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `loans`
--

CREATE TABLE `loans` (
  `loan_id` int(11) NOT NULL,
  `asset_id` int(11) NOT NULL,
  `created_by_user_id` int(11) NOT NULL,
  `loan_start_date` date NOT NULL,
  `expected_return_date` date NOT NULL,
  `actual_return_date` date DEFAULT NULL,
  `loan_purpose` text DEFAULT NULL,
  `customer_company` varchar(200) NOT NULL,
  `customer_email` varchar(200) DEFAULT NULL,
  `status` enum('Active','Returned','Cancelled') DEFAULT 'Active',
  `admin_notes` text DEFAULT NULL,
  `return_notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `loans`
--

INSERT INTO `loans` (`loan_id`, `asset_id`, `created_by_user_id`, `loan_start_date`, `expected_return_date`, `actual_return_date`, `loan_purpose`, `customer_company`, `customer_email`, `status`, `admin_notes`, `return_notes`, `created_at`, `updated_at`) VALUES
(1, 4, 2, '2026-01-19', '2026-02-02', NULL, 'Network testing project', '', NULL, 'Cancelled', NULL, 'hello', '2026-01-19 02:17:29', '2026-01-21 16:11:19'),
(2, 5, 1, '2026-01-21', '2026-02-20', '2026-01-21', 'HCI PoC', 'Thompson hospital', '', 'Returned', 'requires 10TB storage', '', '2026-01-21 14:36:57', '2026-01-21 16:00:02'),
(3, 4, 1, '2026-01-21', '2026-02-20', NULL, 'PoC', 'Testing company', '', 'Cancelled', '', 'testing', '2026-01-21 16:13:38', '2026-01-22 07:38:26'),
(4, 5, 1, '2026-01-22', '2026-02-21', '2026-01-22', 'Poc', 'Thompson hospital', '', 'Returned', '', '', '2026-01-22 06:28:29', '2026-01-22 06:33:02');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `email` varchar(100) DEFAULT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `role` enum('admin','engineer') DEFAULT 'engineer',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `username`, `password`, `full_name`, `email`, `phone`, `department`, `role`, `is_active`, `created_at`) VALUES
(1, 'admin', '0192023a7bbd73250516f069df18b500', 'System Administrator', 'admin@company.com', NULL, NULL, 'admin', 1, '2026-01-19 02:17:29'),
(2, 'john.doe', '482c811da5d5b4bc6d497ffa98491e38', 'John Doe', 'john.doe@company.com', NULL, 'IT Department', 'engineer', 1, '2026-01-19 02:17:29'),
(3, 'jane.smith', '482c811da5d5b4bc6d497ffa98491e38', 'Jane Smith', 'jane.smith@company.com', NULL, 'Security Team', '', 1, '2026-01-19 02:17:29');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `assets`
--
ALTER TABLE `assets`
  ADD PRIMARY KEY (`asset_id`),
  ADD UNIQUE KEY `serial_number` (`serial_number`),
  ADD KEY `idx_asset_status` (`status`);

--
-- Indexes for table `loans`
--
ALTER TABLE `loans`
  ADD PRIMARY KEY (`loan_id`),
  ADD KEY `idx_loan_status` (`status`),
  ADD KEY `idx_loan_borrower` (`created_by_user_id`),
  ADD KEY `idx_loan_asset` (`asset_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `assets`
--
ALTER TABLE `assets`
  MODIFY `asset_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `loans`
--
ALTER TABLE `loans`
  MODIFY `loan_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `loans`
--
ALTER TABLE `loans`
  ADD CONSTRAINT `loans_ibfk_1` FOREIGN KEY (`asset_id`) REFERENCES `assets` (`asset_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `loans_ibfk_2` FOREIGN KEY (`created_by_user_id`) REFERENCES `users` (`user_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
