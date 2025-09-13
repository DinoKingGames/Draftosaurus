<?php
declare(strict_types=1);

require_once __DIR__ . '/../app/config/app.php';
require_once APP_PATH . '/config/database.php';
require_once APP_PATH . '/Repositories/UsuarioRepository.php';
require_once APP_PATH . '/Controllers/Api/AuthController.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_set_cookie_params([
        'httponly' => true,
        'secure'   => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'samesite' => 'Lax',
        'path'     => '/',
    ]);
    session_start();
}

function json_response($data, int $status = 200): void {
    http_response_code($status);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

$method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
$path = '/';
if (!empty($_SERVER['PATH_INFO'])) {
    $path = $_SERVER['PATH_INFO'];
} else {
    $path = $_GET['r'] ?? '/';
}
$path = '/' . ltrim(parse_url($path, PHP_URL_PATH) ?? '/', '/');

$raw = file_get_contents('php://input') ?: '';
$body = [];
if ($raw !== '' && stripos($_SERVER['CONTENT_TYPE'] ?? '', 'application/json') !== false) {
    $tmp = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE && is_array($tmp)) $body = $tmp;
}

try {
    switch (true) {
        case $method === 'GET' && $path === '/health':
            json_response(['ok' => true, 'ts' => time()]);

        case $method === 'GET' && $path === '/session':
            AuthController::session();
            break;

        case $method === 'POST' && $path === '/auth/register':
            AuthController::register($body + $_POST);
            break;

        case $method === 'POST' && $path === '/auth/login':
            AuthController::login($body + $_POST);
            break;

        case $method === 'POST' && $path === '/auth/logout':
            AuthController::logout();
            break;

        default:
            json_response(['error' => 'Not Found', 'path' => $path], 404);
    }
} catch (Throwable $e) {
    // error_log($e);
    json_response(['error' => 'Server error'], 500);
}