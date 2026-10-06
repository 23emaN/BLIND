<?php
// app/models/UserModel.php

require_once '../app/config/Connection.php';

class UserModel
{
    private $db;

    public function __construct()
    {
        $this->db = \App\config\Connection::getInstance()->getPdo();
    }

    public function getUserByUsername($username)
    {
        try {
            $sql = "SELECT user_id, user_name, user_password, user_firstname, user_lastname, active_status, user_status, is_super_admin 
                    FROM tbl_user 
                    WHERE user_name = :user_name 
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_name' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            // ในกรณีที่มีข้อผิดพลาด ส่งคืน false หรือโยน Exception
            return false;
        }
    }
}
