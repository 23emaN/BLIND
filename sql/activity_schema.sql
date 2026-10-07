-- ============================================================
-- ระบบกิจกรรม (Activity) — schema รวมฉบับสุดท้าย (ให้สอดคล้องหน้าบ้าน project_wcag)
--
-- หมวดหมู่กิจกรรม = tbl_attribute (attribute_type = '1') ใช้ร่วมกับ "กิจกรรมที่สนใจ"
--   (ดู activity_merge_category.sql สำหรับการเพิ่ม attribute_icon/attribute_desc + seed 4 หมวด)
-- ตารางในไฟล์นี้:
--   tbl_activity            กิจกรรม
--   tbl_activity_location   สถานที่ที่ใช้บ่อย (preset)
--   tbl_activity_timeslot   ช่วงเวลาที่ใช้บ่อย (preset)
--   tbl_activity_volunteer  การลงทะเบียนเข้าร่วม (+ เช็คชื่อ/ชั่วโมงจิตอาสา)
-- ใช้ utf8mb4 ทั้งหมด, FK เป็น logical (ไม่ผูก constraint จริง ตามสไตล์ schema เดิม)
-- ============================================================

SET NAMES utf8mb4;

-- กิจกรรม
CREATE TABLE IF NOT EXISTS `tbl_activity` (
  `activity_id`     INT(11) NOT NULL AUTO_INCREMENT,
  `activity_title`  VARCHAR(100) NOT NULL,
  `attribute_id`    INT(11) NOT NULL COMMENT 'หมวดหมู่ -> tbl_attribute (attribute_type = 1)',
  `activity_date`   DATE NOT NULL,
  `start_time`      TIME NOT NULL,
  `end_time`        TIME NOT NULL,
  `location`        VARCHAR(255) NOT NULL,
  `max_volunteers`  INT(11) NOT NULL DEFAULT 0 COMMENT 'จำนวนอาสาที่เปิดรับ',
  `reserve_count`   INT(11) NOT NULL DEFAULT 0 COMMENT 'จำนวนสำรองที่นั่งอัตโนมัติ',
  `grant_hours`        VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '1 = ได้ชั่วโมงจิตอาสา, 0 = ไม่ได้ชั่วโมง',
  `hours_per_person`   DECIMAL(4,1) DEFAULT NULL COMMENT 'จำนวนชั่วโมงจิตอาสาต่อคน',
  `hours_count_method` VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '1 = ตามเวลาที่เข้าร่วมจริง (ไม่เกินที่ระบุ), 2 = เข้าร่วมแล้วได้เต็มตามที่ระบุ',
  `activity_detail` TEXT DEFAULT NULL,
  `activity_status` VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '1 = เปิดรับสมัคร, 0 = ปิด/ยกเลิก',
  `active_status`   VARCHAR(1) NOT NULL DEFAULT '1' COMMENT 'soft delete',
  `create_user_id`  INT(11) NOT NULL,
  `create_at`       DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`activity_id`),
  KEY `idx_act_date` (`activity_date`),
  KEY `idx_act_attribute` (`attribute_id`),
  KEY `idx_act_active` (`active_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- สถานที่ที่ใช้บ่อย (preset ช่วยเติมช่องสถานที่; สถานที่จริงเก็บเป็น text ใน tbl_activity.location)
--   location_label = ชื่อย่อแสดงบนปุ่ม chip, location_name = ชื่อเต็มที่เติมลงช่องสถานที่
CREATE TABLE IF NOT EXISTS `tbl_activity_location` (
  `location_id`    INT(11) NOT NULL AUTO_INCREMENT,
  `location_label` VARCHAR(100) NOT NULL COMMENT 'ชื่อย่อแสดงบน chip',
  `location_name`  VARCHAR(255) NOT NULL COMMENT 'ชื่อเต็มที่เติมลงช่องสถานที่',
  `sort_order`     INT(11) NOT NULL DEFAULT 0,
  `active_status`  VARCHAR(1) NOT NULL DEFAULT '1' COMMENT 'soft delete',
  PRIMARY KEY (`location_id`),
  KEY `idx_loc_active` (`active_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- ช่วงเวลาที่ใช้บ่อย (preset ช่วยเติมเวลาเริ่ม/สิ้นสุด)
CREATE TABLE IF NOT EXISTS `tbl_activity_timeslot` (
  `timeslot_id`   INT(11) NOT NULL AUTO_INCREMENT,
  `timeslot_name` VARCHAR(50) NOT NULL,
  `start_time`    TIME NOT NULL,
  `end_time`      TIME NOT NULL,
  `sort_order`    INT(11) NOT NULL DEFAULT 0,
  `active_status` VARCHAR(1) NOT NULL DEFAULT '1' COMMENT 'soft delete',
  PRIMARY KEY (`timeslot_id`),
  KEY `idx_ts_active` (`active_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- การลงทะเบียนเข้าร่วม (เชื่อม tbl_activity <-> tbl_volunteer) + เช็คชื่อ/ชั่วโมงจิตอาสา
CREATE TABLE IF NOT EXISTS `tbl_activity_volunteer` (
  `reg_id`         INT(11) NOT NULL AUTO_INCREMENT,
  `activity_id`    INT(11) NOT NULL,
  `volunteer_id`   INT(11) NOT NULL,
  `reg_status`     VARCHAR(1) NOT NULL DEFAULT '1' COMMENT '1 = ยืนยันเข้าร่วม, 0 = ยกเลิก',
  `attend_status`  VARCHAR(1) NOT NULL DEFAULT '0' COMMENT '0 = ยังไม่ระบุ, 1 = เข้าร่วมจริง, 2 = ไม่มา',
  `checked_in_at`  DATETIME DEFAULT NULL COMMENT 'เวลาเช็คชื่อเข้าร่วม',
  `checkin_method` VARCHAR(1) DEFAULT NULL COMMENT '1 = เช็คอินเอง, 2 = เจ้าหน้าที่เช็คอินให้',
  `checkin_photo`  VARCHAR(255) DEFAULT NULL COMMENT 'รูปถ่ายตอนเช็คอินเอง (key รูปใน S3)',
  `checkin_verify_status` VARCHAR(1) NOT NULL DEFAULT '0' COMMENT '0 = รอตรวจสอบ, 1 = ยืนยันแล้ว, 2 = ไม่ผ่าน',
  `checked_out_at` DATETIME DEFAULT NULL COMMENT 'เวลาเช็คเอาท์',
  `hours_credited` DECIMAL(4,1) DEFAULT NULL COMMENT 'ชั่วโมงจิตอาสาที่ได้รับ (ใช้ออกใบรับรอง)',
  `remark`         VARCHAR(255) DEFAULT NULL,
  `created_at`     DATETIME NOT NULL DEFAULT current_timestamp(),
  `updated_at`     DATETIME DEFAULT NULL,
  PRIMARY KEY (`reg_id`),
  UNIQUE KEY `uniq_act_vol` (`activity_id`, `volunteer_id`),
  KEY `idx_av_activity` (`activity_id`),
  KEY `idx_av_volunteer` (`volunteer_id`),
  KEY `idx_av_reg_status` (`reg_status`),
  KEY `idx_av_attend` (`attend_status`),
  KEY `idx_av_verify` (`checkin_verify_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- seed preset: สถานที่ (ชื่อย่อ + ชื่อเต็ม)
INSERT INTO `tbl_activity_location` (`location_label`, `location_name`, `sort_order`) VALUES
  ('ห้องบันทึกเสียง', 'ห้องบันทึกเสียง ชั้น 3 มูลนิธิช่วยคนตาบอดแห่งประเทศไทย', 1),
  ('อาคารเรียนรวม',  'อาคารเรียนรวม มูลนิธิช่วยคนตาบอดแห่งประเทศไทย',       2),
  ('สวนสันติภาพ',    'สวนสันติภาพ กรุงเทพฯ',                                3);

-- seed preset: ช่วงเวลา
INSERT INTO `tbl_activity_timeslot` (`timeslot_name`, `start_time`, `end_time`, `sort_order`) VALUES
  ('ช่วงเช้า', '09:00:00', '12:00:00', 1),
  ('ช่วงบ่าย', '13:00:00', '16:00:00', 2),
  ('ทั้งวัน',  '09:00:00', '16:00:00', 3);
