-- phpMyAdmin SQL Dump
-- version 5.2.0
-- https://www.phpmyadmin.net/
--
-- Host: localhost:8889
-- Generation Time: Oct 29, 2024 at 08:28 PM
-- Server version: 5.7.39
-- PHP Version: 8.2.0

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `GymOwnerManager`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `admin_id` int(11) NOT NULL,
  `username` varchar(50) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `profile_picture` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`admin_id`, `username`, `password`, `profile_picture`) VALUES
(1, 'zouhair', 'zouhair123', 'uploads/members/admin/1_istockphoto-1392528328-612x612.jpg');

-- --------------------------------------------------------

--
-- Table structure for table `insurance`
--

CREATE TABLE `insurance` (
  `insurance_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `insurance_start_date` date DEFAULT NULL,
  `insurance_expiry_date` date DEFAULT NULL,
  `price` decimal(10,2) NOT NULL DEFAULT '0.00',
  `shown` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `insurance`
--

INSERT INTO `insurance` (`insurance_id`, `member_id`, `insurance_start_date`, `insurance_expiry_date`, `price`, `shown`) VALUES
(1, 2, '2024-10-16', '2024-10-31', '80.00', 0),
(2, 6, '2024-10-29', '2020-10-05', '50.00', 1),
(5, 8, '2024-10-29', '2010-10-24', '80.00', 1),
(11, 101, '1999-01-01', '1999-12-31', '100.00', 1),
(12, 102, '2000-03-15', '2000-09-15', '80.00', 1),
(13, 103, '1998-02-01', '1998-08-01', '90.00', 1),
(14, 104, '1990-05-10', '1990-11-10', '70.00', 1),
(15, 105, '1992-04-01', '1992-10-01', '85.00', 1),
(16, 106, '1995-06-07', '1995-12-07', '75.00', 1),
(17, 107, '1997-08-20', '1998-02-20', '60.00', 1),
(18, 108, '2002-01-13', '2002-07-13', '65.00', 1),
(19, 109, '1991-03-01', '1991-09-01', '55.00', 1),
(20, 110, '1994-07-14', '1995-01-14', '95.00', 1),
(21, 8, '2024-10-10', '2010-10-02', '90.00', 1),
(22, 9, '2024-10-29', '2024-10-31', '80.00', 0),
(23, 111, '2024-10-29', '2020-10-31', '100.00', 1),
(24, 103, '2024-10-15', '2025-10-18', '100.00', 1),
(25, 9, '2024-10-16', '2020-10-23', '10.00', 1),
(26, 9, '2024-10-29', '2024-11-01', '1000.00', 1);

-- --------------------------------------------------------

--
-- Table structure for table `members`
--

CREATE TABLE `members` (
  `member_id` int(11) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `gender` enum('male','female') DEFAULT NULL,
  `picture` varchar(255) DEFAULT NULL,
  `phone_number` varchar(20) DEFAULT NULL,
  `CNE` varchar(20) DEFAULT NULL,
  `activity_status` enum('active','inactive') DEFAULT NULL,
  `insurance_status` enum('valid','expired') DEFAULT NULL,
  `membership_status` enum('valid','expired') DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `members`
--

INSERT INTO `members` (`member_id`, `first_name`, `last_name`, `gender`, `picture`, `phone_number`, `CNE`, `activity_status`, `insurance_status`, `membership_status`, `created_at`) VALUES
(1, 'salma', 'salm', 'female', 'uploads/members/women/images-16.jpeg', '06772828', 'ba7728', 'active', 'valid', 'valid', '2024-10-01 16:48:00'),
(2, 'ilham', 'ilham', 'female', 'uploads/members/women/images-15.jpeg', '03929392', 'bh2922', 'active', 'valid', 'valid', '2024-10-05 16:48:00'),
(3, 'kawtar', 'kawtar', 'female', 'uploads/members/women/images-14.jpeg', '99391399', 'nm9293', 'active', 'valid', 'valid', '2010-10-05 17:49:00'),
(4, 'Chaimaa', 'chaima', 'female', 'uploads/members/women/293cb0b9d92dfec6dd3395330359c68e.jpg', '684455452', 'b2', 'active', 'valid', 'valid', '2024-10-11 16:49:00'),
(5, 'siham', 'siham', 'female', 'uploads/members/women/9bd9143ea135c291b8494b7bb9c8d419.jpg', '01030139', 'bd9191', 'active', 'valid', 'valid', '2024-10-25 16:50:00'),
(6, 'hdehh', 'hbehh', 'female', 'uploads/members/women/images-12.jpeg', '832888', 'hewh281', 'active', 'valid', 'valid', '2024-10-24 16:51:00'),
(7, 'dhdhhh', 'hwhwhh', 'female', 'uploads/members/women/images-13.jpeg', '3923923', 'hyy38829', 'active', 'valid', 'valid', '2024-10-11 16:52:00'),
(8, 'khalid', 'khalid', 'male', 'uploads/members/men/istockphoto-997461858-612x612.jpg', '238288', 'jb3h3', 'active', 'valid', 'valid', '2024-10-16 16:54:00'),
(9, 'soufian', 'soufian', 'male', 'uploads/members/men/istockphoto-1392528328-612x612.jpg', '382382838', 'bh83', 'active', 'valid', 'valid', '2024-10-24 16:55:00'),
(10, 'yeywh', 'uyewyb', 'male', 'uploads/members/men/dbc4f2c5-1ace-42b7-a474-5e93208a7b18.jpg', '88188318', 'by232', 'active', 'valid', 'valid', '2024-10-18 16:55:00'),
(11, 'dhhabh', 'hhhqh', 'male', 'uploads/members/men/360_F_326985142_1aaKcEjMQW6ULp6oI9MYuv8lN9f8sFmj.jpg', '81881', 'n381', 'active', 'valid', 'valid', '2024-10-12 16:56:00'),
(101, 'Alice', 'Smith', 'female', NULL, '0612345678', 'CNE1999', 'active', NULL, NULL, '1999-04-21 00:00:00'),
(102, 'Bob', 'Jones', 'male', NULL, '0612345679', 'CNE2000', 'inactive', 'valid', 'valid', '2000-07-15 00:00:00'),
(103, 'Carol', 'White', 'female', NULL, '0612345680', 'CNE1998', 'active', NULL, NULL, '1998-05-22 00:00:00'),
(104, 'David', 'Brown', 'male', NULL, '0612345681', 'CNE1990', 'inactive', 'valid', 'valid', '1990-11-09 00:00:00'),
(105, 'Eva', 'Green', 'female', NULL, '0612345682', 'CNE1992', 'active', NULL, NULL, '1992-03-18 00:00:00'),
(106, 'Frank', 'Black', 'male', NULL, '0612345683', 'CNE1995', 'inactive', 'valid', 'valid', '1995-06-07 00:00:00'),
(107, 'Grace', 'Miller', 'female', NULL, '0612345684', 'CNE1997', 'active', NULL, NULL, '1997-09-24 00:00:00'),
(108, 'Henry', 'Wilson', 'male', NULL, '0612345685', 'CNE2002', 'inactive', 'valid', 'valid', '2002-02-13 00:00:00'),
(109, 'Isabel', 'Clark', 'female', NULL, '0612345686', 'CNE1991', 'inactive', 'valid', 'valid', '1991-12-25 00:00:00'),
(110, 'Jack', 'King', 'male', NULL, '0612345687', 'CNE1994', 'inactive', 'valid', 'valid', '1994-08-14 00:00:00'),
(111, 'kjjekfk', 'kjrjk', 'female', 'uploads/members/women/diagram-6.png', '09994304', 'b3929', 'active', 'valid', 'valid', '2024-10-31 15:03:00');

-- --------------------------------------------------------

--
-- Table structure for table `memberships`
--

CREATE TABLE `memberships` (
  `membership_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `membership_type` enum('monthly','yearly') DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `expiry_date` date DEFAULT NULL,
  `remaining_days` int(11) DEFAULT NULL,
  `shown` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `memberships`
--

INSERT INTO `memberships` (`membership_id`, `member_id`, `membership_type`, `start_date`, `expiry_date`, `remaining_days`, `shown`) VALUES
(1, 1, 'monthly', '2024-10-28', '2024-10-31', 3, 1),
(2, 2, 'monthly', '2024-10-01', '2024-11-22', 52, 0),
(3, 3, 'monthly', '2024-10-22', '2020-11-30', -1428, 1),
(4, 4, 'monthly', '2024-10-10', '2010-10-04', 5120, 1),
(5, 8, 'monthly', '2024-10-08', '2024-11-02', 25, 1),
(6, 9, 'monthly', '2024-10-17', '2019-10-16', 1828, 1),
(7, 11, 'monthly', '2024-10-24', '2024-10-19', 5, 1),
(8, 10, 'monthly', '2024-10-29', '2020-11-29', -1430, 1),
(9, 101, 'monthly', '1999-01-01', '1999-12-31', 60, 1),
(11, 103, 'monthly', '1998-05-01', '1999-05-01', 0, 1),
(12, 104, 'monthly', '1990-06-15', '1991-06-15', 10, 1),
(13, 105, 'monthly', '1992-03-01', '1993-03-01', 0, 1),
(14, 106, 'monthly', '1994-01-15', '1995-01-15', 100, 1),
(15, 107, 'monthly', '1995-07-07', '1996-07-07', 0, 1),
(16, 108, 'monthly', '2001-02-01', '2019-02-01', -2097, 1),
(19, 9, 'monthly', '2024-10-29', '2020-11-29', -1430, 1),
(20, 11, 'monthly', '2024-10-01', '2010-10-23', 5092, 1),
(21, 1, 'monthly', '2024-10-01', '2010-10-31', 5084, 1),
(22, 10, 'monthly', '2024-10-29', '2010-10-09', 5134, 1);

-- --------------------------------------------------------

--
-- Table structure for table `notifications`
--

CREATE TABLE `notifications` (
  `notification_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `notification_type` enum('insurance_expired','payment_pending','membership_expired') DEFAULT NULL,
  `notification_message` text,
  `is_viewed` tinyint(1) DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `payment_id` int(11) NOT NULL,
  `member_id` int(11) DEFAULT NULL,
  `membership_id` int(11) DEFAULT NULL,
  `amount_paid` decimal(10,2) DEFAULT NULL,
  `pending_amount` decimal(10,2) DEFAULT NULL,
  `payment_date` date DEFAULT NULL,
  `shown` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Dumping data for table `payments`
--

INSERT INTO `payments` (`payment_id`, `member_id`, `membership_id`, `amount_paid`, `pending_amount`, `payment_date`, `shown`) VALUES
(1, 1, NULL, '300.00', '40.00', '2024-10-28', 1),
(2, 3, NULL, '300.00', '100.00', '2024-10-28', 1),
(3, 4, NULL, '600.00', '90.00', '2024-10-28', 1),
(4, 6, NULL, '500.00', '90.00', '2024-10-28', 1),
(5, 8, NULL, '300.00', '90.00', '2024-10-28', 1),
(6, 10, NULL, '40.00', '80.00', '2024-10-28', 1),
(8, 7, NULL, '500.00', '90.00', '2024-10-03', 1),
(10, 10, NULL, '90.00', '90.00', '2024-10-29', 1),
(11, 101, NULL, '500.00', '0.00', '1999-04-21', 1),
(12, 102, NULL, '700.00', '100.00', '2001-07-15', 1),
(13, 103, NULL, '450.00', '50.00', '1998-05-22', 1),
(14, 104, NULL, '650.00', '90.00', '1990-11-09', 1),
(15, 105, NULL, '300.00', '40.00', '1992-03-18', 1),
(16, 106, NULL, '550.00', '80.00', '1995-06-07', 1),
(17, 107, NULL, '800.00', '0.00', '1997-09-24', 1),
(18, 108, NULL, '720.00', '50.00', '2002-02-13', 1),
(19, 109, NULL, '600.00', '90.00', '1991-12-25', 1),
(20, 110, NULL, '400.00', '60.00', '1994-08-14', 1),
(21, 103, NULL, '90.00', '80.00', '2024-10-29', 1),
(22, 11, NULL, '90.00', '10.00', '2024-10-29', 1),
(23, 10, NULL, '90.00', '100.00', '2024-10-29', 1),
(24, 9, NULL, '90.00', '100.00', '2024-10-29', 1);

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` enum('membership','payment','insurance','members') DEFAULT NULL,
  `member_id` int(11) DEFAULT NULL,
  `membership_type` enum('monthly','yearly') DEFAULT NULL,
  `generated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `details` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`admin_id`);

--
-- Indexes for table `insurance`
--
ALTER TABLE `insurance`
  ADD PRIMARY KEY (`insurance_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `members`
--
ALTER TABLE `members`
  ADD PRIMARY KEY (`member_id`);

--
-- Indexes for table `memberships`
--
ALTER TABLE `memberships`
  ADD PRIMARY KEY (`membership_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `notifications`
--
ALTER TABLE `notifications`
  ADD PRIMARY KEY (`notification_id`),
  ADD KEY `member_id` (`member_id`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`payment_id`),
  ADD KEY `member_id` (`member_id`),
  ADD KEY `membership_id` (`membership_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `member_id` (`member_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `admin_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `insurance`
--
ALTER TABLE `insurance`
  MODIFY `insurance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `members`
--
ALTER TABLE `members`
  MODIFY `member_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=112;

--
-- AUTO_INCREMENT for table `memberships`
--
ALTER TABLE `memberships`
  MODIFY `membership_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `notifications`
--
ALTER TABLE `notifications`
  MODIFY `notification_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `payment_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=25;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `insurance`
--
ALTER TABLE `insurance`
  ADD CONSTRAINT `insurance_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`);

--
-- Constraints for table `memberships`
--
ALTER TABLE `memberships`
  ADD CONSTRAINT `memberships_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`);

--
-- Constraints for table `notifications`
--
ALTER TABLE `notifications`
  ADD CONSTRAINT `notifications_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`);

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`),
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`membership_id`) REFERENCES `memberships` (`membership_id`);

--
-- Constraints for table `reports`
--
ALTER TABLE `reports`
  ADD CONSTRAINT `reports_ibfk_1` FOREIGN KEY (`member_id`) REFERENCES `members` (`member_id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
