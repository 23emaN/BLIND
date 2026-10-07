<?php
// app/controllers/ActivityController.php
// หน้าจัดการกิจกรรม (สำหรับเจ้าหน้าที่) — tbl_activity

class ActivityController
{
    private $authUser = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (!$user) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
        $this->authUser = $user;
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
        $this->authUser = $user;
        return $user;
    }

    private function model(): ActivityModel
    {
        require_once '../app/models/ActivityModel.php';
        return new ActivityModel();
    }

    public function index()
    {
        $user    = $this->checkAuth();
        $model   = $this->model();
        $perPage = 25;

        $data = [
            'title'      => 'ปฏิทินกิจกรรม',
            'firstname'  => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname'   => $user['user_lastname'] ?? '',
            'categories' => $model->getCategories(),
            'locations'  => $model->getLocations(),
            'timeslots'  => $model->getTimeslots(),
            'items'      => $model->getList('', '', '', 1, $perPage),
            'total'      => $model->countList('', '', ''),
            'page'       => 1,
            'per_page'   => $perPage,
        ];

        require_once '../app/views/main/activity.php';
    }

    public function filter()
    {
        $this->checkAuthJson();

        $keyword  = trim($_POST['keyword'] ?? '');
        $category = trim($_POST['category_id'] ?? '');
        $month    = trim($_POST['month'] ?? '');
        $page     = max(1, (int) ($_POST['page'] ?? 1));
        $perPage  = (int) ($_POST['per_page'] ?? 25);
        if (!in_array($perPage, [25, 50, 75, 100], true)) {
            $perPage = 25;
        }

        $model = $this->model();
        $total = $model->countList($keyword, $category, $month);
        $items = $model->getList($keyword, $category, $month, $page, $perPage);

        ob_start();
        include '../app/views/main/table/activity_table.php';
        $html = ob_get_clean();

        echo json_encode(['result' => 1, 'html' => $html]);
    }

    public function get()
    {
        $this->checkAuthJson();

        $id   = (int) ($_GET['id'] ?? 0);
        $item = $this->model()->getById($id);

        if (!$item) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลกิจกรรม']);
            return;
        }
        echo json_encode(['result' => 1, 'data' => $item]);
    }

    public function add()
    {
        $user   = $this->checkAuthJson();
        $input  = $this->sanitizeInput();
        $errors = $this->validate($input);
        if ($errors) {
            echo json_encode(['result' => 0, 'msg' => implode("\n", $errors)]);
            return;
        }

        try {
            $this->model()->create($input, (int) ($user['user_id'] ?? 0));
            echo json_encode(['result' => 1, 'msg' => 'สร้างกิจกรรมสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function edit()
    {
        $this->checkAuthJson();

        $id = (int) ($_POST['activity_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสกิจกรรม']);
            return;
        }

        $input  = $this->sanitizeInput();
        $errors = $this->validate($input);
        if ($errors) {
            echo json_encode(['result' => 0, 'msg' => implode("\n", $errors)]);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลกิจกรรม']);
            return;
        }

        try {
            $model->update($id, $input);
            echo json_encode(['result' => 1, 'msg' => 'แก้ไขกิจกรรมสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function delete()
    {
        $this->checkAuthJson();

        $id = (int) ($_POST['activity_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสกิจกรรม']);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลกิจกรรม']);
            return;
        }

        try {
            $model->softDelete($id);
            echo json_encode(['result' => 1, 'msg' => 'ลบกิจกรรมสำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        }
    }

    private function sanitizeInput(): array
    {
        return [
            'activity_title'  => trim($_POST['activity_title'] ?? ''),
            'category_id'     => (int) ($_POST['category_id'] ?? 0),
            'activity_date'   => trim($_POST['activity_date'] ?? ''),
            'start_time'      => trim($_POST['start_time'] ?? ''),
            'end_time'        => trim($_POST['end_time'] ?? ''),
            'location'        => trim($_POST['location'] ?? ''),
            'max_volunteers'  => (int) ($_POST['max_volunteers'] ?? 0),
            'reserve_count'   => max(0, (int) ($_POST['reserve_count'] ?? 0)),
            'activity_detail' => trim($_POST['activity_detail'] ?? ''),
            'grant_hours'        => ($_POST['grant_hours'] ?? '0') === '1' ? '1' : '0',
            'hours_per_person'   => trim($_POST['hours_per_person'] ?? ''),
            'hours_count_method' => ($_POST['hours_count_method'] ?? '1') === '2' ? '2' : '1',
        ];
    }

    // ชั่วโมงจิตอาสา: ไม่ให้ชั่วโมง → null, เว้นว่าง → คำนวณจากช่วงเวลา (ปัดลงทีละ 0.5)
    private function resolveHours(array &$in): void
    {
        if ($in['grant_hours'] !== '1') {
            $in['hours_per_person']   = null;
            $in['hours_count_method'] = '1';
            return;
        }
        if ($in['hours_per_person'] === '') {
            $minutes = (strtotime($in['end_time']) - strtotime($in['start_time'])) / 60;
            $in['hours_per_person'] = floor($minutes / 30) / 2;
        }
    }

    private function validate(array &$in): array
    {
        $errors = [];

        if ($in['activity_title'] === '') {
            $errors[] = 'กรุณากรอกชื่อกิจกรรม';
        } elseif (mb_strlen($in['activity_title']) > 100) {
            $errors[] = 'ชื่อกิจกรรมต้องไม่เกิน 100 ตัวอักษร';
        }

        if (!$this->model()->categoryExists($in['category_id'])) {
            $errors[] = 'กรุณาเลือกหมวดหมู่กิจกรรม';
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $in['activity_date'])) {
            $errors[] = 'กรุณาเลือกวันที่จัดกิจกรรม';
        }

        $timeRe = '/^\d{2}:\d{2}$/';
        if (!preg_match($timeRe, $in['start_time']) || !preg_match($timeRe, $in['end_time'])) {
            $errors[] = 'กรุณาระบุช่วงเวลาให้ถูกต้อง';
        } elseif ($in['end_time'] <= $in['start_time']) {
            $errors[] = 'เวลาสิ้นสุดต้องมากกว่าเวลาเริ่ม';
        }

        if ($in['location'] === '') {
            $errors[] = 'กรุณากรอกสถานที่จัดกิจกรรม';
        }

        if ($in['max_volunteers'] < 1 || $in['max_volunteers'] > 100) {
            $errors[] = 'จำนวนอาสาที่เปิดรับต้องอยู่ระหว่าง 1–100 คน';
        }

        if (mb_strlen($in['activity_detail']) > 500) {
            $errors[] = 'รายละเอียดต้องไม่เกิน 500 ตัวอักษร';
        }

        if (!$errors) {
            $this->resolveHours($in);
            if ($in['grant_hours'] === '1') {
                $h = $in['hours_per_person'];
                if (!is_numeric($h) || $h < 0.5 || $h > 24 || fmod((float) $h * 2, 1) != 0) {
                    $errors[] = 'จำนวนชั่วโมงต่อคนต้องอยู่ระหว่าง 0.5–24 (ทีละ 0.5 ชม.)';
                } else {
                    $in['hours_per_person'] = (float) $h;
                }
            }
        }

        return $errors;
    }

    public function getTable()
    {
        $this->checkAuthJson();
        require_once '../app/models/ActivityModel.php';
        $activityModel = new \App\models\ActivityModel();
        
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = max(1, (int)($_POST['per_page'] ?? 10)); 
        $search = $_POST['search'] ?? '';
        
        $activities = $activityModel->getActivities($page, $per_page, $search);
        $total = $activityModel->countActivities($search);

        $data = [
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search
        ];

        require_once '../app/views/main/table/activity_table.php';
    }

    public function getById()
    {
        $this->checkAuthJson();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
            return;
        }

        require_once '../app/models/ActivityModel.php';
        $activityModel = new \App\models\ActivityModel();
        
        $activity = $activityModel->getActivityById($id);
        
        if ($activity) {
            if (!empty($activity['activity_date'])) {
                $dateParts = explode('-', $activity['activity_date']);
                if (count($dateParts) == 3) {
                    $activity['activity_date'] = $dateParts[2] . '/' . $dateParts[1] . '/' . $dateParts[0];
                }
            }
            if (!empty($activity['activity_image'])) {
                $activity['activity_image'] = BASE_URL . '/' . ltrim($activity['activity_image'], '/');
            }
            echo json_encode(['status' => 'success', 'data' => $activity]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Activity not found']);
        }
    }

    public function update()
    {
        $user = $this->checkAuthJson();
        $id = $_POST['id'] ?? null;
        if (!$id) { echo 'error'; return; }

        $imagePath = '';
        if (isset($_FILES['activity_image']) && $_FILES['activity_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/upload_image/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['activity_image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['activity_image']['tmp_name'], $targetPath)) {
                $imagePath = 'upload_image/' . $fileName;
            }
        }
        
        $activity_date = $_POST['activity_date'] ?? '';
        if (!empty($activity_date)) {
            $dateParts = explode('/', $activity_date);
            if (count($dateParts) == 3) {
                $activity_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }
        
        try {
            require_once '../app/models/ActivityModel.php';
            $activityModel = new \App\models\ActivityModel();
            $activityModel->updateActivity(
                $id, $_POST['title'] ?? '', $_POST['attribute_id'] ?? '', $activity_date, 
                $_POST['start_time'] ?? '', $_POST['end_time'] ?? '', $_POST['location'] ?? '', 
                $_POST['max_volunteers'] ?? 1, $_POST['reserve_count'] ?? 0, 
                $_POST['activity_detail'] ?? '', $imagePath, $user['user_id']
            );
            echo 'success';
        } catch (\Exception $e) {
            echo 'error';
        }
    }

    // Override index so activity_setting.php works if called!
    public function index_old()
    {
        $this->checkAuth();
        require_once '../app/models/ActivityModel.php';
        $activityModel = new \App\models\ActivityModel();
        
        $categories = $activityModel->getCategories();
        
        $data = [
            'title' => 'BLIND - จัดการกิจกรรม',
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_name' => $this->userPayload['user_name'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'categories' => $categories
        ];
        
        require_once '../app/views/main/activity_setting.php';
    }
}
