<?php
    require_once dirname(__DIR__) . '/main/header.php';
    require_once dirname(__DIR__) . '/main/sidebar.php';

    $baseUrl    = defined('BASE_URL') ? BASE_URL : '';
    $type       = $data['type'] ?? '2';
    $itemLabel  = $data['item_label'] ?? 'รายการ';
    // base path ของ endpoint (ส่งมาจาก controller: skill / interest)
    $route      = $data['route'] ?? 'skill';
    $hasMeta    = !empty($data['has_meta']);   // หมวดหมู่กิจกรรม = จัดการ icon + คำอธิบาย
?>
<style>
    /* ===== Image upload (ภาพปกกิจกรรม) ===== */
    .image-upload-wrapper {
        position: relative;
        width: 100%;
        aspect-ratio: 16 / 9;
        max-height: 260px;
        border: 2px dashed #cbd5e1;
        border-radius: 12px;
        background-color: #f8fafc;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        cursor: pointer;
        transition: border-color .2s ease, background-color .2s ease;
    }

    .image-upload-wrapper:hover,
    .image-upload-wrapper.is-dragover {
        border-color: #4f46e5;
        background-color: #eef2ff;
    }

    .image-upload-wrapper.is-invalid {
        border-color: #dc3545;
    }

    .image-upload-placeholder {
        text-align: center;
        color: #64748b;
        pointer-events: none;
    }

    .image-upload-placeholder i {
        font-size: 2.5rem;
        color: #94a3b8;
        display: block;
        line-height: 1.2;
    }

    .image-upload-placeholder .main-text {
        font-weight: 500;
    }

    .image-upload-placeholder small {
        display: block;
        color: #94a3b8;
    }

    #f_image_preview {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        object-fit: contain;
        z-index: 1;
        display: none;
    }

    .upload-overlay {
        position: absolute;
        bottom: 0;
        width: 100%;
        z-index: 2;
        padding: 8px 0;
        text-align: center;
        font-size: .85rem;
        background: rgba(255, 255, 255, .9);
        border-top: 1px solid #e2e8f0;
        display: none;
    }
