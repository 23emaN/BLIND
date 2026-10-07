<?php
namespace App\models;

use App\config\Connection;
use PDO;

class ActivityModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    public function getActivities($page = 1, $per_page = 10, $search = '')
    {
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM tbl_activity WHERE active_status = '1'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND activity_title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY create_at DESC LIMIT :limit OFFSET :offset";

        $stmt = $this->pdo->prepare($sql);
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val, PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $per_page, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countActivities($search = '')
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity WHERE active_status = '1'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND activity_title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function addActivity($name, $type, $userId)
    {
        $sql = "INSERT INTO tbl_attribute (attribute_name, attribute_type, active_status, create_user_id, create_at) 
                VALUES (:name, :type, '1', :user_id, NOW())";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':name' => $name,
            ':type' => $type,
            ':user_id' => $userId
        ]);
    }

    public function insertActivity($title, $attribute_id, $activity_date, $start_time, $end_time, $location, $max_volunteers, $reserve_count, $activity_detail, $imagePath, $userId)
    {
        $sql = "INSERT INTO tbl_activity 
                (activity_title, attribute_id, activity_date, start_time, end_time, location, max_volunteers, reserve_count, activity_detail, activity_image, create_user_id, create_at, activity_status, active_status) 
                VALUES 
                (:title, :attribute_id, :activity_date, :start_time, :end_time, :location, :max_volunteers, :reserve_count, :activity_detail, :imagePath, :user_id, NOW(), '1', '1')";
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute([
            ':title' => $title,
            ':attribute_id' => $attribute_id,
            ':activity_date' => $activity_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':location' => $location,
            ':max_volunteers' => $max_volunteers,
            ':reserve_count' => $reserve_count,
            ':activity_detail' => $activity_detail,
            ':imagePath' => $imagePath,
            ':user_id' => $userId
        ]);
    }

    public function getActivityById($id)
    {
        $sql = "SELECT * FROM tbl_activity WHERE activity_id = :id";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateActivity($id, $title, $attribute_id, $activity_date, $start_time, $end_time, $location, $max_volunteers, $reserve_count, $activity_detail, $imagePath, $userId)
    {
        $sql = "UPDATE tbl_activity SET 
                activity_title = :title, 
                attribute_id = :attribute_id, 
                activity_date = :activity_date, 
                start_time = :start_time, 
                end_time = :end_time, 
                location = :location, 
                max_volunteers = :max_volunteers, 
                reserve_count = :reserve_count, 
                activity_detail = :activity_detail,
                update_user_id = :user_id,
                update_at = NOW()";
        
        $params = [
            ':id' => $id,
            ':title' => $title,
            ':attribute_id' => $attribute_id,
            ':activity_date' => $activity_date,
            ':start_time' => $start_time,
            ':end_time' => $end_time,
            ':location' => $location,
            ':max_volunteers' => $max_volunteers,
            ':reserve_count' => $reserve_count,
            ':activity_detail' => $activity_detail,
            ':user_id' => $userId
        ];

        if ($imagePath !== '') {
            $sql .= ", activity_image = :imagePath";
            $params[':imagePath'] = $imagePath;
        }

        $sql .= " WHERE activity_id = :id";
        
        $stmt = $this->pdo->prepare($sql);
        return $stmt->execute($params);
    }

    public function getCategories() {
        $stmt = $this->pdo->query("SELECT * FROM tbl_attribute WHERE attribute_type = '1' AND active_status = '1' ORDER BY attribute_id ASC");
        return $stmt->fetchAll();
    }

    public function getTimeslots() {
        $stmt = $this->pdo->query("SELECT * FROM tbl_activity_timeslot WHERE active_status = '1' ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }

    public function getLocations() {
        $stmt = $this->pdo->query("SELECT * FROM tbl_activity_location WHERE active_status = '1' ORDER BY sort_order ASC");
        return $stmt->fetchAll();
    }
}
