-- ============================================================
-- tbl_volunteer_skill — ความถนัด/ทักษะ ของจิตอาสา (ตารางแยกเฉพาะ)
-- เชื่อม tbl_volunteer <-> tbl_attribute (attribute_type = '2' = ทักษะและความถนัด)
-- (แยกจาก tbl_mapping_attribute ที่ใช้เก็บกิจกรรมที่สนใจ type 1)
-- ต้องมี SET NAMES utf8mb4 เสมอ
-- ============================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `tbl_volunteer_skill` (
  `volunteer_skill_id` INT(11) NOT NULL AUTO_INCREMENT,
  `volunteer_id`       INT(11) NOT NULL COMMENT '-> tbl_volunteer',
  `attribute_id`       INT(11) NOT NULL COMMENT '-> tbl_attribute (attribute_type = 2)',
  `create_user_id`     INT(11) NOT NULL,
  `created_at`         DATETIME NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`volunteer_skill_id`),
  UNIQUE KEY `uniq_vol_skill` (`volunteer_id`, `attribute_id`),
  KEY `idx_vs_volunteer` (`volunteer_id`),
  KEY `idx_vs_attribute` (`attribute_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
