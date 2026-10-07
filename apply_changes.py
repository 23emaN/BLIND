def append_to_controller():
    with open('app/controllers/ActivityController.php', 'r', encoding='utf-8') as f:
        content = f.read()

    # The friend renamed index to get, or we just want to inject our stuff
    user_methods = """
    public function getTable()
    {
        $this->checkAuthJson();
        require_once '../app/models/ActivityModel.php';
        $activityModel = new \\App\\models\\ActivityModel();
        
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = max(1, (int)($_POST['per_page'] ?? 10)); 
        $search = $_POST['search'] ?? '';
        
        $activities = $activityModel->getActivities($page, $per_page, $search);
        $total = $activityModel->countActivities($search);

        $data = [
            'activities' => $activities,
            'total' => $total,
            'page' => $page,
            'per_page' => $per_page,
            'search' => $search
        ];

        require_once '../app/views/main/table/activity_table.php';
    }

    public function getById()
    {
        $this->checkAuthJson();
        $id = $_POST['id'] ?? null;
        if (!$id) {
            echo json_encode(['status' => 'error', 'message' => 'Missing ID']);
            return;
        }

        require_once '../app/models/ActivityModel.php';
        $activityModel = new \\App\\models\\ActivityModel();
        
        $activity = $activityModel->getActivityById($id);
        
        if ($activity) {
            if (!empty($activity['activity_date'])) {
                $dateParts = explode('-', $activity['activity_date']);
                if (count($dateParts) == 3) {
                    $activity['activity_date'] = $dateParts[2] . '/' . $dateParts[1] . '/' . $dateParts[0];
                }
            }
            if (!empty($activity['activity_image'])) {
                $activity['activity_image'] = BASE_URL . '/' . ltrim($activity['activity_image'], '/');
            }
            echo json_encode(['status' => 'success', 'data' => $activity]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Activity not found']);
        }
    }

    public function update()
    {
        $user = $this->checkAuthJson();
        $id = $_POST['id'] ?? null;
        if (!$id) { echo 'error'; return; }

        $imagePath = '';
        if (isset($_FILES['activity_image']) && $_FILES['activity_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = dirname(__DIR__, 2) . '/upload_image/';
            if (!is_dir($uploadDir)) mkdir($uploadDir, 0777, true);
            $fileName = time() . '_' . basename($_FILES['activity_image']['name']);
            $targetPath = $uploadDir . $fileName;
            if (move_uploaded_file($_FILES['activity_image']['tmp_name'], $targetPath)) {
                $imagePath = 'upload_image/' . $fileName;
            }
        }
        
        $activity_date = $_POST['activity_date'] ?? '';
        if (!empty($activity_date)) {
            $dateParts = explode('/', $activity_date);
            if (count($dateParts) == 3) {
                $activity_date = $dateParts[2] . '-' . $dateParts[1] . '-' . $dateParts[0];
            }
        }
        
        try {
            require_once '../app/models/ActivityModel.php';
            $activityModel = new \\App\\models\\ActivityModel();
            $activityModel->updateActivity(
                $id, $_POST['title'] ?? '', $_POST['attribute_id'] ?? '', $activity_date, 
                $_POST['start_time'] ?? '', $_POST['end_time'] ?? '', $_POST['location'] ?? '', 
                $_POST['max_volunteers'] ?? 1, $_POST['reserve_count'] ?? 0, 
                $_POST['activity_detail'] ?? '', $imagePath, $user['user_id']
            );
            echo 'success';
        } catch (\\Exception $e) {
            echo 'error';
        }
    }

    // Override index so activity_setting.php works if called!
    public function index_old()
    {
        $this->checkAuth();
        require_once '../app/models/ActivityModel.php';
        $activityModel = new \\App\\models\\ActivityModel();
        
        $categories = $activityModel->getCategories();
        
        $data = [
            'title' => 'BLIND - จัดการกิจกรรม',
            'user_id' => $this->userPayload['user_id'] ?? '',
            'user_name' => $this->userPayload['user_name'] ?? '',
            'firstname' => $this->userPayload['user_firstname'] ?? '',
            'lastname' => $this->userPayload['user_lastname'] ?? '',
            'is_super_admin' => $this->userPayload['is_super_admin'] ?? '0',
            'categories' => $categories
        ];
        
        require_once '../app/views/main/activity_setting.php';
    }
"""
    class_end = content.rfind('}')
    new_content = content[:class_end] + user_methods + content[class_end:]
    with open('app/controllers/ActivityController.php', 'w', encoding='utf-8') as f:
        f.write(new_content)

