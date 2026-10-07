<?php
// app/views/main/table/activity_table.php
// ต้องกำหนดก่อน include: $items, $total, $page, $per_page
$list = [];
if (isset($items) && is_array($items)) {
    $list = $items;
} elseif (isset($data['items']) && is_array($data['items'])) {
    $list = $data['items'];
}

$total    = (int) ($total ?? count($list));
$page     = max(1, (int) ($page ?? 1));
$per_page = max(1, (int) ($per_page ?? 25));

if (!function_exists('act_thai_date')) {
    function act_thai_date(?string $date): string
    {
        if (empty($date)) return '-';
        $ts = strtotime($date);
        if ($ts === false) return '-';
        $months = [1=>'ม.ค.',2=>'ก.พ.',3=>'มี.ค.',4=>'เม.ย.',5=>'พ.ค.',6=>'มิ.ย.',7=>'ก.ค.',8=>'ส.ค.',9=>'ก.ย.',10=>'ต.ค.',11=>'พ.ย.',12=>'ธ.ค.'];
        return (int) date('j', $ts) . ' ' . $months[(int) date('n', $ts)] . ' ' . ((int) date('Y', $ts) + 543);
    }
}
?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-start" style="width: 26%;">ชื่อกิจกรรม</th>
                <th class="text-start" style="width: 14%;">หมวดหมู่</th>
                <th class="text-center" style="width: 14%;">วันที่</th>
                <th class="text-center" style="width: 11%;">เวลา</th>
                <th class="text-center" style="width: 11%;">ผู้เข้าร่วม</th>
                <th class="text-center" style="width: 14%;">สถานะ</th>
                <th class="text-center" style="width: 10%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $a): ?>
                    <?php
                    $aid     = (int) ($a['activity_id'] ?? 0);
                    $joined  = (int) ($a['joined_count'] ?? 0);
                    $max     = (int) ($a['max_volunteers'] ?? 0);
                    $full    = $max > 0 && $joined >= $max;
                    ?>
                    <tr>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($a['activity_title'] ?? '-'); ?></div>
                            <div class="table-item-sub"><?php echo htmlspecialchars($a['location'] ?? ''); ?></div>
                        </td>
                        <td class="text-start">
                            <?php echo htmlspecialchars($a['category_name'] ?? '-'); ?>
                        </td>
                        <td class="text-center text-muted">
                            <?php echo act_thai_date($a['activity_date'] ?? null); ?>
                        </td>
                        <td class="text-center text-muted">
                            <?php echo htmlspecialchars(substr($a['start_time'] ?? '', 0, 5)); ?>–<?php echo htmlspecialchars(substr($a['end_time'] ?? '', 0, 5)); ?>
                        </td>
                        <td class="text-center">
                            <span class="<?php echo $full ? 'badge-inactive' : 'badge-active'; ?>">
                                <?php echo $joined; ?>/<?php echo $max; ?> คน
                            </span>
                        </td>
                        <td class="text-center">
                            <?php if ((string) ($a['active_status'] ?? '1') === '1'): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดการใช้งาน</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไขข้อมูล" aria-label="แก้ไขข้อมูล" onclick="editItem(<?php echo $aid; ?>)"><i class="ri-edit-line"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="7" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
