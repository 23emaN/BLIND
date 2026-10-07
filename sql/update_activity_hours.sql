-- ============================================================
-- UPDATE (delta): เพิ่มการตั้งค่า "ชั่วโมงจิตอาสา" ให้ tbl_activity
--   grant_hours         = กิจกรรมนี้ให้ชั่วโมงจิตอาสาหรือไม่
--   hours_per_person    = จำนวนชั่วโมงต่อคน
--   hours_count_method  = วิธีนับชั่วโมง
-- ใช้ต่อจากไฟล์ DB ชุดแรกที่ส่งไปแล้ว — ต้องมี SET NAMES utf8mb4 เสมอ
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `tbl_activity`
  ADD COLUMN IF NOT EXISTS `grant_hours` VARCHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1 = ได้ชั่วโมงจิตอาสา, 0 = ไม่ได้ชั่วโมง' AFTER `reserve_count`,
  ADD COLUMN IF NOT EXISTS `hours_per_person` DECIMAL(4,1) DEFAULT NULL
    COMMENT 'จำนวนชั่วโมงจิตอาสาต่อคน' AFTER `grant_hours`,
  ADD COLUMN IF NOT EXISTS `hours_count_method` VARCHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1 = ตามเวลาที่เข้าร่วมจริง (ไม่เกินที่ระบุ), 2 = เข้าร่วมแล้วได้เต็มตามที่ระบุ' AFTER `hours_per_person`;