def append_to_model():
    with open('app/models/ActivityModel.php', 'r', encoding='utf-8') as f:
        content = f.read()

    user_methods = """
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

        $stmt = $this->db->prepare($sql);
        foreach($params as $key => $val) {
            $stmt->bindValue($key, $val, \\PDO::PARAM_STR);
        }
        $stmt->bindValue(':limit', $per_page, \\PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, \\PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(\\PDO::FETCH_ASSOC);
    }

    public function countActivities($search = '')
    {
        $sql = "SELECT COUNT(*) FROM tbl_activity WHERE active_status = '1'";
        $params = [];

        if ($search !== '') {
            $sql .= " AND activity_title LIKE :search";
            $params[':search'] = '%' . $search . '%';
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        return (int) $stmt->fetchColumn();
    }

    public function getActivityById($id)
    {
        $sql = "SELECT * FROM tbl_activity WHERE activity_id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $id]);
        return $stmt->fetch(\\PDO::FETCH_ASSOC);
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
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
"""
    class_end = content.rfind('}')
    new_content = content[:class_end] + user_methods + content[class_end:]
    with open('app/models/ActivityModel.php', 'w', encoding='utf-8') as f:
        f.write(new_content)

def fix_web_routes():
    routes_content = """<?php
$requestMethod = $_SERVER['REQUEST_METHOD'];
$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'user'   => ['UserController', 'index'],
        'user/get' => ['UserController', 'get'],
        'volunteer' => ['MainController', 'index'],
        'volunteer_approve' => ['VolunteerController', 'index'],
        
        # User routes
        'activity_setting' => ['ActivityController', 'index_old'],

        # Friend routes
        'activity' => ['ActivityController', 'index'],
        'activity/get' => ['ActivityController', 'get'],
        'skill' => ['SkillController', 'index'],
        'skill/get' => ['SkillController', 'get'],
        'interest' => ['InterestController', 'index'],
        'interest/get' => ['InterestController', 'get'],
        'location' => ['LocationController', 'index'],
        'location/get' => ['LocationController', 'get'],
        'timeslot' => ['TimeslotController', 'index'],
        'timeslot/get' => ['TimeslotController', 'get'],
    ],
    'POST' => [
        'auth/login'   => ['AuthController', 'processLogin'],
        
        # User routes
        'volunteer_approve_table' => ['VolunteerController', 'getTable'],
        'volunteer_table' => ['VolunteerController', 'getApprovedTable'],
        'approveVolunteer' => ['VolunteerController', 'approve'],
        'rejectVolunteer' => ['VolunteerController', 'reject'],
        'activity_table' => ['ActivityController', 'getTable'],
        'addActivity' => ['ActivityController', 'add'],
        'getActivityById' => ['ActivityController', 'getById'],
        'updateActivity' => ['ActivityController', 'update'],
        'skill_table' => ['SkillController', 'getTable'],

        # Friend routes
        'user/filter'  => ['UserController', 'filter'],
        'user/add'     => ['UserController', 'add'],
        'user/edit'    => ['UserController', 'edit'],
        'user/delete'  => ['UserController', 'delete'],
        'skill/filter' => ['SkillController', 'filter'],
        'skill/add'    => ['SkillController', 'add'],
        'skill/edit'   => ['SkillController', 'edit'],
        'skill/delete' => ['SkillController', 'delete'],
        'activity/filter' => ['ActivityController', 'filter'],
        'activity/add'    => ['ActivityController', 'add'],
        'activity/edit'   => ['ActivityController', 'edit'],
        'activity/delete' => ['ActivityController', 'delete'],
        'interest/filter' => ['InterestController', 'filter'],
        'interest/add'    => ['InterestController', 'add'],
        'interest/edit'   => ['InterestController', 'edit'],
        'interest/delete' => ['InterestController', 'delete'],
        'location/filter' => ['LocationController', 'filter'],
        'location/add'    => ['LocationController', 'add'],
        'location/edit'   => ['LocationController', 'edit'],
        'location/delete' => ['LocationController', 'delete'],
        'timeslot/filter' => ['TimeslotController', 'filter'],
        'timeslot/add'    => ['TimeslotController', 'add'],
        'timeslot/edit'   => ['TimeslotController', 'edit'],
        'timeslot/delete' => ['TimeslotController', 'delete'],
    ]
];

if (isset($routes[$requestMethod]) && array_key_exists($url, $routes[$requestMethod])) {
    $controllerName = $routes[$requestMethod][$url][0];
    $methodName = $routes[$requestMethod][$url][1];
    require_once "../app/controllers/{$controllerName}.php";
    $controller = new $controllerName();
    $controller->$methodName();
} else {
    header("Location: " . BASE_URL . "/login");
    exit();
}
"""
    with open('routes/web.php', 'w', encoding='utf-8') as f:
        f.write(routes_content)

try:
    append_to_controller()
    append_to_model()
    fix_web_routes()
    print('Done applying user logic on top of friend code!')
except Exception as e:
    print('Error:', e)
