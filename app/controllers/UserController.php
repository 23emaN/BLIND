<?php
// app/controllers/UserController.php

class UserController
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

    // ตรวจสิทธิ์สำหรับ endpoint ที่ตอบ JSON (ไม่ redirect แต่คืน error)
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

    private function model(): UserModel
    {
        require_once '../app/models/UserModel.php';
        return new UserModel();
    }

    // แสดงหน้าหลักของการจัดการผู้ใช้
    public function index()
    {
        $user = $this->checkAuth();

        $model    = $this->model();
        $perPage  = 25;
        $total    = $model->countList('', '');
        $users    = $model->getList('', '', 1, $perPage);

        $data = [
            'title'     => 'จัดการผู้ใช้',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname'  => $user['user_lastname'] ?? '',
            'users'     => $users,
            'total'     => $total,
            'page'      => 1,
            'per_page'  => $perPage,
        ];

        require_once '../app/views/main/user.php';
    }

    // AJAX: ค้นหา/กรอง/แบ่งหน้า — คืน HTML ของตาราง
    public function filter()
    {
        $this->checkAuthJson();

        $keyword = trim($_POST['keyword'] ?? '');
        $status  = trim($_POST['status'] ?? '');
        $page    = max(1, (int) ($_POST['page'] ?? 1));
        $perPage = (int) ($_POST['per_page'] ?? 25);
        if (!in_array($perPage, [25, 50, 75, 100], true)) {
            $perPage = 25;
        }

        $model = $this->model();
        $total = $model->countList($keyword, $status);
        $users = $model->getList($keyword, $status, $page, $perPage);

        // render partial ตารางเป็น string
        ob_start();
        include '../app/views/main/table/user_table.php';
        $html = ob_get_clean();

        echo json_encode(['result' => 1, 'html' => $html]);
    }

    // AJAX: ดึงข้อมูลผู้ใช้รายคน (สำหรับ modal แก้ไข)
    public function get()
    {
        $this->checkAuthJson();

        $id   = (int) ($_GET['id'] ?? 0);
        $user = $this->model()->getById($id);

        if (!$user) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลผู้ใช้']);
            return;
        }
        echo json_encode(['result' => 1, 'data' => $user]);
    }

    // AJAX: เพิ่มผู้ใช้
    public function add()
    {
        $this->checkAuthJson();

        $input  = $this->sanitizeInput();
        $errors = $this->validate($input, true);
        if ($errors) {
            echo json_encode(['result' => 0, 'msg' => implode("\n", $errors)]);
            return;
        }

        $model = $this->model();
        if ($model->usernameExists($input['user_name'])) {
            echo json_encode(['result' => 0, 'msg' => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว']);
            return;
        }

        try {
            $model->create($input);
            echo json_encode(['result' => 1, 'msg' => 'เพิ่มผู้ใช้สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    // AJAX: แก้ไขผู้ใช้
    public function edit()
    {
        $this->checkAuthJson();

        $id = (int) ($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสผู้ใช้']);
            return;
        }

        $input  = $this->sanitizeInput();
        $errors = $this->validate($input, false);
        if ($errors) {
            echo json_encode(['result' => 0, 'msg' => implode("\n", $errors)]);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลผู้ใช้']);
            return;
        }
        if ($model->usernameExists($input['user_name'], $id)) {
            echo json_encode(['result' => 0, 'msg' => 'ชื่อผู้ใช้นี้มีอยู่ในระบบแล้ว']);
            return;
        }

        try {
            $model->update($id, $input);
            echo json_encode(['result' => 1, 'msg' => 'แก้ไขผู้ใช้สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    // AJAX: ลบ (soft delete)
    public function delete()
    {
        $user = $this->checkAuthJson();

        $id = (int) ($_POST['user_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสผู้ใช้']);
            return;
        }

        // กันลบบัญชีตัวเอง
        if ((int) ($user['user_id'] ?? 0) === $id) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่สามารถลบบัญชีที่กำลังใช้งานอยู่ได้']);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูลผู้ใช้']);
            return;
        }

        try {
            $model->softDelete($id);
            echo json_encode(['result' => 1, 'msg' => 'ลบผู้ใช้สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        }
    }

    // อ่านและทำความสะอาด input จากฟอร์ม
    private function sanitizeInput(): array
    {
        return [
            'user_name'      => trim($_POST['user_name'] ?? ''),
            'user_password'  => (string) ($_POST['user_password'] ?? ''),
            'user_firstname' => trim($_POST['user_firstname'] ?? ''),
            'user_lastname'  => trim($_POST['user_lastname'] ?? ''),
            'user_status'    => ($_POST['user_status'] ?? '1') === '0' ? '0' : '1',
            'is_super_admin' => ((int) ($_POST['is_super_admin'] ?? 0)) === 1 ? 1 : 0,
        ];
    }

    // ตรวจความถูกต้อง — $requirePassword = true เฉพาะตอนเพิ่มใหม่
    private function validate(array $input, bool $requirePassword): array
    {
        $errors = [];

        if ($input['user_name'] === '') {
            $errors[] = 'กรุณากรอกชื่อผู้ใช้';
        } elseif (strlen($input['user_name']) > 50) {
            $errors[] = 'ชื่อผู้ใช้ต้องไม่เกิน 50 ตัวอักษร';
        }

        if ($input['user_firstname'] === '') {
            $errors[] = 'กรุณากรอกชื่อ';
        }

        if ($requirePassword || $input['user_password'] !== '') {
            if (strlen($input['user_password']) < 4) {
                $errors[] = 'รหัสผ่านต้องมีอย่างน้อย 4 ตัวอักษร';
            }
        }

        return $errors;
    }
}
