<?php
// app/controllers/UserController.php

class UserController
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

        $data = [
            'title' => 'ผู้ใช้',
            'firstname' => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname' => $user['user_lastname'] ?? ''
        ];

        require_once '../app/views/main/user.php';
    }
}
