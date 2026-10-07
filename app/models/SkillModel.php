<?php
namespace App\models;

use App\config\Connection;
use PDO;

class SkillModel
{
    private $pdo;

    public function __construct()
    {
        $this->pdo = Connection::getInstance()->getPdo();
    }

    public function getSkills($page = 1, $per_page = 10, $search = '')
    {
        $offset = ($page - 1) * $per_page;
        $sql = "SELECT * FROM tbl_attribute WHERE attribute_type = '2' AND active_status = '1'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND attribute_name LIKE :search";
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

    public function countSkills($search = '')
    {
        $sql = "SELECT COUNT(*) FROM tbl_attribute WHERE attribute_type = '2' AND active_status = '1'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND attribute_name LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }
}
