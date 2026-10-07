<div class="table-responsive">
    <table class="table align-middle">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">ลำดับ</th>
                <th class="text-start" style="width: 50%;">ชื่อทักษะความถนัด</th>
                <th class="text-center" style="width: 20%;">วันที่สร้าง</th>
                <th class="text-center" style="width: 20%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php 
                $list = $data['skills'] ?? [];
                $page = $data['page'] ?? 1;
                $per_page = $data['per_page'] ?? 10;
                $total = $data['total'] ?? 0;
            ?>
            <?php if (! empty($list)): ?>
                <?php $i = 0; ?>
                <?php foreach ($list as $skill): ?>
                    <?php $row_num = ($page - 1) * $per_page + (++$i); ?>
                    <tr>
                        <td class="text-center">
                            <?php echo $row_num; ?>
                        </td>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($skill['attribute_name']); ?></div>
                        </td>
                        <td class="text-center text-muted">
                            <?php
                                if (! empty($skill['create_at'])) {
                                    echo date('d/m/Y H:i', strtotime($skill['create_at']));
                                } else {
                                    echo '-';
                                }
                            ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-primary">
                                    <i class="ri-edit-line"></i> แก้ไข
                                </button>
                                <button type="button" class="btn btn-sm btn-danger">
                                    <i class="ri-delete-bin-line"></i> ลบ
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="4" class="text-center text-muted py-4">ไม่มีข้อมูลทักษะความถนัด</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (! empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
