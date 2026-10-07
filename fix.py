import re

def fix_activity_model():
    with open('app/models/ActivityModel.php', 'r', encoding='utf-8') as f:
        content = f.read()

    idx = content.find('// app/models/ActivityModel.php')
    if idx != -1:
        # The team's new model code
        team_content = content[idx:]
        
        # User's methods to insert
        user_methods = """
    public function getActivityById($id)
    {
        $sql = "SELECT * FROM tbl_activity WHERE activity_id = :id";
        $stmt = $this->db->prepare($sql);
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
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }
"""
        class_end_idx = team_content.rfind('}')
        if class_end_idx != -1:
            team_content = team_content[:class_end_idx] + user_methods + team_content[class_end_idx:]
            
        with open('app/models/ActivityModel.php', 'w', encoding='utf-8') as f:
            f.write("<?php\nnamespace App\\models;\nuse PDO;\n" + team_content)

def fix_activity_controller():
    with open('app/controllers/ActivityController.php', 'r', encoding='utf-8') as f:
        content = f.read()

    idx = content.find('    private function checkAuthJson()')
    if idx != -1:
        # Keep everything after the team's refactored controller starts
        team_content = content[idx:]
        
        # Prepend class declaration
        team_content = "<?php\nclass ActivityController\n{\n" + team_content
        
        # User's methods to insert
        user_methods = """
    public function getTable()
    {
        // Fallback for user's table route
        $this->checkAuthJson();
        require_once '../app/models/ActivityModel.php';
        $activityModel = new \\App\\models\\ActivityModel();
        
        $page = max(1, (int)($_POST['page'] ?? 1));
        $per_page = max(1, (int)($_POST['per_page'] ?? 10)); 
        $search = $_POST['search'] ?? '';
        
        $activities = $activityModel->getList($search, '', '', $page, $per_page);
        $total = $activityModel->countList($search, '', '');

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

        $activityModel = $this->model();
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
        if (!$id) { echo "error"; return; }

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
            $this->model()->updateActivity(
                $id, $_POST['title'] ?? '', $_POST['attribute_id'] ?? '', $activity_date, 
                $_POST['start_time'] ?? '', $_POST['end_time'] ?? '', $_POST['location'] ?? '', 
                $_POST['max_volunteers'] ?? 1, $_POST['reserve_count'] ?? 0, 
                $_POST['activity_detail'] ?? '', $imagePath, $user['user_id']
            );
            echo "success";
        } catch (\\Exception $e) {
            echo "error";
        }
    }
"""
        class_end_idx = team_content.rfind('}')
        if class_end_idx != -1:
            team_content = team_content[:class_end_idx] + user_methods + team_content[class_end_idx:]
            
        with open('app/controllers/ActivityController.php', 'w', encoding='utf-8') as f:
            f.write(team_content)

def fix_skill_controller():
    with open('app/controllers/SkillController.php', 'r', encoding='utf-8') as f:
        content = f.read()

    idx = content.find('class SkillController', 25)
    if idx != -1:
        # If there are duplicate SkillController, we remove the first one.
        team_content = "<?php\n" + content[idx:]
        with open('app/controllers/SkillController.php', 'w', encoding='utf-8') as f:
            f.write(team_content)

fix_activity_model()
fix_activity_controller()
try: fix_skill_controller()
except: pass
print('Done!')
