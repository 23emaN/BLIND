<?php
    require_once dirname(__DIR__) . '/main/header.php';
    require_once dirname(__DIR__) . '/main/sidebar.php';

    $baseUrl    = defined('BASE_URL') ? BASE_URL : '';
    $categories = $data['categories'] ?? [];
    $locations  = $data['locations'] ?? [];
    $timeslots  = $data['timeslots'] ?? [];
?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">ปฏิทินกิจกรรม</h2>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="openAddItem()">
                                <i class="ri-add-line"></i>
                                <span>สร้างกิจกรรมใหม่</span>
                            </button>
                        </div>
                    </div>

                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อกิจกรรม หรือสถานที่">
                        </div>
                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; min-width: 500px;">
                            <input type="month" id="filter_month" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                            <select id="filter_category" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="">ทุกหมวดหมู่</option>
                                <?php foreach ($categories as $c): ?>
                                    <option value="<?php echo (int) $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <select id="itemPerPage" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="25">25 รายการ</option>
                                <option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option>
                                <option value="100">100 รายการ</option>
                            </select>
                        </div>
                    </div>

                    <div id="itemTableContainer">
                        <?php require_once __DIR__ . '/table/activity_table.php'; ?>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal สร้าง/แก้ไขกิจกรรม -->
<div class="modal fade" id="itemModal" tabindex="-1" aria-labelledby="itemModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="itemModalLabel">สร้างกิจกรรมใหม่</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="itemForm" autocomplete="off">
                    <input type="hidden" name="activity_id" id="activity_id" value="">

                    <div class="mb-3">
                        <label class="form-label" for="f_title">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="activity_title" id="f_title" maxlength="100">
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="f_category">หมวดหมู่กิจกรรม <span class="text-danger">*</span></label>
                        <select class="form-select" name="category_id" id="f_category">
                            <option value="">— เลือกหมวดหมู่ —</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?php echo (int) $c['category_id']; ?>"><?php echo htmlspecialchars($c['category_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <?php if (!empty($timeslots)): ?>
                        <div class="mb-2">
                            <label class="form-label d-block">เลือกช่วงเวลาที่ใช้บ่อย</label>
                            <div class="d-flex flex-wrap gap-2">
                                <?php foreach ($timeslots as $ts): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary"
                                        onclick="applyTimeslot('<?php echo substr($ts['start_time'], 0, 5); ?>', '<?php echo substr($ts['end_time'], 0, 5); ?>')">
                                        <?php echo htmlspecialchars($ts['timeslot_name']); ?>
                                        (<?php echo substr($ts['start_time'], 0, 5); ?>–<?php echo substr($ts['end_time'], 0, 5); ?>)
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="f_date">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" name="activity_date" id="f_date">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="f_start">เวลาเริ่ม <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="start_time" id="f_start">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label" for="f_end">เวลาสิ้นสุด <span class="text-danger">*</span></label>
                            <input type="time" class="form-control" name="end_time" id="f_end">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="f_location">สถานที่จัดกิจกรรม <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="location" id="f_location" maxlength="255">
                        <?php if (!empty($locations)): ?>
                            <div class="d-flex flex-wrap gap-2 mt-2">
                                <?php foreach ($locations as $loc): ?>
                                    <button type="button" class="btn btn-sm btn-outline-secondary"
                                        onclick="applyLocation(this.dataset.loc)" data-loc="<?php echo htmlspecialchars($loc['location_name'], ENT_QUOTES); ?>"
                                        title="<?php echo htmlspecialchars($loc['location_name'], ENT_QUOTES); ?>">
                                        <i class="ri-add-line"></i> <?php echo htmlspecialchars($loc['location_label']); ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_max">จำนวนอาสาที่เปิดรับ <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" name="max_volunteers" id="f_max" min="1" max="100" value="20">
                            <small class="text-muted">แนะนำ 15–30 คน</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_reserve">จำนวนสำรองที่นั่ง</label>
                            <input type="number" class="form-control" name="reserve_count" id="f_reserve" min="0" max="100" value="5">
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="form-check form-switch mb-2">
                            <input type="hidden" name="grant_hours" value="0">
                            <input class="form-check-input" type="checkbox" name="grant_hours" id="f_grant_hours" value="1" checked onchange="toggleHoursFields()">
                            <label class="form-check-label" for="f_grant_hours">กิจกรรมนี้ให้ชั่วโมงจิตอาสา</label>
                        </div>
                        <div class="row" id="hoursFields">
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="f_hours">จำนวนชั่วโมงต่อคน</label>
                                <input type="number" class="form-control" name="hours_per_person" id="f_hours" min="0.5" max="24" step="0.5">
                                <small class="text-muted">ว่างไว้ = คำนวณจากช่วงเวลากิจกรรม</small>
                            </div>
                            <div class="col-md-6 mb-2">
                                <label class="form-label" for="f_hours_method">วิธีนับชั่วโมง</label>
                                <select class="form-select" name="hours_count_method" id="f_hours_method">
                                    <option value="1">ตามเวลาที่เข้าร่วมจริง (ไม่เกินที่ระบุ)</option>
                                    <option value="2">เข้าร่วมแล้วได้เต็มตามที่ระบุ</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-2">
                        <label class="form-label" for="f_detail">รายละเอียดและคำแนะนำ</label>
                        <textarea class="form-control" name="activity_detail" id="f_detail" rows="3" maxlength="500" placeholder="ไม่บังคับ"></textarea>
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
    const ACT_BASE_URL = '<?php echo $baseUrl; ?>';
    let itemFilterTimer = null;
    let isSubmittingItem = false;

    function GetData(page = 1) {
        const payload = {
            keyword:     ($('#search_input').val() || '').trim(),
            category_id: $('#filter_category').val() || '',
            month:       $('#filter_month').val() || '',
            per_page:    $('#itemPerPage').val() || 25,
            page:        page
        };
        $('#itemTableContainer').html(
            '<div class="table-wrap"><table class="table"><tbody><tr>' +
            '<td class="text-center text-muted py-5">กำลังโหลด...</td>' +
            '</tr></tbody></table></div>'
        );
        $.ajax({
            url: ACT_BASE_URL + '/activity/filter',
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

    // preset: เติมช่วงเวลา / สถานที่
    function applyTimeslot(start, end) {
        $('#f_start').val(start);
        $('#f_end').val(end);
    }
    function applyLocation(name) {
        $('#f_location').val(name);
    }

    // ชั่วโมงจิตอาสา: ซ่อนช่องเมื่อกิจกรรมไม่ให้ชั่วโมง
    function toggleHoursFields() {
        const on = $('#f_grant_hours').is(':checked');
        $('#hoursFields').toggle(on);
        $('#f_hours, #f_hours_method').prop('disabled', !on);
    }

    function openAddItem() {
        document.getElementById('itemForm').reset();
        $('#activity_id').val('');
        $('#f_max').val(20);
        $('#f_reserve').val(5);
        toggleHoursFields();
        $('#itemModalLabel').text('สร้างกิจกรรมใหม่');
        new bootstrap.Modal(document.getElementById('itemModal')).show();
    }

    function editItem(id) {
        $.ajax({
            url: ACT_BASE_URL + '/activity/get?id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.result !== 1) {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่พบข้อมูล', 'error');
                    return;
                }
                const d = response.data;
                document.getElementById('itemForm').reset();
                $('#activity_id').val(d.activity_id);
                $('#f_title').val(d.activity_title);
                $('#f_category').val(String(d.category_id));
                $('#f_date').val(d.activity_date);
                $('#f_start').val((d.start_time || '').substring(0, 5));
                $('#f_end').val((d.end_time || '').substring(0, 5));
                $('#f_location').val(d.location);
                $('#f_max').val(d.max_volunteers);
                $('#f_reserve').val(d.reserve_count);
                $('#f_detail').val(d.activity_detail || '');
                $('#f_grant_hours').prop('checked', d.grant_hours !== '0');
                $('#f_hours').val(d.hours_per_person !== null ? parseFloat(d.hours_per_person) : '');
                $('#f_hours_method').val(d.hours_count_method || '1');
                toggleHoursFields();
                $('#itemModalLabel').text('แก้ไขกิจกรรม');
                new bootstrap.Modal(document.getElementById('itemModal')).show();
            },
            error: function () {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    function submitItem() {
        if (isSubmittingItem) return;
        const id = $('#activity_id').val();
        const url = ACT_BASE_URL + '/activity' + (id ? '/edit' : '/add');

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
            html: 'ต้องการลบกิจกรรม <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: ACT_BASE_URL + '/activity/delete',
                method: 'POST',
                data: { activity_id: id },
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
