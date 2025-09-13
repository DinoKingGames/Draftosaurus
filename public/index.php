<?php
require_once __DIR__ . '/../app/config/app.php';
require_once APP_PATH . '/Core/Router.php';

$router = new Router();
$router->handle();