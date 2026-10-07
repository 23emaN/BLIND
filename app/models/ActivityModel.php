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
// app/models/ActivityModel.php
// จัดการ tbl_activity + หมวดหมู่ + นับจำนวนผู้เข้าร่วม

require_once '../app/config/Connection.php';

class ActivityModel
{
    private $db;

    public function __construct()
    {
        $this->db = \App\config\Connection::getInstance()->getPdo();
    }

    // หมวดหมู่กิจกรรม = tbl_attribute type '1' (ใช้ร่วมกับ "กิจกรรมที่สนใจ")
    // alias ชื่อคอลัมน์ให้เข้ากับ view เดิม (category_id/category_name/category_icon)
    public function getCategories(): array
    {
        $sql = "SELECT attribute_id AS category_id, attribute_name AS category_name,
                       attribute_icon AS category_icon, attribute_desc AS category_desc
                FROM tbl_attribute
                WHERE attribute_type = '1' AND active_status = '1'
                ORDER BY attribute_id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function categoryExists(int $attributeId): bool
    {
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_attribute
                                    WHERE attribute_id = :id AND attribute_type = '1' AND active_status = '1'");
        $stmt->execute([':id' => $attributeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    // สถานที่ที่ใช้บ่อย (preset ช่วยเติมช่องสถานที่)
    public function getLocations(): array
    {
        $sql = "SELECT location_id, location_label, location_name
                FROM tbl_activity_location
                WHERE active_status = '1'
                ORDER BY sort_order, location_id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    // ช่วงเวลาที่ใช้บ่อย (preset ช่วยเติมเวลาเริ่ม/สิ้นสุด)
    public function getTimeslots(): array
    {
        $sql = "SELECT timeslot_id, timeslot_name, start_time, end_time
                FROM tbl_activity_timeslot
                WHERE active_status = '1'
                ORDER BY sort_order, timeslot_id";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    private function buildFilter(string $keyword, string $categoryId, string $month): array
    {
        $where  = ["a.active_status = '1'"];
        $params = [];

        if ($keyword !== '') {
            $where[] = "(a.activity_title LIKE :kw1 OR a.location LIKE :kw2)";
            $params[':kw1'] = '%' . $keyword . '%';
            $params[':kw2'] = '%' . $keyword . '%';
        }
        if ($categoryId !== '' && ctype_digit($categoryId)) {
            $where[] = "a.attribute_id = :cat";
            $params[':cat'] = (int) $categoryId;
        }
        // month รูปแบบ YYYY-MM
        if ($month !== '' && preg_match('/^\d{4}-\d{2}$/', $month)) {
            $where[] = "DATE_FORMAT(a.activity_date, '%Y-%m') = :month";
            $params[':month'] = $month;
        }

        return ['sql' => implode(' AND ', $where), 'params' => $params];
    }

    public function getList(string $keyword, string $categoryId, string $month, int $page, int $perPage): array
    {
        $f      = $this->buildFilter($keyword, $categoryId, $month);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT a.activity_id, a.activity_title, a.activity_date, a.start_time, a.end_time,
                       a.location, a.max_volunteers, a.reserve_count, a.activity_status,
                       at.attribute_name AS category_name, at.attribute_icon AS category_icon,
                       (SELECT COUNT(*) FROM tbl_activity_volunteer av
                         WHERE av.activity_id = a.activity_id AND av.reg_status = '1') AS joined_count
                FROM tbl_activity a
                LEFT JOIN tbl_attribute at ON at.attribute_id = a.attribute_id
                WHERE {$f['sql']}
                ORDER BY a.activity_date DESC, a.start_time DESC
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        foreach ($f['params'] as $k => $v) {
            $stmt->bindValue($k, $v);
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countList(string $keyword, string $categoryId, string $month): int
    {
        $f    = $this->buildFilter($keyword, $categoryId, $month);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_activity a WHERE {$f['sql']}");
        $stmt->execute($f['params']);
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT a.*, a.attribute_id AS category_id, at.attribute_name AS category_name, at.attribute_icon AS category_icon,
                       (SELECT COUNT(*) FROM tbl_activity_volunteer av
                         WHERE av.activity_id = a.activity_id AND av.reg_status = '1') AS joined_count
                FROM tbl_activity a
                LEFT JOIN tbl_attribute at ON at.attribute_id = a.attribute_id
                WHERE a.activity_id = :id AND a.active_status = '1'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    public function create(array $d, int $createUserId): int
    {
        $sql = "INSERT INTO tbl_activity
                    (activity_title, attribute_id, activity_date, start_time, end_time, location,
                     max_volunteers, reserve_count, grant_hours, hours_per_person, hours_count_method,
                     activity_detail, activity_status, active_status, create_user_id, create_at)
                VALUES
                    (:title, :cat, :date, :start, :end, :loc,
                     :max, :reserve, :grant, :hours, :method,
                     :detail, '1', '1', :uid, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':title'   => $d['activity_title'],
            ':cat'     => $d['category_id'],
            ':date'    => $d['activity_date'],
            ':start'   => $d['start_time'],
            ':end'     => $d['end_time'],
            ':loc'     => $d['location'],
            ':max'     => $d['max_volunteers'],
            ':reserve' => $d['reserve_count'],
            ':grant'   => $d['grant_hours'],
            ':hours'   => $d['hours_per_person'],
            ':method'  => $d['hours_count_method'],
            ':detail'  => $d['activity_detail'],
            ':uid'     => $createUserId,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $d): bool
    {
        $sql = "UPDATE tbl_activity SET
                    activity_title = :title, attribute_id = :cat, activity_date = :date,
                    start_time = :start, end_time = :end, location = :loc,
                    max_volunteers = :max, reserve_count = :reserve,
                    grant_hours = :grant, hours_per_person = :hours, hours_count_method = :method,
                    activity_detail = :detail
                WHERE activity_id = :id AND active_status = '1'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':title'   => $d['activity_title'],
            ':cat'     => $d['category_id'],
            ':date'    => $d['activity_date'],
            ':start'   => $d['start_time'],
            ':end'     => $d['end_time'],
            ':loc'     => $d['location'],
            ':max'     => $d['max_volunteers'],
            ':reserve' => $d['reserve_count'],
            ':grant'   => $d['grant_hours'],
            ':hours'   => $d['hours_per_person'],
            ':method'  => $d['hours_count_method'],
            ':detail'  => $d['activity_detail'],
            ':id'      => $id,
        ]);
    }

    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity SET active_status = '0' WHERE activity_id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
