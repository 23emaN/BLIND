<?php
    require_once dirname(__DIR__) . '/main/header.php';
    require_once dirname(__DIR__) . '/main/sidebar.php';

    $baseUrl    = defined('BASE_URL') ? BASE_URL : '';
    $type       = $data['type'] ?? '2';
    $itemLabel  = $data['item_label'] ?? 'รายการ';
    // base path ของ endpoint: type '1' => activity, อื่น ๆ => skill
    $route      = $type === '1' ? 'activity' : 'skill';
?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title"><?php echo htmlspecialchars($data['title'] ?? 'ตั้งค่า'); ?></h2>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="openAddItem()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่ม<?php echo htmlspecialchars($itemLabel); ?></span>
                            </button>
                        </div>
                    </div>

                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อ<?php echo htmlspecialchars($itemLabel); ?>">
                        </div>
                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; min-width: 200px;">
                            <select id="itemPerPage" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="25">25 รายการ</option>
                                <option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option>
                                <option value="100">100 รายการ</option>
                            </select>
                        </div>
                    </div>

                    <div id="itemTableContainer">
                        <?php require_once __DIR__ . '/table/attribute_table.php'; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่ม/แก้ไข -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">เพิ่ม<?php echo htmlspecialchars($itemLabel); ?></h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" autocomplete="off">
                    <input type="hidden" name="attribute_id" id="attribute_id" value="">
                    <div class="mb-2">
                        <label class="form-label" for="f_name">ชื่อ<?php echo htmlspecialchars($itemLabel); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="attribute_name" id="f_name" maxlength="255" autocomplete="off">
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="itemSubmitBtn" onclick="submitItem()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    const ITEM_BASE_URL = '<?php echo $baseUrl; ?>';
    const ITEM_ROUTE    = '<?php echo $route; ?>';
    let itemFilterTimer = null;
    let isSubmittingItem = false;

    function GetData(page = 1) {
        const payload = {
            keyword:  ($('#search_input').val() || '').trim(),
            per_page: $('#itemPerPage').val() || 25,
            page:     page
        };
        $('#itemTableContainer').html(
            '<div class="table-wrap"><table class="table"><tbody><tr>' +
            '<td class="text-center text-muted py-5">กำลังโหลด...</td>' +
            '</tr></tbody></table></div>'
        );
        $.ajax({
            url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/filter',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (response) {
                if (response.result === 1) {
                    $('#itemTableContainer').html(response.html);
                } else {
                    Swal.fire('ผิดพลาด', response.msg || 'โหลดข้อมูลไม่สำเร็จ', 'error');
                }
            },
            error: function () {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    function triggerFilterDebounced() {
        clearTimeout(itemFilterTimer);
        itemFilterTimer = setTimeout(() => GetData(1), 350);
    }

    function openAddItem() {
        document.getElementById('itemForm').reset();
        $('#attribute_id').val('');
        $('#itemModalLabel').text('เพิ่ม<?php echo htmlspecialchars($itemLabel, ENT_QUOTES); ?>');
        new bootstrap.Modal(document.getElementById('itemModal')).show();
        setTimeout(() => $('#f_name').trigger('focus'), 300);
    }

    function editItem(id) {
        $.ajax({
            url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/get?id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.result !== 1) {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่พบข้อมูล', 'error');
                    return;
                }
                document.getElementById('itemForm').reset();
                $('#attribute_id').val(response.data.attribute_id);
                $('#f_name').val(response.data.attribute_name);
                $('#itemModalLabel').text('แก้ไข<?php echo htmlspecialchars($itemLabel, ENT_QUOTES); ?>');
                new bootstrap.Modal(document.getElementById('itemModal')).show();
            },
            error: function () {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    function submitItem() {
        if (isSubmittingItem) return;
        const id = $('#attribute_id').val();
        const url = ITEM_BASE_URL + '/' + ITEM_ROUTE + (id ? '/edit' : '/add');

        isSubmittingItem = true;
        const btn = $('#itemSubmitBtn');
        const originalText = btn.text();
        btn.prop('disabled', true).text('กำลังบันทึก...');

        $.ajax({
            url: url,
            method: 'POST',
            data: $('#itemForm').serialize(),
            dataType: 'json',
            success: function (response) {
                isSubmittingItem = false;
                btn.prop('disabled', false).text(originalText);
                if (response.result === 1) {
                    bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
                    Swal.fire({ icon: 'success', title: response.msg, timer: 1200, showConfirmButton: false });
                    GetData(1);
                } else {
                    Swal.fire({ icon: 'warning', title: 'ไม่สำเร็จ', html: (response.msg || '').replace(/\n/g, '<br>') });
                }
            },
            error: function () {
                isSubmittingItem = false;
                btn.prop('disabled', false).text(originalText);
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    function deleteItem(id, name) {
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            html: 'ต้องการลบ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/delete',
                method: 'POST',
                data: { attribute_id: id },
                dataType: 'json',
                success: function (response) {
                    if (response.result === 1) {
                        Swal.fire({ icon: 'success', title: response.msg, timer: 1200, showConfirmButton: false });
                        GetData(1);
                    } else {
                        Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถลบได้', 'error');
                    }
                },
                error: function () {
                    Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
                }
            });
        });
    }
</script>

<?php
    if (file_exists(dirname(__DIR__) . '/main/footer.php')) {
        require_once dirname(__DIR__) . '/main/footer.php';
    }
?>
