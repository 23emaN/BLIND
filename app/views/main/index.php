<?php
    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">
                     <div class="page-header-box">
                        <div>
                            <h2 class="page-title"><?php echo htmlspecialchars($data['title'] ?? 'หน้าหลัก'); ?></h2>
                        </div>
                    </div>
                    <!-- เนื้อหาหลักจะอยู่ที่นี่ -->
                    <div style="padding: 20px; text-align: center; color: #666;">
                        แดชบอร์ด
                    </div>
                </div> 
            </div>
        </div>
    </div>
</div>

<?php
    if (file_exists(dirname(__DIR__) . '/main/footer.php')) {
        require_once dirname(__DIR__) . '/main/footer.php';
    }
?>
