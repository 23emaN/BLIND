<?php
// app/controllers/LocationController.php
// หน้าตั้งค่าสถานที่ที่ใช้บ่อย (tbl_activity_location)

class LocationController
{
    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (!$user) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
        return $user;
    }

    private function checkAuthJson()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (!$user) {
            echo json_encode(['result' => 0, 'msg' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่']);
            exit();
        }
        return $user;
    }

    private function model(): ActivityPresetModel
    {
        require_once '../app/models/ActivityPresetModel.php';
        return new ActivityPresetModel();
    }

    public function index()
    {
        $user    = $this->checkAuth();
        $model   = $this->model();
        $perPage = 25;

        $data = [
            'title'     => 'ตั้งค่าสถานที่ที่ใช้บ่อย',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname'  => $user['user_lastname'] ?? '',
            'items'     => $model->getLocations('', 1, $perPage),
            'total'     => $model->countLocations(''),
            'page'      => 1,
            'per_page'  => $perPage,
        ];
        require_once '../app/views/main/location.php';
    }

    public function filter()
    {
        $this->checkAuthJson();
        $keyword = trim($_POST['keyword'] ?? '');
        $status  = trim($_POST['status'] ?? '');
        $page    = max(1, (int) ($_POST['page'] ?? 1));
        $perPage = (int) ($_POST['per_page'] ?? 25);
        if (!in_array($perPage, [25, 50, 75, 100], true)) $perPage = 25;

        $model = $this->model();
        $total = $model->countLocations($keyword, $status);
        $items = $model->getLocations($keyword, $page, $perPage, $status);

        ob_start();
        include '../app/views/main/table/location_table.php';
        $html = ob_get_clean();
        echo json_encode(['result' => 1, 'html' => $html]);
    }

    public function get()
    {
        $this->checkAuthJson();
        $item = $this->model()->getLocation((int) ($_GET['id'] ?? 0));
        if (!$item) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }
        echo json_encode(['result' => 1, 'data' => $item]);
    }

    public function add()
    {
        $this->checkAuthJson();
        $label = trim($_POST['location_label'] ?? '');
        $name  = trim($_POST['location_name'] ?? '');
        $err   = $this->validate($label, $name);
        if ($err) { echo json_encode(['result' => 0, 'msg' => implode("\n", $err)]); return; }

        try {
            $this->model()->createLocation($label, $name);
            echo json_encode(['result' => 1, 'msg' => 'เพิ่มสถานที่สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function edit()
    {
        $this->checkAuthJson();
        $id    = (int) ($_POST['location_id'] ?? 0);
        $label = trim($_POST['location_label'] ?? '');
        $name  = trim($_POST['location_name'] ?? '');
        if ($id <= 0) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสรายการ']); return; }
        $err = $this->validate($label, $name);
        if ($err) { echo json_encode(['result' => 0, 'msg' => implode("\n", $err)]); return; }

        $model = $this->model();
        if (!$model->getLocation($id)) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }

        try {
            $model->updateLocation($id, $label, $name);
            echo json_encode(['result' => 1, 'msg' => 'แก้ไขสถานที่สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    private function validate(string $label, string $name): array
    {
        $err = [];
        if ($label === '') $err[] = 'กรุณากรอกชื่อย่อ';
        elseif (mb_strlen($label) > 100) $err[] = 'ชื่อย่อต้องไม่เกิน 100 ตัวอักษร';
        if ($name === '') $err[] = 'กรุณากรอกชื่อเต็ม';
        elseif (mb_strlen($name) > 255) $err[] = 'ชื่อเต็มต้องไม่เกิน 255 ตัวอักษร';
        return $err;
    }

    // AJAX: เปิด/ปิดการใช้งาน (แทนการลบ)
    public function toggle()
    {
        $this->checkAuthJson();
        $id     = (int) ($_POST['location_id'] ?? 0);
        $status = ($_POST['active_status'] ?? '') === '1' ? '1' : '0';
        $model  = $this->model();
        if ($id <= 0 || !$model->getLocation($id)) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }
        try {
            $model->setLocationStatus($id, $status);
            echo json_encode(['result' => 1, 'msg' => ($status === '1' ? 'เปิดใช้งาน' : 'ปิดการใช้งาน') . 'สถานที่แล้ว']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }
}
