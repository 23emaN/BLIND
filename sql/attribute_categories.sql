-- ============================================================
-- หมวดหมู่กิจกรรม = tbl_attribute (attribute_type = '1')
-- ใช้ร่วมกับ "กิจกรรมที่สนใจ" ที่อาสาสมัครเลือก (ไม่มีตารางหมวดหมู่แยก)
-- เพิ่มคอลัมน์ไอคอน/คำอธิบาย ให้ tbl_attribute แล้ว seed 4 หมวดตามหน้าบ้าน
-- ต้องมี SET NAMES utf8mb4 เสมอ ไม่งั้นภาษาไทยจะเพี้ยนตอนรันผ่าน mysql CLI
-- ============================================================

SET NAMES utf8mb4;

ALTER TABLE `tbl_attribute`
  ADD COLUMN IF NOT EXISTS `attribute_icon` VARCHAR(50)  DEFAULT NULL COMMENT 'material icon (สำหรับหมวดหมู่กิจกรรม)' AFTER `attribute_name`,
  ADD COLUMN IF NOT EXISTS `attribute_desc` VARCHAR(255) DEFAULT NULL COMMENT 'คำอธิบายสั้น' AFTER `attribute_icon`;

INSERT INTO `tbl_attribute` (`attribute_name`, `attribute_icon`, `attribute_desc`, `active_status`, `create_user_id`, `create_at`, `attribute_type`) VALUES
  ('ผลิตสื่อและเสียง',   'mic',             'บันทึกเสียงบทเรียน หนังสือเสียง',  '1', 1, NOW(), '1'),
  ('นำทางและสันทนาการ', 'directions_walk', 'พาเดิน–วิ่ง นันทนาการภายนอก',    '1', 1, NOW(), '1'),
  ('ช่วยงานเอกสาร',     'description',     'พิมพ์อักษรเบรลล์ ตรวจทานไฟล์',   '1', 1, NOW(), '1'),
  ('อบรมและทั่วไป',     'school',          'เวิร์กช็อป ช่วยจัดเตรียมสถานที่', '1', 1, NOW(), '1');
