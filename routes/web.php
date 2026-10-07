<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'user'   => ['UserController', 'index'],
        'volunteer' => ['VolunteerController', 'listApproved'],
        'volunteer_approve' => ['VolunteerController', 'index'],
        'activity' => ['ActivityController', 'index'],
        'skill' => ['SkillController', 'index'],
        'user/get' => ['UserController', 'get'],
        'volunteer' => ['MainController', 'index'],
        'volunteer_approve' => ['VolunteerController', 'index'],
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
        'volunteer_approve_table' => ['VolunteerController', 'getTable'],
        'volunteer_table' => ['VolunteerController', 'getApprovedTable'],
        'approveVolunteer' => ['VolunteerController', 'approve'],
        'rejectVolunteer' => ['VolunteerController', 'reject'],
        'activity_table' => ['ActivityController', 'getTable'],
        'addActivity' => ['ActivityController', 'add'],
        'getActivityById' => ['ActivityController', 'getById'],
        'updateActivity' => ['ActivityController', 'update'],
        'skill_table' => ['SkillController', 'getTable']

        'approveVolunteer'        => ['VolunteerController', 'approve'],
        'rejectVolunteer'         => ['VolunteerController', 'reject'],
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
