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

    /**
     * ใช้ตอน login — ดึงผู้ใช้จาก username (รวม user_password เพื่อตรวจรหัส)
     */
    public function getUserByUsername($username)
    {
        try {
            $sql = "SELECT user_id, user_name, user_password, user_firstname, user_lastname, active_status, user_status, is_super_admin
                    FROM tbl_user
                    WHERE user_name = :user_name AND active_status = '1'
                    LIMIT 1";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([':user_name' => $username]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return false;
        }
    }

    /**
     * สร้างเงื่อนไข WHERE + พารามิเตอร์ ใช้ร่วมกันระหว่าง getList และ countList
     */
    private function buildFilter(string $keyword, string $status): array
    {
        $where  = ["active_status = '1'"];
        $params = [];

        if ($keyword !== '') {
            // ตั้ง PDO::ATTR_EMULATE_PREPARES=false ห้ามใช้ชื่อ placeholder ซ้ำ จึงต้องแยกชื่อ
            $like = '%' . $keyword . '%';
            $where[] = "(user_name LIKE :kw1 OR user_firstname LIKE :kw2 OR user_lastname LIKE :kw3)";
            $params[':kw1'] = $like;
            $params[':kw2'] = $like;
            $params[':kw3'] = $like;
        }

        if ($status === '0' || $status === '1') {
            $where[] = "user_status = :st";
            $params[':st'] = $status;
        }

        return ['sql' => implode(' AND ', $where), 'params' => $params];
    }

    /**
     * รายการผู้ใช้แบบแบ่งหน้า (ไม่รวม user_password)
     */
    public function getList(string $keyword, string $status, int $page, int $perPage): array
    {
        $f      = $this->buildFilter($keyword, $status);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT user_id, user_name, user_firstname, user_lastname, user_status, is_super_admin, create_at
                FROM tbl_user
                WHERE {$f['sql']}
                ORDER BY user_id DESC
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

    public function countList(string $keyword, string $status): int
    {
        $f    = $this->buildFilter($keyword, $status);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_user WHERE {$f['sql']}");
        $stmt->execute($f['params']);
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id): ?array
    {
        $sql = "SELECT user_id, user_name, user_firstname, user_lastname, user_status, is_super_admin
                FROM tbl_user
                WHERE user_id = :id AND active_status = '1'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    /**
     * ตรวจว่าชื่อผู้ใช้ซ้ำหรือไม่ (เว้น id ที่กำลังแก้ไข) — นับเฉพาะ record ที่ยัง active
     */
    public function usernameExists(string $username, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM tbl_user
                WHERE user_name = :name AND active_status = '1' AND user_id <> :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':name' => $username, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(array $data): int
    {
        $sql = "INSERT INTO tbl_user
                    (user_name, user_password, user_firstname, user_lastname, active_status, user_status, is_super_admin, create_at)
                VALUES
                    (:user_name, :user_password, :user_firstname, :user_lastname, '1', :user_status, :is_super_admin, NOW())";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':user_name'      => $data['user_name'],
            ':user_password'  => password_hash($data['user_password'], PASSWORD_BCRYPT),
            ':user_firstname' => $data['user_firstname'],
            ':user_lastname'  => $data['user_lastname'],
            ':user_status'    => $data['user_status'],
            ':is_super_admin' => $data['is_super_admin'],
        ]);
        return (int) $this->db->lastInsertId();
    }

    /**
     * แก้ไขผู้ใช้ — เปลี่ยนรหัสผ่านเฉพาะเมื่อส่ง user_password มา (ไม่ว่าง)
     */
    public function update(int $id, array $data): bool
    {
        $fields = [
            'user_name = :user_name',
            'user_firstname = :user_firstname',
            'user_lastname = :user_lastname',
            'user_status = :user_status',
            'is_super_admin = :is_super_admin',
        ];
        $params = [
            ':user_name'      => $data['user_name'],
            ':user_firstname' => $data['user_firstname'],
            ':user_lastname'  => $data['user_lastname'],
            ':user_status'    => $data['user_status'],
            ':is_super_admin' => $data['is_super_admin'],
            ':id'             => $id,
        ];

        if (!empty($data['user_password'])) {
            $fields[] = 'user_password = :user_password';
            $params[':user_password'] = password_hash($data['user_password'], PASSWORD_BCRYPT);
        }

        $sql  = "UPDATE tbl_user SET " . implode(', ', $fields) . " WHERE user_id = :id AND active_status = '1'";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    /**
     * ลบแบบ soft delete (ตั้ง active_status = '0') — ไม่ลบข้อมูลจริง
     */
    public function softDelete(int $id): bool
    {
        $stmt = $this->db->prepare("UPDATE tbl_user SET active_status = '0' WHERE user_id = :id");
        return $stmt->execute([':id' => $id]);
    }
}
