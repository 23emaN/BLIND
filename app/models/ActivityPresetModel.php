<?php
// app/models/ActivityPresetModel.php
// CRUD ของ preset: สถานที่ที่ใช้บ่อย (tbl_activity_location) และ ช่วงเวลาที่ใช้บ่อย (tbl_activity_timeslot)

require_once '../app/config/Connection.php';

class ActivityPresetModel
{
    private $db;

    public function __construct()
    {
        $this->db = \App\config\Connection::getInstance()->getPdo();
    }

    // $status: '' = ทุกสถานะ, '1' = ใช้งานอยู่, '0' = ปิดการใช้งาน (whitelist แล้ว ต่อ SQL ได้ปลอดภัย)
    private function statusWhere(string $status): string
    {
        return ($status === '0' || $status === '1') ? "active_status = '{$status}'" : '1=1';
    }

    // ----- สถานที่ -----
    public function getLocations(string $keyword, int $page, int $perPage, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT location_id, location_label, location_name, sort_order, active_status
                FROM tbl_activity_location
                WHERE " . $this->statusWhere($status) . ($keyword !== '' ? " AND (location_label LIKE :kw1 OR location_name LIKE :kw2)" : "") . "
                ORDER BY sort_order, location_id
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw1', '%' . $keyword . '%');
            $stmt->bindValue(':kw2', '%' . $keyword . '%');
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countLocations(string $keyword, string $status = ''): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity_location
                WHERE " . $this->statusWhere($status) . ($keyword !== '' ? " AND (location_label LIKE :kw1 OR location_name LIKE :kw2)" : "");
        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw1', '%' . $keyword . '%');
            $stmt->bindValue(':kw2', '%' . $keyword . '%');
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getLocation(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT location_id, location_label, location_name, active_status
                                    FROM tbl_activity_location WHERE location_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createLocation(string $label, string $name): int
    {
        $sql = "INSERT INTO tbl_activity_location (location_label, location_name, sort_order, active_status)
                VALUES (:label, :name, (SELECT * FROM (SELECT COALESCE(MAX(sort_order),0)+1 FROM tbl_activity_location) t), '1')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':label' => $label, ':name' => $name]);
        return (int) $this->db->lastInsertId();
    }

    public function updateLocation(int $id, string $label, string $name): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_location SET location_label = :label, location_name = :name
                                    WHERE location_id = :id");
        return $stmt->execute([':label' => $label, ':name' => $name, ':id' => $id]);
    }

    // ----- ช่วงเวลา -----
    public function getTimeslots(string $keyword, int $page, int $perPage, string $status = ''): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT timeslot_id, timeslot_name, start_time, end_time, timeslot_icon, sort_order, active_status
                FROM tbl_activity_timeslot
                WHERE " . $this->statusWhere($status) . ($keyword !== '' ? " AND timeslot_name LIKE :kw" : "") . "
                ORDER BY sort_order, timeslot_id
                LIMIT :limit OFFSET :offset";
        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw', '%' . $keyword . '%');
        }
        $stmt->bindValue(':limit', $perPage, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function countTimeslots(string $keyword, string $status = ''): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity_timeslot
                WHERE " . $this->statusWhere($status) . ($keyword !== '' ? " AND timeslot_name LIKE :kw" : "");
        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw', '%' . $keyword . '%');
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getTimeslot(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT timeslot_id, timeslot_name, start_time, end_time, timeslot_icon, active_status
                                    FROM tbl_activity_timeslot WHERE timeslot_id = :id LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createTimeslot(string $name, string $start, string $end, string $icon): int
    {
        $sql = "INSERT INTO tbl_activity_timeslot (timeslot_name, start_time, end_time, timeslot_icon, sort_order, active_status)
                VALUES (:name, :start, :end, :icon, (SELECT * FROM (SELECT COALESCE(MAX(sort_order),0)+1 FROM tbl_activity_timeslot) t), '1')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name, ':start' => $start, ':end' => $end, ':icon' => $icon]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTimeslot(int $id, string $name, string $start, string $end, string $icon): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_timeslot SET timeslot_name = :name, start_time = :start, end_time = :end, timeslot_icon = :icon
                                    WHERE timeslot_id = :id");
        return $stmt->execute([':name' => $name, ':start' => $start, ':end' => $end, ':icon' => $icon, ':id' => $id]);
    }

    // ----- เปิด/ปิดการใช้งาน (แทนการลบ) -----
    public function setLocationStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_location SET active_status = :status WHERE location_id = :id");
        return $stmt->execute([':status' => $status === '0' ? '0' : '1', ':id' => $id]);
    }

    public function setTimeslotStatus(int $id, string $status): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_timeslot SET active_status = :status WHERE timeslot_id = :id");
        return $stmt->execute([':status' => $status === '0' ? '0' : '1', ':id' => $id]);
    }
}
