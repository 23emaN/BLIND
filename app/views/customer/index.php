<?php 
// app/views/customer/index.php
require_once __DIR__ . '/../main/header.php'; 
?>

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0 rounded-lg">
                <div class="card-header bg-primary text-white">
                    <h3 class="mb-0">ตั้งค่าลูกค้า</h3>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-3">
                        <h5>รายชื่อลูกค้าทั้งหมด</h5>
                        <button class="btn btn-success"><i class="ri-add-line"></i> เพิ่มลูกค้าใหม่</button>
                    </div>
                    
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>รหัส</th>
                                    <th>ชื่อลูกค้า</th>
                                    <th>อีเมล</th>
                                    <th>เบอร์โทรศัพท์</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center text-muted">ยังไม่มีข้อมูลลูกค้า (ส่วนนี้กำลังอยู่ในขั้นตอนการพัฒนา)</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <a class="btn btn-secondary mt-3" href="<?php echo defined('BASE_URL') ? BASE_URL : '/cpd_ac/public'; ?>/main" role="button">กลับหน้าหลัก</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php 
// require_once __DIR__ . '/../main/footer.php'; 
?>
