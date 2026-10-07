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
                            <h2 class="page-title">ผู้ใช้</h2>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn-add-action" onclick="modal_add_customer()">
                                <i class="ri-add-line"></i>
                                <span>เพิ่มผู้ใช้</span>
                            </button>
                        </div>
                    </div>


                    <!-- Filter Toolbar (ค้นหา & ตัวกรองสถานะ) -->
                    <div class="filter-toolbar">
                        <div class="search-box-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" class="search-input" id="search_input" onkeyup="triggerFilterDebounced()" placeholder="ค้นหาชื่อลูกค้า ผู้ดูแล ทีม">
                        </div>
                    </div>

                    <div id="customerTableContainer">
                        <?php require_once __DIR__ . '/table/customer_table.php'; ?>
                    </div>

                </div> <!-- End .main-card-wrapper -->
            </div>
        </div>
    </div>
</div>

<!-- Modal Import Excel -->

<!-- Modal เพิ่มลูกค้าใหม่ (Header/Footer Fixed, มีแต่ Body ที่ Scroll) -->
<div class="modal fade" id="addCustomerModal" tabindex="-1" aria-labelledby="addCustomerModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-lg modal-dialog-custom">
        <div class="modal-content modal-content-custom">

            <!-- Header (Fixed) -->
            <div class="modal-header modal-header-custom">
                <h5 class="modal-title modal-title-custom" id="addCustomerModalLabel">เพิ่มลูกค้าใหม่</h5>
                <button type="button" class="btn-close modal-close-custom" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Body (Scrollable) -->
            <div class="modal-body modal-body-custom">
                <form id="addCustomerForm" autocomplete="off">
                    <!-- Hidden Fields -->
                    <input type="hidden" name="fiscal_id" value="<?php echo htmlspecialchars($data['fiscal_id'] ?? ''); ?>">
                    <input type="hidden" name="company_id" value="<?php echo htmlspecialchars($data['active_company_id'] ?? ''); ?>">
                    <input type="hidden" name="customer_id" id="edit_customer_id" value="">

                    <!-- Section: ข้อมูลทั่วไป -->
                    <div class="mb-4">
                        <h6 class="modal-section-title">ข้อมูลทั่วไป</h6>

                        <!-- ชื่อบริษัท / กิจการ -->
                        <div class="mb-3">
                            <label class="form-label modal-form-label" for="customer_name">
                                ชื่อบริษัท / กิจการ <span class="text-danger">*</span>
                            </label>
                            <div class="position-relative dropdown-autocomplete">
                                <input type="text" class="form-control modal-form-control autocomplete-input" name="customer_name" id="customer_name" placeholder="ระบุชื่อบริษัท / กิจการ (ค้นหาจากลูกค้าเดิมได้)" autocomplete="off">
                                <ul class="dropdown-menu autocomplete-list customer-autocomplete-list w-100 shadow-sm" style="max-height: 200px; overflow-y: auto; padding: 0; margin-top: 4px; border: 1px solid #e2e8f0; border-radius: 8px; position: absolute; z-index: 1050; display: none;"></ul>
                            </div>
                            <div class="invalid-feedback" style="font-size: 0.85rem; font-weight: 500; margin-top: 6px;">กรุณาระบุชื่อบริษัท / กิจการ</div>
                            <small class="text-muted" id="customer_name_hint" style="display:none; font-size: 0.8rem; margin-top:4px;">* ลูกค้าเดิมในระบบ จะถูกดึงข้อมูลมาเติมให้โดยอัตโนมัติ</small>
                        </div>

                        <!-- 3 คอลัมน์: เดือนที่เริ่มให้บริการ / เดือนสิ้นสุด / สถานะลูกค้า -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนที่เริ่มให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_date" id="service_start_date">
                                    <option value="1" selected>มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เดือนสิ้นสุดการให้บริการ</label>
                                <select class="form-select modal-form-select" name="service_start_end" id="service_start_end">
                                    <option value="0" selected>ยังให้บริการอยู่</option>
                                    <option value="1">มกราคม</option>
                                    <option value="2">กุมภาพันธ์</option>
                                    <option value="3">มีนาคม</option>
                                    <option value="4">เมษายน</option>
                                    <option value="5">พฤษภาคม</option>
                                    <option value="6">มิถุนายน</option>
                                    <option value="7">กรกฎาคม</option>
                                    <option value="8">สิงหาคม</option>
                                    <option value="9">กันยายน</option>
                                    <option value="10">ตุลาคม</option>
                                    <option value="11">พฤศจิกายน</option>
                                    <option value="12">ธันวาคม</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">สถานะลูกค้า</label>
                                <select class="form-select modal-form-select" name="active_status">
                                    <option value="1" selected>ใช้บริการอยู่</option>
                                    <option value="0">เลิกจ้าง</option>
                                </select>
                            </div>
                        </div>

                        <!-- 2 คอลัมน์: ผู้ดูแล / ทีม -->
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้ดูแล</label>
                                <select class="form-select modal-form-select" name="user_id" id="user_id_select" onchange="updateTeamInfo()">
                                    <option value="" data-team-id="" data-team-name="" selected>ยังไม่ระบุผู้ดูแล</option>
                                    <?php if (! empty($data['caretakers'])): ?>
                                        <?php foreach ($data['caretakers'] as $caretaker): 
                                            $name = trim(($caretaker['user_firstname'] ?? '') . ' ' . ($caretaker['user_lastname'] ?? ''));
                                            if (empty($name)) {
                                                $name = $caretaker['user_email'] ?? 'User ID: ' . $caretaker['user_id'];
                                            }
                                        ?>
                                            <option value="<?php echo htmlspecialchars($caretaker['user_id'] ?? ''); ?>"
                                                    data-team-id="<?php echo htmlspecialchars($caretaker['team_id'] ?? ''); ?>"
                                                    data-team-name="<?php echo htmlspecialchars($caretaker['team_name'] ?? ''); ?>">
                                                <?php echo htmlspecialchars($name); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ทีม</label>
                                <input type="hidden" name="team_id" id="team_id_hidden">
                                <input type="text" class="form-control modal-form-control team-name-disabled" id="team_name_display" placeholder="ไม่มีทีม" readonly disabled>
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลบัญชี -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลบัญชี</h6>

                        <!-- แถวที่ 1: ปิดงบประจำปี / วันสิ้นรอบบัญชี / ค่าทำบัญชีต่อเดือน -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ปิดงบ</label>
                                <select class="form-select modal-form-select" name="closing_status" id="closing_status">
                                    <option value="0" selected>ปิดงบประจำเดือน</option>
                                    <option value="1">ปิดงบประจำปี</option>
                                    <option value="2">ไม่ปิดงบ</option>
                                </select>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">วันสิ้นรอบบัญชี</label>
                                <div class="modal-input-icon-wrap">
                                    <input type="text" class="form-control modal-form-control modal-input-with-icon" name="fiscal_closing_date" id="fiscal_closing_date" value="31/12/2026" placeholder="31/12/2026">
                                    <i class="ri-calendar-line modal-input-icon modal-input-icon-static"></i>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                             <div class="col-md-4">
                                <label class="form-label modal-form-label">ค่าทำบัญชีต่อเดือน</label>
                                <input type="number" step="0.01" class="form-control modal-form-control" name="accounts_amount" id="accounts_amount" value="0" >
                            </div>

                             <div class="col-md-4">
                                <label class="form-label modal-form-label">ค่าปิดบัญชี</label>
                                <input type="number" step="0.01" class="form-control modal-form-control" name="closing_amount" id="closing_amount" value="0" >
                            </div>

                             <div class="col-md-4">
                                <label class="form-label modal-form-label">ค่าสอบบัญชี</label>
                                <input type="number" step="0.01" class="form-control modal-form-control" name="auditing_amount" id="auditing_amount" value="0" >
                            </div>
                        </div>


                       

                        <!-- แถวที่ 2: จด VAT / มีพนักงาน / ประกันสังคม / cpd / cpa -->
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">จด VAT</label>
                                <select class="form-select modal-form-select" name="is_vat">
                                    <option value="0" selected>ไม่จด VAT</option>
                                    <option value="1">จด VAT</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">มีพนักงาน</label>
                                <select class="form-select modal-form-select" name="is_employees">
                                    <option value="0" selected>ไม่มีพนักงาน</option>
                                    <option value="1">มีพนักงาน</option>
                                </select>
                            </div>

                            <div class="col-md-4">
                                <label class="form-label modal-form-label">ประกันสังคม</label>
                                <select class="form-select modal-form-select" name="is_social_security">
                                    <option value="0" selected>ไม่มีประกันสังคม</option>
                                    <option value="1">มีประกันสังคม</option>
                                </select>
                            </div>
                        </div>

                        <!-- แถวที่ 3: ผู้ทำบัญชี (CPD) / ผู้สอบบัญชี (CPA) -->
                        <div class="row g-3 mt-1">
                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้ทำบัญชี (CPD)</label>
                                <input type="text" class="form-control modal-form-control" name="cpd_name" id="cpd_name" placeholder="ชื่อผู้ทำบัญชี">
                            </div>

                            <div class="col-md-6">
                                <label class="form-label modal-form-label">ผู้สอบบัญชี (CPA)</label>
                                <input type="text" class="form-control modal-form-control" name="cpa_name" id="cpa_name" placeholder="ชื่อผู้สอบบัญชี">
                            </div>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ข้อมูลติดต่อและเอกสาร -->
                    <div>
                        <h6 class="modal-section-title">ข้อมูลติดต่อและเอกสาร</h6>

                        <!-- แถวที่ 1: เบอร์ติดต่อ / อีเมล / LINE ID -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">เบอร์ติดต่อ</label>
                                <input type="number" class="form-control modal-form-control" name="contact_tel" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">อีเมล</label>
                                <input type="email" class="form-control modal-form-control" name="contact_email" placeholder="">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label modal-form-label">LINE ID</label>
                                <input type="text" class="form-control modal-form-control-highlight" name="contact_line_id" placeholder="TBacc">
                            </div>
                        </div>

                        <!-- URL เก็บไฟล์เอกสารลูกค้า -->
                        <!-- <div class="mb-3">
                            <label class="form-label modal-form-label">URL เก็บไฟล์เอกสารลูกค้า</label>
                            <input type="text" class="form-control modal-form-control" name="doc_url" placeholder="เช่น https://drive.google.com/...">
                        </div> -->

                        <!-- LINE Group ID / Token -->
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label modal-form-label mb-0">LINE Group ID / Token</label>
                                <button type="button" class="btn btn-sm modal-video-btn">
                                    <i class="ri-play-circle-line" style="margin-right: 4px;"></i> ดูวิดีโอสอน
                                </button>
                            </div>
                            <input type="text" class="form-control modal-form-control" name="line_token" placeholder="กรอก LINE Group ID หรือ Token สำหรับส่งข้อความ">
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: ระบบราชการ -->
                    <div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="modal-section-title mb-0">ระบบราชการ</h6>
                            <button type="button" class="btn btn-sm modal-video-btn" onclick="addAccountRow()">
                                <i class="ri-add-line"></i> เพิ่มข้อมูล
                            </button>
                        </div>

                        <div id="accountsList" class="gov-accounts-list">
                            <!-- Dynamic rows will be added here -->
                        </div>

                        <div id="accountsEmptyState" class="gov-accounts-empty">
                            <i class="ri-shield-keyhole-line"></i>
                            <span>ยังไม่มีข้อมูลระบบราชการ กด "เพิ่มข้อมูล" เพื่อเริ่มเพิ่ม</span>
                        </div>
                    </div>

                    <!-- เส้นประคั่นส่วน -->
                    <div class="modal-section-divider"></div>

                    <!-- Section: งานรายเดือนที่ไม่ต้องทำ -->
                    <div>
                        <h6 class="modal-section-title">งานรายเดือนที่ไม่ต้องทำ</h6>

                        <!-- Checkboxes Grid -->
                        <div class="row g-3">
                            <?php if (! empty($data['tasks'])): ?>
                                <?php
                                    $totalTasks = count($data['tasks']);
                                    $half       = ceil($totalTasks / 2);
                                    $leftTasks  = array_slice($data['tasks'], 0, $half);
                                    $rightTasks = array_slice($data['tasks'], $half);
                                ?>
                                <!-- Left Column -->
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($leftTasks as $task): ?>
                                            <label class="d-flex align-items-center modal-checkbox-item">
                                                <input type="checkbox" name="monthly_skip[]" value="<?php echo htmlspecialchars($task['tasks_id'] ?? ''); ?>" class="form-check-input modal-checkbox-input me-2">
                                                <span class="modal-checkbox-label"><?php echo htmlspecialchars($task['tasks_name'] ?? ''); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>

                                <!-- Right Column -->
                                <div class="col-md-6">
                                    <div class="d-flex flex-column gap-2">
                                        <?php foreach ($rightTasks as $task): ?>
                                            <label class="d-flex align-items-center modal-checkbox-item">
                                                <input type="checkbox" name="monthly_skip[]" value="<?php echo htmlspecialchars($task['tasks_id'] ?? ''); ?>" class="form-check-input modal-checkbox-input me-2">
                                                <span class="modal-checkbox-label"><?php echo htmlspecialchars($task['tasks_name'] ?? ''); ?></span>
                                            </label>
                                        <?php endforeach; ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <div class="col-12 text-muted">ไม่พบข้อมูลงานรายเดือน</div>
                            <?php endif; ?>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer (Fixed) -->
            <div class="modal-footer modal-footer-custom">
                <button type="button" class="btn modal-btn-cancel" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="button" class="btn modal-btn-save" onclick="submitAddCustomer()">บันทึกข้อมูล</button>
            </div>
        </div>
    </div>
