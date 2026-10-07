<?php
// app/config/CategoryStyles.php
// ชุดสี+รูปทรงของหมวดหมู่กิจกรรม — key ตั้งตามหน้าตา (สี_รูปทรง) ไม่ผูกกับชื่อหมวด
// หน้าบ้านใช้เป็น class cat-{key} (เดิมหน้าบ้านใช้ cat-media/guide/document/training)
// สีกับรูปทรงมาเป็นคู่เสมอ (WCAG 1.4.1: ไม่ใช้สีอย่างเดียวแยกหมวด) — ห้ามให้เลือกสีอิสระ
// จะเพิ่มชุดใหม่: ให้หน้าบ้านเพิ่ม class ใน CSS ก่อน แล้วค่อยเพิ่มที่นี่
// color = ค่าจากตัวแปรธีมหน้าบ้าน (ใช้แสดงตัวอย่างในหลังบ้านเท่านั้น)

return [
    'green_circle'    => ['label' => 'เขียว · วงกลม',        'color' => '#067647', 'shape' => 'circle'],
    'orange_triangle' => ['label' => 'ส้ม · สามเหลี่ยม',      'color' => '#b54708', 'shape' => 'triangle'],
    'blue_diamond'    => ['label' => 'น้ำเงิน · ข้าวหลามตัด', 'color' => '#2b3990', 'shape' => 'diamond'],
    'navy_square'     => ['label' => 'กรมท่า · สี่เหลี่ยม',   'color' => '#0f1e3d', 'shape' => 'square'],
];
