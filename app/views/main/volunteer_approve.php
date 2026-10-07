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
                  <form id="addCustomerForm" autocomplete="off">
                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">ยืนยันตัวตนอาสาสมัคร</h2>
                        </div>
                    </div>

                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อผู้สมัคร" value="<?php echo htmlspecialchars($data['search'] ?? ''); ?>">
                        </div>
                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; min-width: 400px;">
                            <select id="filter_status" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="" <?php echo (isset($data['status']) && $data['status'] === '') ? 'selected' : ''; ?>>ทุกสถานะ</option>
                                <option value="0" <?php echo (!isset($data['status']) || $data['status'] === '0') ? 'selected' : ''; ?>>ยังไม่ได้อนุมัติ</option>
                                <option value="1" <?php echo (isset($data['status']) && $data['status'] === '1') ? 'selected' : ''; ?>>ปฏิเสธ</option>
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
                        <?php require_once __DIR__ . '/table/volunteer_approve_table.php'; ?>
                    </div>


                    <!-- modal ดำเนินการ -->
                   <div class="modal fade" id="approve_volunteer_modal" tabindex="-1" aria-labelledby="approve_volunteer_modalLabel" aria-hidden="true">
                        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
                            <div class="modal-content modal-content-custom">

                                <!-- Header (Fixed) -->
                                <div class="modal-header modal-header-custom">
                                    <!-- <h5 class="modal-title modal-title-custom" id="approve_volunteer_modalLabel">ดำเนินการ</h5> -->
                                    <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <!-- Body (Scrollable) -->
                                <div class="modal-body modal-body-custom">
                                    <form id="approveVolunteerForm" autocomplete="off">
                                        <!-- Hidden Fields -->
                                        <input type="hidden" name="volunteer_id" id="modal_edit_volunteer_id" value="">

                                        <!-- Section: ข้อมูลทั่วไป -->
                                        <div class="mb-4">
                                            <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>
                                            <div class="row g-3">
                                                <div class="col-md-12">
                                                    <label class="form-label">ชื่อ - นามสกุล</label>
                                                    <input type="text" class="form-control" id="modal_vol_name" readonly>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">เบอร์ติดต่อ</label>
                                                    <input type="text" class="form-control" id="modal_vol_phone" readonly>
                                                </div>
                                                <div class="col-md-12">
                                                    <label class="form-label">เลขบัตรประชาชน</label>
                                                    <input type="text" class="form-control" id="modal_vol_citizen" readonly>
                                                </div>
                                             
                                            </div>

                                            <h6 class="modal-section-title mt-4">รูปภาพหลักฐาน</h6>
                                            <div class="row g-3 text-center">
                                                <div class="col-md-6">
                                                    <label class="form-label d-block">รูปถ่ายผู้สมัคร</label>
                                                    <div class="image-upload-wrapper" style="border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 220px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                        <img id="modal_vol_img" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1;">
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label d-block">รูปบัตรประชาชน</label>
                                                    <div class="image-upload-wrapper" style="border: 2px dashed #ccc; border-radius: 8px; padding: 5px; height: 220px; position: relative; background-color: #f8f9fa; display: flex; flex-direction: column; align-items: center; justify-content: center; overflow: hidden;">
                                                        <img id="modal_vol_citizen_img" src="" style="max-width: 100%; max-height: 180px; object-fit: contain; z-index: 1;">
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Section: หมายเหตุการปฏิเสธ (ซ่อนไว้ตอนแรก) -->
                                            <div id="reject_remark_section" class="mt-4" style="display: none;">
                                                <h6 class="modal-section-title text-danger">ระบุหมายเหตุการปฏิเสธ</h6>
                                                <textarea class="form-control" id="reject_remark_input" rows="6" placeholder="โปรดระบุเหตุผล..." oninput="this.classList.remove('is-invalid')"></textarea>
                                            </div>
                                        </div>
                                    </form>
                                </div>

                            <!-- Footer (Fixed) -->
                            <div class="modal-footer modal-footer-custom" id="modal_footer_buttons">
                                <button type="button" class="btn btn-danger text-white" onclick="showRejectRemark()">ปฏิเสธ</button>
                                <button type="button" class="btn btn-success text-white" onclick="approveVolunteer(document.getElementById('modal_edit_volunteer_id').value)">อนุมัติ</button>
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
    function GetData(page) {
        const formData = new FormData();
        formData.append('page', page);
        
        // แนบสถานะ คำค้นหา และจำนวนรายการต่อหน้า
        const filterStatus = document.getElementById('filter_status').value;
        const searchInput = document.getElementById('search_input').value;
        const perPage = document.getElementById('itemPerPage').value;
        
        formData.append('status', filterStatus);
        formData.append('search', searchInput);
        formData.append('per_page', perPage);

        fetch("<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/volunteer_approve_table", {
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

       $('#addCustomerModal').on('hidden.bs.modal', function () {
            $('#customer_name_hint').hide();
            // Optional: clear form fields here if not already cleared elsewhere
        });

    function resetModalButtons() {
        document.getElementById('reject_remark_section').style.display = 'none';
        let remarkInput = document.getElementById('reject_remark_input');
        remarkInput.value = '';
        remarkInput.classList.remove('is-invalid');

        document.getElementById('modal_footer_buttons').innerHTML = `
            <button type="button" class="btn btn-danger text-white" onclick="showRejectRemark()">ปฏิเสธ</button>
            <button type="button" class="btn btn-success text-white" onclick="approveVolunteer(document.getElementById('modal_edit_volunteer_id').value)">อนุมัติ</button>
        `;
    }

    function showRejectRemark() {
        document.getElementById('reject_remark_section').style.display = 'block';
        document.getElementById('modal_footer_buttons').innerHTML = `
            <button type="button" class="btn btn-secondary" onclick="resetModalButtons()">ยกเลิก</button>
            <button type="button" class="btn btn-danger text-white" onclick="confirmRejectVolunteer()">ยืนยันการปฏิเสธ</button>
        `;
    }

    function openApproveModal(data) {
        resetModalButtons(); // Reset to default state before showing
        
        document.getElementById('modal_edit_volunteer_id').value = data.volunteer_id;
        document.getElementById('modal_vol_name').value = data.first_name + ' ' + (data.last_name || '');
        document.getElementById('modal_vol_phone').value = data.volunteer_phone || '-';
        document.getElementById('modal_vol_citizen').value = data.citizen_id || '-';
        
        document.getElementById('modal_vol_img').src = data.volunteer_image || '';
        document.getElementById('modal_vol_citizen_img').src = data.citizen_image || '';

        if (data.approve_status == '1') {
            document.getElementById('reject_remark_section').style.display = 'block';
            document.getElementById('reject_remark_input').value = data.reject_remark || '';
            
            document.getElementById('modal_footer_buttons').innerHTML = `
                <button type="button" class="btn btn-danger text-white" onclick="confirmRejectVolunteer()">บันทึกการแก้ไข</button>
                <button type="button" class="btn btn-success text-white" onclick="approveVolunteer(document.getElementById('modal_edit_volunteer_id').value)">อนุมัติ</button>
            `;
        }

        $('#approve_volunteer_modal').modal('show');
    }

    function approveVolunteer(id) {
        $('#approve_volunteer_modal').modal('hide');
        Swal.fire({
            title: 'ยืนยันการอนุมัติ?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'ยืนยัน',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/approveVolunteer",
                    method: 'POST',
                    data: { id: id },
                    success: function(response) {
                        $('#volunteerTableContainer').html(response);
                        Swal.fire({
                            title: 'อนุมัติแล้ว',
                            icon: 'success',
                            timer: 1500,
                            showConfirmButton: false
                        });
                    }
                });
            }
        });
    }

    function confirmRejectVolunteer() {
        let id = document.getElementById('modal_edit_volunteer_id').value;
        let remarkInput = document.getElementById('reject_remark_input');
        let remark = remarkInput.value;

        if (!remark.trim()) {
            remarkInput.classList.add('is-invalid');
            remarkInput.focus();
            return;
        }

        remarkInput.classList.remove('is-invalid');
        $('#approve_volunteer_modal').modal('hide');
        $.ajax({
            url: "<?php echo defined('BASE_URL') ? BASE_URL : '/Blind_/public'; ?>/rejectVolunteer",
            method: 'POST',
            data: { id: id, remark: remark },
            success: function(response) {
                $('#volunteerTableContainer').html(response);
                Swal.fire({
                    title: 'ปฏิเสธแล้ว',
                    icon: 'success',
                    timer: 1500,
                    showConfirmButton: false
                });
            }
        });
    }

    function viewImage(url) {
        window.open(url, '_blank');
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>