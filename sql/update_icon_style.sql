-- ============================================================
-- UPDATE (delta): สไตล์หมวดหมู่กิจกรรม + ไอคอนช่วงเวลา
--   tbl_attribute.attribute_style        = ชุดสี+รูปทรงของหมวดหมู่ (type 1)
--       ค่า: media / guide / document / training → หน้าบ้านใช้เป็น class cat-{style}
--   tbl_activity_timeslot.timeslot_icon  = ไอคอน Material Symbols ของช่วงเวลา
-- รายการค่าที่อนุญาต: app/config/CategoryStyles.php, app/config/TimeslotIcons.php
-- ต้องมี SET NAMES utf8mb4 เสมอ
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `tbl_attribute`
  ADD COLUMN IF NOT EXISTS `attribute_style` VARCHAR(20) DEFAULT NULL
    COMMENT 'ชุดสี+รูปทรงของหมวดหมู่กิจกรรม (media/guide/document/training)' AFTER `attribute_icon`;

ALTER TABLE `tbl_activity_timeslot`
  ADD COLUMN IF NOT EXISTS `timeslot_icon` VARCHAR(50) DEFAULT NULL
    COMMENT 'ไอคอน Material Symbols ของช่วงเวลา' AFTER `end_time`;

-- seed ให้ตรงกับหน้าบ้านปัจจุบัน (อิงจากไอคอน/ชื่อเดิม ไม่ hardcode id)
UPDATE `tbl_attribute` SET `attribute_style` = CASE `attribute_icon`
    WHEN 'mic'             THEN 'media'
    WHEN 'directions_walk' THEN 'guide'
    WHEN 'description'     THEN 'document'
    WHEN 'school'          THEN 'training'
  END
WHERE `attribute_type` = '1' AND `attribute_style` IS NULL
  AND `attribute_icon` IN ('mic', 'directions_walk', 'description', 'school');

UPDATE `tbl_activity_timeslot` SET `timeslot_icon` = CASE
    WHEN `start_time` < '12:00:00' AND `end_time` > '13:00:00' THEN 'date_range'
    WHEN `start_time` >= '17:00:00'                            THEN 'dark_mode'
    WHEN `start_time` >= '12:00:00'                            THEN 'partly_cloudy_day'
    ELSE 'light_mode'
  END
WHERE `timeslot_icon` IS NULL;
