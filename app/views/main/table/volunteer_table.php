<div class="table-responsive">
    <table class="table align-middle table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">ลำดับ</th>
                <th class="text-start" style="width: 40%;">ชื่อ - นามสกุล</th>
                <th class="text-start" style="width: 10%;">เบอร์ติดต่อ</th>
                <th class="text-start" style="width: 20%;">เลขบัตรประชาชน</th>
                <th class="text-center" style="width: 10%;">สถานะ</th>
                <th class="text-center" style="width: 10%;"></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $list = $data['volunteers'] ?? [];
                $page = $data['page'] ?? 1;
                $per_page = $data['per_page'] ?? 10;
                $total = $data['total'] ?? 0;
            ?>
            <?php if (! empty($list)): ?>
                <?php $i = 0; ?>
                <?php foreach ($list as $volunteer): ?>
                    <?php $row_num = ($page - 1) * $per_page + (++$i); ?>
                    <tr>
                        <td class="text-center text-muted">
                            <?php echo $row_num; ?>
                        </td>
                        <td class="text-start fw-medium">
                            <?php echo htmlspecialchars($volunteer['first_name'] . ' ' . $volunteer['last_name']); ?>
                        </td>
                        <td class="text-start">
                            <?php echo htmlspecialchars($volunteer['volunteer_phone'] ?? '-'); ?>
                        </td>
                        <td class="text-start">
                            <?php echo htmlspecialchars($volunteer['citizen_id'] ?? '-'); ?>
                        </td>
                        <td class="text-center">
                            <?php
                                if (isset($volunteer['active_status'])) {
                                    if ($volunteer['active_status'] == '1') {
                                        echo '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>';
                                    } else {
                                        echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดการใช้งาน</span>';
                                    }
                                } else {
                                    echo '<span class="badge bg-secondary">-</span>';
                                }
                            ?>
                        </td>
                        <td class="text-center"> 
                            <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไขข้อมูล" onclick='GetModal_edit(<?php echo htmlspecialchars(json_encode($volunteer), ENT_QUOTES, 'UTF-8'); ?>)'>
                                <i class="ri-edit-line"></i> 
                            </button>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="6" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="ri-user-search-line display-4 text-light mb-2"></i>
                            <p class="mb-0">ไม่มีข้อมูลอาสาสมัคร</p>
                        </div>
                    </td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (! empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
