<?php
// app/controllers/TimeslotController.php
// หน้าตั้งค่าช่วงเวลาที่ใช้บ่อย (tbl_activity_timeslot)

class TimeslotController
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
            'title'     => 'ตั้งค่าช่วงเวลาที่ใช้บ่อย',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname'  => $user['user_lastname'] ?? '',
            'items'     => $model->getTimeslots('', 1, $perPage),
            'total'     => $model->countTimeslots(''),
            'page'      => 1,
            'per_page'  => $perPage,
        ];
        require_once '../app/views/main/timeslot.php';
    }

    public function filter()
    {
        $this->checkAuthJson();
        $keyword = trim($_POST['keyword'] ?? '');
        $page    = max(1, (int) ($_POST['page'] ?? 1));
        $perPage = (int) ($_POST['per_page'] ?? 25);
        if (!in_array($perPage, [25, 50, 75, 100], true)) $perPage = 25;

        $model = $this->model();
        $total = $model->countTimeslots($keyword);
        $items = $model->getTimeslots($keyword, $page, $perPage);

        ob_start();
        include '../app/views/main/table/timeslot_table.php';
        $html = ob_get_clean();
        echo json_encode(['result' => 1, 'html' => $html]);
    }

    public function get()
    {
        $this->checkAuthJson();
        $item = $this->model()->getTimeslot((int) ($_GET['id'] ?? 0));
        if (!$item) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }
        echo json_encode(['result' => 1, 'data' => $item]);
    }

    public function add()
    {
        $this->checkAuthJson();
        $in  = $this->input();
        $err = $this->validate($in);
        if ($err) { echo json_encode(['result' => 0, 'msg' => implode("\n", $err)]); return; }
        try {
            $this->model()->createTimeslot($in['name'], $in['start'], $in['end']);
            echo json_encode(['result' => 1, 'msg' => 'เพิ่มช่วงเวลาสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function edit()
    {
        $this->checkAuthJson();
        $id = (int) ($_POST['timeslot_id'] ?? 0);
        if ($id <= 0) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสรายการ']); return; }
        $in  = $this->input();
        $err = $this->validate($in);
        if ($err) { echo json_encode(['result' => 0, 'msg' => implode("\n", $err)]); return; }

        $model = $this->model();
        if (!$model->getTimeslot($id)) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }
        try {
            $model->updateTimeslot($id, $in['name'], $in['start'], $in['end']);
            echo json_encode(['result' => 1, 'msg' => 'แก้ไขช่วงเวลาสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function delete()
    {
        $this->checkAuthJson();
        $id = (int) ($_POST['timeslot_id'] ?? 0);
        if ($id <= 0) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสรายการ']); return; }
        $model = $this->model();
        if (!$model->getTimeslot($id)) { echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']); return; }
        try {
            $model->deleteTimeslot($id);
            echo json_encode(['result' => 1, 'msg' => 'ลบช่วงเวลาสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        }
    }

    private function input(): array
    {
        return [
            'name'  => trim($_POST['timeslot_name'] ?? ''),
            'start' => trim($_POST['start_time'] ?? ''),
            'end'   => trim($_POST['end_time'] ?? ''),
        ];
    }

    private function validate(array $in): array
    {
        $err = [];
        if ($in['name'] === '') $err[] = 'กรุณากรอกชื่อช่วงเวลา';
        elseif (mb_strlen($in['name']) > 50) $err[] = 'ชื่อช่วงเวลาต้องไม่เกิน 50 ตัวอักษร';
        $re = '/^\d{2}:\d{2}$/';
        if (!preg_match($re, $in['start']) || !preg_match($re, $in['end'])) {
            $err[] = 'กรุณาระบุเวลาให้ถูกต้อง';
        } elseif ($in['end'] <= $in['start']) {
            $err[] = 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม';
        }
        return $err;
    }
}
