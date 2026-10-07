-- ============================================================
-- UPDATE (delta) 2026-10-07 — ระบบกิจกรรม
--   1) ชั่วโมงจิตอาสาของกิจกรรม       (tbl_activity)
--   2) ชุดสี+รูปทรงของหมวดหมู่กิจกรรม  (tbl_attribute.attribute_style)
--   3) ไอคอนของช่วงเวลาที่ใช้บ่อย       (tbl_activity_timeslot.timeslot_icon)
--
-- รันซ้ำได้ปลอดภัย (ADD COLUMN IF NOT EXISTS / UPDATE เฉพาะแถวที่ยังว่าง)
-- ใช้ต่อจาก DB ชุดที่ส่งไปแล้ว (activity_schema.sql)
-- import ด้วย utf8mb4 เสมอ:
--   mysql -u root --default-character-set=utf8mb4 <ชื่อ DB> < update_2026-10-07.sql
-- ============================================================

SET NAMES utf8mb4;

-- ------------------------------------------------------------
-- 0) รูปภาพ (โค้ดอัปโหลดรูปที่เพื่อนเพิ่มบนเซิร์ฟเวอร์ — DB เซิร์ฟเวอร์น่าจะมีแล้ว
--    IF NOT EXISTS จึงไม่กระทบ; ใส่ไว้ให้ DB เครื่องอื่นตามทัน)
-- ------------------------------------------------------------
ALTER TABLE `tbl_activity`
  ADD COLUMN IF NOT EXISTS `activity_image` VARCHAR(255) DEFAULT NULL
    COMMENT 'ชื่อไฟล์ภาพปกกิจกรรมใน upload_image/' AFTER `activity_date`;

ALTER TABLE `tbl_attribute`
  ADD COLUMN IF NOT EXISTS `attribute_image` VARCHAR(255) DEFAULT NULL
    COMMENT 'ชื่อไฟล์ภาพของหมวดหมู่ใน upload_image/' AFTER `attribute_desc`;

-- ------------------------------------------------------------
-- 1) ชั่วโมงจิตอาสา
-- ------------------------------------------------------------
ALTER TABLE `tbl_activity`
  ADD COLUMN IF NOT EXISTS `grant_hours` VARCHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1 = ได้ชั่วโมงจิตอาสา, 0 = ไม่ได้ชั่วโมง' AFTER `reserve_count`,
  ADD COLUMN IF NOT EXISTS `hours_per_person` DECIMAL(4,1) DEFAULT NULL
    COMMENT 'จำนวนชั่วโมงจิตอาสาต่อคน (NULL เมื่อไม่ได้ชั่วโมง)' AFTER `grant_hours`,
  ADD COLUMN IF NOT EXISTS `hours_count_method` VARCHAR(1) NOT NULL DEFAULT '1'
    COMMENT '1 = ตามเวลาที่เข้าร่วมจริง (ไม่เกินที่ระบุ), 2 = เข้าร่วมแล้วได้เต็มตามที่ระบุ' AFTER `hours_per_person`;

-- ------------------------------------------------------------
-- 2) ชุดสี+รูปทรงของหมวดหมู่กิจกรรม (attribute_type = '1')
--    ค่า: green_circle / orange_triangle / blue_diamond / navy_square
--    หน้าบ้านใช้เป็น class  cat-{attribute_style}
--    สีคู่กับรูปทรงเสมอ (WCAG 1.4.1) — ไม่เก็บรหัสสีใน DB
-- ------------------------------------------------------------
ALTER TABLE `tbl_attribute`
  ADD COLUMN IF NOT EXISTS `attribute_style` VARCHAR(20) DEFAULT NULL
    COMMENT 'ชุดสี+รูปทรงของหมวดหมู่กิจกรรม (green_circle/orange_triangle/blue_diamond/navy_square)' AFTER `attribute_icon`;

ALTER TABLE `tbl_attribute` MODIFY COLUMN `attribute_style` VARCHAR(20) DEFAULT NULL
    COMMENT 'ชุดสี+รูปทรงของหมวดหมู่กิจกรรม (green_circle/orange_triangle/blue_diamond/navy_square)';

-- แปลงชื่อชุดแบบเก่า (ถ้าเคยรันเวอร์ชันก่อนหน้า) → ชื่อใหม่ที่บอกหน้าตา
UPDATE `tbl_attribute` SET `attribute_style` = CASE `attribute_style`
    WHEN 'media'    THEN 'green_circle'
    WHEN 'guide'    THEN 'orange_triangle'
    WHEN 'document' THEN 'blue_diamond'
    WHEN 'training' THEN 'navy_square'
  END
WHERE `attribute_style` IN ('media', 'guide', 'document', 'training');

-- ใส่ค่าให้หมวดเดิม 4 หมวด ให้ตรงกับหน้าบ้านปัจจุบัน (อิงจากไอคอน ไม่ hardcode id)
UPDATE `tbl_attribute` SET `attribute_style` = CASE `attribute_icon`
    WHEN 'mic'             THEN 'green_circle'
    WHEN 'directions_walk' THEN 'orange_triangle'
    WHEN 'description'     THEN 'blue_diamond'
    WHEN 'school'          THEN 'navy_square'
  END
WHERE `attribute_type` = '1' AND `attribute_style` IS NULL
  AND `attribute_icon` IN ('mic', 'directions_walk', 'description', 'school');

-- ------------------------------------------------------------
-- 3) ไอคอนช่วงเวลา (Material Symbols)
--    ค่า: wb_twilight / light_mode / partly_cloudy_day / dark_mode / date_range / schedule
-- ------------------------------------------------------------
ALTER TABLE `tbl_activity_timeslot`
  ADD COLUMN IF NOT EXISTS `timeslot_icon` VARCHAR(50) DEFAULT NULL
    COMMENT 'ไอคอน Material Symbols ของช่วงเวลา' AFTER `end_time`;

-- ใส่ไอคอนตามเวลาเริ่ม ให้แถวที่ยังว่าง
UPDATE `tbl_activity_timeslot` SET `timeslot_icon` = CASE
    WHEN `start_time` < '12:00:00' AND `end_time` > '13:00:00' THEN 'date_range'
    WHEN `start_time` >= '17:00:00'                            THEN 'dark_mode'
    WHEN `start_time` >= '12:00:00'                            THEN 'partly_cloudy_day'
    ELSE 'light_mode'
  END
WHERE `timeslot_icon` IS NULL;
