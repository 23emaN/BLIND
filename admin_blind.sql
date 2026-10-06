-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 04:58 AM
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
-- Database: `admin_blind`
--

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attribute`
--

CREATE TABLE `tbl_attribute` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` text NOT NULL,
  `active_status` varchar(1) NOT NULL DEFAULT '1',
  `create_user_id` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `attribute_type` varchar(1) DEFAULT NULL COMMENT '1 = กิจกรรมที่สนใจเข้าร่วม\r\n2 = ทักษะและความถนัด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_login_token`
--

CREATE TABLE `tbl_login_token` (
  `token_code` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `ip_address` varchar(50) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `create_datetime` datetime NOT NULL,
  `expire_datetime` datetime NOT NULL,
  `end_datetime` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_login_token`
--

INSERT INTO `tbl_login_token` (`token_code`, `user_id`, `ip_address`, `user_agent`, `create_datetime`, `expire_datetime`, `end_datetime`) VALUES
(1, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-05 14:53:48', '2026-10-06 14:53:48', NULL),
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-06 09:22:32', '2026-10-07 09:22:32', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `tbl_mapping_attribute`
--

CREATE TABLE `tbl_mapping_attribute` (
  `mapping_id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `attribute_id` int(11) NOT NULL,
  `create_user_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_user`
--

CREATE TABLE `tbl_user` (
  `user_id` int(11) NOT NULL,
  `user_name` varchar(50) NOT NULL,
  `user_password` varchar(255) NOT NULL,
  `user_firstname` varchar(100) DEFAULT NULL,
  `user_lastname` varchar(100) DEFAULT NULL,
  `active_status` varchar(1) NOT NULL DEFAULT '1',
  `user_status` varchar(1) NOT NULL DEFAULT '1',
  `is_super_admin` int(1) NOT NULL DEFAULT 0,
  `create_at` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_user`
--

INSERT INTO `tbl_user` (`user_id`, `user_name`, `user_password`, `user_firstname`, `user_lastname`, `active_status`, `user_status`, `is_super_admin`, `create_at`) VALUES
(1, 'admin', '$2y$10$t1AZk6m9bM2zRt4rihOYQOIBXqS07P3FaxL9lhqwhU7ifMix.Y.Fe', 'Admin', 'User', '1', '1', 1, '2026-10-05 14:00:00');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_volunteer`
--

CREATE TABLE `tbl_volunteer` (
  `volunteer_id` int(11) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `volunteer_phone` varchar(20) DEFAULT NULL,
  `volunteer_image` varchar(255) NOT NULL COMMENT 'ภาพถ่ายผู้สมัคร',
  `citizen_id` varchar(13) NOT NULL COMMENT 'เลขบัตรประชาชน',
  `approve_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = สถานะเริ่มแรก\r\n1 = reject ปฏิเสธ\r\n2 = approve อนุมัติ',
  `reject_remark` text DEFAULT NULL COMMENT 'หมายเหตุในการปฏิเสธ',
  `create_at` datetime NOT NULL DEFAULT current_timestamp(),
  `citizen_image` varchar(255) NOT NULL COMMENT 'รูปบัตรประชาชน'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `tbl_attribute`
--
ALTER TABLE `tbl_attribute`
  ADD PRIMARY KEY (`attribute_id`),
  ADD KEY `idx_attribute_type` (`attribute_type`),
  ADD KEY `idx_active_status` (`active_status`);

--
-- Indexes for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  ADD PRIMARY KEY (`token_code`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expire_datetime` (`expire_datetime`);

--
-- Indexes for table `tbl_mapping_attribute`
--
ALTER TABLE `tbl_mapping_attribute`
  ADD PRIMARY KEY (`mapping_id`),
  ADD KEY `idx_volunteer_id` (`volunteer_id`),
  ADD KEY `idx_attribute_id` (`attribute_id`);

--
-- Indexes for table `tbl_user`
--
ALTER TABLE `tbl_user`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `idx_user_name` (`user_name`),
  ADD KEY `idx_user_status` (`user_status`);

--
-- Indexes for table `tbl_volunteer`
--
ALTER TABLE `tbl_volunteer`
  ADD PRIMARY KEY (`volunteer_id`),
  ADD UNIQUE KEY `idx_citizen_id` (`citizen_id`),
  ADD KEY `idx_approve_status` (`approve_status`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `tbl_attribute`
--
ALTER TABLE `tbl_attribute`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `tbl_mapping_attribute`
--
ALTER TABLE `tbl_mapping_attribute`
  MODIFY `mapping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `tbl_volunteer`
--
ALTER TABLE `tbl_volunteer`
  MODIFY `volunteer_id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
