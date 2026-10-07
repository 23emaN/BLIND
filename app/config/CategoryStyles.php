<?php
// app/config/CategoryStyles.php
// ชุดสี+รูปทรงของหมวดหมู่กิจกรรม — ต้องตรงกับ class cat-{key} ใน CSS หน้าบ้าน (activity-create.css)
// สีกับรูปทรงมาเป็นคู่เสมอ (WCAG 1.4.1: ไม่ใช้สีอย่างเดียวแยกหมวด) — ห้ามให้เลือกสีอิสระ
// จะเพิ่มชุดใหม่: ให้หน้าบ้านเพิ่ม class ใน CSS ก่อน แล้วค่อยเพิ่มที่นี่
// color = ค่าจากตัวแปรธีมหน้าบ้าน (ใช้แสดงตัวอย่างในหลังบ้านเท่านั้น)

return [
    'media'    => ['label' => 'เขียว · วงกลม',        'color' => '#067647', 'shape' => 'circle'],
    'guide'    => ['label' => 'ส้ม · สามเหลี่ยม',      'color' => '#b54708', 'shape' => 'triangle'],
    'document' => ['label' => 'น้ำเงิน · ข้าวหลามตัด', 'color' => '#2b3990', 'shape' => 'diamond'],
    'training' => ['label' => 'กรมท่า · สี่เหลี่ยม',   'color' => '#0f1e3d', 'shape' => 'square'],
];
