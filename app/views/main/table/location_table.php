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
                <th class="text-start" style="width: 26%;">ชื่อย่อ (บนปุ่ม)</th>
                <th class="text-start" style="width: 38%;">ชื่อเต็ม (เติมลงช่อง)</th>
                <th class="text-center" style="width: 14%;">สถานะ</th>
                <th class="text-center" style="width: 14%;">จัดการ</th>
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
                            <?php if ((string) ($row['active_status'] ?? '1') === '1'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดการใช้งาน</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไขข้อมูล" aria-label="แก้ไขข้อมูล" onclick="editItem(<?php echo $id; ?>)"><i class="ri-edit-line"></i></button>
                                <?php $on = (string) ($row['active_status'] ?? '1') === '1'; ?>
                                <button type="button" class="btn btn-sm <?php echo $on ? 'btn-outline-danger' : 'btn-outline-success'; ?>"
                                    title="<?php echo $on ? 'ปิดการใช้งาน' : 'เปิดใช้งาน'; ?>" aria-label="<?php echo $on ? 'ปิดการใช้งาน' : 'เปิดใช้งาน'; ?>"
                                    data-id="<?php echo $id; ?>" data-name="<?php echo htmlspecialchars($row['location_label'] ?? '', ENT_QUOTES); ?>" data-to="<?php echo $on ? '0' : '1'; ?>"
                                    onclick="toggleItem(this)">
                                    <i class="<?php echo $on ? 'ri-close-line' : 'ri-checkbox-circle-line'; ?>"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr><td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูล</td></tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>
<?php if (!empty($list)): ?><?php include dirname(__DIR__) . '/_pagination.php'; ?><?php endif; ?>
