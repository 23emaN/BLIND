<?php
// app/views/main/sidebar.php

// ตรวจสอบ URL ปัจจุบันสำหรับ Active State
$current_url = $_GET['url'] ?? 'main';
$now_page    = trim(strtok($current_url, '/'));
$base        = defined('BASE_URL') ? BASE_URL : '/Blind_/public';

$current_url = $_GET['url'] ?? 'backoffice';
$now_page = trim(strtok($current_url, '/'));

$dashboard_page = ['main'];
$user_pages = ['user'];
$volunteer_pages = ['volunteer'];
$volunteer_approve_page = ['volunteer_approve'];
$activity_pages = ['activity'];
$skill_pages = ['skill'];
?>

<style>
    .sidebar-area {
        background-color: #F7F9FB;
        font-family: 'Kanit', 'Segoe UI', Tahoma, sans-serif;
        width: 220px;
        padding-top: 78px; /* หลบ Topbar */
        overflow-y: auto !important;
        overflow-x: hidden !important;
        scrollbar-width: thin;
        scrollbar-color: #e2e8f0 transparent;
    }

    .sidebar-area::-webkit-scrollbar { width: 4px; }
    .sidebar-area::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }

    .sidebar-area .menu-inner {
        list-style: none;
        margin: 0;
        padding: 0 0 24px 0;
    }

    /* หัวข้อหมวดหมู่ */
    .sidebar-area .menu-title {
        margin: 14px 0 4px 0 !important;
        padding: 0 18px !important;
        line-height: 1 !important;
        display: block !important;
    }

    .sidebar-area .menu-title .menu-title-text {
        font-size: 11px !important;
        font-weight: 700 !important;
        color: #1e293b !important;
        letter-spacing: 0.01em !important;
    }

    /* เมนูย่อย */
    .sidebar-area .menu-item {
        display: block !important;
        margin: 1px 0 !important;
        padding: 0 !important;
    }

    .sidebar-area .menu-item .menu-link {
        margin: 0 10px !important;
        padding: 6px 10px !important;
        border-radius: 8px !important;
        min-height: unset !important;
        height: auto !important;
        display: flex !important;
        align-items: center !important;
        color: #334155 !important;
        font-weight: 500 !important;
        transition: background-color .15s ease, color .15s ease, transform .15s ease !important;
        text-decoration: none !important;
    }

    .sidebar-area .menu-item .menu-link:hover {
        background-color: #eef2f7 !important;
        color: #0066fe !important;
        transform: translateX(2px);
    }

    .sidebar-area .menu-item .menu-link.active {
        background-color: #e8f0ff !important;
        color: #0066fe !important;
        font-weight: 600 !important;
    }

    .sidebar-area .menu-item .menu-link .menu-icon {
        font-size: 15px !important;
        margin-right: 10px !important;
        color: #64748b !important;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        transition: color .15s ease;
    }

    .sidebar-area .menu-item .menu-link:hover .menu-icon,
    .sidebar-area .menu-item .menu-link.active .menu-icon {
        color: #0066fe !important;
    }

    .sidebar-area .menu-item .menu-link .title {
        font-size: 12px !important;
        line-height: 1.3 !important;
        overflow: hidden;
        text-overflow: ellipsis;
        flex-grow: 1;
        white-space: nowrap;
    }

    /* ซ่อนขีดด้านข้างของ template เดิม */
    .sidebar-area .menu-item .menu-link::before,
    .sidebar-area .menu-item .menu-link.active::before {
        display: none !important;
        content: none !important;
    }

    /* --- โหมดย่อ Sidebar --- */
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-title,
    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .title {
        display: none !important;
    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link {
        justify-content: center !important;
        padding: 10px !important;
    }

    [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .menu-icon {
        margin-right: 0 !important;
        font-size: 1.3rem !important;
    }

    /* --- Mobile --- */
    @media (max-width: 768px) {
        .sidebar-area {
            position: fixed !important;
            top: 0 !important;
            left: -280px;
            height: 100% !important;
            width: 260px !important;
            z-index: 100000 !important;
            transition: left .3s ease;
            box-shadow: 2px 0 10px rgba(0, 0, 0, .05);
            padding-top: 20px;
            background-color: #ffffff !important;
        }

        body[sidebar-data-theme="sidebar-hide"] .sidebar-area { left: 0 !important; }

        .sidebar-backdrop {
            display: none !important;
            position: fixed !important;
            inset: 0 !important;
            background-color: rgba(0, 0, 0, .5) !important;
            z-index: 99999 !important;
            cursor: pointer;
        }

        body[sidebar-data-theme="sidebar-hide"] .sidebar-backdrop { display: block !important; }

        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-title,
        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .title {
            display: block !important;
        }

        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link {
            justify-content: flex-start !important;
            padding: 7px 12px !important;
        }

        [sidebar-data-theme="sidebar-hide"] .sidebar-area .menu-item .menu-link .menu-icon {
            margin-right: 10px !important;
            font-size: 15px !important;
        }

        body .main-content,
        body[sidebar-data-theme="sidebar-hide"] .main-content {
            padding-left: 0 !important;
            margin-left: 0 !important;
            width: 100% !important;
        }
    }
</style>

<div class="sidebar-backdrop" id="sidebar-backdrop" onclick="document.body.setAttribute('sidebar-data-theme', 'sidebar-show');"></div>

<div class="sidebar-area" id="sidebar-area">

    <!-- ปุ่มภาพรวมสำนักงานถูกลบออกไปแล้ว -->

    <aside id="layout-menu" class="layout-menu menu-vertical menu active" style="overflow: hidden !important;">
        <ul class="menu-inner">
             <li class="menu-item <?php echo in_array($now_page, $dashboard_page) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/main"
                    class="menu-link <?php echo in_array($now_page, $dashboard_page) ? 'active' : '' ?>">
                    <i class="ri-dashboard-line menu-icon"></i>
                    <span class="title">แดชบอร์ด</span>
                </a>
            </li>
            <!-- หมวดหมู่: จัดการข้อมูล -->
            <li class="menu-title small">
                <span class="menu-title-text">จัดการข้อมูล</span>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $user_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/user"
                    class="menu-link <?php echo in_array($now_page, $user_pages) ? 'active' : '' ?>">
                    <i class="ri-user-3-line menu-icon"></i>
                    <span class="title">ผู้ใช้</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $volunteer_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/volunteer"
                    class="menu-link <?php echo in_array($now_page, $volunteer_pages) ? 'active' : '' ?>">
                    <i class="ri-team-line menu-icon"></i>
                    <span class="title">อาสาสมัคร</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $volunteer_approve_page) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/volunteer_approve"
                    class="menu-link <?php echo in_array($now_page, $volunteer_approve_page) ? 'active' : '' ?>">
                    <i class="ri-team-line menu-icon"></i>
                    <span class="title">ยืนยันตัวตนอาสาสมัคร</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/activity"
                    class="menu-link">
                    <i class="ri-calendar-event-line menu-icon"></i>
                    <span class="title">ปฏิทินกิจกรรม</span>
                </a>
            </li>

            <!-- หมวดหมู่: ตั้งค่า -->
            <li class="menu-title small">
                <span class="menu-title-text">ตั้งค่า</span>
            </li>

            <li class="menu-item">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/interest"
                    class="menu-link">
                    <i class="ri-checkbox-circle-line menu-icon"></i>
                    <span class="title">หมวดหมู่กิจกรรม</span>
                </a>
            </li>

            <li class="menu-item <?php echo in_array($now_page, $skill_pages) ? 'open active' : '' ?>">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/skill"
                    class="menu-link <?php echo in_array($now_page, $skill_pages) ? 'active' : '' ?>">
                    <i class="ri-checkbox-circle-line menu-icon"></i>
                    <span class="title">ทักษะและความถนัด</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/location"
                    class="menu-link">
                    <i class="ri-map-pin-line menu-icon"></i>
                    <span class="title">สถานที่ที่ใช้บ่อย</span>
                </a>
            </li>

            <li class="menu-item">
                <a href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/timeslot"
                    class="menu-link">
                    <i class="ri-time-line menu-icon"></i>
                    <span class="title">ช่วงเวลาที่ใช้บ่อย</span>
                </a>
            </li>
        </ul>
    </aside>
</div>

<script>

    $(document).ready(function () {
        $(window).on('popstate', function () {

            window.location.reload();
        });

    });

</script>