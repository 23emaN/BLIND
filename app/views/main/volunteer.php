<?php
    require_once dirname(__DIR__) . '/main/header.php';
    require_once dirname(__DIR__) . '/main/sidebar.php';
?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">
                    <div class="page-header-box">
                        <div class="d-flex justify-content-between align-items-center w-100">
                            <h2 class="page-title">รายชื่ออาสาสมัคร</h2>
                        </div>
                    </div>

                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่ออาสาสมัคร" value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
                        </div>
                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; min-width: 400px;">
                            <select id="filter_status" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="" <?php echo (isset($data['status']) && $data['status'] === '') ? 'selected' : ''; ?>>ทุกสถานะ</option>
                                <option value="1" <?php echo (isset($data['status']) && $data['status'] === '1') ? 'selected' : ''; ?>>ใช้งานอยู่</option>
                                <option value="0" <?php echo (isset($data['status']) && $data['status'] === '0') ? 'selected' : ''; ?>>ปิดการใช้งาน</option>
                            </select>
                            <select id="itemPerPage" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="10" <?php echo (isset($data['per_page']) && $data['per_page'] == 10) ? 'selected' : ''; ?>>10 รายการ</option>
                                <option value="25" <?php echo (isset($data['per_page']) && $data['per_page'] == 25) ? 'selected' : ''; ?>>25 รายการ</option>
                                <option value="50" <?php echo (isset($data['per_page']) && $data['per_page'] == 50) ? 'selected' : ''; ?>>50 รายการ</option>
                                <option value="100" <?php echo (isset($data['per_page']) && $data['per_page'] == 100) ? 'selected' : ''; ?>>100 รายการ</option>
                            </select>
                        </div>
                    </div>

                    <div id="volunteerTableContainer">
                        <?php require_once __DIR__ . '/table/volunteer_table.php'; ?>
                    </div>

                    <!-- Modal แก้ไขข้อมูล -->
                   <div class="modal fade" id="edit_volunteer_modal" tabindex="-1" aria-labelledby="edit_volunteer_modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
                            <div class="modal-content modal-content-custom">

                                <div class="modal-header modal-header-custom">
                                    <h5 class="modal-title modal-title-custom">แก้ไขข้อมูลอาสาสมัคร</h5>
                                    <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body modal-body-custom">
                                    <form id="editVolunteerForm" autocomplete="off">
                                        <input type="hidden" id="edit_vol_id">
                                        <div class="mb-4">
                                            <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>
                                            <div class="row g-3">
                                                <div class="col-md-6">
                                                    <label class="form-label">ชื่อ</label>
                                                    <input type="text" class="form-control" id="edit_vol_fname">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">นามสกุล</label>
                                                    <input type="text" class="form-control" id="edit_vol_lname">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">เบอร์ติดต่อ</label>
                                                    <input type="text" class="form-control" id="edit_vol_phone">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label">เลขบัตรประชาชน</label>
                                                    <input type="text" class="form-control" id="edit_vol_citizen">
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">สถานะการใช้งาน</label>
                                                    <select class="form-select" id="edit_vol_status">
                                                        <option value="1">ใช้งานอยู่</option>
                                                        <option value="0">ปิดการใช้งาน</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <h6 class="modal-section-title mt-4">รูปภาพหลักฐาน</h6>
                                            <div class="row g-3 text-center">
                                                <div class="col-md-6">
                                                    <label class="form-label d-block">รูปถ่ายผู้สมัคร</label>
                                                    <div class="image-upload-wrapper" onclick="document.getElementById('upload_vol_img').click()" style="cursor: pointer; border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 220px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                        <img id="edit_vol_img" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1;">
                                                        <div class="upload-overlay text-muted" style="position: absolute; bottom: 5px; width: 100%; font-size: 0.85rem; z-index: 2; background: rgba(255,255,255,0.8); padding: 2px 0;">
                                                            <i class="ri-image-add-line"></i> คลิกที่รูปเพื่อแก้ไข
                                                        </div>
                                                    </div>
                                                    <input type="file" id="upload_vol_img" class="d-none" accept="image/*" onchange="previewImage(this, 'edit_vol_img')">
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label d-block">รูปบัตรประชาชน</label>
                                                    <div class="image-upload-wrapper" onclick="document.getElementById('upload_vol_citizen_img').click()" style="cursor: pointer; border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 220px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                        <img id="edit_vol_citizen_img" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1;">
                                                        <div class="upload-overlay text-muted" style="position: absolute; bottom: 5px; width: 100%; font-size: 0.85rem; z-index: 2; background: rgba(255,255,255,0.8); padding: 2px 0;">
                                                            <i class="ri-image-add-line"></i> คลิกที่รูปเพื่อแก้ไข
                                                        </div>
                                                    </div>
                                                    <input type="file" id="upload_vol_citizen_img" class="d-none" accept="image/*" onchange="previewImage(this, 'edit_vol_citizen_img')">
                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                                <div class="modal-footer modal-footer-custom">
                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                                    <button type="button" class="btn btn-primary" onclick="saveVolunteerEdit()">บันทึกการแก้ไข</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    
    function GetModal_edit(data) {
        document.getElementById('edit_vol_id').value = data.volunteer_id;
        document.getElementById('edit_vol_fname').value = data.first_name || '';
        document.getElementById('edit_vol_lname').value = data.last_name || '';
        document.getElementById('edit_vol_phone').value = data.volunteer_phone || '';
        document.getElementById('edit_vol_citizen').value = data.citizen_id || '';
        document.getElementById('edit_vol_status').value = data.active_status || '0';

        document.getElementById('edit_vol_img').src = data.volunteer_image || '';
        document.getElementById('edit_vol_citizen_img').src = data.citizen_image || '';
        
        // เคลียร์ค่า input file ทุกครั้งที่เปิด
        document.getElementById('upload_vol_img').value = '';
        document.getElementById('upload_vol_citizen_img').value = '';

        $('#edit_volunteer_modal').modal('show');
    }

    function previewImage(input, imgId) {
        if (input.files && input.files[0]) {
            let reader = new FileReader();
            reader.onload = function(e) {
                document.getElementById(imgId).src = e.target.result;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function saveVolunteerEdit() {
        // Here you will add the AJAX call to save the edits to the database
        // For now, it just closes the modal and shows a success alert

        let id = document.getElementById('edit_vol_id').value;
        let fname = document.getElementById('edit_vol_fname').value;
        let lname = document.getElementById('edit_vol_lname').value;
        let phone = document.getElementById('edit_vol_phone').value;
        let citizen = document.getElementById('edit_vol_citizen').value;
        let status = document.getElementById('edit_vol_status').value;

        // Simulate AJAX request
        $('#edit_volunteer_modal').modal('hide');
        Swal.fire({
            title: 'บันทึกสำเร็จ',
            text: 'ระบบได้เตรียมการส่งข้อมูลเรียบร้อยแล้ว (รันเบื้องหลังเพื่ออัปเดต)',
            icon: 'success',
            timer: 1500,
            showConfirmButton: false
        });
    }
    function GetData(page) {
        const formData = new FormData();
        formData.append('page', page);

        const searchInput = document.getElementById('search_input').value;
        const filterStatus = document.getElementById('filter_status').value;
        const perPage = document.getElementById('itemPerPage').value;

        formData.append('search', searchInput);
        formData.append('status', filterStatus);
        formData.append('per_page', perPage);

        fetch("<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/volunteer_table", {
            method: 'POST',
            body: formData
        })
        .then(response => response.text())
        .then(html => {
            document.getElementById('volunteerTableContainer').innerHTML = html;
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
    require_once dirname(__DIR__) . '/main/footer.php';
?>
