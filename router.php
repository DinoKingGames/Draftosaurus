<?php
session_start();


$request = $_SERVER['REQUEST_URI'];
$request = strtok($request, '?');

$basePath = '/Draftosaurus'; 
if (strpos($request, $basePath) === 0) {
    $request = substr($request, strlen($basePath));
}
$request = ltrim($request, '/');


if ($request === '' || $request === 'index.php') {
    require __DIR__ . '/Client/inicio.php'; 
    return;
}

$routes = [
    'contacto'      => 'contacto.php',
    'nosotros'      => 'nosotros.php',
    'jugar'         => 'jugar.php',
    'proximamente'  => 'proximamente.php',
    'seguimiento'   => 'seguimiento.php',
    '' => 'inicio.php',
];

if (array_key_exists($request, $routes)) {
    require __DIR__ . '/Client/' . $routes[$request];
    return;
}

if (isset($_GET['controller']) && isset($_GET['action'])) {
    $controller = ucfirst($_GET['controller']) . 'Controller';
    $action = $_GET['action'];
    $controllerPath = __DIR__ . '/Controllers/' . $controller . '.php';
    if (file_exists($controllerPath)) {
        require_once $controllerPath;
        if (class_exists($controller)) {
            $ctrl = new $controller();
            if (method_exists($ctrl, $action)) {
                $ctrl->$action();
                return;
            }
        }
    }
    
    http_response_code(404);
    echo "Página no encontrada (controlador/acción).";
    return;
}


http_response_code(404);
echo "Página no encontrada.";