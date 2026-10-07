<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'customer' => ['CustomerController', 'index'],
        'user'   => ['UserController', 'index'],
        'volunteer' => ['VolunteerController', 'listApproved'],
        'volunteer_approve' => ['VolunteerController', 'index'],
        'activity' => ['ActivityController', 'index'],
        'skill' => ['SkillController', 'index'],
    ],
    'POST' => [
        'auth/login'  => ['AuthController', 'processLogin'],
        'volunteer_approve_table' => ['VolunteerController', 'getTable'],
        'volunteer_table' => ['VolunteerController', 'getApprovedTable'],
        'approveVolunteer' => ['VolunteerController', 'approve'],
        'rejectVolunteer' => ['VolunteerController', 'reject'],
        'activity_table' => ['ActivityController', 'getTable'],
        'addActivity' => ['ActivityController', 'add'],
        'getActivityById' => ['ActivityController', 'getById'],
        'updateActivity' => ['ActivityController', 'update'],
        'skill_table' => ['SkillController', 'getTable']
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