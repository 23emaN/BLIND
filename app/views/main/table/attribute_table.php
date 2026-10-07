<?php
// app/views/main/table/attribute_table.php
// ต้องกำหนดก่อน include: $items (array), $total, $page, $per_page, $item_label
$list = [];
if (isset($items) && is_array($items)) {
    $list = $items;
} elseif (isset($data['items']) && is_array($data['items'])) {
    $list = $data['items'];
}

$total      = (int) ($total ?? count($list));
$page       = max(1, (int) ($page ?? 1));
$per_page   = max(1, (int) ($per_page ?? 25));
$item_label = $item_label ?? ($data['item_label'] ?? 'รายการ');
$has_meta   = $has_meta ?? !empty($data['has_meta']);
$styles     = $styles ?? ($data['styles'] ?? []);
?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 8%;">ลำดับ</th>
                <th class="text-start" style="width: <?php echo $has_meta ? '22%' : '52%'; ?>;">ชื่อ<?php echo htmlspecialchars($item_label); ?></th>
                <?php if ($has_meta): ?>
                    <th class="text-center" style="width: 14%;">ไอคอน / สี</th>
                    <th class="text-start" style="width: 30%;">คำอธิบาย</th>
                <?php endif; ?>
                <th class="text-center" style="width: <?php echo $has_meta ? '12%' : '20%'; ?>;">สถานะ</th>
                <th class="text-center" style="width: <?php echo $has_meta ? '14%' : '20%'; ?>;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php $i = ($page - 1) * $per_page; ?>
                <?php foreach ($list as $item): ?>
                    <?php $aid = (int) ($item['attribute_id'] ?? 0); ?>
                    <tr>
                        <td class="text-center text-muted"><?php echo ++$i; ?></td>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($item['attribute_name'] ?? '-'); ?></div>
                        </td>
                        <?php if ($has_meta): ?>
                            <?php $st = $styles[$item['attribute_style'] ?? ''] ?? null; ?>
                            <td class="text-center">
                                <span class="cat-style-cell" <?php if ($st): ?>style="--cat-color: <?php echo htmlspecialchars($st['color'], ENT_QUOTES); ?>;" title="<?php echo htmlspecialchars($st['label'], ENT_QUOTES); ?>"<?php endif; ?>>
                                    <?php if (!empty($item['attribute_icon'])): ?>
                                        <span class="material-symbols-outlined" aria-hidden="true"><?php echo htmlspecialchars($item['attribute_icon']); ?></span>
                                    <?php endif; ?>
                                    <?php if ($st): ?>
                                        <span class="cat-mark cat-shape-<?php echo htmlspecialchars($st['shape'], ENT_QUOTES); ?>" aria-hidden="true"></span>
                                        <span class="visually-hidden"><?php echo htmlspecialchars($st['label']); ?></span>
                                    <?php elseif (empty($item['attribute_icon'])): ?>
                                        <span class="text-muted">-</span>
                                    <?php else: ?>
                                        <span class="text-danger small">ยังไม่เลือกสี</span>
                                    <?php endif; ?>
                                </span>
                            </td>
                            <td class="text-start text-muted">
                                <?php echo !empty($item['attribute_desc']) ? htmlspecialchars($item['attribute_desc']) : '-'; ?>
                            </td>
                        <?php endif; ?>
                        <td class="text-center">
                            <?php if ((string) ($item['active_status'] ?? '1') === '1'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดการใช้งาน</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไขข้อมูล" aria-label="แก้ไขข้อมูล" onclick="editItem(<?php echo $aid; ?>)"><i class="ri-edit-line"></i></button>
                                <?php $on = (string) ($item['active_status'] ?? '1') === '1'; ?>
                                <button type="button" class="btn btn-sm <?php echo $on ? 'btn-outline-danger' : 'btn-outline-success'; ?>"
                                    title="<?php echo $on ? 'ปิดการใช้งาน' : 'เปิดใช้งาน'; ?>" aria-label="<?php echo $on ? 'ปิดการใช้งาน' : 'เปิดใช้งาน'; ?>"
                                    data-id="<?php echo $aid; ?>" data-name="<?php echo htmlspecialchars($item['attribute_name'] ?? '', ENT_QUOTES); ?>" data-to="<?php echo $on ? '0' : '1'; ?>"
                                    onclick="toggleItem(this)">
                                    <i class="<?php echo $on ? 'ri-close-line' : 'ri-check-line'; ?>"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="<?php echo $has_meta ? '6' : '4'; ?>" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
