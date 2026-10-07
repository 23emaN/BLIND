<div class="table-responsive">
    <table class="table align-middle table-hover">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">ลำดับ</th>
                <th class="text-start" style="width: 60%;">ชื่อกิจกรรม</th>
                <th class="text-center" style="width: 15%;">สถานะ</th>
                <th class="text-center" style="width: 15%;"></th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $list = $data['activities'] ?? [];
                $page = $data['page'] ?? 1;
                $per_page = $data['per_page'] ?? 10;
                $total = $data['total'] ?? 0;
            ?>
            <?php if (! empty($list)): ?>
                <?php $i = 0; ?>
                <?php foreach ($list as $activity): ?>
                    <?php $row_num = ($page - 1) * $per_page + (++$i); ?>
                    <tr>
                        <td class="text-center text-muted">
                            <?php echo $row_num; ?>
                        </td>
                        <td class="text-start fw-medium">
                            <?php echo htmlspecialchars($activity['activity_title']); ?>
                        </td>
                        <td class="text-center">
                            <?php
                                if (! empty($activity['active_status'])) {
                                    if ($activity['active_status'] == '1') {
                                        echo '<span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>';
                                    } else {
                                        echo '<span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดใช้งาน</span>';
                                    }
                                } else {
                                    echo '<span class="badge bg-secondary">-</span>';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-2">
                                <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไข" onclick="GetModal_edit(<?php echo $activity['activity_id']; ?>)">
                                    <i class="ri-edit-line"></i> 
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-danger" title="ลบ" onclick="delete_activity(<?php echo $activity['activity_id']; ?>)">
                                    <i class="ri-delete-bin-line"></i> 
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-5">
                        <div class="d-flex flex-column align-items-center justify-content-center">
                            <i class="ri-file-list-3-line display-4 text-light mb-2"></i>
                            <p class="mb-0">ไม่มีข้อมูลกิจกรรม</p>
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
