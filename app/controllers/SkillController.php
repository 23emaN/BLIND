<?php
// app/controllers/SkillController.php
// หน้าตั้งค่าทักษะและความถนัด — tbl_attribute ที่ attribute_type = '2'

require_once '../app/controllers/AttributeController.php';

class SkillController extends AttributeController
{
    protected $type      = '2';
    protected $pageTitle = 'ตั้งค่าทักษะและความถนัด';
    protected $itemLabel = 'ทักษะ';
    protected $routeBase = 'skill';
}
