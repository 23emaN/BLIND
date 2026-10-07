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
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class="page-title">ตั้งค่าทักษะความถนัด</h2>
                            <button class="btn btn-primary">
                                <i class="ri-add-line"></i> เพิ่มทักษะความถนัด
                            </button>
                        </div>
                    </div>

                    <div class="filter-toolbar mb-3 mt-3 d-flex justify-content-start">
                        <div class="search-box-wrap" style="max-width: 400px; width: 100%;">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาทักษะความถนัด..." value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
                        </div>
                    </div>

                    <div id="skillTableContainer">
                        <?php require_once __DIR__ . '/table/skill_table.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function GetData(page) {
        const formData = new FormData();
        formData.append('page', page);
        
        const searchInput = document.getElementById('search_input').value;
        formData.append('search', searchInput);

        fetch("<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/skill_table", {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('skillTableContainer').innerHTML = html;
        })
        .catch(error => console.error('Error fetching data:', error));
    }

    let filterTimeout;
    function triggerFilterDebounced() {
        clearTimeout(filterTimeout);
        filterTimeout = setTimeout(() => {
            GetData(1);
        }, 300);
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>
