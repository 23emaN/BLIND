<?php
// app/controllers/ActivityController.php

class ActivityController
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

    public function index()
    {
        $user = $this->checkAuth();

        require_once '../app/models/ActivityModel.php';
        $activityModel = new \App\models\ActivityModel();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = max(1, (int)($_GET['per_page'] ?? 10)); 
        $search = $_GET['search'] ?? ''; 
        
        $activities = $activityModel->getActivities($page, $per_page, $search);
        $total = $activityModel->countActivities($search);

        $categories = $activityModel->getCategories();
        $timeslots = $activityModel->getTimeslots();
        $locations = $activityModel->getLocations();

        $data = [
            'title' => 'ตั้งค่ากิจกรรม',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname' => $user['user_lastname'] ?? '',
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search,
            'categories' => $categories,
            'timeslots' => $timeslots,
            'locations' => $locations
        ];

        require_once '../app/views/main/activity_setting.php';
    }

    public function getTable()
    {
        $this->checkAuth();

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

    public function add()
    {
        $user = $this->checkAuth();
        
        $title = $_POST['title'] ?? '';
        $attribute_id = $_POST['attribute_id'] ?? '';
        $activity_date = $_POST['activity_date'] ?? '';
        $timeslot = $_POST['timeslot'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $location = $_POST['location'] ?? '';
        $max_volunteers = $_POST['max_volunteers'] ?? 1;
        $reserve_count = $_POST['reserve_count'] ?? 0;
        $activity_detail = $_POST['activity_detail'] ?? '';
        
        $imagePath = '';

        // จัดการอัปโหลดรูปภาพ
        if (isset($_FILES['activity_image']) && $_FILES['activity_image']['error'] === UPLOAD_ERR_OK) {
            // Path ด้านนอกสุดคือระดับเดียวกับโฟลเดอร์ public/app
            $uploadDir = dirname(__DIR__, 2) . '/upload_image/';
            
            // สร้างโฟลเดอร์ถ้ายังไม่มี
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['activity_image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['activity_image']['tmp_name'], $targetPath)) {
                // บันทึก Path ลง DB (อ้างอิงจากนอกสุด)
                $imagePath = 'upload_image/' . $fileName;
            }
        }

        // --- แปลงรูปแบบวันที่จาก DD/MM/YYYY เป็น YYYY-MM-DD เพื่อบันทึกลง DB ---
        if (!empty($activity_date)) {
            $dateParts = explode('/', $activity_date);
            if (count($dateParts) == 3) {
                $activity_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }

        // --- เพิ่มการเขียน Log File ---
        $logFile = dirname(__DIR__, 2) . '/activity_log.txt';
        $logData = date('Y-m-d H:i:s') . "\n";
        $logData .= "POST DATA:\n" . print_r($_POST, true) . "\n";
        $logData .= "FILES DATA:\n" . print_r($_FILES, true) . "\n";
        
        try {
            require_once '../app/models/ActivityModel.php';
            $activityModel = new \App\models\ActivityModel();
            
            // บันทึกลงฐานข้อมูล
            $activityModel->insertActivity(
                $title, 
                $attribute_id, 
                $activity_date, 
                $start_time, 
                $end_time, 
                $location, 
                $max_volunteers, 
                $reserve_count, 
                $activity_detail, 
                $imagePath, 
                $user['user_id']
            );
            
            $logData .= "Status: Data successfully inserted into tbl_activity.\n";
            file_put_contents($logFile, $logData . "------------------------\n", FILE_APPEND);
            echo "success";
            
        } catch (\Exception $e) {
            $logData .= "Error: " . $e->getMessage() . "\n";
            file_put_contents($logFile, $logData . "------------------------\n", FILE_APPEND);
            echo "error";
        }
    }

    public function getById()
    {
        $this->checkAuth();
        
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
            return;
        }

        require_once '../app/models/ActivityModel.php';
        $activityModel = new \App\models\ActivityModel();
        
        $activity = $activityModel->getActivityById($id);
        
        if ($activity) {
            // Convert date from YYYY-MM-DD to DD/MM/YYYY for the frontend
            if (!empty($activity['activity_date'])) {
                $dateParts = explode('-', $activity['activity_date']);
                if (count($dateParts) == 3) {
                    $activity['activity_date'] = $dateParts[2] . '/' . $dateParts[1] . '/' . $dateParts[0];
                }
            }
            
            // Generate full image URL if exists
            if (!empty($activity['activity_image'])) {
                // Ensure image path starts correctly or format it for frontend viewing
                $activity['activity_image'] = BASE_URL . '/' . ltrim($activity['activity_image'], '/');
            }

            echo json_encode(['status' => 'success', 'data' => $activity]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Activity not found']);
        }
    }

    public function update()
    {
        $user = $this->checkAuth();
        
        $id = $_POST['id'] ?? null;
        $title = $_POST['title'] ?? '';
        $attribute_id = $_POST['attribute_id'] ?? '';
        $activity_date = $_POST['activity_date'] ?? '';
        $timeslot = $_POST['timeslot'] ?? '';
        $start_time = $_POST['start_time'] ?? '';
        $end_time = $_POST['end_time'] ?? '';
        $location = $_POST['location'] ?? '';
        $max_volunteers = $_POST['max_volunteers'] ?? 1;
        $reserve_count = $_POST['reserve_count'] ?? 0;
        $activity_detail = $_POST['activity_detail'] ?? '';
        
        if (!$id) {
            echo "error";
            return;
        }

        $imagePath = '';

        if (isset($_FILES['activity_image']) && $_FILES['activity_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/upload_image/';
            
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true);
            }

            $fileName = time() . '_' . basename($_FILES['activity_image']['name']);
            $targetPath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES['activity_image']['tmp_name'], $targetPath)) {
                $imagePath = 'upload_image/' . $fileName;
            }
        }

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
                $id,
                $title, 
                $attribute_id, 
                $activity_date, 
                $start_time, 
                $end_time, 
                $location, 
                $max_volunteers, 
                $reserve_count, 
                $activity_detail, 
                $imagePath, 
                $user['user_id']
            );
            
            echo "success";
            
        } catch (\Exception $e) {
            echo "error";
        }
    }
}
