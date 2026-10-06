<?php
// app/controllers/MainController.php

class MainController
{

    private $userPayload = null;

    // Map URL => ชื่อหน้าภาษาไทย
    private $pageTitles = [
        'main'                => 'หน้าหลัก',
        'user'                => 'ผู้ใช้',
        'volunteer'           => 'อาสาสมัคร',
        'volunteer_approve'   => 'ยืนยันตัวตนอาสาสมัคร',
        'activity'            => 'ตั้งค่ากิจกรรมที่สนใจเข้าร่วม',
        'skill'               => 'ตั้งค่าทักษะและความถนัด',
    ];

    // ดึงชื่อหน้าจาก URL ปัจจุบัน
    private function getPageTitle(): string
    {
        $uri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
        // ตัด BASE_URL prefix ออกถ้ามี (กรณี run ใน subfolder)
        $basePath = trim(parse_url(BASE_URL, PHP_URL_PATH) ?? '', '/');
        if ($basePath !== '' && str_starts_with($uri, $basePath . '/')) {
            $uri = substr($uri, strlen($basePath) + 1);
        }
        // ใช้แค่ segment แรก
        $segment = explode('/', $uri)[0];
        return $this->pageTitles[$segment] ?? ucfirst($segment);
    }

    // ตรวจสอบการ Login ไว้เป็นฟังก์ชันส่วนตัว จะได้ไม่ต้องเขียนซ้ำ
    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        
        if (!$user) {
            // ถ้าเช็ค Token ไม่ผ่าน ให้เด้งไปหน้า Login
            header("Location: " . BASE_URL . "/login");
            exit();
        }

        // เก็บข้อมูล user ไว้ใช้ใน Class
        $this->userPayload = $user;
    }

    public function index()
    {
        $this->checkAuth();

        $companies = [];
        $fiscal_years = [];
        // 2. เตรียมข้อมูลส่งไปที่ View (MVC Pattern)
        $data = [
            'title' => 'BLIND - ' . $this->getPageTitle(),
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_name' => $this->userPayload['user_name'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'companies' => $companies,
            'fiscal_years' => $fiscal_years
        ];

        // 3. เรียก View มาแสดงผล
        require_once '../app/views/main/index.php';
    }

    public function logout()
    {
        // 1. (Optional) Invalidate token in database if we want strictly stateful JWT
        require_once '../app/models/AuthModel.php';
        $jwt = \App\models\AuthModel::bearerToken();
        if ($jwt !== '') {
            try {
                $secretKey = $_ENV['JWT_SECRET'] ?? '';
                $token = \Firebase\JWT\JWT::decode($jwt, new \Firebase\JWT\Key($secretKey, 'HS256'));
                if (!empty($token->jti)) {
                    $db = (new \App\Database\Connection())->getPdo();
                    $stmt = $db->prepare("UPDATE tbl_login_token SET end_datetime = NOW() WHERE token_code = :jti");
                    $stmt->execute([':jti' => $token->jti]);
                }
            } catch (\Throwable $e) {
                // Ignore error on logout
            }
        }

        // 2. ลบ Cookie
        setcookie('bo_access_token', '', time() - 3600, '/');

        // กลับไปหน้า Login
        header("Location: " . BASE_URL . "/login");
        exit();
    }
}

