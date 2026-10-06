<?php
// app/controllers/VolunteerController.php

class VolunteerController
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

        require_once '../app/models/VolunteerModel.php';
        $volunteerModel = new \App\models\VolunteerModel();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = max(1, (int)($_GET['per_page'] ?? 10)); // เปลี่ยนค่าเริ่มต้นเป็น 10 ตอนโหลดครั้งแรก
        $status = $_GET['status'] ?? '0'; // ค่าเริ่มต้นสถานะ 0 (ยังไม่ได้อนุมัติ)
        $search = $_GET['search'] ?? ''; 
        
        $volunteers = $volunteerModel->getVolunteersByStatus($status, $page, $per_page, $search);
        $total = $volunteerModel->countVolunteersByStatus($status, $search);

        $data = [
            'title' => 'ยืนยันตัวตนอาสาสมัคร',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname' => $user['user_lastname'] ?? '',
            'volunteers' => $volunteers,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'status' => $status,
            'search' => $search
        ];

        require_once '../app/views/main/volunteer_approve.php';
    }

    public function getTable()
    {
        $this->checkAuth();

        require_once '../app/models/VolunteerModel.php';
        $volunteerModel = new \App\models\VolunteerModel();
        
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = max(1, (int)($_POST['per_page'] ?? 10)); // เปลี่ยนค่าเริ่มต้นเป็น 10
        $status = $_POST['status'] ?? '0';
        $search = $_POST['search'] ?? '';
        
        $volunteers = $volunteerModel->getVolunteersByStatus($status, $page, $per_page, $search);
        $total = $volunteerModel->countVolunteersByStatus($status, $search);

        $data = [
            'volunteers' => $volunteers,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'status' => $status,
            'search' => $search
        ];

        require_once '../app/views/main/table/volunteer_approve_table.php';
    }

    public function approve()
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        
        if ($id > 0) {
            require_once '../app/models/VolunteerModel.php';
            $volunteerModel = new \App\models\VolunteerModel();
            $volunteerModel->approveVolunteer($id);
        }

        // โหลดตารางใหม่กลับไปอัปเดตหน้าจอ
        $this->getTable();
    }

    public function reject()
    {
        $this->checkAuth();
        $id = (int)($_POST['id'] ?? 0);
        $remark = $_POST['remark'] ?? '';
        
        if ($id > 0) {
            require_once '../app/models/VolunteerModel.php';
            $volunteerModel = new \App\models\VolunteerModel();
            $volunteerModel->rejectVolunteer($id, $remark);
        }

        // โหลดตารางใหม่กลับไปอัปเดตหน้าจอ
        $this->getTable();
    }
}
