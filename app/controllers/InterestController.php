<?php
// app/controllers/InterestController.php
// หน้าตั้งค่ากิจกรรมที่สนใจเข้าร่วม — tbl_attribute ที่ attribute_type = '1'
// (ใช้เป็นหมวดหมู่กิจกรรมด้วย — ดู ActivityModel::getCategories)

require_once '../app/controllers/AttributeController.php';

class InterestController extends AttributeController
{
    protected $type      = '1';
    protected $pageTitle = 'ตั้งค่าหมวดหมู่กิจกรรม';
    protected $itemLabel = 'หมวดหมู่กิจกรรม';
    protected $routeBase = 'interest';
    protected $hasMeta   = true;   // หมวดหมู่กิจกรรม จัดการ icon + คำอธิบาย ด้วย
}
