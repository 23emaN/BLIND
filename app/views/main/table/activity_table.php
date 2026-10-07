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
                <th class="text-start" style="width: 28%;">ชื่อกิจกรรม</th>
                <th class="text-start" style="width: 16%;">หมวดหมู่</th>
                <th class="text-center" style="width: 16%;">วันที่</th>
                <th class="text-center" style="width: 12%;">เวลา</th>
                <th class="text-center" style="width: 12%;">ผู้เข้าร่วม</th>
                <th class="text-center" style="width: 16%;">จัดการ</th>
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
                            <div class="action-btn-group">
                                <button type="button" class="btn-action-edit" title="แก้ไข" onclick="editItem(<?php echo $aid; ?>)">
                                    <i class="ri-pencil-line"></i>
                                </button>
                                <button type="button" class="btn-action-delete" title="ลบ" onclick="deleteItem(<?php echo $aid; ?>, '<?php echo htmlspecialchars($a['activity_title'] ?? '', ENT_QUOTES); ?>')">
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
                    <td colspan="6" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (! empty($list)): ?>
<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
