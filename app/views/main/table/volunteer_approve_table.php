<?php
    $list = [];
    if (isset($_POST['data']) && is_array($_POST['data'])) {
    $list = $_POST['data'];
    } elseif (isset($data['volunteers']) && is_array($data['volunteers'])) {
    $list = $data['volunteers'];
    }

    $total    = (int) ($data['total'] ?? count($list));
    $page     = max(1, (int) ($_GET['page'] ?? $_POST['page'] ?? 1));
    $per_page = max(1, (int) ($_GET['per_page'] ?? $_POST['per_page'] ?? 10)); // เปลี่ยนค่าเริ่มต้นเป็น 10
?>

<div class="table-wrap">
    <table class="table align-middle">
        <thead>
            <tr>
                <th class="text-center" style="width: 5%;">ลำดับ</th>
                <th class="text-start" style="width: 20%;">ชื่อ - นามสกุล</th>
                <th class="text-start" style="width: 15%;">เบอร์ติดต่อ</th>
                <th class="text-start" style="width: 15%;">เลขบัตรประชาชน</th>
                <th class="text-start" style="width: 15%;">วันที่สมัคร</th>
                <th class="text-center" style="width: 10%;">หลักฐาน</th>
                <th class="text-center" style="width: 10%;">สถานะ</th>
                <th class="text-center" style="width: 10%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (! empty($list)): ?>
                <?php $i = 0; ?>
                <?php foreach ($list as $volunteer): ?>
                    <?php $row_num = ($page - 1) * $per_page + (++$i); ?>
                    <tr>
                        <td class="text-center">
                            <?php echo $row_num; ?>
                        </td>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?></div>
                        </td>
                        <td class="text-start">
                            <?php echo htmlspecialchars($volunteer['volunteer_phone'] ?? '-'); ?>
                        </td>
                        <td class="text-start">
                            <?php echo htmlspecialchars($volunteer['citizen_id'] ?? '-'); ?>
                        </td>
                        <td class="text-start text-muted">
                            <?php
                                if (! empty($volunteer['create_at'])) {
                                    echo date('d/m/Y H:i', strtotime($volunteer['create_at']));
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <?php if (! empty($volunteer['volunteer_image'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-primary" title="ดูรูปถ่าย" onclick="viewImage('<?php echo htmlspecialchars($volunteer['volunteer_image']); ?>')">
                                        <i class="ri-image-line"></i> รูปถ่าย
                                    </button>
                                <?php endif; ?>
                                <?php if (! empty($volunteer['citizen_image'])): ?>
                                    <button type="button" class="btn btn-sm btn-outline-info" title="ดูบัตรประชาชน" onclick="viewImage('<?php echo htmlspecialchars($volunteer['citizen_image']); ?>')">
                                        <i class="ri-profile-line"></i> บัตร ปชช.
                                    </button>
                                <?php endif; ?>
                            </div>
                        </td>
                        <td class="text-center">
                            <?php 
                                if ($volunteer['approve_status'] == '0') {
                                    echo '<span class="badge bg-warning text-white">รออนุมัติ</span>';
                                } elseif ($volunteer['approve_status'] == '1') {
                                    echo '<span class="badge bg-danger">ปฏิเสธ</span>';
                                } elseif ($volunteer['approve_status'] == '2') {
                                    echo '<span class="badge bg-success">อนุมัติแล้ว</span>';
                                } else {
                                    echo '<span class="badge bg-secondary">-</span>';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-primary" onclick='openApproveModal(<?php echo htmlspecialchars(json_encode($volunteer), ENT_QUOTES, 'UTF-8'); ?>)'>
                                    <?php echo ($volunteer['approve_status'] == '1') ? 'แก้ไข' : 'ดำเนินการ'; ?>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8" class="text-center text-muted py-4">ไม่มีข้อมูลอาสาสมัครที่รออนุมัติ</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (! empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>