<?php
// app/views/main/table/user_table.php
// ต้องกำหนดก่อน include: $users (array), $total, $page, $per_page
$list = [];
if (isset($users) && is_array($users)) {
    $list = $users;
} elseif (isset($data['users']) && is_array($data['users'])) {
    $list = $data['users'];
}

$total    = (int) ($total ?? count($list));
$page     = max(1, (int) ($page ?? 1));
$per_page = max(1, (int) ($per_page ?? 25));
?>

<div class="table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th class="text-start" style="width: 28%;">ชื่อผู้ใช้</th>
                <th class="text-start" style="width: 28%;">ชื่อ - นามสกุล</th>
                <th class="text-center" style="width: 14%;">สิทธิ์</th>
                <th class="text-center" style="width: 14%;">สถานะ</th>
                <th class="text-center" style="width: 16%;">จัดการ</th>
            </tr>
        </thead>
        <tbody>
            <?php if (!empty($list)): ?>
                <?php foreach ($list as $u): ?>
                    <?php
                    $uid       = (int) ($u['user_id'] ?? 0);
                    $fullname  = trim(($u['user_firstname'] ?? '') . ' ' . ($u['user_lastname'] ?? ''));
                    $isSuper   = (int) ($u['is_super_admin'] ?? 0) === 1;
                    $isEnabled = (string) ($u['user_status'] ?? '1') === '1';
                    ?>
                    <tr>
                        <td class="text-start">
                            <div class="table-item-title"><?php echo htmlspecialchars($u['user_name'] ?? '-'); ?></div>
                        </td>
                        <td class="text-start">
                            <?php echo $fullname !== '' ? htmlspecialchars($fullname) : '<span class="text-muted">-</span>'; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isSuper): ?>
                                <span class="badge bg-primary">ผู้ดูแลระบบ</span>
                            <?php else: ?>
                                <span class="badge bg-secondary">ผู้ใช้ทั่วไป</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <?php if ($isEnabled): ?>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-check-line me-1"></i>ใช้งานอยู่</span>
                            <?php else: ?>
                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-2 rounded-pill"><i class="ri-close-line me-1"></i>ปิดการใช้งาน</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-center">
                            <div class="action-btn-group">
                                <button type="button" class="btn btn-sm btn-outline-warning" title="แก้ไขข้อมูล" aria-label="แก้ไขข้อมูล" onclick="editUser(<?php echo $uid; ?>)"><i class="ri-edit-line"></i></button>
                            </div>
                        </td>
                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="5" class="text-center text-muted py-4">ไม่พบข้อมูล</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if (!empty($list)): ?>
    <?php include dirname(__DIR__) . '/_pagination.php'; ?>
<?php endif; ?>
