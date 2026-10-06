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
?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-center" style="width: 10%;">ลำดับ</th>
                <th class="text-start" style="width: 60%;">ชื่อ<?php echo htmlspecialchars($item_label); ?></th>
                <th class="text-center" style="width: 30%;">จัดการ</th>
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
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-edit" title="แก้ไข" onclick="editItem(<?php echo $aid; ?>)">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" class="btn-action-delete" title="ลบ" onclick="deleteItem(<?php echo $aid; ?>, '<?php echo htmlspecialchars($item['attribute_name'] ?? '', ENT_QUOTES); ?>')">
                                    <i class="ri-delete-bin-line"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
