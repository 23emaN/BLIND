<?php
    // 1. นำ Header เข้ามา
    require_once dirname(__DIR__) . '/main/header.php';

    // 2. นำ Sidebar เข้ามา
    require_once dirname(__DIR__) . '/main/sidebar.php';

    $baseUrl = defined('BASE_URL') ? BASE_URL : '';
?>

<div class="container-fluid">
    <div class="main-content d-flex flex-column">
        <div class="content-wrapper">
            <div class="main-page-wrapper">
                <div class="main-card-wrapper">

                    <div class="page-header-box">
                        <div>
                            <h2 class="page-title">จัดการผู้ใช้</h2>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="openAddUser()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มผู้ใช้</span>
                            </button>
                        </div>
                    </div>

                    <!-- Filter Toolbar -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อผู้ใช้ ชื่อ-นามสกุล">
                        </div>

                        <div class="filter-group" style="display: flex; align-items: center; gap: 10px; flex-wrap: nowrap; min-width: 400px;">
                            <select id="userPerPage" class="filter-select" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="25">25 รายการ</option>
                                <option value="50">50 รายการ</option>
                                <option value="75">75 รายการ</option>
                                <option value="100">100 รายการ</option>
                            </select>

                            <select class="filter-select" id="filter_status" style="flex: 1; height: 42px;" onchange="triggerFilterDebounced()">
                                <option value="">ทุกสถานะ</option>
                                <option value="1">ใช้งานได้</option>
                                <option value="0">ถูกระงับ</option>
                            </select>
                        </div>
                    </div>

                    <div id="userTableContainer">
                        <?php require_once __DIR__ . '/table/user_table.php'; ?>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal เพิ่ม/แก้ไขผู้ใช้ -->
<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userModalLabel">เพิ่มผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="userForm" autocomplete="off">
                    <input type="hidden" name="user_id" id="user_id" value="">

                    <div class="mb-3">
                        <label class="form-label" for="f_user_name">ชื่อผู้ใช้ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="user_name" id="f_user_name" maxlength="50" autocomplete="off">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_firstname">ชื่อ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" name="user_firstname" id="f_firstname" maxlength="100">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_lastname">นามสกุล</label>
                            <input type="text" class="form-control" name="user_lastname" id="f_lastname" maxlength="100">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label" for="f_password">
                            รหัสผ่าน <span class="text-danger" id="password_required">*</span>
                            <small class="text-muted" id="password_hint" style="display:none;">(เว้นว่างหากไม่ต้องการเปลี่ยน)</small>
                        </label>
                        <input type="password" class="form-control" name="user_password" id="f_password" autocomplete="new-password">
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_status">สถานะ</label>
                            <select class="form-select" name="user_status" id="f_status">
                                <option value="1">ใช้งานได้</option>
                                <option value="0">ถูกระงับ</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label" for="f_super">สิทธิ์</label>
                            <select class="form-select" name="is_super_admin" id="f_super">
                                <option value="0">ผู้ใช้ทั่วไป</option>
                                <option value="1">ผู้ดูแลระบบสูงสุด</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn btn-primary" id="userSubmitBtn" onclick="submitUser()">บันทึก</button>
            </div>
        </div>
    </div>
</div>

<script>
    const USER_BASE_URL = '<?php echo $baseUrl; ?>';
    let userFilterTimer = null;
    let isSubmittingUser = false;

    // ---- โหลด/กรองตาราง ----
    function GetData(page = 1) {
        const payload = {
            keyword:  ($('#search_input').val() || '').trim(),
            status:   $('#filter_status').val() || '',
            per_page: $('#userPerPage').val() || 25,
            page:     page
        };

        $('#userTableContainer').html(
            '<div class="table-wrap"><table class="table"><tbody><tr>' +
            '<td class="text-center text-muted py-5">กำลังโหลด...</td>' +
            '</tr></tbody></table></div>'
        );

        $.ajax({
            url: USER_BASE_URL + '/user/filter',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function (response) {
                if (response.result === 1) {
                    $('#userTableContainer').html(response.html);
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
        clearTimeout(userFilterTimer);
        userFilterTimer = setTimeout(() => GetData(1), 350);
    }

    // ---- เปิด modal เพิ่ม ----
    function openAddUser() {
        document.getElementById('userForm').reset();
        $('#user_id').val('');
        $('#userModalLabel').text('เพิ่มผู้ใช้');
        $('#password_required').show();
        $('#password_hint').hide();
        $('#f_user_name').prop('readonly', false);
        new bootstrap.Modal(document.getElementById('userModal')).show();
    }

    // ---- เปิด modal แก้ไข ----
    function editUser(id) {
        $.ajax({
            url: USER_BASE_URL + '/user/get?id=' + id,
            method: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.result !== 1) {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่พบข้อมูล', 'error');
                    return;
                }
                const d = response.data;
                document.getElementById('userForm').reset();
                $('#user_id').val(d.user_id);
                $('#f_user_name').val(d.user_name);
                $('#f_firstname').val(d.user_firstname);
                $('#f_lastname').val(d.user_lastname);
                $('#f_status').val(d.user_status);
                $('#f_super').val(String(d.is_super_admin));
                $('#userModalLabel').text('แก้ไขผู้ใช้');
                $('#password_required').hide();
                $('#password_hint').show();
                new bootstrap.Modal(document.getElementById('userModal')).show();
            },
            error: function () {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    // ---- บันทึก (เพิ่ม/แก้ไข) ----
    function submitUser() {
        if (isSubmittingUser) return;

        const id = $('#user_id').val();
        const url = id ? USER_BASE_URL + '/user/edit' : USER_BASE_URL + '/user/add';

        isSubmittingUser = true;
        const btn = $('#userSubmitBtn');
        const originalText = btn.text();
        btn.prop('disabled', true).text('กำลังบันทึก...');

        $.ajax({
            url: url,
            method: 'POST',
            data: $('#userForm').serialize(),
            dataType: 'json',
            success: function (response) {
                isSubmittingUser = false;
                btn.prop('disabled', false).text(originalText);
                if (response.result === 1) {
                    bootstrap.Modal.getInstance(document.getElementById('userModal')).hide();
                    Swal.fire({ icon: 'success', title: response.msg, timer: 1200, showConfirmButton: false });
                    GetData(1);
                } else {
                    Swal.fire({ icon: 'warning', title: 'ไม่สำเร็จ', html: (response.msg || '').replace(/\n/g, '<br>') });
                }
            },
            error: function () {
                isSubmittingUser = false;
                btn.prop('disabled', false).text(originalText);
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อ', 'error');
            }
        });
    }

    // ---- ลบ ----
    function deleteUser(id, name) {
        Swal.fire({
            icon: 'warning',
            title: 'ยืนยันการลบ',
            html: 'ต้องการลบผู้ใช้ <strong>' + $('<div>').text(name).html() + '</strong> ใช่หรือไม่?',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'ลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (!result.isConfirmed) return;
            $.ajax({
                url: USER_BASE_URL + '/user/delete',
                method: 'POST',
                data: { user_id: id },
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
