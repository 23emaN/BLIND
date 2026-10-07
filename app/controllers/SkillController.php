<?php
// app/controllers/SkillController.php

class SkillController
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

        require_once '../app/models/SkillModel.php';
        $skillModel = new \App\models\SkillModel();
        
        $page = max(1, (int)($_GET['page'] ?? 1));
        $per_page = max(1, (int)($_GET['per_page'] ?? 10)); 
        $search = $_GET['search'] ?? ''; 
        
        $skills = $skillModel->getSkills($page, $per_page, $search);
        $total = $skillModel->countSkills($search);

        $data = [
            'title' => 'ตั้งค่าทักษะความถนัด',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname' => $user['user_lastname'] ?? '',
            'skills' => $skills,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search
        ];

        require_once '../app/views/main/skill_setting.php';
    }

    public function getTable()
    {
        $this->checkAuth();

        require_once '../app/models/SkillModel.php';
        $skillModel = new \App\models\SkillModel();
        
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = max(1, (int)($_POST['per_page'] ?? 10)); 
        $search = $_POST['search'] ?? '';
        
        $skills = $skillModel->getSkills($page, $per_page, $search);
        $total = $skillModel->countSkills($search);

        $data = [
            'skills' => $skills,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search
        ];

        require_once '../app/views/main/table/skill_table.php';
    }
}
