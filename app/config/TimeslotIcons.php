<?php
// app/config/TimeslotIcons.php
// ไอคอนที่ให้เลือกสำหรับช่วงเวลาที่ใช้บ่อย (Material Symbols — ชุดเดียวกับหน้าบ้าน)
// key = ชื่อไอคอนที่เก็บใน tbl_activity_timeslot.timeslot_icon, value = คำอธิบายภาษาไทย
// เลือกเฉพาะไอคอนที่หน้าตาต่างกันชัด (light_mode / sunny / wb_sunny แทบเหมือนกัน — ใช้ตัวเดียว)

return [
    'wb_twilight'       => 'เช้าตรู่',
    'light_mode'        => 'ช่วงเช้า',
    'partly_cloudy_day' => 'ช่วงบ่าย',
    'dark_mode'         => 'ช่วงเย็น / กลางคืน',
    'date_range'        => 'ทั้งวัน',
    'schedule'          => 'ทั่วไป (นาฬิกา)',
];
