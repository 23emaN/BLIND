-- ============================================================
-- UPDATE (delta): ปรับ tbl_activity_location
-- เปลี่ยนจากชื่อเดียว -> แยก "ชื่อย่อ (บน chip)" กับ "ชื่อเต็ม (เติมลงช่องสถานที่)"
-- ใช้ต่อจากไฟล์ DB ชุดแรก (admin_blind_export_20261006) ที่ส่งไปแล้ว
-- หมายเหตุ: เป็น preset ไม่มีข้อมูลอ้างอิง จึง drop แล้วสร้างใหม่ได้เลย
-- ต้องมี SET NAMES utf8mb4 เสมอ ไม่งั้นภาษาไทยเพี้ยน
-- ============================================================

SET NAMES utf8mb4;

DROP TABLE IF EXISTS `tbl_activity_location`;

CREATE TABLE `tbl_activity_location` (
  `location_id`    INT(11) NOT NULL AUTO_INCREMENT,
  `location_label` VARCHAR(100) NOT NULL COMMENT 'ชื่อย่อแสดงบน chip',
  `location_name`  VARCHAR(255) NOT NULL COMMENT 'ชื่อเต็มที่เติมลงช่องสถานที่',
  `sort_order`     INT(11) NOT NULL DEFAULT 0,
  `active_status`  VARCHAR(1) NOT NULL DEFAULT '1' COMMENT 'soft delete',
  PRIMARY KEY (`location_id`),
  KEY `idx_loc_active` (`active_status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

INSERT INTO `tbl_activity_location` (`location_label`, `location_name`, `sort_order`) VALUES
  ('ห้องบันทึกเสียง', 'ห้องบันทึกเสียง ชั้น 3 มูลนิธิช่วยคนตาบอดแห่งประเทศไทย', 1),
  ('อาคารเรียนรวม',  'อาคารเรียนรวม มูลนิธิช่วยคนตาบอดแห่งประเทศไทย',       2),
  ('สวนสันติภาพ',    'สวนสันติภาพ กรุงเทพฯ',                                3);
