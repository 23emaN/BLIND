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
                            <h2 class="page-title">ตั้งค่ากิจกรรม</h2>
                            <button class="btn btn-primary" onclick="Modal_add()">
                                <i class="ri-add-line"></i> เพิ่มกิจกรรม
                            </button>
                        </div>
                    </div>

                    <div class="filter-toolbar mb-3 mt-3 d-flex justify-content-start">
                        <div class="search-box-wrap" style="max-width: 400px; width: 100%;">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหากิจกรรม..." value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
                        </div>
                    </div>

                    <div id="activityTableContainer">
                        <?php require_once __DIR__ . '/table/activity_setting_table.php'; ?>
                    </div>

                    <!-- Modal เพิ่มกิจกรรม -->
                    <div class="modal fade" id="add_activity_modal" tabindex="-1" aria-labelledby="add_activity_modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable modal-dialog-custom">
                            <div class="modal-content modal-content-custom">
                                <div class="modal-header modal-header-custom">
                                    <h5 class="modal-title modal-title-custom" id="add_activity_modalLabel">เพิ่มกิจกรรมใหม่</h5>
                                    <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body modal-body-custom">
                                    <form id="addActivityForm" autocomplete="off">
                                        <h6 class="modal-section-title">ข้อมูลหลัก</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="modal_activity_title" placeholder="ระบุชื่อกิจกรรม">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">หมวดหมู่กิจกรรม <span class="text-danger">*</span></label>
                                                <select class="form-select" id="modal_attribute_id">
                                                    <option value="">เลือกหมวดหมู่</option>
                                                    <?php foreach ($data['categories'] ?? [] as $cat): ?>
                                                        <option value="<?php echo $cat['attribute_id']; ?>">
                                                            <?php echo htmlspecialchars($cat['attribute_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control bg-white" id="modal_activity_date" placeholder="เลือกวันที่" readonly>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">ช่วงเวลา <span class="text-danger">*</span></label>
                                                <select class="form-select" id="modal_timeslot" onchange="toggleCustomTime(this)">
                                                    <option value="">เลือกช่วงเวลา</option>
                                                    <?php foreach ($data['timeslots'] ?? [] as $ts): ?>
                                                        <option value="<?php echo $ts['timeslot_id']; ?>" data-start="<?php echo date('H:i', strtotime($ts['start_time'])); ?>" data-end="<?php echo date('H:i', strtotime($ts['end_time'])); ?>">
                                                            <?php echo htmlspecialchars($ts['timeslot_name']); ?> (<?php echo date('H:i', strtotime($ts['start_time'])) . ' - ' . date('H:i', strtotime($ts['end_time'])); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <option value="custom">กำหนดเอง</option>
                                                </select>
                                                <div id="custom_time_wrapper" class="row mt-2" style="display: none;">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">เวลาเริ่ม</label>
                                                        <input type="time" class="form-control form-control-sm" id="modal_start_time">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">เวลาสิ้นสุด</label>
                                                        <input type="time" class="form-control form-control-sm" id="modal_end_time">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">สถานที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                                <select class="form-select" id="modal_location">
                                                    <option value="">เลือกสถานที่</option>
                                                    <?php foreach ($data['locations'] ?? [] as $loc): ?>
                                                        <option value="<?php echo $loc['location_id']; ?>">
                                                            <?php echo htmlspecialchars($loc['location_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <h6 class="modal-section-title">การรับสมัคร</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">จำนวนอาสาสมัครที่เปิดรับ <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="modal_max_volunteers" min="1" value="1">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">จำนวนสำรองที่นั่ง (คน)</label>
                                                <input type="number" class="form-control" id="modal_reserve_count" min="0" value="0">
                                            </div>
                                        </div>

                                        <h6 class="modal-section-title">รายละเอียดและรูปภาพ</h6>
                                        <div class="row g-3 mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label">รายละเอียดกิจกรรม</label>
                                                <textarea class="form-control" id="modal_activity_detail" rows="4" placeholder="ระบุรายละเอียดกิจกรรมที่อาสาสมัครควรรู้..." style="resize: vertical !important; min-height: 120px !important; max-height: 800px !important;"></textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">รูปภาพหน้าปกกิจกรรม (ถ้ามี)</label>
                                                <div class="image-upload-wrapper" onclick="document.getElementById('modal_activity_image').click()" style="cursor: pointer; border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 200px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                    <i id="upload_placeholder_icon" class="ri-add-line" style="font-size: 3rem; color: #ccc;"></i>
                                                    <img id="preview_activity_image" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1; display: none;">
                                                    <div class="upload-overlay text-muted" style="position: absolute; bottom: 5px; width: 100%; font-size: 0.85rem; z-index: 2; background: rgba(255,255,255,0.8); padding: 2px 0; text-align: center;">
                                                        <i class="ri-image-add-line"></i> คลิกเพื่ออัปโหลดรูปภาพ
                                                    </div>
                                                </div>
                                                <input type="file" id="modal_activity_image" class="d-none" accept="image/*" onchange="previewActImage(this)">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer modal-footer-custom">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                    <button type="button" class="btn btn-primary" onclick="saveActivity()">บันทึกข้อมูลกิจกรรม</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Modal แก้ไขกิจกรรม -->
                    <div class="modal fade" id="edit_activity_modal" tabindex="-1" aria-labelledby="edit_activity_modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable modal-dialog-custom">
                            <div class="modal-content modal-content-custom">
                                <div class="modal-header modal-header-custom">
                                    <h5 class="modal-title modal-title-custom" id="edit_activity_modalLabel">แก้ไขกิจกรรม</h5>
                                    <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>
                                <div class="modal-body modal-body-custom">
                                    <form id="editActivityForm" autocomplete="off">
                                        <input type="hidden" id="edit_activity_id">
                                        <h6 class="modal-section-title">ข้อมูลหลัก</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-12">
                                                <label class="form-label">ชื่อกิจกรรม <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control" id="edit_modal_activity_title" placeholder="ระบุชื่อกิจกรรม">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">หมวดหมู่กิจกรรม <span class="text-danger">*</span></label>
                                                <select class="form-select" id="edit_modal_attribute_id">
                                                    <option value="">เลือกหมวดหมู่</option>
                                                    <?php foreach ($data['categories'] ?? [] as $cat): ?>
                                                        <option value="<?php echo $cat['attribute_id']; ?>">
                                                            <?php echo htmlspecialchars($cat['attribute_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                                <input type="text" class="form-control bg-white" id="edit_modal_activity_date" placeholder="เลือกวันที่" readonly>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">ช่วงเวลา <span class="text-danger">*</span></label>
                                                <select class="form-select" id="edit_modal_timeslot" onchange="toggleCustomTime(this, 'edit_')">
                                                    <option value="">เลือกช่วงเวลา</option>
                                                    <?php foreach ($data['timeslots'] ?? [] as $ts): ?>
                                                        <option value="<?php echo $ts['timeslot_id']; ?>" data-start="<?php echo date('H:i', strtotime($ts['start_time'])); ?>" data-end="<?php echo date('H:i', strtotime($ts['end_time'])); ?>">
                                                            <?php echo htmlspecialchars($ts['timeslot_name']); ?> (<?php echo date('H:i', strtotime($ts['start_time'])) . ' - ' . date('H:i', strtotime($ts['end_time'])); ?>)
                                                        </option>
                                                    <?php endforeach; ?>
                                                    <option value="custom">กำหนดเอง</option>
                                                </select>
                                                <div id="edit_custom_time_wrapper" class="row mt-2" style="display: none;">
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">เวลาเริ่ม</label>
                                                        <input type="time" class="form-control form-control-sm" id="edit_modal_start_time">
                                                    </div>
                                                    <div class="col-6">
                                                        <label class="form-label small text-muted">เวลาสิ้นสุด</label>
                                                        <input type="time" class="form-control form-control-sm" id="edit_modal_end_time">
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">สถานที่จัดกิจกรรม <span class="text-danger">*</span></label>
                                                <select class="form-select" id="edit_modal_location">
                                                    <option value="">เลือกสถานที่</option>
                                                    <?php foreach ($data['locations'] ?? [] as $loc): ?>
                                                        <option value="<?php echo $loc['location_id']; ?>">
                                                            <?php echo htmlspecialchars($loc['location_name']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>
                                        </div>

                                        <h6 class="modal-section-title">การรับสมัคร</h6>
                                        <div class="row g-3 mb-4">
                                            <div class="col-md-6">
                                                <label class="form-label">จำนวนอาสาสมัครที่เปิดรับ <span class="text-danger">*</span></label>
                                                <input type="number" class="form-control" id="edit_modal_max_volunteers" min="1" value="1">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label">จำนวนสำรองที่นั่ง (คน)</label>
                                                <input type="number" class="form-control" id="edit_modal_reserve_count" min="0" value="0">
                                            </div>
                                        </div>

                                        <h6 class="modal-section-title">รายละเอียดและรูปภาพ</h6>
                                        <div class="row g-3 mb-2">
                                            <div class="col-md-12">
                                                <label class="form-label">รายละเอียดกิจกรรม</label>
                                                <textarea class="form-control" id="edit_modal_activity_detail" rows="4" placeholder="ระบุรายละเอียดกิจกรรมที่อาสาสมัครควรรู้..." style="resize: vertical !important; min-height: 120px !important; max-height: 800px !important;"></textarea>
                                            </div>
                                            <div class="col-md-12">
                                                <label class="form-label">รูปภาพหน้าปกกิจกรรม (ถ้ามี)</label>
                                                <div class="image-upload-wrapper" onclick="document.getElementById('edit_modal_activity_image').click()" style="cursor: pointer; border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 200px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                    <i id="edit_upload_placeholder_icon" class="ri-add-line" style="font-size: 3rem; color: #ccc;"></i>
                                                    <img id="edit_preview_activity_image" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1; display: none;">
                                                    <div class="upload-overlay text-muted" style="position: absolute; bottom: 5px; width: 100%; font-size: 0.85rem; z-index: 2; background: rgba(255,255,255,0.8); padding: 2px 0; text-align: center;">
                                                        <i class="ri-image-add-line"></i> คลิกเพื่อเปลี่ยนรูปภาพ
                                                    </div>
                                                </div>
                                                <input type="file" id="edit_modal_activity_image" class="d-none" accept="image/*" onchange="previewActImageEdit(this)">
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="modal-footer modal-footer-custom">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                    <button type="button" class="btn btn-warning" onclick="updateActivity()">อัปเดตข้อมูลกิจกรรม</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>

<script>
    // Initialize Flatpickr when modal is shown or globally
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof flatpickr !== 'undefined') {
            let fpOptions = {
                dateFormat: "d/m/Y",
                minDate: "today",
                locale: "th" // ถ้ามีการใช้งาน locale ภาษาไทย
            };
            flatpickr("#modal_activity_date", fpOptions);
            flatpickr("#edit_modal_activity_date", fpOptions);
        }
    });

    function Modal_add() {
        document.getElementById('addActivityForm').reset();
        document.getElementById('preview_activity_image').src = '';
        document.getElementById('preview_activity_image').style.display = 'none';
        document.getElementById('upload_placeholder_icon').style.display = 'block';

        document.getElementById('modal_activity_title').classList.remove('is-invalid');
        document.getElementById('modal_attribute_id').classList.remove('is-invalid');
        document.getElementById('modal_activity_date').classList.remove('is-invalid');
        document.getElementById('modal_timeslot').classList.remove('is-invalid');
        document.getElementById('modal_start_time').classList.remove('is-invalid');
        document.getElementById('modal_end_time').classList.remove('is-invalid');
        document.getElementById('modal_location').classList.remove('is-invalid');
        
        document.getElementById('custom_time_wrapper').style.display = 'none';
        $('#add_activity_modal').modal('show');
    }

    function toggleCustomTime(selectElement, prefix = 'modal_') {
        let wrapperId = prefix === 'edit_' ? 'edit_custom_time_wrapper' : 'custom_time_wrapper';
        let startId = prefix === 'edit_' ? 'edit_modal_start_time' : 'modal_start_time';
        let endId = prefix === 'edit_' ? 'edit_modal_end_time' : 'modal_end_time';
        
        const wrapper = document.getElementById(wrapperId);
        const startInput = document.getElementById(startId);
        const endInput = document.getElementById(endId);

        if (selectElement.value === 'custom') {
            wrapper.style.display = 'flex';
            startInput.value = '';
            endInput.value = '';
        } else if (selectElement.value !== '') {
            wrapper.style.display = 'none';
            const option = selectElement.options[selectElement.selectedIndex];
            startInput.value = option.getAttribute('data-start');
            endInput.value = option.getAttribute('data-end');
        } else {
            wrapper.style.display = 'none';
            startInput.value = '';
            endInput.value = '';
        }
    }

    function previewActImage(input) {
        const preview = document.getElementById('preview_activity_image');
        const icon = document.getElementById('upload_placeholder_icon');

        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
            icon.style.display = 'block';
        }
    }

    function saveActivity() {
        let titleInput = document.getElementById('modal_activity_title');
        let attrInput = document.getElementById('modal_attribute_id');
        let dateInput = document.getElementById('modal_activity_date');
        let timeslotInput = document.getElementById('modal_timeslot');
        let startInput = document.getElementById('modal_start_time');
        let endInput = document.getElementById('modal_end_time');
        let locationInput = document.getElementById('modal_location');
        let maxVolunteersInput = document.getElementById('modal_max_volunteers');
        let reserveCountInput = document.getElementById('modal_reserve_count');
        let detailInput = document.getElementById('modal_activity_detail');
        let imageInput = document.getElementById('modal_activity_image');

        let isValid = true;
        
        [titleInput, attrInput, dateInput, locationInput, maxVolunteersInput].forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (timeslotInput.value === 'custom') {
            if (!startInput.value.trim()) { startInput.classList.add('is-invalid'); isValid = false; } else { startInput.classList.remove('is-invalid'); }
            if (!endInput.value.trim()) { endInput.classList.add('is-invalid'); isValid = false; } else { endInput.classList.remove('is-invalid'); }
        } else if (!timeslotInput.value.trim()) {
            timeslotInput.classList.add('is-invalid'); isValid = false;
        } else {
            timeslotInput.classList.remove('is-invalid');
        }

        if (!isValid) return;

        let formData = new FormData();
        formData.append('title', titleInput.value.trim());
        formData.append('attribute_id', attrInput.value.trim());
        formData.append('activity_date', dateInput.value.trim());
        formData.append('timeslot', timeslotInput.value.trim());
        formData.append('start_time', startInput.value.trim());
        formData.append('end_time', endInput.value.trim());
        formData.append('location', locationInput.value.trim());
        formData.append('max_volunteers', maxVolunteersInput.value.trim());
        formData.append('reserve_count', reserveCountInput.value.trim());
        formData.append('activity_detail', detailInput.value.trim());
        
        if (imageInput.files && imageInput.files[0]) {
            formData.append('activity_image', imageInput.files[0]);
        }

        // ปิด Modal ระหว่างบันทึก
        $('#add_activity_modal').modal('hide');

        $.ajax({
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/addActivity",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                // โหลดหน้า 1 ใหม่หลังเพิ่มเสร็จ
                GetData(1);
                Swal.fire({
                    title: 'เพิ่มกิจกรรมสำเร็จ',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function() {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเพิ่มข้อมูลได้', 'error');
            }
        });
    }
    function GetData(page) {
        const formData = new FormData();
        formData.append('page', page);
        
        const searchInput = document.getElementById('search_input').value;
        formData.append('search', searchInput);

        fetch("<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/activity_table", {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('activityTableContainer').innerHTML = html;
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

    function previewActImageEdit(input) {
        const preview = document.getElementById('edit_preview_activity_image');
        const icon = document.getElementById('edit_upload_placeholder_icon');

        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                preview.src = e.target.result;
                preview.style.display = 'block';
                icon.style.display = 'none';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.src = '';
            preview.style.display = 'none';
            icon.style.display = 'block';
        }
    }

    function GetModal_edit(id) {
        // เคลียร์ฟอร์ม
        document.getElementById('editActivityForm').reset();
        document.getElementById('edit_preview_activity_image').src = '';
        document.getElementById('edit_preview_activity_image').style.display = 'none';
        document.getElementById('edit_upload_placeholder_icon').style.display = 'block';
        
        // เอาคลาส is-invalid ออก
        document.getElementById('edit_modal_activity_title').classList.remove('is-invalid');
        document.getElementById('edit_modal_attribute_id').classList.remove('is-invalid');
        document.getElementById('edit_modal_activity_date').classList.remove('is-invalid');
        document.getElementById('edit_modal_timeslot').classList.remove('is-invalid');
        document.getElementById('edit_modal_start_time').classList.remove('is-invalid');
        document.getElementById('edit_modal_end_time').classList.remove('is-invalid');
        document.getElementById('edit_modal_location').classList.remove('is-invalid');
        
        document.getElementById('edit_custom_time_wrapper').style.display = 'none';
        document.getElementById('edit_activity_id').value = id;

        // ตัวอย่างการดึงข้อมูลเพื่อมาแสดงใน Modal
        $.ajax({
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/getActivityById", // ปรับ URL ตามของจริง
            method: 'POST',
            data: { id: id },
            dataType: 'json',
            success: function(response) {
                if(response.status === 'success') {
                    const data = response.data;
                    document.getElementById('edit_modal_activity_title').value = data.activity_title;
                    document.getElementById('edit_modal_attribute_id').value = data.attribute_id;
                    document.getElementById('edit_modal_activity_date').value = data.activity_date;
                    
                    // ตั้งค่าช่วงเวลา
                    if (data.start_time && data.end_time) {
                        let isCustom = true;
                        // ตรวจสอบว่าตรงกับ timeslot ไหนหรือไม่
                        let timeslotSelect = document.getElementById('edit_modal_timeslot');
                        for (let i = 0; i < timeslotSelect.options.length; i++) {
                            let option = timeslotSelect.options[i];
                            if (option.value && option.value !== 'custom') {
                                let optStart = option.getAttribute('data-start');
                                let optEnd = option.getAttribute('data-end');
                                // ตัดวินาทีออกเพื่อเทียบ
                                let dbStart = data.start_time.substring(0, 5);
                                let dbEnd = data.end_time.substring(0, 5);
                                
                                if (optStart === dbStart && optEnd === dbEnd) {
                                    timeslotSelect.value = option.value;
                                    isCustom = false;
                                    break;
                                }
                            }
                        }
                        
                        if (isCustom) {
                            timeslotSelect.value = 'custom';
                            document.getElementById('edit_custom_time_wrapper').style.display = 'flex';
                            document.getElementById('edit_modal_start_time').value = data.start_time.substring(0, 5);
                            document.getElementById('edit_modal_end_time').value = data.end_time.substring(0, 5);
                        } else {
                            document.getElementById('edit_custom_time_wrapper').style.display = 'none';
                            document.getElementById('edit_modal_start_time').value = '';
                            document.getElementById('edit_modal_end_time').value = '';
                        }
                    }
                    
                    document.getElementById('edit_modal_location').value = data.location;
                    document.getElementById('edit_modal_max_volunteers').value = data.max_volunteers;
                    document.getElementById('edit_modal_reserve_count').value = data.reserve_count;
                    document.getElementById('edit_modal_activity_detail').value = data.activity_detail;
                    
                    // สำหรับรูปภาพ
                    if(data.activity_image) {
                        document.getElementById('edit_preview_activity_image').src = data.activity_image;
                        document.getElementById('edit_preview_activity_image').style.display = 'block';
                        document.getElementById('edit_upload_placeholder_icon').style.display = 'none';
                    }

                    // แสดง Modal
                    $('#edit_activity_modal').modal('show');
                } else {
                    Swal.fire('ผิดพลาด', 'ไม่พบข้อมูลกิจกรรม', 'error');
                }
            },
            error: function() {
                // สำหรับช่วงทดสอบ ถ้าไม่มี Backend ให้โชว์ Modal ขึ้นมาเลย
                $('#edit_activity_modal').modal('show');
                // Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้', 'error');
            }
        });
    }

    function updateActivity() {
        let idInput = document.getElementById('edit_activity_id');
        let titleInput = document.getElementById('edit_modal_activity_title');
        let attrInput = document.getElementById('edit_modal_attribute_id');
        let dateInput = document.getElementById('edit_modal_activity_date');
        let timeslotInput = document.getElementById('edit_modal_timeslot');
        let startInput = document.getElementById('edit_modal_start_time');
        let endInput = document.getElementById('edit_modal_end_time');
        let locationInput = document.getElementById('edit_modal_location');
        let maxVolunteersInput = document.getElementById('edit_modal_max_volunteers');
        let reserveCountInput = document.getElementById('edit_modal_reserve_count');
        let detailInput = document.getElementById('edit_modal_activity_detail');
        let imageInput = document.getElementById('edit_modal_activity_image');

        let isValid = true;
        
        [titleInput, attrInput, dateInput, locationInput, maxVolunteersInput].forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.remove('is-invalid');
            }
        });

        if (timeslotInput.value === 'custom') {
            if (!startInput.value.trim()) { startInput.classList.add('is-invalid'); isValid = false; } else { startInput.classList.remove('is-invalid'); }
            if (!endInput.value.trim()) { endInput.classList.add('is-invalid'); isValid = false; } else { endInput.classList.remove('is-invalid'); }
        } else if (!timeslotInput.value.trim()) {
            timeslotInput.classList.add('is-invalid'); isValid = false;
        } else {
            timeslotInput.classList.remove('is-invalid');
        }

        if (!isValid) return;

        let formData = new FormData();
        formData.append('id', idInput.value);
        formData.append('title', titleInput.value.trim());
        formData.append('attribute_id', attrInput.value.trim());
        formData.append('activity_date', dateInput.value.trim());
        formData.append('timeslot', timeslotInput.value.trim());
        formData.append('start_time', startInput.value.trim());
        formData.append('end_time', endInput.value.trim());
        formData.append('location', locationInput.value.trim());
        formData.append('max_volunteers', maxVolunteersInput.value.trim());
        formData.append('reserve_count', reserveCountInput.value.trim());
        formData.append('activity_detail', detailInput.value.trim());
        
        if (imageInput.files && imageInput.files[0]) {
            formData.append('activity_image', imageInput.files[0]);
        }

        $('#edit_activity_modal').modal('hide');

        $.ajax({
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/updateActivity",
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(response) {
                GetData(1);
                Swal.fire({
                    title: 'อัปเดตกิจกรรมสำเร็จ',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function() {
                Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถอัปเดตข้อมูลได้', 'error');
            }
        });
    }

    function delete_activity(id) {
        Swal.fire({
            title: 'คุณต้องการลบกิจกรรมนี้หรือไม่?',
            // text: "ข้อมูลจะไม่สามารถกู้คืนได้เมื่อถูกลบ!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                // ส่ง AJAX ไปลบข้อมูลที่ Backend
                $.ajax({
                    url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/deleteActivity", // แก้ไข URL ตามของจริง
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        GetData(1); // รีโหลดตาราง
                        Swal.fire(
                            'ลบสำเร็จ!',
                            'กิจกรรมถูกลบเรียบร้อยแล้ว.',
                            'success'
                        );
                    },
                    error: function() {
                        Swal.fire('เกิดข้อผิดพลาด', 'ไม่สามารถลบข้อมูลได้', 'error');
                    }
                });
            }
        });
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>