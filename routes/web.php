<?php

$requestMethod = $_SERVER['REQUEST_METHOD'];

$routes = [
    'GET' => [
        'login'  => ['AuthController', 'showLogin'],
        'main'   => ['MainController', 'index'],
        'user'   => ['UserController', 'index'],
        'user/get' => ['UserController', 'get'],
        'volunteer' => ['MainController', 'index'],
        'volunteer_approve' => ['MainController', 'index'],
        'activity' => ['ActivityController', 'index'],
        'activity/get' => ['ActivityController', 'get'],
        'skill' => ['SkillController', 'index'],
        'skill/get' => ['SkillController', 'get'],
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