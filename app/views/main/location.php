<?php
    require_once dirname(__DIR__) . '/main/header.php';
    require_once dirname(__DIR__) . '/main/sidebar.php';
    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>
<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">
                    <div class="page-header-box">
                        <div><h2 class="page-title">ตั้งค่าสถานที่ที่ใช้บ่อย</h2></div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="openAddItem()">
                                <i class="ri-add-line"></i><span>เพิ่มสถานที่</span>
                            </button>
                        </div>
                    </div>
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อสถานที่">
                        </div>
                        <div class="filter-group" style="display:flex;align-items:center;gap:10px;min-width:200px;">
                            <select id="itemPerPage" class="filter-select" style="flex:1;height:42px;" onchange="triggerFilterDebounced()">
                                <option value="25">25 รายการ</option><option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option><option value="100">100 รายการ</option>
                            </select>
                        </div>
                    </div>
                    <div id="itemTableContainer">
                        <?php require_once __DIR__ . '/table/location_table.php'; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="itemModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">เพิ่มสถานที่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" autocomplete="off">
                    <input type="hidden" name="location_id" id="location_id" value="">
                    <div class="mb-3">
                        <label class="form-label" for="f_label">ชื่อย่อ (แสดงบนปุ่ม) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location_label" id="f_label" maxlength="100" placeholder="เช่น อาคารเรียนรวม">
                    </div>
                    <div class="mb-2">
                        <label class="form-label" for="f_name">ชื่อเต็ม (เติมลงช่องสถานที่) <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location_name" id="f_name" maxlength="255" placeholder="เช่น อาคารเรียนรวม มูลนิธิช่วยคนตาบอดแห่งประเทศไทย">
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
    const ITEM_ROUTE = 'location';
    let itemFilterTimer = null, isSubmittingItem = false;

    function GetData(page = 1) {
        const payload = { keyword: ($('#search_input').val()||'').trim(), per_page: $('#itemPerPage').val()||25, page };
        $('#itemTableContainer').html('<div class="table-wrap"><table class="table"><tbody><tr><td class="text-center text-muted py-5">กำลังโหลด...</td></tr></tbody></table></div>');
        $.ajax({ url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/filter', method:'POST', data:payload, dataType:'json',
            success: r => r.result === 1 ? $('#itemTableContainer').html(r.html) : Swal.fire('ผิดพลาด', r.msg||'โหลดข้อมูลไม่สำเร็จ','error'),
            error: () => Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error') });
    }
    function triggerFilterDebounced() { clearTimeout(itemFilterTimer); itemFilterTimer = setTimeout(() => GetData(1), 350); }

    function openAddItem() {
        document.getElementById('itemForm').reset();
        $('#location_id').val('');
        $('#itemModalLabel').text('เพิ่มสถานที่');
        new bootstrap.Modal(document.getElementById('itemModal')).show();
        setTimeout(() => $('#f_label').trigger('focus'), 300);
    }
    function editItem(id) {
        $.ajax({ url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/get?id=' + id, method:'GET', dataType:'json',
            success: function (r) {
                if (r.result !== 1) { Swal.fire('ผิดพลาด', r.msg||'ไม่พบข้อมูล','error'); return; }
                document.getElementById('itemForm').reset();
                $('#location_id').val(r.data.location_id);
                $('#f_label').val(r.data.location_label);
                $('#f_name').val(r.data.location_name);
                $('#itemModalLabel').text('แก้ไขสถานที่');
                new bootstrap.Modal(document.getElementById('itemModal')).show();
            },
            error: () => Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error') });
    }
    function submitItem() {
        if (isSubmittingItem) return;
        const id = $('#location_id').val();
        const url = ITEM_BASE_URL + '/' + ITEM_ROUTE + (id ? '/edit' : '/add');
        isSubmittingItem = true;
        const btn = $('#itemSubmitBtn'), t = btn.text();
        btn.prop('disabled', true).text('กำลังบันทึก...');
        $.ajax({ url, method:'POST', data:$('#itemForm').serialize(), dataType:'json',
            success: function (r) {
                isSubmittingItem = false; btn.prop('disabled', false).text(t);
                if (r.result === 1) {
                    bootstrap.Modal.getInstance(document.getElementById('itemModal')).hide();
                    Swal.fire({ icon:'success', title:r.msg, timer:1200, showConfirmButton:false }); GetData(1);
                } else { Swal.fire({ icon:'warning', title:'ไม่สำเร็จ', html:(r.msg||'').replace(/\n/g,'<br>') }); }
            },
            error: function () { isSubmittingItem = false; btn.prop('disabled', false).text(t); Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error'); } });
    }
    function deleteItem(id, name) {
        Swal.fire({ icon:'warning', title:'ยืนยันการลบ', html:'ต้องการลบ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?',
            showCancelButton:true, confirmButtonColor:'#d33', confirmButtonText:'ลบ', cancelButtonText:'ยกเลิก', reverseButtons:true
        }).then(res => {
            if (!res.isConfirmed) return;
            $.ajax({ url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/delete', method:'POST', data:{ location_id:id }, dataType:'json',
                success: r => r.result === 1 ? (Swal.fire({icon:'success',title:r.msg,timer:1200,showConfirmButton:false}), GetData(1)) : Swal.fire('ผิดพลาด', r.msg||'ไม่สามารถลบได้','error'),
                error: () => Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error') });
        });
    }
</script>

<?php if (file_exists(dirname(__DIR__) . '/main/footer.php')) require_once dirname(__DIR__) . '/main/footer.php'; ?>
