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

    // ----- สถานที่ -----
    public function getLocations(string $keyword, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT location_id, location_label, location_name, sort_order
                FROM tbl_activity_location
                WHERE active_status = '1'" . ($keyword !== '' ? " AND (location_label LIKE :kw1 OR location_name LIKE :kw2)" : "") . "
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

    public function countLocations(string $keyword): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity_location
                WHERE active_status = '1'" . ($keyword !== '' ? " AND (location_label LIKE :kw1 OR location_name LIKE :kw2)" : "");
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
        $stmt = $this->db->prepare("SELECT location_id, location_label, location_name
                                    FROM tbl_activity_location WHERE location_id = :id AND active_status = '1' LIMIT 1");
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
                                    WHERE location_id = :id AND active_status = '1'");
        return $stmt->execute([':label' => $label, ':name' => $name, ':id' => $id]);
    }

    public function deleteLocation(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_location SET active_status = '0' WHERE location_id = :id");
        return $stmt->execute([':id' => $id]);
    }

    // ----- ช่วงเวลา -----
    public function getTimeslots(string $keyword, int $page, int $perPage): array
    {
        $offset = ($page - 1) * $perPage;
        $sql = "SELECT timeslot_id, timeslot_name, start_time, end_time, sort_order
                FROM tbl_activity_timeslot
                WHERE active_status = '1'" . ($keyword !== '' ? " AND timeslot_name LIKE :kw" : "") . "
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

    public function countTimeslots(string $keyword): int
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity_timeslot
                WHERE active_status = '1'" . ($keyword !== '' ? " AND timeslot_name LIKE :kw" : "");
        $stmt = $this->db->prepare($sql);
        if ($keyword !== '') {
            $stmt->bindValue(':kw', '%' . $keyword . '%');
        }
        $stmt->execute();
        return (int) $stmt->fetchColumn();
    }

    public function getTimeslot(int $id): ?array
    {
        $stmt = $this->db->prepare("SELECT timeslot_id, timeslot_name, start_time, end_time
                                    FROM tbl_activity_timeslot WHERE timeslot_id = :id AND active_status = '1' LIMIT 1");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function createTimeslot(string $name, string $start, string $end): int
    {
        $sql = "INSERT INTO tbl_activity_timeslot (timeslot_name, start_time, end_time, sort_order, active_status)
                VALUES (:name, :start, :end, (SELECT * FROM (SELECT COALESCE(MAX(sort_order),0)+1 FROM tbl_activity_timeslot) t), '1')";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $name, ':start' => $start, ':end' => $end]);
        return (int) $this->db->lastInsertId();
    }

    public function updateTimeslot(int $id, string $name, string $start, string $end): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_timeslot SET timeslot_name = :name, start_time = :start, end_time = :end
                                    WHERE timeslot_id = :id AND active_status = '1'");
        return $stmt->execute([':name' => $name, ':start' => $start, ':end' => $end, ':id' => $id]);
    }

    public function deleteTimeslot(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_activity_timeslot SET active_status = '0' WHERE timeslot_id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
