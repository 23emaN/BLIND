<?php
// app/models/VolunteerModel.php
namespace App\models;

use App\config\Connection;
use PDO;

class VolunteerModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    // ดึงข้อมูลอาสาสมัคร (พร้อมแบ่งหน้าและกรองสถานะ+ค้นหาชื่อ)
    public function getVolunteersByStatus($status, $page = 1, $per_page = 25, $search = '')
    {
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM tbl_volunteer WHERE 1=1";
        $params = [];
        
        if ($status !== '') {
            $sql .= " AND approve_status = :status";
            $params[':status'] = (string)$status;
        }

        if ($search !== '') {
            $sql .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search_full OR first_name LIKE :search_fname OR last_name LIKE :search_lname)";
            $params[':search_full']  = '%' . $search . '%';
            $params[':search_fname'] = '%' . $search . '%';
            $params[':search_lname'] = '%' . $search . '%';
        }
        
        $sql .= " ORDER BY create_at ASC LIMIT :limit OFFSET :offset";
        
        $stmt = $this->pdo->prepare($sql);
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // นับจำนวนอาสาสมัคร (ตามสถานะ+ค้นหาชื่อ)
    public function countVolunteersByStatus($status, $search = '')
    {
        $sql = "SELECT COUNT(*) FROM tbl_volunteer WHERE 1=1";
        $params = [];
        
        if ($status !== '') {
            $sql .= " AND approve_status = :status";
            $params[':status'] = (string)$status;
        }

        if ($search !== '') {
            $sql .= " AND (CONCAT(first_name, ' ', last_name) LIKE :search_full OR first_name LIKE :search_fname OR last_name LIKE :search_lname)";
            $params[':search_full']  = '%' . $search . '%';
            $params[':search_fname'] = '%' . $search . '%';
            $params[':search_lname'] = '%' . $search . '%';
        }
        
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    // อนุมัติอาสาสมัคร
    public function approveVolunteer($id)
    {
        $stmt = $this->pdo->prepare("UPDATE tbl_volunteer SET approve_status = '2' WHERE volunteer_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ปฏิเสธอาสาสมัคร
    public function rejectVolunteer($id, $remark)
    {
        $stmt = $this->pdo->prepare("UPDATE tbl_volunteer SET approve_status = '1', reject_remark = :remark WHERE volunteer_id = :id");
        return $stmt->execute([':id' => $id, ':remark' => $remark]);
    }
}
