<?php
// app/models/AttributeModel.php
// จัดการ tbl_attribute แบบแยกตาม attribute_type ('1' = กิจกรรมที่สนใจ, '2' = ทักษะและความถนัด)

require_once '../app/config/Connection.php';

class AttributeModel
{
    private $db;

    public function __construct()
    {
        $this->db = \App\config\Connection::getInstance()->getPdo();
    }

    private function buildFilter(string $type, string $keyword): array
    {
        $where  = ["active_status = '1'", "attribute_type = :type"];
        $params = [':type' => $type];

        if ($keyword !== '') {
            $where[] = "attribute_name LIKE :kw";
            $params[':kw'] = '%' . $keyword . '%';
        }

        return ['sql' => implode(' AND ', $where), 'params' => $params];
    }

    public function getList(string $type, string $keyword, int $page, int $perPage): array
    {
        $f      = $this->buildFilter($type, $keyword);
        $offset = ($page - 1) * $perPage;

        $sql = "SELECT attribute_id, attribute_name, attribute_icon, attribute_desc, attribute_image, create_at
                FROM tbl_attribute
                WHERE {$f['sql']}
                ORDER BY attribute_id DESC
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

    public function countList(string $type, string $keyword): int
    {
        $f    = $this->buildFilter($type, $keyword);
        $stmt = $this->db->prepare("SELECT COUNT(*) FROM tbl_attribute WHERE {$f['sql']}");
        $stmt->execute($f['params']);
        return (int) $stmt->fetchColumn();
    }

    public function getById(int $id, string $type): ?array
    {
        $sql = "SELECT attribute_id, attribute_name, attribute_icon, attribute_desc, attribute_image, attribute_type
                FROM tbl_attribute
                WHERE attribute_id = :id AND attribute_type = :type AND active_status = '1'
                LIMIT 1";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id, ':type' => $type]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return $row ?: null;
    }

    // ชื่อซ้ำภายใน type เดียวกัน (เว้น id ที่กำลังแก้ไข) — เฉพาะ record ที่ยัง active
    public function nameExists(string $type, string $name, int $excludeId = 0): bool
    {
        $sql = "SELECT COUNT(*) FROM tbl_attribute
                WHERE attribute_type = :type AND attribute_name = :name
                  AND active_status = '1' AND attribute_id <> :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':type' => $type, ':name' => $name, ':id' => $excludeId]);
        return (int) $stmt->fetchColumn() > 0;
    }

    public function create(string $type, string $name, int $createUserId, ?string $icon = null, ?string $desc = null, ?string $image = null): int
    {
        $sql = "INSERT INTO tbl_attribute (attribute_name, attribute_icon, attribute_desc, attribute_image, active_status, create_user_id, create_at, attribute_type)
                VALUES (:name, :icon, :desc, :image, '1', :uid, NOW(), :type)";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':name' => $name,
            ':icon' => ($icon !== null && $icon !== '') ? $icon : null,
            ':desc' => ($desc !== null && $desc !== '') ? $desc : null,
            ':image' => ($image !== null && $image !== '') ? $image : null,
            ':uid'  => $createUserId,
            ':type' => $type,
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, string $type, string $name, ?string $icon = null, ?string $desc = null, ?string $image = null): bool
    {
        $sql = "UPDATE tbl_attribute SET attribute_name = :name, attribute_icon = :icon, attribute_desc = :desc";
        $params = [
            ':name' => $name,
            ':icon' => ($icon !== null && $icon !== '') ? $icon : null,
            ':desc' => ($desc !== null && $desc !== '') ? $desc : null,
            ':id'   => $id,
            ':type' => $type,
        ];
        
        if ($image !== null) {
            $sql .= ", attribute_image = :image";
            $params[':image'] = $image;
        }

        $sql .= " WHERE attribute_id = :id AND attribute_type = :type AND active_status = '1'";

        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function softDelete(int $id, string $type): bool
    {
        $sql = "UPDATE tbl_attribute SET active_status = '0'
                WHERE attribute_id = :id AND attribute_type = :type";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([':id' => $id, ':type' => $type]);
    }
}