</div>


<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
<script src="https://npmcdn.com/flatpickr/dist/l10n/th.js"></script>
<script>

    $(document).ready(function() {
        const allCustomersData = <?php echo json_encode($data['all_customers'] ?? []); ?>;
        
        const thaiMonths = [
            "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
            "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
        ];

        // Setup Customer Autocomplete
        function renderCustomerAutocomplete(inputElem, listElem, query) {
            listElem.empty();
            let matches = allCustomersData.filter(c => c.customer_name.toLowerCase().includes(query.toLowerCase()));
            matches = matches.slice(0, 5);

            if (matches.length > 0) {
                matches.forEach(match => {
                    listElem.append(`
                        <li class="dropdown-item py-2 px-3 border-bottom" style="cursor: pointer;"
                            onmousedown="event.preventDefault(); selectCustomerAutocomplete('${match.customer_name.replace(/'/g, "\\'")}', this)">
                            <div class="fw-semibold text-dark">${match.customer_name}</div>
                        </li>
                    `);
                });
                listElem.show();
            } else {
                listElem.hide();
            }
        }

        $('#customer_name').on('keyup focus', function () {
            const listElem = $(this).siblings('.customer-autocomplete-list');
            if ($(this).val().trim() !== '') {
                renderCustomerAutocomplete($(this), listElem, $(this).val().trim());
            } else {
                listElem.hide();
            }
        });

        $('#customer_name').on('blur', function () {
            $(this).siblings('.customer-autocomplete-list').hide();
        });

        window.selectCustomerAutocomplete = function (cname, el) {
            const inputElem = $(el).closest('.dropdown-autocomplete').find('.autocomplete-input');
            const listElem = $(el).closest('.autocomplete-list');
            inputElem.val(cname);
            listElem.hide();

            const match = allCustomersData.find(c => c.customer_name === cname);
            if (match) {
                $('input[name="contact_tel"]').val(match.customer_phone || '');
                $('input[name="contact_email"]').val(match.customer_email || '');
                $('input[name="contact_line_id"]').val(match.line_id || '');
                $('input[name="line_token"]').val(match.line_group_token || '');
                
                if (match.fiscal_closing_date) {
                    const parts = match.fiscal_closing_date.split('-');
                    if (parts.length === 3) {
                        $('#fiscal_closing_date').val(`${parts[2]}/${parts[1]}/${parts[0]}`);
                    }
                }

                // Account Info
                if (match.closing_status !== null) $('#closing_status').val(match.closing_status).trigger('change.select2');
                if (match.accounts_amount !== null) $('#accounts_amount').val(match.accounts_amount);
                if (match.closing_amount !== null) $('#closing_amount').val(match.closing_amount);
                if (match.auditing_amount !== null) $('#auditing_amount').val(match.auditing_amount);
                
                // Other settings
                if (match.is_vat !== null) $('select[name="is_vat"]').val(match.is_vat).trigger('change.select2');
                if (match.is_employees !== null) $('select[name="is_employees"]').val(match.is_employees).trigger('change.select2');
                if (match.is_social_security !== null) $('select[name="is_social_security"]').val(match.is_social_security).trigger('change.select2');
                
                if (match.cpd_name !== null) $('#cpd_name').val(match.cpd_name);
                if (match.cpa_name !== null) $('#cpa_name').val(match.cpa_name);
                
                // Status & Service Dates
                if (match.active_status !== null) $('select[name="active_status"]').val(match.active_status).trigger('change.select2');
                if (match.service_start_date !== null) {
                    $('#service_start_date').val(match.service_start_date).trigger('change');
                    if (match.service_start_end !== null) {
                        setTimeout(() => { 
                            $('#service_start_end').val(match.service_start_end).trigger('change.select2'); 
                        }, 50);
                    }
                }
                
                // User ID / Team
                if (match.user_id !== null && match.user_id !== '') {
                    if ($('#user_id_select option[value="' + match.user_id + '"]').length > 0) {
                        $('#user_id_select').val(match.user_id).trigger('change').trigger('change.select2');
                    } else {
                        $('#user_id_select').val('').trigger('change').trigger('change.select2');
                    }
                } else {
                    $('#user_id_select').val('').trigger('change').trigger('change.select2');
                }
                
                // Government Accounts
                if (match.accounts && Array.isArray(match.accounts)) {
                    $('#accountsList').empty();
                    let addedDefaults = new Set();
                    match.accounts.forEach(acc => {
                        if (acc.account_name === 'กรมพัฒนาธุรกิจการค้า') {
                            if (addedDefaults.has('DBD')) return;
                            addedDefaults.add('DBD');
                        }
                        if (acc.account_name === 'กรมสรรพากร') {
                            if (addedDefaults.has('RD')) return;
                            addedDefaults.add('RD');
                        }
                        const isDefault = acc.account_name === 'กรมพัฒนาธุรกิจการค้า' || acc.account_name === 'กรมสรรพากร';
                        addAccountRow(acc.account_name, acc.account_user_name, acc.account_password, isDefault);
                    });
                    if (typeof updateAccountsEmptyState === 'function') {
                        updateAccountsEmptyState();
                    }
                }
                $('#customer_name_hint').show();
            }
        };

        $('#addCustomerModal').on('hidden.bs.modal', function () {
            $('#customer_name_hint').hide();
            // Optional: clear form fields here if not already cleared elsewhere
        });

        $('#service_start_date').on('change', function() {
            var startMonth = parseInt($(this).val()) || 1;
            var currentEndMonth = parseInt($('#service_start_end').val());
            
            // ล้างตัวเลือกเดิมออกให้หมด
            $('#service_start_end').empty();
            
            // สร้างตัวเลือก 'ยังให้บริการอยู่' เสมอ
            $('#service_start_end').append($('<option>', {
                value: 0,
                text: 'ยังให้บริการอยู่'
            }));
            
            // สร้างตัวเลือกเฉพาะเดือนที่ >= เดือนที่เริ่ม
            for (var i = startMonth; i <= 12; i++) {
                $('#service_start_end').append($('<option>', {
                    value: i,
                    text: thaiMonths[i - 1]
                }));
            }
            
            // คืนค่าที่เคยเลือกไว้ ถ้าน้อยกว่าเดือนเริ่มต้นให้ปรับเป็น 0
            if (currentEndMonth === 0 || currentEndMonth >= startMonth) {
                $('#service_start_end').val(currentEndMonth);
            } else {
                $('#service_start_end').val(0);
            }
        });
        
        // Trigger on load to set initial state
        $('#service_start_date').trigger('change');

        if (typeof flatpickr !== 'undefined') {
            flatpickr("#fiscal_closing_date", {
                dateFormat: "d/m/Y",
                locale: "th",
                allowInput: true,
                static: true
            });
        }

        // Initialize Select2
        if ($.fn.select2) {
            $('#customerPerPage, #filter_status, #user_id_filter').select2({
                width: '100%'
            });
            $('#addCustomerModal select').select2({
                dropdownParent: $('#addCustomerModal'),
                width: '100%'
            });
        }

        // Clear validation on input
        $('#customer_name').on('input change', function() {
            if ($(this).val().trim()) {
                $(this).removeClass('is-invalid border border-danger');
            }
        });
        updateAccountsEmptyState();
    });

    function togglePasswordVisibility(icon) {
        const input = $(icon).siblings('input')[0];
        if (input) {
            if (input.type === 'password') {
                input.type = 'text';
                $(icon).removeClass('ri-eye-off-line').addClass('ri-eye-line');
            } else {
            input.type = 'password';
            $(icon).removeClass('ri-eye-line').addClass('ri-eye-off-line');
            }
        }
    }

    let filterDebounceTimer = null;

    function triggerFilterDebounced() {
        clearTimeout(filterDebounceTimer);
        filterDebounceTimer = setTimeout(function () {
            loadCustomerTable();
        }, 400);
    }

    function loadCustomerTable(page) {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var payload = {
            keyword: $('#search_input').val().trim(),
            status: $('#filter_status').val(),
            user_id: $('#user_id_filter').val(),
            page: page || 1,
            per_page: parseInt($('#customerPerPage').val()) || 25,
            fiscal_year: '<?php echo isset($data['active_fiscal_year']) ? $data['active_fiscal_year'] : ''; ?>'
        };

        $('#customerTableContainer').html(`
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th class="text-start" style="width: 25%;">ชื่อลูกค้า</th>
                            <th class="text-center" style="width: 14%;">สถานะ</th>
                            <th class="text-center" style="width: 12%;">วันสิ้นรอบ</th>
                            <th class="text-center" style="width: 15%;">ค่าบัญชี</th>
                            <th class="text-center" style="width: 12%;">ผู้ดูแล</th>
                            <th class="text-center" style="width: 10%;">ติดต่อ</th>
                            <th class="text-center" style="width: 12%;">จัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-5">
                                <span class="fw-medium" style="color: #64748b; font-size: 0.9rem;">กำลังโหลด...</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        `);

        $.ajax({
            url: baseUrl + '/customer/filter',
            method: 'POST',
            data: payload,
            dataType: 'json',
            success: function(response) {
                if (response.result === 1) {
                    $('#customerTableContainer').html(response.html);
                } else {
                    console.error('Filter error:', response.msg);
                }
            },
            error: function(xhr, status, error) {
                console.error('FILTER AJAX ERROR', status, error);
            }
        });
    }

    function exportCustomerExcel() {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var keyword = ($('#search_input').val() || '').trim();
        var status = $('#filter_status').val() || '';
        var userId = $('#user_id_filter').val() || '';

        var selectedYear = '<?php echo htmlspecialchars($selected_year ?? '', ENT_QUOTES, 'UTF-8'); ?>';
        var companyName = '<?php echo htmlspecialchars($company_name ?? '', ENT_QUOTES, 'UTF-8'); ?>';

        var params = new URLSearchParams();
        if (keyword) params.append('keyword', keyword);
        if (status !== '') params.append('status', status);
        if (userId) params.append('user_id', userId);
        if (selectedYear) params.append('year', selectedYear);
        if (companyName) params.append('company', companyName);

        var queryString = params.toString();
        window.location.href = baseUrl + '/report/customer' + (queryString ? '?' + queryString : '');
    }

    function downloadCustomerTemplate() {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var fiscalId = $('input[name="fiscal_id"]').val() || '';
        window.location.href = baseUrl + '/report/customer_template?fiscal_id=' + fiscalId;
    }

    function handleImportExcel(input) {
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        if (input.files && input.files[0]) {
            var file = input.files[0];
            var fileName = file.name;
            var fiscalId = $('input[name="fiscal_id"]').val() || '';

            Swal.fire({
                icon: 'question',
                title: 'ยืนยันการนำเข้า?',
                text: 'คุณต้องการอัปโหลดข้อมูลจากไฟล์ ' + fileName + ' ใช่หรือไม่?',
                showCancelButton: true,
                confirmButtonColor: '#3b82f6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'ใช่, นำเข้าเลย',
                cancelButtonText: 'ยกเลิก',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    var formData = new FormData();
                    formData.append('excel_file', file);
                    formData.append('fiscal_id', fiscalId);

                    Swal.fire({
                        title: 'กำลังนำเข้าข้อมูล...',
                        text: 'กรุณารอสักครู่',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    $.ajax({
                        url: baseUrl + '/customer/import_excel',
                        method: 'POST',
                        data: formData,
                        processData: false,
                        contentType: false,
                        dataType: 'json',
                        success: function(response) {
                            $('#importExcelModal').modal('hide');
                            input.value = '';
                            if (response.result === 1) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'สำเร็จ',
                                    text: response.msg || 'นำเข้าข้อมูลลูกค้าสำเร็จ',
                                    confirmButtonColor: '#3b82f6'
                                }).then(() => {
                                    location.reload();
                                });
                            } else {
                                Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถนำเข้าข้อมูลได้', 'error');
                            }
                        },
                        error: function(err) {
                            input.value = '';
                            Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์', 'error');
                            console.error(err);
                        }
                    });
                } else {
                    input.value = '';
                }
            });
        }
    }

    // เพิ่มข้อมูลลูกค้า
    function modal_add_customer() {
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
            document.getElementById('edit_customer_id').value = '';
            document.getElementById('addCustomerModalLabel').innerText = 'เพิ่มลูกค้าใหม่';
            document.querySelectorAll('input[name="monthly_skip[]"]').forEach(cb => cb.checked = false);
            $('#customer_name').removeClass('is-invalid');

            // Reset Select2s
            if ($.fn.select2) {
                $('#addCustomerModal select').trigger('change.select2');
            }
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');

            // Reset password inputs and icons
            document.getElementById('accountsList').innerHTML = '';
            addAccountRow('กรมพัฒนาธุรกิจการค้า', '', '', true);
            addAccountRow('กรมสรรพากร', '', '', true);
            updateAccountsEmptyState();
        }
        const modalElement = document.getElementById('addCustomerModal');
        const myModal = new bootstrap.Modal(modalElement);
        myModal.show();
    }

    function updateTeamInfo() {
        const select = document.getElementById('user_id_select');
        if (!select || select.selectedIndex < 0) {
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');
            return;
        }

        const selectedOption = select.options[select.selectedIndex];
        if (!selectedOption) {
            $('#team_id_hidden').val('');
            $('#team_name_display').val('');
            return;
        }

        const teamId = selectedOption.getAttribute('data-team-id') || '';
        const teamName = selectedOption.getAttribute('data-team-name') || '';

        document.getElementById('team_id_hidden').value = teamId;
        document.getElementById('team_name_display').value = teamName ? teamName : (select.value ? 'ไม่มีทีม' : '');
    }

    let isSubmittingCustomer = false;
    function submitAddCustomer() {
        if (isSubmittingCustomer) return;

        // Get fields
        const customerName = $('#customer_name').val().trim();

        let isValid = true;

        let errorMessages = [];

        // Validate customer_name
        if (!customerName) {
            $('#customer_name').addClass('is-invalid border border-danger');
            errorMessages.push('ชื่อลูกค้า');
            isValid = false;
        } else {
            $('#customer_name').removeClass('is-invalid border border-danger');
        }

        // Validate required accounts
        $('input[name="account_name[]"]').each(function(index) {
            let name = $(this).val();
            if (name === 'กรมพัฒนาธุรกิจการค้า' || name === 'กรมสรรพากร') {
                let user = $('input[name="account_user_name[]"]').eq(index).val().trim();
                let pass = $('input[name="account_password[]"]').eq(index).val().trim();
                
                if (!user) {
                    $('input[name="account_user_name[]"]').eq(index).addClass('border border-danger');
                    errorMessages.push(`Username/ID ของ${name}`);
                    isValid = false;
                }
                
                if (!pass) {
                    $('input[name="account_password[]"]').eq(index).addClass('border border-danger');
                    errorMessages.push(`รหัสผ่าน ของ${name}`);
                    isValid = false;
                }
            }
        });

        if (!isValid) {
            let htmlMsg = '<div style="text-align: left; padding-left: 2rem;">';
            errorMessages.forEach(msg => {
                htmlMsg += `<div class="mb-1 text-danger">- ${msg}</div>`;
            });
            htmlMsg += '</div>';

            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                html: htmlMsg,
                confirmButtonColor: '#3b82f6',
                confirmButtonText: 'ตกลง'
            });
            return; // หยุดการทำงานถ้ากรอกไม่ครบ
        }

        isSubmittingCustomer = true;
        const submitBtn = $('#addCustomerModal .modal-footer button:last-child');
        const originalBtnText = submitBtn.text();
        submitBtn.prop('disabled', true).text('กำลังบันทึก...');

        var formData = $('#addCustomerForm').serialize();
        var customerId = $('#edit_customer_id').val();
        var baseUrl = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>';
        var targetUrl = customerId ? baseUrl + '/customer/edit' : baseUrl + '/customer/add';

        $.ajax({
            url: targetUrl,
            method: 'POST',
            data: formData,
            dataType: 'json',
            success: function(response) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);

                if (response.result === 1) {
                    $('#addCustomerModal').modal('hide');
                    Swal.fire({
                        icon: 'success',
                        title: 'สำเร็จ',
                        text: customerId ? 'แก้ไขข้อมูลลูกค้าสำเร็จ' : 'เพิ่มลูกค้าสำเร็จ',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'ตกลง'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'ผิดพลาด',
                        text: response.msg || 'เกิดข้อผิดพลาดในการบันทึกข้อมูล',
                        confirmButtonColor: '#3b82f6',
                        confirmButtonText: 'ตกลง'
                    });
                }
            },
            error: function(err) {
                isSubmittingCustomer = false;
                submitBtn.prop('disabled', false).text(originalBtnText);
                console.error("AJAX Error:", err);
                Swal.fire({
                    icon: 'error',
                    title: 'ผิดพลาด',
                    text: 'เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์',
                    confirmButtonColor: '#3b82f6',
                    confirmButtonText: 'ตกลง'
                });
            }
        });
    }

    // เปิดคลังไฟล์ของลูกค้ารายนี้
    function viewCustomerDrive(customer_id) {
        window.location.href = '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer_drive?id=' + customer_id;
    }

    // แก้ไขข้อมูลลูกค้า
    function editCustomer(customer_id) {
        // 1. เคลียร์ข้อมูลในฟอร์มเก่าทิ้ง (ถ้ามี)
        const form = document.getElementById('addCustomerForm');
        if(form) {
            form.reset();
            document.getElementById('edit_customer_id').value = customer_id;
            document.getElementById('addCustomerModalLabel').innerText = 'แก้ไขข้อมูลลูกค้า';
            document.querySelectorAll('input[name="monthly_skip[]"]').forEach(cb => cb.checked = false);
            $('#customer_name').removeClass('is-invalid');
        }

        // Fetch existing data
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer/get?id=' + customer_id,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if(response.result === 1) {
                    var data = response.data;
                    $('#customer_name').val(data.customer_name).removeClass('is-invalid');
                    $('#service_start_date').val(data.service_start_date).trigger('change.select2');
                    $('#service_start_end').val(data.service_start_end).trigger('change.select2');
                    $('select[name="active_status"]').val(data.active_status).trigger('change.select2');

                    if(data.user_id && !data.user_delete_at) {
                        $('#user_id_select').val(data.user_id).trigger('change.select2');
                        updateTeamInfo();
                    } else {
                        $('#user_id_select').val('').trigger('change.select2');
                        $('#team_id_hidden').val('');
                        $('#team_name_display').val('');
                    }

                    $('#closing_status').val(data.closing_status).trigger('change.select2');
                    if(data.fiscal_closing_date) {
                        $('#fiscal_closing_date').val(data.fiscal_closing_date);
                    }

                    $('#accounts_amount').val(data.f_accounts_amount || data.accounts_amount || 0);
                    $('#closing_amount').val(data.closing_amount || 0);
                    $('#auditing_amount').val(data.auditing_amount || 0);

                    $('select[name="is_vat"]').val(data.is_vat || 0).trigger('change.select2');
                    $('select[name="is_employees"]').val(data.is_employees || 0).trigger('change.select2');
                    $('select[name="is_social_security"]').val(data.is_social_security || 0).trigger('change.select2');

                    $('input[name="contact_tel"]').val(data.customer_phone);
                    $('input[name="contact_email"]').val(data.customer_email);
                    $('input[name="contact_line_id"]').val(data.line_id);
                    $('input[name="line_token"]').val(data.line_group_token);
                    $('input[name="doc_url"]').val(data.doc_folder_url);

                    $('#cpd_name').val(data.cpd_name || '');
                    $('#cpa_name').val(data.cpa_name || '');

                    // Clear and load accounts
                    document.getElementById('accountsList').innerHTML = '';
                    let hasDBD = false;
                    let hasRD = false;
                    if (data.accounts && data.accounts.length > 0) {
                        let addedDefaults = new Set();
                        data.accounts.forEach(acc => {
                            if (acc.account_name === 'กรมพัฒนาธุรกิจการค้า') {
                                if (addedDefaults.has('DBD')) return;
                                addedDefaults.add('DBD');
                                hasDBD = true;
                            }
                            if (acc.account_name === 'กรมสรรพากร') {
                                if (addedDefaults.has('RD')) return;
                                addedDefaults.add('RD');
                                hasRD = true;
                            }
                            let isDef = (acc.account_name === 'กรมพัฒนาธุรกิจการค้า' || acc.account_name === 'กรมสรรพากร');
                            addAccountRow(acc.account_name, acc.account_user_name, acc.account_password, isDef);
                        });
                    } else {
                        // Compatibility with old data if they don't have accounts but have old fields
                        if (data.rn_user || data.rn_password) {
                            addAccountRow('กรมพัฒนาธุรกิจการค้า', data.rn_user || '', data.rn_password || '', true);
                            hasDBD = true;
                        }
                    }

                    if (!hasDBD) addAccountRow('กรมพัฒนาธุรกิจการค้า', '', '', true);
                    if (!hasRD) addAccountRow('กรมสรรพากร', '', '', true);

                    // check monthly skip
                    if(data.monthly_skip && data.monthly_skip.length > 0) {
                        data.monthly_skip.forEach(function(taskId) {
                            $('input[name="monthly_skip[]"][value="'+taskId+'"]').prop('checked', true);
                        });
                    }

                    // 2. สั่งโชว์ Modal
                    const modalElement = document.getElementById('addCustomerModal');
                    const myModal = new bootstrap.Modal(modalElement);
                    myModal.show();
                } else {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถโหลดข้อมูลได้', 'error');
                }
            },
            error: function() {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการโหลดข้อมูลลูกค้า', 'error');
            }
        });
    }

    function deleteCustomer(customer_id) {
        Swal.fire({
            icon: 'warning',
            title: 'ลบข้อมูลลูกค้า?',
            text: 'ข้อมูลลูกค้านี้จะถูกลบออกจากระบบ',
            showCancelButton: true,
            confirmButtonColor: '#e3342f',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'ลบข้อมูล',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        }).then((result) => {
            if (result.isConfirmed) {
                processDeleteCustomer(customer_id);
            }
        });
    }

    function processDeleteCustomer(customer_id) {
        const fiscalId = $('input[name="fiscal_id"]').val() || '';
        $.ajax({
            url: '<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/customer/delete',
            method: 'POST',
            data: { 
                customer_id: customer_id,
                fiscal_id: fiscalId
            },
            dataType: 'json',
            success: function(response) {
                if (response.result === 1) {
                    Swal.fire('สำเร็จ!', response.msg, 'success').then(() => location.reload());
                } else {
                    Swal.fire('ผิดพลาด', response.msg || 'ไม่สามารถลบข้อมูลได้', 'error');
                }
            },
            error: function() {
                Swal.fire('ผิดพลาด', 'เกิดข้อผิดพลาดในการลบข้อมูลลูกค้า', 'error');
            }
        });
    }

    function updateAccountsEmptyState() {
        const list = document.getElementById('accountsList');
        const empty = document.getElementById('accountsEmptyState');
        if (list && empty) {
            empty.classList.toggle('show', list.children.length === 0);
        }
    }

    function addAccountRow(name = '', user = '', pass = '', isDefault = false) {
        const list = document.getElementById('accountsList');
        const card = document.createElement('div');
        card.className = 'gov-account-card';
        
        const nameAttr = isDefault ? 'readonly style="background-color: #f8f9fa;"' : '';
        const requiredAsterisk = isDefault ? '<span class="text-danger position-absolute" style="right: 12px; top: 50%; transform: translateY(-50%); z-index: 5; pointer-events: none;">*</span>' : '';
        const requiredAsteriskPwd = isDefault ? '<span class="text-danger position-absolute" style="right: 35px; top: 50%; transform: translateY(-50%); z-index: 5; pointer-events: none;">*</span>' : '';
        const removeBtn = isDefault ? '' : `
            <button type="button" class="gov-account-remove" onclick="this.closest('.gov-account-card').remove(); updateAccountsEmptyState();" title="ลบข้อมูล">
                <i class="ri-delete-bin-line"></i>
            </button>
        `;

        card.innerHTML = `
            <div class="gov-account-fields">
                <input type="text" class="form-control modal-form-control" name="account_name[]" value="${name}"
                   placeholder="เช่น กรมสรรพากร" ${nameAttr}
                   autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                   data-lpignore="true" data-1p-ignore data-form-type="other">
                <div class="position-relative">
                    <input type="text" class="form-control modal-form-control w-100" name="account_user_name[]" value="${user}"
                       placeholder="Username/ID"
                       autocomplete="off" autocorrect="off" autocapitalize="off" spellcheck="false"
                       data-lpignore="true" data-1p-ignore data-form-type="other" oninput="this.classList.remove('border', 'border-danger')">
                    ${requiredAsterisk}
                </div>
                <div class="modal-input-icon-wrap position-relative">
                    <input type="password" class="form-control modal-form-control modal-input-with-icon" name="account_password[]" value="${pass}"
                       placeholder="รหัสผ่าน"
                       autocomplete="new-password" autocorrect="off" autocapitalize="off" spellcheck="false"
                       data-lpignore="true" data-1p-ignore data-form-type="other" readonly onfocus="this.removeAttribute('readonly')" oninput="this.classList.remove('border', 'border-danger')">
                    <i class="ri-eye-off-line modal-input-icon modal-input-icon-clickable" onclick="togglePasswordVisibility(this)"></i>
                    ${requiredAsteriskPwd}
                </div>
            </div>
            ${removeBtn}
        `;
        list.appendChild(card);
        updateAccountsEmptyState();
    }
</script>
<?php
    // 3. นำ Footer เข้ามา
require_once dirname(__DIR__) . '/main/footer.php';
?>