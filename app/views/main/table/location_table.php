<?php
// app/views/main/table/location_table.php
$list = (isset($items) && is_array($items)) ? $items : ($data['items'] ?? []);
$total    = (int) ($total ?? count($list));
$page     = max(1, (int) ($page ?? 1));
$per_page = max(1, (int) ($per_page ?? 25));
?>
<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%;">ลำดับ</th>
                <th class="text-start" style="width: 28%;">ชื่อย่อ (บนปุ่ม)</th>
                <th class="text-start" style="width: 44%;">ชื่อเต็ม (เติมลงช่อง)</th>
                <th class="text-center" style="width: 20%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php $i = ($page - 1) * $per_page; ?>
                <?php foreach ($list as $row): ?>
                    <?php $id = (int) ($row['location_id'] ?? 0); ?>
                    <tr>
                        <td class="text-center text-muted"><?php echo ++$i; ?></td>
                        <td class="text-start"><div class="table-item-title"><?php echo htmlspecialchars($row['location_label'] ?? '-'); ?></div></td>
                        <td class="text-start text-muted"><?php echo htmlspecialchars($row['location_name'] ?? '-'); ?></td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-edit" title="แก้ไข" onclick="editItem(<?php echo $id; ?>)"><i class="ri-pencil-line"></i></button>
                                <button type="button" class="btn-action-delete" title="ลบ" onclick="deleteItem(<?php echo $id; ?>, '<?php echo htmlspecialchars($row['location_label'] ?? '', ENT_QUOTES); ?>')"><i class="ri-delete-bin-line"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="4" class="text-center text-muted py-4">ไม่พบข้อมูล</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($list)): ?><?php include dirname(__DIR__) . '/_pagination.php'; ?><?php endif; ?>
