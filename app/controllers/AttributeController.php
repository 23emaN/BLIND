<?php
// app/controllers/AttributeController.php
// Base controller สำหรับหน้า tbl_attribute — subclass กำหนด $type / $pageTitle / $view เอง
// (SkillController = type '2', ActivityController = type '1')

class AttributeController
{
    protected $type      = '2';                 // override ใน subclass
    protected $pageTitle = 'ตั้งค่าทักษะและความถนัด';
    protected $itemLabel = 'ทักษะ';             // ใช้ในข้อความ เช่น "เพิ่มทักษะ"
    protected $routeBase = 'skill';             // base ของ endpoint (skill / interest)
    protected $hasMeta   = false;               // true = จัดการ icon + desc ด้วย (หมวดหมู่กิจกรรม)
    protected $authUser  = null;

    private function checkAuth()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (!$user) {
            header("Location: " . BASE_URL . "/login");
            exit();
        }
        $this->authUser = $user;
        return $user;
    }

    private function checkAuthJson()
    {
        require_once '../app/models/AuthModel.php';
        $user = \App\models\AuthModel::checkWebAuth();
        if (!$user) {
            echo json_encode(['result' => 0, 'msg' => 'เซสชันหมดอายุ กรุณาเข้าสู่ระบบใหม่']);
            exit();
        }
        $this->authUser = $user;
        return $user;
    }

    private function model(): AttributeModel
    {
        require_once '../app/models/AttributeModel.php';
        return new AttributeModel();
    }

    public function index()
    {
        $user    = $this->checkAuth();
        $model   = $this->model();
        $perPage = 25;

        $data = [
            'title'      => $this->pageTitle,
            'type'       => $this->type,
            'route'      => $this->routeBase,
            'item_label' => $this->itemLabel,
            'has_meta'   => $this->hasMeta,
            'firstname'  => $user['user_firstname'] ?? 'ผู้ใช้งาน',
            'lastname'   => $user['user_lastname'] ?? '',
            'items'      => $model->getList($this->type, '', 1, $perPage),
            'total'      => $model->countList($this->type, ''),
            'page'       => 1,
            'per_page'   => $perPage,
        ];

        require_once '../app/views/main/attribute.php';
    }

    public function filter()
    {
        $this->checkAuthJson();

        $keyword = trim($_POST['keyword'] ?? '');
        $page    = max(1, (int) ($_POST['page'] ?? 1));
        $perPage = (int) ($_POST['per_page'] ?? 25);
        if (!in_array($perPage, [25, 50, 75, 100], true)) {
            $perPage = 25;
        }

        $model      = $this->model();
        $total      = $model->countList($this->type, $keyword);
        $items      = $model->getList($this->type, $keyword, $page, $perPage);
        $item_label = $this->itemLabel;
        $has_meta   = $this->hasMeta;

        ob_start();
        include '../app/views/main/table/attribute_table.php';
        $html = ob_get_clean();

        echo json_encode(['result' => 1, 'html' => $html]);
    }

    public function get()
    {
        $this->checkAuthJson();

        $id   = (int) ($_GET['id'] ?? 0);
        $item = $this->model()->getById($id, $this->type);

        if (!$item) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']);
            return;
        }
        echo json_encode(['result' => 1, 'data' => $item]);
    }

    public function add()
    {
        $user = $this->checkAuthJson();

        $name = trim($_POST['attribute_name'] ?? '');
        if ($name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อ' . $this->itemLabel]);
            return;
        }

        $model = $this->model();
        if ($model->nameExists($this->type, $name)) {
            echo json_encode(['result' => 0, 'msg' => 'มีรายการนี้อยู่ในระบบแล้ว']);
            return;
        }

        $icon = $this->hasMeta ? trim($_POST['attribute_icon'] ?? '') : null;
        $desc = $this->hasMeta ? trim($_POST['attribute_desc'] ?? '') : null;

        try {
            $model->create($this->type, $name, (int) ($user['user_id'] ?? 0), $icon, $desc);
            echo json_encode(['result' => 1, 'msg' => 'เพิ่ม' . $this->itemLabel . 'สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function edit()
    {
        $this->checkAuthJson();

        $id   = (int) ($_POST['attribute_id'] ?? 0);
        $name = trim($_POST['attribute_name'] ?? '');
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสรายการ']);
            return;
        }
        if ($name === '') {
            echo json_encode(['result' => 0, 'msg' => 'กรุณากรอกชื่อ' . $this->itemLabel]);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id, $this->type)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']);
            return;
        }
        if ($model->nameExists($this->type, $name, $id)) {
            echo json_encode(['result' => 0, 'msg' => 'มีรายการนี้อยู่ในระบบแล้ว']);
            return;
        }

        $icon = $this->hasMeta ? trim($_POST['attribute_icon'] ?? '') : null;
        $desc = $this->hasMeta ? trim($_POST['attribute_desc'] ?? '') : null;

        try {
            $model->update($id, $this->type, $name, $icon, $desc);
            echo json_encode(['result' => 1, 'msg' => 'แก้ไข' . $this->itemLabel . 'สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการบันทึกข้อมูล']);
        }
    }

    public function delete()
    {
        $this->checkAuthJson();

        $id = (int) ($_POST['attribute_id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบรหัสรายการ']);
            return;
        }

        $model = $this->model();
        if (!$model->getById($id, $this->type)) {
            echo json_encode(['result' => 0, 'msg' => 'ไม่พบข้อมูล']);
            return;
        }

        try {
            $model->softDelete($id, $this->type);
            echo json_encode(['result' => 1, 'msg' => 'ลบ' . $this->itemLabel . 'สำเร็จ']);
        } catch (\Throwable $e) {
            echo json_encode(['result' => 0, 'msg' => 'เกิดข้อผิดพลาดในการลบข้อมูล']);
        }
    }
}
