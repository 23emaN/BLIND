-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 10:33 AM
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
-- Table structure for table `tbl_activity`
--

CREATE TABLE `tbl_activity` (
  `activity_id` int(11) NOT NULL,
  `activity_title` varchar(100) NOT NULL,
  `attribute_id` int(11) NOT NULL COMMENT 'หมวดหมู่ -> tbl_attribute (attribute_type = 1)',
  `activity_date` date NOT NULL,
  `activity_image` varchar(255) DEFAULT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `location` varchar(255) NOT NULL,
  `max_volunteers` int(11) NOT NULL DEFAULT 0 COMMENT 'จำนวนอาสาที่เปิดรับ',
  `reserve_count` int(11) NOT NULL DEFAULT 0 COMMENT 'จำนวนสำรองที่นั่งอัตโนมัติ',
  `activity_detail` text DEFAULT NULL,
  `activity_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '1 = เปิดรับสมัคร, 0 = ปิด/ยกเลิก',
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT 'soft delete',
  `create_user_id` int(11) NOT NULL,
  `create_at` datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_location`
--

CREATE TABLE `tbl_activity_location` (
  `location_id` int(11) NOT NULL,
  `location_label` varchar(100) NOT NULL COMMENT 'ชื่อย่อแสดงบน chip',
  `location_name` varchar(255) NOT NULL COMMENT 'ชื่อเต็มที่เติมลงช่องสถานที่',
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT 'soft delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_activity_location`
--

INSERT INTO `tbl_activity_location` (`location_id`, `location_label`, `location_name`, `sort_order`, `active_status`) VALUES
(1, 'ห้องบันทึกเสียง', 'ห้องบันทึกเสียง ชั้น 3 มูลนิธิช่วยคนตาบอดแห่งประเทศไทย', 1, '1'),
(2, 'อาคารเรียนรวม', 'อาคารเรียนรวม มูลนิธิช่วยคนตาบอดแห่งประเทศไทย', 2, '1'),
(3, 'สวนสันติภาพ', 'สวนสันติภาพ กรุงเทพฯ', 3, '1');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_timeslot`
--

CREATE TABLE `tbl_activity_timeslot` (
  `timeslot_id` int(11) NOT NULL,
  `timeslot_name` varchar(50) NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `sort_order` int(11) NOT NULL DEFAULT 0,
  `active_status` varchar(1) NOT NULL DEFAULT '1' COMMENT 'soft delete'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_activity_timeslot`
--

INSERT INTO `tbl_activity_timeslot` (`timeslot_id`, `timeslot_name`, `start_time`, `end_time`, `sort_order`, `active_status`) VALUES
(1, 'ช่วงเช้า', '09:00:00', '12:00:00', 1, '1'),
(2, 'ช่วงบ่าย', '13:00:00', '16:00:00', 2, '1'),
(3, 'ทั้งวัน', '09:00:00', '16:00:00', 3, '1');

-- --------------------------------------------------------

--
-- Table structure for table `tbl_activity_volunteer`
--

CREATE TABLE `tbl_activity_volunteer` (
  `reg_id` int(11) NOT NULL,
  `activity_id` int(11) NOT NULL,
  `volunteer_id` int(11) NOT NULL,
  `reg_status` varchar(1) NOT NULL DEFAULT '1' COMMENT '1 = ยืนยันเข้าร่วม, 0 = ยกเลิก',
  `attend_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ระบุ, 1 = เข้าร่วมจริง, 2 = ไม่มา',
  `checked_in_at` datetime DEFAULT NULL COMMENT 'เวลาเช็คชื่อเข้าร่วม',
  `checkin_method` varchar(1) DEFAULT NULL COMMENT '1 = เช็คอินเอง, 2 = เจ้าหน้าที่เช็คอินให้',
  `checkin_photo` varchar(255) DEFAULT NULL COMMENT 'รูปถ่ายตอนเช็คอินเอง (key รูปใน S3)',
  `checkin_verify_status` varchar(1) NOT NULL DEFAULT '0' COMMENT '0 = รอตรวจสอบ, 1 = ยืนยันแล้ว, 2 = ไม่ผ่าน',
  `checked_out_at` datetime DEFAULT NULL COMMENT 'เวลาเช็คเอาท์',
  `hours_credited` decimal(4,1) DEFAULT NULL COMMENT 'ชั่วโมงจิตอาสาที่ได้รับ (ใช้ออกใบรับรอง)',
  `remark` varchar(255) DEFAULT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `tbl_attribute`
--

CREATE TABLE `tbl_attribute` (
  `attribute_id` int(11) NOT NULL,
  `attribute_name` text NOT NULL,
  `attribute_icon` varchar(50) DEFAULT NULL COMMENT 'material icon (สำหรับหมวดหมู่กิจกรรม)',
  `attribute_desc` varchar(255) DEFAULT NULL COMMENT 'คำอธิบายสั้น',
  `active_status` varchar(1) NOT NULL DEFAULT '1',
  `create_user_id` int(11) NOT NULL,
  `create_at` datetime NOT NULL,
  `attribute_type` varchar(1) DEFAULT NULL COMMENT '1 = กิจกรรมที่สนใจเข้าร่วม\r\n2 = ทักษะและความถนัด'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `tbl_attribute`
--

INSERT INTO `tbl_attribute` (`attribute_id`, `attribute_name`, `attribute_icon`, `attribute_desc`, `active_status`, `create_user_id`, `create_at`, `attribute_type`) VALUES
(6, 'ผลิตสื่อและเสียง', 'mic', 'บันทึกเสียงบทเรียน หนังสือเสียง', '1', 1, '2026-10-06 15:04:23', '1'),
(7, 'นำทางและสันทนาการ', 'directions_walk', 'พาเดิน–วิ่ง นันทนาการภายนอก', '1', 1, '2026-10-06 15:04:23', '1'),
(8, 'ช่วยงานเอกสาร', 'description', 'พิมพ์อักษรเบรลล์ ตรวจทานไฟล์', '1', 1, '2026-10-06 15:04:23', '1'),
(9, 'อบรมและทั่วไป', 'school', 'เวิร์กช็อป ช่วยจัดเตรียมสถานที่', '1', 1, '2026-10-06 15:04:23', '1');

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
(2, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-06 09:22:32', '2026-10-07 09:22:32', NULL),
(3, 1, '::1', 'curl/8.14.1', '2026-10-06 13:45:51', '2026-10-07 13:45:51', NULL),
(4, 1, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/154.0.0.0 Safari/537.36', '2026-10-06 13:47:09', '2026-10-07 13:47:09', NULL),
(5, 1, '::1', 'curl/8.14.1', '2026-10-06 13:52:13', '2026-10-07 13:52:13', NULL),
(6, 1, '::1', 'curl/8.14.1', '2026-10-06 14:13:33', '2026-10-07 14:13:33', NULL),
(7, 1, '::1', 'curl/8.14.1', '2026-10-06 14:35:13', '2026-10-07 14:35:13', NULL),
(8, 1, '::1', 'curl/8.14.1', '2026-10-06 14:38:54', '2026-10-07 14:38:54', NULL),
(9, 1, '::1', 'curl/8.14.1', '2026-10-06 14:47:29', '2026-10-07 14:47:29', NULL),
(10, 1, '::1', 'curl/8.14.1', '2026-10-06 15:05:10', '2026-10-07 15:05:10', NULL);

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
(1, 'admin', '$2y$10$VNFYFF1FgAict7l5pnrXqeNpN.gbuNns1Db3agPdd30OqEXw/UT2m', 'Admin', 'User', '1', '1', 1, '2026-10-05 14:00:00');

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
-- Indexes for table `tbl_activity`
--
ALTER TABLE `tbl_activity`
  ADD PRIMARY KEY (`activity_id`),
  ADD KEY `idx_act_date` (`activity_date`),
  ADD KEY `idx_act_attribute` (`attribute_id`),
  ADD KEY `idx_act_active` (`active_status`);

--
-- Indexes for table `tbl_activity_location`
--
ALTER TABLE `tbl_activity_location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `idx_loc_active` (`active_status`);

--
-- Indexes for table `tbl_activity_timeslot`
--
ALTER TABLE `tbl_activity_timeslot`
  ADD PRIMARY KEY (`timeslot_id`),
  ADD KEY `idx_ts_active` (`active_status`);

--
-- Indexes for table `tbl_activity_volunteer`
--
ALTER TABLE `tbl_activity_volunteer`
  ADD PRIMARY KEY (`reg_id`),
  ADD UNIQUE KEY `uniq_act_vol` (`activity_id`,`volunteer_id`),
  ADD KEY `idx_av_activity` (`activity_id`),
  ADD KEY `idx_av_volunteer` (`volunteer_id`),
  ADD KEY `idx_av_reg_status` (`reg_status`),
  ADD KEY `idx_av_attend` (`attend_status`),
  ADD KEY `idx_av_verify` (`checkin_verify_status`);

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
-- AUTO_INCREMENT for table `tbl_activity`
--
ALTER TABLE `tbl_activity`
  MODIFY `activity_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_activity_location`
--
ALTER TABLE `tbl_activity_location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_activity_timeslot`
--
ALTER TABLE `tbl_activity_timeslot`
  MODIFY `timeslot_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_activity_volunteer`
--
ALTER TABLE `tbl_activity_volunteer`
  MODIFY `reg_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_attribute`
--
ALTER TABLE `tbl_attribute`
  MODIFY `attribute_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `tbl_login_token`
--
ALTER TABLE `tbl_login_token`
  MODIFY `token_code` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `tbl_mapping_attribute`
--
ALTER TABLE `tbl_mapping_attribute`
  MODIFY `mapping_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `tbl_user`
--
ALTER TABLE `tbl_user`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `tbl_volunteer`
--
ALTER TABLE `tbl_volunteer`
  MODIFY `volunteer_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