</style>
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
                    <div class="mb-4">
                        <label class="form-label" for="f_image">ภาพปกกิจกรรม <span class="text-danger">*</span></label>
                        <div class="image-upload-wrapper" id="f_image_wrapper" onclick="document.getElementById('f_image').click()">
                            <div id="f_image_placeholder" class="image-upload-placeholder">
                                <i class="ri-image-add-line"></i>
                                <span class="main-text">คลิกเพื่อเลือกรูป หรือลากไฟล์มาวางที่นี่</span>
                                <small>แนะนำสัดส่วน 16:9 · ไฟล์ JPG, PNG, WebP · ไม่เกิน 2MB</small>
                            </div>
                            <img id="f_image_preview" src="" alt="ตัวอย่างรูปกิจกรรม">
                            <div class="upload-overlay text-muted" id="f_image_overlay">
                                <i class="ri-image-edit-line"></i> คลิกเพื่อเปลี่ยนรูป
                            </div>
                        </div>
                        <input type="file" class="d-none" name="attribute_image" id="f_image" accept="image/*" onchange="previewImage(this)">
                    </div>
                    <div class="mb-<?php echo $hasMeta ? '3' : '2'; ?>">
                        <label class="form-label" for="f_name">ชื่อ<?php echo htmlspecialchars($itemLabel); ?> <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" name="attribute_name" id="f_name" maxlength="255" autocomplete="off">
                    </div>
                    <?php if ($hasMeta): ?>
                        <div class="mb-3">
                            <label class="form-label" id="iconPickerLabel">ไอคอน</label>
                            <input type="hidden" name="attribute_icon" id="f_icon" value="">
                            <div class="icon-picker-selected">
                                <span class="material-symbols-outlined" id="iconPreview" aria-hidden="true">block</span>
                                <span id="iconPreviewText" class="text-muted">ไม่ใช้ไอคอน</span>
                            </div>
                            <div class="icon-picker-grid" role="group" aria-labelledby="iconPickerLabel">
                                <button type="button" class="icon-picker-item" data-icon="" title="ไม่ใช้ไอคอน" aria-label="ไม่ใช้ไอคอน" onclick="selectIcon(this.dataset.icon)">
                                    <span class="material-symbols-outlined" aria-hidden="true">block</span>
                                </button>
                                <?php foreach ($data['icons'] ?? [] as $iconName => $iconLabel): ?>
                                    <button type="button" class="icon-picker-item" data-icon="<?php echo htmlspecialchars($iconName, ENT_QUOTES); ?>"
                                        data-label="<?php echo htmlspecialchars($iconLabel, ENT_QUOTES); ?>"
                                        title="<?php echo htmlspecialchars($iconLabel, ENT_QUOTES); ?>" aria-label="<?php echo htmlspecialchars($iconLabel, ENT_QUOTES); ?>"
                                        onclick="selectIcon(this.dataset.icon)">
                                        <span class="material-symbols-outlined" aria-hidden="true"><?php echo htmlspecialchars($iconName); ?></span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">ไอคอนที่หน้าบ้านแสดงบนการ์ดหมวดหมู่ — ชี้เมาส์ค้างเพื่อดูความหมาย</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" id="stylePickerLabel">สีและรูปทรง <span class="text-danger">*</span></label>
                            <input type="hidden" name="attribute_style" id="f_style" value="">
                            <div class="style-picker" role="group" aria-labelledby="stylePickerLabel">
                                <?php foreach ($data['styles'] ?? [] as $styleKey => $st): ?>
                                    <button type="button" class="style-picker-item" data-style="<?php echo htmlspecialchars($styleKey, ENT_QUOTES); ?>"
                                        style="--cat-color: <?php echo htmlspecialchars($st['color'], ENT_QUOTES); ?>;"
                                        aria-pressed="false" onclick="selectStyle(this.dataset.style)">
                                        <span class="cat-mark cat-shape-<?php echo htmlspecialchars($st['shape'], ENT_QUOTES); ?>" aria-hidden="true"></span>
                                        <span class="style-picker-text">
                                            <span><?php echo htmlspecialchars($st['label']); ?></span>
                                            <small class="style-picker-used" data-used-for="<?php echo htmlspecialchars($styleKey, ENT_QUOTES); ?>"></small>
                                        </span>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                            <small class="text-muted">สีคู่กับรูปทรงเสมอ เพื่อให้คนตาบอดสีแยกหมวดได้ (WCAG 1.4.1) — ควรให้แต่ละหมวดใช้ชุดไม่ซ้ำกัน</small>
                        </div>
                        <div class="mb-2">
                            <label class="form-label" for="f_desc">คำอธิบายสั้น</label>
                            <textarea class="form-control" name="attribute_desc" id="f_desc" rows="2" maxlength="255" placeholder="เช่น บันทึกเสียงบทเรียน หนังสือเสียง"></textarea>
                        </div>
                    <?php endif; ?>
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
                    if (response.style_usage) STYLE_USAGE = response.style_usage;
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

    // ตัวเลือกไอคอน: เก็บชื่อลง hidden input + ไฮไลต์ปุ่ม + แสดงตัวอย่าง
    function selectIcon(name) {
        if (!$('#f_icon').length) return;
        name = name || '';
        const btn = $('.icon-picker-item').filter(function () { return this.dataset.icon === name; });
        $('.icon-picker-item').removeClass('active').attr('aria-pressed', 'false');
        $('#f_icon').val(name);
        if (name !== '' && !btn.length) {
            // ไอคอนเดิมที่ไม่อยู่ในรายการ (ข้อมูลเก่า) — บันทึกไม่ผ่านจนกว่าจะเลือกใหม่
            $('#iconPreview').text(name);
            $('#iconPreviewText').text('ไอคอนเดิม "' + name + '" ไม่อยู่ในรายการ กรุณาเลือกใหม่').attr('class', 'text-danger');
            return;
        }
        btn.addClass('active').attr('aria-pressed', 'true');
        $('#iconPreview').text(name || 'block');
        $('#iconPreviewText').text(name ? btn.data('label') : 'ไม่ใช้ไอคอน').attr('class', name ? '' : 'text-muted');
    }

    // ชุดสี+รูปทรง: {style: {attribute_id: ชื่อ}} — อัปเดตทุกครั้งที่โหลดตาราง
    let STYLE_USAGE = <?php echo json_encode((object) ($data['style_usage'] ?? []), JSON_UNESCAPED_UNICODE); ?>;

    function selectStyle(key) {
        if (!$('#f_style').length) return;
        key = key || '';
        $('#f_style').val(key);
        $('.style-picker-item').each(function () {
            const on = this.dataset.style === key;
            $(this).toggleClass('active', on).attr('aria-pressed', on ? 'true' : 'false');
        });
        // ตัวอย่างไอคอนใช้สีของชุดที่เลือก
        const el = key ? document.querySelector('.style-picker-item[data-style="' + key + '"]') : null;
        const color = el ? getComputedStyle(el).getPropertyValue('--cat-color').trim() : '';
        $('#iconPreview').css('color', color || '');
    }

    // บอกว่าชุดไหนมีหมวดอื่นใช้อยู่แล้ว (ไม่นับหมวดที่กำลังแก้ไข)
    function renderStyleUsage() {
        const selfId = String($('#attribute_id').val() || '');
        $('.style-picker-used').each(function () {
            const used = STYLE_USAGE[this.dataset.usedFor] || {};
            const names = Object.keys(used).filter(id => id !== selfId).map(id => used[id]);
            $(this).text(names.length ? 'ใช้แล้ว: ' + names.join(', ') : '');
        });
    }

    function openAddItem() {
        document.getElementById('itemForm').reset();
        selectIcon('');
        selectStyle('');
        $('#attribute_id').val('');
        renderStyleUsage();
        $('#f_image').val('');
        $('#f_image_preview').attr('src', '').hide();
        $('#f_image_placeholder').show();
        $('#f_image_overlay').hide();
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
                <?php if ($hasMeta): ?>
                selectIcon(response.data.attribute_icon || '');
                selectStyle(response.data.attribute_style || '');
                renderStyleUsage();
                $('#f_desc').val(response.data.attribute_desc || '');
                <?php endif; ?>

                $('#f_image').val('');
                if (response.data.attribute_image) {
                    $('#f_image_preview').attr('src', ITEM_BASE_URL.replace('/public', '') + '/upload_image/' + response.data.attribute_image).show();
                    $('#f_image_placeholder').hide();
                    $('#f_image_overlay').show();
                } else {
                    $('#f_image_preview').attr('src', '').hide();
                    $('#f_image_placeholder').show();
                    $('#f_image_overlay').hide();
                }

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

        const formElement = document.getElementById('itemForm');
        const formData = new FormData(formElement);

        $.ajax({
            url: url,
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
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
     /*
     * แทนที่ฟังก์ชัน previewImage() เดิมของคุณด้วยชุดนี้
     * (ถ้ามี previewImage อยู่ในไฟล์ JS อื่น ให้ลบอันเก่าออก ไม่งั้นจะชนกัน)
     */
    const IMAGE_MAX_BYTES = 2 * 1024 * 1024; // 2MB
 
    function setImagePreview(src) {
        const preview = document.getElementById('f_image_preview');
        const placeholder = document.getElementById('f_image_placeholder');
        const overlay = document.getElementById('f_image_overlay');
        const wrapper = document.getElementById('f_image_wrapper');
 
        wrapper.classList.remove('is-invalid');
        if (src) {
            preview.src = src;
            preview.style.display = 'block';
            placeholder.style.display = 'none';
            overlay.style.display = 'block';
        } else {
            preview.removeAttribute('src');
            preview.style.display = 'none';
            placeholder.style.display = '';
            overlay.style.display = 'none';
        }
    }
 
    function previewImage(input) {
        const file = input.files && input.files[0];
        if (!file) {
            setImagePreview('');
            return;
        }
        if (!file.type.startsWith('image/')) {
            input.value = '';
            setImagePreview('');
            alert('กรุณาเลือกไฟล์รูปภาพเท่านั้น');
            return;
        }
        if (file.size > IMAGE_MAX_BYTES) {
            input.value = '';
            setImagePreview('');
            alert('ไฟล์ใหญ่เกินไป กรุณาเลือกรูปที่ไม่เกิน 2MB');
            return;
        }
        const reader = new FileReader();
        reader.onload = (e) => setImagePreview(e.target.result);
        reader.readAsDataURL(file);
    }
 
    // Drag & drop
    (function () {
        const wrapper = document.getElementById('f_image_wrapper');
        const input = document.getElementById('f_image');
 
        ['dragenter', 'dragover'].forEach((evt) =>
            wrapper.addEventListener(evt, (e) => {
                e.preventDefault();
                wrapper.classList.add('is-dragover');
            })
        );
        ['dragleave', 'drop'].forEach((evt) =>
            wrapper.addEventListener(evt, (e) => {
                e.preventDefault();
                wrapper.classList.remove('is-dragover');
            })
        );
        wrapper.addEventListener('drop', (e) => {
            if (e.dataTransfer.files.length) {
                input.files = e.dataTransfer.files;
                previewImage(input);
            }
        });
    })();
</script>

<?php
    if (file_exists(dirname(__DIR__) . '/main/footer.php')) {
        require_once dirname(__DIR__) . '/main/footer.php';
    }
?>
