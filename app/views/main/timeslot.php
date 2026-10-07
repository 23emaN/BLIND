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
                        <div><h2 class="page-title">ตั้งค่าช่วงเวลาที่ใช้บ่อย</h2></div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="openAddItem()">
                                <i class="ri-add-line"></i><span>เพิ่มช่วงเวลา</span>
                            </button>
                        </div>
                    </div>
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อช่วงเวลา">
                        </div>
                        <div class="filter-group" style="display:flex;align-items:center;gap:10px;min-width:200px;">
                            <select id="itemPerPage" class="filter-select" style="flex:1;height:42px;" onchange="triggerFilterDebounced()">
                                <option value="25">25 รายการ</option><option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option><option value="100">100 รายการ</option>
                            </select>
                        </div>
                    </div>
                    <div id="itemTableContainer">
                        <?php require_once __DIR__ . '/table/timeslot_table.php'; ?>
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
                <h5 class="modal-title" id="itemModalLabel">เพิ่มช่วงเวลา</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" autocomplete="off">
                    <input type="hidden" name="timeslot_id" id="timeslot_id" value="">
                    <div class="mb-3">
                        <label class="form-label" for="f_name">ชื่อช่วงเวลา <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="timeslot_name" id="f_name" maxlength="50" placeholder="เช่น ช่วงเช้า">
                    </div>
                    <div class="row">
                        <div class="col-6 mb-2">
                            <label class="form-label" for="f_start">เวลาเริ่ม <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="f_start">
                        </div>
                        <div class="col-6 mb-2">
                            <label class="form-label" for="f_end">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" id="f_end">
                        </div>
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
    const ITEM_ROUTE = 'timeslot';
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
        $('#timeslot_id').val('');
        $('#itemModalLabel').text('เพิ่มช่วงเวลา');
        new bootstrap.Modal(document.getElementById('itemModal')).show();
        setTimeout(() => $('#f_name').trigger('focus'), 300);
    }
    function editItem(id) {
        $.ajax({ url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/get?id=' + id, method:'GET', dataType:'json',
            success: function (r) {
                if (r.result !== 1) { Swal.fire('ผิดพลาด', r.msg||'ไม่พบข้อมูล','error'); return; }
                document.getElementById('itemForm').reset();
                $('#timeslot_id').val(r.data.timeslot_id);
                $('#f_name').val(r.data.timeslot_name);
                $('#f_start').val((r.data.start_time||'').substring(0,5));
                $('#f_end').val((r.data.end_time||'').substring(0,5));
                $('#itemModalLabel').text('แก้ไขช่วงเวลา');
                new bootstrap.Modal(document.getElementById('itemModal')).show();
            },
            error: () => Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error') });
    }
    function submitItem() {
        if (isSubmittingItem) return;
        const id = $('#timeslot_id').val();
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
            $.ajax({ url: ITEM_BASE_URL + '/' + ITEM_ROUTE + '/delete', method:'POST', data:{ timeslot_id:id }, dataType:'json',
                success: r => r.result === 1 ? (Swal.fire({icon:'success',title:r.msg,timer:1200,showConfirmButton:false}), GetData(1)) : Swal.fire('ผิดพลาด', r.msg||'ไม่สามารถลบได้','error'),
                error: () => Swal.fire('ผิดพลาด','เกิดข้อผิดพลาดในการเชื่อมต่อ','error') });
        });
    }
</script>

<?php if (file_exists(dirname(__DIR__) . '/main/footer.php')) require_once dirname(__DIR__) . '/main/footer.php'; ?>
